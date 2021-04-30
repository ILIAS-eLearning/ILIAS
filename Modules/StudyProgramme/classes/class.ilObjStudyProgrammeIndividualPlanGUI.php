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
     * @var ilObjStudyProgramme
     */
    public $object;

    /**
     * @var ilLng
     */
    public $lng;


    /**
     * @var ilObjUser
     */
    public $user;

    protected $parent_gui;

    /**
     * @var ilStudyProgrammeUserProgressDB
     */
    protected $sp_user_progress_db;

    public function __construct(
        \ilGlobalTemplateInterface $tpl,
        \ilCtrl $ilCtrl,
        \ilLanguage $lng,
        \ilObjUser $ilUser,
        \ilAccess $ilAccess,
        ilStudyProgrammeUserProgressDB $sp_user_progress_db,
        ilStudyProgrammeUserAssignmentDB $sp_user_assignment_db
    ) {
        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->ilAccess = $ilAccess;
        $this->user = $ilUser;

        $this->assignment_object = null;

        $this->sp_user_progress_db = $sp_user_progress_db;
        $this->sp_user_assignment_db = $sp_user_assignment_db;

        $this->object = null;

        $lng->loadLanguageModule("prg");

        $this->tpl->addCss("Modules/StudyProgramme/templates/css/ilStudyProgramme.css");
    }

    public function setParentGUI($a_parent_gui)
    {
        $this->parent_gui = $a_parent_gui;
    }

    public function setRefId($a_ref_id)
    {
        $this->ref_id = $a_ref_id;
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
        if (!is_numeric($_GET["ass_id"])) {
            throw new ilException("Expected integer 'ass_id'");
        }
        return (int) $_GET["ass_id"];
    }

    protected function getAssignmentObject()
    {
        if ($this->assignment_object === null) {
            $id = $this->getAssignmentId();
            $this->assignment_object = $this->sp_user_assignment_db->getInstanceById((int) $id);
        }
        return $this->assignment_object;
    }

    protected function view()
    {
        require_once("Modules/StudyProgramme/classes/class.ilStudyProgrammeIndividualPlanProgressListGUI.php");
        $progress = $this->getAssignmentObject()->getRootProgress();
        if (
            $this->parent_gui->getStudyProgramme()->getAccessControlByOrguPositionsGlobal()
            && !in_array($progress->getUserId(), $this->parent_gui->viewIndividualPlan())
        ) {
            throw new ilStudyProgrammePositionBasedAccessViolationException(
                "may not access individua plan of user"
            );
        }
        $gui = new ilStudyProgrammeIndividualPlanProgressListGUI($progress);
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
        if (
            $this->parent_gui->getStudyProgramme()->getAccessControlByOrguPositionsGlobal()
            && !in_array($ass->getUserId(), $this->parent_gui->editIndividualPlan())
        ) {
            throw new ilStudyProgrammePositionBasedAccessViolationException(
                "may not access individua plan of user"
            );
        }
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
        if (
            $this->parent_gui->getStudyProgramme()->getAccessControlByOrguPositionsGlobal()
            && !in_array($ass->getUserId(), $this->parent_gui->editIndividualPlan())
        ) {
            throw new ilStudyProgrammePositionBasedAccessViolationException(
                "may not access individual plan of user"
            );
        }
        $ass->updateFromProgram();
        $ass->updateValidityFromProgram();
        $ass->updateDeadlineFromProgram();

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
            if (
                $this->parent_gui->getStudyProgramme()->getAccessControlByOrguPositionsGlobal()
                && !in_array($prgrs->getUserId(), $this->parent_gui->editIndividualPlan())
            ) {
                continue;
            }
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

                if ($deadline !== null && $deadline->format('Y-m-d') < date("Y-m-d")) {
                    $prgrs->markFailed($this->user->getId());
                }
            } elseif ($cur_status == ilStudyProgrammeProgress::STATUS_FAILED) {
                if ($deadline === null || $deadline->format('Y-m-d') > date("Y-m-d")) {
                    $prgrs->markNotFailed((int) $this->user->getId());
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
     * @return DateTime
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
     * @return DateTime
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
        return DateTime::createFromFormat('d.m.Y', $deadline);
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
        $ref_id = $ass->getStudyProgramme()->getRefId();
        $user_id = $ass->getUserId();
        $tpl->setVariable("USERNAME", ilObjUser::_lookupFullname($user_id));
        $tabs = [];
        if ($this->ilAccess->checkAccess("manage_members", "", $ref_id)) {
            $tabs[] = 'view';
            $tabs[] = 'manage';
        }

        if ($this->parent_gui->getStudyProgramme()->getAccessControlByOrguPositionsGlobal()) {
            if (in_array($user_id, $this->parent_gui->viewIndividualPlan())) {
                $tabs[] = 'view';
            }
            if (in_array($user_id, $this->parent_gui->editIndividualPlan())) {
                $tabs[] = 'manage';
            }
        }

        $tabs = array_unique($tabs);
        foreach ($tabs as $_tab) {
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

    public function getLinkTargetView($a_ass_id)
    {
        $cl = "ilObjStudyProgrammeIndividualPlanGUI";
        $this->ctrl->setParameterByClass($cl, "ass_id", $a_ass_id);
        $link = $this->ctrl->getLinkTargetByClass($cl, "view");
        $this->ctrl->setParameterByClass($cl, "ass_id", null);
        return $link;
    }
}
