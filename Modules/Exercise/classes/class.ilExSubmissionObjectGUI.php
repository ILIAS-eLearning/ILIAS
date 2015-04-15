<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Object-based submissions (ends up as static file)
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * 
 * @ilCtrl_Calls ilExSubmissionObjectGUI: 
 * @ingroup ModulesExercise
 */
class ilExSubmissionObjectGUI extends ilExSubmissionBaseGUI
{
	public function executeCommand()
	{
		global $ilCtrl;
		
		$class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();		
		
		switch($class)
		{		
			default:									
				$this->{$cmd."Object"}();				
				break;			
		}
	}
	
	public static function getOverviewContent(ilInfoScreenGUI $a_info, ilExAssignment $a_ass, $a_missing_team, array $a_files)
	{
		switch($a_ass->getType())
		{
			case ilExAssignment::TYPE_BLOG:
				return self::getOverviewContentBlog($a_info, $a_ass, $a_missing_team, $a_files);
			
			case ilExAssignment::TYPE_PORTFOLIO:
				return self::getOverviewContentPortfolio($a_info, $a_ass, $a_missing_team, $a_files);
		}		
	}	
	
	protected static function getOverviewContentBlog(ilInfoScreenGUI $a_info, ilExAssignment $a_ass, $a_missing_team, array $a_files)
	{
		global $lng, $ilCtrl, $ilUser;
		
		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";					
		$wsp_tree = new ilWorkspaceTree($ilUser->getId());

		// #12939
		if(!$wsp_tree->getRootId())
		{
			$wsp_tree->createTreeForUser($ilUser->getId());
		}

		$files_str = "";
		$valid_blog = false;
		if(sizeof($a_files))
		{													
			$a_files = array_pop($a_files);
			$blog_id = (int)$a_files["filetitle"];																						
			$node = $wsp_tree->getNodeData($blog_id);							
			if($node["title"])
			{
				// #10116						
				$ilCtrl->setParameterByClass("ilobjbloggui", "wsp_id", $blog_id);
				$blog_link = $ilCtrl->getLinkTargetByClass(array("ilpersonaldesktopgui", "ilpersonalworkspacegui", "ilobjbloggui"), "");
				$ilCtrl->setParameterByClass("ilobjbloggui", "wsp_id", "");
				$files_str = '<a href="'.$blog_link.'">'.
					$node["title"].'</a>';
				$valid_blog = true;
			}	
			// remove invalid resource if no upload yet (see download below)
			else if(substr($a_files["filename"], -1) == "/")
			{								
				$this->exc->deleteResourceObject($a_files["ass_id"],
					$ilUser->getId(), $a_files["returned_id"]); 
			}
		}						
		if($a_ass->beforeDeadline())
		{
			if(!$valid_blog)
			{				
				$button = ilLinkButton::getInstance();							
				$button->setCaption("exc_create_blog");
				$button->setUrl($ilCtrl->getLinkTargetByClass(array("ilExSubmissionGUI", "ilExSubmissionObjectGUI"), "createBlog"));							
				$files_str.= $button->render();								
			}							
			// #10462
			$blogs = sizeof($wsp_tree->getObjectsFromType("blog"));						
			if((!$valid_blog && $blogs) 
				|| ($valid_blog && $blogs > 1))
			{							
				$button = ilLinkButton::getInstance();							
				$button->setCaption("exc_select_blog".($valid_blog ? "_change" : ""));
				$button->setUrl($ilCtrl->getLinkTargetByClass(array("ilExSubmissionGUI", "ilExSubmissionObjectGUI"), "selectBlog"));									
				$files_str.= " ".$button->render();
			}
		}
		if($files_str)
		{
			$a_info->addProperty($lng->txt("exc_blog_returned"), $files_str);		
		}
		if($a_files && substr($a_files["filename"], -1) != "/")
		{														
			$dl_link = $ilCtrl->getLinkTargetByClass(array("ilExSubmissionGUI", "ilExSubmissionFileGUI"), array("delivered"=>$a_files["returned_id"]));

			$button = ilLinkButton::getInstance();							
			$button->setCaption("download");
			$button->setUrl($dl_link);		

			$a_info->addProperty($lng->txt("exc_files_returned"), $button->render());		
		}							
	}

