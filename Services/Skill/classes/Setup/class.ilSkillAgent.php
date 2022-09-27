<?php declare(strict_types=1);

include_once 'Services/AccessControl/classes/Setup/class.ilAccessRBACOperationsAddedObjective.php';
include_once 'Services/Object/classes/Setup/class.ilObjectNewTypeAddedObjective.php';
include_once 'Services/AccessControl/classes/Setup/class.ilAccessCustomRBACOperationAddedObjective.php';
include_once 'Services/Object/classes/class.ilObject.php';
include_once 'Services/AccessControl/classes/class.ilRbacReview.php';

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

class ilSkillAgent extends Setup\Agent\NullAgent
{
    public function getUpdateObjective(Setup\Config $config = null): Setup\Objective
    {
        return new Setup\ObjectiveCollection(
            "Service/Skill Objectives",
            false,
            new ilDatabaseUpdateStepsExecutedObjective(new ilSkillDBUpdateSteps()),
            ...($this->step_4() + $this->step_5())
        );
    }

    public function step_4() : array
    {
        $objectives = [];
        $skill_tree_type_id = ilObject::_getObjectTypeIdByTitle('skee');

        if (!$skill_tree_type_id) {
            $skill_tree_type_id = ilDBUpdateNewObjectType::addNewType('skee', 'Skill Tree');

            $objectives[] = new ilAccessCustomRBACOperationAddedObjective(
                'read_comp',
                'Read Competences',
                'object',
                6500
            );
            $objectives[] = new ilAccessCustomRBACOperationAddedObjective(
                'read_profiles',
                'Read Competence Profiles',
                'object',
                6510
            );
            $objectives[] = new ilAccessCustomRBACOperationAddedObjective(
                'manage_comp',
                'Manage Competences',
                'object',
                8500
            );
            $objectives[] = new ilAccessCustomRBACOperationAddedObjective(
                'manage_comp_temp',
                'Manage Competence Templates',
                'object',
                8510
            );
            $objectives[] = new ilAccessCustomRBACOperationAddedObjective(
                'manage_profiles',
                'Manage Competence Profiles',
                'object',
                8520
            );

            $objectives[] = new ilAccessRBACOperationsAddedObjective(
                $skill_tree_type_id,
                [
                    ilDBUpdateNewObjectType::RBAC_OP_EDIT_PERMISSIONS,
                    ilDBUpdateNewObjectType::RBAC_OP_VISIBLE,
                    ilDBUpdateNewObjectType::RBAC_OP_READ,
                    ilDBUpdateNewObjectType::RBAC_OP_WRITE,
                    ilDBUpdateNewObjectType::RBAC_OP_DELETE,
                    ilDBUpdateNewObjectType::RBAC_OP_COPY
                ]
            );

            $ops_id = 1;

            $skill_tree_type_id = ilObject::_getObjectTypeIdByTitle('skmg');
            if ($skill_tree_type_id) {
                $objectives[] = new ilAccessRBACOperationsAddedObjective($skill_tree_type_id, [$ops_id]);
            }
        }
        return $objectives;
    }

    public function step_5() : array
    {
        $objectives = [];
        $skill_tree_type_id = ilObject::_getObjectTypeIdByTitle('skee');
        $ops_id = [ilRbacReview::_getCustomRBACOperationId('read_comp')];
        if ($skill_tree_type_id !== null && $ops_id !== null) {
            $objectives[] = new ilAccessRBACOperationsAddedObjective($skill_tree_type_id, $ops_id);
        }
        $skill_tree_type_id = ilObject::_getObjectTypeIdByTitle('skee');
        $ops_ids[] = ilRbacReview::_getCustomRBACOperationId('read_profiles');
        $ops_ids[] = ilRbacReview::_getCustomRBACOperationId('manage_comp');
        $ops_ids[] = ilRbacReview::_getCustomRBACOperationId('manage_comp_temp');
        $ops_ids[] = ilRbacReview::_getCustomRBACOperationId('manage_profiles');
        if ($skill_tree_type_id !== null && !in_array(null, $ops_ids, true)) {
            $objectives[] = new ilAccessRBACOperationsAddedObjective($skill_tree_type_id, [$ops_id]);
        }
        return $objectives;
    }
}
