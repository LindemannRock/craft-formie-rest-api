<?php
/**
 * Formie Transformer Service
 *
 * Stateless transformation helpers shared between the production and test API
 * controllers. Centralises field-value processing, form-field metadata, and
 * submission-content shaping so the two controllers can't drift apart again.
 *
 * @author LindemannRock
 * @copyright Copyright (c) 2026 LindemannRock
 * @link https://lindemannrock.com
 * @package FormieRestApi
 * @since 3.4.0
 */

namespace lindemannrock\formierestapi\services;

use craft\base\Component;
use craft\base\FieldInterface as CraftFieldInterface;
use craft\helpers\Json;
use verbb\formie\base\SubFieldInterface;
use verbb\formie\elements\Form;
use verbb\formie\elements\Submission;

class FormieTransformerService extends Component
{
    /**
     * Field types that have no submission value to transform (decorative /
     * structural fields). Filtered out of submission-field output.
     *
     * @var list<string>
     */
    private const SKIP_FIELD_TYPES = ['Html', 'Heading', 'Section', 'Summary', 'Paragraph'];

    /**
     * Settings keys that drive UI rendering rather than data behaviour.
     * Grouped under `appearance` in the form-fields metadata response.
     *
     * @var list<string>
     */
    private const APPEARANCE_KEYS = [
        'visibility', 'layout', 'labelPosition', 'instructionsPosition', 'displayType',
        'subFieldLabelPosition',
    ];

    /**
     * Settings keys grouped under `advanced` — matches Formie's CP "Advanced" tab.
     *
     * @var list<string>
     */
    private const ADVANCED_KEYS = [
        'cssClasses', 'containerAttributes', 'inputAttributes', 'enableContentEncryption',
    ];

    /**
     * Settings keys grouped under `conditions` — show/hide rules.
     *
     * @var list<string>
     */
    private const CONDITIONS_KEYS = ['enableConditions', 'conditions'];

    /**
     * Settings keys excluded from API output entirely. Mix of:
     *   - Already exposed at top level (handle/label/required/instructions/errorMessage)
     *   - Server-only behaviour (prePopulate, includeInEmail, emailValue,
     *     enableContentEncryption, syncId/layout/page/row IDs, matchField)
     *
     * @var list<string>
     */
    private const DROP_KEYS = [
        // Already top-level
        'handle', 'label', 'required', 'instructions', 'errorMessage',
        // Server-only / sensitive
        'prePopulate', 'includeInEmail', 'emailValue',
        // Layout/identity internals
        'syncId', 'layoutId', 'pageId', 'rowId', 'sortOrder',
        // Server-side validation pairing
        'matchField',
        // Inherited from Craft's base Field but no Formie UI for top-level fields.
        // Subfield recursion still surfaces `enabled` explicitly where it matters.
        'enabled',
    ];

    /**
     * Build the form-fields metadata block returned by `/forms` list and detail
     * endpoints. One entry per custom field.
     *
     * @return list<array{handle: string, label: string, type: string, required: bool, instructions: string|null, errorMessage: string|null, visibility: string|null}>
     */
    public function getFormFields(Form $form): array
    {
        $fields = [];

        foreach ($form->getCustomFields() as $field) {
            $fields[] = $this->fieldMetadata($field);
        }

        return $fields;
    }

