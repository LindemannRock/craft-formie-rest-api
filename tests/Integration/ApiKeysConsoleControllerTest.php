<?php
/**
 * LindemannRock Formie REST API
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\formierestapi\tests\Integration;

use Craft;
use lindemannrock\formierestapi\console\controllers\ApiKeysController;
use lindemannrock\formierestapi\FormieRestApi;
use lindemannrock\formierestapi\models\ApiKey;
use lindemannrock\formierestapi\tests\TestCase;
use verbb\formie\elements\Form;
use yii\console\ExitCode;

/**
 * Covers scripted API-key provisioning against the live Formie catalogue.
 *
 * @since 3.10.2
 */
final class ApiKeysConsoleControllerTest extends TestCase
{
    private const KEY_PREFIX = 'fra-console-test-';

    protected function setUp(): void
    {
        parent::setUp();
        $this->purgeKeys();
    }

    protected function tearDown(): void
    {
        $this->purgeKeys();
        parent::tearDown();
    }

    public function testKnownExplicitFormsAreNormalizedDeduplicatedAndPersisted(): void
    {
        $first = $this->seedForm();
        $second = $this->seedForm();
        $controller = $this->controller('known');
        $controller->forms = " {$first->handle}, {$second->handle}, {$first->handle} ";

        [$exitCode, $output] = $this->runCreate($controller);

        self::assertSame(ExitCode::OK, $exitCode);
        self::assertSame(1, preg_match_all('/fra_[0-9a-f]{64}/', $output));
        self::assertSame(1, preg_match_all('/Signing secret — copy this now/', $output));
        $key = $this->latestKey();
        self::assertNotNull($key);
        self::assertSame([$first->handle, $second->handle], $key->allowedForms);
    }

    public function testUnknownAndMixedExplicitFormsFailWithoutPersisting(): void
    {
        $known = $this->seedForm();

        foreach ([
            'unknown' => 'missingFormHandle',
            'mixed' => $known->handle . ', missingFormHandle',
        ] as $suffix => $forms) {
            $controller = $this->controller($suffix);
            $controller->forms = $forms;

            [$exitCode, $output] = $this->runCreate($controller);

            self::assertSame(ExitCode::DATAERR, $exitCode, $suffix);
            self::assertStringContainsString('Unknown form handle(s): missingFormHandle', $output, $suffix);
            self::assertNull($this->keyByName($controller->name), $suffix);
        }
    }

    public function testWildcardAndDisabledEmptyScopeKeepTheirExistingSemantics(): void
    {
        $wildcard = $this->controller('wildcard');
        $wildcard->forms = ApiKey::ALL_FORMS;
        [$wildcardExit] = $this->runCreate($wildcard);

        self::assertSame(ExitCode::OK, $wildcardExit);
        self::assertSame([ApiKey::ALL_FORMS], $this->keyByName($wildcard->name)?->allowedForms);

        $draft = $this->controller('draft');
        $draft->disabled = true;
        [$draftExit] = $this->runCreate($draft);

        self::assertSame(ExitCode::OK, $draftExit);
        $draftKey = $this->keyByName($draft->name);
        self::assertNotNull($draftKey);
        self::assertSame([], $draftKey->allowedForms);
        self::assertFalse($draftKey->enabled);
    }

    /** @return array{0: int, 1: string} */
    private function runCreate(RecordingApiKeysConsoleController $controller): array
    {
        return [$controller->actionCreate(), $controller->output];
    }

    private function controller(string $suffix): RecordingApiKeysConsoleController
    {
        $controller = new RecordingApiKeysConsoleController('api-keys', FormieRestApi::$plugin);
        $controller->name = self::KEY_PREFIX . $suffix;

        return $controller;
    }

    private function seedForm(): Form
    {
        $form = new Form();
        $form->title = $this->nextTestMarker('Formie REST API Console ', 'form');
        $form->handle = $this->nextTestMarker('fraConsoleForm', 'form');
        $this->saveTestElement($form);

        return $form;
    }

    private function latestKey(): ?ApiKey
    {
        foreach (ApiKey::findAll() as $key) {
            if (str_starts_with($key->name, self::KEY_PREFIX)) {
                return $key;
            }
        }

        return null;
    }

    private function keyByName(string $name): ?ApiKey
    {
        foreach (ApiKey::findAll() as $key) {
            if ($key->name === $name) {
                return $key;
            }
        }

        return null;
    }

    private function purgeKeys(): void
    {
        Craft::$app->getDb()->createCommand()->delete(
            '{{%formierestapi_api_keys}}',
            ['like', 'name', self::KEY_PREFIX . '%', false],
        )->execute();
    }
}

/** Captures console output without writing to PHPUnit process streams. */
final class RecordingApiKeysConsoleController extends ApiKeysController
{
    public string $output = '';

    public function stdout($string)
    {
        $this->output .= (string)$string;
        return strlen((string)$string);
    }

    public function stderr($string)
    {
        $this->output .= (string)$string;
        return strlen((string)$string);
    }
}
