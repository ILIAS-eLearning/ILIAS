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

include_once("./Services/Block/classes/class.ilHtmlBlockGUIGen.php");

/**
* BlockGUI class for simle HTML content.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_IsCalledBy ilExternalFeedBlockGUI: ilColumnGUI
* @ingroup ServicesFeeds
*/
class ilHtmlBlockGUI extends ilHtmlBlockGUIGen
{
	static $block_type = "html";
	
	/**
	* Constructor
	*/
	function ilHtmlBlockGUI()
	{
		global $ilCtrl, $lng;
		
		parent::__construct();
		parent::ilBlockGUI();
		
		//$this->setImage(ilUtil::getImagePath("icon_feed_s.gif"));

		$lng->loadLanguageModule("htmlbl");

		$this->setLimit(99999);
		//$this->setAvailableDetailLevels(1);
		$this->setTitle($this->html_block->getTitle());
	}
		
	/**
	* Get block type
	*
	* @return	string	Block type.
	*/
	function getBlockType()
	{
		return self::$block_type;
	}
	
	/**
	* Get Screen Mode for current command.
	*/
	static function getScreenMode()
	{
		global $ilCtrl;
		
		switch($_GET["cmd"])
		{				
			default:
				return IL_SCREEN_SIDE;
				break;
		}
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilCtrl;

		$next_class = $ilCtrl->getNextClass();
		$cmd = $ilCtrl->getCmd("getHTML");

		switch ($next_class)
		{
			default:
				return $this->$cmd();
		}
	}

	/**
	* Get bloch HTML code.
	*/
	function getHTML()
	{
		global $ilCtrl, $lng, $ilUser;
		
		if ($this->getCurrentDetailLevel() == 0)
		{
			return "";
		}

		return parent::getHTML();
	}
	
	/**
	* Fill data section
	*/
	function fillDataSection()
	{
		$this->setDataSection($this->html_block->getContent());
	}

}

?>
