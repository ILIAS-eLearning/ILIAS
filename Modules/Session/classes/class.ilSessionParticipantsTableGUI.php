<?php declare(strict_types=1);

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
 ********************************************************************
 */

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilSessionParticipantsTableGUI extends ilTable2GUI
{
    protected static array $all_columns = [];

    private ilLogger $logger;
    private ilTree $tree;
    private \ILIAS\DI\RBACServices $rbac;
    private ilObjSession $rep_object;
    private ilParticipants $participants;
    private int $parent_ref_id = 0;
    private int $member_ref_id = 0;
    private array $current_filter = [];

    public function __construct(object $a_parent_gui, ilObjSession $a_parent_obj, string $a_parent_cmd)
    {
        global $DIC;

        $this->logger = $DIC->logger()->root();
        $this->tree = $DIC->repositoryTree();
        $this->rbac = $DIC->rbac();

        $this->rep_object = $a_parent_obj;

        $this->participants = ilParticipants::getInstance($this->getRepositoryObject()->getRefId());
        
        $this->setId('session_part_' . $this->getRepositoryObject()->getId());
        parent::__construct($a_parent_gui, $a_parent_cmd);
        
        $this->parent_ref_id = $this->tree->getParentId(
            $this->getRepositoryObject()->getRefId()
        );

        if ($member_ref = $this->tree->checkForParentType($this->parent_ref_id, 'grp')) {
            $this->member_ref_id = $member_ref;
        } elseif ($member_ref = $this->tree->checkForParentType($this->parent_ref_id, 'crs')) {
            $this->member_ref_id = $member_ref;
        } else {
            throw new \InvalidArgumentException("Error in tree structure. Session has no parent course/group ref_id: " . $this->getRepositoryObject()->getRefId());
        }
    }

    protected function getRepositoryObject() : ilObjSession
    {
        return $this->rep_object;
    }

    protected function isRegistrationEnabled() : bool
    {
        return $this->getRepositoryObject()->enabledRegistration();
    }

    protected function getParticipants() : ilParticipants
    {
        return $this->participants;
    }

    public function init() : void
    {
        $this->lng->loadLanguageModule('sess');
        $this->lng->loadLanguageModule('crs');
        $this->lng->loadLanguageModule('trac');
        $this->lng->loadLanguageModule('mmbr');
        
        $this->setFormName('participants');
        
        $this->initFilter();
        

        $this->setSelectAllCheckbox('participants', true);
        $this->setShowRowsSelector(true);
        
        $this->enable('sort');
        $this->enable('header');
        $this->enable('numinfo');
        $this->enable('select_all');
        
        
        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject(), $this->getParentCmd()));

        $this->setRowTemplate("tpl.sess_members_row.html", "Modules/Session");

        $this->addColumn('', 'f', '1', true);
        $this->addColumn($this->lng->txt('name'), 'name', '20%');
        $this->addColumn($this->lng->txt('login'), 'login', '10%');
        
        $all_cols = $this->getSelectableColumns();
        foreach ($this->getSelectedColumns() as $col) {
            $this->addColumn($all_cols[$col]['txt'], $col);
        }
        
        
        if ($this->isRegistrationEnabled()) {
            $this->addColumn($this->lng->txt('event_tbl_registered'), 'registered');
            $this->setDefaultOrderField('registered');
            $this->setDefaultOrderDirection('desc');
        } else {
            $this->setDefaultOrderField('name');
        }

        $this->addColumn($this->lng->txt('event_tbl_participated'), 'participated');

        if ($this->isRegistrationEnabled()) {
            $this->addColumn($this->lng->txt('sess_part_table_excused'), 'excused');
        }

        $this->addColumn($this->lng->txt('sess_contact'), 'contact');
        if (true === $this->getRepositoryObject()->isRegistrationNotificationEnabled()) {
            $this->addColumn($this->lng->txt('notification'), 'notification_checked');
        }

        $this->addColumn($this->lng->txt('trac_mark'), 'mark');
        $this->addColumn($this->lng->txt('trac_comment'), 'comment');


        $this->addMultiCommand('sendMailToSelectedUsers', $this->lng->txt('mmbr_btn_mail_selected_users'));
        $this->lng->loadLanguageModule('user');
        $this->addMultiCommand('addToClipboard', $this->lng->txt('clipboard_add_btn'));
        
        
        $this->addCommandButton('updateMembers', $this->lng->txt('save'));
    }
    
    public function initFilter() : void
    {
        $login = $this->addFilterItemByMetaType(
            'login',
            ilTable2GUI::FILTER_TEXT,
            false,
            $this->lng->txt('name')
        );
        $this->current_filter['login'] = $login->getValue();
        
        
        if ($this->isColumnSelected('roles')) {
            $role = $this->addFilterItemByMetaType(
                'roles',
                ilTable2GUI::FILTER_SELECT,
                false,
                $this->lng->txt('objs_' . ilObject::_lookupType(ilObject::_lookupObjId($this->member_ref_id)) . '_role')
            );

            $options = [];
            $options[0] = $this->lng->txt('all_roles');
            $role->setOptions($options + $this->getParentLocalRoles());
            $this->current_filter['roles'] = $role->getValue();
        }
        
        if ($this->getRepositoryObject()->enabledRegistration()) {
            $reg = $this->addFilterItemByMetaType(
                'filter_registration',
                ilTable2GUI::FILTER_CHECKBOX,
                false,
                $this->lng->txt('sess_part_filter_registered')
            );
            $this->current_filter['filter_registration'] = $reg->getChecked();
        }
        $participated = $this->addFilterItemByMetaType(
            'filter_participated',
            ilTable2GUI::FILTER_CHECKBOX,
            false,
            $this->lng->txt('sess_part_filter_participated')
        );
        $this->current_filter['filter_participated'] = $participated->getChecked();
    }

    public function getSelectableColumns() : array
    {
        self::$all_columns['roles'] = array(
            'txt' => $this->lng->txt('objs_role'),
            'default' => true
        );
        
        return self::$all_columns;
    }

    public function parse() : void
    {
        $all_participants = [];
        $all_possible_participants = $this->collectParticipants();
        if ($all_possible_participants) {
            // user filter
            $user_query = new ilUserQuery();
            $user_query->setLimit(50000);
            $user_query->setUserFilter($all_possible_participants);
            $user_query->setTextFilter((string) $this->current_filter['login']);
            $res = $user_query->query();
            $all_participants = $res['set'];
        }

        $part = [];
        foreach ($all_participants as $counter => $participant) {
            $usr_data = $this->getParticipants()->getEventParticipants()->getUser((int) $participant['usr_id']);

            $tmp_data = [];
            $tmp_data['id'] = $participant['usr_id'];
            
            $tmp_data['name'] = $participant['lastname'];
            $tmp_data['lastname'] = $participant['lastname'];
            $tmp_data['firstname'] = $participant['firstname'];
            $tmp_data['login'] = $participant['login'];
            $tmp_data['mark'] = $usr_data['mark'] ?? null;
            $tmp_data['comment'] = $usr_data['comment'] ?? null;
            $tmp_data['participated'] = $this->getParticipants()->getEventParticipants()->hasParticipated((int) $participant['usr_id']);
            $tmp_data['registered'] = $this->getParticipants()->getEventParticipants()->isRegistered((int) $participant['usr_id']);
            $tmp_data['excused'] = $this->getParticipants()->getEventParticipants()->isExcused((int) $participant['usr_id']);
            $tmp_data['contact'] = $this->getParticipants()->isContact((int) $participant['usr_id']);

            $notificationShown = false;
            if (true === $this->getRepositoryObject()->isRegistrationNotificationEnabled()) {
                $notificationShown = true;

                $notificationCheckboxEnabled = true;
                if (ilSessionConstants::NOTIFICATION_INHERIT_OPTION === $this->getRepositoryObject()->getRegistrationNotificationOption()) {
                    $notificationCheckboxEnabled = false;
                }
                $tmp_data['notification_checkbox_enabled'] = $notificationCheckboxEnabled;
                $tmp_data['notification_checked'] = $usr_data['notification_enabled'];
            }
            $tmp_data['show_notification'] = $notificationShown;

            $roles = [];
            $local_roles = $this->getParentLocalRoles();
            foreach ($local_roles as $role_id => $role_name) {
                // @todo fix performance
                if ($this->rbac->review()->isAssigned((int) $participant['usr_id'], $role_id)) {
                    $tmp_data['role_ids'][] = $role_id;
                    $roles[] = $role_name;
                }
            }
            $tmp_data['roles'] = implode('<br />', $roles);
            
            if ($this->matchesFilterCriteria($tmp_data)) {
                $part[] = $tmp_data;
            }
        }
        $this->setData($part);
    }
    
    /**
     * @return int[] array of parent course/group participants
     */
    protected function collectParticipants() : array
    {
        $part = ilParticipants::getInstance($this->member_ref_id);
        if (!$part instanceof ilParticipants) {
            return $this->getParticipants()->getParticipants();
        }
        return $part->getParticipants();
    }

    protected function matchesFilterCriteria(array $a_user_info) : bool
    {
        foreach ($this->current_filter as $filter => $filter_value) {
            if (!$filter_value) {
                continue;
            }
            switch ($filter) {
                case 'roles':
                    if (!in_array($filter_value, $a_user_info['role_ids'])) {
                        return false;
                    }
                    break;
                    
                case 'filter_participated':
                    if (!$a_user_info['participated']) {
                        return false;
                    }
                    break;
                    
                case 'filter_registration':
                    if (!$a_user_info['registered']) {
                        return false;
                    }
                    break;
            }
            
            
            $this->logger->info('Filter: ' . $filter . ' -> ' . $filter_value);
        }
        return true;
    }

    protected function fillRow(array $a_set) : void
    {
        $this->tpl->setVariable('VAL_POSTNAME', 'participants');

        if ($this->isRegistrationEnabled()) {
            $this->tpl->setCurrentBlock('registered_col');
            $this->tpl->setVariable('VAL_ID', $a_set['id']);
            $this->tpl->setVariable('REG_CHECKED', $a_set['registered'] ? 'checked="checked"' : '');
            $this->tpl->parseCurrentBlock();
        }
        
        foreach ($this->getSelectedColumns() as $field) {
            if ($field == 'roles') {
                $this->tpl->setCurrentBlock('custom_fields');
                $this->tpl->setVariable('VAL_CUST', (string) $a_set['roles']);
                $this->tpl->parseCurrentBlock();
            }
        }

        if (true === $a_set['show_notification']) {
            $this->tpl->setCurrentBlock('notification_column');
            $this->tpl->setVariable('VAL_ID', $a_set['id']);
            $this->tpl->setVariable('NOTIFICATION_CHECKED', $a_set['notification_checked'] ? 'checked="checked"' : '');

            $enableCheckbox = $a_set['notification_checkbox_enabled'];

            $this->tpl->setVariable('NOTIFICATION_ENABLED', $enableCheckbox ? '' : 'disabled');
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setVariable('VAL_ID', $a_set['id']);
        $this->tpl->setVariable('LASTNAME', $a_set['lastname']);
        $this->tpl->setVariable('FIRSTNAME', $a_set['firstname']);
        $this->tpl->setVariable('LOGIN', $a_set['login']);
        $this->tpl->setVariable('MARK', $a_set['mark']);
        $this->tpl->setVariable('COMMENT', $a_set['comment']);
        $this->tpl->setVariable('PART_CHECKED', $a_set['participated'] ? 'checked="checked"' : '');
        $this->tpl->setVariable('CONTACT_CHECKED', $a_set['contact'] ? 'checked="checked"' : '');
        $this->tpl->setVariable('PART_CHECKED', $a_set['participated'] ? 'checked="checked"' : '');
        if ($this->isRegistrationEnabled()) {
            $this->tpl->setVariable('EXCUSED_CHECKED', $a_set['excused'] ? 'checked="checked"' : '');
        }
    }

    protected function getParentLocalRoles() : array
    {
        $part = null;
        $type = ilObject::_lookupType($this->member_ref_id, true);
        switch ($type) {
            case 'crs':
            case 'grp':
                $part = ilParticipants::getInstance($this->member_ref_id);
                // no break
            default:
                
        }
        if (!$part instanceof ilParticipants) {
            return [];
        }
        
        $review = $this->rbac->review();
        
        $local_parent_roles = $review->getLocalRoles($this->member_ref_id);
        $this->logger->dump($local_parent_roles);
        
        $local_roles_info = [];
        foreach ($local_parent_roles as $index => $role_id) {
            $local_roles_info[$role_id] = ilObjRole::_getTranslation(
                ilObject::_lookupTitle($role_id)
            );
        }
        return $local_roles_info;
    }
}
