<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Modules/Portfolio/classes/class.ilObjPortfolioBaseGUI.php');

/**
 * Portfolio template view gui class 
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_Calls ilObjPortfolioTemplateGUI: ilPortfolioTemplatePageGUI, ilPageObjectGUI, ilNoteGUI
 * @ilCtrl_Calls ilObjPortfolioTemplateGUI: ilObjectCopyGUI, ilInfoScreenGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjPortfolioTemplateGUI: ilPermissionGUI
 *
 * @ingroup ModulesPortfolio
 */
class ilObjPortfolioTemplateGUI extends ilObjPortfolioBaseGUI
{					
	public function getType()
	{
		return "prtt";
	}	
		
	public function executeCommand()
	{
		global $ilNavigationHistory;
				
		$this->tpl->getStandardTemplate();

		// add entry to navigation history
		if(!$this->getCreationMode() &&
			$this->getAccessHandler()->checkAccess("read", "", $this->node_id))
		{
			$link = $this->ctrl->getLinkTarget($this, "view");				
			$ilNavigationHistory->addItem($this->node_id, $link, "prtt");
		}
		
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd("view");		

		switch($next_class)
		{			
			case 'ilportfoliotemplatepagegui':		
				$this->determinePageCall(); // has to be done before locator!
				$this->prepareOutput();				
				$this->handlePageCall($cmd);
				break;
				
			case "ilnotegui";				
				$this->preview();				
				break;
			
			case "ilinfoscreengui":
				$this->prepareOutput();
				$this->infoScreenForward();	
				break;
			
			case "ilcommonactiondispatchergui":
				include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
				$gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
				$this->ctrl->forwardCommand($gui);
				break;
			
			case "ilpermissiongui":
				$this->prepareOutput();
				$this->tabs_gui->activateTab("id_permissions");
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui = new ilPermissionGUI($this);
				$this->ctrl->forwardCommand($perm_gui);
				break;
			
			case "ilobjectcopygui":
				include_once "./Services/Object/classes/class.ilObjectCopyGUI.php";
				$cp = new ilObjectCopyGUI($this);
				$cp->setType("prtt");
				$this->ctrl->forwardCommand($cmd);
				break;
			
			default:			
				$this->addHeaderAction($cmd);				
				return ilObject2GUI::executeCommand();		
		}
	}
		
	protected function setTabs()
	{
		global $ilHelp;	
		
		$ilHelp->setScreenIdComponent("prtt");
		
		if($this->checkPermissionBool("write"))
		{
			$this->tabs_gui->addTab("pages",
				$this->lng->txt("content"),
				$this->ctrl->getLinkTarget($this, "view"));		
		}
			
		if ($this->checkPermissionBool("read"))
		{										
			$this->tabs_gui->addTab("id_info",
				$this->lng->txt("info_short"),
				$this->ctrl->getLinkTargetByClass(array("ilobjportfoliotemplategui", "ilinfoscreengui"), "showSummary"));
		}
				
		if($this->checkPermissionBool("write"))
		{							
			$this->tabs_gui->addTab("settings",
				$this->lng->txt("settings"),
				$this->ctrl->getLinkTarget($this, "edit"));
		}				
			
		if ($this->checkPermissionBool("read"))
		{
			$this->tabs_gui->addNonTabbedLink("preview", 
				$this->lng->txt("user_profile_preview"),
				$this->ctrl->getLinkTarget($this, "preview"));	
		}
		
		// will add permissions if needed
		ilObject2GUI::setTabs();			
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
		
		$this->ctrl->forwardCommand($info);
	}
	
	
	//
	// CREATE/EDIT
	// 
	
	protected function initCreationForms($a_new_type)
	{		
		$forms = parent::initCreationForms($a_new_type);
		
		// :TODO: no import for now?
		unset($forms[self::CFORM_IMPORT]);
		
		return $forms;
	}
	
