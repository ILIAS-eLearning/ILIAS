<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Modules/Portfolio/classes/class.ilObjPortfolioBaseGUI.php');

/**
 * Portfolio view gui class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_Calls ilObjPortfolioGUI: ilPortfolioPageGUI, ilPageObjectGUI
 * @ilCtrl_Calls ilObjPortfolioGUI: ilWorkspaceAccessGUI, ilNoteGUI, ilObjStyleSheetGUI
 *
 * @ingroup ModulesPortfolio
 */
class ilObjPortfolioGUI extends ilObjPortfolioBaseGUI
{		
	protected $ws_access; // [ilWorkspaceAccessHandler]
	
	public function __construct($a_id = 0)
	{		
		parent::__construct($a_id, self::PORTFOLIO_OBJECT_ID, 0);
	}
	
	public function getType()
	{
		return "prtf";
	}
	
	protected function checkPermissionBool($a_perm, $a_cmd = "", $a_type = "", $a_node_id = null)
	{				
		if($a_perm == "create")
		{
			return true;
		}
		if(!$a_node_id)
		{
			$a_node_id = $this->obj_id;
		}
		return $this->access_handler->checkAccess($a_perm, "", $a_node_id);					
	}
	
	function executeCommand()
	{					
		// goto link to portfolio page
		if($_GET["gtp"])
		{		
			$_GET["user_page"] = $_GET["gtp"];	
		}
		
		$title = $this->lng->txt("portfolio");
		if($this->object)
		{
			$title .= ": ".$this->object->getTitle();	
		}
		$this->tpl->setTitle($title);
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_prtf.svg"),
			$this->lng->txt("portfolio"));

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd("view");		
		
		/*
		if($_REQUEST["ecal"])
		{	
			$cmd = "preview";
			$next_class = "";
		}
		*/
		
		switch($next_class)
		{
			case "ilworkspaceaccessgui";	
				if($this->checkPermissionBool("write"))
				{
					$this->setTabs();
					$this->tabs_gui->activateTab("share");
										
					$this->tpl->setPermanentLink("prtf", $this->object->getId());

					include_once('./Services/PersonalWorkspace/classes/class.ilWorkspaceAccessGUI.php');
					$wspacc = new ilWorkspaceAccessGUI($this->object->getId(), $this->access_handler, true, $plink);
					$this->ctrl->forwardCommand($wspacc);
				}
				break;
			
			case 'ilportfoliopagegui':									
				if($this->determinePageCall()) 
				{	
					// only in edit mode
					$this->addLocator();	
										
					include_once "Services/Form/classes/class.ilFileInputGUI.php";
					ilFileInputGUI::setPersonalWorkspaceQuotaCheck(true);						
				}
				$this->handlePageCall($cmd);			
				break;
				
			case "ilnotegui";				
				$this->preview();				
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
				if($cmd != "preview")
				{
					$this->addLocator();
					$this->setTabs();
				}
				$this->$cmd();
				break;
		}

