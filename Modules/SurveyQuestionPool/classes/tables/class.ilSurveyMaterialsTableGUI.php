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
* TableGUI class for survey question materials
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
*
* @ingroup ModulesSurveyQuestionPool
*
*/
class ilSurveyMaterialsTableGUI extends ilTable2GUI
{
    private $counter;
    private $write_access;
    
    public function __construct($a_parent_obj, $a_parent_cmd, $a_write_access = false)
    {
        global $DIC;

        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->write_access = $a_write_access;
        $this->counter = 1;
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->setFormName('evaluation_all');
        $this->setStyle('table', 'fullwidth');
        $this->addColumn('', 'f', '1%');
        $this->addColumn($lng->txt("type"), "type", "");
        $this->addColumn($lng->txt("material"), "material", "");
        $this->setTitle($this->lng->txt('materials'));
        
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setRowTemplate("tpl.il_svy_qpl_material_row.html", "Modules/SurveyQuestionPool");
        $this->setPrefix('idx');
        $this->setSelectAllCheckbox('idx');
        $this->disable('sort');
        $this->enable('header');

        if ($this->write_access) {
            $this->addMultiCommand('deleteMaterial', $this->lng->txt('remove'));
        }
    }
    
    /**
    * Fill data row
    */
    protected function fillRow($data)
    {
        $this->tpl->setVariable("TYPE", $data['type']);
        $this->tpl->setVariable("TITLE", $data['title']);
        $this->tpl->setVariable("HREF", $data['href']);
        $this->tpl->setVariable("CHECKBOX_VALUE", $this->counter - 1);
        $this->tpl->setVariable("COUNTER", $this->counter++);
    }
}
