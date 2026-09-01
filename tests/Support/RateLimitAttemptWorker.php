<?php
/**
 * LindemannRock Formie REST API
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\formierestapi\tests\Support;

use Craft;
use craft\helpers\App;
use craft\mutex\Mutex;
use lindemannrock\formierestapi\services\SecurityService;

require_once dirname(__DIR__) . '/bootstrap.php';

/**
 * One isolated rate-limit attempt released by a parent-owned socket barrier.
 *
 * @since 3.11.0
 */
final class RateLimitAttemptWorker
{
    /**
     * @param list<string> $arguments
     */
    public static function run(array $arguments): int
    {
        $socketUri = $arguments[1] ?? '';
        $apiKey = $arguments[2] ?? '';
        $limit = filter_var($arguments[3] ?? null, FILTER_VALIDATE_INT);
        if ($socketUri === '' || $apiKey === '' || $limit === false || $limit < 1) {
            fwrite(STDERR, "Invalid rate-limit worker arguments.\n");
            return 2;
        }

        $errorCode = 0;
        $errorMessage = '';
        $socket = stream_socket_client($socketUri, $errorCode, $errorMessage, 15);
        if ($socket === false) {
            fwrite(STDERR, "Could not connect to barrier: {$errorCode} {$errorMessage}\n");
            return 3;
        }

        stream_set_timeout($socket, 15);
        try {
            fwrite($socket, "READY\n");
            if (fgets($socket) !== "GO\n") {
                fwrite(STDERR, "Barrier did not release the worker.\n");
                return 4;
            }

            // The shared test bootstrap deliberately uses NullMutex for its
            // `craft-test` application ID. Install Craft's production DB mutex
            // driver so this worker exercises the real cross-process boundary.
            Craft::$app->set('mutex', [
                'class' => Mutex::class,
                'mutex' => App::dbMutexConfig(),
            ]);
            $allowed = (new SecurityService())->checkRateLimit($apiKey, ['rateLimit' => $limit]);
            fwrite($socket, $allowed ? "ALLOWED\n" : "REJECTED\n");
            if (fgets($socket) !== "CLOSE\n") {
                fwrite(STDERR, "Parent did not close the worker cleanly.\n");
                return 6;
            }
            return 0;
        } catch (\Throwable $e) {
            fwrite(STDERR, $e->getMessage() . "\n");
            return 5;
        } finally {
            fclose($socket);
        }
    }
}

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    /** @var list<string> $argv */
    exit(RateLimitAttemptWorker::run($argv));
}
