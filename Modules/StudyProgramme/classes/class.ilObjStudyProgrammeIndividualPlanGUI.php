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

class ilObjStudyProgrammeIndividualPlanGUI
{
    private const POST_VAR_STATUS = "status";
    private const POST_VAR_REQUIRED_POINTS = "required_points";
    private const POST_VAR_DEADLINE = "deadline";
    public const MANUAL_STATUS_NONE = -1;

    public ilGlobalTemplateInterface $tpl;
    public ilCtrl $ctrl;
    public ilLanguage $lng;
    public ilObjUser $user;
    protected ilStudyProgrammeProgressRepository $progress_repository;
    protected ilStudyProgrammeAssignmentRepository $assignment_repository;
    protected ilPRGMessagePrinter $messages;
    protected ILIAS\HTTP\Wrapper\WrapperFactory $http_wrapper;
    protected ILIAS\Refinery\Factory $refinery;

    protected ?ilStudyProgrammeAssignment $assignment_object;
    public ?ilObjStudyProgramme $object;
    protected ?ilPRGPermissionsHelper $permissions;
    protected ilObjStudyProgrammeMembersGUI $parent_gui;
    protected int $ref_id;

    public function __construct(
        ilGlobalTemplateInterface $tpl,
        ilCtrl $ilCtrl,
        ilLanguage $lng,
        ilObjUser $ilUser,
        ilStudyProgrammeProgressRepository $progress_repository,
        ilStudyProgrammeAssignmentRepository $assignment_repository,
        ilPRGMessagePrinter $messages,
        ILIAS\HTTP\Wrapper\WrapperFactory $http_wrapper,
        ILIAS\Refinery\Factory $refinery
    ) {
        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->user = $ilUser;
        $this->progress_repository = $progress_repository;
        $this->assignment_repository = $assignment_repository;
        $this->messages = $messages;
        $this->http_wrapper = $http_wrapper;
        $this->refinery = $refinery;

        $this->assignment_object = null;
        $this->object = null;
        $this->permissions = null;

        $lng->loadLanguageModule("prg");

        $this->tpl->addCss("Modules/StudyProgramme/templates/css/ilStudyProgramme.css");
    }

    public function setParentGUI(ilObjStudyProgrammeMembersGUI $parent_gui) : void
    {
        $this->parent_gui = $parent_gui;
    }

    public function setRefId(int $ref_id) : void
    {
        $this->ref_id = $ref_id;
        $this->object = ilObjStudyProgramme::getInstanceByRefId($ref_id);
        $this->permissions = ilStudyProgrammeDIC::specificDicFor($this->object)['permissionhelper'];
    }

    public function executeCommand() : void
    {
        $cmd = $this->ctrl->getCmd();

        if ($cmd === "" || $cmd === null) {
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
                throw new ilException("ilObjStudyProgrammeMembersGUI: Command not supported: $cmd");
        }

        $this->tpl->setContent($cont);
    }

    protected function getAssignmentId() : int
    {
        return $this->http_wrapper->query()->retrieve("ass_id", $this->refinery->kindlyTo()->int());
    }

    protected function getAssignmentObject() : ?ilStudyProgrammeAssignment
    {
        if ($this->assignment_object === null) {
            $id = $this->getAssignmentId();
            $this->assignment_object = $this->assignment_repository->getInstanceById($id);
        }
        return $this->assignment_object;
    }

    protected function view() : string
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

    protected function manage() : string
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

    protected function updateFromCurrentPlan() : void
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
    
    protected function updateFromInput() : void
    {
        $retrieve =
            $this->refinery->logical()->sequential([
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->kindlyTo()->string()
                ),
                $this->refinery->custom()->transformation(function ($a) {
                    krsort($a, SORT_NUMERIC);
                    return $a;
                })
            ]);


