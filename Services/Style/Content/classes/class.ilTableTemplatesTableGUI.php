<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Table templates table
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilTableTemplatesTableGUI extends ilTable2GUI
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
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
    * Constructor
    */
    public function __construct($a_temp_type, $a_parent_obj, $a_parent_cmd, $a_style_obj)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->rbacsystem = $DIC->rbac()->system();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        ilAccordionGUI::addCss();

        $this->setTitle($lng->txt("sty_templates"));
        $this->style_obj = $a_style_obj;
        $this->temp_type = $a_temp_type;
        
        $this->addColumn("", "", "1");	// checkbox
        $this->addColumn($this->lng->txt("sty_template_name"), "");
        $this->addColumn($this->lng->txt("sty_preview"), "");
        $this->addColumn($this->lng->txt("sty_commands"), "", "1");
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.style_template_row.html", "Services/Style/Content");
        $this->getItems();

        // action commands
        if ($this->parent_obj->checkWrite()) {
            $this->addMultiCommand("deleteTemplateConfirmation", $lng->txt("delete"));
        }

        $this->setEnableTitle(true);
    }

    /**
    * Get items of current folder
    */
    public function getItems()
    {
        $this->setData($this->style_obj->getTemplates($this->temp_type));
    }
    
    /**
    * Fill table row
    */
    protected function fillRow($a_set)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->tpl->setVariable(
            "T_PREVIEW",
            $this->style_obj->lookupTemplatePreview($a_set["id"])
        );
        $this->tpl->setVariable("TID", $a_set["id"]);
        $this->tpl->setVariable("TEMPLATE_NAME", $a_set["name"]);
        $ilCtrl->setParameter($this->parent_obj, "t_id", $a_set["id"]);
        
        if ($this->parent_obj->checkWrite()) {
            $this->tpl->setVariable(
                "LINK_EDIT_TEMPLATE",
                $ilCtrl->getLinkTarget($this->parent_obj, "editTemplate")
            );
            $this->tpl->setVariable("TXT_EDIT", $lng->txt("edit"));
        }
    }
}
