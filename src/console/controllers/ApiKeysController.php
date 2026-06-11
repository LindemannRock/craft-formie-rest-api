<?php
/**
 * Formie REST API plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\formierestapi\console\controllers;

use craft\console\Controller;
use craft\helpers\Console;
use craft\helpers\DateTimeHelper;
use lindemannrock\formierestapi\FormieRestApi;
use lindemannrock\formierestapi\models\ApiKey;
use yii\console\ExitCode;

/**
 * API Keys console commands
 *
 * Headless / CI bootstrap surface for the API Keys feature. The CP UI is the
 * normal admin path; this controller exists so automated provisioning can
 * create keys without a logged-in browser session.
 *
 * Example:
 *   php craft formie-rest-api/api-keys/create \
 *     --name="Entity A integration" \
 *     --forms=productRating,contactForm \
 *     --ip-whitelist=203.0.113.5,192.168.1.0/24 \
 *     --rate-limit=200 \
 *     --valid-until=2027-12-31 \
 *     --no-submissions \
 *     --no-signing \
 *     --disabled
 *
 * The plaintext key and its signing secret are written to stdout exactly
 * once. The plugin's normal logging path only ever sees the prefix.
 *
 * @since 3.10.0
 */
class ApiKeysController extends Controller
{
    /**
     * @var string Human-readable label for the key.
     */
    public string $name = '';

    /**
     * @var string Comma-separated form handles. Use `*` for "all forms".
     *   Empty string is valid only with `--disabled`, creating an incomplete
     *   draft key whose restrictions must be widened before it can be enabled.
     */
    public string $forms = '';

    /**
     * @var string Comma-separated IP whitelist entries (single IP or CIDR,
     *   IPv4/IPv6). Empty string = all IPs allowed.
     */
    public string $ipWhitelist = '';

    /**
     * @var int|null Per-key rate limit in requests per hour.
     */
    public ?int $rateLimit = null;

    /**
     * @var string Optional expiry datetime in any format DateTimeHelper accepts.
     *   Empty string = never expires.
     */
    public string $validUntil = '';

    /**
     * @var bool Limit the key to the forms endpoints (no submission data).
     */
    public bool $noSubmissions = false;

    /**
     * @var bool Don't require HMAC request signing for this key. The signing
     *   secret is still generated and stored, so signing can be enabled later
     *   from the CP without re-issuing the key.
     */
    public bool $noSigning = false;

    /**
     * @var bool Create the key in a disabled state. Default is enabled.
     */
    public bool $disabled = false;

    /**
     * @inheritdoc
     */
    public function options($actionID): array
    {
        $options = parent::options($actionID);

        if ($actionID === 'create') {
            $options[] = 'name';
            $options[] = 'forms';
            $options[] = 'ipWhitelist';
            $options[] = 'rateLimit';
            $options[] = 'validUntil';
            $options[] = 'noSubmissions';
            $options[] = 'noSigning';
            $options[] = 'disabled';
        }

        return $options;
    }

    /**
     * Map CLI option names with hyphens to their PHP property camelCase forms.
     */
    public function optionAliases(): array
    {
        return [
            'ip-whitelist' => 'ipWhitelist',
            'rate-limit' => 'rateLimit',
            'valid-until' => 'validUntil',
            'no-submissions' => 'noSubmissions',
            'no-signing' => 'noSigning',
        ];
    }

