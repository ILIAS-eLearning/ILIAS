<?php
/**
* Class Object
* Basic methods of all Output classes
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @package ilias-core
*/
class ObjectOut
{
	/**
	* ilias object
	* @var		object ilias
	* @access	private
	*/
	var $ilias;

	/**
	* object Definition Object
	* @var		object ilias
	* @access	private
	*/
	var $objDefinition;

	/**
	* template object
	* @var		object ilias
	* @access	private
	*/
	var $tpl;

	/**
	* tree object
	* @var		object ilias
	* @access	private
	*/
	var $tree;

	/**
	* language object
	* @var		object ilias
	* @access	private
	*/
	var $lng;

	/**
	* output data
	* @var		data array
	* @access	private
	*/
	var $data;

	/**
	* object
	* @var          object
	* @access       private
	*/
	var $object;

	var $ref_id;
	var $obj_id;

	/**
	* Constructor
	* @access	public
	*/
	function ObjectOut($a_data, $a_id, $a_call_by_reference)
	{
		global $ilias, $objDefinition, $tpl, $tree, $lng;
		
		$this->ilias =& $ilias;
		$this->objDefinition =& $objDefinition;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->tree =& $tree;

		$this->data = $a_data;
		$this->id = $a_id;
		$this->call_by_reference = $a_call_by_reference;

		$this->ref_id = $_GET["ref_id"];
		$this->obj_id = $_GET["obj_id"];

		// TODO: id_name is wrong & useless. In case of a given obj_id BOTH ids are needed!!
		if ($this->call_by_reference)
		{
			//$this->id_name = "ref_id";
			$this->link_params = "ref_id=".$this->ref_id."&obj_id=".$this->obj_id;

		}
		else
		{
			//$this->id_name = "obj_id";
			$this->link_params = "ref_id=".$this->ref_id;
		}
	}

	/**
	* prepare output of administration view
	*/
	function prepareOutput()
	{
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$title = $this->object->getTitle();

		if (!empty($title))
		{
			$this->tpl->setVariable("HEADER", $title);
		}

		$this->setAdminTabs();
		$this->setLocator();
	}


	/**
	* read corresponding object
	*
	* @param	string		$obj_class		object class name - working with getclass
	*										would be better, but getclass returns lowercase only :-(
	*/
	function readObject($obj_class)
	{
		require_once("./classes/class.".$obj_class.".php");

		if ($this->call_by_reference)
		{
			$this->object =& new $obj_class($_GET["ref_id"], true);
		}
		else
		{
			$this->object =& new $obj_class($_GET["obj_id"], false);
		}
	}


	/**
	* set admin tabs
	* @access	public
	*/
	function setAdminTabs()
	{
		$tabs = array();
		$this->tpl->addBlockFile("TABS", "tabs", "tpl.tabs.html");
		$d = $this->objDefinition->getProperties($this->type);

		foreach ($d as $key => $row)
		{
			$tabs[] = array($row["lng"], $row["name"]);
		}
		
		if (isset($_GET["obj_id"]))
		{
			$object_link = "&obj_id=".$_GET["obj_id"];
		}

		foreach ($tabs as $row)
		{
			$i++;

			// TODO: get rid of $_GET["cmd"]
			if ($row[1] == $_GET["cmd"])
			{
				$tabtype = "tabactive";
				$tab = $tabtype;
			}
			else
			{
				$tabtype = "tabinactive";
				$tab = "tab";
			}

			$this->tpl->setCurrentBlock("tab");
			$this->tpl->setVariable("TAB_TYPE", $tabtype);
			$this->tpl->setVariable("TAB_TYPE2", $tab);
			$this->tpl->setVariable("TAB_LINK", "adm_object.php?ref_id=".$_GET["ref_id"].$object_link."&cmd=".$row[1]);
			$this->tpl->setVariable("TAB_TEXT", $this->lng->txt($row[0]));
			$this->tpl->parseCurrentBlock();
		}	
	}

