<?php
/**
 * LindemannRock Formie REST API
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\formierestapi\tests\Stubs;

use craft\web\Request;

/**
 * Isolated JSON POST request for the Control Panel diagnostic action.
 *
 * @since 3.10.2
 */
final class DiagnosticActionRequest extends Request
{
    /**
     * @param array<string, mixed> $bodyParams
     */
    public function __construct(array $bodyParams)
    {
        parent::__construct();
        $this->setBodyParams($bodyParams);
    }

    public function getMethod(): string
    {
        return 'POST';
    }

    public function getAcceptsJson(): bool
    {
        return true;
    }
}
