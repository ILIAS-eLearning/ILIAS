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
//use ILIAS\UI\Component\Input\Container\Form;
use ILIAS\UI\Component\Modal;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @ilCtrl_Calls ilObjStudyProgrammeMembersGUI: ilStudyProgrammeRepositorySearchGUI
 * @ilCtrl_Calls ilObjStudyProgrammeMembersGUI: ilObjStudyProgrammeIndividualPlanGUI
 * @ilCtrl_Calls ilObjStudyProgrammeMembersGUI: ilObjFileGUI
 * @ilCtrl_Calls ilObjStudyProgrammeMembersGUI: ilStudyProgrammeMailMemberSearchGUI
 * @ilCtrl_Calls ilObjStudyProgrammeMembersGUI: ilFormPropertyDispatchGUI
 */
class ilObjStudyProgrammeMembersGUI
{
    use ilTableCommandHelper;
    use ilPRGCertificateHelper;

    public const DEFAULT_CMD = "view";
    public const TABLE_COMMAND = "prgmemberscmd";


    public const ACTION_ACKNOWLEDGE_COURSES = "acknowledge_completed_courses"; //TODO: doubles ilStudyProgrammeAssignmentsTableActions
    public const ACTION_MAIL_USER = "mail_user";




    public const F_QUERY_PROGRESS_IDS = 'prgrsids';

    public const F_COMMAND_OPTION_ALL = 'select_cmd_all';
    public const F_ALL_PROGRESS_IDS = 'all_progress_ids';
    public const F_SELECTED_USER_IDS = 'usrids';

    protected ?ilObjStudyProgramme $object;
    protected ?ilPRGPermissionsHelper $permissions;
    protected ilObjectGUI $parent_gui;
    protected int $ref_id;

    protected ?ilStudyProgrammeAssignmentsTable $table = null;

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
        protected ServerRequestInterface $request
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
        //throw new \Exception('stop');
        $this->ref_id = $ref_id;
        $this->object = ilObjStudyProgramme::getInstanceByRefId($ref_id);
        $this->permissions = ilStudyProgrammeDIC::specificDicFor($this->object)['permissionhelper'];
        $this->table = ilStudyProgrammeDIC::specificDicFor($this->object)['ilStudyProgrammeAssignmentsTable'];
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

                $selected_ids = array_map(
                    fn($id) => $id->getAssignmentId(),
                    $this->getPrgrsIdsFromQuery()
                );
                $assignments = array_filter(
                    $this->getAssignmentsById(),
                    fn($ass) => in_array($ass->getId(), $selected_ids)
                );
                $dic = ilStudyProgrammeDIC::specificDicFor($this->object);
                $mail_search = $dic['ilStudyProgrammeMailMemberSearchGUI'];
                $mail_search->setAssignments($assignments);
                $mail_search->setBackTarget(
                    $this->ctrl->getLinkTarget($this, $this->getDefaultCommand())
                );
                $this->ctrl->forwardCommand($mail_search);
                break;

