<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Membership/classes/class.ilMembershipGUI.php';

/**
 * Member-tab content
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 *
 * @ilCtrl_Calls ilCourseMembershipGUI: ilMailMemberSearchGUI, ilUsersGalleryGUI, ilRepositorySearchGUI
 * @ilCtrl_Calls ilCourseMembershipGUI: ilCourseParticipantsGroupsGUI, ilObjectCustomuserFieldsGUI
 * @ilCtrl_Calls ilCourseMembershipGUI: ilSessionOverviewGUI
 * @ilCtrl_Calls ilCourseMembershipGUI: ilMemberExportGUI
 */
class ilCourseMembershipGUI extends ilMembershipGUI
{
    /**
     * @return ilAbstractMailMemberRoles
     */
    protected function getMailMemberRoles()
    {
        return new ilMailMemberCourseRoles();
    }
    
    /**
     * Filter user ids by access
     * @param int[] $a_user_ids
     * @return int[]
     */
    public function filterUserIdsByRbacOrPositionOfCurrentUser($a_user_ids)
    {
        return $GLOBALS['DIC']->access()->filterUserIdsByRbacOrPositionOfCurrentUser(
            'manage_members',
            'manage_members',
            $this->getParentObject()->getRefId(),
            $a_user_ids
        );
    }

    /**
     * @inheritdoc
     */
    protected function getMailContextOptions()
    {
        $context_options = [];

        $context_options =
            [
                ilMailFormCall::CONTEXT_KEY => ilCourseMailTemplateTutorContext::ID,
                'ref_id' => $this->getParentObject()->getRefId(),
                'ts' => time()
            ];
        return $context_options;
    }


    /**
     * callback from repository search gui
     * @global ilRbacSystem $rbacsystem
     * @param array $a_usr_ids
     * @param int $a_type role_id
     * @return bool
     */
    public function assignMembers(array $a_usr_ids, $a_type)
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $ilErr = $DIC['ilErr'];

        if (!$this->checkRbacOrPositionAccessBool('manage_members', 'manage_members')) {
            $ilErr->raiseError($this->lng->txt("msg_no_perm_read"), $ilErr->FATAL);
        }

        if (!count($a_usr_ids)) {
            ilUtil::sendFailure($this->lng->txt("crs_no_users_selected"), true);
            return false;
        }
        
        $a_usr_ids = $this->filterUserIdsByRbacOrPositionOfCurrentUser($a_usr_ids);

