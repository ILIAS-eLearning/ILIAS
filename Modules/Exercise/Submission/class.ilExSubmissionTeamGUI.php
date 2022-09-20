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

use ILIAS\Exercise\GUIRequest;

/**
 * Submission team
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @author Alexander Killing <killing@leifos.de>
 *
 * @ilCtrl_Calls ilExSubmissionTeamGUI: ilRepositorySearchGUI
 */
class ilExSubmissionTeamGUI
{
    protected ilCtrl $ctrl;
    protected ilTabsGUI $tabs_gui;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilToolbarGUI $toolbar;
    protected ilObjUser $user;
    protected ilObjExercise $exercise;
    protected ilExAssignment $assignment;
    protected ilExSubmission $submission;
    protected ?ilExAssignmentTeam $team = null;
    /**
     * @var int[]
     */
    protected array $requested_team_ids = [];
    protected GUIRequest $request;

    public function __construct(
        ilObjExercise $a_exercise,
        ilExSubmission $a_submission
    ) {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->toolbar = $DIC->toolbar();
        $this->user = $DIC->user();
        $ilCtrl = $DIC->ctrl();
        $ilTabs = $DIC->tabs();
        $lng = $DIC->language();
        $tpl = $DIC["tpl"];

        $this->exercise = $a_exercise;
        $this->submission = $a_submission;
        $this->assignment = $a_submission->getAssignment();

        // :TODO:
        $this->ctrl = $ilCtrl;
        $this->tabs_gui = $ilTabs;
        $this->lng = $lng;
        $this->tpl = $tpl;

        $this->request = $DIC->exercise()->internal()->gui()->request();
        $this->requested_team_ids = $this->request->getTeamIds();
    }

    /**
     * @throws ilCtrlException
     */
    public function executeCommand(): void
    {
        $ilCtrl = $this->ctrl;

        $class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd("submissionScreenTeam");

        if (!$this->assignment->hasTeam() ||
            !$this->submission->canView()) {
            return;
        }
        $this->team = $this->submission->getTeam();

        if (!$this->submission->isTutor()) {
            self::handleTabs();
            $this->tabs_gui->activateTab("team");
        }

        switch ($class) {
            case 'ilrepositorysearchgui':
                $this->ctrl->setReturn($this, 'submissionScreenTeam');
                $rep_search = new ilRepositorySearchGUI();
                if (!$this->submission->isTutor()) {
                    $rep_search->setPrivacyMode(ilUserAutoComplete::PRIVACY_MODE_RESPECT_USER_SETTING);
                }
                $rep_search->setTitle($this->lng->txt("exc_team_member_add"));
                $rep_search->setCallback($this, 'addTeamMemberActionObject');
                $this->ctrl->forwardCommand($rep_search);
                break;

            default:
                $this->{$cmd . "Object"}();
                break;
        }
    }

    public static function getOverviewContent(
        ilInfoScreenGUI $a_info,
        ilExSubmission $a_submission
    ): void {
        global $DIC;

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();

        if (!$a_submission->getAssignment()->hasTeam()) {
            return;
        }

        $state = ilExcAssMemberState::getInstanceByIds($a_submission->getAssignment()->getId(), $a_submission->getUserId());

        $team_members = $a_submission->getTeam()->getMembers();
        if ($team_members !== []) {									// we have a team
            $team = array();
            foreach ($team_members as $member_id) {
                //$team[] = ilObjUser::_lookupFullname($member_id);
                $team[] = ilUserUtil::getNamePresentation($member_id, false, false, "", false);
            }
            $team = implode("; ", $team);

            if (!$a_submission->getAssignment()->getTeamTutor()) {
                #23685
                // any team member upload?
                if (!$a_submission->getLastSubmission()) {
                    $button = ilLinkButton::getInstance();
                    $button->setCaption("exc_delete_team");
                    $button->setUrl($ilCtrl->getLinkTargetByClass(array("ilExSubmissionGUI", "ilExSubmissionTeamGUI"), "confirmDeleteTeam"));
                    $team .= " " . $button->render();
                }
                $button = ilLinkButton::getInstance();
                $button->setCaption("exc_manage_team");
                $button->setUrl($ilCtrl->getLinkTargetByClass(array("ilExSubmissionGUI", "ilExSubmissionTeamGUI"), "submissionScreenTeam"));
            } else {
                $button = ilLinkButton::getInstance();
                $button->setCaption("exc_team_log");
                $button->setUrl($ilCtrl->getLinkTargetByClass(array("ilExSubmissionGUI", "ilExSubmissionTeamGUI"), "submissionScreenTeamLog"));
            }
            $team .= "<br><br>" . $button->render();

            $a_info->addProperty($lng->txt("exc_team_members"), $team);
        } else {
            //if($a_submission->getAssignment()->beforeDeadline())		// this was "for all users"
            if (!$state->hasSubmissionEnded()) {							// this is for current user/team -> no team creation, if no submission possible
                if (!$a_submission->hasSubmitted()) {
                    $team_info = $lng->txt("exc_no_team_yet_notice");
                } else {
                    $team_info = '<span class="warning">' . $lng->txt("exc_no_team_yet_notice") . '</span>';
                }

                if (!$a_submission->getAssignment()->getTeamTutor()) {
                    $button = ilLinkButton::getInstance();
                    $button->setPrimary(true);
                    $button->setCaption("exc_create_team");		// team creation
                    $button->setUrl($ilCtrl->getLinkTargetByClass(array("ilExSubmissionGUI", "ilExSubmissionTeamGUI"), "createTeam"));
                    $team_info .= " " . $button->render();

                    $team_info .= '<div class="ilFormInfo">' . $lng->txt("exc_no_team_yet_info") . '</div>';
                } else {
                    $team_info .= '<div class="ilFormInfo">' . $lng->txt("exc_no_team_yet_info_tutor") . '</div>';
                }
            } else {
                $team_info = '<span class="warning">' . $lng->txt("exc_create_team_times_up_warning") . '</span>';
            }

            $a_info->addProperty($lng->txt("exc_team_members"), $team_info);
        }
    }

