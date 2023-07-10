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

use ILIAS\ResourceStorage\Flavour\Definition\DefaultDefinitions;
use ILIAS\ResourceStorage\Flavour\Definition\FlavourDefinition;
use ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines\DefaultMachines;
use ILIAS\ResourceStorage\Flavour\Machine\FlavourMachine;
use ILIAS\Setup\Artifact;
use ILIAS\Setup\Artifact\ArrayArtifact;
use ILIAS\Setup\Artifact\BuildArtifactObjective;
use ILIAS\Setup\ImplementationOfInterfaceFinder;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilResourceStorageFlavourArtifact extends BuildArtifactObjective
{
    public const PATH = './Services/ResourceStorage/artifacts/flavour_data.php';

    public function getArtifactPath(): string
    {
        return self::PATH;
    }

    public function build(): Artifact
    {
        $default_machine_ids = (new DefaultMachines())->get();
        $machines = [];

        $finder = new ImplementationOfInterfaceFinder();

        foreach ($finder->getMatchingClassNames(FlavourMachine::class) as $machine_name) {
            /** @var $machine \ILIAS\ResourceStorage\Flavour\Machine\FlavourMachine */
            $machine = new $machine_name();
            $machine_id = $machine->getId();

            if ($machine_name === $machine_id) {
                throw new LogicException(
                    "PLEASE beware that class-related magic constants are not recommended. Altering the implementation-name may result in lost flavours."
                );
            }

            if (64 < strlen($machine_id)) {
                throw new LogicException("ID of machine '$machine_name' exceeds 64 characters.");
            }

            if (isset($default_machine_ids[$machine_id])) {
                throw new LogicException(
                    "Machine '$default_machine_ids[$machine_id]' and '$machine_name' implement the same ID ($machine_id)."
                );
            }

            if (isset($machines[$machine_id])) {
                throw new LogicException(
                    "Machine '$machines[$machine_id]' and '$machine_name' implement the same ID ($machine_id)."
                );
            }

            $machines[$machine_id] = $machine_name;
        }

        $default_definition_ids = (new DefaultDefinitions())->get();
        $definitions = [];

        foreach ($finder->getMatchingClassNames(FlavourDefinition::class) as $definition_name) {
            /** @var $definition FlavourDefinition */
            $definition = new $definition_name();
            $definition_id = $definition->getId();

            if ($definition_name === $definition_id) {
                throw new LogicException(
                    "PLEASE beware that class-related magic constants are not recommended. Altering the implementation-name may result in lost flavours."
                );
            }

            if (64 < strlen($definition_id)) {
                throw new LogicException("ID of definition '$definition_name' exceeds 64 characters.");
            }

            if (isset($definitions[$definition_id])) {
                throw new LogicException(
                    "Definition '$definitions[$definition_id]' and '$definition_name' implement the same ID ($definition_id)."
                );
            }

            if (isset($default_definition_ids[$definition_id])) {
                throw new LogicException(
                    "Definition '$default_definition_ids[$definition_id]' and '$definition_name' implement the same ID ($definition_id)."
                );
            }

            $definitions[$definition_id] = $definition_name;
        }

        return new ArrayArtifact([
            'machines' => $machines,
            'definitions' => $definitions
        ]);
    }
}