        $msgs = $this->messages->getMessageCollection('msg_update_individual_plan');
        $this->updateStatus($this->http_wrapper->post()->retrieve(self::POST_VAR_STATUS, $retrieve), $msgs);
        $this->updateDeadlines($this->http_wrapper->post()->retrieve(self::POST_VAR_DEADLINE, $retrieve), $msgs);
        $this->updateRequiredPoints($this->http_wrapper->post()->retrieve(self::POST_VAR_REQUIRED_POINTS, $retrieve), $msgs);

        if ($msgs->hasAnyMessages()) {
            $this->messages->showMessages($msgs);
        }

        $this->ctrl->setParameter($this, "ass_id", $this->getAssignmentId());
        $this->ctrl->redirect($this, "manage");
    }

    protected function updateStatus(array $progress_updates, ilPRGMessageCollection $msgs) : void
    {
        $programme = $this->parent_gui->getStudyProgramme();
        $acting_user_id = $this->user->getId();

        foreach ($progress_updates as $progress_id => $target_status) {
            switch ($target_status) {
                case ilStudyProgrammeProgress::STATUS_IN_PROGRESS:

                    $progress = $this->progress_repository->get($progress_id);
                    $cur_status = $progress->getStatus();

                    if ($cur_status === ilStudyProgrammeProgress::STATUS_ACCREDITED) {
                        $programme->unmarkAccredited($progress_id, $acting_user_id, $msgs);
                    }
                    if ($cur_status === ilStudyProgrammeProgress::STATUS_NOT_RELEVANT) {
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

    protected function updateDeadlines(array $deadlines, ilPRGMessageCollection $msgs) : void
    {
        $programme = $this->parent_gui->getStudyProgramme();
        $acting_user_id = $this->user->getId();

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

    protected function updateRequiredPoints(array $required_points, ilPRGMessageCollection $msgs) : void
    {
        $programme = $this->parent_gui->getStudyProgramme();
        $acting_user_id = $this->user->getId();

        foreach ($required_points as $progress_id => $points) {
            $points = (int) $points;
            
            if ($points < 0) {
                $msgs->add(false, 'msg_points_must_be_positive', $progress_id);
                continue;
            }

            $progress = $this->progress_repository->get($progress_id);
            $cur_points = $progress->getAmountOfPoints();

            if ($points !== $cur_points) {
                $programme->changeAmountOfPoints($progress_id, $acting_user_id, $msgs, $points);
            }
        }
    }

    protected function showSuccessMessage(string $lng_var) : void
    {
        $this->tpl->setOnScreenMessage("success", $this->lng->txt("prg_$lng_var"), true);
    }

    protected function buildFrame(string $tab, string $content) : string
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
            $tpl->setVariable("CLASS", $_tab === $tab ? "active" : "");
            $tpl->setVariable("LINK", $this->getLinkTargetForSubTab($_tab, $ass->getId()));
            $tpl->setVariable("TITLE", $this->lng->txt("prg_$_tab"));
            $tpl->parseCurrentBlock();
        }
        $tpl->setVariable("CONTENT", $content);

        return $tpl->get();
    }

    protected function getLinkTargetForSubTab(string $tab, int $ass_id) : string
    {
        $this->ctrl->setParameter($this, "ass_id", $ass_id);
        $lnk = $this->ctrl->getLinkTarget($this, $tab);
        $this->ctrl->setParameter($this, "ass_id", null);
        return $lnk;
    }

    public function appendIndividualPlanActions(ilTable2GUI $table) : void
    {
        $table->setFormAction($this->ctrl->getFormAction($this));
        $table->addCommandButton("updateFromCurrentPlan", $this->lng->txt("prg_update_from_current_plan"));
        $table->addCommandButton("updateFromInput", $this->lng->txt("save"));
    }

    public function getLinkTargetView(int $ass_id) : string
    {
        $cl = "ilObjStudyProgrammeIndividualPlanGUI";
        $this->ctrl->setParameterByClass($cl, "ass_id", $ass_id);
        $link = $this->ctrl->getLinkTargetByClass($cl, "view");
        $this->ctrl->setParameterByClass($cl, "ass_id", null);
        return $link;
    }
}
