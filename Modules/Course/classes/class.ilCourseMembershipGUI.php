<?php declare(strict_types=0);

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
 
/**
 * Member-tab content
 * @author       Stefan Meyer <smeyer.ilias@gmx.de>
 * @ilCtrl_Calls ilCourseMembershipGUI: ilMailMemberSearchGUI, ilUsersGalleryGUI, ilRepositorySearchGUI
 * @ilCtrl_Calls ilCourseMembershipGUI: ilCourseParticipantsGroupsGUI, ilObjectCustomuserFieldsGUI
 * @ilCtrl_Calls ilCourseMembershipGUI: ilSessionOverviewGUI
 * @ilCtrl_Calls ilCourseMembershipGUI: ilMemberExportGUI
 */
class ilCourseMembershipGUI extends ilMembershipGUI
{
    protected function getMailMemberRoles() : ?ilAbstractMailMemberRoles
    {
        return new ilMailMemberCourseRoles();
    }

    /**
     * Filter user ids by access
     * @param int[] $a_user_ids
     * @return int[]
     */
    public function filterUserIdsByRbacOrPositionOfCurrentUser(array $a_user_ids) : array
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
    protected function getMailContextOptions() : array
    {
        return [
            ilMailFormCall::CONTEXT_KEY => ilCourseMailTemplateTutorContext::ID,
            'ref_id' => $this->getParentObject()->getRefId(),
            'ts' => time(),
            ilMail::PROP_CONTEXT_SUBJECT_PREFIX => ilContainer::_lookupContainerSetting(
                $this->getParentObject()->getId(),
                ilObjectServiceSettingsGUI::EXTERNAL_MAIL_PREFIX,
                ''
            ),
        ];
    }

    /**
     * Show deletion confirmation with linked courses.
     * @param int[] participants
     */
    protected function showDeleteParticipantsConfirmationWithLinkedCourses(array $participants) : void
    {
        $this->tpl->setOnScreenMessage('question', $this->lng->txt('crs_ref_delete_confirmation_info'));

        $table = new ilCourseReferenceDeleteConfirmationTableGUI(
            $this,
            $this->getParentObject(),
            'confirmDeleteParticipants'
        );
        $table->init();
        $table->setParticipants($participants);
        $table->parse();

        $this->tpl->setContent($table->getHTML());
    }

