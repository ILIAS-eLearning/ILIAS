<?php declare(strict_types = 1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

use ILIAS\Refinery\Transformation;
use ILIAS\Setup\ObjectiveCollection;
use ILIAS\Setup\Objective\NullObjective;
use ILIAS\Setup\Metrics\Storage;
use ILIAS\Setup\Objective;
use ILIAS\Setup\Agent;
use ILIAS\Setup\Config;

/**
 * Class ilUICoreSetupAgent
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilUICoreSetupAgent implements Agent
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
        throw new LogicException(self::class . " has no Config.");
    }

    /**
     * @inheritdoc
     */
    public function getInstallObjective(Config $config = null) : Objective
    {
        return new NullObjective();
    }

    /**
     * @inheritdoc
     */
    public function getUpdateObjective(Config $config = null) : Objective
    {
        return new ilDatabaseUpdateStepsExecutedObjective(
            new ilCtrlDatabaseUpdateSteps()
        );
    }

    /**
     * @inheritdoc
     */
    public function getBuildArtifactObjective() : Objective
    {
        return new ObjectiveCollection(
            'buildIlCtrlArtifacts',
            false,
            new ilCtrlBaseClassArtifactObjective(),
            new ilCtrlStructureArtifactObjective(),
            new ilCtrlPluginStructureArtifactObjective(),
            new ilCtrlSecurityArtifactObjective(),
        );
    }

    /**
     * @inheritdoc
     */
    public function getStatusObjective(Storage $storage) : Objective
    {
        return new NullObjective();
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
    public function getNamedObjective(string $name, Config $config = null) : Objective
    {
        switch ($name) {
            case 'buildIlCtrlArtifacts':
                return $this->getBuildArtifactObjective();

            case 'updateIlCtrlDatabase':
                return $this->getUpdateObjective();

            default:
                throw new InvalidArgumentException("There is no named objective '$name'");
        }
    }
}