    /**
     * Build the metadata block for one field. Recursively walks Formie's
     * SubField fields (Name multi-mode, Date, Address, Phone) to expose
     * their per-subfield settings (`required`, `errorMessage`, `visibility`,
     * `enabled`) — each subfield is independently configurable in Formie.
     *
     * @return array<string, mixed>
     */
    private function fieldMetadata(CraftFieldInterface $field): array
    {
        // Universal top-level (General-tab equivalents)
        $entry = [
            'handle' => $field->handle,
            'label' => property_exists($field, 'label') ? $field->label : $field->handle,
            'type' => $this->shortFieldType($field),
            'required' => (bool) $field->required,
            'instructions' => $field->instructions,
            'errorMessage' => property_exists($field, 'errorMessage') ? $field->errorMessage : null,
        ];

        // Pull every configurable setting from Formie's API (driven by each
        // field's `settingsAttributes()` declaration), then split into
        // appearance (UI rendering hints) vs settings (data behaviour),
        // dropping server-only and CP-internal keys we don't expose.
        $allSettings = method_exists($field, 'getSettings') ? $field->getSettings() : [];

        $appearance = ['visibility' => $this->resolveVisibility($field)];
        $settings = [];
        $advanced = [];
        $conditions = [];

        foreach ($allSettings as $key => $value) {
            if (in_array($key, self::DROP_KEYS, true) || $value === null || $value === '' || $value === []) {
                continue;
            }
            // Normalise position class names → short names ("verbb\\formie\\positions\\Hidden" → "Hidden")
            if (in_array($key, ['labelPosition', 'instructionsPosition', 'subFieldLabelPosition'], true)
                && is_string($value) && str_starts_with($value, 'verbb\\formie\\positions\\')
            ) {
                $value = $this->shortClassName($value);
            }

            if (in_array($key, self::APPEARANCE_KEYS, true)) {
                $appearance[$key] = $value;
            } elseif (in_array($key, self::ADVANCED_KEYS, true)) {
                $advanced[$key] = $value;
            } elseif (in_array($key, self::CONDITIONS_KEYS, true)) {
                $conditions[$key] = $value;
            } else {
                $settings[$key] = $value;
            }
        }

        // Agree's `description` is stored as a rich-text array; render to HTML.
        if (isset($settings['description']) && method_exists($field, 'getDescriptionHtml')) {
            try {
                $settings['description'] = (string) $field->getDescriptionHtml();
            } catch (\Throwable) {
                unset($settings['description']);
            }
        }

        $entry['appearance'] = $appearance;
        if ($settings !== []) {
            $entry['settings'] = $settings;
        }
        if ($advanced !== []) {
            $entry['advanced'] = $advanced;
        }
        // Only emit conditions block if the field opted-in. Flatten Formie's
        // nested `conditions.conditions.conditions` shape into a friendlier
        // single-level structure: { enabled, showRule, conditionRule, rules }.
        if (!empty($conditions['enableConditions'])) {
            $flat = ['enabled' => true];
            $inner = $conditions['conditions'] ?? null;
            if (is_array($inner)) {
                if (isset($inner['showRule'])) {
                    $flat['showRule'] = $inner['showRule'];
                }
                if (isset($inner['conditionRule'])) {
                    $flat['conditionRule'] = $inner['conditionRule'];
                }
                if (isset($inner['conditions']) && is_array($inner['conditions'])) {
                    $flat['rules'] = $inner['conditions'];
                }
            }
            $entry['conditions'] = $flat;
        }

        // SubField fields (Name multi, Date, Address) expose their sub-components.
        // Each subfield is independently configurable — recurse so UI consumers
        // know per-subfield required/error/visibility/enabled.
        if ($field instanceof SubFieldInterface && method_exists($field, 'getFieldLayout')) {
            $layout = $field->getFieldLayout();
            if ($layout !== null) {
                $subFields = [];
                foreach ($layout->getCustomFields() as $sub) {
                    if ($sub instanceof CraftFieldInterface) {
                        $subEntry = $this->fieldMetadata($sub);
                        if (property_exists($sub, 'enabled')) {
                            $subEntry['enabled'] = (bool) $sub->enabled;
                        }
                        $subFields[] = $subEntry;
                    }
                }
                if ($subFields !== []) {
                    $entry['subFields'] = $subFields;
                }
            }
        }

        return $entry;
    }

