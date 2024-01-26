<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Membership/classes/class.ilParticipantsTableGUI.php';
/**
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 *
 * @ingroup ModulesCourse
 */
class ilCourseParticipantsTableGUI extends ilParticipantTableGUI
{
    protected $show_learning_progress = false;
    protected $show_timings = false;
    protected $show_lp_status_sync = false;


    /**
     * @var ilCertificateUserForObjectPreloader|null
     */
    private $preLoader;
    protected $cached_user_names = [];

    /**
     * Constructor
     *
     * @access public
     * @param
     * @param ilObject $rep_object
     * @param bool $a_show_learning_progress
     * @param bool $a_show_timings
     * @param bool $a_show_lp_status_sync
     * @param ilCertificateUserForObjectPreloader|null $preloader
     */
    public function __construct(
        $a_parent_obj,
        ilObject $rep_object,
        $a_show_learning_progress = false,
        $a_show_timings = false,
        $a_show_lp_status_sync = false,
        ilCertificateUserForObjectPreloader $preloader = null
    ) {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        $this->show_learning_progress = $a_show_learning_progress;
        if ($this->show_learning_progress) {
            include_once './Services/Tracking/classes/class.ilLPStatus.php';
        }

        if (null === $preloader) {
            $preloader = new ilCertificateUserForObjectPreloader(new ilUserCertificateRepository(), new ilCertificateActiveValidator());
        }
        $this->preLoader = $preloader;
        
        $this->show_timings = $a_show_timings;
        $this->show_lp_status_sync = $a_show_lp_status_sync;
        
        $this->rep_object = $rep_object;
        
        // #13208
        include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
        if (!ilObjUserTracking::_enabledLearningProgress()) {
            $this->show_lp_status_sync = false;
        }

        $this->lng = $lng;
        $this->lng->loadLanguageModule('crs');
        $this->lng->loadLanguageModule('trac');
        $this->lng->loadLanguageModule('rbac');
        $this->lng->loadLanguageModule('mmbr');
        $this->lng->loadLanguageModule('cert');

        $this->ctrl = $ilCtrl;

        include_once('./Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
        $this->privacy = ilPrivacySettings::_getInstance();
        
        include_once './Services/Membership/classes/class.ilParticipants.php';
        $this->participants = ilParticipants::getInstanceByObjId($this->getRepositoryObject()->getId());


        // required before constructor for columns
        $this->setId('crs_' . $this->getRepositoryObject()->getId());
        parent::__construct($a_parent_obj, 'participants');

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
        
        $this->getItems();
        $this->setTopCommands(true);
        $this->setEnableHeader(true);
        $this->setEnableTitle(true);
        $this->initFilter();
        
        $this->setShowRowsSelector(true);
            
        $preloader->preLoadDownloadableCertificates($this->getRepositoryObject()->getId());

        $lng->loadLanguageModule('certificate');

        $this->addMultiCommand('editParticipants', $this->lng->txt('edit'));
        $this->addMultiCommand('confirmDeleteParticipants', $this->lng->txt('remove'));
        $this->addMultiCommand('sendMailToSelectedUsers', $this->lng->txt('mmbr_btn_mail_selected_users'));
        $this->lng->loadLanguageModule('user');
        $this->addMultiCommand('addToClipboard', $this->lng->txt('clipboard_add_btn'));
        
        $this->addCommandButton('updateParticipantsStatus', $this->lng->txt('save'));
    }
    
    

    public function getItems()
    {
    }


    /**
     * fill row
     *
     * @access public
     * @param
     * @return
     */
    public function fillRow($a_set)
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];

        $this->tpl->setVariable('VAL_ID', $a_set['usr_id']);
        $this->tpl->setVariable('VAL_NAME', $a_set['lastname'] . ', ' . $a_set['firstname']);

