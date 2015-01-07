<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

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
	static $st_data;
	protected $acc_results = false;
	
	/**
	 * Constructor
	 */
	function ilPDNewsBlockGUI()
	{
		global $ilCtrl, $lng, $ilUser, $ilBench, $ilAccess, $ilCtrl;

		$ilBench->start("News", "ilPDNewsBlockGUI_Constructor");
		$news_set = new ilSetting("news");
		
		parent::ilBlockGUI();
		
		$lng->loadLanguageModule("news");
		include_once("./Services/News/classes/class.ilNewsItem.php");

		$this->setLimit(5);
		$this->setAvailableDetailLevels(3);

		$this->dynamic = false;

		// store current access check results
//		$this->acc_results = $ilAccess->getResults();
		
		// read access cache
//		$this->acc_cache_hit = $ilAccess->readCache(
//			((int) $news_set->get("acc_cache_mins")) * 60);

		include_once("./Services/News/classes/class.ilNewsCache.php");
		$this->acache = new ilNewsCache();
		$cres = $this->acache->getEntry($ilUser->getId().":0");
		$this->cache_hit = false;
		if ($this->acache->getLastAccessStatus() == "hit")
		{
			self::$st_data = unserialize($cres);
			$this->cache_hit = true;
		}

		if ($this->getDynamic() && !$this->cache_hit)
		{
			$this->dynamic = true;
			$data = array();
		}
		else if ($this->getCurrentDetailLevel() > 0)
		{
			// do not ask two times for the data (e.g. if user displays a 
			// single item on the personal desktop and the news block is 
			// displayed at the same time)
			if (empty(self::$st_data))
			{
				self::$st_data = $this->getNewsData();
				$data = self::$st_data;
			}
			else
			{
				$data = self::$st_data;
			}
		}
		else
		{
			$data = array();
		}
		
		$this->setTitle($lng->txt("news_internal_news"));
		$this->setRowTemplate("tpl.block_row_news_for_context.html", "Services/News");
		
		$this->setData($data);
		
		$this->handleView();
		
		// reset access check results
		$ilAccess->setResults($this->acc_results);
		
		$ilBench->stop("News", "ilPDNewsBlockGUI_Constructor");
	}
	
	/**
	* Get news for context
	*/
	function getNewsData()
	{
		global $ilUser, $ilAccess;

		include_once("./Services/News/classes/class.ilNewsCache.php");
		$this->acache = new ilNewsCache();
		
		$per = ilNewsItem::_lookupUserPDPeriod($ilUser->getId());
		$data = ilNewsItem::_getNewsItemsOfUser($ilUser->getId(), false,
			false, $per);
//		if (!$this->acc_cache_hit)
//		{
//			$ilAccess->storeCache();
//		}

		$this->acache->storeEntry($ilUser->getId().":0",
			serialize($data));

		return $data;
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
	* Is block used in repository object?
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
		
		$cmd = $_GET["cmd"];
		if($cmd == "post" && is_array($_POST["cmd"]))
		{
			$cmd = array_pop(array_keys($_POST["cmd"]));			
		}
		
		switch($cmd)
		{
			case "showNews":
			case "showFeedUrl":
			case "editSettings":
			case "changeFeedSettings":
				return IL_SCREEN_CENTER;
			
			default:
				return IL_SCREEN_SIDE;
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
		global $ilBench;
		
		if ($this->dynamic)
		{
			$this->setDataSection($this->getDynamicReload());
		}
		else if ($this->getCurrentDetailLevel() > 1 && count($this->getData()) > 0)
		{
			$ilBench->start("News", "ilPDNewsBlockGUI_fillDataSection");
			parent::fillDataSection();
			$ilBench->stop("News", "ilPDNewsBlockGUI_fillDataSection");
		}
		else
		{
			$this->setEnableNumInfo(false);
			if (count($this->getData()) == 0)
			{
				$this->setEnableDetailRow(false);
			}
			$this->setDataSection($this->getOverview());
		}
	}

	/**
	* Get bloch HTML code.
	*/
	function getHTML()
	{
		global $ilCtrl, $lng, $ilUser;
		
		// set footer info
		$this->setFooterInfo($lng->txt("news_block_information"), true);
		
		$news_set = new ilSetting("news");
		$enable_internal_rss = $news_set->get("enable_rss_for_internal");
		$allow_shorter_periods = $news_set->get("allow_shorter_periods");
		$allow_longer_periods = $news_set->get("allow_longer_periods");
		$enable_private_feed = $news_set->get("enable_private_feed");

		// subscribe/unsibscribe link
		include_once("./Services/News/classes/class.ilNewsSubscription.php");
		
		// show feed url
		if ($enable_internal_rss)
		{
			include_once("./Services/News/classes/class.ilRSSButtonGUI.php");
			$this->addBlockCommand(
				$ilCtrl->getLinkTarget($this, "showFeedUrl"),
				$lng->txt("news_get_feed_url"), "", "", true, false, ilRSSButtonGUI::get(ilRSSButtonGUI::ICON_RSS));
		}

		if ($allow_shorter_periods || $allow_longer_periods || $enable_private_feed)
		{
			$this->addBlockCommand(
				$ilCtrl->getLinkTarget($this, "editSettings"),
				$lng->txt("settings"));
		}
			
		$per = ilNewsItem::_lookupUserPDPeriod($ilUser->getId());

		if ($per > 0)
		{
			switch ($per)
			{
				case 2:
				case 3:
				case 5: $per_str = sprintf($lng->txt("news_period_x_days"), $per); break;
				case 7: $per_str = $lng->txt("news_period_1_week"); break;
				case 14: $per_str = sprintf($lng->txt("news_period_x_weeks"), 2); break;
				case 30: $per_str = $lng->txt("news_period_1_month"); break;
				case 60: $per_str = sprintf($lng->txt("news_period_x_months"), 2); break;
				case 120: $per_str = sprintf($lng->txt("news_period_x_months"), 4); break;
				case 180: $per_str = sprintf($lng->txt("news_period_x_months"), 6); break;
				case 366: $per_str = $lng->txt("news_period_1_year"); break;
			}
			if ($per_str != "")
			{
				$this->setTitle($this->getTitle().' <span style="font-weight:normal;">- '.$per_str."</span>");
			}
		}

		if ($this->getCurrentDetailLevel() == 0)
		{
			return "";
		}

		$en = "";
		if ($ilUser->getPref("il_feed_js") == "n")
		{
			$en = $this->getJSEnabler();
		}

		return ilBlockGUI::getHTML().$en;
	}
	

	/**
	* Show feed URL.
	*/
	function showFeedUrl()
	{
		global $lng, $ilCtrl, $ilUser, $ilSetting;

		$news_set = new ilSetting("news");
		
		
		include_once("./Services/News/classes/class.ilNewsItem.php");

		if ($news_set->get("enable_private_feed")) 
		{
			$tpl = new ilTemplate("tpl.show_priv_feed_url.html", true, true, "Services/News");				

			$tpl->setVariable("TXT_PRIV_TITLE", $lng->txt("news_get_priv_feed_title"));
			
			// #14365
			if($ilUser->_getFeedPass($_SESSION["AccountId"]))
			{
				$tpl->setVariable("TXT_PRIV_INFO", $lng->txt("news_get_priv_feed_info"));
				$tpl->setVariable("TXT_PRIV_FEED_URL", $lng->txt("news_feed_url"));			
				$tpl->setVariable("VAL_PRIV_FEED_URL",
					str_replace("://", "://" . $ilUser->getLogin() . ":-password-@", ILIAS_HTTP_PATH)."/privfeed.php?client_id=".rawurlencode(CLIENT_ID)."&user_id=".$ilUser->getId().
						"&hash=".ilObjUser::_lookupFeedHash($ilUser->getId(), true));
				$tpl->setVariable("VAL_PRIV_FEED_URL_TXT",
					str_replace("://", "://" . $ilUser->getLogin() . ":-password-@", ILIAS_HTTP_PATH)."/privfeed.php?client_id=".rawurlencode(CLIENT_ID)."&<br />user_id=".$ilUser->getId().
						"&hash=".ilObjUser::_lookupFeedHash($ilUser->getId(), true));
			}
			else
			{
				$tpl->setVariable("TXT_PRIV_INFO", $lng->txt("news_inactive_private_feed_info"));
				$tpl->setVariable("EDIT_SETTINGS_URL", $ilCtrl->getLinkTarget($this, "editSettings"));
				$tpl->setVariable("EDIT_SETTINGS_TXT", $lng->txt("news_edit_news_settings"));
			}
		}
		else
		{
			$tpl = new ilTemplate("tpl.show_feed_url.html", true, true, "Services/News");
		}
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
		$content_block->addHeaderCommand($ilCtrl->getParentReturn($this),
			$lng->txt("selected_items_back"));

		return $content_block->getHTML();
	}

	function addCloseCommand($a_content_block)
	{
		global $lng, $ilCtrl;
		
		$a_content_block->addHeaderCommand($ilCtrl->getParentReturn($this),
			$lng->txt("selected_items_back"));
	}

	
	/**
	* show news
	*/
	function showNews()
	{
		// workaround for dynamic mode (if cache is disabled, showNews has no data)
//		if (empty(self::$st_data))
//		{
//			$this->setData($this->getNewsData());
//		}

		return parent::showNews();
	}

	/**
	* Show settings screen.
	*/
	function editSettings(ilPropertyFormGUI $a_private_form = null)
	{
		global $ilUser, $lng, $ilCtrl, $ilSetting;
		
		$news_set = new ilSetting("news");
		$enable_internal_rss = $news_set->get("enable_rss_for_internal");
		$allow_shorter_periods = $news_set->get("allow_shorter_periods");
		$allow_longer_periods = $news_set->get("allow_longer_periods");
		$enable_private_feed = $news_set->get("enable_private_feed");
	
		if (!$a_private_form && ($allow_shorter_periods || $allow_longer_periods))
		{
			include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
			$form = new ilPropertyFormGUI();	
			$form->setFormAction($ilCtrl->getFormaction($this));			
			$form->setTitle($lng->txt("news_settings"));

			include_once("./Services/News/classes/class.ilNewsItem.php");
			$default_per = ilNewsItem::_lookupDefaultPDPeriod();
			$per = ilNewsItem::_lookupUserPDPeriod($ilUser->getId());

			$form->setTableWidth("100%");

			$per_opts = array(
				2 => "2 ".$lng->txt("days"),
				3 => "3 ".$lng->txt("days"),
				5 => "5 ".$lng->txt("days"),
				7 => "1 ".$lng->txt("week"),
				14 => "2 ".$lng->txt("weeks"),
				30 => "1 ".$lng->txt("month"),
				60 => "2 ".$lng->txt("months"),
				120 => "4 ".$lng->txt("months"),
				180 => "6 ".$lng->txt("months"),
				366 =>  "1 ".$lng->txt("year"));

			$unset = array();
			foreach($per_opts as $k => $opt)
			{
				if (!$allow_shorter_periods && ($k < $default_per)) $unset[$k] = $k;
				if (!$allow_longer_periods && ($k > $default_per)) $unset[$k] = $k;
			}
			foreach($unset as $k)
			{
				unset($per_opts[$k]);
			}

			$per_sel = new ilSelectInputGUI($lng->txt("news_pd_period"),
				"news_pd_period");
			//$per_sel->setInfo($lng->txt("news_pd_period_info"));
			$per_sel->setOptions($per_opts);
			$per_sel->setValue((int) $per);
			$form->addItem($per_sel);
		
			//$form->addCheckboxProperty($lng->txt("news_public_feed"), "notifications_public_feed",
			//	"1", $public_feed, $lng->txt("news_public_feed_info"));
			//if ($this->getProperty("public_notifications_option"))
			//{
			//	$form->addCheckboxProperty($lng->txt("news_notifications_public"), "notifications_public",
			//		"1", $public, $lng->txt("news_notifications_public_info"));
			//}
			$form->addCommandButton("saveSettings", $lng->txt("save"));
			$form->addCommandButton("cancelSettings", $lng->txt("cancel"));			
			
			$returnForm = $form->getHTML();
		}

		if ($enable_private_feed) 
		{
			if(!$a_private_form)
			{
				$a_private_form = $this->initPrivateSettingsForm();
			}
			$returnForm .= ($returnForm=="") 
				? $a_private_form->getHTML()
				: "<br>".$a_private_form->getHTML();
		}
		
		return $returnForm;
	}
	
	protected function initPrivateSettingsForm()
	{
		global $ilCtrl, $lng, $ilUser;
		
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$feed_form = new ilPropertyFormGUI();
		$feed_form->setFormAction($ilCtrl->getFormaction($this));
		$feed_form->setTitle($lng->txt("priv_feed_settings"));

		$feed_form->setTableWidth("100%");

		$enable_private_feed = new ilCheckboxInputGUI($lng->txt("news_enable_private_feed"), "enable_private_feed");
		$enable_private_feed->setChecked($ilUser->_getFeedPass($ilUser->getId()));
		$feed_form->addItem($enable_private_feed);		

		$passwd = new ilPasswordInputGUI($lng->txt("password"), "desired_password");
		$passwd->setRequired(true);
		$passwd->setInfo(ilUtil::getPasswordRequirementsInfo());
		$enable_private_feed->addSubItem($passwd);

		$feed_form->addCommandButton("changeFeedSettings", $lng->txt("save"));
		$feed_form->addCommandButton("cancelSettings", $lng->txt("cancel"));
		
		return $feed_form;
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
		global $ilCtrl, $ilUser;
		
		$news_set = new ilSetting("news");
		$enable_internal_rss = $news_set->get("enable_rss_for_internal");
		
		ilBlockSetting::_write($this->getBlockType(), "news_pd_period",
			$_POST["news_pd_period"],
			$ilUser->getId(), $this->block_id);
		
		include_once("./Services/News/classes/class.ilNewsCache.php");
		$cache = new ilNewsCache();
		$cache->deleteEntry($ilUser->getId().":0");
			
		$ilCtrl->returnToParent($this);
	}

	/**
	* change user password
	*/
	function changeFeedSettings()
	{
		global $ilCtrl, $lng, $ilUser;
		
		$form = $this->initPrivateSettingsForm();
		if($form->checkInput())
		{			
			// Deactivate private Feed - just delete the password
			if (!$form->getInput("enable_private_feed")) 
			{				
				$ilUser->_setFeedPass($ilUser->getId(), "");		
				ilUtil::sendSuccess($lng->txt("priv_feed_disabled"),true);				
				// $ilCtrl->returnToParent($this);
				$ilCtrl->redirect($this, "showFeedUrl");
			}
			else
			{					
				$passwd = $form->getInput("desired_password");				
				if (md5($passwd) == $ilUser->getPasswd())
				{
					$form->getItemByPostVar("desired_password")->setAlert($lng->txt("passwd_equals_ilpasswd"));
					ilUtil::sendFailure($lng->txt("form_input_not_valid"));
				}
				else
				{
					$ilUser->_setFeedPass($ilUser->getId(), $passwd);		
					ilUtil::sendSuccess($lng->txt("saved_successfully"),true);					
					// $ilCtrl->returnToParent($this);					
					$ilCtrl->redirect($this, "showFeedUrl");
				}
			}			
		}
		
		$form->setValuesByPost();
		return $this->editSettings($form);
	}

}

?>