    /**
     * Build the per-submission `fields` map (handle => {label, handle, type, value, …}).
     * Skips structural/decorative field types and adds Rating-field-specific
     * min/max/ratingType metadata when applicable.
     *
     * @return array<string, array<string, mixed>>
     */
    public function transformSubmissionFields(Submission $submission): array
    {
        $form = $submission->getForm();
        if ($form === null) {
            return [];
        }

        $fieldsByHandle = [];

        // Walk the form's custom fields and pull each value via getFieldValue() —
        // this returns Formie's rich field-value models (Phone, Name, etc.)
        // rather than the flattened strings that getSerializedFieldValues()
        // produces. Critical for fields whose API output should preserve
        // sub-properties (Phone.country, Name.prefix/firstName/middleName/lastName).
        foreach ($form->getCustomFields() as $field) {
            if (!$field instanceof CraftFieldInterface) {
                continue;
            }
            $value = $submission->getFieldValue($field->handle);
            $entry = $this->buildFieldEntry($field, $value);
            if ($entry !== null) {
                $fieldsByHandle[$field->handle] = $entry;
            }
        }

        return $fieldsByHandle;
    }

    /**
     * Process a single submission field's raw value into the API output shape.
     * Produces JSON-friendly scalars/arrays for known Formie field types and
     * falls back to a JSON-encoded string for anything unrecognised.
     */
    public function processFieldValue(CraftFieldInterface $field, mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        return match (get_class($field)) {
            'verbb\formie\fields\Number' => is_numeric($value) ? (float) $value : null,
            'verbb\formie\fields\Dropdown', 'verbb\formie\fields\Radio'
                => is_array($value) ? ($value['value'] ?? $value[0] ?? null) : $value,
            'verbb\formie\fields\Checkboxes'
                => is_array($value)
                    ? array_map(static fn($item) => is_array($item) ? ($item['value'] ?? $item) : $item, $value)
                    : $value,
            'verbb\formie\fields\Date'
                => $value instanceof \DateTime ? $value->format('c') : $value,
            'verbb\formie\fields\Name' => $this->processNameValue($value),
            'verbb\formie\fields\Phone' => $this->processPhoneValue($value),
            'verbb\formie\fields\Email'
                => is_array($value) ? ($value['email'] ?? $value[0] ?? null) : $value,
            'verbb\formie\fields\FileUpload' => $this->processFileUploadValue($value),
            default => is_string($value) ? $value : Json::encode($value),
        };
    }

    /**
     * Build a single `fields` map entry, or null if the field is a structural
     * type that should be skipped (Html, Heading, Section, etc.).
     *
     * @return array<string, mixed>|null
     */
    private function buildFieldEntry(CraftFieldInterface $field, mixed $value): ?array
    {
        $fieldType = $this->shortFieldType($field);
        if (in_array($fieldType, self::SKIP_FIELD_TYPES, true)) {
            return null;
        }

        $label = $field->handle;
        if (property_exists($field, 'label') && isset($field->label)) {
            $label = $field->label;
        }

        $entry = [
            'label' => $label,
            'handle' => $field->handle,
            'type' => $fieldType,
            'value' => $this->processFieldValue($field, $value),
            'required' => (bool) $field->required,
        ];

        // Rating-field-specific metadata (when the LindemannRock rating-field plugin is installed)
        if ($fieldType === 'Rating' && get_class($field) === 'lindemannrock\formieratingfield\fields\Rating') {
            $entry['minValue'] = (string) $field->minValue;
            $entry['maxValue'] = (string) $field->maxValue;
            $entry['ratingType'] = $field->ratingType;
        }

        return $entry;
    }

    /**
     * Render Formie's Name field. Values come from `getFieldValue()` as
     * `verbb\formie\models\Name` instances (multi-mode) or plain strings
     * (single-mode). We use Formie's own API:
     *  - `isMultiple` distinguishes the two modes
     *  - `getName()` → short name (first + last)
     *  - `getFullName()` → full (prefixOption + first + middle + last)
     *  - Raw properties: `prefix`, `prefixOption`, `firstName`, `middleName`,
     *    `lastName`, `name`
     *
     * Single mode returns a plain string; multi mode returns an object with
     * Formie's `fullName` plus every populated raw subfield.
     */
    private function processNameValue(mixed $value): mixed
    {
        if (is_string($value)) {
            return $value !== '' ? $value : null;
        }
        if (!is_object($value)) {
            return $value;
        }

        // Single mode → plain string via Formie's API
        if (property_exists($value, 'isMultiple') && !$value->isMultiple) {
            $name = method_exists($value, 'getName')
                ? $value->getName()
                : (method_exists($value, '__toString') ? (string) $value : '');
            return $name !== '' ? $name : null;
        }

        // Multi mode → object with Formie's fullName + every populated subfield
        $result = [];
        if (method_exists($value, 'getFullName')) {
            $result['fullName'] = $value->getFullName();
        }
        foreach (['prefix', 'prefixOption', 'firstName', 'middleName', 'lastName'] as $k) {
            if (property_exists($value, $k) && $value->$k !== null && $value->$k !== '') {
                $result[$k] = $value->$k;
            }
        }
        return $result;
    }

