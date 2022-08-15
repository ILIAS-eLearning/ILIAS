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
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ModulesCourse
 */
class ilCourseParticipantsTableGUI extends ilParticipantTableGUI
{
    protected bool $show_learning_progress = false;
    protected bool $show_timings = false;
    protected bool $show_lp_status_sync = false;

    private ?ilCertificateUserForObjectPreloader $preLoader = null;
    private ilPrivacySettings $privacy;

    protected ilAccessHandler $access;
    protected ilRbacReview $rbacReview;
    protected ilObjUser $user;

    public function __construct(
        object $a_parent_obj,
        ilObject $rep_object,
        bool $a_show_learning_progress = false,
        bool $a_show_timings = false,
        bool $a_show_lp_status_sync = false,
        ilCertificateUserForObjectPreloader $preloader = null
    ) {
        global $DIC;

        $this->show_learning_progress = $a_show_learning_progress;

        if (null === $preloader) {
            $preloader = new ilCertificateUserForObjectPreloader(
                new ilUserCertificateRepository(),
                new ilCertificateActiveValidator()
            );
        }
        $this->preLoader = $preloader;
        $this->show_timings = $a_show_timings;
        $this->show_lp_status_sync = $a_show_lp_status_sync;
        $this->rep_object = $rep_object;

        if (!ilObjUserTracking::_enabledLearningProgress()) {
            $this->show_lp_status_sync = false;
        }
        $this->privacy = ilPrivacySettings::getInstance();
        $this->access = $DIC->access();
        $this->participants = ilParticipants::getInstanceByObjId($this->getRepositoryObject()->getId());
        $this->rbacReview = $DIC->rbac()->review();
        $this->user = $DIC->user();

        $this->setId('crs_' . $this->getRepositoryObject()->getId());
        parent::__construct($a_parent_obj, 'participants');

        $this->lng->loadLanguageModule('crs');
        $this->lng->loadLanguageModule('trac');
        $this->lng->loadLanguageModule('rbac');
        $this->lng->loadLanguageModule('mmbr');
        $this->lng->loadLanguageModule('cert');
        $this->lng->loadLanguageModule('certificate');

        $this->initSettings();

        $this->setFormName('participants');

        $this->addColumn('', 'f', '1', true);
        $this->addColumn($this->lng->txt('name'), 'name', '20%');

        $all_cols = $this->getSelectableColumns();
        foreach ($this->getSelectedColumns() as $col) {
            $this->addColumn($all_cols[$col]['txt'], $col);
        }

        if ($this->show_learning_progress) {
            $this->addColumn($this->lng->txt('learning_progress'), 'progress');
        }

        if ($this->privacy->enabledCourseAccessTimes()) {
            $this->addColumn($this->lng->txt('last_access'), 'access_ut', '16em');
        }

        $this->addColumn($this->lng->txt('crs_member_passed'), 'passed');
        if ($this->show_lp_status_sync) {
            $this->addColumn($this->lng->txt('crs_member_passed_status_changed'), 'passed_info');
        }

        $this->setSelectAllCheckbox('participants', true);
        $this->addColumn($this->lng->txt('crs_mem_contact'), 'contact');
        $this->addColumn($this->lng->txt('crs_blocked'), 'blocked');
        $this->addColumn($this->lng->txt('crs_notification_list_title'), 'notification');

        $this->addColumn($this->lng->txt('actions'), 'optional', '', false, 'ilMembershipRowActionsHeader');

        $this->setRowTemplate("tpl.show_participants_row.html", "Modules/Course");

        $this->setDefaultOrderField('roles');
        $this->enable('sort');
        $this->enable('header');
        $this->enable('numinfo');
        $this->enable('select_all');

        $this->setEnableNumInfo(true);
        $this->setExternalSegmentation(false);
        $this->setTopCommands(true);
        $this->setEnableHeader(true);
        $this->setEnableTitle(true);
        $this->initFilter();

        $this->setShowRowsSelector(true);

        $preloader->preLoadDownloadableCertificates($this->getRepositoryObject()->getId());
        $this->addMultiCommand('editParticipants', $this->lng->txt('edit'));
        $this->addMultiCommand('confirmDeleteParticipants', $this->lng->txt('remove'));
        $this->addMultiCommand('sendMailToSelectedUsers', $this->lng->txt('mmbr_btn_mail_selected_users'));
        $this->lng->loadLanguageModule('user');
        $this->addMultiCommand('addToClipboard', $this->lng->txt('clipboard_add_btn'));

        $this->addCommandButton('updateParticipantsStatus', $this->lng->txt('save'));
    }

