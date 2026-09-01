<?php
/**
 * LindemannRock Formie REST API
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\formierestapi\tests\Integration;

use craft\base\ElementInterface;
use craft\commerce\elements\Product;
use craft\commerce\elements\Variant;
use craft\elements\Category;
use craft\elements\db\ElementQuery;
use craft\elements\Entry;
use craft\elements\Tag;
use craft\elements\User;
use lindemannrock\formierestapi\services\FormieTransformerService;
use lindemannrock\formierestapi\tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use verbb\formie\elements\Form;
use verbb\formie\elements\Submission;
use verbb\formie\fields\Address;
use verbb\formie\fields\Categories;
use verbb\formie\fields\Entries;
use verbb\formie\fields\Group;
use verbb\formie\fields\Password;
use verbb\formie\fields\Products;
use verbb\formie\fields\Recipients;
use verbb\formie\fields\Repeater;
use verbb\formie\fields\Signature;
use verbb\formie\fields\SingleLineText;
use verbb\formie\fields\Table;
use verbb\formie\fields\Tags;
use verbb\formie\fields\Users;
use verbb\formie\fields\Variants;
use verbb\formie\models\Address as AddressValue;
use verbb\formie\models\FieldLayout;

/**
 * @since 3.10.2
 */
#[CoversClass(FormieTransformerService::class)]
final class FormieAdvancedFieldValuesTest extends TestCase
{
    public function testSignatureSubmissionContentIsReturnedExactlyAndRemainsSparseSelectable(): void
    {
        $form = new Form();
        $form->title = $this->nextTestMarker('Formie REST API Signature ', 'form');
        $form->handle = $this->nextTestMarker('formieApiSignature', 'form');

        $layout = new FieldLayout();
        $layout->setPages([
            [
                'label' => 'Page 1',
                'rows' => [[
                    'fields' => [
                        ['type' => Signature::class, 'handle' => 'approvalSignature', 'label' => 'Approval signature'],
                        ['type' => SingleLineText::class, 'handle' => 'notes', 'label' => 'Notes'],
                    ],
                ]],
            ],
        ]);
        $form->setFormLayout($layout);
        $this->saveTestElement($form);

        $storedSignature = 'data:image/png;base64,' . base64_encode('formie-submission-signature-not-an-api-hmac');
        $submission = new Submission();
        $submission->setForm($form);
        $submission->title = $this->nextTestMarker('Formie REST API Signature ', 'submission');
        $submission->setFieldValue('approvalSignature', $storedSignature);
        $submission->setFieldValue('notes', 'Authorized reader note');
        $this->saveTestElement($submission);

        $transformer = new FormieTransformerService();
        $allFields = $transformer->transformSubmissionFields($submission);

        self::assertArrayHasKey('approvalSignature', $allFields, 'A populated Signature field must not be omitted.');
        self::assertSame('Signature', $allFields['approvalSignature']['type']);
        self::assertSame($storedSignature, $allFields['approvalSignature']['value'], 'The stored data URL must not be transformed or truncated.');
        self::assertNotSame(base64_decode(substr($storedSignature, strlen('data:image/png;base64,')), true), $allFields['approvalSignature']['value'], 'The stored data URL must not be decoded.');
        self::assertNotSame(hash_hmac('sha256', "GET\n/api/v1/formie/submissions/1\n1234567890\n", 'signing-secret'), $allFields['approvalSignature']['value'], 'Formie submission content is not the API request HMAC signature.');

        $signatureOnly = $transformer->transformSubmissionFields($submission, ['approvalSignature']);
        self::assertSame(['approvalSignature'], array_keys($signatureOnly));
        self::assertSame($storedSignature, $signatureOnly['approvalSignature']['value']);

        $notesOnly = $transformer->transformSubmissionFields($submission, ['notes']);
        self::assertSame(['notes'], array_keys($notesOnly), 'Omitting the Signature handle from fields= must omit its content.');
        self::assertSame('Authorized reader note', $notesOnly['notes']['value']);

        $signatureField = new Signature(['handle' => 'emptySignature', 'label' => 'Empty signature']);
        self::assertNull($transformer->processFieldValue($signatureField, ''), 'An empty Signature remains null.');
    }

