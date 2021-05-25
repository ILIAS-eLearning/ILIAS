<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Show all users for a tag
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilUserForTagTableGUI extends ilTable2GUI
{
    /**
     * @var ilAccessHandler
     */
    protected $access;

    
    /**
    * Constructor
    */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_tag)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setData(ilTagging::getUsersForTag($a_tag));
        $this->setTitle($lng->txt("tagging_users_using_tag"));
        
        $this->addColumn($this->lng->txt("user"), "");
        
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.user_for_tag_row.html", "Services/Tagging");
        $this->setEnableTitle(true);
    }
    
    /**
    * Fill table row
    */
    protected function fillRow($a_set)
    {
        $lng = $this->lng;

        $this->tpl->setVariable(
            "USER",
            ilUserUtil::getNamePresentation($a_set["id"], true, false, "", true)
        );
    }
}
