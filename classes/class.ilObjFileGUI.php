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
* Class ilObjFileGUI
*
* @author Sascha Hofmann <shofmann@databay.de> 
* @version $Id$
*
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "class.ilObjectGUI.php";
require_once "./classes/class.ilObjFile.php"; // temp. fix

class ilObjFileGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access	public
	*/
	function ilObjFileGUI($a_data,$a_id,$a_call_by_reference = true, $a_prepare_output = true)
	{
		$this->type = "file";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference, $a_prepare_output);
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
	* create new object form
	*
	* @access	public
	*/
	function createObject()
	{
		global $rbacsystem;

		$new_type = $_POST["new_type"] ? $_POST["new_type"] : $_GET["new_type"];

		if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], $new_type))
		{
			$this->ilErr->raiseError($this->lng->txt("permission_denied"),$this->ilErr->MESSAGE);
		}

		// fill in saved values in case of error
		$data = array();
		$data["fields"] = array();
		$data["fields"]["title"] = ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["Fobject"]["title"],true);
		$data["fields"]["desc"] = ilUtil::stripSlashes($_SESSION["error_post_vars"]["Fobject"]["desc"]);
		$data["fields"]["file"] = $_SESSION["error_post_vars"]["Fobject"]["file"];

		$this->getTemplateFile("new",$this->type);

		foreach ($data["fields"] as $key => $val)
		{
			$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
			$this->tpl->setVariable(strtoupper($key), $val);
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable("FORMACTION", $this->getFormAction("save",$this->ctrl->getFormAction($this)."&new_type=".$new_type));
		//$this->tpl->setVariable("FORMACTION", $this->getFormAction("save","adm_object.php?cmd=gateway&ref_id=".$_GET["ref_id"]."&new_type=".$this->type));
		$this->tpl->setVariable("TXT_TITLE_NOTE", $this->lng->txt("if_no_title_then_filename"));
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
	function saveObject()
	{
		global $rbacsystem, $objDefinition;

		$data = $_FILES["Fobject"];

		// delete trailing '/' in filename
		if (substr($data["name"]["file"],-1) == '/')
		{
			$data["name"]["file"] = substr($data["name"]["file"],0,-1);
		}

		if (empty($data["name"]["file"]))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_file"),$this->ilias->error_obj->MESSAGE);
		}

		if (empty($_POST["Fobject"]["title"]))
		{
			$_POST["Fobject"]["title"] = $_FILES["Fobject"]["name"]["file"];
			//$this->ilias->raiseError($this->lng->txt("msg_no_title"),$this->ilias->error_obj->MESSAGE);
		}

		// create and insert file in grp_tree
		include_once("classes/class.ilObjFile.php");
		$fileObj = new ilObjFile();
		$fileObj->setType($this->type);
		$fileObj->setTitle(ilUtil::stripSlashes($_POST["Fobject"]["title"]));
		$fileObj->setDescription(ilUtil::stripSlashes($_POST["Fobject"]["desc"]));
		$fileObj->setFileName($_FILES["Fobject"]["name"]["file"]);
		$fileObj->setFileType($_FILES["Fobject"]["type"]["file"]);
		$fileObj->create();
		$fileObj->createReference();
		$fileObj->putInTree($_GET["ref_id"]);
		$fileObj->setPermissions($_GET["ref_id"]);
		// upload file to filesystem
		$fileObj->createDirectory();
		$fileObj->getUploadFile($_FILES["Fobject"]["tmp_name"]["file"],$_FILES["Fobject"]["name"]["file"]);

		sendInfo($this->lng->txt("file_added"),true);
		ilUtil::redirect($this->getReturnLocation("save",$this->ctrl->getLinkTarget($this,"")));
		//ilUtil::redirect($this->getReturnLocation("save","adm_object.php?".$this->link_params));
	}
	
	function sendFileObject()
	{
		$this->object->sendFile();
		
		return true;
	}

	/**
	* cancel action and go back to previous page
	* @access	public
	*
	*/
	function cancelObject()
	{
		session_unregister("saved_post");

		sendInfo($this->lng->txt("msg_cancel"),true);

		//sendInfo($this->lng->txt("action_aborted"),true);
		$return_location = $_GET["cmd_return_location"];
				
		ilUtil::redirect($this->ctrl->getLinkTarget($this,$return_location));
		//ilUtil::redirect($this->getReturnLocation("cancel","adm_object.php?".$this->link_params));
	}
	
	// get tabs
	function getTabs(&$tabs_gui)
	{
		global $rbacsystem;

		$this->ctrl->setParameter($this,"ref_id",$this->ref_id);

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

	/**
	* cancel action and go back to previous page
	* @access	public
	*
	*/
	/*function cancelObject()
	{
		$this->link_params = "ref_id=".$this->tree->getParentId($this->ref_id);

		session_unregister("saved_post");

		sendInfo($this->lng->txt("msg_cancel"),true);

		ilUtil::redirect($this->getReturnLocation("cancel","adm_object.php?".$this->link_params));
	}*/

	/**
	* updates object entry in object_data
	*
	* @access	public
	*
	function updateObject()
	{
		$this->object->setTitle(ilUtil::stripSlashes($_POST["Fobject"]["title"]));
		$this->object->setDescription(ilUtil::stripSlashes($_POST["Fobject"]["desc"]));
		$this->update = $this->object->update();

		sendInfo($this->lng->txt("msg_obj_modified"),true);
		$this->link_params = "ref_id=".$this->tree->getParentId($this->ref_id);
		ilUtil::redirect($this->getReturnLocation("update","adm_object.php?".$this->link_params));
	}*/
} // END class.ilObjFileGUI
?>
