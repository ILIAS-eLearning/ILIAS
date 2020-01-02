<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for help modules
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesHelp
 */
class ilHelpModuleTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilSetting
     */
    protected $settings;

    /**
     * @var bool
     */
    protected $has_write_permission;

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_has_write_permission = false)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->settings = $DIC->settings();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $ilAccess = $DIC->access();
        $lng = $DIC->language();
        $this->has_write_permission = $a_has_write_permission;
        
        $this->setId("help_mods");
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->getHelpModules();
        $this->setTitle($lng->txt("help_modules"));
        
        $this->addColumn("", "", "1");
        $this->addColumn($this->lng->txt("title"));
        $this->addColumn($this->lng->txt("help_imported_on"));
        $this->addColumn($this->lng->txt("actions"));
        
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.help_module_row.html", "Services/Help");

        if ($this->has_write_permission) {
            $this->addMultiCommand("confirmHelpModulesDeletion", $lng->txt("delete"));
        }
        //$this->addCommandButton("", $lng->txt(""));
    }
    
    /**
     * Get help modules
     */
    public function getHelpModules()
    {
        $this->setData($this->parent_obj->object->getHelpModules());
    }
    
    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        $lng = $this->lng;
        $ilSetting = $this->settings;
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameter($this->parent_obj, "hm_id", $a_set["id"]);
        if ($this->has_write_permission) {
            if ($a_set["id"] == $ilSetting->get("help_module")) {
                $this->tpl->setCurrentBlock("cmd");
                $this->tpl->setVariable(
                    "HREF_CMD",
                    $ilCtrl->getLinkTarget($this->parent_obj, "deactivateModule")
                );
                $this->tpl->setVariable("TXT_CMD", $lng->txt("deactivate"));
                $this->tpl->parseCurrentBlock();
            } else {
                $this->tpl->setCurrentBlock("cmd");
                $this->tpl->setVariable(
                    "HREF_CMD",
                    $ilCtrl->getLinkTarget($this->parent_obj, "activateModule")
                );
                $this->tpl->setVariable("TXT_CMD", $lng->txt("activate"));
                $this->tpl->parseCurrentBlock();
            }
        }
        $ilCtrl->setParameter($this->parent_obj, "hm_id", "");
        $this->tpl->setVariable("TITLE", $a_set["title"]);
        $this->tpl->setVariable(
            "CREATION_DATE",
            ilDatePresentation::formatDate(new ilDateTime($a_set["create_date"], IL_CAL_DATETIME))
        );
        $this->tpl->setVariable("ID", $a_set["id"]);
    }
}
