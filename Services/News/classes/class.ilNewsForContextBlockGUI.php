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

include_once("Services/Block/classes/class.ilBlockGUI.php");

/**
* BlockGUI class for block NewsForContext
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_IsCalledBy ilNewsForContextBlockGUI: ilColumnGUI
* @ilCtrl_Calls ilNewsForContextBlockGUI: ilNewsItemGUI
*/
class ilNewsForContextBlockGUI extends ilBlockGUI
{
	static $block_type = "news";
	
	/**
	* Constructor
	*/
	function ilNewsForContextBlockGUI()
	{
		global $ilCtrl, $lng;
		
		parent::ilBlockGUI();
		
		$this->setImage(ilUtil::getImagePath("icon_news_s.gif"));

		$lng->loadLanguageModule("news");
		include_once("./Services/News/classes/class.ilNewsItem.php");
		$news_item = new ilNewsItem();
		$news_item->setContextObjId($ilCtrl->getContextObjId());
		$news_item->setContextObjType($ilCtrl->getContextObjType());
		$this->setBlockId($ilCtrl->getContextObjId());
		$this->setLimit(5);
		$this->setAvailableDetailLevels(3);
		if ($ilCtrl->getContextObjType() ==  "crs")
		{
			$data = $news_item->getAggregatedNewsData($_GET["ref_id"]);
		}
		else
		{
			$data = $news_item->queryNewsForContext();
		}
		$this->setTitle($lng->txt("news_internal_news"));
		$this->setRowTemplate("tpl.block_row_news_for_context.html", "Services/News");
		$this->setData($data);
		$this->allow_moving = false;
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
		
		if ($ilCtrl->getCmdClass() == "ilnewsitemgui")
		{
			return IL_SCREEN_CENTER;
		}
		
		switch($_GET["cmd"])
		{
			case "showNews":
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
			case "ilnewsitemgui":
				include_once("./Services/News/classes/class.ilNewsItemGUI.php");
				$news_item_gui = new ilNewsItemGUI();
				$news_item_gui->setEnableEdit($this->getEnableEdit());
				$html = $ilCtrl->forwardCommand($news_item_gui);
				return $html;
				
			default:
				return $this->$cmd();
		}
	}

	/**
	* Set EnableEdit.
	*
	* @param	boolean	$a_enable_edit	Edit mode on/off
	*/
	public function setEnableEdit($a_enable_edit = 0)
	{
		$this->enable_edit = $a_enable_edit;
	}

	/**
	* Get EnableEdit.
	*
	* @return	boolean	Edit mode on/off
	*/
	public function getEnableEdit()
	{
		return $this->enable_edit;
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
		
		if ($this->getCurrentDetailLevel() == 0)
		{
			return "";
		}
		
		if (count($this->getData()) == 0 && !$this->getEnableEdit() &&
			$this->getRepositoryMode())
		{
			return "";
		}

		return parent::getHTML();
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
			$this->tpl->setVariable("VAL_CREATION_DATE", $news["creation_date"]);
			$this->tpl->parseCurrentBlock();
		}
		
		if ($news["ref_id"] > 0 && $news["ref_id"] != $_GET["ref_id"])
		{
			$type = in_array($news["context_obj_type"], array("sahs", "lm", "dbk", "htlm"))
				? "lres"
				: "obj_".$news["context_obj_type"];

			$this->tpl->setCurrentBlock("news_context");
			$this->tpl->setVariable("TYPE", $lng->txt($type));
			$this->tpl->setVariable("TITLE", ilObject::_lookupTitle($news["context_obj_id"]));
			$this->tpl->parseCurrentBlock();
			$ilCtrl->setParameter($this, "news_context", $news["ref_id"]);
		}

		$this->tpl->setVariable("VAL_TITLE", $news["title"]);
		
		$ilCtrl->setParameter($this, "news_id", $news["id"]);
		$this->tpl->setVariable("HREF_SHOW",
			$ilCtrl->getLinkTarget($this, "showNews"));
		$ilCtrl->clearParameters($this);
	}

	/**
	* Get overview.
	*/
	function getOverview()
	{
		global $ilUser, $lng, $ilCtrl;
				
		return '<div class="small">'.((int) count($this->getData()))." ".$lng->txt("news_news_items")."</div>";
	}

	/**
	* show news
	*/
	function showNews()
	{
		global $lng, $ilCtrl;
		
		include_once("./Services/News/classes/class.ilNewsItem.php");
		$news = new ilNewsItem($_GET["news_id"]);
		
		$tpl = new ilTemplate("tpl.show_news.html", true, true, "Services/News");
		if (trim($news->getContent()) != "")		// content
		{
			$tpl->setCurrentBlock("content");
			$tpl->setVariable("VAL_CONTENT", $news->getContent());
			$tpl->parseCurrentBlock();
		}
		if (trim($news->getContentLong()) != "")	// long content
		{
			$tpl->setCurrentBlock("long");
			$tpl->setVariable("VAL_LONG_CONTENT", $news->getContentLong());
			$tpl->parseCurrentBlock();
		}
		if ($news->getUpdateDate() != $news->getCreationDate())		// update date
		{
			$tpl->setCurrentBlock("ni_update");
			$tpl->setVariable("TXT_LAST_UPDATE", $lng->txt("last_update"));
			$tpl->setVariable("VAL_LAST_UPDATE", $news->getUpdateDate());
			$tpl->parseCurrentBlock();
		}
		if ($_GET["news_context"] != "")		// link
		{
			$obj_id = ilObject::_lookupObjId($_GET["news_context"]);
			$obj_type = ilObject::_lookupType($obj_id);
			$tpl->setCurrentBlock("link");
			$tpl->setVariable("HREF_LINK",
				"./goto.php?client_id=".rawurlencode(CLIENT_ID)."&target=".$obj_type."_".$_GET["news_context"]);
			$txt = in_array($obj_type, array("sahs", "lm", "dbk", "htlm"))
				? "lres"
				: "obj_".$obj_type;
			$tpl->setVariable("TXT_LINK", $lng->txt($txt).": ".ilObject::_lookupTitle($obj_id));
			$tpl->parseCurrentBlock();
		}
		$tpl->setVariable("VAL_TITLE", $news->getTitle());			// title
		$tpl->setVariable("VAL_CREATION_DATE", $news->getCreationDate());	// creation date
		
		include_once("./Services/PersonalDesktop/classes/class.ilPDContentBlockGUI.php");
		$content_block = new ilPDContentBlockGUI();
		$content_block->setContent($tpl->get());
		$content_block->setTitle($lng->txt("news_internal_news"));
		//$content_block->setColSpan(2);
		$content_block->setImage(ilUtil::getImagePath("icon_news.gif"));
		$content_block->addHeaderCommand($ilCtrl->getParentReturn($this),
			$lng->txt("close"), true);

		return $content_block->getHTML();
	}

	/**
	* Unsubscribe current user from news
	*/
	function unsubscribeNews()
	{
		global $ilUser, $ilCtrl;
		
		include_once("./Services/News/classes/class.ilNewsSubscription.php");
		ilNewsSubscription::_unsubscribe($_GET["ref_id"], $ilUser->getId());
		$ilCtrl->returnToParent($this);
	}

	/**
	* Subscribe current user from news
	*/
	function subscribeNews()
	{
		global $ilUser, $ilCtrl;

		include_once("./Services/News/classes/class.ilNewsSubscription.php");
		ilNewsSubscription::_subscribe($_GET["ref_id"], $ilUser->getId());
		$ilCtrl->returnToParent($this);
	}
}

?>
