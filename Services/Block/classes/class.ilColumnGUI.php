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

define ("IL_COL_LEFT", "left");
define ("IL_COL_RIGHT", "right");
define ("IL_COL_CENTER", "center");

define ("IL_SCREEN_SIDE", "");
define ("IL_SCREEN_CENTER", "center");
define ("IL_SCREEN_FULL", "full");

/**
* Column user interface class. This class is used on the personal desktop,
* the info screen class and witin container classes.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilColumnGUI:
*/
class ilColumnGUI
{
	protected $side = IL_COL_RIGHT;
	protected $type;
	
	
	//
	// This two arrays may be replaced by some
	// xml or other magic in the future...
	//
	
	protected $locations = array(
		"ilNewsForContextBlockGUI" => "Services/News/",
		"ilPDNotesBlockGUI" => "Services/Notes/",
		"ilPDMailBlockGUI" => "Services/Mail/",
		"ilUsersOnlineBlockGUI" => "Services/PersonalDesktop/",
		"ilPDSysMessageBlockGUI" => "Services/Mail/",
		"ilPDSelectedItemsBlockGUI" => "Services/PersonalDesktop/",
		"ilBookmarkBlockGUI" => "Services/PersonalDesktop/",
		"ilPDNewsBlockGUI" => "Services/News/",
		"ilExternalFeedBlockGUI" => "Services/Feeds/",
		"ilPDFeedbackBlockGUI" => "Services/Feedback/");
	
	protected $blocks = array(
		"info" => array(
			IL_COL_LEFT => array(),
			IL_COL_CENTER => array(),
			IL_COL_RIGHT => array("ilNewsForContextBlockGUI")),
		"pd" => array(
			IL_COL_LEFT => array("ilPDSysMessageBlockGUI", "ilPDFeedbackBlockGUI",
				"ilPDNewsBlockGUI", "ilExternalFeedBlockGUI"),
			IL_COL_CENTER => array("ilPDSelectedItemsBlockGUI"),
			IL_COL_RIGHT => array("ilPDMailBlockGUI", "ilPDNotesBlockGUI",
				"ilUsersOnlineBlockGUI", "ilBookmarkBlockGUI"))
		);

	/**
	* Constructor
	*
	* @param
	*/
	function ilColumnGUI($a_col_type = "", $a_side = "", $use_std_context = false)
	{
		global $ilUser, $tpl, $ilCtrl;

		$this->setColType($a_col_type);
		//if ($a_side == "")
		//{
		//	$a_side = $_GET["col_side"];
		//}

		$this->setSide($a_side);
	}

	/**
	* Get Column Side of Current Command
	*
	* @return	string	Column Side
	*/
	function getCmdSide()
	{
		return $_GET["col_side"];
	}

	/**
	* Set Column Type.
	*
	* @param	string	$a_coltype	Column Type
	*/
	function setColType($a_coltype)
	{
		$this->coltype = $a_coltype;
	}

	/**
	* Get Column Type.
	*
	* @return	string	Column Type
	*/
	function getColType()
	{
		return $this->coltype;
	}

	/**
	* Set Side IL_COL_LEFT | IL_COL_RIGHT.
	*
	* @param	string	$a_side	Side IL_COL_LEFT | IL_COL_RIGHT
	*/
	function setSide($a_side)
	{
		$this->side = $a_side;
	}

	/**
	* Get Side IL_COL_LEFT | IL_COL_RIGHT.
	*
	* @return	string	Side IL_COL_LEFT | IL_COL_RIGHT
	*/
	function getSide()
	{
		return $this->side;
	}

	/**
	* Get Screen Mode for current command.
	*/
	function getScreenMode()
	{
		global $ilCtrl;

		//if ($ilCtrl->getNextClass()
		if (is_array($this->blocks[$this->getColType()][$this->getCmdSide()]))
		{
			foreach($this->blocks[$this->getColType()][$this->getCmdSide()] as $block_class)
			{
				include_once("./".$this->locations[$block_class]."classes/".
					"class.".$block_class.".php");
				$block_type = call_user_func(array($block_class, 'getBlockType'));
				if ($block_type == $_GET["block_type"])
				{
					return call_user_func(array($block_class, 'getScreenMode'));
				}
			}
		}

		return IL_SCREEN_SIDE;
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilCtrl;
		
		$ilCtrl->setParameter($this, "col_side" ,$this->getSide());
		//$ilCtrl->saveParameter($this, "col_side");

		$next_class = $ilCtrl->getNextClass();
		$cmd = $ilCtrl->getCmd("getHTML");

		if ($next_class != "")
		{
			foreach($this->blocks[$this->getColType()][$this->getSide()] as $block_class)
			{
				include_once("./".$this->locations[$block_class]."classes/".
					"class.".$block_class.".php");
				$block_type = call_user_func(array($block_class, 'getBlockType'));
				if ($block_type == $_GET["block_type"])
				{
					$ilCtrl->setParameter($this, "block_type", $block_type);
					$block_gui = new $block_class();
					$html = $ilCtrl->forwardCommand($block_gui);
					$ilCtrl->setParameter($this, "block_type", "");
					
					return $html;
				}
			}
		}
		else
		{
			return $this->$cmd();
		}
	}

