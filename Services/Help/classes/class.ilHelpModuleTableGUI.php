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
 * TableGUI class for help modules
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilHelpModuleTableGUI extends ilTable2GUI
{
    protected ilAccessHandler $access;
    protected ilSetting $settings;
    protected bool $has_write_permission;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        bool $a_has_write_permission = false
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->settings = $DIC->settings();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $this->has_write_permission = $a_has_write_permission;
        
        $this->setId("help_mods");
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->getHelpModules();
        $this->setTitle($lng->txt("help_modules"));
        
        $this->addColumn("", "", "1");
        $this->addColumn($this->lng->txt("title"));
        $this->addColumn($this->lng->txt("help_imported_on"));
        $this->addColumn($this->lng->txt("actions"));
        
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.help_module_row.html", "Services/Help");

        if ($this->has_write_permission) {
            $this->addMultiCommand("confirmHelpModulesDeletion", $lng->txt("delete"));
        }
    }
    
    public function getHelpModules() : void
    {
        $this->setData($this->parent_obj->getObject()->getHelpModules());
    }

    protected function fillRow(array $a_set) : void
    {
        $lng = $this->lng;
        $ilSetting = $this->settings;
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameter($this->parent_obj, "hm_id", $a_set["id"]);
        if ($this->has_write_permission) {
            $this->tpl->setCurrentBlock("cmd");
            if ((int) $a_set["id"] === (int) $ilSetting->get("help_module")) {
                $this->tpl->setVariable(
                    "HREF_CMD",
                    $ilCtrl->getLinkTarget($this->parent_obj, "deactivateModule")
                );
                $this->tpl->setVariable("TXT_CMD", $lng->txt("deactivate"));
            } else {
                $this->tpl->setVariable(
                    "HREF_CMD",
                    $ilCtrl->getLinkTarget($this->parent_obj, "activateModule")
                );
                $this->tpl->setVariable("TXT_CMD", $lng->txt("activate"));
            }
            $this->tpl->parseCurrentBlock();
        }
        $ilCtrl->setParameter($this->parent_obj, "hm_id", "");
        $this->tpl->setVariable("TITLE", $a_set["title"] ?? "");
        $this->tpl->setVariable(
            "CREATION_DATE",
            ilDatePresentation::formatDate(new ilDateTime($a_set["create_date"] ?? null, IL_CAL_DATETIME))
        );
        $this->tpl->setVariable("ID", $a_set["id"]);
    }
}
