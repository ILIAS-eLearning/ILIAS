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
* $Id$Id: class.ilObjFolderGUI.php,v 1.4 2003/07/29 13:04:23 mrus Exp $
* 
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "class.ilObjectGUI.php";

class ilObjFolderGUI extends ilObjectGUI
{
	/**
	* (ref_)id of the table where the folder is included
	*/
	var $tree_id;
	
	/**
	* name of the database table where the folder is included (e.g. grp_tree)
	*/
	var $tree_table;
	
	/**
	* Constructor
	* @access	public
	*/
	function ilObjFolderGUI($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "fold";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference);
		

		if ($_GET["tree_id"])
		{
			$this->tree_id = $_GET["tree_id"];
			
		}
		else
		{
		 	$this->tree_id = $_GET["ref_id"];
			
		}
		
		//temporary substituted
		//$this->tree_table = $_GET["tree_table"];
		$this->tree_table = "grp_tree";
		
		$this->local_tree = new ilTree($this->tree_id);
		$this->local_tree->setTableNames($this->tree_table,"object_data","object_reference");
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
			$data["fields"]["title"] = $_SESSION["error_post_vars"]["Fobject"]["title"];
			$data["fields"]["desc"] = $_SESSION["error_post_vars"]["Fobject"]["desc"];

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
	* @access	public
	*/
	function saveObject()
	{
		global $rbacsystem, $rbacreview, $rbacadmin, $tree, $objDefinition;

		//if ($rbacsystem->checkAccess("create", $_GET["ref_id"], $_GET["new_type"]))
		//{
		// create and insert Folder in objecttree
		include_once("classes/class.ilObjFolder.php");
		$folderObj = new ilObjFolder();
		$folderObj->setType($this->type);
		$folderObj->setTitle($_POST["Fobject"]["title"]);
		$folderObj->setDescription($_POST["Fobject"]["desc"]);
		$folderObj->create();

		//$folderObj->createReference();
		//insert folder in local_tree
		//echo $folderObj->getId()."-".$_GET["obj_id"]."-".$_GET["tree_id"];
		include_once "class.ilObjGroup.php";
		ilObjGroup::insertGroupNode($folderObj->getId(), $_GET["obj_id"],$_GET["tree_id"]);

		/*//$object =& $this->ilias->obj_factory->getInstanceByRefId($_GET["ref_id"]);
		//echo "folder_id:".$folderObj->getId()."übergeordnetes Objekt".$object->getId();
		//insert folder in local_tree
		$this->local_tree->insertNode($folderObj->getId(), $_GET["obj_id"]);
		//make sure that objects without a ref_id gets -1 as substitution 
		$folderObj->setRefId($_GET["tree_id"],$folderObj->getId(),$_GET["obj_id"]);
		*/
		/*}
		else
		{
			$this->ilias->raiseError("No permission to create object", $this->ilias->error_obj->WARNING);
		}*/

		sendInfo($this->lng->txt("folder_added"),true);		
		header("Location: group.php?cmd=show_content&ref_id=".$_GET["ref_id"]);
		exit();
	}
} // END class.ilObjFolderGUI
?>
