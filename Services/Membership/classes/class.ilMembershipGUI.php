<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Base class for member tab content
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilMembershipGUI
{
    /**
     * @var ilObject
     */
    private $repository_object = null;
    
    /**
     * @var ilObjectGUI
     */
    private $repository_gui = null;
    
    /**
     * @var ilLanguage
     */
    protected $lng = null;
    
    /**
     * @var ilCtrl
     */
    protected $ctrl = null;
    
    /**
     * @var ilLogger
     */
    protected $logger = null;
    
    /**
     * @var ilTemplate
     */
    protected $tpl;
    
    /**
     * @var ilAccessHandler
     */
    protected $access;
    
    
    /**
     * Constructor
     * @param ilObject $repository_obj
     */
    public function __construct(ilObjectGUI $repository_gui, ilObject $repository_obj)
    {
        global $DIC;

        $this->repository_gui = $repository_gui;
        $this->repository_object = $repository_obj;
        
        $this->lng = $GLOBALS['DIC']->language();
        $this->lng->loadLanguageModule('crs');
        $this->lng->loadLanguageModule($this->getParentObject()->getType());
        $this->lng->loadLanguageModule('trac');
        $this->tpl = $GLOBALS['DIC']->ui()->mainTemplate();
        $this->ctrl = $GLOBALS['DIC']->ctrl();
        $this->logger = $DIC->logger()->mmbr();
        $this->access = $GLOBALS['DIC']->access();
    }
    
    /**
     * @return ilLanguage
     */
    protected function getLanguage()
    {
        return $this->lng;
    }
    
    /**
     * @return ilCtrl
     */
    protected function getCtrl()
    {
        return $this->ctrl;
    }

    /**
     * @return \ilLogger
     */
    protected function getLogger()
    {
        return $this->logger;
    }


    /**
     * Get parent gui
     * @return ilObjectGUI
     */
    public function getParentGUI()
    {
        return $this->repository_gui;
    }
    
    /**
     * Get parent object
     * @return ilObject
     */
    public function getParentObject()
    {
        return $this->repository_object;
    }
    
    /**
     * Get member object
     * @return ilParticipants
     */
    public function getMembersObject()
    {
        if ($this->participants instanceof ilParticipants) {
            return $this->participants;
        }
        return $this->participants = ilParticipants::getInstance($this->getParentObject()->getRefId());
    }

    /**
     * @return null
     */
    protected function getMailMemberRoles()
    {
        return null;
    }
    
    /**
     * Check permission
     * @param type $a_permission
     * @param type $a_cmd
     * @param type $a_type
     * @param type $a_ref_id
     */
    protected function checkPermissionBool($a_permission, $a_cmd = '', $a_type = '', $a_ref_id = 0)
    {
        if (!$a_ref_id) {
            $a_ref_id = $this->getParentObject()->getRefId();
        }
        return $this->access->checkAccess($a_permission, $a_cmd, $a_ref_id);
    }
    
    /**
     * Check if rbac or position access is granted.
     * @param string $a_rbac_perm
     * @param string $a_pos_perm
     * @param int $a_ref_id
     */
    protected function checkRbacOrPositionAccessBool($a_rbac_perm, $a_pos_perm, $a_ref_id = 0)
    {
        if (!$a_ref_id) {
            $a_ref_id = $this->getParentObject()->getRefId();
        }
        return $this->access->checkRbacOrPositionPermissionAccess($a_rbac_perm, $a_pos_perm, $a_ref_id);
    }

    /**
     * Check permission
     * If not granted redirect to parent gui
     *
     * @param string $a_permission
     * @param string $a_cmd
     */
    protected function checkPermission($a_permission, $a_cmd = "")
    {
        if (!$this->checkPermissionBool($a_permission, $a_cmd)) {
            ilUtil::sendFailure($this->lng->txt('no_permission'), true);
            $this->ctrl->redirect($this->getParentGUI());
        }
    }

    /**
     * check rbac or position access
     *
     * @param $a_rbac_perm
     * @param $a_pos_perm
     */
    protected function checkRbacOrPermissionAccess($a_rbac_perm, $a_pos_perm)
    {
        if (!$this->checkRbacOrPositionAccessBool($a_rbac_perm, $a_pos_perm)) {
            ilUtil::sendFailure($this->lng->txt('no_permission'), true);
            $this->ctrl->redirect($this->getParentGUI());
        }
    }

    
    
    /**
     * Check if current user is allowed to add / search users
     * @return bool
     */
    protected function canAddOrSearchUsers()
    {
        return $this->checkPermissionBool('manage_members');
    }
    
    
    /**
     * Filter user ids by access
     * @param int[] $a_usr_ids
     * @return int[]
     */
    public function filterUserIdsByRbacOrPositionOfCurrentUser($a_user_ids)
    {
        return $a_user_ids;
    }
    
    /**
     * Execute command
     */
    public function executeCommand()
    {
        /**
         * @var ilTabsGUI
         */
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $ilErr = $DIC['ilErr'];
        $ilAccess = $DIC['ilAccess'];
        $rbacsystem = $DIC['rbacsystem'];
        $ilTabs = $DIC['ilTabs'];
        
        $cmd = $this->ctrl->getCmd('participants');
        $next_class = $this->ctrl->getNextClass();
        
        switch ($next_class) {
            case 'ilrepositorysearchgui':

                $this->checkPermission('manage_members');
                
                include_once('./Services/Search/classes/class.ilRepositorySearchGUI.php');
                include_once './Services/Membership/classes/class.ilParticipants.php';
                $rep_search = new ilRepositorySearchGUI();
                $rep_search->addUserAccessFilterCallable([$this,'filterUserIdsByRbacOrPositionOfCurrentUser']);

                $participants = $this->getMembersObject();
                if (
                    $participants->isAdmin($GLOBALS['DIC']['ilUser']->getId()) ||
                    $ilAccess->checkAccess('manage_members', '', $this->getParentObject()->getRefId())
                ) {
                    $rep_search->setCallback(
                        $this,
                        'assignMembers',
                        $this->getParentGUI()->getLocalRoles(),
                        (string) $this->getDefaultRole()
                    );
                } else {
                    //#18445 excludes admin role
                    $rep_search->setCallback(
                        $this,
                        'assignMembers',
                        $this->getLocalRoles(array($this->getParentObject()->getDefaultAdminRole())),
                        (string) $this->getDefaultRole()
                    );
                }
                
                // Set tabs
                $this->ctrl->setReturn($this, 'participants');
                $ret = $this->ctrl->forwardCommand($rep_search);
                break;
            
            
            case 'ilmailmembersearchgui':
                $ilTabs->clearTargets();
                $ilTabs->setBackTarget(
                    $this->lng->txt('btn_back'),
                    $this->ctrl->getLinkTarget($this, $this->getDefaultCommand())
                );

                $mail = new ilMail($ilUser->getId());
                if (!(
                    $this->getParentObject()->getMailToMembersType() == ilCourseConstants::MAIL_ALLOWED_ALL ||
                    $ilAccess->checkAccess('manage_members', "", $this->getParentObject()->getRefId())
                ) ||
                    !$rbacsystem->checkAccess(
                        'internal_mail',
                        $mail->getMailObjectReferenceId()
                    )) {
                    $ilErr->raiseError($this->lng->txt("msg_no_perm_read"), $ilErr->MESSAGE);
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
                
                $this->setSubTabs($GLOBALS['DIC']['ilTabs']);
                $tabs = $GLOBALS['DIC']->tabs()->setSubTabActive(
                    $this->getParentObject()->getType() . '_members_gallery'
                );
                
                $is_admin = (bool) $this->checkRbacOrPositionAccessBool('manage_members', 'manage_members');
                $is_participant = (bool) ilParticipants::_isParticipant($this->getParentObject()->getRefId(), $ilUser->getId());
                if (
                    !$is_admin &&
                    (
                        $this->getParentObject()->getShowMembers() == 0 ||
                        !$is_participant
                    )
                ) {
                    $ilErr->raiseError($this->lng->txt('msg_no_perm_read'), $ilErr->MESSAGE);
                }

                $this->showMailToMemberToolbarButton($GLOBALS['DIC']['ilToolbar'], 'jump2UsersGallery');
                $this->showMemberExportToolbarButton($GLOBALS['DIC']['ilToolbar'], 'jump2UsersGallery');

                require_once 'Services/User/Gallery/classes/class.ilUsersGalleryGUI.php';
                require_once 'Services/User/Gallery/classes/class.ilUsersGalleryParticipants.php';


                $provider = new ilUsersGalleryParticipants($this->getParentObject()->getMembersObject());
                $gallery_gui = new ilUsersGalleryGUI($provider);
                $this->ctrl->forwardCommand($gallery_gui);
                break;
                
            case 'ilcourseparticipantsgroupsgui':

                $this->setSubTabs($GLOBALS['DIC']['ilTabs']);
                $this->checkRbacOrPermissionAccess('manage_members', 'manage_members');
                
                
                include_once './Modules/Course/classes/class.ilCourseParticipantsGroupsGUI.php';
                $cmg_gui = new ilCourseParticipantsGroupsGUI($this->getParentObject()->getRefId());
                if ($cmd == "show" || $cmd = "") {
                    $this->showMailToMemberToolbarButton($GLOBALS['DIC']['ilToolbar']);
                }
                $this->ctrl->forwardCommand($cmg_gui);
                break;
                
            case 'ilsessionoverviewgui':

                $this->setSubTabs($GLOBALS['DIC']['ilTabs']);
                $this->checkRbacOrPermissionAccess('manage_members', 'manage_members');

                include_once './Services/Membership/classes/class.ilParticipants.php';
                $prt = ilParticipants::getInstance($this->getParentObject()->getRefId());
            
                include_once('./Modules/Session/classes/class.ilSessionOverviewGUI.php');
                $overview = new ilSessionOverviewGUI($this->getParentObject()->getRefId(), $prt);
                $this->ctrl->forwardCommand($overview);
                break;
            
            case 'ilmemberexportgui':

                $this->setSubTabs($GLOBALS['DIC']['ilTabs']);
                $this->checkRbacOrPermissionAccess('manage_members', 'manage_members');

                include_once('./Services/Membership/classes/Export/class.ilMemberExportGUI.php');
                $export = new ilMemberExportGUI($this->getParentObject()->getRefId());
                $this->ctrl->forwardCommand($export);
                break;

            case 'ilobjectcustomuserfieldsgui':
                
                $this->setSubTabs($GLOBALS['DIC']['ilTabs']);
                $this->checkRbacOrPermissionAccess('manage_members', 'manage_members');
                $this->activateSubTab($this->getParentObject()->getType() . "_member_administration");
                $this->ctrl->setReturn($this, 'participants');

                include_once './Services/Membership/classes/class.ilObjectCustomUserFieldsGUI.php';
                $cdf_gui = new ilObjectCustomUserFieldsGUI($this->getParentGUI()->object->getId());
                $this->ctrl->forwardCommand($cdf_gui);
                break;
                
            default:

                $this->setSubTabs($GLOBALS['DIC']['ilTabs']);

                //exclude mailMembersBtn cmd from this check
                if (
                    $cmd == "mailMembersBtn" ||
                    $cmd == 'membersMap' ||
                    $cmd == 'printForMembersOutput' ||
                    $cmd == 'jump2UsersGallery'
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
    protected function participants()
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
    
    /**
     * Apply filter for participant table
     */
    protected function participantsApplyFilter()
    {
        $table = $this->initParticipantTableGUI();
        $table->resetOffset();
        $table->writeFilterToSession();
        
        $this->participants();
    }
    
    /**
     * reset participants filter
     */
    protected function participantsResetFilter()
    {
        $table = $this->initParticipantTableGUI();
        $table->resetOffset();
        $table->resetFilter();
        
        $this->participants();
    }


    /**
     * Edit one participant
     */
    protected function editMember()
    {
        $this->activateSubTab($this->getParentObject()->getType() . "_member_administration");
        return $this->editParticipants(array($_REQUEST['member_id']));
    }
    
    /**
     * Edit participants
     * @param array $post_participants
     */
    protected function editParticipants($post_participants = array())
    {
        if (!$post_participants) {
            $post_participants = (array) $_POST['participants'];
        }

        $real_participants = $this->getMembersObject()->getParticipants();
        $participants = array_intersect((array) $post_participants, (array) $real_participants);
        
        if (!count($participants)) {
            ilUtil::sendFailure($this->lng->txt('no_checkbox'), true);
            $this->ctrl->redirect($this, 'participants');
        }
        $table = $this->initEditParticipantTableGUI($participants);
        $this->tpl->setContent($table->getHTML());
        return true;
    }
    
    /**
     * update members
     *
     * @access public
     * @param
     * @return
     */
    public function updateParticipants()
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $rbacreview = $DIC['rbacreview'];
        $ilUser = $DIC['ilUser'];
        $ilAccess = $DIC['ilAccess'];
                
        if (!array_key_exists('participants', $_POST) || !count($_POST['participants'])) {
            ilUtil::sendFailure($this->lng->txt('no_checkbox'), true);
            $this->ctrl->redirect($this, 'participants');
        }
        
        $notifications = $_POST['notification'] ? $_POST['notification'] : array();
        $passed = $_POST['passed'] ? $_POST['passed'] : array();
        $blocked = $_POST['blocked'] ? $_POST['blocked'] : array();
        $contact = $_POST['contact'] ? $_POST['contact'] : array();

        // Determine whether the user has the 'edit_permission' permission
        $hasEditPermissionAccess =
            (
                $ilAccess->checkAccess('edit_permission', '', $this->getParentObject()->getRefId()) or
                $this->getMembersObject()->isAdmin($ilUser->getId())
            );

        // Get all assignable local roles of the object, and
        // determine the role id of the course administrator role.
        $assignableLocalRoles = array();
        $adminRoleId = $this->getParentObject()->getDefaultAdminRole();
        foreach ($this->getLocalTypeRole(false) as $title => $role_id) {
            $assignableLocalRoles[$role_id] = $title;
        }
                
        // Validate the user ids and role ids in the post data
        foreach ($_POST['participants'] as $usr_id) {
            $memberIsAdmin = $rbacreview->isAssigned($usr_id, $adminRoleId);
                        
            // If the current user doesn't have the 'edit_permission'
            // permission, make sure he doesn't remove the course
            // administrator role of members who are course administrator.
            if (!$hasEditPermissionAccess && $memberIsAdmin &&
                !in_array($adminRoleId, $_POST['roles'][$usr_id])
            ) {
                $_POST['roles'][$usr_id][] = $adminRoleId;
            }
                        
            // Validate the role ids in the post data
            foreach ((array) $_POST['roles'][$usr_id] as $role_id) {
                if (!array_key_exists($role_id, $assignableLocalRoles)) {
                    ilUtil::sendFailure($this->lng->txt('msg_no_perm_perm'), true);
                    $this->ctrl->redirect($this, 'participants');
                }
                if (!$hasEditPermissionAccess &&
                    $role_id == $adminRoleId &&
                    !$memberIsAdmin) {
                    ilUtil::sendFailure($this->lng->txt('msg_no_perm_perm'));
                    $this->ctrl->redirect($this, 'participants');
                }
            }
        }
        
        $has_admin = false;
        foreach ($this->getMembersObject()->getAdmins() as $admin_id) {
            if (!isset($_POST['roles'][$admin_id])) {
                $has_admin = true;
                break;
            }
            if (in_array($adminRoleId, $_POST['roles'][$admin_id])) {
                $has_admin = true;
                break;
            }
        }

        if (!$has_admin && is_array($_POST['roles'])) {
            foreach ($_POST['roles'] as $usrId => $roleIdsToBeAssigned) {
                if (in_array($adminRoleId, $roleIdsToBeAssigned)) {
                    $has_admin = true;
                    break;
                }
            }
        }

        if (!$has_admin) {
            ilUtil::sendFailure($this->lng->txt($this->getParentObject()->getType() . '_min_one_admin'), true);
            $this->ctrl->redirect($this, 'participants');
        }

        foreach ($_POST['participants'] as $usr_id) {
            $this->getMembersObject()->updateRoleAssignments($usr_id, (array) $_POST['roles'][$usr_id]);
            
            // Disable notification for all of them
            $this->getMembersObject()->updateNotification($usr_id, 0);
            if (($this->getMembersObject()->isTutor($usr_id) or $this->getMembersObject()->isAdmin($usr_id)) and in_array($usr_id, $notifications)) {
                $this->getMembersObject()->updateNotification($usr_id, 1);
            }
            
            $this->getMembersObject()->updateBlocked($usr_id, 0);
            if ((!$this->getMembersObject()->isAdmin($usr_id) and !$this->getMembersObject()->isTutor($usr_id)) and in_array($usr_id, $blocked)) {
                $this->getMembersObject()->updateBlocked($usr_id, 1);
            }
            
            if ($this instanceof ilCourseMembershipGUI) {
                $this->getMembersObject()->updatePassed($usr_id, in_array($usr_id, $passed), true);
                $this->getMembersObject()->sendNotification(
                    $this->getMembersObject()->NOTIFY_STATUS_CHANGED,
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
        ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "participants");
    }
    
    /**
     * Show confirmation screen for participants deletion
     */
    protected function confirmDeleteParticipants()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilUser = $DIC['ilUser'];
        
        $participants = (array) $_POST['participants'];
        
        if (!count($participants)) {
            ilUtil::sendFailure($this->lng->txt('no_checkbox'), true);
            $this->ctrl->redirect($this, 'participants');
        }

        // Check last admin
        if (!$this->getMembersObject()->checkLastAdmin($participants)) {
            ilUtil::sendFailure($this->lng->txt($this->getParentObject()->getType() . '_at_least_one_admin'), true);
            $this->ctrl->redirect($this, 'participants');
        }
        
        // if only position access is granted, show additional info
        if (!$this->checkPermissionBool('manage_members')) {
            $this->lng->loadLanguageModule('rbac');
            ilUtil::sendInfo($this->lng->txt('rbac_info_only_position_access'));
        }
        
        
        // Access check for admin deletion
        if (
            !$ilAccess->checkAccess(
                'edit_permission',
                '',
                $this->getParentObject()->getRefId()
            ) &&
            !$this->getMembersObject()->isAdmin($GLOBALS['DIC']['ilUser']->getId())
        ) {
            foreach ($participants as $usr_id) {
                if ($this->getMembersObject()->isAdmin($usr_id)) {
                    ilUtil::sendFailure($this->lng->txt("msg_no_perm_perm"), true);
                    $this->ctrl->redirect($this, 'participants');
                }
            }
        }

        if (ilCourseReferencePathInfo::isReferenceMemberUpdateConfirmationRequired(
            $this->repository_object->getRefId(),
            $participants
        )) {
            return $this->showDeleteParticipantsConfirmationWithLinkedCourses($participants);
        }

        include_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');
        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($this->ctrl->getFormAction($this, 'confirmDeleteParticipants'));
        $confirm->setHeaderText($this->lng->txt($this->getParentObject()->getType() . '_header_delete_members'));
        $confirm->setConfirm($this->lng->txt('confirm'), 'deleteParticipants');
        $confirm->setCancel($this->lng->txt('cancel'), 'participants');
        
        foreach ($participants as $usr_id) {
            $name = ilObjUser::_lookupName($usr_id);

            $confirm->addItem(
                'participants[]',
                $name['user_id'],
                $name['lastname'] . ', ' . $name['firstname'] . ' [' . $name['login'] . ']',
                ilUtil::getImagePath('icon_usr.svg')
            );
        }
        
        $this->tpl->setContent($confirm->getHTML());
    }
    
    protected function deleteParticipants()
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        $rbacsystem = $DIC['rbacsystem'];
        $ilAccess = $DIC['ilAccess'];
        $ilUser = $DIC['ilUser'];
                
        $participants = (array) $_POST['participants'];
        
        if (!is_array($participants) or !count($participants)) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, 'participants');
        }
        
        // If the user doesn't have the edit_permission and is not administrator, he may not remove
        // members who have the course administrator role
        if (
            !$ilAccess->checkAccess('edit_permission', '', $this->getParentObject()->getRefId()) &&
            !$this->getMembersObject()->isAdmin($GLOBALS['DIC']['ilUser']->getId())
        ) {
            foreach ($participants as $part) {
                if ($this->getMembersObject()->isAdmin($part)) {
                    ilUtil::sendFailure($this->lng->txt('msg_no_perm_perm'), true);
                    $this->ctrl->redirect($this, 'participants');
                }
            }
        }
        
        if (!$this->getMembersObject()->deleteParticipants($participants)) {
            ilUtil::sendFailure('Error deleting participants.', true);
            $this->ctrl->redirect($this, 'participants');
        } else {
            foreach ((array) $_POST["participants"] as $usr_id) {
                $mail_type = 0;
                // @todo more generic
                switch ($this->getParentObject()->getType()) {
                    case 'crs':
                        $mail_type = $this->getMembersObject()->NOTIFY_DISMISS_MEMBER;
                        break;
                    case 'grp':
                        include_once './Modules/Group/classes/class.ilGroupMembershipMailNotification.php';
                        $mail_type = ilGroupMembershipMailNotification::TYPE_DISMISS_MEMBER;
                        break;
                    case 'lso':
                        $mail_type = ilLearningSequenceMembershipMailNotification::TYPE_DISMISS_MEMBER;
                        break;
                }
                $this->getMembersObject()->sendNotification($mail_type, $usr_id);
            }
        }
        ilUtil::sendSuccess($this->lng->txt($this->getParentObject()->getType() . "_members_deleted"), true);
        $this->ctrl->redirect($this, "participants");

        return true;
    }
    
    /**
     * Send mail to selected users
     */
    protected function sendMailToSelectedUsers()
    {
        $participants = [];
        if ($_POST['participants']) {
            $participants = (array) $_POST['participants'];
        } elseif ($_POST['subscribers']) {
            $participants = (array) $_POST['subscribers'];
        } elseif ($_POST['waiting']) {
            $participants = (array) $_POST['waiting'];
        } elseif ($_GET['member_id']) {
            $participants = array($_GET['member_id']);
        }

        if (!count($participants)) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, 'participants');
        }
        
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

    /**
     * @return array
     */
    protected function getMailContextOptions() : array
    {
        return [];
    }

    
    /**
     * Members map
     */
    protected function membersMap()
    {
        global $DIC;

        $tpl = $DIC['tpl'];
        $this->activateSubTab($this->getParentObject()->getType() . "_members_map");
        include_once("./Services/Maps/classes/class.ilMapUtil.php");
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

        include_once './Services/Membership/classes/class.ilParticipants.php';
        $members = ilParticipants::getInstanceByObjId($this->getParentObject()->getId())->getParticipants();
        foreach ((array) $members as $user_id) {
            $map->addUserMarker($user_id);
        }

        $tpl->setContent($map->getHTML());
        $tpl->setLeftContent($map->getUserListHTML());
    }
    
    /**
     * Mail to members view
     * @global type $ilToolbar
     */
    protected function mailMembersBtn()
    {
        global $DIC;

        $ilToolbar = $DIC['ilToolbar'];
        
        $this->showMailToMemberToolbarButton($GLOBALS['DIC']['ilToolbar'], 'mailMembersBtn');
    }
    
    
    
    
    /**
     * Show participants toolbar
     */
    protected function showParticipantsToolbar()
    {
        global $DIC;

        $ilToolbar = $DIC['ilToolbar'];
        
        if ($this->canAddOrSearchUsers()) {
            include_once './Services/Search/classes/class.ilRepositorySearchGUI.php';
            ilRepositorySearchGUI::fillAutoCompleteToolbar(
                $this,
                $ilToolbar,
                array(
                    'auto_complete_name' => $this->lng->txt('user'),
                    'user_type' => $this->getParentGUI()->getLocalRoles(),
                    'user_type_default' => $this->getDefaultRole(),
                    'submit_name' => $this->lng->txt('add')
                )
            );

            // spacer
            $ilToolbar->addSeparator();

            // search button
            $ilToolbar->addButton(
                $this->lng->txt($this->getParentObject()->getType() . "_search_users"),
                $this->ctrl->getLinkTargetByClass(
                    'ilRepositorySearchGUI',
                    'start'
                )
            );

            // separator
            $ilToolbar->addSeparator();
        }
            
        // print button
        $ilToolbar->addButton(
            $this->lng->txt($this->getParentObject()->getType() . "_print_list"),
            $this->ctrl->getLinkTarget($this, 'printMembers')
        );
        
        $this->showMailToMemberToolbarButton($ilToolbar, 'participants', false);
    }
    
    /**
     * Show member export button
     * @param ilToolbarGUI $toolbar
     * @param type $a_back_cmd
     * @param type $a_separator
     */
    protected function showMemberExportToolbarButton(ilToolbarGUI $toolbar, $a_back_cmd = null, $a_separator = false)
    {
        if (
            $this->getParentObject()->getType() == 'crs' &&
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
    protected function showMailToMemberToolbarButton(ilToolbarGUI $toolbar, $a_back_cmd = null, $a_separator = false)
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $rbacsystem = $DIC['rbacsystem'];
        $ilAccess = $DIC['ilAccess'];
        include_once 'Services/Mail/classes/class.ilMail.php';
        $mail = new ilMail($ilUser->getId());

        if (
            ($this->getParentObject()->getMailToMembersType() == 1) ||
            (
                $ilAccess->checkAccess('manage_members', "", $this->getParentObject()->getRefId()) &&
                $rbacsystem->checkAccess('internal_mail', $mail->getMailObjectReferenceId())
            )
        ) {
            if ($a_separator) {
                $toolbar->addSeparator();
            }

            if ($a_back_cmd) {
                $this->ctrl->setParameter($this, "back_cmd", $a_back_cmd);
            }

            $toolbar->addButton(
                $this->lng->txt("mail_members"),
                $this->ctrl->getLinkTargetByClass('ilMailMemberSearchGUI', '')
            );
        }
    }
    
    /**
     * @todo better implementation
     * Create Mail signature
     */
    public function createMailSignature()
    {
        return $this->getParentGUI()->createMailSignature();
    }

    /**
     * Get default command
     * @return string
     */
    protected function getDefaultCommand()
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

    /**
     * add member tab
     * @param ilTabsGUI $tabs
     * @param bool      $a_is_participant
     */
    public function addMemberTab(ilTabsGUI $tabs, $a_is_participant = false)
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        
        include_once './Services/Mail/classes/class.ilMail.php';
        $mail = new ilMail($GLOBALS['DIC']['ilUser']->getId());

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
            (bool) $this->getParentObject()->getShowMembers() && $a_is_participant
        ) {
            $tabs->addTab(
                'members',
                $member_tab_name,
                $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilusersgallerygui'), 'view')
            );
        } elseif (
            $this->getParentObject()->getMailToMembersType() == 1 &&
            $GLOBALS['DIC']['rbacsystem']->checkAccess('internal_mail', $mail->getMailObjectReferenceId()) &&
            $a_is_participant
        ) {
            $tabs->addTab(
                'members',
                $member_tab_name,
                $this->ctrl->getLinkTarget($this, "mailMembersBtn")
            );
        }
    }

    /**
     * Get member tab name
     * @return string
     */
    protected function getMemberTabName()
    {
        return $this->lng->txt('members');
    }
    
    /**
     * Set sub tabs
     */
    protected function setSubTabs(ilTabsGUI $tabs)
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        
        if ($this->checkRbacOrPositionAccessBool('manage_members', 'manage_members', $this->getParentObject()->getRefId())) {
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
            
            $tree = $DIC->repositoryTree();
            $children = (array) $tree->getSubTree($tree->getNodeData($this->getParentObject()->getRefId()), false, 'sess');
            if (count($children)) {
                $tabs->addSubTabTarget(
                    'events',
                    $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilsessionoverviewgui'), 'listSessions'),
                    '',
                    'ilsessionoverviewgui'
                );
            }

            $tabs->addSubTabTarget(
                $this->getParentObject()->getType() . '_members_gallery',
                $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilUsersGalleryGUI')),
                'view',
                'ilUsersGalleryGUI'
            );
        } elseif ($this->getParentObject()->getShowMembers()) {
            // gallery
            $tabs->addSubTabTarget(
                $this->getParentObject()->getType() . '_members_gallery',
                $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilUsersGalleryGUI')),
                'view',
                'ilUsersGalleryGUI'
            );
        }
        
        include_once './Services/Maps/classes/class.ilMapUtil.php';
        if (ilMapUtil::isActivated() && $this->getParentObject()->getEnableMap()) {
            $tabs->addSubTabTarget(
                $this->getParentObject()->getType() . '_members_map',
                $this->ctrl->getLinkTarget($this, 'membersMap'),
                "membersMap",
                get_class($this)
            );
        }
        
        include_once 'Services/PrivacySecurity/classes/class.ilPrivacySettings.php';
        if (ilPrivacySettings::_getInstance()->checkExportAccess($this->getParentObject()->getRefId())) {
            $tabs->addSubTabTarget(
                'export_members',
                $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilmemberexportgui'), 'show'),
                '',
                'ilmemberexportgui'
            );
        }
    }
    
    /**
     * Required for member table guis.
     * Has to be refactored and should be locate in ilObjCourse, ilObjGroup instead of GUI
     * @return array
     */
    public function readMemberData(array $usr_ids, array $columns)
    {
        return $this->getParentGUI()->readMemberData($usr_ids, $columns);
    }
    
    /**
     * Get parent roles
     * @return type
     */
    public function getLocalRoles()
    {
        return $this->getParentGUI()->getLocalRoles();
    }
    
    /**
     * Parse table of subscription request
     */
    protected function parseSubscriberTable()
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

    /**
     * @return \ilSubscriberTableGUI
     */
    protected function initSubscriberTable()
    {
        $subscriber = new \ilSubscriberTableGUI($this, $this->getParentObject(), true, true);
        $subscriber->setTitle($this->lng->txt('group_new_registrations'));
        return $subscriber;
    }
    
    /**
     * Show subscription confirmation
     * @return boolean
     */
    public function confirmAssignSubscribers()
    {
        if (!is_array($_POST["subscribers"])) {
            ilUtil::sendFailure($this->lng->txt("crs_no_subscribers_selected"), true);
            $this->ctrl->redirect($this, 'participants');
        }

        include_once("Services/Utilities/classes/class.ilConfirmationGUI.php");
        $c_gui = new ilConfirmationGUI();

        // set confirm/cancel commands
        $c_gui->setFormAction($this->ctrl->getFormAction($this, "assignSubscribers"));
        $c_gui->setHeaderText($this->lng->txt("info_assign_sure"));
        $c_gui->setCancel($this->lng->txt("cancel"), "participants");
        $c_gui->setConfirm($this->lng->txt("confirm"), "assignSubscribers");

        foreach ($_POST["subscribers"] as $subscribers) {
            $name = ilObjUser::_lookupName($subscribers);

            $c_gui->addItem(
                'subscribers[]',
                $name['user_id'],
                $name['lastname'] . ', ' . $name['firstname'] . ' [' . $name['login'] . ']',
                ilUtil::getImagePath('icon_usr.svg')
            );
        }

        $this->tpl->setContent($c_gui->getHTML());
        return true;
    }
    
    /**
     * Refuse subscriber confirmation
     * @return boolean
     */
    public function confirmRefuseSubscribers()
    {
        if (!is_array($_POST["subscribers"])) {
            ilUtil::sendFailure($this->lng->txt("crs_no_subscribers_selected"), true);
            $this->ctrl->redirect($this, 'participants');
        }

        $this->lng->loadLanguageModule('mmbr');

        include_once("Services/Utilities/classes/class.ilConfirmationGUI.php");
        $c_gui = new ilConfirmationGUI();

        // set confirm/cancel commands
        $c_gui->setFormAction($this->ctrl->getFormAction($this, "refuseSubscribers"));
        $c_gui->setHeaderText($this->lng->txt("info_refuse_sure"));
        $c_gui->setCancel($this->lng->txt("cancel"), "participants");
        $c_gui->setConfirm($this->lng->txt("confirm"), "refuseSubscribers");

        foreach ($_POST["subscribers"] as $subscribers) {
            $name = ilObjUser::_lookupName($subscribers);

            $c_gui->addItem(
                'subscribers[]',
                $name['user_id'],
                $name['lastname'] . ', ' . $name['firstname'] . ' [' . $name['login'] . ']',
                ilUtil::getImagePath('icon_usr.svg')
            );
        }

        $this->tpl->setContent($c_gui->getHTML());
        return true;
    }
    
    /**
     * Refuse subscribers
     * @global type $rbacsystem
     * @return boolean
     */
    protected function refuseSubscribers()
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        if (!$_POST['subscribers']) {
            ilUtil::sendFailure($this->lng->txt("crs_no_subscribers_selected"), true);
            $this->ctrl->redirect($this, 'participants');
        }
    
        if (!$this->getMembersObject()->deleteSubscribers($_POST["subscribers"])) {
            ilUtil::sendFailure($GLOBALS['DIC']['ilErr']->getMessage(), true);
            $this->ctrl->redirect($this, 'participants');
        } else {
            foreach ($_POST['subscribers'] as $usr_id) {
                if ($this instanceof ilCourseMembershipGUI) {
                    $this->getMembersObject()->sendNotification($this->getMembersObject()->NOTIFY_DISMISS_SUBSCRIBER, $usr_id);
                }
                if ($this instanceof ilGroupMembershipGUI) {
                    include_once './Modules/Group/classes/class.ilGroupMembershipMailNotification.php';
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

        ilUtil::sendSuccess($this->lng->txt("crs_subscribers_deleted"), true);
        $this->ctrl->redirect($this, 'participants');
    }
    
    /**
     * Do assignment of subscription request
     * @global type $rbacsystem
     * @global type $ilErr
     * @return boolean
     */
    public function assignSubscribers()
    {
        global $DIC;

        $ilErr = $DIC['ilErr'];
        
        if (!is_array($_POST["subscribers"])) {
            ilUtil::sendFailure($this->lng->txt("crs_no_subscribers_selected"), true);
            $this->ctrl->redirect($this, 'participants');
        }
        
        if (!$this->getMembersObject()->assignSubscribers($_POST["subscribers"])) {
            ilUtil::sendFailure($ilErr->getMessage(), true);
            $this->ctrl->redirect($this, 'participants');
        } else {
            foreach ($_POST["subscribers"] as $usr_id) {
                if ($this instanceof ilCourseMembershipGUI) {
                    $this->getMembersObject()->sendNotification($this->getMembersObject()->NOTIFY_ACCEPT_SUBSCRIBER, $usr_id);
                    $this->getParentObject()->checkLPStatusSync($usr_id);
                }
                if ($this instanceof ilGroupMembershipGUI) {
                    include_once './Modules/Group/classes/class.ilGroupMembershipMailNotification.php';
                    $this->getMembersObject()->sendNotification(
                        ilGroupMembershipMailNotification::TYPE_ACCEPTED_SUBSCRIPTION_MEMBER,
                        $usr_id
                    );
                }
                if ($this instanceof ilSessionMembershipGUI) {
                    // todo refactor to participants
                    include_once './Modules/Session/classes/class.ilSessionMembershipMailNotification.php';
                    $noti = new ilSessionMembershipMailNotification();
                    $noti->setRefId($this->getParentObject()->getRefId());
                    $noti->setRecipients(array($usr_id));
                    $noti->setType(ilSessionMembershipMailNotification::TYPE_ACCEPTED_SUBSCRIPTION_MEMBER);
                    $noti->send();
                }
            }
        }
        ilUtil::sendSuccess($this->lng->txt("crs_subscribers_assigned"), true);
        $this->ctrl->redirect($this, 'participants');
    }
    
    /**
     * Parse table of subscription request
     * @return ilWaitingListTableGUI
     */
    protected function parseWaitingListTable()
    {
        $wait = $this->initWaitingList();
        
        $wait_users = $this->filterUserIdsByRbacOrPositionOfCurrentUser($wait->getUserIds());
        if (!count($wait_users)) {
            return null;
        }

        include_once './Services/Membership/classes/class.ilWaitingListTableGUI.php';
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
     * @return boolean
     */
    public function confirmAssignFromWaitingList()
    {
        if (!is_array($_POST["waiting"])) {
            ilUtil::sendFailure($this->lng->txt("crs_no_users_selected"), true);
            $this->ctrl->redirect($this, 'participants');
        }

        
        include_once("Services/Utilities/classes/class.ilConfirmationGUI.php");
        $c_gui = new ilConfirmationGUI();

        // set confirm/cancel commands
        $c_gui->setFormAction($this->ctrl->getFormAction($this, "assignFromWaitingList"));
        $c_gui->setHeaderText($this->lng->txt("info_assign_sure"));
        $c_gui->setCancel($this->lng->txt("cancel"), "participants");
        $c_gui->setConfirm($this->lng->txt("confirm"), "assignFromWaitingList");

        foreach ($_POST["waiting"] as $waiting) {
            $name = ilObjUser::_lookupName($waiting);

            $c_gui->addItem(
                'waiting[]',
                $name['user_id'],
                $name['lastname'] . ', ' . $name['firstname'] . ' [' . $name['login'] . ']',
                ilUtil::getImagePath('icon_usr.svg')
            );
        }

        $this->tpl->setContent($c_gui->getHTML());
        return true;
    }
    
    /**
     * Assign from waiting list
     * @global type $rbacsystem
     * @return boolean
     */
    public function assignFromWaitingList()
    {
        if (!array_key_exists('waiting', $_POST) || !count($_POST["waiting"])) {
            ilUtil::sendFailure($this->lng->txt("crs_no_users_selected"), true);
            $this->ctrl->redirect($this, 'participants');
        }
        
        $waiting_list = $this->initWaitingList();

        $added_users = 0;
        foreach ($_POST["waiting"] as $user_id) {
            if (!$tmp_obj = ilObjectFactory::getInstanceByObjId($user_id, false)) {
                continue;
            }
            if ($this->getMembersObject()->isAssigned($user_id)) {
                continue;
            }
            
            if ($this instanceof ilCourseMembershipGUI) {
                $this->getMembersObject()->add($user_id, IL_CRS_MEMBER);
                $this->getMembersObject()->sendNotification($this->getMembersObject()->NOTIFY_ACCEPT_USER, $user_id, true);
                $this->getParentObject()->checkLPStatusSync($user_id);
            }
            if ($this instanceof ilGroupMembershipGUI) {
                include_once './Modules/Group/classes/class.ilGroupMembershipMailNotification.php';
                $this->getMembersObject()->add($user_id, IL_GRP_MEMBER);
                $this->getMembersObject()->sendNotification(
                    ilGroupMembershipMailNotification::TYPE_ACCEPTED_SUBSCRIPTION_MEMBER,
                    $user_id,
                    true
                );
            }
            if ($this instanceof ilSessionMembershipGUI) {
                $this->getMembersObject()->register($user_id);
                $noti = new ilSessionMembershipMailNotification();
                $noti->setRefId($this->getParentObject()->getRefId());
                $noti->setRecipients(array($user_id));
                $noti->setType(ilSessionMembershipMailNotification::TYPE_ACCEPTED_SUBSCRIPTION_MEMBER);
                $noti->send();
            }
            
            $waiting_list->removeFromList($user_id);
            ++$added_users;
        }

        if ($added_users) {
            ilUtil::sendSuccess($this->lng->txt("crs_users_added"), true);
            $this->ctrl->redirect($this, 'participants');
        } else {
            ilUtil::sendFailure($this->lng->txt("crs_users_already_assigned"), true);
            $this->ctrl->redirect($this, 'participants');
        }
    }
    
    /**
     * Refuse from waiting list (confirmation)
     * @return boolean
     */
    public function confirmRefuseFromList()
    {
        if (!is_array($_POST["waiting"])) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, 'participants');
        }

        $this->lng->loadLanguageModule('mmbr');

        include_once("Services/Utilities/classes/class.ilConfirmationGUI.php");
        $c_gui = new ilConfirmationGUI();

        // set confirm/cancel commands
        $c_gui->setFormAction($this->ctrl->getFormAction($this, "refuseFromList"));
        $c_gui->setHeaderText($this->lng->txt("info_refuse_sure"));
        $c_gui->setCancel($this->lng->txt("cancel"), "participants");
        $c_gui->setConfirm($this->lng->txt("confirm"), "refuseFromList");

        foreach ($_POST["waiting"] as $waiting) {
            $name = ilObjUser::_lookupName($waiting);

            $c_gui->addItem(
                'waiting[]',
                $name['user_id'],
                $name['lastname'] . ', ' . $name['firstname'] . ' [' . $name['login'] . ']',
                ilUtil::getImagePath('icon_usr.svg')
            );
        }

        $this->tpl->setContent($c_gui->getHTML());
        return true;
    }
    
    /**
     * refuse from waiting list
     *
     * @access public
     * @return
     */
    protected function refuseFromList()
    {
        if (!array_key_exists('waiting', $_POST) || !count($_POST['waiting'])) {
            ilUtil::sendFailure($this->lng->txt('no_checkbox'), true);
            $this->ctrl->redirect($this, 'participants');
        }
        
        $waiting_list = $this->initWaitingList();

        foreach ($_POST["waiting"] as $user_id) {
            $waiting_list->removeFromList($user_id);
            
            if ($this instanceof ilCourseMembershipGUI) {
                $this->getMembersObject()->sendNotification($this->getMembersObject()->NOTIFY_DISMISS_SUBSCRIBER, $user_id, true);
            }
            if ($this instanceof ilGroupMembershipGUI) {
                include_once './Modules/Group/classes/class.ilGroupMembershipMailNotification.php';
                $this->getMembersObject()->sendNotification(
                    ilGroupMembershipMailNotification::TYPE_REFUSED_SUBSCRIPTION_MEMBER,
                    $user_id,
                    true
                );
            }
            if ($this instanceof ilSessionMembershipGUI) {
                include_once './Modules/Session/classes/class.ilSessionMembershipMailNotification.php';
                $noti = new ilSessionMembershipMailNotification();
                $noti->setRefId($this->getParentObject()->getRefId());
                $noti->setRecipients(array($user_id));
                $noti->setType(ilSessionMembershipMailNotification::TYPE_REFUSED_SUBSCRIPTION_MEMBER);
                $noti->send();
            }
        }
        ilUtil::sendSuccess($this->lng->txt('crs_users_removed_from_list'), true);
        $this->ctrl->redirect($this, 'participants');
    }
    
    /**
     * Add selected users to user clipboard
     */
    protected function addToClipboard()
    {
        // begin-patch clipboard
        $users = [];
        if (isset($_POST['participants'])) {
            $users = (array) $_POST['participants'];
        } elseif (isset($_POST['subscribers'])) {
            $users = (array) $_POST['subscribers'];
        } elseif (isset($_POST['waiting'])) {
            $users = (array) $_POST['waiting'];
        }
        // end-patch clipboard
        if (!count($users)) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->ctrl->redirect($this, 'participants');
        }
        include_once './Services/User/classes/class.ilUserClipboard.php';
        $clip = ilUserClipboard::getInstance($GLOBALS['DIC']['ilUser']->getId());
        $clip->add($users);
        $clip->save();
        
        $this->lng->loadLanguageModule('user');
        ilUtil::sendSuccess($this->lng->txt('clipboard_user_added'), true);
        $this->ctrl->redirect($this, 'participants');
    }

    /**
     * @return null
     */
    protected function getDefaultRole()
    {
        return null;
    }

    /**
     * @param string $a_sub_tab
     */
    protected function activateSubTab($a_sub_tab)
    {
        /**
         * @var ilTabsGUI $tabs
         */
        $tabs = $GLOBALS['DIC']['ilTabs'];
        $tabs->activateSubTab($a_sub_tab);
    }


    
    
    /**
     * Print members
     * @todo: refactor to own class
     */
    protected function printMembers()
    {
        global $DIC;

        $ilTabs = $DIC['ilTabs'];
        
        $this->checkPermission('read');
        
        $ilTabs->clearTargets();

        $ilTabs->setBackTarget(
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
    protected function printMembersOutput()
    {
        global $DIC;

        $tabs = $DIC->tabs();
        $tabs->clearTargets();
        $tabs->setBackTarget(
            $this->lng->txt('back'),
            $this->ctrl->getLinkTarget($this, 'participants')
        );

        $list = $this->initAttendanceList();
        $list->initFromForm();
        $list->setCallback(array($this, 'getAttendanceListUserData'));
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
    protected function printForMembersOutput()
    {
        global $DIC;

        $tabs = $DIC->tabs();
        $tabs->clearTargets();
        $tabs->setBackTarget(
            $this->lng->txt('back'),
            $this->ctrl->getLinkTarget($this, 'jump2UsersGallery')
        );

        $list = $this->initAttendanceList();
        $list->setTitle($this->lng->txt('obj_' . $this->getParentObject()->getType()) . ': ' . $this->getParentObject()->getTitle());
        $list->setId(0);
        $form = $list->initForm('printForMembersOutput');
        $list->initFromForm();
        $list->setCallback(array($this, 'getAttendanceListUserData'));
        $this->member_data = $this->getPrintMemberData($this->getMembersObject()->getParticipants());
        $list->getNonMemberUserData($this->member_data);
        
        $list->getFullscreenHTML();
    }

    /**
     *
     */
    protected function jump2UsersGallery()
    {
        $this->ctrl->redirectByClass('ilUsersGalleryGUI');
    }
    
    
    
    
    /**
     * Init attendance list
     */
    protected function initAttendanceList($a_for_members = false)
    {
        global $DIC;

        /**
         * @var ilWaitingList
         */
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
            include_once 'Services/Membership/classes/class.ilAttendanceList.php';
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
                
        include_once './Services/Tracking/classes/class.ilObjUserTracking.php';
        $show_tracking =
            (ilObjUserTracking::_enabledLearningProgress() and ilObjUserTracking::_enabledUserRelatedData());
        if ($show_tracking) {
            include_once('./Services/Object/classes/class.ilObjectLP.php');
            $olp = ilObjectLP::getInstance($this->getParentObject()->getId());
            $show_tracking = $olp->isActive();
        }
        if ($show_tracking) {
            $list->addPreset('progress', $this->lng->txt('learning_progress'), true);
        }
        
        include_once('./Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
        /**
         * @var ilPrivacySettings
         */
        $privacy = ilPrivacySettings::_getInstance();
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
