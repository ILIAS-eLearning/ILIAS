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
    const DEFAULT_CMD = "view";

    const ACTION_MARK_ACCREDITED = "mark_accredited";
    const ACTION_UNMARK_ACCREDITED = "unmark_accredited";
    const ACTION_SHOW_INDIVIDUAL_PLAN = "show_individual_plan";
    const ACTION_REMOVE_USER = "remove_user";
    const ACTION_CHANGE_EXPIRE_DATE = "change_expire_date";
    const ACTION_CHANGE_DEADLINE = "change_deadline";

    const F_ALL_PROGRESS_IDS = 'all_progress_ids';
    const F_SELECTED_PROGRESS_IDS = 'prgs_ids';

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
     * @var ilStudyProgrammeProgressRepository
     */
    protected $sp_user_progress_db;

    /**
     * @var ilStudyProgrammeProgress[]
     */
    protected $progress_objects;

    /**
     * @var ilPRGMessages[]
     */
    protected $messages;

    /**
     * @var \ILIAS\Data\Factory
     */
    protected $data_factory;

    /**
     * @var ilPRGPermissionsHelper
     */
    protected $permissions;

    public function __construct(
        \ilGlobalTemplateInterface $tpl,
        \ilCtrl $ilCtrl,
        \ilToolbarGUI $ilToolbar,
        \ilLanguage $lng,
        \ilObjUser $user,
        \ilTabsGUI $tabs,
        ilStudyProgrammeProgressRepository $sp_user_progress_db,
        ilStudyProgrammeAssignmentRepository $sp_user_assignment_db,
        ilStudyProgrammeRepositorySearchGUI $repository_search_gui,
        ilObjStudyProgrammeIndividualPlanGUI $individual_plan_gui,
        ilPRGMessagePrinter $messages,
        \ILIAS\Data\Factory $data_factory,
        ilConfirmationGUI $confirmation_gui
    ) {
        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->toolbar = $ilToolbar;
        $this->lng = $lng;
        $this->user = $user;
        $this->tabs = $tabs;
        $this->sp_user_assignment_db = $sp_user_assignment_db;
        $this->sp_user_progress_db = $sp_user_progress_db;
        $this->messages = $messages;
        $this->data_factory = $data_factory;
        $this->confirmation_gui = $confirmation_gui;

        $this->repository_search_gui = $repository_search_gui;
        $this->individual_plan_gui = $individual_plan_gui;

        $this->progress_objects = array();
        $this->object = null;
        $this->permissions = null;

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
        $this->permissions = ilStudyProgrammeDIC::specificDicFor($this->object)['permissionhelper'];
    }

    public function executeCommand() : void
    {
        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass($this);

        if ($cmd == "") {
            $cmd = $this->getDefaultCommand();
        }

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
                    case "confirmedRemoveUsers":
                        $this->confirmedRemoveUsers();
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
        return self::DEFAULT_CMD;
    }

    protected function getAssignmentsById() : array
    {
        $assignments = $this->object->getAssignments();

        return array_filter($assignments, function (ilStudyProgrammeAssignment $assignment) {
            return $assignment->getRootId() == $this->object->getId();
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
            $this->permissions,
            $this->data_factory
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
        if ($this->getStudyProgramme()->isActive()
            && $this->permissions->may(ilOrgUnitOperation::OP_MANAGE_MEMBERS)
        ) {
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
     * @param string[] $user_ids
     */
    public function addUsers(array $user_ids) : bool
    {
        $prg = $this->getStudyProgramme();
        $user_ids = $this->getAddableUsers($user_ids);

        $completed_courses = array();
        foreach ($user_ids as $user_id) {
            $completed_crss = $prg->getCompletedCourses((int) $user_id);
            if ($completed_crss) {
                $completed_courses[$user_id] = $completed_crss;
            }
        }

        if (count($completed_courses) > 0) {
            $this->viewCompletedCourses($completed_courses, $user_ids);
            return true;
        }

        $this->_addUsers($user_ids);

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

                    if ($prg->isActive()) {
                        $progress = $prg->getProgressForAssignment((int) $ass_id);
                        $prg->succeed($progress->getId(), (int) $crsr_id);
                    }
                }
            }
        }

        $this->ctrl->redirect($this, "view");
    }

    /**
     *  @param int[] $users
     */
    protected function getAddableUsers(array $users) : array
    {
        $to_add = $this->permissions->filterUserIds(
            $users,
            ilOrgUnitOperation::OP_MANAGE_MEMBERS
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

    /**
     * Add users to SP
     *
     * @param string[] 	$user_ids
     *
     * @return array <string, ilStudyProgrammeAssignment>
     */
    protected function _addUsers(array $user_ids) : array
    {
        $prg = $this->getStudyProgramme();
        $assignments = array();

        foreach ($user_ids as $user_id) {
            $assignments[$user_id] = $prg->assignUser((int) $user_id);
        }

        if (count($assignments) == 1) {
            ilUtil::sendSuccess($this->lng->txt("prg_added_member"), true);
        }
        if (count($assignments) > 1) {
            ilUtil::sendSuccess($this->lng->txt("prg_added_members"), true);
        }

        return $assignments;
    }

    /**
     * @return int[]
     */
    protected function getPostPrgsIds() : array
    {
        if ($_POST['select_cmd_all']) {
            $prgrs_ids = $_POST[self::F_ALL_PROGRESS_IDS];
            $prgrs_ids = explode(',', $prgrs_ids);
        } else {
            $prgrs_ids = $_POST[self::F_SELECTED_PROGRESS_IDS];
        }
        if ($prgrs_ids === null) {
            $this->showInfoMessage("no_user_selected");
            $this->ctrl->redirect($this, "view");
        }
        return array_map('intval', $prgrs_ids);
    }

    protected function getGetPrgsIds() : array
    {
        $prgrs_ids = $_GET['prgrs_ids'];
        if (is_null($prgrs_ids)) {
            return array();
        }
        return explode(',', $prgrs_ids);
    }

    protected function getPrgrsId() : int
    {
        if (!is_numeric($_GET["prgrs_id"])) {
            throw new ilException("Expected integer 'prgrs_id'");
        }
        return (int) $_GET["prgrs_id"];
    }


    protected function markAccredited() : void
    {
        $prgrs_id = $this->getPrgrsId();
        $msgs = $this->getMessageCollection('msg_mark_accredited');
        $this->markAccreditedByProgressId($prgrs_id, $msgs);
        $this->showMessages($msgs);
        $this->ctrl->redirect($this, "view");
    }

    protected function markAccreditedMulti() : void
    {
        $prgrs_ids = $this->getPostPrgsIds();
        $msgs = $this->getMessageCollection('msg_mark_accredited');
        foreach ($prgrs_ids as $key => $prgrs_id) {
            $this->markAccreditedByProgressId((int) $prgrs_id, $msgs);
        }
        $this->showMessages($msgs);
        $this->ctrl->redirect($this, "view");
    }

    protected function markAccreditedByProgressId(int $prgrs_id, ilPRGMessageCollection $msgs) : void
    {
        $prgrs = $this->getProgressObject($prgrs_id);
        $usr_id = $prgrs->getUserId();
        if (!$this->mayCurrentUserEditProgress($prgrs_id)) {
            $msgs->add(false, 'No permission to edit progress of user', (string) $prgrs_id);
        } else {
            $programme = $this->getStudyProgramme();
            $programme->markAccredited($prgrs_id, $this->user->getId(), $msgs);
        }
    }

    protected function unmarkAccredited() : void
    {
        $prgrs_id = $this->getPrgrsId();
        $msgs = $this->getMessageCollection('msg_unmark_accredited');
        $this->unmarkAccreditedByProgressId($prgrs_id, $msgs);
        $this->showMessages($msgs);
        $this->ctrl->redirect($this, "view");
    }

    public function unmarkAccreditedMulti() : void
    {
        $prgrs_ids = $this->getPostPrgsIds();
        $msgs = $this->getMessageCollection('msg_unmark_accredited');
        foreach ($prgrs_ids as $key => $prgrs_id) {
            $this->unmarkAccreditedByProgressId((int) $prgrs_id, $msgs);
        }
        $this->showMessages($msgs);
        $this->ctrl->redirect($this, "view");
    }

    protected function unmarkAccreditedByProgressId(int $prgrs_id, ilPRGMessageCollection $msgs) : void
    {
        $prgrs = $this->getProgressObject($prgrs_id);
        $usr_id = $prgrs->getUserId();
        if (!$this->mayCurrentUserEditProgress($prgrs_id)) {
            $msgs->add(false, 'No permission to edit progress of user', (string) $prgrs_id);
        } else {
            $programme = $this->getStudyProgramme();
            $programme->unmarkAccredited($prgrs_id, $this->user->getId(), $msgs);
        }
    }

    public function markRelevantMulti() : void
    {
        $prgrs_ids = $this->getPostPrgsIds();
        $msgs = $this->getMessageCollection('msg_mark_relevant');
        $programme = $this->getStudyProgramme();
        foreach ($prgrs_ids as $key => $prgrs_id) {
            if (!$this->mayCurrentUserEditProgress($prgrs_id)) {
                $msgs->add(false, 'No permission to edit progress of user', (string) $prgrs_id);
            } else {
                $programme->markRelevant($prgrs_id, $this->user->getId(), $msgs);
            }
        }
        $this->showMessages($msgs);
        $this->ctrl->redirect($this, "view");
    }

    public function markNotRelevantMulti() : void
    {
        $prgrs_ids = $this->getPostPrgsIds();
        $msgs = $this->getMessageCollection('msg_mark_not_relevant');
        $programme = $this->getStudyProgramme();
        foreach ($prgrs_ids as $key => $prgrs_id) {
            if (!$this->mayCurrentUserEditProgress($prgrs_id)) {
                $msgs->add(false, 'No permission to edit progress of user', (string) $prgrs_id);
            } else {
                $programme->markNotRelevant($prgrs_id, $this->user->getId(), $msgs);
            }
        }
        $this->showMessages($msgs);
        $this->ctrl->redirect($this, "view");
    }

    public function updateFromCurrentPlanMulti() : void
    {
        $prgrs_ids = $this->getPostPrgsIds();
        $msgs = $this->getMessageCollection('msg_update_from_settings');
        foreach ($prgrs_ids as $key => $prgrs_id) {
            if (!$this->mayCurrentUserEditProgress($prgrs_id)) {
                $msgs->add(false, 'no_permission_to_update_plan_of_user', (string) $prgrs_id);
                continue;
            }
            
            $this->object->updatePlanFromRepository(
                $prgrs_id,
                $this->user->getId(),
                $msgs
            );
        }
        $this->showMessages($msgs);
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

    public function removeUser() : string
    {
        $prgrs_id = $this->getPrgrsId();
        return $this->confirmRemoveUsers([$prgrs_id]);
    }

    protected function removeUserMulti() : string
    {
        $prgrs_ids = $this->getPostPrgsIds();
        return $this->confirmRemoveUsers($prgrs_ids);
    }

    protected function confirmedRemoveUsers() : void
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
            $this->showSuccessMessage("remove_users_partial_success");
        } else {
            $this->showSuccessMessage("remove_users_success");
        }
        $this->ctrl->redirect($this, "view");
    }

    protected function confirmRemoveUsers(array $progress_ids) : string
    {
        $this->confirmation_gui->setFormAction($this->ctrl->getFormAction($this));
        $this->confirmation_gui->setHeaderText($this->lng->txt('confirm_to_remove_selected_assignments'));
        $this->confirmation_gui->setConfirm($this->lng->txt('prg_remove_user'), 'confirmedRemoveUsers');
        $this->confirmation_gui->setCancel($this->lng->txt('cancel'), 'view');

        foreach ($progress_ids as $progress_id) {
            $progress = $this->getProgressObject($progress_id);
            $user = ilObjUser::_lookupFullname($progress->getUserId());
            $name = $user . ' (' . $progress->getId() . ')';
            
            $this->confirmation_gui->addItem(
                self::F_SELECTED_PROGRESS_IDS . '[]',
                $progress_id,
                $name
            );
        }
        return $this->confirmation_gui->getHTML();
    }

    /**
     * Remove user
     */
    protected function remove(int $prgrs_id) : void
    {
        $prgrs = $this->getProgressObject($prgrs_id);

        if (!in_array(
            $prgrs->getUserId(),
            $this->permissions->getUserIdsSusceptibleTo(ilOrgUnitOperation::OP_MANAGE_MEMBERS)
        )) {
            throw new ilStudyProgrammePositionBasedAccessViolationException(
                'No permission to manage membership of user'
            );
        }

        $ass = $this->sp_user_assignment_db->get($prgrs->getAssignmentId());
        $prg_ref_id = ilObjStudyProgramme::getRefIdFor($ass->getRootId());
        if ($prg_ref_id != $this->ref_id) {
            throw new ilException("Can only remove users from the node they where assigned to.");
        }
        $prg = ilObjStudyProgramme::getInstanceByRefId($prg_ref_id);
        $prg->removeAssignment($ass);
    }

    /**
     * Get progress object for prgrs id
     */
    protected function getProgressObject(int $prgrs_id) : ilStudyProgrammeProgress
    {
        if (!array_key_exists($prgrs_id, $this->progress_objects)) {
            $this->progress_objects[$prgrs_id] = $this->sp_user_progress_db->get(
                $prgrs_id
            );
        }
        return $this->progress_objects[$prgrs_id];
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
            $this->lng->txt('mail_assignments'),
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
            case self::ACTION_MARK_ACCREDITED:
                $target_name = "markAccredited";
                break;
            case self::ACTION_UNMARK_ACCREDITED:
                $target_name = "unmarkAccredited";
                break;
            case self::ACTION_SHOW_INDIVIDUAL_PLAN:
                return $this->individual_plan_gui->getLinkTargetView($ass_id);
            case self::ACTION_REMOVE_USER:
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


    protected function mayCurrentUserEditProgress(int $progress_id) : bool
    {
        return in_array(
            $this->getProgressObject($progress_id)->getUserId(),
            $this->permissions->getUserIdsSusceptibleTo(ilOrgUnitOperation::OP_EDIT_INDIVIDUAL_PLAN)
        );
    }

    protected function getMessageCollection(string $topic) : ilPRGMessageCollection
    {
        return $this->messages->getMessageCollection($topic);
    }

    protected function showMessages(ilPRGMessageCollection $msg)
    {
        $this->messages->showMessages($msg);
    }
}
