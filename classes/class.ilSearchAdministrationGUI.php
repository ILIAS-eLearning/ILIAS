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

require_once "./classes/class.ilSearch.php";
require_once "./Services/Table/classes/class.ilTableGUI.php";
require_once "./classes/class.ilSearchGUIExplorer.php";
require_once "./classes/class.ilSearchFolder.php";

/**
* search
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version Id: $Id$
* 
*/
class ilSearchAdministrationGUI
{
	/**
	* ilias object
	* @var object DB
	* @access public
	*/	
	var $ilias;

	/**
	* search object
	* @var object 
	* @access public
	*/
	var $folder_obj;
	var $search;
	var $tpl;
	var $lng;
	var $tree;

	// TABLE VARS
	var $res_type;
	var $offset;
	var $sort_by;
	var $sort_order;

	var $folder_id;
	var $viewmode;

	var $message;

	/**
	* Constructor
	* @access	public
	*/
	function ilSearchAdministrationGUI($a_user_id)
	{
		global $ilias,$tpl,$lng;

		// DEFINE SOME CONSTANTS
		define("RESULT_LIMIT",10);
		
		// Initiate variables
		$this->ilias	=& $ilias;
		$this->tpl		=& $tpl;
		$this->lng		=& $lng;
		$this->lng->loadLanguageModule("search");


		$this->folder_id  = $_GET["folder_id"];
		$this->res_type   = $_GET["res_type"];
		$this->offset	  = $_GET["offset"];
		$this->sort_by	  = $_GET["sort_by"];
		$this->sort_order = $_GET["sort_order"];
		
		$this->setUserId($a_user_id);
		$this->setViewmode($_GET["viewmode"]);

		// INITIATE SEARCH OBJECT
		$this->search =& new ilSearch($a_user_id);
		$this->folder_obj =& new ilSearchFolder($this->getUserId(),$_GET["folder_id"]);
		$this->tree = new ilTree(1);

		$this->setFolderId($_GET["folder_id"] ? $_GET["folder_id"] : $this->folder_obj->getRootId());

		$this->performAction();
	}

	function performAction()
	{
		if(!isset($_POST["cmd"]))
		{
			if($this->getViewmode() == "flat")
			{
				$this->__show();
			}
			else
			{
				$this->__showTree();
				return true;
			}
		}
		if(isset($_POST["cmd"]["cancel"]))
		{
			session_unregister("search_rename");
			unset($_POST["del_id"]);
			$this->__show();
		}
		if(isset($_POST["cmd"]["create"]))
		{
			$this->__showCreateFolder();
		}
		if(isset($_POST["cmd"]["create_folder"]))
		{
			$this->createNewFolder();
			$this->__show();
		}
		if(isset($_POST["cmd"]["delete"]))
		{
			$this->__show();
			$this->__showConfirmDeleteFolder();
		}
		if(isset($_POST["cmd"]["confirmed_delete"]))
		{
			$this->deleteFolders();
			$this->__show();
		}
		if(isset($_POST["cmd"]["rename"]))
		{
			$this->__renameItem($_SESSION["search_rename"]);
			$this->__show();
		}

		if(isset($_POST["cmd"]["do_it"]))
		{
			switch($_POST["action"])
			{
				case "0":
					$this->message = $this->lng->txt("search_select_one_action");
					$this->__show();
					break;

				case "rename":
					if(!$this->__showRename())
					{
						$this->__show();
					}
					break;
					
				case "delete":
					$this->__show();
					$this->__showConfirmDeleteFolder();
					break;

				default:
					$this->__moveItem();
					$this->__show();
					break;
			}
		}
	}
	// SET/GET
	function setUserId($a_user_id)
	{
		$this->user_id = $a_user_id;
		
	}
	function getUserId()
	{
		return $this->user_id;
	}
	function setFolderId($a_folder_id)
	{
		$this->folder_id = $a_folder_id;
	}
	function getFolderId()
	{
		return $this->folder_id;
	}
	function setViewmode($a_viewmode)
	{
		switch($a_viewmode)
		{
			case "flat":
				$this->viewmode = "flat";
				$_SESSION["s_viewmode"] = "flat";
				break;

			case "tree":
				$this->viewmode = "tree";
				$_SESSION["s_viewmode"] = "tree";
				break;
				
			default:
				$this->viewmode = $_SESSION["s_viewmode"] ? $_SESSION["s_viewmode"] : "flat";
				break;
		}
	}
	function getViewmode()
	{
		return $this->viewmode;
	}

	function createNewFolder()
	{
		$new_folder_obj = $this->folder_obj->create(ilUtil::stripslashes($_POST["title"]));
	}