    /**
     * Render Formie's Phone field. Values come from `getFieldValue()` as
     * `verbb\formie\models\Phone` instances. Formie already exposes the data
     * we need — we just call its API:
     *  - `number`, `country`, `hasCountryCode` (public properties)
     *  - `getCountryCode()` → e.g. `"+966"` (dial code)
     *  - `getCountryName()` → e.g. `"Saudi Arabia"`
     *  - `(string) $model` → international format `"+966 50 344 1351"`
     *
     * Simple-mode phones (no country selector) return a plain string for
     * backward compatibility; country-mode phones return the rich object.
     */
    private function processPhoneValue(mixed $value): mixed
    {
        if (!is_object($value)) {
            if (is_array($value)) {
                return $value['phoneNumber'] ?? $value['number'] ?? null;
            }
            return $value;
        }

        $number = property_exists($value, 'number') ? $value->number : null;
        $country = property_exists($value, 'country') ? $value->country : null;
        $hasCountryCode = property_exists($value, 'hasCountryCode') ? (bool) $value->hasCountryCode : false;

        // Simple mode: no country code configured → return the plain number
        if (!$hasCountryCode && ($country === null || $country === '')) {
            return $number;
        }

        $result = ['number' => $number];
        if ($country !== null && $country !== '') {
            $result['country'] = $country;
        }

        // Use Formie's helpers when available (verbb\formie\models\Phone)
        if (method_exists($value, 'getCountryCode')) {
            $dialCode = $value->getCountryCode();
            if ($dialCode !== '') {
                $result['countryCode'] = $dialCode;
            }
        }
        if (method_exists($value, 'getCountryName')) {
            $countryName = $value->getCountryName();
            if ($countryName !== '') {
                $result['countryName'] = $countryName;
            }
        }
        // International format via __toString — e.g. "+966 50 344 1351"
        $international = (string) $value;
        if ($international !== '' && $international !== ($number ?? '')) {
            $result['international'] = $international;
        }

        $result['hasCountryCode'] = $hasCountryCode;
        return $result;
    }

    /**
     * Render Formie's FileUpload field value as `[{filename, url}, …]`.
     *
     * @return list<array{filename: string, url: string|null}>|null
     */
    private function processFileUploadValue(mixed $value): ?array
    {
        if (!is_iterable($value)) {
            return null;
        }

        $assets = [];
        foreach ($value as $asset) {
            $assets[] = [
                'filename' => $asset->filename,
                'url' => $asset->getUrl(),
            ];
        }
        return $assets ?: null;
    }

    /**
     * Short class name for a Formie field, e.g. `verbb\formie\fields\Email` → `Email`.
     */
    private function shortFieldType(CraftFieldInterface $field): string
    {
        return $this->shortClassName(get_class($field));
    }

    /**
     * Strip the namespace from a fully-qualified class name.
     */
    private function shortClassName(string $fqcn): string
    {
        return basename(str_replace('\\', '/', $fqcn));
    }

    /**
     * Resolve a field's visibility to one of `visible`/`hidden`/`disabled`.
     * Formie stores the default state as null/empty; we coerce so consumers
     * always get an explicit value.
     */
    private function resolveVisibility(CraftFieldInterface $field): string
    {
        if (!property_exists($field, 'visibility')) {
            return 'visible';
        }
        $value = $field->visibility;
        return is_string($value) && $value !== '' ? $value : 'visible';
    }
}
