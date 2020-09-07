<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Modules/Exercise/classes/class.ilExerciseSubmissionTableGUI.php");

/**
* Exercise participant table
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesExercise
*/
class ilExParticipantTableGUI extends ilExerciseSubmissionTableGUI
{
    protected $user; // [ilObjUser]
    
    protected function initMode($a_item_id)
    {
        $lng = $this->lng;
        
        $this->mode = self::MODE_BY_USER;
        
        // global id for all exercises
        $this->setId("exc_part");
        
        if ($a_item_id > 0) {
            $name = ilObjUser::_lookupName($a_item_id);
            if (trim($name["login"])) {
                $this->user = new ilObjUser($a_item_id);
                                
                $this->setTitle($lng->txt("exc_participant") . ": " .
                    $name["lastname"] . ", " . $name["firstname"] . " [" . $name["login"] . "]");
            }
        }

        $this->setSelectAllCheckbox("ass");
    }
    
    protected function parseData()
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
        /** @var ilExAssignment $ass */
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
    
    protected function parseModeColumns()
    {
        $cols = array();
                
        $cols["name"] = array($this->lng->txt("exc_assignment"), "order_nr");
        $cols["team_members"] = array($this->lng->txt("exc_tbl_team"));
        $cols["idl"] = array($this->lng->txt("exc_tbl_individual_deadline"), "idl");
        
        return $cols;
    }
    
    protected function fillRow($a_item)
    {
        $ilCtrl = $this->ctrl;
        $ilCtrl->setParameter($this->parent_obj, "member_id", $this->user->getId());
        $ilCtrl->setParameter($this->parent_obj, "ass_id", $a_item["ass"]->getId());
                
        // multi-select id
        $this->tpl->setVariable("NAME_ID", "ass");
        $this->tpl->setVariable("VAL_ID", $a_item["ass"]->getId());
        
        $this->parseRow($this->user->getId(), $a_item["ass"], $a_item);
            
        $ilCtrl->setParameter($this->parent_obj, "ass_id", "");
        $ilCtrl->setParameter($this->parent_obj, "member_id", $this->user->getId());
    }

    /**
     * @inheritdoc
     */
    public function numericOrdering($a_field)
    {
        if (in_array($a_field, ["order_nr"])) {
            return true;
        }
        return false;
    }
}
