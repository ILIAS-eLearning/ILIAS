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

use ILIAS\Logging\Config\Basic\ConfigInterface as BasicConfigInterface;
use ILIAS\Logging\Logger\Monolog\FactoryInterface as MonologFactoryInterface;
use Monolog\Logger as MonologLogger;
use ILIAS\Logging\ILIASLogLevel;
use ReflectionClass;
use ILIAS\Logging\Logger\LevelFetcher\LevelFetcherInterface;

class LazyInternalFactory implements LazyInternalFactoryInterface
{
    /**
     * @var array<string, Logger>
     */
    protected array $loggers = [];

    public function __construct(
        protected MonologFactoryInterface $monolog_factory,
        protected BasicConfigInterface $basic_config
    ) {
    }

    public function getLazyGhost(
        string $component_id,
        LevelFetcherInterface $level_fetcher
    ): LoggerInterface {
        if (isset($this->loggers[$component_id])) {
            return $this->loggers[$component_id];
        }

        /** @var Logger $lazy_logger */
        $lazy_logger = new ReflectionClass(Logger::class)->newLazyGhost(
            function (Logger $logger) use ($component_id, $level_fetcher): void {
                $monolog_logger = $this->buildMonologLogger($component_id, $level_fetcher);
                $logger->__construct($monolog_logger);
            }
        );
        return $this->loggers[$component_id] = $lazy_logger;
    }

    protected function buildMonologLogger(
        string $component_id,
        LevelFetcherInterface $level_fetcher
    ): MonologLogger {
        if (!$this->basic_config->isLoggingEnabled()) {
            return $this->monolog_factory->nullLogger($component_id);
        }
        $level = $level_fetcher->fetchLevel();
        if ($level === ILIASLogLevel::OFF) { // OFF is not a standard log level, so Monolog rejects it
            return $this->monolog_factory->nullLogger($component_id);
        }
        return $this->monolog_factory->logger(
            $component_id,
            $level,
            $this->basic_config->pathToLogFile()
        );
    }
}