	protected function initDidacticTemplate(ilPropertyFormGUI $a_form)
	{
		global $ilUser;
		
		include_once "Modules/Portfolio/classes/class.ilObjPortfolio.php";
		$all = ilObjPortfolio::getPortfoliosOfUser($ilUser->getId());
		if(sizeof($all))
		{
			$opts = array(""=>$this->lng->txt("please_select"));
			foreach($all as $item)
			{
				$opts[$item["id"]] = $item["title"];
			}			
			$prtf = new ilSelectInputGUI($this->lng->txt("prtf_create_template_from_portfolio"), "prtf");
			$prtf->setInfo($this->lng->txt("prtf_create_template_from_portfolio_info"));
			$prtf->setOptions($opts);
			$a_form->addItem($prtf);	
		}
		
		// yeah, I know.
		return $a_form;		
	}
	
	protected function afterSave(ilObject $a_new_object)
	{				
		if($_POST["prtf"])
		{				
			include_once "Modules/Portfolio/classes/class.ilObjPortfolio.php";
			$source = new ilObjPortfolio($_POST["prtf"], false);

			ilObjPortfolio::clonePagesAndSettings($source, $a_new_object);
		}

		ilUtil::sendSuccess($this->lng->txt("prtf_portfolio_created"), true);
		$this->ctrl->setParameter($this, "prt_id", $a_new_object->getId());
		$this->ctrl->redirect($this, "view");
	}
		
	protected function initEditCustomForm(ilPropertyFormGUI $a_form)
	{			
		// activation/availability
		
		include_once "Services/Object/classes/class.ilObjectActivation.php";
		$this->lng->loadLanguageModule('rep');
		
		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('rep_activation_availability'));
		$a_form->addItem($section);
		
		// additional info only with multiple references
		$act_obj_info = $act_ref_info = "";
		if(sizeof(ilObject::_getAllReferences($this->object->getId())) > 1)
		{
			$act_obj_info = ' '.$this->lng->txt('rep_activation_online_object_info');
			$act_ref_info = $this->lng->txt('rep_activation_access_ref_info');
		}
		
		$online = new ilCheckboxInputGUI($this->lng->txt('rep_activation_online'),'online');		
		$online->setInfo($this->lng->txt('prtt_activation_online_info').$act_obj_info);		
		$a_form->addItem($online);				
		
		$act_type = new ilRadioGroupInputGUI($this->lng->txt('rep_activation_access'),'access_type');
		$act_type->setInfo($act_ref_info);	
		
			$opt = new ilRadioOption($this->lng->txt('rep_visibility_limitless'), ilObjectActivation::TIMINGS_DEACTIVATED);
			$opt->setInfo($this->lng->txt('prtt_availability_limitless_info'));
			$act_type->addOption($opt);
			
			$opt = new ilRadioOption($this->lng->txt('rep_visibility_until'), ilObjectActivation::TIMINGS_ACTIVATION);
			$opt->setInfo($this->lng->txt('prtt_availability_until_info'));
								
				$start = new ilDateTimeInputGUI($this->lng->txt('rep_activation_limited_start'),'access_begin');
				$start->setShowTime(true);		
				$opt->addSubItem($start);
												
				$end = new ilDateTimeInputGUI($this->lng->txt('rep_activation_limited_end'),'access_end');			
				$end->setShowTime(true);			
				$opt->addSubItem($end);
				
				$visible = new ilCheckboxInputGUI($this->lng->txt('rep_activation_limited_visibility'), 'access_visiblity');
				$visible->setInfo($this->lng->txt('prtt_activation_limited_visibility_info'));
				$opt->addSubItem($visible);
				
			$act_type->addOption($opt);
		