	function deleteFolders()
	{
		foreach($_POST["del_id"] as $folder_id)
		{
			$this->folder_obj->delete($folder_id);
		}
		$this->message = $this->lng->txt("search_objects_deleted");
	}

	// PRIVATE METHODS
	function __showTree()
	{
		// SHOW SEARCH ADMINISTRATION PAGE
		$this->tpl->addBlockFile("CONTENT","content","tpl.search_administration_explorer.html");
		$this->tpl->addBlockFile("STATUSLINE","statusline","tpl.statusline.html");
		infoPanel();

		$this->__showTabs();
		$this->__showLocator();

		// output message
		if($this->message)
		{
			sendInfo($this->message);
		}

		// set header
		$this->__showHeader();

		require_once ("classes/class.ilSearchResultExplorer.php");
		$exp = new ilSearchResultExplorer("search_administration.php",$this->getUserId());
		$exp->setTargetGet("folder_id");

		if ($_GET["sea_expand"] == "")
		{
			$expanded = 1;
		}
		else
		{
			$expanded = $_GET["sea_expand"];
		}

		$exp->setExpand($expanded);
		// build html-output
		$exp->setOutput(0);
		$output = $exp->getOutput();

		$this->tpl->setVariable("EXPLORER", $output);
	}		


	function __show()
	{
		// SHOW SEARCH ADMINISTRATION PAGE
		$this->tpl->addBlockFile("CONTENT","content","tpl.search_administration.html");
		$this->tpl->addBlockFile("STATUSLINE","statusline","tpl.statusline.html");
		infoPanel();
		$this->tpl->setVariable("SEARCH_ADMINISTRATION_ACTION","./search_administration.php?folder_id=".$this->getFolderId());
		
		$this->__showHeader();
		$this->__showLocator();
		$this->__showTabs();


		$this->__showFolders();

		if($this->message)
		{
			sendInfo($this->message);
		}

	}

	function __showRename()
	{
		// NO ITEM SELECTED
		if(!count($_POST["del_id"]))
		{
			$this->message = $this->lng->txt("search_select_exactly_one_object");
			return false;
		}
		// TOO MANY ITEMS SELECTED
		if(count($_POST["del_id"]) > 1)
		{
			$this->message = $this->lng->txt("search_select_exactly_one_object");
			return false;
		}

		// SHOW SEARCH ADMINISTRATION PAGE
		$this->tpl->addBlockFile("CONTENT","content","tpl.search_administration.html");
		$this->tpl->addBlockFile("STATUSLINE","statusline","tpl.statusline.html");
		infoPanel();
		$this->tpl->setVariable("SEARCH_ADMINISTRATION_ACTION","./search_administration.php?folder_id=".$this->getFolderId());
		
		$this->__showHeader();
		$this->__showLocator();
		$this->__showTabs();

		// GET OLD TITLE
		include_once "./classes/class.ilSearchItemFactory.php";

		$tmp_obj = ilSearchItemFactory::getInstance($_POST["del_id"][0]);
		$this->__showRenameForm($tmp_obj->getTitle());
		unset($tmp_obj);

		// SET SESSION VARIABLE TO REMEMBER obj_id
		$_SESSION["search_rename"] = $_POST["del_id"][0];

		if($this->message)
		{
			sendInfo($this->message);
		}
		return true;
	}
	function __renameItem($a_id)
	{
		include_once "./classes/class.ilSearchItemFactory.php";

		$tmp_obj = ilSearchItemFactory::getInstance($a_id);

		$tmp_obj->updateTitle(ilUtil::stripslashes($_POST["title"]));

		$this->message = $this->lng->txt("search_object_renamed");
		
		return true;
	}
		


	function __moveItem()
	{
		if(!count($_POST["del_id"]))
		{
			$this->message = $this->lng->txt("");
			return false;
		}
		
		include_once "./classes/class.ilSearchItemFactory.php";

		// CHECK IF MOVE ACTION IS POSSIBLE
		foreach($_POST["del_id"] as $id)
		{
			$tmp_obj = ilSearchItemFactory::getInstance($id);

			if($tmp_obj->getType() == "seaf")
			{
				$this->message = $this->lng->txt("search_move_folders_not_allowed");
				return false;
			}
			$objects[] =& $tmp_obj;
			unset($tmp_obj);
		}
		include_once "./classes/class.ilSearchFolder.php";
		include_once "./classes/class.ilSearchResult.php";

		$tmp_folder =& new ilSearchFolder($this->getUserId(),$_POST["action"]);
		
		// MOVE ITEMS
		foreach($objects as $obj)
		{
			// COPY DATA
			$search_res_obj =& new ilSearchResult($this->getUserId());
			$search_res_obj->setTitle($obj->getTitle());
			$search_res_obj->setTarget(addslashes(serialize($obj->getTarget())));
			
			$tmp_folder->assignResult($search_res_obj);

			// AND FINALLY:
			$this->folder_obj->delete($obj->getObjId());
			unset($search_res_obj);
		}
		unset($objects);
		$this->message = $this->lng->txt("search_objects_moved");

		return true;
	}
		