	function setLocator($a_tree = "", $a_id = "")
	{
		if (!is_object($a_tree))
		{
			$a_tree =& $this->tree;
		}
		
		if (!($a_id))
		{
			$a_id = $_GET["ref_id"]; 
		}

		$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

		$path = $a_tree->getPathFull($a_id);

        //check if object isn't in tree, this is the case if parent_parent is set
		// TODO: parent_parent no longer exist. need another marker
		if ($a_parent_parent)
		{
			$subObj = getObject($a_ref_id);

			$path[] = array(
				"id"	 => $a_ref_id,
				"title"  => $this->lng->txt($subObj["title"])
				);
		}

		// this is a stupid workaround for a bug in PEAR:IT
		$modifier = 1;

		if (isset($_GET["obj_id"]))
		{
			$modifier = 0;
		}

		foreach ($path as $key => $row)
		{
			if ($key < count($path)-$modifier)
			{
				$this->tpl->touchBlock("locator_separator");
			}

			$this->tpl->setCurrentBlock("locator_item");
			$this->tpl->setVariable("ITEM", $row["title"]);
			// TODO: SCRIPT NAME HAS TO BE VARIABLE!!!
			$this->tpl->setVariable("LINK_ITEM", "adm_object.php?ref_id=".$row["child"]);
			$this->tpl->parseCurrentBlock();
			
		}
		
		if (isset($_GET["obj_id"]))
		{
			$obj_data = getObject($_GET["obj_id"]);

			$this->tpl->setCurrentBlock("locator_item");
			$this->tpl->setVariable("ITEM", $obj_data["title"]);
			// TODO: SCRIPT NAME HAS TO BE VARIABLE!!!
			$this->tpl->setVariable("LINK_ITEM", "adm_object.php?ref_id=".$row["ref_id"]."&obj_id=".$_GET["obj_id"]);
			$this->tpl->parseCurrentBlock();		
		}

		$this->tpl->setCurrentBlock("locator");

		if (DEBUG)
		{
			$debug = "DEBUG: <font color=\"red\">".$this->type."::".$this->id."::".$_GET["cmd"]."</font><br/>";
		}
		
		$prop_name = $this->objDefinition->getPropertyName($_GET["cmd"],$this->type);

		if ($_GET["cmd"] == "confirmDeleteAdm")
		{
			$prop_name = "delete_object";
		}

		$this->tpl->setVariable("TXT_PATH",$debug.$this->lng->txt($prop_name)." ".strtolower($this->lng->txt("of")));
		$this->tpl->parseCurrentBlock();
	}

	/**
	* gateway for all button actions
	* @access	public
	*/
	function gatewayObject()
	{
		require_once ("classes/class.Admin.php");

		$admin = new Admin();

		switch(key($_POST["cmd"]))
		{
			case "cut":
				$this->data = $admin->cutObject($_POST["id"],$_POST["cmd"],$_GET["obj_id"]);
				break;

			case "copy":
				$this->data = $admin->copyObject($_POST["id"],$_POST["cmd"],$_GET["obj_id"]);
				break;

			case "link":
				$this->data = $admin->linkObject($_POST["id"],$_POST["cmd"],$_GET["obj_id"]);
				break;

			case "paste":
				$this->data = $admin->pasteObject($_GET["obj_id"],$_GET["parent"]);
				break;

			case "clear":
				$this->data = $admin->clearObject();
				break;

			case "delete":
				$this->confirmDeleteAdmObject();
				break;

			case "btn_undelete":
				$this->data = $admin->undeleteObject($_POST["trash_id"],$_GET["obj_id"],$_GET["parent"]);
				header("location: adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=trash");
				exit();
				break;

			case "btn_remove_system":
				$this->data = $admin->removeObject($_POST["trash_id"],$_GET["obj_id"],$_GET["parent"]);
				header("location: adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=trash");
				exit();
				break;

			case "cancel":
				session_unregister("saved_post");
				break;

			case "confirm":
				$this->data = $admin->deleteObject($_SESSION["saved_post"],$_GET["obj_id"],$_GET["parent"]);
				break;

			default:
				$this->data = false;
		}

		if (key($_POST["cmd"]) != "delete")
		{
			header("location: adm_object.php?ref_id=".$_GET["ref_id"]);
			exit();
		}
	}


