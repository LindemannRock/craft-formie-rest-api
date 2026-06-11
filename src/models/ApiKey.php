<?php
/**
 * Formie REST API plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\formierestapi\models;

use Craft;
use craft\base\Model;
use craft\db\Query;
use craft\helpers\Db;
use craft\helpers\StringHelper;
use lindemannrock\logginglibrary\traits\LoggingTrait;

/**
 * API Key Model
 *
 * Represents a CP-managed API key gating access to the Formie REST API.
 *
 * Plaintext keys are generated once via `ApiKeyService::generateDbKey()` and
 * shown to the operator on creation. Only the hash (HMAC-SHA256 of the
 * plaintext keyed by Craft's `securityKey`) and a 12-char display prefix
 * persist. Lookup on the enforcement hot path is by `keyPrefix` (unique
 * indexed), then `hash_equals` against `keyHash`.
 *
 * Unlike the key, the paired HMAC signing secret is recoverable: the server
 * must recompute request signatures, so it is stored encrypted at rest
 * (`signingSecretEnc`, via `Security::encryptByKey()` keyed by `securityKey`)
 * and decrypted only at validation time.
 *
 * Restrictions are per-key:
 *  - `canReadSubmissions` — false = forms endpoints only
 *  - `allowedForms` — whitelist of Formie form handles, or `['*']` for all
 *  - `ipWhitelist` — IP/CIDR entries; empty = unrestricted
 *  - `requireSignature` — HMAC signing enforced for this key
 *  - `validUntil` — expiry datetime; null = never expires
 *  - `rateLimit` — requests per hour; null = default
 *
 * @since 3.10.0
 */
class ApiKey extends Model
{
    use LoggingTrait;

    /**
     * Wildcard value stored in `allowedForms` to mean
     * "all current forms, plus any added later."
     */
    public const ALL_FORMS = '*';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_DISABLED = 'disabled';
    public const STATUS_EXPIRED = 'expired';

    // =========================================================================
    // PROPERTIES
    // =========================================================================

    public ?int $id = null;

    public string $name = '';

    /**
     * @var bool When false the key is "paused" — enforcement must reject it.
     *   Lets operators temporarily disable a key without losing the config.
     *   Distinct from `validUntil` (automatic expiry) and from revoke (delete).
     */
    public bool $enabled = true;

    /**
     * @var string HMAC-SHA256 hash of the plaintext key. Not exposed to UI.
     */
    public string $keyHash = '';

    /**
     * @var string Unhashed prefix of the plaintext key, e.g. `fra_a1b2c3d4` (12 chars).
     *   Stored for CP display + as the lookup index for enforcement.
     */
    public string $keyPrefix = '';

    /**
     * @var string|null Encrypted HMAC signing secret (base64 of
     *   `Security::encryptByKey()` output). Never exposed to the UI after
     *   the one-time reveal at creation.
     */
    public ?string $signingSecretEnc = null;

    /**
     * @var bool Whether requests with this key must carry a valid HMAC
     *   signature. Only enforceable while `signingSecretEnc` decrypts.
     */
    public bool $requireSignature = true;

    /**
     * @var bool False = key is limited to the forms endpoints (no submission
     *   data). Replaces the old env-var "limited" tier.
     */
    public bool $canReadSubmissions = true;

    /**
     * @var string[] Form handle whitelist, or `[self::ALL_FORMS]` for all.
     *   Empty array means "no forms allowed" — keys are non-functional until populated.
     */
    public array $allowedForms = [];

    /**
     * @var string[] Allowed client IPs (single address or CIDR, IPv4/IPv6).
     *   Empty array = all IPs allowed (no restriction).
     */
    public array $ipWhitelist = [];

    public ?int $rateLimit = null;

    public ?\DateTime $validUntil = null;

    public ?\DateTime $lastUsedAt = null;

    public ?\DateTime $dateCreated = null;

    public ?\DateTime $dateUpdated = null;

    public ?string $uid = null;

    // =========================================================================
    // INIT + VALIDATION
    // =========================================================================

    public function init(): void
    {
        parent::init();
        $this->setLoggingHandle('formie-rest-api');
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['name', 'keyHash', 'keyPrefix'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['keyHash'], 'string', 'max' => 128],
            [['keyPrefix'], 'string', 'max' => 32],
            [['enabled', 'requireSignature', 'canReadSubmissions'], 'boolean'],
            [['rateLimit'], 'integer', 'min' => 1, 'max' => 100000],
            [['allowedForms', 'ipWhitelist'], 'each', 'rule' => ['string', 'max' => 255]],
            [['allowedForms'], 'validateAllowedForms', 'skipOnEmpty' => false],
            [['ipWhitelist'], 'validateIpWhitelistEntries'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'name' => Craft::t('formie-rest-api', 'Name'),
            'enabled' => Craft::t('formie-rest-api', 'Enabled'),
            'keyPrefix' => Craft::t('formie-rest-api', 'Prefix'),
            'requireSignature' => Craft::t('formie-rest-api', 'Require signing'),
            'canReadSubmissions' => Craft::t('formie-rest-api', 'Read submissions'),
            'allowedForms' => Craft::t('formie-rest-api', 'Allowed forms'),
            'ipWhitelist' => Craft::t('formie-rest-api', 'IP whitelist'),
            'rateLimit' => Craft::t('formie-rest-api', 'Rate limit'),
            'validUntil' => Craft::t('formie-rest-api', 'Valid until'),
        ];
    }

