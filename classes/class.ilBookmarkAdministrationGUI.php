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
* GUI class for personal bookmark administration. It manages folders and bookmarks
* with the help of the two corresponding core classes ilBookmarkFolder and ilBookmark.
* Their methods are called in this User Interface class.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package application
*/

require_once ("classes/class.ilBookmarkExplorer.php");
require_once ("classes/class.ilBookmarkFolder.php");
require_once ("classes/class.ilBookmark.php");
require_once ("classes/class.ilTableGUI.php");

class ilBookmarkAdministrationGUI
{
	/**
	* User Id
	* @var integer
	* @access public
	*/
	var $user_id;

	/**
	* ilias object
	* @var object ilias
	* @access public
	*/
	var $ilias;
	var $tpl;
	var $lng;

	var $tree;
	var $id;
	var $data;

	/**
	* Constructor
	* @access	public
	* @param	integer		user_id (optional)
	*/
	function ilBookmarkAdministrationGUI($bmf_id = 0)
	{
		global $ilias, $tpl, $lng;

		// if no bookmark folder id is given, take dummy root node id (that is 1)
		if (empty($bmf_id))
		{
			$bmf_id = 1;
		}

		// initiate variables
		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->user_id = $_SESSION["AccountId"];
		$this->id = $bmf_id;

		$this->tree = new ilTree($_SESSION["AccountId"]);
		$this->tree->setTableNames('bookmark_tree','bookmark_data');
		$this->root_id = $this->tree->readRootId();

	}


	/**
	* output main frameset of bookmark administration
	* left frame: explorer tree of bookmark folders
	* right frame: content of bookmark folders
	*/
	function frameset()
	{
		$this->tpl = new ilTemplate("tpl.bookmark_frameset.html", false, false);
	}

	/**
	* output explorer tree with bookmark folders
	*/
	function explorer()
	{
		//$this->tpl = new ilTemplate("tpl.explorer.html", false, false);
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.explorer.html");

		$exp = new ilBookmarkExplorer("usr_bookmarks.php",$_SESSION["AccountId"]);
		$exp->setTargetGet("bmf_id");

		if ($_GET["mexpand"] == "")
		{
			$mtree = new ilTree($_SESSION["AccountId"]);
			$mtree->setTableNames('bookmark_tree','bookmark_data');
			$expanded = $mtree->readRootId();
		}
		else
		{
			$expanded = $_GET["mexpand"];
		}

		$exp->setExpand($expanded);

		// build html-output
		$exp->setOutput(0);
		$output = $exp->getOutput();

		$this->tpl->setCurrentBlock("content");
		$this->tpl->setVariable("TXT_EXPLORER_HEADER", $this->lng->txt("bookmarks"));
		$this->tpl->setVariable("EXP_REFRESH", $this->lng->txt("refresh"));
		$this->tpl->setVariable("EXPLORER",$output);
		$this->tpl->setVariable("ACTION", "usr_bookmarks.php?cmd=explorer&mexpand=".$_GET["mexpand"]);
		$this->tpl->parseCurrentBlock();
	}


	/**
	* output main header (title and locator)
	*/
	function main_header()
	{
		global $lng, $tpl;

		$this->tpl->addBlockFile("CONTENT", "content", "tpl.bookmarks.html");

		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");

		// output tabs
		//$this->setTabs();

		// output locator
		$this->displayLocator();

		// output message
		if($this->message)
		{
			sendInfo($this->message);
		}

		// display infopanel if something happened
		infoPanel();

		$this->tpl->setVariable("HEADER",  $this->lng->txt("personal_desktop"));

		//$this->tpl->addBlockFile("OBJECTS", "objects", "tpl.table.html");
		//$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");

		// display tabs
		include "./include/inc.personaldesktop_buttons.php";

	}


