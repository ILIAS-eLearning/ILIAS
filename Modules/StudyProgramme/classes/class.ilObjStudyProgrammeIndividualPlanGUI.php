<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */


/**
 * Class ilObjStudyProgrammeIndividualPlanGUI
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 */

class ilObjStudyProgrammeIndividualPlanGUI
{
    const POST_VAR_STATUS = "status";
    const POST_VAR_REQUIRED_POINTS = "required_points";
    const POST_VAR_DEADLINE = "deadline";
    const MANUAL_STATUS_NONE = -1;

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
     * @var ilStudyProgrammeProgressDB
     */
    protected $progress_repository;

    /**
     * @var ilStudyProgrammeAssignmentRepository
     */
    protected $assignment_repository;

    /**
     * @var ilPRGMessages[]
     */
    protected $messages;

    /**
     * @var ilPRGPermissionsHelper
     */
    protected $permissions;

    public function __construct(
        \ilGlobalTemplateInterface $tpl,
        \ilCtrl $ilCtrl,
        \ilLanguage $lng,
        \ilObjUser $ilUser,
        ilStudyProgrammeProgressRepository $progress_repository,
        ilStudyProgrammeAssignmentRepository $assignment_repository,
        ilPRGMessagePrinter $messages
    ) {
        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->user = $ilUser;

        $this->assignment_object = null;

        $this->progress_repository = $progress_repository;
        $this->assignment_repository = $assignment_repository;
        $this->messages = $messages;

        $this->object = null;
        $this->permissions = null;

        $lng->loadLanguageModule("prg");

        $this->tpl->addCss("Modules/StudyProgramme/templates/css/ilStudyProgramme.css");
    }

    public function setParentGUI($a_parent_gui)
    {
        $this->parent_gui = $a_parent_gui;
    }

