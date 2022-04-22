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

/**
 * Event listener for study programs. Has the following tasks:
 *
 *  * Remove all assignments of a user on all study programms when the
 *    user is removed.
 *
 *  * Add/Remove courses to/from study programms, if upper category is under surveillance
 *
 * @author  Richard Klees <richard.klees@concepts-and-training.de>
 *
 */
class ilStudyProgrammeAppEventListener
{
    /**
     * @throws ilException
     */
    public static function handleEvent(string $component, string $event, array $parameter) : void
    {
        switch ($component) {
            case "Services/User":
                switch ($event) {
                    case "deleteUser":
                        self::onServiceUserDeleteUser($parameter);
                        break;
                }
                break;
            case "Services/Tracking":
                switch ($event) {
                    case "updateStatus":
                        self::onServiceTrackingUpdateStatus($parameter);
                        break;
                }
                break;
            case "Services/Tree":
                switch ($event) {
                    case "insertNode":
                        self::onServiceTreeInsertNode($parameter);
                        break;
                    case "moveTree":
                        self::onServiceTreeMoveTree($parameter);
                        break;
                }
                break;
            case "Services/Object":
                switch ($event) {
                    case "delete":
                    case "toTrash":
                        self::onServiceObjectDeleteOrToTrash($parameter);
                        break;
                }
                break;
            case "Services/ContainerReference":
                switch ($event) {
                    case "deleteReference":
                        self::onServiceObjectDeleteOrToTrash($parameter);
                        break;
                }
                break;

            case "Modules/Course":
                switch ($event) {
                    case "addParticipant":
                        self::addMemberToProgrammes(
                            ilStudyProgrammeAutoMembershipSource::TYPE_COURSE,
                            $parameter
                        );
                        break;
                    case "deleteParticipant":
                        self::removeMemberFromProgrammes(
                            ilStudyProgrammeAutoMembershipSource::TYPE_COURSE,
                            $parameter
                        );
                        break;
                }
                break;
            case "Modules/Group":
                switch ($event) {
                    case "addParticipant":
                        self::addMemberToProgrammes(
                            ilStudyProgrammeAutoMembershipSource::TYPE_GROUP,
                            $parameter
                        );
                        break;
                    case "deleteParticipant":
                        self::removeMemberFromProgrammes(
                            ilStudyProgrammeAutoMembershipSource::TYPE_GROUP,
                            $parameter
                        );
                        break;
                }
                break;
            case "Services/AccessControl":
                switch ($event) {
                    case "assignUser":
                        self::addMemberToProgrammes(
                            ilStudyProgrammeAutoMembershipSource::TYPE_ROLE,
                            $parameter
                        );
                        break;
                    case "deassignUser":
                        self::removeMemberFromProgrammes(
                            ilStudyProgrammeAutoMembershipSource::TYPE_ROLE,
                            $parameter
                        );
                        break;
                }
                break;
            case "Modules/OrgUnit":
                switch ($event) {
                    case "assignUserToPosition":
                        self::addMemberToProgrammes(
                            ilStudyProgrammeAutoMembershipSource::TYPE_ORGU,
                            $parameter
                        );
                        break;
                    case "deassignUserFromPosition":
                        self::removeMemberFromProgrammes(
                            ilStudyProgrammeAutoMembershipSource::TYPE_ORGU,
                            $parameter
                        );
                        break;
                }
                break;
            case "Modules/StudyProgramme":
                switch ($event) {
                    case "userReAssigned":
                        self::sendReAssignedMail($parameter);
                        break;
                    case 'informUserToRestart':
                        self::sendInformToReAssignMail($parameter);
                        break;
                    case 'userRiskyToFail':
                        self::sendRiskyToFailMail($parameter);
                }
                break;
            default:
                throw new ilException(
                    "ilStudyProgrammeAppEventListener::handleEvent: Won't handle events of '$component'."
                );
        }
    }

    private static function onServiceUserDeleteUser(array $parameter) : void
    {
        $assignments = ilStudyProgrammeDIC::dic()['ilStudyProgrammeUserAssignmentDB']
            ->getInstancesOfUser((int) $parameter["usr_id"])
        ;

        foreach ($assignments as $ass) {
            $prg = ilObjStudyProgramme::getInstanceByObjId($ass->getRootId());
            $prg->removeAssignment($ass);
        }
    }

    private static function onServiceTrackingUpdateStatus(array $parameter) : void
    {
        if ((int) $parameter["status"] !== ilLPStatus::LP_STATUS_COMPLETED_NUM) {
            return;
        }

        ilObjStudyProgramme::setProgressesCompletedFor((int) $parameter["obj_id"], (int) $parameter["usr_id"]);
    }

