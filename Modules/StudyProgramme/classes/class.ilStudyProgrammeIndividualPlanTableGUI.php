<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("Services/Table/classes/class.ilTable2GUI.php");
require_once("Modules/StudyProgramme/classes/class.ilStudyProgrammeUserProgress.php");
require_once("Modules/StudyProgramme/classes/class.ilObjStudyProgramme.php");
require_once("Services/Utilities/classes/class.ilUtil.php");

/**
 * Class ilStudyProgrammeIndividualPlanTableGUI
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 *
 */

class ilStudyProgrammeIndividualPlanTableGUI extends ilTable2GUI
{
    const SEL_COLUMN_DEADLINE = "prg_deadline";

    protected $assignment;
    /**
     * @var ilStudyProgrammeUserProgressDB
     */
    protected $sp_user_progress_db;

    public function __construct(ilObjStudyProgrammeIndividualPlanGUI $a_parent_obj, ilStudyProgrammeUserAssignment $a_ass, \ilStudyProgrammeUserProgressDB $sp_user_progress_db)
    {
        $this->setId("manage_indiv");

        $this->sp_user_progress_db = $sp_user_progress_db;

        parent::__construct($a_parent_obj, 'manage');

        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $ilDB = $DIC['ilDB'];
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->db = $ilDB;

        $this->assignment = $a_ass;

        $this->setEnableTitle(true);
        $this->setTopCommands(false);
        $this->setEnableHeader(true);
        // TODO: switch this to internal sorting/segmentation
        $this->setExternalSorting(false);
        $this->setExternalSegmentation(false);
        $this->setRowTemplate("tpl.individual_plan_table_row.html", "Modules/StudyProgramme");
        $this->setDefaultOrderDirection("asc");

        $this->getParentObject()->appendIndividualPlanActions($this);

        $columns = array( "status"
                        , "title"
                        , "prg_points_current"
                        , "prg_points_required"
                        , "prg_manual_status"
                        , "prg_possible"
                        , "prg_changed_by"
                        , "prg_completion_by"
                        );

        foreach ($this->getSelectedColumns() as $column) {
            $columns[] = $column;
        }

        foreach ($columns as $lng_var) {
            $this->addColumn($lng->txt($lng_var));
        }

        $plan = $this->fetchData();

        $this->setMaxCount(count($plan));
        $this->setData($plan);

        $this->determineLimit();
        $this->determineOffsetAndOrder();

        $this->possible_image = "<img src='" . ilUtil::getImagePath("icon_ok.svg") . "' alt='ok'>";
        $this->not_possible_image = "<img src='" . ilUtil::getImagePath("icon_not_ok.svg") . "' alt='not ok'>";
    }

    protected function fillRow($a_set)
    {
        $status = $this->sp_user_progress_db->statusToRepr($a_set["status"]);
        $this->tpl->setVariable("STATUS", $status);

        $title = $a_set["title"];
        if ($a_set["program_status"] == ilStudyProgramme::STATUS_DRAFT) {
            $title .= " (" . $this->lng->txt("prg_status_draft") . ")";
        } elseif ($a_set["program_status"] == ilStudyProgramme::STATUS_OUTDATED) {
            $title .= " (" . $this->lng->txt("prg_status_outdated") . ")";
        }

        $this->tpl->setVariable("TITLE", $title);
        $this->tpl->setVariable("POINTS_CURRENT", $a_set["points_current"]);
        $this->tpl->setVariable("POINTS_REQUIRED", $this->getRequiredPointsInput($a_set["progress_id"], $a_set["status"], $a_set["points_required"]));
        $this->tpl->setVariable("MANUAL_STATUS", $this->getManualStatusSelect($a_set["progress_id"], $a_set["status"]));
        $this->tpl->setVariable("POSSIBLE", $a_set["possible"] ? $this->possible_image : $this->not_possible_image);
        $this->tpl->setVariable("CHANGED_BY", $a_set["changed_by"]);
        $this->tpl->setVariable("COMPLETION_BY", $a_set["completion_by"]);

        foreach ($this->getSelectedColumns() as $column) {
            switch ($column) {
                case self::SEL_COLUMN_DEADLINE:
                    $this->tpl->setCurrentBlock("deadline");
                    $this->tpl->setVariable("DEADLINE", $this->getDeadlineInput($a_set["progress_id"], $a_set["deadline"]));
                    $this->tpl->parseCurrentBlock("deadline");
                    break;
            }
        }
    }

