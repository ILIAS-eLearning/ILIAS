<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Portfolio/classes/class.ilObjPortfolio.php");

/**
 * Portfolio view gui class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_Calls ilObjPortfolioGUI: ilPortfolioPageGUI, ilPageObjectGUI
 * @ilCtrl_Calls ilObjPortfolioGUI: ilWorkspaceAccessGUI, ilNoteGUI
 *
 * @ingroup ServicesPortfolio
 */
class ilObjPortfolioGUI 
{
	protected $user_id; // [int]
	protected $portfolio; // [ilObjPortfolio]
	
	/**
	 * Constructor
	 */
	function __construct()
	{
		global $ilCtrl, $lng, $ilUser;

		$lng->loadLanguageModule("prtf");

		$this->user_id = $ilUser->getId();

		$portfolio_id = $_REQUEST["prt_id"];
		$ilCtrl->setParameter($this, "prt_id", $portfolio_id);

		if($portfolio_id)
		{
			$this->initPortfolioObject($portfolio_id);
		}
	}

	/**
	 * Init portfolio object
	 *
	 * @param int $a_id
	 */
	function initPortfolioObject($a_id)
	{
		$portfolio = new ilObjPortfolio($a_id, false);
		if($portfolio->getId() && $portfolio->getOwner() == $this->user_id)
		{
			$this->portfolio = $portfolio;
		}
	}

	/**
	 * execute command
	 */
	function &executeCommand()
	{
		global $ilCtrl, $ilTabs, $lng, $tpl, $ilUser;

		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd("show");
		
		$lng->loadLanguageModule("user");
		
		$title = $lng->txt("portfolio");
		if($this->portfolio)
		{
			$title .= ": ".$this->portfolio->getTitle();	
		}
		$tpl->setTitle($title);
		$tpl->setTitleIcon(ilUtil::getImagePath("icon_prtf_b.gif"), 
			$lng->txt("portfolio"));

		switch($next_class)
		{
			case "ilworkspaceaccessgui";				
				$ilTabs->clearTargets();
				$ilTabs->setBackTarget($lng->txt("back"),
					$ilCtrl->getLinkTarget($this, "show"));			
				
				$this->setPagesTabs();
				$ilTabs->activateTab("share");
				
				include_once('./Services/PersonalWorkspace/classes/class.ilWorkspaceAccessGUI.php');
				include_once('./Services/Portfolio/classes/class.ilPortfolioAccessHandler.php');
				$handler = new ilPortfolioAccessHandler();
				$wspacc = new ilWorkspaceAccessGUI($this->portfolio->getId(), $handler);
				$ilCtrl->forwardCommand($wspacc);
				break;
			
			case 'ilportfoliopagegui':
				$ilTabs->clearTargets();
				$ilTabs->setBackTarget($lng->txt("back"),
					$ilCtrl->getLinkTarget($this, "pages"));
				
				// edit
				if(isset($_REQUEST["ppage"]))
				{
					$this->setLocator($_REQUEST["ppage"]);
					
					$page_id = $_REQUEST["ppage"];
					$ilCtrl->setParameter($this, "ppage", $_REQUEST["ppage"]);
				}
				// preview
				else
				{
					$page_id = $_REQUEST["user_page"];
					$ilCtrl->setParameter($this, "user_page", $_REQUEST["user_page"]);
				}
				
				include_once("Services/Portfolio/classes/class.ilPortfolioPageGUI.php");
				$page_gui = new ilPortfolioPageGUI($this->portfolio->getId(),
					$page_id, 0, $this->portfolio->hasPublicComments());
				
				$tpl->setCurrentBlock("ContentStyle");
				$tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
					ilObjStyleSheet::getContentStylePath(0));
				$tpl->parseCurrentBlock();

				$ret = $ilCtrl->forwardCommand($page_gui);
				
				if ($ret != "" && $ret !== true)
				{						
					// preview (fullscreen)
					if(isset($_REQUEST["user_page"]))
					{						
						// suppress (portfolio) notes for blog postings 
						$this->preview(false, $ret, ($cmd != "previewEmbedded"));
					}
					// edit
					else
					{
						$tpl->setContent($ret);
					}
				}
				break;
				
			case "ilnotegui";				
				$this->preview();				
				break;
			
			default:		
				if($cmd != "preview")
				{
					$this->setLocator();
				}
				$this->$cmd();
				break;
		}

