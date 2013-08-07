<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Services/Object/classes/class.ilObject2GUI.php');
include_once('./Modules/Portfolio/classes/class.ilObjPortfolio.php');
include_once('./Modules/Portfolio/classes/class.ilPortfolioPage.php');

/**
 * Portfolio view gui class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_Calls ilObjPortfolioGUI: ilPortfolioPageGUI, ilPageObjectGUI
 * @ilCtrl_Calls ilObjPortfolioGUI: ilWorkspaceAccessGUI, ilNoteGUI
 *
 * @ingroup ModulesPortfolio
 */
class ilObjPortfolioGUI extends ilObject2GUI
{
	protected $user_id; // [int]
	protected $additional = array();
	protected $perma_link; // [string]
	
	public function __construct($a_id = 0, $a_id_type = self::PORTFOLIO_OBJECT_ID, $a_parent_node_id = 0)
	{
		global $lng, $ilUser;
		
		parent::__construct($a_id, $a_id_type, $a_parent_node_id);

		$lng->loadLanguageModule("prtf");
		$this->user_id = $ilUser->getId();		
	}
	
	public function getType()
	{
		return "prtf";
	}
	
	protected function checkPermissionBool($a_perm, $a_cmd = "", $a_type = "", $a_node_id = null)
	{
		if($this->id_type == self::PORTFOLIO_OBJECT_ID)
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
		return parent::checkPermissionBool($a_perm, $a_cmd, $a_type, $a_node_id);
	}
	
