<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for role assignments
 *
 * @extends ilTable2GUI
 * @author Fabian Wolf <wolf@leifos.com>
 * @ingroup ModulesCategory
 */
class ilCategoryAssignRoleTableGUI extends ilTable2GUI
{
    /**
     * Constructor
     *
     * @param ilObjCategoryGUI $a_parent_obj
     * @param string $a_parent_cmd
     */
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        $this->setId("ilcatluaar");
        
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->addColumn("", "", "", true, "4%");
        $this->addColumn($lng->txt("title"), "title", "35%");
        $this->addColumn($lng->txt("description"), "desc", "45%");
        $this->addColumn($lng->txt("type"), "type", "16%");
        
        $this->addMultiCommand('assignSave', $lng->txt("change_assignment"));
    
        $ilCtrl->saveParameter($a_parent_obj, 'obj_id');
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setRowTemplate("tpl.cat_role_assignment.html", "Modules/Category");
        $this->setDefaultOrderDirection("asc");
        $this->setShowRowsSelector(false);
        $this->setLimit(999999);
    }

    /**
     * @param array $a_set
     */
    protected function fillRow($a_set)
    {
        $this->tpl->setVariable("INPUT_CHCKBX", $a_set["checkbox"]);
        $this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
        $this->tpl->setVariable("TXT_DESCRIPTION", $a_set["desc"]);
        $this->tpl->setVariable("TXT_TYPE", $a_set["type"]);
    }
}
