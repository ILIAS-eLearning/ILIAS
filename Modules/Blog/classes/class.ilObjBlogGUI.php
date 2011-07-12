<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject2GUI.php";
include_once("./Modules/Blog/classes/class.ilBlogPosting.php");

/**
* Class ilObjBlogGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* $Id: class.ilObjFolderGUI.php 25134 2010-08-13 14:22:11Z smeyer $
*
* @ilCtrl_Calls ilObjBlogGUI: ilBlogPostingGUI, ilWorkspaceAccessGUI, ilPortfolioPageGUI
* @ilCtrl_Calls ilObjBlogGUI: ilInfoScreenGUI
*
* @extends ilObject2GUI
*/
class ilObjBlogGUI extends ilObject2GUI
{
	protected $month; // [string]
	protected $mode; // [int]
	
	const MODE_WORKSPACE = 1;
	const MODE_EMBEDDED_FULL = 2;
	// const MODE_EMBEDDED_LIST = 3;
	
	function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
	{
		global $lng, $ilCtrl;
		
		$lng->loadLanguageModule("blog");
		$this->mode = self::MODE_WORKSPACE;		
		
		$ilCtrl->saveParameter($this, "prvw");
		
		return parent::__construct($a_id, $a_id_type, $a_parent_node_id);		
	}

	function getType()
	{
		return "blog";
	}
	
	/**
	 * Set display mode
	 * 
	 * @param int $a_mode 
	 */
	function setMode($a_mode)
	{
		$a_mode = (int)$a_mode;
		if(in_array($a_mode, array(self::MODE_WORKSPACE, self::MODE_EMBEDDED_FULL)))
		{
			$this->mode = $a_mode;
		}		
	}

	protected function initCreationForms($a_new_type)
	{
		$forms = parent::initCreationForms($a_new_type);

		unset($forms[self::CFORM_IMPORT]);
		unset($forms[self::CFORM_CLONE]);
		
		return $forms;
	}

	protected function initEditCustomForm(ilPropertyFormGUI $a_form)
	{
		global $lng;

		$notes = new ilCheckboxInputGUI($lng->txt("blog_enable_notes"), "notes");
		$a_form->addItem($notes);
	}

	protected function getEditFormCustomValues(array &$a_values)
	{
		$a_values["notes"] = $this->object->getNotesStatus();
	}

	protected function updateCustom(ilPropertyFormGUI $a_form)
	{
		$this->object->updateNotesStatus($a_form->getInput("notes"));
	}

	function setTabs()
	{
		global $lng;

		$this->ctrl->setParameter($this,"wsp_id",$this->node_id);

		if ($this->checkPermissionBool("read"))
		{
			$this->tabs_gui->addTab("content",
				$lng->txt("content"),
				$this->ctrl->getLinkTarget($this, ""));
		}
		
		if ($this->checkPermissionBool("read"))
		{
			$this->tabs_gui->addTab("id_info",
				$lng->txt("info_short"),
				$this->ctrl->getLinkTargetByClass(array("ilobjbloggui", "ilinfoscreengui"), "showSummary"));
		}

		if ($this->checkPermissionBool("write"))
		{
			$this->tabs_gui->addTab("settings",
				$lng->txt("settings"),
				$this->ctrl->getLinkTarget($this, "edit"));
			
			$this->tabs_gui->addNonTabbedLink("preview", $lng->txt("blog_preview"), 
				$this->ctrl->getLinkTarget($this, "preview"));
		}

		// will add permissions if needed
		parent::setTabs();
	}

