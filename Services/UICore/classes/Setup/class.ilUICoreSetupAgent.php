<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

use ILIAS\Refinery\Transformation;
use ILIAS\Setup\ObjectiveCollection;
use ILIAS\Setup\Objective\NullObjective;
use ILIAS\Setup\Metrics\Storage;
use ILIAS\Setup\Objective;
use ILIAS\Setup\Agent;
use ILIAS\Setup\Config;
use ILIAS\Setup\ObjectiveConstructor;

/**
 * Class ilUICoreSetupAgent
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
    public function getNamedObjectives(?Config $config = null) : array
    {
        return [
            'buildIlCtrlArtifacts' => new ObjectiveConstructor(
                'builds all necessary ilCtrl artifacts.',
                function () {
                    return $this->getBuildArtifactObjective();
                }
            ),

            'updateIlCtrlDatabase' => new ObjectiveConstructor(
                'executes all ilCtrl database update steps.',
                function () {
                    return $this->getUpdateObjective();
                }
            ),
        ];
    }
}