    /**
     * Create a new API key.
     *
     * Outputs the plaintext key and signing secret once and exits. Neither is
     * ever logged to the plugin's normal log channel — only stdout in this
     * console context, which the operator explicitly opted into.
     */
    public function actionCreate(): int
    {
        if (trim($this->name) === '') {
            $this->stderr("--name is required.\n", Console::FG_RED);
            return ExitCode::USAGE;
        }

        $apiKey = new ApiKey();
        $apiKey->name = trim($this->name);
        $apiKey->enabled = !$this->disabled;
        $apiKey->requireSignature = !$this->noSigning;
        $apiKey->canReadSubmissions = !$this->noSubmissions;
        $apiKey->allowedForms = $this->parseForms($this->forms);
        $apiKey->ipWhitelist = $this->parseIpWhitelist($this->ipWhitelist);
        $apiKey->rateLimit = $this->rateLimit;

        if ($this->validUntil !== '') {
            $parsed = DateTimeHelper::toDateTime($this->validUntil);
            if ($parsed === false) {
                $this->stderr("--valid-until could not be parsed as a datetime: '{$this->validUntil}'.\n", Console::FG_RED);
                return ExitCode::USAGE;
            }
            $apiKey->validUntil = $parsed;
        }

        $service = FormieRestApi::$plugin->apiKey;
        $generated = $service->generateDbKey();
        $secret = $service->generateSigningSecret();
        $apiKey->keyHash = $generated['hash'];
        $apiKey->keyPrefix = $generated['prefix'];
        $apiKey->signingSecretEnc = $service->encryptSigningSecret($secret);

        if (!$apiKey->save()) {
            $this->stderr("Couldn't save API key. Validation errors:\n", Console::FG_RED);
            foreach ($apiKey->getErrors() as $field => $messages) {
                foreach ($messages as $msg) {
                    $this->stderr("  {$field}: {$msg}\n", Console::FG_RED);
                }
            }
            return ExitCode::DATAERR;
        }

        $this->stdout("✓ API key created.\n\n", Console::FG_GREEN);

        $this->stdout("  Key ID:           ", Console::FG_GREY);
        $this->stdout("{$apiKey->id}\n");
        $this->stdout("  Name:             ", Console::FG_GREY);
        $this->stdout("{$apiKey->name}\n");
        $this->stdout("  Prefix:           ", Console::FG_GREY);
        $this->stdout("{$apiKey->keyPrefix}\n");
        $this->stdout("  Allowed forms:    ", Console::FG_GREY);
        $this->stdout($apiKey->allowsAllForms()
            ? "All forms (*)\n"
            : (empty($apiKey->allowedForms) ? "(none — key is not usable yet)\n" : implode(', ', $apiKey->allowedForms) . "\n"));
        $this->stdout("  Read submissions: ", Console::FG_GREY);
        $this->stdout($apiKey->canReadSubmissions ? "yes\n" : "no (forms endpoints only)\n");
        $this->stdout("  Require signing:  ", Console::FG_GREY);
        $this->stdout($apiKey->requireSignature ? "yes\n" : "no\n");
        $this->stdout("  IP whitelist:     ", Console::FG_GREY);
        $this->stdout(empty($apiKey->ipWhitelist) ? "Any\n" : implode(', ', $apiKey->ipWhitelist) . "\n");
        $this->stdout("  Rate limit:       ", Console::FG_GREY);
        $this->stdout($apiKey->rateLimit !== null ? "{$apiKey->rateLimit} requests/hour\n" : "(default: 100/hour)\n");
        $this->stdout("  Valid until:      ", Console::FG_GREY);
        $this->stdout($apiKey->validUntil !== null
            ? $apiKey->validUntil->format('Y-m-d H:i') . "\n"
            : "Never\n");
        $this->stdout("  Enabled:          ", Console::FG_GREY);
        $this->stdout($apiKey->enabled ? "yes\n" : "no (disabled at creation)\n");

        $this->stdout("\n🔑 API key — copy this now, it will never be shown again:\n\n", Console::FG_YELLOW);
        $this->stdout("    {$generated['plaintext']}\n\n", Console::FG_GREEN);
        $this->stdout("🔏 Signing secret — copy this now, it will never be shown again:\n\n", Console::FG_YELLOW);
        $this->stdout("    {$secret}\n\n", Console::FG_GREEN);
        $this->stdout("Formie REST API stores only a hash of the key and the secret encrypted. If you lose these values you will need to create a new key.\n", Console::FG_GREY);

        return ExitCode::OK;
    }

    /**
     * Parse the `--forms` CLI value into a list of handles, honouring the
     * `*` wildcard shortcut and stripping whitespace from CSV entries.
     *
     * @return string[]
     */
    private function parseForms(string $raw): array
    {
        $trimmed = trim($raw);
        if ($trimmed === '') {
            return [];
        }
        if ($trimmed === ApiKey::ALL_FORMS) {
            return [ApiKey::ALL_FORMS];
        }
        return array_values(array_filter(
            array_map('trim', explode(',', $trimmed)),
            fn(string $h): bool => $h !== '',
        ));
    }

    /**
     * Parse `--ip-whitelist` CSV into the same normalised form the CP
     * textarea produces (trim, lowercase, dedupe, drop blanks). Entry shape
     * is validated by the model rule.
     *
     * @return string[]
     */
    private function parseIpWhitelist(string $raw): array
    {
        $items = array_map(
            fn(string $r): string => strtolower(trim($r)),
            explode(',', $raw),
        );
        return array_values(array_unique(array_filter($items, fn(string $r): bool => $r !== '')));
    }
}
