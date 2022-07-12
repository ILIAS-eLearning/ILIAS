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
 * TableGUI class for tabs
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCTabsTableGUI extends ilTable2GUI
{
    protected ilPCTabs $tabs;
    protected int $pos = 0;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        ilPCTabs $a_tabs
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->addColumn("", "", "1");
        $this->addColumn($lng->txt("cont_position"), "", "1");
        $this->addColumn($lng->txt("title"), "", "100%");
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate(
            "tpl.tabs_row.html",
            "Services/COPage"
        );
            
        $this->tabs = $a_tabs;
        $this->setData($this->tabs->getCaptions());
        $this->setLimit(0);
        
        $this->addMultiCommand("confirmTabsDeletion", $lng->txt("delete"));
        $this->addCommandButton("saveTabs", $lng->txt("save"));
        
        $this->setTitle($lng->txt("cont_tabs"));
    }
    
    protected function fillRow(array $a_set) : void
    {
        $this->pos += 10;
        $this->tpl->setVariable("POS", ilLegacyFormElementsUtil::prepareFormOutput($this->pos));
        $this->tpl->setVariable("TID", $a_set["hier_id"] . ":" . $a_set["pc_id"]);
        $this->tpl->setVariable("VAL_CAPTION", ilLegacyFormElementsUtil::prepareFormOutput($a_set["caption"]));
    }
}
