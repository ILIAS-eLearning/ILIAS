<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */


/**
 * Class ilObjStudyProgrammeIndividualPlanGUI
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 */

class ilObjStudyProgrammeIndividualPlanGUI
{
    /**
     * @var ilCtrl
     */
    public $ctrl;

    /**
     * @var ilTemplate
     */
    public $tpl;

    /**
     * @var ilAccessHandler
     */
    protected $ilAccess;

    /**
     * @var ilObjStudyProgramme
     */
    public $object;

    /**
     * @var ilLog
     */
    protected $ilLog;

    /**
     * @var Ilias
     */
    public $ilias;

    /**
     * @var ilLng
     */
    public $lng;

    /**
     * @var ilToolbarGUI
     */
    public $toolbar;

    /**
     * @var ilObjUser
     */
    public $user;

    protected $parent_gui;

    /**
     * @var ilStudyProgrammeUserProgressDB
     */
    protected $sp_user_progress_db;

    public function __construct($a_parent_gui, $a_ref_id, ilStudyProgrammeUserProgressDB $sp_user_progress_db)
    {
        global $DIC;
        $tpl = $DIC['tpl'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilAccess = $DIC['ilAccess'];
        $ilToolbar = $DIC['ilToolbar'];
        $ilLocator = $DIC['ilLocator'];
        $tree = $DIC['tree'];
        $lng = $DIC['lng'];
        $ilLog = $DIC['ilLog'];
        $ilias = $DIC['ilias'];
        $ilUser = $DIC['ilUser'];

        $this->ref_id = $a_ref_id;
        $this->parent_gui = $a_parent_gui;
        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->ilAccess = $ilAccess;
        $this->ilLocator = $ilLocator;
        $this->tree = $tree;
        $this->toolbar = $ilToolbar;
        $this->ilLog = $ilLog;
        $this->ilias = $ilias;
        $this->lng = $lng;
        $this->user = $ilUser;
        $this->assignment_object = null;
        $this->sp_user_progress_db = $sp_user_progress_db;

        $this->object = null;

        $lng->loadLanguageModule("prg");

        $this->tpl->addCss("Modules/StudyProgramme/templates/css/ilStudyProgramme.css");
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();

        if ($cmd == "") {
            $cmd = "view";
        }

        switch ($cmd) {
            case "view":
            case "manage":
            case "updateFromCurrentPlan":
            case "updateFromInput":
                $cont = $this->$cmd();
                break;
            default:
                throw new ilException("ilObjStudyProgrammeMembersGUI: " .
                                      "Command not supported: $cmd");
        }

        $this->tpl->setContent($cont);
    }

    protected function getAssignmentId()
    {
        require_once("Modules/StudyProgramme/classes/class.ilStudyProgrammeUserAssignment.php");
        if (!is_numeric($_GET["ass_id"])) {
            throw new ilException("Expected integer 'ass_id'");
        }
        return (int) $_GET["ass_id"];
    }

    protected function getAssignmentObject()
    {
        if ($this->assignment_object === null) {
            $id = $this->getAssignmentId();
            $this->assignment_object = ilStudyProgrammeUserAssignment::getInstance($id);
        }
        return $this->assignment_object;
    }

    protected function view()
    {
        require_once("Modules/StudyProgramme/classes/class.ilStudyProgrammeIndividualPlanProgressListGUI.php");
        $gui = new ilStudyProgrammeIndividualPlanProgressListGUI($this->getAssignmentObject()->getRootProgress());
        $gui->setOnlyRelevant(true);
        // Wrap a frame around the original gui element to correct rendering.
        $tpl = new ilTemplate("tpl.individual_plan_tree_frame.html", false, false, "Modules/StudyProgramme");
        $tpl->setVariable("CONTENT", $gui->getHTML());
        return $this->buildFrame("view", $tpl->get());
    }

    protected function manage()
    {
        require_once("Modules/StudyProgramme/classes/class.ilStudyProgrammeIndividualPlanTableGUI.php");
        $ass = $this->getAssignmentObject();
        $this->ctrl->setParameter($this, "ass_id", $ass->getId());
        $this->ctrl->setParameter($this, "cmd", "manage");
        $table = new ilStudyProgrammeIndividualPlanTableGUI($this, $ass, $this->sp_user_progress_db);
        $frame = $this->buildFrame("manage", $table->getHTML());
        $this->ctrl->setParameter($this, "ass_id", null);
        return $frame;
    }

    protected function updateFromCurrentPlan()
    {
        $ass = $this->getAssignmentObject();
        $ass->updateFromProgram();
        $this->ctrl->setParameter($this, "ass_id", $ass->getId());
        $this->showSuccessMessage("update_from_plan_successful");
        $this->ctrl->redirect($this, "manage");
    }

    protected function updateFromInput()
    {
        require_once("Modules/StudyProgramme/classes/class.ilStudyProgrammeUserProgress.php");

        $changed = false;

        $changed = $this->updateStatus();

        $this->ctrl->setParameter($this, "ass_id", $this->getAssignmentId());
        if ($changed) {
            $this->showSuccessMessage("update_successful");
        }
        $this->ctrl->redirect($this, "manage");
    }

    protected function updateStatus()
    {
        $status_updates = $this->getManualStatusUpdates();
        $changed = false;
        foreach ($status_updates as $prgrs_id => $status) {
            $prgrs = $this->sp_user_progress_db->getInstanceById($prgrs_id);
            $cur_status = $prgrs->getStatus();

            if ($status == self::MANUAL_STATUS_NONE && $cur_status == ilStudyProgrammeProgress::STATUS_ACCREDITED) {
                $prgrs->unmarkAccredited($this->user->getId());
                $changed = true;
            } elseif ($status == self::MANUAL_STATUS_NONE && $cur_status == ilStudyProgrammeProgress::STATUS_NOT_RELEVANT) {
                $prgrs->markRelevant($this->user->getId());
                $changed = true;
            } elseif ($status == self::MANUAL_STATUS_NOT_RELEVANT && $cur_status != ilStudyProgrammeProgress::STATUS_NOT_RELEVANT) {
                $prgrs->markNotRelevant($this->user->getId());
                $changed = true;
            } elseif ($status == self::MANUAL_STATUS_ACCREDITED && $cur_status != ilStudyProgrammeProgress::STATUS_ACCREDITED) {
                $prgrs->markAccredited($this->user->getId());
                $changed = true;
            }

            $deadline = null;
            if ($this->postContainDeadline()) {
                $deadline = $this->updateDeadline($prgrs);
            }

            if ($cur_status == ilStudyProgrammeProgress::STATUS_IN_PROGRESS) {
                $changed = $this->updateRequiredPoints($prgrs_id) || $changed;

                if ($deadline !== null && $deadline->get(IL_CAL_DATE) < date("Y-m-d")) {
                    $prgrs->markFailed($this->user->getId());
                }
            } elseif ($cur_status == ilStudyProgrammeProgress::STATUS_FAILED) {
                if ($deadline === null || $deadline->get(IL_CAL_DATE) > date("Y-m-d")) {
                    $prgrs->markNotFailed($this->user->getId());
                }
            }
        }
        return $changed;
    }

    /**
     * Updates current deadline
     *
     * @param ilStudyProgrammeUserProgress 	$prgrs
     *
     * @return ilDateTime
     */
    protected function updateDeadline(ilStudyProgrammeUserProgress $prgrs)
    {
        $deadline = $this->getDeadlineFromForm($prgrs->getId());
        $prgrs->setDeadline($deadline);
        $prgrs->updateProgress($this->user->getId());

        return $deadline;
    }

    protected function updateRequiredPoints($prgrs_id)
    {
        $required_points = $this->getRequiredPointsUpdates($prgrs_id);
        $changed = false;

        $prgrs = $this->sp_user_progress_db->getInstanceById($prgrs_id);
        $cur_status = $prgrs->getStatus();
        if ($cur_status != ilStudyProgrammeProgress::STATUS_IN_PROGRESS) {
            return false;
        }

        if ($required_points < 0) {
            $required_points = 0;
        }

        if ($required_points == $prgrs->getAmountOfPoints()) {
            return false;
        }

        $prgrs->setRequiredAmountOfPoints($required_points, $this->user->getId());
        return true;
    }

    /**
     * Get the deadline from form
     *
     * @param int 	$prgrs_id
     *
     * @return ilDateTime
     */
    protected function getDeadlineFromForm($prgrs_id)
    {
        $post_var = $this->getDeadlinePostVarTitle();
        if (!$this->postContainDeadline()) {
            throw new ilException("Expected array $post_var in POST");
        }

        $post_value = $_POST[$post_var];
        $deadline = $post_value[$prgrs_id];

        if ($deadline == "") {
            return null;
        }

        return new ilDateTime($deadline, IL_CAL_DATE);
    }

    /**
     * Checks whether $_POST contains deadline
     *
     * @return bool
     */
    protected function postContainDeadline()
    {
        $post_var = $this->getDeadlinePostVarTitle();
        if (array_key_exists($post_var, $_POST)) {
            return true;
        }
        return false;
    }

    protected function showSuccessMessage($a_lng_var)
    {
        require_once("Services/Utilities/classes/class.ilUtil.php");
        ilUtil::sendSuccess($this->lng->txt("prg_$a_lng_var"), true);
    }

    protected function getManualStatusUpdates()
    {
        $post_var = $this->getManualStatusPostVarTitle();
        if (!array_key_exists($post_var, $_POST)) {
            throw new ilException("Expected array $post_var in POST");
        }
        return $_POST[$post_var];
    }

    protected function getRequiredPointsUpdates($prgrs_id)
    {
        $post_var = $this->getRequiredPointsPostVarTitle();
        if (!array_key_exists($post_var, $_POST)) {
            throw new ilException("Expected array $post_var in POST");
        }

        $post_value = $_POST[$post_var];
        return (int) $post_value[$prgrs_id];
    }


    protected function buildFrame($tab, $content)
    {
        $tpl = new ilTemplate("tpl.indivdual_plan_frame.html", true, true, "Modules/StudyProgramme");
        $ass = $this->getAssignmentObject();

        $tpl->setVariable("USERNAME", ilObjUser::_lookupFullname($ass->getUserId()));
        foreach (array("view", "manage") as $_tab) {
            $tpl->setCurrentBlock("sub_tab");
            $tpl->setVariable("CLASS", $_tab == $tab ? "active" : "");
            $tpl->setVariable("LINK", $this->getLinkTargetForSubTab($_tab, $ass->getId()));
            $tpl->setVariable("TITLE", $this->lng->txt("prg_$_tab"));
            $tpl->parseCurrentBlock();
        }
        $tpl->setVariable("CONTENT", $content);

        return $tpl->get();
    }

    protected function getLinkTargetForSubTab($a_tab, $a_ass_id)
    {
        $this->ctrl->setParameter($this, "ass_id", $a_ass_id);
        $lnk = $this->ctrl->getLinkTarget($this, $a_tab);
        $this->ctrl->setParameter($this, "ass_id", null);
        return $lnk;
    }

    public function appendIndividualPlanActions(ilTable2GUI $a_table)
    {
        $a_table->setFormAction($this->ctrl->getFormAction($this));
        $a_table->addCommandButton("updateFromCurrentPlan", $this->lng->txt("prg_update_from_current_plan"));
        $a_table->addCommandButton("updateFromInput", $this->lng->txt("save"));
    }

    const POST_VAR_STATUS = "status";
    const POST_VAR_REQUIRED_POINTS = "required_points";
    const POST_VAR_DEADLINE = "deadline";
    const MANUAL_STATUS_NONE = 0;
    const MANUAL_STATUS_NOT_RELEVANT = 1;
    const MANUAL_STATUS_ACCREDITED = 2;

    public function getManualStatusPostVarTitle()
    {
        return self::POST_VAR_STATUS;
    }

    public function getRequiredPointsPostVarTitle()
    {
        return self::POST_VAR_REQUIRED_POINTS;
    }

    public function getDeadlinePostVarTitle()
    {
        return self::POST_VAR_DEADLINE;
    }

    public function getManualStatusNone()
    {
        return self::MANUAL_STATUS_NONE;
    }

    public function getManualStatusNotRelevant()
    {
        return self::MANUAL_STATUS_NOT_RELEVANT;
    }

    public function getManualStatusAccredited()
    {
        return self::MANUAL_STATUS_ACCREDITED;
    }

    public static function getLinkTargetView($ctrl, $a_ass_id)
    {
        $cl = "ilObjStudyProgrammeIndividualPlanGUI";
        $ctrl->setParameterByClass($cl, "ass_id", $a_ass_id);
        $link = $ctrl->getLinkTargetByClass($cl, "view");
        $ctrl->setParameterByClass($cl, "ass_id", null);
        return $link;
    }
}
