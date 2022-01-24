<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

/**
 * GUI class for learning sequence membership features.
 *
 * @ilCtrl_Calls ilLearningSequenceMembershipGUI: ilMailMemberSearchGUI, ilUsersGalleryGUI, ilRepositorySearchGUI
 * @ilCtrl_Calls ilLearningSequenceMembershipGUI: ilCourseParticipantsGroupsGUI, ilObjectCustomuserFieldsGUI
 * @ilCtrl_Calls ilLearningSequenceMembershipGUI: ilSessionOverviewGUI
 * @ilCtrl_Calls ilLearningSequenceMembershipGUI: ilMemberExportGUI
 *
 */
class ilLearningSequenceMembershipGUI extends ilMembershipGUI
{
    protected ilObjectGUI $repository_gui;
    protected ilObject $obj;
    protected ilObjUserTracking $obj_user_tracking;
    protected ilPrivacySettings $privacy_settings;
    protected ilRbacReview $rbac_review;
    protected ilSetting $settings;
    protected ilToolbarGUI $toolbar;

    public function __construct(
        ilObjectGUI $repository_gui,
        ilObject $obj,
        ilObjUserTracking $obj_user_tracking,
        ilPrivacySettings $privacy_settings,
        ilRbacReview $rbac_review,
        ilSetting $settings,
        ilToolbarGUI $toolbar
    ) {
        parent::__construct($repository_gui, $obj);

        $this->obj = $obj;
        $this->obj_user_tracking = $obj_user_tracking;
        $this->privacy_settings = $privacy_settings;
        $this->rbac_review = $rbac_review;
        $this->settings = $settings;
        $this->toolbar = $toolbar;
    }

    protected function printMembers() : void
    {
        $this->checkPermission('read');
        if ($this->checkRbacOrPositionAccessBool('manage_members', 'manage_members')) {
            $back_cmd = 'participants';
        } else {
            $back_cmd = 'jump2UsersGallery';
        }

        global $DIC;
        $ilTabs = $DIC['ilTabs'];
        $ilTabs->clearTargets();
        $ilTabs->setBackTarget(
            $this->lng->txt('back'),
            $this->ctrl->getLinkTarget($this, $back_cmd)
        );

        $list = $this->initAttendanceList();
        $form = $list->initForm('printMembersOutput');
        $this->tpl->setContent($form->getHTML());
    }

    protected function getDefaultCommand() : string
    {
        return $_GET['back_cmd'];
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
     * @param array<int>  $user_ids
     */
    public function assignMembers(array $user_ids, string $type) : bool
    {
        $object = $this->getParentObject();
        $members = $this->getParentObject()->getLSParticipants();

        if (count($user_ids) == 0) {
            $this->lng->loadLanguageModule('search');
            ilUtil::sendFailure($this->lng->txt('search_err_user_not_exist'), true);
            return false;
        }

        $assigned = false;
        foreach ($user_ids as $new_member) {
            $new_member = (int) $new_member;

            if ($members->isAssigned($new_member)) {
                continue;
            }

            switch ($type) {
                case $object->getDefaultAdminRole():
                    $members->add($new_member, IL_LSO_ADMIN);
                    $members->sendNotification(
                        ilLearningSequenceMembershipMailNotification::TYPE_ADMISSION_MEMBER,
                        $new_member
                    );
                    $assigned = true;
                    break;
                case $object->getDefaultMemberRole():
                    $members->add($new_member, IL_LSO_MEMBER);
                    $members->sendNotification(
                        ilLearningSequenceMembershipMailNotification::TYPE_ADMISSION_MEMBER,
                        $new_member
                    );
                    $assigned = true;
                    break;
                default:
                    if (in_array($type, $object->getLocalLearningSequenceRoles(true))) {
                        $members->add($new_member, IL_LSO_MEMBER);
                        $members->updateRoleAssignments($new_member, array($type));
                    } else {
                        ilLoggerFactory::getLogger('lso')->notice(
                            'Can not find role with id .' . $type . ' to assign users.'
                        );
                        ilUtil::sendFailure($this->lng->txt("lso_cannot_find_role"), true);
                        return false;
                    }

                    $members->sendNotification(
                        ilLearningSequenceMembershipMailNotification::TYPE_ADMISSION_MEMBER,
                        $new_member
                    );
                    $assigned = true;
                    break;
            }
        }

        if ($assigned) {
            ilUtil::sendSuccess($this->lng->txt("lso_msg_member_assigned"), true);
        } else {
            ilUtil::sendSuccess($this->lng->txt('lso_users_already_assigned'), true);
        }

        $this->ctrl->redirect($this, 'participants');
        return $assigned;
    }

    /**
     * save in participants table
     */
    protected function updateParticipantsStatus() : void
    {
        $members = $this->getParentObject()->getLSParticipants();

        $participants = (array) $_POST['visible_member_ids'];
        $notification = (array) $_POST['notification'];

        foreach ($participants as $participant) {
            if ($members->isAdmin($participant)) {
                $members->updateNotification($participant, in_array($participant, (bool) $notification));
                continue;
            }
            $members->updateNotification($participant, false);
        }

        ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'participants');
    }

