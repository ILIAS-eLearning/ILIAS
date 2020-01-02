<?php

declare(strict_types=1);

/**
* @author Daniel Weise <daniel.weise@concepts-and-training.de>
*/
class ilLearningSequenceParticipantsTableGUI extends ilParticipantTableGUI
{
    /**
     * @var bool
     */
    protected $show_learning_progress;

    /**
     * @var array
     */
    protected $current_filter = array();

    public function __construct(
        ilLearningSequenceMembershipGUI $parent_gui,
        ilObjLearningSequence $ls_object,
        ilObjUserTracking $obj_user_tracking,
        ilPrivacySettings $privacy_settings,
        ilLanguage $lng,
        ilAccess $access,
        ilRbacReview $rbac_review,
        ilSetting $settings
    ) {
        $this->parent_gui = $parent_gui;
        $this->rep_object = $ls_object;
        $this->show_learning_progress = $show_learning_progress;

        $this->obj_user_tracking = $obj_user_tracking;
        $this->privacy_settings = $privacy_settings;
        $this->lng = $lng;
        $this->access = $access;
        $this->rbac_review = $rbac_review;
        $this->settings = $settings;

        $this->lng->loadLanguageModule('lso');
        $this->lng->loadLanguageModule('trac');
        $this->lng->loadLanguageModule('rbac');
        $this->lng->loadLanguageModule('mmbr');
        $this->lng->loadLanguageModule('user');
        $this->lng->loadLanguageModule('ps');

        $this->participants = $ls_object->getLSParticipants();

        $this->setPrefix('participants');

        $this->setId('lso_' . $this->getRepositoryObject()->getId());
        parent::__construct($parent_gui, 'participants');

        $this->initSettings();
        $this->initForm();
    }

    protected function initForm()
    {
        $this->setFormName('participants');
        $this->setDefaultOrderField('roles');
        $this->setRowTemplate("tpl.show_participants_row.html", "Modules/LearningSequence");
        $this->setShowRowsSelector(true);
        $this->setSelectAllCheckbox('participants');

        $this->addColumn('', 'f', "1");
        $this->addColumn($this->lng->txt('name'), 'lastname', '20%');
        $this->addColumn($this->lng->txt('login'), 'login');

        $all_cols = $this->getSelectableColumns();
        foreach ($this->getSelectedColumns() as $col) {
            $this->addColumn($all_cols[$col]['txt'], $col);
        }

        if (
            $this->obj_user_tracking->hasExtendedData(ilObjUserTracking::EXTENDED_DATA_LAST_ACCESS) &&
            ilObjUserTracking::_enabledLearningProgress()
        ) {
            $this->addColumn($this->lng->txt('first_access'), "first_access");
            $this->addColumn($this->lng->txt('last_access'), "last_access");
        }
        $this->addColumn($this->lng->txt('completed_steps'), "completed_steps");
        $this->addColumn($this->lng->txt('last_visited_step'), "last_visited_step");
        $this->addColumn($this->lng->txt('lso_notification'), 'notification');
        $this->addColumn($this->lng->txt(''), 'optional');

        $this->enable('sort');
        $this->enable('header');
        $this->enable('numinfo');
        $this->enable('select_all');

        $this->initFilter();

        $this->addMultiCommand('editParticipants', $this->lng->txt('edit'));
        $this->addMultiCommand('confirmDeleteParticipants', $this->lng->txt('remove'));
        $this->addMultiCommand('sendMailToSelectedUsers', $this->lng->txt('mmbr_btn_mail_selected_users'));
        $this->addMultiCommand('addToClipboard', $this->lng->txt('clipboard_add_btn'));
        $this->addCommandButton('updateParticipantsStatus', $this->lng->txt('save'));
    }

