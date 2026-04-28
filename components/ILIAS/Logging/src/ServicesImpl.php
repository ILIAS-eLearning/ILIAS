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

class ServicesImpl implements Services
{
    public function __construct(
        protected readonly LoggerFactory $factory,
        protected readonly LoggingConfig $config,
    ) {
    }

    public function getFactory(): LoggerFactory
    {
        return $this->factory;
    }

    public function getComponentLogger(string $component_id): \ilLogger
    {
        return $this->factory->getComponentLogger($component_id);
    }

    public function getRootLogger(): \ilLogger
    {
        return $this->factory->getRootLogger();
    }

    public function root(): \ilLogger
    {
        return $this->factory->getRootLogger();
    }

    /**
     * Fluent shortcut: `$services->myComponent()` returns the logger for
     * the component id `myComponent`.
     */
    public function __call(string $name, array $arguments): \ilLogger
    {
        assert($arguments === []);
        return $this->factory->getComponentLogger($name);
    }

    public function getLogger(string $component_id): \ilLogger
    {
        return $this->factory->getComponentLogger($component_id);
    }

    public function getConfig(): LoggingConfig
    {
        return $this->config;
    }

    public function getSettings(): \ilLoggingSettings
    {
        return new \ilLoggingDBSettings($this->config);
    }

    public function initUser(string $login): void
    {
        $this->factory->initUser($login);
    }
}
