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
class ilLOListGUI
{
	var $tpl;
	var $lng;
	var $objDefinition;
	var $tree;
	var $rbacsystem;
	var $ilias;

	function ilLOListGUI()
	{
		global $objDefinition, $tpl, $lng, $tree, $rbacsystem, $ilias;

		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->ilias =& $ilias;
		$this->objDefinition =& $objDefinition;
		$this->tree =& $tree;
		$this->rbacsystem = $rbacsystem;

		$cmd = $_GET["cmd"];
		if($cmd == "")
		{
			$cmd = "view";
		}
		if($cmd == "post")
		{
			$cmd = key($_POST["cmd"]);
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
			$tpl = new ilTemplate("tpl.lo_list.html", false, false);
			$tpl->show();
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
		$exp = new ilExplorer("lo_list.php?cmd=displayList");

		$this->tpl->addBlockFile("CONTENT", "content", "tpl.explorer.html");
		if ($_GET["expand"] == "")
		{
			$expanded = "1";
		}
		else
			$expanded = $_GET["expand"];

		$exp->setExpand($expanded);
		$exp->setExpandTarget("lo_list.php?cmd=explorer");
		//filter object types
		$exp->addFilter("root");
		$exp->addFilter("cat");
		$exp->addFilter("grp");
		$exp->addFilter("crs");
		//$exp->addFilter("le");
		$exp->setFiltered(true);

		//build html-output
		$exp->setOutput(0);
		$output = $exp->getOutput();
		$this->tpl->setCurrentBlock("content");
		$this->tpl->setVariable("TXT_EXPLORER_HEADER", $this->lng->txt("learning_objects"));
		$this->tpl->setVariable("EXPLORER",$output);
		$this->tpl->setVariable("ACTION", "lo_list.php?cmd=explorer&expand=".$_GET["expand"]);
		$this->tpl->parseCurrentBlock();

		$this->tpl->show();
	}


	/**
	* display list of courses and learning modules
	*/
	function displayList()
	{
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.lo_overview.html");
		// add everywhere wegen sparkassen skin
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");


		// set tabs
		// display different buttons depending on viewmod
		if (!isset($_SESSION["viewmode"]) or $_SESSION["viewmode"] == "flat")
		{
			$ftabtype = "tabactive";
			$ttabtype = "tabinactive";
		}
		else
		{
			$ftabtype = "tabinactive";
			$ttabtype = "tabactive";
		}

		$this->tpl->addBlockFile("TABS", "tabs", "tpl.tabs.html");
		$this->tpl->setCurrentBlock("tab");
		$this->tpl->setVariable("TAB_TYPE", $ttabtype);
		$this->tpl->setVariable("TAB_TARGET", "bottom");
		$this->tpl->setVariable("TAB_LINK", "lo_list.php?viewmode=tree");
		$this->tpl->setVariable("TAB_TEXT", $this->lng->txt("treeview"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("tab");
		$this->tpl->setVariable("TAB_TYPE", $ftabtype);
		$this->tpl->setVariable("TAB_TARGET", "bottom");
		$this->tpl->setVariable("TAB_LINK", "lo_list.php?viewmode=flat");
		$this->tpl->setVariable("TAB_TEXT", $this->lng->txt("flatview"));
		$this->tpl->parseCurrentBlock();

		// set locator
		$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");
		$this->tpl->setVariable("TXT_LOCATOR",$this->lng->txt("locator"));
		$this->tpl->setCurrentBlock("locator_item");
		$this->tpl->setVariable("ITEM", $this->lng->txt("lo_available"));
		$this->tpl->setVariable("LINK_ITEM", "lo_list.php");
		$this->tpl->parseCurrentBlock();

		// display infopanel if something happened
		infoPanel();

		$this->tpl->setCurrentBlock("content");
		$this->tpl->setVariable("TXT_PAGEHEADLINE",  $this->lng->txt("lo_available"));
		//$this->tpl->parseCurrentBlock();			// this line produces an empty <h1></h1>, alex 16.2.03

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

		// display different content depending on viewmode
		switch ($_SESSION["viewmode"])
		{
			case "flat":
				$lr_arr = ilUtil::getObjectsByOperations('lm','visible');
				$lr_arr = ilUtil::getObjectsByOperations('slm','visible');
				$lr_arr = ilUtil::getObjectsByOperations('crs','visible');
				break;

			case "tree":
				//go through valid objects and filter out the lessons only
				$lr_arr = array();
				$objects = $this->tree->getChilds($_GET["ref_id"],"title");

				if (count($objects) > 0)
				{
					foreach ($objects as $key => $object)
					{
						if ((($object["type"] == "lm") ||
							($object["type"] == "slm") ||
							($object["type"] == "crs"))
							&& $this->rbacsystem->checkAccess('visible',$object["child"]))
						{
							$lr_arr[$key] = $object;
						}
					}
				}
				break;
		}

		$maxcount = count($lr_arr);		// for numinfo in table footer
		include_once "./include/inc.sort.php";
		$lr_arr = sortArray($lr_arr,$_GET["sort_by"],$_GET["sort_order"]);
		$lr_arr = array_slice($lr_arr,$offset,$limit);

		// load template for table
		$this->tpl->addBlockfile("LO_TABLE", "lo_table", "tpl.table.html");
		$this->tpl->setVariable("FORMACTION", "lo_list.php?cmd=post&ref_id=".$_GET["ref_id"]);
		$this->tpl->setVariable("ACTIONTARGET", "bottom");


		$lr_num = count($lr_arr);

		// render table content data
		if ($lr_num > 0)
		{
			$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.lo_tbl_rows.html");

			// counter for rowcolor change
			$num = 0;

			foreach ($lr_arr as $lr_data)
			{
				$this->tpl->setCurrentBlock("tbl_content");

				// change row color
				$this->tpl->setVariable("ROWCOL", ilUtil::switchColor($num,"tblrow2","tblrow1"));
				$num++;

				$obj_icon = "icon_".$lr_data["type"]."_b.gif";

				$this->tpl->setVariable("TITLE", $lr_data["title"]);

				// learning modules
				if ($lr_data["type"] == "lm")
				{
					$obj_link = "content/lm_presentation.php?ref_id=".$lr_data["ref_id"];
					$this->tpl->setVariable("VIEW_LINK", $obj_link);
					$this->tpl->setVariable("VIEW_TARGET", "_top");
					$this->tpl->setVariable("EDIT_LINK","content/lm_edit.php?ref_id=".$lr_data["ref_id"]);
					$this->tpl->setVariable("EDIT_TARGET","bottom");
					$this->tpl->setVariable("TXT_EDIT", "(".$this->lng->txt("edit").")");
					if (!$this->ilias->account->isDesktopItem($lr_data["ref_id"], "lm"))
					{
						$this->tpl->setVariable("TO_DESK_LINK", "lo_list.php?cmd=addToDesk&ref_id=".$lr_data["ref_id"]."&type=lm");
						$this->tpl->setVariable("TXT_TO_DESK", "(".$this->lng->txt("to_desktop").")");
					}
				}

				// scorm learning modules
				if ($lr_data["type"] == "slm")
				{
					$obj_link = "content/scorm_presentation.php?ref_id=".$lr_data["ref_id"];
					$this->tpl->setVariable("VIEW_LINK", $obj_link);
					$this->tpl->setVariable("VIEW_TARGET", "bottom");
				}

				// scorm learning modules
				if ($lr_data["type"] == "crs")
				{
					$obj_link = "lo_list.php?cmd=displayList&ref_id=".$lr_data["ref_id"];
					$this->tpl->setVariable("VIEW_LINK", $obj_link);
				}

				$this->tpl->setVariable("IMG", $obj_icon);
				$this->tpl->setVariable("ALT_IMG", $this->lng->txt("obj_".$lr_data["type"]));
				$this->tpl->setVariable("DESCRIPTION", $lr_data["description"]);
				$this->tpl->setVariable("STATUS", "N/A");
				$this->tpl->setVariable("LAST_VISIT", "N/A");
				$this->tpl->setVariable("LAST_CHANGE", ilFormat::formatDate($lr_data["last_update"]));
				$this->tpl->setVariable("CONTEXTPATH", $this->getContextPath($lr_data["ref_id"]));
				$this->tpl->parseCurrentBlock();
			}
		}
		else
		{

			$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.no_objects_row.html");
			$this->tpl->setCurrentBlock("tbl_content");
			$this->tpl->setVariable("ROWCOL", "tblrow1");
			$this->tpl->setVariable("COLSPAN", "6");
			$this->tpl->setVariable("TXT_NO_OBJECTS",$this->lng->txt("lo_no_content"));
			$this->tpl->parseCurrentBlock();
		}

		$this->showPossibleSubObjects();

		// create table
		$tbl = new ilTableGUI();

		// title & header columns
		//$tbl->setTitle($this->lng->txt("lo_available"),"icon_crs_b.gif",$this->lng->txt("lo_available"));
		//$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		$tbl->setHeaderNames(array($this->lng->txt("title"),$this->lng->txt("description"),$this->lng->txt("status"),$this->lng->txt("last_visit"),$this->lng->txt("last_change"),$this->lng->txt("context")));
		$tbl->setHeaderVars(array("title","description","status","last_visit","last_update","context"),
			array("cmd" => "displayList", "ref_id" => $_GET["ref_id"]));
		//$tbl->setColumnWidth(array("7%","7%","15%","31%","6%","17%"));

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($limit);
		$tbl->setOffset($offset);
		$tbl->setMaxCount($maxcount);

		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		//$tbl->disable("content");
		$tbl->disable("title");

		// render table
		$tbl->render();

		$this->tpl->show();
	}

	function create()
	{
		header("Location: obj_location_new.php?new_type=".$_POST["new_type"]."&from=lo_list.php");
		exit;
	}

	function addToDesk()
	{
		$this->ilias->account->addDesktopItem($_GET["ref_id"], $_GET["type"]);
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

		$d = $this->objDefinition->getSubObjects("cat");

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
					if($row["name"] == "lm" || $row["name"] == "crs")
					{
						$subobj[] = $row["name"];
					}
				}
			}
		}

		if (is_array($subobj))
		{
			//build form
			$opts = ilUtil::formSelect(12,"new_type",$subobj);
			$this->tpl->setVariable("COLUMN_COUNTS", 6);
			$this->tpl->setCurrentBlock("add_object");
			$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
			$this->tpl->setVariable("BTN_NAME", "create");
			$this->tpl->setVariable("TXT_ADD", $this->lng->txt("add"));
			$this->tpl->parseCurrentBlock();
		}
	}

}
?>
