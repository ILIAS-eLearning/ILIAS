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

namespace ILIAS;

use ILIAS\Component\EntryPoint;

class Setup implements Component\Component
{
    public function init(
        array | \ArrayAccess &$define,
        array | \ArrayAccess &$implement,
        array | \ArrayAccess &$use,
        array | \ArrayAccess &$contribute,
        array | \ArrayAccess &$seek,
        array | \ArrayAccess &$provide,
        array | \ArrayAccess &$pull,
        array | \ArrayAccess &$internal,
    ): void {
        $contribute[EntryPoint::class] = fn() =>
            new \ILIAS\Setup\CLI\App(
                $internal["command.install"],
                $internal["command.update"],
                $internal["command.build"],
                $internal["command.achieve"],
                $internal["command.status"],
                $internal["command.migrate"]
            );

        $contribute[\ILIAS\Setup\Agent::class] = fn() =>
            new \ilSetupAgent(
                $pull[\ILIAS\Refinery\Factory::class],
                $pull[\ILIAS\Data\Factory::class]
            );

        $internal["command.install"] = fn() =>
            new \ILIAS\Setup\CLI\InstallCommand(
                $internal["agent_finder"],
                $internal["config_reader"],
                $internal["common_preconditions"]
            );
        $internal["command.update"] = fn() =>
            new \ILIAS\Setup\CLI\UpdateCommand(
                $internal["agent_finder"],
                $internal["config_reader"],
                $internal["common_preconditions"]
            );
        $internal["command.build"] = fn() =>
            new \ILIAS\Setup\CLI\BuildCommand(
                $internal["agent_finder"]
            );
        $internal["command.achieve"] = fn() =>
            new \ILIAS\Setup\CLI\AchieveCommand(
                $internal["agent_finder"],
                $internal["config_reader"],
                $internal["common_preconditions"],
                $pull[\ILIAS\Refinery\Factory::class],
            );
        $internal["command.status"] = fn() =>
            new \ILIAS\Setup\CLI\StatusCommand(
                $internal["agent_finder"]
            );
        $internal["command.migrate"] = fn() =>
            new \ILIAS\Setup\CLI\MigrateCommand(
                $internal["agent_finder"],
                $internal["common_preconditions"]
            );

        $internal["common_preconditions"] = fn() =>
            [
                new \ilOwnRiskConfirmedObjective(),
                new \ilUseRootConfirmed()
            ];

        $internal["common_agent"] = fn() =>
            new \ilSetupAgent(
                $pull[\ILIAS\Refinery\Factory::class],
                $pull[\ILIAS\Data\Factory::class]
            );

        $internal["agent_finder"] = fn() =>
            new \ILIAS\Setup\ImplementationOfAgentFinder(
                $pull[\ILIAS\Refinery\Factory::class],
                $pull[\ILIAS\Data\Factory::class],
                $use[\ILIAS\Language\Language::class],
                $internal["interface_finder"],
                $seek[\ILIAS\Setup\Agent::class]
            );

        $internal["config_reader"] = fn() =>
            new \ILIAS\Setup\CLI\ConfigReader(
                $internal["json.parser"]
            );

        $internal["interface_finder"] = fn() =>
            new \ILIAS\Setup\ImplementationOfInterfaceFinder();

        $internal["json.parser"] = fn() =>
            new \Seld\JsonLint\JsonParser();
    }
}
