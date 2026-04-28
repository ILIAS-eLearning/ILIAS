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

namespace ILIAS\Logging;

use ILIAS\Environment\Configuration\Instance\ClientIdProvider;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Logging\Configuration\LoggingConfig;
use Monolog\Logger;
use Monolog\LogRecord;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\NullHandler;
use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;
use Monolog\Processor\PsrLogMessageProcessor;

/**
 * Instance-based replacement for the legacy static {@see \ilLoggerFactory}.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class LoggerFactory
{
    private const DEFAULT_FORMAT = "[%extra.suid%] [%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";

    public const ROOT_LOGGER = 'root';
    public const COMPONENT_ROOT = 'log_root';

    /**
     * @var array<string, \ilLogger>
     */
    private array $loggers = [];

    private ?string $active_login = null;

    public function __construct(
        private readonly LoggingConfig $config,
        private readonly ClientIdProvider $client_id_provider,
        private readonly GlobalHttpState $http,
    ) {
    }

    public function getConfig(): LoggingConfig
    {
        return $this->config;
    }

    public function getRootLogger(): \ilLogger
    {
        return $this->getComponentLogger(self::ROOT_LOGGER);
    }

    public function getComponentLogger(string $component_id): \ilLogger
    {
        if (isset($this->loggers[$component_id])) {
            return $this->loggers[$component_id];
        }

        $prefix = $this->client_id_provider->getClientId()->toString();
        $logger = new Logger(($prefix !== '' ? $prefix . '_' : '') . $component_id);

        if (!$this->config->isLoggingEnabled()) {
            $logger->pushHandler(new NullHandler());
            return $this->loggers[$component_id] = new \ilComponentLogger($logger);
        }

        $stream_handler = new StreamHandler(
            $this->config->getLogDirectory() . '/' . $this->config->getLogFile(),
            Logger::DEBUG,
            true
        );

        $level_lookup = $component_id === self::ROOT_LOGGER ? self::COMPONENT_ROOT : $component_id;
        $stream_handler->setLevel($this->config->getLevelByComponent($level_lookup));

        $line_formatter = new \ilLineFormatter(self::DEFAULT_FORMAT, 'Y-m-d H:i:s.u', true, true);
        $stream_handler->setFormatter($line_formatter);

        if ($this->config->isCacheEnabled()) {
            $logger->pushHandler(
                new FingersCrossedHandler(
                    $stream_handler,
                    new ErrorLevelActivationStrategy($this->config->getCacheLevel()),
                    1000
                )
            );
        } else {
            $logger->pushHandler($stream_handler);
        }

        $this->maybeAttachBrowserHandler($logger, $line_formatter);

        $logger->pushProcessor(static function (LogRecord $record): LogRecord {
            $extra = $record->extra;
            $extra['suid'] = substr(session_id(), 0, 5);
            return $record->with(extra: $extra);
        });
        $logger->pushProcessor(new \ilTraceProcessor(\ilLogLevel::DEBUG));
        $logger->pushProcessor(new PsrLogMessageProcessor());

        return $this->loggers[$component_id] = new \ilComponentLogger($logger);
    }

    public function initUser(string $login): void
    {
        $this->active_login = $login;

        if (!$this->config->isBrowserLogEnabledForUser($login)) {
            return;
        }
        if (!$this->isConsoleAvailable()) {
            return;
        }

        $line_formatter = new \ilLineFormatter(self::DEFAULT_FORMAT, 'Y-m-d H:i:s.u', true, true);
        foreach ($this->loggers as $component_id => $component_logger) {
            $browser_handler = new BrowserConsoleHandler();
            $browser_handler->setLevel($this->config->getLevelByComponent($component_id));
            $browser_handler->setFormatter($line_formatter);
            $component_logger->getLogger()->pushHandler($browser_handler);
        }
    }

    private function maybeAttachBrowserHandler(Logger $logger, \ilLineFormatter $formatter): void
    {
        if ($this->active_login === null) {
            return;
        }
        if (!$this->config->isBrowserLogEnabledForUser($this->active_login)) {
            return;
        }
        if (!$this->isConsoleAvailable()) {
            return;
        }

        $browser_handler = new BrowserConsoleHandler();
        $browser_handler->setLevel($this->config->getLevel());
        $browser_handler->setFormatter($formatter);
        $logger->pushHandler($browser_handler);
    }

    private function isConsoleAvailable(): bool
    {
        if (!class_exists(\ilContext::class, false) || \ilContext::getType() !== \ilContext::CONTEXT_WEB) {
            return false;
        }

        $server = $this->http->request()->getServerParams();
        if (strtolower((string) ($server['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest') {
            return false;
        }

        $accept = (string) ($server['HTTP_ACCEPT'] ?? '');
        if (str_contains($accept, 'text/html')) {
            return true;
        }
        if (str_contains($accept, 'application/json')) {
            return false;
        }
        return true;
    }
}
