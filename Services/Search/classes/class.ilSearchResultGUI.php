<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
* Class ilSearchGUI
*
* GUI class for 'simple' search
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* @package ilias-search
*
*/
include_once 'Services/Search/classes/class.ilSearchBaseGUI.php';

class ilSearchresultGUI extends ilSearchBaseGUI
{
	/*
	 * Id of current user
	 */
	var $user_id;

	/**
	* Constructor
	* @access public
	*/
	function ilSearchResultGUI()
	{
		global $ilUser;


		parent::ilSearchBaseGUI();

		$this->setUserId($ilUser->getId());

		$this->__initFolderObject();
		$this->ctrl->saveParameter($this,'folder_id');
		$this->ctrl->setParameter($this,'folder_id',$this->folder_obj->getFolderId());

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


	/**
	* Control
	* @access public
	*/
	function &executeCommand()
	{
		global $rbacsystem;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			default:
				if($cmd == 'gateway')
				{
					if($_POST['action'] and is_numeric($_POST['action']))
					{
						$this->prepareOutput();
						$this->moveItem();
						
						return true;
					}
					$cmd = $_POST['action'];
				}
				if(!$cmd)
				{
					$cmd = "showResults";
				}

				$this->prepareOutput();
				$this->$cmd();
				break;
		}
		return true;
	}

	function cancel()
	{
		unset($_POST['del_id']);
		unset($_SESSION['search_rename']);
		$this->showResults();

		return true;
	}

	function create()
	{
		// SHOW SEARCH ADMINISTRATION PAGE
		$this->setLocator();
		$this->tpl->addBlockFile("ADM_CONTENT","adm_content","tpl.search_results.html",'Services/Search');
		$this->ctrl->setParameter($this,'folder_id',$this->folder_obj->getFolderId());
		$this->tpl->setVariable("SEARCH_ADMINISTRATION_ACTION",$this->ctrl->getFormAction($this));

		$this->tpl->setCurrentBlock("FOLDER_CREATE_FORM");
		$this->tpl->setVariable("FOLDER_CREATE_FORM_TXT",$this->lng->txt("search_new_folder"));
		$this->tpl->setVariable("FOLDER_CREATE_FORM_TITLE_TXT",$this->lng->txt("title"));
		$this->tpl->setVariable("FOLDER_CREATE_FORM_VALUE","");
		$this->tpl->setVariable("FOLDER_CREATE_FORM_CMD","save");
		$this->tpl->setVariable("FOLDER_CREATE_FORM_SUBMIT_1",$this->lng->txt("cancel"));
		$this->tpl->setVariable("FOLDER_CREATE_FORM_SUBMIT_2",$this->lng->txt("save"));
		$this->tpl->parseCurrentBlock();

	}

	function save()
	{
		if(!strlen($_POST['title']))
		{
			ilUtil::sendInfo($this->lng->txt('search_enter_title'));
			$this->create();

			return false;
		}

		$this->folder_obj->create(ilUtil::stripslashes($_POST["title"]));
		ilUtil::sendInfo($this->lng->txt('search_added_new_folder'));
		$this->showResults();

		return true;
	}

	function update()
	{
		if(!strlen($_POST['title']))
		{
			ilUtil::sendInfo($this->lng->txt('search_enter_title'));
			$this->showResults();

			return false;
		}

		include_once "Services/Search/classes/class.ilSearchItemFactory.php";

		$tmp_obj = ilSearchItemFactory::getInstance($_SESSION['search_rename']);
		$tmp_obj->updateTitle(ilUtil::stripslashes($_POST["title"]));

		ilUtil::sendInfo($this->lng->txt("search_object_renamed"));
		$this->showResults();
		
		return true;
	}		


