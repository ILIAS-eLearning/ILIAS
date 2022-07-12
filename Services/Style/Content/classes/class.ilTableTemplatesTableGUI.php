<?php declare(strict_types=1);

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

use ILIAS\Style\Content;

/**
 * Table templates table
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilTableTemplatesTableGUI extends ilTable2GUI
{
    protected string $temp_type;
    protected ilObjStyleSheet $style_obj;
    protected ilAccessHandler $access;
    protected ilRbacSystem $rbacsystem;
    protected Content\Access\StyleAccessManager $access_manager;

    public function __construct(
        string $a_temp_type,
        object $a_parent_obj,
        string $a_parent_cmd,
        ilObjStyleSheet $a_style_obj,
        Content\Access\StyleAccessManager $access_manager
    ) {
        global $DIC;

        $this->access_manager = $access_manager;
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->rbacsystem = $DIC->rbac()->system();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        ilAccordionGUI::addCss();

        $this->setTitle($lng->txt("sty_templates"));
        $this->style_obj = $a_style_obj;
        $this->temp_type = $a_temp_type;
        
        $this->addColumn("", "", "1");	// checkbox
        $this->addColumn($this->lng->txt("sty_template_name"), "");
        $this->addColumn($this->lng->txt("sty_preview"), "");
        $this->addColumn($this->lng->txt("sty_commands"), "", "1");
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.style_template_row.html", "Services/Style/Content");
        $this->getItems();

        // action commands
        if ($this->access_manager->checkWrite()) {
            $this->addMultiCommand("deleteTemplateConfirmation", $lng->txt("delete"));
        }

        $this->setEnableTitle(true);
    }

    public function getItems() : void
    {
        $this->setData($this->style_obj->getTemplates($this->temp_type));
    }
    
    protected function fillRow(array $a_set) : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->tpl->setVariable(
            "T_PREVIEW",
            $this->style_obj->lookupTemplatePreview((int) $a_set["id"])
        );
        $this->tpl->setVariable("TID", $a_set["id"]);
        $this->tpl->setVariable("TEMPLATE_NAME", $a_set["name"]);
        $ilCtrl->setParameter($this->parent_obj, "t_id", $a_set["id"]);
        
        if ($this->access_manager->checkWrite()) {
            $this->tpl->setVariable(
                "LINK_EDIT_TEMPLATE",
                $ilCtrl->getLinkTarget($this->parent_obj, "editTemplate")
            );
            $this->tpl->setVariable("TXT_EDIT", $lng->txt("edit"));
        }
    }
}
