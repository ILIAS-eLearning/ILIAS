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

include_once("Services/News/classes/class.ilNewsForContextBlockGUI.php");

/**
* BlockGUI class for block NewsForContext
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id: class.ilNewsForContextBlockGUI.php 12920 2007-01-03 19:13:46Z akill $
*
* @ilCtrl_IsCalledBy ilPDNewsBlockGUI: ilColumnGUI
*
* @ingroup ServicesNews
*/
class ilPDNewsBlockGUI extends ilNewsForContextBlockGUI
{
	static $block_type = "pdnews";
	
	/**
	* Constructor
	*/
	function ilPDNewsBlockGUI()
	{
		global $ilCtrl, $lng, $ilUser;
		
		parent::ilBlockGUI();
		
		$this->setImage(ilUtil::getImagePath("icon_news_s.gif"));

		$lng->loadLanguageModule("news");
		include_once("./Services/News/classes/class.ilNewsItem.php");
		
		$data = ilNewsItem::_getNewsItemsOfUser($ilUser->getId());

		$this->setLimit(5);
		$this->setAvailableDetailLevels(3);
		
		$this->setTitle($lng->txt("news_internal_news"));
		$this->setRowTemplate("tpl.block_row_news_for_context.html", "Services/News");
		$this->setData($data);
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
			case "showNews":
			case "showFeedUrl":
				return IL_SCREEN_CENTER;
				break;
			
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
	* Fill data section
	*/
	function fillDataSection()
	{
		if ($this->getCurrentDetailLevel() > 1 && count($this->getData()) > 0)
		{
			parent::fillDataSection();
		}
		else
		{
			$this->setEnableNumInfo(false);
			$this->setDataSection($this->getOverview());
		}
	}

	/**
	* Get bloch HTML code.
	*/
	function getHTML()
	{
		global $ilCtrl, $lng, $ilUser;
		
		// subscribe/unsibscribe link
		include_once("./Services/News/classes/class.ilNewsSubscription.php");
		
		$this->handleView();
		
		// show feed url
		$this->addBlockCommand(
			$ilCtrl->getLinkTarget($this, "showFeedUrl"),
			$lng->txt("news_get_feed_url"));

		if ($this->getCurrentDetailLevel() == 0)
		{
			return "";
		}

		return ilBlockGUI::getHTML();
	}
	
	/**
	* get flat bookmark list for personal desktop
	*/
	function fillRow($news)
	{
		global $ilUser, $ilCtrl, $lng;

		if ($this->getCurrentDetailLevel() > 2)
		{
			$this->tpl->setCurrentBlock("long");
			$this->tpl->setVariable("VAL_CONTENT", $news["content"]);
			$this->tpl->parseCurrentBlock();
			$this->tpl->setVariable("VAL_CREATION_DATE", $news["creation_date"]);
		}
		
		if ($news["priority"] == 0)
		{
			$this->tpl->setCurrentBlock("notification");
			$this->tpl->setVariable("CHAR_NOT", $lng->txt("news_first_letter_of_word_notification"));
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setCurrentBlock("news_context");
		$this->tpl->setVariable("TYPE", $lng->txt("obj_".$news["context_obj_type"]));
		$this->tpl->setVariable("IMG_TYPE",
			ilUtil::getImagePath("icon_".$news["context_obj_type"]."_s.gif"));
		$this->tpl->setVariable("TITLE", ilObject::_lookupTitle($news["context_obj_id"]));
		$this->tpl->parseCurrentBlock();
		$ilCtrl->setParameter($this, "news_context", $news["ref_id"]);
		
		$this->tpl->setVariable("VAL_TITLE", $news["title"]);
		
		$ilCtrl->setParameter($this, "news_id", $news["id"]);
		$this->tpl->setVariable("HREF_SHOW",
			$ilCtrl->getLinkTarget($this, "showNews"));
		$ilCtrl->clearParameters($this);
	}

	/**
	* Show feed URL.
	*/
	function showFeedUrl()
	{
		global $lng, $ilCtrl, $ilUser;
		
		include_once("./Services/News/classes/class.ilNewsItem.php");
		
		$tpl = new ilTemplate("tpl.show_feed_url.html", true, true, "Services/News");
		$tpl->setVariable("TXT_TITLE", $lng->txt("news_get_feed_title"));
		$tpl->setVariable("TXT_INFO", $lng->txt("news_get_feed_info"));
		$tpl->setVariable("TXT_FEED_URL", $lng->txt("news_feed_url"));
		$tpl->setVariable("VAL_FEED_URL",
			ILIAS_HTTP_PATH."/feed.php?client_id=".rawurlencode(CLIENT_ID)."&user_id=".$ilUser->getId().
				"&hash=".ilObjUser::_lookupFeedHash($ilUser->getId(), true));
		$tpl->setVariable("VAL_FEED_URL_TXT",
			ILIAS_HTTP_PATH."/feed.php?client_id=".rawurlencode(CLIENT_ID)."&<br />user_id=".$ilUser->getId().
				"&hash=".ilObjUser::_lookupFeedHash($ilUser->getId(), true));
		
		include_once("./Services/PersonalDesktop/classes/class.ilPDContentBlockGUI.php");
		$content_block = new ilPDContentBlockGUI();
		$content_block->setContent($tpl->get());
		$content_block->setTitle($lng->txt("news_internal_news"));
		$content_block->setImage(ilUtil::getImagePath("icon_news.gif"));
		$content_block->addHeaderCommand($ilCtrl->getParentReturn($this),
			$lng->txt("close"), true);

		return $content_block->getHTML();
	}

}

?>
