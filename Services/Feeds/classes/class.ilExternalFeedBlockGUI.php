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

include_once("./Services/Block/classes/class.ilBlockGUI.php");
include_once("./Services/Feeds/classes/class.ilExternalFeed.php");

/**
* BlockGUI class for external feed block
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_IsCalledBy ilExternalFeedBlockGUI: ilColumnGUI
* @ingroup ServicesFeeds
*/
class ilExternalFeedBlockGUI extends ilBlockGUI
{
	static $block_type = "feed";
	
	/**
	* Constructor
	*/
	function ilExternalFeedBlockGUI()
	{
		global $ilCtrl, $lng;
		
		parent::ilBlockGUI();
		
		$this->setImage(ilUtil::getImagePath("icon_feed_s.gif"));

		$lng->loadLanguageModule("feed");
		include_once("./Services/Feeds/classes/class.ilExternalFeed.php");
		$this->feed = new ilExternalFeed();
		
		// Tagesschau: RSS 2.0
		//$this->feed->setUrl("http://www.tagesschau.de/xml/rss2");

		// Planet Eduforge: RSS 2.0
		//$this->feed->setUrl("http://planet.eduforge.org/rss20.xml");

		// ILIAS open source forum
		$this->feed->setUrl("http://www.ilias.de/iosbb/rss.php");
		
		// Spiegel: RSS 0.91
		//$this->feed->setUrl("http://www.spiegel.de/schlagzeilen/rss/index.xml");

		// Heise: Atom
		//$this->feed->setUrl("http://www.heise.de/newsticker/heise-atom.xml");

		$this->feed->fetch();
		
		$this->setLimit(5);
		$this->setAvailableDetailLevels(2);
		$this->setTitle($this->feed->getChannelTitle());
		$this->setRowTemplate("tpl.block_external_feed_row.html", "Services/Feeds");
		$this->setData($this->feed->getItems());
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
			case "showFeedItem":
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
/*
		include_once("./Services/News/classes/class.ilNewsSubscription.php");
		if (ilNewsSubscription::_hasSubscribed($_GET["ref_id"], $ilUser->getId()))
		{
			$this->addBlockCommand(
				$ilCtrl->getLinkTarget($this, "unsubscribeNews"),
				$lng->txt("news_unsubscribe"));
		}
		else
		{
			$this->addBlockCommand(
				$ilCtrl->getLinkTarget($this, "subscribeNews"),
				$lng->txt("news_subscribe"));
		}
		
		// add edit commands
		if ($this->getEnableEdit())
		{
			$this->addBlockCommand(
				$ilCtrl->getLinkTargetByClass("ilnewsitemgui", "editNews"),
				$lng->txt("edit"));

			$this->addBlockCommand(
				$ilCtrl->getLinkTargetByClass("ilnewsitemgui", "createNewsItem"),
				$lng->txt("add"));
		}
*/
		
		if ($this->getCurrentDetailLevel() == 0)
		{
			return "";
		}

		return parent::getHTML();
	}

	/**
	* Fill feed item row
	*/
	function fillRow($item)
	{
		global $ilUser, $ilCtrl, $lng;

		$ilCtrl->setParameter($this, "feed_item_id", $item->getId());
		$this->tpl->setVariable("VAL_TITLE", $item->getTitle());
		$this->tpl->setVariable("HREF_SHOW",
			$ilCtrl->getLinkTarget($this, "showFeedItem"));
		$ilCtrl->setParameter($this, "feed_item_id", "");
	}

	/**
	* Get overview.
	*/
	function getOverview()
	{
		global $ilUser, $lng, $ilCtrl;
		
		$this->setEnableNumInfo(false);
		return '<div class="small">'.((int) count($this->getData()))." ".$lng->txt("feed_feed_items")."</div>";
	}

	/**
	* Show Feed Item
	*/
	function showFeedItem()
	{
		global $lng, $ilCtrl;
		
		include_once("./Services/News/classes/class.ilNewsItem.php");
		
		foreach($this->feed->getItems() as $item)
		{
			if ($item->getId() == $_GET["feed_item_id"])
			{
				$c_item = $item;
			}
		}
		
		$tpl = new ilTemplate("tpl.show_feed_item.html", true, true, "Services/Feeds");
		
		if (is_object($c_item))
		{
//var_dump($c_item->getMagpieItem());
			if (trim($c_item->getSummary()) != "")		// summary
			{
				$tpl->setCurrentBlock("content");
				$tpl->setVariable("VAL_CONTENT", $c_item->getSummary());
				$tpl->parseCurrentBlock();
			}
			if (trim($c_item->getDate()) != "" || trim($c_item->getAuthor()) != "")		// date
			{
				$tpl->setCurrentBlock("date");
				if (trim($c_item->getAuthor()) != "")
				{
					$tpl->setVariable("VAL_AUTHOR", $c_item->getAuthor()." - ");
				}
				$tpl->setVariable("VAL_DATE", $c_item->getDate());
				$tpl->parseCurrentBlock();
			}
			if (trim($c_item->getLink()) != "")		// link
			{
				$tpl->setCurrentBlock("link");
				$tpl->setVariable("HREF_LINK", $c_item->getLink());
				$tpl->setVariable("TXT_LINK", $lng->txt("feed_open_source_page"));
				$tpl->parseCurrentBlock();
			}
			$tpl->setVariable("VAL_TITLE", $c_item->getTitle());			// title
		}
		
		include_once("./Services/PersonalDesktop/classes/class.ilPDContentBlockGUI.php");
		$content_block = new ilPDContentBlockGUI();
		$content_block->setContent($tpl->get());
		$content_block->setTitle($this->feed->getChannelTitle());
		$content_block->setImage(ilUtil::getImagePath("icon_feed.gif"));
		$content_block->addHeaderCommand($ilCtrl->getParentReturn($this),
			$lng->txt("close"), true);

		return $content_block->getHTML();
	}
}

?>