		return true;
	}
	
	/**
	 * Set all tabs
	 *
	 * @param
	 * @return
	 */
	function setTabs()
	{
		
	}

	/**
	 * Show list of user portfolios
	 */
	protected function show()
	{
		global $tpl, $lng, $ilToolbar, $ilCtrl;

		$ilToolbar->addButton($lng->txt("prtf_add_portfolio"),
			$ilCtrl->getLinkTarget($this, "add"));
			
		$ilToolbar->addButton("TEST IMPORT PROFILE",
			$ilCtrl->getLinkTarget($this, "importProfile"));

		include_once "Services/Portfolio/classes/class.ilPortfolioTableGUI.php";
		$table = new ilPortfolioTableGUI($this, "show", $this->user_id);

		$tpl->setContent($table->getHTML());
	}
	
	protected function saveTitles()
	{
		global $ilCtrl, $lng;
		
		foreach($_POST["title"] as $id => $title)
		{
			if(trim($title))
			{
				$portfolio = new ilObjPortfolio($id, false);
				$portfolio->setTitle($title);
				
				if(is_array($_POST["online"]) && in_array($id, $_POST["online"]))
				{
					$portfolio->setOnline(true);
				}
				else
				{
					$portfolio->setOnline(false);
				}
				
				$portfolio->update();
			}
		}
		
		ilUtil::sendSuccess($lng->txt("saved_successfully"), true);
		$ilCtrl->redirect($this, "show");
	}

	/**
	 * Show portfolio creation form
	 */
	protected function add()
	{
		global $tpl;

		$form = $this->initForm();

		$tpl->setContent($form->getHTML());
	}

	/**
	 * Create new portfolio instance
	 */
	protected function save()
	{
		global $tpl, $lng, $ilCtrl;
		
		$form = $this->initForm();
		if($form->checkInput())
		{
			$portfolio = new ilObjPortfolio();
			$portfolio->setTitle($form->getInput("title"));
			// $portfolio->setDescription($form->getInput("desc"));
			$portfolio->create();
			
			include_once("Services/Portfolio/classes/class.ilPortfolioPage.php");
			$page = new ilPortfolioPage($portfolio->getId());
			if($form->getInput("ptype") == "page")
			{				
				$page->setType(ilPortfolioPage::TYPE_PAGE);
				$page->setTitle($form->getInput("fpage"));		
				
				// use template as basis
				$layout_id = $form->getInput("tmpl");
				if($layout_id)
				{
					include_once("./Services/Style/classes/class.ilPageLayout.php");
					$layout_obj = new ilPageLayout($layout_id);
					$page->setXMLContent($layout_obj->getXMLContent());
				}
			}
			else
			{
				$page->setType(ilPortfolioPage::TYPE_BLOG);
				$page->setTitle($form->getInput("blog"));								
			}
			$page->create();

			ilUtil::sendSuccess($lng->txt("prtf_portfolio_created"), true);
			$ilCtrl->setParameter($this, "prt_id", $portfolio->getId());
			$ilCtrl->redirect($this, "pages");
		}

		$form->setValuesByPost();
		$tpl->setContent($form->getHTML());
	}

	/**
	 * Show portfolio edit form
	 */
	protected function edit()
	{
		global $tpl, $ilTabs, $ilCtrl, $lng;
		
		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt("back"),
			$ilCtrl->getLinkTarget($this, "show"));
		
		$this->setPagesTabs();
		$ilTabs->activateTab("edit");

		$form = $this->initForm("edit");

		$tpl->setContent($form->getHTML());
	}

	/**
	 * Update portfolio properties
	 */
	protected function update()
	{
		global $tpl, $lng, $ilCtrl, $ilUser;

		$form = $this->initForm("edit");
		if($form->checkInput())
		{
			$this->portfolio->setTitle($form->getInput("title"));
			$this->portfolio->setDescription($form->getInput("desc"));
			$this->portfolio->setOnline($form->getInput("online"));
			$this->portfolio->setPublicComments($form->getInput("comments"));
			$this->portfolio->setProfilePicture($form->getInput("ppic"));
			$this->portfolio->setBackgroundColor($form->getInput("bg_color"));
			$this->portfolio->setFontcolor($form->getInput("font_color"));
			
			if($_FILES["banner"]["tmp_name"]) 
			{
				$this->portfolio->uploadImage($_FILES["banner"]);
			}
			else if($form->getItemByPostVar("banner")->getDeletionFlag())
			{
				$this->portfolio->deleteImage();
			}			
			
			$this->portfolio->update();			

			// if portfolio is not online, it cannot be default
			if(!$form->getInput("online"))
			{
				ilObjPortfolio::setUserDefault($ilUser->getId(), 0);
			}
			
			ilUtil::sendSuccess($lng->txt("prtf_portfolio_updated"), true);
			$ilCtrl->redirect($this, "pages");
		}

		$form->setValuesByPost();
		$tpl->setContent($form->getHTML());
	}

	/**
	 * Init portfolio form
	 *
	 * @param string $a_mode
	 * @return ilPropertyFormGUI
	 */
	protected function initForm($a_mode = "create")
	{
		global $lng, $ilCtrl, $ilUser, $ilSetting;

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));		

		// title
		$ti = new ilTextInputGUI($lng->txt("title"), "title");
		$ti->setMaxLength(128);
		$ti->setSize(40);
		$ti->setRequired(true);
		$form->addItem($ti);

		/* description
		$ta = new ilTextAreaInputGUI($lng->txt("description"), "desc");
		$ta->setCols(40);
		$ta->setRows(2);
		$form->addItem($ta);		
		*/
		
		if($a_mode == "create")
		{
			$type = new ilRadioGroupInputGUI($lng->txt("prtf_first_page_title"), "ptype");
			$type->setRequired(true);
			$form->addItem($type);
			
			$type_page = new ilRadioOption($lng->txt("page"), "page");
			$type->addOption($type_page);
						
			// 1st page
			$tf = new ilTextInputGUI($lng->txt("title"), "fpage");
			$tf->setMaxLength(128);
			$tf->setSize(40);
			$tf->setRequired(true);
			$type_page->addSubItem($tf);	
			
			include_once "Services/Style/classes/class.ilPageLayout.php";
			$templates = ilPageLayout::activeLayouts(false, ilPageLayout::MODULE_PORTFOLIO);
			if($templates)
			{			
				$options = array(0 => $lng->txt("none"));
				foreach ($templates as $templ)
				{
					$templ->readObject();
					$options[$templ->getId()] = $templ->getTitle();			
				}
				
				$use_template = new ilSelectInputGUI($lng->txt("prtf_use_page_layout"), "tmpl");
				$use_template->setRequired(true);
				$use_template->setOptions($options);
				$type_page->addSubItem($use_template);
			}
			
			if(!$ilSetting->get('disable_wsp_blogs'))
			{
				$options = array();
				include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
				$tree = new ilWorkspaceTree($ilUser->getId());
				$root = $tree->getNodeData($tree->readRootId());
				foreach ($tree->getSubTree($root) as $node)
				{
					if ($node["type"] == "blog")
					{
						$options[$node["obj_id"]] = $node["title"];
					}
				}
				asort($options);		

				if(sizeof($options))
				{			
					$type_blog = new ilRadioOption($lng->txt("obj_blog"), "blog");
					$type->addOption($type_blog);

					$obj = new ilSelectInputGUI($lng->txt("obj_blog"), "blog");
					$obj->setRequired(true);
					$obj->setOptions($options);
					$type_blog->addSubItem($obj);
				}
				else
				{
					ilUtil::sendInfo($lng->txt("prtf_no_blogs_info"));				
					$type->setValue("page");
				}
			}

			$form->setTitle($lng->txt("prtf_create_portfolio"));
			$form->addCommandButton("save", $lng->txt("save"));
			$form->addCommandButton("show", $lng->txt("cancel"));
		}
		else
		{
			// online
			$online = new ilCheckboxInputGUI($lng->txt("online"), "online");
			$form->addItem($online);
			
			// comments
			$comments = new ilCheckboxInputGUI($lng->txt("prtf_public_comments"), "comments");
			$form->addItem($comments);
			
			// profile picture
			$ppic = new ilCheckboxInputGUI($lng->txt("prtf_profile_picture"), "ppic");
			$form->addItem($ppic);
			
			$prfa_set = new ilSetting("prfa");
			if($prfa_set->get("banner"))
			{
				$img = new ilImageFileInputGUI($lng->txt("prtf_banner"), "banner");
				$form->addItem($img);
					
				// show existing file
				$file = $this->portfolio->getImageFullPath(true);
				if($file)
				{
					$img->setImage($file);
				}		
			}

			$bg_color = new ilColorPickerInputGUI($lng->txt("prtf_background_color"), "bg_color");
			$form->addItem($bg_color);

			$font_color = new ilColorPickerInputGUI($lng->txt("prtf_font_color"), "font_color");
			$form->addItem($font_color);		
						
			$ti->setValue($this->portfolio->getTitle());
			// $ta->setValue($this->portfolio->getDescription());
			$online->setChecked($this->portfolio->isOnline());
			$comments->setChecked($this->portfolio->hasPublicComments());
			$ppic->setChecked($this->portfolio->hasProfilePicture());
			$bg_color->setValue($this->portfolio->getBackgroundColor());
			$font_color->setValue($this->portfolio->getFontColor());
		
			$form->setTitle($lng->txt("prtf_edit_portfolio"));
			$form->addCommandButton("update", $lng->txt("save"));
			$form->addCommandButton("show", $lng->txt("cancel"));
		}

		return $form;		
	}

	/**
	 * Set default portfolio for user
	 */
	protected function setDefault()
	{
		global $ilCtrl, $lng;

		if($this->portfolio)
		{
			ilObjPortfolio::setUserDefault($this->user_id, $this->portfolio->getId());
			ilUtil::sendSuccess($lng->txt("settings_saved"), true);
		}
		$ilCtrl->redirect($this, "show");
	}

	/**
	 * Confirm portfolio deletion
	 */
	function confirmPortfolioDeletion()
	{
		global $ilCtrl, $tpl, $lng;

		if (!is_array($_POST["prtfs"]) || count($_POST["prtfs"]) == 0)
		{
			ilUtil::sendInfo($lng->txt("no_checkbox"), true);
			$ilCtrl->redirect($this, "show");
		}
		else
		{
			include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
			$cgui = new ilConfirmationGUI();
			$cgui->setFormAction($ilCtrl->getFormAction($this));
			$cgui->setHeaderText($lng->txt("prtf_sure_delete_portfolios"));
			$cgui->setCancel($lng->txt("cancel"), "show");
			$cgui->setConfirm($lng->txt("delete"), "deletePortfolios");

			foreach ($_POST["prtfs"] as $id)
			{
				$cgui->addItem("prtfs[]", $id, ilObjPortfolio::_lookupTitle($id));
			}

			$tpl->setContent($cgui->getHTML());
		}
	}

	/**
	 * Delete portfolios
	 */
	function deletePortfolios()
	{
		global $lng, $ilCtrl;

		if (is_array($_POST["prtfs"]))
		{
			foreach ($_POST["prtfs"] as $id)
			{
				$portfolio = new ilObjPortfolio($id, false);
				if ($portfolio->getOwner() == $this->user_id)
				{
					$portfolio->delete();
				}
			}
		}
		ilUtil::sendSuccess($lng->txt("prtf_portfolio_deleted"), true);
		$ilCtrl->redirect($this, "show");
	}


	//
	// PAGES
	//

	protected function setPagesTabs()
	{
		global $lng, $ilTabs, $ilCtrl;				
		
		$ilTabs->addTab("pages",
			$lng->txt("content"),
			$ilCtrl->getLinkTarget($this, "pages"));
		
		$ilTabs->addTab("edit",
			$lng->txt("settings"),
			$ilCtrl->getLinkTarget($this, "edit"));
		
		$lng->loadLanguageModule("wsp");
		$ilTabs->addTab("share",
			$lng->txt("wsp_permissions"),
			$ilCtrl->getLinkTargetByClass("ilworkspaceaccessgui", "share"));
		
		$ilTabs->addNonTabbedLink("preview", 
			$lng->txt("user_profile_preview"),
			$ilCtrl->getLinkTarget($this, "preview"));	
	}
	
	/**
	 * Show list of portfolio pages
	 */
	protected function pages()
	{
		global $tpl, $lng, $ilToolbar, $ilCtrl, $ilTabs, $ilUser, $ilSetting;

		$ilTabs->clearTargets();
		
		$ilCtrl->setParameter($this, "prt_id", "");
		$ilTabs->setBackTarget($lng->txt("back"),
			$ilCtrl->getLinkTarget($this, "show"));
		$ilCtrl->setParameter($this, "prt_id", $this->portfolio->getId());
		
		$this->setPagesTabs();
		$ilTabs->activateTab("pages");

		$ilToolbar->addButton($lng->txt("prtf_add_page"),
			$ilCtrl->getLinkTarget($this, "addPage"));
		
		if(!$ilSetting->get('disable_wsp_blogs'))
		{
			$ilToolbar->addButton($lng->txt("prtf_add_blog"),
				$ilCtrl->getLinkTarget($this, "addBlog"));
		}
		
		$ilToolbar->addSeparator();
		
		$ilToolbar->addButton($lng->txt("export"),
			$ilCtrl->getLinkTarget($this, "export"));		
				
		include_once "Services/Portfolio/classes/class.ilPortfolioPageTableGUI.php";
		$table = new ilPortfolioPageTableGUI($this, "show", $this->portfolio);
		
		// exercise portfolio?			
		include_once "Modules/Exercise/classes/class.ilObjExercise.php";			
		$exercises = ilObjExercise::findUserFiles($ilUser->getId(), $this->portfolio->getId());
		if($exercises)
		{
			$info = array();
			foreach($exercises as $exercise)
			{
				$info[] = $this->getExerciseInfo($exercise["ass_id"], $table->dataExists());
			}
			ilUtil::sendInfo(implode("<br />", $info));									
		}
		
		include_once('classes/class.ilLink.php');
		$goto = ilLink::_getStaticLink($this->portfolio->getId(), "prtf", true);
		$goto = "<div style=\"margin:10px;\" class=\"small\"><a href=\"".$goto.
			"\" target=\"blank\">goto test</a></div>";

		$tpl->setContent($table->getHTML().$goto);
	}
	
	function getExerciseInfo($a_assignment_id, $a_add_submit = false)
	{		
		global $lng, $ilCtrl, $ilUser;
		
		include_once "Modules/Exercise/classes/class.ilExAssignment.php";			
		$ass = new ilExAssignment($a_assignment_id);		
		$exercise_id = $ass->getExerciseId();

		// exercise goto
		include_once "classes/class.ilLink.php";
		$exc_ref_id = array_shift(ilObject::_getAllReferences($exercise_id));
		$exc_link = ilLink::_getStaticLink($exc_ref_id, "exc");

		$info = sprintf($lng->txt("prtf_exercise_info"), 
			$ass->getTitle(),
			"<a href=\"".$exc_link."\">".
			ilObject::_lookupTitle($exercise_id)."</a>");
		
		// submit button
		if($a_add_submit)
		{
			$ilCtrl->setParameter($this, "exc", $exercise_id);				
			$ilCtrl->setParameter($this, "ass", $a_assignment_id);
			$submit_link = $ilCtrl->getLinkTarget($this, "finalize");
			$ilCtrl->setParameter($this, "ass", "");
			$ilCtrl->setParameter($this, "exc", "");	
			$info .= " <a class=\"submit\" href=\"".$submit_link."\">".$lng->txt("prtf_finalize_portfolio")."</a>";
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
			
			$info .= "<br />".sprintf($lng->txt("prtf_exercise_submitted_info"), 
				ilDatePresentation::formatDate(new ilDateTime($submitted["ts"], IL_CAL_DATETIME)),
				"<a href=\"".$dl_link."\">".$lng->txt("download")."</a>");
			
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
			$overlay->setAutoHide(false);
			// $overlay->setCloseElementId($cl_id);
			$overlay->add();

			// trigger
			$overlay->addTrigger($ol_id."_tr", "click", $ol_id."_tr");

			$info .= "<div id=\"".$ol_id."_tr\"><a href=\"#\">".$lng->txt("exc_instruction")."</a></div>".
				"<div id=\"".$ol_id."\" style=\"display:none; background-color:white; border: 1px solid #bbb; padding: 10px;\">".$tooltip."</div>";
		}
		
		return $info;
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
	 * Show portfolio page creation form
	 */
	protected function addPage()
	{
		global $tpl, $lng, $ilTabs, $ilCtrl;

		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt("back"),
			$ilCtrl->getLinkTarget($this, "pages"));

		$form = $this->initPageForm("create");
		$tpl->setContent($form->getHTML());
	}

	/**
	 * Init portfolio page form
	 *
	 * @param string $a_mode
	 * @return ilPropertyFormGUI
	 */
	public function initPageForm($a_mode = "create")
	{
		global $lng, $ilCtrl, $ilUser;

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));

		// title
		$ti = new ilTextInputGUI($lng->txt("title"), "title");
		$ti->setMaxLength(200);
		$ti->setRequired(true);
		$form->addItem($ti);

		// save and cancel commands
		if ($a_mode == "create")
		{
			include_once "Services/Style/classes/class.ilPageLayout.php";
			$templates = ilPageLayout::activeLayouts(false, ilPageLayout::MODULE_PORTFOLIO);
			if($templates)
			{			
				$use_template = new ilRadioGroupInputGUI($lng->txt("prtf_use_page_layout"), "tmpl");
				$use_template->setRequired(true);
				$form->addItem($use_template);

				$opt = new ilRadioOption($lng->txt("none"), 0);
				$use_template->addOption($opt);

				foreach ($templates as $templ)
				{
					$templ->readObject();

					$opt = new ilRadioOption($templ->getTitle().$templ->getPreview(), $templ->getId());
					$use_template->addOption($opt);			
				}
			}
			
			$form->setTitle($lng->txt("prtf_add_page").": ".
				$this->portfolio->getTitle());
			$form->addCommandButton("savePage", $lng->txt("save"));
			$form->addCommandButton("pages", $lng->txt("cancel"));			
		}
		else
		{
			/* edit is done directly in table gui
			$form->setTitle($lng->txt("prtf_edit_page"));
			$form->addCommandButton("updatePage", $lng->txt("save"));
			$form->addCommandButton("pages", $lng->txt("cancel"));
			*/			
		}
		
		return $form;
	}
		
	/**
	 * Create new portfolio page
	 */
	public function savePage()
	{
		global $tpl, $lng, $ilCtrl, $ilTabs;

		$form = $this->initPageForm("create");
		if ($form->checkInput())
		{
			include_once("Services/Portfolio/classes/class.ilPortfolioPage.php");
			$page = new ilPortfolioPage($this->portfolio->getId());
			$page->setType(ilPortfolioPage::TYPE_PAGE);		
			$page->setTitle($form->getInput("title"));		
			
			// use template as basis
			$layout_id = $form->getInput("tmpl");
			if($layout_id)
			{
				include_once("./Services/Style/classes/class.ilPageLayout.php");
				$layout_obj = new ilPageLayout($layout_id);
				$page->setXMLContent($layout_obj->getXMLContent());
			}
			
			$page->create();

			ilUtil::sendSuccess($lng->txt("prtf_page_created"), true);
			$ilCtrl->redirect($this, "pages");
		}

		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt("back"),
			$ilCtrl->getLinkTarget($this, "pages"));

		$form->setValuesByPost();
		$tpl->setContent($form->getHtml());
	}
	
	/**
	 * Show portfolio blog page creation form
	 */
	protected function addBlog()
	{
		global $tpl, $lng, $ilTabs, $ilCtrl;

		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt("back"),
			$ilCtrl->getLinkTarget($this, "pages"));

		$form = $this->initBlogForm("create");
		$tpl->setContent($form->getHTML());
	}
	
	/**
	 * Init portfolio page form
	 *
	 * @param string $a_mode
	 * @return ilPropertyFormGUI
	 */
	public function initBlogForm($a_mode = "create")
	{
		global $lng, $ilCtrl, $ilUser;

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));

		$options = array();
		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
		$tree = new ilWorkspaceTree($ilUser->getId());
		$root = $tree->getNodeData($tree->readRootId());
		foreach ($tree->getSubTree($root) as $node)
		{
			if ($node["type"] == "blog")
			{
				$options[$node["obj_id"]] = $node["title"];
			}
		}
		asort($options);	
		
		// no blogs to add?
		if(!sizeof($options))
		{
			ilUtil::sendInfo($lng->txt("prtf_no_blogs_info"), true);
			$ilCtrl->redirect($this, "pages");
		}
		
		$obj = new ilSelectInputGUI($lng->txt("obj_blog"), "blog");
		$obj->setRequired(true);
		$obj->setOptions($options);
		$form->addItem($obj);

		// save and cancel commands
		if ($a_mode == "create")
		{
			$form->setTitle($lng->txt("prtf_add_blog").": ".
				$this->portfolio->getTitle());
			$form->addCommandButton("saveBlog", $lng->txt("save"));
			$form->addCommandButton("pages", $lng->txt("cancel"));
			
		}
		else
		{
			/* edit is done directly in table gui
			$form->setTitle($lng->txt("prtf_edit_page"));
			$form->addCommandButton("updatePage", $lng->txt("save"));
			$form->addCommandButton("pages", $lng->txt("cancel"));
			*/			
		}
		
		return $form;
	}
	
	/**
	 * Create new portfolio blog page
	 */
	public function saveBlog()
	{
		global $tpl, $lng, $ilCtrl, $ilTabs;

		$form = $this->initBlogForm("create");
		if ($form->checkInput())
		{
			include_once("Services/Portfolio/classes/class.ilPortfolioPage.php");
			$page = new ilPortfolioPage($this->portfolio->getId());
			$page->setType(ilPortfolioPage::TYPE_BLOG);		
			$page->setTitle($form->getInput("blog"));									
			$page->create();

			ilUtil::sendSuccess($lng->txt("prtf_page_created"), true);
			$ilCtrl->redirect($this, "pages");
		}

		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt("back"),
			$ilCtrl->getLinkTarget($this, "pages"));

		$form->setValuesByPost();
		$tpl->setContent($form->getHtml());
	}

	/**
	 * Save ordering of portfolio pages
	 */
	function savePortfolioPagesOrdering()
	{
		global $ilCtrl, $ilUser, $lng;

		include_once("Services/Portfolio/classes/class.ilPortfolioPage.php");

		if (is_array($_POST["title"]))
		{
			foreach ($_POST["title"] as $k => $v)
			{
				$page = new ilPortfolioPage($this->portfolio->getId(),
					ilUtil::stripSlashes($k));
				$page->setTitle(ilUtil::stripSlashes($v));
				$page->setOrderNr(ilUtil::stripSlashes($_POST["order"][$k]));
				$page->update();
			}
			ilPortfolioPage::fixOrdering($this->portfolio->getId());
		}
		
		ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
		$ilCtrl->redirect($this, "pages");
	}

	/**
	 * Confirm portfolio deletion
	 */
	function confirmPortfolioPageDeletion()
	{
		global $ilCtrl, $tpl, $lng;

		if (!is_array($_POST["prtf_pages"]) || count($_POST["prtf_pages"]) == 0)
		{
			ilUtil::sendInfo($lng->txt("no_checkbox"), true);
			$ilCtrl->redirect($this, "pages");
		}
		else
		{
			include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
			$cgui = new ilConfirmationGUI();
			$cgui->setFormAction($ilCtrl->getFormAction($this));
			$cgui->setHeaderText($lng->txt("prtf_sure_delete_portfolio_pages"));
			$cgui->setCancel($lng->txt("cancel"), "pages");
			$cgui->setConfirm($lng->txt("delete"), "deletePortfolioPages");

			include_once("Services/Portfolio/classes/class.ilPortfolioPage.php");
			foreach ($_POST["prtf_pages"] as $id)
			{
				$page = new ilPortfolioPage($this->portfolio->getId(), $id);
				$title = $page->getTitle();
				if($page->getType() == ilPortfolioPage::TYPE_BLOG)
				{
					$title = $lng->txt("obj_blog").": ".ilObject::_lookupTitle((int)$title);		
				}				
				$cgui->addItem("prtf_pages[]", $id, $title);
			}

			$tpl->setContent($cgui->getHTML());
		}
	}

	/**
	 * Delete portfolio pages
	 */
	function deletePortfolioPages()
	{
		global $lng, $ilCtrl;

		include_once("Services/Portfolio/classes/class.ilPortfolioPage.php");
		if (is_array($_POST["prtf_pages"]))
		{
			foreach ($_POST["prtf_pages"] as $id)
			{
				$page = new ilPortfolioPage($this->portfolio->getId(), $id);
				$page->delete();
			}
		}
		ilUtil::sendSuccess($lng->txt("prtf_portfolio_page_deleted"), true);
		$ilCtrl->redirect($this, "pages");
	}
	
	protected function importProfile()
	{
		global $ilUser, $lng;
		
		include_once "Services/User/classes/class.ilExtPublicProfilePage.php";
		include_once "Services/Portfolio/classes/class.ilPortfolioPage.php";
		
		$users = array($ilUser->getId());
		
		foreach($users as $user_id)
		{		
			$port = new ilObjPortfolio();
			$port->setOwner($user_id);
			$port->setTitle($lng->txt("prtf_portfolio_default"));
			$port->setOnline(true);
			$port->create();
			
			ilObjPortfolio::setUserDefault($user_id, $port->getId());
			
			// first page has public profile as default
			$xml = "<PageObject>".
				"<PageContent PCID=\"".ilUtil::randomHash()."\">".
					"<Profile Mode=\"inherit\" User=\"".$user_id."\"/>".
				"</PageContent>".
			"</PageObject>";
			
			// insert profile as first page
			$first = new ilPortfolioPage($port->getId());		
			$first->setTitle("###-");
			$first->setXMLContent($xml);			
			$first->create();			

			// additional pages?
			$pages = ilExtPublicProfilePage::getPagesOfUser($user_id);
			if($pages)
			{
				foreach($pages as $p)
				{
					$source = new ilExtPublicProfilePage($p["id"]);

					$target = new ilPortfolioPage($port->getId());
					$target->setTitle($source->getTitle());
					$target->setXMLContent($source->getXMLContent());
					$target->create();

					// $source->delete();
				}			
			}	
		}
		
		$this->show();
	}		
	
	/**
	 * Show user page
	 */
	function preview($a_return = false, $a_content = false, $a_show_notes = true)
	{
		global $ilUser, $tpl, $ilCtrl, $ilTabs, $lng;
		
		$portfolio_id = $this->portfolio->getId();
		$user_id = $this->portfolio->getOwner();
		
		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt("back"),
			$ilCtrl->getLinkTarget($this, "pages"));
			
		include_once("./Services/Portfolio/classes/class.ilPortfolioPage.php");
		$pages = ilPortfolioPage::getAllPages($portfolio_id);		
		$current_page = $_GET["user_page"];

		// display first page of portfolio if none given
		if(!$current_page && $pages)
		{
			$current_page = $pages;
			$current_page = array_shift($current_page);
			$current_page = $current_page["id"];
		}		
		
		// render tabs
		$current_blog = null;
		if(count($pages) > 1)
		{
			foreach ($pages as $p)
			{	
				if($p["type"] == ilPortfolioPage::TYPE_BLOG)
				{							
					// needed for blog comments (see below)
					if($p["id"] == $current_page)
					{
						$current_blog = (int)$p["title"];
					}									
					include_once "Modules/Blog/classes/class.ilObjBlog.php";
					$p["title"] = ilObjBlog::_lookupTitle($p["title"]);										
				}
				
				$ilCtrl->setParameter($this, "user_page", $p["id"]);
				$ilTabs->addTab("user_page_".$p["id"],
					$p["title"],
					$ilCtrl->getLinkTarget($this, "preview"));				
			}
			
			$ilTabs->activateTab("user_page_".$current_page);
			$ilCtrl->setParameter($this, "user_page", $current_page);
		}
		
		if(!$a_content)
		{
			// get current page content
			include_once("./Services/Portfolio/classes/class.ilPortfolioPageGUI.php");
			$page_gui = new ilPortfolioPageGUI($portfolio_id, $current_page);
			$page_gui->setEmbedded(true);

			$tpl->setCurrentBlock("ContentStyle");
			$tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
				ilObjStyleSheet::getContentStylePath(0));
			$tpl->parseCurrentBlock();

			$content = $ilCtrl->getHTML($page_gui);
		}
		else
		{
			$content = $a_content;
		}
		
		if($a_return)
		{
			return $content;
		}
		
		$notes = "";
		if($a_show_notes && $this->portfolio->hasPublicComments())
		{			
			include_once("./Services/Notes/classes/class.ilNoteGUI.php");
						
			if(!$current_blog)
			{
				$note_gui = new ilNoteGUI($portfolio_id, 0, "pf");
				$note_gui->setRepositoryMode(false);
			}
			// including blog object comments here  (or ilCtrl won't work)
			else
			{
				$note_gui = new ilNoteGUI($current_blog, 0, "blog");
			}
			
			$note_gui->enablePublicNotes(true);
			$note_gui->enablePrivateNotes(false);
			$note_gui->enablePublicNotesDeletion($ilUser->getId() == $user_id);
						
			$next_class = $ilCtrl->getNextClass($this);
			if ($next_class == "ilnotegui")
			{
				$notes = $ilCtrl->forwardCommand($note_gui);
			}
			else
			{
				$notes = $note_gui->getNotesHTML();
			}
		}
		
		include_once('classes/class.ilLink.php');
		$goto = ilLink::_getStaticLink($portfolio_id, "prtf", true);
		$goto = "<div style=\"margin:10px;\" class=\"small\"><a href=\"".$goto.
			"\" target=\"blank\">goto test</a></div>";
		
		$name = ilObjUser::_lookupName($user_id);
		$name = $name["lastname"].", ".($t = $name["title"] ? $t . " " : "").$name["firstname"];
		
		// show banner?
		$banner = false;
		$prfa_set = new ilSetting("prfa");
		if($prfa_set->get("banner"))
		{		
			$banner = $this->portfolio->getImageFullPath();
		}
		
		// profile picture
		$ppic = null;
		if($this->portfolio->hasProfilePicture())
		{
			$ppic = ilObjUser::_getPersonalPicturePath($user_id, "big");
		}
		
		include_once("./Services/User/classes/class.ilUserUtil.php");
		$tpl->setFullscreenHeader($this->portfolio->getTitle(), 
			$name, 	
			$ppic,
			$banner,
			$this->portfolio->getBackgroundColor(),
			$this->portfolio->getFontColor());
		
		$tpl->setContent($content.$goto.$notes);			
		$tpl->setFrameFixedWidth(true);
		
		echo $tpl->show("DEFAULT", true, true);
		exit();
	}
			
	function export()
	{
		include_once "Services/Portfolio/classes/class.ilPortfolioHTMLExport.php";
		$export = new ilPortfolioHTMLExport($this, $this->portfolio);
		$zip = $export->buildExportFile();
		
	    ilUtil::deliverFile($zip, $this->portfolio->getTitle().".zip", '', false, true);
	}
	
	/**
	 * Finalize and submit blog to exercise
	 */
	protected function finalize()
	{
		global $ilUser, $ilCtrl, $lng;
		
		$exc_id = (int)$_REQUEST["exc"];
		$ass_id = (int)$_REQUEST["ass"];
		
		include_once "Services/Portfolio/classes/class.ilPortfolioHTMLExport.php";
		$export = new ilPortfolioHTMLExport($this, $this->portfolio);
		$file = $export->buildExportFile();
		
		$meta = array(
			"name" => $this->portfolio->getId(),
			"tmp_name" => $file,
			"size" => filesize($file),			
			);		
				
		// remove existing files
		include_once "Modules/Exercise/classes/class.ilExAssignment.php";
		$ass = new ilExAssignment($ass_id);
		$uploads = $ass->getDeliveredFiles($exc_id, $ass_id, $ilUser->getID());
		if($uploads)
		{
			$ids = array();
			foreach($uploads as $item)
			{
				$ids[] = $item["returned_id"];
			}
			$ass->deleteDeliveredFiles($exc_id, $ass_id, $ids, $ilUser->getID());
		}
			
		// add export file as upload
		include_once "Modules/Exercise/classes/class.ilObjExercise.php";		
		$exc = new ilObjExercise($exc_id, false);
		$exc->deliverFile($meta, $ass_id, $ilUser->getID(), true);		
		
		ilUtil::sendSuccess($lng->txt("prtf_finalized"), true);
		$ilCtrl->redirect($this, "pages");
	}
	
	protected function setLocator($a_page_id = null)
	{
		global $ilLocator, $lng, $ilCtrl, $tpl;
		
		$ilCtrl->setParameter($this, "prt_id", "");
		$ilLocator->addItem($lng->txt("portfolio"),
			$ilCtrl->getLinkTarget($this, "show"));
		
		if($this->portfolio)
		{
			$ilCtrl->setParameter($this, "prt_id", $this->portfolio->getId());	
			$ilLocator->addItem($this->portfolio->getTitle(),
				$ilCtrl->getLinkTarget($this, "pages"));
		}
		
		if($a_page_id)
		{
			include_once "Services/Portfolio/classes/class.ilPortfolioPage.php";			
			$page = new ilPortfolioPage($this->portfolio->getId(), $a_page_id);
			$title = $page->getTitle();
			if($page->getType() == ilPortfolioPage::TYPE_BLOG)
			{
				$title = ilObject::_lookupTitle($title);
			}
			$ilCtrl->setParameterByClass("ilportfoliopagegui", "ppage", $a_page_id);	
			$ilLocator->addItem($title,
				$ilCtrl->getLinkTargetByClass("ilportfoliopagegui", "edit"));			
		}
		
		$tpl->setLocator();		
	}
	
	/**
	 * Select target portfolio for page(s) copy
	 */
	function copyPageForm($a_form = null)
	{
		global $ilCtrl, $tpl, $lng, $ilUser;

		if (!is_array($_POST["prtf_pages"]) || count($_POST["prtf_pages"]) == 0)
		{
			ilUtil::sendInfo($lng->txt("no_checkbox"), true);
			$ilCtrl->redirect($this, "pages");
		}
		else
		{			
			if(!$a_form)
			{
				$a_form = $this->initCopyPageForm();
			}
		
			foreach($_POST["prtf_pages"] as $page_id)
			{
				$item = new ilHiddenInputGUI("prtf_pages[]");
				$item->setValue($page_id);
				$a_form->addItem($item);
			}
			
			$tpl->setContent($a_form->getHTML());
		}		
	}
	
	function copyPage()
	{		
		global $ilCtrl, $lng;
		
		$form = $this->initCopyPageForm();
		if($form->checkInput())
		{
			// existing
			if($form->getInput("target") == "old")
			{
				$portfolio_id = $form->getInput("prtf");
				$portfolio = new ilObjPortfolio($portfolio_id, false);				
			}
			// new
			else
			{
				$portfolio = new ilObjPortfolio();
				$portfolio->setTitle($form->getInput("title"));
				$portfolio->create();		
				$portfolio_id = $portfolio->getId();
			}
			
			// copy page(s)
			include_once "Services/Portfolio/classes/class.ilPortfolioPage.php";
			foreach($_POST["prtf_pages"] as $page_id)
			{				
				$source = new ilPortfolioPage($portfolio_id, $page_id);
			
				$target = new ilPortfolioPage($portfolio_id);
				$target->setXMLContent($source->copyXmlContent());
				$target->setType($source->getType());
				$target->setTitle($source->getTitle());
				$target->create();							
			}
				
			ilUtil::sendSuccess($lng->txt("prtf_pages_copied"), true);
			$ilCtrl->redirect($this, "pages");
		}
		
		$form->setValuesByPost();
		$this->copyPageForm($form);
	}
	
	function initCopyPageForm()
	{
		global $lng, $ilCtrl, $ilUser;
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));		
		$form->setTitle($lng->txt("prtf_copy_page"));			

		$tgt = new ilRadioGroupInputGUI($lng->txt("target"), "target");
		$tgt->setRequired(true);
		$form->addItem($tgt);

		$all = ilObjPortfolio::getPortfoliosOfUser($ilUser->getId());			
		if(sizeof($all) > 1)
		{			
			$old = new ilRadioOption($lng->txt("prtf_existing_portfolio"), "old");
			$tgt->addOption($old);

			$options = array();
			foreach($all as $item)
			{
				if($item["id"] != $this->portfolio->getId())
				{
					$options[$item["id"]] = $item["title"]; 
				}
			}				
			$prtf = new ilSelectInputGUI($lng->txt("portfolio"), "prtf");
			$prtf->setRequired(true);
			$prtf->setOptions($options);
			$old->addSubItem($prtf);
		}

		$new = new ilRadioOption($lng->txt("prtf_new_portfolio"), "new");
		$tgt->addOption($new);

		// 1st page
		$tf = new ilTextInputGUI($lng->txt("title"), "title");
		$tf->setMaxLength(128);
		$tf->setSize(40);
		$tf->setRequired(true);
		$new->addSubItem($tf);		

		$form->addCommandButton("copyPage", $lng->txt("save"));
		$form->addCommandButton("pages", $lng->txt("cancel"));
		
		return $form;
	}
	
	function _goto($a_target)
	{
		$id = explode("_", $a_target);
	
		$_GET["baseClass"] = "ilPersonalDesktopGUI";	
		$_GET["prt_id"] = $id[0];		
		$_GET["cmd"] = "jumpToPortfolio";		
		include("ilias.php");
		exit;
	}
}

?>