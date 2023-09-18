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

namespace ILIAS\ResourceStorage\Flavour\Machine;

use ILIAS\ResourceStorage\Flavour\Definition\FlavourDefinition;
use ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines\DefaultMachines;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Factory
{
    /**
     * @var array<string, class-string<FlavourMachine>>
     */
    protected array $machines_string = [];
    /**
     * @var FlavourMachine[]
     */
    protected array $machines_instances = [];
    private \ILIAS\ResourceStorage\Flavour\Engine\Factory $engines;

    public function __construct(
        \ILIAS\ResourceStorage\Flavour\Engine\Factory $engine_factory,
        array $machines_string = []
    ) {
        $default_machines = new DefaultMachines();
        $this->machines_string = array_merge($default_machines->get(), $machines_string);
        $this->engines = $engine_factory;
    }

    public function get(FlavourDefinition $definition): FlavourMachine
    {
        $null_machine = new NullMachine();
        $definition_id = $definition->getFlavourMachineId();

        $machine_string = $this->machines_string[$definition_id] ?? null;
        if ($machine_string === null) {
            return $null_machine->withReason('No machine found for definition ' . $definition->getId());
        }
        if (isset($this->machines_instances[$definition_id])) {
            return $this->machines_instances[$definition_id];
        }
        try {
            $machine = new $machine_string();
        } catch (\Throwable $t) {
            return $null_machine->withReason('Could not instantiate machine ' . $machine_string);
        }

        if (!$machine instanceof FlavourMachine) {
            return $null_machine->withReason('Machine ' . $machine_string . ' does not implement FlavourMachine');
        }

        $engine = $this->engines->get($machine);

        if (!$engine instanceof \ILIAS\ResourceStorage\Flavour\Engine\Engine || !$engine->isRunning()) {
            return $null_machine->withReason(
                'Machine ' . $machine_string . ' depends on engine ' .
                $machine->dependsOnEngine()
                . ' which is not running or available.'
            );
        }

        $machine = $machine->withEngine($engine);

        return $this->machines_instances[$definition_id] = $machine;
    }
}