		return true;
	}
	
	protected function setTabs()
	{
		global $ilHelp;	
		
		$ilHelp->setScreenIdComponent("prtf");
			
		if($this->checkPermissionBool("write"))
		{
			$this->tabs_gui->addTab("pages",
				$this->lng->txt("content"),
				$this->ctrl->getLinkTarget($this, "view"));				
							
			$this->tabs_gui->addTab("settings",
				$this->lng->txt("settings"),
				$this->ctrl->getLinkTarget($this, "edit"));
		
			$this->tabs_gui->addNonTabbedLink("preview", 
				$this->lng->txt("user_profile_preview"),
				$this->ctrl->getLinkTarget($this, "preview"));	
						
			$this->lng->loadLanguageModule("wsp");
			$this->tabs_gui->addTab("share",
				$this->lng->txt("wsp_permissions"),
				$this->ctrl->getLinkTargetByClass("ilworkspaceaccessgui", "share"));
		}		
	}
	
	protected function addLocator()
	{		
		if(!$this->creation_mode)
		{
			$this->ctrl->setParameter($this, "prt_id", $this->object->getId());	
		}
		
		parent::addLocatorItems();
		
		$this->tpl->setLocator();
	}
			
	
	//
	// CREATE/EDIT
	// 
	
	protected function initCreationForms($a_new_type)
	{		
		return array(self::CFORM_NEW => $this->initCreateForm($a_new_type));		
	}
	
	protected function initCreateForm($a_new_type)
	{
		global $ilSetting;
		
		$this->ctrl->setParameter($this, "new_type", $this->getType());
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));		
				
		// title
		$ti = new ilTextInputGUI($this->lng->txt("title"), "title");
		$ti->setSize(min(40, ilObject::TITLE_LENGTH));
		$ti->setMaxLength(ilObject::TITLE_LENGTH);
		$ti->setRequired(true);
		$form->addItem($ti);

		/* description
		$ta = new ilTextAreaInputGUI($this->lng->txt("description"), "desc");
		$ta->setCols(40);
		$ta->setRows(2);
		$form->addItem($ta);		
		*/
						
		$main = new ilRadioGroupInputGUI($this->lng->txt("prtf_creation_mode"), "mode");
		$main->setValue("mode_scratch");
		$form->addItem($main);
		
		$opt_scratch = new ilRadioOption($this->lng->txt("prtf_creation_mode_scratch"), "mode_scratch");
		$main->addOption($opt_scratch);
		
		
		// 1st page
		
		$type = new ilRadioGroupInputGUI($this->lng->txt("prtf_first_page_title"), "ptype");
		$type->setRequired(true);
		$opt_scratch->addSubItem($type);

		$type_page = new ilRadioOption($this->lng->txt("page"), "page");
		$type->addOption($type_page);
		
		// page type: page
		$tf = new ilTextInputGUI($this->lng->txt("title"), "fpage");
		$tf->setMaxLength(128);
		$tf->setSize(40);
		$tf->setRequired(true);
		$type_page->addSubItem($tf);	

		// page templates
		include_once "Services/Style/classes/class.ilPageLayout.php";
		$templates = ilPageLayout::activeLayouts(false, ilPageLayout::MODULE_PORTFOLIO);
		if($templates)
		{			
			$options = array(0 => $this->lng->txt("none"));
			foreach ($templates as $templ)
			{
				$templ->readObject();
				$options[$templ->getId()] = $templ->getTitle();			
			}

			$use_template = new ilSelectInputGUI($this->lng->txt("prtf_use_page_layout"), "tmpl");
			$use_template->setRequired(true);
			$use_template->setOptions($options);
			$type_page->addSubItem($use_template);
		}

		// page type: blog
		if(!$ilSetting->get('disable_wsp_blogs'))
		{
			$options = array();
			include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
			$tree = new ilWorkspaceTree($this->user_id);
			$root = $tree->readRootId();
			if($root)
			{
				$root = $tree->getNodeData($root);
				foreach ($tree->getSubTree($root) as $node)
				{
					if ($node["type"] == "blog")
					{
						$options[$node["obj_id"]] = $node["title"];
					}
				}
				asort($options);		
			}
			if(sizeof($options))
			{			
				$type_blog = new ilRadioOption($this->lng->txt("obj_blog"), "blog");
				$type->addOption($type_blog);

				$obj = new ilSelectInputGUI($this->lng->txt("obj_blog"), "blog");
				$obj->setRequired(true);
				$obj->setOptions(array(""=>$this->lng->txt("please_select"))+$options);
				$type_blog->addSubItem($obj);
			}
			else
			{
				ilUtil::sendInfo($this->lng->txt("prtf_no_blogs_info"));				
				$type->setValue("page");
			}
		}
				
		
		// portfolio templates
		
		$opt_tmpl = new ilRadioOption($this->lng->txt("prtf_creation_mode_template"), "mode_tmpl");
		$main->addOption($opt_tmpl);
				
		include_once "Modules/Portfolio/classes/class.ilObjPortfolioTemplate.php";
		$templates = ilObjPortfolioTemplate::getAvailablePortfolioTemplates();
		if(!sizeof($templates))
		{
			$opt_tmpl->setDisabled(true);			
		}
		else
		{			
			$tmpl = new ilSelectInputGUI($this->lng->txt("obj_prtt"), "prtt");
			$tmpl->setRequired(true);
			$tmpl->setOptions(array(""=>$this->lng->txt("please_select"))+$templates);
			$opt_tmpl->addSubItem($tmpl);
			
			// incoming from repository
			if((int)$_REQUEST["prtt_pre"])
			{
				$tmpl->setValue((int)$_REQUEST["prtt_pre"]);
				$main->setValue("mode_tmpl");
			}
		}
		
		
		$form->setTitle($this->lng->txt("prtf_create_portfolio"));
		$form->addCommandButton("save", $this->lng->txt("save"));
		$form->addCommandButton("toRepository", $this->lng->txt("cancel"));
		
		return $form;
	}
	
	public function save()
	{		
		$form = $this->initCreateForm("prtf");
		if($form->checkInput())
		{
			// trigger portfolio template "import" process
			if($form->getInput("mode") == "mode_tmpl")
			{					
				$_REQUEST["pt"] = $form->getInput("title");
				return $this->createPortfolioFromTemplate();				
			}			
		}
		
		return parent::save();
	}
	
	protected function afterSave(ilObject $a_new_object)
	{		
		// create 1st page / blog
		include_once("Modules/Portfolio/classes/class.ilPortfolioPage.php");
		$page = $this->getPageInstance(null, $a_new_object->getId());
		if($_POST["ptype"] == "page")
		{				
			$page->setType(ilPortfolioPage::TYPE_PAGE);
			$page->setTitle($_POST["fpage"]);		

			// use template as basis
			$layout_id = $_POST["tmpl"];
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
			$page->setTitle($_POST["blog"]);								
		}
		$page->create();

		ilUtil::sendSuccess($this->lng->txt("prtf_portfolio_created"), true);
		$this->ctrl->setParameter($this, "prt_id", $a_new_object->getId());
		$this->ctrl->redirect($this, "view");
	}
	
	protected function toRepository()
	{		
		global $ilAccess;
		
		// return to exercise (portfolio assignment)
		$exc_ref_id = (int)$_REQUEST["exc_id"];
		if($exc_ref_id &&
			$ilAccess->checkAccess("read", "", $exc_ref_id))
		{
			include_once "Services/Link/classes/class.ilLink.php";
			ilUtil::redirect(ilLink::_getLink($exc_ref_id, "exc"));
		}
		
		$this->ctrl->redirectByClass("ilportfoliorepositorygui", "show");
	}	
	
	protected function initEditForm()
	{
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));	
		
		// title
		$ti = new ilTextInputGUI($this->lng->txt("title"), "title");
		$ti->setSize(min(40, ilObject::TITLE_LENGTH));
		$ti->setMaxLength(ilObject::TITLE_LENGTH);
		$ti->setRequired(true);
		$ti->setValue($this->object->getTitle());
		$form->addItem($ti);

		/* description
		$ta = new ilTextAreaInputGUI($this->lng->txt("description"), "desc");
		$ta->setCols(40);
		$ta->setRows(2);
		$ta->setValue($this->object->getDescription());
		$form->addItem($ta);		
		*/
		
		// :TODO: online 
		$online = new ilCheckboxInputGUI($this->lng->txt("online"), "online");
		$online->setChecked($this->object->isOnline());
		$form->addItem($online);
		
		$this->initEditCustomForm($form);

		$form->setTitle($this->lng->txt("prtf_edit_portfolio"));
		$form->addCommandButton("update", $this->lng->txt("save"));
		$form->addCommandButton("view", $this->lng->txt("cancel"));		

		return $form;		
	}	
	
	protected function getEditFormCustomValues(array &$a_values)
	{
		$a_values["online"] = $this->object->isOnline();
		
		parent::getEditFormCustomValues($a_values);
	}	
	
	public function updateCustom(ilPropertyFormGUI $a_form)
	{				
		$this->object->setOnline($a_form->getInput("online"));

		// if portfolio is not online, it cannot be default
		if(!$a_form->getInput("online"))
		{
			ilObjPortfolio::setUserDefault($this->user_id, 0);
		}		
		
		parent::updateCustom($a_form);		
	}
		
	
	//
	// PAGES
	//	
	
	/**
	 * Get portfolio template page instance
	 * 
	 * @param int $a_page_id
	 * @param int $a_portfolio_id
	 * @return ilPortfolioPage
	 */
	protected function getPageInstance($a_page_id = null, $a_portfolio_id = null)
	{		
		// #11531
		if(!$a_portfolio_id && $this->object)
		{
			$a_portfolio_id = $this->object->getId();
		}
		include_once "Modules/Portfolio/classes/class.ilPortfolioPage.php";			
		$page = new ilPortfolioPage($a_page_id);
		$page->setPortfolioId($a_portfolio_id);
		return $page;
	}
	
	/**
	 * Get portfolio template page gui instance
	 * 
	 * @param int $a_page_id
	 * @return ilPortfolioPageGUI
	 */
	protected function getPageGUIInstance($a_page_id)
	{
		include_once("Modules/Portfolio/classes/class.ilPortfolioPageGUI.php");
		$page_gui = new ilPortfolioPageGUI(
			$this->object->getId(),
			$a_page_id, 
			0, 
			$this->object->hasPublicComments()
		);
		$page_gui->setAdditional($this->getAdditional());
		return $page_gui;
	}
	
	public function getPageGUIClassName()
	{
		return "ilportfoliopagegui";
	}
	
	protected function initCopyPageFormOptions(ilFormPropertyGUI $a_tgt)
	{
		$old = new ilRadioOption($this->lng->txt("prtf_existing_portfolio"), "old");
		$a_tgt->addOption($old);

		$options = array();
		$all = ilObjPortfolio::getPortfoliosOfUser($this->user_id);			
		foreach($all as $item)
		{				
			$options[$item["id"]] = $item["title"]; 				
		}				
		$prtf = new ilSelectInputGUI($this->lng->txt("portfolio"), "prtf");
		$prtf->setRequired(true);
		$prtf->setOptions($options);
		$old->addSubItem($prtf);	

		$new = new ilRadioOption($this->lng->txt("prtf_new_portfolio"), "new");
		$a_tgt->addOption($new);

		$tf = new ilTextInputGUI($this->lng->txt("title"), "title");
		$tf->setMaxLength(128);
		$tf->setSize(40);
		$tf->setRequired(true);
		$new->addSubItem($tf);		
	}
	
	
	//
	// BLOG
	// 
	
	/**
	 * Init blog page form
	 *
	 * @param string $a_mode
	 * @return ilPropertyFormGUI
	 */
	public function initBlogForm()
	{		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));

		$options = array();
		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
		$tree = new ilWorkspaceTree($this->user_id);
		$root = $tree->readRootId();
		if($root)
		{
			$root = $tree->getNodeData($root);
			foreach ($tree->getSubTree($root, true, "blog") as $node)
			{				
				$options[$node["obj_id"]] = $node["title"];				
			}
			asort($options);	
		}
		
		// no blogs to add?
		if(!sizeof($options))
		{
			ilUtil::sendInfo($this->lng->txt("prtf_no_blogs_info"), true);
			$this->ctrl->redirect($this, "view");
		}
		
		$obj = new ilSelectInputGUI($this->lng->txt("obj_blog"), "blog");
		$obj->setRequired(true);
		$obj->setOptions($options);
		$form->addItem($obj);

		$form->setTitle($this->lng->txt("prtf_add_blog").": ".
			$this->object->getTitle());
		$form->addCommandButton("saveBlog", $this->lng->txt("save"));
		$form->addCommandButton("view", $this->lng->txt("cancel"));
		
		return $form;
	}
	
	/**
	 * Create new portfolio blog page
	 */
	public function saveBlog()
	{		
		$form = $this->initBlogForm();
		if ($form->checkInput() && $this->checkPermissionBool("write"))
		{
			$page = $this->getPageInstance();
			$page->setType(ilPortfolioPage::TYPE_BLOG);		
			$page->setTitle($form->getInput("blog"));									
			$page->create();

			ilUtil::sendSuccess($this->lng->txt("prtf_blog_page_created"), true);
			$this->ctrl->redirect($this, "view");
		}

		$this->tabs_gui->clearTargets();
		$this->tabs_gui->setBackTarget($this->lng->txt("back"),
			$this->ctrl->getLinkTarget($this, "view"));

		$form->setValuesByPost();
		$this->tpl->setContent($form->getHtml());
	}
	
	
	//
	// EXERCISE ASSIGNMENT
	// 
	
	function getExerciseInfo($a_assignment_id, $a_add_submit = false)
	{				
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

		$info = sprintf($this->lng->txt("prtf_exercise_info"), 
			$ass->getTitle(),
			"<a href=\"".$exc_link."\">".
			ilObject::_lookupTitle($exercise_id)."</a>");
		
		// submit button
		if($a_add_submit && !$times_up)
		{
			$this->ctrl->setParameter($this, "exc", $exercise_id);				
			$this->ctrl->setParameter($this, "ass", $a_assignment_id);
			$submit_link = $this->ctrl->getLinkTarget($this, "finalize");
			$this->ctrl->setParameter($this, "ass", "");
			$this->ctrl->setParameter($this, "exc", "");	
			
			include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";
			$button = ilLinkButton::getInstance();
			$button->setCaption("prtf_finalize_portfolio");
			$button->setPrimary(true);
			$button->setUrl($submit_link);			
			$info .= " ".$button->render();			
		}
		
		// submitted files
		$submitted = ilExAssignment::getDeliveredFiles($exercise_id, $a_assignment_id, $this->user_id, true);
		if($submitted)
		{
			$submitted = array_pop($submitted);
			
			$this->ctrl->setParameter($this, "ass", $a_assignment_id);
			$dl_link = $this->ctrl->getLinkTarget($this, "downloadExcSubFile");
			$this->ctrl->setParameter($this, "ass", "");
			
			$rel = ilDatePresentation::useRelativeDates();
			ilDatePresentation::setUseRelativeDates(false);
			
			include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";
			$button = ilLinkButton::getInstance();
			$button->setCaption("download");
			$button->setUrl($dl_link);			
			
			$info .= "<br />".sprintf($this->lng->txt("prtf_exercise_submitted_info"), 
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
				$this->ctrl->setParameter($this, "ass", $a_assignment_id);
				$this->ctrl->setParameter($this, "file", urlencode($file["name"]));
				$dl_link = $this->ctrl->getLinkTarget($this, "downloadExcAssFile");
				$this->ctrl->setParameter($this, "file", "");			
				$this->ctrl->setParameter($this, "ass", "");			
				
				$tooltip .= $file["name"].": <a href=\"".$dl_link."\">".
					$this->lng->txt("download")."</a>";										
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

			$info .= "<div id=\"".$ol_id."_tr\"><a href=\"#\">".$this->lng->txt("exc_instruction")."</a></div>".
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
		if($_GET["ass"])
		{		
			include_once "Modules/Exercise/classes/class.ilExAssignment.php";			
			$ass = new ilExAssignment((int)$_GET["ass"]);
			
			$submitted = ilExAssignment::getDeliveredFiles($ass->getExerciseId(), $ass->getId(), $this->user_id);
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
	 * Finalize and submit portfolio to exercise
	 */
	protected function finalize()
	{		
		// to make exercise gui load assignment
		$_GET["ass_id"] = $_REQUEST["ass"];
		
		// #11173 - ref_id is needed for notifications
		$exc_ref_id = array_shift(ilObject::_getAllReferences($_REQUEST["exc"]));
		
		include_once "Modules/Exercise/classes/class.ilObjExerciseGUI.php";
		$exc_gui = new ilObjExerciseGUI(null, $exc_ref_id, true);
		$exc_gui->submitPortfolio($this->object->getId());
		
		ilUtil::sendSuccess($this->lng->txt("prtf_finalized"), true);
		$this->ctrl->redirect($this, "view");
	}
	
	
	//
	// CREATE FROM TEMPLATE
	// 
	
	protected function createPortfolioFromTemplate(ilPropertyFormGUI $a_form = null)
	{
		$title = trim($_REQUEST["pt"]);
		$prtt_id = (int)$_REQUEST["prtt"];
		
		// valid template?		
		include_once "Modules/Portfolio/classes/class.ilObjPortfolioTemplate.php";		
		$templates = array_keys(ilObjPortfolioTemplate::getAvailablePortfolioTemplates());
		if(!sizeof($templates) || !in_array($prtt_id, $templates))
		{
			$this->toRepository();
		}
		unset($templates);
				
		$this->ctrl->setParameter($this, "prtt", $prtt_id);
		
		if(!$a_form)
		{
			$a_form = $this->initCreatePortfolioFromTemplateForm($prtt_id, $title);
		}
		if($a_form)
		{						
			$this->tpl->setContent($a_form->getHTML());						
		}
		else
		{
			$this->createPortfolioFromTemplateProcess(false);
		}		
	}
	
	protected function initCreatePortfolioFromTemplateForm($a_prtt_id, $a_title)
	{					
		global $ilSetting, $ilUser;
				
		if((int)$_REQUEST["exc_id"])
		{
			$this->ctrl->setParameter($this, "exc_id", (int)$_REQUEST["exc_id"]);		
			$this->ctrl->setParameter($this, "ass_id", (int)$_REQUEST["ass_id"]);		
		}		
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));		
		
		$tmpl = new ilNonEditableValueGUI($this->lng->txt("obj_prtt"));
		$tmpl->setValue(ilObject::_lookupTitle($a_prtt_id));
		$form->addItem($tmpl);
				
		$title = new ilNonEditableValueGUI($this->lng->txt("title"), "pt");
		$title->setValue($a_title);
		$form->addItem($title);		
		
		// gather user blogs
		if(!$ilSetting->get('disable_wsp_blogs'))
		{
			$blog_options = array();
			include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
			$tree = new ilWorkspaceTree($this->user_id);
			$root = $tree->readRootId();
			if($root)
			{
				$root = $tree->getNodeData($root);
				foreach ($tree->getSubTree($root, true, "blog") as $node)
				{					
					$blog_options[$node["obj_id"]] = $node["title"];					
				}
				asort($blog_options);		
			}
		}
			
		$has_form_content = false;
				
		include_once "Services/WebDAV/classes/class.ilDiskQuotaActivationChecker.php";			
		$check_quota = ilDiskQuotaActivationChecker::_isPersonalWorkspaceActive();			
		$quota_sum = 0;				
							
		include_once "Services/Skill/classes/class.ilPersonalSkill.php";
		$pskills = array_keys(ilPersonalSkill::getSelectedUserSkills($ilUser->getId()));	
		$skill_ids = array();
		
		include_once "Modules/Portfolio/classes/class.ilPortfolioTemplatePage.php";
		foreach(ilPortfolioTemplatePage::getAllPages($a_prtt_id) as $page)
		{
			switch($page["type"])
			{
				case ilPortfolioTemplatePage::TYPE_PAGE:	
					// skills
					$source_page = new ilPortfolioTemplatePage($page["id"]);	
					$source_page->buildDom(true);
					$dom = $source_page->getDom();					
					if($dom instanceof php4DOMDocument)
					{						
						$dom = $dom->myDOMDocument;
					}
					$xpath = new DOMXPath($dom);
					$nodes = $xpath->query("//PageContent/Skills");
					foreach($nodes as $node)
					{
						$skill_id = $node->getAttribute("Id");
						if(!in_array($skill_id, $pskills))
						{
							$skill_ids[] = $skill_id;
						}
					}
					unset($nodes);
					unset($xpath);
					unset($dom);
					if($check_quota)
					{																									
						$quota_sum += $source_page->getPageDiskSize();
					}	
					if(sizeof($skill_ids))
					{
						$has_form_content = true;
					}
					break;
				
				case ilPortfolioTemplatePage::TYPE_BLOG_TEMPLATE:
					if(!$ilSetting->get('disable_wsp_blogs'))
					{
						$has_form_content = true;
						
						$field_id = "blog_".$page["id"];
											
						$blog = new ilRadioGroupInputGUI($this->lng->txt("obj_blog").": ".
							$page["title"], $field_id);
						$blog->setRequired(true);
						$blog->setValue("blog_create");
						$form->addItem($blog);

						$new_blog = new ilRadioOption($this->lng->txt("prtf_template_import_blog_create"), "blog_create");
						$blog->addOption($new_blog);
						
						$title = new ilTextInputGUI($this->lng->txt("title"), $field_id."_create_title");
						$title->setRequired(true);
						$new_blog->addSubItem($title);
						
						if(sizeof($blog_options))
						{			
							$reuse_blog = new ilRadioOption($this->lng->txt("prtf_template_import_blog_reuse"), "blog_resuse");
							$blog->addOption($reuse_blog);

							$obj = new ilSelectInputGUI($this->lng->txt("obj_blog"), $field_id."_reuse_blog");
							$obj->setRequired(true);
							$obj->setOptions(array(""=>$this->lng->txt("please_select"))+$blog_options);
							$reuse_blog->addSubItem($obj);
						}		
												
						$blog->addOption(new ilRadioOption($this->lng->txt("prtf_template_import_blog_ignore"), "blog_ignore"));
					}					
					break;								
			}
		}		
		
		if($skill_ids)
		{
			include_once "Services/Skill/classes/class.ilSkillTreeNode.php";						
			$skills = new ilCheckboxGroupInputGUI($this->lng->txt("skills"), "skill_ids");
			$skills->setInfo($this->lng->txt("prtf_template_import_new_skills"));
			$skills->setValue($skill_ids);
			foreach($skill_ids as $skill_id)
			{				
				$skills->addOption(new ilCheckboxOption(ilSkillTreeNode::_lookupTitle($skill_id), $skill_id));
			}					
			$form->addItem($skills);			
		}
		
		if($quota_sum)
		{
			include_once "Services/DiskQuota/classes/class.ilDiskQuotaHandler.php";			
			if(!ilDiskQuotaHandler::isUploadPossible($quota_sum))
			{
				ilUtil::sendFailure($this->lng->txt("prtf_template_import_quota_failure"), true);				
				$this->ctrl->redirect($this, "create");				
			}
		}
		
		// no dialog needed, go ahead
		if(!$has_form_content)
		{
			return;
		}
		
		$form->setTitle($this->lng->txt("prtf_creation_mode").": ".$this->lng->txt("prtf_creation_mode_template"));
		$form->addCommandButton("createPortfolioFromTemplateProcess", $this->lng->txt("continue"));
		$form->addCommandButton("toRepository", $this->lng->txt("cancel"));
		
		return $form;
	}	
	
	protected function createPortfolioFromTemplateProcess($a_process_form = true)
	{
		global $ilSetting, $ilUser, $ilAccess;
		
		$title = trim($_REQUEST["pt"]);
		$prtt_id = (int)$_REQUEST["prtt"];
		
		// valid template?		
		include_once "Modules/Portfolio/classes/class.ilObjPortfolioTemplate.php";		
		$templates = array_keys(ilObjPortfolioTemplate::getAvailablePortfolioTemplates());
		if(!sizeof($templates) || !in_array($prtt_id, $templates))
		{
			$this->toRepository();
		}
		unset($templates);
		
		// build page recipe (aka import form values)
		$recipe = null;
		if($a_process_form)
		{			
			$this->ctrl->setParameter($this, "prtt", $prtt_id);		
			
			$form = $this->initCreatePortfolioFromTemplateForm($prtt_id, $title);
			if($form->checkInput())
			{
				include_once "Modules/Portfolio/classes/class.ilPortfolioTemplatePage.php";
				foreach(ilPortfolioTemplatePage::getAllPages($prtt_id) as $page)
				{
					switch($page["type"])
					{												
						case ilPortfolioTemplatePage::TYPE_BLOG_TEMPLATE:
							if(!$ilSetting->get('disable_wsp_blogs'))
							{							
								$field_id = "blog_".$page["id"];
								switch($form->getInput($field_id))
								{
									case "blog_create":
										$recipe[$page["id"]] = array("blog", "create", 
											trim($form->getInput($field_id."_create_title")));
										break;

									case "blog_resuse":
										$recipe[$page["id"]] = array("blog", "reuse", 
											(int)$form->getInput($field_id."_reuse_blog"));
										break;

									case "blog_ignore":
										$recipe[$page["id"]] = array("blog", "ignore");
										break;
								}
							}			
							break;						
					}
				}
				
				$recipe["skills"] = (array)$form->getInput("skill_ids");														
			}
			else
			{
				$form->setValuesByPost();
				return $this->createPortfolioFromTemplate($form);				
			}			
		}
		
		$source = new ilObjPortfolioTemplate($prtt_id, false);
		
		// create portfolio		
		include_once "Modules/Portfolio/classes/class.ilObjPortfolio.php";
		$target = new ilObjPortfolio();				
		$target->setTitle($title);
		$target->create();
		$target_id = $target->getId();
				
		$source->clonePagesAndSettings($source, $target, $recipe);
						
		// link portfolio to exercise assignment
		$exc_ref_id = (int)$_REQUEST["exc_id"];
		$ass_id = (int)$_REQUEST["ass_id"];		
		if($exc_ref_id &&
			$ass_id && 
			$ilAccess->checkAccess("read", "", $exc_ref_id))
		{			
			include_once "Modules/Exercise/classes/class.ilObjExercise.php";
			include_once "Modules/Exercise/classes/class.ilExAssignment.php";							
			$exc = new ilObjExercise($exc_ref_id);						
			$ass = new ilExAssignment($ass_id);			
			if($ass->getExerciseId() == $exc->getId() &&
				$ass->getType() == ilExAssignment::TYPE_PORTFOLIO)
			{				
				$exc->addResourceObject($target_id, $ass_id, $ilUser->getId());
			}
		}
		
		ilUtil::sendSuccess($this->lng->txt("prtf_portfolio_created"), true);
		$this->ctrl->setParameter($this, "prt_id", $target_id);
		$this->ctrl->redirect($this, "view");
	}

	public static function _goto($a_target)
	{
		$id = explode("_", $a_target);
		
		$_GET["baseClass"] = "ilsharedresourceGUI";	
		$_GET["prt_id"] = $id[0];		
		if(sizeof($id) == 2)
		{
			$_GET["gtp"] = $id[1];
		}		
		
		include("ilias.php");
		exit;
	}
}

?>