<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * TableGUI for the presentation og roles and role templates
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesAccessControl
 */
class ilRoleTableGUI extends ilTable2GUI
{
    const TYPE_GLOBAL_AU = 1;
    const TYPE_GLOBAL_UD = 2;
    const TYPE_LOCAL_AU = 3;
    const TYPE_LOCAL_UD = 4;
    const TYPE_ROLT_AU = 5;
    const TYPE_ROLT_UD = 6;


    const TYPE_VIEW = 1;
    const TYPE_SEARCH = 2;
    
    private $path_gui = null;

    private $type = self::TYPE_VIEW;
    private $role_title_filter = '';
    private $role_folder_id = 0;

    /**
     * Constructor
     * @param object $a_parent_gui
     * @param string $a_parent_cmd
     */
    public function __construct($a_parent_gui, $a_parent_cmd)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];

        $this->ctrl = $ilCtrl;

        $this->setId('rolf_role_tbl');
        
        parent::__construct($a_parent_gui, $a_parent_cmd);
        $this->lng->loadLanguageModule('rbac');
        $this->lng->loadLanguageModule('search');
    }

    /**
     * Set table type
     * @param int $a_type
     */
    public function setType($a_type)
    {
        $this->type = $a_type;
    }

    /**
     * Set role title filter
     * @param string $a_filter
     */
    public function setRoleTitleFilter($a_filter)
    {
        $this->role_title_filter = $a_filter;
    }

    /**
     * Get role title filter
     * @return string
     */
    public function getRoleTitleFilter()
    {
        return $this->role_title_filter;
    }

    /**
     * Get table type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get path gui
     * @return ilPathGUI $path
     */
    protected function getPathGUI()
    {
        return $this->path_gui;
    }


    
    /**
     * Init table
     */
    public function init()
    {
        $this->addColumn('', 'f', '1px');

        switch ($this->getType()) {
            case self::TYPE_VIEW:
                $this->setShowRowsSelector(true);
                $this->setDefaultOrderField('title');
                $this->setDefaultOrderDirection('asc');
                //$this->setId('rolf_role_tbl');
                $this->addColumn($this->lng->txt('search_title_description'), 'title', '30%');
                $this->addColumn($this->lng->txt('type'), 'rtype', '20%');
                $this->addColumn($this->lng->txt('context'), '', '40%');
                $this->addColumn($this->lng->txt('actions'), '', '10%');
                $this->setTitle($this->lng->txt('objs_role'));
                
                if ($GLOBALS['DIC']['rbacsystem']->checkAccess('delete', $this->getParentObject()->object->getRefId())) {
                    $this->addMultiCommand('confirmDelete', $this->lng->txt('delete'));
                }
                break;
            
            case self::TYPE_SEARCH:
                $this->setShowRowsSelector(true);
                $this->disable('sort');
                //$this->setId('rolf_role_search_tbl');
                $this->addColumn($this->lng->txt('search_title_description'), 'title', '30%');
                $this->addColumn($this->lng->txt('type'), 'rtype', '20%');
                $this->addColumn($this->lng->txt('context'), '', '50%');
                $this->setTitle($this->lng->txt('rbac_role_rights_copy'));
                $this->addMultiCommand('chooseCopyBehaviour', $this->lng->txt('btn_next'));
                $this->addCommandButton('roleSearch', $this->lng->txt('btn_previous'));
                break;
        }


        $this->setRowTemplate('tpl.role_row.html', 'Services/AccessControl');
        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject()));
        $this->setSelectAllCheckbox('roles');

        include_once './Services/Tree/classes/class.ilPathGUI.php';
        $this->path_gui = new ilPathGUI();
        $this->getPathGUI()->enableTextOnly(false);
        $this->getPathGUI()->enableHideLeaf(false);
        

        // Filter initialisation

        if ($this->getType() == self::TYPE_VIEW) {
            $this->initFilter();
        }
    }

    /**
     * Init filter
     */
    public function initFilter()
    {
        $this->setDisableFilterHiding(true);

        switch ($this->getType()) {
            case self::TYPE_VIEW:
                $action[ilRbacReview::FILTER_ALL] = $this->lng->txt('all_roles');
                $action[ilRbacReview::FILTER_ALL_GLOBAL] = $this->lng->txt('all_global_roles');
                $action[ilRbacReview::FILTER_ALL_LOCAL] = $this->lng->txt('all_local_roles');
                $action[ilRbacReview::FILTER_INTERNAL] = $this->lng->txt('internal_local_roles_only');
                $action[ilRbacReview::FILTER_NOT_INTERNAL] = $this->lng->txt('non_internal_local_roles_only');
                $action[ilRbacReview::FILTER_TEMPLATES] = $this->lng->txt('role_templates_only');
                break;

            case self::TYPE_SEARCH:
                $action[ilRbacReview::FILTER_ALL] = $this->lng->txt('all_roles');
                $action[ilRbacReview::FILTER_ALL_GLOBAL] = $this->lng->txt('all_global_roles');
                $action[ilRbacReview::FILTER_ALL_LOCAL] = $this->lng->txt('all_local_roles');
                $action[ilRbacReview::FILTER_INTERNAL] = $this->lng->txt('internal_local_roles_only');
                $action[ilRbacReview::FILTER_NOT_INTERNAL] = $this->lng->txt('non_internal_local_roles_only');
                break;
        }

        include_once './Services/Form/classes/class.ilSelectInputGUI.php';
        $roles = new ilSelectInputGUI($this->lng->txt('rbac_role_selection'), 'role_type');

        $roles->setOptions($action);

        $this->addFilterItem($roles);

        $roles->readFromSession();
        if (!$roles->getValue()) {
            $roles->setValue(ilRbacReview::FILTER_ALL_GLOBAL);
        }

        // title filter
        include_once './Services/Form/classes/class.ilTextInputGUI.php';
        $title = new ilTextInputGUI($this->lng->txt('title'), 'role_title');
        $title->setSize(16);
        $title->setMaxLength(64);

        $this->addFilterItem($title);
        $title->readFromSession();

        $this->filter['role_type'] = $roles->getValue();
        $this->filter['role_title'] = $title->getValue();
    }

    /**
     *
     * @param array $set
     */
    public function fillRow($set)
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        $tree = $DIC['tree'];

        if ($set['type'] == 'role') {
            if ($set['parent'] != ROLE_FOLDER_ID) {
                $this->ctrl->setParameterByClass(
                    "ilobjrolegui",
                    "rolf_ref_id",
                    $set['parent']
                );
            }

            $this->ctrl->setParameterByClass("ilobjrolegui", "obj_id", $set["obj_id"]);
            $link = $this->ctrl->getLinkTargetByClass("ilobjrolegui", "perm");
            $this->ctrl->setParameterByClass("ilobjrolegui", "rolf_ref_id", "");
        } else {
            $this->ctrl->setParameterByClass("ilobjroletemplategui", "obj_id", $set["obj_id"]);
            $link = $this->ctrl->getLinkTargetByClass("ilobjroletemplategui", "perm");
        }

        switch ($set['rtype']) {
            case self::TYPE_GLOBAL_AU:
                $this->tpl->setVariable('ROLE_TYPE', $this->lng->txt('rbac_auto_global'));
                break;
            case self::TYPE_GLOBAL_UD:
                $this->tpl->setVariable('ROLE_TYPE', $this->lng->txt('rbac_ud_global'));
                break;
            case self::TYPE_LOCAL_AU:
                $this->tpl->setVariable('ROLE_TYPE', $this->lng->txt('rbac_auto_local'));
                break;
            case self::TYPE_LOCAL_UD:
                $this->tpl->setVariable('ROLE_TYPE', $this->lng->txt('rbac_ud_local'));
                break;
            case self::TYPE_ROLT_AU:
                $this->tpl->setVariable('ROLE_TYPE', $this->lng->txt('rbac_auto_rolt'));
                break;
            case self::TYPE_ROLT_UD:
                $this->tpl->setVariable('ROLE_TYPE', $this->lng->txt('rbac_ud_rolt'));
                break;
        }



        if (
            ($set['obj_id'] != ANONYMOUS_ROLE_ID and
            $set['obj_id'] != SYSTEM_ROLE_ID and
            substr($set['title_orig'], 0, 3) != 'il_') or
            $this->getType() == self::TYPE_SEARCH) {
            $this->tpl->setVariable('VAL_ID', $set['obj_id']);
        }
        $this->tpl->setVariable('VAL_TITLE_LINKED', $set['title']);
        $this->tpl->setVariable('VAL_LINK', $link);
        if (strlen($set['description'])) {
            $this->tpl->setVariable('VAL_DESC', $set['description']);
        }

        /**
        if((substr($set['title_orig'],0,3) == 'il_') and ($set['type'] == 'rolt'))
        {
            $this->tpl->setVariable('VAL_PRE',$this->lng->txt('predefined_template'));
        }
        */

        $ref = $set['parent'];
        if ($ref == ROLE_FOLDER_ID) {
            $this->tpl->setVariable('CONTEXT', $this->lng->txt('rbac_context_global'));
        } else {
            $this->tpl->setVariable(
                'CONTEXT',
                (string) $this->getPathGUI()->getPath(ROOT_FOLDER_ID, $ref)
            );
        }

        if ($this->getType() == self::TYPE_VIEW and $set['obj_id'] != SYSTEM_ROLE_ID) {
            if ($GLOBALS['DIC']['rbacsystem']->checkAccess('write', $this->role_folder_id)) {
                // Copy role
                $this->tpl->setVariable('COPY_TEXT', $this->lng->txt('rbac_role_rights_copy'));
                $this->ctrl->setParameter($this->getParentObject(), "copy_source", $set["obj_id"]);
                $link = $this->ctrl->getLinkTarget($this->getParentObject(), 'roleSearch');
                $this->tpl->setVariable(
                    'COPY_LINK',
                    $link
                );
            }
        }
    }

    /**
     * Parse role list
     * @param array $role_list
     */
    public function parse($role_folder_id)
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        $ilUser = $DIC['ilUser'];
        
        $this->role_folder_id = $role_folder_id;

        include_once './Services/AccessControl/classes/class.ilObjRole.php';
        
        if ($this->getType() == self::TYPE_VIEW) {
            $filter_orig = $filter = $this->getFilterItemByPostVar('role_title')->getValue();
            $type = $this->getFilterItemByPostVar('role_type')->getValue();
        } else {
            $filter_orig = $filter = $this->getRoleTitleFilter();
            $type = ilRbacReview::FILTER_ALL;
        }

        
        // the translation must be filtered
        if ($type == ilRbacReview::FILTER_INTERNAL or $type == ilRbacReview::FILTER_ALL) {
            // roles like il_crs_... are filtered manually
            $filter = '';
        }

        $role_list = $rbacreview->getRolesByFilter(
            $type,
            0,
            $filter
        );
        
        $counter = 0;
        $rows = array();
        foreach ((array) $role_list as $role) {
            if (
                $role['parent'] and
                    (
                        $GLOBALS['DIC']['tree']->isDeleted($role['parent']) or
                        !$GLOBALS['DIC']['tree']->isInTree($role['parent'])
                    )
            ) {
                continue;
            }
            
            $title = ilObjRole::_getTranslation($role['title']);
            if ($type == ilRbacReview::FILTER_INTERNAL or $type == ilRbacReview::FILTER_ALL) {
                if (strlen($filter_orig)) {
                    if (stristr($title, $filter_orig) == false) {
                        continue;
                    }
                }
            }
            
            
            $rows[$counter]['title_orig'] = $role['title'];
            $rows[$counter]['title'] = $title;
            $rows[$counter]['description'] = $role['description'];
            $rows[$counter]['obj_id'] = $role['obj_id'];
            $rows[$counter]['parent'] = $role['parent'];
            $rows[$counter]['type'] = $role['type'];

            $auto = (substr($role['title'], 0, 3) == 'il_' ? true : false);


            // Role templates
            if ($role['type'] == 'rolt') {
                $rows[$counter]['rtype'] = $auto ? self::TYPE_ROLT_AU :	self::TYPE_ROLT_UD;
            } else {
                // Roles
                if ($role['parent'] == ROLE_FOLDER_ID) {
                    if ($role['obj_id'] == ANONYMOUS_ROLE_ID or $role['obj_id'] == SYSTEM_ROLE_ID) {
                        $rows[$counter]['rtype'] = self::TYPE_GLOBAL_AU;
                    } else {
                        $rows[$counter]['rtype'] = self::TYPE_GLOBAL_UD;
                    }
                } else {
                    $rows[$counter]['rtype'] = $auto ? self::TYPE_LOCAL_AU : self::TYPE_LOCAL_UD;
                }
            }

            ++$counter;
        }
        $this->setMaxCount(count($rows));
        $this->setData($rows);
    }
}
