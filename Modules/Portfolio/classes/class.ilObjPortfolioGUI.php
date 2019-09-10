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
 * @ilCtrl_Calls ilObjPortfolioGUI: ilWorkspaceAccessGUI, ilNoteGUI
 * @ilCtrl_Calls ilObjPortfolioGUI: ilObjStyleSheetGUI, ilPortfolioExerciseGUI
 *
 * @ingroup ModulesPortfolio
 */
class ilObjPortfolioGUI extends ilObjPortfolioBaseGUI
{		
	/**
	 * @var ilHelpGUI
	 */
	protected $help;

	protected $ws_access; // [ilWorkspaceAccessHandler]

	/**
	 * @var \ILIAS\DI\UIServices
	 */
	protected $ui;

	/**
	 * @var ilPortfolioDeclarationOfAuthorship
	 */
	protected $declaration_authorship;
	
	public function __construct($a_id = 0)
	{		
		global $DIC;

		$this->lng = $DIC->language();
		$this->help = $DIC["ilHelp"];
		$this->settings = $DIC->settings();
		$this->access = $DIC->access();
		$this->user = $DIC->user();
		$this->ctrl = $DIC->ctrl();
		$this->ui = $DIC->ui();

		parent::__construct($a_id, self::PORTFOLIO_OBJECT_ID, 0);
		$this->declaration_authorship = new ilPortfolioDeclarationOfAuthorship();

		$this->ctrl->saveParameter($this, "exc_back_ref_id");
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
		$lng = $this->lng;
		
		$this->checkPermission("read");

		// goto link to portfolio page
		if($_GET["gtp"])
		{		
			$_GET["user_page"] = $_GET["gtp"];	
		}
		
		$this->setTitleAndDescription();
		
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
					
					if($this->access_handler->getPermissions($this->object->getId()) &&
						!$this->object->isOnline())
					{
						//ilUtil::sendInfo($lng->txt("prtf_shared_offline_info"));
					}
										
					$this->tpl->setPermanentLink("prtf", $this->object->getId());

					include_once('./Services/PersonalWorkspace/classes/class.ilWorkspaceAccessGUI.php');
					$wspacc = new ilWorkspaceAccessGUI($this->object->getId(), $this->access_handler, true);
					$wspacc->setBlockingMessage($this->getOfflineMessage());
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
				include_once ("./Services/Style/Content/classes/class.ilObjStyleSheetGUI.php");
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
				
			case "ilportfolioexercisegui":
				$this->ctrl->setReturn($this, "view");
				include_once "Modules/Portfolio/classes/class.ilPortfolioExerciseGUI.php";
				$gui = new ilPortfolioExerciseGUI($this->user_id, $this->object->getId());
				$this->ctrl->forwardCommand($gui);
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
		$ilHelp = $this->help;
		
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
	
	protected function setTitleAndDescription() 
	{
		// parent::setTitleAndDescription();
		
		$title = $this->lng->txt("portfolio");
		if($this->object)
		{
			$title .= ": ".$this->object->getTitle();	
		}
		$this->tpl->setTitle($title);
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_prtf.svg"),
			$this->lng->txt("portfolio"));
		
		if($this->object &&
			!$this->object->isOnline())
		{
			$this->tpl->setAlertProperties(array(
				array("alert" => true, 
					"property" => $this->lng->txt("status"),
					"value" => $this->lng->txt("offline"))
			));
		}
	}
			
	
	//
	// CREATE/EDIT
	// 

	/**
	 * create new object form
	 *
	 * @access	public
	 */
	public function create()
	{
		$tpl = $this->tpl;
		$ilErr = $this->ilErr;

		$new_type = $_REQUEST["new_type"];

		// add new object to custom parent container
		$this->ctrl->saveParameter($this, "crtptrefid");
		// use forced callback after object creation
		$this->ctrl->saveParameter($this, "crtcb");

		if (!$this->checkPermissionBool("create", "", $new_type))
		{
			$ilErr->raiseError($this->lng->txt("permission_denied"),$ilErr->MESSAGE);
		}
		else
		{
			$this->lng->loadLanguageModule($new_type);
			$this->ctrl->setParameter($this, "new_type", $new_type);

			$forms = $this->initCreationForms($new_type);

			// copy form validation error: do not show other creation forms
			if($_GET["cpfl"] && isset($forms[self::CFORM_CLONE]))
			{
				$forms = array(self::CFORM_CLONE => $forms[self::CFORM_CLONE]);
			}
			$tpl->setContent($this->getCreateInfoMessage().$this->getCreationFormsHTML($forms));
		}
	}

