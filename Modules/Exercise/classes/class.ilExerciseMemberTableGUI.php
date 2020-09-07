<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Modules/Exercise/classes/class.ilExerciseSubmissionTableGUI.php");


/**
 * Exercise member table
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ModulesExercise
 */
class ilExerciseMemberTableGUI extends ilExerciseSubmissionTableGUI
{
    /**
     * @var ilExAssignment
     */
    protected $ass;

    protected $teams = array();

    public function __construct($a_parent_obj, $a_parent_cmd, ilObjExercise $a_exc, $a_item_id)
    {
        /** @var ilCtrl $ilCtrl */
        global $ilCtrl;
        //var_dump($ilCtrl->getCmd()); exit;
        parent::__construct($a_parent_obj, $a_parent_cmd, $a_exc, $a_item_id);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj, "saveStatusAll"));
    }

    protected function initMode($a_item_id)
    {
        $lng = $this->lng;
        
        $this->mode = self::MODE_BY_ASSIGNMENT;
        
        // global id for all exercises
        $this->setId("exc_mem");
        
        include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
        $this->ass = new ilExAssignment($a_item_id);
        
        $this->setTitle($lng->txt("exc_assignment") . ": " . $this->ass->getTitle());
        $this->setSelectAllCheckbox("member");
    }
    
    protected function parseData()
    {
        $this->addCommandButton("saveStatusAll", $this->lng->txt("exc_save_all"));
        
        $tmp_data = $this->ass->getMemberListData();
        
        // filter user access
        $usr_ids = array_keys($tmp_data);
        $filtered_usr_ids = $GLOBALS['DIC']->access()->filterUserIdsByRbacOrPositionOfCurrentUser(
            'etit_submissions_grades',
            'edit_submissions_grades',
            $this->exc->getRefId(),
            $usr_ids
        );
        $data = [];
        foreach ($filtered_usr_ids as $usr_id) {
            $data[$usr_id] = $tmp_data[$usr_id];
        }
        
        
        $idl = $this->ass->getIndividualDeadlines();
        $calc_deadline = $this->ass->getCalculatedDeadlines();
        
        // team upload?  (1 row == 1 team)
        if ($this->ass->hasTeam()) {
            $teams = ilExAssignmentTeam::getInstancesFromMap($this->ass->getId());
            $team_map = ilExAssignmentTeam::getAssignmentTeamMap($this->ass->getId());
            
            $tmp = array();
            
            foreach ($data as $item) {
                // filter
                if ($this->filter["status"] &&
                    $item["status"] != $this->filter["status"]) {
                    continue;
                }
                
                $team_id = $team_map[$item["usr_id"]];
                                
                if (!$team_id) {
                    // #11957
                    $team_id = "nty" . $item["usr_id"];
                }
                
                if (!isset($tmp[$team_id])) {
                    $tmp[$team_id] = $item;
                    
                    if (is_numeric($team_id)) {
                        $tmp[$team_id]["submission_obj"] = new ilExSubmission($this->ass, $item["usr_id"], $teams[$team_id]);
                    } else {
                        // ilExSubmission should not try to auto-load
                        $tmp[$team_id]["submission_obj"] = new ilExSubmission($this->ass, $item["usr_id"], new ilExAssignmentTeam());
                    }
                }
                                
                $tmp[$team_id]["team"][$item["usr_id"]] = $item["name"];
                
                if (is_numeric($team_id)) {
                    $idl_team_id = "t" . $team_id;
                    if (array_key_exists($idl_team_id, $idl)) {
                        $tmp[$team_id]["idl"] = $idl[$idl_team_id];
                    }

                    if (isset($calc_deadline["team"][$team_id])) {
                        $tmp[$team_id]["calc_deadline"] = $calc_deadline["team"][$team_id]["calculated_deadline"];
                    }
                } else {
                    if (isset($calc_deadline["user"][$item["usr_id"]])) {
                        $tmp["nty" . $item["usr_id"]]["calc_deadline"] = $calc_deadline["user"][$item["usr_id"]]["calculated_deadline"];
                    }
                }
            }
            
            // filter (team-wide)
            if ($this->filter["name"]) {
                foreach ($tmp as $idx => $item) {
                    if (!stristr(implode("", $item["team"]), $this->filter["name"])) {
                        unset($tmp[$idx]);
                    }
                }
            }
            if ($this->filter["subm"]) {
                foreach ($tmp as $idx => $item) {
                    $submission = $item["submission_obj"];
                    if ($this->filter["subm"] == "y" &&
                        !$submission->getLastSubmission()) {
                        unset($tmp[$idx]);
                    } elseif ($this->filter["subm"] == "n" &&
                        $submission->getLastSubmission()) {
                        unset($tmp[$idx]);
                    }
                }
            }
            
            $data = $tmp;
            unset($tmp);
        } else {
            foreach ($data as $idx => $item) {
                // filter
                if ($this->filter["status"] &&
                    $item["status"] != $this->filter["status"]) {
                    unset($data[$idx]);
                    continue;
                }
                if ($this->filter["name"] &&
                    !stristr($item["name"], $this->filter["name"]) &&
                    !stristr($item["login"], $this->filter["name"])) {
                    unset($data[$idx]);
                    continue;
                }
                
                $data[$idx]["submission_obj"] = new ilExSubmission($this->ass, $item["usr_id"]);
                
                // filter
                if ($this->filter["subm"]) {
                    $submission = $data[$idx]["submission_obj"];
                    if ($this->filter["subm"] == "y" &&
                        !$submission->getLastSubmission()) {
                        unset($data[$idx]);
                        continue;
                    } elseif ($this->filter["subm"] == "n" &&
                        $submission->getLastSubmission()) {
                        unset($data[$idx]);
                        continue;
                    }
                }
                
                if (array_key_exists($item["usr_id"], $idl)) {
                    $data[$idx]["idl"] = $idl[$item["usr_id"]];
                }

                if (isset($calc_deadline["user"][$item["usr_id"]])) {
                    $data[$idx]["calc_deadline"] = $calc_deadline["user"][$item["usr_id"]]["calculated_deadline"];
                }
            }
        }
        
        return $data;
    }
    
    protected function getModeColumns()
    {
        $cols = array();
                        
        if (!$this->ass->hasTeam()) {
            $selected = $this->getSelectedColumns();
            
            if (in_array("image", $selected)) {
                $cols["image"] = array($this->lng->txt("image"));
            }
            
            $cols["name"] = array($this->lng->txt("name"), "name");
            
            if (in_array("login", $selected)) {
                $cols["login"] = array($this->lng->txt("login"), "login");
            }
        } else {
            $cols["name"] = array($this->lng->txt("exc_team"));
        }
        
        return $cols;
    }
    
    protected function parseModeColumns()
    {
        $cols = array();

        if (!$this->ass->hasTeam()) {
            $cols["image"] = array($this->lng->txt("image"));
            $cols["name"] = array($this->lng->txt("name"), "name");
            $cols["login"] = array($this->lng->txt("login"), "login");
        } else {
            $cols["name"] = array($this->lng->txt("exc_tbl_team"));
        }
                
        if ($this->ass->hasActiveIDl()) {
            $cols["idl"] = array($this->lng->txt("exc_tbl_individual_deadline"), "idl");
        }

        if ($this->ass->getDeadlineMode() == ilExAssignment::DEADLINE_RELATIVE && $this->ass->getRelativeDeadline()) {
            $cols["calc_deadline"] = array($this->lng->txt("exc_tbl_calculated_deadline"), "calc_deadline");
        }

        return $cols;
    }
    
    protected function fillRow($member)
    {
        $ilCtrl = $this->ctrl;

        $member_id = $member["usr_id"];
        
        $ilCtrl->setParameter($this->parent_obj, "ass_id", $this->ass->getId());
        $ilCtrl->setParameter($this->parent_obj, "member_id", $member_id);
                        
        // multi-select id
        $this->tpl->setVariable("NAME_ID", "member");
        $this->tpl->setVariable("VAL_ID", $member_id);

        $this->parseRow($member_id, $this->ass, $member);
                        
        $ilCtrl->setParameter($this->parent_obj, "ass_id", $this->ass->getId()); // #17140
        $ilCtrl->setParameter($this->parent_obj, "member_id", "");
    }
}