    public function returnToParentObject(): void
    {
        $this->ctrl->returnToParent($this);
    }

    public static function handleTabs(): void
    {
        global $DIC;

        $ilTabs = $DIC->tabs();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        $ilTabs->addTab(
            "team",
            $lng->txt("exc_team"),
            $ilCtrl->getLinkTargetByClass("ilExSubmissionTeamGUI", "submissionScreenTeam")
        );

        $ilTabs->addTab(
            "log",
            $lng->txt("exc_team_log"),
            $ilCtrl->getLinkTargetByClass("ilExSubmissionTeamGUI", "submissionScreenTeamLog")
        );
    }

    protected function canEditTeam(): bool
    {
        return (($this->submission->canSubmit() &&
            !$this->submission->getAssignment()->getTeamTutor()) ||
            $this->submission->isTutor());
    }

    /**
     * Displays a form which allows members to manage team uploads
     */
    public function submissionScreenTeamObject(): void
    {
        $ilToolbar = $this->toolbar;

        // #13414
        $read_only = !$this->canEditTeam();

        if ($this->submission->getAssignment()->afterDeadlineStrict(false)) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("exercise_time_over"));
        } elseif (!$read_only) {
            $add_search = $this->submission->isTutor();
            // add member
            ilRepositorySearchGUI::fillAutoCompleteToolbar(
                $this,
                $ilToolbar,
                array(
                    'auto_complete_name' => $this->lng->txt('user'),
                    'submit_name' => $this->lng->txt('add'),
                    'add_search' => $add_search,
                    'add_from_container' => $this->exercise->getRefId()
                )
            );
        } elseif ($this->submission->getAssignment()->getTeamTutor()) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("exc_no_team_yet_info_tutor"));
        }

        $tbl = new ilExAssignmentTeamTableGUI(
            $this,
            "submissionScreenTeam",
            ilExAssignmentTeamTableGUI::MODE_EDIT,
            $this->exercise->getRefId(),
            $this->team,
            $read_only
        );

        $this->tpl->setContent($tbl->getHTML());
    }

    /**
     * @throws ilExcUnknownAssignmentTypeException
     */
    public function addTeamMemberActionObject(
        array $a_user_ids = array()
    ): void {
        if (!$this->canEditTeam()) {
            $this->ctrl->redirect($this, "submissionScreenTeam");
        }

        if ($a_user_ids === []) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"));
            return;
        }

        $new_users = [];

        foreach ($a_user_ids as $user_id) {
            if ($this->team->addTeamMember($user_id, $this->exercise->getRefId())) {
                $new_users[] = $user_id;

                // #14277
                if (!$this->exercise->members_obj->isAssigned($user_id)) {
                    $this->exercise->members_obj->assignMember($user_id);
                }
            } else {
                // #11959
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt("exc_members_already_assigned_team"), true);
            }
        }

        if ($new_users !== []) {
            // re-evaluate complete team, as new member could have already submitted
            $this->exercise->processExerciseStatus(
                $this->assignment,
                $this->team->getMembers(),
                $this->submission->hasSubmitted(),
                $this->submission->validatePeerReviews()
            );
            // :TODO: notification?
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
        }

        $this->ctrl->redirect($this, "submissionScreenTeam");
    }

    /**
     * @throws ilExcUnknownAssignmentTypeException
     */
    public function confirmDeleteTeamObject(): void
    {
        $this->confirmRemoveTeamMemberObject(true);
    }

    /**
     * @throws ilExcUnknownAssignmentTypeException
     */
    public function confirmRemoveTeamMemberObject(
        bool $a_full_delete = false
    ): void {
        $ilUser = $this->user;
        $tpl = $this->tpl;

        if (!$this->submission->isTutor()) {
            $ids = $a_full_delete ? $this->team->getMembers() : $this->requested_team_ids;

            if ([] === $ids && !$this->canEditTeam()) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt("select_one"), true);
                $this->ctrl->redirect($this, "submissionScreenTeam");
            }
        } else {
            $ids = $this->requested_team_ids;
            if ([] === $ids) {
                $this->returnToParentObject();
            }
        }

        $members = $this->team->getMembers();
        if (count($members) <= count($ids)) {
            if (count($members) == 1 && $members[0] == $ilUser->getId()) {
                // direct team deletion - no confirmation
                $this->removeTeamMemberObject($a_full_delete);
                return;
            } else {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt("exc_team_at_least_one"), true);
                $this->ctrl->redirect($this, "submissionScreenTeam");
            }
        }

        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setHeaderText($this->lng->txt("exc_team_member_remove_sure"));
        $cgui->setConfirm($this->lng->txt("remove"), "removeTeamMember");
        $cgui->setCancel($this->lng->txt("cancel"), $this->submission->isTutor()
            ? "returnToParent"
            : "submissionScreenTeam");

        $files = $this->submission->getFiles();

        foreach ($ids as $id) {
            $details = array();
            foreach ($files as $file) {
                if ($file["owner_id"] == $id) {
                    $details[] = $file["filetitle"];
                }
            }
            $uname = ilUserUtil::getNamePresentation($id);
            if ($details !== []) {
                $uname .= ": " . implode(", ", $details);
            }
            $cgui->addItem("id[]", $id, $uname);
        }

        $tpl->setContent($cgui->getHTML());
    }

    /**
     * @throws ilExcUnknownAssignmentTypeException
     */
    public function removeTeamMemberObject(
        bool $a_full_delete = false
    ): void {
        $ilUser = $this->user;

        $cancel_cmd = $this->submission->isTutor()
            ? "returnToParent"
            : "submissionScreenTeam";

        $ids = [];
        if ($a_full_delete) {
            $ids = $this->team->getMembers();
        } else {
            $ids = $this->requested_team_ids;
        }
        $ids = array_filter(array_map('intval', $ids));

        if ([] === $ids && !$this->canEditTeam()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("select_one"), true);
            $this->ctrl->redirect($this, $cancel_cmd);
        }

        $team_deleted = $a_full_delete;
        if (!$team_deleted) {
            $members = $this->team->getMembers();
            if (count($members) <= count($ids)) {
                if (count($members) == 1 && $members[0] == $ilUser->getId()) {
                    $team_deleted = true;
                } else {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt("exc_team_at_least_one"), true);
                    $this->ctrl->redirect($this, $cancel_cmd);
                }
            }
        }

        foreach ($ids as $user_id) {
            $this->team->removeTeamMember($user_id, $this->exercise->getRefId());
        }

        // reset ex team members, as any submission is not valid without team
        $this->exercise->processExerciseStatus(
            $this->assignment,
            $ids,
            false
        );

        if (!$team_deleted) {
            // re-evaluate complete team, as removed member might have had submitted
            $this->exercise->processExerciseStatus(
                $this->assignment,
                $this->team->getMembers(),
                $this->submission->hasSubmitted(),
                $this->submission->validatePeerReviews()
            );
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
        if (!$team_deleted) {
            $this->ctrl->redirect($this, $cancel_cmd);
        } else {
            $this->ctrl->redirect($this, "returnToParent");
        }
    }

    public function submissionScreenTeamLogObject(): void
    {
        $this->tabs_gui->activateTab("log");

        $tbl = new ilExAssignmentTeamLogTableGUI(
            $this,
            "submissionScreenTeamLog",
            $this->team
        );

        $this->tpl->setContent($tbl->getHTML());
    }

    public function createSingleMemberTeamObject(): void
    {
        ilExAssignmentTeam::getTeamId(
            $this->assignment->getId(),
            $this->submission->getUserId(),
            true
        );
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
        $this->returnToParentObject();
    }

    public function showTeamLogObject(): void
    {
        $tbl = new ilExAssignmentTeamLogTableGUI($this, "showTeamLog", $this->team);
        $this->tpl->setContent($tbl->getHTML());
    }

    /**
     * Get Adopt form
     */
    public function getAdoptForm(): ilPropertyFormGUI
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;
        $ilUser = $this->user;

        $form = new ilPropertyFormGUI();
        $form->setTitle($lng->txt("exc_team_assignment_adopt_user"));
        $form->setFormAction($ctrl->getFormAction($this, "createAdoptedTeam"));

        $teams = new ilRadioGroupInputGUI($lng->txt("exc_assignment"), "ass_adpt");
        $teams->setValue(-1);

        $teams->addOption(new ilRadioOption($lng->txt("exc_team_assignment_adopt_none_user"), -1));

        $current_map = ilExAssignmentTeam::getAssignmentTeamMap($this->assignment->getId());

        $options = ilExAssignmentTeam::getAdoptableTeamAssignments($this->assignment->getExerciseId(), $this->assignment->getId(), $ilUser->getId());
        foreach ($options as $id => $item) {
            $members = array();
            $free = false;
            foreach ($item["user_team"] as $user_id) {
                $members[$user_id] = ilUserUtil::getNamePresentation($user_id);

                if (array_key_exists($user_id, $current_map)) {
                    $members[$user_id] .= " (" . $lng->txt("exc_team_assignment_adopt_already_assigned") . ")";
                } else {
                    $free = true;
                }
            }
            asort($members);
            $members = implode("<br />", $members);
            $option = new ilRadioOption($item["title"], $id);
            $option->setInfo($members);
            if (!$free) {
                $option->setDisabled(true);
            }
            $teams->addOption($option);
        }

        $form->addItem($teams);

        $form->addCommandButton("createAdoptedTeam", $lng->txt("save"));
        $form->addCommandButton("returnToParent", $lng->txt("cancel"));

        return $form;
    }

    public function createTeamObject(): void
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        $tpl = $this->tpl;

        if ($this->submission->canSubmit()) {
            $options = ilExAssignmentTeam::getAdoptableTeamAssignments($this->assignment->getExerciseId(), $this->assignment->getId(), $ilUser->getId());
            if ($options !== []) {
                $form = $this->getAdoptForm();
                $tpl->setContent($form->getHTML());
                return;
            }

            ilExAssignmentTeam::getTeamId($this->assignment->getId(), $ilUser->getId(), true);

            // #18046
            if (!$this->exercise->members_obj->isAssigned($ilUser->getId())) {
                $this->exercise->members_obj->assignMember($ilUser->getId());
            }

            $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
        }

        $ilCtrl->redirect($this, "returnToParent");
    }

    /**
     * @throws ilExcUnknownAssignmentTypeException
     */
    public function createAdoptedTeamObject(): void
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        $lng = $this->lng;

        $form = $this->getAdoptForm();
        $form->checkInput();
        if ($this->submission->canSubmit()) {
            $src_ass_id = $form->getInput("ass_adpt");
            if ($src_ass_id > 0) {
                ilExAssignmentTeam::adoptTeams($src_ass_id, $this->assignment->getId(), $ilUser->getId(), $this->exercise->getRefId());
            } else {
                ilExAssignmentTeam::getTeamId($this->assignment->getId(), $ilUser->getId(), true);
            }

            $this->tpl->setOnScreenMessage('success', $lng->txt("settings_saved"), true);
        }

        $ilCtrl->redirect($this, "returnToParent");
    }

    /**
     * Add user as member
     * @throws ilExcUnknownAssignmentTypeException
     */
    public function addUserFromAutoCompleteObject(): void
    {
        $user_login = $this->request->getUserLogin();
        if ($user_login == "") {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_no_search_string'));
            $this->submissionScreenTeamObject();
            return;
        }

        $users = explode(',', $user_login);

        $user_ids = array();
        foreach ($users as $user) {
            $user_id = ilObjUser::_lookupId($user);

            if (!$user_id) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('user_not_known'));
                $this->submissionScreenTeamObject();
                return;
            }

            $user_ids[] = $user_id;
        }

        $this->addTeamMemberActionObject($user_ids);
    }
}
