<?php

declare(strict_types=1);

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

class ilLearningSequenceParticipantsTableGUI extends ilParticipantTableGUI
{
    protected ilLearningSequenceMembershipGUI $parent_gui;
    protected ilObjLearningSequence $ls_object;
    protected ilObjUserTracking $obj_user_tracking;
    protected ilPrivacySettings $privacy_settings;
    protected ilAccess $access;
    protected ilRbacReview $rbac_review;
    protected ilSetting $settings;

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

    protected function initForm(): void
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

    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('VAL_ID', $a_set['usr_id']);
        $this->tpl->setVariable('VAL_NAME', $a_set['lastname'] . ', ' . $a_set['firstname']);
        $this->tpl->setVariable('VAL_LOGIN', $a_set['login']);

        if (
            !$this->access->checkAccessOfUser((int) $a_set['usr_id'], 'read', '', $this->getRepositoryObject()->getRefId()) &&
            is_array($info = $this->access->getInfo())
        ) {
            $this->tpl->setCurrentBlock('access_warning');
            $this->tpl->setVariable('PARENT_ACCESS', $info[0]['text']);
            $this->tpl->parseCurrentBlock();
        }

        if (!ilObjUser::_lookupActive((int) $a_set['usr_id'])) {
            $this->tpl->setCurrentBlock('access_warning');
            $this->tpl->setVariable('PARENT_ACCESS', $this->lng->txt('usr_account_inactive'));
            $this->tpl->parseCurrentBlock();
        }

        foreach ($this->getSelectedColumns() as $field) {
            switch ($field) {
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
                case 'roles':
                    $this->tpl->setCurrentBlock('custom_fields');
                    $this->tpl->setVariable('VAL_CUST', (string) $a_set['roles_label']);
                    $this->tpl->parseCurrentBlock();
                    break;
                case 'org_units':
                    $this->tpl->setCurrentBlock('custom_fields');
                    $this->tpl->setVariable('VAL_CUST', ilOrgUnitPathStorage::getTextRepresentationOfUsersOrgUnits($a_set['usr_id']));
                    $this->tpl->parseCurrentBlock();
                    break;
                default:
                    $this->tpl->setCurrentBlock('custom_fields');
                    $this->tpl->setVariable('VAL_CUST', isset($a_set[$field]) ? (string) $a_set[$field] : '');
                    $this->tpl->parseCurrentBlock();
                    break;
            }
        }

        $this->tpl->setVariable('VAL_POSTNAME', 'participants');

        if (
            $this->obj_user_tracking->hasExtendedData(ilObjUserTracking::EXTENDED_DATA_LAST_ACCESS) &&
            ilObjUserTracking::_enabledLearningProgress()

        ) {
            $this->tpl->setVariable('FIRST_ACCESS', $this->getFirstAccess((int) $a_set['usr_id']));
            $this->tpl->setVariable('LAST_ACCESS', $this->getLastAccess((int) $a_set['usr_id']));
        }

        $this->tpl->setVariable('COMPLETED_STEPS', $this->getCompletedSteps((int) $a_set['usr_id']));
        $this->tpl->setVariable('LAST_VISITED_STEP', $this->getLastVisitedStep((int) $a_set['usr_id']));

        if ($this->getParticipants()->isAdmin((int) $a_set['usr_id'])) {
            $this->tpl->setVariable('VAL_NOTIFICATION_ID', (int) $a_set['usr_id']);
            $this->tpl->setVariable(
                'VAL_NOTIFICATION_CHECKED',
                $a_set['notification'] ? 'checked="checked"' : ''
            );
        }

