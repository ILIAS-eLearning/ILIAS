<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * TableGUI class for style editor (image list)
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilStyleMediaQueryTableGUI extends ilTable2GUI
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
    public function __construct($a_parent_obj, $a_parent_cmd, $a_style_obj)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->rbacsystem = $DIC->rbac()->system();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $ilAccess = $DIC->access();
        $lng = $DIC->language();
        $rbacsystem = $DIC->rbac()->system();
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->setTitle($lng->txt("sty_media_queries"));
        $this->setDescription($lng->txt("sty_media_query_info"));
        $this->style_obj = $a_style_obj;
        
        $this->addColumn("", "", "1");	// checkbox
        $this->addColumn($this->lng->txt("sty_order"));
        $this->addColumn($this->lng->txt("sty_query"), "");
        $this->addColumn($this->lng->txt("actions"), "");
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.style_media_query_row.html", "Services/Style/Content");
        //$this->setSelectAllCheckbox("file");
        $this->getItems();

        // action commands
        if ($this->parent_obj->checkWrite()) {
            $this->addCommandButton("saveMediaQueryOrder", $lng->txt("sty_save_order"));
            $this->addMultiCommand("deleteMediaQueryConfirmation", $lng->txt("delete"));
        }
        
        $this->setEnableTitle(true);
    }

    /**
    * Get items of current folder
    */
    public function getItems()
    {
        $this->setData($this->style_obj->getMediaQueries());
    }
    
    /**
    * Fill table row
    */
    protected function fillRow($a_set)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;
        $rbacsystem = $this->rbacsystem;
        
        $this->tpl->setVariable("MQUERY", $a_set["mquery"]);
        $this->tpl->setVariable("MQID", $a_set["id"]);
        $this->tpl->setVariable("ORDER_NR", $a_set["order_nr"]);

        if ($this->parent_obj->checkWrite()) {
            $this->tpl->setVariable("TXT_EDIT", $lng->txt("edit"));
            $ilCtrl->setParameter($this->parent_obj, "mq_id", $a_set["id"]);
            $this->tpl->setVariable(
                "LINK_EDIT_MQUERY",
                $ilCtrl->getLinkTarget($this->parent_obj, "editMediaQuery")
            );
        }
    }
}
