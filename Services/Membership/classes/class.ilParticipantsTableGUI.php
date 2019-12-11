<?php

include_once('./Services/Table/classes/class.ilTable2GUI.php');

/*
 * Abstract base class for course, group participants table guis
 * @author Stefan Meyer <smeyer.ilias@gmx.de
 */
abstract class ilParticipantTableGUI extends ilTable2GUI
{
    protected static $export_allowed = false;
    protected static $confirmation_required = true;
    protected static $accepted_ids = null;
    protected static $all_columns = null;
    protected static $has_odf_definitions = false;
    
    protected $participants = null;
    
    protected $current_filter = array();
    
    /**
     * @var ilObject
     */
    protected $rep_object;

    
    /**
     * Init table filter
     */
    public function initFilter()
    {
        $this->setDefaultFilterVisiblity(true);
        
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
                $this->lng->txt('objs_role')
            );

            $options = array();
            $options[0] = $this->lng->txt('all_roles');
            $role->setOptions($options + $this->getParentObject()->getLocalRoles());
            $this->current_filter['roles'] = $role->getValue();
        }
        
        if ($this->isColumnSelected('org_units')) {
            include_once './Modules/OrgUnit/classes/class.ilObjOrgUnit.php';
            $root = ilObjOrgUnit::getRootOrgRefId();
            include_once './Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php';
            $tree = ilObjOrgUnitTree::_getInstance();
            $nodes = $tree->getAllChildren($root);
            
            include_once './Modules/OrgUnit/classes/PathStorage/class.ilOrgUnitPathStorage.php';
            $paths = ilOrgUnitPathStorage::getTextRepresentationOfOrgUnits();
            
            $options[0] = $this->lng->txt('select_one');
            foreach ($paths as $org_ref_id => $path) {
                $options[$org_ref_id] = $path;
            }
            
            $org = $this->addFilterItemByMetaType(
                'org_units',
                ilTable2GUI::FILTER_SELECT,
                false,
                $this->lng->txt('org_units')
            );
            $org->setOptions($options);
            $this->current_filter['org_units'] = $org->getValue();
        }
    }
    

    /**
     * Get selectable columns
     * @return
     */
    public function getSelectableColumns()
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        
        $GLOBALS['DIC']['lng']->loadLanguageModule('ps');
        if (self::$all_columns) {
            # return self::$all_columns;
        }

        include_once './Services/PrivacySecurity/classes/class.ilExportFieldsInfo.php';
        $ef = ilExportFieldsInfo::_getInstanceByType($this->getRepositoryObject()->getType());
        self::$all_columns = $ef->getSelectableFieldsInfo($this->getRepositoryObject()->getId());
        
        if ($ilSetting->get('user_portfolios')) {
            self::$all_columns['prtf'] = array(
                'txt' => $this->lng->txt('obj_prtf'),
                'default' => false
            );
        }
        
        $login = array_splice(self::$all_columns, 0, 1);
        self::$all_columns = array_merge(
            array(
                'roles' =>
                    array(
                        'txt' => $this->lng->txt('objs_role'),
                        'default' => true
                )
            ),
            self::$all_columns
        );
        self::$all_columns = array_merge($login, self::$all_columns);
        return self::$all_columns;
    }
    
    /**
     * @return \ilObject
     */
    protected function getRepositoryObject()
    {
        return $this->rep_object;
    }
    
    
    /**
     * Get participants
     * @return \ilParticipants
     */
    protected function getParticipants()
    {
        return $this->participants;
    }

    /**
     * Check acceptance
     * @param object $a_usr_id
     * @return
     */
    public function checkAcceptance($a_usr_id)
    {
        if (!self::$confirmation_required) {
            return true;
        }
        if (!self::$export_allowed) {
            return false;
        }
        return in_array($a_usr_id, self::$accepted_ids);
    }

    /**
     * Init acceptance
     * @return
     */
    protected function initSettings()
    {
        if (self::$accepted_ids !== null) {
            return true;
        }
        self::$export_allowed = ilPrivacySettings::_getInstance()->checkExportAccess($this->getRepositoryObject()->getRefId());
        
        self::$confirmation_required = ($this->getRepositoryObject()->getType() == 'crs')
            ? ilPrivacySettings::_getInstance()->courseConfirmationRequired()
            : ilPrivacySettings::_getInstance()->groupConfirmationRequired();
        
        include_once 'Services/Membership/classes/class.ilMemberAgreement.php';
        self::$accepted_ids = ilMemberAgreement::lookupAcceptedAgreements($this->getRepositoryObject()->getId());

        include_once('Modules/Course/classes/Export/class.ilCourseDefinedFieldDefinition.php');
        self::$has_odf_definitions = ilCourseDefinedFieldDefinition::_hasFields($this->getRepositoryObject()->getId());
    }

    /**
     * show edit links
     * @param type $a_set
     * @return boolean
     */
    protected function showActionLinks($a_set)
    {
        $loc_enabled = (
            $this->getRepositoryObject()->getType() == 'crs' and
                $this->getRepositoryObject()->getViewMode() == IL_CRS_VIEW_OBJECTIVE
        );
        
        if (!self::$has_odf_definitions and !$loc_enabled) {
            $this->ctrl->setParameter($this->parent_obj, 'member_id', $a_set['usr_id']);
            $this->tpl->setCurrentBlock('link');
            $this->tpl->setVariable('LINK_NAME', $this->ctrl->getLinkTarget($this->parent_obj, 'editMember'));
            $this->tpl->setVariable('LINK_TXT', $this->lng->txt('edit'));
            $this->tpl->parseCurrentBlock();
            return true;
        }
        
        // show action menu
        include_once './Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';
        $list = new ilAdvancedSelectionListGUI();
        $list->setSelectionHeaderClass('small');
        $list->setItemLinkClass('small');
        $list->setId('actl_' . $a_set['usr_id'] . '_' . $this->getId());
        $list->setListTitle($this->lng->txt('actions'));

        $this->ctrl->setParameter($this->parent_obj, 'member_id', $a_set['usr_id']);
        $list->addItem($this->lng->txt('edit'), '', $this->ctrl->getLinkTarget($this->getParentObject(), 'editMember'));

        if (self::$has_odf_definitions) {
            $this->ctrl->setParameterByClass('ilobjectcustomuserfieldsgui', 'member_id', $a_set['usr_id']);
            $trans = $this->lng->txt($this->getRepositoryObject()->getType() . '_cdf_edit_member');
            $list->addItem($trans, '', $this->ctrl->getLinkTargetByClass('ilobjectcustomuserfieldsgui', 'editMember'));
        }
        
        if ($loc_enabled) {
            $this->ctrl->setParameterByClass('illomembertestresultgui', 'uid', $a_set['usr_id']);
            $list->addItem(
                $this->lng->txt('crs_loc_mem_show_res'),
                '',
                $this->ctrl->getLinkTargetByClass('illomembertestresultgui', '')
            );
        }
        
        $this->tpl->setVariable('ACTION_USER', $list->getHTML());
    }
}
