<?php
/**
* Class Object
* Basic methods of all Output classes
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$Id$
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
		$this->tree = & $tree;
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
	function setLocator()
	{
		global $lng;

		$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

		if ($_GET["parent_parent"] != "")
			$path = $this->tree->getPathFull($_GET["parent"], $_GET["parent_parent"]);
		else
			$path = $this->tree->getPathFull($_GET["obj_id"], $_GET["parent"]);

        //check if object isn't in tree, this is the case if parent_parent is set
		if ($_GET["parent_parent"] != "")
		{
			$path[] = array(
				"id"	 => $_GET["obj_id"],
				"title"  => "TITLE",
				"parent" => $_GET["parent"],
				"parent_parent" => $_GET["parent_parent"]
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
			$this->tpl->setVariable("LINK_ITEM", "adm_object.php?obj_id=".$row["id"].
							  "&parent=".$row["parent"]."&parent_parent=".$row["parent_parent"]);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("locator");
		$this->tpl->setVariable("TXT_PATH", "DEBUG: <font color=\"red\">".$_GET["type"]."::".$methode."</font><br>".$lng->txt("path"));
		$this->tpl->parseCurrentBlock();

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
	function addPermissionObject()
	{
	}

	function viewObject()
	{
		$this->getTemplateFile("view");
		$num = 0;

		//table header
		foreach ($this->data["cols"] as $key)
		{
			$this->tpl->setCurrentBlock("table_header_cell");
			if ($key != "")
			    $out = $this->lng->txt($key);
			else
				$out = "&nbsp;";
			$this->tpl->setVariable("TEXT", $out);
			$this->tpl->setVariable("LINK", "adm_object.php?obj_id=".$_GET["obj_id"]."&parent=".
							  $_GET["parent"]."&parent_parent=".$_GET["parent_parent"]."&order=type&direction=".
							  $_GET["dir"]."&cmd=".$_GET["cmd"]);
			$this->tpl->parseCurrentBlock();
		}
		
		if (is_array($this->data["data"][0]))
		{
			//table cell
			for ($i=0; $i< count($this->data["data"]); $i++)
			{
				$data = $this->data["data"][$i];
				$ctrl = $this->data["ctrl"][$i];
		
				$num++;
		
				// color changing
				$css_row = TUtil::switchColor($num,"tblrow1","tblrow2");
			
				//checkbox
				if ($ctrl["type"] == "adm")
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
				$this->tpl->setVariable("TEXT", "");
				$this->tpl->parseCurrentBlock();
			
				//data
				foreach ($data as $key => $val)
				{
					//build link
					$link = "adm_object.php?";
					
					foreach ($ctrl as $key => $val2)
					{
						$link .= $key."=".$val2;
						if ($key != $ctrl[count($ctrl)-1][$key])
					    	$link .= "&";
					}
					$this->tpl->setCurrentBlock("begin_link");
					$this->tpl->setVariable("LINK_TARGET", $link);
					$this->tpl->parseCurrentBlock();
					$this->tpl->touchBlock("end_link");
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
			$this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
		}

		// SHOW VALID OPERATIONS
		$this->showOperations();

		// SHOW POSSIBLE SUB OBJECTS
		$this->showPossibleSubObjects();
	}

	function showOperations()
	{
		$notoperations = array();
		if (empty($_SESSION["clipboard"]))
		{
			$notoperations[] = "paste";
			$notoperations[] = "clear";
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
				$this->tpl->setVariable("BTN_VALUE", $this->lng->txt($val["lng"]));
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("operation");
			$this->tpl->parseCurrentBlock();
		}		
	}		

	function showPossibleSubObjects()
	{
		$d = $this->objDefinition->getSubObjects($_GET["type"]); // TYPE ALWAYS SET ???????????????????
		foreach ($d as $row)
		{
			//@todo max value abfragen und entsprechend evtl aus der liste streichen
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

	function getTemplateFile($a_cmd,$a_type = '')
	{
		if(!$a_type)
			$a_type = $_GET["type"];

		$template = "tpl.".$a_type."_".$a_cmd.".html";
		if(!$this->tpl->fileExists($template))
		{
			$template = "tpl.obj_".$a_cmd.".html";
		}
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", $template);
	}
}