	protected function addLocator($a_page_id = null)
	{
		global $ilLocator, $ilCtrl, $tpl;
		
		if($this->object)
		{
			$ilCtrl->setParameter($this, "prt_id", $this->object->getId());	
			$ilLocator->addItem($this->object->getTitle(),
				$ilCtrl->getLinkTarget($this, "view"));
		}
		
		if($a_page_id)
		{
			include_once "Modules/Portfolio/classes/class.ilPortfolioPage.php";			
			$page = new ilPortfolioPage($a_page_id);
			$page->setPortfolioId($this->object->getId());
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

	function executeCommand()
	{
		global $ilCtrl, $ilTabs, $lng, $tpl;

		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd("view");
		
		$lng->loadLanguageModule("user");
		
		$title = $lng->txt("portfolio");
		if($this->object)
		{
			$title .= ": ".$this->object->getTitle();	
		}
		$tpl->setTitle($title);
		$tpl->setTitleIcon(ilUtil::getImagePath("icon_prtf_b.png"), 
			$lng->txt("portfolio"));

		switch($next_class)
		{
			case "ilworkspaceaccessgui";	
				if($this->checkPermissionBool("write"))
				{
					$this->setTabs();
					$ilTabs->activateTab("share");
					
					include_once('Services/PermanentLink/classes/class.ilPermanentLinkGUI.php');
					$plink = new ilPermanentLinkGUI("prtf", $this->object->getId());
					$plink = $plink->getHTML();

					include_once('./Services/PersonalWorkspace/classes/class.ilWorkspaceAccessGUI.php');
					$wspacc = new ilWorkspaceAccessGUI($this->object->getId(), $this->access_handler, true, $plink);
					$ilCtrl->forwardCommand($wspacc);
				}
				break;
			
			case 'ilportfoliopagegui':										
				include_once "Services/Form/classes/class.ilFileInputGUI.php";
				ilFileInputGUI::setPersonalWorkspaceQuotaCheck(true);						

				$ilTabs->clearTargets();
				$ilTabs->setBackTarget($lng->txt("back"),
					$ilCtrl->getLinkTarget($this, "view"));
				
				// edit
				if(isset($_REQUEST["ppage"]) && $this->checkPermissionBool("write"))
				{
					$this->addLocator($_REQUEST["ppage"]);
					
					$page_id = $_REQUEST["ppage"];
					$ilCtrl->setParameter($this, "ppage", $_REQUEST["ppage"]);
				}
				// preview
				else
				{
					$page_id = $_REQUEST["user_page"];
					$ilCtrl->setParameter($this, "user_page", $_REQUEST["user_page"]);
				}
				
				include_once("Modules/Portfolio/classes/class.ilPortfolioPageGUI.php");
				$page_gui = new ilPortfolioPageGUI($this->object->getId(),
					$page_id, 0, $this->object->hasPublicComments());
				$page_gui->setAdditional($this->getAdditional());
				
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
				if($cmd != "preview" && $this->id_type == self::PORTFOLIO_OBJECT_ID)
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
		global $lng, $ilTabs, $ilCtrl, $ilHelp;		
		
		if($this->checkPermissionBool("write"))
		{
			$ilHelp->setScreenIdComponent("prtf");
			
			$ilTabs->addTab("pages",
				$lng->txt("content"),
				$ilCtrl->getLinkTarget($this, "view"));
		
			$ilTabs->addTab("settings",
				$lng->txt("settings"),
				$ilCtrl->getLinkTarget($this, "edit"));

			if($this->id_type == self::PORTFOLIO_OBJECT_ID)
			{
				$lng->loadLanguageModule("wsp");
				$ilTabs->addTab("share",
					$lng->txt("wsp_permissions"),
					$ilCtrl->getLinkTargetByClass("ilworkspaceaccessgui", "share"));
			}
		}
		
		$ilTabs->addNonTabbedLink("preview", 
			$lng->txt("user_profile_preview"),
			$ilCtrl->getLinkTarget($this, "preview"));	
	}

	
	//
	// CREATE/EDIT
	// 
	
	protected function initCreationForms($a_new_type)
	{
		if($this->id_type == self::PORTFOLIO_OBJECT_ID)
		{
			return array(self::CFORM_NEW => $this->initCreateForm($a_new_type));
		}
		
		return parent::initCreationForms($a_new_type);
	}
	
	protected function initCreateForm($a_new_type)
	{
		global $ilCtrl, $lng, $ilSetting;
		
		if($this->id_type != self::PORTFOLIO_OBJECT_ID)
		{
			return parent::initCreateForm($a_new_type);
		}
		
		$ilCtrl->setParameter($this, "new_type", $this->getType());
		
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
		$form->addCommandButton("toRepository", $lng->txt("cancel"));
		
		return $form;
	}
	
	protected function afterSave(ilObject $a_new_object)
	{
		global $ilCtrl, $lng;
		
		if($this->id_type != self::PORTFOLIO_OBJECT_ID)
		{
			return parent::afterSave($a_new_object);
		}
		
		// create 1st page / blog
		include_once("Modules/Portfolio/classes/class.ilPortfolioPage.php");
		$page = new ilPortfolioPage();
		$page->setPortfolioId($a_new_object->getId());
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

		ilUtil::sendSuccess($lng->txt("prtf_portfolio_created"), true);
		$ilCtrl->setParameter($this, "prt_id", $a_new_object->getId());
		$ilCtrl->redirect($this, "view");
	}
	
	protected function toRepository()
	{
		global $ilCtrl;
		
		$ilCtrl->redirectByClass("ilportfoliorepositorygui", "show");
	}	
	
	protected function initEditForm()
	{
		global $lng, $ilCtrl;
		
		if($this->id_type != self::PORTFOLIO_OBJECT_ID)
		{
			return parent::initEditForm();
		}
					
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));	
		
		// title
		$ti = new ilTextInputGUI($lng->txt("title"), "title");
		$ti->setMaxLength(128);
		$ti->setSize(40);
		$ti->setRequired(true);
		$ti->setValue($this->object->getTitle());
		$form->addItem($ti);

		/* description
		$ta = new ilTextAreaInputGUI($lng->txt("description"), "desc");
		$ta->setCols(40);
		$ta->setRows(2);
		$ta->setValue($this->object->getDescription());
		$form->addItem($ta);		
		*/
		
		// online
		$online = new ilCheckboxInputGUI($lng->txt("online"), "online");
		$online->setChecked($this->object->isOnline());
		$form->addItem($online);
		
		$this->initEditCustomForm($form);

		$form->setTitle($lng->txt("prtf_edit_portfolio"));
		$form->addCommandButton("update", $lng->txt("save"));
		$form->addCommandButton("view", $lng->txt("cancel"));		

		return $form;		
	}
	
	protected function initEditCustomForm(ilPropertyFormGUI $a_form)
	{					
		global $lng;
		
		// comments
		$comments = new ilCheckboxInputGUI($lng->txt("prtf_public_comments"), "comments");
		$a_form->addItem($comments);

		// profile picture
		$ppic = new ilCheckboxInputGUI($lng->txt("prtf_profile_picture"), "ppic");
		$a_form->addItem($ppic);

		$prfa_set = new ilSetting("prfa");
		if($prfa_set->get("banner"))
		{			
			include_once "Services/Form/classes/class.ilFileInputGUI.php";
			ilFileInputGUI::setPersonalWorkspaceQuotaCheck(true);	

			$dimensions = " (".$prfa_set->get("banner_width")."x".
				$prfa_set->get("banner_height").")";

			$img = new ilImageFileInputGUI($lng->txt("prtf_banner").$dimensions, "banner");
			$a_form->addItem($img);

			// show existing file
			$file = $this->object->getImageFullPath(true);
			if($file)
			{
				$img->setImage($file);
			}		
		}

		$bg_color = new ilColorPickerInputGUI($lng->txt("prtf_background_color"), "bg_color");
		$a_form->addItem($bg_color);

		$font_color = new ilColorPickerInputGUI($lng->txt("prtf_font_color"), "font_color");
		$a_form->addItem($font_color);								
	}
	
	protected function getEditFormCustomValues(array &$a_values)
	{
		$a_values["online"] = $this->object->isOnline();
		$a_values["comments"] = $this->object->hasPublicComments();
		$a_values["ppic"] = $this->object->hasProfilePicture();
		$a_values["bg_color"] = $this->object->getBackgroundColor();
		$a_values["font_color"] = $this->object->getFontColor();
	}
	
	/**
	 * Update portfolio properties
	 */
	public function updateCustom(ilPropertyFormGUI $a_form)
	{		
		if($this->id_type == self::PORTFOLIO_OBJECT_ID)
		{
			$this->object->setOnline($a_form->getInput("online"));
			
			// if portfolio is not online, it cannot be default
			if(!$a_form->getInput("online"))
			{
				ilObjPortfolio::setUserDefault($this->user_id, 0);
			}
		}
		
		$this->object->setPublicComments($a_form->getInput("comments"));
		$this->object->setProfilePicture($a_form->getInput("ppic"));
		$this->object->setBackgroundColor($a_form->getInput("bg_color"));
		$this->object->setFontcolor($a_form->getInput("font_color"));

		$prfa_set = new ilSetting("prfa");

		if($_FILES["banner"]["tmp_name"])
		{
			$this->object->uploadImage($_FILES["banner"]);
		}
		else if($prfa_set->get('banner') and $a_form->getItemByPostVar("banner")->getDeletionFlag())
		{
			$this->object->deleteImage();
		}			
	}
	
	
	//
	// PAGES
	//
	
	/**
	 * Show list of portfolio pages
	 */
	public function view()
	{
		global $tpl, $lng, $ilToolbar, $ilCtrl, $ilTabs, $ilSetting, $tree;
		
		if(!$this->checkPermissionBool("write"))
		{
			return;
		}
		
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
				
		include_once "Modules/Portfolio/classes/class.ilPortfolioPageTableGUI.php";
		$table = new ilPortfolioPageTableGUI($this, "view", $this->object);
		
		// exercise portfolio?			
		include_once "Modules/Exercise/classes/class.ilObjExercise.php";			
		$exercises = ilObjExercise::findUserFiles($this->user_id, $this->object->getId());
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
					$part = $this->getExerciseInfo($exercise["ass_id"], $table->dataExists());
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
		
		$tpl->setContent($table->getHTML());
	}
	
	function getExerciseInfo($a_assignment_id, $a_add_submit = false)
	{		
		global $lng, $ilCtrl;
		
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

		$info = sprintf($lng->txt("prtf_exercise_info"), 
			$ass->getTitle(),
			"<a href=\"".$exc_link."\">".
			ilObject::_lookupTitle($exercise_id)."</a>");
		
		// submit button
		if($a_add_submit && !$times_up)
		{
			$ilCtrl->setParameter($this, "exc", $exercise_id);				
			$ilCtrl->setParameter($this, "ass", $a_assignment_id);
			$submit_link = $ilCtrl->getLinkTarget($this, "finalize");
			$ilCtrl->setParameter($this, "ass", "");
			$ilCtrl->setParameter($this, "exc", "");	
			$info .= " <a class=\"submit emphsubmit\" href=\"".$submit_link."\">".$lng->txt("prtf_finalize_portfolio")."</a>";
		}
		
		// submitted files
		$submitted = ilExAssignment::getDeliveredFiles($exercise_id, $a_assignment_id, $this->user_id, true);
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
				"<a href=\"".$dl_link."\" class=\"submit\">".$lng->txt("download")."</a>");
			
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
	 * Show portfolio page creation form
	 */
	protected function addPage()
	{
		global $tpl, $lng, $ilTabs, $ilCtrl;

		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt("back"),
			$ilCtrl->getLinkTarget($this, "view"));

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
		global $lng, $ilCtrl;

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
				$this->object->getTitle());
			$form->addCommandButton("savePage", $lng->txt("save"));
			$form->addCommandButton("view", $lng->txt("cancel"));			
		}
		else
		{
			/* edit is done directly in table gui
			$form->setTitle($lng->txt("prtf_edit_page"));
			$form->addCommandButton("updatePage", $lng->txt("save"));
			$form->addCommandButton("view", $lng->txt("cancel"));
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
		if ($form->checkInput() && $this->checkPermissionBool("write"))
		{
			include_once("Modules/Portfolio/classes/class.ilPortfolioPage.php");
			$page = new ilPortfolioPage();
			$page->setPortfolioId($this->object->getId());
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
			$ilCtrl->redirect($this, "view");
		}

		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt("back"),
			$ilCtrl->getLinkTarget($this, "view"));

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
			$ilCtrl->getLinkTarget($this, "view"));

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
		global $lng, $ilCtrl;

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));

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
		
		// no blogs to add?
		if(!sizeof($options))
		{
			ilUtil::sendInfo($lng->txt("prtf_no_blogs_info"), true);
			$ilCtrl->redirect($this, "view");
		}
		
		$obj = new ilSelectInputGUI($lng->txt("obj_blog"), "blog");
		$obj->setRequired(true);
		$obj->setOptions($options);
		$form->addItem($obj);

		// save and cancel commands
		if ($a_mode == "create")
		{
			$form->setTitle($lng->txt("prtf_add_blog").": ".
				$this->object->getTitle());
			$form->addCommandButton("saveBlog", $lng->txt("save"));
			$form->addCommandButton("view", $lng->txt("cancel"));
			
		}
		else
		{
			/* edit is done directly in table gui
			$form->setTitle($lng->txt("prtf_edit_page"));
			$form->addCommandButton("updatePage", $lng->txt("save"));
			$form->addCommandButton("view", $lng->txt("cancel"));
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
		if ($form->checkInput() && $this->checkPermissionBool("write"))
		{
			$page = new ilPortfolioPage();
			$page->setPortfolioId($this->object->getId());
			$page->setType(ilPortfolioPage::TYPE_BLOG);		
			$page->setTitle($form->getInput("blog"));									
			$page->create();

			ilUtil::sendSuccess($lng->txt("prtf_page_created"), true);
			$ilCtrl->redirect($this, "view");
		}

		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt("back"),
			$ilCtrl->getLinkTarget($this, "view"));

		$form->setValuesByPost();
		$tpl->setContent($form->getHtml());
	}

	/**
	 * Save ordering of portfolio pages
	 */
	function savePortfolioPagesOrdering()
	{
		global $ilCtrl, $lng;
		
		if(!$this->checkPermissionBool("write"))
		{
			return;
		}

		if (is_array($_POST["order"]))
		{
			foreach ($_POST["order"] as $k => $v)
			{
				$page = new ilPortfolioPage(ilUtil::stripSlashes($k));
				$page->setPortfolioId($this->object->getId());
				if($_POST["title"][$k])
				{
					$page->setTitle(ilUtil::stripSlashes($_POST["title"][$k]));
				}
				$page->setOrderNr(ilUtil::stripSlashes($v));
				$page->update();
			}
			ilPortfolioPage::fixOrdering($this->object->getId());
		}
		
		ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
		$ilCtrl->redirect($this, "view");
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
			$ilCtrl->redirect($this, "view");
		}
		else
		{
			include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
			$cgui = new ilConfirmationGUI();
			$cgui->setFormAction($ilCtrl->getFormAction($this));
			$cgui->setHeaderText($lng->txt("prtf_sure_delete_portfolio_pages"));
			$cgui->setCancel($lng->txt("cancel"), "view");
			$cgui->setConfirm($lng->txt("delete"), "deletePortfolioPages");

			foreach ($_POST["prtf_pages"] as $id)
			{
				$page = new ilPortfolioPage($id);
				$page->setPortfolioId($this->object->getId());
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
				
		if(!$this->checkPermissionBool("write"))
		{
			return;
		}

		if (is_array($_POST["prtf_pages"]))
		{
			foreach ($_POST["prtf_pages"] as $id)
			{
				$page = new ilPortfolioPage($id);
				$page->setPortfolioId($this->object->getId());
				$page->delete();
			}
		}
		ilUtil::sendSuccess($lng->txt("prtf_portfolio_page_deleted"), true);
		$ilCtrl->redirect($this, "view");
	}
	
	/**
	 * Show user page
	 */
	function preview($a_return = false, $a_content = false, $a_show_notes = true)
	{
		global $tpl, $ilCtrl, $ilTabs, $lng;
		
		// public profile
		if($_REQUEST["back_url"])
		{
			$back = $_REQUEST["back_url"];						
		}		
		// shared
		else if($_GET["baseClass"] != "ilPublicUserProfileGUI" && 
			$this->user_id && $this->user_id != ANONYMOUS_USER_ID)
		{
			if(!$this->checkPermissionBool("write"))
			{
				$ilCtrl->setParameterByClass("ilportfoliorepositorygui", "shr_id", $this->object->getOwner());
				$back = $ilCtrl->getLinkTargetByClass(array("ilpersonaldesktopgui", "ilportfoliorepositorygui"), "showOther");
				$ilCtrl->setParameterByClass("ilportfoliorepositorygui", "shr_id", "");
			}
			// owner
			else
			{
				$back = $ilCtrl->getLinkTarget($this, "view");
			}
		}
		$tpl->setTopBar($back);
		
		$portfolio_id = $this->object->getId();
		$user_id = $this->object->getOwner();
		
		$ilTabs->clearTargets();
			
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
		}
		
		$ilCtrl->setParameter($this, "user_page", $current_page);
		
		if(!$a_content)
		{
			// get current page content
			include_once("./Modules/Portfolio/classes/class.ilPortfolioPageGUI.php");
			$page_gui = new ilPortfolioPageGUI($portfolio_id, $current_page, 0, 
				$this->object->hasPublicComments());
			$page_gui->setEmbedded(true);
			$page_gui->setAdditional($this->getAdditional());

			$content = $ilCtrl->getHTML($page_gui);
		}
		else
		{
			$content = $a_content;
		}
		
		if($a_return && $this->checkPermissionBool("write"))
		{
			return $content;
		}
		
		// blog posting comments are handled within the blog
		$notes = "";
		if($a_show_notes && $this->object->hasPublicComments() && !$current_blog)
		{			
			include_once("./Services/Notes/classes/class.ilNoteGUI.php");			
			$note_gui = new ilNoteGUI($portfolio_id, $current_page, "pfpg");
			$note_gui->setRepositoryMode(false);			
			$note_gui->enablePublicNotes(true);
			$note_gui->enablePrivateNotes(false);
			$note_gui->enablePublicNotesDeletion($this->user_id == $user_id);
						
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
			
		if($this->perma_link === null)
		{
			include_once('Services/PermanentLink/classes/class.ilPermanentLinkGUI.php');
			$plink = new ilPermanentLinkGUI("prtf", $this->object->getId());
			$plink = $plink->getHTML();		
		}
		else
		{
			$plink = $this->perma_link;
		}
		
		self::renderFullscreenHeader($this->object, $tpl, $user_id);
		
		// wiki/forum will set locator items
		$tpl->setVariable("LOCATOR", "");
		
		// #10717
		$tpl->setContent($content.
			'<div class="ilClearFloat">'.$notes.$plink.'</div>');			
		$tpl->setFrameFixedWidth(true);
		
		echo $tpl->show("DEFAULT", true, true);
		exit();
	}
	
	/**
	 * Render banner, user name
	 * 
	 * @param object  $a_tpl
	 * @param int $a_user_id 
	 * @param bool $a_export_path
	 */
	public static function renderFullscreenHeader($a_portfolio, $a_tpl, $a_user_id, $a_export = false)
	{		
		$name = ilObjUser::_lookupName($a_user_id);
		$name = $name["lastname"].", ".($t = $name["title"] ? $t . " " : "").$name["firstname"];
		
		// show banner?
		$banner = $banner_width = $banner_height = false;
		$prfa_set = new ilSetting("prfa");
		if($prfa_set->get("banner"))
		{		
			$banner = $a_portfolio->getImageFullPath();
			$banner_width = $prfa_set->get("banner_width");
			$banner_height = $prfa_set->get("banner_height");
			if($a_export)
			{
				$banner = basename($banner);
			}
		}
		
		// profile picture
		$ppic = null;
		if($a_portfolio->hasProfilePicture())
		{
			$ppic = ilObjUser::_getPersonalPicturePath($a_user_id, "big");
			if($a_export)
			{
				$ppic = basename($ppic);
			}
		}
		
		include_once("./Services/User/classes/class.ilUserUtil.php");
		$a_tpl->setFullscreenHeader($a_portfolio->getTitle(), 
			$name, 	
			$ppic,
			$banner,
			$a_portfolio->getBackgroundColor(),
			$a_portfolio->getFontColor(),
			$banner_width,
			$banner_height,
			$a_export);
		$a_tpl->setBodyClass("std ilExternal ilPortfolio");
	}
			
	function export()
	{
		include_once "Modules/Portfolio/classes/class.ilPortfolioHTMLExport.php";
		$export = new ilPortfolioHTMLExport($this, $this->object);
		$zip = $export->buildExportFile();
		
	    ilUtil::deliverFile($zip, $this->object->getTitle().".zip", '', false, true);
	}
	
	/**
	 * Finalize and submit portfolio to exercise
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
		$exc_gui->submitPortfolio($this->object->getId());
		
		ilUtil::sendSuccess($lng->txt("prtf_finalized"), true);
		$ilCtrl->redirect($this, "view");
	}
	
	/**
	 * Select target portfolio for page(s) copy
	 */
	function copyPageForm($a_form = null)
	{
		global $ilCtrl, $tpl, $lng;

		if (!is_array($_POST["prtf_pages"]) || count($_POST["prtf_pages"]) == 0)
		{
			ilUtil::sendInfo($lng->txt("no_checkbox"), true);
			$ilCtrl->redirect($this, "view");
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
			foreach($_POST["prtf_pages"] as $page_id)
			{				
				$source = new ilPortfolioPage($page_id);
				$source->setPortfolioId($portfolio_id);
				$target = new ilPortfolioPage();
				$target->setPortfolioId($portfolio_id);
				$target->setXMLContent($source->copyXmlContent());
				$target->setType($source->getType());
				$target->setTitle($source->getTitle());
				$target->create();							
			}
				
			ilUtil::sendSuccess($lng->txt("prtf_pages_copied"), true);
			$ilCtrl->redirect($this, "view");
		}
		
		$form->setValuesByPost();
		$this->copyPageForm($form);
	}
	
	function initCopyPageForm()
	{
		global $lng, $ilCtrl;
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));		
		$form->setTitle($lng->txt("prtf_copy_page"));			

		$tgt = new ilRadioGroupInputGUI($lng->txt("target"), "target");
		$tgt->setRequired(true);
		$form->addItem($tgt);

		$all = ilObjPortfolio::getPortfoliosOfUser($this->user_id);			
		if(sizeof($all) > 1)
		{			
			$old = new ilRadioOption($lng->txt("prtf_existing_portfolio"), "old");
			$tgt->addOption($old);

			$options = array();
			foreach($all as $item)
			{
				if($item["id"] != $this->object->getId())
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
		$form->addCommandButton("view", $lng->txt("cancel"));
		
		return $form;
	}
	
	/**
	* Set Additonal Information.
	*
	* @param	array	$a_additional	Additonal Information
	*/
	function setAdditional($a_additional)
	{
		$this->additional = $a_additional;
	}

	/**
	* Get Additonal Information.
	*
	* @return	array	Additonal Information
	*/
	function getAdditional()
	{
		return $this->additional;
	}
	
	function setPermaLink($a_link)
	{
		$this->perma_link = $a_link;
	}	
	
	function _goto($a_target)
	{
		$id = explode("_", $a_target);
	
		$_GET["baseClass"] = "ilsharedresourceGUI";	
		$_GET["prt_id"] = $id[0];		
		
		include("ilias.php");
		exit;
	}
}

?>