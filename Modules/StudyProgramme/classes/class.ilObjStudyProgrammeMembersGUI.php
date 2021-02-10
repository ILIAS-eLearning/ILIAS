<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

/**
 * @ilCtrl_Calls ilObjStudyProgrammeMembersGUI: ilStudyProgrammeRepositorySearchGUI
 * @ilCtrl_Calls ilObjStudyProgrammeMembersGUI: ilObjStudyProgrammeIndividualPlanGUI
 * @ilCtrl_Calls ilObjStudyProgrammeMembersGUI: ilObjFileGUI
 * @ilCtrl_Calls ilObjStudyProgrammeMembersGUI: ilStudyProgrammeMailMemberSearchGUI
 * @ilCtrl_Calls ilObjStudyProgrammeMembersGUI: ilStudyProgrammeChangeExpireDateGUI
 * @ilCtrl_Calls ilObjStudyProgrammeMembersGUI: ilStudyProgrammeChangeDeadlineGUI
 * @ilCtrl_Calls ilObjStudyProgrammeMembersGUI: ilFormPropertyDispatchGUI
 */
class ilObjStudyProgrammeMembersGUI
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
     * @var ilAccess
     */
    public $access;

    /**
     * @var ilObjStudyProgramme
     */
    public $object;

    /**
     * @var ilLanguage
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

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilObjectGUI
     */
    protected $parent_gui;

    /**
     * @var ilStudyProgrammeUserProgressDB
     */
    protected $sp_user_progress_db;

    /**
     * @var ilStudyProgrammeUserProgress[]
     */
    protected $progress_objects;

    public function __construct(
        \ilGlobalTemplateInterface $tpl,
        \ilCtrl $ilCtrl,
        \ilToolbarGUI $ilToolbar,
        \ilAccess $access,
        \ilLanguage $lng,
        \ilObjUser $user,
        \ilTabsGUI $tabs,
        ilStudyProgrammeUserProgressDB $sp_user_progress_db,
        ilStudyProgrammeUserAssignmentDB $sp_user_assignment_db,
        ilStudyProgrammeRepositorySearchGUI $repository_search_gui,
        ilObjStudyProgrammeIndividualPlanGUI $individual_plan_gui,
        ilStudyProgrammePositionBasedAccess $position_based_access
    ) {
        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->toolbar = $ilToolbar;
        $this->access = $access;
        $this->lng = $lng;
        $this->user = $user;
        $this->tabs = $tabs;
        $this->sp_user_assignment_db = $sp_user_assignment_db;
        $this->sp_user_progress_db = $sp_user_progress_db;

        $this->repository_search_gui = $repository_search_gui;
        $this->individual_plan_gui = $individual_plan_gui;

        $this->progress_objects = array();
        $this->position_based_access = $position_based_access;
        $this->object = null;

        $lng->loadLanguageModule("prg");
    }

    public function setParentGUI(ilObjectGUI $a_parent_gui) : void
    {
        $this->parent_gui = $a_parent_gui;
    }

    public function setRefId(int $ref_id) : void
    {
        $this->ref_id = $ref_id;
        $this->object = \ilObjStudyProgramme::getInstanceByRefId($ref_id);
    }

    public function executeCommand() : void
    {
        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass($this);

        if ($cmd == "") {
            $cmd = "view";
        }

        # TODO: Check permission of user!!

        switch ($next_class) {
            case "ilstudyprogrammerepositorysearchgui":
                $this->repository_search_gui->setCallback($this, "addUsers");
                $this->ctrl->setReturn($this, "view");
                $this->ctrl->forwardCommand($this->repository_search_gui);
                break;
            case "ilobjstudyprogrammeindividualplangui":
                $this->individual_plan_gui->setParentGUI($this);
                $this->individual_plan_gui->setRefId($this->ref_id);
                $this->ctrl->forwardCommand($this->individual_plan_gui);
                break;
            case "ilstudyprogrammemailmembersearchgui":
                $this->tabs->clearTargets();
                $this->tabs->setBackTarget(
                    $this->lng->txt('btn_back'),
                    $this->ctrl->getLinkTarget($this, $this->getDefaultCommand())
                );
                $dic = ilStudyProgrammeDIC::dic();
                $mail_search = $dic['ilStudyProgrammeMailMemberSearchGUI'];
                $mail_search->setAssignments($this->getAssignmentsById());
                $mail_search->setBackTarget(
                    $this->ctrl->getLinkTarget($this, $this->getDefaultCommand())
                );
                $this->ctrl->forwardCommand($mail_search);
                break;
            case "ilstudyprogrammechangeexpiredategui":
                $this->tabs->clearTargets();
                $this->tabs->setBackTarget(
                    $this->lng->txt('btn_back'),
                    $this->ctrl->getLinkTarget($this, $this->getDefaultCommand())
                );
                $dic = ilStudyProgrammeDIC::dic();
                $gui = $dic['ilStudyProgrammeChangeExpireDateGUI'];
                $gui->setRefId($this->ref_id);
                $gui->setProgressIds($this->getGetPrgsIds());
                $this->ctrl->forwardCommand($gui);
                break;
            case "ilstudyprogrammechangedeadlinegui":
                $this->tabs->clearTargets();
                $this->tabs->setBackTarget(
                    $this->lng->txt('btn_back'),
                    $this->ctrl->getLinkTarget($this, $this->getDefaultCommand())
                );
                $dic = ilStudyProgrammeDIC::dic();
                $gui = $dic['ilStudyProgrammeChangeDeadlineGUI'];
                $gui->setRefId($this->ref_id);
                $gui->setProgressIds($this->getGetPrgsIds());
                $this->ctrl->forwardCommand($gui);
                break;
            case false:
                switch ($cmd) {
                    case "view":
                    case "markAccredited":
                    case "markAccreditedMulti":
                    case "unmarkAccredited":
                    case "unmarkAccreditedMulti":
                    case "removeUser":
                    case "removeUserMulti":
                    case "addUsersWithAcknowledgedCourses":
                    case "markNotRelevantMulti":
                    case "markRelevantMulti":
                    case "updateFromCurrentPlanMulti":
                    case "applyFilter":
                    case "resetFilter":
                    case "changeDeadlineMulti":
                    case "changeExpireDateMulti":
                        $cont = $this->$cmd();
                        $this->tpl->setContent($cont);
                        break;
                    default:
                        throw new ilException("ilObjStudyProgrammeMembersGUI: " .
                                              "Command not supported: $cmd");
                }
                break;
            default:
                throw new ilException(
                    "ilObjStudyProgrammeMembersGUI: Can't forward to next class $next_class"
                );
        }
    }

    protected function getDefaultCommand() : string
    {
        return "view";
    }

    protected function getAssignmentsById() : array
    {
        $assignments = $this->object->getAssignments();

        return array_filter($assignments, function (ilStudyProgrammeUserAssignment $assignment) {
            return $assignment->getStudyProgramme()->getId() == $this->object->getId();
        });
    }

    protected function getMembersTableGUI() : ilStudyProgrammeMembersTableGUI
    {
        $prg_id = ilObject::_lookupObjId($this->ref_id);
        $table = new ilStudyProgrammeMembersTableGUI(
            $prg_id,
            $this->ref_id,
            $this,
            "view",
            "",
            $this->sp_user_progress_db,
            $this->position_based_access
        );
        return $table;
    }

    /**
     * Shows table with all members of the SP
     *
     * @return string
     */
    protected function view() : string
    {
        if ($this->getStudyProgramme()->isActive()) {
            $this->initSearchGUI();
            $this->initMailToMemberButton($this->toolbar, true);
        }

        if (!$this->getStudyProgramme()->isActive()) {
            ilUtil::sendInfo($this->lng->txt("prg_no_members_not_active"));
        }
        $table = $this->getMembersTableGUI();
        return $table->getHTML();
    }

    public function applyFilter() : void
    {
        $table = $this->getMembersTableGUI();
        $table->resetOffset();
        $table->writeFilterToSession();
        $this->ctrl->redirect($this, "view");
    }

    public function resetFilter() : void
    {
        $table = $this->getMembersTableGUI();
        $table->resetOffset();
        $table->resetFilter();
        $this->ctrl->redirect($this, "view");
    }


    /**
     * Assigns a users to SP
     *
     * @param int[] 	$users
     *
     * @return null
     */
    public function addUsers(array $users) : bool
    {
        $prg = $this->getStudyProgramme();
        $users = $this->getAddableUsers($users);

        $completed_courses = array();
        foreach ($users as $user_id) {
            $completed_crss = $prg->getCompletedCourses((int) $user_id);
            if ($completed_crss) {
                $completed_courses[$user_id] = $completed_crss;
            }
        }

        if (count($completed_courses) > 0) {
            $this->viewCompletedCourses($completed_courses, $users);
            return true;
        }

        $this->_addUsers($users);

        $this->ctrl->redirect($this, "view");
    }

    /**
     * Shows list of completed courses for each user if he should be assigned
     *
     * @param int[] 	$completed_courses
     * @param int[] 	$users
     *
     * @return null
     */
    public function viewCompletedCourses(array $completed_courses, array $users) : void
    {
        $tpl = new ilTemplate(
            "tpl.acknowledge_completed_courses.html",
            true,
            true,
            "Modules/StudyProgramme"
        );
        $tpl->setVariable("TITLE", $this->lng->txt("prg_acknowledge_completed_courses"));
        $tpl->setVariable("CAPTION_ADD", $this->lng->txt("btn_next"));
        $tpl->setVariable("CAPTION_CANCEL", $this->lng->txt("cancel"));
        $tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
        $tpl->setVariable("ADD_CMD", "addUsersWithAcknowledgedCourses");
        $tpl->setVariable("CANCEL_CMD", "view");

        foreach ($completed_courses as $user_id => $completed_courses) {
            $names = ilObjUser::_lookupName($user_id);
            $tpl->setCurrentBlock("usr_section");
            $tpl->setVariable("FIRSTNAME", $names["firstname"]);
            $tpl->setVariable("LASTNAME", $names["lastname"]);
            $table = new ilStudyProgrammeAcknowledgeCompletedCoursesTableGUI(
                $this,
                $user_id,
                $completed_courses
            );
            $tpl->setVariable("TABLE", $table->getHTML());
            $tpl->parseCurrentBlock();
        }

        foreach ($users as $usr_id) {
            $tpl->setCurrentBlock("usr_ids_section");
            $tpl->setVariable("USR_ID", $usr_id);
            $tpl->parseCurrentBlock();
        }

        $this->tpl->setContent($tpl->get());
    }

    /**
     * Assign users if they have any completed course
     */
    public function addUsersWithAcknowledgedCourses() : void
    {
        $users = $_POST["users"];
        $users = $this->getAddableUsers($users);
        $assignments = $this->_addUsers($users);

        $completed_programmes = $_POST["courses"];
        if (is_array($completed_programmes)) {
            foreach ($completed_programmes as $user_id => $prg_ref_ids) {
                $ass_id = $assignments[$user_id]->getId();
                foreach ($prg_ref_ids as $ids) {
                    [$prg_ref_id, $crs_id, $crsr_id] = explode(";", $ids);
                    $prg = $this->getStudyProgramme((int) $prg_ref_id);
                    $progress = $prg->getProgressForAssignment((int) $ass_id);
                    $progress->setLPCompleted((int) $crsr_id, $user_id);
                }
            }
        }

        $this->ctrl->redirect($this, "view");
    }

    protected function getAddableUsers(array $users) : array
    {
        if ($this->mayManageMembers()) {
            return $users;
        }

        if ($this->getStudyProgramme()->getAccessControlByOrguPositionsGlobal()) {
            $to_add = $this->position_based_access->filterUsersAccessibleForOperation(
                $this->getStudyProgramme(),
                ilOrgUnitOperation::OP_MANAGE_MEMBERS,
                $users
            );
            $cnt_not_added = count($users) - count($to_add);
            if ($cnt_not_added > 0) {
                ilUtil::sendInfo(
                    sprintf(
                        $this->lng->txt('could_not_add_users_no_permissons'),
                        $cnt_not_added
                    ),
                    true
                );
            }
            return $to_add;
        }
    }

    /**
     * Add users to SP
     *
     * @param int[] 	$users
     *
     * @return ilStudyProgrammeUserAssignment[]
     */
    protected function _addUsers(array $users) : array
    {
        $prg = $this->getStudyProgramme();

        $assignments = array();

        foreach ($users as $user_id) {
            $assignments[$user_id] = $prg->assignUser((int) $user_id);
        }

        if (count($users) == 1) {
            ilUtil::sendSuccess($this->lng->txt("prg_added_member"), true);
        }
        if (count($users) > 1) {
            ilUtil::sendSuccess($this->lng->txt("prg_added_members"), true);
        }

        return $assignments;
    }

    /**
     * Get post prgs ids
     *
     * @return string[]
     */
    protected function getPostPrgsIds() : array
    {
        $prgrs_ids = $_POST['prgs_ids'];
        if ($prgrs_ids === null) {
            $this->showInfoMessage("no_user_selected");
            $this->ctrl->redirect($this, "view");
        }
        return $prgrs_ids;
    }

    protected function getGetPrgsIds() : array
    {
        $prgrs_ids = $_GET['prgrs_ids'];
        if (is_null($prgrs_ids)) {
            return array();
        }
        return explode(',', $prgrs_ids);
    }

    /**
     * Mark SP for single user accredited
     */
    public function markAccredited() : void
    {
        $prgrs_id = $this->getPrgrsId();
        $this->markAccreditedById($prgrs_id);
        $this->showSuccessMessage("mark_accredited_success");
        $this->ctrl->redirect($this, "view");
    }

    /**
     * Mark SP for users accredited
     */
    public function markAccreditedMulti() : void
    {
        $prgrs_ids = $this->getPostPrgsIds();
        $errors = 0;
        foreach ($prgrs_ids as $key => $prgrs_id) {
            try {
                $this->markAccreditedById((int) $prgrs_id);
            } catch (ilStudyProgrammePositionBasedAccessViolationException $e) {
                $errors++;
            }
        }
        if ($errors === 0) {
            $this->showSuccessMessage("mark_accredited_multi_success");
        } else {
            $this->showInfoMessage("some_users_may_not_be_accredited");
        }
        $this->ctrl->redirect($this, "view");
    }

    /**
     * Accredited SP
     */
    protected function markAccreditedById(int $prgrs_id) : void
    {
        $prgrs = $this->getProgressObject($prgrs_id);
        $usr_id = $prgrs->getUserId();
        if (
            $this->object->getAccessControlByOrguPositionsGlobal() &&
            !in_array($usr_id, $this->editIndividualPlan()) &&
            !$this->mayManageMembers()
        ) {
            throw new ilStudyProgrammePositionBasedAccessViolationException(
                'No permission to edit progress of user'
            );
        }
        $prgrs->markAccredited($this->user->getId());

        $ass = $this->sp_user_assignment_db->getInstanceById($prgrs->getAssignmentId());
        $this->updateUserAssignmentFromProgramm($ass);
    }

    /**
     * Unmark SP for single user accredited
     */
    public function unmarkAccredited() : void
    {
        $prgrs_id = $this->getPrgrsId();
        $this->unmarkAccreditedByProgressId($prgrs_id);
        $this->showSuccessMessage("unmark_accredited_success");
        $this->ctrl->redirect($this, "view");
    }

    /**
     * Deaccredited SP
     */
    protected function unmarkAccreditedByProgressId(int $prgrs_id) : void
    {
        $prgrs = $this->getProgressObject($prgrs_id);
        $usr_id = $prgrs->getUserId();
        if (
            $this->object->getAccessControlByOrguPositionsGlobal() &&
            !in_array($usr_id, $this->editIndividualPlan()) &&
            !$this->mayManageMembers()
        ) {
            throw new ilStudyProgrammePositionBasedAccessViolationException(
                'No permission to edit progress of user'
            );
        }
        $prgrs->unmarkAccredited();

        $ass = $this->sp_user_assignment_db->getInstanceById($prgrs->getAssignmentId());
        $this->updateUserAssignmentFromProgramm($ass);
    }

    /**
     * Unmark SP for users accredited
     */
    public function unmarkAccreditedMulti() : void
    {
        $prgrs_ids = $this->getPostPrgsIds();
        $errors = 0;
        foreach ($prgrs_ids as $key => $prgrs_id) {
            $prgrs_status = $this->getProgressObject((int) $prgrs_id)->getStatus();
            if ($prgrs_status == ilStudyProgrammeProgress::STATUS_ACCREDITED) {
                try {
                    $this->unmarkAccreditedByProgressId((int) $prgrs_id);
                } catch (ilStudyProgrammePositionBasedAccessViolationException $e) {
                    $errors++;
                }
            }
        }
        if ($errors === 0) {
            $this->showSuccessMessage("unmark_accredited_multi_success");
        } else {
            $this->showInfoMessage("some_users_may_not_be_unmarked_accredited");
        }
        $this->ctrl->redirect($this, "view");
    }

    /**
     * Mark SP as relevant for users
     */
    public function markRelevantMulti() : void
    {
        $prgrs_ids = $this->getPostPrgsIds();
        $errors = 0;
        foreach ($prgrs_ids as $key => $prgrs_id) {
            $prgrs = $this->getProgressObject((int) $prgrs_id);
            $usr_id = $prgrs->getUserId();
            if ($this->object->getAccessControlByOrguPositionsGlobal() &&
                !in_array($usr_id, $this->editIndividualPlan()) &&
                !$this->mayManageMembers()
            ) {
                $errors++;
                continue;
            }

            $prgrs_status = $this->getProgressObject((int) $prgrs_id)->getStatus();
            if (
                 $prgrs_status == ilStudyProgrammeProgress::STATUS_IN_PROGRESS ||
                 $prgrs_status == ilStudyProgrammeProgress::STATUS_ACCREDITED
            ) {
                continue;
            }
            $prgrs->markRelevant($this->user->getId());
        }
        if ($errors === 0) {
            $this->showSuccessMessage("mark_relevant_multi_success");
        } else {
            $this->showInfoMessage("some_users_may_not_be_marked_relevant");
        }
        $this->ctrl->redirect($this, "view");
    }

    /**
     * Mark SP as not relevant for users
     */
    public function markNotRelevantMulti() : void
    {
        $prgrs_ids = $this->getPostPrgsIds();
        $errors = 0;
        foreach ($prgrs_ids as $key => $prgrs_id) {
            $prgrs = $this->getProgressObject((int) $prgrs_id);
            $usr_id = $prgrs->getUserId();
            if ($this->object->getAccessControlByOrguPositionsGlobal() &&
                !in_array($usr_id, $this->editIndividualPlan()) &&
                !$this->mayManageMembers()
            ) {
                $errors++;
                continue;
            }
            $prgrs->markNotRelevant($this->user->getId());
        }
        if ($errors === 0) {
            $this->showSuccessMessage("mark_not_relevant_multi_success");
        } else {
            $this->showInfoMessage("some_users_may_not_be_marked_not_relevant");
        }
        $this->ctrl->redirect($this, "view");
    }

    /**
     * Update user plan from current SP structure if they has no individual plan
     */
    public function updateFromCurrentPlanMulti() : void
    {
        $prgrs_ids = $this->getPostPrgsIds();
        $not_updated = array();

        foreach ($prgrs_ids as $key => $prgrs_id) {
            //** ilStudyProgrammeUserProgress */
            $prgrs = $this->getProgressObject((int) $prgrs_id);
            //** ilStudyProgrammeUserAssignment */
            $ass = $this->sp_user_assignment_db->getInstanceById($prgrs->getAssignmentId());
            $prg = $ass->getStudyProgramme();
            if ($prg->getRefId() != $this->ref_id) {
                $not_updated[] = $prgrs_id;
                continue;
            }

            $this->updateUserAssignmentFromProgramm($ass);
        }

        if (count($not_updated) == count($prgrs_ids)) {
            $this->showInfoMessage("update_from_current_plan_not_possible");
        } elseif (count($not_updated) > 0) {
            $this->showSuccessMessage("update_from_current_plan_partitial_success");
        } else {
            $this->showSuccessMessage("update_from_current_plan_success");
        }

        $this->ctrl->redirect($this, "view");
    }

    public function changeDeadlineMulti() : void
    {
        $this->ctrl->setParameterByClass(
            'ilStudyProgrammeChangeDeadlineGUI',
            'prgrs_ids',
            implode(',', $this->getPostPrgsIds())
        );

        $link = $this->ctrl->getLinkTargetByClass(
            'ilStudyProgrammeChangeDeadlineGUI',
            'showDeadlineConfig',
            '',
            false,
            false
        );

        $this->ctrl->clearParameterByClass('ilStudyProgrammeChangeDeadlineGUI', 'prgrs_ids');
        $this->ctrl->redirectToURL($link);
    }

    public function changeExpireDateMulti() : void
    {
        $this->ctrl->setParameterByClass(
            'ilStudyProgrammeChangeExpireDateGUI',
            'prgrs_ids',
            implode(',', $this->getPostPrgsIds())
        );

        $link = $this->ctrl->getLinkTargetByClass(
            'ilStudyProgrammeChangeExpireDateGUI',
            'showExpireDateConfig',
            '',
            false,
            false
        );

        $this->ctrl->clearParameterByClass('ilStudyProgrammeChangeExpireDateGUI', 'prgrs_ids');
        $this->ctrl->redirectToURL($link);
    }

    /**
     * Remove single user from SP
     */
    public function removeUser() : void
    {
        $prgrs_id = $this->getPrgrsId();
        $this->remove($prgrs_id);
        $this->showSuccessMessage("remove_user_success");
        $this->ctrl->redirect($this, "view");
    }

    /**
     * Remove user from SP
     */
    protected function removeUserMulti() : void
    {
        $prgrs_ids = $this->getPostPrgsIds();
        $not_removed = array();
        foreach ($prgrs_ids as $key => $prgrs_id) {
            try {
                $this->remove((int) $prgrs_id);
            } catch (ilException $e) {
                $not_removed[] = $prgrs_id;
            }
        }
        if (count($not_removed) == count($prgrs_ids)) {
            $this->showInfoMessage("remove_users_not_possible");
        } elseif (count($not_removed) > 0) {
            $this->showSuccessMessage("remove_users_partitial_success");
        } else {
            $this->showSuccessMessage("remove_users_success");
        }
        $this->ctrl->redirect($this, "view");
    }

    /**
     * Rmeove user
     */
    protected function remove(int $prgrs_id) : void
    {
        $prgrs = $this->getProgressObject($prgrs_id);
        $usr_id = $prgrs->getUserId();
        if (
            $this->object->getAccessControlByOrguPositionsGlobal() &&
            !in_array($usr_id, $this->manageMembers()) &&
            !$this->mayManageMembers()
        ) {
            throw new ilStudyProgrammePositionBasedAccessViolationException(
                'No permission to manage membership of user'
            );
        }
        $ass = $this->sp_user_assignment_db->getInstanceById($prgrs->getAssignmentId());
        $prg = $ass->getStudyProgramme();
        if ($prg->getRefId() != $this->ref_id) {
            throw new ilException("Can only remove users from the node they where assigned to.");
        }
        $ass->deassign();
    }

    /**
     * Get progress object for prgrs id
     */
    protected function getProgressObject(int $prgrs_id) : ilStudyProgrammeUserProgress
    {
        if (!array_key_exists($prgrs_id, $this->progress_objects)) {
            $this->progress_objects[$prgrs_id] = $this->sp_user_progress_db->getInstanceById(
                $prgrs_id
            );
        }
        return $this->progress_objects[$prgrs_id];
    }

    /**
     * Get current prgrs_id from URL
     */
    protected function getPrgrsId() : int
    {
        if (!is_numeric($_GET["prgrs_id"])) {
            throw new ilException("Expected integer 'prgrs_id'");
        }
        return (int) $_GET["prgrs_id"];
    }

    /**
     * Shows ilUtil success message
     */
    protected function showSuccessMessage(string $lng_var) : void
    {
        ilUtil::sendSuccess($this->lng->txt("prg_$lng_var"), true);
    }

    /**
     * Shows ilUtil failed message
     */
    protected function showInfoMessage(string $lng_var) : void
    {
        ilUtil::sendInfo($this->lng->txt("prg_$lng_var"), true);
    }

    protected function initSearchGUI() : void
    {
        ilStudyProgrammeRepositorySearchGUI::fillAutoCompleteToolbar(
            $this,
            $this->toolbar,
            array(
                "auto_complete_name" => $this->lng->txt("user"),
                "submit_name" => $this->lng->txt("add"),
                "add_search" => true
            )
        );
    }

    protected function initMailToMemberButton(ilToolbarGUI $toolbar, bool $separator = false) : void
    {
        if ($separator) {
            $toolbar->addSeparator();
        }

        $toolbar->addButton(
            $this->lng->txt('mail_members'),
            $this->ctrl->getLinkTargetByClass(
                'ilStudyProgrammeMailMemberSearchGUI',
                'showSelectableUsers'
            )
        );
    }

    /**
     * Get studyprogramm object for ref_id
     * Use this ref_id if argument is null
     */
    public function getStudyProgramme(int $ref_id = null) : ilObjStudyProgramme
    {
        if ($ref_id === null) {
            $ref_id = $this->ref_id;
        }
        return ilObjStudyProgramme::getInstanceByRefId($ref_id);
    }

    /**
     * Get the link target for an action on user progress.
     */
    public function getLinkTargetForAction(string $action, int $prgrs_id, int $ass_id) : string
    {
        switch ($action) {
            case ilStudyProgrammeUserProgress::ACTION_MARK_ACCREDITED:
                $target_name = "markAccredited";
                break;
            case ilStudyProgrammeUserProgress::ACTION_UNMARK_ACCREDITED:
                $target_name = "unmarkAccredited";
                break;
            case ilStudyProgrammeUserProgress::ACTION_SHOW_INDIVIDUAL_PLAN:
                return $this->individual_plan_gui->getLinkTargetView($ass_id);
            case ilStudyProgrammeUserProgress::ACTION_REMOVE_USER:
                $target_name = "removeUser";
                break;
            default:
                throw new ilException("Unknown action: $action");
        }

        $this->ctrl->setParameter($this, "prgrs_id", $prgrs_id);
        $link = $this->ctrl->getLinkTarget($this, $target_name);
        $this->ctrl->setParameter($this, "prgrs_id", null);
        return $link;
    }

    public function visibleUsers() : array
    {
        return array_unique(array_merge(
            $this->viewMembers(),
            $this->readLearningProgress(),
            $this->viewIndividualPlan(),
            $this->editIndividualPlan(),
            $this->manageMembers()
        ));
    }

    protected $view_members;
    public function viewMembers()
    {
        if (!$this->view_members) {
            $this->view_members =
                array_unique(array_merge(
                    $this->position_based_access->getUsersInPrgAccessibleForOperation(
                        $this->object,
                        ilOrgUnitOperation::OP_VIEW_MEMBERS
                    ),
                    $this->manageMembers()
                ));
        }
        return $this->view_members;
    }

    protected $read_learning_progress;
    public function readLearningProgress()
    {
        if (!$this->read_learning_progress) {
            $this->read_learning_progress =
                array_unique(array_merge(
                    $this->position_based_access->getUsersInPrgAccessibleForOperation(
                        $this->object,
                        ilOrgUnitOperation::OP_READ_LEARNING_PROGRESS
                    ),
                    $this->viewIndividualPlan()
                ));
        }
        return $this->read_learning_progress;
    }

    protected $view_individual_plan;
    public function viewIndividualPlan()
    {
        if (!$this->view_individual_plan) {
            $this->view_individual_plan =
                array_unique(array_merge(
                    $this->position_based_access->getUsersInPrgAccessibleForOperation(
                        $this->object,
                        ilOrgUnitOperation::OP_VIEW_INDIVIDUAL_PLAN
                    ),
                    $this->editIndividualPlan()
                ));
        }
        return $this->view_individual_plan;
    }

    protected $edit_individual_plan;
    public function editIndividualPlan()
    {
        if (!$this->edit_individual_plan) {
            $this->edit_individual_plan =
                $this->position_based_access->getUsersInPrgAccessibleForOperation(
                    $this->object,
                    ilOrgUnitOperation::OP_EDIT_INDIVIDUAL_PLAN
                );
        }
        return $this->edit_individual_plan;
    }

    protected $manage_members;
    public function manageMembers()
    {
        if (!$this->manage_members) {
            $this->manage_members =
                $this->position_based_access->getUsersInPrgAccessibleForOperation(
                    $this->object,
                    ilOrgUnitOperation::OP_MANAGE_MEMBERS
                );
        }
        return $this->manage_members;
    }

    public function mayManageMembers() : bool
    {
        return $this->access->checkAccessOfUser(
            $this->user->getId(),
            'manage_members',
            '',
            $this->object->getRefId()
        );
    }

    public function getLocalMembers() : array
    {
        return $this->object->getMembers();
    }

    public function isOperationAllowedForUser(int $usr_id, string $operation) : bool
    {
        return $this->mayManageMembers()
            || $this->position_based_access->isUserAccessibleForOperationAtPrg($usr_id, $this->object, $operation);
    }

    protected function updateUserAssignmentFromProgramm(ilStudyProgrammeUserAssignment $ass) : void
    {
        $ass->updateFromProgram();
        $ass->updateValidityFromProgram();
        $ass->updateDeadlineFromProgram();
    }
}
