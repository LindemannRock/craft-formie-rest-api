<?php
/**
 * LindemannRock Formie REST API
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\formierestapi\tests\Integration;

use lindemannrock\formierestapi\controllers\SettingsController;
use lindemannrock\formierestapi\FormieRestApi;
use lindemannrock\formierestapi\tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @since 3.6.0
 */
#[CoversClass(SettingsController::class)]
final class SettingsControllerSectionScopeTest extends TestCase
{
    public function testSettingsSectionsMatchRenderedFormScopes(): void
    {
        $controller = new SettingsController('settings', FormieRestApi::$plugin);
        $method = new \ReflectionMethod($controller, 'validationAttributesForSection');

        $expected = [
            'general' => [
                'pluginName',
            ],
            'interface' => [
                'timeFormat',
                'monthFormat',
                'dateOrder',
                'dateSeparator',
                'showSeconds',
            ],
            'test' => [],
        ];

        foreach ($expected as $section => $attributes) {
            self::assertSame($attributes, $method->invoke($controller, $section), "Unexpected {$section} settings scope.");
        }
    }
}