		$a_form->addItem($act_type);						
		
		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('properties'));
		$a_form->addItem($section);
	
		parent::initEditCustomForm($a_form);
	}
	
	protected function getEditFormCustomValues(array &$a_values)
	{				
		$a_values["online"] = $this->object->isOnline();
		
		$a_values["access_type"] = $this->object->isActivationLimited() ? 
			ilObjectActivation::TIMINGS_ACTIVATION : 
			ilObjectActivation::TIMINGS_DEACTIVATED;
		
		$a_values["access_visiblity"] = $this->object->getActivationVisibility();
		
		$date = $this->object->getActivationStartDate();
		$date = new ilDateTime($date ? $date : time(), IL_CAL_UNIX);
		$date = $date->get(IL_CAL_DATETIME);	
		$a_values["access_begin"] = array(
			"date" => substr($date, 0, 10),
			"time" => substr($date, 11)
		);
		
		$date = $this->object->getActivationEndDate();
		$date = new ilDateTime($date ? $date : time(), IL_CAL_UNIX);
		$date = $date->get(IL_CAL_DATETIME);
		$a_values["access_end"] = array(
			"date" => substr($date, 0, 10),
			"time" => substr($date, 11)
		);
			
		parent::getEditFormCustomValues($a_values);
	}
	
	public function updateCustom(ilPropertyFormGUI $a_form)
	{				
		$this->object->setOnline($a_form->getInput("online"));
		
		// activation
		if($a_form->getInput("access_type") == ilObjectActivation::TIMINGS_ACTIVATION)
		{	
			$this->object->setActivationLimited(true);								    			
			$this->object->setActivationVisibility($a_form->getInput("access_visiblity"));
			
			$date = $a_form->getInput("access_begin");			
			$date = new ilDateTime($date["date"]." ".$date["time"], IL_CAL_DATETIME);
			$this->object->setActivationStartDate($date->get(IL_CAL_UNIX));

			$date = $a_form->getInput("access_end");			
			$date = new ilDateTime($date["date"]." ".$date["time"], IL_CAL_DATETIME);
			$this->object->setActivationEndDate($date->get(IL_CAL_UNIX));							
		}
		else
		{
			$this->object->setActivationLimited(false);
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
	 * @return ilPortfolioTemplatePage
	 */
	protected function getPageInstance($a_page_id = null)
	{		
		include_once "Modules/Portfolio/classes/class.ilPortfolioTemplatePage.php";			
		$page = new ilPortfolioTemplatePage($a_page_id);
		$page->setPortfolioId($this->object->getId());
		return $page;
	}
	
	/**
	 * Get portfolio template page gui instance
	 * 
	 * @param int $a_page_id
	 * @return ilPortfolioTemplatePageGUI
	 */
	protected function getPageGUIInstance($a_page_id)
	{
		include_once("Modules/Portfolio/classes/class.ilPortfolioTemplatePageGUI.php");
		$page_gui = new ilPortfolioTemplatePageGUI(
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
		return "ilportfoliotemplatepagegui";
	}
	
	
	//
	// BLOG
	// 
	
	/**
	 * Init blog template page form
	 *
	 * @param string $a_mode
	 * @return ilPropertyFormGUI
	 */
	public function initBlogForm()
	{		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));

		$obj = new ilTextInputGUI($this->lng->txt("title"), "blog");
		$obj->setRequired(true);	
		$form->addItem($obj);

		// save and cancel commands		
		$form->setTitle($this->lng->txt("prtf_add_blog").": ".
			$this->object->getTitle());
		$form->addCommandButton("saveBlog", $this->lng->txt("save"));
		$form->addCommandButton("view", $this->lng->txt("cancel"));			
		
		return $form;
	}
	
	/**
	 * Create new portfolio blog template page
	 */
	public function saveBlog()
	{		
		$form = $this->initBlogForm();
		if ($form->checkInput() && $this->checkPermissionBool("write"))
		{
			$page = $this->getPageInstance();			
			$page->setType(ilPortfolioTemplatePage::TYPE_BLOG_TEMPLATE);		
			$page->setTitle($form->getInput("blog"));									
			$page->create();

			ilUtil::sendSuccess($this->lng->txt("prtf_page_created"), true);
			$this->ctrl->redirect($this, "view");
		}

		$this->tabs_gui->clearTargets();
		$this->tabs_gui->setBackTarget($this->lng->txt("back"),
			$this->ctrl->getLinkTarget($this, "view"));

		$form->setValuesByPost();
		$this->tpl->setContent($form->getHtml());
	}
	
	
	public function _goto($a_target)
	{
		$id = explode("_", $a_target);
		
		$_GET["baseClass"] = "ilRepositoryGUI";	
		$_GET["ref_id"] = $id[0];		
		$_GET["cmd"] = "view";
	
		include("ilias.php");
		exit;
	}
}

?>