    public function testStructuredAndNestedFieldFamiliesKeepTheirDocumentedShapes(): void
    {
        $transformer = new FormieTransformerService();

        $addressField = new Address(['handle' => 'officeAddress', 'label' => 'Office address']);
        $address = new AddressValue([
            'address1' => '123 Test Street',
            'city' => 'Dubai',
            'state' => 'Dubai',
            'zip' => '00000',
            'country' => 'AE',
            'countryOption' => 'United Arab Emirates',
        ]);
        self::assertSame([
            'address1' => '123 Test Street',
            'city' => 'Dubai',
            'state' => 'Dubai',
            'zip' => '00000',
            'country' => 'AE',
            'countryOption' => 'United Arab Emirates',
        ], $transformer->processFieldValue($addressField, $address));

        $group = $this->nestedField(new Group(['handle' => 'contactGroup', 'label' => 'Contact group']));
        self::assertSame(
            ['nestedText' => 'Group value'],
            $transformer->processFieldValue($group, $group->normalizeValue(['nestedText' => 'Group value'], null)),
        );

        $repeater = $this->nestedField(new Repeater(['handle' => 'contactRows', 'label' => 'Contact rows']));
        self::assertSame(
            [['nestedText' => 'First row'], ['nestedText' => 'Second row']],
            $transformer->processFieldValue($repeater, $repeater->normalizeValue([
                ['nestedText' => 'First row'],
                ['nestedText' => 'Second row'],
            ], null)),
        );

        $table = new Table([
            'handle' => 'orderLines',
            'label' => 'Order lines',
            'columns' => [
                'col1' => ['heading' => 'Item', 'handle' => 'item', 'type' => 'singleline'],
                'col2' => ['heading' => 'Quantity', 'handle' => 'quantity', 'type' => 'number'],
                'col3' => ['heading' => 'Approved', 'handle' => 'approved', 'type' => 'checkbox'],
                'col4' => ['heading' => 'Divider', 'handle' => 'divider', 'type' => 'heading'],
            ],
        ]);
        self::assertSame([
            ['item' => 'Widget', 'quantity' => 2.0, 'approved' => true],
        ], $transformer->processFieldValue($table, [[
            'col1' => 'Widget',
            'col2' => '2',
            'col3' => '1',
            'col4' => 'must not leak',
        ]]));

        $recipients = new Recipients([
            'handle' => 'notifyTeams',
            'label' => 'Notify teams',
            'displayType' => 'checkboxes',
            'options' => [
                ['label' => 'Support', 'value' => 'support@example.test'],
                ['label' => 'Sales', 'value' => 'sales@example.test'],
            ],
        ]);
        self::assertSame([
            ['label' => 'Support', 'value' => 'support@example.test'],
            ['label' => 'Sales', 'value' => 'sales@example.test'],
        ], $transformer->processFieldValue(
            $recipients,
            $recipients->normalizeValue(['support@example.test', 'sales@example.test'], null),
        ));

        $password = new Password(['handle' => 'accountPassword', 'label' => 'Account password']);
        self::assertNull($transformer->processFieldValue($password, '$2y$13$stored-password-hash-must-not-leak'));
    }

    public function testEverySupportedRelationshipFamilyReturnsElementShapes(): void
    {
        $transformer = new FormieTransformerService();

        $entry = new Entry(['id' => 101, 'title' => 'Reference entry', 'slug' => 'reference-entry']);
        $category = new Category(['id' => 102, 'title' => 'Reference category', 'slug' => 'reference-category']);
        $tag = new Tag(['id' => 103, 'title' => 'Reference tag', 'slug' => 'reference-tag']);
        $user = new User([
            'id' => 104,
            'fullName' => 'API Reader',
            'email' => 'reader@example.test',
            'username' => 'api-reader',
        ]);
        $product = new Product(['id' => 105, 'title' => 'Reference product', 'slug' => 'reference-product']);
        $variant = new Variant(['id' => 106, 'title' => 'Reference variant', 'slug' => 'reference-variant']);

        $cases = [
            [new Entries(['handle' => 'relatedEntries']), $entry, ['id' => 101, 'title' => 'Reference entry', 'slug' => 'reference-entry']],
            [new Categories(['handle' => 'relatedCategories']), $category, ['id' => 102, 'title' => 'Reference category', 'slug' => 'reference-category']],
            [new Tags(['handle' => 'relatedTags']), $tag, ['id' => 103, 'title' => 'Reference tag', 'slug' => 'reference-tag']],
            [new Users(['handle' => 'relatedUsers']), $user, ['id' => 104, 'fullName' => 'API Reader', 'email' => 'reader@example.test', 'username' => 'api-reader']],
            [new Products(['handle' => 'relatedProducts']), $product, ['id' => 105, 'title' => 'Reference product', 'slug' => 'reference-product']],
            [new Variants(['handle' => 'relatedVariants']), $variant, ['id' => 106, 'title' => 'Reference variant', 'slug' => 'reference-variant']],
        ];

        foreach ($cases as [$field, $element, $expected]) {
            self::assertSame([$expected], $transformer->processFieldValue(
                $field,
                $this->cachedElementQuery($element),
            ));
        }
    }

    public function testGenericFallbackKeepsStringsAndJsonEncodesStructuredValues(): void
    {
        $transformer = new FormieTransformerService();
        $field = new SingleLineText(['handle' => 'customValue', 'label' => 'Custom value']);

        self::assertSame('Exact string', $transformer->processFieldValue($field, 'Exact string'));
        self::assertSame('{"nested":"value"}', $transformer->processFieldValue($field, ['nested' => 'value']));
    }

    /**
     * @template T of Group|Repeater
     * @param T $field
     * @return T
     */
    private function nestedField(Group|Repeater $field): Group|Repeater
    {
        $field->setRows([[
            'fields' => [[
                'type' => SingleLineText::class,
                'handle' => 'nestedText',
                'label' => 'Nested text',
            ]],
        ]]);

        return $field;
    }

    private function cachedElementQuery(ElementInterface $element): ElementQuery
    {
        $query = new ElementQuery($element::class);
        $query->setCachedResult([$element]);

        return $query;
    }
}
