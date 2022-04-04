<?php declare(strict_types=1);

/* Copyright (c) 2020 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;
use ILIAS\Refinery;

class ilMailSetupAgent implements Setup\Agent
{
    use Setup\Agent\HasNoNamedObjective;

    public function hasConfig() : bool
    {
        return false;
    }

    public function getArrayToConfigTransformation() : Refinery\Transformation
    {
        throw new LogicException('Agent has no config.');
    }

    public function getInstallObjective(Setup\Config $config = null) : Setup\Objective
    {
        return new ilFileSystemComponentDataDirectoryCreatedObjective(
            'mail',
            ilFileSystemComponentDataDirectoryCreatedObjective::DATADIR
        );
    }

    public function getUpdateObjective(Setup\Config $config = null) : Setup\Objective
    {
        return new ilDatabaseUpdateStepsExecutedObjective(
            new ilMailDatabaseUpdateSteps()
        );
    }

    public function getBuildArtifactObjective() : Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }

    public function getStatusObjective(Setup\Metrics\Storage $storage) : Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }

    public function getMigrations() : array
    {
        return [];
    }
}
