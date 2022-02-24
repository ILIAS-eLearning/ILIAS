<?php declare(strict_types=1);

use ILIAS\Setup;
use ILIAS\Refinery\Transformation;

class ilComponentsSetupAgent implements Setup\Agent
{
    use Setup\Agent\HasNoNamedObjective;

    /**
     * @inheritdoc
     */
    public function hasConfig() : bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getArrayToConfigTransformation() : Transformation
    {
        throw new LogicException(self::class . " has no Config.");
    }

    /**
     * @inheritdoc
     */
    public function getInstallObjective(Setup\Config $config = null) : Setup\Objective
    {
        return new ilComponentDefinitionsStoredObjective();
    }

    /**
     * @inheritdoc
     */
    public function getUpdateObjective(Setup\Config $config = null) : Setup\Objective
    {
        return new Setup\ObjectiveCollection(
            "Updates of Services/Components",
            false,
            new ilDatabaseUpdateStepsExecutedObjective(
                new ilIntroduceComponentArtifactDBUpdateSteps()
            ),
            new ilComponentDefinitionsStoredObjective(false)
        );
    }

    /**
     * @inheritdoc
     */
    public function getBuildArtifactObjective() : Setup\Objective
    {
        return new Setup\ObjectiveCollection(
            "Artifacts for Services/Component",
            false,
            new ilComponentBuildComponentInfoObjective(),
            new ilComponentBuildPluginInfoObjective()
        );
    }

    /**
     * @inheritdoc
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
