<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * TableGUI class for recent changes in wiki
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilWikiSearchResultsTableGUI extends ilTable2GUI
{
    protected int $wiki_id = 0;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        int $a_wiki_id,
        array $a_results,
        string $a_term
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
    
    protected function fillRow(array $a_set) : void
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
