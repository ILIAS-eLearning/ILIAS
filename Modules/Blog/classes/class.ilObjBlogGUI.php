<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject2GUI.php";
require_once "./Modules/Blog/classes/class.ilBlogPosting.php";
require_once "./Services/PersonalDesktop/interfaces/interface.ilDesktopItemHandling.php";

/**
* Class ilObjBlogGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* $Id: class.ilObjFolderGUI.php 25134 2010-08-13 14:22:11Z smeyer $
*
* @ilCtrl_Calls ilObjBlogGUI: ilBlogPostingGUI, ilWorkspaceAccessGUI, ilPortfolioPageGUI
* @ilCtrl_Calls ilObjBlogGUI: ilInfoScreenGUI, ilNoteGUI, ilCommonActionDispatcherGUI
* @ilCtrl_Calls ilObjBlogGUI: ilPermissionGUI, ilObjectCopyGUI, ilRepositorySearchGUI
* @ilCtrl_Calls ilObjBlogGUI: ilExportGUI, ilObjStyleSheetGUI
*
* @extends ilObject2GUI
*/
class ilObjBlogGUI extends ilObject2GUI implements ilDesktopItemHandling
{
	protected $month; // [string]
	protected $items; // [array]
	protected $keyword; // [string]
	protected $author; // [int]
	protected $month_default; // [bool
	
	function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
	{
		global $lng, $ilCtrl;
		
	    parent::__construct($a_id, $a_id_type, $a_parent_node_id);		
		
		if($this->object)
		{
			$this->month = (string)$_REQUEST["bmn"];
			$this->keyword = (string)$_REQUEST["kwd"];
			$this->author = (int)$_REQUEST["ath"];

			// gather postings by month
			$this->items = $this->buildPostingList($this->object->getId());	
			if($this->items)
			{			
				// current month (if none given or empty)			
				if(!$this->month || !$this->items[$this->month])
				{
					$this->month = array_keys($this->items);
					$this->month = array_shift($this->month);
					$this->month_default = true;
				}
			}

			$ilCtrl->setParameter($this, "bmn", $this->month);
		}
		
		$lng->loadLanguageModule("blog");
		$ilCtrl->saveParameter($this, "prvm");
	}

	function getType()
	{
		return "blog";
	}
	
	protected function initCreationForms($a_new_type)
	{
		$forms = parent::initCreationForms($a_new_type);

		if($this->id_type == self::WORKSPACE_NODE_ID)
		{
			unset($forms[self::CFORM_IMPORT]);
			unset($forms[self::CFORM_CLONE]);
		}
		
		return $forms;
	}
	
	protected function afterSave(ilObject $a_new_object)
	{
		global $ilCtrl;
		
		ilUtil::sendSuccess($this->lng->txt("object_added"), true);		
		$ilCtrl->redirect($this, "");
	}
	
	protected function setSettingsSubTabs($a_active)
	{
		// general properties
		$this->tabs_gui->addSubTab("properties",
			$this->lng->txt("blog_properties"),
				$this->ctrl->getLinkTarget($this, 'edit'));
		
		$this->tabs_gui->addSubTab("style",
			$this->lng->txt("obj_sty"),
			$this->ctrl->getLinkTarget($this, 'editStyleProperties'));
		
		$this->tabs_gui->activateSubTab($a_active);
	}

	protected function initEditCustomForm(ilPropertyFormGUI $a_form)
	{
		global $lng, $ilSetting;
		
		$this->setSettingsSubTabs("properties");
		
		if($this->id_type == self::REPOSITORY_NODE_ID)
		{	
			$appr = new ilCheckboxInputGUI($lng->txt("blog_enable_approval"), "approval");
			$appr->setInfo($lng->txt("blog_enable_approval_info"));
			$a_form->addItem($appr);
		}
		
		$notes = new ilCheckboxInputGUI($lng->txt("blog_enable_notes"), "notes");
		$a_form->addItem($notes);
				
		if($ilSetting->get('enable_global_profiles'))
		{
			$rss = new ilCheckboxInputGUI($lng->txt("blog_enable_rss"), "rss");
			$rss->setInfo($lng->txt("blog_enable_rss_info"));
			$a_form->addItem($rss);
		}
		
		
		// navigation
		
		$nav = new ilFormSectionHeaderGUI();
		$nav->setTitle($lng->txt("blog_settings_navigation"));
		$a_form->addItem($nav);
		
		$nav_mode = new ilRadioGroupInputGUI($lng->txt("blog_nav_mode"), "nav");
		$nav_mode->setRequired(true);
		$a_form->addItem($nav_mode);	
		
		$opt = new ilRadioOption($lng->txt("blog_nav_mode_month_list"), ilObjBlog::NAV_MODE_LIST);
		$opt->setInfo($lng->txt("blog_nav_mode_month_list_info"));
		$nav_mode->addOption($opt);
		
		$detail_num = new ilNumberInputGUI($lng->txt("blog_nav_mode_month_list_num_detail"), "nav_list_detail");
		$detail_num->setInfo($lng->txt("blog_nav_mode_month_list_num_detail_info"));
		$detail_num->setRequired(true);
		$detail_num->setSize(3);
		$detail_num->setMinValue(0);
		$opt->addSubItem($detail_num);
		
		$mon_num = new ilNumberInputGUI($lng->txt("blog_nav_mode_month_list_num_month"), "nav_list_mon");
		$mon_num->setInfo($lng->txt("blog_nav_mode_month_list_num_month_info"));
		$mon_num->setSize(3);
		$mon_num->setMinValue(1);
		$opt->addSubItem($mon_num);
		
		$opt = new ilRadioOption($lng->txt("blog_nav_mode_month_single"), ilObjBlog::NAV_MODE_MONTH);
		$opt->setInfo($lng->txt("blog_nav_mode_month_single_info"));
		$nav_mode->addOption($opt);
					
		$order_options = array();
		if($this->object->getOrder())
		{				
			foreach($this->object->getOrder() as $item)
			{
				$order_options[] = $lng->txt("blog_".$item);
			}			
		}		
		
		if(!in_array($lng->txt("blog_navigation"), $order_options))
		{
			$order_options[] = $lng->txt("blog_navigation");
		}
		
		if($this->id_type == self::REPOSITORY_NODE_ID)
		{	
			if(!in_array($lng->txt("blog_authors"), $order_options))
			{
				$order_options[] = $lng->txt("blog_authors");
			}
			
			$auth = new ilCheckboxInputGUI($lng->txt("blog_enable_nav_authors"), "nav_authors");
			$auth->setInfo($lng->txt("blog_enable_nav_authors_info"));
			$a_form->addItem($auth);
		}		
				
		$keyw = new ilCheckboxInputGUI($lng->txt("blog_enable_keywords"), "keywords");
		$keyw->setInfo($lng->txt("blog_enable_keywords_info"));		
		$a_form->addItem($keyw);		
		
		if(!in_array($lng->txt("blog_keywords"), $order_options))
		{
			$order_options[] = $lng->txt("blog_keywords");
		}
		
		$order = new ilNonEditableValueGUI($lng->txt("blog_nav_sortorder"), "order");		
		$order->setMultiValues($order_options);
		$order->setValue(array_shift($order_options));
		$order->setMulti(true, true, false);
		$a_form->addItem($order);
		
		
		// presentation (frame)
		
		$pres = new ilFormSectionHeaderGUI();
		$pres->setTitle($lng->txt("blog_presentation_frame"));
		$a_form->addItem($pres);
	
		$ppic = new ilCheckboxInputGUI($lng->txt("blog_profile_picture"), "ppic");
		$a_form->addItem($ppic);
		
		if($this->id_type == self::REPOSITORY_NODE_ID)
		{
			$ppic->setInfo($lng->txt("blog_profile_picture_repository_info"));
		}
		
		$blga_set = new ilSetting("blga");
		if($blga_set->get("banner"))
		{		
			include_once "Services/Form/classes/class.ilFileInputGUI.php";
			ilFileInputGUI::setPersonalWorkspaceQuotaCheck(true);
			
			$dimensions = " (".$blga_set->get("banner_width")."x".
				$blga_set->get("banner_height").")";
			
			$img = new ilImageFileInputGUI($lng->txt("blog_banner").$dimensions, "banner");
			$a_form->addItem($img);
			
			// show existing file
			$file = $this->object->getImageFullPath(true);
			if($file)
			{
				$img->setImage($file);
			}
		}		
		
		/* #15000
		$bg_color = new ilColorPickerInputGUI($lng->txt("blog_background_color"), "bg_color");
		$a_form->addItem($bg_color);

		$font_color = new ilColorPickerInputGUI($lng->txt("blog_font_color"), "font_color");
		$a_form->addItem($font_color);	
		*/
		
		// presentation (overview)
		
		$list = new ilFormSectionHeaderGUI();
		$list->setTitle($lng->txt("blog_presentation_overview"));
		$a_form->addItem($list);
		
		$post_num = new ilNumberInputGUI($lng->txt("blog_list_num_postings"), "ov_list_post_num");
		$post_num->setInfo($lng->txt("blog_list_num_postings_info"));
		$post_num->setSize(3);
		$post_num->setMinValue(1);
		$post_num->setRequired(true);
		$a_form->addItem($post_num);
		
		$abs_shorten = new ilCheckboxInputGUI($lng->txt("blog_abstract_shorten"), "abss");
		$a_form->addItem($abs_shorten);
		
		$abs_shorten_len = new ilNumberInputGUI($lng->txt("blog_abstract_shorten_length"), "abssl");
		$abs_shorten_len->setSize(5);
		$abs_shorten_len->setRequired(true);
		$abs_shorten_len->setSuffix($lng->txt("blog_abstract_shorten_characters"));
		$abs_shorten_len->setMinValue(50, true);
		$abs_shorten->addSubItem($abs_shorten_len);
		
		$abs_img = new ilCheckboxInputGUI($lng->txt("blog_abstract_image"), "absi");
		$abs_img->setInfo($lng->txt("blog_abstract_image_info"));
		$a_form->addItem($abs_img);
		
		$abs_img_width = new ilNumberInputGUI($lng->txt("blog_abstract_image_width"), "absiw");
		$abs_img_width->setSize(5);
		$abs_img_width->setRequired(true);
		$abs_img_width->setSuffix($lng->txt("blog_abstract_image_pixels"));
		$abs_img_width->setMinValue(32, true);
		$abs_img->addSubItem($abs_img_width);
		
		$abs_img_height = new ilNumberInputGUI($lng->txt("blog_abstract_image_height"), "absih");
		$abs_img_height->setSize(5);
		$abs_img_height->setRequired(true);
		$abs_img_height->setSuffix($lng->txt("blog_abstract_image_pixels"));
		$abs_img_height->setMinValue(32, true);
		$abs_img->addSubItem($abs_img_height);
	}

	protected function getEditFormCustomValues(array &$a_values)
	{
		if($this->id_type == self::REPOSITORY_NODE_ID)
		{
			$a_values["approval"] = $this->object->hasApproval();
			$a_values["nav_authors"] = $this->object->hasAuthors();
		}
		$a_values["keywords"] = $this->object->hasKeywords();
		$a_values["notes"] = $this->object->getNotesStatus();
		$a_values["ppic"] = $this->object->hasProfilePicture();
		/*
		$a_values["bg_color"] = $this->object->getBackgroundColor();
		$a_values["font_color"] = $this->object->getFontColor();		 
		*/
		$a_values["banner"] = $this->object->getImage();
		$a_values["rss"] = $this->object->hasRSS();
		$a_values["abss"] = $this->object->hasAbstractShorten();		
		$a_values["absi"] = $this->object->hasAbstractImage();		
		$a_values["nav"] = $this->object->getNavMode();
		$a_values["nav_list_detail"] = $this->object->getNavModeListPostings();
		$a_values["nav_list_mon"] = $this->object->getNavModeListMonths();		
		$a_values["ov_list_post_num"] = $this->object->getOverviewPostings();
		
		// #13420
		$a_values["abssl"] = $this->object->getAbstractShortenLength() ? $this->object->getAbstractShortenLength() : ilObjBlog::ABSTRACT_DEFAULT_SHORTEN_LENGTH;
		$a_values["absiw"] = $this->object->getAbstractImageWidth() ? $this->object->getAbstractImageWidth() : ilObjBlog::ABSTRACT_DEFAULT_IMAGE_WIDTH;
		$a_values["absih"] = $this->object->getAbstractImageHeight() ? $this->object->getAbstractImageHeight() : ilObjBlog::ABSTRACT_DEFAULT_IMAGE_HEIGHT;
	}

