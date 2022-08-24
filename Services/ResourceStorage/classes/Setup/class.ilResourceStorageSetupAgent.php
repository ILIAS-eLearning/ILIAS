<?php

declare(strict_types=1);

use ILIAS\Refinery\Transformation;
use ILIAS\Setup\Agent;
use ILIAS\Setup\Config;
use ILIAS\Setup\Metrics;
use ILIAS\Setup\Objective;
use ILIAS\Setup\ObjectiveCollection;

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
 * Class ilResourceStorageSetupAgent
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilResourceStorageSetupAgent implements Agent
{
    use Agent\HasNoNamedObjective;

    public function hasConfig(): bool
    {
        return false;
    }

    public function getArrayToConfigTransformation(): Transformation
    {
        throw new \LogicException("Agent has no config.");
    }

    public function getInstallObjective(Config $config = null): Objective
    {
        return new ObjectiveCollection(
            'IRSS Installation',
            false,
            new ilStorageContainersExistingObjective(),
            new ilDatabaseUpdateStepsExecutedObjective(
                new ilResourceStorageDB80()
            )
        );
    }

    public function getUpdateObjective(Config $config = null): Objective
    {
        return new ObjectiveCollection(
            'IRSS Update',
            false,
            new ilStorageContainersExistingObjective(),
            new ilDatabaseUpdateStepsExecutedObjective(
                new ilResourceStorageDB80()
            )
        );
    }

    public function getBuildArtifactObjective(): Objective
    {
        return new Objective\NullObjective();
    }

    public function getStatusObjective(Metrics\Storage $storage): Objective
    {
        return new Objective\NullObjective();
    }

    /**
     * @return \ilStorageHandlerV1Migration[]
     */
    public function getMigrations(): array
    {
        return [new ilStorageHandlerV1Migration()];
    }
}
