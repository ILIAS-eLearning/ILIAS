<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

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
	/**
	 * @var ilHelpGUI
	 */
	protected $help;

	/**
	 * @var ilSetting
	 */
	protected $settings;

	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;

	static $block_type = "news";
	static $st_data;
	
	/**
	* Constructor
	*/
	function __construct()
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->lng = $DIC->language();
		$this->user = $DIC->user();
		$this->help = $DIC["ilHelp"];
		$this->access = $DIC->access();
		$this->settings = $DIC->settings();
		$this->tabs = $DIC->tabs();
		$ilCtrl = $DIC->ctrl();
		$lng = $DIC->language();
		$ilUser = $DIC->user();
		$ilHelp = $DIC["ilHelp"];

		parent::__construct();
		
		$lng->loadLanguageModule("news");
		$ilHelp->addHelpSection("news_block");
		
		include_once("./Services/News/classes/class.ilNewsItem.php");
		$this->setBlockId($ilCtrl->getContextObjId());
		$this->setLimit(5);
		$this->setAvailableDetailLevels(3);
		$this->setEnableNumInfo(true);
		
		$this->dynamic = false;
		include_once("./Services/News/classes/class.ilNewsCache.php");
		$this->acache = new ilNewsCache();
		$cres = unserialize($this->acache->getEntry($ilUser->getId().":".$_GET["ref_id"]));
		$this->cache_hit = false;

		if ($this->acache->getLastAccessStatus() == "hit" && is_array($cres))
		{
			self::$st_data = ilNewsItem::prepareNewsDataFromCache($cres);
			$this->cache_hit = true;
		}
		if ($this->getDynamic() && !$this->cache_hit)
		{
			$this->dynamic = true;
			$data = array();
		}
		else if ($this->getCurrentDetailLevel() > 0)
		{
				if (!empty(self::$st_data))
				{
					$data = self::$st_data;
				}
				else
				{
					$data = $this->getNewsData();
					self::$st_data = $data;
				}
		}
		else
		{
			$data = array();
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
		$ilCtrl = $this->ctrl;
		$ilUser = $this->user;

		include_once("./Services/News/classes/class.ilNewsCache.php");
		$this->acache = new ilNewsCache();
/*		$cres = $this->acache->getEntry($ilUser->getId().":".$_GET["ref_id"]);
		if ($this->acache->getLastAccessStatus() == "hit" && false)
		{
			$news_data = unserialize($cres);
		}
		else
		{*/
			$news_item = new ilNewsItem();
			$news_item->setContextObjId($ilCtrl->getContextObjId());
			$news_item->setContextObjType($ilCtrl->getContextObjType());
			
			// workaround, better: reduce constructor and introduce
			//$prevent_aggregation = $this->getProperty("prevent_aggregation");
			$prevent_aggregation = true;
			if ($ilCtrl->getContextObjType() != "frm")
			{
				$forum_grouping = true;
			}
			else
			{
				$forum_grouping = false;
			}
	
			
			$news_data = $news_item->getNewsForRefId($_GET["ref_id"], false, false, 0,
				$prevent_aggregation, $forum_grouping);

			$this->acache->storeEntry($ilUser->getId().":".$_GET["ref_id"],
				serialize($news_data));

//		}
//var_dump($news_data);
		return $news_data;
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
		global $DIC;

		$ilCtrl = $DIC->ctrl();
		
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
	function executeCommand()
	{
		$ilCtrl = $this->ctrl;

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
		if ($this->dynamic)
		{
			$this->setDataSection($this->getDynamicReload());
		}
		else if ($this->getCurrentDetailLevel() > 1 && count($this->getData()) > 0)
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
		$ilCtrl = $this->ctrl;
		$lng = $this->lng;
		$ilUser = $this->user;
		
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
				include_once("./Services/News/classes/class.ilRSSButtonGUI.php");
				$this->addBlockCommand(
					ILIAS_HTTP_PATH."/feed.php?client_id=".rawurlencode(CLIENT_ID)."&".
						"ref_id=".$_GET["ref_id"],
						$lng->txt("news_feed_url"), "", "", true, false, ilRSSButtonGUI::get(ilRSSButtonGUI::ICON_RSS));

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
			$this->getRepositoryMode() && !$this->dynamic
			&& (!$news_set->get("enable_rss_for_internal") ||
				!ilBlockSetting::_lookup($this->getBlockType(), "public_feed",
				0, $this->block_id)))
		{
			return "";
		}

		$en = "";
		if ($ilUser->getPref("il_feed_js") == "n")
		{
//			$en = getJSEnabler();
		}

		return parent::getHTML().$en;
	}

	/**
	* Handles show/hide notification view and removes notifications if hidden.
	*/
	function handleView()
	{
		$ilUser = $this->user;
		
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
		$ilCtrl = $this->ctrl;
		$lng = $this->lng;

		if ($this->getCurrentDetailLevel() > 2)
		{
			$this->tpl->setCurrentBlock("long");
			//$this->tpl->setVariable("VAL_CONTENT", $news["content"]);
				$this->tpl->setVariable("VAL_CREATION_DATE",
					ilDatePresentation::formatDate(new ilDateTime($news["creation_date"],IL_CAL_DATETIME)));
			$this->tpl->parseCurrentBlock();
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
			
			$lang_type = in_array($type, array("sahs", "lm", "htlm"))
				? "lres"
				: "obj_".$type;

			$this->tpl->setCurrentBlock("news_context");
			$this->tpl->setVariable("TYPE", $lng->txt($lang_type));
			$this->tpl->setVariable("IMG_TYPE",
				ilObject::_getIcon($obj_id, "tiny", $type));
			$this->tpl->setVariable("TITLE",
				ilUtil::shortenWords(ilObject::_lookupTitle($obj_id)));
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
		$this->tpl->setVariable("VAL_TITLE",
			ilUtil::shortenWords(ilNewsItem::determineNewsTitle
			($news["context_obj_type"], $news["title"], $news["content_is_lang_var"],
			$news["agg_ref_id"], $news["aggregation"])));
		
		
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
		$ilUser = $this->user;
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;
				
		return '<div class="small">'.((int) count($this->getData()))." ".$lng->txt("news_news_items")."</div>";
	}

	/**
	* show news
	*/
	function showNews()
	{
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;
		$ilUser = $this->user;
		$ilAccess = $this->access;
		
		// workaround for dynamic mode (if cache is disabled, showNews has no data)
		if (empty(self::$st_data))
		{
			$this->setData($this->getNewsData());
		}
		
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
			$news_list[] = array(
				"id" => $news->getId(),
				"ref_id" => $_GET["news_context"],
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
				"context_obj_type" => $news->getContextObjType(),
				"context_sub_obj_id" => $news->getContextSubObjId(),
				"content_is_lang_var" => $news->getContentIsLangVar(),
				"content_text_is_lang_var" => $news->getContentTextIsLangVar(),
				"loc_context" => $_GET["news_context"],
                "title" => $news->getTitle());
			ilNewsItem::_setRead($ilUser->getId(), $_GET["news_id"]);
		}

		$row_css = "";
		$cache_deleted = false;
		foreach ($news_list as $item)
		{
			$row_css = ($row_css != "tblrow1")
					? "tblrow1"
					: "tblrow2";

			if ($item["ref_id"] > 0 && !$ilAccess->checkAccess("read", "", $item["ref_id"]))
			{
				$tpl->setCurrentBlock("content");
				$tpl->setVariable("VAL_CONTENT", $lng->txt("news_sorry_not_accessible_anymore"));
				$tpl->parseCurrentBlock();
				$tpl->setCurrentBlock("item");
				$tpl->setVariable("ITEM_ROW_CSS", $row_css);
				$tpl->parseCurrentBlock();
				if (!$cache_deleted)
				{
					$this->acache->deleteEntry($ilUser->getId() . ":" . $_GET["ref_id"]);
					$cache_deleted = true;
				}
				continue;
			}

			// user
			if ($item["user_id"] > 0 && ilObject::_exists($item["user_id"]))
			{
				// get login
				if (ilObjUser::_exists($item["user_id"])) 
				{
					$user = new ilObjUser($item["user_id"]);
					$displayname = $user->getLogin();
				} else 
				{
					// this should actually not happen, since news entries 
					// should be deleted when the user is going to be removed
					$displayname = "&lt;". strtolower($lng->txt("deleted")) ."&gt;";
				}
			
				$tpl->setCurrentBlock("user_info");
				$tpl->setVariable("VAL_AUTHOR", $displayname);
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
				$mpl = new ilMediaPlayerGUI("news_pl_".$item["mob_id"]);
				if (strcasecmp("Reference", $med->getLocationType()) == 0)
					$mpl->setFile($med->getLocation());
				else
					$mpl->setFile(ilObjMediaObject::_getURL($mob->getId())."/".$med->getLocation());
				$mpl->setDisplayHeight($med->getHeight());
				$tpl->setCurrentBlock("player");
				$tpl->setVariable("PLAYER",
					$mpl->getMp3PlayerHtml());
				$tpl->parseCurrentBlock();
			}
			
			// access
			if ($enable_internal_rss && $item["visibility"] != "")
			{
				$obj_id = ilObject::_lookupObjId($item["ref_id"]);
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
			include_once("./Services/News/classes/class.ilNewsRendererFactory.php");
			$renderer = ilNewsRendererFactory::getRenderer($item["context_obj_type"]);
			if (trim($item["content"]) != "")		// content
			{
				$it = new ilNewsItem($item["id"]);
				$renderer->setNewsItem($it, $item["ref_id"]);
				$tpl->setCurrentBlock("content");
				$tpl->setVariable("VAL_CONTENT", $renderer->getDetailContent());
				$tpl->parseCurrentBlock();
			}
			if (trim($item["content_long"]) != "")	// long content
			{
				$tpl->setCurrentBlock("long");
				$tpl->setVariable("VAL_LONG_CONTENT", $this->makeClickable($item["content_long"]));
				$tpl->parseCurrentBlock();
			}
			if ($item["update_date"] != $item["creation_date"])		// update date
			{
				$tpl->setCurrentBlock("ni_update");
				$tpl->setVariable("TXT_LAST_UPDATE", $lng->txt("last_update"));
				$tpl->setVariable("VAL_LAST_UPDATE",
					ilDatePresentation::formatDate(new ilDateTime($item["update_date"],IL_CAL_DATETIME)));
				$tpl->parseCurrentBlock();
			}
			
			// creation date
			if ($item["creation_date"] != "")
			{
				$tpl->setCurrentBlock("ni_update");
					$tpl->setVariable("VAL_CREATION_DATE",
						ilDatePresentation::formatDate(new ilDateTime($item["creation_date"],IL_CAL_DATETIME)));
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
				
				// file hack, not nice
				if ($obj_type == "file")
				{
					$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $item["ref_id"]);
					$url = $ilCtrl->getLinkTargetByClass("ilrepositorygui", "sendfile");				
					$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
					
					include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";
					$button = ilLinkButton::getInstance();
					$button->setUrl($url);
					$button->setCaption("download");
					
					$tpl->setCurrentBlock("download");					
					$tpl->setVariable("BUTTON_DOWNLOAD", $button->render());					
					$tpl->parseCurrentBlock();
				}
				
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

				// wiki hack, not nice
				if ($obj_type == "wiki" && $item["context_sub_obj_type"] == "wpg"
					&& $item["context_sub_obj_id"] > 0)
				{
					include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
					$wptitle = ilWikiPage::lookupTitle($item["context_sub_obj_id"]);
					if ($wptitle != "")
					{
						$add = "_".ilWikiUtil::makeUrlTitle($wptitle);
					}
				}

				$url_target = "./goto.php?client_id=".rawurlencode(CLIENT_ID)."&target=".
					$obj_type."_".$item["ref_id"].$add;

				// lm page hack, not nice
				if (in_array($obj_type, array("lm")) && $item["context_sub_obj_type"] == "pg"
					&& $item["context_sub_obj_id"] > 0)
				{
					$url_target = "./goto.php?client_id=".rawurlencode(CLIENT_ID)."&target=".
						"pg_".$item["context_sub_obj_id"]."_".$item["ref_id"];
				}
				
				// blog posting hack, not nice
				if ($obj_type == "blog" && $item["context_sub_obj_type"] == "blp"
					&& $item["context_sub_obj_id"] > 0)
				{
					$url_target = "./goto.php?client_id=".rawurlencode(CLIENT_ID)."&target=".
						"blog_".$item["ref_id"]."_".$item["context_sub_obj_id"];
				}
	
				$context_opened = false;				
				if ($item["loc_context"] != null && $item["loc_context"] != $item["loc_stop"])
				{

					$tpl->setCurrentBlock("context");
					$context_opened = true;
					$cont_loc = new ilLocatorGUI();
					$cont_loc->addContextItems($item["loc_context"], true, $item["loc_stop"]);
					$tpl->setVariable("CONTEXT_LOCATOR", $cont_loc->getHTML());
				}
				
//var_dump($item);
				if ($item["no_context_title"] !== true)
				{
					if (!$context_opened) 
					{
						$tpl->setCurrentBlock("context");						
					}	
					$tpl->setVariable("HREF_CONTEXT_TITLE", $url_target);
					$tpl->setVariable("CONTEXT_TITLE", $obj_title);
					$tpl->setVariable("IMG_CONTEXT_TITLE", ilObject::_getIcon($obj_id, "big", $obj_type));
				}
				if ($context_opened)
				{
					$tpl->parseCurrentBlock();
				}
	
				$tpl->setVariable("HREF_TITLE", $url_target);
			}
			
			// title
			$tpl->setVariable("VAL_TITLE",
				ilNewsItem::determineNewsTitle($item["context_obj_type"],
				$item["title"], $item["content_is_lang_var"], $item["agg_ref_id"],
				$item["aggregation"]));
			

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
	 * Make clickable
	 *
	 * @param
	 * @return
	 */
	function makeClickable($a_str)
	{
		// this fixes bug 8744. We assume that strings that contain < and >
		// already contain html, we do not handle these
		if (is_int(strpos($a_str, ">")) && is_int(strpos($a_str, "<")))
		{
			return $a_str;
		}
		
		return ilUtil::makeClickable($a_str);
	}
	
	
	/**
	* Unsubscribe current user from news
	*/
	function unsubscribeNews()
	{
		$ilUser = $this->user;
		$ilCtrl = $this->ctrl;
		
		include_once("./Services/News/classes/class.ilNewsSubscription.php");
		ilNewsSubscription::_unsubscribe($_GET["ref_id"], $ilUser->getId());
		$ilCtrl->returnToParent($this);
	}

	/**
	* Subscribe current user from news
	*/
	function subscribeNews()
	{
		$ilUser = $this->user;
		$ilCtrl = $this->ctrl;

		include_once("./Services/News/classes/class.ilNewsSubscription.php");
		ilNewsSubscription::_subscribe($_GET["ref_id"], $ilUser->getId());
		$ilCtrl->returnToParent($this);
	}
	
	/**
	* block footer
	*/
	function fillFooter()
	{
		$ilCtrl = $this->ctrl;
		$lng = $this->lng;
		$ilUser = $this->user;

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
		$ilUser = $this->user;
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;
		
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
		$ilCtrl = $this->ctrl;
		$ilUser = $this->user;
		
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
		$ilCtrl = $this->ctrl;
		$ilUser = $this->user;

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
		$ilUser = $this->user;
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;
		$ilSetting = $this->settings;
		$ilTabs = $this->tabs;

		$ilTabs->clearTargets();

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

		if ($hide_news_date != "")
		{
			$hide_news_date = explode(" ", $hide_news_date);
		}

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->settings_form = new ilPropertyFormGUI();
		$this->settings_form->setTitle($lng->txt("news_settings"));
		
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
				$dt_prop->setRequired(true);
				if ($hide_news_date != "")
				{
					$dt_prop->setDate(new ilDateTime($hide_news_date[0].' '.$hide_news_date[1],IL_CAL_DATETIME));
				}
				#$dt_prop->setDate($hide_news_date[0]);
				#$dt_prop->setTime($hide_news_date[1]);
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
		$ilCtrl = $this->ctrl;
		
		$ilCtrl->returnToParent($this);
	}
	
	/**
	* Save settings.
	*/
	function saveSettings()
	{
		$ilCtrl = $this->ctrl;
		$ilUser = $this->user;
		
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

				// hide date
				$hd = $this->settings_form->getItemByPostVar("hide_news_date");				
				$hide_date = $hd->getDate();
				if ($_POST["hide_news_per_date"] && $hide_date != null)
				{
					ilBlockSetting::_write($this->getBlockType(), "hide_news_date",
						$hide_date->get(IL_CAL_DATETIME),
						0, $this->block_id);
				}
				else
				{
					ilBlockSetting::_write($this->getBlockType(), "hide_news_date",
						"",
						0, $this->block_id);
				}
			}
			
			include_once("./Services/News/classes/class.ilNewsCache.php");
			$cache = new ilNewsCache();
			$cache->deleteEntry($ilUser->getId().":".$_GET["ref_id"]);

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
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;
		$ilUser = $this->user;
		
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
		$content_block->addHeaderCommand($ilCtrl->getParentReturn($this),
			$lng->txt("close"), true);

		return $content_block->getHTML();
	}

	function addCloseCommand($a_content_block)
	{
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;
		
		$a_content_block->addHeaderCommand($ilCtrl->getParentReturn($this),
			$lng->txt("close"), true);
	}
	
	function getDynamic()
	{
		$ilCtrl = $this->ctrl;
		$ilUser = $this->user;
		
		if ($ilCtrl->getCmd() == "hideNotifications" ||
			$ilCtrl->getCmd() == "showNotifications")
		{
			return false;
		}
		
		if ($ilCtrl->getCmdClass() != "ilcolumngui" && $ilCtrl->getCmd() != "enableJS")
		{
			$sess_feed_js = "";
			if (isset($_SESSION["il_feed_js"]))
			{
				$sess_feed_js = $_SESSION["il_feed_js"];
			}
			
			if ($sess_feed_js != "n" &&
				($ilUser->getPref("il_feed_js") != "n" || $sess_feed_js == "y"))
			{
				// do not get feed dynamically, if cache hit is given.
//				if (!$this->feed->checkCacheHit())
//				{
					return true;
//				}
			}
		}
		
		return false;
	}

	function getDynamicReload()
	{
		$ilCtrl = $this->ctrl;
		$lng = $this->lng;
		
		$ilCtrl->setParameterByClass("ilcolumngui", "block_id",
			"block_".$this->getBlockType()."_".$this->getBlockId());

		$rel_tpl = new ilTemplate("tpl.dynamic_reload.html", true, true, "Services/News");
		$rel_tpl->setVariable("TXT_LOADING", $lng->txt("news_loading_news"));
		$rel_tpl->setVariable("BLOCK_ID", "block_".$this->getBlockType()."_".$this->getBlockId());
		$rel_tpl->setVariable("TARGET", 
			$ilCtrl->getLinkTargetByClass("ilcolumngui", "updateBlock", "", true));
			
		// no JS
		$rel_tpl->setVariable("TXT_NEWS_CLICK_HERE", $lng->txt("news_no_js_click_here"));
		$rel_tpl->setVariable("TARGET_NO_JS",
			$ilCtrl->getLinkTargetByClass(strtolower(get_class($this)), "disableJS"));

		return $rel_tpl->get();
	}
	
	function getJSEnabler()
	{
		$ilCtrl = $this->ctrl;
		$lng = $this->lng;
		
		$ilCtrl->setParameterByClass("ilcolumngui", "block_id",
			"block_".$this->getBlockType()."_".$this->getBlockId());
//echo "hh";
		$rel_tpl = new ilTemplate("tpl.js_enabler.html", true, true, "Services/News");
		$rel_tpl->setVariable("BLOCK_ID", "block_".$this->getBlockType()."_".$this->getBlockId());
		$rel_tpl->setVariable("TARGET", 
			$ilCtrl->getLinkTargetByClass(strtolower(get_class($this)), "enableJS", true, "", false));
			
		return $rel_tpl->get();
	}
	
	
	function disableJS()
	{
		$ilCtrl = $this->ctrl;
		$ilUser = $this->user;

		$_SESSION["il_feed_js"] = "n";
		$ilUser->writePref("il_feed_js", "n");
$ilCtrl->returnToParent($this);
		//$ilCtrl->redirectByClass("ilpersonaldesktopgui", "show");
	}
	
	function enableJS()
	{
		$ilUser = $this->user;
//echo "enableJS";
		$_SESSION["il_feed_js"] = "y";
		$ilUser->writePref("il_feed_js", "y");
		echo $this->getHTML();
		exit;
	}

}

?>
