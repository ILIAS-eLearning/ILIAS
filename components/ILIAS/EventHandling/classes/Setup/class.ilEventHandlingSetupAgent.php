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

use ILIAS\Setup;
use ILIAS\Refinery\Transformation;

class ilEventHandlingSetupAgent implements Setup\Agent
{
    use Setup\Agent\HasNoNamedObjective;

    /**
     * @inheritDoc
     */
    public function hasConfig(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getArrayToConfigTransformation(): Transformation
    {
        throw new LogicException(self::class . " has no Config.");
    }

    /**
     * @inheritDoc
     */
    public function getInstallObjective(Setup\Config $config = null): Setup\Objective
    {
        return new ilEventHandlingDefinitionsStoredObjective();
    }

    /**
     * @inheritDoc
     */
    public function getUpdateObjective(Setup\Config $config = null): Setup\Objective
    {
        return new Setup\ObjectiveCollection(
            "Updates of Services/EventHandling",
            false,
            new ilDatabaseUpdateStepsExecutedObjective(
                new ilIntroduceEventHandlingArtifactDBUpdateSteps()
            ),
            new ilEventHandlingDefinitionsStoredObjective(false)
        );
    }

    /**
     * @inheritDoc
     */
    public function getBuildObjective(): Setup\Objective
    {
        return new Setup\ObjectiveCollection(
            "Artifacts for Services/EventHandling",
            false,
            new ilEventHandlingBuildEventInfoObjective()
        );
    }

    /**
     * @inheritDoc
     */
    public function getStatusObjective(Setup\Metrics\Storage $storage): Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }

    /**
     * @inheritDoc
     */
    public function getMigrations(): array
    {
        return [];
    }
}
