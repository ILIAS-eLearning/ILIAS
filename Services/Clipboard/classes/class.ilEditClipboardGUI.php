<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/MediaObjects/classes/class.ilObjMediaObjectGUI.php");

/**
* Class ilEditClipboardGUI
*
* Clipboard for editing
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ilCtrl_Calls ilEditClipboardGUI: ilObjMediaObjectGUI
*
* @ingroup ServicesClipboard
*/
class ilEditClipboardGUI
{
	/**
	* Constructor
	* @access	public
	*/
	function ilEditClipboardGUI()
	{
		global $lng, $ilCtrl;
		
		$this->multiple = false;
		$this->page_back_title = $lng->txt("cont_back");
		if ($_GET["returnCommand"] != "")
		{
			$this->mode = "getObject";
		}
		else
		{
			$this->mode = "";
		}

		$ilCtrl->setParameter($this, "returnCommand",
			rawurlencode($_GET["returnCommand"]));

		$ilCtrl->saveParameter($this, array("clip_item_id"));
	}

	/**
	* get all gui classes that are called from this one (see class ilCtrl)
	*
	* @param	array		array of gui classes that are called
	*/
	function _forwards()
	{
		return array("ilObjMediaObjectGUI");
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilUser, $ilCtrl, $ilTabs, $lng;
		
		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();

		switch($next_class)
		{
			case "ilobjmediaobjectgui":
				$ilCtrl->setReturn($this, "view");
				$ilTabs->clearTargets();
				$ilTabs->setBackTarget($lng->txt("back"),
					$ilCtrl->getLinkTarget($this, "view"));
				$mob_gui =& new ilObjMediaObjectGUI("", $_GET["clip_item_id"],false, false);
				$mob_gui->setTabs();
				$ret =& $ilCtrl->forwardCommand($mob_gui);
				switch($cmd)
				{
					case "save":
						$ilUser->addObjectToClipboard($ret->getId(), "mob", $ret->getTitle());
						$ilCtrl->redirect($this, "view");
						break;
				}
				break;

			default:
				$ret =& $this->$cmd();
				break;
		}

		return $ret;
	}
	
	/**
	* set, if multiple selections are enabled
	*/
	function setMultipleSelections($a_multiple = true)
	{
		$this->multiple = $a_multiple;
	}

	/**
	* check wether multiple selections are enabled
	*/
	function getMultipleSelections()
	{
		return $this->multiple;
	}

	/**
	* Set Insert Button Title.
	*
	* @param	string	$a_insertbuttontitle	Insert Button Title
	*/
	function setInsertButtonTitle($a_insertbuttontitle)
	{
		$this->insertbuttontitle = $a_insertbuttontitle;
	}

	/**
	* Get Insert Button Title.
	*
	* @return	string	Insert Button Title
	*/
	function getInsertButtonTitle()
	{
		global $lng;
		
		if ($this->insertbuttontitle == "")
		{
			return $lng->txt("insert");
		}
		
		return $this->insertbuttontitle;
	}

	/*
	* display clipboard content
	*/
	function view()
	{
		global $tree, $ilUser, $ilCtrl, $lng, $tpl, $ilToolbar;

		include_once("./Services/UIComponent/Button/classes/class.ilLinkButton.php");
		$but = ilLinkButton::getInstance();
		$but->setUrl($ilCtrl->getLinkTargetByClass("ilobjmediaobjectgui", "create"));
		$but->setCaption("cont_create_mob");
		$ilToolbar->addButtonInstance($but);

		include_once("./Services/Clipboard/classes/class.ilClipboardTableGUI.php");
		$table_gui = new ilClipboardTableGUI($this, "view");
		$tpl->setContent($table_gui->getHTML());
	}


	/**
	* get Object
	*/
	function getObject()
	{
		$this->mode = "getObject";
		$this->view();
	}


	/**
	* remove item from clipboard
	*/
	function remove()
	{
		global $ilias, $ilUser, $lng, $ilCtrl;
		
		// check number of objects
		if (!isset($_POST["id"]))
		{
			$ilias->raiseError($lng->txt("no_checkbox"),$ilias->error_obj->MESSAGE);
		}

		foreach($_POST["id"] AS $obj_id)
		{
			$id = explode(":", $obj_id);
			if ($id[0] == "mob")
			{
				$ilUser->removeObjectFromClipboard($id[1], "mob");
				include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
				$mob = new ilObjMediaObject($id[1]);
				$mob->delete();			// this method don't delete, if mob is used elsewhere
			}
			if ($id[0] == "incl")
			{
				$ilUser->removeObjectFromClipboard($id[1], "incl");
			}
		}
		$ilCtrl->redirect($this, "view");
	}

	/**
	* insert
	*/
	function insert()
	{
		global $ilias, $lng;
		
		// check number of objects
		if (!isset($_POST["id"]))
		{
			$ilias->raiseError($lng->txt("no_checkbox"),$ilias->error_obj->MESSAGE);
		}
		
		if (!$this->getMultipleSelections())
		{
			if(count($_POST["id"]) > 1)
			{
				$ilias->raiseError($lng->txt("cont_select_max_one_item"),$ilias->error_obj->MESSAGE);
			}
		}

		$_SESSION["ilEditClipboard_mob_id"] = $_POST["id"];
		ilUtil::redirect($_GET["returnCommand"]);
	}
	
	function _getSelectedIDs()
	{
		return $_SESSION["ilEditClipboard_mob_id"];
	}

	/**
	* output tabs
	*/
	function setTabs()
	{
		global $ilTabs, $lng, $tpl;

		$tpl->setTitle($lng->txt("clipboard"));
		$this->getTabs($ilTabs);
	}
	
	/**
	* Set title for back link
	*/
	function setPageBackTitle($a_title)
	{
		$this->page_back_title = $a_title;
	}

	/**
	* adds tabs to tab gui object
	*
	* @param	object		$tabs_gui		ilTabsGUI object
	*/
	function getTabs(&$tabs_gui)
	{
		global $ilCtrl;
		
		// back to upper context
		$tabs_gui->setBackTarget($this->page_back_title,
			$ilCtrl->getParentReturn($this));
	}

}
?>
