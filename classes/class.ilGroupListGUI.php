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

require_once "./classes/class.ilTableGUI.php";
require_once "./classes/class.ilGroupGUI.php";

/**
* Course and Learning Module List GUI Class
*
* @author Peter Gabriel <pgabriel@databay.de>
* @author Sascha Hofmann <shofmann@databay.de>
* @author Alex Killing <alex.killing@gmx.de>
*
* @version $Id$
*
* @package ilias
*/
class ilGroupListGUI
{
	var $tpl;
	var $lng;
	var $objDefinition;
	var $tree;
	var $rbacsystem;
	var $ilias;

	function ilGroupListGUI()
	{
		global $objDefinition, $tpl, $lng, $tree, $rbacsystem, $ilias;

		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->ilias =& $ilias;
		$this->objDefinition =& $objDefinition;
		$this->tree =& $tree;
		$this->rbacsystem = $rbacsystem;
		
		
		
		$cmd = $_GET["cmd"];

		if ($cmd == "")
		{
			$cmd = "view";
		}

		if ($cmd == "post")
		{
			if (isset($_POST["cmd"]["action"]))
			{
				$cmd = $_POST["action_type"];
			}
			else
			{
				$cmd = key($_POST["cmd"]);
			}
		}
		$this->$cmd();
	}

	/**
	* calls current view mode (tree frame or list)
	*/
	function view()
	{
		if (isset($_GET["viewmode"]))
		{
			$_SESSION["viewmode"] = $_GET["viewmode"];
		}

		// tree frame
		if ($_SESSION["viewmode"] == "tree")
		{
			$this->tpl = new ilTemplate("tpl.grp_list.html", false, false);
			$this->tpl->setVariable ("EXP", "grp_list.php?cmd=explorer&expand=1");
			$this->tpl->setVariable ("SOURCE", "grp_list.php?cmd=displayList&ref_id=".$_GET["ref_id"]);
			$this->tpl->show();
		}
		else	// list
		{
			$this->displayList();
		}
	}

	/**
	* output explorer menu
	*/
	function explorer()
	{
		require_once "./classes/class.ilExplorer.php";
		$exp = new ilExplorer("grp_list.php?cmd=displayList");

		$this->tpl->addBlockFile("CONTENT", "content", "tpl.explorer.html");
		if ($_GET["expand"] == "")
		{
			$expanded = "1";
		}
		else
			$expanded = $_GET["expand"];

		$exp->setExpand($expanded);
		$exp->setExpandTarget("grp_list.php?cmd=explorer");
		//filter object types
		$exp->addFilter("root");
		$exp->addFilter("cat");
		//$exp->addFilter("grp");
		//$exp->addFilter("crs");
		//$exp->addFilter("le");
		$exp->setFiltered(true);

		//build html-output
		$exp->setOutput(0);
		$output = $exp->getOutput();
		$this->tpl->setCurrentBlock("content");
		$this->tpl->setVariable("TXT_EXPLORER_HEADER", $this->lng->txt("groups"));
		$this->tpl->setVariable("EXPLORER",$output);
		$this->tpl->setVariable("ACTION", "grp_list.php?cmd=explorer&expand=".$_GET["expand"]);
		$this->tpl->parseCurrentBlock();

		$this->tpl->show();
	}
	
