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
 * TableGUI class for content popup
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCIIMPopupTableGUI extends ilTable2GUI
{
    protected ilPCInteractiveImage $content_obj;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        ilPCInteractiveImage $a_content_obj
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->addColumn("", "", "1");
        $this->addColumn($lng->txt("title"), "", "100%");
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate(
            "tpl.iim_popup_content_row.html",
            "Services/COPage"
        );
            
        $this->content_obj = $a_content_obj;
        $this->setData($this->content_obj->getPopups());
        $this->setLimit(0);
        
        $this->addMultiCommand("confirmPopupDeletion", $lng->txt("delete"));
        $this->addCommandButton("savePopups", $lng->txt("cont_save_all_titles"));
        
        $this->setTitle($lng->txt("cont_content_popups"));
    }
    
    protected function fillRow(array $a_set) : void
    {
        $this->tpl->setVariable("TID", $a_set["hier_id"] . ":" . $a_set["pc_id"]);
        $this->tpl->setVariable("TITLE", ilLegacyFormElementsUtil::prepareFormOutput($a_set["title"]));
    }
}
