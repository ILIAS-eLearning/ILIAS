<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for role assignment in user administration
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesUser
*/
class ilRoleAssignmentTableGUI extends ilTable2GUI
{
    
    /**
    * Constructor
    */
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $ilAccess = $DIC['ilAccess'];

        $lng->loadLanguageModule('rbac');
        $this->setId("usrroleass");

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setTitle($lng->txt("role_assignment"));
        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");
        $this->setDisableFilterHiding(true);
        $this->addColumn("", "", "1", true);
        $this->addColumn($this->lng->txt("role"), "title");
        $this->addColumn($this->lng->txt("description"), "description");
        $this->addColumn($this->lng->txt("context"), "path");
        $this->setSelectAllCheckbox("role_id[]");
        $this->initFilter();
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.role_assignment_row.html", "Services/User");
        $this->setEnableTitle(true);

        $this->addMultiCommand("assignSave", $lng->txt("change_assignment"));
    }
    
    /**
    * Init filter
    */
    public function initFilter()
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        // roles
        $option[0] = $lng->txt('assigned_roles');
        $option[1] = $lng->txt('all_roles');
        $option[2] = $lng->txt('all_global_roles');
        $option[3] = $lng->txt('all_local_roles');
        $option[4] = $lng->txt('internal_local_roles_only');
        $option[5] = $lng->txt('non_internal_local_roles_only');

        include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
        $si = new ilSelectInputGUI($lng->txt("roles"), "role_filter");
        $si->setOptions($option);
        $this->addFilterItem($si);
        $si->readFromSession();
        $this->filter["role_filter"] = $si->getValue();
    }
    
    /**
    * Fill table row
    */
    protected function fillRow($a_set)
    {
        global $DIC;

        $lng = $DIC['lng'];

        if ($a_set['checkbox']) {
            $this->tpl->setVariable("CHECKBOX", $a_set["checkbox"]);
        }
        $this->tpl->setVariable("ROLE", $a_set["role"]);
        $this->tpl->setVariable("DESCRIPTION", $a_set["description"]);
        $this->tpl->setVariable("CONTEXT", $a_set["context"]);
    }
}
