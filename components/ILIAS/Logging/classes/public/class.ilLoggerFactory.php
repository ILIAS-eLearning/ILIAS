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

use ILIAS\Logging\Logger\LoggerFactoryInterface;
use ILIAS\Logging\Logger\DefaultConfigLoggerFactoryInterface;
use ILIAS\Logging\Logger\LegacyInitiator;

/**
 * Logging factory
 *
 * @deprecated Please use {@see \ILIAS\Logging\Logger\LoggerInterface} via
 *  {@see \ILIAS\Logging\Logger\LoggerFactoryInterface} instead.
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilLoggerFactory
{
    private static ?ilLoggerFactory $instance = null;

    private LoggerFactoryInterface $logger_factory;
    private DefaultConfigLoggerFactoryInterface $default_config_logger_factory;

    /**
     * @var array<string, ilComponentLogger>
     */
    private array $loggers = [];

    protected function __construct()
    {
        $initiator = LegacyInitiator::getInstance();
        $this->logger_factory = $initiator->loggerFactory();
        $this->default_config_logger_factory = $initiator->defaultConfigLoggerFactory();
    }

    public static function getInstance(): ilLoggerFactory
    {
        if (!static::$instance instanceof ilLoggerFactory) {
            static::$instance = new ilLoggerFactory();
        }
        return static::$instance;
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
        return $factory->getComponentLogger('root');
    }


    /**
     * Init user specific log options
     */
    public function initUser(string $a_login): void
    {
    }

    public function getSettings(): ilLoggingSettings
    {
        return ilLoggingDBSettings::getInstance();
    }

    public function getComponentLogger(string $a_component_id): ilLogger
    {
        if (isset($this->loggers[$a_component_id])) {
            return $this->loggers[$a_component_id];
        }

        if ($a_component_id === 'root') {
            return $this->loggers['root'] = new ilComponentLogger(
                $this->default_config_logger_factory->getLazy('legacy_root')
            );
        }
        return $this->loggers[$a_component_id] = new ilComponentLogger(
            $this->logger_factory->getLazy($a_component_id)
        );
    }
}
