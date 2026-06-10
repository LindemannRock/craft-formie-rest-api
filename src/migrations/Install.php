<?php
/**
 * Formie REST API plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\formierestapi\migrations;

use craft\db\Migration;
use craft\helpers\Db;
use craft\helpers\StringHelper;

/**
 * Install Migration
 *
 * Creates the plugin's database tables:
 *  - `formierestapi_settings` — single-row plugin settings (always id=1)
 *  - `formierestapi_api_keys` — CP-managed API keys (hashed key + encrypted signing secret)
 *
 * @since 3.10.0
 */
class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->createSettingsTable();
        $this->createApiKeysTable();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->dropTableIfExists('{{%formierestapi_api_keys}}');
        $this->dropTableIfExists('{{%formierestapi_settings}}');

        return true;
    }

    /**
     * Create settings table (single row, always id=1)
     *
     * Date/time format columns are nullable — null means "inherit the
     * lindemannrock-base default" (config/lindemannrock-base.php or code default).
     */
    private function createSettingsTable(): void
    {
        if ($this->db->tableExists('{{%formierestapi_settings}}')) {
            return;
        }

        $this->createTable('{{%formierestapi_settings}}', [
            'id' => $this->primaryKey(),
            'pluginName' => $this->string(255)->notNull()->defaultValue('Formie REST API'),
            'logLevel' => $this->string(20)->notNull()->defaultValue('error'),
            'timeFormat' => $this->string(2)->null(),
            'monthFormat' => $this->string(20)->null(),
            'dateOrder' => $this->string(3)->null(),
            'dateSeparator' => $this->string(1)->null(),
            'showSeconds' => $this->boolean()->null(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->insert('{{%formierestapi_settings}}', [
            'id' => 1,
            'pluginName' => 'Formie REST API',
            'logLevel' => 'error',
            'timeFormat' => null,
            'monthFormat' => null,
            'dateOrder' => null,
            'dateSeparator' => null,
            'showSeconds' => null,
            'dateCreated' => Db::prepareDateForDb(new \DateTime()),
            'dateUpdated' => Db::prepareDateForDb(new \DateTime()),
            'uid' => StringHelper::UUID(),
        ]);
    }

    /**
     * Create the API keys table.
     *
     * The plaintext key is never stored: `keyHash` is HMAC-SHA256 of the
     * plaintext keyed by Craft's `securityKey`, and `keyPrefix` (the first
     * 12 chars, unique-indexed) is the enforcement-lookup handle. The HMAC
     * signing secret IS recoverable (`signingSecretEnc`, encrypted via
     * `Security::encryptByKey()`) because the server must recompute request
     * signatures.
     */
    private function createApiKeysTable(): void
    {
        if ($this->db->tableExists('{{%formierestapi_api_keys}}')) {
            return;
        }

        $this->createTable('{{%formierestapi_api_keys}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'enabled' => $this->boolean()->notNull()->defaultValue(true),
            'keyHash' => $this->string(128)->notNull(),
            'keyPrefix' => $this->string(32)->notNull(),
            'signingSecretEnc' => $this->text()->null(),
            'requireSignature' => $this->boolean()->notNull()->defaultValue(true),
            'canReadSubmissions' => $this->boolean()->notNull()->defaultValue(true),
            'allowedForms' => $this->text()->null()->comment('JSON array of form handles, or ["*"]'),
            'ipWhitelist' => $this->text()->null()->comment('JSON array of IP/CIDR entries; empty = unrestricted'),
            'rateLimit' => $this->integer()->null()->comment('Requests per hour; null = default'),
            'validUntil' => $this->dateTime()->null(),
            'lastUsedAt' => $this->dateTime()->null(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, '{{%formierestapi_api_keys}}', ['keyPrefix'], true);
        $this->createIndex(null, '{{%formierestapi_api_keys}}', ['validUntil'], false);
    }
}
