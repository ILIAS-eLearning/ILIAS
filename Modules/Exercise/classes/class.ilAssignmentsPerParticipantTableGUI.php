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
 
/**
 * Exercise participant table
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilAssignmentsPerParticipantTableGUI extends ilExerciseSubmissionTableGUI
{
    protected ilObjUser $user;
    
    protected function initMode(int $a_item_id) : void
    {
        $lng = $this->lng;

        $this->mode = self::MODE_BY_USER;

        // global id for all exercises
        $this->setId("exc_part");

        if ($a_item_id > 0) {
            $name = ilObjUser::_lookupName($a_item_id);
            if (trim($name["login"]) !== '' && trim($name["login"]) !== '0') {
                $this->user = new ilObjUser($a_item_id);

                $this->setTitle(
                    $lng->txt("exc_participant") . ": " .
                        $name["lastname"] . ", " . $name["firstname"] . " [" . $name["login"] . "]"
                );
            }
        }

        $this->setSelectAllCheckbox("ass");
    }

    /**
     * @throws ilExcUnknownAssignmentTypeException
     */
    protected function parseData() : array
    {
        $ilAccess = $this->access;
        $ilCtrl = $this->ctrl;
        $this->addCommandButton("saveStatusParticipant", $this->lng->txt("save"));

        // #14650 - invalid user
        if (!$this->user) {
            $ilCtrl->setParameter($this->getParentObject(), "member_id", "");
            $ilCtrl->setParameter($this->getParentObject(), "part_id", ""); // #20073
            $ilCtrl->redirect($this->getParentObject(), $this->getParentCmd());
        }

        // #18327
        if (!$ilAccess->checkAccessOfUser($this->user->getId(), "read", "", $this->exc->getRefId()) &&
            is_array($info = $ilAccess->getInfo())) {
            $this->setDescription('<span class="warning">' . $info[0]['text'] . '</span>');
        }

        $data = array();
        foreach (ilExAssignment::getInstancesByExercise($this->exc->getId()) as $ass) {
            // ilExAssignment::getMemberListData()
            $member_status = $ass->getMemberStatus($this->user->getId());

            // filter
            if ($this->filter["status"] &&
                $member_status->getStatus() != $this->filter["status"]) {
                continue;
            }

            $submission = new ilExSubmission($ass, $this->user->getId());
            $idl = $ass->getIndividualDeadlines();

            if ($this->filter["subm"]) {
                if ($this->filter["subm"] == "y" &&
                    !$submission->getLastSubmission()) {
                    continue;
                } elseif ($this->filter["subm"] == "n" &&
                    $submission->getLastSubmission()) {
                    continue;
                }
            }

            $row = array(
                "ass" => $ass,
                "submission_obj" => $submission,
                "name" => $ass->getTitle(),
                "status" => $member_status->getStatus(),
                "mark" => $member_status->getMark(),
                "sent_time" => $member_status->getSentTime(),
                "status_time" => $member_status->getStatusTime(),
                "feedback_time" => $member_status->getFeedbackTime(),
                "submission" => $submission->getLastSubmission(),
                "notice" => $member_status->getNotice(),
                "comment" => $member_status->getComment(),
                "order_nr" => $ass->getOrderNr()
            );

            if ($ass->hasTeam()) {
                $team_map = ilExAssignmentTeam::getAssignmentTeamMap($ass->getId());

                $row["team"] = array();
                foreach ($submission->getTeam()->getMembers() as $user_id) {
                    $row["team"][$user_id] = ilObjUser::_lookupFullname($user_id);
                }
                asort($row["team"]);

                $team_id = $team_map[$this->user->getId()];
                if (is_numeric($team_id)) {
                    $idl_team_id = "t" . $team_id;
                    if (array_key_exists($idl_team_id, $idl)) {
                        $row["idl"] = $idl[$idl_team_id];
                    }
                }
            } else {
                if (array_key_exists($this->user->getId(), $idl)) {
                    $row["idl"] = $idl[$this->user->getId()];
                }
            }

            $data[] = $row;
        }

        return $data;
    }

    protected function parseModeColumns() : array
    {
        $cols = array();

        $cols["name"] = array($this->lng->txt("exc_assignment"), "order_nr");
        $cols["team_members"] = array($this->lng->txt("exc_tbl_team"));
        $cols["idl"] = array($this->lng->txt("exc_tbl_individual_deadline"), "idl");

        return $cols;
    }

    /**
     * @throws ilDatabaseException
     * @throws ilDateTimeException
     * @throws ilObjectNotFoundException
     */
    protected function fillRow(array $a_set) : void
    {
        $ilCtrl = $this->ctrl;
        $ilCtrl->setParameter($this->parent_obj, "member_id", $this->user->getId());
        $ilCtrl->setParameter($this->parent_obj, "ass_id", $a_set["ass"]->getId());

        // multi-select id
        $this->tpl->setVariable("NAME_ID", "sel_ass_ids");
        $this->tpl->setVariable("LISTED_NAME_ID", "listed_ass_ids");
        $this->tpl->setVariable("VAL_ID", $a_set["ass"]->getId());

        $this->parseRow($this->user->getId(), $a_set["ass"], $a_set);
            
        $ilCtrl->setParameter($this->parent_obj, "ass_id", "");
        $ilCtrl->setParameter($this->parent_obj, "member_id", $this->user->getId());
    }

    public function numericOrdering(string $a_field) : bool
    {
        return $a_field === "order_nr";
    }
}
