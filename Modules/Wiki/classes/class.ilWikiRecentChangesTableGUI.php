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
class ilWikiRecentChangesTableGUI extends ilTable2GUI
{
    protected int $wiki_id = 0;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        int $a_wiki_id
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
    
    public function getRecentChanges() : void
    {
        $changes = ilWikiPage::getRecentChanges("wpg", $this->wiki_id);
        $this->setDefaultOrderField("date");
        $this->setDefaultOrderDirection("desc");
        $this->setData($changes);
    }
    
    protected function fillRow(array $a_set) : void
    {
        $ilCtrl = $this->ctrl;

        $title = ilWikiPage::lookupTitle($a_set["id"]);
        $this->tpl->setVariable("TXT_PAGE_TITLE", $title);
        $this->tpl->setVariable(
            "DATE",
            ilDatePresentation::formatDate(new ilDateTime($a_set["date"], IL_CAL_DATETIME))
        );
        $ilCtrl->setParameterByClass("ilwikipagegui", "page", rawurlencode($title));
        $ilCtrl->setParameterByClass("ilwikipagegui", "old_nr", $a_set["nr"] ?? "");
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
