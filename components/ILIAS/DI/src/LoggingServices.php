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

namespace ILIAS\DI;

/**
 * @deprecated Please instead use {@see \ILIAS\Logging\Logger\LoggerFactoryInterface},
 *  {@see \ILIAS\Logging\Logger\DefaultConfigLoggerFactoryInterface} and {@see \ILIAS\Logging\Config\ConfigInterface}.
 *  Ideally in your Component.php. If that's not possible then via $DIC['logging.factory'],
 *  $DIC['logging.defaultConfigFactory'], and $DIC['logging.config].
 */
class LoggingServices
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Get a component logger.
     * @return \ilLogger
     */
    public function __call(string $method_name, array $args)
    {
        assert(count($args) === 0);
        return $this->container['ilLoggerFactory']->getComponentLogger($method_name);
    }

    public function forComponent(string $component_id): \ilLogger
    {
        return $this->container["ilLoggerFactory"]->getComponentLogger($component_id);
    }
}
