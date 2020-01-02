<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * Description of class class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilSessionParticipantsTableGUI extends ilTable2GUI
{
    protected static $all_columns = null;
    
    /**
     * @var ilObject
     */
    private $rep_object = null;
    
    
    /**
     * Ref id of parent object
     * @var type
     */
    private $parent_ref_id = 0;
    
    /**
     * Ref id of parent member object course/group
     * @var type
     */
    private $member_ref_id = 0;
    

    
    /**
     * @var ilLogger
     */
    private $logger = null;
    
    
    /**
     * @var ilSessionPartcipants
     */
    private $participants = null;
    
    
    /**
     * @param object $a_parent_gui
     * @param ilObjSession $a_parent_obj
     * @param string $a_parent_cmd
     * @throws \InvalidArgumentException
     */
    public function __construct($a_parent_gui, ilObjSession $a_parent_obj, $a_parent_cmd)
    {
        $this->logger = $GLOBALS['DIC']->logger()->sess();

        $this->rep_object = $a_parent_obj;

        include_once './Services/Membership/classes/class.ilParticipants.php';
        $this->participants = ilParticipants::getInstance($this->getRepositoryObject()->getRefId());
        
        $this->setId('session_part_' . $this->getRepositoryObject()->getId());
        parent::__construct($a_parent_gui, $a_parent_cmd);
        
        $this->parent_ref_id = $GLOBALS['DIC']->repositoryTree()->getParentId(
            $this->getRepositoryObject()->getRefId()
        );
        
        $tree = $GLOBALS['DIC']->repositoryTree();
        if ($member_ref = $tree->checkForParentType($this->parent_ref_id, 'grp')) {
            $this->member_ref_id = $member_ref;
        } elseif ($member_ref = $tree->checkForParentType($this->parent_ref_id, 'crs')) {
            $this->member_ref_id = $member_ref;
        } else {
            throw new \InvalidArgumentException("Error in tree structure. Session has no parent course/group ref_id: " . $this->getRepositoryObject()->getRefId());
        }
    }
    
    
    /**
     * @return ilObjSession
     */
    protected function getRepositoryObject()
    {
        return $this->rep_object;
    }
    
    /**
     * Check if registration is enabled
     * @return bool
     */
    protected function isRegistrationEnabled()
    {
        return $this->getRepositoryObject()->enabledRegistration();
    }
    
    
    /**
     * Get participants
     * @return ilSessionParticipants
     */
    protected function getParticipants()
    {
        return $this->participants;
    }


    /**
     * Init table
     */
    public function init()
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
        $this->addColumn($this->lng->txt('sess_contact'), 'contact');
        $this->addColumn($this->lng->txt('trac_mark'), 'mark');
        $this->addColumn($this->lng->txt('trac_comment'), 'comment');
        
        
        $this->addMultiCommand('sendMailToSelectedUsers', $this->lng->txt('mmbr_btn_mail_selected_users'));
        $this->lng->loadLanguageModule('user');
        $this->addMultiCommand('addToClipboard', $this->lng->txt('clipboard_add_btn'));
        
        
        $this->addCommandButton('updateMembers', $this->lng->txt('save'));
    }
    
    public function initFilter()
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

            $options = array();
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
            $this->current_filter['filter_registration'] = (bool) $reg->getChecked();
        }
        $participated = $this->addFilterItemByMetaType(
            'filter_participated',
            ilTable2GUI::FILTER_CHECKBOX,
            false,
            $this->lng->txt('sess_part_filter_participated')
        );
        $this->current_filter['filter_participated'] = (bool) $participated->getChecked();
    }
    
    /**
     * Get selectable columns
     * @return
     */
    public function getSelectableColumns()
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        
        
        self::$all_columns['roles'] = array(
            'txt' => $this->lng->txt('objs_role'),
            'default' => true
        );
        
        return self::$all_columns;
    }
    
    
    /**
     * parse table
     *
     * @access public
     * @return
     */
    public function parse()
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
        
        foreach ($all_participants as $counter => $participant) {
            $usr_data = $this->getParticipants()->getEventParticipants()->getUser($participant['usr_id']);
            
            $tmp_data = [];
            $tmp_data['id'] = $participant['usr_id'];
            
            $tmp_data['name'] = $participant['lastname'];
            $tmp_data['lastname'] = $participant['lastname'];
            $tmp_data['firstname'] = $participant['firstname'];
            $tmp_data['login'] = $participant['login'];
            $tmp_data['mark'] = $usr_data['mark'];
            $tmp_data['comment'] = $usr_data['comment'];
            $tmp_data['participated'] = $this->getParticipants()->getEventParticipants()->hasParticipated($participant['usr_id']);
            $tmp_data['registered'] = $this->getParticipants()->getEventParticipants()->isRegistered($participant['usr_id']);
            $tmp_data['contact'] = $this->getParticipants()->isContact($participant['usr_id']);
            
            $roles = array();
            $local_roles = $this->getParentLocalRoles();
            foreach ($local_roles as $role_id => $role_name) {
                // @todo fix performance
                if ($GLOBALS['DIC']['rbacreview']->isAssigned($participant['usr_id'], $role_id)) {
                    $tmp_data['role_ids'][] = $role_id;
                    $roles[] = $role_name;
                }
            }
            $tmp_data['roles'] = implode('<br />', $roles);
            
            if ($this->matchesFilterCriteria($tmp_data)) {
                $part[] = $tmp_data;
            }
        }
        $this->setData($part ? $part : array());
    }
    
    /**
     * Collect participants
     * @return int[] array of parent course/group participants
     */
    protected function collectParticipants()
    {
        $part = ilParticipants::getInstance($this->member_ref_id);
        if (!$part instanceof ilParticipants) {
            return $this->getParticipants()->getParticipants();
        }
        return $part->getParticipants();
    }


    /**
     * Check if user is filtered
     * @param type $a_user_info
     */
    protected function matchesFilterCriteria($a_user_info)
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
    
    
    /**
     * fill row
     *
     * @access public
     * @param array data set
     */
    public function fillRow($a_set)
    {
        $this->tpl->setVariable('VAL_POSTNAME', 'participants');

        if ($this->isRegistrationEnabled()) {
            $this->tpl->setCurrentBlock('registered_col');
            $this->tpl->setVariable('VAL_ID', $a_set['id']);
            $this->tpl->setVariable('REG_CHECKED', $a_set['registered'] ? 'checked="checked"' : '');
            $this->tpl->parseCurrentBlock();
        }
        
        foreach ($this->getSelectedColumns() as $field) {
            switch ($field) {
                case 'roles':
                    $this->tpl->setCurrentBlock('custom_fields');
                    $this->tpl->setVariable('VAL_CUST', (string) $a_set['roles']);
                    $this->tpl->parseCurrentBlock();
                    break;
        
            }
        }
        $this->tpl->setVariable('VAL_ID', $a_set['id']);
        $this->tpl->setVariable('LASTNAME', $a_set['lastname']);
        $this->tpl->setVariable('FIRSTNAME', $a_set['firstname']);
        $this->tpl->setVariable('LOGIN', $a_set['login']);
        $this->tpl->setVariable('MARK', $a_set['mark']);
        $this->tpl->setVariable('COMMENT', $a_set['comment']);
        $this->tpl->setVariable('PART_CHECKED', $a_set['participated'] ? 'checked="checked"' : '');
        $this->tpl->setVariable('CONTACT_CHECKED', $a_set['contact'] ? 'checked="checked"' : '');
    }
    
    /**
     * Get local roles of parent object
     */
    protected function getParentLocalRoles()
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
        
        $review = $GLOBALS['DIC']->rbac()->review();
        
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