	/*
	* display content of bookmark folder
	*/
	function view($a_output_header = true)
	{
		global $tree, $rbacsystem;

		$mtree = new ilTree($_SESSION["AccountId"]);
		$mtree->setTableNames('bookmark_tree','bookmark_data');

		if ($a_output_header)
		{
			$this->main_header();
		}
		$this->tpl->addBlockFile("OBJECTS", "objects", "tpl.table.html");

		$this->tpl->setVariable("FORMACTION", "usr_bookmarks.php?bmf_id=".$this->id."&cmd=post");

		$objects = ilBookmarkFolder::getObjects($this->id);
		
		$this->tpl->setCurrentBlock("objects");
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.bookmark_row.html");
		$cnt = 0;
		
		// return to parent folder
		if ($this->id != $mtree->readRootId() || $this->id =="")
		{			
			$this->tpl->setCurrentBlock("tbl_content");
			
			// color changing
			$css_row = ilUtil::switchColor($cnt++,"tblrow1","tblrow2");

			$this->tpl->setVariable("CHECKBOX", "&nbsp;");
			$this->tpl->setVariable("ROWCOL", $css_row);

			$val = ilUtil::getImagePath("icon_cat.gif");
			$this->tpl->setVariable("IMG", $val);
			
			// title
			$link = "usr_bookmarks.php?bmf_id=".$mtree->getParentId($this->id);
			$this->tpl->setVariable("TXT_TITLE", "..");
			$this->tpl->setVariable("LINK_TARGET", $link);

			$this->tpl->parseCurrentBlock();
		}

		foreach ($objects as $key => $object)
		{
			// type icon
			$link = ($object["type"] == "bmf") ?
				"usr_bookmarks.php?cmd=editForm&type=bmf&obj_id=".$object["obj_id"]."&bmf_id=".$this->id :
				"usr_bookmarks.php?cmd=editForm&type=bm&obj_id=".$object["obj_id"]."&bmf_id=".$this->id;

			
			$this->tpl->setCurrentBlock("tbl_content");
			
			// edit link
			$this->tpl->setCurrentBlock("edit");
			$this->tpl->setVariable("TXT_EDIT", $this->lng->txt("edit"));
			$this->tpl->setVariable("LINK_EDIT", $link);
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("tbl_content");


			// color changing
			$css_row = ilUtil::switchColor($cnt++,"tblrow1","tblrow2");

			// surpress checkbox for particular object types
			//$this->tpl->setVariable("CHECKBOX_ID", $object["type"].":".$object["obj_id"]);
			$this->tpl->setVariable("CHECKBOX",ilUtil::formCheckBox("", "id[]", $object["type"].":".$object["obj_id"]));
			$this->tpl->setVariable("ROWCOL", $css_row);

			$img_type = ($object["type"] == "bmf") ? "cat" : $object["type"];
			$val = ilUtil::getImagePath("icon_".$img_type.".gif");
			$this->tpl->setVariable("IMG", $val);
			
			// title
			$link = ($object["type"] == "bmf") ?
				"usr_bookmarks.php?bmf_id=".$object["obj_id"] :
				$object["target"];
			$this->tpl->setVariable("TXT_TITLE", $object["title"]);
			$this->tpl->setVariable("LINK_TARGET", $link);

			// target
			$this->tpl->setVariable("TXT_TARGET", $object["target"]);

			$this->tpl->parseCurrentBlock();
		}

		$tbl = new ilTableGUI();

		if (!empty($this->id) && $this->id != 1)
		{
			$BookmarkFolder = new ilBookmarkFolder($this->id);
			$addstr = " ".$this->lng->txt("in")." ".$BookmarkFolder->getTitle();
		}
		else
		{
			$addstr = "";
		}

		// title & header columns
		$tbl->setTitle($this->lng->txt("bookmarks").$addstr,
			"icon_bm_b.gif", $this->lng->txt("bookmarks"));
		//$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		$tbl->setHeaderNames(array("", $this->lng->txt("type"), $this->lng->txt("title"),
			$this->lng->txt("bookmark_target")));
		$tbl->setHeaderVars(array("", "type", "title", "target"),
			array("bmf_id" => $this->id));
		$tbl->setColumnWidth(array("1%", "1%", "49%", "49%"));

		//$tbl->setOrderColumn($_GET["sort_by"]);
		//$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($limit);
		$tbl->setOffset($offset);
		$tbl->setMaxCount($maxcount);

		// footer
		$tbl->setFooter("tblfooter", $this->lng->txt("previous"),$this->lng->txt("next"));
		//$tbl->disable("content");
		$tbl->disable("footer");

		// render table
		$tbl->render();


		// SHOW POSSIBLE SUB OBJECTS
		$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
		$this->tpl->setVariable("NUM_COLS", 4);
		$this->showPossibleSubObjects();

		$this->tpl->parseCurrentBlock();
	}

	/**
	* output a cell in object list
	*/
	function add_cell($val, $link = "")
	{
		if (!empty($link))
		{
			$this->tpl->setCurrentBlock("begin_link");
			$this->tpl->setVariable("LINK_TARGET", $link);
			$this->tpl->parseCurrentBlock();
			$this->tpl->touchBlock("end_link");
		}

		$this->tpl->setCurrentBlock("text");
		$this->tpl->setVariable("TEXT_CONTENT", $val);
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("table_cell");
		$this->tpl->parseCurrentBlock();
	}

	/**
	* display locator
	*/
	function displayLocator()
	{
		global $lng;

		if (empty($this->id))
		{
			return;
		}

		$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

		$path = $this->tree->getPathFull($this->id);

		$modifier = 1;

		$this->tpl->setVariable("TXT_LOCATOR",$this->lng->txt("locator"));
		$this->tpl->touchBlock("locator_separator");
		$this->tpl->setCurrentBlock("locator_item");
		$this->tpl->setVariable("ITEM", $this->lng->txt("personal_desktop"));
		$this->tpl->setVariable("LINK_ITEM", "usr_personaldesktop.php");
		$this->tpl->setVariable("LINK_TARGET","target=\"bottom\"");
		$this->tpl->parseCurrentBlock();

		foreach ($path as $key => $row)
		{
			if ($key < count($path)-$modifier)
			{
				$this->tpl->touchBlock("locator_separator");
			}

			$this->tpl->setCurrentBlock("locator_item");
			$title = ($row["child"] == 1) ?
				$lng->txt("bookmarks_of")." ".$this->ilias->account->getFullname() :
				$row["title"];
			$this->tpl->setVariable("ITEM", $title);
			// TODO: SCRIPT NAME HAS TO BE VARIABLE!!!
			$this->tpl->setVariable("LINK_ITEM", "usr_bookmarks.php?bmf_id=".$row["child"]);
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("locator");

		$this->tpl->parseCurrentBlock();
	}


	/**
	* display new bookmark folder form
	*/
	function newFormBookmarkFolder()
	{
		global $tpl, $lng;

		$this->main_header();

		$tpl->addBlockFile("OBJECTS", "objects", "tpl.bookmark_newfolder.html");
		$tpl->setVariable("TITLE", $this->get_last("title", ""));
		$tpl->setVariable("TXT_TITLE", $lng->txt("title"));
		$tpl->setVariable("TXT_SAVE", $lng->txt("save"));
		$tpl->setVariable("TXT_FOLDER_NEW", $lng->txt("bookmark_folder_new"));
		$tpl->setVariable("FORMACTION", "usr_bookmarks.php?bmf_id=".$this->id."&cmd=createBookmarkFolder");
	}


	/**
	* display edit bookmark folder form
	*/
	function editFormBookmarkFolder()
	{
		global $tpl, $lng;

		$this->main_header();

		$tpl->addBlockFile("OBJECTS", "objects", "tpl.bookmark_newfolder.html");

		$bmf = new ilBookmarkFolder($_GET["obj_id"]);

		$tpl->setVariable("TXT_TITLE", $lng->txt("title"));
		$tpl->setVariable("TITLE", $this->get_last("title", $bmf->getTitle()));
		$tpl->setVariable("TXT_SAVE", $lng->txt("save"));
		$tpl->setVariable("TXT_FOLDER_NEW", $lng->txt("bookmark_folder_edit"));
		$tpl->setVariable("FORMACTION", "usr_bookmarks.php?obj_id=".$_GET["obj_id"].
			"&bmf_id=".$this->id."&cmd=updateBookmarkFolder");
	}


	/**
	* display new bookmark form
	*/
	function newFormBookmark()
	{
		global $tpl, $lng;

		$this->main_header();

		$tpl->addBlockFile("OBJECTS", "objects", "tpl.bookmark_new.html");
		$tpl->setVariable("TXT_BOOKMARK_NEW", $lng->txt("bookmark_new"));
		$tpl->setVariable("TXT_TARGET", $lng->txt("bookmark_target"));
		$tpl->setVariable("TXT_TITLE", $lng->txt("title"));

		$tpl->setVariable("TITLE", $this->get_last("title", ""));
		$tpl->setVariable("TARGET", $this->get_last("target", "http://"));

		$tpl->setVariable("TXT_SAVE", $lng->txt("save"));

		$tpl->setVariable("FORMACTION", "usr_bookmarks.php?bmf_id=".$this->id."&cmd=createBookmark");
		$tpl->parseCurrentBlock();
	}


	/**
	* get stored post var in case of an error/warning otherwise return passed value
	*/
	function get_last($a_var, $a_value)
	{
		return 	(!empty($_SESSION["message"])) ?
				($_SESSION["error_post_vars"][$a_var]) :
				$a_value;
	}

	/**
	* display edit bookmark form
	*/
	function editFormBookmark()
	{
		global $tpl, $lng;

		$this->main_header();

		$tpl->addBlockFile("OBJECTS", "objects", "tpl.bookmark_new.html");

		$tpl->setVariable("TXT_BOOKMARK_NEW", $lng->txt("bookmark_edit"));
		$tpl->setVariable("TXT_TARGET", $lng->txt("bookmark_target"));
		$tpl->setVariable("TXT_TITLE", $lng->txt("title"));

		$Bookmark = new ilBookmark($_GET["obj_id"]);
		$tpl->setVariable("TITLE", $this->get_last("title", $Bookmark->getTitle()));
		$tpl->setVariable("TARGET", $this->get_last("target", $Bookmark->getTarget()));

		$tpl->setVariable("TXT_SAVE", $lng->txt("save"));

		$tpl->setVariable("FORMACTION", "usr_bookmarks.php?obj_id=".$_GET["obj_id"].
			"&bmf_id=".$this->id."&cmd=updateBookmark");
		$tpl->parseCurrentBlock();
	}


	/**
	* create new bookmark folder in db
	*/
	function createBookmarkFolder()
	{
		// check title
		if (empty($_POST["title"]))
		{
			//$this->ilias->raiseError($this->lng->txt("please_enter_title"),$this->ilias->error_obj->MESSAGE);
			sendInfo($this->lng->txt("please_enter_title"), true);
			ilUtil::redirect("usr_bookmarks.php?bmf_id=".$this->id."&cmd=newForm&type=bmf");
		}

		// create bookmark folder
		$bmf = new ilBookmarkFolder();
		$bmf->setTitle($_POST["title"]);
		$bmf->setParent($this->id);

		$bmf->create();

		$this->view();
	}


	/**
	* update bookmark folder
	*/
	function updateBookmarkFolder()
	{
		// check title
		if (empty($_POST["title"]))
		{
			$this->ilias->raiseError($this->lng->txt("please_enter_title"),$this->ilias->error_obj->MESSAGE);
		}

		// update bookmark folder
		$bmf = new ilBookmarkFolder($_GET["obj_id"]);
		$bmf->setTitle($_POST["title"]);
		$bmf->update();

		$this->view();
	}


	/**
	* create new bookmark in db
	*/
	function createBookmark()
	{
		// check title and target
		if (empty($_POST["title"]))
		{
			//$this->ilias->raiseError($this->lng->txt("please_enter_title"),$this->ilias->error_obj->MESSAGE);
			sendInfo($this->lng->txt("please_enter_title"), true);
			ilUtil::redirect("usr_bookmarks.php?bmf_id=".$this->id."&cmd=newForm&type=bm");
		}
		if (empty($_POST["target"]))
		{
			//$this->ilias->raiseError($this->lng->txt("please_enter_target"),$this->ilias->error_obj->MESSAGE);
			sendInfo($this->lng->txt("please_enter_target"), true);
			ilUtil::redirect("usr_bookmarks.php?bmf_id=".$this->id."&cmd=newForm&type=bm");
		}

		// create bookmark
		$bm = new ilBookmark();
		$bm->setTitle($_POST["title"]);
		$bm->setTarget($_POST["target"]);
		$bm->setParent($this->id);
		$bm->create();

		$this->view();
	}

	/**
	* update bookmark in db
	*/
	function updateBookmark()
	{
		// check title and target
		if (empty($_POST["title"]))
		{
			$this->ilias->raiseError($this->lng->txt("please_enter_title"),$this->ilias->error_obj->MESSAGE);
		}
		if (empty($_POST["target"]))
		{
			$this->ilias->raiseError($this->lng->txt("please_enter_target"),$this->ilias->error_obj->MESSAGE);
		}

		// update bookmark
		$bm = new ilBookmark($_GET["obj_id"]);
		$bm->setTitle($_POST["title"]);
		$bm->setTarget($_POST["target"]);
		$bm->update();

		$this->view();
	}

	/**
	* display deletion conformation screen
	*/
	function delete()
	{

		$this->main_header();

		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		$this->tpl->addBlockFile("OBJECTS", "objects", "tpl.obj_confirm.html");

		sendInfo($this->lng->txt("info_delete_sure"));
		$this->tpl->setVariable("FORMACTION", "usr_bookmarks.php?bmf_id=".$this->id."&cmd=post");

		// output table header
		$cols = array("type", "title", "bookmark_target");
		foreach ($cols as $key)
		{
			$this->tpl->setCurrentBlock("table_header");
			$this->tpl->setVariable("TEXT",$this->lng->txt($key));
			$this->tpl->parseCurrentBlock();
		}

		$_SESSION["saved_post"] = $_POST["id"];

		foreach($_POST["id"] as $id)
		{
			list($type, $obj_id) = explode(":", $id);
			switch($type)
			{
				case "bmf":
					$BookmarkFolder = new ilBookmarkFolder($obj_id);
					$title = $BookmarkFolder->getTitle();
					$target = "&nbsp;";
					unset($BookmarkFolder);
					break;

				case "bm":
					$Bookmark = new ilBookmark($obj_id);
					$title = $Bookmark->getTitle();
					$target = $Bookmark->getTarget();
					unset($Bookmark);
					break;
			}

			// output type icon
			$this->tpl->setCurrentBlock("table_cell");
			$img_type = ($type == "bmf") ? "cat" : $type;
			$this->tpl->setVariable("TEXT_CONTENT",ilUtil::getImageTagByType($img_type, $this->tpl->tplPath));
			$this->tpl->parseCurrentBlock();

			// output title
			$this->tpl->setCurrentBlock("table_cell");
			$this->tpl->setVariable("TEXT_CONTENT", $title);
			$this->tpl->parseCurrentBlock();

			// output target
			$this->tpl->setCurrentBlock("table_cell");
			$this->tpl->setVariable("TEXT_CONTENT", $target);
			$this->tpl->parseCurrentBlock();

			// output table row
			$this->tpl->setCurrentBlock("table_row");
			$this->tpl->setVariable("CSS_ROW",ilUtil::switchColor(++$counter,"tblrow1","tblrow2"));
			$this->tpl->parseCurrentBlock();
		}

		// cancel and confirm button
		$buttons = array( "cancel"  => $this->lng->txt("cancel"),
			"confirm"  => $this->lng->txt("confirm"));

		$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
		foreach($buttons as $name => $value)
		{
			$this->tpl->setCurrentBlock("operation_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}

	}

	/**
	* cancel deletion
	*/
	function cancel()
	{
		session_unregister("saved_post");
		$this->view();
	}

	/**
	* deletion confirmed -> delete folders / bookmarks
	*/
	function confirm()
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
			list($type, $id) = explode(":", $id);

			// get node data and subtree nodes
			$node_data = $this->tree->getNodeData($id);
			$subtree_nodes = $this->tree->getSubTree($node_data);

			// delete tree
			$this->tree->deleteTree($node_data);

			// delete objects of subtree nodes
			foreach ($subtree_nodes as $node)
			{
				switch ($node["type"])
				{
					case "bmf":
						$BookmarkFolder = new ilBookmarkFolder($node["obj_id"]);
						$BookmarkFolder->delete();
						break;

					case "bm":
						$Bookmark = new ilBookmark($node["obj_id"]);
						$Bookmark->delete();
						break;
				}
			}
		}

		$this->main_header();

		// Feedback
		sendInfo($this->lng->txt("info_deleted"),true);

		$this->view(false);
	}



	/**
	* display copy, paste, ... actions
	*/
	function showActions()
	{
		global $objDefinition;

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

		$d = $objDefinition->getActions("bmf");

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

	/**
	* display subobject addition selection
	*/
	function showPossibleSubObjects()
	{
		global $objDefinition;

		$d = $objDefinition->getCreatableSubObjects("bmf");

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
			$opts = ilUtil::formSelect(12,"type",$subobj);

			$this->tpl->setCurrentBlock("add_object");
			$this->tpl->setVariable("COLUMN_COUNTS", 7);
			$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
			$this->tpl->setVariable("BTN_NAME", "newForm");
			$this->tpl->setVariable("TXT_ADD", $this->lng->txt("add"));
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable("TPLPATH",$this->tpl->tplPath);

		$this->tpl->setCurrentBlock("tbl_action_select");
		//$this->tpl->setVariable("SELECT_ACTION",ilUtil::formSelect("","action_type",$actions,false,true));
		$this->tpl->setVariable("BTN_NAME","delete");
		$this->tpl->setVariable("BTN_VALUE",$this->lng->txt("delete"));
		$this->tpl->parseCurrentBlock();

	}
}
?>