    public function fillRow($set)
    {
        $this->tpl->setVariable('VAL_ID', $set['usr_id']);
        $this->tpl->setVariable('VAL_NAME', $set['lastname'] . ', ' . $set['firstname']);
        $this->tpl->setVariable('VAL_LOGIN', $set['login']);

        if (
            !$this->access->checkAccessOfUser($set['usr_id'], 'read', '', $this->getRepositoryObject()->getRefId()) &&
            is_array($info = $this->access->getInfo())
        ) {
            $this->tpl->setCurrentBlock('access_warning');
            $this->tpl->setVariable('PARENT_ACCESS', $info[0]['text']);
            $this->tpl->parseCurrentBlock();
        }

        if (!ilObjUser::_lookupActive($set['usr_id'])) {
            $this->tpl->setCurrentBlock('access_warning');
            $this->tpl->setVariable('PARENT_ACCESS', $this->lng->txt('usr_account_inactive'));
            $this->tpl->parseCurrentBlock();
        }

        foreach ($this->getSelectedColumns() as $field) {
            switch ($field) {
                case 'prtf':
                    $tmp = array();
                    if (is_array($set['prtf'])) {
                        foreach ($set['prtf'] as $prtf_url => $prtf_txt) {
                            $tmp[] = '<a href="' . $prtf_url . '">' . $prtf_txt . '</a>';
                        }
                    }
                    $this->tpl->setCurrentBlock('custom_fields');
                    $this->tpl->setVariable('VAL_CUST', (string) implode('<br />', $tmp));
                    $this->tpl->parseCurrentBlock();
                    break;
                case 'roles':
                    $this->tpl->setCurrentBlock('custom_fields');
                    $this->tpl->setVariable('VAL_CUST', (string) $set['roles_label']);
                    $this->tpl->parseCurrentBlock();
                    break;
                case 'org_units':
                    $this->tpl->setCurrentBlock('custom_fields');
                    $this->tpl->setVariable('VAL_CUST', (string) ilOrgUnitPathStorage::getTextRepresentationOfUsersOrgUnits($set['usr_id']));
                    $this->tpl->parseCurrentBlock();
                    break;
                default:
                    $this->tpl->setCurrentBlock('custom_fields');
                    $this->tpl->setVariable('VAL_CUST', isset($set[$field]) ? (string) $set[$field] : '');
                    $this->tpl->parseCurrentBlock();
                    break;
            }
        }

        $this->tpl->setVariable('VAL_POSTNAME', 'participants');
        $this->tpl->setVariable('FIRST_ACCESS', $this->getFirstAccess((int) $set['usr_id']));
        $this->tpl->setVariable('LAST_ACCESS', $this->getLastAccess((int) $set['usr_id']));
        $this->tpl->setVariable('COMPLETED_STEPS', $this->getCompletedSteps((int) $set['usr_id']));
        $this->tpl->setVariable('LAST_VISITED_STEP', $this->getLastVisitedStep((int) $set['usr_id']));

        if ($this->getParticipants()->isAdmin($set['usr_id'])) {
            $this->tpl->setVariable('VAL_NOTIFICATION_ID', $set['usr_id']);
            $this->tpl->setVariable(
                'VAL_NOTIFICATION_CHECKED',
                $set['notification'] ? 'checked="checked"' : ''
            );
        }

        $this->showActionLinks($set);
        $this->tpl->setVariable('VAL_LOGIN', $set['login']);
    }

    protected function getFirstAccess(int $user_id)
    {
        $data = $this->getRepositoryObject()->getStateDB()->getFirstAccessFor(
            (int) $this->getRepositoryObject()->getRefId(),
            array($user_id)
        );

        if ($data[$user_id] === -1) {
            return "-";
        }

        return $data[$user_id];
    }

    protected function getLastAccess(int $user_id)
    {
        $data = $this->getRepositoryObject()->getStateDB()->getLastAccessFor(
            (int) $this->getRepositoryObject()->getRefId(),
            array($user_id)
        );

        if ($data[$user_id] === -1) {
            return "-";
        }

        return $data[$user_id];
    }

    protected function getCompletedSteps(int $user_id)
    {
        $passed = 0;
        $learner_items = array();

        $learner_items = $this->getRepositoryObject()->getLSLearnerItems(
            $user_id
        );

        foreach ($learner_items as $learner_item) {
            if ($learner_item->getLearningProgressStatus() === 1) {
                $passed++;
            }
        }

        return $passed . " / " . count($learner_items);
    }

