<?php
/**
 * Formie REST API plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\formierestapi\console\controllers;

use lindemannrock\base\console\controllers\AbstractHelpController;

/**
 * Console help for Formie REST API commands.
 *
 * @since 3.4.0
 */
final class HelpController extends AbstractHelpController
{
    /**
     * @inheritdoc
     */
    protected function helpManifest(): array
    {
        return [
            'title' => 'Formie REST API',
            'pluginHandle' => 'formie-rest-api',
            'commandPrefixes' => [
                'php craft',
                'ddev craft',
            ],
            'summary' => 'Use these commands to create and manage API keys and paired HMAC signing secrets for Formie REST API consumers.',
            'common' => [
                'api-keys/create',
            ],
            'groups' => [
                [
                    'name' => 'api-keys',
                    'label' => 'API Keys',
                    'description' => 'Create CP-managed API keys for external consumers.',
                    'commands' => [
                        [
                            'path' => 'api-keys/create',
                            'summary' => 'Create a database-backed API key with its signing secret.',
                            'description' => 'Creates a CP-managed key (the same kind the API Keys control panel page manages) and prints the plaintext key and signing secret to stdout exactly once. Supports per-key form scoping, submissions toggle, signing toggle, IP whitelist, rate limit, expiry, and a disabled draft state.',
                            'examples' => [
                                'formie-rest-api/api-keys/create --name="Partner integration" --forms=contactForm,productRating',
                                'formie-rest-api/api-keys/create --name="Reporting" --forms="*" --no-submissions --rate-limit=200',
                            ],
                            'notes' => [
                                'The key is stored hashed and the signing secret encrypted — neither can be shown again after creation.',
                                'Use --no-signing to create a key that does not require HMAC signing (the secret is still generated and stored, so signing can be enabled later in the CP).',
                                'Empty --forms is only valid together with --disabled (draft key).',
                            ],
                        ],
                    ],
                ],
                [
                    'name' => 'security',
                    'label' => 'Security (legacy)',
                    'description' => 'Generate legacy environment-variable API credentials. Deprecated — kept only for the env-key migration bridge; create keys with api-keys/create instead.',
                    'commands' => [
                        [
                            'path' => 'security/generate-key',
                            'summary' => 'Generate legacy env-var API keys and optional signing secrets.',
                            'description' => 'Start an interactive generator for primary, limited, test, or all API key slots. The flow can write the generated env block to .env or print it for manual copy/paste.',
                            'examples' => [
                                'formie-rest-api/security/generate-key',
                            ],
                            'notes' => [
                                'Deprecated: environment-variable keys will be removed in a future release.',
                                'Primary keys can read forms and submissions; limited keys are read-forms only.',
                                'Test keys only work when Craft devMode is enabled.',
                                'If you generate a paired signing secret, clients must send X-Timestamp and X-Signature with each request.',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
