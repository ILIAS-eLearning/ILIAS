<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for survey question source materials
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id: class.ilSurveyMaterialsTableGUI.php 26013 2010-10-12 16:01:03Z hschottm $
*
* @ingroup ModulesSurveyQuestionPool
*
*/
class SurveyMaterialsSourceTableGUI extends ilTable2GUI
{
    public function __construct($a_parent_obj, $a_parent_cmd, $a_cancel_cmd)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
                
        $this->addColumn($lng->txt("title"), "title");
        $this->addColumn($lng->txt("action"), "");
        $this->setTitle($this->lng->txt('select_object_to_link'));
        
        $this->setLimit(9999);
        $this->disable("numinfo");
                
        $this->setRowTemplate("tpl.il_svy_qpl_material_source_row.html", "Modules/SurveyQuestionPool");

        $this->setFormAction($ilCtrl->getFormAction($this->getParentObject(), $this->getParentCmd()));
        $this->addCommandButton($a_cancel_cmd, $this->lng->txt('cancel'));
        
        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");
    }
    
    /**
    * Fill data row
    */
    protected function fillRow($data)
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $url_cmd = "add" . strtoupper($data["item_type"]);
        $url_type = strtolower($data["item_type"]);
    
        $ilCtrl->setParameter($this->getParentObject(), $url_type, $data["item_id"]);
        $url = $ilCtrl->getLinkTarget($this->getParentObject(), $url_cmd) .
        $ilCtrl->setParameter($this->getParentObject(), $url_type, "");
    
        $this->tpl->setVariable("TITLE", $data['title']);
        $this->tpl->setVariable("URL_ADD", $url);
        $this->tpl->setVariable("TXT_ADD", $lng->txt("add"));
    }
}
