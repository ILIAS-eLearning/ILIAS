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

namespace ILIAS\Logging\Logger;

use ILIAS\DI\Container;
use ILIAS\Logging\Config\Basic\ConfigInterface as BasicConfigInterface;
use ILIAS\Logging\Config\Basic\Config as BasicConfig;
use ILIAS\Logging\Config\Basic\IniReader;
use ILIAS\Logging\Config\ByComponent\RepositoryInterface as ComponentConfigRepoInterface;
use ILIAS\Logging\Config\ByComponent\DBRepository as ComponentConfigRepo;
use ILIAS\Logging\Config\ByComponent\ConfigInterface as ComponentConfigInterface;
use ILIAS\Logging\Config\ByComponent\Config as ComponentConfig;
use ILIAS\Logging\Logger\LoggerFactoryInterface;
use ILIAS\Logging\Logger\LoggerFactory;
use ILIAS\Logging\Logger\LazyInternalFactoryInterface;
use ILIAS\Logging\Logger\LazyInternalFactory;
use ILIAS\Logging\Logger\Monolog\Factory as MonologFactory;
use ILIAS\Logging\Logger\LevelFetcher\LevelFetcherFactory;
use ILIAS\Logging\Logger\LevelFetcher\LevelFetcherFactoryInterface;
use ILIAS\Logging\Logger\DefaultConfigLoggerFactoryInterface;
use ILIAS\Logging\Logger\DefaultConfigLoggerFactory;
use ILIAS\Logging\ILIASLogLevel;

class LegacyInitiator
{
    protected static self $instance;

    protected Container $dic;

    protected BasicConfigInterface $basic_config;
    protected ComponentConfigRepoInterface $component_config_repo;
    protected ComponentConfigInterface $component_config;
    protected LazyInternalFactoryInterface $lazy_internal_factory;
    protected LevelFetcherFactoryInterface $level_fetcher_factory;
    protected LoggerFactoryInterface $logger_factory;
    protected DefaultConfigLoggerFactoryInterface $default_config_logger_factory;

    protected function __construct(
    ) {
        global $DIC;

        $this->dic = $DIC;
    }

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    public function basicConfig(): BasicConfigInterface
    {
        if (isset($this->basic_config)) {
            return $this->basic_config;
        }
        /**
         * This exists purely to appease unit tests in other components,
         * which somehow depend on the dependencies of ilLoggerFactory.
         */
        if ($this->dic->offsetExists('ilIliasIniFile')) {
            $basic_config = new BasicConfig(new IniReader($this->dic->iliasIni()));
        } else {
            $basic_config = new class () implements BasicConfigInterface {
                public function isLoggingEnabled(): bool
                {
                    return (bool) ILIAS_LOG_ENABLED;
                }

                public function pathToLogFile(): string
                {
                    return rtrim(ILIAS_LOG_DIR, '/') . '/' .
                        ltrim(ILIAS_LOG_FILE, '/');
                }

                public function pathToLogDirectory(): string
                {
                    return ILIAS_LOG_DIR;
                }

                public function defaultLevel(): ILIASLogLevel
                {
                    return ILIASLogLevel::INFO;
                }
            };
        }

        return $this->basic_config = $basic_config;
    }

    public function componentConfigRepository(): ComponentConfigRepoInterface
    {
        return $this->component_config_repo ??= new ComponentConfigRepo(
            $this->dic->database()
        );
    }

    public function componentConfig(): ComponentConfigInterface
    {
        return $this->component_config ??= new ComponentConfig(
            $this->componentConfigRepository(),
            $this->basicConfig()
        );
    }

    protected function lazyInternalFactory(): LazyInternalFactoryInterface
    {
        return $this->lazy_internal_factory ??= new LazyInternalFactory(
            new MonologFactory(),
            $this->basicConfig()
        );
    }

    protected function levelFetcherFactory(): LevelFetcherFactoryInterface
    {
        return $this->level_fetcher_factory ??= new LevelFetcherFactory();
    }

    public function loggerFactory(): LoggerFactoryInterface
    {
        return $this->logger_factory ??= new LoggerFactory(
            $this->lazyInternalFactory(),
            $this->componentConfig(),
            $this->levelFetcherFactory()
        );
    }

    public function defaultConfigLoggerFactory(): DefaultConfigLoggerFactoryInterface
    {
        return $this->default_config_logger_factory ??= new DefaultConfigLoggerFactory(
            $this->lazyInternalFactory(),
            $this->basicConfig(),
            $this->levelFetcherFactory()
        );
    }
}
