<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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


/**
* Class ilObjStyleSheetGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "class.ilObjectGUI.php";

class ilObjStyleSheetGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjStyleSheetGUI($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "sty";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference, false);
	}

	/**
	*
	*/
	function editObject()
	{
		global $rbacsystem, $lng;

		if (!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError("No permission to edit the object",$this->ilias->error_obj->WARNING);
		}
		else
		{
			$this->getTemplateFile("edit");
			$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("title"));
			$this->tpl->setVariable(strtoupper("TITLE"), $this->object->getTitle());
			$this->tpl->setVariable("TXT_DESCRIPTION", $this->lng->txt("description"));
			$this->tpl->setVariable(strtoupper("DESCRIPTION"), $this->object->getDescription());
			$this->tpl->parseCurrentBlock();
			$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$this->ref_id."$obj_str&cmd=update");
			$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
			$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		}
	}

	function saveObject()
	{
		global $rbacsystem;
		// TODO: create permission check!?
		if (!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError("No permission to edit the object",$this->ilias->error_obj->WARNING);
		}
		else
		{
			$class_name = "ilObjStyleSheet";
			require_once("classes/class.ilObjStyleSheet.php");
			$newObj = new ilObjStyleSheet();
			$newObj->setTitle($_POST["style_title"]);
			$newObj->setDescription($_POST["style_description"]);
			$newObj->create();
		}
		header("Location:".$this->getReturnLocation("save",
			"adm_object.php?".$this->link_params));
		exit();

	}

} // END class.ObjStyleSheetGUI
?>