	/**
	* displays list of groups that are located under the node given by ref_id
	*/
	function displayList()
	{

		global  $tree, $rbacsystem;

		require_once "./include/inc.header.php";
		require_once "./classes/class.ilExplorer.php";
		require_once "./classes/class.ilTableGUI.php";

		$this->tpl->addBlockFile("CONTENT", "content", "tpl.group_basic.html");
		//$title = $this->object->getTitle();
		//$locatorscript = "group.php?cmd=choose_view&";
		$locatorscript = "grp_list.php?&cmd=displayList&viewmode=".$_SESSION["viewmode"]."&";
		infoPanel();
		sendInfo();
		$this->setAdminTabs(true, "");
		$this->setLocator();

		$this->tpl->setVariable("HEADER",  $this->lng->txt("groups_overview"));

		// set offset & limit
		$offset = intval($_GET["offset"]);
		$limit = intval($_GET["limit"]);

		if ($limit == 0)
		{
			$limit = 10;	// TODO: move to user settings
		}

		if ($offset == "")
		{

			$offset = 0;	// TODO: move to user settings
		}
		// set default sort column
		if (empty($_GET["sort_by"]))
		{
			$_GET["sort_by"] = "title";
		}

		if (!isset($_SESSION["viewmode"]))
		{
			$_SESSION["viewmode"] = "flat";
		}

		$this->tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");
		$obj_data =& $this->ilias->obj_factory->getInstanceByRefId($_GET["ref_id"]);

		//check if user got permission to create new groups

		//TODO
		//if($rbacsystem->checkAccess("write",$this->object->getRefId() ))
		//{
			//show "new group" button only if category or dlib objects were chosen(current object)
			//if(strcmp($obj_data->getType(), "cat") == 0 || strcmp($obj_data->getType(), "dlib") == 0)
			//{
				//var_dump($_GET); echo "----";var_dump($_POST);
				$this->tpl->setCurrentBlock("btn_cell");
				//right solution
				$this->tpl->setVariable("BTN_LINK","obj_location_new.php?new_type=grp&from=grp_list.php");
				$this->tpl->setVariable("BTN_TARGET","target=\"bottom\"");
				$this->tpl->setVariable("BTN_TXT", $this->lng->txt("grp_new"));
				//temp.solution
				//$this->tpl->setVariable("BTN_LINK","group.php?cmd=create&parent_ref_id=".$_GET["ref_id"]."&type=grp&ref_id=".$_GET["ref_id"]);
				//$this->tpl->setVariable("BTN_TXT", $this->lng->txt("grp_new"));
				$this->tpl->parseCurrentBlock();
			//}
		//}


/*		if ($this->tree->getSavedNodeData($this->ref_id))
		{
			$this->tpl->setCurrentBlock("btn_cell");
			$this->tpl->setVariable("BTN_LINK","group.php?cmd=trash&ref_id=".$_GET["ref_id"]);
			$this->tpl->setVariable("BTN_TXT", $this->lng->txt("trash"));
			$this->tpl->parseCurrentBlock();
		}
*/
		// display different content depending on viewmode
		switch ($_SESSION["viewmode"])
		{
			case "flat":
				$cont_arr = ilUtil::getObjectsByOperations('grp','visible');
				break;

			case "tree":
				//go through valid objects and filter out the groups only
				$cont_arr = array();
				
				$objects = $tree->getChilds($_GET["ref_id"],"title");
				
				if (count($objects) > 0)
				{
					foreach ($objects as $key => $object)
					{
						if ($object["type"] == "grp" && $rbacsystem->checkAccess('visible',$object["child"]))
						{
							$cont_arr[$key] = $object;
						}
					}
				}
				break;
		}

		$maxcount = count($cont_arr);

		include_once "./include/inc.sort.php";
		$cont_arr = sortArray($cont_arr,$_GET["sort_by"],$_GET["sort_order"]);
		$cont_arr = array_slice($cont_arr,$offset,$limit);

		// load template for table
		$this->tpl->addBlockfile("CONTENT", "group_table", "tpl.table.html");
		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.grp_tbl_rows.html");
		$cont_num = count($cont_arr);

		// render table content data
		if ($cont_num > 0)
		{
			// counter for rowcolor change
			$num = 0;
			foreach ($cont_arr as $cont_data)
			{
				$this->tpl->setCurrentBlock("tbl_content");
				$newuser = new ilObjUser($cont_data["owner"]);
				// change row color
				$this->tpl->setVariable("ROWCOL", ilUtil::switchColor($num,"tblrow2","tblrow1"));
				$num++;
				//$obj_link = "group.php?cmd=show_content&ref_id=".$cont_data["ref_id"]."&tree_id=".$cont_data["obj_id"]."&obj_id=".$cont_data["obj_id"];
//				$obj_link = "group.php?cmd=view&ref_id=".$cont_data["ref_id"];
				//setting the group view mode
				if(isset($_SESSION["grp_viewmode"])) 
					$grp_view = $_SESSION["grp_viewmode"];
				else 
					$grp_view="tree"; //default grp_viewmode is tree-view
				$obj_link = "group.php?cmd=view&grp_viewmode=$grp_view&ref_id=".$cont_data["ref_id"];
				$obj_icon = "icon_".$cont_data["type"]."_b.gif";
				$this->tpl->setVariable("TITLE", $cont_data["title"]);
				$this->tpl->setVariable("LINK", $obj_link);
				$this->tpl->setVariable("LINK_TARGET", "bottom");
				$this->tpl->setVariable("IMG", $obj_icon);
				$this->tpl->setVariable("ALT_IMG", $this->lng->txt("obj_".$cont_data["type"]));
				$this->tpl->setVariable("DESCRIPTION", $cont_data["description"]);
				$this->tpl->setVariable("OWNER", $newuser->getFullName($cont_data["owner"]));
				$this->tpl->setVariable("LAST_CHANGE", $cont_data["last_update"]);
				$this->tpl->setVariable("CONTEXTPATH", $this->getContextPath($cont_data["ref_id"]));
				$this->tpl->parseCurrentBlock();
			}
		}
		else
		{
			$this->tpl->setCurrentBlock("no_content");
			$this->tpl->setVariable("TXT_MSG_NO_CONTENT",$this->lng->txt("group_not_available"));
			$this->tpl->parseCurrentBlock("no_content");
		}

		// create table
		$tbl = new ilTableGUI();

		// title & header columns
		$tbl->setTitle($this->lng->txt("groups_overview"),"icon_grp_b.gif",$this->lng->txt("groups_overview"));
		$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		$tbl->setHeaderNames(array($this->lng->txt("title"),$this->lng->txt("description"),$this->lng->txt("owner"),$this->lng->txt("last_change"),$this->lng->txt("context")));
		$tbl->setHeaderVars(array("title","description","owner","last_change","context"), array("cmd"=>"DisplayList", "ref_id"=>$_GET["ref_id"]));
		$tbl->setColumnWidth(array("7%","10%","15%","15%","22%"));
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($limit);
		$tbl->setOffset($offset);
		$tbl->setMaxCount($maxcount);

		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));

		// render table
		$tbl->render();
		$this->tpl->show();
	}

	function create()
	{
		header("Location: obj_location_new.php?new_type=".$_POST["new_type"]."&from=lo_list.php");
		exit;
	}

	function edit()
	{
		if (!is_array($_POST["items"]))
		{
			$this->message .= $this->lng->txt("select_one");
			$this->view();
			return;
		}

		foreach ($_POST["items"] as $item)
		{
			header("location: ./content/lm_edit.php?ref_id=$item");
			exit;
		}
	}

	function export()
	{
		//  select min one element
		if (!is_array($_POST["items"]) || count($_POST["items"])==0 )
		{
			$this->message .= $this->lng->txt("select_one");
			$this->view();
			return;
		}
		
		// select max one element
		if (count($_POST["items"])>1)
		{
			$this->message .= $this->lng->txt("select_one");
			$this->view();
			return;
		}
		
		if ($_POST["items"])
		{
			
			foreach ($_POST["items"] as $item)
			{
				/**
				*	exports just dbk-objects.
				*/
				$tmp_obj =& $this->ilias->obj_factory->getInstanceByRefId($item);

				if ($tmp_obj->getType() == "dbk" ) {
					require_once "content/classes/class.ilObjDlBook.php";
					$dbk =& new ilObjDlBook($this->id, true);
					$dbk->export($item);
				}
				
				// DO SOMETHING $item = ref_id of selected object
			}
		}

		$this->view();
	}

	function addToDesk()
	{
		if ($_GET["ref_id"] and $_GET["type"])
		{
			$this->ilias->account->addDesktopItem($_GET["ref_id"],$_GET["type"]);
		}
		else
		{
			if ($_POST["items"])
			{
				foreach ($_POST["items"] as $item)
				{
					$tmp_obj =& $this->ilias->obj_factory->getInstanceByRefId($item);
					$this->ilias->account->addDesktopItem($item, $tmp_obj->getType());
					unset($tmp_obj);
				}
			}
		}

		$this->view();
	}

	// TODO: this function is common and belongs to class util!
	/**
	* builds a path string to show the context
	* you may leave startnode blank. root node of tree is used instead
	* @param	integer	endnode_id
	* @param	integer	startnode_id
	* @return	string	path
	* @access	public
	*/
	function getContextPath($a_endnode_id, $a_startnode_id = 0)
	{
		$path = "";

		$tmpPath = $this->tree->getPathFull($a_endnode_id, $a_startnode_id);

		// count -1, to exclude the forum itself
		for ($i = 0; $i < (count($tmpPath) - 1); $i++)
		{
			if ($path != "")
			{
				$path .= " > ";
			}

			$path .= $tmpPath[$i]["title"];
		}

		return $path;
	}

	/**
	* show possible subobjects (pulldown menu)
	*
	* @access	public
	*/
	function showPossibleSubObjects()
	{

		$d = $this->objDefinition->getCreatableSubObjects("cat");

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
					if($row["name"] == "lm" || $row["name"] == "dbk" || $row["name"] == "crs")
					{
						$subobj[] = $row["name"];
					}
				}
			}
		}

		if (is_array($subobj))
		{
			$this->showActionSelect($subobj);

			//build form
			$opts = ilUtil::formSelect(12,"new_type",$subobj);
			$this->tpl->setVariable("COLUMN_COUNTS", 7);
			$this->tpl->setCurrentBlock("add_object");
			$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
			$this->tpl->setVariable("BTN_NAME", "create");
			$this->tpl->setVariable("TXT_ADD", $this->lng->txt("add"));
			$this->tpl->parseCurrentBlock();
		}
	}

	function showActionSelect(&$subobj)
	{
		$actions = array("edit" => $this->lng->txt("edit"),"addToDesk" => $this->lng->txt("to_desktop")
						 ,"export" => $this->lng->txt("export"));

		if(is_array($subobj))
		{
			if(in_array("dbk",$subobj) or in_array("lm",$subobj))
			{
				$this->tpl->setVariable("TPLPATH",$this->tpl->tplPath);
				
				$this->tpl->setCurrentBlock("tbl_action_select");
				$this->tpl->setVariable("SELECT_ACTION",ilUtil::formSelect("","action_type",$actions,false,true));
				$this->tpl->setVariable("BTN_NAME","action");
				$this->tpl->setVariable("BTN_VALUE",$this->lng->txt("submit"));
				$this->tpl->parseCurrentBlock();
			}
				
		}
	}

	/**
	* set Locator
	*
	* @param	object	tree object
	* @param	integer	reference id
	* @access	public
	*/
	function setLocator()
	{
		$a_tree =& $this->tree;
		$a_id = $_GET["ref_id"];

		$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

		$path = $a_tree->getPathFull($a_id);

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
			if ($row["child"] != $a_tree->getRootId())
			{
				$this->tpl->setVariable("ITEM", $row["title"]);
			}

			else
			{
				$this->tpl->setVariable("ITEM", $this->lng->txt("groups_overview"));
			}

			$this->tpl->setVariable("LINK_ITEM", "grp_list.php?ref_id=".$row["child"]);
			$this->tpl->setVariable("LINK_TARGET", " target=\"bottom\" ");

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

		$this->tpl->setVariable("TXT_LOCATOR",$debug.$this->lng->txt("locator"));
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* set admin tabs
	* @access	public
	* @param	boolean; whether standard tabs are set or not
	* @param	multdimensional array for additional tabs; optional
	*/
	function setAdminTabs($settabs=false, $addtabs="")
	{
		
		$this->tpl->addBlockFile("TABS", "tabs", "tpl.tabs.html");
		
		if ($settabs and $_SESSION["viewmode"]=="tree")
		{

			$this->tpl->setCurrentBlock("tab");
			$this->tpl->setVariable("TAB_TYPE", 'tabinactive');
			$this->tpl->setVariable("TAB_TARGET", "bottom");
			$this->tpl->setVariable("TAB_LINK", "grp_list.php?viewmode=flat");
			$this->tpl->setVariable("TAB_TEXT", $this->lng->txt("flatview"));
			$this->tpl->parseCurrentBlock();
		}
		elseif($settabs and $_SESSION["viewmode"]=="flat")
		{
		
			$this->tpl->setCurrentBlock("tab");
			$this->tpl->setVariable("TAB_TYPE", 'tabinactive');
			$this->tpl->setVariable("TAB_TARGET", "bottom");
			$this->tpl->setVariable("TAB_LINK", "grp_list.php?viewmode=tree");
			$this->tpl->setVariable("TAB_TEXT", $this->lng->txt("treeview"));
			$this->tpl->parseCurrentBlock();
		}

		if (!empty($addtabs))
		{

			foreach($addtabs as $addtab)
			{
				$this->tpl->setCurrentBlock("tab");
				$this->tpl->setVariable("TAB_TYPE", $addtab["ftabtype"]);
				$this->tpl->setVariable("TAB_TARGET", $addtab["target"]);
				$this->tpl->setVariable("TAB_LINK", "grp_list.php?".$addtab["tab_cmd"]);
				$this->tpl->setVariable("TAB_TEXT", $this->lng->txt($addtab["tab_text"]));
				$this->tpl->parseCurrentBlock();
			}
		}
	}
}
?>
