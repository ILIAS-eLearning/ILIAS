<?php
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

require_once ("./classes/class.ilBookmarkFolder.php");
require_once ("./classes/class.ilBookmark.php");

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

		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		//$title = $this->object->getTitle();

		// catch feedback message
		//sendInfo();

		//if (!empty($title))
		//{
			$this->tpl->setVariable("HEADER", $lng->txt("bookmarks"));
		//}
		$this->displayLocator();

	}

	/*
	* display content of bookmark folder
	*/
	function view()
	{
		global $tree, $rbacsystem;

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.obj_view.html");
		$num = 0;


		$this->tpl->setVariable("FORMACTION", "bookmarks.php?bmf_id=".$this->id."&cmd=post");

		//table header
		$this->tpl->setCurrentBlock("table_header_cell");
		$cols = array("", "type", "title", "bookmark_target");
		foreach ($cols as $key)
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
			$this->tpl->setVariable("HEADER_LINK", "bookmarks.php?bmf_id=".$this->id."&order=type&direction=".
							  $_GET["dir"]."&cmd=".$_GET["cmd"]);

			$this->tpl->parseCurrentBlock();
		}


		$this->objectList = array();
		$this->data["data"] = array();
		$this->data["ctrl"] = array();
		$childs = $this->tree->getChilds($this->id, "title");

		$objects = array();
		$bookmarks = array();
		foreach ($childs as $key => $child)
		{
			switch ($child["type"])
			{
				case "bmf":
					$objects[] = $child;
					break;
					
				case "bm":
					$bookmarks[] = $child;
					break;
			}
		}
		foreach ($bookmarks as $key => $bookmark)
		{
			$objects[] = $bookmark;
		}

		$cnt = 0;
		foreach ($objects as $key => $object)
		{
			// color changing
			$css_row = ilUtil::switchColor($cnt++,"tblrow1","tblrow2");

			// surpress checkbox for particular object types
			$this->tpl->setCurrentBlock("checkbox");
			$this->tpl->setVariable("CHECKBOX_ID", $object["type"].":".$object["obj_id"]);
			$this->tpl->setVariable("CSS_ROW", $css_row);
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("table_cell");
			$this->tpl->parseCurrentBlock();

			//foreach ($data as $key => $val)
			//{
				// process clipboard information
				/*
				if (isset($_SESSION["clipboard"]))
				{
					$cmd = $_SESSION["clipboard"]["cmd"];
					$parent = $_SESSION["clipboard"]["parent"];

					foreach ($_SESSION["clipboard"]["ref_ids"] as $clip_id)
					{
						if ($ctrl["ref_id"] == $clip_id)
						{
							if ($cmd == "cut" and $key == "title")
							{
								$val = "<del>".$val."</del>";
							}

							if ($cmd == "copy" and $key == "title")
							{
								$val = "<font color=\"green\">+</font>  ".$val;
							}

							if ($cmd == "link" and $key == "title")
							{
								$val = "<font color=\"black\"><</font> ".$val;
							}
						}
					}
				}*/
				// 

				// type icon
				$link = ($object["type"] == "bmf") ?
					"bookmarks.php?cmd=editForm&type=bmf&obj_id=".$object["obj_id"]."&bmf_id=".$this->id :
					"bookmarks.php?cmd=editForm&type=bm&obj_id=".$object["obj_id"]."&bmf_id=".$this->id;
				$img_type = ($object["type"] == "bmf") ? "cat" : $object["type"];
				$val = ilUtil::getImageTagByType($img_type, $this->tpl->tplPath);

				$this->add_cell($val, $link);
				
				// title
				$link = ($object["type"] == "bmf") ?
					"bookmarks.php?bmf_id=".$object["obj_id"] :
					$object["target"];
				$this->add_cell($object["title"], $link);

				// target
				$this->add_cell($object["target"], $object["target"]);

			//} //foreach

			$this->tpl->setCurrentBlock("table_row");
			$this->tpl->setVariable("CSS_ROW", $css_row);
			$this->tpl->parseCurrentBlock();
		}
		if(count($objects) == 0)
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
	* output a cell in object list
	*/
	function add_cell($val, $link = "")
	{
		if(!empty($link))
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

		if(empty($this->id))
		{
			return;
		}

		$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

		$path = $this->tree->getPathFull($this->id);

		$modifier = 1;
		/*
		if (isset($_GET["obj_id"]))
		{
			$modifier = 0;
		}*/

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
			$this->tpl->setVariable("LINK_ITEM", "bookmarks.php?bmf_id=".$row["child"]);
			$this->tpl->parseCurrentBlock();

		}

		/*
		if (isset($_GET["obj_id"]))
		{
			$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($_GET["obj_id"]);

			$this->tpl->setCurrentBlock("locator_item");
			$this->tpl->setVariable("ITEM", $obj_data->getTitle());
			// TODO: SCRIPT NAME HAS TO BE VARIABLE!!!
			$this->tpl->setVariable("LINK_ITEM", "adm_object.php?ref_id=".$row["ref_id"]."&obj_id=".$_GET["obj_id"]);
			$this->tpl->parseCurrentBlock();
		}*/

		$this->tpl->setCurrentBlock("locator");

		//$prop_name = $this->objDefinition->getPropertyName($_GET["cmd"],$this->type);

		/*
		if ($_GET["cmd"] == "confirmDeleteAdm")
		{
			$prop_name = "delete_object";
		} */

		//$this->tpl->setVariable("TXT_PATH",$this->lng->txt($prop_name)." ".strtolower($this->lng->txt("of")));
		$this->tpl->parseCurrentBlock();
	}


	/**
	* display new bookmark folder form
	*/
	function newFormBookmarkFolder()
	{
		global $tpl, $lng;

		$tpl->addBlockFile("ADM_CONTENT", "ADM_content", "tpl.bookmark_newfolder.html");

		$tpl->setVariable("TXT_TITLE", $lng->txt("title"));
		$tpl->setVariable("TXT_SAVE", $lng->txt("save"));
		$tpl->setVariable("TXT_FOLDER_NEW", $lng->txt("bookmark_folder_new"));
		$tpl->setVariable("FORMACTION", "bookmarks.php?bmf_id=".$this->id."&cmd=createBookmarkFolder");
	}


	/**
	* display edit bookmark folder form
	*/
	function editFormBookmarkFolder()
	{
		global $tpl, $lng;

		$tpl->addBlockFile("ADM_CONTENT", "ADM_content", "tpl.bookmark_newfolder.html");

		$bmf = new ilBookmarkFolder($_GET["obj_id"]);

		$tpl->setVariable("TXT_TITLE", $lng->txt("title"));
		$tpl->setVariable("TITLE", $bmf->getTitle());
		$tpl->setVariable("TXT_SAVE", $lng->txt("save"));
		$tpl->setVariable("TXT_FOLDER_NEW", $lng->txt("bookmark_folder_edit"));
		$tpl->setVariable("FORMACTION", "bookmarks.php?obj_id=".$_GET["obj_id"].
			"&bmf_id=".$this->id."&cmd=updateBookmarkFolder");
	}


	/**
	* display new bookmark form
	*/
	function newFormBookmark()
	{
		global $tpl, $lng;

		$tpl->addBlockFile("ADM_CONTENT", "ADM_content", "tpl.bookmark_new.html");

		$tpl->setVariable("TXT_BOOKMARK_NEW", $lng->txt("bookmark_new"));
		$tpl->setVariable("TXT_TARGET", $lng->txt("bookmark_target"));
		$tpl->setVariable("TXT_TITLE", $lng->txt("title"));
		$tpl->setVariable("TARGET", "http://");

		$tpl->setVariable("TXT_SAVE", $lng->txt("save"));

		$tpl->setVariable("FORMACTION", "bookmarks.php?bmf_id=".$this->id."&cmd=createBookmark");
		$tpl->parseCurrentBlock();
	}

	/**
	* display edit bookmark form
	*/
	function editFormBookmark()
	{
		global $tpl, $lng;

		$tpl->addBlockFile("ADM_CONTENT", "ADM_content", "tpl.bookmark_new.html");

		$tpl->setVariable("TXT_BOOKMARK_NEW", $lng->txt("bookmark_edit"));
		$tpl->setVariable("TXT_TARGET", $lng->txt("bookmark_target"));
		$tpl->setVariable("TXT_TITLE", $lng->txt("title"));

		$Bookmark = new ilBookmark($_GET["obj_id"]);
		$tpl->setVariable("TITLE", $Bookmark->getTitle());
		$tpl->setVariable("TARGET", $Bookmark->getTarget());

		$tpl->setVariable("TXT_SAVE", $lng->txt("save"));

		$tpl->setVariable("FORMACTION", "bookmarks.php?obj_id=".$_GET["obj_id"].
			"&bmf_id=".$this->id."&cmd=updateBookmark");
		$tpl->parseCurrentBlock();
	}


	/**
	* create new bookmark folder in db
	*/
	function createBookmarkFolder()
	{
		$bmf = new ilBookmarkFolder();
		$bmf->setTitle($_POST["title"]);
		$bmf->setParent($this->id);

		$bmf->create();

		$this->view();
	}


	function updateBookmarkFolder()
	{
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
		$bm = new ilBookmark();
		$bm->setTitle($_POST["title"]);
		$bm->setTarget($_POST["target"]);
		$bm->setParent($this->id);
		$bm->create();

		$this->view();
	}

	function updateBookmark()
	{
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
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.obj_confirm.html");

		sendInfo($this->lng->txt("info_delete_sure"));
		$this->tpl->setVariable("FORMACTION", "bookmarks.php?bmf_id=".$this->id."&cmd=post");

		// output table header
		$cols = array("type", "title", "target");
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

		// Feedback
		sendInfo($this->lng->txt("info_deleted"),true);

		$this->view();
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

		$d = $objDefinition->getSubObjects("bmf");

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

			$this->tpl->setCurrentBlock("add_obj");
			$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
			$this->tpl->setVariable("FORMACTION_OBJ_ADD", "bookmarks.php?cmd=newForm&bmf_id=".$_GET["bmf_id"]);
			$this->tpl->setVariable("TXT_ADD", $this->lng->txt("add"));
			$this->tpl->parseCurrentBlock();
		}
	}
}
?>
