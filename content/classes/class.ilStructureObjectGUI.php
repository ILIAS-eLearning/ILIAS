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

require_once("./content/classes/class.ilLMObjectGUI.php");

/**
* Class ilStructureObjectGUI
*
* User Interface for Structure Objects Editing
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilStructureObjectGUI extends ilLMObjectGUI
{
	var $obj;	// structure object
	var $tree;

	/**
	* Constructor
	* @access	public
	*/
	function ilStructureObjectGUI(&$a_content_obj, &$a_tree)
	{
		global $ilias, $tpl, $lng;

		parent::ilLMObjectGUI($a_content_obj);
		$this->tree =& $a_tree;
	}

	/**
	* set structure object
	*
	* @param	object		$a_st_object	structure object
	*/
	function setStructureObject(&$a_st_object)
	{
		$this->obj =& $a_st_object;
		$this->actions = $this->objDefinition->getActions("st");
	}
	
	
	/**
	* this function is called by condition handler gui interface
	*/
	function getType()
	{
		return "st";
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
//echo "<br>:cmd:".$this->ctrl->getCmd().":cmdClass:".$this->ctrl->getCmdClass().":";
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			default:
				if (($cmd == "create") && ($_POST["new_type"] == "pg"))
				{
					$this->setTabs();
					$pg_gui =& new ilLMPageObjectGUI($this->content_object);
					$ret =& $pg_gui->executeCommand();
				}
				else
				{
					$ret =& $this->$cmd();
				}
				break;
		}
	}


	/**
	* create new page or chapter in chapter
	*/
	function create()
	{
		if ($_GET["obj_id"] != "")
		{
			$this->setTabs();
		}
		parent::create();
	}


	/*
	* display pages of structure object
	*/
	function view()
	{
		global $tree;

		$this->setTabs();

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.structure_edit.html", true);
		$num = 0;

		$this->tpl->setCurrentBlock("form");
		$this->ctrl->setParameter($this, "backcmd", "view");
		$this->tpl->setVariable("FORMACTION",
			$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("HEADER_TEXT", $this->lng->txt("cont_pages"));
		$this->tpl->setVariable("CHECKBOX_TOP", IL_FIRST_NODE);

		$cnt = 0;
		$childs = $this->tree->getChilds($this->obj->getId());
		foreach ($childs as $child)
		{
			if($child["type"] != "pg")
			{
				continue;
			}
			$this->tpl->setCurrentBlock("table_row");
			// color changing
			$css_row = ilUtil::switchColor($cnt++,"tblrow1","tblrow2");

			// checkbox
			$this->tpl->setVariable("CHECKBOX_ID", $child["obj_id"]);
			$this->tpl->setVariable("CSS_ROW", $css_row);
			$this->tpl->setVariable("IMG_OBJ", ilUtil::getImagePath("icon_le.gif"));

			// link
			$this->ctrl->setParameterByClass("ilLMPageObjectGUI", "obj_id", $child["obj_id"]);
			$link = $this->ctrl->getLinkTargetByClass("ilLMPageObjectGUI", "view", "", true);
			$this->tpl->setVariable("LINK_TARGET", $link);

			// title
			$this->tpl->setVariable("TEXT_CONTENT", $child["title"]);

			$this->tpl->parseCurrentBlock();
		}
		if($cnt == 0)
		{
			$this->tpl->setCurrentBlock("notfound");
			$this->tpl->setVariable("NUM_COLS", 3);
			$this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
			$this->tpl->parseCurrentBlock();
		}
		//else
		//{
			// SHOW VALID ACTIONS
			$this->tpl->setVariable("NUM_COLS", 3);
			//$this->setActions(array("confirmTermDeletion" => "delete", "addDefinition" => "cont_add_definition"));
			$acts = array("delete" => "delete", "cutPage" => "cutPage",
				"copyPage" => "copyPage");
//echo ":".$this->checkClipboardContentType().":<br>";
			if(ilEditClipboard::getContentObjectType() == "pg")
			{
				$acts["pastePage"] = "pastePage";
			}
			$this->setActions($acts);
			$this->showActions();
		//}

		// SHOW POSSIBLE SUB OBJECTS
		$this->tpl->setVariable("NUM_COLS", 3);
		//$this->showPossibleSubObjects("st");
		$subobj = array("pg");
		$opts = ilUtil::formSelect(12,"new_type",$subobj);
		//$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
		$this->tpl->setCurrentBlock("add_object");
		$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
		//$this->tpl->setVariable("FORMACTION_OBJ_ADD", "adm_object.php?cmd=create&ref_id=".$_GET["ref_id"]);
		$this->tpl->setVariable("BTN_NAME", "create");
		$this->tpl->setVariable("TXT_ADD", $this->lng->txt("insert"));
		$this->tpl->parseCurrentBlock();


		$this->tpl->setCurrentBlock("form");
		$this->tpl->parseCurrentBlock();

	}


	/*
	* display subchapters of structure object
	*/
	function subchap()
	{
		global $tree;

		$this->setTabs();

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.structure_edit.html", true);
		$num = 0;

		$this->tpl->setCurrentBlock("form");
		$this->ctrl->setParameter($this, "backcmd", "subchap");
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("HEADER_TEXT", $this->lng->txt("cont_subchapters"));
		$this->tpl->setVariable("CHECKBOX_TOP", IL_FIRST_NODE);

		$cnt = 0;
		$childs = $this->tree->getChilds($this->obj->getId());
		foreach ($childs as $child)
		{
			if($child["type"] != "st")
			{
				continue;
			}
			$this->tpl->setCurrentBlock("table_row");
			// color changing
			$css_row = ilUtil::switchColor($cnt++,"tblrow1","tblrow2");

			// checkbox
			$this->tpl->setVariable("CHECKBOX_ID", $child["obj_id"]);
			$this->tpl->setVariable("CSS_ROW", $css_row);
			$this->tpl->setVariable("IMG_OBJ", ilUtil::getImagePath("icon_cat.gif"));

			// type
			$this->ctrl->setParameterByClass("ilStructureObjectGUI", "obj_id", $child["obj_id"]);
			$link = $this->ctrl->getLinkTargetByClass("ilStructureObjectGUI", "view");
			$this->tpl->setVariable("LINK_TARGET", $link);

			// title
			$this->tpl->setVariable("TEXT_CONTENT", $child["title"]);

			$this->tpl->parseCurrentBlock();
		}
		if($cnt == 0)
		{
			$this->tpl->setCurrentBlock("notfound");
			$this->tpl->setVariable("NUM_COLS", 2);
			$this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
			$this->tpl->parseCurrentBlock();
		}
		//else
		//{
			// SHOW VALID ACTIONS
			$this->tpl->setVariable("NUM_COLS", 3);
			$acts = array("delete" => "delete", "move" => "moveChapter");
			if(ilEditClipboard::getContentObjectType() == "st")
			{
				if ($this->tree->isInTree(ilEditClipboard::getContentObjectId()))
				{
					$acts["pasteChapter"] =  "pasteChapter";
				}
			}
			/*if(!empty($_SESSION["ilEditClipboard"]))
			{
				$acts["paste"] = "paste";
			}*/
			$this->setActions($acts);
			$this->showActions();
		//}

		// SHOW POSSIBLE SUB OBJECTS
		$this->tpl->setVariable("NUM_COLS", 3);
		//$this->showPossibleSubObjects("st");
		$subobj = array("st");
		$opts = ilUtil::formSelect(12,"new_type",$subobj);
		//$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
		$this->tpl->setCurrentBlock("add_object");
		$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
		//$this->tpl->setVariable("FORMACTION_OBJ_ADD", "adm_object.php?cmd=create&ref_id=".$_GET["ref_id"]);
		$this->tpl->setVariable("BTN_NAME", "create");
		$this->tpl->setVariable("TXT_ADD", $this->lng->txt("insert"));
		$this->tpl->parseCurrentBlock();

		//$this->tpl->setVariable("NUM_COLS", 2);
		//$this->showPossibleSubObjects("st");

		$this->tpl->setCurrentBlock("form");
		$this->tpl->parseCurrentBlock();

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
	* save new chapter
	*/
	function save()
	{
		$meta_data =& new ilMetaData($_GET["new_type"], $this->content_object->getId());

		$this->obj =& new ilStructureObject($this->content_object);
		$this->obj->assignMetaData($meta_data);
		$this->obj->setType("st");
		$this->obj->setTitle(ilUtil::stripSlashes($_POST["Fobject"]["title"]));
		$this->obj->setDescription(ilUtil::stripSlashes($_POST["Fobject"]["desc"]));
		$this->obj->setLMId($this->content_object->getId());
		$this->obj->create();

		$this->putInTree();

		// check the tree
		$this->checkTree();

		if (!empty($_GET["obj_id"]))
		{
			$this->ctrl->redirect($this, "subchap");
		}

	}

	/**
	* save meta data
	*/
	function saveMeta()
	{
//echo "lmobjectgui_Savemeta1<br>";
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->obj);
//echo "lmobjectgui_Savemeta2<br>"; exit;
//echo "title_value:".htmlentities($_POST["meta"]["Title"]["Value"]); exit;
		$meta_gui->save($_POST["meta_section"]);
//echo "lmobjectgui_Savemeta3<br>";
		$this->ctrl->redirect($this, "view");
	}

	/**
	* put chapter into tree
	*/
	function putInTree()
	{
//echo "st:putInTree";
		// chapters should be behind pages in the tree
		// so if target is first node, the target is substituted with
		// the last child of type pg
		if ($_GET["target"] == IL_FIRST_NODE)
		{
			$tree = new ilTree($this->content_object->getId());
			$tree->setTableNames('lm_tree','lm_data');
			$tree->setTreeTablePK("lm_id");

			// determine parent node id
			$parent_id = (!empty($_GET["obj_id"]))
				? $_GET["obj_id"]
				: $tree->getRootId();
			// determine last child of type pg
			$childs =& $tree->getChildsByType($parent_id, "pg");
			if (count($childs) != 0)
			{
				$_GET["target"] = $childs[count($childs) - 1]["obj_id"];
			}
		}
		if (empty($_GET["target"]))
		{
			$_GET["target"] = IL_LAST_NODE;
		}

		parent::putInTree();
	}

	/**
	* cut page
	*/
	function cutPage()
	{
//echo "1";
//unset($_SESSION["message"]);
//echo $_SESSION["referer"];
		if(!isset($_POST["id"]))
		{
//echo "2:".$_SESSION["message"].":";
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);

		}
		if(count($_POST["id"]) > 1)
		{
			$this->ilias->raiseError($this->lng->txt("cont_select_max_one_item"),$this->ilias->error_obj->MESSAGE);
		}

		if(count($_POST["id"]) == 1 && $_POST["id"][0] == IL_FIRST_NODE)
		{
			$this->ilias->raiseError($this->lng->txt("cont_select_item"), $this->ilias->error_obj->MESSAGE);
		}

		// SAVE POST VALUES
		ilEditClipboard::storeContentObject("pg",$_POST["id"][0]);

		$tree = new ilTree($this->content_object->getId());
		$tree->setTableNames('lm_tree','lm_data');
		$tree->setTreeTablePK("lm_id");

		// cut selected object
		$cutted = 0;
		foreach ($_POST["id"] as $id)
		{
			if ($id == -1)
			{
				continue;
			}
			
			$obj =& ilLMObjectFactory::getInstance($this->content_object, $id);
			$obj->setLMId($this->content_object->getId());
			$node_data = $tree->getNodeData($id);
			//$obj->delete();
			if($tree->isInTree($id))
			{
				$parent_id = $tree->getParentId($id);
				$tree->deleteTree($node_data);
				
				// write history entry
				require_once("classes/class.ilHistory.php");
				ilHistory::_createEntry($id, "cut",
					array(ilLMObject::_lookupTitle($parent_id), $parent_id),
					$this->content_object->getType().":pg");
				ilHistory::_createEntry($parent_id, "cut_page",
					array(ilLMObject::_lookupTitle($id), $id),
					$this->content_object->getType().":st");
			}
			$cutted++;
		}

		// tree check
		$this->checkTree();

		if($cutted > 0)
		{
			sendInfo($this->lng->txt("msg_cut_clipboard"), true);
		}

		$this->ctrl->redirect($this, "view");
	}

	/**
	* copy page
	*/
	function copyPage()
	{
		if(!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}
		if(count($_POST["id"]) > 1)
		{
			$this->ilias->raiseError($this->lng->txt("cont_select_max_one_item"),$this->ilias->error_obj->MESSAGE);
		}

		if(count($_POST["id"]) == 1 && $_POST["id"][0] == IL_FIRST_NODE)
		{
			$this->ilias->raiseError($this->lng->txt("cont_select_item"), $this->ilias->error_obj->MESSAGE);
		}

		// SAVE POST VALUES
		ilEditClipboard::storeContentObject("pg",$_POST["id"][0],"copy");

		sendInfo($this->lng->txt("msg_copy_clipboard"), true);

		$this->ctrl->redirect($this, "view");
	}

	/**
	* paste page
	*/
	function pastePage()
	{
		if(ilEditClipboard::getContentObjectType() != "pg")
		{
			$this->ilias->raiseError($this->lng->txt("no_page_in_clipboard"),$this->ilias->error_obj->MESSAGE);
		}

		$tree = new ilTree($this->content_object->getId());
		$tree->setTableNames('lm_tree','lm_data');
		$tree->setTreeTablePK("lm_id");

		// paste selected object
		$id = ilEditClipboard::getContentObjectId();

		// copy page, if action is copy
		if (ilEditClipboard::getAction() == "copy")
		{
			// check wether page belongs to lm
			if (ilLMObject::_lookupContObjID(ilEditClipboard::getContentObjectId())
				== $this->content_object->getID())
			{
				$lm_page = new ilLMPageObject($this->content_object, $id);
				$new_page =& $lm_page->copy();
				$id = $new_page->getId();
			}
			else
			{
				// get page from other content object into current content object
				$lm_id = ilLMObject::_lookupContObjID(ilEditClipboard::getContentObjectId());
				$lm_obj =& $this->ilias->obj_factory->getInstanceByObjId($lm_id);
				$lm_page = new ilLMPageObject($lm_obj, $id);
				$new_page =& $lm_page->copyToOtherContObject($this->content_object);
				$id = $new_page->getId();
			}
		}

		if(!$tree->isInTree($id))
		{
			if(!isset($_POST["id"]))
			{
				$target = IL_FIRST_NODE;
			}
			else
			{
				$target = $_POST["id"][0];
			}
			$tree->insertNode($id, $this->obj->getId(), $target);
			ilEditClipboard::clear();
		}
		
		// write history comments
		include_once("classes/class.ilHistory.php");
		ilHistory::_createEntry($id, "paste",
			array(ilLMObject::_lookupTitle($this->obj->getId()), $this->obj->getId()),
			$this->content_object->getType().":pg");
		ilHistory::_createEntry($parent_id, "paste_page",
			array(ilLMObject::_lookupTitle($id), $id),
			$this->content_object->getType().":st");

		// check the tree
		$this->checkTree();

		$this->ctrl->redirect($this, "view");
	}


	/**
	* move a single chapter  (selection)
	*/
	function moveChapter()
	{
		$cont_obj_gui =& new ilObjContentObjectGUI("",$this->content_object->getRefId(),
			true, false);
		$cont_obj_gui->moveChapter($this->obj->getId());

		$this->ctrl->redirect($this, "subchap");
	}


	/**
	* paste chapter
	*/
	function pasteChapter()
	{
		$id = ilEditClipboard::getContentObjectId();
		if($id == $_POST["id"][0])
		{
			ilEditClipboard::clear();
			$this->subchap();
			return;
		}
		$cont_obj_gui =& new ilObjContentObjectGUI("",$this->content_object->getRefId(),
			true, false);
		$cont_obj_gui->pasteChapter($this->obj->getId());

		$this->ctrl->redirect($this, "subchap");
	}
	
	
	//
	// Condition handling stuff
	//
	
	function initConditionHandlerInterface()
	{
		include_once("classes/class.ilConditionHandlerInterface.php");
		$this->condHI =& new ilConditionHandlerInterface($this);
		$this->condHI->setTargetType("st");
		$this->condHI->setTargetId($this->obj->getId());
		$this->condHI->setTargetTitle($this->obj->getTitle());
	}
	
	/**
	* list preconditions of chapter
	*/
	function preconditions()
	{
		$this->setTabs();		
		$this->initConditionHandlerInterface();
		
		$condList =& $this->condHI->chi_list();
		$this->tpl->setVariable("ADM_CONTENT", $condList);
		$this->tpl->parseCurrentBlock();
		
	}
	
	/**
	* condition trigger object selection
	*/
	function chi_selector()
	{
		$this->setTabs();
		$this->initConditionHandlerInterface();
		$this->condHI->chi_selector("content", "ADM_CONTENT");
	}		

	/**
	* assign trigger object to chapter
	*/
	function chi_assign()
	{
		$this->initConditionHandlerInterface();
		$this->condHI->chi_assign(false);
		$this->preconditions();
	}

	/**
	* update conditions of chapter
	*/
	function chi_update()
	{
		$this->initConditionHandlerInterface();
		$this->condHI->chi_update();
		$this->preconditions();
	}
	
	/**
	* delete precondition(s)
	*/
	function chi_delete()
	{
		$this->initConditionHandlerInterface();
		$this->condHI->chi_delete();
		$this->preconditions();
	}

	/**
	* cancel creation of new page or chapter
	*/
	function cancel()
	{
		sendInfo($this->lng->txt("msg_cancel"), true);
		if ($_GET["obj_id"] != 0)
		{
			if ($_GET["new_type"] == "pg")
			{
				$this->ctrl->redirect($this, "view");
			}
			else
			{
				$this->ctrl->redirect($this, "subchap");
			}
		}
	}


	/**
	* output tabs
	*/
	function setTabs()
	{
		// catch feedback message
		include_once("classes/class.ilTabsGUI.php");
		$tabs_gui =& new ilTabsGUI();
		//$this->getTabs($tabs_gui);
		$tabs_gui->getTargetsByObjectType($this, "st");
		$this->tpl->setVariable("TABS", $tabs_gui->getHTML());
		$this->tpl->setVariable("HEADER",
			$this->lng->txt($this->obj->getType()).": ".$this->obj->getTitle());
	}

	/**
	* adds tabs to tab gui object
	*
	* @param	object		$tabs_gui		ilTabsGUI object
	*/
	function getTabs(&$tabs_gui)
	{
		// back to upper context
		$tabs_gui->addTarget("edit", $this->ctrl->getLinkTarget($this, "view")
			, "view", get_class($this));

		$tabs_gui->addTarget("cont_preview", $this->ctrl->getLinkTarget($this, "preview")
			, "preview", get_class($this));

		$tabs_gui->addTarget("meta_data", $this->ctrl->getLinkTarget($this, "editMeta")
			, "editMeta", get_class($this));

		$tabs_gui->addTarget("clipboard", $this->ctrl->getLinkTargetByClass("ilEditClipboardGUI", "view")
			, "view", "ilEditClipboardGUI");

	}

}
?>
