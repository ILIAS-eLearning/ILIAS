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
* Class ilObjFolderGUI
*
* @author Martin Rus <develop-ilias@uni-koeln.de>
* $Id$Id: class.ilObjFolderGUI.php,v 1.18 2004/04/12 13:46:52 shofmann Exp $
*
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "class.ilObjectGUI.php";

class ilObjFolderGUI extends ilObjectGUI
{
	var $folder_tree;		// folder tree

	/**
	* Constructor
	* @access	public
	*/
	function ilObjFolderGUI($a_data, $a_id = 0, $a_call_by_reference = true, $a_prepare_output = true)
	{
		$this->type = "fold";
		$this->ilObjectGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

		//$this->lng =& $lng;
	}

	/**
	* set tree
	*/
	function setFolderTree($a_tree)
	{
		$this->folder_tree =& $a_tree;
	}

	/**
	* create new object form
	*
	* @access	public
	*/
	function createObject()
	{
		$new_type = $_POST["new_type"] ? $_POST["new_type"] : $_GET["new_type"];

		// creates a child object
		global $rbacsystem;

		/*if (!$rbacsystem->checkAccess("create_fold", $_GET["ref_id"]))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
			exit();
		}*/

		// fill in saved values in case of error
		$data = array();
		$data["fields"] = array();
		$data["fields"]["title"] = ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["Fobject"]["title"],true);
		$data["fields"]["desc"] = ilUtil::stripSlashes($_SESSION["error_post_vars"]["Fobject"]["desc"]);

		$this->getTemplateFile("edit",$new_type);

		foreach ($data["fields"] as $key => $val)
		{
			$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
			$this->tpl->setVariable(strtoupper($key), $val);
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable("FORMACTION", $this->getFormAction("save","adm_object.php?cmd=gateway&ref_id=".$_GET["ref_id"]."&new_type=".$new_type));
		$this->tpl->setVariable("TXT_HEADER", $this->lng->txt($this->type."_new"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt($this->type."_add"));
		$this->tpl->setVariable("CMD_SUBMIT", "save");
		$this->tpl->setVariable("TARGET", $this->getTargetFrame("save"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
	}

	/**
	* save object
	*
	* @access	public
	*/
	function saveObject($a_parent = 0)
	{
		if ($a_parent == 0)
		{
			$a_parent = $_GET["ref_id"];
		}

		// create and insert Folder in grp_tree
		include_once("classes/class.ilObjFolder.php");
		$folderObj = new ilObjFolder();
		$folderObj->setType($this->type);
		$folderObj->setTitle(ilUtil::stripSlashes($_POST["Fobject"]["title"]));
		$folderObj->setDescription(ilUtil::stripSlashes($_POST["Fobject"]["desc"]));
		$folderObj->create();
		$this->object =& $folderObj;

		if (is_object($this->folder_tree))		// groups gui should call ObjFolderGUI->setFolderTree also
		{
			$folderObj->setFolderTree($this->folder_tree);
		}
		else
		{
			$folderObj->setFolderTree($this->tree);
		}

		if ($this->withReferences())		// check if this folders use references
		{								// note: e.g. folders in media pools don't
			$folderObj->createReference();
			$folderObj->putInTree($a_parent);
		}

		// no notify for folders
		//$folderObj->notify("new",$_GET["ref_id"],$_GET["parent_non_rbac_id"],$_GET["ref_id"],$folderObj->getRefId());

		sendInfo($this->lng->txt("fold_added"),true);
		ilUtil::redirect($this->getReturnLocation("save","adm_object.php?".$this->link_params));
	}
} // END class.ilObjFolderGUI
?>