        if (!$ilAccess->checkAccessOfUser($a_set['usr_id'], 'read', '', $this->getRepositoryObject()->getRefId()) and
            is_array($info = $ilAccess->getInfo())) {
            $this->tpl->setCurrentBlock('access_warning');
            $this->tpl->setVariable('PARENT_ACCESS', $info[0]['text']);
            $this->tpl->parseCurrentBlock();
        }

        if (!$a_set['active']) {
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
                    $a_set['birthday'] = $a_set['birthday'] ? ilDatePresentation::formatDate(new ilDate($a_set['birthday'], IL_CAL_DATE)) : $this->lng->txt('no_date');
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
                    $this->tpl->setVariable('VAL_CUST', (string) implode('<br />', $tmp)) ;
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
                    include_once './Modules/OrgUnit/classes/PathStorage/class.ilOrgUnitPathStorage.php';
                    $this->tpl->setVariable('VAL_CUST', (string) ilOrgUnitPathStorage::getTextRepresentationOfUsersOrgUnits($a_set['usr_id']));
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

        if ($ilAccess->checkAccess("grade", "", $this->rep_object->getRefId())) {
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
        if (true === $isPreloaded) {
            $this->tpl->setCurrentBlock('link');
            $this->tpl->setVariable('LINK_NAME', $this->ctrl->getLinkTarget($this->parent_obj, 'deliverCertificate'));
            $this->tpl->setVariable('LINK_TXT', $this->lng->txt('download_certificate'));
            $this->tpl->parseCurrentBlock();
        }
        $this->ctrl->clearParameters($this->parent_obj);

        if ($this->show_timings) {
            $this->ctrl->setParameterByClass('ilcoursecontentgui', 'member_id', $a_set['usr_id']);
            $this->tpl->setCurrentBlock('link');
            $this->tpl->setVariable('LINK_NAME', $this->ctrl->getLinkTargetByClass('ilcoursecontentgui', 'showUserTimings'));
            $this->tpl->setVariable('LINK_TXT', $this->lng->txt('timings_timings'));
            $this->tpl->parseCurrentBlock();
        }
    }

    /**
     * Parse data
     * @return
     *
     * @global ilRbacReview $rbacreview
     */
    public function parse()
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];

        $this->determineOffsetAndOrder(true);

        include_once './Services/User/classes/class.ilUserQuery.php';

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
        
