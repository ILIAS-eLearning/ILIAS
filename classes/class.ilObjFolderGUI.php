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
* $Id$Id: class.ilObjFolderGUI.php,v 1.11 2003/10/31 12:33:22 shofmann Exp $
* 
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "class.ilObjectGUI.php";

class ilObjFolderGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access	public
	*/
	function ilObjFolderGUI($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "fold";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,false);

		$this->setReturnLocation("cut","group.php?cmd=show_content&ref_id=".$_GET["ref_id"]);
		$this->setReturnLocation("clear","group.php?cmd=show_content&ref_id=".$_GET["ref_id"]);
		$this->setReturnLocation("copy","group.php?cmd=show_content&ref_id=".$_GET["ref_id"]);
		$this->setReturnLocation("link","group.php?cmd=show_content&ref_id=".$_GET["ref_id"]);
		$this->setReturnLocation("paste","group.php?cmd=show_content&ref_id=".$_GET["ref_id"]);
		$this->setReturnLocation("cancelDelete","group.php?cmd=show_content&ref_id=".$_GET["ref_id"]);
		$this->setReturnLocation("cancel","group.php?cmd=show_content&ref_id=".$_GET["ref_id"]);
		$this->setReturnLocation("confirmedDelete","group.php?cmd=show_content&ref_id=".$_GET["ref_id"]);
		$this->setReturnLocation("removeFromSystem","group.php?cmd=show_content&ref_id=".$_GET["ref_id"]);
		$this->setReturnLocation("undelete","group.php?cmd=show_content&ref_id=".$_GET["ref_id"]);
	}

	/**
	* create new object form
	*
	* @access	public
	*/
	function createObject()
	{
		// creates a child object
		global $rbacsystem;
	
		// TODO: get rid of $_GET variable
		/*if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], $_POST["new_type"]))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		else
		{*/
			// fill in saved values in case of error
			$data = array();
			$data["fields"] = array();
			$data["fields"]["title"] = ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["Fobject"]["title"],true);
			$data["fields"]["desc"] = ilUtil::stripSlashes($_SESSION["error_post_vars"]["Fobject"]["desc"]);

			$this->getTemplateFile("edit");
			
			//$this->tpl->addBlockFile("CONTENT", "content", "tpl.folder_new.html");
			//$this->tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");
			//$this->tpl->addBlockFile("CONTENT", "content", "tpl.main.html");
			//$this->tpl = new ilTemplate("tpl.obj_edit.html", true, true);
			//$this->tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");	
			
			foreach ($data["fields"] as $key => $val)
			{
				$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
				$this->tpl->setVariable(strtoupper($key), $val);
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setVariable("FORMACTION", $this->getFormAction("save","group.php?cmd=save&ref_id=".$_GET["ref_id"]."&obj_id=".$_GET["obj_id"]."&tree_id=".$_GET["tree_id"]."&tree_table=".$_GET["tree_table"]."&new_type=".$_POST["new_type"]."&parent_non_rbac_id=".$_GET["obj_id"]));
			//$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
			
			$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
			$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
			$this->tpl->setVariable("CMD_SUBMIT", "save");

			$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
			$this->tpl->show();
		//}
	}

	/**
	* save object
	*
	* @access	public
	*/
	function saveObject()
	{
		//var_dump($_GET["ref_id"]);exit;
		global $rbacsystem, $objDefinition;

		$new_type = $_POST["new_type"] ? $_POST["new_type"] : $_GET["new_type"];

		// create permission is already checked in createObject. This check here is done to prevent hacking attempts
//		if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], $new_type))
//		{
//			$this->ilias->raiseError($this->lng->txt("no_create_permission"), $this->ilias->error_obj->MESSAGE);
//		}
		
		// create and insert Folder in grp_tree
		include_once("classes/class.ilObjFolder.php");
		$folderObj = new ilObjFolder();
		$folderObj->setType($this->type);
		$folderObj->setTitle(ilUtil::stripSlashes($_POST["Fobject"]["title"]));
		$folderObj->setDescription(ilUtil::stripSlashes($_POST["Fobject"]["desc"]));
		$folderObj->create();
		$folderObj->createReference();
		//insert folder in grp_tree
		$folderObj->putInTree($_GET["ref_id"]);
			
		// no notify for folders
		//$folderObj->notify("new",$_GET["ref_id"],$_GET["parent_non_rbac_id"],$_GET["ref_id"],$folderObj->getRefId());

		sendInfo($this->lng->txt("fold_add"),true);		
		header("Location: group.php?cmd=show_content&ref_id=".$_GET["ref_id"]);
		exit();
	}
} // END class.ilObjFolderGUI
?>