    protected function fillRow(array $a_set) : void
    {
        $this->tpl->setVariable('VAL_ID', $a_set['usr_id']);
        $this->tpl->setVariable('VAL_NAME', $a_set['lastname'] . ', ' . $a_set['firstname']);

        if (
            !$this->access->checkAccessOfUser($a_set['usr_id'], 'read', '', $this->getRepositoryObject()->getRefId()) &&
            is_array($info = $this->access->getInfo())
        ) {
            $this->tpl->setCurrentBlock('access_warning');
            $this->tpl->setVariable('PARENT_ACCESS', $info[0]['text']);
            $this->tpl->parseCurrentBlock();
        }

        if (!ilObjUser::_lookupActive($a_set['usr_id'])) {
            $this->tpl->setCurrentBlock('access_warning');
            $this->tpl->setVariable('PARENT_ACCESS', $this->lng->txt('usr_account_inactive'));
            $this->tpl->parseCurrentBlock();
        }

        foreach ($this->getSelectedColumns() as $field) {
            switch ($field) {
                case 'gender':
                    $a_set['gender'] = $a_set['gender'] ? $this->lng->txt('gender_' . $a_set['gender']) : '';
                    $this->tpl->setCurrentBlock('custom_fields');
                    $this->tpl->setVariable('VAL_CUST', $a_set[$field]);
                    $this->tpl->parseCurrentBlock();
                    break;

                case 'birthday':
                    $a_set['birthday'] = $a_set['birthday'] ? ilDatePresentation::formatDate(new ilDate(
                        $a_set['birthday'],
                        IL_CAL_DATE
                    )) : $this->lng->txt('no_date');
                    $this->tpl->setCurrentBlock('custom_fields');
                    $this->tpl->setVariable('VAL_CUST', $a_set[$field]);
                    $this->tpl->parseCurrentBlock();
                    break;

                case 'consultation_hour':
                    $this->tpl->setCurrentBlock('custom_fields');
                    $dts = array();
                    foreach ((array) $a_set['consultation_hours'] as $ch) {
                        $tmp = ilDatePresentation::formatPeriod(
                            new ilDateTime($ch['dt'], IL_CAL_UNIX),
                            new ilDateTime($ch['dtend'], IL_CAL_UNIX)
                        );
                        if ($ch['explanation']) {
                            $tmp .= ' ' . $ch['explanation'];
                        }
                        $dts[] = $tmp;
                    }
                    $dt_string = implode('<br />', $dts);
                    $this->tpl->setVariable('VAL_CUST', $dt_string);
                    $this->tpl->parseCurrentBlock();
                    break;

                case 'prtf':
                    $tmp = array();
                    if (is_array($a_set['prtf'])) {
                        foreach ($a_set['prtf'] as $prtf_url => $prtf_txt) {
                            $tmp[] = '<a href="' . $prtf_url . '">' . $prtf_txt . '</a>';
                        }
                    }
                    $this->tpl->setCurrentBlock('custom_fields');
                    $this->tpl->setVariable('VAL_CUST', implode('<br />', $tmp));
                    $this->tpl->parseCurrentBlock();
                    break;

                case 'odf_last_update':
                    $this->tpl->setCurrentBlock('custom_fields');
                    $this->tpl->setVariable('VAL_CUST', (string) $a_set['odf_info_txt']);
                    $this->tpl->parseCurrentBlock();
                    break;

                case 'roles':
                    $this->tpl->setCurrentBlock('custom_fields');
                    $this->tpl->setVariable('VAL_CUST', (string) $a_set['roles_label']);
                    $this->tpl->parseCurrentBlock();
                    break;

                case 'org_units':
                    $this->tpl->setCurrentBlock('custom_fields');
                    $this->tpl->setVariable(
                        'VAL_CUST',
                        ilOrgUnitPathStorage::getTextRepresentationOfUsersOrgUnits($a_set['usr_id'])
                    );
                    $this->tpl->parseCurrentBlock();
                    break;

                default:
                    $this->tpl->setCurrentBlock('custom_fields');
                    $this->tpl->setVariable('VAL_CUST', isset($a_set[$field]) ? (string) $a_set[$field] : '');
                    $this->tpl->parseCurrentBlock();
                    break;
            }
        }

        if ($this->privacy->enabledCourseAccessTimes()) {
            $this->tpl->setVariable('VAL_ACCESS', $a_set['access_time']);
        }
        if ($this->show_learning_progress) {
            $this->tpl->setCurrentBlock('lp');
            $icons = ilLPStatusIcons::getInstance(ilLPStatusIcons::ICON_VARIANT_LONG);
            $icon_rendered = $icons->renderIconForStatus($icons->lookupNumStatus($a_set['progress']));

            $this->tpl->setVariable('LP_STATUS_ALT', $this->lng->txt($a_set['progress']));
            $this->tpl->setVariable('LP_STATUS_ICON', $icon_rendered);

            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setVariable('VAL_POSTNAME', 'participants');

        if ($this->access->checkAccess("grade", "", $this->rep_object->getRefId())) {
            $this->tpl->setCurrentBlock('grade');
            $this->tpl->setVariable('VAL_PASSED_ID', $a_set['usr_id']);
            $this->tpl->setVariable('VAL_PASSED_CHECKED', ($a_set['passed'] ? 'checked="checked"' : ''));
            $this->tpl->parseCurrentBlock();
        } else {
            $this->tpl->setVariable('VAL_PASSED_TXT', ($a_set['passed']
                ? $this->lng->txt("yes")
                : $this->lng->txt("no")));
        }

        if (
            $this->getParticipants()->isAdmin($a_set['usr_id']) ||
            $this->getParticipants()->isTutor($a_set['usr_id'])
        ) {
            // cognos-blu-patch: begin
            $this->tpl->setCurrentBlock('with_contact');
            $this->tpl->setVariable('VAL_CONTACT_ID', $a_set['usr_id']);
            $this->tpl->setVariable('VAL_CONTACT_CHECKED', $a_set['contact'] ? 'checked="checked"' : '');
            $this->tpl->parseCurrentBlock();
            // cognos-blu-patch: end

            $this->tpl->setCurrentBlock('with_notification');
            $this->tpl->setVariable('VAL_NOTIFICATION_ID', $a_set['usr_id']);
            $this->tpl->setVariable('VAL_NOTIFICATION_CHECKED', ($a_set['notification'] ? 'checked="checked"' : ''));
            $this->tpl->parseCurrentBlock();
        }

        // blocked only for real members
        if (
            !$this->getParticipants()->isAdmin($a_set['usr_id']) &&
            !$this->getParticipants()->isTutor($a_set['usr_id'])
        ) {
            $this->tpl->setCurrentBlock('with_blocked');
            $this->tpl->setVariable('VAL_BLOCKED_ID', $a_set['usr_id']);
            $this->tpl->setVariable('VAL_BLOCKED_CHECKED', ($a_set['blocked'] ? 'checked="checked"' : ''));
            $this->tpl->parseCurrentBlock();
        }

        if ($this->show_lp_status_sync) {
            $this->tpl->setVariable('PASSED_INFO', $a_set["passed_info"]);
        }

        $this->showActionLinks($a_set);

        $isPreloaded = $this->preLoader->isPreloaded($this->getRepositoryObject()->getId(), $a_set['usr_id']);
        if ($isPreloaded) {
            $this->tpl->setCurrentBlock('link');
            $this->tpl->setVariable('LINK_NAME', $this->ctrl->getLinkTarget($this->parent_obj, 'deliverCertificate'));
            $this->tpl->setVariable('LINK_TXT', $this->lng->txt('download_certificate'));
            $this->tpl->parseCurrentBlock();
        }
        $this->ctrl->clearParameters($this->parent_obj);

        if ($this->show_timings) {
            $this->ctrl->setParameterByClass('ilcoursecontentgui', 'member_id', $a_set['usr_id']);
            $this->tpl->setCurrentBlock('link');
            $this->tpl->setVariable(
                'LINK_NAME',
                $this->ctrl->getLinkTargetByClass('ilcoursecontentgui', 'showUserTimings')
            );
            $this->tpl->setVariable('LINK_TXT', $this->lng->txt('timings_timings'));
            $this->tpl->parseCurrentBlock();
        }
    }

    public function parse() : void
    {
        $this->determineOffsetAndOrder(true);

        $additional_fields = $this->getSelectedColumns();
        unset($additional_fields["firstname"]);
        unset($additional_fields["lastname"]);
        unset($additional_fields["last_login"]);
        unset($additional_fields["access_until"]);
        unset($additional_fields['consultation_hour']);
        unset($additional_fields['prtf']);
        unset($additional_fields['roles']);
        unset($additional_fields['org_units']);

        $part = $this->participants->getParticipants();

        $part = $this->access->filterUserIdsByRbacOrPositionOfCurrentUser(
            'manage_members',
            'manage_members',
            $this->getRepositoryObject()->getRefId(),
            $part
        );

        if ($part === []) {
            $this->setData(array());
            return;
        }

        $udf_ids = $usr_data_fields = $odf_ids = array();
        foreach ($additional_fields as $field) {
            if (substr($field, 0, 3) == 'udf') {
                $udf_ids[] = substr($field, 4);
                continue;
            }
            if (substr($field, 0, 3) == 'odf') {
                $odf_ids[] = substr($field, 4);
                continue;
            }

            $usr_data_fields[] = $field;
        }

        $usr_data = ilUserQuery::getUserListData(
            '',
            '',
            0,
            9999,
            $this->current_filter['login'],
            '',
            null,
            false,
            false,
            0,
            0,
            null,
            $usr_data_fields,
            $part
        );
        // filter by array
        $usr_ids = array();
        $local_roles = $this->getParentObject()->getLocalRoles();
        foreach ((array) $usr_data['set'] as $user) {
            if ($this->current_filter['roles'] ?? false) {
                if (!$this->rbacReview->isAssigned($user['usr_id'], $this->current_filter['roles'])) {
                    continue;
                }
            }
            if ($this->current_filter['org_units'] ?? false) {
                $org_unit = $this->current_filter['org_units'];

                $assigned = ilObjOrgUnitTree::_getInstance()->getOrgUnitOfUser($user['usr_id']);
                if (!in_array($org_unit, $assigned)) {
                    continue;
                }
            }

            $usr_ids[] = $user['usr_id'];
        }

        // merge course data
        $course_user_data = $this->getParentObject()->readMemberData(
            $usr_ids,
            $this->getSelectedColumns()
        );
        $a_user_data = array();
        foreach ((array) $usr_data['set'] as $ud) {
            $user_id = (int) $ud['usr_id'];

            if (!in_array($user_id, $usr_ids)) {
                continue;
            }
            // #15434
            if (!$this->checkAcceptance($user_id)) {
                $ud = array();
            }

            $a_user_data[$user_id] = array_merge($ud, $course_user_data[$user_id]);

            $roles = array();
            foreach ($local_roles as $role_id => $role_name) {
                // @todo fix performance
                if ($this->rbacReview->isAssigned($user_id, $role_id)) {
                    $roles[] = $role_name;
                }
            }

            $a_user_data[$user_id]['name'] = ($a_user_data[$user_id]['lastname'] . ', ' . $a_user_data[$user_id]['firstname']);
            $a_user_data[$user_id]['roles_label'] = implode('<br />', $roles);
            $a_user_data[$user_id]['roles'] = $this->participants->setRoleOrderPosition($user_id);

            if ($this->show_lp_status_sync) {
                // #9912 / #13208
                $passed_info = "";
                if ($a_user_data[$user_id]["passed_info"]) {
                    $pinfo = $a_user_data[$user_id]["passed_info"];
                    if ($pinfo["user_id"]) {
                        if ($pinfo["user_id"] < 0) {
                            $passed_info = $this->lng->txt("crs_passed_status_system");
                        } elseif ($pinfo["user_id"] > 0) {
                            $name = ilObjUser::_lookupName($pinfo["user_id"]);
                            $passed_info = $this->lng->txt("crs_passed_status_manual_by") . ": " . $name["login"];
                        }
                    }
                    if ($pinfo["timestamp"]) {
                        $passed_info .= "<br />" . ilDatePresentation::formatDate($pinfo["timestamp"]);
                    }
                }
                $a_user_data[$user_id]["passed_info"] = $passed_info;
            }
        }

        // Custom user data fields
        if ($udf_ids !== []) {
            $data = ilUserDefinedData::lookupData($usr_ids, $udf_ids);
            foreach ($data as $usr_id => $fields) {
                if (!$this->checkAcceptance((int) $usr_id)) {
                    continue;
                }

                foreach ($fields as $field_id => $value) {
                    $a_user_data[$usr_id]['udf_' . $field_id] = $value;
                }
            }
        }
        // Object specific user data fields
        if ($odf_ids !== []) {
            $data = ilCourseUserData::_getValuesByObjId($this->getRepositoryObject()->getId());
            foreach ($data as $usr_id => $fields) {
                $usr_id = (int) $usr_id;
                // #7264: as we get data for all course members filter against user data
                if (!$this->checkAcceptance($usr_id) || !in_array($usr_id, $usr_ids)) {
                    continue;
                }

                foreach ($fields as $field_id => $value) {
                    $a_user_data[$usr_id]['odf_' . $field_id] = $value;
                }
            }

            // add last edit date
            foreach (ilObjectCustomUserFieldHistory::lookupEntriesByObjectId($this->getRepositoryObject()->getId()) as $usr_id => $edit_info) {
                if (!isset($a_user_data[$usr_id])) {
                    continue;
                }

                if ($usr_id == $edit_info['update_user']) {
                    $a_user_data[$usr_id]['odf_last_update'] = '';
                    $a_user_data[$usr_id]['odf_info_txt'] = $this->lng->txt('cdf_edited_by_self');
                    if (ilPrivacySettings::getInstance()->enabledAccessTimesByType($this->getRepositoryObject()->getType())) {
                        $a_user_data[$usr_id]['odf_last_update'] .= ('_' . $edit_info['editing_time']->get(IL_CAL_UNIX));
                        $a_user_data[$usr_id]['odf_info_txt'] .= (', ' . ilDatePresentation::formatDate($edit_info['editing_time']));
                    }
                } else {
                    $a_user_data[$usr_id]['odf_last_update'] = $edit_info['update_user'];
                    $a_user_data[$usr_id]['odf_last_update'] .= ('_' . $edit_info['editing_time']->get(IL_CAL_UNIX));

                    $name = ilObjUser::_lookupName($edit_info['update_user']);
                    $a_user_data[$usr_id]['odf_info_txt'] = ($name['firstname'] . ' ' . $name['lastname'] . ', ' . ilDatePresentation::formatDate($edit_info['editing_time']));
                }
            }
        }

        // consultation hours
        if ($this->isColumnSelected('consultation_hour')) {
            foreach (ilBookingEntry::lookupManagedBookingsForObject(
                $this->getRepositoryObject()->getId(),
                $this->user->getId()
            ) as $buser => $booking) {
                if (isset($a_user_data[$buser])) {
                    $a_user_data[$buser]['consultation_hour'] = $booking[0]['dt'];
                    $a_user_data[$buser]['consultation_hour_end'] = $booking[0]['dtend'];
                    $a_user_data[$buser]['consultation_hours'] = $booking;
                }
            }
        }

        // always sort by name first
        $a_user_data = ilArrayUtil::sortArray(
            $a_user_data,
            'name',
            $this->getOrderDirection()
        );
        $this->setData($a_user_data);
    }
}