        $part = $GLOBALS['DIC']->access()->filterUserIdsByRbacOrPositionOfCurrentUser(
            'manage_members',
            'manage_members',
            $this->getRepositoryObject()->getRefId(),
            $part
        );
        
        
        if (!$part) {
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
            if ($this->current_filter['roles']) {
                if (!$GLOBALS['DIC']['rbacreview']->isAssigned($user['usr_id'], $this->current_filter['roles'])) {
                    continue;
                }
            }
            if ($this->current_filter['org_units']) {
                $org_unit = $this->current_filter['org_units'];
                
                include_once './Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php';
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
            $this->getSelectedColumns(),
            true
        );
        $a_user_data = array();
        foreach ((array) $usr_data['set'] as $ud) {
            $user_id = $ud['usr_id'];
            
            if (!in_array($user_id, $usr_ids)) {
                continue;
            }
            // #15434
            if (!$this->checkAcceptance($user_id)) {
                $ud = [
                    'active' => $ud['active'],
                    'firstname' => $ud['firstname'],
                    'lastname' => $ud['lastname'],
                    'login' => $ud['login']
                ];
            }
                        
            $a_user_data[$user_id] = array_merge($ud, $course_user_data[$user_id]);


            $roles = array();
            foreach ($local_roles as $role_id => $role_name) {
                // @todo fix performance
                if ($GLOBALS['DIC']['rbacreview']->isAssigned($user_id, $role_id)) {
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
                            $name = $this->lookupUserName((int) $pinfo["user_id"]);
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
        if ($udf_ids) {
            include_once './Services/User/classes/class.ilUserDefinedData.php';
            $data = ilUserDefinedData::lookupData($usr_ids, $udf_ids);
            foreach ($data as $usr_id => $fields) {
                if (!$this->checkAcceptance($usr_id)) {
                    continue;
                }

                foreach ($fields as $field_id => $value) {
                    $a_user_data[$usr_id]['udf_' . $field_id] = $value;
                }
            }
        }
        // Object specific user data fields
        if ($odf_ids) {
            include_once './Modules/Course/classes/Export/class.ilCourseUserData.php';
            $data = ilCourseUserData::_getValuesByObjId($this->getRepositoryObject()->getId());
            foreach ($data as $usr_id => $fields) {
                // #7264: as we get data for all course members filter against user data
                if (!$this->checkAcceptance($usr_id) || !in_array($usr_id, $usr_ids)) {
                    continue;
                }

                foreach ($fields as $field_id => $value) {
                    $a_user_data[$usr_id]['odf_' . $field_id] = $value;
                }
            }
            
            
            // add last edit date
            include_once './Services/Membership/classes/class.ilObjectCustomUserFieldHistory.php';
            foreach (ilObjectCustomUserFieldHistory::lookupEntriesByObjectId($this->getRepositoryObject()->getId()) as $usr_id => $edit_info) {
                if (!isset($a_user_data[$usr_id])) {
                    continue;
                }
                
                include_once './Services/PrivacySecurity/classes/class.ilPrivacySettings.php';
                if ($usr_id == $edit_info['update_user']) {
                    $a_user_data[$usr_id]['odf_last_update'] = '';
                    $a_user_data[$usr_id]['odf_info_txt'] = $GLOBALS['DIC']['lng']->txt('cdf_edited_by_self');
                    if (ilPrivacySettings::_getInstance()->enabledAccessTimesByType($this->getRepositoryObject()->getType())) {
                        $a_user_data[$usr_id]['odf_last_update'] .= ('_' . $edit_info['editing_time']->get(IL_CAL_UNIX));
                        $a_user_data[$usr_id]['odf_info_txt'] .= (', ' . ilDatePresentation::formatDate($edit_info['editing_time']));
                    }
                } else {
                    $a_user_data[$usr_id]['odf_last_update'] = $edit_info['update_user'];
                    $a_user_data[$usr_id]['odf_last_update'] .= ('_' . $edit_info['editing_time']->get(IL_CAL_UNIX));
                    
                    $name = $this->lookupUserName((int) $edit_info['update_user']);
                    $a_user_data[$usr_id]['odf_info_txt'] = ($name['firstname'] . ' ' . $name['lastname'] . ', ' . ilDatePresentation::formatDate($edit_info['editing_time']));
                }
            }
        }

        // consultation hours
        if ($this->isColumnSelected('consultation_hour')) {
            include_once './Services/Booking/classes/class.ilBookingEntry.php';
            foreach (ilBookingEntry::lookupManagedBookingsForObject($this->getRepositoryObject()->getId(), $GLOBALS['DIC']['ilUser']->getId()) as $buser => $booking) {
                if (isset($a_user_data[$buser])) {
                    $a_user_data[$buser]['consultation_hour'] = $booking[0]['dt'];
                    $a_user_data[$buser]['consultation_hour_end'] = $booking[0]['dtend'];
                    $a_user_data[$buser]['consultation_hours'] = $booking;
                }
            }
        }
        
        // always sort by name first
        $a_user_data = ilUtil::sortArray(
            $a_user_data,
            'name',
            $this->getOrderDirection()
        );
        
        return $this->setData($a_user_data);
    }

    /**
     * @return array{user_id: int, firstname: string, lastname: string, login: string, title: string}
     */
    protected function lookupUserName(int $user_id) : array
    {
        if (isset($this->cached_user_names[$user_id])) {
            return $this->cached_user_names[$user_id];
        }
        return $this->cached_user_names[$user_id] = ilObjUser::_lookupName($user_id);
    }
}
