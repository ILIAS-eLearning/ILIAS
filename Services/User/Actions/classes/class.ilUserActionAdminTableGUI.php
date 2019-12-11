<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for user action administration
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesUser
 */
class ilUserActionAdminTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilLanguage
     */
    protected $lng;


    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_data, $a_write_permission = false)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setData($a_data);
        $this->setTitle($this->lng->txt(""));

        $this->addColumn($this->lng->txt("user_action"));
        $this->addColumn($this->lng->txt("active"), "", "1");
        
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.user_action_admin_row.html", "Services/User/Actions");

        //$this->addMultiCommand("", $this->lng->txt(""));
        if ($a_write_permission) {
            $this->addCommandButton("save", $this->lng->txt("save"));
        }
    }
    
    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        if ($a_set["active"]) {
            $this->tpl->touchBlock("checked");
        }
        $this->tpl->setVariable("VAL", $a_set["action_type_name"]);
        $this->tpl->setVariable("ACTION_ID", $a_set["action_comp_id"] . ":" . $a_set["action_type_id"]);
    }
}
