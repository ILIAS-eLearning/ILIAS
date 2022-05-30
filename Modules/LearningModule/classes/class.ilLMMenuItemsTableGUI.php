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
 * TableGUI class for lm menu items
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLMMenuItemsTableGUI extends ilTable2GUI
{
    protected ilLMMenuEditor $lmme;
    protected ilAccessHandler $access;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        ilLMMenuEditor $a_lmme
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->lmme = $a_lmme;
        $entries = $this->lmme->getMenuEntries();

        $this->setData($entries);
        $this->setTitle($lng->txt("cont_custom_menu_entries"));
        $this->disable("footer");
        
        //		$this->addColumn("", "", "1px", true);
        $this->addColumn($this->lng->txt("link"));
        $this->addColumn($this->lng->txt("active"));
        $this->addColumn($this->lng->txt("actions"));
        
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.lm_menu_entry_row.html", "Modules/LearningModule");

        //		$this->addMultiCommand("deleteMenuEntry", $lng->txt("delete"));
        $this->addCommandButton("saveMenuProperties", $lng->txt("save"));
    }
    
    protected function fillRow(array $a_set) : void
    {
        $ilCtrl = $this->ctrl;
        
        $ilCtrl->setParameter($this->parent_obj, "menu_entry", $a_set["id"]);
        
        $this->tpl->setCurrentBlock("cmd");
        $this->tpl->setVariable("HREF_CMD", $ilCtrl->getLinkTarget($this->parent_obj, "editMenuEntry"));
        $this->tpl->setVariable("CMD", $this->lng->txt("edit"));
        $this->tpl->parseCurrentBlock();

        $this->tpl->setCurrentBlock("cmd");
        $this->tpl->setVariable("HREF_CMD", $ilCtrl->getLinkTarget($this->parent_obj, "deleteMenuEntry"));
        $this->tpl->setVariable("CMD", $this->lng->txt("delete"));
        $this->tpl->parseCurrentBlock();

        $ilCtrl->setParameter($this, "menu_entry", "");

        $this->tpl->setVariable("LINK_ID", $a_set["id"]);
        
        if ($a_set["type"] == "intern") {
            $a_set["link"] = ILIAS_HTTP_PATH . "/goto.php?target=" . $a_set["link"];
        }

        // add http:// prefix if not exist
        if (!strstr($a_set["link"], '://') && !strstr($a_set["link"], 'mailto:')) {
            $a_set["link"] = "https://" . $a_set["link"];
        }

        $this->tpl->setVariable("HREF_LINK", $a_set["link"]);
        $this->tpl->setVariable("LINK", $a_set["title"]);

        if (ilUtil::yn2tf($a_set["active"])) {
            $this->tpl->setVariable("ACTIVE_CHECK", "checked=\"checked\"");
        }
    }
}
