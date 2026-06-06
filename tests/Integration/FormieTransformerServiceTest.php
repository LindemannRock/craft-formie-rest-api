<?php
/**
 * LindemannRock Formie REST API
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\formierestapi\tests\Integration;

use lindemannrock\formieratingfield\fields\Rating;
use lindemannrock\formierestapi\services\FormieTransformerService;
use lindemannrock\formierestapi\tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @since 3.6.0
 */
#[CoversClass(FormieTransformerService::class)]
final class FormieTransformerServiceTest extends TestCase
{
    public function testRatingFieldMetadataSerializesNumericBoundsAsIntegers(): void
    {
        $field = new Rating([
            'handle' => 'productRating',
            'label' => 'Product Rating',
            'required' => true,
        ]);
        $field->minValue = 1;
        $field->maxValue = 5;
        $field->ratingType = Rating::RATING_TYPE_STAR;

        $method = new \ReflectionMethod(FormieTransformerService::class, 'buildFieldEntry');
        $entry = $method->invoke(new FormieTransformerService(), $field, 4);

        self::assertIsArray($entry);
        self::assertSame(1, $entry['minValue']);
        self::assertSame(5, $entry['maxValue']);
        self::assertSame(Rating::RATING_TYPE_STAR, $entry['ratingType']);
    }
}
