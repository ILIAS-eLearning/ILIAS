<?php

use ILIAS\Setup;
use ILIAS\Refinery\Transformation;

class ilEventHandlingSetupAgent implements Setup\Agent
{
    use Setup\Agent\HasNoNamedObjective;

    /**
     * @inheritDoc
     */
    public function hasConfig() : bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getArrayToConfigTransformation() : Transformation
    {
        throw new LogicException(self::class . " has no Config.");
    }

    /**
     * @inheritDoc
     */
    public function getInstallObjective(Setup\Config $config = null) : Setup\Objective
    {
        return new ilEventHandlingDefinitionsStoredObjective();
    }

    /**
     * @inheritDoc
     */
    public function getUpdateObjective(Setup\Config $config = null) : Setup\Objective
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
    public function getBuildArtifactObjective() : Setup\Objective
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
    public function getStatusObjective(Setup\Metrics\Storage $storage) : Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }

    /**
     * @inheritDoc
     */
    public function getMigrations() : array
    {
        return [];
    }
}