    private static function onServiceTreeInsertNode(array $parameter) : void
    {
        $node_ref_id = (int) $parameter["node_id"];
        $parent_ref_id = (int) $parameter["parent_id"];

        $node_type = ilObject::_lookupType($node_ref_id, true);
        $parent_type = ilObject::_lookupType($parent_ref_id, true);

        if ($node_type === "crsr" && $parent_type === "prg") {
            self::adjustProgrammeLPMode($parent_ref_id);
        }
        if ($parent_type === "prg" && in_array($node_type, ["prg", "prgr"])) {
            self::addMissingProgresses($parent_ref_id);
        }
        if ($node_type === "crs" && $parent_type === "cat") {
            self::addCrsToProgrammes($node_ref_id, $parent_ref_id);
        }
    }

    private static function onServiceTreeMoveTree(array $parameter) : void
    {
        $node_ref_id = (int) $parameter["source_id"];
        $new_parent_ref_id = (int) $parameter["target_id"];
        $old_parent_ref_id = (int) $parameter["old_parent_id"];

        $node_type = ilObject::_lookupType($node_ref_id, true);
        $new_parent_type = ilObject::_lookupType($new_parent_ref_id, true);
        $old_parent_type = ilObject::_lookupType($old_parent_ref_id, true);

        if (!in_array($node_type, ["crsr","crs"])
            || (
                ($new_parent_type !== "prg" && $old_parent_type !== "prg")
                &&
                $old_parent_type !== "cat"
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

        if ($new_parent_type === "prg") {
            self::adjustProgrammeLPMode($new_parent_ref_id);
        } elseif ($old_parent_type === "prg") {
            self::adjustProgrammeLPMode($old_parent_ref_id);
        }
    }

    private static function onServiceObjectDeleteOrToTrash(array $parameter) : void
    {
        $old_parent_ref_id = (int) $parameter["old_parent_ref_id"];

        $old_parent_type = ilObject::_lookupType($old_parent_ref_id, true);

        if ($old_parent_type !== "prg") {
            return;
        }

        self::adjustProgrammeLPMode($old_parent_ref_id);
    }

    private static function getStudyProgramme(int $ref_id) : ilObjStudyProgramme
    {
        return ilObjStudyProgramme::getInstanceByRefId($ref_id);
    }

    private static function adjustProgrammeLPMode(int $ref_id) : void
    {
        $obj = self::getStudyProgramme($ref_id);
        $obj->adjustLPMode();
    }

    private static function addMissingProgresses(int $ref_id) : void
    {
        $obj = self::getStudyProgramme($ref_id);
        $obj->addMissingProgresses();
    }

    private static function addCrsToProgrammes(int $crs_ref_id, int $cat_ref_id) : void
    {
        ilObjStudyProgramme::addCrsToProgrammes($crs_ref_id, $cat_ref_id);
    }

    private static function removeCrsFromProgrammes(int $crs_ref_id, int $cat_ref_id) : void
    {
        ilObjStudyProgramme::removeCrsFromProgrammes($crs_ref_id, $cat_ref_id);
    }

    private static function addMemberToProgrammes(string $src_type, array $params) : void
    {
        $usr_id = $params['usr_id'];
        $id = $params['obj_id'];
        if (
            $src_type === ilStudyProgrammeAutoMembershipSource::TYPE_GROUP ||
            $src_type === ilStudyProgrammeAutoMembershipSource::TYPE_COURSE
        ) {
            $ref_ids = ilObject::_getAllReferences($id);
            $id = array_shift($ref_ids);
        }
        if ($src_type === ilStudyProgrammeAutoMembershipSource::TYPE_ROLE) {
            $id = $params['role_id'];
        }

        ilObjStudyProgramme::addMemberToProgrammes($src_type, $id, $usr_id);
    }

    private static function removeMemberFromProgrammes(string $src_type, array $params) : void
    {
        $usr_id = $params['usr_id'];
        $id = $params['obj_id'];
        if (
            $src_type === ilStudyProgrammeAutoMembershipSource::TYPE_GROUP ||
            $src_type === ilStudyProgrammeAutoMembershipSource::TYPE_COURSE
        ) {
            $ref_ids = ilObject::_getAllReferences($id);
            $id = array_shift($ref_ids);
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

    private static function sendInformToReAssignMail(array $params) : void
    {
        $usr_id = $params['usr_id'];
        $progress_id = $params['progress_id'];
        ilObjStudyProgramme::sendInformToReAssignMail($progress_id, $usr_id);
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
