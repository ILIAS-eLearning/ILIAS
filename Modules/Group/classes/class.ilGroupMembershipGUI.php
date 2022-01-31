<?php declare(strict_types=1);
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory;

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
    protected GlobalHttpState $http;
    protected Factory $refinery;

    public function __construct(ilObjectGUI $repository_gui, ilObject $repository_obj)
    {
        global $DIC;

        parent::__construct($repository_gui, $repository_obj);
        $this->refinery  = $DIC->refinery();
        $this->http = $DIC->http();
    }

    /**
     * @return ilAbstractMailMemberRoles | null
     */
    protected function getMailMemberRoles() : ?ilAbstractMailMemberRoles
    {
        return new ilMailMemberGroupRoles();
    }


    /**
     * Filter user ids by access
     * @param int[] $a_user_ids
     * @return int[]
     */
    public function filterUserIdsByRbacOrPositionOfCurrentUser(array $a_user_ids) : array
    {
        return $this->access->filterUserIdsByRbacOrPositionOfCurrentUser(
            'manage_members',
            'manage_members',
            $this->getParentObject()->getRefId(),
            $a_user_ids
        );
    }

    /**
     * @param int[]  $user_ids
     * @param string $a_type
     * @return bool
     * @throws ilCtrlException
     */
    public function assignMembers(array $user_ids, string $a_type) : bool
    {
        if (!count($user_ids)) {
            $this->lng->loadLanguageModule('search');
            ilUtil::sendFailure($this->lng->txt('search_err_user_not_exist'), true);
            return false;
        }

        $assigned = false;
        foreach ($user_ids as $new_member) {
            if ($this->getMembersObject()->isAssigned($new_member)) {
                continue;
            }
            switch ($a_type) {
                case $this->getParentObject()->getDefaultAdminRole():
                    $this->getMembersObject()->add($new_member, ilParticipants::IL_GRP_ADMIN);
                    $this->getMembersObject()->sendNotification(
                        ilGroupMembershipMailNotification::TYPE_ADMISSION_MEMBER,
                        $new_member
                    );
                    $assigned = true;
                    break;
                
                case $this->getParentObject()->getDefaultMemberRole():
                    $this->getMembersObject()->add($new_member, ilParticipants::IL_GRP_MEMBER);
                    $this->getMembersObject()->sendNotification(
                        ilGroupMembershipMailNotification::TYPE_ADMISSION_MEMBER,
                        $new_member
                    );
                    $assigned = true;
                    break;
                    
                default:
                    if (in_array($a_type, $this->getParentObject()->getLocalGroupRoles(true))) {
                        $this->getMembersObject()->add($new_member, ilParticipants::IL_GRP_MEMBER);
                        $this->getMembersObject()->updateRoleAssignments($new_member, (array) $a_type);
                    } else {
                        ilLoggerFactory::getLogger('crs')->notice('Can not find role with id .' . $a_type . ' to assign users.');
                        ilUtil::sendFailure($this->lng->txt("crs_cannot_find_role"), true);
                        return false;
                    }
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
        return true;
    }

    /**
     * save in participants table
     */
    protected function updateParticipantsStatus() : void
    {
        $participants = [];
        if ($this->http->wrapper()->post()->has('visible_member_ids')) {
            $participants = $this->http->wrapper()->post()->retrieve(
                'visible_member_ids',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        $notification = [];
        if ($this->http->wrapper()->post()->has('notification')) {
            $notification = $this->http->wrapper()->post()->retrieve(
                'notification',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        $contact = [];
        if ($this->http->wrapper()->post()->has('contact')) {
            $contact = $this->http->wrapper()->post()->retrieve(
                'contact',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
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
    
    
    protected function initParticipantTableGUI() : ilGroupParticipantsTableGUI
    {
        $show_tracking =
            (ilObjUserTracking::_enabledLearningProgress() && ilObjUserTracking::_enabledUserRelatedData())
        ;
        if ($show_tracking) {
            $olp = ilObjectLP::getInstance($this->getParentObject()->getId());
            $show_tracking = $olp->isActive();
        }

        return new ilGroupParticipantsTableGUI(
            $this,
            $this->getParentObject(),
            $show_tracking
        );
    }
    
    protected function initEditParticipantTableGUI(array $participants) : ilGroupEditParticipantsTableGUI
    {
        $table = new ilGroupEditParticipantsTableGUI($this, $this->getParentObject());
        $table->setTitle($this->lng->txt($this->getParentObject()->getType() . '_header_edit_members'));
        $table->setData($this->getParentGUI()->readMemberData($participants));
        
        return $table;
    }
    
    
    
    /**
     * Init participant view template
     */
    protected function initParticipantTemplate() : void
    {
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.grp_edit_members.html', 'Modules/Group');
    }
    
    public function getLocalTypeRole(bool $a_translation = false) : array
    {
        return $this->getParentObject()->getLocalGroupRoles($a_translation);
    }
    

    protected function initWaitingList() : ilGroupWaitingList
    {
        return new ilGroupWaitingList($this->getParentObject()->getId());
    }

    /**
     * @inheritDoc
     */
    protected function getDefaultRole() : ?int
    {
        return $this->getParentGUI()->object->getDefaultMemberRole();
    }
    
    public function getPrintMemberData(array $a_members) : array
    {
        $member_data = $this->readMemberData($a_members, array());
        $member_data = $this->getParentGUI()->addCustomData($member_data);
        return $member_data;
    }
    
    /**
     * Callback from attendance list
     */
    public function getAttendanceListUserData(int $a_user_id) : array
    {
        if (is_array($this->member_data) && array_key_exists($a_user_id, $this->member_data)) {
            $user_data = $this->member_data[$a_user_id];
            $user_data['access'] = $this->member_data['access_time'];
            $user_data['progress'] = $this->lng->txt($this->member_data['progress']);
            return $user_data;
        }
        return [];
    }

    /**
     * @inheritDoc
     */
    protected function getMailContextOptions() : array
    {
        $context_options = [
            ilMail::PROP_CONTEXT_SUBJECT_PREFIX => ilContainer::_lookupContainerSetting(
                $this->getParentObject()->getId(),
                ilObjectServiceSettingsGUI::EXTERNAL_MAIL_PREFIX,
                ''
            ),
        ];

        return $context_options;
    }
}
