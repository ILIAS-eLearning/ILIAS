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

use ILIAS\Logging\Configuration\LoggingConfig;
use ILIAS\Logging\Configuration\NullLoggingConfig;
use Monolog\Handler\NullHandler;
use Monolog\Logger;

/**
 * Fallback {@see LoggerFactory} for environments without a bootstrapped
 * `logging.services` (e.g. unit tests). Returns Monolog loggers that swallow
 * all records via {@see NullHandler}.
 *
 * Skips the parent constructor on purpose; all parent methods that touch the
 * uninitialised parent properties are overridden here.
 */
final class NullLoggerFactory extends LoggerFactory
{
    private array $loggers = [];

    private LoggingConfig $config;

    public function __construct(?LoggingConfig $config = null)
    {
        $this->config = $config ?? new NullLoggingConfig();
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
        $logger = new Logger($component_id);
        $logger->pushHandler(new NullHandler());
        return $this->loggers[$component_id] = new \ilComponentLogger($logger);
    }

    public function initUser(string $login): void
    {
    }
}
