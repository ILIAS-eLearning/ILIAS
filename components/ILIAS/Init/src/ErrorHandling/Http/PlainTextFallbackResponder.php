<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Init\ErrorHandling\Http;

use PDOException;
use Throwable;
use DateTimeZone;
use DateTimeImmutable;
use ILIAS\HTTP\StatusCode;

/**
 * Responder that sends a minimal plain-text error response without relying on
 * any ILIAS service (no DIC, no UI framework, no templates).
 *
 * Use this as a last-resort fallback when the DI container or other
 * infrastructure is not available — for instance in the catch block of
 * error.php when the bootstrap itself has failed.
 *
 * The consumer MUST wrap the bootstrap / main logic in a try-catch and call
 * {@see respond()} in the catch block. In DEVMODE the exception is re-thrown
 * so that Whoops / the developer can inspect the full stack trace.
 *
 * This responder always works: it uses only PHP built-ins (headers, echo,
 * error_log, exit). Prefer {@see ErrorPageResponder}
 * when the DIC is available, as it renders a proper ILIAS page with the
 * UI framework.
 */
class PlainTextFallbackResponder
{
    /**
     * Send a minimal plain-text error response and terminate the process.
     *
     * The status code defaults to 500 (Internal Server Error). The caller may pass
     * a different code when the failure context is known.
     *
     * @param int $status_code HTTP status code (default: 500).
     * @throws Throwable in DEVMODE
     */
    public function respond(
        Throwable $e,
        int $status_code = StatusCode::HTTP_INTERNAL_SERVER_ERROR,
        ?string $status_message = null
    ): never {
        if (\defined('DEVMODE') && DEVMODE) {
            throw $e;
        }

        if (!headers_sent()) {
            http_response_code($status_code);
            header('Content-Type: text/plain; charset=UTF-8');
        }

        $session_prefix = session_id() !== '' ? session_id() : 'no-session';
        $incident_id = $session_prefix . '_' . (new \Random\Randomizer())->getInt(1, 9999);
        $timestamp = (new DateTimeImmutable())
            ->setTimezone(new DateTimeZone('UTC'))
            ->format('Y-m-d\TH:i:s\Z');

        echo ($status_message ?? 'Internal Server Error') . "\n";
        echo "Incident: $incident_id\n";
        echo "Timestamp: $timestamp\n";

        if ($e instanceof PDOException) {
            echo "Message: A database error occurred. Please contact the system administrator with the incident id.\n";
        } else {
            echo "Message: {$e->getMessage()}\n";
        }

        error_log(
            \sprintf(
                "[%s] INCIDENT %s — Uncaught %s: %s in %s:%d\nStack trace:\n%s\n",
                $timestamp,
                $incident_id,
                \get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
                $e->getTraceAsString()
            )
        );

        exit(1);
    }
}
