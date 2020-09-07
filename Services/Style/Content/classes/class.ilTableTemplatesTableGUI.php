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
* Table templates table
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesStyle
*/
class ilTableTemplatesTableGUI extends ilTable2GUI
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
    public function __construct($a_temp_type, $a_parent_obj, $a_parent_cmd, $a_style_obj)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->rbacsystem = $DIC->rbac()->system();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        parent::__construct($a_parent_obj, $a_parent_cmd);
        

        include_once("./Services/Accordion/classes/class.ilAccordionGUI.php");
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
        if ($this->parent_obj->checkWrite()) {
            $this->addMultiCommand("deleteTemplateConfirmation", $lng->txt("delete"));
        }

        $this->setEnableTitle(true);
    }

    /**
    * Get items of current folder
    */
    public function getItems()
    {
        $this->setData($this->style_obj->getTemplates($this->temp_type));
    }
    
    /**
    * Fill table row
    */
    protected function fillRow($a_set)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->tpl->setVariable(
            "T_PREVIEW",
            $this->style_obj->lookupTemplatePreview($a_set["id"])
        );
        $this->tpl->setVariable("TID", $a_set["id"]);
        $this->tpl->setVariable("TEMPLATE_NAME", $a_set["name"]);
        $ilCtrl->setParameter($this->parent_obj, "t_id", $a_set["id"]);
        
        if ($this->parent_obj->checkWrite()) {
            $this->tpl->setVariable(
                "LINK_EDIT_TEMPLATE",
                $ilCtrl->getLinkTarget($this->parent_obj, "editTemplate")
            );
            $this->tpl->setVariable("TXT_EDIT", $lng->txt("edit"));
        }
    }
}
