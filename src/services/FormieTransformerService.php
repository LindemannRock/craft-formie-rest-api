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
     * Build the form-fields metadata block returned by `/forms` list and detail
     * endpoints. One entry per custom field.
     *
     * @return list<array{handle: string, label: string, type: string, required: bool, instructions: string|null, errorMessage: string|null, visibility: string|null}>
     */
    public function getFormFields(Form $form): array
    {
        $fields = [];

        foreach ($form->getCustomFields() as $field) {
            $fields[] = [
                'handle' => $field->handle,
                'label' => $field->label,
                'type' => $this->shortFieldType($field),
                'required' => (bool) $field->required,
                'instructions' => $field->instructions,
                'errorMessage' => property_exists($field, 'errorMessage') ? $field->errorMessage : null,
                // Formie stores "visible" as null/empty internally — coerce to the
                // explicit string so API consumers get one of: visible|hidden|disabled.
                'visibility' => $this->resolveVisibility($field),
            ];
        }

        return $fields;
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

        $content = $submission->getSerializedFieldValues();
        $fieldsByHandle = [];

        if (!empty($content)) {
            // Happy path — we have serialized values; iterate over those
            foreach ($content as $handle => $value) {
                $field = $form->getFieldByHandle($handle);
                if ($field !== null && !$field instanceof CraftFieldInterface) {
                    // Formie's FieldInterface doesn't formally extend Craft's, but
                    // every concrete Formie field does — guard for static analysis.
                    $field = null;
                }
                if ($field === null) {
                    // Field not in current layout but submission has a value for it
                    $fieldsByHandle[$handle] = [
                        'label' => $handle,
                        'handle' => $handle,
                        'type' => 'unknown',
                        'value' => $value,
                        'required' => false,
                    ];
                    continue;
                }

                $entry = $this->buildFieldEntry($field, $value);
                if ($entry !== null) {
                    $fieldsByHandle[$handle] = $entry;
                }
            }
            return $fieldsByHandle;
        }

        // Fallback — no serialized content; walk the layout and read live values
        $fieldLayout = $form->getFieldLayout();
        if ($fieldLayout === null) {
            return [];
        }

        foreach ($fieldLayout->getCustomFields() as $field) {
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
            'verbb\formie\fields\Name'
                => is_array($value)
                    ? [
                        'firstName' => $value['firstName'] ?? null,
                        'lastName' => $value['lastName'] ?? null,
                        'fullName' => trim(($value['firstName'] ?? '') . ' ' . ($value['lastName'] ?? '')),
                    ]
                    : $value,
            'verbb\formie\fields\Phone'
                => is_array($value) ? ($value['phoneNumber'] ?? $value['number'] ?? null) : $value,
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
        return basename(str_replace('\\', '/', get_class($field)));
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
