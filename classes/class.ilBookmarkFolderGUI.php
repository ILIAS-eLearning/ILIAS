<?php
/**
* GUI class for bookmark folder handling
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package application
*/

require_once ("./classes/class.ilBookmarkFolder.php");

class ilBookmarkFolderGUI
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
	function ilBookmarkFolderGUI($bmf_id = 0)
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


	}

	/*
	* display content of bookmark folder
	*/
	function display()
	{
		$this->objectList = array();
		$this->data["data"] = array();
		$this->data["ctrl"] = array();
		$this->data["cols"] = array("", "type", "title", "target");
		$childs = $this->tree->getChilds($this->id, "title");

		foreach ($childs as $key => $val)
	    {
			//visible data part
			$this->data["data"][] = array(
				"type" => ilUtil::getImageTagByType($val["type"],$this->tpl->tplPath),
				"title" => $val["title"],
				"target" => $val["desc"]
			);
			//control information
			$this->data["ctrl"][] = array(
				"type" => $val["type"],
				"ref_id" => $val["obj_id"]
			);
	    } //foreach

		$this->displayList();

	}

	/**
	* display create bookmark folder form
	*/
	function create()
	{
		global $tpl, $lng;

		$tpl->addBlockFile("ADM_CONTENT", "ADM_content", "tpl.bookmark_newfolder.html");
		//$tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");

		//$tpl->setVariable("PAGETITLE", "ILIAS - ".$lng->txt("bookmarks"));
		//$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("bookmark_new"));
		$tpl->setVariable("TXT_URL", $lng->txt("url"));
		$tpl->setVariable("TXT_DESCRIPTION", $lng->txt("description"));

		// folder selection list
		/*
		$folders = $bookmarks->getFolders();
		foreach ($folders as $folder)
		{
			$tpl->setCurrentBlock("selfolders");
			$tpl->setVariable("SEL_OPTION", $folder["name"]);
			$tpl->setVariable("SEL_VALUE", $folder["id"]);
			$tpl->parseCurrentBlock();
		} */

		$tpl->setVariable("TXT_TOP", $lng->txt("top"));
		$tpl->setVariable("TXT_NAME", $lng->txt("name"));
		$tpl->setVariable("TXT_FOLDER", $lng->txt("folder"));
		$tpl->setVariable("TXT_SAVE", $lng->txt("save"));
		$tpl->setVariable("TXT_FOLDER_NEW", $lng->txt("folder_new"));
		$tpl->setVariable("FORMACTION", "bookmarks.php?cmd=createBookmarkfolder");
		//$tpl->parseCurrentBlock();

		//$tpl->setVariable("PAGECONTENT",$tpl->get());
		//$tpl->show();
	}


	/**
	* insert new bookmark folder into db
	*/
	function createBookmarkfolder()
	{
		$bmf = new ilBookmarkFolder();
		$bmf->setName($_POST["name"]);
		$bmf->setParent($this->id);
		$bmf->create();

		$this->display();
	}


	/**
	* display object list
	*/
	function displayList()
	{
		global $tree, $rbacsystem;

	    //$this->getTemplateFile("view");
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.obj_view.html");
		$num = 0;

		$this->tpl->setVariable("FORMACTION", "bookmarks.php?bmf_id=".$this->id."$obj_str&cmd=gateway");

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
			$this->tpl->setVariable("HEADER_LINK", "bookmarks.php?bmf_id=".$this->id."&order=type&direction=".
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
				$css_row = ilUtil::switchColor($i+1,"tblrow1","tblrow2");

				// surpress checkbox for particular object types
				$this->tpl->setCurrentBlock("checkbox");
				$this->tpl->setVariable("CHECKBOX_ID", $ctrl["obj_id"]);
				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->parseCurrentBlock();

				$this->tpl->setCurrentBlock("table_cell");
				$this->tpl->parseCurrentBlock();

				foreach ($data as $key => $val)
				{
					//build link
					$link = "bookmarks.php?";

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

						$this->tpl->parseCurrentBlock();
						$this->tpl->touchBlock("end_link");
					}

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
			$opts = ilUtil::formSelect(12,"new_type",$subobj);

			$this->tpl->setCurrentBlock("add_obj");
			$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
			$this->tpl->setVariable("FORMACTION_OBJ_ADD", "bookmarks.php?cmd=create&bmf_id=".$_GET["bmf_id"]);
			$this->tpl->setVariable("TXT_ADD", $this->lng->txt("add"));
			$this->tpl->parseCurrentBlock();
		}
	}


}
?>