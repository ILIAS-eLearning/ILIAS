<?php

use ILIAS\Setup;
use ILIAS\Refinery\Transformation;
use ILIAS\Setup\ObjectiveCollection;

/**
 * Class ilUICoreSetupAgent
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
final class ilUICoreSetupAgent implements Setup\Agent
{
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
        throw new \LogicException(self::class . " has no Config.");
    }

    /**
     * @inheritdoc
     */
    public function getInstallObjective(Setup\Config $config = null) : Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }

    /**
     * @inheritdoc
     */
    public function getUpdateObjective(Setup\Config $config = null) : Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }

    /**
     * @inheritdoc
     */
    public function getBuildArtifactObjective() : Setup\Objective
    {
        return new ObjectiveCollection(
            'buildIlCtrlArtifacts',
            false,
            new \ilCtrlStructureArtifactObjective(),
            new \ilCtrlBaseClassArtifactObjective()
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

    /**
     * @inheritDoc
     */
    public function getNamedObjective(string $name, Setup\Config $config = null) : Setup\Objective
    {
        switch ($name) {
            case 'buildIlCtrlArtifacts':
                return $this->getBuildArtifactObjective();

            default:
                throw new \InvalidArgumentException("There is no named objective '$name'");
        }
    }
}