    /**
     * Get selectable columns
     *
     * @return array[] 	$cols
     */
    public function getSelectableColumns()
    {
        $cols = array();

        $cols[self::SEL_COLUMN_DEADLINE] = array(
                "txt" => $this->lng->txt("prg_deadline"));

        return $cols;
    }

    protected function fetchData()
    {
        $prg = $this->assignment->getStudyProgramme();
        $prg_id = $prg->getId();
        $ass_id = $this->assignment->getId();
        $usr_id = $this->assignment->getUserId();
        $plan = array();

        $prg->applyToSubTreeNodes(function ($node) use ($prg_id, $ass_id, $usr_id, &$plan) {
            $progress = $this->sp_user_progress_db->getInstance($ass_id, $node->getId(), $usr_id);
            $completion_by_id = $progress->getCompletionBy();
            if ($completion_by_id) {
                $completion_by = ilObjUser::_lookupLogin($completion_by_id);
                if (!$completion_by) {
                    $type = ilObject::_lookupType($completion_by_id);
                    if ($type == "crsr") {
                        $completion_by = ilContainerReference::_lookupTitle($completion_by_id);
                    } else {
                        $completion_by = ilObject::_lookupTitle($completion_by_id);
                    }
                }
            } else {
                $completion_by = implode(", ", $progress->getNamesOfCompletedOrAccreditedChildren());
            }
            $plan[] = array( "status" => $progress->getStatus()
                           , "title" => $node->getTitle()
                           , "points_current" => $progress->getCurrentAmountOfPoints()
                           , "points_required" => $progress->getAmountOfPoints()
                           , "possible" => $progress->isSuccessful() || $progress->canBeCompleted() || !$progress->isRelevant()
                           , "changed_by" => ilObjUser::_lookupLogin($progress->getLastChangeBy())
                           , "completion_by" => $completion_by
                           , "progress_id" => $progress->getId()
                           , "program_status" => $progress->getStudyProgramme()->getStatus()
                           , "deadline" =>$progress->getDeadline()
                           );
        });
        return $plan;
    }

    protected function getManualStatusSelect($a_progress_id, $a_status)
    {
        if ($a_status == ilStudyProgrammeProgress::STATUS_COMPLETED) {
            return "";
        }

        $parent = $this->getParentObject();
        $status_title = $parent->getManualStatusPostVarTitle();
        $manual_status_none = $parent->getManualStatusNone();
        $manual_status_not_relevant = $parent->getManualStatusNotRelevant();
        $manual_status_accredited = $parent->getManualStatusAccredited();

        require_once("Services/Form/classes/class.ilSelectInputGUI.php");
        $select = new ilSelectInputGUI("", $status_title . "[$a_progress_id]");
        $select->setOptions(array( $manual_status_none => "-"
            , $manual_status_accredited => $this->lng->txt("prg_status_accredited")
            , $manual_status_not_relevant => $this->lng->txt("prg_status_not_relevant")
            ));
        if ($a_status == ilStudyProgrammeProgress::STATUS_NOT_RELEVANT) {
            $select->setValue($manual_status_not_relevant);
        } elseif ($a_status == ilStudyProgrammeProgress::STATUS_ACCREDITED) {
            $select->setValue($manual_status_accredited);
        }

        return $select->render();
    }

    protected function getRequiredPointsInput($a_progress_id, $a_status, $a_points_required)
    {
        if ($a_status != ilStudyProgrammeProgress::STATUS_IN_PROGRESS) {
            return $a_points_required;
        }

        $required_points_title = $this->getParentObject()->getRequiredPointsPostVarTitle();

        require_once("Services/Form/classes/class.ilNumberInputGUI.php");
        $input = new ilNumberInputGUI("", $required_points_title . "[$a_progress_id]");
        $input->setValue($a_points_required);
        $input->setSize(5);
        return $input->render();
    }

    protected function getDeadlineInput($a_progress_id, $deadline)
    {
        require_once("Services/Form/classes/class.ilDateTimeInputGUI.php");

        $deadline_title = $this->getParentObject()->getDeadlinePostVarTitle();
        $gui = new ilDateTimeInputGUI("", $deadline_title . "[$a_progress_id]");
        $gui->setDate($deadline);

        return $gui->render();
    }
}
