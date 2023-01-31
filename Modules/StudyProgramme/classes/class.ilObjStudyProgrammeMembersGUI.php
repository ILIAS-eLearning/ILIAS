<?php

declare(strict_types=1);

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

use ILIAS\Data\Factory;

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
    use ilTableCommandHelper;

    private const DEFAULT_CMD = "view";

    public const ACTION_MARK_ACCREDITED = "mark_accredited";
    public const ACTION_UNMARK_ACCREDITED = "unmark_accredited";
    public const ACTION_SHOW_INDIVIDUAL_PLAN = "show_individual_plan";
    public const ACTION_REMOVE_USER = "remove_user";
    public const ACTION_CHANGE_DEADLINE = "change_deadline";

    public const F_COMMAND_OPTION_ALL = 'select_cmd_all';
    public const F_ALL_PROGRESS_IDS = 'all_progress_ids';
    public const F_SELECTED_PROGRESS_IDS = 'prgs_ids';
    public const F_SELECTED_USER_IDS = 'usrids';

    protected ilGlobalTemplateInterface $tpl;
    protected ilCtrl $ctrl;
    protected ilToolbarGUI $toolbar;
    protected ilLanguage $lng;
    protected ilObjUser $user;
    protected ilTabsGUI $tabs;
    protected ilPRGAssignmentDBRepository $assignment_db;
    protected ilStudyProgrammeRepositorySearchGUI $repository_search_gui;
    protected ilObjStudyProgrammeIndividualPlanGUI $individual_plan_gui;
    protected ilPRGMessagePrinter $messages;
    protected Factory $data_factory;
    protected ilConfirmationGUI $confirmation_gui;
    protected ILIAS\HTTP\Wrapper\WrapperFactory $http_wrapper;
    protected ILIAS\Refinery\Factory $refinery;
    protected ?ilObjStudyProgramme $object;
    protected ?ilPRGPermissionsHelper $permissions;
    protected ilObjectGUI $parent_gui;
    protected int $ref_id;

    public function __construct(
        ilGlobalTemplateInterface $tpl,
        ilCtrl $ilCtrl,
        ilToolbarGUI $ilToolbar,
        ilLanguage $lng,
        ilObjUser $user,
        ilTabsGUI $tabs,
        ilPRGAssignmentDBRepository $assignment_db,
        ilStudyProgrammeRepositorySearchGUI $repository_search_gui,
        ilObjStudyProgrammeIndividualPlanGUI $individual_plan_gui,
        ilPRGMessagePrinter $messages,
        Factory $data_factory,
        ilConfirmationGUI $confirmation_gui,
        ILIAS\HTTP\Wrapper\WrapperFactory $http_wrapper,
        ILIAS\Refinery\Factory $refinery
    ) {
        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->toolbar = $ilToolbar;
        $this->lng = $lng;
        $this->user = $user;
        $this->tabs = $tabs;
        $this->assignment_db = $assignment_db;
        $this->repository_search_gui = $repository_search_gui;
        $this->individual_plan_gui = $individual_plan_gui;
        $this->messages = $messages;
        $this->data_factory = $data_factory;
        $this->confirmation_gui = $confirmation_gui;
        $this->http_wrapper = $http_wrapper;
        $this->refinery = $refinery;
        $this->object = null;
        $this->permissions = null;

        $lng->loadLanguageModule("prg");
        $this->toolbar->setPreventDoubleSubmission(true);
    }

    public function setParentGUI(ilObjectGUI $a_parent_gui): void
    {
        $this->parent_gui = $a_parent_gui;
    }

    public function setRefId(int $ref_id): void
    {
        $this->ref_id = $ref_id;
        $this->object = ilObjStudyProgramme::getInstanceByRefId($ref_id);
        $this->permissions = ilStudyProgrammeDIC::specificDicFor($this->object)['permissionhelper'];
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass($this);

        if ($cmd === "" || $cmd === null) {
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
                    case "confirmedUpdateFromCurrentPlan":
                        $this->confirmedUpdateFromCurrentPlan();
                        break;
                    case "mailUserMulti":
                        $this->mailToSelectedUsers();
                        break;

                    default:
                        throw new ilException("ilObjStudyProgrammeMembersGUI: Command not supported: $cmd");
                }
                break;
            default:
                throw new ilException(
                    "ilObjStudyProgrammeMembersGUI: Can't forward to next class $next_class"
                );
        }
    }

    protected function getDefaultCommand(): string
    {
        return self::DEFAULT_CMD;
    }

    protected function getAssignmentsById(): array
    {
        return $this->assignment_db->getAllForNodeIsContained($this->object->getId());
    }

    protected function getMembersTableGUI(): ilStudyProgrammeMembersTableGUI
    {
        $prg_id = ilObject::_lookupObjId($this->ref_id);
        $dic = ilStudyProgrammeDIC::specificDicFor($this->object);
        $table = new ilStudyProgrammeMembersTableGUI(
            $prg_id,
            $this->ref_id,
            $this,
            $this->permissions,
            $this->data_factory,
            $dic['ui.factory'],
            $dic['ui.renderer'],
            $dic['ilStudyProgrammeUserTable'],
            $dic['filter.assignment'],
            $this->user,
            "view",
            ""
        );
        return $table;
    }

    /**
     * @return int[]
     */
    protected function getPostPrgsIds(): array
    {
        if ($this->http_wrapper->post()->has(self::F_COMMAND_OPTION_ALL)) {
            $pgs_ids = $this->http_wrapper->post()->retrieve(
                self::F_ALL_PROGRESS_IDS,
                $this->refinery->custom()->transformation(
                    fn ($ids) => explode(',', $ids)
                )
            );
        } else {
            $pgs_ids = $this->http_wrapper->post()->retrieve(
                self::F_SELECTED_PROGRESS_IDS,
                $this->refinery->custom()->transformation(fn ($ids) => $ids)
            );
        }
        if ($pgs_ids === null) {
            $this->showInfoMessage("no_user_selected");
            $this->ctrl->redirect($this, "view");
        }

        $r = [];
        foreach ($pgs_ids as $pgs_id) {
            $r[] = PRGProgressId::createFromString($pgs_id);
        }
        return $r;
    }

    protected function getGetPrgsIds(): array
    {
        $prgrs_ids = $_GET['prgrs_ids'];
        $ids = [];
        if (!is_null($prgrs_ids)) {
            foreach (explode(',', $prgrs_ids) as $id) {
                $ids[] = PRGProgressId::createFromString($id);
            };
        }
        return $ids;
    }

    protected function getPrgrsId(): PRGProgressId
    {
        if (!$_GET["prgrs_id"]) {
            throw new ilException("Expected 'prgrs_id'");
        }
        return PRGProgressId::createFromString($_GET["prgrs_id"]);
    }

    /**
     * Shows table with all members of the SP
     */
    protected function view(): string
    {
        if ($this->getStudyProgramme()->isActive() && $this->permissions->may(ilOrgUnitOperation::OP_MANAGE_MEMBERS)) {
            $this->initSearchGUI();
            $this->initMailToMemberButton($this->toolbar, true);
        }

        if (!$this->getStudyProgramme()->isActive()) {
            $this->tpl->setOnScreenMessage("info", $this->lng->txt("prg_no_members_not_active"));
        }
        $table = $this->getMembersTableGUI();
        return $table->getHTML();
    }

    public function applyFilter(): void
    {
        $table = $this->getMembersTableGUI();
        $table->resetOffset();
        $table->writeFilterToSession();
        $this->ctrl->redirect($this, "view");
    }

    public function resetFilter(): void
    {
        $table = $this->getMembersTableGUI();
        $table->resetOffset();
        $table->resetFilter();
        $this->ctrl->redirect($this, "view");
    }

    /**
     * Assigns a users to SP
     * @param string[] $user_ids
     * @return bool|void
     */
    public function addUsers(array $user_ids)
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
     * @param int[] $completed_courses
     * @param int[] $users
     */
    public function viewCompletedCourses(array $completed_courses, array $users): void
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

        foreach ($completed_courses as $user_id => $completed) {
            $names = ilObjUser::_lookupName($user_id);
            $tpl->setCurrentBlock("usr_section");
            $tpl->setVariable("FIRSTNAME", $names["firstname"]);
            $tpl->setVariable("LASTNAME", $names["lastname"]);
            $table = new ilStudyProgrammeAcknowledgeCompletedCoursesTableGUI(
                $this,
                $user_id,
                $completed
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
    public function addUsersWithAcknowledgedCourses(): void
    {
        $users = $this->http_wrapper->post()->retrieve(
            "users",
            $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
        );
        $users = $this->getAddableUsers($users);
        $assignments = $this->_addUsers($users);

        $completed_programmes = null;
        if ($this->http_wrapper->post()->has('courses')) {
            $completed_programmes = $this->http_wrapper->post()->retrieve(
                "courses",
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string())
                )
            );
        }

        if (is_array($completed_programmes)) {
            foreach ($completed_programmes as $user_id => $prg_ref_ids) {
                $ass = $assignments[$user_id];
                foreach ($prg_ref_ids as $ids) {
                    [$prg_ref_id, $crs_id, $crsr_id] = explode(";", $ids);
                    $prg = $this->getStudyProgramme((int) $prg_ref_id);
                    if ($prg->isActive()) {
                        $prg->succeed($user_id, (int)$crsr_id, $ass);
                    }
                }
            }
        }

        $this->ctrl->redirect($this, "view");
    }

    /**
     * @param int[] $users
     */
    protected function getAddableUsers(array $users): array
    {
        $to_add = $this->permissions->filterUserIds(
            $users,
            ilOrgUnitOperation::OP_MANAGE_MEMBERS
        );

        $cnt_not_added = count($users) - count($to_add);
        if ($cnt_not_added > 0) {
            $this->tpl->setOnScreenMessage(
                "info",
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
     * @param string[] $user_ids
     * @return array <string, ilStudyProgrammeAssignment>
     */
    protected function _addUsers(array $user_ids): array
    {
        $prg = $this->getStudyProgramme();
        $assignments = array();

        foreach ($user_ids as $user_id) {
            $assignments[$user_id] = $prg->assignUser((int) $user_id);
        }

        if (count($assignments) === 1) {
            $this->tpl->setOnScreenMessage("success", $this->lng->txt("prg_added_member"), true);
        }
        if (count($assignments) > 1) {
            $this->tpl->setOnScreenMessage("success", $this->lng->txt("prg_added_members"), true);
        }

        return $assignments;
    }

    protected function markAccredited(): void
    {
        $prgrs_id = $this->getPrgrsId();
        $msgs = $this->getMessageCollection('msg_mark_accredited');
        $this->markAccreditedByProgressId($prgrs_id, $msgs);
        $this->showMessages($msgs);
        $this->ctrl->redirect($this, "view");
    }

    protected function markAccreditedMulti(): void
    {
        $prgrs_ids = $this->getPostPrgsIds();
        $msgs = $this->getMessageCollection('msg_mark_accredited');
        foreach ($prgrs_ids as $key => $prgrs_id) {
            $this->markAccreditedByProgressId($prgrs_id, $msgs);
        }
        $this->showMessages($msgs);
        $this->ctrl->redirect($this, "view");
    }

    protected function markAccreditedByProgressId(PRGProgressId $prgrs_id, ilPRGMessageCollection $msgs): void
    {
        $usr_id = $prgrs_id->getUsrId();
        if (!$this->mayCurrentUserEditProgressForUser($usr_id)) {
            $msgs->add(false, 'No permission to edit progress of user', (string) $prgrs_id);
        } else {
            $programme = $this->getStudyProgramme();
            $programme->markAccredited($prgrs_id->getAssignmentId(), $this->user->getId(), $msgs);
        }
    }

    protected function unmarkAccredited(): void
    {
        $prgrs_id = $this->getPrgrsId();
        $msgs = $this->getMessageCollection('msg_unmark_accredited');
        $this->unmarkAccreditedByProgressId($prgrs_id, $msgs);
        $this->showMessages($msgs);
        $this->ctrl->redirect($this, "view");
    }

    public function unmarkAccreditedMulti(): void
    {
        $prgrs_ids = $this->getPostPrgsIds();
        $msgs = $this->getMessageCollection('msg_unmark_accredited');
        foreach ($prgrs_ids as $key => $prgrs_id) {
            $this->unmarkAccreditedByProgressId($prgrs_id, $msgs);
        }
        $this->showMessages($msgs);
        $this->ctrl->redirect($this, "view");
    }

    protected function unmarkAccreditedByProgressId(PRGProgressId $prgrs_id, ilPRGMessageCollection $msgs): void
    {
        $usr_id = $prgrs_id->getUsrId();
        if (!$this->mayCurrentUserEditProgressForUser($usr_id)) {
            $msgs->add(false, 'No permission to edit progress of user', (string) $prgrs_id);
        } else {
            $programme = $this->getStudyProgramme();
            $programme->unmarkAccredited($prgrs_id->getAssignmentId(), $this->user->getId(), $msgs);
        }
    }

    public function markRelevantMulti(): void
    {
        $prgrs_ids = $this->getPostPrgsIds();
        $msgs = $this->getMessageCollection('msg_mark_relevant');
        $programme = $this->getStudyProgramme();
        foreach ($prgrs_ids as $key => $prgrs_id) {
            if (!$this->mayCurrentUserEditProgressForUser($prgrs_id->getUsrId())) {
                $msgs->add(false, 'No permission to edit progress of user', (string) $prgrs_id);
            } else {
                $programme->markRelevant($prgrs_id->getAssignmentId(), $this->user->getId(), $msgs);
            }
        }
        $this->showMessages($msgs);
        $this->ctrl->redirect($this, "view");
    }

    public function markNotRelevantMulti(): void
    {
        $prgrs_ids = $this->getPostPrgsIds();
        $msgs = $this->getMessageCollection('msg_mark_not_relevant');
        $programme = $this->getStudyProgramme();
        foreach ($prgrs_ids as $key => $prgrs_id) {
            if (!$this->mayCurrentUserEditProgressForUser($prgrs_id->getUsrId())) {
                $msgs->add(false, 'No permission to edit progress of user', (string) $prgrs_id);
            } else {
                $programme->markNotRelevant($prgrs_id->getAssignmentId(), $this->user->getId(), $msgs);
            }
        }
        $this->showMessages($msgs);
        $this->ctrl->redirect($this, "view");
    }

    public function updateFromCurrentPlanMulti(): string
    {
        $this->confirmation_gui->setFormAction($this->ctrl->getFormAction($this, 'confirmUpdateFromCurrentPlan'));
        $this->confirmation_gui->setHeaderText($this->lng->txt('header_update_current_plan'));
        $this->confirmation_gui->setConfirm($this->lng->txt('confirm'), 'confirmedUpdateFromCurrentPlan');
        $this->confirmation_gui->setCancel($this->lng->txt('cancel'), 'view');

        foreach ($this->getPostPrgsIds() as $progress_id) {
            $user_name = ilObjUser::_lookupFullname($progress_id->getUsrId());
            $this->confirmation_gui->addItem(
                self::F_SELECTED_PROGRESS_IDS . '[]',
                (string)$progress_id,
                $user_name
            );
        }
        return $this->confirmation_gui->getHTML();
    }

    public function confirmedUpdateFromCurrentPlan()
    {
        $pgs_ids = $this->getPostPrgsIds();
        $msgs = $this->getMessageCollection('msg_update_from_settings');
        foreach ($pgs_ids as $idx => $pgs_id) {
            if (!$this->mayCurrentUserEditProgressForUser($pgs_id->getUsrId())) {
                $msgs->add(false, 'no_permission_to_update_plan_of_user', (string) $pgs_id);
                continue;
            } else {
                $msgs->add(true, '', (string) $pgs_id);
            }

            $this->object->updatePlanFromRepository(
                $pgs_id->getAssignmentId(),
                $this->user->getId(),
                $msgs
            );
        }
        $this->showMessages($msgs);
        $this->ctrl->redirect($this, "view");
    }

    public function changeDeadlineMulti(): void
    {
        $this->ctrl->setParameterByClass(
            'ilStudyProgrammeChangeDeadlineGUI',
            'prgrs_ids',
            implode(',', $this->getPostPrgsIds())
        );

        $link = $this->ctrl->getLinkTargetByClass(
            'ilStudyProgrammeChangeDeadlineGUI',
            'showDeadlineConfig'
        );

        $this->ctrl->clearParameterByClass('ilStudyProgrammeChangeDeadlineGUI', 'prgrs_ids');
        $this->ctrl->redirectToURL($link);
    }

    public function changeExpireDateMulti(): void
    {
        $this->ctrl->setParameterByClass(
            'ilStudyProgrammeChangeExpireDateGUI',
            'prgrs_ids',
            implode(',', $this->getPostPrgsIds())
        );

        $link = $this->ctrl->getLinkTargetByClass(
            'ilStudyProgrammeChangeExpireDateGUI',
            'showExpireDateConfig'
        );

        $this->ctrl->clearParameterByClass('ilStudyProgrammeChangeExpireDateGUI', 'prgrs_ids');
        $this->ctrl->redirectToURL($link);
    }

    public function removeUser(): string
    {
        $prgrs_id = $this->getPrgrsId();
        return $this->confirmRemoveUsers([$prgrs_id]);
    }

    protected function removeUserMulti(): string
    {
        $pgs_ids = $this->getPostPrgsIds();
        return $this->confirmRemoveUsers($pgs_ids);
    }

    protected function confirmRemoveUsers(array $progress_ids): string
    {
        $this->confirmation_gui->setFormAction($this->ctrl->getFormAction($this));
        $this->confirmation_gui->setHeaderText($this->lng->txt('confirm_to_remove_selected_assignments'));
        $this->confirmation_gui->setConfirm($this->lng->txt('prg_remove_user'), 'confirmedRemoveUsers');
        $this->confirmation_gui->setCancel($this->lng->txt('cancel'), 'view');

        foreach ($progress_ids as $progress_id) {
            $user_name = ilObjUser::_lookupFullname($progress_id->getUsrId());
            $this->confirmation_gui->addItem(
                self::F_SELECTED_PROGRESS_IDS . '[]',
                (string)$progress_id,
                $user_name
            );
        }
        return $this->confirmation_gui->getHTML();
    }

    protected function confirmedRemoveUsers(): void
    {
        $pgs_ids = $this->getPostPrgsIds();
        $not_removed = array();
        foreach ($pgs_ids as $idx => $pgs_id) {
            try {
                $this->removeAssignment($pgs_id);
            } catch (ilException $e) {
                $not_removed[] = $pgs_id;
            }
        }
        if (count($not_removed) === count($pgs_ids)) {
            $this->showInfoMessage("remove_users_not_possible");
        } elseif (count($not_removed) > 0) {
            $this->showSuccessMessage("remove_users_partial_success");
        } else {
            $this->showSuccessMessage("remove_users_success");
        }
        $this->ctrl->redirect($this, "view");
    }

    /**
     * Remove user
     */
    protected function removeAssignment(PRGProgressId $pgs_id): void
    {
        if (!in_array(
            $pgs_id->getUsrId(),
            $this->permissions->getUserIdsSusceptibleTo(ilOrgUnitOperation::OP_MANAGE_MEMBERS)
        )) {
            throw new ilStudyProgrammePositionBasedAccessViolationException(
                'No permission to manage membership of user'
            );
        }

        $ass = $this->assignment_db->get($pgs_id->getAssignmentId());
        $prg_ref_id = ilObjStudyProgramme::getRefIdFor($ass->getRootId());
        if ($prg_ref_id !== $this->ref_id) {
            throw new ilException("Can only remove users from the node they where assigned to.");
        }
        $prg = ilObjStudyProgramme::getInstanceByRefId($prg_ref_id);
        $prg->removeAssignment($ass);
    }

    /**
     * Shows ilUtil success message
     */
    protected function showSuccessMessage(string $lng_var): void
    {
        $this->tpl->setOnScreenMessage("success", $this->lng->txt("prg_$lng_var"), true);
    }

    /**
     * Shows ilUtil failed message
     */
    protected function showInfoMessage(string $lng_var): void
    {
        $this->tpl->setOnScreenMessage("info", $this->lng->txt("prg_$lng_var"), true);
    }

    protected function initSearchGUI(): void
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

    protected function initMailToMemberButton(ilToolbarGUI $toolbar, bool $separator = false): void
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
    public function getStudyProgramme(int $ref_id = null): ilObjStudyProgramme
    {
        if ($ref_id === null) {
            $ref_id = $this->ref_id;
        }
        return ilObjStudyProgramme::getInstanceByRefId($ref_id);
    }

    /**
     * Get the link target for an action on user progress.
     */
    public function getLinkTargetForAction(string $action, string $prgrs_id, int $ass_id): string
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

    protected function mayCurrentUserEditProgressForUser(int $usr_id): bool
    {
        return in_array(
            $usr_id,
            $this->permissions->getUserIdsSusceptibleTo(ilOrgUnitOperation::OP_EDIT_INDIVIDUAL_PLAN)
        );
    }

    protected function getMessageCollection(string $topic): ilPRGMessageCollection
    {
        return $this->messages->getMessageCollection($topic);
    }

    protected function showMessages(ilPRGMessageCollection $msg): void
    {
        $this->messages->showMessages($msg);
    }

    protected function mailToSelectedUsers(): void
    {
        $dic = ilStudyProgrammeDIC::dic();
        $gui = $dic['ilStudyProgrammeMailMemberSearchGUI'];

        $selected = $this->getPostPrgsIds();
        $selected_ids = array_map(
            fn ($id) => $id->getAssignmentId(),
            $selected
        );

        $assignments = array_filter(
            $this->getAssignmentsById(),
            fn ($ass) => in_array($ass->getId(), $selected_ids)
        );
        $gui->setAssignments($assignments);
        $this->tabs->clearTargets();
        $this->tabs->setBackTarget(
            $this->lng->txt('btn_back'),
            $this->ctrl->getLinkTarget($this, $this->getDefaultCommand())
        );
        $this->ctrl->forwardCommand($gui);
    }
}
