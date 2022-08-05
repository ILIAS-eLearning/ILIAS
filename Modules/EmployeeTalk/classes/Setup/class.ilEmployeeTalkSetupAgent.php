<?php declare(strict_types=1);

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

namespace ILIAS\EmployeeTalk\Setup;

use ILIAS\Refinery\Transformation;
use ILIAS\Setup;
use ILIAS\Setup\Config;
use ILIAS\Setup\Metrics;
use ILIAS\Setup\Migration;
use ILIAS\Setup\Objective;

/**
 * @author Nicolas Schaefli <nick@fluxlabs.ch>
 */
final class ilEmployeeTalkSetupAgent implements Setup\Agent
{
    public function hasConfig() : bool
    {
        return false;
    }

    public function getArrayToConfigTransformation() : Transformation
    {
        throw new \LogicException(
            self::class . " has no config."
        );
    }

    public function getInstallObjective(Config $config = null) : Objective
    {
        return new \ilTreeAdminNodeAddedObjective('tala', '__TalkTemplateAdministration');
    }

    public function getBuildArtifactObjective() : Objective
    {
        return new Objective\NullObjective();
    }

    public function getStatusObjective(Metrics\Storage $storage) : Objective
    {
        return new Objective\NullObjective();
    }

    public function getMigrations() : array
    {
        return [];
    }

    public function getNamedObjectives(?Config $config = null) : array
    {
        return [];
    }


    public function getUpdateObjective(Setup\Config $config = null) : Setup\Objective
    {
        return new Setup\ObjectiveCollection(
            'Employee Talks',
            true,
            new \ilTreeAdminNodeAddedObjective('tala', '__TalkTemplateAdministration'),
            new \ilDatabaseUpdateStepsExecutedObjective(new ilEmployeeTalkDBUpdateSteps())
        );
    }
}
