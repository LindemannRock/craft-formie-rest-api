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
use lindemannrock\formierestapi\FormieRestApi;
use yii\console\ExitCode;

/**
 * Security utilities for Formie REST API
 *
 * @since 3.4.0
 */
class SecurityController extends Controller
{
    /**
     * Generate a secure API key (and optional paired HMAC signing secret) and
     * optionally update the .env file.
     */
    public function actionGenerateKey(): int
    {
        $this->stdout("Formie REST API - API Key Generator\n", Console::FG_CYAN);
        $this->stdout(str_repeat('=', 60) . "\n\n");

        $keyType = $this->select('Which API key do you want to generate?', [
            'primary' => 'Primary key (read_forms, read_submissions, create_submissions) — FORMIE_API_KEY',
            'limited' => 'Limited key (read_forms only) — FORMIE_API_KEY_LIMITED',
            'test' => 'Test key (devMode only) — FORMIE_API_KEY_TEST',
            'all' => 'All three (Primary + Limited + Test) — same prefix, asked per key',
        ]);

        if ($keyType === 'test' && !Craft::$app->config->general->devMode) {
            $this->stdout("\nError: Test keys only work when devMode is enabled.\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        // Prefix prompt (shared across keys when 'all' is chosen)
        $this->stdout("\nKey prefix:\n", Console::FG_YELLOW);
        $this->stdout("  • Press Enter        → use default 'fra_'\n");
        $this->stdout("  • Type '-'           → no prefix (64 hex chars only)\n");
        $this->stdout("  • Type custom value  → use as-is (e.g. 'mycorp_')\n\n");

        $prefixInput = $this->prompt('Prefix?', [
            'default' => 'fra_',
            'pattern' => '/^(-|[a-zA-Z0-9_]{1,16})$/',
        ]);
        $prefix = $prefixInput === '-' ? '' : $prefixInput;

        $envPath = defined('CRAFT_BASE_PATH') ? CRAFT_BASE_PATH . DIRECTORY_SEPARATOR . '.env' : Craft::getAlias('@root/.env');

        $types = $keyType === 'all' ? ['primary', 'limited', 'test'] : [$keyType];

        foreach ($types as $i => $type) {
            // Skip test silently in 'all' mode if devMode is off (instead of erroring)
            if ($type === 'test' && !Craft::$app->config->general->devMode) {
                $this->stdout("\n— Skipping Test key (devMode is off) —\n\n", Console::FG_YELLOW);
                continue;
            }

            if (count($types) > 1) {
                $this->stdout("\n" . str_repeat('-', 60) . "\n", Console::FG_GREY);
                $this->stdout(sprintf("Key %d of %d — %s\n", $i + 1, count($types), strtoupper($type)), Console::FG_CYAN);
                $this->stdout(str_repeat('-', 60) . "\n", Console::FG_GREY);
            }

            $result = $this->generateOne($type, $prefix, $envPath);
            if ($result === ExitCode::UNSPECIFIED_ERROR) {
                return $result;
            }
        }

        return ExitCode::OK;
    }

    /**
     * Run the generate flow for one key type. Reads .env fresh on every call
     * so successive 'all'-mode iterations see prior writes.
     */
    private function generateOne(string $keyType, string $prefix, string $envPath): int
    {
        [$keyVar, $secretVar, $blockTitle] = match ($keyType) {
            'limited' => ['FORMIE_API_KEY_LIMITED', 'FORMIE_API_SIGNING_SECRET_LIMITED', 'Limited'],
            'test' => ['FORMIE_API_KEY_TEST', 'FORMIE_API_SIGNING_SECRET_TEST', 'Test (devMode only)'],
            default => ['FORMIE_API_KEY', 'FORMIE_API_SIGNING_SECRET', 'Primary'],
        };

        $key = FormieRestApi::$plugin->apiKey->generateApiKey($prefix);

        $this->stdout("\nGenerated key:\n", Console::FG_YELLOW);
        $this->stdout($key . "\n\n", Console::FG_GREEN);

        $envContent = file_exists($envPath) ? (file_get_contents($envPath) ?: '') : '';
        $existingKey = $this->extractEnvValue($envContent, $keyVar);
        $existingSecret = $this->extractEnvValue($envContent, $secretVar);

        // Warn about replacing an existing key (could break live consumers)
        if ($existingKey !== null) {
            $this->stdout("Existing {$keyVar} found in .env\n", Console::FG_YELLOW);
            $this->stdout("WARNING: ", Console::FG_RED);
            $this->stdout("Replacing this key will invalidate every API consumer using it.\n");
            $this->stdout("All current integrations will start receiving 401 Unauthorized until updated.\n\n");

            if (!$this->confirm('Replace the existing key?', false)) {
                $this->stdout("Cancelled. Existing {$keyVar} unchanged.\n", Console::FG_YELLOW);
                return ExitCode::OK;
            }
        }

        // HMAC signing secret prompt
        $this->stdout("HMAC request signing (optional, recommended for production):\n", Console::FG_YELLOW);
        $this->stdout("  Adds replay protection (5-min timestamp window) and integrity check.\n");
        $this->stdout("  When the matching {$secretVar} is set, this key will REQUIRE\n");
        $this->stdout("  every request to include X-Signature + X-Timestamp headers.\n\n");

        $signingSecret = null;
        if ($existingSecret !== null) {
            $this->stdout("Existing {$secretVar} found in .env\n", Console::FG_YELLOW);
            if ($this->confirm('Generate a NEW signing secret? (no = keep existing)', false)) {
                $signingSecret = FormieRestApi::$plugin->apiKey->generateSigningSecret();
                $this->stdout("\nGenerated new signing secret:\n", Console::FG_YELLOW);
                $this->stdout($signingSecret . "\n\n", Console::FG_GREEN);
            } else {
                $signingSecret = $existingSecret;
                $this->stdout("Existing signing secret will be preserved.\n\n", Console::FG_CYAN);
            }
        } elseif ($this->confirm('Also generate a paired signing secret?', false)) {
            $signingSecret = FormieRestApi::$plugin->apiKey->generateSigningSecret();
            $this->stdout("\nGenerated signing secret:\n", Console::FG_YELLOW);
            $this->stdout($signingSecret . "\n\n", Console::FG_GREEN);
        }

        // Build the desired clean block
        $block = "# Formie REST API — {$blockTitle} (updated " . date('Y-m-d H:i:s') . ")\n";
        $block .= $keyVar . '="' . $key . '"' . "\n";
        if ($signingSecret !== null) {
            $block .= $secretVar . '="' . $signingSecret . '"' . "\n";
        }

        $this->stdout("Env variables: ", Console::FG_CYAN);
        $this->stdout($keyVar . ($signingSecret !== null ? " + {$secretVar}" : '') . "\n");

        if (!$this->confirm('Write to .env? (no = copy/paste yourself)', true)) {
            $this->stdout("\nNot written. Copy this block into your env source:\n\n", Console::FG_YELLOW);
            $this->stdout($block . "\n", Console::FG_GREEN);
            $this->printReminders($signingSecret !== null);
            return ExitCode::OK;
        }

        if (!file_exists($envPath)) {
            $this->stdout("\nError: .env file not found at {$envPath}\n", Console::FG_RED);
            $this->stdout("Add manually:\n\n{$block}\n", Console::FG_GREEN);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        // Strip any existing scattered entries + their plugin-comment headers
        $envContent = $this->stripPluginEntries($envContent, [$keyVar, $secretVar]);

        // Normalize trailing whitespace then append clean block
        $envContent = rtrim($envContent, "\n") . "\n\n" . $block;

        if (file_put_contents($envPath, $envContent) === false) {
            $this->stdout("\nError: Could not write to .env file\n", Console::FG_RED);
            $this->stdout("Add manually:\n\n{$block}\n", Console::FG_GREEN);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $action = $existingKey !== null ? 'Updated' : 'Added';
        $this->stdout("\n✓ {$action} \"Formie REST API — {$blockTitle}\" block in .env\n", Console::FG_GREEN);
        $this->printReminders($signingSecret !== null);

        return ExitCode::OK;
    }

    /**
     * Extract the value of an env-style assignment (`VAR="value"` or `VAR=value`).
     * Returns null when the variable is not present.
     */
    private function extractEnvValue(string $content, string $var): ?string
    {
        $pattern = '/^' . preg_quote($var, '/') . '=(?:"([^"]*)"|(\S*))/m';
        if (preg_match($pattern, $content, $m) === 1) {
            return $m[1] !== '' ? $m[1] : ($m[2] ?? '');
        }
        return null;
    }

    /**
     * Remove any lines that assign one of the given env vars, plus an immediately-
     * preceding plugin-owned `# Formie REST API ...` comment, plus a leading blank.
     * Used to clean up scattered legacy entries before writing the consolidated block.
     *
     * @param array<int, string> $vars
     */
    private function stripPluginEntries(string $content, array $vars): string
    {
        $lines = explode("\n", $content);
        $out = [];

        foreach ($lines as $line) {
            $isTarget = false;
            foreach ($vars as $v) {
                if (preg_match('/^\s*' . preg_quote($v, '/') . '=/', $line) === 1) {
                    $isTarget = true;
                    break;
                }
            }

            if (!$isTarget) {
                $out[] = $line;
                continue;
            }

            // Drop the immediately-preceding "# Formie REST API ..." comment, if any
            $lastIdx = count($out) - 1;
            if ($lastIdx >= 0 && preg_match('/^# Formie REST API/', trim($out[$lastIdx])) === 1) {
                array_pop($out);
            }

            // Drop a preceding blank line so we don't accumulate gaps
            $lastIdx = count($out) - 1;
            if ($lastIdx >= 0 && trim($out[$lastIdx]) === '') {
                array_pop($out);
            }
            // (the target var line itself is dropped — we don't append it)
        }

        return implode("\n", $out);
    }

    private function printReminders(bool $hasSigningSecret): void
    {
        $this->stdout("Reminders:\n", Console::FG_YELLOW);
        $this->stdout("• Never commit .env to version control\n");
        $this->stdout("• Store the values securely (password manager recommended)\n");
        $this->stdout("• Share with API consumers via a secure channel\n");
        $this->stdout("• Restart PHP / DDEV if the new values are not picked up\n");
        $this->stdout("• Send the API key via the X-API-Key request header\n");
        if ($hasSigningSecret) {
            $this->stdout("• Signing required: clients must send X-Timestamp + X-Signature too\n");
            $this->stdout("• See README → \"HMAC Request Signing\" for the signature spec\n");
        }
        $this->stdout("\n");
    }
}
