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

use ceLTIc\LTI\Enum\LogLevel;
use ceLTIc\LTI\Http\HttpMessage;
use ceLTIc\LTI\Util;
use Psr\Log\AbstractLogger;

/**
 * Writes what celtic/lti logs to the log of the LTI component, at its level, with the place of the library
 * that logged it. On debug the library logs every request it gets, every message it sends and every service
 * call with their bodies and headers, and ILIAS the time each call took; otherwise only what fails, which are
 * mostly calls the other side did not answer, so they are warnings of ILIAS. The requests ILIAS refuses are
 * logged by ILIAS itself, with what identifies them.
 *
 * Neither the requests nor the answers show a token, a client assertion, a signature or a cookie in the log.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
final class ilLTILibraryLogger extends AbstractLogger
{
    private const string HIDDEN = '[hidden]';
    private const string SECRETS = 'access_token|client_assertion|registration_token|id_token|JWT|oauth_signature|\w*secret';
    private const string SOURCE = '/celtic/lti/src/';
    // what the library logs that tells nothing about the request
    private const array NOISE = ['JwtClient set to', 'HttpClient set to', 'LoggerClient set to'];
    // the library logs the reason of every request it refuses, which ILIAS logs with its own context
    private const string REFUSED = 'Request failed with reason';

    private function __construct(private readonly ilLogger $logger)
    {
    }

    /**
     * Makes celtic/lti log to the LTI component from here on in this request, and time its calls.
     */
    public static function register(): void
    {
        global $DIC;

        if (Util::getLoggerClient() instanceof self) {
            return;
        }

        $logger = $DIC->logger()->forComponent('lti');
        Util::setLoggerClient(new self($logger));
        Util::$logLevel = match (true) {
            $logger->isHandling(ilLogLevel::DEBUG) => LogLevel::Debug,
            $logger->isHandling(ilLogLevel::WARNING) => LogLevel::Error,
            default => LogLevel::None,
        };
        HttpMessage::setHttpClient(new ilLTIHttpClient(HttpMessage::getHttpClient(), $logger));
        $logger->debug('LTI request {method} {uri} on ILIAS {ilias}, celtic/lti {library}, PHP {php}', [
            'method' => (string) ($_SERVER['REQUEST_METHOD'] ?? 'CLI'),
            'uri' => self::hideSecrets((string) ($_SERVER['REQUEST_URI'] ?? '')),
            'ilias' => ILIAS_VERSION,
            'library' => Util::$version,
            'php' => PHP_VERSION,
        ]);
    }

    public function log($level, string|Stringable $message, array $context = []): void
    {
        $message = (string) $message;
        foreach (self::NOISE as $noise) {
            if (str_starts_with($message, $noise)) {
                return;
            }
        }

        $detailed = in_array($level, ['debug', 'info'], true) || str_starts_with($message, self::REFUSED);
        $message = 'celtic/lti ' . $this->findSource() . ': ' . self::hideSecrets($message);
        if ($detailed) {
            $this->logger->debug($message);
        } else {
            $this->logger->warning($message);
        }
    }

    /**
     * The ids that tell a request apart in the logs of both sides, as key=value, without the empty ones.
     *
     * @param array $ids
     */
    public static function describe(array $ids): string
    {
        $pairs = [];
        foreach ($ids as $key => $value) {
            if ($value !== null && $value !== '') {
                $pairs[] = $key . '=' . (is_scalar($value) ? (string) $value : json_encode($value));
            }
        }

        return self::hideSecrets(implode(' ', $pairs));
    }

    /**
     * The message without the secrets it may hold.
     */
    public static function hideSecrets(string $message): string
    {
        return (string) preg_replace(
            [
                '/\bBearer\s+\S+/i',
                '/eyJ[\w-]*\.[\w-]*\.[\w-]*/',
                '/(["\'](?:' . self::SECRETS . ')["\']\s*(?:=>|:)\s*["\'])[^"\']*/',
                '/\b((?:' . self::SECRETS . ')=)("?)[^&\s",]*/',
                '/((?:Set-)?Cookie["\']?\s*(?:=>|:)\s*["\']?)[^\r\n"\']*/i',
            ],
            [
                'Bearer ' . self::HIDDEN,
                self::HIDDEN,
                '$1' . self::HIDDEN,
                '$1$2' . self::HIDDEN,
                '$1' . self::HIDDEN,
            ],
            $message
        );
    }

    /**
     * The file and line of the library that logged.
     */
    private function findSource(): string
    {
        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $frame) {
            $file = str_replace('\\', '/', $frame['file'] ?? '');
            $position = strpos($file, self::SOURCE);
            if ($position !== false && !str_ends_with($file, '/Util.php')) {
                return substr($file, $position + strlen(self::SOURCE)) . ':' . ($frame['line'] ?? 0);
            }
        }

        return 'Util.php';
    }
}
