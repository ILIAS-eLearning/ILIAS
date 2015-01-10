<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageObjectGUI.php");
include_once("./Modules/Wiki/classes/class.ilWikiPage.php");

/**
* Class ilWikiPage GUI class
* 
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
*
* @ilCtrl_Calls ilWikiPageGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMediaPoolTargetSelector
* @ilCtrl_Calls ilWikiPageGUI: ilPublicUserProfileGUI, ilPageObjectGUI, ilNoteGUI
* @ilCtrl_Calls ilWikiPageGUI: ilCommonActionDispatcherGUI, ilRatingGUI, ilWikiStatGUI
*
* @ingroup ModulesWiki
*/
class ilWikiPageGUI extends ilPageObjectGUI
{
	/**
	* Constructor
	*/
	function __construct($a_id = 0, $a_old_nr = 0, $a_wiki_ref_id = 0)
	{
		global $tpl;

		// needed for notifications
		$this->setWikiRefId($a_wiki_ref_id);
		
		parent::__construct("wpg", $a_id, $a_old_nr);
		$this->getPageObject()->setWikiRefId($this->getWikiRefId());
		
		// content style
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		
		$tpl->setCurrentBlock("SyntaxStyle");
		$tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
			ilObjStyleSheet::getSyntaxStylePath());
		$tpl->parseCurrentBlock();
	}
	
	/**
	 * Set screen id component
	 *
	 * @param
	 * @return
	 */
	function setScreenIdComponent()
	{
		global $ilHelp;
		
		$ilHelp->setScreenIdComponent("copgwpg");
	}

	function setWikiRefId($a_ref_id)
    {
		$this->wiki_ref_id = $a_ref_id;
	}

	function getWikiRefId()
    {
		return $this->wiki_ref_id;
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilCtrl, $ilTabs, $ilUser, $ilAccess;
		
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			case "ilnotegui":
				$this->getTabs();
				$ilTabs->setTabActive("pg");
				return $this->preview();
				break;
						
			case "ilratinggui":				
				// for rating side block
				include_once("./Services/Rating/classes/class.ilRatingGUI.php");
				$rating_gui = new ilRatingGUI();
				$rating_gui->setObject($this->getPageObject()->getParentId(), "wiki",
					$this->getPageObject()->getId(), "wpg");
				$rating_gui->setUpdateCallback(array($this, "updateStatsRating"));				
				$this->ctrl->forwardCommand($rating_gui);
				$ilCtrl->redirect($this, "preview");
				break;

			case "ilpageobjectgui":
	die("Deprecated. Wikipage gui forwarding to ilpageobject");
				return;
				
			case "ilcommonactiondispatchergui":
				include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
				$gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
				$gui->enableCommentsSettings(false);
				$gui->setRatingCallback($this, "preview");
				$this->ctrl->forwardCommand($gui);
				break;
			
			case "ilwikistatgui":		
				if($ilAccess->checkAccess("statistics_read", "", $this->wiki_ref_id))
				{
					$this->tabs_gui->clearTargets(); // see ilObjWikiGUI::getTabs()
					$this->getTabs("statistics");

					include_once "Modules/Wiki/classes/class.ilWikiStatGUI.php";
					$gui = new ilWikiStatGUI($this->getPageObject()->getParentId(),
						$this->getPageObject()->getId());
					$this->ctrl->forwardCommand($gui);
				}
				break;			
				
			default:

				if($_GET["ntf"])
				{
					include_once "./Services/Notification/classes/class.ilNotification.php";
                    switch($_GET["ntf"])
				    {
						case 1:
							ilNotification::setNotification(ilNotification::TYPE_WIKI, $ilUser->getId(), $this->getPageObject()->getParentId(), false);
							break;

						case 2:
							// remove all page notifications here?
							ilNotification::setNotification(ilNotification::TYPE_WIKI, $ilUser->getId(), $this->getPageObject()->getParentId(), true);
							break;

						case 3:
							ilNotification::setNotification(ilNotification::TYPE_WIKI_PAGE, $ilUser->getId(), $this->getPageObject()->getId(), false);
							break;

						case 4:
							ilNotification::setNotification(ilNotification::TYPE_WIKI_PAGE, $ilUser->getId(), $this->getPageObject()->getId(), true);
							break;
				   }
				   $ilCtrl->redirect($this, "preview");
				}
				
				$this->setPresentationTitle($this->getWikiPage()->getTitle());
				return parent::executeCommand();
		}
	}

	/**
	* Set Wiki Page Object.
	*
	* @param	object	$a_wikipage	Wiki Page Object
	*/
	function setWikiPage($a_wikipage)
	{
		$this->setPageObject($a_wikipage);
	}

	/**
	* Get Wiki Page Object.
	*
	* @return	object	Wiki Page Object
	*/
	function getWikiPage()
	{
		return $this->getPageObject();
	}

	/**
	* Get wiki page gui for id and title
	*/
	static function getGUIForTitle($a_wiki_id, $a_title, $a_old_nr = 0, $a_wiki_ref_id = 0)
	{
		global $ilDB;

		include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
		$id = ilWikiPage::getPageIdForTitle($a_wiki_id, $a_title);
		$page_gui = new ilWikiPageGUI($id, $a_old_nr, $a_wiki_ref_id);
		
		return $page_gui;
	}
	
	function setSideBlock()
	{
		ilObjWikiGUI::renderSideBlock($this->getWikiPage()->getId(),
			$this->wiki_ref_id, $this->getWikiPage());
	}
	
	function addHeaderAction($a_redraw = false)
	{			
		global $ilUser, $ilAccess;
		
		$wiki_id = $this->getPageObject()->getParentId();
		$page_id = $this->getPageObject()->getId();
		
		include_once "Services/Object/classes/class.ilCommonActionDispatcherGUI.php";
		$dispatcher = new ilCommonActionDispatcherGUI(ilCommonActionDispatcherGUI::TYPE_REPOSITORY, 
			$ilAccess, "wiki", $_GET["ref_id"], $wiki_id);
		$dispatcher->setSubObject("wpg", $page_id);

		include_once "Services/Object/classes/class.ilObjectListGUI.php";
		ilObjectListGUI::prepareJSLinks($this->ctrl->getLinkTarget($this, "redrawHeaderAction", "", true), 			
			$this->ctrl->getLinkTargetByClass(array("ilcommonactiondispatchergui", "ilnotegui"), "", "", true, false), 
			$this->ctrl->getLinkTargetByClass(array("ilcommonactiondispatchergui", "iltagginggui"), "", "", true, false));

		$lg = $dispatcher->initHeaderAction();
		$lg->enableNotes(true);
		$lg->enableComments(ilObjWiki::_lookupPublicNotes($wiki_id), false);
		
		// rating
		if (ilObjWiki::_lookupRatingOverall($wiki_id))
		{
			$lg->enableRating(true, $this->lng->txt("wiki_rate_overall"), 
				false,
				// so ilCtrl does not use the shortcut via ilWikiGUI
				array("ilcommonactiondispatchergui", "ilratinggui"));
		}

		// notification
		if ($ilUser->getId() != ANONYMOUS_USER_ID)
		{
			include_once "./Services/Notification/classes/class.ilNotification.php";
			if(ilNotification::hasNotification(ilNotification::TYPE_WIKI, $ilUser->getId(), $wiki_id))
			{
				$this->ctrl->setParameter($this, "ntf", 1);
				$lg->addCustomCommand($this->ctrl->getLinkTarget($this), "wiki_notification_deactivate_wiki");

				$lg->addHeaderIcon("not_icon",
					ilUtil::getImagePath("notification_on.svg"),
					$this->lng->txt("wiki_notification_activated"));
			}
			else
			{
				$this->ctrl->setParameter($this, "ntf", 2);
				$lg->addCustomCommand($this->ctrl->getLinkTarget($this), "wiki_notification_activate_wiki");
				
				if(ilNotification::hasNotification(ilNotification::TYPE_WIKI_PAGE, $ilUser->getId(), $page_id))
				{
					$this->ctrl->setParameter($this, "ntf", 3);
					$lg->addCustomCommand($this->ctrl->getLinkTarget($this), "wiki_notification_deactivate_page");

					$lg->addHeaderIcon("not_icon",
						ilUtil::getImagePath("notification_on.svg"),
						$this->lng->txt("wiki_page_notification_activated"));					
				}
				else
				{
					$this->ctrl->setParameter($this, "ntf", 4);
					$lg->addCustomCommand($this->ctrl->getLinkTarget($this), "wiki_notification_activate_page");
					
					$lg->addHeaderIcon("not_icon",
						ilUtil::getImagePath("notification_off.svg"),
						$this->lng->txt("wiki_notification_deactivated"));
				}
			}
			$this->ctrl->setParameter($this, "ntf", "");
		}		
		
		if(!$a_redraw)
		{
			$this->tpl->setHeaderActionMenu($lg->getHeaderAction());		
		}
		else
		{
			// we need to add onload code manually (rating, comments, etc.)
			return $lg->getHeaderAction().
				$this->tpl->getOnLoadCodeForAsynch();
		}
	}
		
	function redrawHeaderAction()
	{		
		echo $this->addHeaderAction(true);
		exit;
	}

	/**
	* View wiki page.
	*/
	function preview()
	{
		global $ilCtrl, $ilAccess, $lng, $tpl, $ilUser, $ilSetting, $ilToolbar;

		// block/unblock
		if ($this->getPageObject()->getBlocked())
		{
			ilUtil::sendInfo($lng->txt("wiki_page_status_blocked"));
		}

		$this->increaseViewCount();
				
		$this->addHeaderAction();
		
		// content
		$this->setSideBlock();
		
		$wtpl = new ilTemplate("tpl.wiki_page_view_main_column.html",
			true, true, "Modules/Wiki");
		
		$callback = array($this, "observeNoteAction");
		
		// notes
		if (!$ilSetting->get("disable_comments") &&
			ilObjWiki::_lookupPublicNotes($this->getPageObject()->getParentId()))
		{
			$may_delete = ($ilSetting->get("comments_del_tutor", 1) &&
				$ilAccess->checkAccess("write", "", $_GET["ref_id"]));		
			$wtpl->setVariable("NOTES", $this->getNotesHTML($this->getPageObject(),
				true, ilObjWiki::_lookupPublicNotes($this->getPageObject()->getParentId()),
				$may_delete, $callback));
		}
		
		// permanent link
		$append = ($_GET["page"] != "")
			? "_".ilWikiUtil::makeUrlTitle($_GET["page"])
			: "";

		// see #13804
		if ($_GET["page"] != "")
		{
			$tpl->setPermanentLink("wiki", "", "wpage_".$this->getPageObject()->getId()."_".$_GET["ref_id"]);
		}
		else
		{
			$tpl->setPermanentLink("wiki", $_GET["ref_id"]);
		}



		// page content
		$this->setOutputMode(IL_PAGE_PRESENTATION);
		$this->setRenderPageContainer(true);
		$wtpl->setVariable("PAGE", $this->showPage());

		$tpl->setLoginTargetPar("wiki_".$_GET["ref_id"].$append);

		// last edited info
		include_once("./Services/User/classes/class.ilUserUtil.php");
		$wtpl->setVariable("LAST_EDITED_INFO",
			$lng->txt("wiki_last_edited").": ".
			ilDatePresentation::formatDate(
				new ilDateTime($this->getPageObject()->getLastChange(),IL_CAL_DATETIME)).", ".
			ilUserUtil::getNamePresentation($this->getPageObject()->getLastChangeUser(),
				false, true, $ilCtrl->getLinkTarget($this, "preview")));

		$tpl->setLoginTargetPar("wiki_".$_GET["ref_id"].$append);
		
		//highlighting
		if ($_GET["srcstring"] != "")
		{
			include_once './Services/Search/classes/class.ilUserSearchCache.php';
			$cache =  ilUserSearchCache::_getInstance($ilUser->getId());
			$cache->switchSearchType(ilUserSearchCache::LAST_QUERY);
			$search_string = $cache->getQuery();
			
			// advanced search?
			if(is_array($search_string))
			{
				$search_string = $search_string["lom_content"];
			}

			include_once("./Services/UIComponent/TextHighlighter/classes/class.ilTextHighlighterGUI.php");
			include_once("./Services/Search/classes/class.ilQueryParser.php");
			$p = new ilQueryParser($search_string);
			$p->parse();

			$words = $p->getQuotedWords();
			if (is_array($words))
			{
				foreach ($words as $w)
				{
					ilTextHighlighterGUI::highlight("ilCOPageContent", $w, $tpl);
				}
			}
			$this->fill_on_load_code = true;
		}
		
		return $wtpl->get();
	}
	
	function showPage()
	{
		global $tpl, $ilCtrl;
		
		// content style
/*		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		$tpl->setCurrentBlock("ContentStyle");
		$tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath(0));
		$tpl->parseCurrentBlock();
*/
		$this->setTemplateOutput(false);
		
		if(!$this->getAbstractOnly())
		{
			$this->setPresentationTitle($this->getWikiPage()->getTitle());
			
			// wiki stats clean up
			// $this->increaseViewCount(); 
		}
	
		return parent::showPage();
	}
	
	protected function increaseViewCount()
	{
		global $ilUser;
		
		$this->getWikiPage()->increaseViewCnt();
		
		// enable object statistics
		require_once('Services/Tracking/classes/class.ilChangeEvent.php');
		ilChangeEvent::_recordReadEvent("wiki", $this->getWikiPage()->getWikiRefId(),
			$this->getWikiPage()->getWikiId(), $ilUser->getId());	
		
		include_once "./Modules/Wiki/classes/class.ilWikiStat.php";
		ilWikiStat::handleEvent(ilWikiStat::EVENT_PAGE_READ, $this->getWikiPage());
	}

	/**
	* Finalizing output processing.
	*/
	function postOutputProcessing($a_output)
	{
		global $ilCtrl;

//echo htmlentities($a_output);
		include_once("./Modules/Wiki/classes/class.ilWikiUtil.php");

		$ilCtrl->setParameterByClass("ilobjwikigui", "from_page", ilWikiUtil::makeUrlTitle($_GET["page"]));
		$output = ilWikiUtil::replaceInternalLinks($a_output,
			$this->getWikiPage()->getWikiId(),
			($this->getOutputMode() == "offline"));
		$ilCtrl->setParameterByClass("ilobjwikigui", "from_page", $_GET["from_page"]);

		return $output;
	}
	
	/**
	* All links to a specific page
	*/
	function whatLinksHere()
	{
		global $tpl;
		
		include_once("./Modules/Wiki/classes/class.ilWikiPagesTableGUI.php");
		
		$this->setSideBlock($_GET["wpg_id"]);
		$table_gui = new ilWikiPagesTableGUI($this, "whatLinksHere",
			$this->getWikiPage()->getWikiId(), IL_WIKI_WHAT_LINKS_HERE, $_GET["wpg_id"]);
			
		$tpl->setContent($table_gui->getHTML());
	}

	function getTabs($a_activate = "")
	{
		global $ilTabs, $ilCtrl, $ilAccess;

		parent::getTabs($a_activate);
		
		if($ilAccess->checkAccess("statistics_read", "", $_GET["ref_id"])) 
		{
			$ilTabs->addTarget("statistics",
				$this->ctrl->getLinkTargetByClass(array("ilwikipagegui", "ilwikistatgui"),
				"initial"), "", "ilwikistatgui");
		}		
		
		$ilCtrl->setParameterByClass("ilobjwikigui", "wpg_id",
			ilWikiPage::getPageIdForTitle($this->getPageObject()->getParentId(),
			ilWikiUtil::makeDbTitle($_GET["page"])));
		$ilCtrl->setParameterByClass("ilobjwikigui", "page", ilWikiUtil::makeUrlTitle($_GET["page"]));

		$ilTabs->addTarget("wiki_what_links_here",
			$this->ctrl->getLinkTargetByClass("ilwikipagegui",
			"whatLinksHere"), "whatLinksHere");
		//$ilTabs->addTarget("wiki_print_view",
		//	$this->ctrl->getLinkTargetByClass("ilobjwikigui",
		//	"printViewSelection"), "printViewSelection");
		$ilTabs->addTarget("wiki_print_view",
			$this->ctrl->getLinkTargetByClass("ilwikipagegui",
			"printViewSelection"), "printViewSelection");

	}

	/**
	* Delete wiki page confirmation screen.
	*/
	function deleteWikiPageConfirmationScreen()
	{
		global $ilAccess, $tpl, $ilCtrl, $lng;
		
		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"]))
		{
			include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
			$confirmation_gui = new ilConfirmationGUI();
			$confirmation_gui->setFormAction($ilCtrl->getFormAction($this));
			$confirmation_gui->setHeaderText($lng->txt("wiki_page_deletion_confirmation"));
			$confirmation_gui->setCancel($lng->txt("cancel"), "cancelWikiPageDeletion");
			$confirmation_gui->setConfirm($lng->txt("delete"), "confirmWikiPageDeletion");
			
			$dtpl = new ilTemplate("tpl.wiki_page_deletion_confirmation.html", true,
				true, "Modules/Wiki");
				
			$dtpl->setVariable("PAGE_TITLE", $this->getWikiPage()->getTitle());
			
			// other pages that link to this page
			$dtpl->setVariable("TXT_OTHER_PAGES", $lng->txt("wiki_other_pages_linking"));
			$pages = ilWikiPage::getLinksToPage($this->getWikiPage()->getWikiId(),
					$this->getWikiPage()->getId());
			if (count($pages) > 0)
			{
				foreach($pages as $page)
				{
					$dtpl->setCurrentBlock("lpage");
					$dtpl->setVariable("TXT_LINKING_PAGE", $page["title"]);
					$dtpl->parseCurrentBlock();
				}
			}
			else
			{
				$dtpl->setCurrentBlock("lpage");
				$dtpl->setVariable("TXT_LINKING_PAGE", "-");
				$dtpl->parseCurrentBlock();
			}
			
			// contributors
			$dtpl->setVariable("TXT_CONTRIBUTORS", $lng->txt("wiki_contributors"));
			$contributors = ilWikiPage::getPageContributors($this->getWikiPage()->getId());
			foreach($contributors as $contributor)
			{
				$dtpl->setCurrentBlock("contributor");
				$dtpl->setVariable("TXT_CONTRIBUTOR",
					$contributor["lastname"].", ".$contributor["firstname"]);
				$dtpl->parseCurrentBlock();
			}
			
			// notes/comments
			include_once("./Services/Notes/classes/class.ilNote.php");
			$cnt_note_users = ilNote::getUserCount($this->getPageObject()->getParentId(),
				$this->getPageObject()->getId(), "wpg");
			$dtpl->setVariable("TXT_NUMBER_USERS_NOTES_OR_COMMENTS",
				$lng->txt("wiki_number_users_notes_or_comments"));
			$dtpl->setVariable("TXT_NR_NOTES_COMMENTS", $cnt_note_users);
			
			$confirmation_gui->addItem("", "", $dtpl->get());
			
			$tpl->setContent($confirmation_gui->getHTML());
		}
	}

	/**
	* Cancel wiki page deletion
	*/
	function cancelWikiPageDeletion()
	{
		global $lng, $ilCtrl;
		
		$ilCtrl->redirect($this, "preview");
		
	}
	
	/**
	* Delete the wiki page
	*/
	function confirmWikiPageDeletion()
	{
		global $ilAccess, $tpl, $ilCtrl, $lng;
		
		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"]))
		{
			$this->getPageObject()->delete();
			
			ilUtil::sendSuccess($lng->txt("wiki_page_deleted"), true);
		}
		
		$ilCtrl->redirectByClass("ilobjwikigui", "allPages");
	}

	////
	//// Print view selection
	////

	/**
	 * Print view selection
	 *
	 * @param
	 * @return
	 */
	function printViewSelection()
	{
		global $ilUser, $lng, $ilToolbar, $ilCtrl, $tpl;

		/*$ilToolbar->setFormAction($ilCtrl->getFormActionByClass("ilobjwikigui", "printView"),
			false, "print_view");
		$ilToolbar->addFormButton($lng->txt("cont_show_print_view"), "printView");
		$ilToolbar->setCloseFormTag(false);*/

		$this->initPrintViewSelectionForm();

		$tpl->setContent($this->form->getHTML());
	}

	/**
	 * Init print view selection form.
	 */
	public function initPrintViewSelectionForm()
	{
		global $lng, $ilCtrl;

		$pages = ilWikiPage::getAllPages(ilObject::_lookupObjId($this->getWikiRefId()));

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		
		// because of PDF export
		$this->form->setPreventDoubleSubmission(false);
		
//var_dump($pages);
		// selection type
		$radg = new ilRadioGroupInputGUI($lng->txt("cont_selection"), "sel_type");
		$radg->setValue("page");
			$op1 = new ilRadioOption($lng->txt("cont_current_page"), "page");
			$radg->addOption($op1);
			$op2 = new ilRadioOption($lng->txt("wiki_whole_wiki")
				." (".$lng->txt("wiki_pages").": ".count($pages).")", "wiki");
			$radg->addOption($op2);
			$op3= new ilRadioOption($lng->txt("wiki_selected_pages"), "selection");
			$radg->addOption($op3);

			include_once("./Services/Form/classes/class.ilNestedListInputGUI.php");
			$nl = new ilNestedListInputGUI("", "obj_id");
			$op3->addSubItem($nl);

			foreach ($pages as $p)
			{
				$nl->addListNode($p["id"], $p["title"], 0, false, false,
						ilUtil::getImagePath("icon_pg.svg"), $lng->txt("wiki_page"));
			}

		$this->form->addItem($radg);

		$this->form->addCommandButton("printViewOrder", $lng->txt("wiki_show_print_view"));
		$this->form->addCommandButton("pdfExportOrder", $lng->txt("wiki_show_pdf_export"));
		//$this->form->setOpenTag(false);
		//$this->form->setCloseTag(false);

		$this->form->setTitle($lng->txt("cont_print_selection"));
		$this->form->setFormAction($ilCtrl->getFormAction($this, "printViewOrder"));
	}

	public function printViewOrder()
	{
		$this->printViewOrderList();	
	}
	
	public function pdfExportOrder()
	{
		$this->printViewOrderList(true);		
	}
	
	protected function printViewOrderList($a_pdf_export = false)
	{
		global $ilTabs;
		
		$pg_ids = $all_pages = array();
		
		// coming from type selection
		if(!is_array($_POST["wordr"]))
		{
			switch(trim($_POST["sel_type"]))
			{
				case "wiki":
					include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
					$all_pages = ilWikiPage::getAllPages($this->getPageObject()->getWikiId());
					foreach ($all_pages as $p)
					{
						$pg_ids[] = $p["id"];
					}
					break;

				case "selection":
					if (is_array($_POST["obj_id"]))
					{
						$pg_ids = $_POST["obj_id"];
					}
					else
					{
						$pg_ids[] = $_GET["wpg_id"];
					}
					if(sizeof($pg_ids) > 1)
					{						
						break;
					}
					else
					{
						 $_GET["wpg_id"] = array_pop($pg_ids);
					}
					// fallthrough

				// no order needed for single page
				default:
				//case "page":				
					$this->ctrl->setParameterByClass("ilObjWikiGUI", "wpg_id", $_GET["wpg_id"]); 
					if($a_pdf_export)
					{
						$this->ctrl->redirectByClass("ilObjWikiGUI", "pdfExport");						
					}
					else
					{
						$this->ctrl->redirectByClass("ilObjWikiGUI", "printView");						
					}
					break;
			}
			
			if($a_pdf_export)
			{
				$this->ctrl->setParameter($this, "pexp", 1);
			}
		}
		// refresh sorting
		else
		{
			$a_pdf_export = (bool)$_GET["pexp"];
		
			asort($_POST["wordr"]);			
			$pg_ids = array_keys($_POST["wordr"]);						
		}
		
		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($this->lng->txt("back"),
			$this->ctrl->getLinkTarget($this, "preview"));
		
		if(!sizeof($all_pages))
		{
			include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
			$all_pages = ilWikiPage::getAllPages($this->getPageObject()->getWikiId());
		}
		
		include_once "Modules/Wiki/classes/class.ilWikiExportOrderTableGUI.php";
		$tbl = new ilWikiExportOrderTableGUI($this, "printViewOrderList", $a_pdf_export, $all_pages, $pg_ids);		
		$this->tpl->setContent($tbl->getHTML());		
	}	
	

	////
	//// Block/Unblock
	////

	/**
	 * Block
	 */
	function blockWikiPage()
	{
		global $ilAccess, $tpl, $ilCtrl, $lng;

		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"]))
		{
			$this->getPageObject()->setBlocked(true);
			$this->getPageObject()->update();

			ilUtil::sendSuccess($lng->txt("wiki_page_blocked"), true);
		}

		$ilCtrl->redirect($this, "preview");
	}

	/**
	 * Unblock
	 */
	function unblockWikiPage()
	{
		global $ilAccess, $tpl, $ilCtrl, $lng;

		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"]))
		{
			$this->getPageObject()->setBlocked(false);
			$this->getPageObject()->update();

			ilUtil::sendSuccess($lng->txt("wiki_page_unblocked"), true);
		}

		$ilCtrl->redirect($this, "preview");
	}
	

	////
	//// Rename
	////

	/**
	 * Rename wiki page form
	 */
	function renameWikiPage()
	{
		global $ilAccess, $tpl, $ilCtrl, $lng;

		if ($ilAccess->checkAccess("edit_content", "", $_GET["ref_id"]))
		{
			$this->initRenameForm();
			$tpl->setContent($this->form->getHTML());
		}
	}

	/**
	 * Init renaming form.
	 *
	 * @param        int        $a_mode        Edit Mode
	 */
	protected function initRenameForm()
	{
		global $lng, $ilCtrl;

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();

		// new name
		$ti = new ilTextInputGUI($lng->txt("wiki_new_page_name"), "new_page_name");
		$ti->setMaxLength(200);
		$ti->setSize(50);
		$ti->setValue($this->getPageObject()->getTitle());
		$ti->setRequired(true);
		$this->form->addItem($ti);

		$this->form->addCommandButton("renamePage", $lng->txt("wiki_rename"));
		$this->form->addCommandButton("preview", $lng->txt("cancel"));

		$this->form->setTitle($lng->txt("wiki_rename_page"));
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	}

	/**
	 * Rename page
	 */
	public function renamePage()
	{
		global $tpl, $lng, $ilCtrl, $ilAccess;

		$this->initRenameForm();
		if ($this->form->checkInput())
		{
			if ($ilAccess->checkAccess("edit_content", "", $_GET["ref_id"]))
			{
				$new_name = $this->form->getInput("new_page_name");
				
				$page_title = ilWikiUtil::makeDbTitle($new_name);
				$pg_id = ilWikiPage::_getPageIdForWikiTitle($this->getPageObject()->getWikiId(), $page_title);

				// we might get the same page id back here, if the page
				// name only differs in diacritics
				// see bug http://www.ilias.de/mantis/view.php?id=11226
				if ($pg_id > 0 && $pg_id != $this->getPageObject()->getId())
				{
					ilUtil::sendFailure($lng->txt("wiki_page_already_exists"));
				}
				else
				{
					$new_name = $this->getPageObject()->rename($new_name);
					$ilCtrl->setParameterByClass("ilobjwikigui", "page", ilWikiUtil::makeUrlTitle($new_name));
					ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
					$ilCtrl->redirect($this, "preview");
				}
			}
		}

		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHtml());
	}
	
	//// 
	/// Rating
	////
	
	function activateWikiPageRating()
	{
		global $lng, $ilCtrl;
		
		$this->getPageObject()->setRating(true);
		$this->getPageObject()->update();
		
		ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
		$ilCtrl->redirect($this, "preview");
	}
	
	function deactivateWikiPageRating()
	{
		global $lng, $ilCtrl;
		
		$this->getPageObject()->setRating(false);
		$this->getPageObject()->update();
		
		ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
		$ilCtrl->redirect($this, "preview");
	}
	
	
	function observeNoteAction($a_wiki_id, $a_page_id, $a_type, $a_action, $a_note_id)
	{			
		// #10040 - get note text
		include_once "Services/Notes/classes/class.ilNote.php";
		$note = new ilNote($a_note_id);
		$note = $note->getText();
		
		include_once "./Services/Notification/classes/class.ilNotification.php";
		ilWikiUtil::sendNotification("comment", ilNotification::TYPE_WIKI_PAGE, $this->getWikiRefId(), $a_page_id, $note);
	}
		
	public function updateStatsRating($a_wiki_id, $a_wiki_type, $a_page_id, $a_page_type)
	{
		include_once "./Modules/Wiki/classes/class.ilWikiStat.php";
		ilWikiStat::handleEvent(ilWikiStat::EVENT_PAGE_RATING, $this->getPageObject());	
	}
	
	
	//
	// advanced meta data
	// 
	
	protected function initAdvancedMetaDataForm()
	{
		global $ilCtrl, $lng;
			
		$page = $this->getWikiPage();
		
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this, "updateAdvancedMetaData"));
		
		// :TODO:
		$form->setTitle($lng->txt("wiki_advmd_block_title").": ".$page->getTitle());
		
		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordGUI.php');
		$this->record_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_EDITOR,'wiki',$page->getWikiId(),'wpg',$page->getId());
		$this->record_gui->setPropertyForm($form);
		$this->record_gui->setSelectedOnly(true); // #14912
		$this->record_gui->parse();
		
		$form->addCommandButton("updateAdvancedMetaData", $lng->txt("save"));
		$form->addCommandButton("preview", $lng->txt("cancel"));
		
		return $form;
	}
		
	function editAdvancedMetaData(ilPropertyFormGUI $a_form = null)
	{
		global $ilTabs, $lng, $ilCtrl, $tpl;
		
		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt("back"),
			$ilCtrl->getLinkTarget($this, "preview"));
		
		if(!$a_form)
		{
			$a_form = $this->initAdvancedMetaDataForm();
		}
		
		$tpl->setContent($a_form->getHTML());
	}
	
	function updateAdvancedMetaData()
	{		
		global $ilCtrl, $lng;
		
		$form = $this->initAdvancedMetaDataForm();
	
		// needed for proper advanced MD validation	 		
		$form->checkInput();			
		if(!$this->record_gui->importEditFormPostValues())
		{	
			$this->editInfoObject($form);
			return false;
		}	
				
		if($this->record_gui->writeEditForm())
		{
			ilUtil::sendSuccess($lng->txt("settings_saved"), true);
		}		
		$ilCtrl->redirect($this, "preview");
	}
	
	function hideAdvancedMetaData()
	{
		global $ilCtrl, $lng;
		
		$this->getPageObject()->hideAdvancedMetadata(true);
		$this->getPageObject()->update();
			
		ilUtil::sendSuccess($lng->txt("settings_saved"), true);	
		$ilCtrl->redirect($this, "preview");
	}
	
	function unhideAdvancedMetaData()
	{
		global $ilCtrl, $lng;
		
		$this->getPageObject()->hideAdvancedMetadata(false);
		$this->getPageObject()->update();
			
		ilUtil::sendSuccess($lng->txt("settings_saved"), true);
		$ilCtrl->redirect($this, "preview");
	}

	/**
	 * Edit
	 *
	 * @param
	 * @return
	 */
	function edit()
	{
		global $tpl, $lng;

		$tpl->addJavascript("./Modules/Wiki/js/WikiEdit.js");
		$tpl->addOnLoadCode("il.Wiki.Edit.txt.page_exists = '".$lng->txt("wiki_page_exists")."';");
		$tpl->addOnLoadCode("il.Wiki.Edit.txt.new_page = '".$lng->txt("wiki_new_page")."';");

		return parent::edit();
	}

	/**
	 * Returns form to insert a wiki link per ajax
	 */
	function insertWikiLink()
	{
		global $lng, $ilCtrl;

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->addCommandButton("addWikiLink", $lng->txt("wiki_add_link"));
		$form->addCommandButton("searchWikiLink", $lng->txt("search"));

		// Target page
		$tp = new ilTextInputGUI($this->lng->txt("wiki_target_page"), "target_page");
		$tp->setSize(18);
		$tp->setInfo("...");
		$tp->setDataSource($ilCtrl->getLinkTarget($this, "insertWikiLinkAC", "", true));
		$form->addItem($tp);

		// Link text
		$lt = new ilTextInputGUI($this->lng->txt("wiki_link_text"), "link_text");
		$lt->setSize(18);
		$form->addItem($lt);

		$form->setTitle($lng->txt("wiki_link"));

		echo $form->getHTML();
		exit;
	}

	/**
	 * Auto complete for insert wiki link
	 */
	function insertWikiLinkAC()
	{
		$result = array();

		$term = $_GET["term"];

		// if page exists, make it first entry
		if (ilWikiPage::_wikiPageExists($this->getPageObject()->getParentId(), $term))
		{
			$entry = new stdClass();
			$entry->value = $term;
			$entry->label = $term;
			$result[] = $entry;
		}

		$res = ilWikiPage::getPagesForSearch($this->getPageObject()->getParentId(), $term);

		$cnt = 0;
		foreach ($res as $r)
		{
			if ($result[0]->value == $r)
			{
				continue;
			}
			if ($cnt++ > 19)
			{
				continue;
			}
			$entry = new stdClass();
			$entry->value = $r;
			$entry->label = $r;
			$result[] = $entry;
		}

		include_once './Services/JSON/classes/class.ilJsonUtil.php';
		echo ilJsonUtil::encode($result);
		exit;
	}

	/**
	 * Search wiki link list
	 */
	function searchWikiLinkAC()
	{
		global $lng;

		$lng->loadLanguageModule("wiki");

		$tpl = new ilTemplate("tpl.wiki_ac_search_result.html", true, true, "Modules/Wiki");
		$term = trim($_GET["term"]);

		$pages = ilObjWiki::_performSearch($this->getPageObject()->getParentId(), $term);
		$found = array();
		foreach ($pages as $page)
		{
			$found[] = array("page_id" => $page[""], "title" => ilWikiPage::lookupTitle($page));
		}

		// sort if all pages are listed
		if ($term == "")
		{
			$found = ilUtil::sortArray($found, "title", "asc");
		}

		foreach ($found as $f)
		{
			$tpl->setCurrentBlock("item");
			$tpl->setVariable("WIKI_TITLE", $f["title"]);
			$tpl->parseCurrentBlock();
		}

		if (count($pages) == 0)
		{
			$tpl->setVariable("INFOTEXT", str_replace("$1", $term, $lng->txt("wiki_no_page_found")));
		}
		else if ($term == '')
		{
			$tpl->setVariable("INFOTEXT", $lng->txt("wiki_no_search_term"), $term);
		}
		else
		{
			$tpl->setVariable("INFOTEXT", str_replace("$1", $term, $lng->txt("wiki_pages_found")));
		}

		$tpl->setVariable("TXT_BACK", $lng->txt("back"));
		echo $tpl->get();
		exit;
	}
} 

?>