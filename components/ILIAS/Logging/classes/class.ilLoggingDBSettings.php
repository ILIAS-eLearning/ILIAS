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

use ILIAS\Logging\Config\Basic\ConfigInterface as BasicConfig;
use ILIAS\Logging\Config\ByComponent\ConfigInterface as ComponentConfig;
use ILIAS\Logging\Logger\LegacyInitiator;

/**
 * @deprecated Please use {@see \ILIAS\Logging\Config\ConfigInterface} instead.
 *
 * @defgroup ServicesLogging Services/Logging
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ServicesLogging
 */
class ilLoggingDBSettings implements ilLoggingSettings
{
    protected static ?ilLoggingDBSettings $instance = null;

    protected BasicConfig $basic_config;
    protected ComponentConfig $component_config;

    private function __construct()
    {
        $initiator = LegacyInitiator::getInstance();
        $this->basic_config = $initiator->basicConfig();
        $this->component_config = $initiator->componentConfig();
    }

    public static function getInstance(): self
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new self();
    }

    /**
     * Get level by component
     * @todo better performance
     */
    public function getLevelByComponent(string $a_component_id): int
    {
        return $this->component_config->level($a_component_id)->value;
    }

    /**
     * Check if logging is enabled
     */
    public function isEnabled(): bool
    {
        return $this->basic_config->isLoggingEnabled();
    }

    public function getLogDir(): string
    {
        return $this->basic_config->pathToLogDirectory();
    }

    public function getLevel(): int
    {
        return $this->basic_config->defaultLevel()->value;
    }
}
