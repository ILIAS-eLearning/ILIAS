<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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

		$ilCtrl->saveParameter($this, array("clip_mob_id"));
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
		global $ilUser, $ilCtrl;
		
		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();

		switch($next_class)
		{
			case "ilobjmediaobjectgui":
				$ilCtrl->setReturn($this, "view");
				require_once("classes/class.ilTabsGUI.php");
				$mob_gui =& new ilObjMediaObjectGUI("", $_GET["clip_mob_id"],false, false);
				$mob_gui->setAdminTabs();
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
		global $tree, $ilUser, $ilCtrl, $lng, $tpl;

		$tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		$tpl->setCurrentBlock("btn_cell");
		$tpl->setVariable("BTN_LINK",
			$ilCtrl->getLinkTargetByClass("ilobjmediaobjectgui", "create"));
		$tpl->setVariable("BTN_TXT", $lng->txt("cont_create_mob"));
		$tpl->parseCurrentBlock();

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
			$ilUser->removeObjectFromClipboard($obj_id, "mob");
			include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
			$mob = new ilObjMediaObject($obj_id);
			$mob->delete();			// this method don't delete, if mob is used elsewhere
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
		ilUtil::redirect(ilUtil::appendUrlParameterString(
			$_GET["returnCommand"], "clip_obj_type=mob&clip_obj_id=".$_POST["id"][0]));
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

		$tpl->setTitleIcon(ilUtil::getImagePath("icon_clip_b.gif"));
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
