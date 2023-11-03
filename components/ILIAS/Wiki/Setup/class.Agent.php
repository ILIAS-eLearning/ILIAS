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

namespace ILIAS\Wiki\Setup;

use ILIAS\Setup;
use ILIAS\Setup\Objective;
use ILIAS\Setup\Metrics;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class Agent extends Setup\Agent\NullAgent
{
    public function getUpdateObjective(Setup\Config $config = null): Setup\Objective
    {
        return new Setup\ObjectiveCollection(
            "Updates of Services/Skill",
            false,
            ...$this->getObjectives()
        );
    }

    public function getStatusObjective(Metrics\Storage $storage): Objective
    {
        return new \ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new ilWikiDBUpdateSteps());
    }

    protected function getObjectives(): array
    {
        $objectives = [];

        $objectives[] = new \ilAccessCustomRBACOperationAddedObjective(
            "add_pages",
            "Create Pages",
            "object",
            3070,
            ["wiki"]
        );

        $objectives[] = new AccessRBACOperationClonedObjective(
            "wiki",
            "edit_content",
            "add_pages"
        );


        // db update steps
        $objectives[] = new \ilDatabaseUpdateStepsExecutedObjective(new ilWikiDBUpdateSteps());

        return $objectives;
    }
}
