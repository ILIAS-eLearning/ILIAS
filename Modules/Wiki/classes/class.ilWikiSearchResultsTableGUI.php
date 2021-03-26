<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * TableGUI class for recent changes in wiki
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilWikiSearchResultsTableGUI extends ilTable2GUI
{
    public function __construct(
        $a_parent_obj,
        $a_parent_cmd = "",
        $a_wiki_id,
        $a_results,
        $a_term
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->wiki_id = $a_wiki_id;
        
        $this->addColumn($lng->txt("wiki_page"), "", "100%");
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate(
            "tpl.search_result.html",
            "Modules/Wiki"
        );
            
        $this->setData($a_results);
        $this->setLimit(0);
        
        $this->setTitle($lng->txt("wiki_search_results") . ' "' . str_replace(array('"'), "", $a_term) . '"');
    }
    
    /**
    * Standard Version of Fill Row. Most likely to
    * be overwritten by derived class.
    */
    protected function fillRow($a_set)
    {
        $ilCtrl = $this->ctrl;

        $title = ilWikiPage::lookupTitle($a_set["page_id"]);
        $this->tpl->setVariable("TXT_PAGE_TITLE", $title);
        $ilCtrl->setParameterByClass(
            "ilwikipagegui",
            "page",
            ilWikiUtil::makeUrlTitle($title)
        );
        $this->tpl->setVariable(
            "HREF_PAGE",
            $ilCtrl->getLinkTargetByClass("ilwikipagegui", "preview")
        );
    }
}