        $this->showActionLinks($a_set);
        $this->tpl->setVariable('VAL_LOGIN', $a_set['login']);
    }

    protected function getFirstAccess(int $user_id): string
    {
        $data = $this->getRepositoryObject()->getStateDB()->getFirstAccessFor(
            $this->getRepositoryObject()->getRefId(),
            [$user_id]
        );

        if ($data[$user_id] === '-1') {
            return "-";
        }

        return $data[$user_id];
    }

    protected function getLastAccess(int $user_id): string
    {
        $data = $this->getRepositoryObject()->getStateDB()->getLastAccessFor(
            $this->getRepositoryObject()->getRefId(),
            [$user_id]
        );

        if ($data[$user_id] === '-1') {
            return "-";
        }

        return $data[$user_id];
    }

    protected function getCompletedSteps(int $user_id): string
    {
        $passed = 0;

        $learner_items = $this->getRepositoryObject()->getLSLearnerItems(
            $user_id
        );

        $completion_states = $this->rep_object->getLPCompletionStates();

        foreach ($learner_items as $learner_item) {
            if (in_array($learner_item->getLearningProgressStatus(), $completion_states)) {
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
    protected function getLastVisitedStep(int $user_id): string
    {
        $data = $this->getRepositoryObject()->getStateDB()->getCurrentItemsFor(
            $this->getRepositoryObject()->getRefId(),
            [$user_id]
        );

        if ($data[$user_id] === -1) {
            return "-";
        }

        return $this->getTitleFor((int) $data[$user_id]);
    }

    protected function getTitleFor(int $ref_id): string
    {
        return ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id));
    }

    public function parse(): void
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

            if (array_key_exists('org_units', $this->current_filter)) {
                $org_unit = $this->current_filter['org_units'];
                $title = ilObjectFactory::getInstanceByRefId($org_unit)->getTitle();
                $user_units = ilOrgUnitPathStorage::getTextRepresentationOfUsersOrgUnits($user_id);
                if (strpos($user_units, $title) === false) {
                    continue;
                }
            }

            $filtered_user_ids[] = $user_id;
            $user_data[$user_id] = array_merge($ud, $lso_user_data[$user_id]);

            $roles = array();
            foreach ($local_roles as $role_id => $role_name) {
                if ($this->rbac_review->isAssigned((int) $user_id, $role_id)) {
                    $roles[] = $role_name;
                }
            }

            $user_data[$user_id]['name'] = $user_data[$user_id]['lastname'] . ', ' . $user_data[$user_id]['firstname'];
            $user_data[$user_id]['roles_label'] = implode('<br />', $roles);
            $user_data[$user_id]['roles'] = $this->participants->setRoleOrderPosition((int) $user_id);
        }

        // Custom user data fields
        if ($udf_ids !== []) {
            $data = ilUserDefinedData::lookupData($ls_participants, $udf_ids);
            foreach ($data as $usr_id => $fields) {
                if (!$this->checkAcceptance((int) $usr_id)) {
                    continue;
                }

                foreach ($fields as $field_id => $value) {
                    $user_data[$usr_id]['udf_' . $field_id] = $value;
                }
            }
        }

        $user_data = ilArrayUtil::sortArray(
            $user_data,
            'name',
            $this->getOrderDirection()
        );

        $this->setData($user_data);
    }

    public function getSelectableColumns(): array
    {
        $ef = $this->getExportFieldsInfo();
        $columns = $ef->getSelectableFieldsInfo(
            $this->getRepositoryObject()->getId()
        );

        if ($this->settings->get('user_portfolios')) {
            $columns['prtf'] = array(
                'txt' => $this->lng->txt('obj_prtf'),
                'default' => false
            );
        }

        return array_merge(
            [
                'roles' => [
                    'txt' => $this->lng->txt('objs_role'),
                    'default' => true
                ]
            ],
            [
                'org_units' => [
                    'txt' => $this->lng->txt("org_units"),
                    'default' => false
                ]
            ],
            $columns
        );
    }

    protected function getExportFieldsInfo(): ilExportFieldsInfo
    {
        return ilExportFieldsInfo::_getInstanceByType(
            $this->getRepositoryObject()->getType()
        );
    }
}