	/**
	* Get HTML for column.
	*/
	function getHTML()
	{
		global $ilCtrl;
		
		$ilCtrl->setParameter($this, "col_side" ,$this->getSide());
		
		$this->tpl = new ilTemplate("tpl.column.html", true, true, "Services/Block");
		
		$this->addBlocks();
		
		$this->addHiddenBlockSelector();
		
		return $this->tpl->get();
	}
	
	/**
	* Add blocks.
	*/
	function addBlocks()
	{
		global $ilCtrl;
		
		$blocks = array();
		
		foreach($this->blocks[$this->getColType()][$this->getSide()] as $block_class)
		{
			include_once("./".$this->locations[$block_class]."classes/".
				"class.".$block_class.".php");
			$block_gui = new $block_class();
			
			$ilCtrl->setParameter($this, "block_type", $block_gui->getBlockType());
			$this->tpl->setCurrentBlock("col_block");
			$html = $ilCtrl->getHTML($block_gui);

			$this->tpl->setVariable("BLOCK", $html);
			$this->tpl->parseCurrentBlock();
			$ilCtrl->setParameter($this, "block_type", "");
		}
	}

	/**
	* Add hidden block selector.
	*/
	function addHiddenBlockSelector()
	{
		global $lng, $ilUser, $ilCtrl;
		
		// show selector for hidden blocks
		include_once("Services/Block/classes/class.ilBlockSetting.php");
		$hidden_blocks = array();
		$blocks = array("pdmail" => $lng->txt("mail"),
			"pdnotes" => $lng->txt("notes"),
			"pdusers" => $lng->txt("users_online"),
			"pdnews" => $lng->txt("news"),
			"pdbookm" => $lng->txt("my_bms"),
			"news" => $lng->txt("news"),
			"feed" => $lng->txt("feed"));

		foreach($this->blocks[$this->getColType()][$this->getSide()] as $block_class)
		{
			include_once("./".$this->locations[$block_class]."classes/".
				"class.".$block_class.".php");
			$block_type = call_user_func(array($block_class, 'getBlockType'));
			if (ilBlockSetting::_lookupDetailLevel($block_type, $ilUser->getId()) == 0)
			{
				$hidden_blocks[$block_type] = $blocks[$block_type];
			}
			else if (ilBlockSetting::_lookupDetailLevel($block_type, $ilUser->getId(),
				$ilCtrl->getContextObjId()) == 0)
			{
				$hidden_blocks[$block_type."_".$ilCtrl->getContextObjId()] = $blocks[$block_type];
			}
		}
		if (count($hidden_blocks) > 0)
		{
			$this->tpl->setCurrentBlock("hidden_block_selector");
			$this->tpl->setVariable("HB_ACTION", $ilCtrl->getFormAction($this));
			$this->tpl->setVariable("BLOCK_SEL", ilUtil::formSelect("", "block", $hidden_blocks,
				false, true, 0, "ilEditSelect"));
			$this->tpl->setVariable("TXT_ACTIVATE", $lng->txt("show"));
			$this->tpl->parseCurrentBlock();
		}
		//return $tpl->get();

	}

	/**
	* Update Block (asynchronous)
	*/
	function updateBlock()
	{
		global $ilCtrl;
		
		foreach($this->blocks[$this->getColType()][$this->getSide()] as $block_class)
		{
			include_once("./".$this->locations[$block_class]."classes/".
				"class.".$block_class.".php");
			$block_type = call_user_func(array($block_class, 'getBlockType'));

			if (is_int(strpos($_GET["block_id"], "block_".$block_type."_")))
			{
				$block_gui = new $block_class();
				$ilCtrl->setParameter($this, "block_type", $block_type);
				echo $ilCtrl->getHTML($block_gui);
				exit;
			}
		}
		echo "Error: ilColumnGUI::updateBlock: Block '".
			$_GET["block_id"]."' unknown.";
		exit;
	}

	/**
	* Activate hidden block
	*/
	function activateBlock()
	{
		global $ilUser, $ilCtrl;

		if ($_POST["block"] != "")
		{
			$block = explode("_", $_POST["block"]);
			include_once("Services/Block/classes/class.ilBlockSetting.php");
			ilBlockSetting::_writeDetailLevel($block[0], 2, $ilUser->getId(), $block[1]);
		}

		$ilCtrl->returnToParent($this);
	}

}
