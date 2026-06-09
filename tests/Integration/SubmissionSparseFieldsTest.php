<?php
/**
 * LindemannRock Formie REST API
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\formierestapi\tests\Integration;

use lindemannrock\formierestapi\controllers\ApiController;
use lindemannrock\formierestapi\services\FormieTransformerService;
use lindemannrock\formierestapi\tests\TestCase;
use verbb\formie\elements\Form;
use verbb\formie\elements\Submission;
use verbb\formie\fields\SingleLineText;
use verbb\formie\models\FieldLayout;

/**
 * Sparse fieldset support: `?fields=a,b` returns only the requested field
 * handles in each submission's `fields` map, and the transformer skips the
 * (expensive) per-field value resolution for everything else.
 *
 * @since 3.8.0
 */
final class SubmissionSparseFieldsTest extends TestCase
{
    public function testSparseSelectionReturnsOnlyRequestedFields(): void
    {
        $form = $this->seedFormWithFields(['alpha', 'bravo', 'charlie']);
        $submission = $this->seedSubmission($form, ['alpha' => 'A', 'bravo' => 'B', 'charlie' => 'C']);

        $fields = (new FormieTransformerService())->transformSubmissionFields($submission, ['alpha', 'charlie']);

        self::assertSame(['alpha', 'charlie'], array_keys($fields), 'Only requested handles are returned, in form order.');
        self::assertSame('A', $fields['alpha']['value']);
        self::assertSame('C', $fields['charlie']['value']);
    }

    public function testNullSelectionReturnsAllFields(): void
    {
        $form = $this->seedFormWithFields(['alpha', 'bravo']);
        $submission = $this->seedSubmission($form, ['alpha' => 'A', 'bravo' => 'B']);

        $fields = (new FormieTransformerService())->transformSubmissionFields($submission);

        self::assertSame(['alpha', 'bravo'], array_keys($fields), 'No selection returns the full field set (backward compatible).');
    }

    public function testUnknownHandleIsSilentlyIgnored(): void
    {
        $form = $this->seedFormWithFields(['alpha']);
        $submission = $this->seedSubmission($form, ['alpha' => 'A']);

        $fields = (new FormieTransformerService())->transformSubmissionFields($submission, ['alpha', 'doesNotExist']);

        self::assertSame(['alpha'], array_keys($fields), 'Unknown handles produce no entry and no error.');
    }

    public function testParseFieldListNormalisesInput(): void
    {
        $parse = new \ReflectionMethod(ApiController::class, 'parseFieldList');

        self::assertNull($parse->invoke(null, null), 'absent → all fields');
        self::assertNull($parse->invoke(null, ''), 'empty → all fields');
        self::assertNull($parse->invoke(null, '   '), 'whitespace → all fields');
        self::assertNull($parse->invoke(null, ', ,,'), 'only separators → all fields');
        self::assertNull($parse->invoke(null, ['a', 'b']), 'non-string (fields[]=) → all fields');
        self::assertSame(['a', 'b', 'c'], $parse->invoke(null, 'a,b,c'));
        self::assertSame(['a', 'b'], $parse->invoke(null, ' a , b '), 'trims whitespace');
        self::assertSame(['a', 'c'], $parse->invoke(null, 'a,,c'), 'drops blank entries');
    }

    /**
     * @param string[] $handles
     */
    private function seedFormWithFields(array $handles): Form
    {
        $form = new Form();
        $form->title = $this->nextTestMarker('Formie REST API Test ', 'form');
        $form->handle = $this->nextTestMarker('formieApiTest', 'form');

        $fieldConfigs = array_map(static fn(string $handle): array => [
            'type' => SingleLineText::class,
            'handle' => $handle,
            'label' => ucfirst($handle),
        ], $handles);

        $layout = new FieldLayout();
        $layout->setPages([
            ['label' => 'Page 1', 'rows' => [['fields' => $fieldConfigs]]],
        ]);
        $form->setFormLayout($layout);

        $this->saveTestElement($form);

        return $form;
    }

    /**
     * @param array<string, string> $values
     */
    private function seedSubmission(Form $form, array $values): Submission
    {
        $submission = new Submission();
        $submission->setForm($form);
        $submission->title = $this->nextTestMarker('formieApiTest', 'submission');
        foreach ($values as $handle => $value) {
            $submission->setFieldValue($handle, $value);
        }
        $this->saveTestElement($submission);

        return $submission;
    }
}