	protected function getOverviewContentPortfolio(ilInfoScreenGUI $a_info, ilExAssignment $a_ass, $a_missing_team, array $a_files)
	{
		global $lng, $ilCtrl, $ilUser;
						
		include_once "Modules/Portfolio/classes/class.ilObjPortfolio.php";

		$files_str = "";
		$valid_prtf = false;
		if(sizeof($a_files))
		{
			$a_files = array_pop($a_files);
			$portfolio_id = (int)$a_files["filetitle"];

			// #11746
			if(ilObject::_exists($portfolio_id, false, "prtf"))
			{									
				$portfolio = new ilObjPortfolio($portfolio_id, false);											
				if($portfolio->getTitle())
				{								
					// #10116 / #12791														
					$ilCtrl->setParameterByClass("ilobjportfoliogui", "prt_id", $portfolio_id);
					$prtf_link = $ilCtrl->getLinkTargetByClass(array("ilpersonaldesktopgui", "ilportfoliorepositorygui", "ilobjportfoliogui"), "view");
					$ilCtrl->setParameterByClass("ilobjportfoliogui", "prt_id", "");

					$files_str = '<a href="'.$prtf_link.
						'">'.$portfolio->getTitle().'</a>';
					$valid_prtf = true;
				}
			}
			// remove invalid resource if no upload yet (see download below)
			else if(substr($a_files["filename"], -1) == "/")
			{		
				$this->exc->deleteResourceObject($a_files["ass_id"],
					$ilUser->getId(), $a_files["returned_id"]);							
			}
		}
		if($a_ass->beforeDeadline())
		{
			if(!$valid_prtf)
			{
				// if there are portfolio templates available show form first
				include_once "Modules/Portfolio/classes/class.ilObjPortfolioTemplate.php";
				$has_prtt = sizeof(ilObjPortfolioTemplate::getAvailablePortfolioTemplates())
					? "Template"
					: "";

				$button = ilLinkButton::getInstance();							
				$button->setCaption("exc_create_portfolio");
				$button->setUrl($ilCtrl->getLinkTargetByClass(array("ilExSubmissionGUI", "ilExSubmissionObjectGUI"), "createPortfolio".$has_prtt));										
				$files_str .= $button->render();
			}
			// #10462
			$prtfs = sizeof(ilObjPortfolio::getPortfoliosOfUser($ilUser->getId()));		
			if((!$valid_prtf && $prtfs) 
				|| ($valid_prtf && $prtfs > 1))
			{		
				$button = ilLinkButton::getInstance();							
				$button->setCaption("exc_select_portfolio".($valid_prtf ? "_change" : ""));
				$button->setUrl($ilCtrl->getLinkTargetByClass(array("ilExSubmissionGUI", "ilExSubmissionObjectGUI"), "selectPortfolio"));	
				$files_str.= " ".$button->render();
			}
		}
		if($files_str)
		{
			$a_info->addProperty($lng->txt("exc_portfolio_returned"), $files_str);	
		}
		if($a_files && substr($a_files["filename"], -1) != "/")
		{														
			$dl_link =$ilCtrl->getLinkTargetByClass(array("ilExSubmissionGUI", "ilExSubmissionFileGUI"), array("delivered"=>$a_files["returned_id"]));

			$button = ilLinkButton::getInstance();							
			$button->setCaption("download");
			$button->setUrl($dl_link);		

			$a_info->addProperty($lng->txt("exc_files_returned"), $button->render());		
		}
	}			
	
	
	
	
	
	
	
	

	//
	// BLOG
	//
	
