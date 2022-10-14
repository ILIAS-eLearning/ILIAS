<?php

declare(strict_types=1);

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
 ********************************************************************
 */

use ILIAS\Setup;

class ilSkillSetupAgent extends Setup\Agent\NullAgent
{
    public function getUpdateObjective(Setup\Config $config = null): Setup\Objective
    {
        return new Setup\ObjectiveCollection(
            "Updates of Services/Skill",
            false,
            new ilDatabaseUpdateStepsExecutedObjective(
                new ilSkillDBUpdateSteps()
            ),
            ...$this->getRbacObjectives()
        );
    }

    public function getStatusObjective(Setup\Metrics\Storage $storage): Setup\Objective
    {
        return new ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new ilSkillDBUpdateSteps());
    }

    public function getRbacObjectives(): array
    {
        $objectives = [];

        // add basic object type
        $objectives[] = new ilObjectNewTypeAddedObjective("skee", "Skill Tree");

        // custom rbac operations
        $objectives[] = new ilAccessCustomRBACOperationAddedObjective(
            "read_comp",
            "Read Competences",
            "object",
            6500,
            ["skee"]
        );
        $objectives[] = new ilAccessCustomRBACOperationAddedObjective(
            "read_profiles",
            "Read Competence Profiles",
            "object",
            6510,
            ["skee"]
        );
        $objectives[] = new ilAccessCustomRBACOperationAddedObjective(
            "manage_comp",
            "Manage Competences",
            "object",
            8500,
            ["skee"]
        );
        $objectives[] = new ilAccessCustomRBACOperationAddedObjective(
            "manage_comp_temp",
            "Manage Competence Templates",
            "object",
            8510,
            ["skee"]
        );
        $objectives[] = new ilAccessCustomRBACOperationAddedObjective(
            "manage_profiles",
            "Manage Competence Profiles",
            "object",
            8520,
            ["skee"]
        );

        // add create operation for relevant container type
        $objectives[] = new ilAccessCustomRBACOperationAddedObjective(
            "create_skee",
            "Create Skill Tree",
            "create",
            9999,
            ["skmg"]
        );

        // common rbac operations
        $objectives[] = new ilAccessRbacStandardOperationsAddedObjective("skee");

        return $objectives;
    }
}
