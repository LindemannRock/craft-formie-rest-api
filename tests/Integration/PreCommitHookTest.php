<?php
/**
 * LindemannRock Formie REST API
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\formierestapi\tests\Integration;

use lindemannrock\formierestapi\tests\TestCase;
use Symfony\Component\Process\Process;

/**
 * Protects read-only workspace and standalone pre-commit routing.
 *
 * @since 3.11.0
 */
final class PreCommitHookTest extends TestCase
{
    public function testWorkspaceRunsComposerCiOnlyThroughDdevWithoutMutation(): void
    {
        $fixture = $this->createHookFixture(workspace: true);
        $result = $this->runHook($fixture);

        self::assertSame(0, $result->getExitCode(), $result->getErrorOutput());
        $log = (string)file_get_contents($fixture['log']);
        self::assertStringContainsString('ddev:exec cd plugins/formie-rest-api', $log);
        self::assertStringContainsString('composer ci', $log);
        self::assertStringNotContainsString("\nphp:", "\n" . $log);
        self::assertStringNotContainsString("\ncomposer:", "\n" . $log);
        self::assertSame($fixture['snapshot'], $this->snapshot($fixture['packageRoot']));
    }

    public function testWorkspaceDdevFailurePropagatesWithoutHostFallbackOrMutation(): void
    {
        $fixture = $this->createHookFixture(workspace: true, ddevExit: 37);
        $result = $this->runHook($fixture);

        self::assertSame(37, $result->getExitCode());
        self::assertStringContainsString('failed in DDEV (exit 37)', $result->getErrorOutput());
        $log = (string)file_get_contents($fixture['log']);
        self::assertStringNotContainsString("\nphp:", "\n" . $log);
        self::assertStringNotContainsString("\ncomposer:", "\n" . $log);
        self::assertSame($fixture['snapshot'], $this->snapshot($fixture['packageRoot']));
    }

    public function testWorkspaceRejectsMissingDdevWithoutHostFallbackOrMutation(): void
    {
        $fixture = $this->createHookFixture(workspace: true, includeDdev: false);
        $result = $this->runHook($fixture);

        self::assertSame(127, $result->getExitCode());
        self::assertStringContainsString("'ddev' is unavailable", $result->getErrorOutput());
        self::assertSame('', (string)file_get_contents($fixture['log']));
        self::assertSame($fixture['snapshot'], $this->snapshot($fixture['packageRoot']));
    }

    public function testStandaloneValidatesMetadataPlatformAndToolsBeforeComposerCi(): void
    {
        $fixture = $this->createHookFixture();
        $result = $this->runHook($fixture);

        self::assertSame(0, $result->getExitCode(), $result->getErrorOutput());
        $log = (string)file_get_contents($fixture['log']);
        self::assertMatchesRegularExpression(
            '/composer:validate --no-plugins --no-check-publish --no-interaction.*composer:check-platform-reqs --no-interaction.*php:scripts\/check-quality-platform[.]php.*composer:ci/s',
            $log,
        );
        self::assertStringNotContainsString("\nddev:", "\n" . $log);
        self::assertSame($fixture['snapshot'], $this->snapshot($fixture['packageRoot']));
    }

    public function testStandalonePlatformFailurePropagatesBeforeToolsOrComposerCi(): void
    {
        $fixture = $this->createHookFixture(platformExit: 42);
        $result = $this->runHook($fixture);

        self::assertSame(42, $result->getExitCode());
        $log = (string)file_get_contents($fixture['log']);
        self::assertStringContainsString('composer:check-platform-reqs --no-interaction', $log);
        self::assertStringNotContainsString('php:scripts/check-quality-platform.php', $log);
        self::assertStringNotContainsString('composer:ci', $log);
        self::assertSame($fixture['snapshot'], $this->snapshot($fixture['packageRoot']));
    }

    public function testStandaloneQualityToolFailurePropagatesBeforeComposerCi(): void
    {
        $fixture = $this->createHookFixture(qualityExit: 79);
        $result = $this->runHook($fixture);

        self::assertSame(79, $result->getExitCode());
        self::assertStringNotContainsString('composer:ci', (string)file_get_contents($fixture['log']));
        self::assertSame($fixture['snapshot'], $this->snapshot($fixture['packageRoot']));
    }