    protected function deleteParticipantsWithLinkedCourses() : void
    {
        $participants = $this->initParticipantsFromPost();

        if (!is_array($participants) || $participants === []) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, 'participants');
        }

        // If the user doesn't have the edit_permission and is not administrator, he may not remove
        // members who have the course administrator role
        if (
            !$this->access->checkAccess('edit_permission', '', $this->getParentObject()->getRefId()) &&
            !$this->getMembersObject()->isAdmin($GLOBALS['DIC']['ilUser']->getId())
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
            foreach ($this->initParticipantsFromPost() as $usr_id) {
                $mail_type = 0;
                switch ($this->getParentObject()->getType()) {
                    case 'crs':
                        $mail_type = ilCourseMembershipMailNotification::TYPE_DISMISS_MEMBER;
                        break;
                }
                $this->getMembersObject()->sendNotification($mail_type, $usr_id);
            }
        }

        $refs = [];
        if ($this->http->wrapper()->post()->has('refs')) {
            $refs = $this->http->wrapper()->post()->retrieve(
                'refs',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }

        // Delete course reference assignments
        foreach ($refs as $usr_id => $usr_info) {
            foreach ((array) $usr_info as $course_ref_id => $tmp) {
                $part = ilParticipants::getInstance($course_ref_id);
                $part->delete($usr_id);
            }
        }
        $this->tpl->setOnScreenMessage(
            'success',
            $this->lng->txt($this->getParentObject()->getType() . "_members_deleted"),
            true
        );
        $this->ctrl->redirect($this, "participants");
    }

    /**
     * callback from repository search gui
     */
    public function assignMembers(array $a_usr_ids, int $a_type) : bool
    {
        if (!$this->checkRbacOrPositionAccessBool('manage_members', 'manage_members')) {
            $this->error->raiseError($this->lng->txt("msg_no_perm_read"), $this->error->FATAL);
        }
        if ($a_usr_ids === []) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("crs_no_users_selected"), true);
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
                    $this->getMembersObject()->add($user_id, ilParticipants::IL_CRS_MEMBER);
                    break;
                case $this->getParentObject()->getDefaultTutorRole():
                    $this->getMembersObject()->add($user_id, ilParticipants::IL_CRS_TUTOR);
                    break;
                case $this->getParentObject()->getDefaultAdminRole():
                    $this->getMembersObject()->add($user_id, ilParticipants::IL_CRS_ADMIN);
                    break;
                default:
                    if (in_array($a_type, $this->getParentObject()->getLocalCourseRoles(true))) {
                        $this->getMembersObject()->add($user_id, ilParticipants::IL_CRS_MEMBER);
                        $this->getMembersObject()->updateRoleAssignments($user_id, (array) $a_type);
                    } else {
                        ilLoggerFactory::getLogger('crs')->notice('Can\'t find role with id .' . $a_type . ' to assign users.');
                        $this->tpl->setOnScreenMessage('failure', $this->lng->txt("crs_cannot_find_role"), true);
                        return false;
                    }
                    break;
            }
            $this->getMembersObject()->sendNotification(ilCourseMembershipMailNotification::TYPE_ADMISSION_MEMBER, $user_id);

            $this->getParentObject()->checkLPStatusSync($user_id);

            ++$added_users;
        }
        if ($added_users) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("crs_users_added"), true);
            $this->ctrl->redirect($this, 'participants');
        }
        $this->tpl->setOnScreenMessage('failure', $this->lng->txt("crs_users_already_assigned"), true);
        return false;
    }

    protected function initParticipantStatusFromPostFor(string $item_key) : array
    {
        if ($this->http->wrapper()->post()->has($item_key)) {
            return $this->http->wrapper()->post()->retrieve(
                $item_key,
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        return [];
    }

    protected function updateParticipantsStatus() : void
    {
        $visible_members = [];
        if ($this->http->wrapper()->post()->has('visible_member_ids')) {
            $visible_members = $this->http->wrapper()->post()->retrieve(
                'visible_member_ids',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        $passed = $this->initParticipantStatusFromPostFor('passed');
        $blocked = $this->initParticipantStatusFromPostFor('blocked');
        $contact = $this->initParticipantStatusFromPostFor('contact');
        $notification = $this->initParticipantStatusFromPostFor('notification');

        foreach ($visible_members as $member_id) {
            if ($this->access->checkAccess("grade", "", $this->getParentObject()->getRefId())) {
                $this->getMembersObject()->updatePassed($member_id, in_array($member_id, $passed), true);
                $this->updateLPFromStatus($member_id, in_array($member_id, $passed));
            }

            if ($this->getMembersObject()->isAdmin($member_id) || $this->getMembersObject()->isTutor($member_id)) {
                // remove blocked
                $this->getMembersObject()->updateBlocked($member_id, false);
                $this->getMembersObject()->updateNotification($member_id, in_array($member_id, $notification));
                $this->getMembersObject()->updateContact($member_id, in_array($member_id, $contact));
            } else {
                // send notifications => unblocked
                if ($this->getMembersObject()->isBlocked($member_id) && !in_array($member_id, $blocked)) {
                    $this->getMembersObject()->sendNotification(
                        ilCourseMembershipMailNotification::TYPE_UNBLOCKED_MEMBER,
                        $member_id
                    );
                }
                // => blocked
                if (!$this->getMembersObject()->isBlocked($member_id) && in_array($member_id, $blocked)) {
                    $this->getMembersObject()->sendNotification(
                        ilCourseMembershipMailNotification::TYPE_BLOCKED_MEMBER,
                        $member_id
                    );
                }

                // normal member => remove notification, contact
                $this->getMembersObject()->updateNotification($member_id, false);
                $this->getMembersObject()->updateContact($member_id, false);
                $this->getMembersObject()->updateBlocked($member_id, in_array($member_id, $blocked));
            }
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'participants');
    }

    protected function initParticipantTableGUI() : ilParticipantTableGUI
    {
        $show_tracking =
            (ilObjUserTracking::_enabledLearningProgress() && ilObjUserTracking::_enabledUserRelatedData());
        if ($show_tracking) {
            $olp = ilObjectLP::getInstance($this->getParentObject()->getId());
            $show_tracking = $olp->isActive();
        }

        $timings_enabled =
            (ilObjectActivation::hasTimings($this->getParentObject()->getRefId()) && ($this->getParentObject()->getViewMode() == ilCourseConstants::IL_CRS_VIEW_TIMING));

        return new ilCourseParticipantsTableGUI(
            $this,
            $this->getParentObject(),
            $show_tracking,
            $timings_enabled,
            $this->getParentObject()->getStatusDetermination() == ilObjCourse::STATUS_DETERMINATION_LP
        );
    }

    protected function initEditParticipantTableGUI(array $participants) : ilCourseEditParticipantsTableGUI
    {
        /** @noinspection PhpParamsInspection */
        $table = new ilCourseEditParticipantsTableGUI($this, $this->getParentObject());
        $table->setTitle($this->lng->txt($this->getParentObject()->getType() . '_header_edit_members'));
        $table->setData($this->getParentGUI()->readMemberData($participants));

        return $table;
    }

    protected function initParticipantTemplate() : void
    {
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.crs_edit_members.html', 'Modules/Course');
    }

    /**
     * @todo refactor delete
     */
    public function getLocalTypeRole(bool $a_translation = false) : array
    {
        return $this->getParentObject()->getLocalCourseRoles($a_translation);
    }

    protected function updateLPFromStatus(int $a_member_id, bool $a_passed) : void
    {
        $this->getParentGUI()->updateLPFromStatus($a_member_id, $a_passed);
    }

    protected function initWaitingList() : ilCourseWaitingList
    {
        return new ilCourseWaitingList($this->getParentObject()->getId());
    }

    protected function getDefaultRole() : ?int
    {
        return $this->getParentGUI()->getObject()->getDefaultMemberRole();
    }

    protected function deliverCertificate() : void
    {
        $this->getParentGUI()->deliverCertificateObject();
    }

    protected function getPrintMemberData(array $a_members) : array
    {
        $this->lng->loadLanguageModule('trac');

        $is_admin = true;
        $privacy = ilPrivacySettings::getInstance();

        if ($privacy->enabledCourseAccessTimes()) {
            $progress = ilLearningProgress::_lookupProgressByObjId($this->getParentObject()->getId());
        }

        $show_tracking =
            (ilObjUserTracking::_enabledLearningProgress() && ilObjUserTracking::_enabledUserRelatedData());
        if ($show_tracking) {
            $olp = ilObjectLP::getInstance($this->getParentObject()->getId());
            $show_tracking = $olp->isActive();
        }

        if ($show_tracking) {
            $completed = ilLPStatusWrapper::_lookupCompletedForObject($this->getParentObject()->getId());
            $in_progress = ilLPStatusWrapper::_lookupInProgressForObject($this->getParentObject()->getId());
            $failed = ilLPStatusWrapper::_lookupFailedForObject($this->getParentObject()->getId());
        }

        $profile_data = ilObjUser::_readUsersProfileData($a_members);

        // course defined fields
        $cdfs = ilCourseUserData::_getValuesByObjId($this->getParentObject()->getId());

        $print_member = [];
        foreach ($a_members as $member_id) {
            // GET USER OBJ
            if ($tmp_obj = ilObjectFactory::getInstanceByObjId($member_id, false)) {
                // udf
                $udf_data = new ilUserDefinedData($member_id);
                foreach ($udf_data->getAll() as $field => $value) {
                    list($f, $field_id) = explode('_', $field);
                    $print_member[$member_id]['udf_' . $field_id] = (string) $value;
                }

                foreach ((array) ($cdfs[$member_id] ?? []) as $cdf_field => $cdf_value) {
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
                if ($this->getMembersObject()->isAdmin($member_id) || $this->getMembersObject()->isTutor($member_id)) {
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
                    if (isset($progress[$member_id]['ts']) && $progress[$member_id]['ts']) {
                        ilDatePresentation::setUseRelativeDates(false);
                        $print_member[$member_id]['access'] = ilDatePresentation::formatDate(new ilDateTime(
                            $progress[$member_id]['ts'],
                            IL_CAL_UNIX
                        ));
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
        $print_order = (string) (ilSession::get('crs_print_order') ?? '');
        return ilArrayUtil::sortArray($print_member, 'name', $print_order, false, true);
    }

    public function getAttendanceListUserData(int $user_id, array $filters = []) : array
    {
        if (is_array($this->member_data) && array_key_exists($user_id, $this->member_data)) {
            return $this->member_data[$user_id];
        }
        return [];
    }
}