	function executeCommand()
	{
		global $ilCtrl, $tpl, $ilTabs, $lng;

		// goto link to blog posting
		if($_GET["gtp"])
		{
			$ilCtrl->setCmdClass('ilblogpostinggui');
			$ilCtrl->setCmd('preview');
			$_GET["page"] = $_GET["gtp"];
			
			// force fullscreen / fixed width
			$_REQUEST["prvw"] = 1;
			$ilCtrl->setParameter($this, "prvw", 1);
		}
		
		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();
		
		switch($next_class)
		{
			case 'ilblogpostinggui':
				$ilCtrl->setParameter($this, "bmn", $_REQUEST["bmn"]);
				$ilTabs->setBackTarget($lng->txt("back"),
					$ilCtrl->getLinkTarget($this, ""));

				include_once("./Modules/Blog/classes/class.ilBlogPostingGUI.php");
				$bpost_gui = new ilBlogPostingGUI($this->node_id, $this->getAccessHandler(),
					$_GET["page"], $_GET["old_nr"], $this->object->getNotesStatus());
				
				if (!$this->checkPermissionBool("write"))
				{
					$bpost_gui->setEnableEditing(false);
				}

				/*
				include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
				$bpost_gui->setStyleId(ilObjStyleSheet::getEffectiveContentStyleId(
					$this->object->getStyleSheetId(), "blog"));
				$this->setContentStyleSheet();
				*/

				$ret = $ilCtrl->forwardCommand($bpost_gui);
				if ($ret != "")
				{					
					$items = $this->buildPostingList($this->object->getId());
					$nav = $this->renderNavigation($items);
					unset($items);
					
					if(!$_REQUEST["prvw"])
					{
						$tpl->setRightContent($nav);
						$tpl->setContent($ret);
					}
					else
					{
						$this->renderFullScreen($ret, $nav);
					}
				}
				break;
				
			case "ilinfoscreengui":
				$this->setTabs();
				$this->infoScreenForward();	
				break;

			default:
				return parent::executeCommand();			
		}

		return true;
	}
	
	/**
	 * Create new posting
	 */
	function createPosting()
	{
		global $ilCtrl, $lng;

		if($_POST["title"])
		{
			// create new posting
			include_once("./Modules/Blog/classes/class.ilBlogPosting.php");
			$posting = new ilBlogPosting();
			$posting->setTitle($_POST["title"]);
			$posting->setBlogId($this->object->getId());
			$posting->create();

			$ilCtrl->setParameterByClass("ilblogpostinggui", "page", $posting->getId());
			$ilCtrl->redirectByClass("ilblogpostinggui", "edit");
		}
		else
		{
			$ilCtrl->redirect($this, "render");
		}
	}
	
	function getHTML()
	{
		return $this->render(true);			
	}
	
	function preview()
	{
		global $ilCtrl;
		
		$_REQUEST["prvw"] = 1;		
		$ilCtrl->setParameter($this, "prvw", 1);
		$this->render();
	}
	
	function renderFullScreen($a_content, $a_navigation)
	{
		global $tpl, $lng, $ilCtrl, $ilUser, $ilTabs;
		
		$owner = $this->object->getOwner();
		
		$ilTabs->clearTargets();
		
		// back		
		if($owner == $ilUser->getId())
		{			
			$ilCtrl->setParameter($this, "prvw", "");
			$back = $ilCtrl->getLinkTarget($this, "");
			$ilTabs->setBackTarget($lng->txt("blog_back_to_ilias"), $back);
		}
		else
		{
			// if deeplink this will not be possible
		}		
		
		// title
		$tpl->setTitle($this->object->getTitle());
		$tpl->setTitleIcon(null);
		
		// owner
		include_once("./Services/User/classes/class.ilUserUtil.php");
		$owner = ilUserUtil::getNamePresentation($owner, true, false); 		
		$tpl->setDescription($owner);
		
		// content
		$tpl->setContent($a_content);
		$tpl->setRightContent($a_navigation);
		$tpl->setFrameFixedWidth(true);

		echo $tpl->show("DEFAULT", true, true);
		exit();
	}

