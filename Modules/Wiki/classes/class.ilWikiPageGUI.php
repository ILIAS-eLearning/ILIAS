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
* @ilCtrl_Calls ilWikiPageGUI: ilRatingGUI, ilPublicUserProfileGUI, ilPageObjectGUI, ilNoteGUI
* @ilCtrl_Calls ilWikiPageGUI: ilCommonActionDispatcherGUI
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

		$this->setPageToc(ilObjWiki::_lookupPageToc(
			ilObject::_lookupObjId($a_wiki_ref_id)));
		
		parent::__construct("wpg", $a_id, $a_old_nr);
		
		// content style
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		
		$tpl->setCurrentBlock("SyntaxStyle");
		$tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
			ilObjStyleSheet::getSyntaxStylePath());
		$tpl->parseCurrentBlock();
		
		$this->setEnabledMaps(true);
		$this->setPreventHTMLUnmasking(true);
		$this->setEnabledInternalLinks(true);
		$this->setEnableAnchors(true);
		$this->setEnabledWikiLinks(true);
		$this->setEnabledPCTabs(true);

		$cfg = new ilPageConfig();
		$cfg->setIntLinkFilterWhiteList(true);
		$cfg->addIntLinkFilter("RepositoryItem");
		$this->setPageConfig($cfg);
		$this->setIntLinkHelpDefault("RepositoryItem", 0);

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

	function initPageObject($a_parent_type, $a_id, $a_old_nr)
	{
		$page = new ilWikiPage($a_id, $a_old_nr);
		$page->setWikiRefId($this->getWikiRefId());
		$this->setPageObject($page);
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
		global $ilCtrl, $ilTabs, $ilUser;
		
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
				include_once("./Services/Rating/classes/class.ilRatingGUI.php");
				$rating_gui = new ilRatingGUI();
				$rating_gui->setObject($this->getPageObject()->getParentId(), "wiki",
					$this->getPageObject()->getId(), "wpg");
				$this->ctrl->forwardCommand($rating_gui);
				$ilCtrl->redirect($this, "preview");
				break;
				
			case "ilpageobjectgui":
				$page_gui = new ilPageObjectGUI("wpg",
					$this->getPageObject()->getId(), $this->getPageObject()->old_nr);
				$page_gui->setPresentationTitle($this->getWikiPage()->getTitle());
				return $ilCtrl->forwardCommand($page_gui);
				
			case "ilcommonactiondispatchergui":
				include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
				$gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
				$gui->enableCommentsSettings(false);
				$this->ctrl->forwardCommand($gui);
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
		
		include_once "Services/Object/classes/class.ilCommonActionDispatcherGUI.php";
		$dispatcher = new ilCommonActionDispatcherGUI(ilCommonActionDispatcherGUI::TYPE_REPOSITORY, 
			$ilAccess, "wiki", $_GET["ref_id"], $this->getPageObject()->getParentId());
		$dispatcher->setSubObject("wpg", $this->getPageObject()->getId());

		include_once "Services/Object/classes/class.ilObjectListGUI.php";
		ilObjectListGUI::prepareJSLinks($this->ctrl->getLinkTarget($this, "redrawHeaderAction", "", true), 			
			$this->ctrl->getLinkTargetByClass(array("ilcommonactiondispatchergui", "ilnotegui"), "", "", true, false), 
			$this->ctrl->getLinkTargetByClass(array("ilcommonactiondispatchergui", "iltagginggui"), "", "", true, false));

		$lg = $dispatcher->initHeaderAction();
		$lg->enableNotes(true);
		$lg->enableComments(ilObjWiki::_lookupPublicNotes($this->getPageObject()->getParentId()), false);

		// notification
		if ($ilUser->getId() != ANONYMOUS_USER_ID)
		{
			include_once "./Services/Notification/classes/class.ilNotification.php";
			if(ilNotification::hasNotification(ilNotification::TYPE_WIKI, $ilUser->getId(), $this->getPageObject()->getParentId()))
			{
				$this->ctrl->setParameter($this, "ntf", 1);
				$lg->addCustomCommand($this->ctrl->getLinkTarget($this), "wiki_notification_deactivate_wiki");

				$lg->addHeaderIcon("not_icon",
					ilUtil::getImagePath("notification_on.png"),
					$this->lng->txt("wiki_notification_activated"));
			}
			else
			{
				$this->ctrl->setParameter($this, "ntf", 2);
				$lg->addCustomCommand($this->ctrl->getLinkTarget($this), "wiki_notification_activate_wiki");
				
				if(ilNotification::hasNotification(ilNotification::TYPE_WIKI_PAGE, $ilUser->getId(), $this->getPageObject()->getId()))
				{
					$this->ctrl->setParameter($this, "ntf", 3);
					$lg->addCustomCommand($this->ctrl->getLinkTarget($this), "wiki_notification_deactivate_page");

					$lg->addHeaderIcon("not_icon",
						ilUtil::getImagePath("notification_on.png"),
						$this->lng->txt("wiki_page_notification_activated"));					
				}
				else
				{
					$this->ctrl->setParameter($this, "ntf", 4);
					$lg->addCustomCommand($this->ctrl->getLinkTarget($this), "wiki_notification_activate_page");
					
					$lg->addHeaderIcon("not_icon",
						ilUtil::getImagePath("notification_off.png"),
						$this->lng->txt("wiki_notification_deactivated"));
				}
			}
			$this->ctrl->setParameter($this, "ntf", "");
		}		
		
		// rating
		$wiki_id = $this->getPageObject()->getParentId();
		if (ilObjWiki::_lookupRating($wiki_id)
			&& $this->getPageObject()->getRating()
			&& $this->getPageObject()->old_nr == 0)
		{
			include_once("./Services/Rating/classes/class.ilRatingGUI.php");
			$rating_gui = new ilRatingGUI();
			$rating_gui->setObject($this->getPageObject()->getParentId(), "wiki",
				$this->getPageObject()->getId(), "wpg");
			$rating_gui->setYourRatingText($this->lng->txt("wiki_rate_page"));
			$rating_gui->enableCategories(ilObjWiki::_lookupRatingCategories($wiki_id));
			$lg->addHeaderIconHTML("rating", $this->ctrl->getHtml($rating_gui));
		}
		
		if(!$a_redraw)
		{
			$this->tpl->setHeaderActionMenu($lg->getHeaderAction());		
		}
		else
		{
			return $lg->getHeaderAction();
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
			$wtpl->setVariable("NOTES", $this->getNotesHTML($this->getPageObject(),
				true, ilObjWiki::_lookupPublicNotes($this->getPageObject()->getParentId()),
				$ilAccess->checkAccess("write", "", $_GET["ref_id"]), $callback));
		}
		
		// permanent link
		$append = ($_GET["page"] != "")
			? "_".ilWikiUtil::makeUrlTitle($_GET["page"])
			: "";
		include_once("./Services/PermanentLink/classes/class.ilPermanentLinkGUI.php");
		$perma_link = new ilPermanentLinkGUI("wiki", $_GET["ref_id"], $append);
		$wtpl->setVariable("PERMA_LINK", $perma_link->getHTML());

		// page content
		$wtpl->setVariable("PAGE", parent::preview());

		$tpl->setLoginTargetPar("wiki_".$_GET["ref_id"].$append);

		// last edited info
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
		$this->setPresentationTitle($this->getWikiPage()->getTitle());
		$this->increaseViewCount();			
		$output = parent::showPage();
		
		return $output;
	}
	
	protected function increaseViewCount()
	{
		global $ilUser;
		
		$this->getWikiPage()->increaseViewCnt();
		
		// enable object statistics
		require_once('Services/Tracking/classes/class.ilChangeEvent.php');
		ilChangeEvent::_recordReadEvent("wiki", $this->getWikiPage()->getWikiRefId(),
			$this->getWikiPage()->getWikiId(), $ilUser->getId());			
	}

	/**
	* Finalizing output processing.
	*/
	function postOutputProcessing($a_output)
	{
//echo htmlentities($a_output);
		include_once("./Modules/Wiki/classes/class.ilWikiUtil.php");
		$output = ilWikiUtil::replaceInternalLinks($a_output,
			$this->getWikiPage()->getWikiId(),
			($this->getOutputMode() == "offline"));
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
		global $ilTabs, $ilCtrl;

		parent::getTabs($a_activate);
		
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

		$ilToolbar->setFormAction($ilCtrl->getFormActionByClass("ilobjwikigui", "printView"),
			false, "print_view");
		$ilToolbar->addFormButton($lng->txt("cont_show_print_view"), "printView");
		$ilToolbar->setCloseFormTag(false);

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
						ilUtil::getImagePath("icon_pg_s.png"), $lng->txt("wiki_page"));
			}

		$this->form->addItem($radg);

		$this->form->addCommandButton("printView", $lng->txt("cont_show_print_view"));
		//$this->form->setOpenTag(false);
		$this->form->setCloseTag(false);

		$this->form->setTitle($lng->txt("cont_print_selection"));
		//$this->form->setFormAction($ilCtrl->getFormAction($this));
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

		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"]))
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
			if ($ilAccess->checkAccess("write", "", $_GET["ref_id"]))
			{
				$new_name = $this->form->getInput("new_page_name");

				if (ilWikiPage::exists($this->getPageObject()->getWikiId(), $new_name))
				{
					ilUtil::sendFailure($lng->txt("wiki_page_already_exists"));
				}
				else
				{
					$this->getPageObject()->rename($new_name);
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
} 
?>
