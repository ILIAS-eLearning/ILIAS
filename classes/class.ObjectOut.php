<?php
/**
* Class Object
* Basic methods of all Output classes
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$Id: class.ObjectOut.php,v 1.26 2003/02/25 17:36:49 akill Exp $
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
	* Constructor
	* @access	public
	*/
	function ObjectOut($a_data)
	{
		global $ilias, $objDefinition, $tpl, $tree, $lng;
		
		$this->ilias =& $ilias;
		$this->objDefinition =& $objDefinition;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->tree =& $tree;
		$this->data = $a_data;
        
        //prepare output of administration view
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");

		$this->setAdminTabs();
		$this->setLocator();
	}

	/**
	* set admin tabs
	* @access	public
	*/
	function setAdminTabs()
	{
		global $lng;

		$tabs = array();
		$this->tpl->addBlockFile("TABS", "tabs", "tpl.tabs.html");
		$d = $this->objDefinition->getProperties($_GET["type"]);

		foreach ($d as $key => $row)
			$tabs[] = array($row["lng"], $row["name"]);

		foreach ($tabs as $row)
		{
			$i++;
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
			$this->tpl->setVariable("TAB_LINK", "adm_object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&parent_parent=".
							  $_GET["parent_parent"]."&cmd=".$row[1]);
			$this->tpl->setVariable("TAB_TEXT", $lng->txt($row[0]));
			$this->tpl->parseCurrentBlock();
		}	
	}

	function setLocator($a_tree = "", $a_obj_id = "", $a_parent = "", $a_parent_parent = "")
	{
		if (!is_object($a_tree))
		{
			$a_tree =& $this->tree;
		}
		
		if (!($a_obj_id))
		{
			$a_obj_id = $_GET["obj_id"]; 
		}
		
		if (!($a_parent))
		{
			$a_parent = $_GET["parent"]; 
		}

		if (!($a_parent_parent))
		{
			$a_parent_parent = $_GET["parent_parent"]; 
		}

		global $lng;

		$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

		if ($a_parent_parent)
		{
			$path = $a_tree->getPathFull($a_parent, $a_parent_parent);
		}
		else
		{
			$path = $a_tree->getPathFull($a_obj_id, $a_parent);
		}

        //check if object isn't in tree, this is the case if parent_parent is set
		if ($a_parent_parent)
		{
			$subObj = getObject($a_obj_id);

			$path[] = array(
				"id"	 => $a_obj_id,
				"title"  => $this->lng->txt($subObj["title"]),
				"parent" => $a_parent,
				"parent_parent" => $a_parent_parent
				);
		}

		foreach ($path as $key => $row)
		{
			if ($key < count($path)-1)
			{
				$this->tpl->touchBlock("locator_separator");
			}
			$this->tpl->setCurrentBlock("locator_item");
			$this->tpl->setVariable("ITEM", $row["title"]);
			// TODO: SCRIPT NAME HAS TO BE VARIABLE!!!
			$this->tpl->setVariable("LINK_ITEM", "adm_object.php?obj_id=".$row["id"].
							  "&parent=".$row["parent"]."&parent_parent=".$row["parent_parent"]);
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("locator");
		
		if (DEBUG)
		{
			$debug = "DEBUG: <font color=\"red\">".$_GET["type"]."::".$a_obj_id."::".$_GET["cmd"]."</font><br>";
		}
		
		$prop_name = $this->objDefinition->getPropertyName($_GET["cmd"],$_GET["type"]);

		if($_GET["cmd"] == "confirmDeleteAdm")
		{
			$prop_name = "delete_object";
		}

		$this->tpl->setVariable("TXT_PATH",$debug.$lng->txt($prop_name)." ".$lng->txt("of"));
		$this->tpl->parseCurrentBlock();
	}

	/**
	* this methods handles all button actions
	* @access	public
	*/
	function gatewayObject()
	{
		global $lng;

		switch(key($_POST["cmd"]))
		{
			case "delete":
				$this->confirmDeleteAdmObject();
				break;

			case "btn_undelete":
				header("location: adm_object.php?obj_id=".$_GET["obj_id"]."&parent=".
					   $_GET["parent"]."&parent_parent=".$_GET["parent_parent"]."&cmd=trash");
				exit();

			case "btn_remove_system":
				header("location: adm_object.php?obj_id=".$_GET["obj_id"]."&parent=".
					   $_GET["parent"]."&parent_parent=".$_GET["parent_parent"]."&cmd=trash");
				exit();

			default:
				header("location: adm_object.php?obj_id=".$_GET["obj_id"]."&parent=".
					   $_GET["parent"]."&parent_parent=".$_GET["parent_parent"]."&cmd=view");
				exit();
		}
	}

	function createObject()
	{
		$this->getTemplateFile("edit");
		foreach ($this->data["fields"] as $key => $val)
		{
			$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
			$this->tpl->setVariable(strtoupper($key), $val);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setVariable("FORMACTION", "adm_object.php?cmd=save"."&obj_id=".$_GET["obj_id"].
						  "&parent=".$_GET["parent"]."&parent_parent=".$_GET["parent_parent"]."&new_type=".$_POST["new_type"]);
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));

	}
	function saveObject()
	{
		header("Location: adm_object.php?obj_id=".$_GET["obj_id"]."&parent=".
			   $_GET["parent"]."&parent_parent=".$_GET["parent_parent"]."&cmd=view");
		exit();
	}
	function editObject()
	{
		$this->getTemplateFile("edit");
		foreach ($this->data["fields"] as $key => $val)
		{
			$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
			$this->tpl->setVariable(strtoupper($key), $val);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setVariable("FORMACTION", "adm_object.php?&cmd=update&obj_id=".$_GET["obj_id"]."&parent=".
						  $_GET["parent"]."&parent_parent=".$_GET["parent_parent"]);
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
	}

	function updateObject()
	{
		header("Location: adm_object.php?obj_id=".$_GET["obj_id"]."&parent=".
			   $_GET["parent"]."&parent_parent=".$_GET["parent_parent"]."&cmd=view");
		exit();
	}
	function permObject()
	{
		$this->getTemplateFile("perm");
		$this->tpl->setCurrentBlock("tableheader");
		$this->tpl->setVariable("TXT_PERMISSION", $this->lng->txt("permission"));
		$this->tpl->setVariable("TXT_ROLES", $this->lng->txt("roles"));
		$this->tpl->parseCurrentBlock();

		$num = 0;
		foreach($this->data["rolenames"] as $name)
		{
			// BLOCK ROLENAMES
			$this->tpl->setCurrentBlock("ROLENAMES");
			$this->tpl->setVariable("ROLE_NAME",$name);
			$this->tpl->parseCurrentBlock();

			// BLOCK CHECK INHERIT
			$this->tpl->setCurrentBLock("CHECK_INHERIT");
			$this->tpl->setVariable("CHECK_INHERITANCE",$this->data["check_inherit"][$num++]);
			$this->tpl->parseCurrentBlock();
		}
		$num = 0;
		foreach($this->data["permission"] as $ar_perm)
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
		if ($this->data["local_role"] != "")
		{
			// ADD LOCAL ROLE
			$this->tpl->setCurrentBlock("LOCAL_ROLE");
			$this->tpl->setVariable("TXT_ADD", $this->lng->txt("add"));
			$this->tpl->setVariable("MESSAGE_BOTTOM", $this->lng->txt("you_may_add_local_roles"));
			$this->tpl->setVariable("FORMACTION_LR","adm_object.php?cmd=addRole&obj_id=".$_GET["obj_id"]."&parent=".
									$_GET["parent"]."&parent_parent=".$_GET["parent_parent"]);

			$this->tpl->parseCurrentBlock();
		}
		// PARSE BLOCKFILE
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORMACTION","adm_object.php?cmd=permSave&obj_id=".$_GET["obj_id"]."&parent=".
								$_GET["parent"]."&parent_parent=".$_GET["parent_parent"]);
		$this->tpl->parseCurrentBlock();
	}

	function permSaveObject()
	{
		header("Location: adm_object.php?obj_id=".$_GET["obj_id"]."&parent=".
			   $_GET["parent"]."&parent_parent=".$_GET["parent_parent"]."&cmd=perm");
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
	function alterOperationsOnObject()
	{
	}

	function displayList()
	{
		global $tree, $rbacsystem;

	    $this->getTemplateFile("view");
		$num = 0;

		$this->tpl->setVariable("FORMACTION", "adm_object.php?obj_id=".$_GET["obj_id"]."&parent=".
								$_GET["parent"]."&cmd=gateway");

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

			$this->tpl->setVariable("HEADER_TEXT", $out);
			$this->tpl->setVariable("HEADER_LINK", "adm_object.php?obj_id=".$_GET["obj_id"]."&parent=".
							  $_GET["parent"]."&parent_parent=".$_GET["parent_parent"]."&order=type&direction=".
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

				$num++;

				// color changing
				$css_row = TUtil::switchColor($num,"tblrow1","tblrow2");

				// surpress checkbox for particular object types
				if (!$this->objDefinition->hasCheckbox($ctrl["type"]))
				{
					$this->tpl->touchBlock("empty_cell");
				}
				else
				{
					$this->tpl->setCurrentBlock("checkbox");
					$this->tpl->setVariable("CHECKBOX_ID", $ctrl["id"]);
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

					foreach ($ctrl as $key2 => $val2)
					{
						$link .= $key2."=".$val2;
						if ($key2 != $ctrl[count($ctrl)-1][$key2])
						{
					    	$link .= "&";
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
	* list childs of current object
	*/
	function viewObject()
	{
		global $tree, $rbacsystem,$lng;

		//prepare objectlist
		$this->objectList = array();
		$this->data["data"] = array();
		$this->data["ctrl"] = array();
		$this->data["cols"] = array("", "type", "title", "description", "last_change");

		if ($tree->getChilds($_GET["obj_id"], $_GET["order"], $_GET["direction"]))
		{
			foreach ($tree->Childs as $key => $val)
		    {
				// visible
				if (!$rbacsystem->checkAccess("visible",$val["id"],$val["parent"]))
				{
					continue;
				}
				//visible data part
				$this->data["data"][] = array(
					"type" => "<img src=\"".$this->tpl->tplPath."/images/"."icon_".$val["type"].".gif\" border=\"0\">",
					"title" => $val["title"],
					"description" => $val["desc"],
					"last_change" => Format::formatDate($val["last_update"])
				);

				//control information
				$this->data["ctrl"][] = array(
					"type" => $val["type"],
					"obj_id" => $val["id"],
					"parent" => $val["parent"],
					"parent_parent" => $val["parent_parent"]
				);
		    } //foreach
		} //if

		$this->displayList();
	}


	function confirmDeleteAdmObject()
	{

		$this->getTemplateFile("confirm");
		$this->ilias->error_obj->sendInfo($this->lng->txt("info_delete_sure"));
		$this->tpl->setVariable("FORMACTION", "adm_object.php?obj_id=".$_GET["obj_id"]."&parent=".
								$_GET["parent"]."&cmd=gateway");
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

		if($this->data["empty"] == true)
		{
			return;
		}

		$this->ilias->error_obj->sendInfo($this->lng->txt("info_trash"));
		$this->tpl->setVariable("FORMACTION", "adm_object.php?obj_id=".$_GET["obj_id"]."&parent=".
								$_GET["parent"]."&cmd=gateway");
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

		if (count($operations)>0) {
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
			$this->tpl->setVariable("FORMACTION_OBJ_ADD", "adm_object.php?cmd=create&obj_id=".
							  $_GET["obj_id"]."&parent=".$_GET["parent"]."&parent_parent=".$_GET["parent_parent"]);
			$this->tpl->setVariable("TXT_ADD", $this->lng->txt("add"));
			$this->tpl->parseCurrentBlock();
		}
	}

	function getTemplateFile($a_cmd,$a_type = "")
	{
		// <get rid of $_GET variable
		if(!$a_type)
		{
			$a_type = $_GET["type"];
		}

		$template = "tpl.".$a_type."_".$a_cmd.".html";

		if(!$this->tpl->fileExists($template))
		{
			$template = "tpl.obj_".$a_cmd.".html";
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", $template);
	}
}