	/**
	 * Render root folder
	 * 
	 * @param bool $a_return
	 */
	function render($a_return = false)
	{
		global $tpl, $ilTabs, $ilCtrl, $lng, $ilToolbar;
		
		// we have to discuss this for portfolios
		if(!$this->checkPermissionBool("read") && $this->mode != self::MODE_EMBEDDED_FULL)
		{
			ilUtil::sendInfo($lng->txt("no_permission"), true);
			// $ilCtrl->redirect($this, "infoScreen");
			return;
		}

		// gather postings by month
		$items = $this->buildPostingList($this->object->getId());
		
		if($this->mode == self::MODE_WORKSPACE && !$a_return && !$_REQUEST["prvw"])
		{
			$ilTabs->activateTab("content");
			$this->renderToolbar();
		}
		
		$list = $nav = "";		
		if($items)
		{			
			// current month (if none given or empty)
			$this->month = $_REQUEST["bmn"];
			if(!$this->month || !$items[$this->month])
			{
				$this->month = array_keys($items);
				$this->month = array_shift($this->month);
			}

			if($items[$this->month])
			{				
				$ilCtrl->setParameter($this, "bmn", $this->month);
				$list = $this->renderList($items[$this->month], $this->month);
				$nav = $this->renderNavigation($items);
			}			
		}
		
		if(!$a_return)
		{
			if(!$_REQUEST["prvw"])
			{					
				$tpl->setContent($list);
				$tpl->setRightContent($nav);
			}
			else
			{							
				$this->renderFullScreen($list, $nav);
			}
		}
		else
		{
			switch($this->mode)
			{
				case self::MODE_WORKSPACE:
					return array("list"=>$list,
						"navigation"=>$nav);

				case self::MODE_EMBEDDED_FULL:
					$wtpl = new ilTemplate("tpl.blog_embedded.html", true, true, "Modules/Blog");
					$wtpl->setVariable("VAL_LIST", $list);
					$wtpl->setVariable("VAL_NAVIGATION", $nav);							
					return $wtpl->get();
			}
		}
	}
	
	function buildPostingList($a_obj_id)
	{
		$items = array();
		foreach(ilBlogPosting::getAllPostings($a_obj_id) as $posting)
		{
			$month = substr($posting["created"]->get(IL_CAL_DATE), 0, 7);
			$items[$month][$posting["id"]] = $posting;
		}
		return $items;
	}

	function renderToolbar()
	{
		global $lng, $ilCtrl, $ilToolbar;

		if($this->checkPermissionBool("write"))
		{
			$ilToolbar->setFormAction($ilCtrl->getFormAction($this));

			include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
			$title = new ilTextInputGUI($lng->txt("title"), "title");
			$ilToolbar->addInputItem($title, $lng->txt("title"));

			$ilToolbar->addFormButton($lng->txt("blog_add_posting"), "createPosting");
		}
	}

