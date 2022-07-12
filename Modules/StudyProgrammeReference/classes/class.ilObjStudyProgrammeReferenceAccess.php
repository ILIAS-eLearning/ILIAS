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

class ilObjStudyProgrammeReferenceAccess extends ilContainerReferenceAccess
{
    /**
    * Checks whether a user may invoke a command or not
    * (this method is called by ilAccessHandler::checkAccess)
    *
    * Please do not check any preconditions handled by
    * ilConditionHandler here. Also don't do any RBAC checks.
    *
    * @global ilAccessHandler $ilAccess
    *
    * @param	string		$a_cmd			command (not permission!)
    * @param	string		$a_permission	permission
    * @param	int			$a_ref_id		reference id
    * @param	int			$a_obj_id		object id
    * @param	int			$a_user_id		user id (if not provided, current user is taken)
    *
    * @return	boolean		true, if everything is ok
    */
    public function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "") : bool
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];
        switch ($a_permission) {
            case 'visible':
            case 'read':
                $target_ref_id = ilContainerReference::_lookupTargetRefId($a_obj_id);
                if (!$ilAccess->checkAccessOfUser($a_user_id, $a_permission, $a_cmd, $target_ref_id)) {
                    return false;
                }
                break;
            case "delete":

                if (!$ilAccess->checkAccessOfUser($a_user_id, $a_permission, $a_cmd, $a_ref_id)) {
                    return false;
                }
                $tree = $DIC['tree'];
                $target_ref_id = ilContainerReference::_lookupTargetRefId($a_obj_id);
                $prg = ilObjStudyProgramme::getInstanceByRefId($target_ref_id);
                $target_id = $prg->getId();
                $progress_db = ilStudyProgrammeDIC::dic()['ilStudyProgrammeUserProgressDB'];
                $parent = $tree->getParentNodeData($a_ref_id);
                if ($parent["type"] === "prg" && !$parent["deleted"]) {
                    $parent = ilObjStudyProgramme::getInstanceByRefId($parent["ref_id"]);
                    foreach ($parent->getProgresses() as $parent_progress
                    ) {
                        $progress = $progress_db->getByPrgIdAndAssignmentId(
                            $target_id,
                            $parent_progress->getAssignmentId()
                        );
                        
                        if (!$progress) {
                            continue;
                        }
                        if ($progress->isRelevant()) {
                            return false;
                        }
                    }
                }

                break;
        }

        return true;
    }

    public static function _getCommands(int $a_ref_id = null) : array
    {
        global $DIC;
        $ilAccess = $DIC->access();
        $prgr_obj_id = ilObject::_lookupObjId($a_ref_id);
        $target_ref_id = ilContainerReference::_lookupTargetRefId($prgr_obj_id);

        $commands = [];

        if ($ilAccess->checkAccess('write', '', $a_ref_id)) {
            $commands[] = ["permission" => "write", "cmd" => "editReference", "lang_var" => "edit"];
        }
        if ($ilAccess->checkAccess('read', '', $target_ref_id)) {
            $commands[] = array('permission' => 'visible', 'cmd' => 'view', 'lang_var' => 'show', 'default' => true);
        }

        return $commands;
    }
}