	/**
	 * Get cereat info message
	 *
	 * @param
	 * @return
	 */
	protected function getCreateInfoMessage()
	{
		global $DIC;

		$ctrl = $this->ctrl;
		$lng = $this->lng;
		$ui = $DIC->ui();
		$ilSetting =$DIC->settings();

		$message = "";
		// page type: blog
		if (!$ilSetting->get('disable_wsp_blogs'))
		{
			$options = array();
			include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
			$tree = new ilWorkspaceTree($this->user_id);
			$root = $tree->readRootId();
			if ($root)
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
			if (!sizeof($options))
			{

				// #18147
				$this->lng->loadLanguageModule('pd');
				$url = $this->ctrl->getLinkTargetByClass("ilpersonaldesktopgui", "jumpToWorkspace");
				$text = $this->lng->txt("pd_personal_workspace");

				$text = sprintf($this->lng->txt("prtf_no_blogs_info"), $text);

				$mbox = $ui->factory()->messageBox()->info($text)
					->withLinks([$ui->factory()->link()->standard($this->lng->txt("pd_personal_workspace"),
						$url)]);

				$message = $ui->renderer()->render($mbox);
			}

		}
		return $message;
	}


	protected function initCreationForms($a_new_type)
	{		
		return array(self::CFORM_NEW => $this->initCreateForm($a_new_type));		
	}
	