    protected function initParticipantTableGUI() : ilLearningSequenceParticipantsTableGUI
    {
        return new ilLearningSequenceParticipantsTableGUI(
            $this,
            $this->getParentObject(),
            $this->obj_user_tracking,
            $this->privacy_settings,
            $this->lng,
            $this->access,
            $this->rbac_review,
            $this->settings
        );
    }

    public function getParentObject() : ilObjLearningSequence
    {
        $obj = parent::getParentObject();
        if (!$obj instanceof ilObjLearningSequence) {
            throw new Exception('Invalid class type ' . get_class($obj) . ". Expected ilObjLearningSequence.");
        }
        return $obj;
    }

    protected function initEditParticipantTableGUI(array $participants) : ilLearningSequenceEditParticipantsTableGUI
    {
        $table = new ilLearningSequenceEditParticipantsTableGUI(
            $this,
            $this->getParentObject(),
            $this->getParentObject()->getLSParticipants(),
            $this->privacy_settings
        );

        $table->setTitle($this->lng->txt($this->getParentObject()->getType() . '_header_edit_members'));
        $table->setData($this->readMemberData($participants));

        return $table;
    }

    /**
     * Init participant view template
     */
    protected function initParticipantTemplate() : void
    {
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.lso_edit_members.html', 'Modules/LearningSequence');
    }

    /**
     * @return array<string, int>
     */
    public function getLocalTypeRole(bool $translation = false) : array
    {
        return $this->getParentObject()->getLocalLearningSequenceRoles($translation);
    }

    /**
     * @param array<int|string> $user_ids
     * @param string[] $columns
     * @return array<int|string, array<mixed>>
     */
    public function readMemberData(array $user_ids, array $columns = null) : array
    {
        return $this->getParentObject()->readMemberData($user_ids, $columns);
    }

    protected function updateLPFromStatus()
    {
        return null;
    }

    protected function initWaitingList() : ilLearningSequenceWaitingList
    {
        return new ilLearningSequenceWaitingList($this->getParentObject()->getId());
    }

    protected function getDefaultRole() : ?int
    {
        return $this->getParentObject()->getDefaultMemberRole();
    }

    /**
     * @return array<string, mixed>
     */
    public function getPrintMemberData(array $members) : array
    {
        $member_data = $this->readMemberData($members, array());
        $member_data = $this->getParentGUI()->addCustomData($member_data);

        return $member_data;
    }

    /**
     * @return array<string, mixed>
     */
    public function getAttendanceListUserData(int $user_id) : array
    {
        $data = array();

        if ($this->filterUserIdsByRbacOrPositionOfCurrentUser([$user_id])) {
            $data = $this->member_data[$user_id];
            $data['access'] = $data['access_time'];
            $data['progress'] = $this->lng->txt($data['progress']);
        }

        return $data;
    }

    protected function getMailMemberRoles() : ?ilAbstractMailMemberRoles
    {
        return new ilMailMemberLearningSequenceRoles();
    }

    protected function setSubTabs(ilTabsGUI $tabs) : void
    {
        $access = $this->checkRbacOrPositionAccessBool(
            'manage_members',
            'manage_members',
            $this->getParentObject()->getRefId()
        );

        if ($access) {
            $tabs->addSubTabTarget(
                $this->getParentObject()->getType() . "_member_administration",
                $this->ctrl->getLinkTarget($this, 'participants'),
                "members",
                get_class($this)
            );

            $tabs->addSubTabTarget(
                $this->getParentObject()->getType() . '_members_gallery',
                $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilUsersGalleryGUI')),
                'view',
                'ilUsersGalleryGUI'
            );
        } elseif ($this->getParentObject()->getShowMembers()) {
            $tabs->addSubTabTarget(
                $this->getParentObject()->getType() . '_members_gallery',
                $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilUsersGalleryGUI')),
                'view',
                'ilUsersGalleryGUI'
            );
        }
    }

    protected function showParticipantsToolbar() : void
    {
        $toolbar_entries = [
            'auto_complete_name' => $this->lng->txt('user'),
            'user_type' => $this->getParentGUI()->getLocalRoles(),
            'user_type_default' => $this->getDefaultRole(),
            'submit_name' => $this->lng->txt('add'),
            'add_search' => true
        ];

        $search_params = ['crs', 'grp'];
        $parent_container = $this->obj->getParentObjectInfo(
            $this->obj->getRefId(),
            $search_params
        );
        if (!is_null($parent_container)) {
            $container_id = $parent_container['ref_id'];
            $toolbar_entries['add_from_container'] = $container_id;
        }

        ilRepositorySearchGUI::fillAutoCompleteToolbar(
            $this,
            $this->toolbar,
            $toolbar_entries
        );

        $this->toolbar->addSeparator();

        $this->toolbar->addButton(
            $this->lng->txt($this->getParentObject()->getType() . "_print_list"),
            $this->ctrl->getLinkTarget($this, 'printMembers')
        );

        $this->showMailToMemberToolbarButton($this->toolbar, 'participants');
    }
}
