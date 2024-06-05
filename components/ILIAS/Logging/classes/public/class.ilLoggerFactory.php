<?php

declare(strict_types=1);

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


use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\NullHandler;
use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;
use ILIAS\DI\Container;
use Monolog\Processor\PsrLogMessageProcessor;

/**
 * Logging factory
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilLoggerFactory
{
    protected const DEFAULT_FORMAT = "[%suid%] [%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";

    protected const ROOT_LOGGER = 'root';
    protected const COMPONENT_ROOT = 'log_root';
    protected const SETUP_LOGGER = 'setup';

    private static ?ilLoggerFactory $instance = null;

    private ilLoggingSettings $settings;
    protected Container $dic;

    private bool $enabled; //ToDo PHP8 Review: This is a private var never read only written and should probably be removed.

    /**
     * @var array<string, ilComponentLogger>
     */
    private array $loggers = array();

    protected function __construct(ilLoggingSettings $settings)
    {
        global $DIC;

        $this->dic = $DIC;
        $this->settings = $settings;
        $this->enabled = $this->getSettings()->isEnabled();
    }

    public static function getInstance(): ilLoggerFactory
    {
        if (!static::$instance instanceof ilLoggerFactory) {
            $settings = ilLoggingDBSettings::getInstance();
            static::$instance = new ilLoggerFactory($settings);
        }
        return static::$instance;
    }

    public static function newInstance(ilLoggingSettings $settings): ilLoggerFactory
    {
        return static::$instance = new self($settings);
    }

    private function isLoggingEnabled(): bool
    {
        return $this->enabled;
    }


    /**
     * Get component logger
     */
    public static function getLogger(string $a_component_id): ilLogger
    {
        $factory = self::getInstance();
        return $factory->getComponentLogger($a_component_id);
    }

    /**
     * The unique root logger has a fixed error level
     */
    public static function getRootLogger(): ilLogger
    {
        $factory = self::getInstance();
        return $factory->getComponentLogger(self::ROOT_LOGGER);
    }


    /**
     * Init user specific log options
     */
    public function initUser(string $a_login): void
    {
        if (!$this->getSettings()->isBrowserLogEnabledForUser($a_login)) {
            return;
        }
    }

    /**
     * Check if console handler is available
     */
    protected function isConsoleAvailable(): bool
    {
        if (ilContext::getType() != ilContext::CONTEXT_WEB) {
            return false;
        }
        if (
            $this->dic->isDependencyAvailable('ctrl') && $this->dic->ctrl()->isAsynch() ||
            (
                $this->dic->isDependencyAvailable('http') &&
                strtolower($this->dic->http()->request()->getServerParams()['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
            )
        ) {
            return false;
        }

        if (
            $this->dic->isDependencyAvailable('http') &&
            strpos($this->dic->http()->request()->getServerParams()['HTTP_ACCEPT'], 'text/html') !== false
        ) {
            return true;
        }
        if (
            $this->dic->isDependencyAvailable('http') &&
            strpos($this->dic->http()->request()->getServerParams()['HTTP_ACCEPT'], 'application/json') !== false
        ) {
            return false;
        }
        return true;
    }

    public function getSettings(): ilLoggingSettings
    {
        return $this->settings;
    }

    /**
     * @return ilComponentLogger[]
     */
    protected function getLoggers(): array
    {
        return $this->loggers;
    }

    public function getComponentLogger(string $a_component_id): ilLogger
    {
        if (isset($this->loggers[$a_component_id])) {
            return $this->loggers[$a_component_id];
        }

        $loggerNamePrefix = '';
        if (defined('CLIENT_ID')) {
            $loggerNamePrefix = CLIENT_ID . '_';
        }

        switch ($a_component_id) {
            case 'root':
                break;

            default:
                break;
        }

        if (!$this->isLoggingEnabled()) {
            return $this->loggers[$a_component_id] = new ilComponentLogger();
        }


        // register new logger
        $this->loggers[$a_component_id] = new ilComponentLogger();

        return $this->loggers[$a_component_id];
    }
}
