<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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
* TableGUI class for 
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup Services
*/
class ilTrashTableGUI extends ilTable2GUI
{
	
	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		$this->ref_id = $a_ref_id;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		//$this->setTitle($lng->txt(""));
		
		$this->addColumn($this->lng->txt(""), "", "1", 1);
		$this->addColumn($this->lng->txt("type"), "", "1");
		$this->addColumn($this->lng->txt("title"), "title");
		$this->addColumn($this->lng->txt("last_change"), "last_update");
		$this->setDefaultOrderField("title");
		$this->setDefaultOrderDirection("asc");
		
		
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.trash_list_row.html", "Services/Repository");
		//$this->disable("footer");
		$this->setEnableTitle(true);
		$this->setSelectAllCheckbox("trash_id[]");
		

		$this->addMultiCommand("removeFromSystem", $lng->txt("btn_remove_system"));
		$this->addMultiCommand("undelete", $lng->txt("btn_undelete"));
	}
	
	/**
	* Fill table row
	*/
	protected function fillRow($a_set)
	{
		global $lng;
		
		$img = ilUtil::getImagePath("icon_".$a_set["type"].".gif");
		if (is_file($img))
		{
			$this->tpl->setVariable("IMG_TYPE", ilUtil::img($img,
				$lng->txt("icon")." ".$lng->txt("obj_".$a_set["type"])));
		}
		$this->tpl->setVariable("ID", $a_set["ref_id"]);
		$this->tpl->setVariable("VAL_TITLE", $a_set["title"]);
		$this->tpl->setVariable("VAL_LAST_CHANGE", $a_set["last_update"]);
	}

}
?>
