<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Show all users for a tag
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUserForTagTableGUI extends ilTable2GUI
{
    protected ilAccessHandler $access;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        string $a_tag
    ) {
        global $DIC;

        $this->access = $DIC->access();

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setData(ilTagging::getUsersForTag($a_tag));
        $this->setTitle($this->lng->txt("tagging_users_using_tag"));
        
        $this->addColumn($this->lng->txt("user"), "");
        
        $this->setEnableHeader(true);
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.user_for_tag_row.html", "Services/Tagging");
        $this->setEnableTitle(true);
    }
    
    /**
     * @inheritDoc
     */
    protected function fillRow(array $a_set) : void
    {
        $this->tpl->setVariable(
            "USER",
            ilUserUtil::getNamePresentation($a_set["id"], true, false, "", true)
        );
    }
}
