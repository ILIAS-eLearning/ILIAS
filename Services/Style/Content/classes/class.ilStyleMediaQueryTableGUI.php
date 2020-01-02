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
class ilStyleMediaQueryTableGUI extends ilTable2GUI
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
        
        $this->setTitle($lng->txt("sty_media_queries"));
        $this->setDescription($lng->txt("sty_media_query_info"));
        $this->style_obj = $a_style_obj;
        
        $this->addColumn("", "", "1");	// checkbox
        $this->addColumn($this->lng->txt("sty_order"));
        $this->addColumn($this->lng->txt("sty_query"), "");
        $this->addColumn($this->lng->txt("actions"), "");
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.style_media_query_row.html", "Services/Style/Content");
        //$this->setSelectAllCheckbox("file");
        $this->getItems();

        // action commands
        if ($this->parent_obj->checkWrite()) {
            $this->addCommandButton("saveMediaQueryOrder", $lng->txt("sty_save_order"));
            $this->addMultiCommand("deleteMediaQueryConfirmation", $lng->txt("delete"));
        }
        
        $this->setEnableTitle(true);
    }

    /**
    * Get items of current folder
    */
    public function getItems()
    {
        $this->setData($this->style_obj->getMediaQueries());
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
        
        $this->tpl->setVariable("MQUERY", $a_set["mquery"]);
        $this->tpl->setVariable("MQID", $a_set["id"]);
        $this->tpl->setVariable("ORDER_NR", $a_set["order_nr"]);

        if ($this->parent_obj->checkWrite()) {
            $this->tpl->setVariable("TXT_EDIT", $lng->txt("edit"));
            $ilCtrl->setParameter($this->parent_obj, "mq_id", $a_set["id"]);
            $this->tpl->setVariable(
                "LINK_EDIT_MQUERY",
                $ilCtrl->getLinkTarget($this->parent_obj, "editMediaQuery")
            );
        }
    }
}
