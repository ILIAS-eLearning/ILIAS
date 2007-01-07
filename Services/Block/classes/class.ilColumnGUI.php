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
		"ilHtmlBlockGUI" => "Services/Block/",
		"ilPDFeedbackBlockGUI" => "Services/Block/");
	
	protected $block_types = array(
			"ilPDMailBlockGUI" => "pdmail",
			"ilPDNotesBlockGUI" => "pdnotes",
			"ilUsersOnlineBlockGUI" => "pdusers",
			"ilPDNewsBlockGUI" => "pdnews",
			"ilBookmarkBlockGUI" => "pdbookm",
			"ilNewsForContextBlockGUI" => "news",
			"ilExternalFeedBlockGUI" => "feed",
			"ilPDFeedbackBlockGUI" => "pdfeedb",
			"ilPDSysMessageBlockGUI" => "pdsysmess",
			"ilPDSelectedItemsBlockGUI" => "pditems",
			"ilHtmlBlockGUI" => "html"
		);

		
	protected $default_blocks = array(
		"info" => array(
			"ilNewsForContextBlockGUI" => IL_COL_RIGHT),
		"pd" => array(
			"ilPDSysMessageBlockGUI" => IL_COL_LEFT,
			"ilPDFeedbackBlockGUI" => IL_COL_LEFT,
			"ilPDNewsBlockGUI" => IL_COL_LEFT,
			"ilPDSelectedItemsBlockGUI" => IL_COL_CENTER,
			"ilPDMailBlockGUI" => IL_COL_RIGHT,
			"ilPDNotesBlockGUI" => IL_COL_RIGHT,
			"ilUsersOnlineBlockGUI" => IL_COL_RIGHT,
			"ilBookmarkBlockGUI" => IL_COL_RIGHT)
		);

	protected $custom_blocks = array(
		"info" => array(""),
		"pd" => array("ilExternalFeedBlockGUI")
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

		if ($ilCtrl->getCmdClass() == "ilcolumngui")
		{
			switch ($ilCtrl->getCmd())
			{
				case "addBlock":
					return IL_SCREEN_CENTER;
			}
		}

		$cur_block_type = ($_GET["block_type"])
			? $_GET["block_type"]
			: $_POST["block_type"];

		if ($class = array_search($cur_block_type, $this->block_types))
		{
			include_once("./".$this->locations[$class]."classes/".
				"class.".$class.".php");
			return call_user_func(array($class, 'getScreenMode'));
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

		$cur_block_type = ($_GET["block_type"])
			? $_GET["block_type"]
			: $_POST["block_type"];

		if ($next_class != "")
		{
			// forward to block
			if ($class = array_search($cur_block_type, $this->block_types))
			{
				include_once("./".$this->locations[$class]."classes/".
					"class.".$class.".php");
				$ilCtrl->setParameter($this, "block_type", $cur_block_type);
				$block_gui = new $class();
				$html = $ilCtrl->forwardCommand($block_gui);
				$ilCtrl->setParameter($this, "block_type", "");
				
				return $html;
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
		
		$this->determineBlocks();
		$this->showBlocks();
		
		$this->addHiddenBlockSelector();
		
		return $this->tpl->get();
	}
	
	/**
	* Show blocks.
	*/
	function showBlocks()
	{
		global $ilCtrl;
		
		$blocks = array();
		
		$i = 1;
		foreach($this->blocks[$this->getSide()] as $block)
		{
			$gui_class = $block["class"];
			$block_class = substr($block["class"], 0, strlen($block["class"])-3);
			
			// get block gui class
			include_once("./".$this->locations[$gui_class]."classes/".
				"class.".$gui_class.".php");
			$block_gui = new $gui_class();
			if ($this->getSide() == IL_COL_LEFT)
			{
				$block_gui->setAllowMove("right");
			}
			else if ($this->getSide() == IL_COL_RIGHT)
			{
				$block_gui->setAllowMove("left");
			}
			if ($i > 1)
			{
				$block_gui->setAllowMove("up");
			}
			if ($i < count($this->blocks[$this->getSide()]))
			{
				$block_gui->setAllowMove("down");
			}
			
			// get block for custom blocks
			if ($block["custom"])
			{
				include_once("./".$this->locations[$gui_class]."classes/".
					"class.".$block_class.".php");
				$app_block = new $block_class($block["id"]);
				$block_gui->setBlock($app_block);
			}

			$ilCtrl->setParameter($this, "block_type", $block_gui->getBlockType());
			$this->tpl->setCurrentBlock("col_block");
			$html = $ilCtrl->getHTML($block_gui);

			$this->tpl->setVariable("BLOCK", $html);
			$this->tpl->parseCurrentBlock();
			$ilCtrl->setParameter($this, "block_type", "");
			
			// count (moveable) blocks
			if ($block["type"] != "pdsysmess" && $block["type"] != "pdfeedb")
			{
				$i++;
			}
		}
	}

	/**
	* Add hidden block and create block selectors.
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
			"pdnews" => $lng->txt("news_internal_news"),
			"pdbookm" => $lng->txt("my_bms"),
			"news" => $lng->txt("news_internal_news"),
			"feed" => $lng->txt("feed"),
			"html" => $lng->txt("html_block"),
			);

		foreach($this->blocks[$this->getSide()] as $block)
		{
			include_once("./".$this->locations[$block["class"]]."classes/".
				"class.".$block["class"].".php");
				
			if ($block["custom"] == false)
			{
				if ($ilCtrl->getContextObjType() == "user")	// personal desktop
				{
					if (ilBlockSetting::_lookupDetailLevel($block["type"], $ilUser->getId()) == 0)
					{
						$hidden_blocks[$block["type"]] = $blocks[$block["type"]];
					}
				}
				else if ($ilCtrl->getContextObjType() != "")
				{
					if (ilBlockSetting::_lookupDetailLevel($block["type"], $ilUser->getId(),
						$ilCtrl->getContextObjId()) == 0)
					{
						$hidden_blocks[$block_type."_".$ilCtrl->getContextObjId()] = $blocks[$block_type];
					}
				}
			}
			else
			{
				if (ilBlockSetting::_lookupDetailLevel($block["type"], $ilUser->getId(),
					$block["id"]) == 0)
				{
					$hidden_blocks[$block_type."_".$block["id"]] = $blocks[$block["type"]];
				}
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
		
		// create block selection list
		$add_blocks = array();
		if ($this->getSide() == IL_COL_RIGHT)
		{
			foreach($this->custom_blocks[$this->getColType()] as $block_class)
			{
				include_once("./".$this->locations[$block_class]."classes/".
					"class.".$block_class.".php");
				$block_type = call_user_func(array($block_class, 'getBlockType'));
				$add_blocks[$block_type] = $blocks[$block_type];
			}
		}
		if (count($add_blocks) > 0)
		{
			$this->tpl->setCurrentBlock("add_block_selector");
			$this->tpl->setVariable("AB_ACTION", $ilCtrl->getFormAction($this));
			$this->tpl->setVariable("ADD_BLOCK_SEL", ilUtil::formSelect("", "block_type", $add_blocks,
				false, true, 0, "ilEditSelect"));
			$this->tpl->setVariable("TXT_ADD", $lng->txt("create"));
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
		
		$this->determineBlocks();
		$i = 1;
		foreach ($this->blocks[$this->getSide()] as $block)
		{
			include_once("./".$this->locations[$block["class"]]."classes/".
				"class.".$block["class"].".php");

			if (is_int(strpos($_GET["block_id"], "block_".$block["type"]."_")))
			{
				$gui_class = $block["class"];
				$block_class = substr($block["class"], 0, strlen($block["class"])-3);
				
				$block_gui = new $gui_class();
				if ($this->getSide() == IL_COL_LEFT)
				{
					$block_gui->setAllowMove("right");
				}
				else if ($this->getSide() == IL_COL_RIGHT)
				{
					$block_gui->setAllowMove("left");
				}
				if ($i > 1)
				{
					$block_gui->setAllowMove("up");
				}
				if ($i < count($this->blocks[$this->getSide()]))
				{
					$block_gui->setAllowMove("down");
				}
				
				// get block for custom blocks
				if ($block["custom"])
				{
					include_once("./".$this->locations[$gui_class]."classes/".
						"class.".$block_class.".php");
					$app_block = new $block_class($block["id"]);
					$block_gui->setBlock($app_block);
				}

				$ilCtrl->setParameter($this, "block_type", $block["type"]);
				echo $ilCtrl->getHTML($block_gui);
				exit;
			}
			
			// count (moveable) blocks
			if ($block["type"] != "pdsysmess" && $block["type"] != "pdfeedb")
			{
				$i++;
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

	/**
	* Add a block
	*/
	function addBlock()
	{
		global $ilCtrl;
		
		$class = array_search($_POST["block_type"], $this->block_types);
		$ilCtrl->setCmdClass($class);
		$ilCtrl->setCmd("create");
		include_once("./".$this->locations[$class]."classes/class.".$class.".php");
		$block_gui = new $class();
		
		$ilCtrl->setParameter($this, "block_type", $_POST["block_type"]);
		$html = $ilCtrl->forwardCommand($block_gui);
		$ilCtrl->setParameter($this, "block_type", "");
		return $html;
	}
	
	function determineBlocks()
	{
		global $ilUser, $ilCtrl;
		
		include_once("./Services/Block/classes/class.ilBlockSetting.php");
		$this->blocks[IL_COL_LEFT] = array();
		$this->blocks[IL_COL_RIGHT] = array();
		$this->blocks[IL_COL_CENTER] = array();
		
		$user_id = ($this->getColType() == "pd")
			? $ilUser->getId()
			: 0;
		
		$def_nr = 1000;
		foreach($this->default_blocks[$this->getColType()] as $class => $def_side)
		{
			$type = $this->block_types[$class];
			$nr = ilBlockSetting::_lookupNr($type, $user_id);
			if ($nr === false)
			{
				$nr = $def_nr++;
			}
			// extra handling for system messages and feedback block
			if ($type == "pdsysmess")		// always show sys mess first
			{
				$nr = -15;
			}
			if ($type == "pdfeedb")		// always show feedback request second
			{
				$nr = -10;
			}
			$side = ilBlockSetting::_lookupSide($type, $user_id);
			if ($side === false)
			{
				$side = $def_side;
			}
			$this->blocks[$side][] = array(
				"nr" => $nr,
				"class" => $class,
				"type" => $type,
				"id" => 0,
				"custom" => false);
		}
		
		include_once("./Services/Block/classes/class.ilCustomBlock.php");
		$costum_block = new ilCustomBlock();
		$costum_block->setContextObjId($ilCtrl->getContextObjId());
		$costum_block->setContextObjType($ilCtrl->getContextObjType());
		$c_blocks = $costum_block->queryBlocksForContext();
		foreach($c_blocks as $c_block)
		{
			$type = $c_block["type"];
			$class = array_search($type, $this->block_types);
			$nr = ilBlockSetting::_lookupNr($type, $user_id, $c_block["id"]);
			if ($nr === false)
			{
				$nr = $def_nr++;
			}
			$side = ilBlockSetting::_lookupSide($type, $user_id, $c_block["id"]);
			if ($side === false)
			{
				$side = $def_side;
			}
			$this->blocks[$side][] = array(
				"nr" => $nr,
				"class" => $class,
				"type" => $type,
				"id" => $c_block["id"],
				"custom" => true);
		}
		
		$this->blocks[IL_COL_LEFT] =
			ilUtil::sortArray($this->blocks[IL_COL_LEFT], "nr", "asc", true);
		$this->blocks[IL_COL_RIGHT] =
			ilUtil::sortArray($this->blocks[IL_COL_RIGHT], "nr", "asc", true);
		$this->blocks[IL_COL_CENTER] =
			ilUtil::sortArray($this->blocks[IL_COL_CENTER], "nr", "asc", true);

	}

	function moveBlock()
	{
		global $ilUser, $ilCtrl;
		
		$this->determineBlocks();
		
		if ($this->getColType() == "pd")
		{
			$bid = explode("_", $_GET["block_id"]);
			$i = 2;
			foreach($this->blocks[$this->getCmdSide()] as $block)
			{
				// only handle non-hidden blocks
				if (ilBlockSetting::_lookupDetailLevel($block["type"],
					$ilUser->getId(), $block["id"]) != 0)
				{
					ilBlockSetting::_writeNumber($block["type"], $i, $ilUser->getId(), $block["id"]);

					if ($block["type"] == $bid[0] && $block["id"] == $bid[1])
					{
						if ($_GET["move_dir"] == "up")
						{
							ilBlockSetting::_writeNumber($block["type"], $i-3, $ilUser->getId(), $block["id"]);
						}
						if ($_GET["move_dir"] == "down")
						{
							ilBlockSetting::_writeNumber($block["type"], $i+3, $ilUser->getId(), $block["id"]);
						}
						if ($_GET["move_dir"] == "left")
						{
							ilBlockSetting::_writeNumber($block["type"], 200, $ilUser->getId(), $block["id"]);
							ilBlockSetting::_writeSide($block["type"], IL_COL_LEFT, $ilUser->getId(), $block["id"]);
						}
						if ($_GET["move_dir"] == "right")
						{
							ilBlockSetting::_writeNumber($block["type"], 200, $ilUser->getId(), $block["id"]);
							ilBlockSetting::_writeSide($block["type"], IL_COL_RIGHT, $ilUser->getId(), $block["id"]);
						}
					}
					else
					{
						ilBlockSetting::_writeNumber($block["type"], $i, $ilUser->getId(), $block["id"]);
					}
					$i+=2;
				}
			}
		}
		$ilCtrl->returnToParent($this);
	}
}
