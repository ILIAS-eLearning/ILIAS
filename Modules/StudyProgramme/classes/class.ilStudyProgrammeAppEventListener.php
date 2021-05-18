<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/**
 * Event listener for study programs. Has the following tasks:
 *
 *  * Remove all assignments of a user on all study programms when the
 *    user is removed.
 *
 *  * Add/Remove courses to/trom study programms, if upper category is under surveillance
 *
 * @author  Richard Klees <richard.klees@concepts-and-training.de>
 *
 */

class ilStudyProgrammeAppEventListener
{

    /**
     * @throws ilException
     */
    public static function handleEvent($a_component, $a_event, $a_parameter)
    {
        switch ($a_component) {
            case "Services/User":
                switch ($a_event) {
                    case "deleteUser":
                        self::onServiceUserDeleteUser($a_parameter);
                        break;
                }
                break;
            case "Services/Tracking":
                switch ($a_event) {
                    case "updateStatus":
                        self::onServiceTrackingUpdateStatus($a_parameter);
                        break;
                }
                break;
            case "Services/Tree":
                switch ($a_event) {
                    case "insertNode":
                        self::onServiceTreeInsertNode($a_parameter);
                        break;
                    case "moveTree":
                        self::onServiceTreeMoveTree($a_parameter);
                        break;
                }
                break;
            case "Services/Object":
                switch ($a_event) {
                    case "delete":
                    case "toTrash":
                        self::onServiceObjectDeleteOrToTrash($a_parameter);
                        break;
                }
                break;
            case "Services/ContainerReference":
                switch ($a_event) {
                    case "deleteReference":
                        self::onServiceObjectDeleteOrToTrash($a_parameter);
                        break;
                }
                break;

            case "Modules/Course":
                switch ($a_event) {
                    case "addParticipant":
                        self::addMemberToProgrammes(
                            ilStudyProgrammeAutoMembershipSource::TYPE_COURSE,
                            $a_parameter
                        );
                        break;
                    case "deleteParticipant":
                        self::removeMemberFromProgrammes(
                            ilStudyProgrammeAutoMembershipSource::TYPE_COURSE,
                            $a_parameter
                        );
                        break;
                }
                break;
            case "Modules/Group":
                switch ($a_event) {
                    case "addParticipant":
                        self::addMemberToProgrammes(
                            ilStudyProgrammeAutoMembershipSource::TYPE_GROUP,
                            $a_parameter
                        );
                        break;
                    case "deleteParticipant":
                        self::removeMemberFromProgrammes(
                            ilStudyProgrammeAutoMembershipSource::TYPE_GROUP,
                            $a_parameter
                        );
                        break;
                }
                break;
            case "Services/AccessControl":
                switch ($a_event) {
                    case "assignUser":
                        self::addMemberToProgrammes(
                            ilStudyProgrammeAutoMembershipSource::TYPE_ROLE,
                            $a_parameter
                        );
                        break;
                    case "deassignUser":
                        self::removeMemberFromProgrammes(
                            ilStudyProgrammeAutoMembershipSource::TYPE_ROLE,
                            $a_parameter
                        );
                        break;
                }
                break;
            case "Modules/OrgUnit":
                switch ($a_event) {
                    case "assignUserToPosition":
                        self::addMemberToProgrammes(
                            ilStudyProgrammeAutoMembershipSource::TYPE_ORGU,
                            $a_parameter
                        );
                        break;
                    case "deassignUserFromPosition":
                    //case "delete":
                        self::removeMemberFromProgrammes(
                            ilStudyProgrammeAutoMembershipSource::TYPE_ORGU,
                            $a_parameter
                        );
                        break;
                }
                break;
            case "Modules/StudyProgramme":
                switch ($a_event) {
                    case "userReAssigned":
                        self::sendReAssignedMail($a_parameter);
                        break;
                    case 'informUserToRestart':
                        self::sendInformToReAssignMail($a_parameter);
                        break;
                    case 'userRiskyToFail':
                        self::sendRiskyToFailMail($a_parameter);
                }
                break;#
            default:
                throw new ilException("ilStudyProgrammeAppEventListener::handleEvent: "
                                     . "Won't handle events of '$a_component'.");
        }
    }

    private static function onServiceUserDeleteUser($a_parameter)
    {
        $assignments = ilStudyProgrammeDIC::dic()['ilStudyProgrammeUserAssignmentDB']->getInstancesOfUser((int) $a_parameter["usr_id"]);
        foreach ($assignments as $ass) {
            $prg = ilObjStudyProgramme::getInstanceByObjId($ass->getRootId());
            $prg->removeAssignment($ass);
        }
    }

    private static function onServiceTrackingUpdateStatus($a_par)
    {
        require_once("./Services/Tracking/classes/class.ilLPStatus.php");
        if ($a_par["status"] != ilLPStatus::LP_STATUS_COMPLETED_NUM) {
            return;
        }

        require_once("./Modules/StudyProgramme/classes/class.ilObjStudyProgramme.php");
        ilObjStudyProgramme::setProgressesCompletedFor((int) $a_par["obj_id"], (int) $a_par["usr_id"]);
    }

