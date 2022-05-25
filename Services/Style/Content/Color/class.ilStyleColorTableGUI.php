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
 * TableGUI class for style editor (image list)
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilStyleColorTableGUI extends ilTable2GUI
{
    protected ilObjStyleSheet $style_obj;
    protected ilAccessHandler $access;
    protected ilRbacSystem $rbacsystem;
    protected Content\Access\StyleAccessManager $access_manager;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        ilObjStyleSheet $a_style_obj,
        Content\Access\StyleAccessManager $access_manager
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->rbacsystem = $DIC->rbac()->system();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $this->access_manager = $access_manager;

        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->setTitle($lng->txt("sty_colors"));
        $this->setDescription($lng->txt("sty_color_info"));
        $this->style_obj = $a_style_obj;
        
        $this->addColumn("", "", "1");	// checkbox
        $this->addColumn($this->lng->txt("sty_color_name"));
        $this->addColumn($this->lng->txt("sty_color_code"));
        $this->addColumn($this->lng->txt("sty_color"));
        $this->addColumn($this->lng->txt("sty_color_flavors"));
        $this->addColumn($this->lng->txt("sty_commands"), "", "1");
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.style_color_row.html", "Services/Style/Content");
        //$this->setSelectAllCheckbox("file");
        $this->getItems();

        // action commands
        if ($this->access_manager->checkWrite()) {
            $this->addMultiCommand("deleteColorConfirmation", $lng->txt("delete"));
        }
        
        $this->setEnableTitle(true);
    }

    public function getItems() : void
    {
        $this->setData($this->style_obj->getColors());
    }
    
    protected function fillRow(array $a_set) : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        for ($i = -80; $i <= 80; $i += 20) {
            $this->tpl->setCurrentBlock("flavor");
            $this->tpl->setVariable("FLAVOR_NAME", "(" . $i . ")");
            $this->tpl->setVariable(
                "FLAVOR_CODE",
                ilObjStyleSheet::_getColorFlavor($a_set["code"], $i)
            );
            $this->tpl->parseCurrentBlock();
        }
        
        $this->tpl->setVariable("COLOR_NAME_ENC", ilLegacyFormElementsUtil::prepareFormOutput($a_set["name"]));
        $this->tpl->setVariable("COLOR_NAME", $a_set["name"]);
        $this->tpl->setVariable("COLOR_CODE", $a_set["code"]);
        
        if ($this->access_manager->checkWrite()) {
            $this->tpl->setVariable("TXT_EDIT", $lng->txt("edit"));
            $ilCtrl->setParameter($this->parent_obj, "c_name", rawurlencode($a_set["name"]));
            $this->tpl->setVariable(
                "LINK_EDIT_COLOR",
                $ilCtrl->getLinkTarget($this->parent_obj, "editColor")
            );
        }
    }
}