    public function setRefId(int $ref_id) : void
    {
        $this->ref_id = $a_ref_id;
        $this->object = \ilObjStudyProgramme::getInstanceByRefId($ref_id);
        $this->permissions = ilStudyProgrammeDIC::specificDicFor($this->object)['permissionhelper'];
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
            $this->assignment_object = $this->assignment_repository->getInstanceById((int) $id);
        }
        return $this->assignment_object;
    }

    protected function view()
    {
        $ass = $this->getAssignmentObject();

        if (!in_array(
            $ass->getUserId(),
            $this->permissions->getUserIdsSusceptibleTo(ilOrgUnitOperation::OP_VIEW_INDIVIDUAL_PLAN)
        )) {
            throw new ilStudyProgrammePositionBasedAccessViolationException(
                "may not access individual plan of user"
            );
        }
        $prg = ilObjStudyProgramme::getInstanceByObjId($ass->getRootId());
        $progress = $prg->getProgressForAssignment($ass->getId());
        
        $gui = new ilStudyProgrammeIndividualPlanProgressListGUI($progress);
        $gui->setOnlyRelevant(true);
        // Wrap a frame around the original gui element to correct rendering.
        $tpl = new ilTemplate("tpl.individual_plan_tree_frame.html", false, false, "Modules/StudyProgramme");
        $tpl->setVariable("CONTENT", $gui->getHTML());
        return $this->buildFrame("view", $tpl->get());
    }


    protected function manage()
    {
        $ass = $this->getAssignmentObject();

        if (!in_array(
            $ass->getUserId(),
            $this->permissions->getUserIdsSusceptibleTo(ilOrgUnitOperation::OP_EDIT_INDIVIDUAL_PLAN)
        )) {
            throw new ilStudyProgrammePositionBasedAccessViolationException(
                "may not access individual plan of user"
            );
        }


        $this->ctrl->setParameter($this, "ass_id", $ass->getId());
        $this->ctrl->setParameter($this, "cmd", "manage");
        $table = new ilStudyProgrammeIndividualPlanTableGUI($this, $ass, $this->progress_repository);
        $frame = $this->buildFrame("manage", $table->getHTML());
        $this->ctrl->setParameter($this, "ass_id", null);
        return $frame;
    }

    protected function updateFromCurrentPlan()
    {
        $ass = $this->getAssignmentObject();

        if (!in_array(
            $ass->getUserId(),
            $this->permissions->getUserIdsSusceptibleTo(ilOrgUnitOperation::OP_EDIT_INDIVIDUAL_PLAN)
        )) {
            throw new ilStudyProgrammePositionBasedAccessViolationException(
                "may not access individual plan of user"
            );
        }

        $prg = $this->parent_gui->getStudyProgramme();
        $progress = $prg->getProgressForAssignment($ass->getId());
        $msgs = $this->messages->getMessageCollection('msg_update_individual_plan');
        $this->object->updatePlanFromRepository(
            $progress->getId(),
            $this->user->getId(),
            $msgs
        );

        $this->ctrl->setParameter($this, "ass_id", $ass->getId());
        $this->showSuccessMessage("update_from_plan_successful");
        $this->ctrl->redirect($this, "manage");
    }

    protected function digestInput(array $post) : array
    {
        $params = [
            self::POST_VAR_STATUS,
            self::POST_VAR_DEADLINE,
            self::POST_VAR_REQUIRED_POINTS
        ];

        $ret = [];
        foreach ($params as $postvar) {
            $ret[$postvar] = [];
            if (array_key_exists($postvar, $post)) {
                $ret[$postvar] = $post[$postvar];
                krsort($ret[$postvar], SORT_NUMERIC);
            }
        }
        return $ret;
    }
    
    protected function updateFromInput()
    {
        $values = $this->digestInput($_POST);
        $msgs = $this->messages->getMessageCollection('msg_update_individual_plan');
        $this->updateStatus($values[self::POST_VAR_STATUS], $msgs);
        $this->updateDeadlines($values[self::POST_VAR_DEADLINE], $msgs);
        $this->updateRequiredPoints($values[self::POST_VAR_REQUIRED_POINTS], $msgs);

        if ($msgs->hasAnyMessages()) {
            $this->messages->showMessages($msgs);
        }

        $this->ctrl->setParameter($this, "ass_id", $this->getAssignmentId());
        $this->ctrl->redirect($this, "manage");
    }

    protected function updateStatus(array $progress_updates, ilPRGMessageCollection $msgs)
    {
        $programme = $this->parent_gui->getStudyProgramme();
        $acting_user_id = (int) $this->user->getId();

        foreach ($progress_updates as $progress_id => $target_status) {
            switch ($target_status) {
                case ilStudyProgrammeProgress::STATUS_IN_PROGRESS:

                    $progress = $this->progress_repository->get($progress_id);
                    $cur_status = $progress->getStatus();

                    if ($cur_status == ilStudyProgrammeProgress::STATUS_ACCREDITED) {
                        $programme->unmarkAccredited($progress_id, $acting_user_id, $msgs);
                    }
                    if ($cur_status == ilStudyProgrammeProgress::STATUS_NOT_RELEVANT) {
                        $programme->markRelevant($progress_id, $acting_user_id, $msgs);
                    }
                    break;

                case ilStudyProgrammeProgress::STATUS_ACCREDITED:
                    $programme->markAccredited($progress_id, $acting_user_id, $msgs);
                    break;
                
                case ilStudyProgrammeProgress::STATUS_NOT_RELEVANT:
                    $programme->markNotRelevant($progress_id, $acting_user_id, $msgs);
                    break;

                case self::MANUAL_STATUS_NONE:
                    break;

                default:
                    $msgs->add(false, 'msg_impossible_target_status', $progress_id);
            }
        }
    }

    protected function updateDeadlines(array $deadlines, ilPRGMessageCollection $msgs)
    {
        $programme = $this->parent_gui->getStudyProgramme();
        $acting_user_id = (int) $this->user->getId();

        foreach ($deadlines as $progress_id => $deadline) {
            if (trim($deadline) === '') {
                $deadline = null;
            } else {
                $deadline = DateTimeImmutable::createFromFormat('d.m.Y', $deadline);
            }
            
            $progress = $this->progress_repository->get($progress_id);
            $cur_deadline = $progress->getDeadline();

            if ($deadline != $cur_deadline) {
                $programme->changeProgressDeadline($progress_id, $acting_user_id, $msgs, $deadline);
            }
        }
    }

    protected function updateRequiredPoints(array $required_points, ilPRGMessageCollection $msgs)
    {
        $programme = $this->parent_gui->getStudyProgramme();
        $acting_user_id = (int) $this->user->getId();

        foreach ($required_points as $progress_id => $points) {
            $points = (int) $points;
            
            if ($points < 0) {
                $msgs->add(false, 'msg_points_must_be_positive', $progress_id);
                continue;
            }

            $progress = $this->progress_repository->get($progress_id);
            $cur_points = $progress->getAmountOfPoints();

            if ($points != $cur_points) {
                $programme->changeAmountOfPoints($progress_id, $acting_user_id, $msgs, $points);
            }
        }
    }

    protected function showSuccessMessage($a_lng_var)
    {
        ilUtil::sendSuccess($this->lng->txt("prg_$a_lng_var"), true);
    }

    protected function buildFrame($tab, $content)
    {
        $tabs = [];
        if ($this->permissions->may(ilOrgUnitOperation::OP_VIEW_INDIVIDUAL_PLAN)) {
            $tabs[] = 'view';
        }
        if ($this->permissions->may(ilOrgUnitOperation::OP_EDIT_INDIVIDUAL_PLAN)) {
            $tabs[] = 'manage';
        }

        $tpl = new ilTemplate("tpl.indivdual_plan_frame.html", true, true, "Modules/StudyProgramme");
        $ass = $this->getAssignmentObject();
        $user_id = $ass->getUserId();
        $tpl->setVariable("USERNAME", ilObjUser::_lookupFullname($user_id));

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

    public function getLinkTargetView($a_ass_id)
    {
        $cl = "ilObjStudyProgrammeIndividualPlanGUI";
        $this->ctrl->setParameterByClass($cl, "ass_id", $a_ass_id);
        $link = $this->ctrl->getLinkTargetByClass($cl, "view");
        $this->ctrl->setParameterByClass($cl, "ass_id", null);
        return $link;
    }
}
