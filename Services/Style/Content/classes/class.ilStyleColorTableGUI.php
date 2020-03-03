<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

include_once("Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for style editor (image list)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesStyle
*/
class ilStyleColorTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
    * Constructor
    */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_style_obj)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->rbacsystem = $DIC->rbac()->system();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $ilAccess = $DIC->access();
        $lng = $DIC->language();
        $rbacsystem = $DIC->rbac()->system();
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->setTitle($lng->txt("sty_colors"));
        $this->setDescription($lng->txt("sty_color_info"));
        $this->style_obj = $a_style_obj;
        
        $this->addColumn("", "", "1");	// checkbox
        $this->addColumn($this->lng->txt("sty_color_name"), "");
        $this->addColumn($this->lng->txt("sty_color_code"), "");
        $this->addColumn($this->lng->txt("sty_color"), "");
        $this->addColumn($this->lng->txt("sty_color_flavors"), "");
        $this->addColumn($this->lng->txt("sty_commands"), "", "1");
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.style_color_row.html", "Services/Style/Content");
        //$this->setSelectAllCheckbox("file");
        $this->getItems();

        // action commands
        if ($this->parent_obj->checkWrite()) {
            $this->addMultiCommand("deleteColorConfirmation", $lng->txt("delete"));
        }
        
        $this->setEnableTitle(true);
    }

    /**
    * Get items of current folder
    */
    public function getItems()
    {
        $this->setData($this->style_obj->getColors());
    }
    
    /**
    * Fill table row
    */
    protected function fillRow($a_set)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;
        $rbacsystem = $this->rbacsystem;
        
        for ($i = -80; $i<=80; $i+=20) {
            $this->tpl->setCurrentBlock("flavor");
            $this->tpl->setVariable("FLAVOR_NAME", "(" . $i . ")");
            $this->tpl->setVariable(
                "FLAVOR_CODE",
                ilObjStyleSheet::_getColorFlavor($a_set["code"], $i)
            );
            $this->tpl->parseCurrentBlock();
        }
        
        $this->tpl->setVariable("COLOR_NAME_ENC", ilUtil::prepareFormOutput($a_set["name"]));
        $this->tpl->setVariable("COLOR_NAME", $a_set["name"]);
        $this->tpl->setVariable("COLOR_CODE", $a_set["code"]);
        
        if ($this->parent_obj->checkWrite()) {
            $this->tpl->setVariable("TXT_EDIT", $lng->txt("edit"));
            $ilCtrl->setParameter($this->parent_obj, "c_name", rawurlencode($a_set["name"]));
            $this->tpl->setVariable(
                "LINK_EDIT_COLOR",
                $ilCtrl->getLinkTarget($this->parent_obj, "editColor")
            );
        }
    }
}