	protected function createBlogObject()
	{
		global $ilUser;
		
		$this->handleTabs();
		
		if (mktime() > $this->assignment->getDeadline() && ($this->assignment->getDeadline() != 0))
		{
			ilUtil::sendInfo($this->lng->txt("exercise_time_over"));
		}
		
		$tpl = new ilTemplate("tpl.exc_select_resource.html", true, true, "Modules/Exercise");
		$tpl->setVariable("TXT_TITLE", $this->lng->txt("exc_create_blog").": ".$this->assignment->getTitle());
		$tpl->setVariable("TREE", $this->renderWorkspaceExplorer("createBlog"));
		$tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$tpl->setVariable("CMD_SUBMIT", "saveBlog");
		$tpl->setVariable("CMD_CANCEL", "returnToParent");
		
		ilUtil::sendInfo($this->lng->txt("exc_create_blog_select_info"));
					
		$this->tpl->setContent($tpl->get());
	}
	
	protected function selectBlogObject()
	{
		global $ilUser;
		
		$this->handleTabs();
		
		if (mktime() > $this->assignment->getDeadline() && ($this->assignment->getDeadline() != 0))
		{
			ilUtil::sendInfo($this->lng->txt("exercise_time_over"));
		}
		
		$tpl = new ilTemplate("tpl.exc_select_resource.html", true, true, "Modules/Exercise");
		$tpl->setVariable("TXT_TITLE", $this->lng->txt("exc_select_blog").": ".$this->assignment->getTitle());
		$tpl->setVariable("TREE", $this->renderWorkspaceExplorer("selectBlog"));
		$tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$tpl->setVariable("CMD_SUBMIT", "setSelectedBlog");
		$tpl->setVariable("CMD_CANCEL", "returnToParent");
		
		ilUtil::sendInfo($this->lng->txt("exc_select_blog_info"));
					
		$this->tpl->setContent($tpl->get());
	}
	
	protected function saveBlogObject()
	{
		global $ilUser;
		
		if(!$_POST["node"])
		{
			ilUtil::sendFailure($this->lng->txt("select_one"));
			return $this->createBlogObject();
		}
		
		$parent_node = $_POST["node"];
		
		include_once "Modules/Blog/classes/class.ilObjBlog.php";
		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";
		
		$blog = new ilObjBlog();
		$blog->setTitle($this->exercise->getTitle()." - ".$this->assignment->getTitle());
		$blog->create();
		
		$tree = new ilWorkspaceTree($ilUser->getId());
		
		$node_id = $tree->insertObject($parent_node, $blog->getId());
		
		$access_handler = new ilWorkspaceAccessHandler($tree);
		$access_handler->setPermissions($parent_node, $node_id);
		
		$this->exercise->addResourceObject($node_id, $this->assignment->getId(), $ilUser->getId());
		
		ilUtil::sendSuccess($this->lng->txt("exc_blog_created"), true);
		$this->ctrl->redirect($this, "returnToParent");
	}
	
	protected function setSelectedBlogObject()
	{
		global $ilUser;
		
		if($_POST["node"])
		{
			include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";		
			$tree = new ilWorkspaceTree($ilUser->getId());
			$node = $tree->getNodeData($_POST["node"]);
			if($node && $node["type"] == "blog")
			{
				$this->removeExistingSubmissions();
				$this->exercise->addResourceObject($node["wsp_id"], $this->assignment->getId(), $ilUser->getId());
				
				ilUtil::sendSuccess($this->lng->txt("exc_blog_selected"), true);
				$this->ctrl->setParameter($this, "blog_id", $node["wsp_id"]);
				$this->ctrl->redirect($this, "askDirectionSubmission");				
			}
		}
		
		ilUtil::sendFailure($this->lng->txt("select_one"));
		return $this->selectPortfolioObject();
	}
	