	function renderList(array $items, $a_month, $a_offline = false)
	{
		global $lng, $ilCtrl;
		
		include_once "Services/Calendar/classes/class.ilCalendarUtil.php";
		$wtpl = new ilTemplate("tpl.blog_list.html", true, true, "Modules/Blog");
		
		include_once "Services/Calendar/classes/class.ilCalendarUtil.php";
		$title = ilCalendarUtil::_numericMonthToString(substr($a_month, 6)).
				" ".substr($a_month, 0, 4);
		$wtpl->setVariable("TXT_CURRENT_MONTH", $title);		
		
		foreach($items as $item)
		{
			if(!$a_offline)
			{
				$ilCtrl->setParameterByClass("ilblogpostinggui", "page", $item["id"]);
				$preview = $ilCtrl->getLinkTargetByClass("ilblogpostinggui", "preview");
			}
			else
			{
				$preview = "blp_".$item["id"].".html";
			}

			// actions
			if($this->checkPermissionBool("write") && !$a_offline)
			{
				include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
				$alist = new ilAdvancedSelectionListGUI();
				$alist->setId($item["id"]);
				$alist->setListTitle($lng->txt("actions"));
				$alist->addItem($lng->txt("edit"), "edit", 
					$ilCtrl->getLinkTargetByClass("ilblogpostinggui", "edit"));
				$alist->addItem($lng->txt("delete"), "delete",
					$ilCtrl->getLinkTargetByClass("ilblogpostinggui", "deleteBlogPostingConfirmationScreen"));

				$wtpl->setCurrentBlock("actions");
				$wtpl->setVariable("ACTION_SELECTOR", $alist->getHTML());
				$wtpl->parseCurrentBlock();
			}

			// comments
			if($this->object->getNotesStatus())
			{
				// count (public) notes
				include_once("Services/Notes/classes/class.ilNote.php");
				$count = sizeof(ilNote::_getNotesOfObject($this->obj_id, 
					$item["id"], "blp", IL_NOTE_PUBLIC));
				
				$wtpl->setCurrentBlock("comments");
				$wtpl->setVariable("TEXT_COMMENTS", $lng->txt("blog_comments"));
				$wtpl->setVariable("URL_COMMENTS", $preview);
				$wtpl->setVariable("COUNT_COMMENTS", $count);
				$wtpl->parseCurrentBlock();
			}

			$wtpl->setCurrentBlock("posting");
			
			// title
			$wtpl->setVariable("URL_TITLE", $preview);
			$wtpl->setVariable("TITLE", $item["title"]);
			$wtpl->setVariable("DATETIME",
				ilDatePresentation::formatDate($item["created"], IL_CAL_DATE));
			
			// permanent link
			$goto = $this->getAccessHandler()->getGotoLink($this->node_id, $this->obj_id, "_".$item["id"]);
			$wtpl->setVariable("URL_PERMALINK", $goto); // :TODO:
			$wtpl->setVariable("TEXT_PERMALINK", $lng->txt("blog_permanent_link"));

			// content
			$page = new ilBlogPosting($item["id"]);
			$page->buildDom();
			$wtpl->setVariable("CONTENT", $page->getFirstParagraphText());
			$wtpl->setVariable("URL_MORE", $preview); 
			$wtpl->setVariable("TEXT_MORE", $lng->txt("blog_list_more"));

			$wtpl->parseCurrentBlock();
		}

		return $wtpl->get();
	}

	function renderNavigation(array $items, $a_offline = false)
	{
		global $ilCtrl;

		$max_detail_postings = 10;
		
		$wtpl = new ilTemplate("tpl.blog_list_navigation.html",	true, true,
			"Modules/Blog");

		include_once "Services/Calendar/classes/class.ilCalendarUtil.php";
		$counter = 0;
		foreach($items as $month => $postings)
		{
			$month_name = ilCalendarUtil::_numericMonthToString(substr($month, 6)).
				" ".substr($month, 0, 4);

			if(!$a_offline)
			{
				$ilCtrl->setParameter($this, "bmn", $month);
				$month_url = $ilCtrl->getLinkTarget($this, "render");
			}
			else
			{
				$month_url = "blm_".$month.".html";
			}

			// list postings for month
			if($counter < $max_detail_postings)
			{
				$wtpl->setCurrentBlock("navigation_item");
				foreach($postings as $id => $posting)
				{
					$counter++;

					$caption = /* ilDatePresentation::formatDate($posting["created"], IL_CAL_DATETIME).
						", ".*/ $posting["title"];

					if(!$a_offline)
					{
						$ilCtrl->setParameterByClass("ilblogpostinggui", "page", $id);
						$url = $ilCtrl->getLinkTargetByClass("ilblogpostinggui", "preview");					
					}
					else
					{
						$url = "blp_".$id.".html";
					}
					
					$wtpl->setVariable("NAV_ITEM_URL", $url);
					$wtpl->setVariable("NAV_ITEM_CAPTION", $caption);
					$wtpl->parseCurrentBlock();
				}

				$wtpl->setCurrentBlock("navigation_month_details");
				$wtpl->setVariable("NAV_MONTH", $month_name);
				$wtpl->setVariable("URL_MONTH", $month_url);
				$wtpl->parseCurrentBlock();
			}
			// summarized month
			else
			{
				$ilCtrl->setParameter($this, "bmn", $month);

				$wtpl->setCurrentBlock("navigation_month");
				$wtpl->setVariable("MONTH_NAME", $month_name);				
				$wtpl->setVariable("URL_MONTH", $month_url);
				$wtpl->setVariable("MONTH_COUNT", sizeof($postings));
				$wtpl->parseCurrentBlock();
			}
		}

		$ilCtrl->setParameter($this, "bmn", $this->month);

		return $wtpl->get();
	}
	
