<?php

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

declare(strict_types=1);

use ILIAS\Data\Factory;
use ILIAS\UI\Component\Input\Container\Form\Standard as Form;

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
    use ilPRGCertificateHelper;

    private const DEFAULT_CMD = "view";

    public const ACTION_MARK_ACCREDITED = "mark_accredited";
    public const ACTION_UNMARK_ACCREDITED = "unmark_accredited";
    public const ACTION_SHOW_INDIVIDUAL_PLAN = "show_individual_plan";
    public const ACTION_REMOVE_USER = "remove_user";
    public const ACTION_CHANGE_DEADLINE = "change_deadline";
    public const ACTION_MARK_RELEVANT = "mark_relevant";
    public const ACTION_UNMARK_RELEVANT = "unmark_relevant";
    public const ACTION_UPDATE_FROM_CURRENT_PLAN = "update_from_current_plan";
    public const ACTION_CHANGE_EXPIRE_DATE = "change_expire_date";
    public const ACTION_UPDATE_CERTIFICATE = "update_certificate";
    public const ACTION_ACKNOWLEDGE_COURSES = "acknowledge_completed_courses";
    public const ACTION_REMOVE_CERTIFICATE = "remove_certificate";

    public const F_COMMAND_OPTION_ALL = 'select_cmd_all';
    public const F_ALL_PROGRESS_IDS = 'all_progress_ids';
    public const F_SELECTED_PROGRESS_IDS = 'prgs_ids';
    public const F_SELECTED_USER_IDS = 'usrids';

    protected ?ilObjStudyProgramme $object;
    protected ?ilPRGPermissionsHelper $permissions;
    protected ilObjectGUI $parent_gui;
    protected int $ref_id;

    public function __construct(
        protected ilGlobalTemplateInterface $tpl,
        protected ilCtrl $ctrl,
        protected ilToolbarGUI $toolbar,
        protected ilLanguage $lng,
        protected ilObjUser $user,
        protected ilTabsGUI $tabs,
        protected ilPRGAssignmentDBRepository $assignment_db,
        protected ilStudyProgrammeRepositorySearchGUI $repository_search_gui,
        protected ilObjStudyProgrammeIndividualPlanGUI $individual_plan_gui,
        protected ilPRGMessagePrinter $messages,
        protected Factory $data_factory,
        protected ilConfirmationGUI $confirmation_gui,
        protected ILIAS\HTTP\Wrapper\WrapperFactory $http_wrapper,
        protected ILIAS\Refinery\Factory $refinery,
        protected ILIAS\UI\Factory $ui_factory,
        protected ILIAS\UI\Renderer $ui_renderer,
        protected GuzzleHttp\Psr7\ServerRequest $request,
    ) {
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
                $dic = ilStudyProgrammeDIC::specificDicFor($this->object);
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
                    case "markNotRelevantMulti":
                    case "markRelevant":
                    case "markRelevantMulti":
                    case "updateFromCurrentPlan":
                    case "updateFromCurrentPlanMulti":
                    case "acknowledgeCourses":
                    case "acknowledgeCoursesMulti":
                    case "applyFilter":
                    case "resetFilter":
                    case "changeDeadline":
                    case "changeDeadlineMulti":
                    case "changeExpireDate":
                    case "changeExpireDateMulti":
                    case "updateCertificate":
                    case "updateCertificateMulti":
                    case "removeCertificate":
                    case "removeCertificateMulti":
                        $cont = $this->$cmd();
                        $this->tpl->setContent($cont);
                        break;
                    case "confirmedRemoveUsers":
                        $this->confirmedRemoveUsers();
                        break;
                    case "confirmedUpdateFromCurrentPlan":
                        $this->confirmedUpdateFromCurrentPlan();
                        break;
                    case "confirmedAcknowledgeCourses":
                        $this->confirmedAcknowledgeCourses();
                        break;
                    case "confirmedAcknowledgeAllCourses":
                        $this->confirmedAcknowledgeAllCourses();
                        break;

                    case "mailUserMulti":
                        $this->mailToSelectedUsers();
                        break;
                    case "markNotRelevant":
                        $this->markNotRelevant();
                        break;
                    case "confirmedUpdateCertificate":
                        $this->confirmedUpdateCertificate();
                        break;
                    case "confirmedRemovalOfCertificate":
                        $this->confirmedRemovalOfCertificate();
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
     * @return PRGProgressId[]
     */
    protected function getPostPrgsIds(): array
    {
        if ($this->http_wrapper->post()->has(self::F_COMMAND_OPTION_ALL)) {
            $pgs_ids = $this->http_wrapper->post()->retrieve(
                self::F_ALL_PROGRESS_IDS,
                $this->refinery->custom()->transformation(
                    fn($ids) => explode(',', $ids)
                )
            );
        } else {
            $pgs_ids = $this->http_wrapper->post()->retrieve(
                self::F_SELECTED_PROGRESS_IDS,
                $this->refinery->custom()->transformation(fn($ids) => $ids)
            );
        }
        if ($pgs_ids === null) {
            $this->showInfoMessage("no_user_selected");
            $this->ctrl->redirect($this, "view");
        }
        if (is_string($pgs_ids)) {
            $pgs_ids = [$pgs_ids];
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
        $user_ids = $this->getAddableUsers($user_ids);
        $prg = $this->getStudyProgramme();
        $assignments = [];
        $with_courses = [];

        foreach ($user_ids as $user_id) {
            $ass = $prg->assignUser((int) $user_id);
            $assignments[] = $ass;
            if($prg->getCompletedCourses((int) $user_id)) {
                $with_courses[] = $ass;
            }
        }

        if (count($assignments) === 1) {
            $this->tpl->setOnScreenMessage("success", $this->lng->txt("prg_added_member"), true);
        }
        if (count($assignments) > 1) {
            $this->tpl->setOnScreenMessage("success", $this->lng->txt("prg_added_members"), true);
        }

        if($with_courses) {
            $this->tpl->setContent(
                $this->ui_renderer->render(
                    $this->viewCompletedCourses($assignments)
                )
            );
            return true;

        } else {
            $this->ctrl->redirect($this, "view");
        }
    }

    /**
     * Shows list of completed courses for each assignment
     */
    public function viewCompletedCourses(array $assignments): Form
    {
        $prg = $this->getStudyProgramme();
        $completed_courses = [];
        $ass_ids = [];
        foreach ($assignments as $ass) {
            $ass_ids[] = $ass->getId();
            $completed_crss = $prg->getCompletedCourses($ass->getUserId());

            $label = sprintf(
                "%s (%s)",
                $ass->getUserInformation()->getFullname(),
                $ass->getId()
            );
            $options = [];
            foreach($completed_crss as $opt) {
                $options[implode(';', [$ass->getId(), $opt['prg_obj_id'],$opt['crsr_id']])] = $opt['title'];
            }

            $completed_courses[] = $this->ui_factory->input()->field()->multiselect($label, $options);
        }

        $form_action = $this->ctrl->getFormAction($this, 'confirmedAcknowledgeCourses')
            . '&ass_ids=' . implode(',', $ass_ids);

        $form = $this->ui_factory->input()->container()->form()->standard(
            $form_action,
            [
                $this->ui_factory->input()->field()->section(
                    $completed_courses,
                    $this->lng->txt("prg_acknowledge_completed_courses")
                )
            ]
        )->withAdditionalTransformation(
            $this->refinery->custom()->transformation(
                function ($values) {
                    $values = array_merge(...array_filter(array_shift($values)));
                    return array_map(
                        fn($entry) => explode(';', $entry),
                        $values
                    );
                }
            )
        )->withSubmitLabel(
            $this->lng->txt("btn_next")
        );

        $this->toolbar->addComponent(
            $this->ui_factory->link()->standard(
                $this->lng->txt('prg_cancel_acknowledge_completed_courses'),
                $this->ctrl->getLinkTarget($this, self::DEFAULT_CMD)
            )
        );
        $this->toolbar->addComponent(
            $this->ui_factory->link()->standard(
                $this->lng->txt('prg_acknowledge_all_completed_courses'),
                $this->ctrl->getLinkTarget($this, 'confirmedAcknowledgeAllCourses')
                . '&ass_ids=' . implode(',', $ass_ids)
            )
        );

        return $form;
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

    public function markRelevant(): void
    {
        $prgrs_id = $this->getPrgrsId();
        $msgs = $this->getMessageCollection('msg_mark_relevant');
        $programme = $this->getStudyProgramme();
        if (!$this->mayCurrentUserEditProgressForUser($prgrs_id->getUsrId())) {
            $msgs->add(false, "No permission to edit progress of user", (string) $prgrs_id);
        } else {
            $programme->markRelevant($prgrs_id->getAssignmentId(), $this->user->getId(), $msgs);
        }
        $this->showMessages($msgs);
        $this->ctrl->redirect($this, "view");
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

    public function markNotRelevant(): void
    {
        $prgrs_id = $this->getPrgrsId();
        $msgs = $this->getMessageCollection('msg_mark_not_relevant');
        $programme = $this->getStudyProgramme();
        if (!$this->mayCurrentUserEditProgressForUser($prgrs_id->getUsrId())) {
            $msgs->add(false, 'No permission to edit progress of user', (string) $prgrs_id);
        } else {
            $programme->markNotRelevant($prgrs_id->getAssignmentId(), $this->user->getId(), $msgs);
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

    public function updateFromCurrentPlan(): string
    {
        $this->confirmation_gui->setFormAction($this->ctrl->getFormAction($this, 'confirmUpdateFromCurrentPlan'));
        $this->confirmation_gui->setHeaderText($this->lng->txt('header_update_current_plan'));
        $this->confirmation_gui->setConfirm($this->lng->txt('confirm'), 'confirmedUpdateFromCurrentPlan');
        $this->confirmation_gui->setCancel($this->lng->txt('cancel'), 'view');

        $prgs_id = $this->getPrgrsId();
        $user_name = ilObjUser::_lookupFullname($prgs_id->getUsrId());
        $this->confirmation_gui->addItem(
            self::F_SELECTED_PROGRESS_IDS,
            (string) $prgs_id,
            $user_name
        );
        return $this->confirmation_gui->getHTML();
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
                (string) $progress_id,
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

    public function acknowledgeCourses(): string
    {
        $progress_id = $this->getPrgrsId();
        $assignments = [
            $this->assignment_db->get($progress_id->getAssignmentId())
        ];
        return $this->ui_renderer->render(
            $this->viewCompletedCourses($assignments)
        );
    }

    public function acknowledgeCoursesMulti(): string
    {
        $assignments = [];
        foreach ($this->getPostPrgsIds() as $progress_id) {
            $assignments[] = $this->assignment_db->get($progress_id->getAssignmentId());
        }
        return $this->ui_renderer->render(
            $this->viewCompletedCourses($assignments)
        );
    }

    /**
     * @return ilPRGAssignment[]
     */
    protected function getAssignmentsFromQuery(): array
    {
        $ass_ids = $this->http_wrapper->query()->retrieve(
            'ass_ids',
            $this->refinery->custom()->transformation(fn($ids) => explode(',', $ids))
        );
        $prg = $this->getStudyProgramme();
        $assignments = array_map(
            fn($ass_id) => $this->assignment_db->get((int) $ass_id),
            $ass_ids
        );

        $assignments = array_filter(
            $assignments,
            fn($ass) => $ass->getRootId() === $prg->getId()
        );
        return $assignments;
    }

    public function confirmedAcknowledgeAllCourses()
    {
        $prg = $this->getStudyProgramme();
        $assignments = $this->getAssignmentsFromQuery();
        $msgs = $this->getMessageCollection('msg_acknowledge_courses');

        foreach ($assignments as $ass) {
            $ass_ids[] = $ass->getId();
            $completed_crss = $prg->getCompletedCourses($ass->getUserId());
            $nodes = [];
            foreach($completed_crss as $opt) {
                $nodes[] = [$opt['prg_obj_id'], $opt['crsr_id']];
            }
            $prg->acknowledgeCourses(
                $ass->getId(),
                $nodes,
                $msgs
            );
        }
        $this->showMessages($msgs);
        $this->ctrl->redirect($this, "view");
    }

    public function confirmedAcknowledgeCourses()
    {
        $assignments = $this->getAssignmentsFromQuery();

        $form = $this->viewCompletedCourses($assignments)->withRequest($this->request);
        $data = $form->getData();

        $msgs = $this->getMessageCollection('msg_acknowledge_courses');

        if($data) {
            $acknowledge = [];
            foreach ($data as $ack) {
                [$assignment_id, $node_obj_id, $courseref_obj_id] = $ack;
                if(! array_key_exists($assignment_id, $acknowledge)) {
                    $acknowledge[$assignment_id] = [];
                }
                $acknowledge[$assignment_id][] = [(int) $node_obj_id, (int) $courseref_obj_id];
            }
            foreach ($acknowledge as $ass_id => $nodes) {
                $this->object->acknowledgeCourses(
                    (int) $ass_id,
                    $nodes,
                    $msgs
                );
            }
            $this->showMessages($msgs);
        }
        $this->ctrl->redirect($this, "view");
    }

    public function changeDeadline(): void
    {
        $this->ctrl->setParameterByClass(
            'ilStudyProgrammeChangeDeadlineGUI',
            'prgrs_ids',
            $this->getPrgrsId()
        );

        $link = $this->ctrl->getLinkTargetByClass(
            'ilStudyProgrammeChangeDeadlineGUI',
            'showDeadlineConfig'
        );

        $this->ctrl->clearParameterByClass('ilStudyProgrammeChangeDeadlineGUI', 'prgrs_ids');
        $this->ctrl->redirectToURL($link);
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

    public function changeExpireDate(): void
    {
        $this->ctrl->setParameterByClass(
            'ilStudyProgrammeChangeExpireDateGUI',
            'prgrs_ids',
            $this->getPrgrsId()
        );

        $link = $this->ctrl->getLinkTargetByClass(
            'ilStudyProgrammeChangeExpireDateGUI',
            'showExpireDateConfig'
        );

        $this->ctrl->clearParameterByClass('ilStudyProgrammeChangeExpireDateGUI', 'prgrs_ids');
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
                (string) $progress_id,
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

        $toolbar->addComponent(
            $this->ui_factory->link()->standard(
                $this->lng->txt('mail_assignments'),
                $this->ctrl->getLinkTargetByClass(
                    'ilStudyProgrammeMailMemberSearchGUI',
                    'showSelectableUsers'
                )
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
            case self::ACTION_UNMARK_RELEVANT:
                $target_name = "markNotRelevant";
                break;
            case self::ACTION_MARK_RELEVANT:
                $target_name = "markRelevant";
                break;
            case self::ACTION_UPDATE_FROM_CURRENT_PLAN:
                $target_name = "updateFromCurrentPlan";
                break;
            case self::ACTION_ACKNOWLEDGE_COURSES:
                $target_name = "acknowledgeCourses";
                break;
            case self::ACTION_CHANGE_DEADLINE:
                $target_name = "changeDeadline";
                break;
            case self::ACTION_CHANGE_EXPIRE_DATE:
                $target_name = "changeExpireDate";
                break;
            case self::ACTION_UPDATE_CERTIFICATE:
                $target_name = "updateCertificate";
                break;
            case self::ACTION_REMOVE_CERTIFICATE:
                $target_name = "removeCertificate";
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
        $dic = ilStudyProgrammeDIC::specificDicFor($this->object);
        $gui = $dic['ilStudyProgrammeMailMemberSearchGUI'];
        $selected = $this->getPostPrgsIds();
        $selected_ids = array_map(
            fn($id) => $id->getAssignmentId(),
            $selected
        );

        $assignments = array_filter(
            $this->getAssignmentsById(),
            fn($ass) => in_array($ass->getId(), $selected_ids)
        );
        $gui->setAssignments($assignments);
        $this->tabs->clearTargets();
        $this->tabs->setBackTarget(
            $this->lng->txt('btn_back'),
            $this->ctrl->getLinkTarget($this, $this->getDefaultCommand())
        );
        $this->ctrl->forwardCommand($gui);
    }

    public function updateCertificate(): string
    {
        $this->confirmation_gui->setFormAction($this->ctrl->getFormAction($this, 'confirmUpdateCertificate'));
        $this->confirmation_gui->setHeaderText($this->lng->txt('header_update_certificate'));
        $this->confirmation_gui->setConfirm($this->lng->txt('confirm'), 'confirmedUpdateCertificate');
        $this->confirmation_gui->setCancel($this->lng->txt('cancel'), 'view');

        $prgs_id = $this->getPrgrsId();
        $user_name = ilObjUser::_lookupFullname($prgs_id->getUsrId());
        $this->confirmation_gui->addItem(
            self::F_SELECTED_PROGRESS_IDS,
            (string) $prgs_id,
            $user_name
        );
        return $this->confirmation_gui->getHTML();
    }

    public function updateCertificateMulti(): string
    {
        $this->confirmation_gui->setFormAction($this->ctrl->getFormAction($this, 'confirmUpdateCertificate'));
        $this->confirmation_gui->setHeaderText($this->lng->txt('header_update_certificate'));
        $this->confirmation_gui->setConfirm($this->lng->txt('confirm'), 'confirmedUpdateCertificate');
        $this->confirmation_gui->setCancel($this->lng->txt('cancel'), 'view');

        foreach ($this->getPostPrgsIds() as $progress_id) {
            $user_name = ilObjUser::_lookupFullname($progress_id->getUsrId());
            $this->confirmation_gui->addItem(
                self::F_SELECTED_PROGRESS_IDS . '[]',
                (string) $progress_id,
                $user_name
            );
        }
        return $this->confirmation_gui->getHTML();
    }

    public function confirmedUpdateCertificate(): void
    {
        $msgs = $this->getMessageCollection('msg_update_certificate');
        foreach ($this->getPostPrgsIds() as $idx => $prgs_id) {
            if (!$this->mayCurrentUserEditProgressForUser($prgs_id->getUsrId())) {
                $this->showInfoMessage("no_permission_to_update_certificate");
            } else {

                $assignment = $this->assignment_db->get($prgs_id->getAssignmentId());
                $progress = $assignment->getProgressForNode($prgs_id->getNodeId());
                if(!$progress->isSuccessful()) {
                    $msgs->add(false, 'will_not_update_cert_for_unsuccessful_progress', (string) $prgs_id);
                    continue;
                }

                if ($this->updateCertificateForPrg(
                    $prgs_id->getNodeId(),
                    $prgs_id->getUsrId()
                )) {
                    $msgs->add(true, '', (string) $prgs_id);
                } else {
                    $msgs->add(false, 'error_updating_certificate', (string) $prgs_id);
                }
            }
        }
        $this->showMessages($msgs);
        $this->ctrl->redirect($this, "view");
    }

    public function removeCertificate(): string
    {
        $this->confirmation_gui->setFormAction($this->ctrl->getFormAction($this, 'confirmRemovalOfCertificate'));
        $this->confirmation_gui->setHeaderText($this->lng->txt('header_remove_certificate'));
        $this->confirmation_gui->setConfirm($this->lng->txt('confirm'), 'confirmedRemovalOfCertificate');
        $this->confirmation_gui->setCancel($this->lng->txt('cancel'), 'view');

        $prgs_id = $this->getPrgrsId();
        $user_name = ilObjUser::_lookupFullname($prgs_id->getUsrId());
        $this->confirmation_gui->addItem(
            self::F_SELECTED_PROGRESS_IDS,
            (string) $prgs_id,
            $user_name
        );
        return $this->confirmation_gui->getHTML();
    }

    public function removeCertificateMulti(): string
    {
        $this->confirmation_gui->setFormAction($this->ctrl->getFormAction($this, 'confirmRemovalOfCertificate'));
        $this->confirmation_gui->setHeaderText($this->lng->txt('header_remove_certificate'));
        $this->confirmation_gui->setConfirm($this->lng->txt('confirm'), 'confirmedRemovalOfCertificate');
        $this->confirmation_gui->setCancel($this->lng->txt('cancel'), 'view');

        foreach ($this->getPostPrgsIds() as $progress_id) {
            $user_name = ilObjUser::_lookupFullname($progress_id->getUsrId());
            $this->confirmation_gui->addItem(
                self::F_SELECTED_PROGRESS_IDS . '[]',
                (string) $progress_id,
                $user_name
            );
        }

        return $this->confirmation_gui->getHTML();
    }

    public function confirmedRemovalOfCertificate(): void
    {
        $msgs = $this->getMessageCollection('msg_remove_certificate');
        $pgs_ids = $this->getPostPrgsIds();
        foreach ($pgs_ids as $idx => $prgs_id) {
            if (!$this->mayCurrentUserEditProgressForUser($prgs_id->getUsrId())) {
                $this->showInfoMessage("no_permission_to_remove_certificate");
            } else {
                $this->removeCertificateForUser(
                    $prgs_id->getNodeId(),
                    $prgs_id->getUsrId(),
                );
            }
        }
        $this->showSuccessMessage("successfully_removed_certificate");
        $this->ctrl->redirect($this, "view");
    }
}