	protected function renderWorkspaceExplorer($a_cmd)
	{
		global $ilUser;
		
		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";
		require_once 'Services/PersonalWorkspace/classes/class.ilWorkspaceExplorer.php';
		
		$tree = new ilWorkspaceTree($ilUser->getId());
		$access_handler = new ilWorkspaceAccessHandler($tree);
		$exp = new ilWorkspaceExplorer(ilWorkspaceExplorer::SEL_TYPE_RADIO, '', 
			'exc_wspexpand', $tree, $access_handler);
		$exp->setTargetGet('wsp_id');
		
		if($a_cmd == "selectBlog")
		{
			$exp->removeAllFormItemTypes();
			$exp->addFilter('blog');
			$exp->addFormItemForType('blog');
		}
	
		if($_GET['exc_wspexpand'] == '')
		{
			// not really used as session is already set [see above]
			$expanded = $tree->readRootId();
		}
		else
		{
			$expanded = $_GET['exc_wspexpand'];
		}
		
		$exp->setExpandTarget($this->ctrl->getLinkTarget($this, $a_cmd));
		$exp->setPostVar('node');
		$exp->setExpand($expanded);
		$exp->setOutput(0);
	
		return $exp->getOutput();
	}
	
	
	//
	// PORTFOLIO
	//
	
