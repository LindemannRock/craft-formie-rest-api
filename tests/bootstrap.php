<?php

/**
 * PHPUnit bootstrap for the formie-rest-api plugin.
 *
 * Delegates to the shared Base integration bootstrap. Workspace runs use the
 * Craft project root; standalone CI supplies an exact disposable project root.
 *
 * @since 3.5.0
 */

declare(strict_types=1);

$packageRoot = dirname(__DIR__);
$configuredProjectRoot = $_SERVER['FORMIE_REST_API_TEST_PROJECT_ROOT']
    ?? $_ENV['FORMIE_REST_API_TEST_PROJECT_ROOT']
    ?? null;
$projectRoot = is_string($configuredProjectRoot) && $configuredProjectRoot !== ''
    ? $configuredProjectRoot
    : dirname($packageRoot, 2);
$baseBootstrap = $projectRoot . '/vendor/lindemannrock/craft-plugin-base/src/testing/bootstrap.php';

if (!file_exists($baseBootstrap)) {
    fwrite(STDERR, "Base plugin testing bootstrap not found at {$baseBootstrap}\n");
    fwrite(STDERR, "Run `composer install` and ensure lindemannrock/craft-plugin-base ^5.38.2 is present.\n");
    exit(1);
}

require_once $baseBootstrap;

\lindemannrock\base\testing\bootstrap($projectRoot);