	function __showCreateFolder()
	{
		// SHOW SEARCH ADMINISTRATION PAGE
		$this->tpl->addBlockFile("CONTENT","content","tpl.search_administration.html");
		$this->tpl->addBlockFile("STATUSLINE","statusline","tpl.statusline.html");
		infoPanel();
		$this->tpl->setVariable("SEARCH_ADMINISTRATION_ACTION","./search_administration.php?folder_id=".$this->getFolderId());
		
		$this->__showHeader();
		$this->__showLocator();
		$this->__showTabs();

		$this->__showCreateFolderForm();
		if($this->message)
		{
			sendInfo($this->message);
		}

	}

	function __showConfirmDeleteFolder()
	{
		if(!count($_POST["del_id"]))
		{
			sendInfo($this->lng->txt("search_no_selection"));
			return false;
		}
		sendInfo($this->lng->txt("search_delete_sure"));

		$this->tpl->setCurrentBlock("CONFIRM_DELETE");
		$this->tpl->setVariable("TXT_DELETE_CANCEL",$this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_DELETE_CONFIRM",$this->lng->txt("delete"));
		$this->tpl->parseCurrentBlock();
	}

	function __showCreateFolderForm()
	{
		$this->tpl->setVariable("SEARCH_ADMINISTRATION_ACTION","./search_administration.php?folder_id=".$this->getFolderId());

		$this->tpl->setCurrentBlock("FOLDER_CREATE_FORM");
		$this->tpl->setVariable("FOLDER_CREATE_FORM_TXT",$this->lng->txt("search_new_folder"));
		$this->tpl->setVariable("FOLDER_CREATE_FORM_TITLE_TXT",$this->lng->txt("title"));
		$this->tpl->setVariable("FOLDER_CREATE_FORM_VALUE","");
		$this->tpl->setVariable("FOLDER_CREATE_FORM_CMD","create_folder");
		$this->tpl->setVariable("FOLDER_CREATE_FORM_SUBMIT_1",$this->lng->txt("cancel"));
		$this->tpl->setVariable("FOLDER_CREATE_FORM_SUBMIT_2",$this->lng->txt("save"));
		$this->tpl->parseCurrentBlock();
	}
	
	function __showRenameForm($a_old_title)
	{
		$this->tpl->setVariable("SEARCH_ADMINISTRATION_ACTION","./search_administration.php?folder_id=".$this->getFolderId());

		$this->tpl->setCurrentBlock("FOLDER_CREATE_FORM");
		$this->tpl->setVariable("FOLDER_CREATE_FORM_TXT",$this->lng->txt("search_rename_title"));
		$this->tpl->setVariable("FOLDER_CREATE_FORM_TITLE_TXT",$this->lng->txt("title"));
		$this->tpl->setVariable("FOLDER_CREATE_FORM_VALUE",$a_old_title);
		$this->tpl->setVariable("FOLDER_CREATE_FORM_CMD","rename");
		$this->tpl->setVariable("FOLDER_CREATE_FORM_SUBMIT_1",$this->lng->txt("cancel"));
		$this->tpl->setVariable("FOLDER_CREATE_FORM_SUBMIT_2",$this->lng->txt("rename"));
		$this->tpl->parseCurrentBlock();
	}

	function __showHeader()
	{
		if($this->getFolderId() == $this->folder_obj->getRootId())
		{
			$this->tpl->setVariable("TXT_HEADER",$this->lng->txt("search_search_results"));
		}
		else
		{
			// SHOW BACK TO TOP IMAGE
			$this->tpl->setVariable("TXT_HEADER",$this->folder_obj->getTitle());
			$this->tpl->setCurrentBlock("top");
			$this->tpl->setVariable("LINK_TOP", "search_administration.php?folder_id=".$this->folder_obj->getParentId());
			$this->tpl->setVariable("IMG_TOP",ilUtil::getImagePath("ic_top.gif"));
			$this->tpl->parseCurrentBlock();
		}
		// SHOW TREE/FLAT IMAGES
		$this->tpl->setVariable("H_FORMACTION","./search_administration.php?folder_id=".$this->getFolderId());

		$this->tpl->setCurrentBlock("content");
		$this->tpl->setVariable("LINK_FLAT", "search_administration.php?viewmode=flat&folder_id=".$this->getFolderId());
		$this->tpl->setVariable("IMG_FLAT",ilUtil::getImagePath("ic_flatview.gif"));

		$this->tpl->setVariable("LINK_TREE", "search_administration.php?viewmode=tree&folder_id".$this->getFolderId());
		$this->tpl->setVariable("IMG_TREE",ilUtil::getImagePath("ic_treeview.gif"));

		if($this->getViewmode() == 'flat')
		{
			$this->tpl->setCurrentBlock("commands");
			// possible subobjects
			$opts = ilUtil::formSelect("", "new_type", array("folder"));
			$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
			$this->tpl->setVariable("BTN_NAME", "create");
			$this->tpl->setVariable("TXT_ADD", $this->lng->txt("add"));
			$this->tpl->parseCurrentBlock();
		}
	}

	function __showLocator()
	{
		$path_info = $this->folder_obj->getPath();

		$this->tpl->addBlockFile("LOCATOR","locator","tpl.locator.html");

		$this->tpl->setCurrentBlock("locator_item");
		$this->tpl->setVariable("LINK_ITEM","./search.php");
		$this->tpl->setVariable("LINK_TARGET", ilFrameTargetInfo::_getFrame("MainContent"));
		$this->tpl->setVariable("ITEM",$this->lng->txt("mail_search_word"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->touchBlock("locator_separator_prefix");
		$this->tpl->setCurrentBlock("locator_item");
		$this->tpl->setVariable("LINK_ITEM","./search_administration.php");
		$this->tpl->setVariable("LINK_TARGET",ilFrameTargetInfo::_getFrame("MainContent"));
		$this->tpl->setVariable("ITEM",$this->lng->txt("search_search_results"));
		$this->tpl->parseCurrentBlock();

		for($i = 1; $i < count($path_info); ++$i)
		{
			$this->tpl->touchBlock("locator_separator_prefix");
			$this->tpl->setCurrentBlock("locator_item");
			$this->tpl->setVariable("LINK_ITEM","./search_administration.php?folder_id=".$path_info[$i]["child"]);
			$this->tpl->setVariable("LINK_TARGET",ilFrameTargetInfo::_getFrame("MainContent"));
			$this->tpl->setVariable("ITEM",$path_info[$i]["title"]);
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("locator");
		$this->tpl->setVariable("TXT_LOCATOR",$this->lng->txt("locator"));
		$this->tpl->parseCurrentBlock();

	}
		
	function __showTabs()
	{
		$this->tpl->addBlockFile("TABS","tabs","tpl.tabs.html");

		if($this->res_type)
		{
			$this->tpl->setCurrentBlock("tab");
			$this->tpl->setVariable("TAB_TYPE","tabinactive");
			$this->tpl->setVariable("TAB_LINK","search.php");
			$this->tpl->setVariable("TAB_TEXT",$this->lng->txt("search_all_results"));
			$this->tpl->parseCurrentBlock();
		}
		// SEARCH ADMINISTRATION
		$this->tpl->setCurrentBlock("tab");
		$this->tpl->setVariable("TAB_TYPE","tabinactive");
		$this->tpl->setVariable("TAB_LINK","search.php?folder_id=".$this->getFolderId());
		$this->tpl->setVariable("TAB_TEXT",$this->lng->txt("search"));
		$this->tpl->parseCurrentBlock();

		return true;
	}

	function __getActions()
	{
		$options[0] = $this->lng->txt("search_select_one_action");

		if($this->folder_obj->hasResults() and $this->folder_obj->countFolders())
		{
			// SHOW MOVE TO
			$tree_data = $this->folder_obj->getTree();

			foreach($tree_data as $node)
			{
				$prefix = $this->lng->txt("search_move_to") ;
				for($i = 0; $i < $node["depth"];++$i)
				{
					$prefix .= "&nbsp;&nbsp;";
				}
				if($node["obj_id"] == $this->folder_obj->getRootId())
				{
					$options[$node["obj_id"]] = $prefix.$this->lng->txt("search_search_results");
				}
				else
				{
					$options[$node["obj_id"]] = $prefix.$node["title"];
				}

			}			

		}
		// SHOW RENAME
		$options["rename"] = $this->lng->txt("rename");

		// SHOW DELETE
		$options["delete"] = $this->lng->txt("delete");

		return ilUtil::formSelect($_POST["action"],"action",$options,false,true);
	}

		

	function __showFolders()
	{
		$items = $this->getChildFolders();

		if(count($items) or $this->getFolderId() != $this->folder_obj->getRootId())
		{
			$counter = $this->__appendParentLink(count($items)) ? 0 : 1;
			foreach($items as $item)
			{
				$checked = (is_array($_POST["del_id"]) and in_array($item["obj_id"],$_POST["del_id"])) ? 1 : 0;

				$this->tpl->setVariable("CHECK",ilUtil::formCheckbox($checked,"del_id[]",$item["obj_id"]));

				$this->tpl->setCurrentBlock("TBL_FOLDER_ROW");
				$this->tpl->setVariable("ROWCOL",$counter % 2 ? "tblrow1" : "tblrow2");

				list($link,$target) = $this->__formatLink($item);
				$this->tpl->setVariable("FOLDER_LINK",$link);
				$this->tpl->setVariable("FOLDER_TARGET",$target);
				$this->tpl->setVariable("FOLDER_TITLE",$this->__formatTitle($item));
				$this->tpl->parseCurrentBlock();
				++$counter;
			}
			if(count($items))
			{
				$this->tpl->setCurrentBlock("TBL_FOOTER");
				$this->tpl->setVariable("TBL_FOOTER_IMG_SRC",ilUtil::getImagePath("arrow_downright.gif"));
				$this->tpl->setVariable("TBL_FOOTER_SELECT",$this->__getActions());
				$this->tpl->setVariable("TBL_FOOTER_SUBMIT",$this->lng->txt("ok"));
				$this->tpl->parseCurrentBlock();
			}
		}
		else
		{
			$this->tpl->setCurrentBlock("TBL_ROW_FOLDER");
			$this->tpl->setVariable("TXT_NO_FOLDER",$this->lng->txt("search_no_results_saved"));
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("TBL_FOLDER");
		$this->tpl->setVariable("COLSPAN",count($items) ? 2 : 1);
		$this->tpl->setVariable("TXT_FOLDER_HEADER",$this->lng->txt("search_my_search_results"));
		$this->tpl->setVariable("TXT_FOLDER_TITLE",$this->lng->txt("title"));
		$this->tpl->parseCurrentBlock();
		
	}

	function __appendParentLink($nr_items)
	{
		if($this->getFolderId() == $this->folder_obj->getRootId())
		{
			return false;
		}
		else
		{
			if($nr_items)
			{
				$this->tpl->setVariable("CHECK","&nbsp;");
			}
			$this->tpl->setCurrentBlock("TBL_FOLDER_ROW");
			$this->tpl->setVariable("ROWCOL","tblrow1");

			list($link,$target) = $this->__formatLink(array("type" => "top"));
			$this->tpl->setVariable("FOLDER_LINK",$link);
			$this->tpl->setVariable("FOLDER_TARGET",$target);
			$this->tpl->setVariable("FOLDER_TITLE",$this->__formatTitle(array("type" => "top")));
			$this->tpl->parseCurrentBlock();
			return true;
		}
	}

	function __formatTitle($a_item)
	{
		switch($a_item["type"])
		{
			case "seaf":
				$img = "<img border=\"0\" vspace=\"0\" align=\"left\" src=\"".
					ilUtil::getImagePath("icon_cat.gif")."\"\>&nbsp;";
				
				return $img.$a_item["title"];

			case "sea":
				$img = "<img border=\"0\" vspace=\"0\" align=\"left\" src=\"".
					ilUtil::getImagePath("icon_bm.gif")."\"\>&nbsp;";
				
				return $img.$a_item["title"];

			case "top":
				$img = "<img border=\"0\" vspace=\"0\" align=\"left\" src=\"".
					ilUtil::getImagePath("icon_cat.gif")."\"\>&nbsp;";

				return $img."..";
		}
	}

	function __formatLink($a_item)
	{
		switch($a_item["type"])
		{
			case "seaf":
				$target = ilFrameTargetInfo::_getFrame("MainContent");
				$link = "./search_administration.php?folder_id=".$a_item["obj_id"];

				return array($link,$target);

			case "sea":
				include_once "./classes/class.ilSearchResult.php";

				$tmp_obj =& new ilSearchResult($this->getUserId(),$a_item["obj_id"]);

				$link = $tmp_obj->createLink();
				unset($tmp_obj);

				return $link;
				
			case "top":
				$parent_id = $this->folder_obj->getParentId();
				$target = ilFrameTargetInfo::_getFrame("MainContent");
				$link = "./search_administration.php?folder_id=".$parent_id;

				return array($link,$target);
		}
	}
		
	function getChildFolders()
	{
		return $folder = $this->folder_obj->getChilds();
	}
		
	
} // END class.Search
?>