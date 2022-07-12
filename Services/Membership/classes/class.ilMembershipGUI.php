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
 
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory;

/**
 * Base class for member tab content
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilMembershipGUI
{
    private ilObject $repository_object;
    private ?ilObjectGUI $repository_gui;
    protected GlobalHttpState $http;
    protected Factory $refinery;
    protected ilLanguage $lng;
    protected ilCtrlInterface $ctrl;
    protected ilLogger $logger;
    protected ilGlobalTemplateInterface $tpl;
    protected ilAccessHandler $access;
    protected ?ilParticipants $participants = null;
    protected ilObjUser $user;
    protected ilErrorHandling $error;
    protected ilTabsGUI $tabs;
    protected ilToolbarGUI $toolbar;
    protected ilRbacSystem $rbacsystem;
    protected ilRbacReview $rbacreview;
    protected ilTree $tree;
    protected array $member_data = [];

    public function __construct(ilObjectGUI $repository_gui, ilObject $repository_obj)
    {
        global $DIC;

        $this->repository_gui = $repository_gui;
        $this->repository_object = $repository_obj;

        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('crs');
        $this->lng->loadLanguageModule($this->getParentObject()->getType());
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->logger = $DIC->logger()->mmbr();
        $this->access = $DIC->access();
        $this->user = $DIC->user();
        $this->error = $DIC['ilErr'];
        $this->tabs = $DIC->tabs();
        $this->toolbar = $DIC->toolbar();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->rbacreview = $DIC->rbac()->review();
        $this->tree = $DIC->repositoryTree();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
    }

    /**
     * @return int[]
     */
    protected function initParticipantsFromPost() : array
    {
        if ($this->http->wrapper()->post()->has('participants')) {
            return $this->http->wrapper()->post()->retrieve(
                'participants',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        return [];
    }

    protected function initMemberIdFromGet() : int
    {
        if ($this->http->wrapper()->query()->has('member_id')) {
            return $this->http->wrapper()->query()->retrieve(
                'member_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        return 0;
    }

    protected function initSubscribersFromPost() : array
    {
        if ($this->http->wrapper()->post()->has('subscribers')) {
            return $this->http->wrapper()->post()->retrieve(
                'subscribers',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        return [];
    }

    protected function initWaitingListIdsFromPost() : array
    {
        if ($this->http->wrapper()->post()->has('waiting')) {
            return $this->http->wrapper()->post()->retrieve(
                'waiting',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        return [];
    }

    protected function getLanguage() : ilLanguage
    {
        return $this->lng;
    }

    protected function getCtrl() : ilCtrlInterface
    {
        return $this->ctrl;
    }

    protected function getLogger() : ilLogger
    {
        return $this->logger;
    }

    public function getParentGUI() : ilObjectGUI
    {
        return $this->repository_gui;
    }

    public function getParentObject() : ilObject
    {
        return $this->repository_object;
    }

    public function getMembersObject() : ilParticipants
    {
        if ($this->participants instanceof ilParticipants) {
            return $this->participants;
        }
        return $this->participants = ilParticipants::getInstance($this->getParentObject()->getRefId());
    }

    protected function getMailMemberRoles() : ?ilAbstractMailMemberRoles
    {
        return null;
    }

    protected function checkPermissionBool(
        string $a_permission,
        string $a_cmd = '',
        string $a_type = '',
        int $a_ref_id = 0
    ) : bool {
        if ($a_ref_id === 0) {
            $a_ref_id = $this->getParentObject()->getRefId();
        }
        return $this->access->checkAccess($a_permission, $a_cmd, $a_ref_id);
    }

    protected function checkRbacOrPositionAccessBool(
        string $a_rbac_perm,
        string $a_pos_perm,
        int $a_ref_id = 0
    ) : bool {
        if ($a_ref_id === 0) {
            $a_ref_id = $this->getParentObject()->getRefId();
        }
        return $this->access->checkRbacOrPositionPermissionAccess($a_rbac_perm, $a_pos_perm, $a_ref_id);
    }

    /**
     * Check permission
     * If not granted redirect to parent gui
     */
    protected function checkPermission(string $a_permission, string $a_cmd = "") : void
    {
        if (!$this->checkPermissionBool($a_permission, $a_cmd)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_permission'), true);
            $this->ctrl->redirect($this->getParentGUI());
        }
    }

    /**
     * check rbac or position access
     */
    protected function checkRbacOrPermissionAccess(string $a_rbac_perm, string $a_pos_perm) : void
    {
        if (!$this->checkRbacOrPositionAccessBool($a_rbac_perm, $a_pos_perm)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_permission'), true);
            $this->ctrl->redirect($this->getParentGUI());
        }
    }

    /**
     * Check if current user is allowed to add / search users
     */
    protected function canAddOrSearchUsers() : bool
    {
        return $this->checkPermissionBool('manage_members');
    }

    /**
     * Filter user ids by access
     * @param int[] $a_usr_ids
     * @return int[]
     */
    public function filterUserIdsByRbacOrPositionOfCurrentUser(array $a_user_ids) : array
    {
        return $a_user_ids;
    }

    public function executeCommand() : void
    {
        $cmd = $this->ctrl->getCmd('participants');
        $next_class = $this->ctrl->getNextClass();

        switch ($next_class) {
            case 'ilrepositorysearchgui':

                $this->checkPermission('manage_members');

                $rep_search = new ilRepositorySearchGUI();
                $rep_search->addUserAccessFilterCallable([$this, 'filterUserIdsByRbacOrPositionOfCurrentUser']);

                $participants = $this->getMembersObject();
                if (
                    $participants->isAdmin($this->user->getId()) ||
                    $this->access->checkAccess('manage_members', '', $this->getParentObject()->getRefId())
                ) {
                    $rep_search->setCallback(
                        $this,
                        'assignMembers',
                        $this->getParentGUI()->getLocalRoles()
                    );
                } else {
                    //#18445 excludes admin role
                    $rep_search->setCallback(
                        $this,
                        'assignMembers',
                        $this->getLocalRoles()
                    );
                }

                // Set tabs
                $this->ctrl->setReturn($this, 'participants');
                $ret = $this->ctrl->forwardCommand($rep_search);
                break;

            case 'ilmailmembersearchgui':
                $this->tabs->clearTargets();
                $this->tabs->setBackTarget(
                    $this->lng->txt('btn_back'),
                    $this->ctrl->getLinkTarget($this, $this->getDefaultCommand())
                );

                $mail = new ilMail($this->user->getId());
                if (!(
                    $this->getParentObject()->getMailToMembersType() === ilCourseConstants::MAIL_ALLOWED_ALL ||
                        $this->access->checkAccess('manage_members', "", $this->getParentObject()->getRefId())
                ) ||
                    !$this->rbacsystem->checkAccess(
                        'internal_mail',
                        $mail->getMailObjectReferenceId()
                    )) {
                    $this->error->raiseError($this->lng->txt("msg_no_perm_read"), $this->error->MESSAGE);
                }

                $mail_search = new ilMailMemberSearchGUI(
                    $this,
                    $this->getParentObject()->getRefId(),
                    $this->getMailMemberRoles()
                );
                $mail_search->setObjParticipants(
                    ilParticipants::getInstance($this->getParentObject()->getRefId())
                );
                $this->ctrl->forwardCommand($mail_search);
                break;

            case 'ilusersgallerygui':

                $this->setSubTabs($this->tabs);
                $this->tabs->setSubTabActive(
                    $this->getParentObject()->getType() . '_members_gallery'
                );
                $is_admin = $this->checkRbacOrPositionAccessBool('manage_members', 'manage_members');
                $is_participant = ilParticipants::_isParticipant(
                    $this->getParentObject()->getRefId(),
                    $this->user->getId()
                );
                if (
                    !$is_admin &&
                    (
                        $this->getParentObject()->getShowMembers() === 0 ||
                        !$is_participant
                    )
                ) {
                    $this->error->raiseError($this->lng->txt('msg_no_perm_read'), $this->error->MESSAGE);
                }

                $this->showMailToMemberToolbarButton($this->toolbar, 'jump2UsersGallery');
                $this->showMemberExportToolbarButton($this->toolbar, 'jump2UsersGallery');

                $provider = new ilUsersGalleryParticipants($this->getParentObject()->getMembersObject());
                $gallery_gui = new ilUsersGalleryGUI($provider);
                $this->ctrl->forwardCommand($gallery_gui);
                break;

            case 'ilcourseparticipantsgroupsgui':

                $this->setSubTabs($this->tabs);
                $this->checkRbacOrPermissionAccess('manage_members', 'manage_members');

                $cmg_gui = new ilCourseParticipantsGroupsGUI($this->getParentObject()->getRefId());
                if ($cmd === "show" || $cmd = "") {
                    $this->showMailToMemberToolbarButton($this->toolbar);
                }
                $this->ctrl->forwardCommand($cmg_gui);
                break;

            case 'ilsessionoverviewgui':

                $this->setSubTabs($this->tabs);
                $this->checkRbacOrPermissionAccess('manage_members', 'manage_members');

                $prt = ilParticipants::getInstance($this->getParentObject()->getRefId());

                $overview = new ilSessionOverviewGUI($this->getParentObject()->getRefId(), $prt);
                $this->ctrl->forwardCommand($overview);
                break;

            case 'ilmemberexportgui':

                $this->setSubTabs($this->tabs);
                $this->checkRbacOrPermissionAccess('manage_members', 'manage_members');

                $export = new ilMemberExportGUI($this->getParentObject()->getRefId());
                $this->ctrl->forwardCommand($export);
                break;

            case 'ilobjectcustomuserfieldsgui':

                $this->setSubTabs($this->tabs);
                $this->checkRbacOrPermissionAccess('manage_members', 'manage_members');
                $this->activateSubTab($this->getParentObject()->getType() . "_member_administration");
                $this->ctrl->setReturn($this, 'participants');
                $cdf_gui = new ilObjectCustomUserFieldsGUI($this->getParentGUI()->getObject()->getId());
                $this->ctrl->forwardCommand($cdf_gui);
                break;

            default:
                $this->setSubTabs($this->tabs);
                //exclude mailMembersBtn cmd from this check
                if (
                    $cmd === "mailMembersBtn" ||
                    $cmd === 'membersMap' ||
                    $cmd === 'printForMembersOutput' ||
                    $cmd === 'jump2UsersGallery'
                ) {
                    $this->checkPermission('read');
                } else {
                    $this->checkRbacOrPermissionAccess('manage_members', 'manage_members');
                }
                $this->$cmd();
                break;
        }
    }

    /**
     * Show participant table, subscriber table, wating list table;
     */
    protected function participants() : void
    {
        $this->initParticipantTemplate();
        $this->showParticipantsToolbar();
        $this->activateSubTab($this->getParentObject()->getType() . "_member_administration");

        // show waiting list table
        $waiting = $this->parseWaitingListTable();
        if ($waiting instanceof ilWaitingListTableGUI) {
            $this->tpl->setVariable('TABLE_WAIT', $waiting->getHTML());
        }

        // show subscriber table
        $subscriber = $this->parseSubscriberTable();
        if ($subscriber instanceof ilSubscriberTableGUI) {
            $this->tpl->setVariable('TABLE_SUB', $subscriber->getHTML());
        }

        // show member table
        $table = $this->initParticipantTableGUI();
        $table->setTitle($this->lng->txt($this->getParentObject()->getType() . '_mem_tbl_header'));
        $table->setFormAction($this->ctrl->getFormAction($this));
        $table->parse();

        // filter commands
        $table->setFilterCommand('participantsApplyFilter');
        $table->setResetCommand('participantsResetFilter');

        $this->tpl->setVariable('MEMBERS', $table->getHTML());
    }
    
    public function getAttendanceListUserData(int $user_id, array $filters = []) : array
    {
        return [];
    }
    
    /**
     * Apply filter for participant table
     */
    protected function participantsApplyFilter() : void
    {
        $table = $this->initParticipantTableGUI();
        $table->resetOffset();
        $table->writeFilterToSession();

        $this->participants();
    }

    /**
     * reset participants filter
     */
    protected function participantsResetFilter() : void
    {
        $table = $this->initParticipantTableGUI();
        $table->resetOffset();
        $table->resetFilter();

        $this->participants();
    }

    /**
     * Edit one participant
     */
    protected function editMember() : void
    {
        $this->activateSubTab($this->getParentObject()->getType() . "_member_administration");
        $this->editParticipants(array($this->initMemberIdFromGet()));
    }

    /**
     * Edit participants
     * @param int[] $post_participants
     */
    protected function editParticipants(array $post_participants = array()) : void
    {
        if (!$post_participants) {
            $post_participants = $this->initParticipantsFromPost();
        }

        $real_participants = $this->getMembersObject()->getParticipants();
        $participants = array_intersect($post_participants, $real_participants);

        if (!count($participants)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_checkbox'), true);
            $this->ctrl->redirect($this, 'participants');
        }
        $table = $this->initEditParticipantTableGUI($participants);
        $this->tpl->setContent($table->getHTML());
    }

    /**
     * update members
     */
    public function updateParticipants() : void
    {
        $participants = $this->initParticipantsFromPost();
        if (!count($participants)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_checkbox'), true);
            $this->ctrl->redirect($this, 'participants');
        }
        $notifications = $passed = $blocked = $contact = [];
        if ($this->http->wrapper()->post()->has('notification')) {
            $notifications = $this->http->wrapper()->post()->retrieve(
                'notification',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        if ($this->http->wrapper()->post()->has('passed')) {
            $passed = $this->http->wrapper()->post()->retrieve(
                'passed',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        if ($this->http->wrapper()->post()->has('blocked')) {
            $blocked = $this->http->wrapper()->post()->retrieve(
                'blocked',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        if ($this->http->wrapper()->post()->has('contact')) {
            $contact = $this->http->wrapper()->post()->retrieve(
                'contact',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }

        // Determine whether the user has the 'edit_permission' permission
        $hasEditPermissionAccess =
            (
                $this->access->checkAccess('edit_permission', '', $this->getParentObject()->getRefId()) or
                $this->getMembersObject()->isAdmin($this->user->getId())
            );

        // Get all assignable local roles of the object, and
        // determine the role id of the course administrator role.
        $assignableLocalRoles = array();
        $adminRoleId = $this->getParentObject()->getDefaultAdminRole();
        foreach ($this->getLocalTypeRole(false) as $title => $role_id) {
            $assignableLocalRoles[$role_id] = $title;
        }

        $post_roles = [];
        if ($this->http->wrapper()->post()->has('roles')) {
            $post_roles = $this->http->wrapper()->post()->retrieve(
                'roles',
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->kindlyTo()->dictOf(
                        $this->refinery->kindlyTo()->int()
                    )
                )
            );
        }

        // Validate the user ids and role ids in the post data
        foreach ($participants as $usr_id) {
            $memberIsAdmin = $this->rbacreview->isAssigned($usr_id, (int) $adminRoleId);

            // If the current user doesn't have the 'edit_permission'
            // permission, make sure he doesn't remove the course
            // administrator role of members who are course administrator.
            if (
                !$hasEditPermissionAccess &&
                $memberIsAdmin &&
                (
                    !is_array($post_roles[$usr_id]) ||
                    !in_array($adminRoleId, $post_roles[$usr_id])
                )
            ) {
                $post_roles[$usr_id][] = $adminRoleId;
            }

            // Validate the role ids in the post data
            foreach ((array) $post_roles[$usr_id] as $role_id) {
                if (!array_key_exists($role_id, $assignableLocalRoles)) {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_no_perm_perm'), true);
                    $this->ctrl->redirect($this, 'participants');
                }
                if (!$hasEditPermissionAccess &&
                    $role_id == $adminRoleId &&
                    !$memberIsAdmin) {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_no_perm_perm'));
                    $this->ctrl->redirect($this, 'participants');
                }
            }
        }

        $has_admin = false;
        foreach ($this->getMembersObject()->getAdmins() as $admin_id) {
            if (!isset($post_roles[$admin_id])) {
                $has_admin = true;
                break;
            }
            if (in_array($adminRoleId, (array) $post_roles[$admin_id])) {
                $has_admin = true;
                break;
            }
        }

        if (!$has_admin && is_array($post_roles)) {
            // TODO PHP8 Review: Check change of SuperGlobals
            foreach ($post_roles as $roleIdsToBeAssigned) {
                if (in_array($adminRoleId, $roleIdsToBeAssigned)) {
                    $has_admin = true;
                    break;
                }
            }
        }

        if (!$has_admin) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt($this->getParentObject()->getType() . '_min_one_admin'), true);
            $this->ctrl->redirect($this, 'participants');
        }

        foreach ($participants as $usr_id) {
            $this->getMembersObject()->updateRoleAssignments($usr_id, (array) $post_roles[$usr_id]);

            // Disable notification for all of them
            $this->getMembersObject()->updateNotification($usr_id, false);
            if (($this->getMembersObject()->isTutor($usr_id) || $this->getMembersObject()->isAdmin($usr_id)) && in_array(
                $usr_id,
                $notifications
            )) {
                $this->getMembersObject()->updateNotification($usr_id, true);
            }

            $this->getMembersObject()->updateBlocked($usr_id, false);
            if ((!$this->getMembersObject()->isAdmin($usr_id) && !$this->getMembersObject()->isTutor($usr_id)) && in_array(
                $usr_id,
                $blocked
            )) {
                $this->getMembersObject()->updateBlocked($usr_id, true);
            }

            if ($this instanceof ilCourseMembershipGUI) {
                $this->getMembersObject()->updatePassed($usr_id, in_array($usr_id, $passed), true);
                $this->getMembersObject()->sendNotification(
                    ilCourseMembershipMailNotification::TYPE_STATUS_CHANGED,
                    $usr_id
                );
            }

            if (
                ($this->getMembersObject()->isAdmin($usr_id) || $this->getMembersObject()->isTutor($usr_id)) &&
                in_array($usr_id, $contact)
            ) {
                $this->getMembersObject()->updateContact($usr_id, true);
            } else {
                $this->getMembersObject()->updateContact($usr_id, false);
            }

            $this->updateLPFromStatus($usr_id, in_array($usr_id, $passed));
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "participants");
    }

    protected function updateLPFromStatus(int $usr_id, bool $has_passed) : void
    {
    }

    /**
     * Show confirmation screen for participants deletion
     */
    protected function confirmDeleteParticipants() : void
    {
        $participants = $this->initParticipantsFromPost();

        if (!count($participants)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_checkbox'), true);
            $this->ctrl->redirect($this, 'participants');
        }

        // Check last admin
        if (!$this->getMembersObject()->checkLastAdmin($participants)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt($this->getParentObject()->getType() . '_at_least_one_admin'), true);
            $this->ctrl->redirect($this, 'participants');
        }

        // if only position access is granted, show additional info
        if (!$this->checkPermissionBool('manage_members')) {
            $this->lng->loadLanguageModule('rbac');
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('rbac_info_only_position_access'));
        }

        // Access check for admin deletion
        if (
            !$this->access->checkAccess(
                'edit_permission',
                '',
                $this->getParentObject()->getRefId()
            ) &&
            !$this->getMembersObject()->isAdmin($this->user->getId())
        ) {
            foreach ($participants as $usr_id) {
                if ($this->getMembersObject()->isAdmin($usr_id)) {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt("msg_no_perm_perm"), true);
                    $this->ctrl->redirect($this, 'participants');
                }
            }
        }

        if (ilCourseReferencePathInfo::isReferenceMemberUpdateConfirmationRequired(
            $this->repository_object->getRefId(),
            $participants
        )) {
            $this->showDeleteParticipantsConfirmationWithLinkedCourses($participants);
            return;
        }

        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($this->ctrl->getFormAction($this, 'confirmDeleteParticipants'));
        $confirm->setHeaderText($this->lng->txt($this->getParentObject()->getType() . '_header_delete_members'));
        $confirm->setConfirm($this->lng->txt('confirm'), 'deleteParticipants');
        $confirm->setCancel($this->lng->txt('cancel'), 'participants');

        foreach ($participants as $usr_id) {
            $name = ilObjUser::_lookupName($usr_id);

            $confirm->addItem(
                'participants[]',
                (string) $name['user_id'],
                $name['lastname'] . ', ' . $name['firstname'] . ' [' . $name['login'] . ']',
                ilUtil::getImagePath('icon_usr.svg')
            );
        }
        $this->tpl->setContent($confirm->getHTML());
    }

    protected function deleteParticipants() : void
    {
        $participants = $this->initParticipantsFromPost();
        if (!count($participants)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, 'participants');
        }

        // If the user doesn't have the edit_permission and is not administrator, he may not remove
        // members who have the course administrator role
        if (
            !$this->access->checkAccess('edit_permission', '', $this->getParentObject()->getRefId()) &&
            !$this->getMembersObject()->isAdmin($this->user->getId())
        ) {
            foreach ($participants as $part) {
                if ($this->getMembersObject()->isAdmin($part)) {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_no_perm_perm'), true);
                    $this->ctrl->redirect($this, 'participants');
                }
            }
        }

        if (!$this->getMembersObject()->deleteParticipants($participants)) {
            $this->tpl->setOnScreenMessage('failure', 'Error deleting participants.', true);
            $this->ctrl->redirect($this, 'participants');
        } else {
            foreach ($participants as $usr_id) {
                $mail_type = 0;
                // @todo more generic
                switch ($this->getParentObject()->getType()) {
                    case 'crs':
                        $mail_type = ilCourseMembershipMailNotification::TYPE_DISMISS_MEMBER;
                        break;
                    case 'grp':
                        $mail_type = ilGroupMembershipMailNotification::TYPE_DISMISS_MEMBER;
                        break;
                    case 'lso':
                        $mail_type = ilLearningSequenceMembershipMailNotification::TYPE_DISMISS_MEMBER;
                        break;
                }
                $this->getMembersObject()->sendNotification($mail_type, $usr_id);
            }
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt($this->getParentObject()->getType() . "_members_deleted"), true);
        $this->ctrl->redirect($this, "participants");
    }

    protected function sendMailToSelectedUsers() : void
    {
        $participants = [];
        if ($this->http->wrapper()->post()->has('participants')) {
            $participants = $this->initParticipantsFromPost();
        } elseif ($this->http->wrapper()->query()->has('member_id')) {
            $participants = [$this->initMemberIdFromGet()];
        } elseif ($this->http->wrapper()->post()->has('subscribers')) {
            $participants = $this->initSubscribersFromPost();
        } elseif ($this->http->wrapper()->post()->has('waiting')) {
            $participants = $this->initWaitingListIdsFromPost();
        }
        if (!count($participants)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, 'participants');
        }

        $rcps = [];
        foreach ($participants as $usr_id) {
            $rcps[] = ilObjUser::_lookupLogin($usr_id);
        }
        $context_options = $this->getMailContextOptions();

        ilMailFormCall::setRecipients($rcps);
        ilUtil::redirect(
            ilMailFormCall::getRedirectTarget(
                $this,
                'participants',
                array(),
                array(
                    'type' => 'new',
                    'sig' => $this->createMailSignature()
                ),
                $context_options
            )
        );
    }

    protected function getMailContextOptions() : array
    {
        return [];
    }

    /**
     * Members map
     */
    protected function membersMap() : void
    {
        $this->activateSubTab($this->getParentObject()->getType() . "_members_map");
        if (!ilMapUtil::isActivated() || !$this->getParentObject()->getEnableMap()) {
            return;
        }

        $map = ilMapUtil::getMapGUI();
        $map->setMapId("course_map")
            ->setWidth("700px")
            ->setHeight("500px")
            ->setLatitude($this->getParentObject()->getLatitude())
            ->setLongitude($this->getParentObject()->getLongitude())
            ->setZoom($this->getParentObject()->getLocationZoom())
            ->setEnableTypeControl(true)
            ->setEnableNavigationControl(true)
            ->setEnableCentralMarker(true);

        $members = ilParticipants::getInstanceByObjId($this->getParentObject()->getId())->getParticipants();
        foreach ($members as $user_id) {
            $map->addUserMarker($user_id);
        }

        $this->tpl->setContent($map->getHtml());
        $this->tpl->setLeftContent($map->getUserListHtml());
    }

    protected function mailMembersBtn() : void
    {
        $this->showMailToMemberToolbarButton($this->toolbar, 'mailMembersBtn');
    }

    /**
     * Show participants toolbar
     */
    protected function showParticipantsToolbar() : void
    {
        if ($this->canAddOrSearchUsers()) {
            ilRepositorySearchGUI::fillAutoCompleteToolbar(
                $this,
                $this->toolbar,
                array(
                    'auto_complete_name' => $this->lng->txt('user'),
                    'user_type' => $this->getParentGUI()->getLocalRoles(),
                    'user_type_default' => $this->getDefaultRole(),
                    'submit_name' => $this->lng->txt('add')
                )
            );

            // spacer
            $this->toolbar->addSeparator();

            // search button
            $this->toolbar->addButton(
                $this->lng->txt($this->getParentObject()->getType() . "_search_users"),
                $this->ctrl->getLinkTargetByClass(
                    'ilRepositorySearchGUI',
                    'start'
                )
            );

            // separator
            $this->toolbar->addSeparator();
        }

        // print button
        $this->toolbar->addButton(
            $this->lng->txt($this->getParentObject()->getType() . "_print_list"),
            $this->ctrl->getLinkTarget($this, 'printMembers')
        );
        $this->showMailToMemberToolbarButton($this->toolbar, 'participants', false);
    }

    protected function showMemberExportToolbarButton(
        ilToolbarGUI $toolbar,
        ?string $a_back_cmd = null,
        bool $a_separator = false
    ) : void {
        if (
            $this->getParentObject()->getType() === 'crs' &&
            $this->getParentObject()->getShowMembersExport()) {
            if ($a_separator) {
                $toolbar->addSeparator();
            }

            if ($a_back_cmd) {
                $this->ctrl->setParameter($this, "back_cmd", $a_back_cmd);
            }
            $toolbar->addButton(
                $this->lng->txt($this->getParentObject()->getType() . '_print_list'),
                $this->ctrl->getLinkTarget($this, 'printForMembersOutput')
            );
        }
    }

    /**
     * Show mail to member toolbar button
     */
    protected function showMailToMemberToolbarButton(
        ilToolbarGUI $toolbar,
        ?string $a_back_cmd = null,
        bool $a_separator = false
    ) : void {
        $mail = new ilMail($this->user->getId());

        if (
            ($this->getParentObject()->getMailToMembersType() === 1) ||
            (
                $this->access->checkAccess('manage_members', "", $this->getParentObject()->getRefId()) &&
                $this->rbacsystem->checkAccess('internal_mail', $mail->getMailObjectReferenceId())
            )
        ) {
            if ($a_separator) {
                $toolbar->addSeparator();
            }

            if ($a_back_cmd !== null) {
                $this->ctrl->setParameter($this, "back_cmd", $a_back_cmd);
            }

            $toolbar->addButton(
                $this->lng->txt("mail_members"),
                $this->ctrl->getLinkTargetByClass('ilMailMemberSearchGUI', '')
            );
        }
    }

    /**
     * Create Mail signature
     * @todo better implementation
     */
    public function createMailSignature() : string
    {
        return $this->getParentGUI()->createMailSignature();
    }

    protected function getDefaultCommand() : string
    {
        $has_manage_members_permission = $this->checkRbacOrPositionAccessBool(
            'manage_members',
            'manage_members',
            $this->getParentObject()->getRefId()
        );
        if ($has_manage_members_permission) {
            return 'participants';
        }

        if ($this->getParentObject()->getShowMembers()) {
            return 'jump2UsersGallery';
        }
        return 'mailMembersBtn';
    }

    public function addMemberTab(ilTabsGUI $tabs, bool $a_is_participant = false) : void
    {
        $mail = new ilMail($this->user->getId());

        $member_tab_name = $this->getMemberTabName();

        $has_manage_members_permission = $this->checkRbacOrPositionAccessBool(
            'manage_members',
            'manage_members',
            $this->getParentObject()->getRefId()
        );

        if ($has_manage_members_permission) {
            $tabs->addTab(
                'members',
                $member_tab_name,
                $this->ctrl->getLinkTarget($this, '')
            );
        } elseif (
            $this->getParentObject()->getShowMembers() &&
            $a_is_participant
        ) {
            $tabs->addTab(
                'members',
                $member_tab_name,
                $this->ctrl->getLinkTargetByClass(array(get_class($this), 'ilusersgallerygui'), 'view')
            );
        } elseif (
            $this->getParentObject()->getMailToMembersType() === 1 &&
            $this->rbacsystem->checkAccess('internal_mail', $mail->getMailObjectReferenceId()) &&
            $a_is_participant
        ) {
            $tabs->addTab(
                'members',
                $member_tab_name,
                $this->ctrl->getLinkTarget($this, "mailMembersBtn")
            );
        }
    }

    protected function getMemberTabName() : string
    {
        return $this->lng->txt('members');
    }

    /**
     * Set sub tabs
     */
    protected function setSubTabs(ilTabsGUI $tabs) : void
    {
        if ($this->checkRbacOrPositionAccessBool(
            'manage_members',
            'manage_members',
            $this->getParentObject()->getRefId()
        )) {
            $tabs->addSubTabTarget(
                $this->getParentObject()->getType() . "_member_administration",
                $this->ctrl->getLinkTarget($this, 'participants'),
                "members",
                get_class($this)
            );

            // show group overview
            if ($this instanceof ilCourseMembershipGUI) {
                $tabs->addSubTabTarget(
                    "crs_members_groups",
                    $this->ctrl->getLinkTargetByClass("ilCourseParticipantsGroupsGUI", "show"),
                    "",
                    "ilCourseParticipantsGroupsGUI"
                );
            }

            $children = $this->tree->getSubTree(
                $this->tree->getNodeData($this->getParentObject()->getRefId()),
                false,
                ['sess']
            );
            if (count($children)) {
                $tabs->addSubTabTarget(
                    'events',
                    $this->ctrl->getLinkTargetByClass(array(get_class($this), 'ilsessionoverviewgui'), 'listSessions'),
                    '',
                    'ilsessionoverviewgui'
                );
            }
            $tabs->addSubTabTarget(
                $this->getParentObject()->getType() . '_members_gallery',
                $this->ctrl->getLinkTargetByClass(array(get_class($this), 'ilUsersGalleryGUI')),
                'view',
                'ilUsersGalleryGUI'
            );
        } elseif ($this->getParentObject()->getShowMembers()) {
            // gallery
            $tabs->addSubTabTarget(
                $this->getParentObject()->getType() . '_members_gallery',
                $this->ctrl->getLinkTargetByClass(array(get_class($this), 'ilUsersGalleryGUI')),
                'view',
                'ilUsersGalleryGUI'
            );
        }

        if (ilMapUtil::isActivated() && $this->getParentObject()->getEnableMap()) {
            $tabs->addSubTabTarget(
                $this->getParentObject()->getType() . '_members_map',
                $this->ctrl->getLinkTarget($this, 'membersMap'),
                "membersMap",
                get_class($this)
            );
        }

        if (ilPrivacySettings::getInstance()->checkExportAccess($this->getParentObject()->getRefId())) {
            $tabs->addSubTabTarget(
                'export_members',
                $this->ctrl->getLinkTargetByClass(array(get_class($this), 'ilmemberexportgui'), 'show'),
                '',
                'ilmemberexportgui'
            );
        }
    }

    /**
     * Required for member table guis.
     * @todo needs refactoring and should not be located in GUI classes
     */
    public function readMemberData(array $usr_ids, array $columns) : array
    {
        return $this->getParentGUI()->readMemberData($usr_ids, $columns);
    }

    /**
     * @return array<int, string>
     */
    public function getLocalRoles() : array
    {
        return $this->getParentGUI()->getLocalRoles();
    }

    /**
     * Parse table of subscription request
     */
    protected function parseSubscriberTable() : ?ilSubscriberTableGUI
    {
        $subscribers = $this->getMembersObject()->getSubscribers();
        $filtered_subscribers = $this->filterUserIdsByRbacOrPositionOfCurrentUser($subscribers);
        if (!count($filtered_subscribers)) {
            return null;
        }
        $subscriber = $this->initSubscriberTable();
        $subscriber->readSubscriberData(
            $filtered_subscribers
        );
        return $subscriber;
    }

    protected function initSubscriberTable() : ilSubscriberTableGUI
    {
        $subscriber = new ilSubscriberTableGUI($this, $this->getParentObject(), true, true);
        $subscriber->setTitle($this->lng->txt('group_new_registrations'));
        return $subscriber;
    }

    /**
     * Show subscription confirmation
     */
    public function confirmAssignSubscribers() : void
    {
        $subscribers = $this->initSubscribersFromPost();
        if (!count($subscribers)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("crs_no_subscribers_selected"), true);
            $this->ctrl->redirect($this, 'participants');
        }

        $c_gui = new ilConfirmationGUI();

        // set confirm/cancel commands
        $c_gui->setFormAction($this->ctrl->getFormAction($this, "assignSubscribers"));
        $c_gui->setHeaderText($this->lng->txt("info_assign_sure"));
        $c_gui->setCancel($this->lng->txt("cancel"), "participants");
        $c_gui->setConfirm($this->lng->txt("confirm"), "assignSubscribers");

        foreach ($subscribers as $subscriber) {
            $name = ilObjUser::_lookupName($subscriber);

            $c_gui->addItem(
                'subscribers[]',
                (string) $name['user_id'],
                $name['lastname'] . ', ' . $name['firstname'] . ' [' . $name['login'] . ']',
                ilUtil::getImagePath('icon_usr.svg')
            );
        }
        $this->tpl->setContent($c_gui->getHTML());
    }

    /**
     * Refuse subscriber confirmation
     */
    public function confirmRefuseSubscribers() : void
    {
        $subscribers = $this->initSubscribersFromPost();
        if (!count($subscribers)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("crs_no_subscribers_selected"), true);
            $this->ctrl->redirect($this, 'participants');
        }
        $this->lng->loadLanguageModule('mmbr');
        $c_gui = new ilConfirmationGUI();
        // set confirm/cancel commands
        $c_gui->setFormAction($this->ctrl->getFormAction($this, "refuseSubscribers"));
        $c_gui->setHeaderText($this->lng->txt("info_refuse_sure"));
        $c_gui->setCancel($this->lng->txt("cancel"), "participants");
        $c_gui->setConfirm($this->lng->txt("confirm"), "refuseSubscribers");

        foreach ($subscribers as $subscriber_id) {
            $name = ilObjUser::_lookupName($subscriber_id);

            $c_gui->addItem(
                'subscribers[]',
                (string) $name['user_id'],
                $name['lastname'] . ', ' . $name['firstname'] . ' [' . $name['login'] . ']',
                ilUtil::getImagePath('icon_usr.svg')
            );
        }

        $this->tpl->setContent($c_gui->getHTML());
    }

    protected function refuseSubscribers() : void
    {
        $subscribers = $this->initSubscribersFromPost();
        if (!count($subscribers)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("crs_no_subscribers_selected"), true);
            $this->ctrl->redirect($this, 'participants');
        }

        if (!$this->getMembersObject()->deleteSubscribers($subscribers)) {
            $this->tpl->setOnScreenMessage('failure', $this->error->getMessage(), true);
            $this->ctrl->redirect($this, 'participants');
        } else {
            foreach ($subscribers as $usr_id) {
                if ($this instanceof ilCourseMembershipGUI) {
                    $this->getMembersObject()->sendNotification(
                        ilCourseMembershipMailNotification::TYPE_REFUSED_SUBSCRIPTION_MEMBER,
                        $usr_id
                    );
                }
                if ($this instanceof ilGroupMembershipGUI) {
                    $this->getMembersObject()->sendNotification(
                        ilGroupMembershipMailNotification::TYPE_REFUSED_SUBSCRIPTION_MEMBER,
                        $usr_id
                    );
                }
                if ($this instanceof ilSessionMembershipGUI) {
                    $noti = new ilSessionMembershipMailNotification();
                    $noti->setRefId($this->getParentObject()->getRefId());
                    $noti->setRecipients(array($usr_id));
                    $noti->setType(ilSessionMembershipMailNotification::TYPE_REFUSED_SUBSCRIPTION_MEMBER);
                    $noti->send();
                }
                if ($this instanceof ilLearningSequenceMembershipGUI) {
                    $this->getMembersObject()->sendNotification(
                        ilLearningSequenceMembershipMailNotification::TYPE_REFUSED_SUBSCRIPTION_MEMBER,
                        $usr_id
                    );
                }
            }
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("crs_subscribers_deleted"), true);
        $this->ctrl->redirect($this, 'participants');
    }

    /**
     * Do assignment of subscription request
     */
    public function assignSubscribers() : void
    {
        $subscribers = $this->initSubscribersFromPost();
        if (!count($subscribers)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("crs_no_subscribers_selected"), true);
            $this->ctrl->redirect($this, 'participants');
        }

        if (!$this->getMembersObject()->assignSubscribers($subscribers)) {
            $this->tpl->setOnScreenMessage('failure', $this->error->getMessage(), true);
            $this->ctrl->redirect($this, 'participants');
        } else {
            foreach ($subscribers as $usr_id) {
                if ($this instanceof ilCourseMembershipGUI) {
                    $this->getMembersObject()->sendNotification(
                        ilCourseMembershipMailNotification::TYPE_ACCEPTED_SUBSCRIPTION_MEMBER,
                        $usr_id
                    );
                    $this->getParentObject()->checkLPStatusSync($usr_id);
                }
                if ($this instanceof ilGroupMembershipGUI) {
                    $this->getMembersObject()->sendNotification(
                        ilGroupMembershipMailNotification::TYPE_ACCEPTED_SUBSCRIPTION_MEMBER,
                        $usr_id
                    );
                }
                if ($this instanceof ilSessionMembershipGUI) {
                    // todo refactor to participants
                    $noti = new ilSessionMembershipMailNotification();
                    $noti->setRefId($this->getParentObject()->getRefId());
                    $noti->setRecipients(array($usr_id));
                    $noti->setType(ilSessionMembershipMailNotification::TYPE_ACCEPTED_SUBSCRIPTION_MEMBER);
                    $noti->send();
                }
            }
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("crs_subscribers_assigned"), true);
        $this->ctrl->redirect($this, 'participants');
    }

    /**
     * Parse table of subscription request
     */
    protected function parseWaitingListTable() : ?ilWaitingListTableGUI
    {
        $wait = $this->initWaitingList();

        $wait_users = $this->filterUserIdsByRbacOrPositionOfCurrentUser($wait->getUserIds());
        if (!count($wait_users)) {
            return null;
        }

        $waiting_table = new ilWaitingListTableGUI($this, $this->getParentObject(), $wait);
        $waiting_table->setUserIds(
            $wait_users
        );
        $waiting_table->readUserData();
        $waiting_table->setTitle($this->lng->txt('crs_waiting_list'));

        return $waiting_table;
    }

    /**
     * Assign from waiting list (confirmatoin)
     */
    public function confirmAssignFromWaitingList() : void
    {
        $waiting_list_ids = $this->initWaitingListIdsFromPost();
        if (!count($waiting_list_ids)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("crs_no_users_selected"), true);
            $this->ctrl->redirect($this, 'participants');
        }
        $c_gui = new ilConfirmationGUI();
        // set confirm/cancel commands
        $c_gui->setFormAction($this->ctrl->getFormAction($this, "assignFromWaitingList"));
        $c_gui->setHeaderText($this->lng->txt("info_assign_sure"));
        $c_gui->setCancel($this->lng->txt("cancel"), "participants");
        $c_gui->setConfirm($this->lng->txt("confirm"), "assignFromWaitingList");

        foreach ($waiting_list_ids as $waiting) {
            $name = ilObjUser::_lookupName($waiting);

            $c_gui->addItem(
                'waiting[]',
                (string) $name['user_id'],
                $name['lastname'] . ', ' . $name['firstname'] . ' [' . $name['login'] . ']',
                ilUtil::getImagePath('icon_usr.svg')
            );
        }

        $this->tpl->setContent($c_gui->getHTML());
    }

    /**
     * Assign from waiting list
     */
    public function assignFromWaitingList() : void
    {
        $waiting_list_ids = $this->initWaitingListIdsFromPost();
        if (!count($waiting_list_ids)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("crs_no_users_selected"), true);
            $this->ctrl->redirect($this, 'participants');
        }

        $waiting_list = $this->initWaitingList();

        $added_users = 0;
        foreach ($waiting_list_ids as $user_id) {
            if (!$tmp_obj = ilObjectFactory::getInstanceByObjId((int) $user_id, false)) {
                continue;
            }
            if ($this->getMembersObject()->isAssigned((int) $user_id)) {
                continue;
            }

            if ($this instanceof ilCourseMembershipGUI) {
                $this->getMembersObject()->add($user_id, ilParticipants::IL_CRS_MEMBER);
                $this->getMembersObject()->sendNotification(
                    ilCourseMembershipMailNotification::TYPE_ADMISSION_MEMBER,
                    (int) $user_id,
                    true
                );
                $this->getParentObject()->checkLPStatusSync((int) $user_id);
            }
            if ($this instanceof ilGroupMembershipGUI) {
                $this->getMembersObject()->add($user_id, ilParticipants::IL_GRP_MEMBER);
                $this->getMembersObject()->sendNotification(
                    ilGroupMembershipMailNotification::TYPE_ACCEPTED_SUBSCRIPTION_MEMBER,
                    (int) $user_id,
                    true
                );
            }
            if ($this instanceof ilSessionMembershipGUI) {
                $this->getMembersObject()->register((int) $user_id);
                $noti = new ilSessionMembershipMailNotification();
                $noti->setRefId($this->getParentObject()->getRefId());
                $noti->setRecipients(array($user_id));
                $noti->setType(ilSessionMembershipMailNotification::TYPE_ACCEPTED_SUBSCRIPTION_MEMBER);
                $noti->send();
            }

            $waiting_list->removeFromList((int) $user_id);
            ++$added_users;
        }

        if ($added_users) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("crs_users_added"), true);
        } else {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("crs_users_already_assigned"), true);
        }
        $this->ctrl->redirect($this, 'participants');
    }

    /**
     * Refuse from waiting list (confirmation)
     */
    public function confirmRefuseFromList() : void
    {
        $waiting_list_ids = $this->initWaitingListIdsFromPost();
        if (!count($waiting_list_ids)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, 'participants');
        }
        $this->lng->loadLanguageModule('mmbr');
        $c_gui = new ilConfirmationGUI();

        // set confirm/cancel commands
        $c_gui->setFormAction($this->ctrl->getFormAction($this, "refuseFromList"));
        $c_gui->setHeaderText($this->lng->txt("info_refuse_sure"));
        $c_gui->setCancel($this->lng->txt("cancel"), "participants");
        $c_gui->setConfirm($this->lng->txt("confirm"), "refuseFromList");

        foreach ($waiting_list_ids as $waiting) {
            $name = ilObjUser::_lookupName($waiting);

            $c_gui->addItem(
                'waiting[]',
                (string) $name['user_id'],
                $name['lastname'] . ', ' . $name['firstname'] . ' [' . $name['login'] . ']',
                ilUtil::getImagePath('icon_usr.svg')
            );
        }
        $this->tpl->setContent($c_gui->getHTML());
    }

    /**
     * refuse from waiting list
     */
    protected function refuseFromList() : void
    {
        $waiting_list_ids = $this->initWaitingListIdsFromPost();
        if (!count($waiting_list_ids)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_checkbox'), true);
            $this->ctrl->redirect($this, 'participants');
        }

        $waiting_list = $this->initWaitingList();

        foreach ($waiting_list_ids as $user_id) {
            $waiting_list->removeFromList((int) $user_id);

            if ($this instanceof ilCourseMembershipGUI) {
                $this->getMembersObject()->sendNotification(
                    ilCourseMembershipMailNotification::TYPE_REFUSED_SUBSCRIPTION_MEMBER,
                    (int) $user_id,
                    true
                );
            }
            if ($this instanceof ilGroupMembershipGUI) {
                $this->getMembersObject()->sendNotification(
                    ilGroupMembershipMailNotification::TYPE_REFUSED_SUBSCRIPTION_MEMBER,
                    (int) $user_id,
                    true
                );
            }
            if ($this instanceof ilSessionMembershipGUI) {
                $noti = new ilSessionMembershipMailNotification();
                $noti->setRefId($this->getParentObject()->getRefId());
                $noti->setRecipients(array($user_id));
                $noti->setType(ilSessionMembershipMailNotification::TYPE_REFUSED_SUBSCRIPTION_MEMBER);
                $noti->send();
            }
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('crs_users_removed_from_list'), true);
        $this->ctrl->redirect($this, 'participants');
    }

    /**
     * Add selected users to user clipboard
     */
    protected function addToClipboard() : void
    {
        // begin-patch clipboard
        $users = [];
        if ($this->http->wrapper()->post()->has('participants')) {
            $users = $this->initParticipantsFromPost();
        } elseif ($this->http->wrapper()->post()->has('subscribers')) {
            $users = $this->initSubscribersFromPost();
        } elseif ($this->http->wrapper()->post()->has('waiting')) {
            $users = $this->initWaitingListIdsFromPost();
        }
        // end-patch clipboard
        if (!count($users)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'), true);
            $this->ctrl->redirect($this, 'participants');
        }
        $clip = ilUserClipboard::getInstance($this->user->getId());
        $clip->add($users);
        $clip->save();

        $this->lng->loadLanguageModule('user');
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('clipboard_user_added'), true);
        $this->ctrl->redirect($this, 'participants');
    }

    protected function getDefaultRole() : ?int
    {
        return null;
    }

    protected function activateSubTab(string $a_sub_tab) : void
    {
        $this->tabs->activateSubTab($a_sub_tab);
    }

    /**
     * @todo: refactor to own class
     */
    protected function printMembers() : void
    {
        $this->checkPermission('read');

        $this->tabs->clearTargets();

        $this->tabs->setBackTarget(
            $this->lng->txt('back'),
            $this->ctrl->getLinkTarget($this, 'participants')
        );

        $list = $this->initAttendanceList();
        $form = $list->initForm('printMembersOutput');
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * print members output
     */
    protected function printMembersOutput() : void
    {
        $this->tabs->clearTargets();
        $this->tabs->setBackTarget(
            $this->lng->txt('back'),
            $this->ctrl->getLinkTarget($this, 'participants')
        );

        $list = $this->initAttendanceList();
        $list->initFromForm();
        $list->setCallback([$this, 'getAttendanceListUserData']);
        $this->member_data = $this->getPrintMemberData(
            $this->filterUserIdsByRbacOrPositionOfCurrentUser(
                $this->getMembersObject()->getParticipants()
            )
        );

        $list->getNonMemberUserData($this->member_data);
        $list->getFullscreenHTML();
    }

    /**
     * print members output
     */
    protected function printForMembersOutput() : void
    {
        $this->tabs->clearTargets();
        $this->tabs->setBackTarget(
            $this->lng->txt('back'),
            $this->ctrl->getLinkTarget($this, 'jump2UsersGallery')
        );

        $list = $this->initAttendanceList();
        $list->setTitle($this->lng->txt('obj_' . $this->getParentObject()->getType()) . ': ' . $this->getParentObject()->getTitle());
        $list->setId('0');
        $list->initForm('printForMembersOutput');
        $list->initFromForm();
        $list->setCallback([$this, 'getAttendanceListUserData']);
        $this->member_data = $this->getPrintMemberData($this->getMembersObject()->getParticipants());
        $list->getNonMemberUserData($this->member_data);
        $list->getFullscreenHTML();
    }

    protected function jump2UsersGallery() : void
    {
        $this->ctrl->redirectByClass('ilUsersGalleryGUI');
    }

    protected function initAttendanceList(bool $a_for_members = false) : ?ilAttendanceList
    {
        global $DIC;

        $waiting_list = $this->initWaitingList();

        if ($this instanceof ilSessionMembershipGUI) {
            $member_id = $DIC->repositoryTree()->checkForParentType(
                $this->getParentObject()->getRefId(),
                'grp'
            );
            if (!$member_id) {
                $member_id = $DIC->repositoryTree()->checkForParentType(
                    $this->getParentObject()->getRefId(),
                    'crs'
                );
            }
            if (!$member_id) {
                $DIC->logger()->sess()->warning('Cannot find parent course or group for ref_id: ' . $this->getParentObject()->getRefId());
                $member_id = $this->getParentObject()->getRefId();
            }
            $part = ilParticipants::getInstance($member_id);

            $list = new ilAttendanceList(
                $this,
                $this->getParentObject(),
                $part,
                $waiting_list
            );
        } else {
            $list = new ilAttendanceList(
                $this,
                $this->getParentObject(),
                $this->getMembersObject(),
                $waiting_list
            );
        }
        $list->setId($this->getParentObject()->getType() . '_memlist_' . $this->getParentObject()->getId());

        $list->setTitle(
            $this->lng->txt($this->getParentObject()->getType() . '_members_print_title'),
            $this->lng->txt('obj_' . $this->getParentObject()->getType()) . ': ' . $this->getParentObject()->getTitle()
        );

        $show_tracking =
            (ilObjUserTracking::_enabledLearningProgress() and ilObjUserTracking::_enabledUserRelatedData());
        if ($show_tracking) {
            $olp = ilObjectLP::getInstance($this->getParentObject()->getId());
            $show_tracking = $olp->isActive();
        }
        if ($show_tracking) {
            $list->addPreset('progress', $this->lng->txt('learning_progress'), true);
        }

        $privacy = ilPrivacySettings::getInstance();
        if ($privacy->enabledAccessTimesByType($this->getParentObject()->getType())) {
            $list->addPreset('access', $this->lng->txt('last_access'), true);
        }

        switch ($this->getParentObject()->getType()) {
            case 'crs':
                $list->addPreset('status', $this->lng->txt('crs_status'), true);
                $list->addPreset('passed', $this->lng->txt('crs_passed'), true);
                break;

            case 'sess':
                $list->addPreset('mark', $this->lng->txt('trac_mark'), true);
                $list->addPreset('comment', $this->lng->txt('trac_comment'), true);
                if ($this->getParentObject()->enabledRegistration()) {
                    $list->addPreset('registered', $this->lng->txt('event_tbl_registered'), true);
                }
                $list->addPreset('participated', $this->lng->txt('event_tbl_participated'), true);
                $list->addBlank($this->lng->txt('sess_signature'));

                $list->addUserFilter('registered', $this->lng->txt('event_list_registered_only'));
                break;

            case 'grp':
            default:
                break;
        }
        return $list;
    }
}