	protected function initCreateForm($a_new_type)
	{
		$ilSetting = $this->settings;
		
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
		include_once "Services/COPage/Layout/classes/class.ilPageLayout.php";
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
		$form->addCommandButton("save", $this->lng->txt("create"));
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
				$_REQUEST["prtt_pre"] = (int)$_REQUEST["prtt"];
				return $this->createFromTemplateDirect($form->getInput("title"));
				//return $this->createPortfolioFromTemplate();
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
				include_once("./Services/COPage/Layout/classes/class.ilPageLayout.php");
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
		$ilAccess = $this->access;
		
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
	
	protected function initCopyPageFormOptions(ilPropertyFormGUI $a_form)
	{

		$a_tgt = new ilRadioGroupInputGUI($this->lng->txt("target"), "target");
		$a_tgt->setRequired(true);
		$a_form->addItem($a_tgt);

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

		// add blog
		$radg = new ilRadioGroupInputGUI($this->lng->txt("obj_blog"), "creation_mode");
		$radg->setInfo($this->lng->txt(""));
		$radg->setValue("new");
		$radg->setInfo($this->lng->txt(""));

		$op1 = new ilRadioOption($this->lng->txt("prtf_add_new_blog"), "new" ,$this->lng->txt("prtf_add_new_blog_info"));
		$radg->addOption($op1);
		$form->addItem($radg);

		// Blog title
		$ti = new ilTextInputGUI($this->lng->txt("title"), "title");
		$ti->setRequired(true);
		$op1->addSubItem($ti);


		if(sizeof($options))
		{
			$op2 = new ilRadioOption($this->lng->txt("prtf_add_existing_blog"), "existing");
			$radg->addOption($op2);

			$obj = new ilSelectInputGUI($this->lng->txt("obj_blog"), "blog");
			$obj->setOptions($options);
			$op2->addSubItem($obj);
		}

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
		global $DIC;

		$ilUser = $DIC->user();

		$form = $this->initBlogForm();
		if ($form->checkInput() && $this->checkPermissionBool("write"))
		{
			if ($form->getInput("creation_mode") == "existing")
			{
				$page = $this->getPageInstance();
				$page->setType(ilPortfolioPage::TYPE_BLOG);
				$page->setTitle($form->getInput("blog"));
				$page->create();
			}
			else
			{
				$blog = new ilObjBlog();
				$blog->setTitle($form->getInput("title"));
				$blog->create();

				include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
				$tree = new ilWorkspaceTree($ilUser->getId());

				// @todo: see also e.g. ilExSubmissionObjectGUI->getOverviewContentBlog, this needs refactoring, consumer should not
				// be responsibel to handle this
				if(!$tree->getRootId())
				{
					$tree->createTreeForUser($ilUser->getId());
				}

				include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";
				$access_handler = new ilWorkspaceAccessHandler($tree);
				$node_id = $tree->insertObject($tree->readRootId(), $blog->getId());
				$access_handler->setPermissions($tree->readRootId(), $node_id);

				$page = $this->getPageInstance();
				$page->setType(ilPortfolioPage::TYPE_BLOG);
				$page->setTitle($blog->getId());
				$page->create();
			}

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
		$ilSetting = $this->settings;
		$ilUser = $this->user;
				
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
		foreach(ilPortfolioTemplatePage::getAllPortfolioPages($a_prtt_id) as $page)
		{
			switch($page["type"])
			{
				case ilPortfolioTemplatePage::TYPE_PAGE:	
					// skills
					$source_page = new ilPortfolioTemplatePage($page["id"]);
					$source_page->buildDom(true);
					$skill_ids = $this->getSkillsToPortfolioAssignment($pskills, $skill_ids, $source_page);

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
		$ilSetting = $this->settings;
		$ilUser = $this->user;
		$ilAccess = $this->access;
		
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
				foreach(ilPortfolioTemplatePage::getAllPortfolioPages($prtt_id) as $page)
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
		$this->linkPortfolioToAssignment($target_id);
		
		ilUtil::sendSuccess($this->lng->txt("prtf_portfolio_created_from_template"), true);
		$this->ctrl->setParameter($this, "prt_id", $target_id);
		$this->ctrl->redirect($this, "preview");
	}

	/**
	 * Create portfolio template direct
	 */
	protected function createFromTemplateDirect($title = "")
	{
		$prtt_id = (int)$_REQUEST["prtt_pre"];
		if ($title == "")
		{
			$title = ilObject::_lookupTitle($prtt_id);
		}

		// valid template?
		include_once "Modules/Portfolio/classes/class.ilObjPortfolioTemplate.php";
		$templates = array_keys(ilObjPortfolioTemplate::getAvailablePortfolioTemplates());
		if(!sizeof($templates) || !in_array($prtt_id, $templates))
		{
			$this->toRepository();
		}
		unset($templates);

		$source = new ilObjPortfolioTemplate($prtt_id, false);

		// create portfolio
		include_once "Modules/Portfolio/classes/class.ilObjPortfolio.php";
		$target = new ilObjPortfolio();
		$target->setTitle($title);
		$target->create();
		$target_id = $target->getId();

		$source->clonePagesAndSettings($source, $target, null, true);

		// link portfolio to exercise assignment
		//$this->linkPortfolioToAssignment($target_id);

		ilUtil::sendSuccess($this->lng->txt("prtf_portfolio_created_from_template"), true);
		$this->ctrl->setParameter($this, "prt_id", $target_id);
		$this->ctrl->redirect($this, "preview");
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

	public function createPortfolioFromAssignment()
	{
		$ilUser = $this->user;
		$ilSetting = $this->settings;

		include_once "Modules/Portfolio/classes/class.ilObjPortfolioTemplate.php";
		include_once "Modules/Portfolio/classes/class.ilPortfolioTemplatePage.php";
		include_once "Modules/Portfolio/classes/class.ilObjPortfolio.php";

		$title = trim($_REQUEST["pt"]);
		$prtt_id = (int)$_REQUEST["prtt"];

		// get assignment template
		$ass_template_id = 0;
		if ((int)$_REQUEST["ass_id"] > 0)
		{
			include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
			$ass = new ilExAssignment((int)$_REQUEST["ass_id"]);
			$ass_template_id = ilObject::_lookupObjectId($ass->getPortfolioTemplateId());
		}

		if($prtt_id > 0)
		{
			$templates = array_keys(ilObjPortfolioTemplate::getAvailablePortfolioTemplates());
			if(!sizeof($templates) || !in_array($prtt_id, $templates))
			{
				if ($ass_template_id != $prtt_id)
				{
					$this->toRepository();
				}
			}

			//quota manipulation
			include_once "Services/WebDAV/classes/class.ilDiskQuotaActivationChecker.php";
			$check_quota = (int)ilDiskQuotaActivationChecker::_isPersonalWorkspaceActive();
			$quota_sum = 0;

			//skills manipulation
			include_once "Services/Skill/classes/class.ilPersonalSkill.php";
			$pskills = array_keys(ilPersonalSkill::getSelectedUserSkills($ilUser->getId()));
			$skill_ids = array();

			$recipe = array();
			foreach(ilPortfolioTemplatePage::getAllPortfolioPages($prtt_id) as $page)
			{
				switch($page["type"])
				{
					case ilPortfolioTemplatePage::TYPE_BLOG_TEMPLATE:
						if(!$ilSetting->get('disable_wsp_blogs'))
						{
							$recipe[$page["id"]] = array("blog", "create", $page['title']);
						}
						break;
					case ilPortfolioTemplatePage::TYPE_PAGE:
						$source_page = new ilPortfolioTemplatePage($page["id"]);
						$source_page->buildDom(true);
						if($check_quota)
						{
							$quota_sum += $source_page->getPageDiskSize();
						}
						$skill_ids = $this->getSkillsToPortfolioAssignment($pskills, $skill_ids, $source_page);
						break;
				}
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

			if($skill_ids)
			{
				$recipe["skills"] = $skill_ids;
			}

		}

		// create portfolio
		$target = new ilObjPortfolio();
		$target->setTitle($title);
		$target->create();
		$target_id = $target->getId();

		if($prtt_id)
		{
			$source = new ilObjPortfolioTemplate($prtt_id, false);
			$source->clonePagesAndSettings($source, $target,$recipe);
		}

		// link portfolio to exercise assignment
		$this->linkPortfolioToAssignment($target_id);

		$this->ctrl->setParameter($this, "prt_id", $target_id);
		if($prtt_id)
		{
			ilUtil::sendSuccess($this->lng->txt("prtf_portfolio_created_from_template"), true);
			$this->ctrl->redirect($this, "preview");
		}
		else
		{
			ilUtil::sendSuccess($this->lng->txt("prtf_portfolio_created"), true);
			$this->ctrl->redirect($this, "view");
		}
	}

	function linkPortfolioToAssignment($a_target_id)
	{
		$ilAccess = $this->access;
		$ilUser = $this->user;

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
				// #16205
				include_once "Modules/Exercise/classes/class.ilExSubmission.php";
				$sub = new ilExSubmission($ass, $ilUser->getId());
				$sub->addResourceObject($a_target_id);
			}
		}
	}

	/**
	 * @param array a_pskills
	 * @param array a_skill_ids
	 * @param ilPortfolioTemplatePage $a_source_page
	 * @return array
	 */
	function getSkillsToPortfolioAssignment($a_pskills, $a_skill_ids, $a_source_page)
	{
		$dom = $a_source_page->getDom();
		if($dom instanceof php4DOMDocument)
		{
			$dom = $dom->myDOMDocument;
		}
		$xpath = new DOMXPath($dom);
		$nodes = $xpath->query("//PageContent/Skills");
		foreach($nodes as $node)
		{
			$skill_id = $node->getAttribute("Id");
			if(!in_array($skill_id, $a_pskills))
			{
				$a_skill_ids[] = $skill_id;
			}
		}
		unset($nodes);
		unset($xpath);
		unset($dom);

		return $a_skill_ids;

	}
	
	/**
	 * Export PDF selection
	 *
	 * @param
	 */
	function exportPDFSelection()
	{
		global $DIC;

		$tpl = $DIC["tpl"];

		$form = $this->initPDFSelectionForm();

		$tpl->setContent($form->getHTML());

	}

	/**
	 * Init print view selection form.
	 */
	public function initPDFSelectionForm()
	{
		global $DIC;

		$lng = $this->lng;
		$ilCtrl = $this->ctrl;

		$tabs = $DIC->tabs();

		$tabs->clearTargets();
		$tabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "view"));

		$pages = ilPortfolioPage::getAllPortfolioPages($this->object->getId());


		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();

		// because of PDF export
		$form->setPreventDoubleSubmission(false);

		// declaration of authorship
		if ($this->declaration_authorship->getForUser($this->user) != "")
		{
			$cb = new ilCheckboxInputGUI($this->lng->txt("prtf_decl_authorship"), "decl_author");
			$cb->setInfo($this->declaration_authorship->getForUser($this->user));
			$form->addItem($cb);
		}

		// signature
		$cb = new ilCheckboxInputGUI($this->lng->txt("prtf_signature"), "signature");
		$cb->setInfo($this->lng->txt("prtf_signature_info"));
		$form->addItem($cb);


		// selection type
		$radg = new ilRadioGroupInputGUI($lng->txt("prtf_print_selection"), "sel_type");
		$radg->setValue("all_pages");
		$op2 = new ilRadioOption($lng->txt("prtf_all_pages"), "all_pages");
		$radg->addOption($op2);
		$op3= new ilRadioOption($lng->txt("prtf_selected_pages"), "selection");
		$radg->addOption($op3);

		include_once("./Services/Form/classes/class.ilNestedListInputGUI.php");
		$nl = new ilNestedListInputGUI("", "obj_id");
		$op3->addSubItem($nl);

		foreach ($pages as $p)
		{
			if ($p["type"] != ilPortfolioPage::TYPE_BLOG)
			{
				$nl->addListNode($p["id"], $p["title"], 0, false, false,
					ilUtil::getImagePath("icon_pg.svg"), $lng->txt("page"));
			}
			else
			{
				$nl->addListNode($p["id"], $lng->txt("obj_blog").": ".ilObject::_lookupTitle($p["title"]), 0, false, false,
					ilUtil::getImagePath("icon_blog.svg"), $lng->txt("obj_blog"));
				$pages2 = ilBlogPosting::getAllPostings($p["title"]);
				foreach ($pages2 as $p2)
				{
					$nl->addListNode("b".$p2["id"], $p2["title"], $p["id"], false, false,
						ilUtil::getImagePath("icon_pg.svg"), $lng->txt("page"));
				}
			}
		}

		$form->addItem($radg);

		$form->addCommandButton("exportPDF", $lng->txt("prtf_pdf"));
		if (DEVMODE == "1")
		{
			$form->addCommandButton("exportPDFDev", $lng->txt("prtf_pdf")." (DEV)");
		}

		$form->setTitle($lng->txt("prtf_print_options"));
		$form->setFormAction($ilCtrl->getFormAction($this, "exportPDF"));

		return $form;
	}

	/**
	 * @throws ilWACException
	 */
	public function exportPDFDev()
	{
		$this->exportPDF(true);
	}

	/**
	 * @param bool $a_dev_mode
	 * @throws ilWACException
	 */
	public function exportPDF($a_dev_mode = false)
	{
		require_once 'Services/WebAccessChecker/classes/class.ilWACSignedPath.php';
		ilWACSignedPath::setTokenMaxLifetimeInSeconds(180);

		// prepare generation before contents are processed (for mathjax)
		ilPDFGeneratorUtils::prepareGenerationRequest("Portfolio", "ContentExport");

		$html = $this->printView(true);

		// :TODO: fixing css dummy parameters
		$html = preg_replace("/\?dummy\=[0-9]+/", "", $html);
		$html = preg_replace("/\?vers\=[0-9A-Za-z\-]+/", "", $html);

		$html = preg_replace("/src=\"\\.\\//ims", "src=\"" . ILIAS_HTTP_PATH . "/", $html);
		$html = preg_replace("/href=\"\\.\\//ims", "href=\"" . ILIAS_HTTP_PATH . "/", $html);


		if ($a_dev_mode)
		{
			echo $html;
			exit;
		}

		//$html = str_replace("&amp;", "&", $html);

		$pdf_factory = new ilHtmlToPdfTransformerFactory();
		$pdf_factory->deliverPDFFromHTMLString($html, "portfolio.pdf", ilHtmlToPdfTransformerFactory::PDF_OUTPUT_DOWNLOAD, "Portfolio", "ContentExport");
	}

	public function printView($a_pdf_export = false)
	{
		global $tpl;

		$lng = $this->lng;

		$pages = ilPortfolioPage::getAllPortfolioPages($this->object->getId());


		$tpl = new ilTemplate("tpl.main.html", true, true);

		$tpl->setBodyClass("ilPrtfPdfBody");

		$tpl->setCurrentBlock("AdditionalStyle");
		$tpl->setVariable("LOCATION_ADDITIONAL_STYLESHEET", ilUtil::getStyleSheetLocation("filesystem"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("ContentStyle");
		$tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath($this->object->getStyleSheetId(), false));
		$tpl->parseCurrentBlock();

		$tpl->setVariable("LOCATION_STYLESHEET", ilObjStyleSheet::getContentPrintStyle());
		//$this->setContentStyleSheet($tpl);

		// syntax style
		$tpl->setCurrentBlock("SyntaxStyle");
		$tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
			ilObjStyleSheet::getSyntaxStylePath());
		$tpl->parseCurrentBlock();


		include_once("./Modules/Portfolio/classes/class.ilPortfolioPageGUI.php");

		$page_content = "";

		// cover page
		$cover_tpl = new ilTemplate("tpl.prtf_cover.html", true, true, "Modules/Portfolio");
		foreach ($pages as $page)
		{
			if ($page["type"] != ilPortfolioPage::TYPE_BLOG)
			{
				if ($_POST["sel_type"] == "selection" && (!is_array($_POST["obj_id"]) || !in_array($page["id"], $_POST["obj_id"])))
				{
					continue;
				}
				$cover_tpl->setCurrentBlock("content_item");
				$cover_tpl->setVariable("ITEM_TITLE", $page["title"]);
				$cover_tpl->parseCurrentBlock();
			}
			else
			{
				$cover_tpl->setCurrentBlock("content_item");
				$cover_tpl->setVariable("ITEM_TITLE", $lng->txt("obj_blog").": ".ilObject::_lookupTitle($page["title"]));
				$cover_tpl->parseCurrentBlock();
			}
		}

		if ($_POST["signature"])
		{
			$cover_tpl->setCurrentBlock("signature");
			$cover_tpl->setVariable("TXT_SIGNATURE", $lng->txt("prtf_signature_date"));
			$cover_tpl->parseCurrentBlock();
		}

		if ($_POST["decl_author"])
		{
			$cover_tpl->setCurrentBlock("decl_author");
			$cover_tpl->setVariable("TXT_DECL_AUTHOR",
				nl2br($this->declaration_authorship->getForUser($this->user)));
			$cover_tpl->parseCurrentBlock();
		}

		$cover_tpl->setVariable("PORTFOLIO_TITLE", $this->object->getTitle());
		$cover_tpl->setVariable("PORTFOLIO_ICON", ilUtil::getImagePath("icon_prtf.svg"));

		$cover_tpl->setVariable("TXT_AUTHOR", $lng->txt("prtf_author"));
		$cover_tpl->setVariable("TXT_LINK", $lng->txt("prtf_link"));
		$cover_tpl->setVariable("TXT_DATE", $lng->txt("prtf_date_of_print"));

		$author = ilObjUser::_lookupName($this->object->getOwner());
		$author_str = $author["firstname"]." ".$author["lastname"];
		$cover_tpl->setVariable("AUTHOR", $author_str);

		include_once('./Services/Link/classes/class.ilLink.php');
		$href = ilLink::_getStaticLink($this->object->getId(), "prtf");
		$cover_tpl->setVariable("LINK", $href);

		ilDatePresentation::setUseRelativeDates(false);
		$date_str = ilDatePresentation::formatDate(new ilDate(date("Y-m-d"), IL_CAL_DATE));
		$cover_tpl->setVariable("DATE", $date_str);

		$page_content .= $cover_tpl->get();
		$page_content .= '<p style="page-break-after:always;"></p>';

		$page_head_tpl = new ilTemplate("tpl.prtf_page_head.html", true, true, "Modules/Portfolio");
		$page_head_tpl->setVariable("AUTHOR", $author_str);
		$page_head_tpl->setVariable("DATE", $date_str);
		$page_head_str = $page_head_tpl->get();

		foreach ($pages as $page)
		{
			if ($page["type"] != ilPortfolioPage::TYPE_BLOG)
			{
				if ($_POST["sel_type"] == "selection" && (!is_array($_POST["obj_id"]) || !in_array($page["id"], $_POST["obj_id"])))
				{
					continue;
				}

				$page_gui = new ilPortfolioPageGUI($this->object->getId(), $page["id"]);
				$page_gui->setOutputMode("print");
				$page_gui->setPresentationTitle($page["title"]);
				$html = $this->ctrl->getHTML($page_gui);
				$page_content .= $page_head_str.$html;

				if ($a_pdf_export)
				{
					$page_content .= '<p style="page-break-after:always;"></p>';
				}
			}
			else
			{
				$pages2 = ilBlogPosting::getAllPostings($page["title"]);
				foreach ($pages2 as $p2)
				{
					if ($_POST["sel_type"] == "selection" && (!is_array($_POST["obj_id"]) || !in_array("b".$p2["id"], $_POST["obj_id"])))
					{
						continue;
					}
					$page_gui = new ilBlogPostingGUI(0, null, $p2["id"]);
					$page_gui->setFileDownloadLink("#");
					$page_gui->setFullscreenLink("#");
					$page_gui->setSourcecodeDownloadScript("#");
					$page_gui->setOutputMode("print");
					$page_content .= $page_head_str.$page_gui->showPage(ilObject::_lookupTitle($page["title"]).": ".$page_gui->getBlogPosting()->getTitle());

					if ($a_pdf_export)
					{
						$page_content .= '<p style="page-break-after:always;"></p>';
					}
				}
			}
		}

		$page_content = '<div class="ilInvisibleBorder">'.$page_content.'</div>';

		if(!$a_pdf_export)
		{
			$page_content .= '<script type="text/javascript" language="javascript1.2">
				<!--
					il.Util.addOnLoad(function () {
						il.Util.print();
					});
				//-->
				</script>';
		}

		$tpl->setVariable("CONTENT", $page_content);

		if(!$a_pdf_export)
		{
			$tpl->show(false);
			exit;
		}
		else
		{
			$tpl->fillJavaScriptFiles();
			$ret = $tpl->get("DEFAULT", false, false, false, true, false, false);
			return $ret;
		}
	}

	/**
	 * Get offline message for sharing tab
	 *
	 * @return string
	 */
	protected function getOfflineMessage()
	{
		$ui = $this->ui;
		$lng = $this->lng;
		$ctrl = $this->ctrl;

		if (!$this->object->isOnline())
		{
			$f = $ui->factory();
			$renderer = $ui->renderer();

			$buttons = [$f->button()->standard($lng->txt("prtf_set_online"),
				$ctrl->getLinkTarget($this, "setOnlineAndShare"))];

			return $renderer->render($f->messageBox()->info($lng->txt("prtf_no_offline_share_info"))
				->withButtons($buttons));
		}
		return "";
	}

	/**
	 * Set online and switch to share screen
	 */
	protected function setOnlineAndShare()
	{
		$ilCtrl = $this->ctrl;
		$lng = $this->lng;

		if (ilObjPortfolio::_lookupOwner($this->object->getId()) == $this->user_id)
		{
			$this->object->setOnline(true);
			$this->object->update();
			ilUtil::sendSuccess($lng->txt("prtf_has_been_set_online"), true);
		}
		$ilCtrl->redirectByClass("ilworkspaceaccessgui", "");
	}

}
?>