    private static function onServiceTreeInsertNode($a_parameter)
    {
        $node_ref_id = $a_parameter["node_id"];
        $parent_ref_id = $a_parameter["parent_id"];

        $node_type = ilObject::_lookupType($node_ref_id, true);
        $parent_type = ilObject::_lookupType($parent_ref_id, true);

        if ($node_type == "crsr" && $parent_type == "prg") {
            self::adjustProgrammeLPMode($parent_ref_id);
        }
        if (in_array($node_type, ["prg", "prgr"]) && $parent_type == "prg") {
            self::addMissingProgresses($parent_ref_id);
        }
        if ($node_type == "crs" && $parent_type == "cat") {
            self::addCrsToProgrammes($node_ref_id, $parent_ref_id);
        }
    }

    private static function onServiceTreeMoveTree($a_parameter)
    {
        $node_ref_id = $a_parameter["source_id"];
        $new_parent_ref_id = $a_parameter["target_id"];
        $old_parent_ref_id = $a_parameter["old_parent_id"];

        $node_type = ilObject::_lookupType($node_ref_id, true);
        $new_parent_type = ilObject::_lookupType($new_parent_ref_id, true);
        $old_parent_type = ilObject::_lookupType($old_parent_ref_id, true);

        if (!in_array($node_type, ["crsr","crs"])
            || (
                ($new_parent_type != "prg" && $old_parent_type != "prg")
                &&
                $old_parent_type != "cat"
            )
        ) {
            return;
        }

        if ($node_type === 'crs') {
            self::removeCrsFromProgrammes($node_ref_id, $old_parent_ref_id);
            if ($new_parent_type === 'cat') {
                self::addCrsToProgrammes($node_ref_id, $new_parent_ref_id);
            }
        }

        if ($new_parent_type == "prg") {
            self::adjustProgrammeLPMode($new_parent_ref_id);
        } elseif ($old_parent_type == "prg") {
            self::adjustProgrammeLPMode($old_parent_ref_id);
        }
    }

    private static function onServiceObjectDeleteOrToTrash($a_parameter)
    {
        $node_ref_id = $a_parameter["ref_id"];
        $old_parent_ref_id = $a_parameter["old_parent_ref_id"];

        $node_type = $a_parameter["type"];
        $old_parent_type = ilObject::_lookupType($old_parent_ref_id, true);

        if ($old_parent_type !== "prg") {
            return;
        }

        self::adjustProgrammeLPMode($old_parent_ref_id);
    }

    private static function getStudyProgramme($a_ref_id)
    {
        require_once("Modules/StudyProgramme/classes/class.ilObjStudyProgramme.php");
        return ilObjStudyProgramme::getInstanceByRefId($a_ref_id);
    }

    private static function adjustProgrammeLPMode($a_ref_id)
    {
        $obj = self::getStudyProgramme($a_ref_id);
        $obj->adjustLPMode();
    }

    private static function addMissingProgresses($a_ref_id)
    {
        $obj = self::getStudyProgramme($a_ref_id);
        $obj->addMissingProgresses();
    }

    private static function addCrsToProgrammes(int $crs_ref_id, int $cat_ref_id)
    {
        ilObjStudyProgramme::addCrsToProgrammes($crs_ref_id, $cat_ref_id);
    }

    private static function removeCrsFromProgrammes(int $crs_ref_id, int $cat_ref_id)
    {
        ilObjStudyProgramme::removeCrsFromProgrammes($crs_ref_id, $cat_ref_id);
    }

    private static function addMemberToProgrammes(string $src_type, array $params)
    {
        $usr_id = $params['usr_id'];
        $id = $params['obj_id'];
        if (
            $src_type === ilStudyProgrammeAutoMembershipSource::TYPE_GROUP ||
            $src_type === ilStudyProgrammeAutoMembershipSource::TYPE_COURSE
        ) {
            $id = array_shift(ilObject::_getAllReferences($id));
        }
        if ($src_type === ilStudyProgrammeAutoMembershipSource::TYPE_ROLE) {
            $id = $params['role_id'];
        }

        ilObjStudyProgramme::addMemberToProgrammes($src_type, $id, $usr_id);
    }

    private static function removeMemberFromProgrammes(string $src_type, array $params)
    {
        $usr_id = $params['usr_id'];
        $id = $params['obj_id'];
        if (
            $src_type === ilStudyProgrammeAutoMembershipSource::TYPE_GROUP ||
            $src_type === ilStudyProgrammeAutoMembershipSource::TYPE_COURSE
        ) {
            $id = array_shift(ilObject::_getAllReferences($id));
        }
        if ($src_type === ilStudyProgrammeAutoMembershipSource::TYPE_ROLE) {
            $id = $params['role_id'];
        }

        ilObjStudyProgramme::removeMemberFromProgrammes($src_type, $id, $usr_id);
    }

    private static function sendReAssignedMail(array $params) : void
    {
        $usr_id = $params['usr_id'];
        $ref_id = $params['root_prg_ref_id'];

        ilObjStudyProgramme::sendReAssignedMail($ref_id, $usr_id);
    }

    /**
     * @throws ilException
     */
    private static function sendInformToReAssignMail(array $params) : void
    {
        $usr_id = $params['usr_id'];
        $assignment_id = $params['ass_id'];

        ilObjStudyProgramme::sendInformToReAssignMail($assignment_id, $usr_id);
    }

    /**
     * @throws ilException
     */
    private static function sendRiskyToFailMail(array $params) : void
    {
        $usr_id = $params['usr_id'];
        $progress_id = $params['progress_id'];
        ilObjStudyProgramme::sendRiskyToFailMail($progress_id, $usr_id);
    }
}