	protected function updateCustom(ilPropertyFormGUI $a_form)
	{		
		global $lng;
		
		if($this->id_type == self::REPOSITORY_NODE_ID)
		{
			$this->object->setApproval($a_form->getInput("approval"));
			$this->object->setAuthors($a_form->getInput("nav_authors"));
		}
		$this->object->setKeywords($a_form->getInput("keywords"));
		$this->object->setNotesStatus($a_form->getInput("notes"));
		$this->object->setProfilePicture($a_form->getInput("ppic"));
		/*
		$this->object->setBackgroundColor($a_form->getInput("bg_color"));
		$this->object->setFontColor($a_form->getInput("font_color"));		 
		*/
		$this->object->setRSS($a_form->getInput("rss"));
		$this->object->setAbstractShorten($a_form->getInput("abss"));
		$this->object->setAbstractShortenLength($a_form->getInput("abssl"));
		$this->object->setAbstractImage($a_form->getInput("absi"));
		$this->object->setAbstractImageWidth($a_form->getInput("absiw"));
		$this->object->setAbstractImageHeight($a_form->getInput("absih"));
		$this->object->setNavMode($a_form->getInput("nav"));
		$this->object->setNavModeListPostings($a_form->getInput("nav_list_detail"));
		$this->object->setNavModeListMonths($a_form->getInput("nav_list_mon"));
		$this->object->setOverviewPostings($a_form->getInput("ov_list_post_num"));
		
		$order = $a_form->getInput("order");
		foreach($order as $idx => $value)
		{
			if($value == $lng->txt("blog_navigation"))
			{
				$order[$idx] = "navigation";
			}
			else if($value == $lng->txt("blog_keywords"))
			{
				$order[$idx] = "keywords";
			}
			else
			{
				$order[$idx]= "authors";
			}
		}
		$this->object->setOrder($order);
		
		// banner field is optional
		$banner = $a_form->getItemByPostVar("banner");
		if($banner)
		{				
			if($_FILES["banner"]["tmp_name"]) 
			{
				$this->object->uploadImage($_FILES["banner"]);
			}
			else if($banner->getDeletionFlag())
			{
				$this->object->deleteImage();
			}
		}
	}

	function setTabs()
	{
		global $lng, $ilHelp;

		if($this->id_type == self::WORKSPACE_NODE_ID)
		{
			$this->ctrl->setParameter($this,"wsp_id",$this->node_id);
		}
		
		$ilHelp->setScreenIdComponent("blog");

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
			
			if($this->id_type == self::REPOSITORY_NODE_ID)
			{	
				$this->tabs_gui->addTab("contributors",
					$lng->txt("blog_contributors"),
					$this->ctrl->getLinkTarget($this, "contributors"));	
			}		
			
			if($this->id_type == self::REPOSITORY_NODE_ID)
			{
				$this->tabs_gui->addTab("export",
					$lng->txt("export"),
					$this->ctrl->getLinkTargetByClass("ilexportgui", ""));
			}
		}
		
		if($this->mayContribute())
		{
			$this->tabs_gui->addNonTabbedLink("preview", $lng->txt("blog_preview"), 
				$this->ctrl->getLinkTarget($this, "preview"));
		}

