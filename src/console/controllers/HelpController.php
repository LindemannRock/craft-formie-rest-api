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
            'summary' => 'Use these commands to generate API keys and optional paired HMAC signing secrets for Formie REST API consumers.',
            'common' => [
                'security/generate-key',
            ],
            'groups' => [
                [
                    'name' => 'security',
                    'label' => 'Security',
                    'description' => 'Generate API credentials for external consumers.',
                    'commands' => [
                        [
                            'path' => 'security/generate-key',
                            'summary' => 'Generate API keys and optional signing secrets.',
                            'description' => 'Start an interactive generator for primary, limited, test, or all API key slots. The flow can write the generated env block to .env or print it for manual copy/paste.',
                            'examples' => [
                                'formie-rest-api/security/generate-key',
                            ],
                            'notes' => [
                                'Primary keys can read forms, read submissions, and use reserved create-submission scope.',
                                'Limited keys are read-forms only.',
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
