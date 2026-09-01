<?php
/**
 * LindemannRock Formie REST API
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\formierestapi\tests\Integration;

use Composer\Semver\Semver;
use Craft;
use lindemannrock\base\helpers\PluginHelper;
use lindemannrock\formierestapi\models\Settings;
use lindemannrock\formierestapi\tests\TestCase;
use lindemannrock\logginglibrary\LoggingLibrary;

/**
 * Keeps package constraints, public requirements, and required shared runtime
 * surfaces aligned with the supported dependency floor.
 *
 * @since 3.10.2
 */
final class DependencyContractTest extends TestCase
{
    private const REQUIREMENTS = [
        'craftcms/cms' => '^5.10',
        'lindemannrock/craft-plugin-base' => '^5.38.2',
        'lindemannrock/craft-logging-library' => '^5.18.2',
    ];

    public function testComposerConstraintsEnforceApprovedFloors(): void
    {
        $composer = $this->composerMetadata();

        foreach (self::REQUIREMENTS as $package => $constraint) {
            self::assertSame($constraint, $composer['require'][$package] ?? null, $package);
        }

        self::assertTrue(Semver::satisfies('5.10.0', self::REQUIREMENTS['craftcms/cms']));
        self::assertFalse(Semver::satisfies('5.9.99', self::REQUIREMENTS['craftcms/cms']));
        self::assertTrue(Semver::satisfies('5.38.2', self::REQUIREMENTS['lindemannrock/craft-plugin-base']));
        self::assertFalse(Semver::satisfies('5.38.1', self::REQUIREMENTS['lindemannrock/craft-plugin-base']));
        self::assertTrue(Semver::satisfies('5.18.2', self::REQUIREMENTS['lindemannrock/craft-logging-library']));
        self::assertFalse(Semver::satisfies('5.18.1', self::REQUIREMENTS['lindemannrock/craft-logging-library']));
        self::assertSame('^1.12.33', $composer['require-dev']['phpstan/phpstan'] ?? null);
    }

    public function testPublicRequirementsAndBootstrapDiagnosticMatchComposer(): void
    {
        $readme = $this->packageFile('README.md');
        $requirements = $this->packageFile('docs/get-started/requirements.md');
        $bootstrap = $this->packageFile('tests/bootstrap.php');

        self::assertStringContainsString('Craft%20CMS-5.10%2B', $readme);
        self::assertStringContainsString('- Craft CMS 5.10 or greater', $readme);
        self::assertStringContainsString('- [Base](https://github.com/LindemannRock/craft-plugin-base) 5.38.2 or greater', $readme);
        self::assertStringContainsString('- [Logging Library](https://github.com/LindemannRock/craft-logging-library) 5.18.2+ (required by Composer; install in CP for log viewing)', $readme);

        self::assertStringContainsString('| [Craft CMS](https://craftcms.com/) | 5.10+ |', $requirements);
        self::assertStringContainsString('| [lindemannrock/craft-plugin-base](https://github.com/LindemannRock/craft-plugin-base) | 5.38.2+ |', $requirements);
        self::assertStringContainsString('| [lindemannrock/craft-logging-library](https://github.com/LindemannRock/craft-logging-library) | 5.18.2+ | Optional — install in CP for log viewing |', $requirements);
        self::assertStringContainsString('craft-plugin-base ^5.38.2 is present', $bootstrap);
    }

    public function testRequiredSharedRuntimeSurfacesAreAvailable(): void
    {
        self::assertTrue(trait_exists(\lindemannrock\base\traits\DateFormatSettingsTrait::class));
        self::assertTrue(trait_exists(\lindemannrock\base\traits\LogLevelSettingsTrait::class));
        self::assertTrue(trait_exists(\lindemannrock\base\traits\PluginNameSettingsTrait::class));
        self::assertTrue(trait_exists(\lindemannrock\base\traits\SettingsPersistenceTrait::class));
        self::assertTrue(class_exists(\lindemannrock\base\helpers\SettingsPostHelper::class));
        self::assertTrue(trait_exists(\lindemannrock\logginglibrary\traits\LoggingTrait::class));
        self::assertTrue(class_exists(LoggingLibrary::class));

        $baseRoot = dirname((string)(new \ReflectionClass(PluginHelper::class))->getFileName(), 3);
        self::assertFileExists($baseRoot . '/src/templates/_components/bulk-actions-menu.twig');
        self::assertFileExists($baseRoot . '/src/templates/_components/bulk-status-menu.twig');

        $logging = Craft::$app->getPlugins()->getPlugin('logging-library');
        self::assertInstanceOf(LoggingLibrary::class, $logging);
        self::assertTrue(PluginHelper::isPluginEnabled('logging-library'));
    }

    public function testSettingsPersistAndReloadWithoutLeavingState(): void
    {
        $transaction = Craft::$app->getDb()->beginTransaction();

        try {
            $settings = Settings::loadFromDatabase();
            $marker = '__formie_rest_api_dependency_floor_test__';
            $settings->pluginName = $marker;

            self::assertTrue($settings->saveToDatabase(['pluginName']));
            self::assertSame($marker, Settings::loadFromDatabase()->pluginName);
        } finally {
            $transaction->rollBack();
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function composerMetadata(): array
    {
        return json_decode(
            $this->packageFile('composer.json'),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
    }

    private function packageFile(string $path): string
    {
        $content = file_get_contents($this->packageRoot() . '/' . $path);
        self::assertIsString($content, $path);

        return $content;
    }

    private function packageRoot(): string
    {
        return dirname(__DIR__, 2);
    }
}
