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
* Class ilObjRoleFolderGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$
* 
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "class.ilObjectGUI.php";

class ilObjRoleFolderGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access	public
	*/
	function ilObjRoleFolderGUI($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "rolf";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference);
	}

	/**
	* view object
	*
	* @access	public
	*/
	function viewObject()
	{
		global $rbacsystem, $rbacreview;

		if (!$rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		//prepare objectlist
		$this->data = array();
		$this->data["data"] = array();
		$this->data["ctrl"] = array();

		$this->data["cols"] = array("", "type", "name", "description", "last_change");

		if ($list = $rbacreview->getRoleListByObject($_GET["ref_id"],true))
		{
			foreach ($list as $key => $val)
			{
				//visible data part
				$this->data["data"][] = array(
						"type"			=> $val["type"],
						"name"			=> $val["title"],
						"description"	=> $val["desc"],
						"last_change"	=> $val["last_update"],
						"obj_id"		=> $val["obj_id"]
					);

			}
		} //if roledata

		$this->maxcount = count($this->data["data"]);

		// sorting array
		include_once "./include/inc.sort.php";
		$this->data["data"] = sortArray($this->data["data"],$_GET["sort_by"],$_GET["sort_order"]);
		$this->data["data"] = array_slice($this->data["data"],$_GET["offset"],$_GET["limit"]);

		// now compute control information
		foreach ($this->data["data"] as $key => $val)
		{
			$this->data["ctrl"][$key] = array(
											"ref_id"	=> $this->object->getRefId(),
											"obj_id"	=> $val["obj_id"],
											"type"		=> $val["type"],
											// DEFAULT ACTION IS 'permObject()'
											"cmd"		=> "perm"
											);		

			unset($this->data["data"][$key]["obj_id"]);
			$this->data["data"][$key]["last_change"] = ilFormat::formatDate($this->data["data"][$key]["last_change"]);
		}

		parent::displayList();
	} //function

	/**
	* confirmObject
	* handles deletion of roles!!
	* 
	* @access	public
	*/
	function confirmedDeleteObject()
	{
		global $rbacsystem;

		// FOR NON_REF_OBJECTS WE CHECK ACCESS ONLY OF PARENT OBJECT ONCE
		if (!$rbacsystem->checkAccess('delete',$this->object->getRefId()))
		{
			$perform_delete = false;
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_delete")." ".
						 $not_deletable,$this->ilias->error_obj->WARNING);
		}
		else
		{
			$feedback["count"] = count($_SESSION["saved_post"]);
		
			// FOR ALL SELECTED OBJECTS
			foreach ($_SESSION["saved_post"] as $id)
			{
				// instatiate correct object class (role or rolt)
				$obj =& $this->ilias->obj_factory->getInstanceByObjId($id);

				if ($obj->getType() == "role")
				{
					$obj->setParent($this->object->getRefId());
					$feedback["role"] = true;
				}
				else
				{
					$feedback["rolt"] = true;
				}

				$obj->delete();
				unset($obj);
			}
			
			// Compose correct feedback
			if ($feedback["count"] > 1)
			{
				if ($feedback["role"] === true)
				{
					if ($feedback["rolt"] === true)
					{
						sendInfo($this->lng->txt("msg_deleted_roles_rolts"),true);					
					}
					else
					{
						sendInfo($this->lng->txt("msg_deleted_roles"),true);						
					}
				}
				else
				{
					sendInfo($this->lng->txt("msg_deleted_rolts"),true);						
				}
			}
			else
			{
				if ($feedback["role"] === true)
				{
					sendInfo($this->lng->txt("msg_deleted_role"),true);	
				}
				else
				{
					sendInfo($this->lng->txt("msg_deleted_rolt"),true);	
				}	
			}
			
			header("location: adm_object.php?ref_id=".$_GET["ref_id"]);
			exit();
		}
	}
	
	/**
	* role folders are created automatically
	*
	* @access	public
	*/
	function createObject()
	{
		$this->object->setTitle($this->lng->txt("obj_".$this->object->getType()."_local"));
		$this->object->setDescription("obj_".$this->object->getType()."_local_desc");
		
		$this->saveObject();
	}
	
	/**
	* display deletion confirmation screen
	*
	* @access	public
	*/
	function deleteObject()
	{
		if(!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}
		// SAVE POST VALUES
		$_SESSION["saved_post"] = $_POST["id"];

		unset($this->data);
		$this->data["cols"] = array("type", "title", "description", "last_change");

		foreach($_POST["id"] as $id)
		{
			$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($id);

			$this->data["data"]["$id"] = array(
				"type"        => $obj_data->getType(),
				"title"       => $obj_data->getTitle(),
				"desc"        => $obj_data->getDescription(),
				"last_update" => $obj_data->getLastUpdateDate());
		}

		$this->data["buttons"] = array( "cancelDelete"  => $this->lng->txt("cancel"),
								  "confirmedDelete"  => $this->lng->txt("confirm"));

		$this->getTemplateFile("confirm");

		sendInfo($this->lng->txt("info_delete_sure"));

		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=gateway");

		// BEGIN TABLE HEADER
		foreach ($this->data["cols"] as $key)
		{
			$this->tpl->setCurrentBlock("table_header");
			$this->tpl->setVariable("TEXT",$this->lng->txt($key));
			$this->tpl->parseCurrentBlock();
		}
		// END TABLE HEADER

		// BEGIN TABLE DATA
		$counter = 0;

		foreach ($this->data["data"] as $key => $value)
		{
			// BEGIN TABLE CELL
			foreach ($value as $key => $cell_data)
			{
				$this->tpl->setCurrentBlock("table_cell");

				// CREATE TEXT STRING
				if ($key == "type")
				{
					$this->tpl->setVariable("TEXT_CONTENT",ilUtil::getImageTagByType($cell_data,$this->tpl->tplPath));
				}
				else
				{
					$this->tpl->setVariable("TEXT_CONTENT",$cell_data);
				}

				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("table_row");
			$this->tpl->setVariable("CSS_ROW",ilUtil::switchColor(++$counter,"tblrow1","tblrow2"));
			$this->tpl->parseCurrentBlock();
			// END TABLE CELL
		}
		// END TABLE DATA

		// BEGIN OPERATION_BTN
		foreach ($this->data["buttons"] as $name => $value)
		{
			$this->tpl->setCurrentBlock("operation_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* ???
	* TODO: what is the purpose of this function?
	* @access	public
	*/
	function adoptPermSaveObject()
	{
		sendinfo($this->lng->txt("saved_successfully"),true);
		
		header("Location: adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=perm");
		exit();
	}
	
	/**
	* show possible subobjects (pulldown menu)
	* overwritten to prevent displaying of role templates in local role folders
	*
	* @access	public
 	*/
	function showPossibleSubObjects()
	{
		$d = $this->objDefinition->getCreatableSubObjects($this->object->getType());
		
		if ($this->object->getRefId() != ROLE_FOLDER_ID)
		{
			unset($d["rolt"]);
		}

		if (count($d) > 0)
		{
			foreach ($d as $row)
			{
			    $count = 0;
				if ($row["max"] > 0)
				{
					//how many elements are present?
					for ($i=0; $i<count($this->data["ctrl"]); $i++)
					{
						if ($this->data["ctrl"][$i]["type"] == $row["name"])
						{
						    $count++;
						}
					}
				}
				if ($row["max"] == "" || $count < $row["max"])
				{
					$subobj[] = $row["name"];
				}
			}
		}

		if (is_array($subobj))
		{
			//build form
			$opts = ilUtil::formSelect(12,"new_type",$subobj);
			$this->tpl->setCurrentBlock("add_object");
			$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
			$this->tpl->setVariable("BTN_NAME", "create");
			$this->tpl->setVariable("TXT_ADD", $this->lng->txt("add"));
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* save object
	* @access	public
	*/
	function saveObject()
	{
		global $rbacadmin;

		// role folders are created automatically
		$_GET["new_type"] = $this->object->getType();
		$_POST["Fobject"]["title"] = $this->object->getTitle();
		$_POST["Fobject"]["desc"] = $this->object->getDescription();

		// always call parent method first to create an object_data entry & a reference
		$newObj = parent::saveObject();

		// put here your object specific stuff	

		// always send a message
		sendInfo($this->lng->txt("rolf_added"),true);
		
		header("Location:".$this->getReturnLocation("save","adm_object.php?".$this->link_params));
		exit();
	}
} // END class.ilObjRoleFolderGUI
?>