    /**
     * Enabled keys must have an explicit form permission boundary. An empty
     * allowlist is valid only for disabled draft keys.
     */
    public function validateAllowedForms(string $attribute): void
    {
        if ($this->enabled && empty($this->allowedForms)) {
            $this->addError($attribute, Craft::t('formie-rest-api', 'Enabled keys must allow all forms or at least one specific form.'));
        }
    }

    /**
     * Each whitelist entry must be a single IP or CIDR range (IPv4 or IPv6).
     * Mirrors the matching rules in `SecurityService::ipMatches()` so anything
     * that validates here is enforceable at request time.
     */
    public function validateIpWhitelistEntries(string $attribute): void
    {
        foreach ($this->$attribute as $entry) {
            if (!is_string($entry) || $entry === '' || !self::isValidIpOrCidr($entry)) {
                $this->addError($attribute, Craft::t('formie-rest-api', 'Invalid IP whitelist entry: "{entry}". Use a single IP or CIDR range (e.g. 203.0.113.5 or 192.168.1.0/24).', ['entry' => (string)$entry]));
            }
        }
    }

    private static function isValidIpOrCidr(string $entry): bool
    {
        $address = $entry;
        $mask = null;

        if (str_contains($entry, '/')) {
            [$address, $maskStr] = explode('/', $entry, 2);
            $mask = filter_var($maskStr, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
            if ($mask === false) {
                return false;
            }
        }

        $binary = @inet_pton($address);
        if ($binary === false) {
            return false;
        }

        // CIDR mask must fit the address family (32 bits IPv4, 128 bits IPv6)
        return $mask === null || $mask <= strlen($binary) * 8;
    }

    // =========================================================================
    // SEMANTIC HELPERS
    // =========================================================================

    /**
     * True if this key is allowed against any form (the `*` wildcard).
     */
    public function allowsAllForms(): bool
    {
        return in_array(self::ALL_FORMS, $this->allowedForms, true);
    }

    /**
     * True if this key currently allows the given form handle.
     * Used on the enforcement hot path.
     */
    public function allowsForm(string $formHandle): bool
    {
        if ($this->allowsAllForms()) {
            return true;
        }
        return in_array($formHandle, $this->allowedForms, true);
    }

    /**
     * The display status string. Priority order: disabled > expired > active.
     * Disabled comes first because it reflects an intentional operator action;
     * expiry is automatic/background.
     */
    public function getStatus(): string
    {
        if (!$this->enabled) {
            return self::STATUS_DISABLED;
        }
        if ($this->validUntil !== null && !$this->isStillValid()) {
            return self::STATUS_EXPIRED;
        }
        return self::STATUS_ACTIVE;
    }

    /**
     * True if the key is enabled and not expired.
     */
    public function isStillValid(): bool
    {
        if (!$this->enabled) {
            return false;
        }
        if ($this->validUntil === null) {
            return true;
        }
        return $this->validUntil > new \DateTime('now', new \DateTimeZone('UTC'));
    }

    // =========================================================================
    // FINDERS
    // =========================================================================

    public static function findById(int $id): ?self
    {
        $row = (new Query())
            ->from('{{%formierestapi_api_keys}}')
            ->where(['id' => $id])
            ->one();

        return $row ? self::populateFromRow($row) : null;
    }

    /**
     * Find a key by its unhashed prefix. Used on the enforcement hot path:
     * client supplies plaintext, we slice off the prefix, look it up here,
     * then verify the rest with `hash_equals` against `keyHash`.
     */
    public static function findByPrefix(string $keyPrefix): ?self
    {
        $row = (new Query())
            ->from('{{%formierestapi_api_keys}}')
            ->where(['keyPrefix' => $keyPrefix])
            ->one();

        return $row ? self::populateFromRow($row) : null;
    }

    /**
     * @return self[]
     */
    public static function findAll(): array
    {
        $keys = [];
        $rows = (new Query())
            ->from('{{%formierestapi_api_keys}}')
            ->orderBy(['dateCreated' => SORT_DESC])
            ->all();

        foreach ($rows as $row) {
            $keys[] = self::populateFromRow($row);
        }

        return $keys;
    }

    public static function count(): int
    {
        return (int)(new Query())
            ->from('{{%formierestapi_api_keys}}')
            ->count();
    }

    private static function populateFromRow(array $row): self
    {
        $key = new self();
        $key->id = (int)$row['id'];
        $key->name = (string)$row['name'];
        $key->enabled = (bool)($row['enabled'] ?? true);
        $key->keyHash = (string)$row['keyHash'];
        $key->keyPrefix = (string)$row['keyPrefix'];
        $key->signingSecretEnc = isset($row['signingSecretEnc']) && $row['signingSecretEnc'] !== '' ? (string)$row['signingSecretEnc'] : null;
        $key->requireSignature = (bool)($row['requireSignature'] ?? true);
        $key->canReadSubmissions = (bool)($row['canReadSubmissions'] ?? true);
        $key->allowedForms = self::decodeJsonArray($row['allowedForms'] ?? null);
        $key->ipWhitelist = self::decodeJsonArray($row['ipWhitelist'] ?? null);
        $key->rateLimit = isset($row['rateLimit']) ? (int)$row['rateLimit'] : null;
        $key->validUntil = self::parseDate($row['validUntil'] ?? null);
        $key->lastUsedAt = self::parseDate($row['lastUsedAt'] ?? null);
        $key->dateCreated = self::parseDate($row['dateCreated'] ?? null);
        $key->dateUpdated = self::parseDate($row['dateUpdated'] ?? null);
        $key->uid = $row['uid'] ?? null;

        return $key;
    }

    /**
     * @return string[]
     */
    private static function decodeJsonArray(?string $json): array
    {
        if ($json === null || $json === '') {
            return [];
        }
        $decoded = json_decode($json, true);
        return is_array($decoded) ? array_values(array_filter($decoded, 'is_string')) : [];
    }

    private static function parseDate(mixed $value): ?\DateTime
    {
        if (empty($value)) {
            return null;
        }
        try {
            return new \DateTime((string)$value, new \DateTimeZone('UTC'));
        } catch (\Throwable) {
            return null;
        }
    }

    // =========================================================================
    // PERSISTENCE
    // =========================================================================

    public function save(): bool
    {
        if (!$this->validate()) {
            $this->logError('API key validation failed', [
                'errors' => $this->getErrors(),
            ]);
            return false;
        }

        try {
            $attributes = [
                'name' => $this->name,
                'enabled' => (int)$this->enabled,
                'keyHash' => $this->keyHash,
                'keyPrefix' => $this->keyPrefix,
                'signingSecretEnc' => $this->signingSecretEnc,
                'requireSignature' => (int)$this->requireSignature,
                'canReadSubmissions' => (int)$this->canReadSubmissions,
                'allowedForms' => json_encode(array_values($this->allowedForms), JSON_THROW_ON_ERROR),
                'ipWhitelist' => json_encode(array_values($this->ipWhitelist), JSON_THROW_ON_ERROR),
                'rateLimit' => $this->rateLimit,
                'validUntil' => $this->validUntil ? Db::prepareDateForDb($this->validUntil) : null,
                'lastUsedAt' => $this->lastUsedAt ? Db::prepareDateForDb($this->lastUsedAt) : null,
                'dateUpdated' => Db::prepareDateForDb(new \DateTime()),
            ];

            if ($this->id) {
                Craft::$app->getDb()
                    ->createCommand()
                    ->update('{{%formierestapi_api_keys}}', $attributes, ['id' => $this->id])
                    ->execute();
            } else {
                $attributes['dateCreated'] = Db::prepareDateForDb(new \DateTime());
                $attributes['uid'] = StringHelper::UUID();

                Craft::$app->getDb()
                    ->createCommand()
                    ->insert('{{%formierestapi_api_keys}}', $attributes)
                    ->execute();

                $this->id = (int)Craft::$app->getDb()->getLastInsertID();
                $this->uid = $attributes['uid'];
            }

            $this->logInfo('API key saved', [
                'id' => $this->id,
                'name' => $this->name,
                'keyPrefix' => $this->keyPrefix,
            ]);

            return true;
        } catch (\Throwable $e) {
            $this->logError('Failed to save API key', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function delete(): bool
    {
        if (!$this->id) {
            return false;
        }

        try {
            $result = Craft::$app->getDb()
                ->createCommand()
                ->delete('{{%formierestapi_api_keys}}', ['id' => $this->id])
                ->execute();

            if ($result > 0) {
                $this->logInfo('API key deleted', [
                    'id' => $this->id,
                    'name' => $this->name,
                    'keyPrefix' => $this->keyPrefix,
                ]);
                return true;
            }
            return false;
        } catch (\Throwable $e) {
            $this->logError('Failed to delete API key', [
                'id' => $this->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
