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
require_once("include/inc.header.php");
require_once("classes/class.ilObjGroupGUI.php");
require_once("classes/class.ilGroupExplorer.php");
require_once("classes/class.ilTableGUI.php");
require_once("classes/class.ilTree.php");
require_once("classes/class.ilObjGroup.php");
/**
* Class ilGroupGUI 
*
* GUI class for ilLearningModule
*
* @author Martin Rus <mrus@smail.uni-koeln.de>
* @version $Id$
*
* @package group
*/
class ilGroupGUI extends ilObjGroupGUI
{
	var $g_obj;
	var $g_tree;
	var $tpl;
	var $lng;
	var $objDefinition;
	var $tree;
	var $rbacsystem;
	var $ilias;
	var $object;
	var $grp_tree;
	/**
	* Constructor
	* @access	public
	*/
	
	function ilGroupGUI($a_data,$a_id,$a_call_by_reference)

	{
		global $tpl, $ilias, $lng, $tree, $rbacsystem, $objDefinition;




		$this->type ="grp";
		

		

		
		$this->ilias =& $ilias;
		$this->objDefinition =& $objDefinition;
		$this->tpl =& $tpl;
		//$this->lng =& $lng;
		$this->tree =& $tree;
		$this->formaction = array();
		$this->return_location = array();

		$this->data = $a_data;
		$this->id = $a_id;
		$this->call_by_reference = $a_call_by_reference;
		

		$this->ref_id = $_GET["ref_id"];
		$this->obj_id = $_GET["obj_id"];

		// get the object
		$this->assignObject();
		
		
		//$this->object =& $ilias->obj_factory->getInstanceByRefId($_GET["ref_id"]);
		
		$this->lng =& $this->object->lng;
		//$this->setLocator();

		sendInfo();

		$this->lng =& $this->object->lng;




		//var_dump ($this->object);
		//echo $this->object->getRefId()."fff";
		
		if (isset($_GET["tree_id"]))
		{
			$this->grp_tree = new ilTree($_GET["tree_id"]);
		}else{
			$this->grp_tree = new ilTree($this->object->getId());
		}
		$this->grp_tree->setTableNames("grp_tree","object_data");
		
		
		//echo ($id);

		$cmd = $_GET["cmd"];

		if($cmd == "")
		{
			$cmd = "view";
		}
		if (isset($_POST["cmd"]))
		{
			//var_dump ($_POST);
			//var_dump ($_POST["id"]);
			$cmd = key($_POST["cmd"]);
			$fullcmd = $cmd."object";
			//echo ($fullcmd);
			$this->$fullcmd();
			exit();
			
		}

		$this->$cmd();
		
		//var_dump($_GET);

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
			$this->tpl = new ilTemplate("tpl.group.html", false, false);
			$this->tpl->show();
		}
		else	// list
		{

			$this->displayList();
		}
	}
	
	
	/**
	* 
	*/
	function displayList()
	{ 

	global  $tree, $rbacsystem;
	
	require_once "./include/inc.header.php";
	require_once "./classes/class.ilExplorer.php";
	require_once "./classes/class.ilTableGUI.php";
	



	$this->tpl->addBlockFile("CONTENT", "content", "tpl.group_detail.html");
	$this->tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");
	infoPanel();
	$this->setLocator();
	$this->tpl->setVariable("FORMACTION", "group.php?gateway=true&ref_id=".$_GET["ref_id"]);
	$this->tpl->setVariable("FORM_ACTION_METHOD", "post");

	$this->tpl->setCurrentBlock("content");
	$this->tpl->setVariable("TXT_PAGEHEADLINE",  $this->lng->txt("groups_overview"));

	// set offset & limit
	$offset = intval($_GET["offset"]);
	$limit = intval($_GET["limit"]);

	if ($limit == 0)
	{
		$limit = 10;	// TODO: move to user settings 
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


	if (!isset($_SESSION["viewmode"]) or $_SESSION["viewmode"] == "flat")
	{
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK","group.php?viewmode=tree");
		$this->tpl->setVariable("BTN_TXT", $this->lng->txt("treeview"));
		$this->tpl->parseCurrentBlock();
	}
	else
	{
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK","group.php?viewmode=flat");
		$this->tpl->setVariable("BTN_TARGET","target=\"_parent\"");
		$this->tpl->setVariable("BTN_TXT", $this->lng->txt("flatview"));
		$this->tpl->parseCurrentBlock();
	}

	$this->tpl->setCurrentBlock("btn_cell");
	$this->tpl->setVariable("BTN_LINK","group.php?cmd=create&parent_ref_id=".$_GET["ref_id"]."&type=grp");
	$this->tpl->setVariable("BTN_TXT", $this->lng->txt("group_new"));
	$this->tpl->parseCurrentBlock();


	// display different content depending on viewmode
	switch ($_SESSION["viewmode"])
	{
		case "flat":
		$cont_arr = ilUtil::getObjectsByOperations('grp','visible');
		break;
		
		case "tree":
		//go through valid objects and filter out the lessons only
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

	require_once "./include/inc.sort.php";
	$cont_arr = sortArray($cont_arr,$_GET["sort_by"],$_GET["sort_order"]);
	$cont_arr = array_slice($cont_arr,$offset,$limit);
	

	// load template for table
	$this->tpl->addBlockfile("GROUP_DETAILS_TABLE", "group_table", "tpl.table.html");
	// load template for table content data
	$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.grp_tbl_rows_checkbox.html");
	$cont_num = count($cont_arr);


	// render table content data
	if ($cont_num > 0)
	{ 
		// counter for rowcolor change
		$num = 0;
	//	var_dump ($cont_arr);
		foreach ($cont_arr as $cont_data)
		{
			$this->tpl->setCurrentBlock("tbl_content");
			$newuser = new ilObjUser($cont_data["owner"]);
			// change row color
			$this->tpl->setVariable("ROWCOL", ilUtil::switchColor($num,"tblrow2","tblrow1"));
			$num++;


			$obj_link = "group.php?gateway=true&cmd=show_content&ref_id=".$cont_data["ref_id"];


			$obj_icon = "icon_".$cont_data["type"]."_b.gif";
			$this->tpl->setVariable("CHECKBOX", ilUtil::formCheckBox(0,"id[]",$cont_data["ref_id"]));

			$this->tpl->setVariable("TITLE", $cont_data["title"]);
			$this->tpl->setVariable("LO_LINK", $obj_link);
			//$this->tpl->setVariable("LINK_TARGET", "_parent");
			$this->tpl->setVariable("IMG", $obj_icon);
			$this->tpl->setVariable("ALT_IMG", $this->lng->txt("obj_".$cont_data["type"]));
			$this->tpl->setVariable("DESCRIPTION", $cont_data["description"]);
			$this->tpl->setVariable("OWNER", $newuser->getFullName($cont_data["owner"]));
			$this->tpl->setVariable("LAST_VISIT", "N/A");
			//$this->tpl->setVariable("ROLE_IN_GROUP", "keine Rolle zugewiesen");
			$this->tpl->setVariable("LAST_CHANGE", $cont_data["last_update"]);//ilFormat::formatDate($cont_data["last_update"])
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
	$tbl->setHeaderNames(array("",$this->lng->txt("title"),$this->lng->txt("description"),$this->lng->txt("owner"),$this->lng->txt("last_visit"),$this->lng->txt("last_change"),$this->lng->txt("context")));
	$tbl->setHeaderVars(array("checkbox", "title","description","owner","last_visit","last_change","context"));
	$tbl->setColumnWidth(array("3%", "7%","7%","15%","31%","6%","17%"));

	// control
	$tbl->setOrderColumn($_GET["sort_by"]);
	$tbl->setOrderDirection($_GET["sort_order"]);
	$tbl->setLimit($limit);
	$tbl->setOffset($offset);
	$tbl->setMaxCount($maxcount);


	$this->tpl->SetVariable("COLUMN_COUNTS", "7");
	$this->tpl->SetVariable("COLUMN_COUNTS", "7");
	$this->showActions(true);

	// footer
	$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
	$tbl->disable("content");
	$tbl->disable("footer");

	// render table
	$tbl->render();
	$this->tpl->show();


	}



	function explorer()
	{


		require_once "include/inc.header.php";
		require_once "classes/class.ilExplorer.php";
		require_once "classes/class.ilGroupExplorer.php";

		$this->tpl->addBlockFile("CONTENT", "content", "tpl.explorer.html");

		$exp = new ilGroupExplorer("group.php?cmd=displayList");

		if ($_GET["expand"] == "")
		{
			$expanded = "1";
		}
		else
		{
			$expanded = $_GET["expand"];
		}

		$exp->setExpand($expanded);

		//filter object types
		$exp->addFilter("root");
		$exp->addFilter("cat");
		$exp->addFilter("grp");
		$exp->setFiltered(true);

		//build html-output
		$exp->setOutput(0);
		$output = $exp->getOutput();

		$this->tpl->setCurrentBlock("content");
		$this->tpl->setVariable("TXT_EXPLORER_HEADER",$this->lng->txt("groups"));
		$this->tpl->setVariable("EXPLORER",$output);
		//$this->tpl->setVariable("ACTION", "group_menu.php?expand=".$_GET["expand"]);
		$this->tpl->parseCurrentBlock();

		$this->tpl->show();

	}

	function show_content()
	{//var_dump($_POST)."#".var_dump($_GET);
		
		global $tree, $tpl, $lng, $rbacsystem;

		
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.group_detail.html");

		$this->tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");
		infoPanel();
		$this->tpl->setVariable("FORMACTION", "group.php?gateway=true&ref_id=".$_GET["ref_id"]."&obj_id=".$this->object->getId()."&tree_id=".$this->grp_tree->getTreeId()."&tree_table=grp_tree");
		$this->tpl->setVariable("FORM_ACTION_METHOD", "post");
		
		$this->tpl->setCurrentBlock("content");
		$this->tpl->setVariable("TXT_PAGEHEADLINE",  $this->lng->txt("group_details"));
		// set offset & limit
		$offset = intval($_GET["offset"]);
		$limit = intval($_GET["limit"]);

		if ($limit == 0)
		{
			$limit = 10;	// TODO: move to user settings
		}

		// set default sort column
		if (empty($_GET["sort_by"]))
		{
			$_GET["sort_by"] = "title";
		}
		$this->setLocator();

		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK","group.php?cmd=groupmembers&ref_id=".$_GET["ref_id"]);
		$this->tpl->setVariable("BTN_TXT", $this->lng->txt("group_members"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK","group.php?cmd=newmembersobject&ref_id=".$_GET["ref_id"]);
		$this->tpl->setVariable("BTN_TXT", $this->lng->txt("new_member"));
		$this->tpl->parseCurrentBlock();

		$cont_arr = array();
		
		
		
		
		//$objects = $tree->getChilds($_GET["ref_id"],"title");
		$objects = $this->grp_tree->getChilds($this->object->getId(),"title");
		
		if (count($objects) > 0)
		{
			foreach ($objects as $key => $object)
			{
				//if ($rbacsystem->checkAccess('visible',$object["child"]))
				//{
					$cont_arr[$key] = $object;
				//}
			}
		}

		$maxcount = count($cont_arr);

		// load template for table
		$this->tpl->addBlockfile("GROUP_DETAILS_TABLE", "group_table", "tpl.table.html");
		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.grp_tbl_rows_checkbox.html");
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

				$obj_link = $this->getURLbyType($cont_data);

				$obj_icon = "icon_".$cont_data["type"]."_b.gif";
				$this->tpl->setVariable("CHECKBOX", ilUtil::formCheckBox(0,"id[]",$cont_data["ref_id"]));

				$this->tpl->setVariable("TITLE", $cont_data["title"]);
				$this->tpl->setVariable("LO_LINK", $obj_link);

				$this->tpl->setVariable("IMG", $obj_icon);
				$this->tpl->setVariable("ALT_IMG", $lng->txt("obj_".$cont_data["type"]));
				$this->tpl->setVariable("DESCRIPTION", $cont_data["description"]);
				$this->tpl->setVariable("OWNER", $newuser->getFullName());
				$this->tpl->setVariable("LAST_VISIT", "N/A");
				$this->tpl->setVariable("LAST_CHANGE", ilFormat::formatDate($cont_data["last_update"]));
				//TODO
				//$this->tpl->setVariable("CONTEXTPATH", $this->getContextPath($cont_data["ref_id"]));
				$this->tpl->parseCurrentBlock();

			}
		}
		else
		{
			$this->tpl->setCurrentBlock("no_content");
			$this->tpl->setVariable("TXT_MSG_NO_CONTENT",$lng->txt("group_any_objects"));
			$this->tpl->parseCurrentBlock("no_content");
		}

		// create table
		$tbl = new ilTableGUI();

		// title & header columns
		$tbl->setTitle($lng->txt("group_details"),"icon_grp_b.gif",$lng->txt("group_details"));
		//$tbl->setHelp("tbl_help.php","icon_help.gif",$lng->txt("help"));
		$tbl->setHeaderNames(array("",$lng->txt("title"),$lng->txt("description"),$lng->txt("owner"),$lng->txt("last_visit"),$lng->txt("last_change"),$lng->txt("context")));
		$tbl->setHeaderVars(array("checkbox","title","description","status","last_visit","last_change","context"));
		$tbl->setColumnWidth(array("3%","7%","7%","15%","15%","6%","22%"));

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($limit);
		$tbl->setOffset($offset);
		$tbl->setMaxCount($maxcount);




		//$this->tpl->setCurrentBlock("tbl_action_btn");
		$this->tpl->SetVariable("COLUMN_COUNTS", "7");
		$this->showActions(true);

		// footer
		$tbl->setFooter("tblfooter",$lng->txt("previous"),$lng->txt("next"));
		//$tbl->disable("content");
		//$tbl->disable("footer");

		// render table
		$tbl->render();

		$this->tpl->show();
	}

	function getURLbyType($cont_data)
	{
		switch ($cont_data["type"])
		{ 
		
  		case "frm":
			$URL = "forums_threads_liste.php?ref_id=".$cont_data["ref_id"];
		break;	
		
		case "crs":
			$URL = "lo_content.php?ref_id=".$cont_data["ref_id"];
		break;
		
		case "le":
			$URL = "content/lm_presentation.php?lm_id=".$cont_data["ref_id"];
		break;
		
		case "fold":
			//TODO
			if (isset($_GET["tree_id"]))
			{
				$URL = "group.php?obj_id=".$cont_data["obj_id"]."&ref_id=".$_GET["ref_id"]."&tree_id=".$_GET["tree_id"]."&tree_table=grp_tree&cmd=show_content";
		
			}else{
			
				$URL = "group.php?obj_id=".$cont_data["obj_id"]."&ref_id=".$_GET["ref_id"]."&tree_id=".$this->object->getId()."&tree_table=grp_tree&cmd=show_content";
			}
		
		break;
		}

	return $URL;
	}
	

	function getContextPath($a_endnode_id, $a_startnode_id = 0)
	{
		global $tree;

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
	
	function copyObject()
	{
		global $rbacsystem;

		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		// TODO: WE NEED ONLY THE ID IN THIS PLACE. MAYBE BY A FUNCTION getNodeIdsOfSubTree??
		// FOR ALL SELECTED OBJECTS


		// FOR ALL OBJECTS THAT SHOULD BE COPIED

		foreach ($_POST["id"] as $ref_id)
		{
			// GET COMPLETE NODE_DATA OF ALL SUBTREE NODES
			$node_data = $this->tree->getNodeData($ref_id);
			$subtree_nodes = $this->tree->getSubTree($node_data);

			$all_node_data[] = $node_data;
			$all_subtree_nodes[] = $subtree_nodes;

			// CHECK READ PERMISSION OF ALL OBJECTS IN ACTUAL SUBTREE
			foreach ($subtree_nodes as $node)
			{
				if (!$rbacsystem->checkAccess('read',$node["ref_id"]))
				{
					$no_copy[] = $node["ref_id"];
				}
			}
		}

		// IF THERE IS ANY OBJECT WITH NO PERMISSION TO 'read'
		if (count($no_copy))
		{

			$no_copy = implode(',',$no_copy);
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_copy")." ".
									 $no_copy,$this->ilias->error_obj->MESSAGE);
		}

		// COPY THEM
		// SAVE SUBTREE
		// TODO: clipboard is enough
		$clipboard["parent"] = $_GET["ref_id"];
		$clipboard["cmd"] = key($_POST["cmd"]);

		/*
		foreach ($_POST["id"] as $ref_id)
		{
			$clipboard["ref_ids"][] = $ref_id;

			$this->ilias->raiseError($this->lng->txt("msg_no_perm_copy")." ".implode(',',$this->getTitlesByRefId($no_copy)),
									 $this->ilias->error_obj->MESSAGE);

		}*/

		$_SESSION["clipboard"]["parent"] = $_GET["ref_id"];
		$_SESSION["clipboard"]["cmd"] = key($_POST["cmd"]);
		$_SESSION["clipboard"]["ref_ids"] = $_POST["id"];

		sendinfo($this->lng->txt("msg_copy_clipboard"),true);

		header("location: group.php?cmd=DisplayList&ref_id=".$_GET["ref_id"]);
		exit();
	}
	/**
	* paste object from clipboard to current place
	*
	* @access	public
 	*/
	function pasteObject()
	{
		global $rbacsystem,$rbacadmin,$tree,$objDefinition;
		// CHECK SOME THINGS
		if ($_SESSION["clipboard"]["cmd"] == "copy")
		{
			// IF CMD WAS 'copy' CALL PRIVATE CLONE METHOD
			$this->cloneObject($_GET["ref_id"]);
			return true;
			exit; // und wech... will never be executed
		}

		// PASTE IF CMD WAS 'cut' (TODO: Could be merged with 'link' routine below in some parts)
		if ($_SESSION["clipboard"]["cmd"] == "cut")
		{	
			
			//echo ($_GET["ref_id"]);
			// TODO:i think this can be substituted by $this->object ????
			$object =& $this->ilias->obj_factory->getInstanceByRefId($_GET["ref_id"]);
			//$object = $this->object;
			//echo ($object);	
			// this loop does all checks
			
			foreach ($_SESSION["clipboard"]["ref_ids"] as $ref_id)
			{
				$obj_data =& $this->ilias->obj_factory->getInstanceByRefId($ref_id);
				
				// CHECK ACCESS
				if (!$rbacsystem->checkAccess('create', $_GET["ref_id"], $obj_data->getType()))
				{
					$no_paste[] = $ref_id;
				}

				// CHECK IF REFERENCE ALREADY EXISTS
				if ($_GET["ref_id"] == $obj_data->getRefId())
				{
					$exists[] = $ref_id;
					break;
				}

				// CHECK IF PASTE OBJECT SHALL BE CHILD OF ITSELF
				// TODO: FUNCTION IST NOT LONGER NEEDED IN THIS WAY. WE ONLY NEED TO CHECK IF
				// THE COMBINATION child/parent ALREADY EXISTS

				//if ($tree->isGrandChild(1,0))
				//if ($tree->isGrandChild($id, $_GET["ref_id"]))
				//{
			//		$is_child[] = $ref_id;
				//}

				// CHECK IF OBJECT IS ALLOWED TO CONTAIN PASTED OBJECT AS SUBOBJECT
				$obj_type = $obj_data->getType();
			
				if (!in_array($obj_type, array_keys($objDefinition->getSubObjects($object->getType()))))
				{
					$not_allowed_subobject[] = $obj_data->getType();
				}
			}

//////////////////////////
// process checking results
		
			if (count($exists))
			{
				$this->ilias->raiseError($this->lng->txt("msg_obj_exists"),$this->ilias->error_obj->MESSAGE);
			}

			if (count($is_child))
			{
				$this->ilias->raiseError($this->lng->txt("msg_not_in_itself")." ".implode(',',$is_child),
										 $this->ilias->error_obj->MESSAGE);
			}

			if (count($not_allowed_subobject))
			{
				$this->ilias->raiseError($this->lng->txt("msg_may_not_contain")." ".implode(',',$not_allowed_subobject),
										 $this->ilias->error_obj->MESSAGE);
			}

			if (count($no_paste))
			{
				$this->ilias->raiseError($this->lng->txt("msg_no_perm_paste")." ".
										 implode(',',$no_paste),$this->ilias->error_obj->MESSAGE);
			}
/////////////////////////////////////////
// everything ok: now paste the objects to new location

			foreach($_SESSION["clipboard"]["ref_ids"] as $ref_id)
			{

				// get node data
				$top_node = $tree->getNodeData($ref_id);
			
				// get subnodes of top nodes
				$subnodes[$ref_id] = $tree->getSubtree($top_node);
			
				// delete old tree entries
				$tree->deleteTree($top_node);
			}

			// now move all subtrees to new location
			foreach($subnodes as $key => $subnode)
			{
				//first paste top_node....
				$rbacadmin->revokePermission($key);
				$obj_data =& $this->ilias->obj_factory->getInstanceByRefId($key);
				$obj_data->putInTree($_GET["ref_id"]);
				$obj_data->setPermissions($_GET["ref_id"]);

				// ... remove top_node from list....
				array_shift($subnode);
				
				// ... insert subtree of top_node if any subnodes exist
				if (count($subnode) > 0)
				{
					foreach ($subnode as $node)
					{
						$rbacadmin->revokePermission($node["child"]);
						$obj_data =& $this->ilias->obj_factory->getInstanceByRefId($node["child"]);
						$obj_data->putInTree($node["parent"]);
						$obj_data->setPermissions($node["parent"]);
					}
				}
			}
		} // END IF 'cut & paste'
		
		// PASTE IF CMD WAS 'linkt' (TODO: Could be merged with 'cut' routine above)
		if ($_SESSION["clipboard"]["cmd"] == "link")
		{
			
			// TODO:i think this can be substituted by $this->object ????
			$object =& $this->ilias->obj_factory->getInstanceByRefId($_GET["ref_id"]);
	
			// this loop does all checks
			foreach ($_SESSION["clipboard"]["ref_ids"] as $ref_id)
			{
				$obj_data =& $this->ilias->obj_factory->getInstanceByRefId($ref_id);

				// CHECK ACCESS
				if (!$rbacsystem->checkAccess('create', $_GET["ref_id"], $obj_data->getType()))
				{
					$no_paste[] = $ref_id;
				}

				// CHECK IF REFERENCE ALREADY EXISTS
				if ($_GET["ref_id"] == $obj_data->getRefId())
				{
					$exists[] = $ref_id;
					break;
				}

				// CHECK IF PASTE OBJECT SHALL BE CHILD OF ITSELF
				// TODO: FUNCTION IST NOT LONGER NEEDED IN THIS WAY. WE ONLY NEED TO CHECK IF
				// THE COMBINATION child/parent ALREADY EXISTS

				//if ($tree->isGrandChild(1,0))
				//if ($tree->isGrandChild($id, $_GET["ref_id"]))
				//{
			//		$is_child[] = $ref_id;
				//}

				// CHECK IF OBJECT IS ALLOWED TO CONTAIN PASTED OBJECT AS SUBOBJECT
				$obj_type = $obj_data->getType();
			
				if (!in_array($obj_type, array_keys($objDefinition->getSubObjects($object->getType()))))
				{
					$not_allowed_subobject[] = $obj_data->getType();
				}
			}

//////////////////////////
// process checking results
		
			if (count($exists))
			{
				$this->ilias->raiseError($this->lng->txt("msg_obj_exists"),$this->ilias->error_obj->MESSAGE);
			}

			if (count($is_child))
			{
				$this->ilias->raiseError($this->lng->txt("msg_not_in_itself")." ".implode(',',$is_child),
										 $this->ilias->error_obj->MESSAGE);
			}

			if (count($not_allowed_subobject))
			{
				$this->ilias->raiseError($this->lng->txt("msg_may_not_contain")." ".implode(',',$not_allowed_subobject),
										 $this->ilias->error_obj->MESSAGE);
			}

			if (count($no_paste))
			{
				$this->ilias->raiseError($this->lng->txt("msg_no_perm_paste")." ".
										 implode(',',$no_paste),$this->ilias->error_obj->MESSAGE);
			}
/////////////////////////////////////////
// everything ok: now paste the objects to new location

			foreach($_SESSION["clipboard"]["ref_ids"] as $ref_id)
			{

				// get node data
				$top_node = $tree->getNodeData($ref_id);
			
				// get subnodes of top nodes
				$subnodes[$ref_id] = $tree->getSubtree($top_node);
			}

			// now move all subtrees to new location
			foreach($subnodes as $key => $subnode)
			{
				//first paste top_node....
				$obj_data =& $this->ilias->obj_factory->getInstanceByRefId($key);
				$obj_data->createReference();
				$obj_data->putInTree($_GET["ref_id"]);
				$obj_data->setPermissions($_GET["ref_id"]);
				//echo ($obj_data->getRefId()." ". $_GET["grp_id"]);
				//$this->grp_tree->insertNode($obj_data->getRefId(), $_GET["grp_id"]);

				// ... remove top_node from list....
				array_shift($subnode);

				// ... insert subtree of top_node if any subnodes exist
				if (count($subnode) > 0)
				{
					foreach ($subnode as $node)
					{
						$obj_data =& $this->ilias->obj_factory->getInstanceByRefId($node["child"]);
						$obj_data->createReference();
						// TODO: $node["parent"] is wrong in case of new reference!!!!
						$obj_data->putInTree($node["parent"]);
						$obj_data->setPermissions($node["parent"]);
					}
				}
			}
		} // END IF 'link & paste'
				
		// clear clipboard
		$this->clearObject();
		
		// TODO: sendInfo does not work in this place :-(
		sendInfo($this->lng->txt("msg_changes_ok"),true);
		header("location: group.php?cmd=displayList");
		exit();
	}

	function cloneObject($a_ref_id)
	{


		global $rbacsystem;

		if(!is_array($_SESSION["clipboard"]["ref_ids"]))
		{
			$this->ilias->raiseError($this->lng->txt("msg_error_copy"),$this->ilias->error_obj->MESSAGE);
		}

		foreach ($_SESSION["clipboard"]["ref_ids"] as $ref_id)
		{
			// CHECK SOME THINGS
			$obj_data =& $this->ilias->obj_factory->getInstanceByRefId($ref_id);
			$data = $this->tree->getNodeData($ref_id);

			// CHECK ACCESS
			if (!$rbacsystem->checkAccess('create',$a_ref_id,$obj_data->getType()))
			{
				$no_paste[] = $ref_id;
			}

			// CHECK IF PASTE OBJECT SHALL BE CHILD OF ITSELF
			if ($this->tree->isGrandChild($ref_id,$a_ref_id))
			{
				$is_child[] = $ref_id;
			}

			// CHECK IF OBJECT IS ALLOWED TO CONTAIN PASTED OBJECT AS SUBOBJECT
			$object =& $this->ilias->obj_factory->getInstanceByRefId($a_ref_id);

			if (!in_array($obj_data->getType(),array_keys($this->objDefinition->getSubObjects($object->getType()))))
			{
				$not_allowed_subobject[] = $obj_data->getType();
			}
		}
		if (count($no_paste))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_create")." ".implode(',',$this->getTitlesByRefId($no_paste)),
									 $this->ilias->error_obj->MESSAGE);
		}

		if (count($is_child))
		{
			$this->ilias->raiseError($this->lng->txt("msg_not_in_itself")." ".implode(',',$this->getTitlesByRefId($is_child)),
									 $this->ilias->error_obj->MESSAGE);
		}

		if (count($not_allowed_subobject))
		{
			$this->ilias->raiseError($this->lng->txt("msg_may_not_contain")." ".
									 implode(',',$not_allowed_subobject),
									 $this->ilias->error_obj->MESSAGE);
		}

		// NOW CLONE ALL OBJECTS
		// THEREFORE THE CLONE METHOD OF ALL OBJECTS IS CALLED
		foreach ($_SESSION["clipboard"]["ref_ids"] as $id)
		{
			$this->cloneNodes($id,$a_ref_id);
		}

		$this->clearObject();

		sendinfo($this->lng->txt("msg_cloned"),true);

		header("location: adm_object.php?ref_id=".$_GET["ref_id"]);
		exit();
	} // END CLONE

	/**
	* cut object(s) out from a container and write the information to clipboard
	*
	* @access	public
	*/
	function cutObject()
	{
		global $clipboard,$tree,$rbacsystem,$rbacadmin;

		// CHECK NOTHING CHECKED
		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		// CHECK ACCESS
		foreach ($_POST["id"] as $ref_id)
		{
			if(!$rbacsystem->checkAccess('delete',$ref_id))
			{
				$no_cut[] = $ref_id;
			}
		}

		// NO ACCESS IF ONE OBJECT COULD NOT BE DELETED
		if (count($no_cut))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_cut")." ".
									 implode(',',$no_cut),$this->ilias->error_obj->MESSAGE);
		}

		// WRITE TO CLIPBOARD
		$clipboard["parent"] = $_GET["grp_id"];
		$clipboard["cmd"] = key($_POST["cmd"]);		
		foreach($_POST["id"] as $ref_id)
		{
			$clipboard["ref_ids"][] = $ref_id;
		}

		$_SESSION["clipboard"] = $clipboard;	
		header("location: group.php?cmd=displayList");
		exit();
	}

	/**
	* clear clipboard
	*
	* @access	public
	*/
	function clearObject()
	{
		session_unregister("clipboard");
		
		header("location: group.php?cmd=displayList");
		exit();

	}

	function groupmembers ()
	{
		$num = 0;



		$newGrp = new ilObjGroup($_GET["ref_id"],true);
		$member_ids = $newGrp->getGroupMemberIds($_GET["ref_id"]);
			
		$member_arr = array();
		foreach ($member_ids as $member_id)
		{
			array_push($member_arr, new ilObjUser($member_id));
		}

	

		// output data
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.grp_members.html");

		infoPanel();
		$this->tpl->setVariable("FORMACTION", "group.php?gateway=true&ref_id=".$_GET["ref_id"]);
		$this->tpl->setVariable("FORM_ACTION_METHOD", "post");

		$this->tpl->setCurrentBlock("content");
		$this->tpl->setVariable("TXT_PAGEHEADLINE", $this->lng->txt("group_members"));



		$this->tpl->addBlockfile("GROUP_MEMBERS_TABLE", "member_table", "tpl.table.html");
		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.grp_tbl_members.html");
		$num = 0;
		
		$maxcount = count($member_arr);
		foreach($member_arr as $member)
		{
			$this->tpl->setCurrentBlock("tbl_content");
			$grp_role_id = $newGrp->getGroupRoleId($member->getId());
			$newObj 	 = new ilObject($grp_role_id,false);
			$this->tpl->setVariable("ROWCOL", ilUtil::switchColor($num,"tblrow2","tblrow1"));
			$num++;
				
//todo: chechAccess, each user sees only the symbols belonging to his rigths
	
	
			$link_contact = "mail_new.php?mobj_id=3&type=new&mail_data[rcp_to]=".$member->getLogin();
			$link_change = "group.php?cmd=changeMemberObject&ref_id=".$this->ref_id."&mem_id=".$member->getId();

			$link_leave = "group.php?type=grp&cmd=leaveGrp&ref_id=".$this->ref_id."&mem_id=".$member->getId();
			$img_contact = "pencil";
			$img_change = "change";
			$img_leave = "group_out";						
			$val_contact = ilUtil::getImageTagByType($img_contact, $this->tpl->tplPath);
			$val_change = ilUtil::getImageTagByType($img_change, $this->tpl->tplPath);
			$val_leave  = ilUtil::getImageTagByType($img_leave, $this->tpl->tplPath);
			$obj_icon = "icon_usr_b.gif";
			$this->tpl->setVariable("IMG", $obj_icon);
			$this->tpl->setVariable("ALT_IMG", $this->lng->txt("obj_usr"));


			$this->tpl->setVariable("CHECKBOX", ilUtil::formCheckBox(0,"id[]",$member->getId()));
			$this->tpl->setVariable("LOGIN",$member->getLogin());
			$this->tpl->setVariable("FIRSTNAME", $member->getFirstname());
			$this->tpl->setVariable("LASTNAME", $member->getLastname());
			$this->tpl->setVariable("ANNOUNCEMENT_DATE", "Announcement Date");
			$this->tpl->setVariable("ROLENAME", $this->lng->txt($newObj->getTitle()));
			$this->tpl->setVariable("LINK_CONTACT", $link_contact);
			$this->tpl->setVariable("CONTACT", $val_contact);
			$this->tpl->setVariable("LINK_CHANGE", $link_change);
			$this->tpl->setVariable("CHANGE", $val_change);
			$this->tpl->setVariable("LINK_LEAVE", $link_leave);
			$this->tpl->setVariable("LEAVE", $val_leave);
			$this->tpl->parseCurrentBlock();
	// END TABLE MEMBERS
		}





		$this->tpl->setCurrentBlock("tbl_action_btn");


		$this->tpl->SetVariable("COLUMN_COUNTS", "6");
		$this->tpl->setVariable("BTN_NAME", "deassignMember");
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("deassign"));
		$this->tpl->parseCurrentBlock();
		//$this->tpl->setVariable("BTN_NAME", "mail");
		//$this->tpl->setVariable("BTN_VALUE", "Write mail");
		//$this->tpl->parseCurrentBlock();
		$this->tpl->setVariable("BTN_NAME", "changeMember");
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("change_status"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("tbl_action_row");
		$this->tpl->parseCurrentBlock();

		$tbl = new ilTableGUI();
	
		$tbl->setHeaderNames(array("",$this->lng->txt("login"),$this->lng->txt("firstname"),$this->lng->txt("lastname")/*,$lng->txt("announcement_date")*/,$this->lng->txt("role_in_group"),""));
		$tbl->setHeaderVars(array("checkbox","title","description","status"/*,"last_visit"*/,"last_change","context"));
		$tbl->setColumnWidth(array("3%","7%","7%",/*"15%",*/"15%","6%","5%"));
		
// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($limit);
		$tbl->setOffset($offset);
		$tbl->setMaxCount($maxcount);

// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		//$tbl->disable("content");
		//$tbl->disable("footer");

// render table
		$tbl->render();
		$this->tpl->show();

	}
	function deleteObject()
	{
		//var_dump ($_POST["id"]);
		if(!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}
		// SAVE POST VALUES
		$_SESSION["saved_post"] = $_POST["id"];
		//echo ("hallo!!!");
		unset($this->data);
		$this->data["cols"] = array("type", "title", "description", "last_change");
		$this->call_by_reference = true;
		
		foreach($_POST["id"] as $id)
		{
			if ($this->call_by_reference)
			{
				$obj_data =& $this->ilias->obj_factory->getInstanceByRefId($id);
			}
			else
			{
				$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($id);
			}

			$this->data["data"]["$id"] = array(
				"type"        => $obj_data->getType(),
				"title"       => $obj_data->getTitle(),
				"desc"        => $obj_data->getDescription(),
				"last_update" => $obj_data->getLastUpdateDate());
		}

		$this->data["buttons"] = array( "cancelDelete"  => $this->lng->txt("cancel"),
								  "confirmedDelete"  => $this->lng->txt("confirm"));
		$this->tpl->addBlockFile ("CONTENT", "content", "tpl.obj_confirm.html");
		//$this->getTemplateFile("confirm");

		sendInfo($this->lng->txt("info_delete_sure"));
		$this->tpl->setVariable("FORMACTION", "group.php?gateway=true");
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
			foreach($value as $key => $cell_data)
			{
				$this->tpl->setCurrentBlock("table_cell");

				// CREATE TEXT STRING
				if($key == "type")
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
		$this->tpl->show();
	}

	/**
	* confirmed deletion if object -> objects are moved to trash
	*
	* However objects are only removed from tree!! That means that the objects
	* itself stay in the database but are not linked in any context within the system.
	* Trash Bin Feature: Objects can be refreshed in trash
	*
	* @access	public
	*/
	function confirmedDeleteObject()
	{
		global $tree, $rbacsystem, $rbacadmin, $objDefinition;

		// AT LEAST ONE OBJECT HAS TO BE CHOSEN.
		if (!isset($_SESSION["saved_post"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		// FOR ALL SELECTED OBJECTS
		foreach ($_SESSION["saved_post"] as $id)
		{
			// GET COMPLETE NODE_DATA OF ALL SUBTREE NODES
			$node_data = $tree->getNodeData($id);
			$subtree_nodes = $tree->getSubTree($node_data);

			$all_node_data[] = $node_data;
			$all_subtree_nodes[] = $subtree_nodes;

			// CHECK DELETE PERMISSION OF ALL OBJECTS
			foreach ($subtree_nodes as $node)
			{
				if (!$rbacsystem->checkAccess('delete',$node["child"]))
				{
					$not_deletable[] = $node["child"];
					$perform_delete = false;
				}
			}
		}

		// IF THERE IS ANY OBJECT WITH NO PERMISSION TO DELETE
		if (count($not_deletable))
		{
			$not_deletable = implode(',',$not_deletable);
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_delete")." ".
									 $not_deletable,$this->ilias->error_obj->MESSAGE);
		}

		// DELETE THEM
		if (!$all_node_data[0]["type"])
		{
			// OBJECTS ARE NO 'TREE OBJECTS'
			if ($rbacsystem->checkAccess('delete',$_GET["ref_id"]))
			{
				foreach($_SESSION["saved_post"] as $id)
				{
					//$obj = getObject($id);
					$obj =& $this->ilias->obj_factory->getInstanceByObjId($id);
					$obj->delete();
				}
			}
			else
			{
				$this->ilias->raiseError($this->lng->txt("no_perm_delete"),$this->ilias->error_obj->MESSAGE);
			}
		}
		else
		{
			// SAVE SUBTREE AND DELETE SUBTREE FROM TREE
			foreach ($_SESSION["saved_post"] as $id)
			{
				// DELETE OLD PERMISSION ENTRIES
				$subnodes = $tree->getSubtree($tree->getNodeData($id));

				foreach ($subnodes as $subnode)
				{
					$rbacadmin->revokePermission($subnode["child"]);
				}
				$tree->saveSubTree($id);
				$tree->deleteTree($tree->getNodeData($id));
			}
		}
		// Feedback
		sendInfo($this->lng->txt("info_deleted"),true);

		header("location: group.php?cmd=displayList");
		exit();

	}


	/**
	* cancel deletion of object
	*
	* @access	public
	*/
	function canceldeleteObject()
	{
		session_unregister("saved_post");
		
		header("location: group.php?cmd=displayList");
		exit();

	}
	
		/**
	* create an new reference of an object in tree
	* it's like a hard link of unix
	*
	* @access	public
	*/
	function linkObject()
	{
		global $clipboard,$tree,$rbacsystem,$rbacadmin,$objDefinition;

		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		// CHECK ACCESS
		foreach ($_POST["id"] as $ref_id)
		{
			if (!$rbacsystem->checkAccess('delete',$ref_id))
			{
				$no_cut[] = $ref_id;
			}

			$object =& $this->ilias->obj_factory->getInstanceByRefId($ref_id);
			$actions = $objDefinition->getActions($object->getType());
			
			

			if ($actions["link"]["exec"] == 'false')
			{
				$no_link[] = $object->getType();
			}
		}

		// NO ACCESS
		if (count($no_cut))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_link")." ".
									 implode(',',$no_cut),$this->ilias->error_obj->MESSAGE);
		}

		if (count($no_link))
		{
			$this->ilias->raiseError($this->lng->txt("msg_not_possible_link")." ".
									 implode(',',$no_link),$this->ilias->error_obj->MESSAGE);
		}

		// WRITE TO CLIPBOARD
		$clipboard["parent"] = $_GET["ref_id"];
		$clipboard["cmd"] = key($_POST["cmd"]);
		
		foreach ($_POST["id"] as $ref_id)
		{
			$clipboard["ref_ids"][] = $ref_id;
		}

		$_SESSION["clipboard"] = $clipboard;

		header("location: group.php?cmd=DisplayList");
		exit();

	}
	/**
	* create new object form
	*
	* @access	public
	*/
	function createobject()
	{
		//TODO: check the
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

			$this->tpl->addBlockFile("CONTENT", "content" ,"tpl.obj_edit.html");
			//$this->tpl->addBlockFile("CONTENT", "content", "tpl.groups_overview.html");
			foreach ($data["fields"] as $key => $val)
			{
				$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
				$this->tpl->setVariable(strtoupper($key), $val);
				//$this->tpl->parseCurrentBlock();
			}
//
//			echo ("refid: ".$_GET["ref_id"]." new_type : ".$_POST["new_type"]);
//			$this->tpl->setVariable("FORMACTION","group.php?cmd=save&ref_id=".$_GET["ref_id"].
//=======
			echo ("refid: ".$_GET["ref_id"]." new_type : ".$_POST["new_type"]);
			$this->tpl->setVariable("FORMACTION","group.php?cmd=save&ref_id=".$_GET["ref_id"].
//>>>>>>> 1.7
				"&new_type=".$_POST["new_type"]);

			$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
			$this->tpl->setVariable("BTN_NAME", "cmd[save]");
			$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		}
		$this->tpl->show();
	}

	/* save object
	*
	* @access	public
	*/
	function saveObject()
	{
	
	//functionality is implemented in function "save"
	//ToDo: move this functionality from functon "save" to function "saveobject"
	}

	/**
	* show possible action (form buttons)
	*
	* @access	public
 	*/
	function showActions($with_subobjects = false)
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

		$d = $this->objDefinition->getActions("grp");
		//echo ("typ: ".$_GET["type"]);
		//var_dump ($d);

		foreach ($d as $row)
		{
			if (!in_array($row["name"], $notoperations))
			{
				$operations[] = $row;
			}
		}

		if (count($operations) > 0)
		{
			foreach ($operations as $val)
			{
				$this->tpl->setCurrentBlock("tbl_action_btn");
				$this->tpl->setVariable("BTN_NAME", $val["lng"]);
				$this->tpl->setVariable("BTN_VALUE", $this->lng->txt($val["lng"]));
				$this->tpl->parseCurrentBlock();
			}
		}

		if ($with_subobjects == true)
		{
			$this->showPossibleSubObjects();
		}

		$this->tpl->setCurrentBlock("tbl_action_row");
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* show possible subobjects (pulldown menu)
	*
	* @access	public
 	*/
	function showPossibleSubObjects()
	{
		$d = $this->objDefinition->getSubObjects("grp");

		$import = false;

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
					if($row["import"] == "1")	// import allowed?
					{
						$import = true;
					}
				}
			}
		}
		
		$import = false;
		if (is_array($subobj))
		{
			// show import button if at least one
			// object type can be imported
			if ($import)
			{
				$this->tpl->setCurrentBlock("import_object");
				$this->tpl->setVariable("BTN_IMP", "import");
				$this->tpl->setVariable("TXT_IMP", $this->lng->txt("import"));
				$this->tpl->parseCurrentBlock();
			}

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
	* set Locator
	*
	* @param	object	tree object
	* @param	integer	reference id
	* @access	public
 	*/
	

	/*function setLocator($a_tree = "", $a_id = "")
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
			//$subObj = getObject($a_ref_id);
			$subObj =& $this->ilias->obj_factory->getInstanceByRefId($a_ref_id);

			$path[] = array(
				"id"	 => $a_ref_id,
				"title"  => $this->lng->txt($subObj->getTitle())
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
			$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($_GET["obj_id"]);

			$this->tpl->setCurrentBlock("locator_item");
			$this->tpl->setVariable("ITEM", $obj_data->getTitle());
			// TODO: SCRIPT NAME HAS TO BE VARIABLE!!!
			$this->tpl->setVariable("LINK_ITEM", "adm_object.php?ref_id=".$_GET["ref_id"]."&obj_id=".$_GET["obj_id"]);
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

		//$this->tpl->setVariable("TXT_PATH",$debug.$this->lng->txt($prop_name)." ".strtolower($this->lng->txt("of")));
		$this->tpl->setVariable("TXT_LOCATOR",$debug.$this->lng->txt("locator"));
		$this->tpl->parseCurrentBlock();

	}*/

	/**
	* create new object form
	*/
	function create()
	{
		//TODO: check the acces rights; compare class.ilObjectGUI.php

		global $rbacsystem;

			if(isset($_POST["new_type"]))
			{
				$new_type =  $_POST["new_type"];
			}else{
				$new_type =	 $_GET["type"];
			}
			
			$data = array();
			$data["fields"] = array();
			$data["fields"]["group_name"] = "";
			$data["fields"]["desc"] = "";


			//$this->getTemplateFile("new","group");
			$this->tpl->addBlockFile("CONTENT", "content", "tpl.group_new.html");
			
			infoPanel();
			
			$node = $this->tree->getNodeData($_GET["parent_ref_id"]);
			$this->tpl->setVariable("TXT_PAGEHEADLINE", $node["title"]);	
			
			foreach ($data["fields"] as $key => $val)
			{
				$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
				$this->tpl->setVariable(strtoupper($key), $val);
				
			}

			$stati = array("group_status_public","group_status_private","group_status_closed");

			//build form
			$opts = ilUtil::formSelect(0,"group_status_select",$stati);

			$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
			$this->tpl->setVariable("TXT_GROUP_STATUS", $this->lng->txt("group_status"));
			//$this->tpl->setVariable("FORMACTION", "group.php?cmd=save"."&ref_id=".$_GET["ref_id"].
			//	"&new_type=".$_POST["new_type"]);
			

			$this->tpl->setVariable("FORMACTION", "group.php?gateway=true&cmd=save"."&ref_id=".$_GET["parent_ref_id"].
//=======
//			$this->tpl->setVariable("FORMACTION", "group.php?cmd=save"."&ref_id=".$_GET["parent_ref_id"].

				"&new_type=".$new_type);
				
			$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
			$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
			$this->tpl->parseCurrentBlock();
			$this->tpl->show();
	}
	
	/**
	* function that executes "displayList" 
	* functon ist only executed if $_POST["cmd"] is set to "cancel"
	*/

	function cancelObject()
	{
		$this->displayList();
	}



	/**
	* displays form in which the member-status can be changed
	* @access public
	*/
	function changeMemberObject()
	{
		global $ilias,$tpl;

		include_once "./classes/class.ilTableGUI.php";

		$member_ids = array();
		if(isset($_POST["id"]))
			$member_ids = $_POST["id"];
		else if(isset($_GET["mem_id"]))
			$member_ids[0] = $_GET["mem_id"];

		$newGrp = new ilObjGroup($_GET["ref_id"],true);
		$stati = array(0=>"grp_member_role",1=>"grp_admin_role");

		//build data structure
		foreach($member_ids as $member_id)
		{
			$member =& $ilias->obj_factory->getInstanceByObjId($member_id);
			$mem_status = $newGrp->getMemberStatus($member_id);

			$this->data["data"][$member->getId()]= array(
				"login"        => $member->getLogin(),
				"firstname"       => $member->getFirstname(),
				"lastname"        => $member->getLastname(),
				"grp_role" => ilUtil::formSelect($mem_status,"member_status_select[".$member->getId()."]",$stati,false,true)
				);
			unset($member);
		}

		$this->tpl->addBlockfile("CONTENT", "content", "tpl.grp_chooseuser.html");
		infoPanel();

		$this->tpl->addBlockfile("NEW_MEMBERS_TABLE", "member_table", "tpl.table.html");

		// load template for table content data
		$this->tpl->setVariable("FORMACTION", "group.php?ref_id=".$_GET["ref_id"]."&gateway=true");

		$this->data["buttons"] = array( "updateMemberStatus"  => $this->lng->txt("confirm"),
						"cancel"  => $this->lng->txt("cancel"));

		$this->tpl->setCurrentBlock("tbl_action_row");
		$this->tpl->setVariable("COLUMN_COUNTS",4);
		$this->tpl->setVariable("TPLPATH",$this->ilias->tplPath);

		foreach ($this->data["buttons"] as $name => $value)
		{
			$this->tpl->setCurrentBlock("tbl_action_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}

		// create table
		$tbl = new ilTableGUI($this->data["data"]);
		// title & header columns
		$tbl->setTitle($this->lng->txt("change member status"),"icon_usr_b.gif",$this->lng->txt("change member status"));
		$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		$tbl->setHeaderNames(array($this->lng->txt("firstname"),$this->lng->txt("lastname"),$this->lng->txt("role"),$this->lng->txt("status")));
		$tbl->setHeaderVars(array("firstname","lastname","role","status"),array("ref_id"=>$_GET["ref_id"],"cmd"=>$_GET["cmd"]));

		$tbl->setColumnWidth(array("25%","25%","25%","25%"));

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit(10);
		$tbl->setOffset(0);
		$tbl->setMaxCount(count($this->data["data"]));

		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));

		// render table
		$tbl->render();
		$this->tpl->show();
	}


	function updateMemberStatusObject()
	{
		global $rbacsystem;
		

		if($rbacsystem->checkAccess("write",$this->object->getRefId()) )
			if(isset($_POST["member_status_select"]))
			{
				foreach($_POST["member_status_select"] as $key=>$value)
				{
					$this->object->setMemberStatus($key,$value);
				}
			}
		//TODO: link back
		header("Location: group.php?cmd=displaylist");

	}

		/**
	* displays confirmation formular with users that shall be DEassigned from group
	* @access public
	*/
	function deassignMemberObject()
	{
		$this->tpl->setVariable("FORMACTION", "group.php?ref_id=".$_GET["ref_id"]."&gateway=true");
		$this->tpl->setVariable("FORM_ACTION_METHOD", "post");
		$user_ids = array();

		if(isset($_POST["id"]))
		{
			$user_ids = $_POST["id"];


		}

		else if(isset($_GET["mem_id"]))
			$user_ids = $_GET["mem_id"];
		if(isset($user_ids))
		{
			$confirm = "confirmedDeassignMember";
			$cancel  = "canceled";
			$info	 = "info_delete_sure";
			$status  = "";
			//$this->confirmationObject($user_ids, $confirm, $cancel, $info, $status);
			//$this->tpl->show();


		$this->tpl->addBlockFile("CONTENT", "content", "tpl.confirm_table.html");
		infoPanel();
		$this->tpl->addBlockFile("CONFIRM_TABLE", "confirm_table", "tpl.table.html");
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.grp_tbl_confirm.html");
		sendInfo($this->lng->txt($info));

		$num = 0;
		if(is_array($user_ids))
		{
			foreach($user_ids as $id)
			{
				$this->tpl->setCurrentBlock("tbl_content");
				$this->tpl->setVariable("ROWCOL", ilUtil::switchColor($num,"tblrow2","tblrow1"));
				$num++;
				$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($id);


				$this->tpl->setVariable("TYPE", ilUtil::getImageTagByType("usr",$this->tpl->tplPath));
				$this->tpl->setVariable("TITLE",$obj_data->getTitle());
				$this->tpl->setVariable("DESCRIPTION", $obj_data->getDescription());
				$this->tpl->setVariable("LAST_UPDATE",$obj_data->getLastUpdateDate());
				$this->tpl->parseCurrentBlock();
			}
		}
		else
		{

			$this->tpl->setCurrentBlock("tbl_content");
			$this->tpl->setVariable("ROWCOL", ilUtil::switchColor($num,"tblrow2","tblrow1"));
			$num++;
			$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($user_ids);

			$this->tpl->setVariable("TYPE", ilUtil::getImageTagByType("usr",$this->tpl->tplPath));
			$this->tpl->setVariable("TITLE",$obj_data->getTitle());
			$this->tpl->setVariable("DESCRIPTION", $obj_data->getDescription());
			$this->tpl->setVariable("LAST_UPDATE",$obj_data->getLastUpdateDate());
			$this->tpl->parseCurrentBlock();
		}

		$this->data["buttons"] = array( "ConfirmedDeassignMember"  => $this->lng->txt("confirm"),
						"cancel"  => $this->lng->txt("cancel"));

		$this->tpl->setCurrentBlock("tbl_action_row");
		$this->tpl->setVariable("COLUMN_COUNTS",4);
		$this->tpl->setVariable("TPLPATH",$this->ilias->tplPath);

		foreach ($this->data["buttons"] as $name => $value)
		{
			$this->tpl->setCurrentBlock("tbl_action_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}


		$tbl = new ilTableGUI();

		$tbl->setHeaderNames(array("",$this->lng->txt("title"),$this->lng->txt("description"),$this->lng->txt("last_change")));
		$tbl->setHeaderVars(array("type","title","description","last_change"));
		$tbl->setColumnWidth(array("3%","7%","7%","15%"));

		// control
		//$tbl->setOrderColumn($_GET["sort_by"]);
		//$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($limit);
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->setOffset($offset);
		$tbl->setMaxCount($maxcount);


		$tbl->render();
		$this->tpl->show();

		}
		else
		{
			sendInfo($this->lng->txt("You have to choose at least one user !"),true);
			header("Location: group.php?cmd=displayList");
		}

	}

	/**
	* Deassign members from group
	* @access public
	*/
	function confirmedDeassignMemberObject()
	{
		global $rbacsystem,$ilias;

		if(isset($_SESSION["saved_post"]) )
		{
			//$mem_id = $_SESSION["saved_post"][0];
			foreach($_SESSION["saved_post"] as $mem_id)
			{
				$newGrp = new ilObjGroup($_GET["ref_id"],true);
				//Check if user wants to skip himself
				//if($_SESSION["AccountId"] == $_GET["mem_id"])
				if($_SESSION["AccountId"] == $mem_id)
				{
					if($rbacsystem->checkAccess('leave',$_GET["ref_id"]))
					{
						//check ammount of members
						if(count($newGrp->getGroupMemberIds()) == 1)
						{
							if($rbacsystem->checkAccess('delete',$_GET["ref_id"]))
							{
								//GROUP DELETE
								$this->ilias->raiseError("Gruppe loeschen, da letztes Mitglied!",$this->ilias->error_obj->MESSAGE);
							}
							else
								$this->ilias->raiseError("You do not have the permissions to delete this group!",$this->ilias->error_obj->MESSAGE);
						}
						else
						{
							$role_id = $newGrp->getGroupRoleId($_SESSION["AccountId"]);

							$member_Obj =& $ilias->obj_factory->getInstanceByObjId($role_id);

							if(strcmp($member_Obj->getTitle(), "grp_Member")==0)
							{
								//if(!$newGrp->leaveGroup($_GET["mem_id"]))
								if(!$newGrp->leaveGroup($mem_id))
									$this->ilias->raiseError("Error while attempting to discharge user!",$this->ilias->error_obj->MESSAGE);
							}
							//if user is admin, he has to make another user become admin
							else if(strcmp($member_Obj->getTitle(),"grp_Administrator")==0 )
							{
								if(count($newGrp->getGroupAdminIds()) <= 1)
								{
									if(!isset($_POST["newAdmin_id"]) )
										$this->chooseNewAdmin();
									else
									{
										foreach($_POST["newAdmin_id"] as $newAdmin)
										{
											$newGrp->leaveGroup($newAdmin);
											$newGrp->joinGroup($newAdmin,1); //join as admin
										}
										//remove old admin from group
										//if(!$newGrp->leaveGroup($_GET["mem_id"]))
										if(!$newGrp->leaveGroup($mem_id))
											$this->ilias->raiseError("Error while attempting to discharge user!",$this->ilias->error_obj->MESSAGE);
									}
								}
								else if(!$newGrp->leaveGroup($_SESSION["AccountId"]))
									$this->ilias->raiseError("Error while attempting to discharge user!",$this->ilias->error_obj->MESSAGE);
							}
						}
					}
					else
						$this->ilias->raiseError("You are not allowed to leave this group!",$this->ilias->error_obj->MESSAGE);
				}
				//check if user has the permission to skip other groupmember
				else if($rbacsystem->checkAccess('write',$_GET["ref_id"]))
				{
					//if(!$newGrp->leaveGroup($_GET["mem_id"]))
					if(!$newGrp->leaveGroup($mem_id))
						$this->ilias->raiseError("Error while attempting to discharge user!",$this->ilias->error_obj->MESSAGE);

				}
				else
				{
					$this->ilias->raiseError("You are not allowed to discharge this group member!",$this->ilias->error_obj->MESSAGE);
				}
			}
		}
		header("Location: group.php?cmd=displayList");
	}




	/*
	* save object
	*
	* @access	public
	*/
 function save()

	{	global $rbacsystem;

		//TODO: check the acces rights; compare class.ilObjectGUI.php




		// always call parent method first to create an object_data entry & a reference
		$groupObj = parent::saveObject();
//>>>>>>> 1.9

		$rfoldObj = $groupObj->initRoleFolder();

		// setup rolefolder & default local roles if needed (see ilObjForum & ilObjForumGUI for an example)

		$groupObj->createDefaultGroupRoles($rfoldObj->getRefId());
		$groupObj->joinGroup($this->ilias->account->getId(),1); //join as admin=1

		//0=public,1=private,2=closed
		$groupObj->setGroupStatus($_POST["group_status_select"]);








		$grp_tree = $groupObj->createNewGroupTree($groupObj->getId(),$groupObj->getRefId());
		$groupObj->insertGroupNode($rfoldObj->getId(),$rfoldObj->getRefId(),$groupObj->getId(),$grp_tree);


		// always send a message
		sendInfo($this->lng->txt("grp_added"),true);
		header("location: group.php?cmd=DisplayList");
		exit();
	}

	
	/**
	* displays search form for new users
	* @access public
	*/
	function newMembersObject()
	{
		$this->tpl->addBlockFile("CONTENT", "content","tpl.grp_newmember.html");

		$this->tpl->setVariable("TXT_MEMBER_NAME", $this->lng->txt("Username"));
		$this->tpl->setVariable("TXT_STATUS", $this->lng->txt("Member Status"));

		$radio_member = ilUtil::formRadioButton($_POST["status"] ? 0:1,"status",0);
		$radio_admin = ilUtil::formRadioButton($_POST["status"] ? 1:0,"status",1);
		$this->tpl->setVariable("RADIO_MEMBER", $radio_member);
		$this->tpl->setVariable("RADIO_ADMIN", $radio_admin);
		$this->tpl->setVariable("TXT_MEMBER_STATUS", "Member");
		$this->tpl->setVariable("TXT_ADMIN_STATUS", "Admin");
		$this->tpl->setVariable("TXT_SEARCH", "Search");
		if(isset($_POST["search_user"]) )
			$this->tpl->setVariable("SEARCH_STRING", $_POST["search_user"]);
		else if(isset($_GET["search_user"]) )
			$this->tpl->setVariable("SEARCH_STRING", $_GET["search_user"]);

		$this->tpl->setVariable("FORMACTION_NEW_MEMBER", "adm_object.php?type=grp&cmd=newMembers&ref_id=".$_GET["ref_id"]);//"&search_user=".$_POST["search_user"]
		$this->tpl->parseCurrentBlock();

		//query already started ?
		$this->tpl->show();
		if( (isset($_POST["search_user"]) && isset($_POST["status"]) ) || ( isset($_GET["search_user"]) && isset($_GET["status"]) ) )//&& isset($_GET["ref_id"]) )
		{
			$member_ids = ilObjUser::searchUsers($_POST["search_user"] ? $_POST["search_user"] : $_GET["search_user"]);

			//INTERIMS SOLUTION
			$_SESSION["status"] = $_POST["status"];

			foreach($member_ids as $member)
			{
				$this->data["data"][$member["usr_id"]]= array(
					"check"		=> ilUtil::formCheckBox(0,"user_id[]",$member["usr_id"]),
					"login"        => $member["login"],
					"firstname"       => $member["firstname"],
					"lastname"        => $member["lastname"]
					);

			}

			//display search results
			infoPanel();

			$this->tpl->addBlockfile("NEW_MEMBERS_TABLE", "member_table", "tpl.table.html");
			// load template for table content data

			$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=gateway");

			$this->data["buttons"] = array( "assignMember"  => $this->lng->txt("assign"),
							"canceled"  => $this->lng->txt("cancel"));

			$this->tpl->setCurrentBlock("tbl_action_row");
			$this->tpl->setVariable("COLUMN_COUNTS",4);
			$this->tpl->setVariable("TPLPATH",$this->tplPath);

			foreach ($this->data["buttons"] as $name => $value)
			{
				$this->tpl->setCurrentBlock("tbl_action_btn");
				$this->tpl->setVariable("BTN_NAME",$name);
				$this->tpl->setVariable("BTN_VALUE",$value);
				$this->tpl->parseCurrentBlock();
			}

			//sort data array
			include_once "./include/inc.sort.php";
			$this->data["data"] = sortArray($this->data["data"], $_GET["sort_by"], $_GET["sort_order"]);
			$output = array_slice($this->data["data"],$_GET["offset"],$_GET["limit"]);

			// create table
			include_once "./classes/class.ilTableGUI.php";
			$tbl = new ilTableGUI($output);
			// title & header columns
			$tbl->setTitle($this->lng->txt("member list"),"icon_usr_b.gif",$this->lng->txt("member list"));
			$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
			$tbl->setHeaderNames(array($this->lng->txt("check"),$this->lng->txt("username"),$this->lng->txt("firstname"),$this->lng->txt("lastname")));
			$tbl->setHeaderVars(array("check","login","firstname","lastname"),array("ref_id"=>$_GET["ref_id"],"cmd"=>$_GET["cmd"],"search_user"=>$_POST["search_user"] ? $_POST["search_user"] : $_GET["search_user"],"status"=>$_POST["status"] ? $_POST["status"] : $_GET["status"]));

			$tbl->setColumnWidth(array("5%","25%","35%","35%"));

			// control
			$tbl->setOrderColumn($_GET["sort_by"]);
			$tbl->setOrderDirection($_GET["sort_order"]);
			$tbl->setLimit($_GET["limit"]);
			$tbl->setOffset($_GET["offset"]);
			$tbl->setMaxCount(count($this->data["data"]));

			$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));

			// render table
			$tbl->render();
			$this->tpl->show();


		}
	}


}
?>
