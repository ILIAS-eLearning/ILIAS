<?php

class ilObjStudyProgrammeReferenceAccess extends ilContainerReferenceAccess
{
    /**
    * Checks wether a user may invoke a command or not
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
    public function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "")
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
                $assignment_ids = [];
                $target_ref_id = ilContainerReference::_lookupTargetRefId($a_obj_id);
                $prg = ilObjStudyProgramme::getInstanceByRefId($target_ref_id);
                $target_id = $prg->getId();
                $progress_db = ilStudyProgrammeDIC::dic()['ilStudyProgrammeUserProgressDB'];
                $parent = $tree->getParentNodeData($a_ref_id);
                if ($parent["type"] === "prg" && !$parent["deleted"]) {
                    $parent = ilObjStudyProgramme::getInstanceByRefId($parent["ref_id"]);
                    foreach ($parent->getProgresses() as $parent_progress
                    ) {
                        try {
                            $progress =
                                $progress_db->getInstanceForAssignment(
                                    $target_id,
                                    $parent_progress->getAssignmentId()
                                );
                        } catch (ilStudyProgrammeNoProgressForAssignmentException $e) {
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

    public static function _getCommands($a_ref_id = null)
    {
        global $DIC;

        $ilAccess = $DIC->access();

        if ($ilAccess->checkAccess('write', '', $a_ref_id)) {
            // Only local (reference specific commands)
            $commands = [
                ["permission" => "read", "cmd" => "view", "lang_var" => "show", "default" => true]
                ,["permission" => "write", "cmd" => "view", "lang_var" => "edit_content"]
                ,["permission" => "write", "cmd" => "edit", "lang_var" => "settings"]
                ,["permission" => "write", "cmd" => "editReference", "lang_var" => "edit"]
            ];
        } else {
            $commands = ilObjStudyProgrammeAccess::_getCommands();
        }
        return $commands;
    }
}