	/**
	* create new object form
	*/
	function createObject()
	{
		// creates a child object
		global $rbacsystem;

		// TODO: get rid of $_GET variable
		if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], $_POST["new_type"]))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		else
		{
			$data = array();
			$data["fields"] = array();
			$data["fields"]["title"] = "";
			$data["fields"]["desc"] = "";

			$this->getTemplateFile("edit");

			foreach ($data["fields"] as $key => $val)
			{
				$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
				$this->tpl->setVariable(strtoupper($key), $val);
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setVariable("FORMACTION", "adm_object.php?cmd=save"."&ref_id=".$_GET["ref_id"].
				"&new_type=".$_POST["new_type"]);
			$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
			$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		}
	}


	/**
	* save object
	*/
	function saveObject()
	{
		global $rbacsystem, $rbacreview, $rbacadmin, $tree, $objDefinition;

		if ($rbacsystem->checkAccess("create", $_GET["ref_id"], $_GET["new_type"]))
		{
			// create and insert object in objecttree
			$class_name = $objDefinition->getClassName($_GET["new_type"])."Object";
			$newObj = new $class_name();
			$newObj->setType($_GET["new_type"]);
			$newObj->setTitle($_POST["Fobject"]["title"]);
			$newObj->setDescription($_POST["Fobject"]["desc"]);
			$newObj->create();
			$newObj->createReference();
			$newObj->putInTree($_GET["ref_id"]);

			unset($newObj);
		}
		else
		{
			$this->ilias->raiseError("No permission to create object", $this->ilias->error_obj->WARNING);
		}

		header("Location: adm_object.php?".$this->link_params);
		exit();
	}


	/**
	* edit object
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
			$fields = array();
			$fields["title"] = $this->object->getTitle();
			$fields["desc"] = $this->object->getDescription();
			$this->displayEditForm($fields);
		}
	}


	/**
	* display edit form (usually called by editObject)
	*
	* @access	private
	* @param	array	$fields		key/value pairs of input fields
	*/
	function displayEditForm($fields)
	{
		$this->getTemplateFile("edit");
		foreach ($fields as $key => $val)
		{
			$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
			$this->tpl->setVariable(strtoupper($key), $val);
			$this->tpl->parseCurrentBlock();
		}
		$obj_str = ($this->call_by_reference) ? "" : "&obj_id=".$this->obj_id;
		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$this->ref_id."$obj_str&cmd=update");
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
	}


	/**
	* update object in db
	*/
	function updateObject()
	{
		global $rbacsystem;

		if ($rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->object->setTitle($_POST["Fobject"]["title"]);
			$this->object->setDescription($_POST["Fobject"]["desc"]);
			$this->update = $this->object->update();
		}
		else
		{
			$this->ilias->raiseError("No permission to edit the object",$this->ilias->error_obj->WARNING);
		}

		header("Location: adm_object.php?ref_id=".$this->ref_id);
		exit();
	}

	/**
	* show permissions of current node
	*/
	function permObject()
	{
		global $lng, $rbacsystem, $rbacreview, $rbacadmin;
		static $num = 0;

		$obj = getObjectByReference($this->object->getRefId());

		if ($rbacsystem->checkAccess("edit permission", $this->object->getRefId()))
		{
			// Es werden nur die Rollen übergeordneter Ordner angezeigt, lokale Rollen anderer Zweige nicht
			$parentRoles = $rbacadmin->getParentRoleIds($this->object->getRefId());
			$data = array();

			foreach ($parentRoles as $r)
			{
				// GET ALL LOCAL ROLE IDS
				$role_folders = $rbacadmin->getRoleFolderOfObject($this->object->getRefId());
				$local_roles = $rbacadmin->getRolesAssignedToFolder($role_folders["child"]);
				$data["rolenames"][] = $r["title"];

				if(!in_array($r["obj_id"],$local_roles))
				{
					$data["check_inherit"][] = TUtil::formCheckBox(0,"stop_inherit[]",$r["obj_id"]);
				}
				else
				{
					$data["check_inherit"][] = TUtil::formCheckBox(1,"stop_inherit[]",$r["obj_id"]);
				}
			}

			$ope_list = getOperationList($this->object->getType());
			// BEGIN TABLE_DATA_OUTER
			foreach ($ope_list as $key => $operation)
			{
				$opdata = array();
				$opdata["name"] = $operation["operation"];

				foreach ($parentRoles as $role)
				{
					$checked = $rbacsystem->checkPermission($this->object->getRefId(), $role["obj_id"],$operation["operation"],$_GET["parent"]);
					// Es wird eine 2-dim Post Variable übergeben: perm[rol_id][ops_id]
					$box = TUtil::formCheckBox($checked,"perm[".$role["obj_id"]."][]",$operation["ops_id"]);
					$opdata["values"][] = $box;
				}
				$data["permission"][] = $opdata;
			}
		}
		else
		{
			$this->ilias->raiseError("No permission to change permissions",$this->ilias->error_obj->WARNING);
		}
		$rolf_data = $rbacadmin->getRoleFolderOfObject($this->object->getRefId());
		$permission = $rolf_data ? 'write' : 'create';
		$rolf_id = $rolf_data["obj_id"] ? $rolf_data["obj_id"] : $this->object->getRefId();
		$rolf_parent = $role_data["parent"] ? $rolf_data["parent"] : $_GET["parent"];

		if ($rbacsystem->checkAccess("edit permission", $this->object->getRefId()) &&
		   $rbacsystem->checkAccess($permission, $rolf_id, "rolf"))
		{
			// Check if object is able to contain role folder
			$child_objects = $rbacadmin->getModules($this->object->getType(), $this->object->getRefId());

			if ($child_objects["rolf"])
			{
				$data["local_role"]["child"] = $this->object->getRefId();
				$data["local_role"]["parent"] = $_GET["parent"];
			}
		}

		// output data
		$this->getTemplateFile("perm");
		$this->tpl->setCurrentBlock("tableheader");
		$this->tpl->setVariable("TXT_PERMISSION", $this->lng->txt("permission"));
		$this->tpl->setVariable("TXT_ROLES", $this->lng->txt("roles"));
		$this->tpl->parseCurrentBlock();

		$num = 0;

		foreach($data["rolenames"] as $name)
		{
			// BLOCK ROLENAMES
			$this->tpl->setCurrentBlock("ROLENAMES");
			$this->tpl->setVariable("ROLE_NAME",$name);
			$this->tpl->parseCurrentBlock();

			// BLOCK CHECK INHERIT
			$this->tpl->setCurrentBLock("CHECK_INHERIT");
			$this->tpl->setVariable("CHECK_INHERITANCE",$data["check_inherit"][$num++]);
			$this->tpl->parseCurrentBlock();
		}
		$num = 0;

		foreach($data["permission"] as $ar_perm)
		{
			foreach ($ar_perm["values"] as $box)
			{
				// BEGIN TABLE CHECK PERM
				$this->tpl->setCurrentBlock("CHECK_PERM");
				$this->tpl->setVariable("CHECK_PERMISSION",$box);
				$this->tpl->parseCurrentBlock();
				// END CHECK PERM
			}

			// BEGIN TABLE DATA OUTER
			$this->tpl->setCurrentBlock("TABLE_DATA_OUTER");
			$css_row = TUtil::switchColor($num++, "tblrow1", "tblrow2");
			$this->tpl->setVariable("CSS_ROW",$css_row);
			$this->tpl->setVariable("PERMISSION", $ar_perm["name"]);
			$this->tpl->parseCurrentBlock();
			// END TABLE DATA OUTER
		}
		if ($data["local_role"] != "")
		{
			// ADD LOCAL ROLE
			$this->tpl->setCurrentBlock("LOCAL_ROLE");
			$this->tpl->setVariable("TXT_ADD", $this->lng->txt("add"));
			$this->tpl->setVariable("MESSAGE_BOTTOM", $this->lng->txt("you_may_add_local_roles"));
			$this->tpl->setVariable("FORMACTION_LR","adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=addRole");

			$this->tpl->parseCurrentBlock();
		}
		// PARSE BLOCKFILE
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORMACTION","adm_object.php?".$this->link_params."&cmd=perm");
		$this->tpl->parseCurrentBlock();
	}

	
	/**
	* save permissions
	*/
	function permSaveObject()
	{
		global $tree,$rbacsystem,$rbacreview,$rbacadmin;

		// TODO: get rid of $_GET variables

		if ($rbacsystem->checkAccess('edit permission',$_GET["ref_id"]))
		{
			$rbacadmin->revokePermission($_GET["ref_id"]);

			foreach ($_POST["perm"] as $key => $new_role_perms)
			{
				// $key enthaelt die aktuelle Role_Id
				$rbacadmin->grantPermission($key,$new_role_perms, $_GET["ref_id"]);
			}
		}
		else
		{
			$this->ilias->raiseError("No permission to change permission",$this->ilias->error_obj->WARNING);
		}
		// Wenn die Vererbung der Rollen Templates unterbrochen werden soll,
		// muss folgendes geschehen:
		// - existiert kein RoleFolder, wird er angelegt und die Rechte aus den Permission Templates ausgelesen
		// - existiert die Rolle im aktuellen RoleFolder werden die Permission Templates dieser Rolle angezeigt
		// - existiert die Rolle nicht im aktuellen RoleFolder wird sie dort angelegt
		//   und das Permission Template an den Wert des nächst höher gelegenen Permission Templates angepasst

		if ($_POST["stop_inherit"])
		{
			foreach ($_POST["stop_inherit"] as $stop_inherit)
			{
				$rolf_data = $rbacadmin->getRoleFolderOfObject($_GET["ref_id"]);
				if (!($rolf_id = $rolf_data["child"]))
				{
					// CHECK ACCESS 'create' rolefolder
					if ($rbacsystem->checkAccess('create', $_GET["ref_id"],'rolf'))
					{
						require_once ("classes/class.RoleFolderObject.php");
						$rolfObj = new RoleFolderObject();
						$rolfObj->setTitle("Local roles");
						$rolfObj->setDescription("Role Folder of object no. ".$_GET["ref_id"]);
						$rolfObj->create();
						$rolfObj->createReference();
						$rolfObj->putInTree($_GET["ref_id"]);
						unset($rolfObj);
					}
					else
					{
						$this->ilias->raiseError("No permission to create Role Folder",$this->ilias->error_obj->WARNING);
					}
				}
				// CHECK ACCESS 'write' of role folder
				$rolf_data = $rbacadmin->getRoleFolderOfObject($_GET["ref_id"]);
				if ($rbacsystem->checkAccess('write',$rolf_data["child"]))
				{
					$parentRoles = $rbacadmin->getParentRoleIds();
					$rbacadmin->copyRolePermission($stop_inherit,$parentRoles[$stop_inherit]["parent"],
												   $rolf_data["child"],$stop_inherit);
					$rbacadmin->assignRoleToFolder($stop_inherit,$rolf_data["child"],$_GET["ref_id"],'n');
				}
				else
				{
					$this->ilias->raiseError("No permission to write to role folder",$this->ilias->error_obj->WARNING);
				}
			}// END FOREACH
		}// END STOP INHERIT
	
		header("Location: adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=perm");
		exit();
	}


	function addRoleObject()
	{
	}

	function ownerObject()
	{
		$this->getTemplateFile("owner");
		$this->tpl->setVariable("OWNER_NAME", $this->data);
		$this->tpl->setVariable("TXT_OBJ_OWNER", $this->lng->txt("obj_owner"));
		$this->tpl->setVariable("CMD","update");
		$this->tpl->parseCurrentBlock();
	}


	/**
	* display object list
	*/
	function displayList()
	{
		global $tree, $rbacsystem;

	    $this->getTemplateFile("view");
		$num = 0;

		$obj_str = ($this->call_by_reference) ? "" : "&obj_id=".$this->obj_id;
		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$this->ref_id."$obj_str&cmd=gateway");

		//table header
		$this->tpl->setCurrentBlock("table_header_cell");

		foreach ($this->data["cols"] as $key)
		{
			if ($key != "")
			{
			    $out = $this->lng->txt($key);
			}
			else
			{
				$out = "&nbsp;";
			}
			$num++;

			$this->tpl->setVariable("HEADER_TEXT", $out);
			$this->tpl->setVariable("HEADER_LINK", "adm_object.php?ref_id=".$_GET["ref_id"]."&order=type&direction=".
							  $_GET["dir"]."&cmd=".$_GET["cmd"]);

			$this->tpl->parseCurrentBlock();
		}

		if (is_array($this->data["data"][0]))
		{
			//table cell
			for ($i=0; $i < count($this->data["data"]); $i++)
			{
				$data = $this->data["data"][$i];
				$ctrl = $this->data["ctrl"][$i];

				// color changing
				$css_row = TUtil::switchColor($i+1,"tblrow1","tblrow2");

				// surpress checkbox for particular object types
				if (!$this->objDefinition->hasCheckbox($ctrl["type"]))
				{
					$this->tpl->touchBlock("empty_cell");
				}
				else
				{
					$this->tpl->setCurrentBlock("checkbox");
					$this->tpl->setVariable("CHECKBOX_ID", $ctrl["ref_id"]);
					$this->tpl->setVariable("CSS_ROW", $css_row);
					$this->tpl->parseCurrentBlock();
				}

				$this->tpl->setCurrentBlock("table_cell");
				$this->tpl->parseCurrentBlock();

				foreach ($data as $key => $val)
				{
					//build link
					$link = "adm_object.php?";

					if ($_GET["type"] == "lo" && $key == "type")
					{
						$link = "lo_view.php?";
					}

					$n = 0;

					foreach ($ctrl as $key2 => $val2)
					{
						$link .= $key2."=".$val2;

						if ($n < count($ctrl)-1)
						{
					    	$link .= "&";
							$n++;
						}
					}

					if ($key == "title" || $key == "type")
					{
						$this->tpl->setCurrentBlock("begin_link");
						$this->tpl->setVariable("LINK_TARGET", $link);

						if ($_GET["type"] == "lo" && $key == "type")
						{
							$this->tpl->setVariable("NEW_TARGET", "\" target=\"lo_view\"");
						}

						$this->tpl->parseCurrentBlock();
						$this->tpl->touchBlock("end_link");
					}

			// TODO: this loop is for marking objects 'cut' or 'copied'.
			// the array structure if clipboard must be change!!! 
					if (isset($_SESSION["clipboard"]))
					{
						foreach ($_SESSION["clipboard"] as $clip_id => $clip)
						{
							if ($ctrl["ref_id"] == $clip_id)
							{
								if ($clip["cmd"]["cut"] and $key == "title")
								{
									$val = "<del>".$val."</del>";
								}
								
								if ($clip["cmd"]["copy"] and $key == "title")
								{
									$val = "<font color=\"green\">+</font>  ".$val;
								}
							}
						}
					}

					$this->tpl->setCurrentBlock("text");
					$this->tpl->setVariable("TEXT_CONTENT", $val);
					$this->tpl->parseCurrentBlock();
					$this->tpl->setCurrentBlock("table_cell");
					$this->tpl->parseCurrentBlock();

				} //foreach

				$this->tpl->setCurrentBlock("table_row");
				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->parseCurrentBlock();
			} //for
		} //if is_array
		else
		{
			$this->tpl->setCurrentBlock("notfound");
			$this->tpl->setVariable("NUM_COLS", $num);
			$this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
		}

		// SHOW VALID ACTIONS
		$this->showActions();

		// SHOW POSSIBLE SUB OBJECTS
		$this->showPossibleSubObjects();
	}

	/**
	* list childs of current object"
	*/
	function viewObject()
	{
		global $tree,$rbacsystem,$lng;

		//prepare objectlist
		$this->objectList = array();
		$this->data["data"] = array();
		$this->data["ctrl"] = array();
		$this->data["cols"] = array("", "type", "title", "description", "last_change");

		$childs = $tree->getChilds($_GET["ref_id"], $_GET["order"], $_GET["direction"]);

		foreach ($childs as $key => $val)
	    {
			// visible
			if (!$rbacsystem->checkAccess("visible",$val["ref_id"]))
			{
				continue;
			}
			//visible data part
			$this->data["data"][] = array(
				"type" => "<img src=\"".$this->tpl->tplPath."/images/"."icon_".$val["type"]."_b.gif\" border=\"0\">",
				"title" => $val["title"],
				"description" => $val["desc"],
				"last_change" => Format::formatDate($val["last_update"])
			);
			//control information
			$this->data["ctrl"][] = array(
				"type" => $val["type"],
				"ref_id" => $val["ref_id"]
			);
	    } //foreach

		$this->displayList();
	}

	function confirmDeleteAdmObject()
	{
		global $lng;

		if(!isset($_POST["id"]))
		{
			$this->ilias->raiseError($lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}
		// SAVE POST VALUES
		$_SESSION["saved_post"] = $_POST["id"];

		unset($this->data);
		$this->data["cols"] = array("type", "title", "description", "last_change");

		foreach($_POST["id"] as $id)
		{
			$obj_data = getObject($id);
			$this->data["data"]["$id"] = array(
				"type"        => $obj_data["type"],
				"title"       => $obj_data["title"],
				"desc"        => $obj_data["desc"],
				"last_update" => $obj_data["last_update"]);
		}
		$this->data["buttons"] = array( "cancel"  => $lng->txt("cancel"),
								  "confirm"  => $lng->txt("confirm"));

		$this->getTemplateFile("confirm");
		$this->ilias->error_obj->sendInfo($this->lng->txt("info_delete_sure"));
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

		foreach($this->data["data"] as $key => $value)
		{
			// BEGIN TABLE CELL
			foreach($value as $key => $cell_data)
			{
				$this->tpl->setCurrentBlock("table_cell");

				// CREATE TEXT STRING
				if($key == "type")
				{
					$this->tpl->setVariable("TEXT_CONTENT",TUtil::getImageTagByType($cell_data,$this->tpl->tplPath));
				}
				else
				{
					$this->tpl->setVariable("TEXT_CONTENT",$cell_data);
				}
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("table_row");
			$this->tpl->setVariable("CSS_ROW",TUtil::switchColor(++$counter,"tblrow1","tblrow2"));
			$this->tpl->parseCurrentBlock();
			// END TABLE CELL
		}
		// END TABLE DATA

		// BEGIN OPERATION_BTN
		foreach($this->data["buttons"] as $name => $value)
		{
			$this->tpl->setCurrentBlock("operation_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}
	}

	function trashObject()
	{
		$this->getTemplateFile("confirm");

		if ($this->data["empty"] == true)
		{
			return;
		}

		$this->ilias->error_obj->sendInfo($this->lng->txt("info_trash"));
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

		foreach($this->data["data"] as $key1 => $value)
		{
			// BEGIN TABLE CELL
			foreach($value as $key2 => $cell_data)
			{
				$this->tpl->setCurrentBlock("table_cell");
				// CREATE CHECKBOX
				if($key2 == "checkbox")
				{
					$this->tpl->setVariable("TEXT_CONTENT",TUtil::formCheckBox(0,"trash_id[]",$key1));
				}

				// CREATE TEXT STRING
				elseif($key2 == "type")
				{
					$this->tpl->setVariable("TEXT_CONTENT",TUtil::getImageTagByType($cell_data,$this->tpl->tplPath));
				}
				else
				{
					$this->tpl->setVariable("TEXT_CONTENT",$cell_data);
				}

				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("table_row");
			$this->tpl->setVariable("CSS_ROW",TUtil::switchColor(++$counter,"tblrow1","tblrow2"));
			$this->tpl->parseCurrentBlock();
			// END TABLE CELL
		}
		// END TABLE DATA

		// BEGIN OPERATION_BTN
		foreach($this->data["buttons"] as $name => $value)
		{
			$this->tpl->setCurrentBlock("operation_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}
	}

	function showActions()
	{
		$notoperations = array();
		// NO PASTE AND CLEAR IF CLIPBOARD IS EMPTY
		if (empty($_SESSION["clipboard"]))
		{
			$notoperations[] = "paste";
			$notoperations[] = "clear";
		}
		// CUT COPY PASTE LINK DELETE IS NOT POSSIBLE IF CLIPBOARD IS FILLED
		if ($_SESSION["clipboard"])
		{
			$notoperations[] = "cut";
			$notoperations[] = "copy";
			$notoperations[] = "link";
		}

		$operations = array();

		$d = $this->objDefinition->getActions($_GET["type"]);

		foreach ($d as $row)
		{
			if (!in_array($row["name"], $notoperations))
			{
				$operations[] = $row;
			}
		}

		if (count($operations)>0)
		{
			foreach ($operations as $val)
			{
				$this->tpl->setCurrentBlock("operation_btn");
				$this->tpl->setVariable("BTN_NAME", $val["lng"]);
				$this->tpl->setVariable("BTN_VALUE", $this->lng->txt($val["lng"]));
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("operation");
			$this->tpl->parseCurrentBlock();
		}
	}

	function showPossibleSubObjects()
	{
		$d = $this->objDefinition->getSubObjects($_GET["type"]);

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
			$opts = TUtil::formSelect(12,"new_type",$subobj);

			$this->tpl->setCurrentBlock("add_obj");
			$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
			$this->tpl->setVariable("FORMACTION_OBJ_ADD", "adm_object.php?cmd=create&ref_id=".$_GET["ref_id"]);
			$this->tpl->setVariable("TXT_ADD", $this->lng->txt("add"));
			$this->tpl->parseCurrentBlock();
		}
	}

	function getTemplateFile($a_cmd,$a_type = "")
	{
		// <get rid of $_GET variable
		if (!$a_type)
		{
			$a_type = $_GET["type"];
		}

		$template = "tpl.".$a_type."_".$a_cmd.".html";

		if (!$this->tpl->fileExists($template))
		{
			$template = "tpl.obj_".$a_cmd.".html";
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", $template);
	}
}

