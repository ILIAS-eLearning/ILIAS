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

/**
 * GUI class for course/group subscriptions
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ServicesMembership
 */
class ilSubscriberTableGUI extends ilTable2GUI
{
    protected static ?array $all_columns = null;
    protected static bool $has_odf_definitions = false;
    protected array $subscribers = array();
    protected bool $show_subject = true;
    protected ilObject $rep_object;

    public function __construct(
        object $a_parent_obj,
        ilObject $rep_object,
        bool $show_content = true,
        bool $show_subject = true
    ) {
        $this->rep_object = $rep_object;
        $this->setId('crs_sub_' . $this->getRepositoryObject()->getId());
        parent::__construct($a_parent_obj, 'participants');

        $this->lng->loadLanguageModule('grp');
        $this->lng->loadLanguageModule('crs');
        $this->setShowSubject($show_subject);
        $this->setFormName('subscribers');
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, 'participants'));

        $this->addColumn('', 'f', "1%", true);
        $this->addColumn($this->lng->txt('name'), 'lastname', '20%');

        $all_cols = $this->getSelectableColumns();
        foreach ($this->getSelectedColumns() as $col) {
            $this->addColumn(
                $all_cols[$col]['txt'],
                $col,
                array_key_exists('width', $all_cols[$col]) ? $all_cols[$col]['width'] : ""
            );
        }

        if ($this->getShowSubject()) {
            $this->addColumn($this->lng->txt('application_date'), 'sub_time', "20%");
            $this->addColumn($this->lng->txt('subject'), 'subject', '60%');
        } else {
            $this->addColumn($this->lng->txt('application_date'), 'sub_time', "60%");
        }
        $this->addColumn('', 'mail', '20%');
        if ($this->getRepositoryObject()->getType() === "sess") {
            $this->addMultiCommand('confirmAssignSubscribers', $this->lng->txt('sess_accept_request'));
        } else {
            $this->addMultiCommand('confirmAssignSubscribers', $this->lng->txt('assign'));
        }
        $this->addMultiCommand('confirmRefuseSubscribers', $this->lng->txt('refuse'));
        $this->addMultiCommand('sendMailToSelectedUsers', $this->lng->txt('crs_mem_send_mail'));

        // begin-patch clipboard
        $this->lng->loadLanguageModule('user');
        $this->addMultiCommand('addToClipboard', $this->lng->txt('clipboard_add_btn'));
        // end-patch clipboard

        $this->setPrefix('subscribers');
        $this->setSelectAllCheckbox('subscribers', true);
        $this->setRowTemplate("tpl.show_subscribers_row.html", "Services/Membership");

        if ($show_content) {
            $this->enable('sort');
            $this->enable('header');
            $this->enable('numinfo');
            $this->enable('select_all');
        } else {
            $this->disable('content');
            $this->disable('header');
            $this->disable('footer');
            $this->disable('numinfo');
            $this->disable('select_all');
        }

        $this->setExternalSegmentation(true);
        self::$has_odf_definitions = (bool) ilCourseDefinedFieldDefinition::_hasFields($this->getRepositoryObject()->getId());
    }

    protected function getRepositoryObject(): ilObject
    {
        return $this->rep_object;
    }

    public function getSelectableColumns(): array
    {
        if (self::$all_columns) {
            return self::$all_columns;
        }

        if ($this->getRepositoryObject()->getType() === 'sess') {
            self::$all_columns['login'] = [
                'txt' => $this->lng->txt('login'),
                'width' => '15%',
                'default' => 1
            ];
            return self::$all_columns;
        }
        $ef = ilExportFieldsInfo::_getInstanceByType($this->getRepositoryObject()->getType());
        self::$all_columns = $ef->getSelectableFieldsInfo($this->getRepositoryObject()->getId());

        // #25215
        if (
            is_array(self::$all_columns) &&
            array_key_exists('consultation_hour', self::$all_columns)
        ) {
            unset(self::$all_columns['consultation_hour']);
        }
        return self::$all_columns;
    }

    protected function fillRow(array $a_set): void
    {
        if (!ilObjCourseGrouping::_checkGroupingDependencies($this->getRepositoryObject(), (int) $a_set['usr_id']) and
            ($ids = ilObjCourseGrouping::getAssignedObjects())) {
            $prefix = $this->getRepositoryObject()->getType();
            $this->tpl->setVariable(
                'ALERT_MSG',
                sprintf(
                    $this->lng->txt($prefix . '_lim_assigned'),
                    ilObject::_lookupTitle(current($ids))
                )
            );
        }
        $this->tpl->setVariable('VAL_ID', $a_set['usr_id']);
        $this->tpl->setVariable('VAL_NAME', $a_set['lastname'] . ', ' . $a_set['firstname']);

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

                case 'odf_last_update':
                    $this->tpl->setVariable('VAL_CUST', (string) $a_set['odf_info_txt']);
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

        $this->tpl->setVariable(
            'VAL_SUBTIME',
            ilDatePresentation::formatDate(new ilDateTime($a_set['sub_time'], IL_CAL_UNIX))
        );
        $this->showActionLinks($a_set);
        if ($this->getShowSubject()) {
            if (strlen($a_set['subject'])) {
                $this->tpl->setCurrentBlock('subject');
                $this->tpl->setVariable('VAL_SUBJECT', '"' . $a_set['subject'] . '"');
                $this->tpl->parseCurrentBlock();
            } else {
                $this->tpl->touchBlock('subject');
            }
        }
    }

    /**
     * Show action links (mail ; edit crs|grp data)
     */
    public function showActionLinks(array $a_set): void
    {
        if (!self::$has_odf_definitions) {
            $this->ctrl->setParameterByClass(get_class($this->getParentObject()), 'member_id', $a_set['usr_id']);
            $link = $this->ctrl->getLinkTargetByClass(get_class($this->getParentObject()), 'sendMailToSelectedUsers');
            $this->tpl->setVariable('MAIL_LINK', $link);
            $this->tpl->setVariable('MAIL_TITLE', $this->lng->txt('crs_mem_send_mail'));
            return;
        }

        // show action menu
        $list = new ilAdvancedSelectionListGUI();
        $list->setSelectionHeaderClass('small');
        $list->setItemLinkClass('small');
        $list->setId('actl_' . $a_set['usr_id'] . '_' . $this->getId());
        $list->setListTitle($this->lng->txt('actions'));

        $this->ctrl->setParameterByClass(get_class($this->getParentObject()), 'member_id', $a_set['usr_id']);
        $this->ctrl->setParameter($this->parent_obj, 'member_id', $a_set['usr_id']);
        $trans = $this->lng->txt($this->getRepositoryObject()->getType() . '_mem_send_mail');
        $link = $this->ctrl->getLinkTargetByClass(get_class($this->getParentObject()), 'sendMailToSelectedUsers');
        $list->addItem($trans, '', $link, 'sendMailToSelectedUsers');

        $this->ctrl->setParameterByClass('ilobjectcustomuserfieldsgui', 'member_id', $a_set['usr_id']);
        $trans = $this->lng->txt($this->getRepositoryObject()->getType() . '_cdf_edit_member');
        $list->addItem($trans, '', $this->ctrl->getLinkTargetByClass('ilobjectcustomuserfieldsgui', 'editMember'));

        $this->tpl->setVariable('ACTION_USER', $list->getHTML());
    }

    /**
     * read data
     * @access protected
     * @param int[] subscriber ids
     * @throws ilDateTimeException
     */
    public function readSubscriberData(array $a_subscriber_ids): void
    {
        $subscriber_data = ilParticipants::lookupSubscribersData($this->getRepositoryObject()->getId());
        $sub_ids = $sub_data = [];
        foreach ($a_subscriber_ids as $usr_id) {
            $sub_ids[] = $usr_id;
            $sub_data[$usr_id] = $subscriber_data[$usr_id];
        }

        $this->determineOffsetAndOrder();

        $additional_fields = $this->getSelectedColumns();
        unset(
            $additional_fields["firstname"],
            $additional_fields["lastname"],
            $additional_fields["last_login"],
            $additional_fields["access_until"],
            $additional_fields['org_units']
        );

        $udf_ids = $usr_data_fields = $odf_ids = array();
        foreach ($additional_fields as $field) {
            if (strpos($field, 'udf') === 0) {
                $udf_ids[] = substr($field, 4);
                continue;
            }
            if (strpos($field, 'odf') === 0) {
                $odf_ids[] = substr($field, 4);
                continue;
            }

            $usr_data_fields[] = $field;
        }

        $usr_data = ilUserQuery::getUserListData(
            $this->getOrderField(),
            $this->getOrderDirection(),
            $this->getOffset(),
            $this->getLimit(),
            '',
            '',
            null,
            false,
            false,
            0,
            0,
            null,
            $usr_data_fields,
            $sub_ids
        );

        $usr_ids = [];
        foreach ((array) $usr_data['set'] as $user) {
            $usr_ids[] = $user['usr_id'];
        }

        // merge course data
        $course_user_data = $this->getParentObject()->readMemberData($usr_ids, array());
        $a_user_data = array();
        foreach ((array) $usr_data['set'] as $ud) {
            $a_user_data[$ud['usr_id']] = array_merge($ud, (array) $course_user_data[$ud['usr_id']]);
        }

        // Custom user data fields
        if (is_array($udf_ids)) {
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
        if (is_array($odf_ids)) {
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
            foreach (ilObjectCustomUserFieldHistory::lookupEntriesByObjectId($this->getRepositoryObject()->getId()) as $usr_id => $edit_info) {
                if (!isset($a_user_data[$usr_id])) {
                    continue;
                }

                if ($usr_id == $edit_info['update_user']) {
                    $a_user_data[$usr_id]['odf_last_update'] = '';
                    $a_user_data[$usr_id]['odf_info_txt'] = $GLOBALS['DIC']['lng']->txt('cdf_edited_by_self');
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

        foreach ($usr_data['set'] as $user) {
            // Check acceptance
            if (!$this->checkAcceptance((int) $user['usr_id'])) {
                continue;
            }
            // DONE: accepted
            foreach ($usr_data_fields as $field) {
                $a_user_data[$user['usr_id']][$field] = $user[$field] ?: '';
            }
        }

        // Waiting list subscription
        foreach ($sub_data as $usr_id => $usr_data) {
            if (!in_array($usr_id, $usr_ids)) {
                continue;
            }
            $a_user_data[$usr_id]['sub_time'] = $usr_data['time'];
            $a_user_data[$usr_id]['subject'] = $usr_data['subject'];
        }

        $this->setMaxCount(count($sub_ids));
        $this->setData($a_user_data);
    }

    protected function checkAcceptance(int $a_usr_id): bool
    {
        return true;
    }

    public function setShowSubject(bool $a_value): void
    {
        $this->show_subject = $a_value;
    }

    public function getShowSubject(): bool
    {
        return $this->show_subject;
    }
}