	protected function selectPortfolioObject()
	{
		global $ilUser;
		
		$this->tabs_gui->clearTargets();
		$this->tabs_gui->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "returnToParent"));
		
		// $this->tabs_gui->setTabActive("content");
		// $this->addContentSubTabs("content");
		
		if (mktime() > $this->assignment->getDeadline() && ($this->assignment->getDeadline() != 0))
		{
			ilUtil::sendInfo($this->lng->txt("exercise_time_over"));
		}
		
		$tpl = new ilTemplate("tpl.exc_select_resource.html", true, true, "Modules/Exercise");
		
		include_once "Modules/Portfolio/classes/class.ilObjPortfolio.php";
		$portfolios = ilObjPortfolio::getPortfoliosOfUser($ilUser->getId());
		if($portfolios)
		{
			$tpl->setCurrentBlock("item");
			foreach($portfolios as $portfolio)
			{
				$tpl->setVariable("ITEM_ID", $portfolio["id"]);
				$tpl->setVariable("ITEM_TITLE", $portfolio["title"]);
				$tpl->parseCurrentBlock();				
			}			
		}
		
		$tpl->setVariable("TXT_TITLE", $this->lng->txt("exc_select_portfolio").": ".$this->assignment->getTitle());
		$tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$tpl->setVariable("CMD_SUBMIT", "setSelectedPortfolio");
		$tpl->setVariable("CMD_CANCEL", "returnToParent");
		
		ilUtil::sendInfo($this->lng->txt("exc_select_portfolio_info"));
					
		$this->tpl->setContent($tpl->get());
	}
	
	protected function initPortfolioTemplateForm(array $a_templates)
	{
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = new ilPropertyFormGUI();		
		$form->setTitle($this->lng->txt("exc_create_portfolio").": ".$this->assignment->getTitle());	
		$form->setFormAction($this->ctrl->getFormAction($this, "setSelectedPortfolioTemplate"));
				
		$prtt = new ilRadioGroupInputGUI($this->lng->txt("obj_prtt"), "prtt");
		$prtt->setRequired(true);
		$prtt->addOption(new ilRadioOption($this->lng->txt("exc_create_portfolio_no_template"), -1));		
		foreach($a_templates as $id => $title)
		{
			$prtt->addOption(new ilRadioOption('"'.$title.'"', $id));
		}
		$prtt->setValue(-1);
		$form->addItem($prtt);
			
		$form->addCommandButton("setSelectedPortfolioTemplate", $this->lng->txt("save"));				
		$form->addCommandButton("returnToParent", $this->lng->txt("cancel"));
		
		return $form;		
	}
	
	protected function createPortfolioTemplateObject(ilPropertyFormGUI $a_form = null)
	{
				include_once "Modules/Portfolio/classes/class.ilObjPortfolioTemplate.php";
		$templates = ilObjPortfolioTemplate::getAvailablePortfolioTemplates();
		if(!sizeof($templates))
		{
			$this->ctrl->redirect($this, "returnToParent");
		}
		
		$this->tabs_gui->clearTargets();
		$this->tabs_gui->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "returnToParent"));
		
		if(!$a_form)
		{
			$a_form = $this->initPortfolioTemplateForm($templates);
		}
		
		$this->tpl->setContent($a_form->getHTML());		
	}
	
	protected function setSelectedPortfolioTemplateObject()
	{		
		include_once "Modules/Portfolio/classes/class.ilObjPortfolioTemplate.php";
		$templates = ilObjPortfolioTemplate::getAvailablePortfolioTemplates();
		if(!sizeof($templates))
		{
			$this->ctrl->redirect($this, "returnToParent");
		}
		
		$form = $this->initPortfolioTemplateForm($templates);
		if($form->checkInput())
		{
			$prtt = $form->getInput("prtt");
			if($prtt > 0 && array_key_exists($prtt, $templates))
			{
				$title = $this->exercise->getTitle()." - ".$this->assignment->getTitle();
				$this->ctrl->setParameterByClass("ilObjPortfolioGUI", "exc_id", $this->exercise->getRefId());
				$this->ctrl->setParameterByClass("ilObjPortfolioGUI", "ass_id", $this->assignment->getId());
				$this->ctrl->setParameterByClass("ilObjPortfolioGUI", "pt", $title);
				$this->ctrl->setParameterByClass("ilObjPortfolioGUI", "prtt", $prtt);
				$this->ctrl->redirectByClass(array("ilPersonalDesktopGUI", "ilPortfolioRepositoryGUI", "ilObjPortfolioGUI"), "createPortfolioFromTemplate");
			}
			else
			{
				// do not use template
				return $this->createPortfolioObject();
			}			
		}
		
		$form->setValuesByPost();
		$this->createPortfolioTemplateObject($form);
	}
	
	protected function createPortfolioObject()
	{
		global $ilUser;
		
		include_once "Modules/Portfolio/classes/class.ilObjPortfolio.php";
		$portfolio = new ilObjPortfolio();
		$portfolio->setTitle($this->exercise->getTitle()." - ".$this->assignment->getTitle());
		$portfolio->create();
	
		$this->exercise->addResourceObject($portfolio->getId(), $this->assignment->getId(), $ilUser->getId());
		
		ilUtil::sendSuccess($this->lng->txt("exc_portfolio_created"), true);
		$this->ctrl->redirect($this, "returnToParent");
	}
	
	protected function setSelectedPortfolioObject()
	{
		global $ilUser;
		
		if($_POST["item"])
		{			
			$this->removeExistingSubmissions();
			$this->exercise->addResourceObject($_POST["item"], $this->assignment->getId(), $ilUser->getId());
						
			ilUtil::sendSuccess($this->lng->txt("exc_portfolio_selected"), true);
			$this->ctrl->setParameter($this, "prtf_id", $_POST["item"]);
			$this->ctrl->redirect($this, "askDirectionSubmission");									
		}
		
		ilUtil::sendFailure($this->lng->txt("select_one"));
		return $this->selectPortfolioObject();
	}
	
	
	//
	// SUBMIT BLOG/PORTFOLIO
	//	
	
	/**
	 * remove existing files/submissions for assignment
	 */
	public function removeExistingSubmissions()
	{		
		global $ilUser;
		
		$submitted = ilExAssignment::getDeliveredFiles($this->assignment->getExerciseId(), $this->assignment->getId(), $ilUser->getId());
		if($submitted)
		{
			$files = array();
			foreach($submitted as $item)
			{
				$files[] = $item["returned_id"];
			}
			ilExAssignment::deleteDeliveredFiles($this->assignment->getExerciseId(), $this->assignment->getId(), $files, $ilUser->getId());
		}			
	}
	
	protected function askDirectionSubmissionObject()
	{
		global $tpl;
		
		$this->tabs_gui->setTabActive("content");
		$this->addContentSubTabs("content");
		
		include_once "Services/Utilities/classes/class.ilConfirmationGUI.php";
		$conf = new ilConfirmationGUI();
		
		
		if($_REQUEST["blog_id"])
		{
			$this->ctrl->setParameter($this, "blog_id", $_REQUEST["blog_id"]);
			$txt = $this->lng->txt("exc_direct_submit_blog"); 
		}
		else
		{
			$this->ctrl->setParameter($this, "prtf_id", $_REQUEST["prtf_id"]);
			$txt = $this->lng->txt("exc_direct_submit_portfolio"); 
		}
		$conf->setFormAction($this->ctrl->getFormAction($this, "directSubmit"));
		
		$conf->setHeaderText($txt);
		$conf->setConfirm($this->lng->txt("submit"), "directSubmit");
		$conf->setCancel($this->lng->txt("cancel"), "returnToParent");
		
		$tpl->setContent($conf->getHTML());
	}
	
	protected function directSubmitObject()
	{
		global $ilUser;
		
		$success = false;
		
		// submit current version of blog
		if($_REQUEST["blog_id"])
		{
			$success = $this->submitBlog($_REQUEST["blog_id"]);
			$this->ctrl->setParameter($this, "blog_id", "");
		}
		// submit current version of portfolio
		else if($_REQUEST["prtf_id"])
		{
			$success = 	$this->submitPortfolio($_REQUEST["prtf_id"]);
			$this->ctrl->setParameter($this, "prtf_id", "");
		}
				
		if($success)
		{	
			ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
		}
		else
		{
			ilUtil::sendFailure($this->lng->txt("msg_failed"), true);
		}
		$this->ctrl->redirect($this, "returnToParent");		
	}
	
	/**
	 * Submit blog for assignment
	 * 
	 * @param int $a_blog_id
	 * @return bool
	 */
	function submitBlog($a_blog_id)
	{
		global $ilUser;
		
		if($this->exercise && $this->ass)
		{
			$blog_id = $a_blog_id;		

			include_once "Modules/Blog/classes/class.ilObjBlogGUI.php";
			$blog_gui = new ilObjBlogGUI($blog_id, ilObjBlogGUI::WORKSPACE_NODE_ID);
			if($blog_gui->object)
			{
				$file = $blog_gui->buildExportFile();
				$size = filesize($file);
				if($size)
				{
					$this->removeExistingSubmissions();
					
					$meta = array(
						"name" => $blog_id,
						"tmp_name" => $file,
						"size" => $size	
						);		
					$this->exercise->deliverFile($meta, $this->assignment->getId(), $ilUser->getId(), true);	

					$this->sendNotifications($this->assignment->getId());
					$this->exercise->handleSubmission($this->assignment->getId());	
					return true;
				}
			}
		}
		return false;
	}
	
	/**
	 * Submit portfolio for assignment
	 * 
	 * @param int $a_portfolio_id
	 * @return bool 
	 */
	function submitPortfolio($a_portfolio_id)
	{
		global $ilUser;
		
		if($this->exercise && $this->ass)
		{
			$prtf_id = $a_portfolio_id;			

			include_once "Modules/Portfolio/classes/class.ilObjPortfolio.php";
			$prtf = new ilObjPortfolio($prtf_id, false);	
			if($prtf->getTitle())
			{
				include_once "Modules/Portfolio/classes/class.ilPortfolioHTMLExport.php";
				$export = new ilPortfolioHTMLExport(null, $prtf);
				$file = $export->buildExportFile();
				$size = filesize($file);
				if($size)
				{
					$this->removeExistingSubmissions();
					
					$meta = array(
						"name" => $prtf_id,
						"tmp_name" => $file,
						"size" => $size
						);		
					$this->exercise->deliverFile($meta, $this->assignment->getId(), $ilUser->getId(), true);	

					$this->sendNotifications($this->assignment->getId());
					$this->exercise->handleSubmission($this->assignment->getId());	
					return true;
				}
			}
		}
		return false;
	}	
}
