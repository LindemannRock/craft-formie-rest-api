<?php
/**
 * Formie REST API plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\formierestapi\console\controllers;

use Craft;
use craft\console\Controller;
use craft\helpers\Console;
use yii\console\ExitCode;

/**
 * Security utilities for Formie REST API
 *
 * @since 3.4.0
 */
class SecurityController extends Controller
{
    /**
     * Generate a secure API key and optionally update the .env file
     *
     * @return int
     */
    public function actionGenerateKey(): int
    {
        $this->stdout("Formie REST API - API Key Generator\n", Console::FG_CYAN);
        $this->stdout(str_repeat('=', 60) . "\n\n");

        $keyType = $this->select('Which API key do you want to generate?', [
            'primary' => 'Primary key (read_forms, read_submissions, create_submissions) — FORMIE_API_KEY',
            'limited' => 'Limited key (read_forms only) — FORMIE_API_KEY_LIMITED',
            'test' => 'Test key (devMode only) — FORMIE_API_KEY_TEST',
        ]);

        if ($keyType === 'test' && !Craft::$app->config->general->devMode) {
            $this->stdout("\nError: Test keys only work when devMode is enabled.\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $envVar = match ($keyType) {
            'limited' => 'FORMIE_API_KEY_LIMITED',
            'test' => 'FORMIE_API_KEY_TEST',
            default => 'FORMIE_API_KEY',
        };

        $this->stdout("\nKey prefix:\n", Console::FG_YELLOW);
        $this->stdout("  • Press Enter        → use default 'fra_'\n");
        $this->stdout("  • Type '-'           → no prefix (64 hex chars only)\n");
        $this->stdout("  • Type custom value  → use as-is (e.g. 'mycorp_')\n\n");

        $prefixInput = $this->prompt('Prefix?', [
            'default' => 'fra_',
            'pattern' => '/^(-|[a-zA-Z0-9_]{1,16})$/',
        ]);

        $prefix = $prefixInput === '-' ? '' : $prefixInput;

        $key = $prefix . bin2hex(random_bytes(32));

        $this->stdout("\nGenerated key:\n", Console::FG_YELLOW);
        $this->stdout($key . "\n\n", Console::FG_GREEN);

        $envPath = defined('CRAFT_BASE_PATH') ? CRAFT_BASE_PATH . DIRECTORY_SEPARATOR . '.env' : Craft::getAlias('@root/.env');

        $this->stdout("Env variable: ", Console::FG_CYAN);
        $this->stdout("{$envVar}\n");
        $this->stdout(".env path:    ", Console::FG_CYAN);
        $this->stdout("{$envPath}\n\n");

        if (!$this->confirm("Write to this .env file? (no = copy/paste yourself, e.g. into hosting panel)", false)) {
            $this->stdout("\nKey not written. Copy the value above and store it where appropriate:\n", Console::FG_YELLOW);
            $this->stdout("  • Hosting panel env vars (Servd, Forge, Cloudways, etc.)\n");
            $this->stdout("  • CI/CD secret store (GitHub Actions, etc.)\n");
            $this->stdout("  • Local .env file (manually)\n\n");
            $this->stdout("Reminder: send via the X-API-Key request header.\n\n");
            return ExitCode::OK;
        }

        if (!file_exists($envPath)) {
            $this->stdout("\nError: .env file not found at {$envPath}\n", Console::FG_RED);
            $this->stdout("Add manually: {$envVar}=\"{$key}\"\n\n", Console::FG_GREEN);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $envContent = file_get_contents($envPath);
        if ($envContent === false) {
            $this->stdout("Error: Could not read .env file at {$envPath}\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $exists = preg_match('/^' . preg_quote($envVar, '/') . '=/m', $envContent) === 1;

        if ($exists) {
            $this->stdout("\nExisting {$envVar} found in .env\n", Console::FG_YELLOW);
            $this->stdout("WARNING: ", Console::FG_RED);
            $this->stdout("Replacing this key will invalidate every API consumer using it.\n");
            $this->stdout("All current integrations will start receiving 401 Unauthorized until updated.\n\n");

            if (!$this->confirm('Replace the existing key?', false)) {
                $this->stdout("\nCancelled. Existing key unchanged.\n", Console::FG_YELLOW);
                return ExitCode::OK;
            }

            $envContent = preg_replace(
                '/^' . preg_quote($envVar, '/') . '=.*$/m',
                $envVar . '="' . $key . '"',
                $envContent
            );
            $action = 'Updated';
        } else {
            if ($envContent !== '' && !str_ends_with($envContent, "\n")) {
                $envContent .= "\n";
            }
            $envContent .= "\n# Formie REST API {$envVar} (generated " . date('Y-m-d H:i:s') . ")\n";
            $envContent .= $envVar . '="' . $key . '"' . "\n";
            $action = 'Added';
        }

        if (file_put_contents($envPath, $envContent) === false) {
            $this->stdout("\nError: Could not write to .env file\n", Console::FG_RED);
            $this->stdout("Add manually: {$envVar}=\"{$key}\"\n\n", Console::FG_GREEN);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout("\n✓ {$action} {$envVar} in .env file\n", Console::FG_GREEN);
        $this->stdout("Location: {$envPath}\n\n", Console::FG_CYAN);

        $this->stdout("Reminders:\n", Console::FG_YELLOW);
        $this->stdout("• Never commit .env to version control\n");
        $this->stdout("• Store the key securely (password manager recommended)\n");
        $this->stdout("• Share with API consumers via a secure channel\n");
        $this->stdout("• Restart PHP / DDEV if the new value is not picked up\n");
        $this->stdout("• Send via the X-API-Key request header\n\n");

        return ExitCode::OK;
    }
}
