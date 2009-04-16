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
* TableGUI class for access keys
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesAccessibility
*/
class ilAccessKeyTableGUI extends ilTable2GUI
{
	
	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		// get keys
		include_once("./Services/Accessibility/classes/class.ilAccessKey.php");
		
		$keys = ilAccessKey::getKeys();
		$data = array();
		foreach ($keys as $f => $k)
		{
			$data[] = array("func_id" => $f, "access_key" => $k);
		}
		$this->setData($data);
		$this->setTitle($lng->txt("acc_access_keys"));
		$this->setLimit(9999);
		
		$this->addColumn($this->lng->txt("acc_component"), "", "");
		$this->addColumn($this->lng->txt("acc_function"), "", "");
		$this->addColumn($this->lng->txt("acc_access_key"), "", "");
		
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.access_key_row.html", "Services/Accessibility");
		$this->disable("footer");
		$this->setEnableTitle(true);

//		$this->addMultiCommand("", $lng->txt(""));
		$this->addCommandButton("saveAccessKeys", $lng->txt("save"));
	}
	
	/**
	* Fill table row
	*/
	protected function fillRow($a_set)
	{
		global $lng;

		$this->tpl->setVariable("VAL_COMPONENT", ilAccessKey::getComponentNames($a_set["func_id"]));
		$this->tpl->setVariable("VAL_FUNCTION", ilAccessKey::getFunctionName($a_set["func_id"]));
		$this->tpl->setVariable("FUNC_ID", $a_set["func_id"]);
		$this->tpl->setVariable("VAL_ACC_KEY", ilUtil::prepareFormOutput($a_set["access_key"]));
	}

}
?>