		// will add permissions if needed
		parent::setTabs();
	}

	function executeCommand()
	{
		global $ilCtrl, $tpl, $ilTabs, $lng, $ilNavigationHistory;

		// goto link to blog posting
		if($_GET["gtp"])
		{
			$page_id = (int)$_GET["gtp"];
			include_once "Modules/Blog/classes/class.ilBlogPosting.php";
			if(ilBlogPosting::exists($this->object_id, $page_id))
			{			
				// #12312
				$ilCtrl->setCmdClass("ilblogpostinggui");			
				$_GET["blpg"] = $page_id;
				$ilCtrl->setCmd("previewFullscreen");			
			}
			else
			{
				ilUtil::sendFailure($lng->txt("blog_posting_not_found"));			
			}							
		}
		
		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();
		
		if($this->id_type == self::REPOSITORY_NODE_ID)
		{			
			$tpl->getStandardTemplate();
			
			// add entry to navigation history
			if(!$this->getCreationMode() &&
				$this->getAccessHandler()->checkAccess("read", "", $this->node_id))
			{
				$link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", "frameset");				
				$ilNavigationHistory->addItem($this->node_id, $link, "blog");
			}
		}
		
		switch($next_class)
		{
			case 'ilblogpostinggui':	
				// #9680
				if($this->id_type == self::REPOSITORY_NODE_ID)
				{						
					$this->setLocator();						
				}				
				else
				{
					include_once "Services/Form/classes/class.ilFileInputGUI.php";
					ilFileInputGUI::setPersonalWorkspaceQuotaCheck(true);
				}				
				$ilTabs->setBackTarget($lng->txt("back"),
					$ilCtrl->getLinkTarget($this, ""));
					
				include_once("./Modules/Blog/classes/class.ilBlogPostingGUI.php");
				$bpost_gui = new ilBlogPostingGUI($this->node_id, 
					$this->getAccessHandler(),
					$_GET["blpg"], 
					$_GET["old_nr"], 
					($this->object->getNotesStatus() && !$this->disable_notes),
					$this->mayContribute($_GET["blpg"]));
				
				// needed for editor			
				$bpost_gui->setStyleId(ilObjStyleSheet::getEffectiveContentStyleId(
					$this->object->getStyleSheetId(), "blog"));				
			
				// keep preview mode through notes gui (has its own commands)
				switch($cmd)
				{
					// blog preview
					case "previewFullscreen":
						$ilCtrl->setParameter($this, "prvm", "fsc");
						break;

					// blog in portfolio
					case "previewEmbedded":
						$ilCtrl->setParameter($this, "prvm", "emb");
						break;			
					
					// edit
					default:						
						$this->setContentStyleSheet();	
						
						$this->ctrl->setParameterByClass("ilblogpostinggui", "blpg", $_GET["blpg"]);
						$this->tabs_gui->addNonTabbedLink("preview", $lng->txt("blog_preview"), 
							$this->ctrl->getLinkTargetByClass("ilblogpostinggui", "previewFullscreen"));
						$this->ctrl->setParameterByClass("ilblogpostinggui", "blpg", "");
						break;
				}
				
				$ret = $ilCtrl->forwardCommand($bpost_gui);
				if ($ret != "")
				{	
					// keep preview mode through notes gui
					if($_REQUEST["prvm"])
					{
						$cmd = "preview".(($_REQUEST["prvm"] == "fsc") ? "Fullscreen" : "Embedded");						
					}
					
					// $is_owner = $this->object->getOwner() == $ilUser->getId();
					$is_owner = $this->mayContribute();
					$is_active = $bpost_gui->getBlogPosting()->getActive();
					
					// do not show inactive postings 
					if(($cmd == "previewFullscreen" || $cmd == "previewEmbedded")
						&& !$is_owner && !$is_active)
					{
						$this->ctrl->redirect($this, "preview");
					}
					
					switch($cmd)
					{
						// blog preview
						case "previewFullscreen":		
							$this->addHeaderAction($cmd);	
							$this->filterInactivePostings();
							$nav = $this->renderNavigation($this->items, "preview", $cmd);							
							$this->renderFullScreen($ret, $nav);
							break;
							
						// blog in portfolio
						case "previewEmbedded":
							$this->filterInactivePostings();
							$nav = $this->renderNavigation($this->items, "gethtml", $cmd);	
							return $this->buildEmbedded($ret, $nav);
						
						// ilias/editor
						default:	
							// infos about draft status / snippet
							$info = array();
							if(!$is_active)
							{
								// single author blog (owner) in personal workspace
								if($this->id_type == self::WORKSPACE_NODE_ID)
								{								
									$info[] = $lng->txt("blog_draft_info");
								}
								else
								{
									$info[] = $lng->txt("blog_draft_info_contributors");
								}
							}
							if($cmd != "history" && !$bpost_gui->getBlogPosting()->getFirstParagraphText())
							{
								$info[] = $lng->txt("blog_new_posting_info");
							}							
							if($this->object->hasApproval() && !$bpost_gui->getBlogPosting()->isApproved())
							{								
								// #9737
								$info[] = $lng->txt("blog_posting_edit_approval_info");
							}
							if(sizeof($info) && !$tpl->hasMessage("info")) // #15121
							{
								ilUtil::sendInfo(implode("<br />", $info));	
							}					
							// revert to edit cmd to avoid confusion
							$this->addHeaderAction("render");	
							$tpl->setContent($ret);
							$nav = $this->renderNavigation($this->items, "render", $cmd, null, $is_owner);	
							$tpl->setRightContent($nav);	
							break;
					}
				}
				break;
				
			case "ilinfoscreengui":
				$this->prepareOutput();
				$this->addHeaderAction("render");
				$this->infoScreenForward();	
				break;
			
			case "ilnotegui":
				$this->preview();
				break;
			
			case "ilcommonactiondispatchergui":
				include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
				$gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
				$this->ctrl->forwardCommand($gui);
				break;
			
			case "ilpermissiongui":
				$this->prepareOutput();
				$ilTabs->activateTab("id_permissions");
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui = new ilPermissionGUI($this);
				$this->ctrl->forwardCommand($perm_gui);
				break;
			
			case "ilobjectcopygui":
				include_once "./Services/Object/classes/class.ilObjectCopyGUI.php";
				$cp = new ilObjectCopyGUI($this);
				$cp->setType("blog");
				$this->ctrl->forwardCommand($cp);
				break;
			
			case 'ilrepositorysearchgui':
				$this->prepareOutput();
				$ilTabs->activateTab("contributors");
				include_once('./Services/Search/classes/class.ilRepositorySearchGUI.php');
				$rep_search = new ilRepositorySearchGUI();
				$rep_search->setTitle($this->lng->txt("blog_add_contributor"));
				$rep_search->setCallback($this,'addContributor');
				$this->ctrl->setReturn($this,'contributors');				
				$ret =& $this->ctrl->forwardCommand($rep_search);
				break;
			
			case 'ilexportgui':
				$this->prepareOutput();
				$ilTabs->activateTab("export");
				include_once("./Services/Export/classes/class.ilExportGUI.php");
				$exp_gui = new ilExportGUI($this); 
				$exp_gui->addFormat("xml");
				$exp_gui->addFormat("html", null, $this, "buildExportFile"); // #13419
				$ret = $ilCtrl->forwardCommand($exp_gui);
				break;
			
			case "ilobjstylesheetgui":
				include_once ("./Services/Style/classes/class.ilObjStyleSheetGUI.php");
				$this->ctrl->setReturn($this, "editStyleProperties");
				$style_gui = new ilObjStyleSheetGUI("", $this->object->getStyleSheetId(), false, false);
				$style_gui->omitLocator();
				if ($cmd == "create" || $_GET["new_type"]=="sty")
				{
					$style_gui->setCreationMode(true);
				}

				if ($cmd == "confirmedDelete")
				{
					$this->object->setStyleSheetId(0);
					$this->object->update();
				}

				$ret = $this->ctrl->forwardCommand($style_gui);

				if ($cmd == "save" || $cmd == "copyStyle" || $cmd == "importStyle")
				{
					$style_id = $ret;
					$this->object->setStyleSheetId($style_id);
					$this->object->update();
					$this->ctrl->redirectByClass("ilobjstylesheetgui", "edit");
				}
				break;

			default:				
				if($cmd != "gethtml")
				{
					// desktop item handling, must be toggled before header action
					if($cmd == "addToDesk" || $cmd == "removeFromDesk")
					{
						$this->{$cmd."Object"}();
						if($_GET["prvm"])
						{
							$cmd = "preview";
						}
						else
						{
							$cmd = "render";
						}
						$ilCtrl->setCmd($cmd);
					}					
					$this->addHeaderAction($cmd);
				}
				return parent::executeCommand();			
		}
		
		return true;
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

		if($this->id_type != self::WORKSPACE_NODE_ID)
		{
			$info->enablePrivateNotes();
		}
		
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
		
		if($this->id_type == self::WORKSPACE_NODE_ID)
		{
			$info->addProperty($this->lng->txt("perma_link"), $this->getPermanentLinkWidget());
		}
		
		$this->ctrl->forwardCommand($info);
	}
	
	/**
	 * Create new posting
	 */
	function createPosting()
	{
		global $ilCtrl, $ilUser;
		
		$title = trim(ilUtil::stripSlashes($_POST["title"]));
		if($title)
		{
			// create new posting
			include_once("./Modules/Blog/classes/class.ilBlogPosting.php");
			$posting = new ilBlogPosting();
			$posting->setTitle($title);
			$posting->setBlogId($this->object->getId());
			$posting->setActive(false);
			$posting->setAuthor($ilUser->getId());
			$posting->create();
			
			// switch month list to current month (will include new posting)
			$ilCtrl->setParameter($this, "bmn", date("Y-m"));
			
			$ilCtrl->setParameterByClass("ilblogpostinggui", "blpg", $posting->getId());
			$ilCtrl->redirectByClass("ilblogpostinggui", "edit");
		}
		else
		{
			$ilCtrl->redirect($this, "render");
		}
	}
	
	// --- ObjectGUI End
	
	
	/**
	 * Render object context
	 */
	function render()
	{
		global $tpl, $ilTabs, $ilCtrl, $lng, $ilToolbar, $ilUser, $tree;
		
		if(!$this->checkPermissionBool("read"))
		{
			ilUtil::sendInfo($lng->txt("no_permission"));
			return;
		}

		$ilTabs->activateTab("content");
		
		// toolbar
		if($this->mayContribute())
		{
			$ilToolbar->setFormAction($ilCtrl->getFormAction($this, "createPosting"));

			include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
			$title = new ilTextInputGUI($lng->txt("title"), "title");
			$ilToolbar->addInputItem($title, $lng->txt("title"));
			
			include_once "Services/UIComponent/Button/classes/class.ilSubmitButton.php";
			$button = ilSubmitButton::getInstance();
			$button->setCaption("blog_add_posting");
			$button->setCommand("createPosting");
			$ilToolbar->addButtonInstance($button);
						
			// exercise blog?			
			include_once "Modules/Exercise/classes/class.ilObjExercise.php";			
			$exercises = ilObjExercise::findUserFiles($ilUser->getId(), $this->node_id);
			if($exercises)
			{
				$info = array();				
				foreach($exercises as $exercise)
				{					
					// #9988
					$active_ref = false;
					foreach(ilObject::_getAllReferences($exercise["obj_id"]) as $ref_id)
					{
						if(!$tree->isSaved($ref_id))
						{
							$active_ref = true;
							break;
						}
					}
					if($active_ref)
					{					
						$part = $this->getExerciseInfo($exercise["ass_id"]);
						if($part)
						{
							$info[] = $part;
						}
					}
				}				
				if(sizeof($info))
				{
					ilUtil::sendInfo(implode("<br />", $info));										
				}
			}
		}
								
		// $is_owner = ($this->object->getOwner() == $ilUser->getId());
		$is_owner = $this->mayContribute();
		
		$list_items = $this->getListItems($is_owner);
		
		$list = $nav = "";		
		if($list_items)
		{	
			$list = $this->renderList($list_items, "preview", null, $is_owner);			
			$nav = $this->renderNavigation($this->items, "render", "preview", null, $is_owner);		
		}
		
		$this->setContentStyleSheet();	
					
		$tpl->setContent($list);
		$tpl->setRightContent($nav);
	}
	
	function getExerciseInfo($a_assignment_id)
	{		
		global $lng, $ilCtrl, $ilUser;
		
		include_once "Modules/Exercise/classes/class.ilExAssignment.php";			
		$ass = new ilExAssignment($a_assignment_id);		
		$exercise_id = $ass->getExerciseId();		
		if(!$exercise_id)
		{
			return;
		}
		
		// is the assignment still open?
		$times_up = false;
		if($ass->getDeadline() && $ass->getDeadline() - time() <= 0)
		{
			$times_up = true;
		}

		// exercise goto
		include_once "./Services/Link/classes/class.ilLink.php";
		$exc_ref_id = array_shift(ilObject::_getAllReferences($exercise_id));
		$exc_link = ilLink::_getStaticLink($exc_ref_id, "exc");
		
		$info = sprintf($lng->txt("blog_exercise_info"), 
			$ass->getTitle(),
			"<a href=\"".$exc_link."\">".
			ilObject::_lookupTitle($exercise_id)."</a>");
		
		// submit button
		if(!$times_up)
		{			
			$ilCtrl->setParameter($this, "exc", $exercise_id);				
			$ilCtrl->setParameter($this, "ass", $a_assignment_id);
			$submit_link = $ilCtrl->getLinkTarget($this, "finalize");
			$ilCtrl->setParameter($this, "ass", "");
			$ilCtrl->setParameter($this, "exc", "");	
			
			include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";
			$button = ilLinkButton::getInstance();
			$button->setCaption("blog_finalize_blog");
			$button->setPrimary(true);
			$button->setUrl($submit_link);			
			$info .= " ".$button->render();
		}
		
		// submitted files
		$submitted = ilExAssignment::getDeliveredFiles($exercise_id, $a_assignment_id, $ilUser->getId(), true);
		if($submitted)
		{						
			$submitted = array_pop($submitted);
			
			$ilCtrl->setParameter($this, "ass", $a_assignment_id);
			$dl_link = $ilCtrl->getLinkTarget($this, "downloadExcSubFile");
			$ilCtrl->setParameter($this, "ass", "");
			
			$rel = ilDatePresentation::useRelativeDates();
			ilDatePresentation::setUseRelativeDates(false);
			
			include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";
			$button = ilLinkButton::getInstance();
			$button->setCaption("download");		
			$button->setUrl($dl_link);			
			
			$info .= "<br />".sprintf($lng->txt("blog_exercise_submitted_info"), 
				ilDatePresentation::formatDate(new ilDateTime($submitted["ts"], IL_CAL_DATETIME)),
				$button->render());
			
			ilDatePresentation::setUseRelativeDates($rel);
		}		
		
		
		// work instructions incl. files
		
		$tooltip = "";

		$ass = $ass->getInstruction();
		if($ass)
		{
			$tooltip .= nl2br($ass);					
		}

		$ass_files = ilExAssignment::getFiles($exercise_id, $a_assignment_id);
		if (count($ass_files) > 0)
		{
			$tooltip .= "<br /><br />";
			
			foreach($ass_files as $file)
			{
				$ilCtrl->setParameter($this, "ass", $a_assignment_id);
				$ilCtrl->setParameter($this, "file", urlencode($file["name"]));
				$dl_link = $ilCtrl->getLinkTarget($this, "downloadExcAssFile");
				$ilCtrl->setParameter($this, "file", "");			
				$ilCtrl->setParameter($this, "ass", "");			
				
				$tooltip .= $file["name"].": <a href=\"".$dl_link."\">".
					$lng->txt("download")."</a>";										
			}
		}			
		
		if($tooltip)
		{
			$ol_id = "exc_ass_".$a_assignment_id;

			include_once "Services/UIComponent/Overlay/classes/class.ilOverlayGUI.php";
			$overlay = new ilOverlayGUI($ol_id);

			// overlay
			$overlay->setAnchor($ol_id."_tr");
			$overlay->setTrigger($ol_id."_tr", "click", $ol_id."_tr");
			$overlay->add();

			$info .= "<div id=\"".$ol_id."_tr\"><a href=\"#\">".$lng->txt("exc_instruction")."</a></div>".
				"<div id=\"".$ol_id."\" style=\"display:none; padding:10px;\" class=\"ilOverlay\">".$tooltip."</div>";
		}
		
		return "<div>".$info."</div>";
	}
	
	function downloadExcAssFile()
	{
		if($_GET["ass"] && $_GET["file"])
		{		
			include_once "Modules/Exercise/classes/class.ilExAssignment.php";			
			$ass = new ilExAssignment((int)$_GET["ass"]);
			
			$ass_files = ilExAssignment::getFiles($ass->getExerciseId(), $ass->getId());
			if (count($ass_files) > 0)
			{
				foreach($ass_files as $file)
				{
					if($file["name"] == $_GET["file"])
					{
						ilUtil::deliverFile($file["fullpath"], $file["name"]);						
					}												
				}
			}
		}					
	}
	
	function downloadExcSubFile()
	{
		global $ilUser;
		
		if($_GET["ass"])
		{		
			include_once "Modules/Exercise/classes/class.ilExAssignment.php";			
			$ass = new ilExAssignment((int)$_GET["ass"]);
			
			$submitted = ilExAssignment::getDeliveredFiles($ass->getExerciseId(), $ass->getId(), $ilUser->getId());
			if (count($submitted) > 0)
			{
				$submitted = array_pop($submitted);			
				
				$user_data = ilObjUser::_lookupName($submitted["user_id"]);
				$title = ilObject::_lookupTitle($submitted["obj_id"])." - ".
					$ass->getTitle()." - ".
					$user_data["firstname"]." ".
					$user_data["lastname"]." (".
					$user_data["login"].").zip";
									
				ilUtil::deliverFile($submitted["filename"], $title);																	
			}
		}					
	}

	/**
	 * Return embeddable HTML chunk
	 * 
	 * @return string 
	 */	
	function getHTML()
	{				
		// getHTML() is called by ilRepositoryGUI::show()
		if($this->id_type == self::REPOSITORY_NODE_ID)
		{
			return;
		}
		
		// there is no way to do a permissions check here, we have no wsp
		
		$this->filterInactivePostings();
		
		$list_items = $this->getListItems();		
		
		$list = $nav = "";
		if($list_items)
		{				
			$list = $this->renderList($list_items, "previewEmbedded");
			$nav = $this->renderNavigation($this->items, "gethtml", "previewEmbedded");
		}		
		
		return $this->buildEmbedded($list, $nav);		
	}
	
	/**
	 * Filter blog postings by month, keyword or author
	 * 
	 * @param bool $a_show_inactive
	 * @return array
	 */
	protected function getListItems($a_show_inactive = false)
	{
		if($this->author)
		{
			$list_items = array();
			foreach($this->items as $month => $items)
			{
				foreach($items as $id => $item)
				{
					if($item["author"] == $this->author)
					{
						$list_items[$id] = $item;
					}
				}
			}
		}
		else if($this->keyword)
		{
			$list_items = $this->filterItemsByKeyword($this->items, $this->keyword);
		}
		else
		{
			$max = $this->object->getOverviewPostings();		
			if($this->month_default && $max)
			{
				$list_items = array();
				foreach($this->items as $month => $postings)
				{
					foreach($postings as $id => $item)
					{
						if(!$a_show_inactive && 
							!ilBlogPosting::_lookupActive($id, "blp"))
						{
							continue;
						}
						$list_items[$id] = $item;
						
						if(sizeof($list_items) >= $max)
						{
							break(2);
						}
					}
				}
			}
			else
			{
				$list_items = $this->items[$this->month];
			}
		}
		return $list_items;
	}
	
	/**
	 * Render fullscreen presentation
	 */
	function preview()
	{		
		global $lng;
		
		if(!$this->checkPermissionBool("read"))
		{
			ilUtil::sendInfo($lng->txt("no_permission"));
			return;
		}

		$this->filterInactivePostings();
		
		$list_items = $this->getListItems();
		
		$list = $nav = "";		
		if($list_items)
		{									
			$list = $this->renderList($list_items, "previewFullscreen");
			$nav = $this->renderNavigation($this->items, "preview", "previewFullscreen");		
		}
						
		$this->renderFullScreen($list, $nav);
	}
		
	/**
	 * Build and deliver export file 	 
	 */
	function export()
	{
		$zip = $this->buildExportFile();
		
	    ilUtil::deliverFile($zip, $this->object->getTitle().".zip", '', false, true);
	}
	
	
	// --- helper functions 
	
	/**
	 * Combine content (list/posting) and navigation to html chunk
	 * 
	 * @param string $a_content
	 * @param string $a_nav
	 * @return string
	 */
	protected function buildEmbedded($a_content, $a_nav)
	{
		$wtpl = new ilTemplate("tpl.blog_embedded.html", true, true, "Modules/Blog");
		$wtpl->setVariable("VAL_LIST", $a_content);
		$wtpl->setVariable("VAL_NAVIGATION", $a_nav);							
		return $wtpl->get();
	}
	
	/**
	 * Build fullscreen context
	 * 
	 * @param string $a_content
	 * @param string $a_navigation
	 */	
	function renderFullScreen($a_content, $a_navigation)
	{
		global $tpl, $ilUser, $ilTabs, $ilLocator;
		
		$owner = $this->object->getOwner();
		
		$ilTabs->clearTargets();
		$ilLocator->clearItems();
		$tpl->setLocator();
		
		// back (edit)
		if($owner == $ilUser->getId())
		{					
			// from shared/deeplink		
			if($this->id_type == self::WORKSPACE_NODE_ID)
			{	
				$back = "ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToWorkspace&wsp_id=".$this->node_id;
			}
			// from editor (#10073)
			else if($this->mayContribute())
			{
				$prvm = $_GET["prvm"];
				$this->ctrl->setParameter($this, "prvm", "");				
				if(!$_GET["blpg"])
				{								
					$back = $this->ctrl->getLinkTarget($this, "");
				}
				else
				{
					$this->ctrl->setParameterByClass("ilblogpostinggui", "bmn", $this->month);
					$this->ctrl->setParameterByClass("ilblogpostinggui", "blpg", $_GET["blpg"]);
					$back = $this->ctrl->getLinkTargetByClass("ilblogpostinggui", "preview");
				}
				$this->ctrl->setParameter($this, "prvm", $prvm);
			}
		}
		// back 
		else if($ilUser->getId() && $ilUser->getId() != ANONYMOUS_USER_ID)
		{		
			// workspace (always shared)
			if($this->id_type == self::WORKSPACE_NODE_ID)
			{	
				$back = "ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToWorkspace&dsh=".$owner;
			}
			// contributor
			else if($this->mayContribute())
			{
				$back = $this->ctrl->getLinkTarget($this, "");
			}
			// listgui / parent container
			else
			{
				global $tree;
				$parent_id = $tree->getParentId($this->node_id);
				include_once "Services/Link/classes/class.ilLink.php";
				$back = ilLink::_getStaticLink($parent_id);
			}
		}				
		
		global $ilMainMenu;
		$ilMainMenu->setMode(ilMainMenuGUI::MODE_TOPBAR_ONLY);		
		$ilMainMenu->setTopBarBack($back);
		
		$this->renderFullscreenHeader($tpl, $owner);
			
		// #13564
		$this->ctrl->setParameter($this, "bmn", "");
		$tpl->setTitleUrl($this->ctrl->getLinkTarget($this, "preview")); 
		$this->ctrl->setParameter($this, "bmn", $this->month);
				
		$this->setContentStyleSheet();		
	
		// content
		$tpl->setContent($a_content);
		$tpl->setRightContent($a_navigation);		
	}
	
	/**
	 * Render banner, user name
	 * 
	 * @param object  $a_tpl
	 * @param int $a_user_id 
	 * @param bool $a_export_path
	 */
	protected function renderFullscreenHeader($a_tpl, $a_user_id, $a_export = false)
	{
		// repository blogs are multi-author 
		$name = null;
		if($this->id_type != self::REPOSITORY_NODE_ID)
		{
			$name = ilObjUser::_lookupName($a_user_id);
			$name = $name["lastname"].", ".($t = $name["title"] ? $t . " " : "").$name["firstname"];
		}
		
		// show banner?
		$banner = false;
		$blga_set = new ilSetting("blga");
		if($blga_set->get("banner"))
		{		
			$banner = $this->object->getImageFullPath();
			$banner_width = $blga_set->get("banner_width");
			$banner_height = $blga_set->get("banner_height");		
			if($a_export)
			{
				$banner = basename($banner);
			}
		}
		
		$ppic = null;
		if($this->object->hasProfilePicture())
		{			
			// repository (multi-user)
			if($this->id_type == self::REPOSITORY_NODE_ID)
			{				
				// #15030
				if($_GET["blpg"] && !$a_export)
				{										
					include_once "Modules/Blog/classes/class.ilBlogPosting.php";
					$post = new ilBlogPosting($_GET["blpg"]);
					$author_id = $post->getAuthor();		
					if($author_id)
					{
						$ppic = ilObjUser::_getPersonalPicturePath($author_id, "xsmall", true, true);		
						
						$name = ilObjUser::_lookupName($author_id);
						$name = $name["lastname"].", ".($t = $name["title"] ? $t . " " : "").$name["firstname"];
					}
				}
			}
			// workspace (author == owner)
			else 
			{
				$ppic = ilObjUser::_getPersonalPicturePath($a_user_id, "xsmall", true, true);
				if($a_export)
				{
					$ppic = basename($ppic);
				}
			}			
		}
		
		$a_tpl->resetHeaderBlock(false);
		// $a_tpl->setBackgroundColor($this->object->getBackgroundColor());
		$a_tpl->setBanner($banner, $banner_width, $banner_height, $a_export);
		$a_tpl->setTitleIcon($ppic);
		$a_tpl->setTitle($this->object->getTitle());
		// $a_tpl->setTitleColor($this->object->getFontColor());		
		$a_tpl->setDescription($name);		
		
		// to get rid of locator in repository preview
		$a_tpl->setVariable("LOCATOR", "");
		
		// :TODO: obsolete?
		// $a_tpl->setBodyClass("std ilExternal ilBlog");		
	}
	
	/**
	 * Gather all blog postings
	 * 
	 * @param int $a_obj_id
	 * @return array
	 */
	protected function buildPostingList($a_obj_id)
	{
		$author_found = false;	
		
		$items = array();
		foreach(ilBlogPosting::getAllPostings($a_obj_id) as $posting)
		{
			if($this->author && $posting["author"] == $this->author)
			{
				$author_found = true;
			}
			
			$month = substr($posting["created"]->get(IL_CAL_DATE), 0, 7);
			$items[$month][$posting["id"]] = $posting;
		}
		
		if($this->author && !$author_found)
		{
			$this->author = null;
		}
		
		return $items;
	}
	
	/**
	 * Build posting month list
	 * 
	 * @param array $items
	 * @param string $a_cmd
	 * @param bool $a_link_template
	 * @param bool $a_show_inactive
	 * @param string $a_export_directory
	 * @return string 
	 */
	function renderList(array $items, $a_cmd = "preview", $a_link_template = null, $a_show_inactive = false, $a_export_directory = null)
	{
		global $lng, $ilCtrl;
		
		include_once "Services/Calendar/classes/class.ilCalendarUtil.php";
		$wtpl = new ilTemplate("tpl.blog_list.html", true, true, "Modules/Blog");
		
		// quick editing in portfolio
		if($_REQUEST["prt_id"] && 
			stristr($a_cmd, "embedded"))
		{		
			global $ilUser;
			if(ilObject::_lookupOwner($_REQUEST["prt_id"]) == $ilUser->getId())
			{				
				// see ilPortfolioPageTableGUI::fillRow()
				$ilCtrl->setParameterByClass("ilportfoliopagegui", "ppage", (int)$_REQUEST["user_page"]);
				$link = $ilCtrl->getLinkTargetByClass(array("ilportfoliopagegui", "ilobjbloggui"), "render");
				$ilCtrl->setParameterByClass("ilportfoliopagegui", "ppage", "");
				
				$wtpl->setCurrentBlock("prtf_edit_bl");
				$wtpl->setVariable("PRTF_BLOG_URL", $link);
				$wtpl->setVariable("PRTF_BLOG_TITLE", sprintf($lng->txt("prtf_edit_embedded_blog"), $this->object->getTitle()));
				$wtpl->parseCurrentBlock();
			}
		}
										
		$can_approve = ($this->object->hasApproval() && $this->checkPermissionBool("write"));
		$can_deactivate = $this->checkPermissionBool("write");
		
		include_once("./Modules/Blog/classes/class.ilBlogPostingGUI.php");	
		$last_month = null;
		foreach($items as $item)
		{			
			// only published items
			$is_active = ilBlogPosting::_lookupActive($item["id"], "blp");
			if(!$is_active && !$a_show_inactive)
			{
				continue;
			}
			
			if(!$this->keyword && !$this->author)
			{
				$month = substr($item["created"]->get(IL_CAL_DATE), 0, 7);				
			}
			
			if(!$last_month || $last_month != $month)
			{
				if($last_month)
				{
					$wtpl->setCurrentBlock("month_bl");
					$wtpl->parseCurrentBlock();
				}
								
				// title according to current "filter"/navigation
				if($this->keyword)
				{
					$title = $lng->txt("blog_keyword").": ".$this->keyword;
				}
				else if($this->author)
				{
					include_once "Services/User/classes/class.ilUserUtil.php";
					$title = $lng->txt("blog_author").": ".ilUserUtil::getNamePresentation($this->author);
				}
				else
				{
					include_once "Services/Calendar/classes/class.ilCalendarUtil.php";
					$title = ilCalendarUtil::_numericMonthToString((int)substr($month, 5)).
							" ".substr($month, 0, 4);
					
					$last_month = $month;
				}	
								
				$wtpl->setVariable("TXT_CURRENT_MONTH", $title);					
			}
			
			if(!$a_link_template)
			{
				$ilCtrl->setParameterByClass("ilblogpostinggui", "bmn", $this->month);
				$ilCtrl->setParameterByClass("ilblogpostinggui", "blpg", $item["id"]);
				$preview = $ilCtrl->getLinkTargetByClass("ilblogpostinggui", $a_cmd);
			}
			else
			{
				$preview = $this->buildExportLink($a_link_template, "posting", $item["id"]);
			}

			// actions					
			$item_contribute = $this->mayContribute($item["id"], $item["author"]);
			if(($item_contribute || $can_approve || $can_deactivate) && !$a_link_template && $a_cmd == "preview")
			{
				include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
				$alist = new ilAdvancedSelectionListGUI();
				$alist->setId($item["id"]);
				$alist->setListTitle($lng->txt("actions"));
												
				if($is_active && $this->object->hasApproval() && !$item["approved"])
				{
					if($can_approve)
					{
						$ilCtrl->setParameter($this, "apid", $item["id"]);
						$alist->addItem($lng->txt("blog_approve"), "approve", 
							$ilCtrl->getLinkTarget($this, "approve"));
						$ilCtrl->setParameter($this, "apid", "");
					}
					
					$wtpl->setVariable("APPROVAL", $lng->txt("blog_needs_approval"));
				}
				
				if($item_contribute)
				{
					$alist->addItem($lng->txt("edit_content"), "edit", 
						$ilCtrl->getLinkTargetByClass("ilblogpostinggui", "edit"));
					
					// #11858
					if($is_active)
					{
						$alist->addItem($lng->txt("blog_toggle_draft"), "deactivate", 
							$ilCtrl->getLinkTargetByClass("ilblogpostinggui", "deactivatePageToList"));
					}
					else
					{
						$alist->addItem($lng->txt("blog_toggle_final"), "activate", 
							$ilCtrl->getLinkTargetByClass("ilblogpostinggui", "activatePageToList"));
					}
					
					$alist->addItem($lng->txt("rename"), "rename", 
						$ilCtrl->getLinkTargetByClass("ilblogpostinggui", "edittitle"));
					
					if($this->object->hasKeywords()) // #13616
					{
						$alist->addItem($lng->txt("blog_edit_keywords"), "keywords", 
							$ilCtrl->getLinkTargetByClass("ilblogpostinggui", "editKeywords"));		
					}
					
					$alist->addItem($lng->txt("blog_edit_date"), "editdate", 
						$ilCtrl->getLinkTargetByClass("ilblogpostinggui", "editdate"));
					$alist->addItem($lng->txt("delete"), "delete",
						$ilCtrl->getLinkTargetByClass("ilblogpostinggui", "deleteBlogPostingConfirmationScreen"));				
				}
				else if($can_deactivate)
				{
					// #10513				
					if($is_active)
					{					
						$ilCtrl->setParameter($this, "apid", $item["id"]);
						$alist->addItem($lng->txt("blog_toggle_draft_admin"), "deactivate", 
							$ilCtrl->getLinkTarget($this, "deactivateAdmin"));
						$ilCtrl->setParameter($this, "apid", "");
					}
					
					$alist->addItem($lng->txt("delete"), "delete",
						$ilCtrl->getLinkTargetByClass("ilblogpostinggui", "deleteBlogPostingConfirmationScreen"));		
				}

				$wtpl->setCurrentBlock("actions");
				$wtpl->setVariable("ACTION_SELECTOR", $alist->getHTML());
				$wtpl->parseCurrentBlock();
			}
			
			// comments
			if($this->object->getNotesStatus() && !$a_link_template && !$this->disable_notes)
			{
				// count (public) notes
				include_once("Services/Notes/classes/class.ilNote.php");
				$count = sizeof(ilNote::_getNotesOfObject($this->obj_id, 
					$item["id"], "blp", IL_NOTE_PUBLIC));
				
				if($a_cmd != "preview")
				{
					$wtpl->setCurrentBlock("comments");
					$wtpl->setVariable("TEXT_COMMENTS", $lng->txt("blog_comments"));
					$wtpl->setVariable("URL_COMMENTS", $preview);
					$wtpl->setVariable("COUNT_COMMENTS", $count);
					$wtpl->parseCurrentBlock();
				}
				/* we disabled comments in edit mode (should always be done via pagegui)
				else
				{
					$hash = ilCommonActionDispatcherGUI::buildAjaxHash(ilCommonActionDispatcherGUI::TYPE_WORKSPACE, 
						$this->node_id, "blog", $this->obj_id, "blp", $item["id"]);
					$notes_link = "#\" onclick=\"".ilNoteGUI::getListCommentsJSCall($hash);
				}
				*/				
			}
							
			// permanent link
			if($a_cmd != "preview" && $a_cmd != "previewEmbedded")
			{
				if($this->id_type == self::WORKSPACE_NODE_ID)
				{				
					$goto = $this->getAccessHandler()->getGotoLink($this->node_id, $this->obj_id, "_".$item["id"]);
				}
				else
				{
					include_once "Services/Link/classes/class.ilLink.php";
					$goto = ilLink::_getStaticLink($this->node_id, $this->getType(), true, "_".$item["id"]);
				}
				$wtpl->setCurrentBlock("permalink");
				$wtpl->setVariable("URL_PERMALINK", $goto); 
				$wtpl->setVariable("TEXT_PERMALINK", $lng->txt("blog_permanent_link"));
				$wtpl->parseCurrentBlock();				
			}
						
			$snippet = ilBlogPostingGUI::getSnippet($item["id"], 
				$this->object->hasAbstractShorten(),
				$this->object->getAbstractShortenLength(),
				"&hellip;",
				$this->object->hasAbstractImage(),
				$this->object->getAbstractImageWidth(),
				$this->object->getAbstractImageHeight(),
				$a_export_directory);	
			
			if($snippet)
			{
				$wtpl->setCurrentBlock("more");
				$wtpl->setVariable("URL_MORE", $preview); 
				$wtpl->setVariable("TEXT_MORE", $lng->txt("blog_list_more"));
				$wtpl->parseCurrentBlock();
			}
			
			$wtpl->setCurrentBlock("posting");
			
			if(!$is_active)
			{
				$wtpl->setVariable("DRAFT_CLASS", " ilBlogListItemDraft");
			}
			
			$author = "";
			if($this->id_type == self::REPOSITORY_NODE_ID)
			{				
				$author_id = $item["author"];
				if($author_id)
				{
					include_once "Services/User/classes/class.ilUserUtil.php";
					$author = ilUserUtil::getNamePresentation($author_id)." - ";
				}				
			}
			
			// title
			$wtpl->setVariable("URL_TITLE", $preview);
			$wtpl->setVariable("TITLE", $item["title"]);
			$wtpl->setVariable("DATETIME", $author.
				ilDatePresentation::formatDate($item["created"], IL_CAL_DATE));		

			// content			
			$wtpl->setVariable("CONTENT", $snippet);			

			$wtpl->parseCurrentBlock();
		}
		
		// permalink
		if($a_cmd == "previewFullscreen")
		{			
			$this->tpl->setPermanentLink("blog", $this->node_id, 
				($this->id_type == self::WORKSPACE_NODE_ID)
				? "_wsp"
				: "");			
		}
		
		return $wtpl->get();
	}
	
	/**
	 * Build navigation by date block
	 *
	 * @param array $a_items
	 * @param string $a_list_cmd
	 * @param string $a_posting_cmd
	 * @param bool $a_link_template
	 * @param bool $a_show_inactive
	 * @return string
	 */
	protected function renderNavigationByDate(array $a_items, $a_list_cmd = "render", $a_posting_cmd = "preview", $a_link_template = null, $a_show_inactive = false)
	{
		global $ilCtrl;
			
		// gather page active status
		foreach($a_items as $month => $postings)
		{
			foreach(array_keys($postings) as $id)
			{				
				$active = ilBlogPosting::_lookupActive($id, "blp");			
				if(!$a_show_inactive && !$active)
				{			
					unset($a_items[$month][$id]);
				}
				else
				{
					$a_items[$month][$id]["active"] = $active;
				}
			}
			if(!sizeof($a_items[$month]))
			{
				unset($a_items[$month]);
			}
		}	
		
		// list month (incl. postings)
		if($this->object->getNavMode() == ilObjBlog::NAV_MODE_LIST || $a_link_template)
		{				
			$max_detail_postings = $this->object->getNavModeListPostings();
			$max_months = $this->object->getNavModeListMonths();

			$wtpl = new ilTemplate("tpl.blog_list_navigation_by_date.html", true, true, "Modules/Blog");

			$ilCtrl->setParameter($this, "blpg", "");

			include_once "Services/Calendar/classes/class.ilCalendarUtil.php";
			$counter = $mon_counter = $last_year = 0;
			foreach($a_items as $month => $postings)
			{			
				if(!$a_link_template && $max_months && $mon_counter >= $max_months)
				{
					break;
				}
				
				$add_year = false;
				$year = substr($month, 0, 4);
				if(!$last_year || $year != $last_year)
				{
					// #13562
					$add_year = true;
					$last_year = $year;
				}
				
				$mon_counter++;

				$month_name = ilCalendarUtil::_numericMonthToString((int)substr($month, 5));

				if(!$a_link_template)
				{				
					$ilCtrl->setParameter($this, "bmn", $month);
					$month_url = $ilCtrl->getLinkTarget($this, $a_list_cmd);
				}
				else
				{
					$month_url = $this->buildExportLink($a_link_template, "list", $month);
				}

				// list postings for month
				if($counter < $max_detail_postings)
				{													
					if($add_year)
					{
						$wtpl->setCurrentBlock("navigation_year_details");
						$wtpl->setVariable("YEAR", $year);
						$wtpl->parseCurrentBlock();			
					}				
					
					foreach($postings as $id => $posting)
					{						
						if($max_detail_postings && $counter >= $max_detail_postings)
						{
							break;
						}

						$counter++;

						$caption = /* ilDatePresentation::formatDate($posting["created"], IL_CAL_DATETIME).
							", ".*/ $posting["title"];

						if(!$a_link_template)
						{
							$ilCtrl->setParameterByClass("ilblogpostinggui", "bmn", $month);
							$ilCtrl->setParameterByClass("ilblogpostinggui", "blpg", $id);
							$url = $ilCtrl->getLinkTargetByClass("ilblogpostinggui", $a_posting_cmd);					
						}
						else
						{
							$url = $this->buildExportLink($a_link_template, "posting", $id);
						}					

						if(!$posting["active"])
						{
							$wtpl->setVariable("NAV_ITEM_DRAFT", $this->lng->txt("blog_draft"));
						}			
						else if($this->object->hasApproval() && !$posting["approved"])
						{
							$wtpl->setVariable("NAV_ITEM_APPROVAL", $this->lng->txt("blog_needs_approval"));
						}

						$wtpl->setCurrentBlock("navigation_item");
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
					if($add_year)
					{
						$wtpl->setCurrentBlock("navigation_year");
						$wtpl->setVariable("YEAR", $year);
						$wtpl->parseCurrentBlock();			
					}		
					
					$wtpl->setCurrentBlock("navigation_month");
					$wtpl->setVariable("MONTH_NAME", $month_name);				
					$wtpl->setVariable("URL_MONTH", $month_url);
					$wtpl->setVariable("MONTH_COUNT", sizeof($postings));
					$wtpl->parseCurrentBlock();
				}
			}

			$ilCtrl->setParameter($this, "bmn", $this->month);
			$ilCtrl->setParameterByClass("ilblogpostinggui", "bmn", "");

			return $wtpl->get();
		}
		// single month
		else
		{
			$wtpl = new ilTemplate("tpl.blog_list_navigation_month.html", true, true, "Modules/Blog");

			$ilCtrl->setParameter($this, "blpg", "");

			include_once "Services/Calendar/classes/class.ilCalendarUtil.php";
			$month_options = array();
			foreach($a_items as $month => $postings)
			{			
				$month_name = ilCalendarUtil::_numericMonthToString((int)substr($month, 5)).
					" ".substr($month, 0, 4);

				$month_options[$month] = $month_name;
				
				if($month == $this->month)
				{
					if(!$a_link_template)
					{				
						$ilCtrl->setParameter($this, "bmn", $month);
						$month_url = $ilCtrl->getLinkTarget($this, $a_list_cmd);
					}
					else
					{
						$month_url = $this->buildExportLink($a_link_template, "list", $month);
					}
					
					foreach($postings as $id => $posting)
					{												
						$caption = /* ilDatePresentation::formatDate($posting["created"], IL_CAL_DATETIME).
							", ".*/ $posting["title"];

						if(!$a_link_template)
						{
							$ilCtrl->setParameterByClass("ilblogpostinggui", "bmn", $month);
							$ilCtrl->setParameterByClass("ilblogpostinggui", "blpg", $id);
							$url = $ilCtrl->getLinkTargetByClass("ilblogpostinggui", $a_posting_cmd);					
						}
						else
						{
							$url = $this->buildExportLink($a_link_template, "posting", $id);
						}					

						if(!$posting["active"])
						{
							$wtpl->setVariable("NAV_ITEM_DRAFT", $this->lng->txt("blog_draft"));
						}			
						else if($this->object->hasApproval() && !$posting["approved"])
						{
							$wtpl->setVariable("NAV_ITEM_APPROVAL", $this->lng->txt("blog_needs_approval"));
						}

						$wtpl->setCurrentBlock("navigation_item");
						$wtpl->setVariable("NAV_ITEM_URL", $url);
						$wtpl->setVariable("NAV_ITEM_CAPTION", $caption);
						$wtpl->parseCurrentBlock();							
					}

					$wtpl->setCurrentBlock("navigation_month_details");
					if($_GET["blpg"])
					{
						$wtpl->setVariable("NAV_MONTH", $month_name);
						$wtpl->setVariable("URL_MONTH", $month_url);
					}
					$wtpl->parseCurrentBlock();					
				}	
			}
			
			if(!$_GET["blpg"])
			{
				$wtpl->setCurrentBlock("option_bl");
				foreach($month_options as $value => $caption)
				{
					$wtpl->setVariable("OPTION_VALUE", $value);
					$wtpl->setVariable("OPTION_CAPTION", $caption);
					if($value == $this->month)
					{
						$wtpl->setVariable("OPTION_SEL", ' selected="selected"');
					}
					$wtpl->parseCurrentBlock();			
				}
				
				$wtpl->setVariable("FORM_ACTION", $ilCtrl->getFormAction($this, $a_list_cmd));
			}
			
			$ilCtrl->setParameter($this, "bmn", $this->month);
			$ilCtrl->setParameterByClass("ilblogpostinggui", "bmn", "");
			
			return $wtpl->get();
		}
	}
	
	/**
	 * Build navigation by keywords block 
	 *
	 * @param string $a_list_cmd
	 * @param bool $a_show_inactive
	 * @return string
	 */
	protected function renderNavigationByKeywords($a_list_cmd = "render", $a_show_inactive = false)
	{
		global $ilCtrl;
		
		$keywords = $this->getKeywords($a_show_inactive, $_GET["blpg"]);
		if($keywords)
		{		
			$wtpl = new ilTemplate("tpl.blog_list_navigation_keywords.html", true, true, "Modules/Blog");
							
			$max = max($keywords);
			include_once "Services/Tagging/classes/class.ilTagging.php";

			$wtpl->setCurrentBlock("keyword");
			foreach($keywords as $keyword => $counter)
			{										
				$ilCtrl->setParameter($this, "kwd", $keyword);
				$url = $ilCtrl->getLinkTarget($this, $a_list_cmd);
				$ilCtrl->setParameter($this, "kwd", "");

				$wtpl->setVariable("TXT_KEYWORD", $keyword);				
				$wtpl->setVariable("CLASS_KEYWORD", ilTagging::getRelevanceClass($counter, $max));				
				$wtpl->setVariable("URL_KEYWORD", $url);
				$wtpl->parseCurrentBlock();					
			}			
		
			return $wtpl->get();
		}		
	}
		
	protected function renderNavigationByAuthors(array $a_items, $a_list_cmd = "render", $a_show_inactive = false)
	{
		global $ilCtrl;
		
		$authors = array();
		foreach($a_items as $month => $items)
		{
			foreach($items as $item)
			{
				if(($a_show_inactive || ilBlogPosting::_lookupActive($item["id"], "blp")) && $item["author"])
				{
					$authors[] = $item["author"];					
				}	
			}
		}			

		$authors = array_unique($authors);			
		if(sizeof($authors) > 1)
		{					
			include_once "Services/User/classes/class.ilUserUtil.php";

			$list = array();
			foreach($authors as $user_id)
			{								
				if($user_id)
				{
					$ilCtrl->setParameter($this, "ath", $user_id);
					$url = $ilCtrl->getLinkTarget($this, $a_list_cmd);
					$ilCtrl->setParameter($this, "ath", "");

					$name = ilUserUtil::getNamePresentation($user_id, true);
					$idx = trim(strip_tags($name))."///".$user_id;  // #10934
					$list[$idx] = array($name, $url);	
				}
			}
			ksort($list);
			
			$wtpl = new ilTemplate("tpl.blog_list_navigation_authors.html", true, true, "Modules/Blog");
			
			$wtpl->setCurrentBlock("author");				
			foreach($list as $author)
			{
				$wtpl->setVariable("TXT_AUTHOR", $author[0]);							
				$wtpl->setVariable("URL_AUTHOR", $author[1]);							
				$wtpl->parseCurrentBlock();		
			}
			
			return $wtpl->get();
		}
	}
	
	/**
	 * Build navigation blocks
	 *
	 * @param array $a_items
	 * @param string $a_list_cmd
	 * @param string $a_posting_cmd
	 * @param bool $a_link_template
	 * @param bool $a_show_inactive
	 * @return string
	 */
	function renderNavigation(array $a_items, $a_list_cmd = "render", $a_posting_cmd = "preview", $a_link_template = null, $a_show_inactive = false)
	{
		global $ilCtrl, $ilSetting;
		
		if($this->object->getOrder())
		{
			$order = array_flip($this->object->getOrder());
		}
		else
		{
			$order = array(
				"navigation" => 0
				,"keywords" => 2
				,"authors" => 1
			);
		}
				
		$wtpl = new ilTemplate("tpl.blog_list_navigation.html", true, true, "Modules/Blog");
			
		$blocks = array();
		
		// by date
		if(sizeof($a_items))
		{			
			$blocks[$order["navigation"]] = array(
				$this->lng->txt("blog_navigation"),
				$this->renderNavigationByDate($a_items, $a_list_cmd, $a_posting_cmd, $a_link_template, $a_show_inactive)
			);
		}
				
		// authors
		if($this->id_type == self::REPOSITORY_NODE_ID && 
			$this->object->hasAuthors())
		{
			$authors = $this->renderNavigationByAuthors($a_items, $a_list_cmd, $a_show_inactive);
			if($authors)
			{
				$blocks[$order["authors"]] = array($this->lng->txt("blog_authors"), $authors);
			}
		}		
		
		// is not part of (html) export
		if(!$a_link_template)
		{
			if($this->object->hasKeywords())
			{
				// keywords 		
				$may_edit_keywords = ($_GET["blpg"] && 
					$this->mayContribute($_GET["blpg"]) && 
					$a_list_cmd != "preview" && 
					$a_list_cmd != "gethtml");
				$keywords = $this->renderNavigationByKeywords($a_list_cmd, $a_show_inactive);	
				if($keywords || $may_edit_keywords)
				{
					if(!$keywords)
					{
						$keywords = $this->lng->txt("blog_no_keywords");
					}
					$cmd = null;
					if($may_edit_keywords)
					{
						$ilCtrl->setParameterByClass("ilblogpostinggui", "blpg", $_GET["blpg"]);				
						$cmd = 	$ilCtrl->getLinkTargetByClass("ilblogpostinggui", "editKeywords");	
						$ilCtrl->setParameterByClass("ilblogpostinggui", "blpg", "");
					}
					$blocks[$order["keywords"]] = array(
						$this->lng->txt("blog_keywords"),
						$keywords,
						$cmd 
							? array($cmd, $this->lng->txt("blog_edit_keywords")) 
							: null
					);
				}
			}

			// rss
			if($this->object->hasRSS() && 
				$ilSetting->get('enable_global_profiles') &&
				$a_list_cmd == "preview")
			{
				// #10827
				$blog_id = $this->node_id;		
				if($this->id_type != self::WORKSPACE_NODE_ID)
				{	
					$blog_id .= "_cll";
				}			
				$url = ILIAS_HTTP_PATH."/feed.php?blog_id=".$blog_id.
					"&client_id=".rawurlencode(CLIENT_ID);

				include_once("./Services/News/classes/class.ilRSSButtonGUI.php");
				$wtpl->setVariable("RSS_BUTTON", ilRSSButtonGUI::get(ilRSSButtonGUI::ICON_RSS, $url));
			}
		}
		
		if(sizeof($blocks))
		{			
			include_once "Services/UIComponent/Panel/classes/class.ilPanelGUI.php";
			
			ksort($blocks);
			foreach($blocks as $block)
			{
				$panel = ilPanelGUI::getInstance();
				$panel->setPanelStyle(ilPanelGUI::PANEL_STYLE_SECONDARY);
				$panel->setHeadingStyle(ilPanelGUI::HEADING_STYLE_BLOCK);
				$panel->setHeading($block[0]);
				$panel->setBody($block[1]);
				
				if(isset($block[2]) && is_array($block[2]))
				{										
					$panel->setFooter('<a href="'.$block[2][0].'">'.$block[2][1].'</a>');
				}
				
				$wtpl->setCurrentBlock("block_bl");		
				$wtpl->setVariable("BLOCK", $panel->getHTML());
				$wtpl->parseCurrentBlock();
			}
		}
		
		return $wtpl->get();
	}
	
	/**
	 * Get keywords for single posting or complete blog
	 * 
	 * @param bool $a_show_inactive
	 * @param int $a_posting_id
	 * @return array
	 */
	function getKeywords($a_show_inactive, $a_posting_id = null)
	{								
		$keywords = array();
		include_once("./Modules/Blog/classes/class.ilBlogPosting.php");
		if($a_posting_id)
		{						
			foreach(ilBlogPosting::getKeywords($this->obj_id, $a_posting_id) as $keyword)
			{
				$keywords[$keyword]++;
			}
		}
		else
		{							
			foreach($this->items as $month => $items)
			{
				foreach($items as $item)
				{
					if($a_show_inactive || ilBlogPosting::_lookupActive($item["id"], "blp"))
					{					
						foreach(ilBlogPosting::getKeywords($this->obj_id, $item["id"]) as $keyword)
						{
							$keywords[$keyword]++;							
						}
					}
				}
			}									
		}
		ksort($keywords);
		return $keywords;
	}
	
	/**
	 * Build export file
	 *
	 * @return string
	 */
	function buildExportFile()
	{
		// create export file
		include_once("./Services/Export/classes/class.ilExport.php");
		ilExport::_createExportDirectory($this->object->getId(), "html", "blog");
		$exp_dir = ilExport::_getExportDirectory($this->object->getId(), "html", "blog");

		$subdir = $this->object->getType()."_".$this->object->getId();
		$export_dir = $exp_dir."/".$subdir;

		// initialize temporary target directory
		ilUtil::delDir($export_dir);
		ilUtil::makeDir($export_dir);
		
		// system style html exporter
		include_once("./Services/Style/classes/class.ilSystemStyleHTMLExport.php");
		$this->sys_style_html_export = new ilSystemStyleHTMLExport($export_dir);
	    $this->sys_style_html_export->addImage("icon_blog.svg");
		$this->sys_style_html_export->export();
		
		// init co page html exporter
		include_once("./Services/COPage/classes/class.ilCOPageHTMLExport.php");
		$this->co_page_html_export = new ilCOPageHTMLExport($export_dir);
		$this->co_page_html_export->setContentStyleId($this->object->getStyleSheetId());
		$this->co_page_html_export->createDirectories();
		$this->co_page_html_export->exportStyles();
		$this->co_page_html_export->exportSupportScripts();	
		
		// banner / profile picture
		$blga_set = new ilSetting("blga");
		if($blga_set->get("banner"))
		{		
			$banner = $this->object->getImageFullPath();
			if($banner)
			{
				copy($banner, $export_dir."/".basename($banner));
			}
		}
		$ppic = ilObjUser::_getPersonalPicturePath($this->object->getOwner(), "xsmall", true, true);
		if($ppic)
		{
			$ppic = array_shift(explode("?", $ppic));
			copy($ppic, $export_dir."/".basename($ppic));
		}	

		// export pages
		$this->exportHTMLPages($export_dir);

		// zip everything
		if (true)
		{
			// zip it all
			$date = time();
			$zip_file = ilExport::_getExportDirectory($this->object->getId(), "html", "blog").
				"/".$date."__".IL_INST_ID."__".
				$this->object->getType()."_".$this->object->getId().".zip";
			ilUtil::zip($export_dir, $zip_file);
			ilUtil::delDir($export_dir);
		}
		
		return $zip_file;
	}

	/**
	 * Export all pages
	 * 
	 * @param string $a_target_directory
	 * @param string $a_link_template (embedded)
	 * @param array $a_tpl_callback (embedded)
	 * @param object $a_co_page_html_export (embedded)
	 * @param string $a_index_name (embedded)
	 */
	function exportHTMLPages($a_target_directory, $a_link_template = null, $a_tpl_callback = null, $a_co_page_html_export = null, $a_index_name = "index.html")
	{					
		if(!$a_link_template)
		{
			$a_link_template = "bl{TYPE}_{ID}.html";
		}
		
		if($a_co_page_html_export)
		{
			$this->co_page_html_export = $a_co_page_html_export;
		}
		
		$nav = $this->renderNavigation($this->items, "", "", $a_link_template);
		
		// month list
		$has_index = false;
		foreach(array_keys($this->items) as $month)
		{									
			$file = $this->buildExportLink($a_link_template, "list", $month);
			$list = $this->renderList($this->items[$month], "render", $a_link_template, false, $a_target_directory);			
			
			if(!$a_tpl_callback)
			{
				$tpl = $this->buildExportTemplate();
			}
			else
			{
				$tpl = call_user_func($a_tpl_callback);				
			}		
			
			$file = $this->writeExportFile($a_target_directory, $file, 
				$tpl, $list, $nav);

			if(!$has_index)
			{
				copy($file, $a_target_directory."/".$a_index_name);
				$has_index = true;
			}
		}

		// single postings
		include_once("./Services/COPage/classes/class.ilPageContentUsage.php");
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		$pages = ilBlogPosting::getAllPostings($this->object->getId(), 0);		
		foreach ($pages as $page)
		{
			if (ilBlogPosting::_exists("blp", $page["id"]))
			{				
				include_once("./Modules/Blog/classes/class.ilBlogPostingGUI.php");
				$blp_gui = new ilBlogPostingGUI(0, null, $page["id"]);
				$blp_gui->setOutputMode("offline");
				$blp_gui->setFullscreenLink("fullscreen.html"); // #12930 - see page.xsl
				$blp_gui->add_date = true;
				$page_content = $blp_gui->showPage();
							
				$back = $this->buildExportLink($a_link_template, "list", 
					substr($page["created"]->get(IL_CAL_DATE), 0, 7));
				
				$file = $this->buildExportLink($a_link_template, "posting", $page["id"]);
								
				if(!$a_tpl_callback)
				{
					$tpl = $this->buildExportTemplate();
				}
				else
				{
					$tpl = call_user_func($a_tpl_callback);				
				}		
				
				$this->writeExportFile($a_target_directory, $file, $tpl, 
					$page_content, $nav, $back);
				
				$this->co_page_html_export->collectPageElements("blp:pg", $page["id"]);
			}
		}
		$this->co_page_html_export->exportPageElements();
	}
	
	/**
	 * Build static export link
	 * 
	 * @param string $a_template
	 * @param string $a_type
	 * @param mixed $a_id
	 * @return string
	 */
	protected function buildExportLink($a_template, $a_type, $a_id)
	{
		if($a_type == "list")
		{
			$a_type = "m";
		}
		else
		{
			$a_type = "p";
		}
		$link = str_replace("{TYPE}", $a_type, $a_template);
		return str_replace("{ID}", $a_id, $link);
	}
	
	/**
	 * Build export "frame"
	 * 
	 * @param type $a_back_url
	 * @return ilTemplate 
	 */
	protected function buildExportTemplate($a_back_url = "")
	{		
		global $ilTabs, $lng;
		
		$tpl = $this->co_page_html_export->getPreparedMainTemplate();
		
		$tpl->getStandardTemplate();
	
		$ilTabs->clearTargets();
		if($a_back_url)
		{			
			$ilTabs->setBackTarget($lng->txt("back"), $a_back_url);
		}
				
		$this->renderFullscreenHeader($tpl, $this->object->getOwner(), true);
		
		return $tpl;
	}
	
	/**
	 * Write HTML to file
	 * 
	 * @param type $a_target_directory
	 * @param type $a_file
	 * @param type $a_tpl
	 * @param type $a_content
	 * @param type $a_right_content
	 * @return string 
	 */
	protected function writeExportFile($a_target_directory, $a_file, $a_tpl, $a_content, $a_right_content = null)
	{
		$file = $a_target_directory."/".$a_file;
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
		
		$a_tpl->setContent($ep_tpl->get());		

		$content = $a_tpl->get("DEFAULT", false, false, false,
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
	 * Finalize and submit blog to exercise
	 */
	protected function finalize()
	{
		global $ilCtrl, $lng;
		
		// to make exercise gui load assignment
		$_GET["ass_id"] = $_REQUEST["ass"];
				
		// #11173 - ref_id is needed for notifications
		$exc_ref_id = array_shift(ilObject::_getAllReferences($_REQUEST["exc"]));
	
		include_once "Modules/Exercise/classes/class.ilObjExerciseGUI.php";
		$exc_gui = new ilObjExerciseGUI(null, $exc_ref_id, true);
		$exc_gui->submitBlog($this->node_id);
		
		ilUtil::sendSuccess($lng->txt("blog_finalized"), true);
		$ilCtrl->redirect($this, "render");
	}
	
	function getNotesSubId()
	{
		if($_REQUEST["blpg"])
		{
			return $_REQUEST["blpg"];
		}
		return 0;
	}
	
	function disableNotes($a_value = false)
	{
		$this->disable_notes = (bool)$a_value;
	}
		
	protected function addHeaderAction($a_cmd)
	{	
		global $ilUser, $ilCtrl;
		
		// preview?
		if($a_cmd == "preview" || $a_cmd == "previewFullscreen" || $_GET["prvm"])
		{						
			// notification
			if($ilUser->getId() != ANONYMOUS_USER_ID)			
			{			
				if(!$_GET["prvm"])
				{
					$ilCtrl->setParameter($this, "prvm", "fsc");
				}
				$this->insertHeaderAction($this->initHeaderAction(null, null, true));	
				if(!$_GET["prvm"])
				{
					$ilCtrl->setParameter($this, "prvm", "");
				}
			}
		}
		else
		{
			return parent::addHeaderAction();
		}
	}
	
	protected function initHeaderAction($sub_type = null, $sub_id = null, $a_is_preview = false)
	{
		global $ilUser, $ilCtrl;	
		
		if(!$this->obj_id)
		{
			return false;
		}

		$sub_type = $sub_id = null;
		if($_GET["blpg"])
		{
			$sub_type = "blp";
			$sub_id = $_GET["blpg"];
		}		
				
		$lg = parent::initHeaderAction($sub_type, $sub_id);
		
		if($a_is_preview)
		{
			$lg->enableComments(false);
			$lg->enableNotes(false);
			$lg->enableTags(false);		
			
			include_once "./Services/Notification/classes/class.ilNotification.php";
			if(ilNotification::hasNotification(ilNotification::TYPE_BLOG, $ilUser->getId(), $this->obj_id))
			{
				$ilCtrl->setParameter($this, "ntf", 1);
				$link = $ilCtrl->getLinkTarget($this, "setNotification");
				$ilCtrl->setParameter($this, "ntf", "");				
				$lg->addCustomCommand($link, "blog_notification_toggle_off");
				
				$lg->addHeaderIcon("not_icon",
					ilUtil::getImagePath("notification_on.svg"),
					$this->lng->txt("blog_notification_activated"));
			}
			else
			{
				$ilCtrl->setParameter($this, "ntf", 2);
				$link = $ilCtrl->getLinkTarget($this, "setNotification");
				$ilCtrl->setParameter($this, "ntf", "");
				$lg->addCustomCommand($link, "blog_notification_toggle_on");
				
				$lg->addHeaderIcon("not_icon",
					ilUtil::getImagePath("notification_off.svg"),
					$this->lng->txt("blog_notification_deactivated"));
			}
			
			// #11758			
			if($this->mayContribute())
			{
				$ilCtrl->setParameter($this, "prvm", "");
				
				$ilCtrl->setParameter($this, "bmn", "");
				$ilCtrl->setParameter($this, "blpg", "");
				$link = $ilCtrl->getLinkTarget($this, "");		
				$ilCtrl->setParameter($this, "blpg", $sub_id);
				$ilCtrl->setParameter($this, "bmn", $this->month);									
				$lg->addCustomCommand($link, "blog_edit"); // #11868	
								
				if($sub_id && $this->mayContribute($sub_id))			
				{										
					$link = $ilCtrl->getLinkTargetByClass("ilblogpostinggui", "edit");									
					$lg->addCustomCommand($link, "blog_edit_posting");	
				}
				
				$ilCtrl->setParameter($this, "prvm", "fsc");
			}			
			
			$ilCtrl->setParameter($this, "ntf", "");
		}
		
		return $lg;
	}
	
	protected function setNotification()
	{
		global $ilUser, $ilCtrl;
		
		include_once "./Services/Notification/classes/class.ilNotification.php";
		switch($_GET["ntf"])
		{
			case 1:
				ilNotification::setNotification(ilNotification::TYPE_BLOG, $ilUser->getId(), $this->obj_id, false);
				break;
			
			case 2:
				ilNotification::setNotification(ilNotification::TYPE_BLOG, $ilUser->getId(), $this->obj_id, true);
				break;
		}
		
		$ilCtrl->redirect($this, "preview");
	}

	/**
	 * Get title for blog posting (used in ilNotesGUI)
	 * 
	 * @param int $a_blog_id
	 * @param int $a_posting_id 
	 * @return string
	 */
	static function lookupSubObjectTitle($a_blog_id, $a_posting_id)
	{
		// page might be deleted, so setting halt on errors to false
		include_once "Modules/Blog/classes/class.ilBlogPosting.php";
		$post = new ilBlogPosting($a_posting_id);
		if($post->getBlogId() == $a_blog_id)
		{
			return $post->getTitle();
		}
	}
	
	/**
	 * Filter inactive items from items list
	 * 
	 * @return array
	 */
	protected function filterInactivePostings()
	{				
		foreach($this->items as $month => $postings)
		{
			foreach($postings as $id => $item)
			{
				if(!ilBlogPosting::_lookupActive($id, "blp"))
				{
					unset($this->items[$month][$id]);
				}
				else if($this->object->hasApproval() && !$item["approved"])
				{
					unset($this->items[$month][$id]);
				}
			}
			if(!sizeof($this->items[$month]))
			{
				unset($this->items[$month]);
			}
		}		
		
		if($this->items && !isset($this->items[$this->month]))
		{
			$this->month = array_shift(array_keys($this->items));
		}
	}
		
	protected function filterItemsByKeyWord(array $a_items, $a_keyword)
	{		
		$res = array();
		include_once("./Modules/Blog/classes/class.ilBlogPosting.php");
		foreach($a_items as $month => $items)
		{						
			foreach($items as $item)
			{
				if(in_array($a_keyword,
					ilBlogPosting::getKeywords($this->obj_id, $item["id"])))
				{
					$res[] = $item;
				}
			}
		}
		return $res;		
	}	
	
	/**
	 * Check if user may contribute at all and may edit posting (if given)
	 * 
	 * @param int $a_posting_id
	 * @param int $a_author_id
	 * @return boolean
	 */
	protected function mayContribute($a_posting_id = null, $a_author_id = null)
	{
		global $ilUser;
		
		// single author blog (owner) in personal workspace
		if($this->id_type == self::WORKSPACE_NODE_ID)
		{
			return $this->checkPermissionBool("write");				
		}
			
		if($this->checkPermissionBool("contribute"))
		{		
			// check owner of posting
			if($a_posting_id)
			{
				if(!$a_author_id)
				{
					include_once "Modules/Blog/classes/class.ilBlogPosting.php";
					$post = new ilBlogPosting($a_posting_id);
					$a_author_id = $post->getAuthor();					
				}				
				if($ilUser->getId() == $a_author_id)
				{
					return true;
				}
				else
				{
					return false;
				}
			}			
			
			return true;
		}
		return false;
	}
	
	function addLocatorItems()
	{
		global $ilLocator;
		
		if (is_object($this->object))
		{
			$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, ""), "", $this->node_id);		
		}
	}
	
	function approve()
	{
		if($this->checkPermissionBool("write") && (int)$_GET["apid"])
		{			
			include_once "Modules/Blog/classes/class.ilBlogPosting.php";
			$post = new ilBlogPosting((int)$_GET["apid"]);			
			$post->setApproved(true);
			$post->setBlogNodeId($this->node_id, ($this->id_type == self::WORKSPACE_NODE_ID));
			$post->update(true, false, true, "new"); // #13434
			
			ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
		}
				
		$this->ctrl->redirect($this, "render");
	}
	
	
	// 
	// contributors
	//
	
	function contributors()
	{
		global $ilTabs, $ilToolbar, $ilCtrl, $lng, $tpl;					
		
		if(!$this->checkPermissionBool("write"))
		{
			return;
		}
		
		$ilTabs->activateTab("contributors");
	
		// add member
		include_once './Services/Search/classes/class.ilRepositorySearchGUI.php';
		ilRepositorySearchGUI::fillAutoCompleteToolbar(
			$this,
			$ilToolbar,
			array(
				'auto_complete_name'	=> $lng->txt('user'),
				'submit_name'			=> $lng->txt('add'),
				'add_search'			=> true,
				'add_from_container'    => $this->node_id				
			)
		);

		$other_roles = $this->object->getRolesWithContribute($this->node_id);
		if($other_roles)
		{
			ilUtil::sendInfo(sprintf($lng->txt("blog_contribute_other_roles"), implode(", ", $other_roles)));
		}
		
		include_once "Modules/Blog/classes/class.ilContributorTableGUI.php";
		$tbl = new ilContributorTableGUI($this, "contributors", $this->object->getLocalContributorRole($this->node_id));
		
		$tpl->setContent($tbl->getHTML());							
	}
	
	/**
	 * Autocomplete submit
	 */
	public function addUserFromAutoComplete()
	{		
		global $lng;
		
		if(!strlen(trim($_POST['user_login'])))
		{
			ilUtil::sendFailure($lng->txt('msg_no_search_string'));
			return $this->contributors();
		}
		$users = explode(',', $_POST['user_login']);

		$user_ids = array();
		foreach($users as $user)
		{
			$user_id = ilObjUser::_lookupId($user);

			if(!$user_id)
			{
				ilUtil::sendFailure($lng->txt('user_not_known'));				
				return $this->contributors();				
			}
			
			$user_ids[] = $user_id;
		}
	
		return $this->addContributor($user_ids);											
	}
		
	/**
	 * Centralized method to add contributors
	 * 
	 * @param array $a_user_ids
	 */
	public function addContributor($a_user_ids = array())
	{		
		global $ilCtrl, $lng, $rbacreview, $rbacadmin;
		
		if(!$this->checkPermissionBool("write"))
		{
			return;
		}
		
		if(!count($a_user_ids))
		{
			ilUtil::sendFailure($lng->txt("no_checkbox"));
			return $this->contributors();
		}
		
		// get contributor role
		$contr_role_id = $this->object->getLocalContributorRole($this->node_id);
		if(!$contr_role_id)
		{
			ilUtil::sendFailure($lng->txt("missing_perm"));
			return $this->contributors();
		}		
		
		foreach($a_user_ids as $user_id)
		{
			if(!$rbacreview->isAssigned($user_id, $contr_role_id))
			{
				$rbacadmin->assignUser($contr_role_id, $user_id);
			}
		}

		ilUtil::sendSuccess($lng->txt("settings_saved"), true);
		$ilCtrl->redirect($this, "contributors");
	}
	
	/**
	 * Used in ilContributorTableGUI
	 */
	public function confirmRemoveContributor()
	{	
		$ids = $_POST["id"];
		
		if(!sizeof($ids))
		{
			ilUtil::sendFailure($this->lng->txt("select_one"), true);
			$this->ctrl->redirect($this, "contributors");
		}
		
		include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
		$confirm = new ilConfirmationGUI();
		$confirm->setHeaderText($this->lng->txt('blog_confirm_delete_contributors'));
		$confirm->setFormAction($this->ctrl->getFormAction($this, 'removeContributor'));
		$confirm->setConfirm($this->lng->txt('delete'), 'removeContributor');
		$confirm->setCancel($this->lng->txt('cancel'), 'contributors');
		
		include_once 'Services/User/classes/class.ilUserUtil.php';
		
		foreach($ids as $user_id)
		{
			$confirm->addItem('id[]', $user_id, 
				ilUserUtil::getNamePresentation($user_id, false, false, "", true));
		}
		
		$this->tpl->setContent($confirm->getHTML());
	}
	
	/**
	 * Used in ilContributorTableGUI
	 */
	public function removeContributor()
	{
		global $ilCtrl, $lng, $rbacadmin;
		
		$ids = $_POST["id"];
		
		if(!sizeof($ids))
		{
			ilUtil::sendFailure($lng->txt("select_one"), true);
			$ilCtrl->redirect($this, "contributors");
		}
		
		// get contributor role
		$contr_role_id = $this->object->getLocalContributorRole($this->node_id);
		if(!$contr_role_id)
		{
			ilUtil::sendFailure($lng->txt("missing_perm"));
			return $this->contributors();
		}		
		
		foreach($ids as $user_id)
		{			
			$rbacadmin->deassignUser($contr_role_id, $user_id);
		}
				
		ilUtil::sendSuccess($lng->txt("settings_saved"), true);
		$this->ctrl->redirect($this, "contributors");				
	}
	
	/**
	 * @see ilDesktopItemHandling::addToDesk()
	 */
	public function addToDeskObject()
	{
		global $lng;

		include_once './Services/PersonalDesktop/classes/class.ilDesktopItemGUI.php';
		ilDesktopItemGUI::addToDesktop();
		ilUtil::sendSuccess($lng->txt("added_to_desktop"));		
	}
	
	/**
	 * @see ilDesktopItemHandling::removeFromDesk()
	 */
	public function removeFromDeskObject()
	{
		global $lng;

		include_once './Services/PersonalDesktop/classes/class.ilDesktopItemGUI.php';
		ilDesktopItemGUI::removeFromDesktop();
		ilUtil::sendSuccess($lng->txt("removed_from_desktop"));
	}
	
	public function deactivateAdmin()
	{
		if($this->checkPermissionBool("write") && (int)$_GET["apid"])
		{			
			// ilBlogPostingGUI::deactivatePage()
			include_once "Modules/Blog/classes/class.ilBlogPosting.php";
			$post = new ilBlogPosting((int)$_GET["apid"]);
			$post->setApproved(false);
			$post->setActive(false);
			$post->update(true, false, false);
			
			ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
		}
				
		$this->ctrl->redirect($this, "render");
	}
	
	
	////
	//// Style related functions
	////
	
	function setContentStyleSheet($a_tpl = null)
	{
		global $tpl;

		if ($a_tpl != null)
		{
			$ctpl = $a_tpl;
		}
		else
		{
			$ctpl = $tpl;
		}

		$ctpl->setCurrentBlock("ContentStyle");
		$ctpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath($this->object->getStyleSheetId()));
		$ctpl->parseCurrentBlock();
	}
	
	function editStyleProperties()
	{		
		$this->checkPermission("write");
		
		$this->tabs_gui->activateTab("settings");
		$this->setSettingsSubTabs("style");
		
		$form = $this->initStylePropertiesForm();
		$this->tpl->setContent($form->getHTML());				
	}
	
	function initStylePropertiesForm()
	{
		global $ilSetting;
						
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		$this->lng->loadLanguageModule("style");

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		
		$fixed_style = $ilSetting->get("fixed_content_style_id");
		$style_id = $this->object->getStyleSheetId();

		if ($fixed_style > 0)
		{
			$st = new ilNonEditableValueGUI($this->lng->txt("style_current_style"));
			$st->setValue(ilObject::_lookupTitle($fixed_style)." (".
				$this->lng->txt("global_fixed").")");
			$form->addItem($st);
		}
		else
		{
			$st_styles = ilObjStyleSheet::_getStandardStyles(true, false,
				$_GET["ref_id"]);

			$st_styles[0] = $this->lng->txt("default");
			ksort($st_styles);

			if ($style_id > 0)
			{
				// individual style
				if (!ilObjStyleSheet::_lookupStandard($style_id))
				{
					$st = new ilNonEditableValueGUI($this->lng->txt("style_current_style"));
					$st->setValue(ilObject::_lookupTitle($style_id));
					$form->addItem($st);

					// delete command
					$form->addCommandButton("editStyle", $this->lng->txt("style_edit_style"));
					$form->addCommandButton("deleteStyle", $this->lng->txt("style_delete_style"));
				}
			}

			if ($style_id <= 0 || ilObjStyleSheet::_lookupStandard($style_id))
			{
				$style_sel = new ilSelectInputGUI($this->lng->txt("style_current_style"), 
					"style_id");
				$style_sel->setOptions($st_styles);
				$style_sel->setValue($style_id);
				$form->addItem($style_sel);

				$form->addCommandButton("saveStyleSettings", $this->lng->txt("save"));
				$form->addCommandButton("createStyle", $this->lng->txt("sty_create_ind_style"));
			}
		}
		
		$form->setTitle($this->lng->txt("blog_style"));
		$form->setFormAction($this->ctrl->getFormAction($this));
		
		return $form;
	}

	function createStyle()
	{		
		$this->ctrl->redirectByClass("ilobjstylesheetgui", "create");
	}
		
	function editStyle()
	{
		$this->ctrl->redirectByClass("ilobjstylesheetgui", "edit");
	}

	function deleteStyle()
	{		
		$this->ctrl->redirectByClass("ilobjstylesheetgui", "delete");
	}

	function saveStyleSettings()
	{
		global $ilSetting;
	
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		if ($ilSetting->get("fixed_content_style_id") <= 0 &&
			(ilObjStyleSheet::_lookupStandard($this->object->getStyleSheetId())
			|| $this->object->getStyleSheetId() == 0))
		{
			$this->object->setStyleSheetId(ilUtil::stripSlashes($_POST["style_id"]));
			$this->object->update();
			
			ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
		}
		$this->ctrl->redirect($this, "editStyleProperties");
	}
	
	/**
	 * Deep link
	 * 
	 * @param string $a_target 
	 */
	public static function _goto($a_target)
	{							
		if(substr($a_target, -3) == "wsp")
		{		
			$id = explode("_", $a_target);		

			$_GET["baseClass"] = "ilSharedResourceGUI";	
			$_GET["wsp_id"] = $id[0];		
			if(sizeof($id) == 3)
			{
				$_GET["gtp"] = $id[1];
			}						
		}
		else
		{
			$id = explode("_", $a_target);		
			
			$_GET["baseClass"] = "ilRepositoryGUI";	
			$_GET["ref_id"] = $id[0];		
			$_GET["cmd"] = "preview";
			if(sizeof($id) == 2)
			{
				$_GET["gtp"] = $id[1];
			}	
		}
		
		include("ilias.php");
		exit;
	}
}

?>