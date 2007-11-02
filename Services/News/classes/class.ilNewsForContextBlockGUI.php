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
*
* @ingroup ServicesNews
*/
class ilNewsForContextBlockGUI extends ilBlockGUI
{
	static $block_type = "news";
	static $st_data;
	
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
		$this->setBlockId($ilCtrl->getContextObjId());
		$this->setLimit(5);
		$this->setAvailableDetailLevels(3);
		$this->setEnableNumInfo(true);
		
		if (!empty(self::$st_data))
		{
			$data = self::$st_data;
		}
		else
		{
			$data = $this->getNewsData();
			self::$st_data = $data;
		}
		
		$this->setTitle($lng->txt("news_internal_news"));
		$this->setRowTemplate("tpl.block_row_news_for_context.html", "Services/News");
		$this->setData($data);
		$this->allow_moving = false;
		$this->handleView();
	}
	
	/**
	* Get news for context
	*/
	function getNewsData()
	{
		global $ilCtrl;
		
		$news_item = new ilNewsItem();
		$news_item->setContextObjId($ilCtrl->getContextObjId());
		$news_item->setContextObjType($ilCtrl->getContextObjType());
		
		// workaround, better: reduce constructor and introduce
		//$prevent_aggregation = $this->getProperty("prevent_aggregation");
//		if ($ilCtrl->getContextObjType() == "frm")
//		{
			$prevent_aggregation = true;
//		}
		
		return $news_item->getNewsForRefId($_GET["ref_id"], false, false, 0,
			$prevent_aggregation);
	}
		
	/**
	* Get block type
	*
	* @return	string	Block type.
	*/
	static function getBlockType()
	{
		return self::$block_type;
	}

	/**
	* Is this a repository object
	*
	* @return	string	Block type.
	*/
	static function isRepositoryObject()
	{
		return false;
	}
	
	/**
	* Get Screen Mode for current command.
	*/
	static function getScreenMode()
	{
		global $ilCtrl;
		
		if ($ilCtrl->getCmdClass() == "ilnewsitemgui")
		{
			return IL_SCREEN_FULL;
		}
		
		switch($ilCtrl->getCmd())
		{
			case "showNews":
			case "showFeedUrl":
				return IL_SCREEN_CENTER;
				break;
				
			case "editSettings":
			case "saveSettings":
				return IL_SCREEN_FULL;
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
		
		$news_set = new ilSetting("news");
		$enable_internal_rss = $news_set->get("enable_rss_for_internal");
		
		$hide_block = ilBlockSetting::_lookup($this->getBlockType(), "hide_news_block",
			0, $this->block_id);
		if ($hide_block)
		{
			$this->setFooterInfo($lng->txt("news_hidden_news_block"));
		}

		if ($this->getProperty("title") != "")
		{
			$this->setTitle($this->getProperty("title"));
		}
		
		$public_feed = ilBlockSetting::_lookup($this->getBlockType(), "public_feed",
			0, $this->block_id);
		if ($public_feed)
		{
			if ($enable_internal_rss)
			{
				$this->addBlockCommand(
					ILIAS_HTTP_PATH."/feed.php?client_id=".rawurlencode(CLIENT_ID)."&".
						"ref_id=".$_GET["ref_id"],
						$lng->txt("news_feed_url"), "_blank",
						ilUtil::getImagePath("rss.gif"));
			}
		}

/*	Subscription Concept is abandonded for now (Alex)
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
*/
		
		// add edit commands
		if ($this->getEnableEdit())
		{
			$this->addBlockCommand(
				$ilCtrl->getLinkTargetByClass("ilnewsitemgui", "editNews"),
				$lng->txt("edit"));

			$ilCtrl->setParameter($this, "add_mode", "block");
			$this->addBlockCommand(
				$ilCtrl->getLinkTargetByClass("ilnewsitemgui", "createNewsItem"),
				$lng->txt("add"));
			$ilCtrl->setParameter($this, "add_mode", "");
		}

		if ($this->getProperty("settings") == true)
		{
			$this->addBlockCommand(
				$ilCtrl->getLinkTarget($this, "editSettings"),
				$lng->txt("settings"));
		}
		
		// do not display hidden repository news blocks for users
		// who do not have write permission
		if (!$this->getEnableEdit() && $this->getRepositoryMode() &&
			ilBlockSetting::_lookup($this->getBlockType(), "hide_news_block",
			0, $this->block_id))
		{
			return "";
		}
		
		// do not display empty news blocks for users
		// who do not have write permission
		if (count($this->getData()) == 0 && !$this->getEnableEdit() &&
			$this->getRepositoryMode())
		{
			return "";
		}

		return parent::getHTML();
	}

	/**
	* Handles show/hide notification view and removes notifications if hidden.
	*/
	function handleView()
	{
		global $ilUser;
		
		include_once("Services/Block/classes/class.ilBlockSetting.php");
		$this->view = ilBlockSetting::_lookup($this->getBlockType(), "view",
			$ilUser->getId(), $this->block_id);

		// check whether notices and messages exist
		$got_notices = $got_messages = false;
		foreach($this->data as $row)
		{
			if ($row["priority"] == 0) $got_notices = true;
			if ($row["priority"] == 1) $got_messages = true;
		}
		$this->show_view_selection = false;

		if ($got_notices && $got_messages)
		{
			$this->show_view_selection = true;
		}
		else if ($got_notices)
		{
			$this->view = "";
		}
		
		// remove notifications if hidden
/*
		if (($this->view == "hide_notifications") && $this->show_view_selection)
		{
			$rset = array();
			foreach($this->data as $k => $row)
			{
				if ($row["priority"] == 1)
				{
					$rset[$k] = $row;
				}
			}
			$this->data = $rset;
		}
*/
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
			//$this->tpl->setVariable("VAL_CONTENT", $news["content"]);
			$this->tpl->setVariable("VAL_CREATION_DATE",
				ilFormat::formatDate($news["creation_date"], "datetime", true));
			$this->tpl->parseCurrentBlock();
		}
		
		// notification
		if ($news["priority"] == 0)
		{
/*
			$this->tpl->setCurrentBlock("notification");
			$this->tpl->setVariable("CHAR_NOT", $lng->txt("news_first_letter_of_word_notification"));
			$this->tpl->parseCurrentBlock();
*/
		}

		
		// title image type
		if ($news["ref_id"] > 0)
		{
			if ($news["agg_ref_id"] > 0)
			{
				$obj_id = ilObject::_lookupObjId($news["agg_ref_id"]);
				$type = ilObject::_lookupType($obj_id);
				$context_ref = $news["agg_ref_id"];
			}
			else
			{
				$obj_id = $news["context_obj_id"];
				$type = $news["context_obj_type"];
				$context_ref = $news["ref_id"];
			}
			
			$lang_type = in_array($type, array("sahs", "lm", "dbk", "htlm"))
				? "lres"
				: "obj_".$type;

			$this->tpl->setCurrentBlock("news_context");
			$this->tpl->setVariable("TYPE", $lng->txt($lang_type));
			$this->tpl->setVariable("IMG_TYPE",
				ilObject::_getIcon($obj_id, "tiny", $type));
			$this->tpl->setVariable("TITLE", ilObject::_lookupTitle($obj_id));
			if ($news["user_read"] > 0)
			{
				$this->tpl->setVariable("TITLE_CLASS", 'class="light"');
			}
			
			$this->tpl->parseCurrentBlock();
			$ilCtrl->setParameter($this, "news_context", $context_ref);
		}
		else
		{
			$ilCtrl->setParameter($this, "news_context", "");
		}

		// title
		$this->tpl->setVariable("VAL_TITLE", ilNewsItem::determineNewsTitle
			($news["context_obj_type"], $news["title"], $news["content_is_lang_var"],
			$news["agg_ref_id"], $news["aggregation"]));
		
		
		if ($news["user_read"] > 0)
		{
			$this->tpl->setVariable("A_CLASS", 'class="light"');
		}
		
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
		global $lng, $ilCtrl, $ilUser;
		
		$news_set = new ilSetting("news");
		$enable_internal_rss = $news_set->get("enable_rss_for_internal");

		include_once("./Services/News/classes/class.ilNewsItem.php");
		$news = new ilNewsItem($_GET["news_id"]);
		
		$tpl = new ilTemplate("tpl.show_news.html", true, true, "Services/News");

		// get current item in data set
		$previous = $next = "";
		reset($this->data);
		$c = current($this->data);
		$curr_cnt = 1;

		while($c["id"] > 0 &&
			 $c["id"] != $_GET["news_id"])
		{
			$previous = $c;
			$c = next($this->data);
			$curr_cnt++;
		}
		
		// collect news items to show
		$news_list = array();
		if (is_array($c["aggregation"]))	// we have an aggregation
		{
			//$agg_obj_id = ilObject::_lookupObjId($c["agg_ref_id"]);
			//$agg_obj_type = ilObject::_lookupType($agg_obj_id);
			//$agg_obj_title = ilObject::_lookupObjId($agg_obj_id);
			$news_list[] = array("ref_id" => $c["agg_ref_id"],
				"agg_ref_id" => $c["agg_ref_id"],
				"aggregation" => $c["aggregation"],
				"user_id" => "",
				"content_type" => "text",
				"mob_id" => 0,
				"visibility" => "",
				"content" => "",
				"content_long" => "",
				"update_date" => $news->getUpdateDate(),
				"creation_date" => "",
				"content_is_lang_var" => false,
				"loc_context" => $_GET["news_context"],
				"context_obj_type" => $news->getContextObjType(),
				"title" => "");

			foreach($c["aggregation"] as $c_item)
			{
				ilNewsItem::_setRead($ilUser->getId(), $c_item["id"]);
				$c_item["loc_context"] = $c_item["ref_id"];
				$c_item["loc_stop"] = $_GET["news_context"];
				$news_list[] = $c_item;
			}
		}
		else								// no aggregation, simple news item
		{
			$news_list[] = array("ref_id" => $_GET["news_context"],
				"user_id" => $news->getUserId(),
				"content_type" => $news->getContentType(),
				"mob_id" => $news->getMobId(),
				"visibility" => $news->getVisibility(),
				"priority" => $news->getPriority(),
				"content" => $news->getContent(),
				"content_long" => $news->getContentLong(),
				"update_date" => $news->getUpdateDate(),
				"creation_date" => $news->getCreationDate(),
				"context_sub_obj_type" => $news->getContextSubObjType(),
				"context_sub_obj_id" => $news->getContextSubObjId(),
				"content_is_lang_var" => $news->getContentIsLangVar(),
				"loc_context" => $_GET["news_context"],
				"title" => $news->getTitle());
			ilNewsItem::_setRead($ilUser->getId(), $_GET["news_id"]);
		}
			
		foreach ($news_list as $item)
		{
			// user
			if ($item["user_id"] > 0)
			{
				$tpl->setCurrentBlock("user_info");
				$user_obj = new ilObjUser($item["user_id"]);
				$tpl->setVariable("VAL_AUTHOR", $user_obj->getLogin());
				$tpl->setVariable("TXT_AUTHOR", $lng->txt("author"));
				$tpl->parseCurrentBlock();
			}
			
			// media player
			if ($item["content_type"] == NEWS_AUDIO &&
				$item["mob_id"] > 0 && ilObject::_exists($item["mob_id"]))
			{
				include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
				include_once("./Services/MediaObjects/classes/class.ilMediaPlayerGUI.php");
				$mob = new ilObjMediaObject($item["mob_id"]);
				$med = $mob->getMediaItem("Standard");
				$mpl = new ilMediaPlayerGUI();
				$mpl->setFile(ilObjMediaObject::_getDirectory($mob->getId())."/".
					$med->getLocation());
				$tpl->setCurrentBlock("player");
				$tpl->setVariable("PLAYER",
					$mpl->getMp3PlayerHtml());
				$tpl->parseCurrentBlock();
			}
			
			// access
			if ($enable_internal_rss && $item["visibility"] != "")
			{
				$tpl->setCurrentBlock("access");
				$tpl->setVariable("TXT_ACCESS", $lng->txt("news_news_item_visibility"));
				if ($item["visibility"] == NEWS_PUBLIC ||
					($item["priority"] == 0 &&
					ilBlockSetting::_lookup("news", "public_notifications",
					0, $obj_id)))
				{
					$tpl->setVariable("VAL_ACCESS", $lng->txt("news_visibility_public"));
				}
				else
				{
					$tpl->setVariable("VAL_ACCESS", $lng->txt("news_visibility_users"));
				}
				$tpl->parseCurrentBlock();
			}
	
			// content
			if (trim($item["content"]) != "")		// content
			{
				$tpl->setCurrentBlock("content");
				$tpl->setVariable("VAL_CONTENT", ilUtil::makeClickable($item["content"]));
				$tpl->parseCurrentBlock();
			}
			if (trim($item["content_long"]) != "")	// long content
			{
				$tpl->setCurrentBlock("long");
				$tpl->setVariable("VAL_LONG_CONTENT", ilUtil::makeClickable($item["content_long"]));
				$tpl->parseCurrentBlock();
			}
			if ($item["update_date"] != $item["creation_date"])		// update date
			{
				$tpl->setCurrentBlock("ni_update");
				$tpl->setVariable("TXT_LAST_UPDATE", $lng->txt("last_update"));
				$tpl->setVariable("VAL_LAST_UPDATE",
					ilFormat::formatDate($item["update_date"], "datetime", true));
				$tpl->parseCurrentBlock();
			}
			
			// creation date
			if ($item["creation_date"] != "")
			{
				$tpl->setCurrentBlock("ni_update");
				$tpl->setVariable("VAL_CREATION_DATE",
					ilFormat::formatDate($item["creation_date"], "datetime", true));
				$tpl->setVariable("TXT_CREATED", $lng->txt("created"));
				$tpl->parseCurrentBlock();
			}

						
			// context / title
			if ($_GET["news_context"] > 0)
			{
				//$obj_id = ilObject::_lookupObjId($_GET["news_context"]);
				$obj_id = ilObject::_lookupObjId($item["ref_id"]);
				$obj_type = ilObject::_lookupType($obj_id);
				$obj_title = ilObject::_lookupTitle($obj_id);
				
				// forum hack, not nice
				$add = "";
				if ($obj_type == "frm" && $item["context_sub_obj_type"] == "pos"
					&& $item["context_sub_obj_id"] > 0)
				{
					include_once("./Modules/Forum/classes/class.ilObjForumAccess.php");
					$pos = $item["context_sub_obj_id"];
					$thread = ilObjForumAccess::_getThreadForPosting($pos);
					if ($thread > 0)
					{
						$add = "_".$thread."_".$pos;
					}
				}
				//$url_target = "./goto.php?client_id=".rawurlencode(CLIENT_ID)."&target=".
				//	$obj_type."_".$_GET["news_context"].$add;
				$url_target = "./goto.php?client_id=".rawurlencode(CLIENT_ID)."&target=".
					$obj_type."_".$item["ref_id"].$add;
	
				
				$tpl->setCurrentBlock("context");
				if ($item["loc_context"] != "")
				{
					$cont_loc = new ilLocatorGUI();
					$cont_loc->addContextItems($item["loc_context"], true,
						$item["loc_stop"]);
					$tpl->setVariable("CONTEXT_LOCATOR",
						$cont_loc->getHTML());
				}
				
//var_dump($item);
				if ($item["no_context_title"] !== true)
				{
					$tpl->setVariable("HREF_CONTEXT_TITLE", $url_target);
					$tpl->setVariable("CONTEXT_TITLE", $obj_title);
					$tpl->setVariable("IMG_CONTEXT_TITLE",
						ilObject::_getIcon($obj_id, "big", $obj_type));
				}
				$tpl->parseCurrentBlock();
	
				$tpl->setVariable("HREF_TITLE", $url_target);
			}
			
			// title
			//if ($item["content_is_lang_var"])
			//{
				$tpl->setVariable("VAL_TITLE",
					ilNewsItem::determineNewsTitle($item["context_obj_type"],
					$item["title"], $item["content_is_lang_var"], $item["agg_ref_id"],
					$item["aggregation"]));
			//}
			//else
			//{
			//	$tpl->setVariable("VAL_TITLE", $item["title"]);			// title
			//}
			
			$row_css = ($row_css != "tblrow1")
				? "tblrow1"
				: "tblrow2";
			
			$tpl->setCurrentBlock("item");
			$tpl->setVariable("ITEM_ROW_CSS", $row_css);
			$tpl->parseCurrentBlock();
		}
		
		include_once("./Services/PersonalDesktop/classes/class.ilPDContentBlockGUI.php");
		$content_block = new ilPDContentBlockGUI();
		$content_block->setContent($tpl->get());
		if ($this->getProperty("title") != "")
		{
			$content_block->setTitle($this->getProperty("title"));
		}
		else
		{
			$content_block->setTitle($lng->txt("news_internal_news"));
		}
		//$content_block->setColSpan(2);
		$content_block->setImage(ilUtil::getImagePath("icon_news.gif"));
		$this->addCloseCommand($content_block);

		// previous
		if ($previous != "")
		{
			if ($previous["ref_id"] > 0)
			{
				$ilCtrl->setParameter($this, "news_context", $previous["ref_id"]);
			}
			$ilCtrl->setParameter($this, "news_id", $previous["id"]);
			$content_block->addFooterLink($lng->txt("previous"),
				$ilCtrl->getLinkTarget($this, "showNews"), "", "", true);
			$ilCtrl->setParameter($this, "news_context", "");
		}
		
		// next
		if ($c = next($this->data))
		{
			if ($c["ref_id"] > 0)
			{
				$ilCtrl->setParameter($this, "news_context", $c["ref_id"]);
			}
			$ilCtrl->setParameter($this, "news_id", $c["id"]);
			$content_block->addFooterLink($lng->txt("next"),
				$ilCtrl->getLinkTarget($this, "showNews"), "", "", true);
		}
		$ilCtrl->setParameter($this, "news_context", "");
		$ilCtrl->setParameter($this, "news_id", "");
		$content_block->setCurrentItemNumber($curr_cnt);
		$content_block->setEnableNumInfo(true);
		$content_block->setData($this->getData());

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
	
	/**
	* block footer
	*/
	function fillFooter()
	{
		global $ilCtrl, $lng, $ilUser;

		parent::fillFooter();
		
		if ($this->show_view_selection)
		{
			$this->showViewFooter();
		}
	}

	/**
	* Show additional footer for show/hide notifications
	*/
	function showViewFooter()
	{
		global $ilUser, $lng, $ilCtrl;
		
		return;		// notifications always shown
		
		$this->clearFooterLinks();
		$this->addFooterLink("[".$lng->txt("news_first_letter_of_word_notification")."] ".
			$lng->txt("news_notifications").": ", "", "", "", false, true);
		if ($this->view == "hide_notifications")
		{
			$this->addFooterLink($lng->txt("show"),
				$ilCtrl->getLinkTarget($this,
					"showNotifications"),
				$ilCtrl->getLinkTarget($this,
					"showNotifications", "", true),
				"block_".$this->getBlockType()."_".$this->block_id
				);
			$this->addFooterLink($lng->txt("hide"));
		}
		else
		{
			$this->addFooterLink($lng->txt("show"));
			$this->addFooterLink($lng->txt("hide"),
				$ilCtrl->getLinkTarget($this,
					"hideNotifications"),
				$ilCtrl->getLinkTarget($this,
					"hideNotifications", "", true),
				"block_".$this->getBlockType()."_".$this->block_id
				);
		}

		$this->fillFooterLinks();
	}
	
	function showNotifications()
	{
		global $ilCtrl, $ilUser;
		
		include_once("Services/Block/classes/class.ilBlockSetting.php");
		$view = ilBlockSetting::_write($this->getBlockType(), "view", "",
			$ilUser->getId(), $this->block_id);

		// reload data
		$data = $this->getNewsData();
		$this->setData($data);
		$this->handleView();

		if ($ilCtrl->isAsynch())
		{
			echo $this->getHTML();
			exit;
		}
		else
		{
			$ilCtrl->returnToParent($this);
		}
	}
	
	function hideNotifications()
	{
		global $ilCtrl, $ilUser;

		include_once("Services/Block/classes/class.ilBlockSetting.php");
		$view = ilBlockSetting::_write($this->getBlockType(), "view", "hide_notifications",
			$ilUser->getId(), $this->block_id);

		// reload data
		$data = $this->getNewsData();
		$this->setData($data);
		$this->handleView();

		if ($ilCtrl->isAsynch())
		{
			echo $this->getHTML();
			exit;
		}
		else
		{
			$ilCtrl->returnToParent($this);
		}
	}
	
	/**
	* Show settings screen.
	*/
	function editSettings()
	{
		$this->initSettingsForm();
		return $this->settings_form->getHTML();
	}
	
	/**
	* Init setting form
	*/
	function initSettingsForm()
	{
		global $ilUser, $lng, $ilCtrl, $ilSetting;
		
		$news_set = new ilSetting("news");
		$enable_internal_rss = $news_set->get("enable_rss_for_internal");

		$public = ilBlockSetting::_lookup($this->getBlockType(), "public_notifications",
			0, $this->block_id);
		$public_feed = ilBlockSetting::_lookup($this->getBlockType(), "public_feed",
			0, $this->block_id);
		$hide_block = ilBlockSetting::_lookup($this->getBlockType(), "hide_news_block",
			0, $this->block_id);
		$hide_news_per_date = ilBlockSetting::_lookup($this->getBlockType(), "hide_news_per_date",
			0, $this->block_id);
		$hide_news_date = ilBlockSetting::_lookup($this->getBlockType(), "hide_news_date",
			0, $this->block_id);
		$hide_news_date = explode(" ", $hide_news_date);

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->settings_form = new ilPropertyFormGUI();
		$this->settings_form->setTitle($lng->txt("news_settings"));
		$this->settings_form->setTitleIcon(ilUtil::getImagePath("icon_news.gif"));
		
		// hide news block for learners
		if ($this->getProperty("hide_news_block_option"))
		{
			$ch = new ilCheckboxInputGUI($lng->txt("news_hide_news_block"),
				"hide_news_block");
			$ch->setInfo($lng->txt("news_hide_news_block_info"));
			$ch->setChecked($hide_block);
			$this->settings_form->addItem($ch);
			
			$hnpd = new ilCheckboxInputGUI($lng->txt("news_hide_news_per_date"),
				"hide_news_per_date");
			$hnpd->setInfo($lng->txt("news_hide_news_per_date_info"));
			$hnpd->setChecked($hide_news_per_date);
			
				$dt_prop = new ilDateTimeInputGUI($lng->txt("news_hide_news_date"),
					"hide_news_date");
				$dt_prop->setDate($hide_news_date[0]);
				$dt_prop->setTime($hide_news_date[1]);
				$dt_prop->setShowTime(true);
				//$dt_prop->setInfo($lng->txt("news_hide_news_date_info"));
				$hnpd->addSubItem($dt_prop);
				
			$this->settings_form->addItem($hnpd);
			
		}
		
		// default visibility
		if ($this->getProperty("default_visibility_option") &&
			$enable_internal_rss)
		{
			$default_visibility = ilBlockSetting::_lookup($this->getBlockType(), "default_visibility",
				0, $this->block_id);
			if ($default_visibility == "")
			{
				$default_visibility =
					ilNewsItem::_getDefaultVisibilityForRefId($_GET["ref_id"]);
			}

			// Default Visibility
			$radio_group = new ilRadioGroupInputGUI($lng->txt("news_default_visibility"), "default_visibility");
			$radio_option = new ilRadioOption($lng->txt("news_visibility_users"), "users");
			$radio_group->addOption($radio_option);
			$radio_option = new ilRadioOption($lng->txt("news_visibility_public"), "public");
			$radio_group->addOption($radio_option);
			$radio_group->setInfo($lng->txt("news_news_item_visibility_info"));
			$radio_group->setRequired(false);
			$radio_group->setValue($default_visibility);
			$this->settings_form->addItem($radio_group);
		}

		// public notifications
		if ($this->getProperty("public_notifications_option") &&
			$enable_internal_rss)
		{
			$ch = new ilCheckboxInputGUI($lng->txt("news_notifications_public"),
				"notifications_public");
			$ch->setInfo($lng->txt("news_notifications_public_info"));
			$ch->setChecked($public);
			$this->settings_form->addItem($ch);
		}

		// extra rss feed
		if ($enable_internal_rss)
		{
			$ch = new ilCheckboxInputGUI($lng->txt("news_public_feed"),
				"notifications_public_feed");
			$ch->setInfo($lng->txt("news_public_feed_info"));
			$ch->setChecked($public_feed);
			$this->settings_form->addItem($ch);
		}

		
		//$this->settings_form->addCheckboxProperty($lng->txt("news_public_feed"), "notifications_public_feed",
		//	"1", $public_feed, $lng->txt("news_public_feed_info"));
		//if ($this->getProperty("public_notifications_option"))
		//{
		//	$this->settings_form->addCheckboxProperty($lng->txt("news_notifications_public"), "notifications_public",
		//		"1", $public, $lng->txt("news_notifications_public_info"));
		//}
		$this->settings_form->addCommandButton("saveSettings", $lng->txt("save"));
		$this->settings_form->addCommandButton("cancelSettings", $lng->txt("cancel"));
		$this->settings_form->setFormAction($ilCtrl->getFormaction($this));
	}
	
	/**
	* Cancel settings.
	*/
	function cancelSettings()
	{
		global $ilCtrl;
		
		$ilCtrl->returnToParent($this);
	}
	
	/**
	* Save settings.
	*/
	function saveSettings()
	{
		global $ilCtrl;
		
		$this->initSettingsForm();
		
		if ($this->settings_form->checkInput())
		{
			$news_set = new ilSetting("news");
			$enable_internal_rss = $news_set->get("enable_rss_for_internal");
			
			if ($enable_internal_rss)
			{
				ilBlockSetting::_write($this->getBlockType(), "public_notifications", $_POST["notifications_public"],
					0, $this->block_id);
				ilBlockSetting::_write($this->getBlockType(), "public_feed", $_POST["notifications_public_feed"],
					0, $this->block_id);
				ilBlockSetting::_write($this->getBlockType(), "default_visibility", $_POST["default_visibility"],
					0, $this->block_id);
			}
			
			if ($this->getProperty("hide_news_block_option"))
			{
				ilBlockSetting::_write($this->getBlockType(), "hide_news_block", $_POST["hide_news_block"],
					0, $this->block_id);
				ilBlockSetting::_write($this->getBlockType(), "hide_news_per_date", $_POST["hide_news_per_date"],
					0, $this->block_id);
//echo "-".$this->getBlockType()."-".$this->block_id."-"; // news obj_id
				ilBlockSetting::_write($this->getBlockType(), "hide_news_date",
					$_POST["hide_news_date"]["date"]." ".$_POST["hide_news_date"]["time"],
					0, $this->block_id);
			}
				
			$ilCtrl->returnToParent($this);
		}
		else
		{
			$this->settings_form->setValuesByPost();
			return $this->settings_form->getHtml();
		}
	}

	/**
	* Show feed URL.
	*/
	function showFeedUrl()
	{
		global $lng, $ilCtrl, $ilUser;
		
		include_once("./Services/News/classes/class.ilNewsItem.php");
		
		$title = ilObject::_lookupTitle($this->block_id);
		
		$tpl = new ilTemplate("tpl.show_feed_url.html", true, true, "Services/News");
		$tpl->setVariable("TXT_TITLE",
			sprintf($lng->txt("news_feed_url_for"), $title));
		$tpl->setVariable("TXT_INFO", $lng->txt("news_get_feed_info"));
		$tpl->setVariable("TXT_FEED_URL", $lng->txt("news_feed_url"));
		$tpl->setVariable("VAL_FEED_URL",
			ILIAS_HTTP_PATH."/feed.php?client_id=".rawurlencode(CLIENT_ID)."&user_id=".$ilUser->getId().
				"&obj_id=".$this->block_id.
				"&hash=".ilObjUser::_lookupFeedHash($ilUser->getId(), true));
		$tpl->setVariable("VAL_FEED_URL_TXT",
			ILIAS_HTTP_PATH."/feed.php?client_id=".rawurlencode(CLIENT_ID)."&<br />user_id=".$ilUser->getId().
				"&obj_id=".$this->block_id.
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

	function addCloseCommand($a_content_block)
	{
		global $lng, $ilCtrl;
		
		$a_content_block->addHeaderCommand($ilCtrl->getParentReturn($this),
			$lng->txt("close"), true);
	}
}

?>