    /**
     * Different to the concept we decide to use the title of
     * the object instead of its actually number in the ls items list.
     * The ls item list could change and the number isn't very revealing.
     */
    protected function getLastVisitedStep(int $user_id)
    {
        $data = $this->getRepositoryObject()->getStateDB()->getCurrentItemsFor(
            (int) $this->getRepositoryObject()->getRefId(),
            array($user_id)
        );

        if ($data[$user_id] === -1) {
            return "-";
        }

        return $this->getTitleFor((int) $data[$user_id]);
    }

    protected function getTitleFor(int $ref_id) : string
    {
        return ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id));
    }

    public function parse()
    {
        $this->determineOffsetAndOrder(true);

        $ls_participants = $this->participants->getParticipants();

        $ls_participants = $this->access->filterUserIdsByRbacOrPositionOfCurrentUser(
            'manage_members',
            'manage_members',
            $this->getRepositoryObject()->getRefId(),
            $ls_participants
        );

        if (!$ls_participants) {
            $this->setData(array());
            return;
        }

        $lso_user_data = $this->parent_gui->readMemberData(
            $ls_participants,
            $this->getSelectedColumns()
        );

        $additional_fields = $this->getSelectedColumns();
        unset($additional_fields['prtf']);
        unset($additional_fields['roles']);
        unset($additional_fields['org_units']);

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
            $this->getOrderField(),
            $this->getOrderDirection(),
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
            $ls_participants
        );

        $user_data = array();
        $filtered_user_ids = array();
        $local_roles = $this->parent_gui->getLocalRoles();

        foreach ($usr_data['set'] as $ud) {
            $user_id = $ud['usr_id'];

            if ($this->current_filter['roles']) {
                if (!$this->rbac_review->isAssigned($user_id, $this->current_filter['roles'])) {
                    continue;
                }
            }

            if ($this->current_filter['org_units']) {
                $org_unit = $this->current_filter['org_units'];
                $title = ilObjectFactory::getInstanceByRefId($org_unit)->getTitle();
                $user_units = (string) ilOrgUnitPathStorage::getTextRepresentationOfUsersOrgUnits($user_id);
                if (strpos($user_units, $title) === false) {
                    continue;
                }
            }

            $filtered_user_ids[] = $user_id;
            $user_data[$user_id] = array_merge($ud, (array) $lso_user_data[$user_id]);

            $roles = array();
            foreach ($local_roles as $role_id => $role_name) {
                if ($this->rbac_review->isAssigned($user_id, $role_id)) {
                    $roles[] = $role_name;
                }
            }

            $user_data[$user_id]['name'] = $user_data[$user_id]['lastname'] . ', ' . $user_data[$user_id]['firstname'];
            $user_data[$user_id]['roles_label'] = implode('<br />', $roles);
            $user_data[$user_id]['roles'] = $this->participants->setRoleOrderPosition($user_id);
        }

        // Custom user data fields
        if ($udf_ids) {
            $data = ilUserDefinedData::lookupData($ls_participants, $udf_ids);
            foreach ($data as $usr_id => $fields) {
                if (!$this->checkAcceptance($usr_id)) {
                    continue;
                }

                foreach ($fields as $field_id => $value) {
                    $user_data[$usr_id]['udf_' . $field_id] = $value;
                }
            }
        }

        $user_data = ilUtil::sortArray(
            $user_data,
            'name',
            $this->getOrderDirection()
        );

        return $this->setData($user_data);
    }

    public function getSelectableColumns()
    {
        $ef = $this->getExportFieldsInfo();
        $columns = array();
        $columns = $ef->getSelectableFieldsInfo(
            $this->getRepositoryObject()->getId()
        );

        if ($this->settings->get('user_portfolios')) {
            $columns['prtf'] = array(
                'txt' => $this->lng->txt('obj_prtf'),
                'default' => false
            );
        }

        $columns = array_merge(
            array(
                'roles' => array(
                    'txt' => $this->lng->txt('objs_role'),
                    'default' => true
                )
            ),
            array(
                'org_units' => array(
                    'txt' => $this->lng->txt("org_units"),
                    'default' => false
                )
            ),
            $columns
        );

        return $columns;
    }

    protected function getExportFieldsInfo()
    {
        return ilExportFieldsInfo::_getInstanceByType(
            $this->getRepositoryObject()->getType()
        );
    }
}
