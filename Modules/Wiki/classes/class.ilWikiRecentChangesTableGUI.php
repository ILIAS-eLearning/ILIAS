<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * TableGUI class for recent changes in wiki
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilWikiRecentChangesTableGUI extends ilTable2GUI
{
    public function __construct(
        $a_parent_obj,
        $a_parent_cmd = "",
        $a_wiki_id
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->wiki_id = $a_wiki_id;
        
        $this->addColumn($lng->txt("wiki_last_changed"), "", "33%");
        $this->addColumn($lng->txt("wiki_page"), "", "33%");
        $this->addColumn($lng->txt("wiki_last_changed_by"), "", "67%");
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate(
            "tpl.table_row_recent_changes.html",
            "Modules/Wiki"
        );
        $this->getRecentChanges();
        
        $this->setShowRowsSelector(true);
        
        $this->setTitle($lng->txt("wiki_recent_changes"));
    }
    
    /**
    * Get pages for list.
    */
    public function getRecentChanges()
    {
        $changes = ilWikiPage::getRecentChanges("wpg", $this->wiki_id);
        $this->setDefaultOrderField("date");
        $this->setDefaultOrderDirection("desc");
        $this->setData($changes);
    }
    
    /**
    * Standard Version of Fill Row. Most likely to
    * be overwritten by derived class.
    */
    protected function fillRow($a_set)
    {
        $ilCtrl = $this->ctrl;

        $title = ilWikiPage::lookupTitle($a_set["id"]);
        $this->tpl->setVariable("TXT_PAGE_TITLE", $title);
        $this->tpl->setVariable(
            "DATE",
            ilDatePresentation::formatDate(new ilDateTime($a_set["date"], IL_CAL_DATETIME))
        );
        $ilCtrl->setParameterByClass("ilwikipagegui", "page", rawurlencode($title));
        $ilCtrl->setParameterByClass("ilwikipagegui", "old_nr", $a_set["nr"]);
        $this->tpl->setVariable(
            "HREF_PAGE",
            $ilCtrl->getLinkTargetByClass("ilwikipagegui", "preview")
        );

        // user name
        $this->tpl->setVariable(
            "TXT_USER",
            ilUserUtil::getNamePresentation(
                $a_set["user"],
                true,
                true,
                $ilCtrl->getLinkTarget($this->getParentObject(), $this->getParentCmd())
            )
        );
    }
}