    public function testStandaloneComposerCiFailurePropagatesWithoutMutation(): void
    {
        $fixture = $this->createHookFixture(ciExit: 43);
        $result = $this->runHook($fixture);

        self::assertSame(43, $result->getExitCode());
        self::assertStringContainsString('pre-commit checks failed (exit 43)', $result->getErrorOutput());
        self::assertSame($fixture['snapshot'], $this->snapshot($fixture['packageRoot']));
    }

    /** @return array{packageRoot: string, bin: string, log: string, snapshot: array<string, string>} */
    private function createHookFixture(
        bool $workspace = false,
        int $ddevExit = 0,
        int $platformExit = 0,
        int $qualityExit = 0,
        int $ciExit = 0,
        bool $includeDdev = true,
    ): array {
        $root = $this->createTrackedTempDirectory('formie-rest-api-hook');
        $packageRoot = $workspace ? $root . '/plugins/formie-rest-api' : $root . '/formie-rest-api';
        $bin = $root . '/bin';
        $log = $root . '/commands.log';
        mkdir($packageRoot . '/.githooks', recursive: true);
        mkdir($bin);
        copy($this->packageRoot() . '/.githooks/pre-commit', $packageRoot . '/.githooks/pre-commit');
        file_put_contents($packageRoot . '/sentinel.txt', "must remain byte-identical\n");
        file_put_contents($log, '');
        if ($workspace) {
            mkdir($root . '/.ddev');
            file_put_contents($root . '/.ddev/config.yaml', "php_version: \"8.3\"\n");
        }

        if ($includeDdev) {
            $this->writeExecutable(
                $bin . '/ddev',
                "#!/bin/sh\nprintf 'ddev:%s\\n' \"\$*\" >> \"\$FORMIE_REST_API_HOOK_TEST_LOG\"\nexit {$ddevExit}\n",
            );
        }
        $this->writeExecutable(
            $bin . '/php',
            "#!/bin/sh\nprintf 'php:%s\\n' \"\$*\" >> \"\$FORMIE_REST_API_HOOK_TEST_LOG\"\n"
            . "if [ \"\$1\" = \"scripts/check-quality-platform.php\" ]; then exit {$qualityExit}; fi\n"
            . "if [ \"\$1\" = \"-r\" ]; then printf '8.3.30'; exit 0; fi\nexit 92\n",
        );
        $this->writeExecutable(
            $bin . '/composer',
            "#!/bin/sh\nprintf 'composer:%s\\n' \"\$*\" >> \"\$FORMIE_REST_API_HOOK_TEST_LOG\"\n"
            . "if [ \"\$1\" = \"check-platform-reqs\" ]; then exit {$platformExit}; fi\n"
            . "if [ \"\$1\" = \"ci\" ]; then exit {$ciExit}; fi\nexit 0\n",
        );

        return [
            'packageRoot' => $packageRoot,
            'bin' => $bin,
            'log' => $log,
            'snapshot' => $this->snapshot($packageRoot),
        ];
    }

    private function runHook(array $fixture): Process
    {
        $process = new Process(
            ['/bin/bash', $fixture['packageRoot'] . '/.githooks/pre-commit'],
            $fixture['packageRoot'],
            [
                'PATH' => $fixture['bin'] . ':/usr/bin:/bin',
                'FORMIE_REST_API_HOOK_TEST_LOG' => $fixture['log'],
            ],
        );
        $process->run();

        return $process;
    }

    /** @return array<string, string> */
    private function snapshot(string $root): array
    {
        $snapshot = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS),
        );
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $snapshot[substr($file->getPathname(), strlen($root) + 1)] = hash_file('sha256', $file->getPathname());
            }
        }
        ksort($snapshot);

        return $snapshot;
    }

    private function writeExecutable(string $path, string $source): void
    {
        file_put_contents($path, $source);
        chmod($path, 0700);
    }

    private function packageRoot(): string
    {
        return dirname(__DIR__, 2);
    }
}
