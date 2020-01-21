<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Membership/classes/class.ilMembershipGUI.php';

/**
 * GUI class for membership features
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 * @ilCtrl_Calls ilGroupMembershipGUI: ilMailMemberSearchGUI, ilUsersGalleryGUI, ilRepositorySearchGUI
 * @ilCtrl_Calls ilGroupMembershipGUI: ilCourseParticipantsGroupsGUI, ilObjectCustomuserFieldsGUI
 * @ilCtrl_Calls ilGroupMembershipGUI: ilSessionOverviewGUI
 * @ilCtrl_Calls ilGroupMembershipGUI: ilMemberExportGUI
 *
 */
class ilGroupMembershipGUI extends ilMembershipGUI
{
    /**
     * @return ilAbstractMailMemberRoles | null
     */
    protected function getMailMemberRoles()
    {
        return new ilMailMemberGroupRoles();
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
     * @access public
     */
    public function assignMembers($user_ids, $a_type)
    {
        if (empty($user_ids[0])) {
            $this->lng->loadLanguageModule('search');
            ilUtil::sendFailure($this->lng->txt('search_err_user_not_exist'), true);
            return false;
        }

        $assigned = false;
        foreach ((array) $user_ids as $new_member) {
            if ($this->getMembersObject()->isAssigned($new_member)) {
                continue;
            }
            switch ($a_type) {
                case $this->getParentObject()->getDefaultAdminRole():
                    $this->getMembersObject()->add($new_member, IL_GRP_ADMIN);
                    include_once './Modules/Group/classes/class.ilGroupMembershipMailNotification.php';
                    $this->getMembersObject()->sendNotification(
                        ilGroupMembershipMailNotification::TYPE_ADMISSION_MEMBER,
                        $new_member
                    );
                    $assigned = true;
                    break;
                
                case $this->getParentObject()->getDefaultMemberRole():
                    $this->getMembersObject()->add($new_member, IL_GRP_MEMBER);
                    include_once './Modules/Group/classes/class.ilGroupMembershipMailNotification.php';
                    $this->getMembersObject()->sendNotification(
                        ilGroupMembershipMailNotification::TYPE_ADMISSION_MEMBER,
                        $new_member
                    );
                    $assigned = true;
                    break;
                    
                default:
                    if (in_array($a_type, $this->getParentObject()->getLocalGroupRoles(true))) {
                        $this->getMembersObject()->add($new_member, IL_GRP_MEMBER);
                        $this->getMembersObject()->updateRoleAssignments($new_member, (array) $a_type);
                    } else {
                        ilLoggerFactory::getLogger('crs')->notice('Can not find role with id .' . $a_type . ' to assign users.');
                        ilUtil::sendFailure($this->lng->txt("crs_cannot_find_role"), true);
                        return false;
                    }
                    include_once './Modules/Group/classes/class.ilGroupMembershipMailNotification.php';
                    $this->getMembersObject()->sendNotification(
                        ilGroupMembershipMailNotification::TYPE_ADMISSION_MEMBER,
                        $new_member
                    );
                    $assigned = true;
                    break;
            }
        }
        
        if ($assigned) {
            ilUtil::sendSuccess($this->lng->txt("grp_msg_member_assigned"), true);
        } else {
            ilUtil::sendSuccess($this->lng->txt('grp_users_already_assigned'), true);
        }
        $this->ctrl->redirect($this, 'participants');
    }

    /**
     * save in participants table
     */
    protected function updateParticipantsStatus()
    {
        $participants = (array) $_POST['visible_member_ids'];
        $notification = (array) $_POST['notification'];
        $contact = (array) $_POST['contact'];

        ilLoggerFactory::getLogger('grp')->dump($contact);

        foreach ($participants as $mem_id) {
            if ($this->getMembersObject()->isAdmin($mem_id)) {
                $this->getMembersObject()->updateContact($mem_id, in_array($mem_id, $contact));
                $this->getMembersObject()->updateNotification($mem_id, in_array($mem_id, $notification));
            } else {
                $this->getMembersObject()->updateContact($mem_id, false);
                $this->getMembersObject()->updateNotification($mem_id, false);
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

        include_once './Modules/Group/classes/class.ilGroupParticipantsTableGUI.php';
        return new ilGroupParticipantsTableGUI(
            $this,
            $this->getParentObject(),
            $show_tracking
        );
    }
    
    /**
     * init edit participants table gui
     * @param array $participants
     * @return \ilGroupEditParticipantsTableGUI
     */
    protected function initEditParticipantTableGUI(array $participants)
    {
        include_once './Modules/Group/classes/class.ilGroupEditParticipantsTableGUI.php';
        $table = new ilGroupEditParticipantsTableGUI($this, $this->getParentObject());
        $table->setTitle($this->lng->txt($this->getParentObject()->getType() . '_header_edit_members'));
        $table->setData($this->getParentGUI()->readMemberData($participants));
        
        return $table;
    }
    
    
    
    /**
     * Init participant view template
     */
    protected function initParticipantTemplate()
    {
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.grp_edit_members.html', 'Modules/Group');
    }
    
    /**
     * @todo refactor delete
     */
    public function getLocalTypeRole($a_translation = false)
    {
        return $this->getParentObject()->getLocalGroupRoles($a_translation);
    }
    
    /**
     * Update lp from status
     */
    protected function updateLPFromStatus()
    {
        return null;
    }
    
    /**
     * init waiting list
     * @return ilGroupWaitingList
     */
    protected function initWaitingList()
    {
        include_once './Modules/Group/classes/class.ilGroupWaitingList.php';
        $wait = new ilGroupWaitingList($this->getParentObject()->getId());
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
     * @param array $a_members
     * @return array
     */
    public function getPrintMemberData($a_members)
    {
        $member_data = $this->readMemberData($a_members, array());
        $member_data = $this->getParentGUI()->addCustomData($member_data);
        return $member_data;
    }
    
    /**
     * Callback from attendance list
     * @param int $a_user_id
     * @return array
     */
    public function getAttendanceListUserData($a_user_id)
    {
        if (is_array($this->member_data) && array_key_exists($a_user_id, $this->member_data)) {
            $user_data = $this->member_data[$a_user_id];
            $user_data['access'] = $this->member_data['access_time'];
            $user_data['progress'] = $this->lng->txt($this->member_data['progress']);
            return $user_data;
        }
        return [];
    }
}
