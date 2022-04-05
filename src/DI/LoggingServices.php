<?php
/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\DI;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Provides fluid interface to LoggingServices.
 */
class LoggingServices
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Get interface to the global logger.
     * @return \ilLogger
     */
    public function root()
    {
        return $this->container["ilLoggerFactory"]->getRootLogger();
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
}