	function showResults($a_confirm_delete = false)
	{
		$this->setLocator();
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.search_results.html','Services/Search');
		$this->ctrl->setParameter($this,'folder_id',$this->folder_obj->getFolderId());
		$this->tpl->setVariable("SEARCH_ADMINISTRATION_ACTION",$this->ctrl->getFormAction($this));

		if($a_confirm_delete)
		{
			ilUtil::sendInfo($this->lng->txt("search_delete_sure"));

			$this->tpl->setCurrentBlock("CONFIRM_DELETE");
			$this->tpl->setVariable("TXT_DELETE_CANCEL",$this->lng->txt("cancel"));
			$this->tpl->setVariable("TXT_DELETE_CONFIRM",$this->lng->txt("delete"));
			$this->tpl->parseCurrentBlock();
		}

		$items = $this->folder_obj->getChilds();
		if(count($items) or $this->folder_obj->getFolderId() != $this->folder_obj->getRootId())
		{
			$counter = $this->__appendParentLink(count($items)) ? 0 : 1;
			foreach($items as $item)
			{
				if($item['type'] == 'seaf')
				{
					list($link,$target) = $this->__formatLink($item);
					$this->tpl->setCurrentBlock("folder");
					$this->tpl->setVariable("FOLDER_LINK",$link);
					$this->tpl->setVariable("FOLDER_TARGET",$target);
					$this->tpl->setVariable("FOLDER_TITLE",$this->__formatTitle($item));
					$this->tpl->parseCurrentBlock();
					++$counter;
				}
				else
				{
					include_once 'Services/Search/classes/class.ilSearchObjectListFactory.php';
					
					$item_data = unserialize(stripslashes($item['target']));

					$item_list_gui =& ilSearchObjectListFactory::_getInstance($item_data['type']);
					$item_list_gui->initItem($target['id'],ilObject::_lookupObjId($item_data['id']));
					$this->tpl->setCurrentBlock("link");
					$this->tpl->setVariable("HTML",$item_list_gui->getListItemHTML($item_data['id'],
																				   $id = ilObject::_lookupObjId($item_data['id']),
																				   ilObject::_lookupTitle($id),
																				   ilObject::_lookupDescription($id)));
																										  
					$this->tpl->parseCurrentBlock();
				}
				$checked = (is_array($_POST["del_id"]) and in_array($item["obj_id"],$_POST["del_id"])) ? 1 : 0;
				$this->tpl->setCurrentBlock("TBL_FOLDER_ROW");
				$this->tpl->setVariable("CHECK",ilUtil::formCheckbox($checked,"del_id[]",$item["obj_id"]));
				$this->tpl->setVariable("ROWCOL",$counter % 2 ? "tblrow1" : "tblrow2");
				$this->tpl->parseCurrentBlock();
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

	function confirmDelete()
	{
		if(!count($_POST["del_id"]))
		{
			ilUtil::sendInfo($this->lng->txt("search_no_selection"));
			$this->showResults();

			return false;
		}
		
		$this->showResults(true);

		return true;
	}

	function delete()
	{
		foreach($_POST["del_id"] as $folder_id)
		{
			$this->folder_obj->delete($folder_id);
		}
		$this->message = $this->lng->txt("search_objects_deleted");
		$this->showResults();

		return true;
	}

	function setLocator()
	{
		global $ilLocator;
		
return;
		$ilLocator->addItem($this->lng->txt('search_search_results'),
			$this->ctrl->getLinkTarget($this));
		$this->tpl->setLocator();
	}

	function rename()
	{
		// NO ITEM SELECTED
		if(!count($_POST["del_id"]))
		{
			ilUtil::sendInfo($this->lng->txt("search_select_exactly_one_object"));
			$this->showResults();

			return false;
		}
		// TOO MANY ITEMS SELECTED
		if(count($_POST["del_id"]) > 1)
		{
			ilUtil::sendInfo($this->lng->txt("search_select_exactly_one_object"));
			$this->showResults();

			return false;
		}
		// GET OLD TITLE
		include_once "Services/Search/classes/class.ilSearchItemFactory.php";

		$tmp_obj = ilSearchItemFactory::getInstance($_POST["del_id"][0]);
		
		if($tmp_obj->getType() == 'sea')
		{
			ilUtil::sendInfo($this->lng->txt("search_select_folder"));
			$this->showResults();

			return false;
		}

		// SHOW SEARCH ADMINISTRATION PAGE
		$this->setLocator();
		$this->tpl->addBlockFile("ADM_CONTENT","adm_content","tpl.search_results.html",'Services/Search');
		$this->ctrl->setParameter($this,'folder_id',$this->folder_obj->getFolderId());
		$this->tpl->setVariable("SEARCH_ADMINISTRATION_ACTION",$this->ctrl->getFormAction($this));
		
		// GET OLD TITLE
		include_once "Services/Search/classes/class.ilSearchItemFactory.php";

		$tmp_obj = ilSearchItemFactory::getInstance($_POST["del_id"][0]);
		$this->__showRenameForm($tmp_obj->getTitle());
		unset($tmp_obj);

		// SET SESSION VARIABLE TO REMEMBER obj_id
		$_SESSION["search_rename"] = $_POST["del_id"][0];

		return true;
	}

	function moveItem()
	{
		if(!count($_POST["del_id"]))
		{
			$this->showResults();
			return false;
		}
		
		include_once "Services/Search/classes/class.ilSearchItemFactory.php";

		// CHECK IF MOVE ACTION IS POSSIBLE
		foreach($_POST["del_id"] as $id)
		{
			$tmp_obj = ilSearchItemFactory::getInstance($id);

			if($tmp_obj->getType() == "seaf")
			{
				ilUtil::sendInfo($this->lng->txt("search_move_folders_not_allowed"));
				$this->showResults();
				return false;
			}
			$objects[] =& $tmp_obj;
			unset($tmp_obj);
		}
		include_once "Services/Search/classes/class.ilUserResult.php";

		$tmp_folder =& new ilSearchFolder($this->getUserId(),$_POST["action"]);
		
		// MOVE ITEMS
		foreach($objects as $obj)
		{
			// COPY DATA
			$search_res_obj =& new ilUserResult($this->getUserId());
			$search_res_obj->setTitle($obj->getTitle());
			$search_res_obj->setTarget(addslashes(serialize($obj->getTarget())));
			
			$tmp_folder->assignResult($search_res_obj);

			// AND FINALLY:
			$this->folder_obj->delete($obj->getObjId());
			unset($search_res_obj);
		}
		unset($objects);
		ilUtil::sendInfo($this->lng->txt("search_objects_moved"));
		$this->showResults();

		return true;
	}



	function prepareOutput()
	{
		global $lng;
		
		parent::prepareOutput();
		
		// SHOW ADD FOLDER
		$this->tpl->setCurrentBlock("add_commands");
		// possible subobjects
		$opts = ilUtil::formSelect("", "new_type", array("folder"));
		$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
		$this->tpl->setVariable("BTN_NAME", "create");
		$this->tpl->setVariable("TXT_ADD", $this->lng->txt("add"));
		$this->tpl->setVariable("H_FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();

		$this->ctrl->setParameter($this,'folder_id',$this->folder_obj->getFolderId());
		$this->tpl->setVariable("TXT_HEADER",$this->lng->txt('search_search_results'));

		$this->tpl->addBlockFile("TABS","tabs","tpl.tabs.html");

		$this->tpl->setCurrentBlock("tab");
		$this->tpl->setVariable("TAB_TYPE","tabinactive");
		$this->tpl->setVariable("TAB_LINK",$this->ctrl->getLinkTargetByClass('ilsearchgui'));
		$this->tpl->setVariable("TAB_TEXT",$this->lng->txt("search"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("tab");
		$this->tpl->setVariable("TAB_TYPE","tabinactive");
		$this->tpl->setVariable("TAB_LINK",$this->ctrl->getLinkTargetByClass('iladvancedsearchgui'));
		$this->tpl->setVariable("TAB_TEXT",$this->lng->txt("search_advanced"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("tab");
		$this->tpl->setVariable("TAB_TYPE","tabactive");
		$this->ctrl->setParameter($this,'folder_id',$this->folder_obj->getFolderId());
		$this->tpl->setVariable("TAB_LINK",$this->ctrl->getLinkTarget($this));
		$this->tpl->setVariable("TAB_TEXT",$this->lng->txt("search_search_results"));
		$this->tpl->parseCurrentBlock();

		// show top button if folder isn't root folder
		if($this->folder_obj->getFolderId() != $this->folder_obj->getRootId())
		{
			$this->tpl->setVariable("TXT_HEADER",$this->folder_obj->getTitle());
			$this->tpl->setCurrentBlock("top");
			$this->ctrl->setParameter($this,'folder_id',$this->folder_obj->getParentId());
			$this->tpl->setVariable("LINK_TOP",$this->ctrl->getLinkTarget($this));
			$this->tpl->setVariable("IMG_TOP",ilUtil::getImagePath("ic_top.gif"));
			$this->tpl->parseCurrentBlock();
		}
	}

	// PRIVATE
	function __initFolderObject()
	{
		include_once 'Services/Search/classes/class.ilSearchFolder.php';

		$this->folder_obj = new ilSearchFolder($this->getUserId(),(int) $_GET['folder_id']);

		return true;
	}

	function __appendParentLink($nr_items)
	{
		if($this->folder_obj->getFolderId() == $this->folder_obj->getRootId())
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
				$this->ctrl->setParameter($this,'folder_id',$a_item['obj_id']);

				return array($this->ctrl->getLinkTarget($this),
					ilFrameTargetInfo::_getFrame("MainContent"));

			case "sea":
				include_once "Services/Search/classes/class.ilUserResult.php";

				$tmp_obj =& new ilUserResult($this->getUserId(),$a_item["obj_id"]);

				$link = $tmp_obj->createLink();
				unset($tmp_obj);

				return $link;
				
			case "top":
				$this->ctrl->setParameter($this,'folder_id',$this->folder_obj->getParentId());
				
				return array($this->ctrl->getLinkTarget($this),
					ilFrameTargetInfo::_getFrame("MainContent"));
		}
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
		$options["confirmDelete"] = $this->lng->txt("delete");

		return ilUtil::formSelect($_POST["action"],"action",$options,false,true);
	}

	function __showRenameForm($a_old_title)
	{
		$this->tpl->setCurrentBlock("FOLDER_CREATE_FORM");
		$this->tpl->setVariable("FOLDER_CREATE_FORM_TXT",$this->lng->txt("search_rename_title"));
		$this->tpl->setVariable("FOLDER_CREATE_FORM_TITLE_TXT",$this->lng->txt("title"));
		$this->tpl->setVariable("FOLDER_CREATE_FORM_VALUE",$a_old_title);
		$this->tpl->setVariable("FOLDER_CREATE_FORM_CMD","update");
		$this->tpl->setVariable("FOLDER_CREATE_FORM_SUBMIT_1",$this->lng->txt("cancel"));
		$this->tpl->setVariable("FOLDER_CREATE_FORM_SUBMIT_2",$this->lng->txt("rename"));
		$this->tpl->parseCurrentBlock();
	}




}
?>