        $added_users = 0;
        foreach ($a_usr_ids as $user_id) {
            if (!$tmp_obj = ilObjectFactory::getInstanceByObjId($user_id, false)) {
                continue;
            }
            if ($this->getMembersObject()->isAssigned($user_id)) {
                continue;
            }
            switch ($a_type) {
                case $this->getParentObject()->getDefaultMemberRole():
                    $this->getMembersObject()->add($user_id, IL_CRS_MEMBER);
                    break;
                case $this->getParentObject()->getDefaultTutorRole():
                    $this->getMembersObject()->add($user_id, IL_CRS_TUTOR);
                    break;
                case $this->getParentObject()->getDefaultAdminRole():
                    $this->getMembersObject()->add($user_id, IL_CRS_ADMIN);
                    break;
                default:
                    if (in_array($a_type, $this->getParentObject()->getLocalCourseRoles(true))) {
                        $this->getMembersObject()->add($user_id, IL_CRS_MEMBER);
                        $this->getMembersObject()->updateRoleAssignments($user_id, (array) $a_type);
                    } else {
                        ilLoggerFactory::getLogger('crs')->notice('Can\'t find role with id .' . $a_type . ' to assign users.');
                        ilUtil::sendFailure($this->lng->txt("crs_cannot_find_role"), true);
                        return false;
                    }
                    break;
            }
            $this->getMembersObject()->sendNotification($this->getMembersObject()->NOTIFY_ACCEPT_USER, $user_id);

            $this->getParentObject()->checkLPStatusSync($user_id);

            ++$added_users;
        }
        if ($added_users) {
            ilUtil::sendSuccess($this->lng->txt("crs_users_added"), true);
            $this->ctrl->redirect($this, 'participants');
        }
        ilUtil::sendFailure($this->lng->txt("crs_users_already_assigned"), true);
        return false;
    }
    
    /**
     * => save button in member table
     */
    protected function updateParticipantsStatus()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        $ilUser = $DIC['ilUser'];
        $rbacadmin = $DIC['rbacadmin'];
        
        $visible_members = (array) $_POST['visible_member_ids'];
        $passed = (array) $_POST['passed'];
        $blocked = (array) $_POST['blocked'];
        $contact = (array) $_POST['contact'];
        $notification = (array) $_POST['notification'];
        
        foreach ($visible_members as $member_id) {
            if ($ilAccess->checkAccess("grade", "", $this->getParentObject()->getRefId())) {
                $this->getMembersObject()->updatePassed($member_id, in_array($member_id, $passed), true);
                $this->updateLPFromStatus($member_id, in_array($member_id, $passed));
            }
            
            if ($this->getMembersObject()->isAdmin($member_id) or $this->getMembersObject()->isTutor($member_id)) {
                // remove blocked
                $this->getMembersObject()->updateBlocked($member_id, 0);
                $this->getMembersObject()->updateNotification($member_id, in_array($member_id, $notification));
                $this->getMembersObject()->updateContact($member_id, in_array($member_id, $contact));
            } else {
                // send notifications => unblocked
                if ($this->getMembersObject()->isBlocked($member_id) && !in_array($member_id, $blocked)) {
                    $this->getMembersObject()->sendNotification($this->getMembersObject()->NOTIFY_UNBLOCK_MEMBER, $member_id);
                }
                // => blocked
                if (!$this->getMembersObject()->isBlocked($member_id) && in_array($member_id, $blocked)) {
                    $this->getMembersObject()->sendNotification($this->getMembersObject()->NOTIFY_BLOCK_MEMBER, $member_id);
                }

                // normal member => remove notification, contact
                $this->getMembersObject()->updateNotification($member_id, false);
                $this->getMembersObject()->updateContact($member_id, false);
                $this->getMembersObject()->updateBlocked($member_id, in_array($member_id, $blocked));
            }
        }
            
        
        ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'participants');
    }
    
    
    /**
     * @return \ilParticpantTableGUI
     */
    protected function initParticipantTableGUI()
    {
        include_once './Services/Tracking/classes/class.ilObjUserTracking.php';
        $show_tracking =
            (ilObjUserTracking::_enabledLearningProgress() && ilObjUserTracking::_enabledUserRelatedData())
        ;
        if ($show_tracking) {
            include_once('./Services/Object/classes/class.ilObjectLP.php');
            $olp = ilObjectLP::getInstance($this->getParentObject()->getId());
            $show_tracking = $olp->isActive();
        }

        include_once('./Services/Object/classes/class.ilObjectActivation.php');
        $timings_enabled =
            (ilObjectActivation::hasTimings($this->getParentObject()->getRefId()) && ($this->getParentObject()->getViewMode() == IL_CRS_VIEW_TIMING))
        ;
        
        
        include_once './Modules/Course/classes/class.ilCourseParticipantsTableGUI.php';
        return new ilCourseParticipantsTableGUI(
            $this,
            $this->getParentObject(),
            $show_tracking,
            $timings_enabled,
            $this->getParentObject()->getStatusDetermination() == ilObjCourse::STATUS_DETERMINATION_LP
        );
    }

    /**
     * init edit participants table gui
     * @param array $participants
     * @return \ilCourseEditParticipantsTableGUI
     */
    protected function initEditParticipantTableGUI(array $participants)
    {
        include_once './Modules/Course/classes/class.ilCourseEditParticipantsTableGUI.php';
        $table = new ilCourseEditParticipantsTableGUI($this, $this->getParentObject());
        $table->setTitle($this->lng->txt($this->getParentObject()->getType() . '_header_edit_members'));
        $table->setData($this->getParentGUI()->readMemberData($participants));
        
        return $table;
    }

    /**
     * Init participant view template
     */
    protected function initParticipantTemplate()
    {
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.crs_edit_members.html', 'Modules/Course');
    }
    
    /**
     * @todo refactor delete
     */
    public function getLocalTypeRole($a_translation = false)
    {
        return $this->getParentObject()->getLocalCourseRoles($a_translation);
    }
    
    /**
     * Update lp from status
     */
    protected function updateLPFromStatus($a_member_id, $a_passed)
    {
        return $this->getParentGUI()->updateLPFromStatus($a_member_id, $a_passed);
    }
    
    /**
     * init waiting list
     * @return ilCourseWaitingList
     */
    protected function initWaitingList()
    {
        include_once './Modules/Course/classes/class.ilCourseWaitingList.php';
        $wait = new ilCourseWaitingList($this->getParentObject()->getId());
        return $wait;
    }

    /**
     * @return int
     */
    protected function getDefaultRole()
    {
        return $this->getParentGUI()->object->getDefaultMemberRole();
    }
    
    /**
     * Deliver certificate for an user on the member list
     * @return type
     */
    protected function deliverCertificate()
    {
        return $this->getParentGUI()->deliverCertificateObject();
    }
    
    /**
     * Get print member data
     * @param array $a_members
     */
    protected function getPrintMemberData($a_members)
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $lng = $DIC['lng'];

        $lng->loadLanguageModule('trac');

        $is_admin = true;
        include_once('./Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
        $privacy = ilPrivacySettings::_getInstance();

        if ($privacy->enabledCourseAccessTimes()) {
            include_once('./Services/Tracking/classes/class.ilLearningProgress.php');
            $progress = ilLearningProgress::_lookupProgressByObjId($this->getParentObject()->getId());
        }

        include_once './Services/Tracking/classes/class.ilObjUserTracking.php';
        $show_tracking =
            (ilObjUserTracking::_enabledLearningProgress() and ilObjUserTracking::_enabledUserRelatedData());
        if ($show_tracking) {
            include_once('./Services/Object/classes/class.ilObjectLP.php');
            $olp = ilObjectLP::getInstance($this->getParentObject()->getId());
            $show_tracking = $olp->isActive();
        }
        
        if ($show_tracking) {
            include_once 'Services/Tracking/classes/class.ilLPStatusWrapper.php';
            $completed = ilLPStatusWrapper::_lookupCompletedForObject($this->getParentObject()->getId());
            $in_progress = ilLPStatusWrapper::_lookupInProgressForObject($this->getParentObject()->getId());
            $failed = ilLPStatusWrapper::_lookupFailedForObject($this->getParentObject()->getId());
        }
        
        $profile_data = ilObjUser::_readUsersProfileData($a_members);

        // course defined fields
        include_once('Modules/Course/classes/Export/class.ilCourseUserData.php');
        $cdfs = ilCourseUserData::_getValuesByObjId($this->getParentObject()->getId());

        foreach ($a_members as $member_id) {
            // GET USER OBJ
            if ($tmp_obj = ilObjectFactory::getInstanceByObjId($member_id, false)) {
                // udf
                include_once './Services/User/classes/class.ilUserDefinedData.php';
                $udf_data = new ilUserDefinedData($member_id);
                foreach ($udf_data->getAll() as $field => $value) {
                    list($f, $field_id) = explode('_', $field);
                    $print_member[$member_id]['udf_' . $field_id] = (string) $value;
                }
                
                foreach ((array) $cdfs[$member_id] as $cdf_field => $cdf_value) {
                    $print_member[$member_id]['cdf_' . $cdf_field] = (string) $cdf_value;
                }

                foreach ((array) $profile_data[$member_id] as $field => $value) {
                    $print_member[$member_id][$field] = $value;
                }
                
                $print_member[$member_id]['login'] = $tmp_obj->getLogin();
                $print_member[$member_id]['name'] = $tmp_obj->getLastname() . ', ' . $tmp_obj->getFirstname();

                if ($this->getMembersObject()->isAdmin($member_id)) {
                    $print_member[$member_id]['role'] = $this->lng->txt("il_crs_admin");
                } elseif ($this->getMembersObject()->isTutor($member_id)) {
                    $print_member[$member_id]['role'] = $this->lng->txt("il_crs_tutor");
                } elseif ($this->getMembersObject()->isMember($member_id)) {
                    $print_member[$member_id]['role'] = $this->lng->txt("il_crs_member");
                }
                if ($this->getMembersObject()->isAdmin($member_id) or $this->getMembersObject()->isTutor($member_id)) {
                    if ($this->getMembersObject()->isNotificationEnabled($member_id)) {
                        $print_member[$member_id]['status'] = $this->lng->txt("crs_notify");
                    } else {
                        $print_member[$member_id]['status'] = $this->lng->txt("crs_no_notify");
                    }
                } else {
                    if ($this->getMembersObject()->isBlocked($member_id)) {
                        $print_member[$member_id]['status'] = $this->lng->txt("crs_blocked");
                    } else {
                        $print_member[$member_id]['status'] = $this->lng->txt("crs_unblocked");
                    }
                }
    
                if ($is_admin) {
                    $print_member[$member_id]['passed'] = $this->getMembersObject()->hasPassed($member_id) ?
                                      $this->lng->txt('crs_member_passed') :
                                      $this->lng->txt('crs_member_not_passed');
                }
                if ($privacy->enabledCourseAccessTimes()) {
                    if (isset($progress[$member_id]['ts']) and $progress[$member_id]['ts']) {
                        ilDatePresentation::setUseRelativeDates(false);
                        $print_member[$member_id]['access'] = ilDatePresentation::formatDate(new ilDateTime($progress[$member_id]['ts'], IL_CAL_UNIX));
                        ilDatePresentation::setUseRelativeDates(true);
                    } else {
                        $print_member[$member_id]['access'] = $this->lng->txt('no_date');
                    }
                }
                if ($show_tracking) {
                    if (in_array($member_id, $completed)) {
                        $print_member[$member_id]['progress'] = $this->lng->txt(ilLPStatus::LP_STATUS_COMPLETED);
                    } elseif (in_array($member_id, $in_progress)) {
                        $print_member[$member_id]['progress'] = $this->lng->txt(ilLPStatus::LP_STATUS_IN_PROGRESS);
                    } elseif (in_array($member_id, $failed)) {
                        $print_member[$member_id]['progress'] = $this->lng->txt(ilLPStatus::LP_STATUS_FAILED);
                    } else {
                        $print_member[$member_id]['progress'] = $this->lng->txt(ilLPStatus::LP_STATUS_NOT_ATTEMPTED);
                    }
                }
            }
        }
        return ilUtil::sortArray($print_member, 'name', $_SESSION['crs_print_order'], false, true);
    }
    
    /**
     * Callback from attendance list
     * @param int $a_user_id
     */
    public function getAttendanceListUserData($a_user_id)
    {
        if (is_array($this->member_data) && array_key_exists($a_user_id, $this->member_data)) {
            return $this->member_data[$a_user_id];
        }
        return [];
    }
}
