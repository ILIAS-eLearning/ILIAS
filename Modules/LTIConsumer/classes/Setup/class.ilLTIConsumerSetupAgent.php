<?php declare(strict_types=1);

use ILIAS\Setup;
use ILIAS\Refinery;
use ILIAS\UI;

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
class ilLTIConsumerSetupAgent implements Setup\Agent
{
    use Setup\Agent\HasNoNamedObjective;

    public function hasConfig() : bool
    {
        return false;
    }

    public function getArrayToConfigTransformation() : Refinery\Transformation
    {
        throw new LogicException("Agent has no config.");
    }

    public function getInstallObjective(Setup\Config $config = null) : Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }

    public function getUpdateObjective(Setup\Config $config = null) : Setup\Objective
    {
        return new ilDatabaseUpdateStepsExecutedObjective(
            new ilLTIConsumerDatabaseUpdateSteps()
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

    /**
     * @return mixed[]
     */
    public function getMigrations() : array
    {
        return [];
    }
}
