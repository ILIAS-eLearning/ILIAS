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
* $Id$Id: class.ilObjFolderGUI.php,v 1.21 2004/05/06 18:42:47 shofmann Exp $
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
	}

	function _forwards()
	{
		return array();
	}
	
	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch ($next_class)
		{
			default:
				if (empty($cmd))
				{
					$this->ctrl->returnToParent($this);
					$cmd = "view";
				}

				$cmd .= "Object";
				$this->$cmd();
				break;
		}		
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

		$this->tpl->setVariable("FORMACTION", $this->getFormAction("save",$this->ctrl->getFormAction($this)."&new_type=".$new_type));
//		$this->tpl->setVariable("FORMACTION", $this->getFormAction("save","adm_object.php?cmd=gateway&ref_id=".$_GET["ref_id"]."&new_type=".$new_type));
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
		$folderObj = new ilObjFolder(0,$this->withReferences());
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
		{									// note: e.g. folders in media pools don't
			$folderObj->createReference();
			$folderObj->setPermissions($a_parent);
		}

		$folderObj->putInTree($a_parent);
			
		sendInfo($this->lng->txt("fold_added"),true);
		ilUtil::redirect($this->getReturnLocation("save",$this->ctrl->getLinkTarget($this,"")));
		//ilUtil::redirect($this->getReturnLocation("save","adm_object.php?".$this->link_params));
	}

	// get tabs
	function getTabs(&$tabs_gui)
	{
		global $rbacsystem;

		$this->ctrl->setParameter($this,"ref_id",$this->ref_id);

		if ($rbacsystem->checkAccess('read',$this->ref_id))
		{
			$tabs_gui->addTarget("view_content",
				$this->ctrl->getLinkTarget($this, ""), "", get_class($this));
		}
		
		if ($rbacsystem->checkAccess('write',$this->ref_id))
		{
			$tabs_gui->addTarget("edit_properties",
				$this->ctrl->getLinkTarget($this, "edit"), "edit", get_class($this));
		}

		if ($rbacsystem->checkAccess('edit_permission',$this->ref_id))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTarget($this, "perm"), "perm", get_class($this));
		}

		if ($this->ctrl->getTargetScript() == "adm_object.php")
		{
			$tabs_gui->addTarget("show_owner",
				$this->ctrl->getLinkTarget($this, "owner"), "owner", get_class($this));
			
			if ($this->tree->getSavedNodeData($this->ref_id))
			{
				$tabs_gui->addTarget("trash",
					$this->ctrl->getLinkTarget($this, "trash"), "trash", get_class($this));
			}
		}
	}
} // END class.ilObjFolderGUI
?>