	/**
	* this one is called from the info button in the repository
	* not very nice to set cmdClass/Cmd manually, if everything
	* works through ilCtrl in the future this may be changed
	*/
	function infoScreen()
	{
		$this->ctrl->setCmd("showSummary");
		$this->ctrl->setCmdClass("ilinfoscreengui");
		$this->infoScreenForward();
	}
	
	/**
	* show information screen
	*/
	function infoScreenForward()
	{
		global $ilTabs, $ilErr;
		
		$ilTabs->activateTab("id_info");

		if (!$this->checkPermissionBool("visible"))
		{
			$ilErr->raiseError($this->lng->txt("msg_no_perm_read"));
		}

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);

		$info->enablePrivateNotes();
		
		if ($this->checkPermissionBool("read"))
		{
			$info->enableNews();
		}

		// no news editing for files, just notifications
		$info->enableNewsEditing(false);
		if ($this->checkPermissionBool("write"))
		{
			$news_set = new ilSetting("news");
			$enable_internal_rss = $news_set->get("enable_rss_for_internal");
			
			if ($enable_internal_rss)
			{
				$info->setBlockProperty("news", "settings", true);
				$info->setBlockProperty("news", "public_notifications_option", true);
			}
		}
		
		// standard meta data
		$info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());
		
		$info->addProperty("goto test", $this->getAccessHandler()->getGotoLink($this->node_id, $this->object->getId()));
		
		$this->ctrl->forwardCommand($info);
	}
	
	function export()
	{
		$this->buildExportFile();
		
		// :TODO: message
		
	    // $ilCtrl->redirect($this, "render");
	}
	
	/**
	 * Build export file
	 *
	 * @param
	 * @return
	 */
	function buildExportFile()
	{
		global $ilias;

		// create export file
		include_once("./Services/Export/classes/class.ilExport.php");
		ilExport::_createExportDirectory($this->object->getId(), "html", "blog");
		$exp_dir =
			ilExport::_getExportDirectory($this->object->getId(), "html", "blog");

		$this->subdir = $this->object->getType()."_".$this->object->getId();
		$this->export_dir = $exp_dir."/".$this->subdir;

		// initialize temporary target directory
		ilUtil::delDir($this->export_dir);
		ilUtil::makeDir($this->export_dir);
		
		// system style html exporter
		include_once("./Services/Style/classes/class.ilSystemStyleHTMLExport.php");
		$this->sys_style_html_export = new ilSystemStyleHTMLExport($this->export_dir);
	    $this->sys_style_html_export->addImage("icon_blog_b.gif");
		$this->sys_style_html_export->export();

		// init co page html exporter
		include_once("./Services/COPage/classes/class.ilCOPageHTMLExport.php");
		$this->co_page_html_export = new ilCOPageHTMLExport($this->export_dir);
		/* $this->co_page_html_export->setContentStyleId(
			$this->object->getStyleSheetId()); */
		$this->co_page_html_export->createDirectories();
		$this->co_page_html_export->exportStyles();
		$this->co_page_html_export->exportSupportScripts();

		// export pages
		$this->exportHTMLPages();

		// zip everything
		if (true)
		{
			// zip it all
			$date = time();
			$zip_file = ilExport::_getExportDirectory($this->object->getId(), "html", "blog").
				"/".$date."__".IL_INST_ID."__".
				$this->object->getType()."_".$this->object->getId().".zip";
			ilUtil::zip($this->export_dir, $zip_file);
			ilUtil::delDir($this->export_dir);
		}
	}

	/**
	 * Export all pages
	 */
	function exportHTMLPages()
	{
		global $tpl, $ilBench, $ilLocator;

		$pages = ilBlogPosting::getAllPostings($this->object->getId(), 0);
		
		$items = $this->buildPostingList($this->object->getId());
		$nav = $this->renderNavigation($items, true);
		
		// month list
		$has_index = false;
		foreach(array_keys($items) as $month)
		{						
			$list = $this->renderList($items[$month], $month, true);			
			$file = $this->writeExportFile("blm_".$month.".html", $list, $nav);
			
			if(!$has_index)
			{
				copy($file, $this->export_dir."/index.html");
				$has_index = true;
			}
		}

		// single postings
		include_once("./Services/COPage/classes/class.ilPageContentUsage.php");
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		foreach ($pages as $page)
		{
			if (ilPageObject::_exists("blp", $page["id"]))
			{
				$this->exportPageHTML($page["id"], $nav, $page["created"]->get(IL_CAL_DATE));
				$this->co_page_html_export->collectPageElements("blp:pg", $page["id"]);
			}
		}
		$this->co_page_html_export->exportPageElements();
	}
	
	function writeExportFile($a_file, $a_content, $a_right_content = null, $a_back_url = null)
	{
		global $lng, $ilTabs;
		
		$this->tpl = $this->co_page_html_export->getPreparedMainTemplate();
		
		$this->tpl->getStandardTemplate();
		$file = $this->export_dir."/".$a_file;
		// return if file is already existing
		if (@is_file($file))
		{
			return;
		}
		
		// export template: page content
		$ep_tpl = new ilTemplate("tpl.export_page.html", true, true,
			"Modules/Blog");
		$ep_tpl->setVariable("PAGE_CONTENT", $a_content);		
	
		// export template: right content			
		if($a_right_content)
		{
			$ep_tpl->setVariable("RIGHT_CONTENT", $a_right_content);
		}
		
		// workaround
		$this->tpl->setVariable("MAINMENU", "<div style='min-height:40px;'></div>");
		$this->tpl->setTitle($this->object->getTitle());
		$this->tpl->setTitleIcon("./images/icon_blog_b.gif",
			$lng->txt("obj_blog"));
		
		$ilTabs->clearTargets();
		if($a_back_url)
		{			
			$ilTabs->setBackTarget($lng->txt("back"), $a_back_url);
		}

		$this->tpl->setContent($ep_tpl->get());
		//$this->tpl->fillMainContent();
		$content = $this->tpl->get("DEFAULT", false, false, false,
			true, true, true);

		// open file
		if (!file_put_contents($file, $content))
		{
			die ("<b>Error</b>: Could not open \"".$file."\" for writing".
					" in <b>".__FILE__."</b> on line <b>".__LINE__."</b><br />");
		}

		// set file permissions
		chmod($file, 0770);
		
		return $file;
	}

	/**
	 * Export page html
	 */
	function exportPageHTML($a_post_id, $a_navigation, $a_date)
	{
		global $lng;
		
		// page
		include_once("./Modules/Blog/classes/class.ilBlogPostingGUI.php");
		$blp_gui = new ilBlogPostingGUI(0, null, $a_post_id);
		$blp_gui->setOutputMode("offline");
		$page_content = $blp_gui->showPage();
		
		$back = "blm_".substr($a_date, 0, 7).".html";

		$this->writeExportFile("blp_".$a_post_id.".html", $page_content, $a_navigation, $back);

		/*
		if ($this->wiki->getStartPage() == $wpg_gui->getPageObject()->getTitle())
		{
			copy($file, $this->export_dir."/index.html");
		}		 
		*/
	}

	function _goto($a_target)
	{
		$id = explode("_", $a_target);
		
		$_GET["baseClass"] = "ilsharedresourceGUI";	
		$_GET["wsp_id"] = $id[0];		
		if(sizeof($id) == 3)
		{
			$_GET["gtp"] = $id[1];
		}
		include("ilias.php");
		exit;
	}
}

?>