            case false:
                switch ($cmd) {

                    case self::TABLE_COMMAND:
                        $dic = ilStudyProgrammeDIC::specificDicFor($this->object);
                        $table = $dic['ilStudyProgrammeAssignmentsTable'];
                        $actions = $dic['ilStudyProgrammeAssignmentsTableActions'];

                        $actions->doCommand(
                            $table->getQueryCommand(),
                            $table->getProgressIds()
                        );
                        /*
                                                if($response) {
                                                    echo $response;
                                                    exit();
                                                }
                                                $this->ctrl->redirect($this, "view");
                        */
                        //build table endpoint and deal with commands there.
                        //endpoint will output html or redirect.
                        echo 'endpoint';
                        exit();



                    case "view":
                    case "acknowledgeCourses":
                    case "acknowledgeCoursesMulti":
                        $cont = $this->$cmd();
                        $this->tpl->setContent($cont);
                        break;
                    case "confirmedAcknowledgeCourses":
                        $this->confirmedAcknowledgeCourses();
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

    /**
     * @return PRGProgressId[]
     */
    /*    protected function getPostPrgrsIdsFromModal(): array
        {
            $prgrs_ids = [];
            if ($this->http_wrapper->post()->has(self::F_MODAL_POST_PRGSIDS)) {
                $prgrs_ids = $this->http_wrapper->post()->retrieve(
                    self::F_MODAL_POST_PRGSIDS,
                    $this->refinery->custom()->transformation(
                        fn($ids) => array_map(
                            fn($id) => PRGProgressId::createFromString($id),
                            $ids
                        )
                    )
                );
            }
            return $prgrs_ids;
        }
    */
    protected function getPrgrsIdsFromQuery(): array
    {
        $prgrs_ids = [];
        if ($this->http_wrapper->query()->has(self::F_QUERY_PROGRESS_IDS)) {
            $prgrs_ids = $this->http_wrapper->query()->retrieve(
                self::F_QUERY_PROGRESS_IDS,
                $this->refinery->custom()->transformation(
                    fn($ids) => array_map(
                        fn($id) => PRGProgressId::createFromString($id),
                        explode(',', $ids)
                    )
                )
            );
        }
        return $prgrs_ids;
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

        $dic = ilStudyProgrammeDIC::specificDicFor($this->object);
        $table = $dic['ilStudyProgrammeAssignmentsTable'];


        return $this->ui_renderer->render(
            [
                $table->getFilter(
                    $this->ctrl->getLinkTarget($this, self::DEFAULT_CMD)
                ),
                $table->getTable()->withRequest($this->request)
            ]
        );
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
                $this->viewCompletedCourses($with_courses)
            );
            return true;

        } else {
            $this->ctrl->redirect($this, "view");
        }
    }

    /**
     * Shows list of completed courses for each assignment
     */
    public function viewCompletedCourses(array $assignments): string
    {
        $tpl = new ilTemplate(
            "tpl.acknowledge_completed_courses.html",
            true,
            true,
            "components/ILIAS/StudyProgramme"
        );
        $tpl->setVariable("TITLE", $this->lng->txt("prg_acknowledge_completed_courses"));
        $tpl->setVariable("CAPTION_ADD", $this->lng->txt("btn_next"));
        $tpl->setVariable("CAPTION_CANCEL", $this->lng->txt("prg_cancel_acknowledge_completed_courses"));
        $tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
        $tpl->setVariable("CANCEL_CMD", "view");
        $tpl->setVariable("ADD_CMD", "confirmedAcknowledgeCourses");

        $prg = $this->getStudyProgramme();
        $completed_courses = [];
        foreach ($assignments as $ass) {
            $completed_crss = $prg->getCompletedCourses($ass->getUserId());

            $tpl->setCurrentBlock("usr_section");
            $tpl->setVariable("FIRSTNAME", $ass->getUserInformation()->getFirstname());
            $tpl->setVariable("LASTNAME", $ass->getUserInformation()->getlastname());
            $table = new ilStudyProgrammeAcknowledgeCompletedCoursesTableGUI(
                $this,
                $ass,
                $completed_crss
            );
            $tpl->setVariable("TABLE", $table->getHTML());
            $tpl->parseCurrentBlock();
        }
        return $tpl->get();
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
    /*
        protected function markAccredited(array $prgrs_ids): void
        {
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
        protected function unmarkAccredited(array $prgrs_ids): void
        {
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

        protected function markRelevant(array $prgrs_ids): void
        {
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

        protected function markNotRelevant(array $prgrs_ids): void
        {
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

        protected function updateFromCurrentPlan(array $prgrs_ids): void
        {
            $msgs = $this->getMessageCollection('msg_update_from_settings');
            foreach ($prgrs_ids as $idx => $prgrs_id) {
                if (!$this->mayCurrentUserEditProgressForUser($prgrs_id->getUsrId())) {
                    $msgs->add(false, 'no_permission_to_update_plan_of_user', (string) $prgrs_id);
                    continue;
                } else {
                    $msgs->add(true, '', (string) $prgrs_id);
                }

                $this->object->updatePlanFromRepository(
                    $prgrs_id->getAssignmentId(),
                    $this->user->getId(),
                    $msgs
                );
            }
            $this->showMessages($msgs);
            $this->ctrl->redirect($this, "view");
        }

    */
    protected function acknowledgeCourses(array $prgrs_ids): string
    {
        $assignments = [];
        foreach ($prgrs_ids as $progress_id) {
            $assignments[] = $this->assignment_db->get($progress_id->getAssignmentId());
        }
        return $this->viewCompletedCourses($assignments);
    }

    protected function confirmedAcknowledgeCourses()
    {
        $msgs = $this->getMessageCollection('msg_acknowledge_courses');
        $post = $this->http_wrapper->post()->retrieve(
            'acknowledge',
            $this->refinery->custom()->transformation(
                fn($value) => $value ? array_map(fn($entry) => explode(';', $entry), $value) : $value
            )
        );
        if($post) {
            $acknowledge = [];
            foreach ($post as $ack) {
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

    /*
        protected function changeDeadline(array $prgrs_ids, ?DateTimeImmutable $deadline): void
        {
            $msgs = $this->getMessageCollection('msg_change_deadline_date');
            foreach ($prgrs_ids as $progress_id) {
                $assignment_id = $progress_id->getAssignmentId();
                $this->object->changeProgressDeadline($assignment_id, $this->user->getId(), $msgs, $deadline);
            }
            $this->showMessages($msgs);
            $this->ctrl->redirect($this, "view");
        }

        protected function changeExpiryDate(array $prgrs_ids, ?DateTimeImmutable $validity): void
        {
            $msgs = $this->getMessageCollection('msg_change_expire_date');
            foreach ($prgrs_ids as $progress_id) {
                $assignment_id = $progress_id->getAssignmentId();
                $this->object->changeProgressValidityDate($assignment_id, $this->user->getId(), $msgs, $validity);
            }
            $this->showMessages($msgs);
            $this->ctrl->redirect($this, "view");
        }
        protected function confirmedRemoveAssignment(array $prgrs_ids): void
        {
            $not_removed = [];
            foreach ($prgrs_ids as $idx => $prgrs_id) {
                try {
                    $this->removeAssignmentByProgressId($prgrs_id);
                } catch (ilException $e) {
                    $not_removed[] = $prgrs_id;
                }
            }
            if (count($not_removed) === count($prgrs_ids)) {
                $this->showInfoMessage("remove_users_not_possible");
            } elseif (count($not_removed) > 0) {
                $this->showSuccessMessage("remove_users_partial_success");
            } else {
                $this->showSuccessMessage("remove_users_success");
            }
            $this->ctrl->redirect($this, "view");
        }

        protected function removeAssignmentByProgressId(PRGProgressId $pgs_id): void
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


        protected function showSuccessMessage(string $lng_var): void
        {
            $this->tpl->setOnScreenMessage("success", $this->lng->txt("prg_$lng_var"), true);
        }


        protected function showInfoMessage(string $lng_var): void
        {
            $this->tpl->setOnScreenMessage("info", $this->lng->txt("prg_$lng_var"), true);
        }
    */

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

        $link = $this->table->getLinkMailToAllUsers();

        $toolbar->addComponent(
            $this->ui_factory->link()->standard(
                $this->lng->txt('mail_assignments'),
                $link->__toString()
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

    protected function mailToSelectedUsers(array $prgrs_ids): void
    {
        $this->ctrl->setParameterByClass(
            ilStudyProgrammeMailMemberSearchGUI::class,
            self::F_QUERY_PROGRESS_IDS,
            implode(',', array_map('strval', $prgrs_ids))
        );

        $link = $this->ctrl->getLinkTargetByClass(
            ilStudyProgrammeMailMemberSearchGUI::class,
            'sendMailToSelectedUsers'
        );
        $this->ctrl->redirectToURL($link);
    }

    protected function updateCertificate(array $prgrs_ids): void
    {
        $msgs = $this->getMessageCollection('msg_update_certificate');
        foreach ($prgrs_ids as $idx => $prgrs_id) {
            if (!$this->mayCurrentUserEditProgressForUser($prgrs_id->getUsrId())) {
                $this->showInfoMessage("no_permission_to_update_certificate");
            } else {

                $assignment = $this->assignment_db->get($prgrs_id->getAssignmentId());
                $progress = $assignment->getProgressForNode($prgrs_id->getNodeId());
                if(!$progress->isSuccessful()) {
                    $msgs->add(false, 'will_not_update_cert_for_unsuccessful_progress', (string) $prgrs_id);
                    continue;
                }

                if ($this->updateCertificateForPrg(
                    $prgrs_id->getNodeId(),
                    $prgrs_id->getUsrId()
                )) {
                    $msgs->add(true, '', (string) $prgrs_id);
                } else {
                    $msgs->add(false, 'error_updating_certificate', (string) $prgrs_id);
                }
            }
        }
        $this->showMessages($msgs);
        $this->ctrl->redirect($this, "view");
    }

    public function removeCertificate(array $prgrs_ids): void
    {
        $msgs = $this->getMessageCollection('msg_remove_certificate');
        foreach ($prgrs_ids as $idx => $prgrs_id) {
            if (!$this->mayCurrentUserEditProgressForUser($prgrs_id->getUsrId())) {
                $this->showInfoMessage("no_permission_to_remove_certificate");
            } else {
                $this->removeCertificateForUser(
                    $prgrs_id->getNodeId(),
                    $prgrs_id->getUsrId(),
                );
            }
        }
        $this->showSuccessMessage("successfully_removed_certificate");
        $this->ctrl->redirect($this, "view");
    }
}
