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
		$tpl->setTitle($lng->txt("portfolio"));

		switch($next_class)
		{
			case "ilworkspaceaccessgui";				
				$ilTabs->clearTargets();
				$ilTabs->setBackTarget($lng->txt("back"),
					$ilCtrl->getLinkTarget($this, "show"));			
				
				$tpl->setTitle($lng->txt("portfolio"));
				
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
					$page_id);

				$tpl->setCurrentBlock("ContentStyle");
				$tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
					ilObjStyleSheet::getContentStylePath(0));
				$tpl->parseCurrentBlock();

				$ret = $ilCtrl->forwardCommand($page_gui);
				
				if ($ret != "" && $ret !== true)
				{									
					if(isset($_REQUEST["user_page"]))
					{						
						$this->preview(false, $ret, ($cmd != "previewEmbedded"));
					}
					else
					{
						$tpl->setContent($ret);
					}
				}
				break;
				
			case "ilnotegui";				
				$ilTabs->clearTargets();
				$ilTabs->setBackTarget($lng->txt("back"),
					$ilCtrl->getLinkTarget($this, "preview"));	
				
				$content = $this->preview(true);
				
				include_once("./Services/Notes/classes/class.ilNoteGUI.php");
				$note_gui = new ilNoteGUI($this->portfolio->getId(), 0, "pf", false);
				$note_gui->enablePublicNotes(true);
				$note_gui->enablePrivateNotes(true);
				$note_gui->enablePublicNotesDeletion($ilUser->getId() == $this->portfolio->getUserId());
				$note_gui->setRepositoryMode(false);
				$tpl->setContent($content.$ilCtrl->forwardCommand($note_gui));
				break;
			
			default:				
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
			$page->setTitle($form->getInput("fpage"));
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
		global $tpl;

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
			$this->portfolio->update();
			
			// if portfolio is not online, it cannot be default
			if(!$form->getInput("online"))
			{
				ilObjPortfolio::setUserDefault($ilUser->getId(), 0);
			}

			ilUtil::sendSuccess($lng->txt("prtf_portfolio_updated"), true);
			$ilCtrl->redirect($this, "show");
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
		global $lng, $ilCtrl;

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
			// 1st page
			$tf = new ilTextInputGUI($lng->txt("prtf_first_page_title"), "fpage");
			$tf->setMaxLength(128);
			$tf->setSize(40);
			$tf->setRequired(true);
			$form->addItem($tf);				

			$form->setTitle($lng->txt("prtf_create_portfolio"));
			$form->addCommandButton("save", $lng->txt("save"));
			$form->addCommandButton("show", $lng->txt("cancel"));
		}
		else
		{
			// online
			$online = new ilCheckboxInputGUI($lng->txt("online"), "online");
			$form->addItem($online);

			$ti->setValue($this->portfolio->getTitle());
			// $ta->setValue($this->portfolio->getDescription());
			$online->setChecked($this->portfolio->isOnline());
			
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

	/**
	 * Show list of portfolio pages
	 */
	protected function pages()
	{
		global $tpl, $lng, $ilToolbar, $ilCtrl, $ilTabs;

		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt("back"),
			$ilCtrl->getLinkTarget($this, "show"));

		$ilToolbar->addButton($lng->txt("prtf_add_page"),
			$ilCtrl->getLinkTarget($this, "addPage"));
	
		include_once "Services/Portfolio/classes/class.ilPortfolioPageTableGUI.php";
		$table = new ilPortfolioPageTableGUI($this, "show", $this->portfolio);

		$tpl->setContent($table->getHTML());
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

		include_once("Services/Portfolio/classes/class.ilPortfolioPage.php");
		
		$type = new ilRadioGroupInputGUI($lng->txt("type"), "type");
		$type->setRequired(true);
		$form->addItem($type);
		
		// type: page
		$page = new ilRadioOption($lng->txt("page"), ilPortfolioPage::TYPE_PAGE);
		$type->addOption($page);
		
		// title
		$ti = new ilTextInputGUI($lng->txt("title"), "title");
		$ti->setMaxLength(200);
		$ti->setRequired(true);
		$page->addSubItem($ti);
		
		// type: blog
		$blog = new ilRadioOption($lng->txt("obj_blog"), ilPortfolioPage::TYPE_BLOG);
		$type->addOption($blog);
		
		// blog
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
		$obj = new ilSelectInputGUI($lng->txt("obj_blog"), "blog");
		$obj->setRequired(true);
		$obj->setOptions($options);
		$blog->addSubItem($obj);

		// save and cancel commands
		if ($a_mode == "create")
		{
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
			$page->setType($form->getInput("type"));
			
			switch($form->getInput("type"))
			{
				case ilPortfolioPage::TYPE_PAGE:
					$page->setTitle($form->getInput("title"));		
					break;
				
				case ilPortfolioPage::TYPE_BLOG:
					$page->setTitle($form->getInput("blog"));		
					break;
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
				$cgui->addItem("prtf_pages[]", $id, ilPortfolioPage::lookupTitle($id));
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
		
		// title
		$tpl->setTitle($this->portfolio->getTitle());
		$tpl->setTitleIcon(null);
		
		// page title
		include_once("./Services/User/classes/class.ilUserUtil.php");
		$tpl->setDescription(ilUserUtil::getNamePresentation($user_id, true, false));
		
		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt("back"),
			$ilCtrl->getLinkTarget($this, "show"));
			
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
		if(count($pages) > 1)
		{
			foreach ($pages as $p)
			{
				if($p["type"] == ilPortfolioPage::TYPE_BLOG)
				{
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
		if($a_show_notes)
		{
			include_once("./Services/Notes/classes/class.ilNoteGUI.php");
			$note_gui = new ilNoteGUI($portfolio_id, 0, "pf", false);
			$note_gui->enablePublicNotes(true);
			$note_gui->enablePrivateNotes(true);
			$note_gui->enablePublicNotesDeletion($ilUser->getId() == $user_id);
			$note_gui->setRepositoryMode(false);
			$notes = $note_gui->getNotesHTML();
		}
		
		include_once('classes/class.ilLink.php');
		$goto = ilLink::_getStaticLink($portfolio_id, "prtf", true);
		$goto = "<div style=\"margin:10px;\" class=\"small\"><a href=\"".$goto.
			"\" target=\"blank\">goto test</a></div>";
		
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