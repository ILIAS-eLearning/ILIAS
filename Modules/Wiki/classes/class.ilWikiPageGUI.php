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
* @ilCtrl_Calls ilWikiPageGUI: ilObjectMetaDataGUI, ilPropertyFormGUI
*
* @ingroup ModulesWiki
*/
class ilWikiPageGUI extends ilPageObjectGUI
{
    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilSetting
     */
    protected $settings;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilObjWiki
     */
    protected $wiki;

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
    * Constructor
    */
    public function __construct($a_id = 0, $a_old_nr = 0, $a_wiki_ref_id = 0)
    {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->help = $DIC["ilHelp"];
        $this->ctrl = $DIC->ctrl();
        $this->tabs = $DIC->tabs();
        $this->user = $DIC->user();
        $this->access = $DIC->access();
        $this->lng = $DIC->language();
        $this->settings = $DIC->settings();
        $this->toolbar = $DIC->toolbar();
        $tpl = $DIC["tpl"];
        $this->ui = $DIC->ui();

        // needed for notifications
        $this->setWikiRefId($a_wiki_ref_id);
        
        parent::__construct("wpg", $a_id, $a_old_nr);
        $this->getPageObject()->setWikiRefId($this->getWikiRefId());
        
        // content style
        include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
        
        $tpl->setCurrentBlock("SyntaxStyle");
        $tpl->setVariable(
            "LOCATION_SYNTAX_STYLESHEET",
            ilObjStyleSheet::getSyntaxStylePath()
        );
        $tpl->parseCurrentBlock();
    }
    
    /**
     * Set screen id component
     *
     * @param
     * @return
     */
    public function setScreenIdComponent()
    {
        $ilHelp = $this->help;
        
        $ilHelp->setScreenIdComponent("copgwpg");
    }

    public function setWikiRefId($a_ref_id)
    {
        $this->wiki_ref_id = $a_ref_id;
    }

    public function getWikiRefId()
    {
        return $this->wiki_ref_id;
    }

    /**
     * Set wiki
     *
     * @param ilObjWiki $a_val wiki
     */
    public function setWiki($a_val)
    {
        $this->wiki = $a_val;
    }
    
    /**
     * Get wiki
     *
     * @return ilObjWiki wiki
     */
    public function getWiki()
    {
        return $this->wiki;
    }
    
    /**
    * execute command
    */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;
        $ilUser = $this->user;
        $ilAccess = $this->access;
        $lng = $this->lng;
        $tpl = $this->tpl;

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $head_title = ilObject::_lookupTitle(ilObject::_lookupObjId((int) $_GET["ref_id"])) . ": " . $this->getWikiPage()->getTitle();
        $tpl->setHeaderPageTitle($head_title);
        // see #13804
        if ($_GET["page"] != "") {
            $tpl->setPermanentLink("wiki", "", "wpage_" . $this->getPageObject()->getId() . "_" . $_GET["ref_id"], "", $head_title);
        } else {
            $tpl->setPermanentLink("wiki", $_GET["ref_id"]);
        }


        switch ($next_class) {
            case "ilnotegui":
                $this->getTabs();
                $ilTabs->setTabActive("pg");
                return $this->preview();
                break;
                        
            case "ilratinggui":
                // for rating side block
                include_once("./Services/Rating/classes/class.ilRatingGUI.php");
                $rating_gui = new ilRatingGUI();
                $rating_gui->setObject(
                    $this->getPageObject()->getParentId(),
                    "wiki",
                    $this->getPageObject()->getId(),
                    "wpg"
                );
                $rating_gui->setUpdateCallback(array($this, "updateStatsRating"));
                $this->ctrl->forwardCommand($rating_gui);
                $ilCtrl->redirect($this, "preview");
                break;

            case "ilcommonactiondispatchergui":
                include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $gui->enableCommentsSettings(false);
                $gui->setRatingCallback($this, "preview");
                $this->ctrl->forwardCommand($gui);
                break;
            
            case "ilwikistatgui":
                if ($ilAccess->checkAccess("statistics_read", "", $this->wiki_ref_id)) {
                    $this->tabs_gui->clearTargets(); // see ilObjWikiGUI::getTabs()
                    $this->getTabs("statistics");

                    include_once "Modules/Wiki/classes/class.ilWikiStatGUI.php";
                    $gui = new ilWikiStatGUI(
                        $this->getPageObject()->getParentId(),
                        $this->getPageObject()->getId()
                    );
                    $this->ctrl->forwardCommand($gui);
                }
                break;
            case 'ilobjectmetadatagui':
                
                if (!$ilAccess->checkAccess("write", "", $this->wiki_ref_id)) {
                    ilUtil::sendFailure($lng->txt("permission_denied"), true);
                    $ilCtrl->redirect($this, "preview");
                }
                return parent::executeCommand();
                break;

            case "ilpropertyformgui":
                // only case is currently adv metadata internal link in info settings, see #24497
                $form = $this->initAdvancedMetaDataForm();
                $ilCtrl->forwardCommand($form);
                break;

            default:

                if (strtolower($ilCtrl->getNextClass()) == "ilpageeditorgui") {
                    self::initEditingJS($this->tpl);
                }

                if ($_GET["ntf"]) {
                    include_once "./Services/Notification/classes/class.ilNotification.php";
                    switch ($_GET["ntf"]) {
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
    public function setWikiPage($a_wikipage)
    {
        $this->setPageObject($a_wikipage);
    }

    /**
    * Get Wiki Page Object.
    *
    * @return	object	Wiki Page Object
    */
    public function getWikiPage()
    {
        return $this->getPageObject();
    }

    /**
    * Get wiki page gui for id and title
    */
    public static function getGUIForTitle($a_wiki_id, $a_title, $a_old_nr = 0, $a_wiki_ref_id = 0)
    {
        global $DIC;

        $ilDB = $DIC->database();

        include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
        $id = ilWikiPage::getPageIdForTitle($a_wiki_id, $a_title);
        $page_gui = new ilWikiPageGUI($id, $a_old_nr, $a_wiki_ref_id);
        
        return $page_gui;
    }
    
    public function setSideBlock()
    {
        ilObjWikiGUI::renderSideBlock(
            $this->getWikiPage()->getId(),
            $this->wiki_ref_id,
            $this->getWikiPage()
        );
    }
    
    public function addHeaderAction($a_redraw = false)
    {
        $ilUser = $this->user;
        $ilAccess = $this->access;
        
        $wiki_id = $this->getPageObject()->getParentId();
        $page_id = $this->getPageObject()->getId();
        
        include_once "Services/Object/classes/class.ilCommonActionDispatcherGUI.php";
        $dispatcher = new ilCommonActionDispatcherGUI(
            ilCommonActionDispatcherGUI::TYPE_REPOSITORY,
            $ilAccess,
            "wiki",
            $_GET["ref_id"],
            $wiki_id
        );
        $dispatcher->setSubObject("wpg", $page_id);

        include_once "Services/Object/classes/class.ilObjectListGUI.php";
        ilObjectListGUI::prepareJSLinks(
            $this->ctrl->getLinkTarget($this, "redrawHeaderAction", "", true),
            $this->ctrl->getLinkTargetByClass(array("ilcommonactiondispatchergui", "ilnotegui"), "", "", true, false),
            $this->ctrl->getLinkTargetByClass(array("ilcommonactiondispatchergui", "iltagginggui"), "", "", true, false)
        );

        $lg = $dispatcher->initHeaderAction();
        $lg->enableNotes(true);
        $lg->enableComments(ilObjWiki::_lookupPublicNotes($wiki_id), false);
        
        // rating
        if (ilObjWiki::_lookupRatingOverall($wiki_id)) {
            $lg->enableRating(
                true,
                $this->lng->txt("wiki_rate_overall"),
                false,
                // so ilCtrl does not use the shortcut via ilWikiGUI
                array("ilcommonactiondispatchergui", "ilratinggui")
            );
        }

        // notification
        if ($ilUser->getId() != ANONYMOUS_USER_ID) {
            include_once "./Services/Notification/classes/class.ilNotification.php";
            if (ilNotification::hasNotification(ilNotification::TYPE_WIKI, $ilUser->getId(), $wiki_id)) {
                $this->ctrl->setParameter($this, "ntf", 1);
                if (ilNotification::hasOptOut($wiki_id)) {
                    $lg->addCustomCommand($this->ctrl->getLinkTarget($this), "wiki_notification_deactivate_wiki");
                }

                $lg->addHeaderIcon(
                    "not_icon",
                    ilUtil::getImagePath("notification_on.svg"),
                    $this->lng->txt("wiki_notification_activated")
                );
            } else {
                $this->ctrl->setParameter($this, "ntf", 2);
                $lg->addCustomCommand($this->ctrl->getLinkTarget($this), "wiki_notification_activate_wiki");
                
                if (ilNotification::hasNotification(ilNotification::TYPE_WIKI_PAGE, $ilUser->getId(), $page_id)) {
                    $this->ctrl->setParameter($this, "ntf", 3);
                    $lg->addCustomCommand($this->ctrl->getLinkTarget($this), "wiki_notification_deactivate_page");

                    $lg->addHeaderIcon(
                        "not_icon",
                        ilUtil::getImagePath("notification_on.svg"),
                        $this->lng->txt("wiki_page_notification_activated")
                    );
                } else {
                    $this->ctrl->setParameter($this, "ntf", 4);
                    $lg->addCustomCommand($this->ctrl->getLinkTarget($this), "wiki_notification_activate_page");
                    
                    $lg->addHeaderIcon(
                        "not_icon",
                        ilUtil::getImagePath("notification_off.svg"),
                        $this->lng->txt("wiki_notification_deactivated")
                    );
                }
            }
            $this->ctrl->setParameter($this, "ntf", "");
        }
        
        if (!$a_redraw) {
            $this->tpl->setHeaderActionMenu($lg->getHeaderAction());
        } else {
            // we need to add onload code manually (rating, comments, etc.)
            return $lg->getHeaderAction() .
                $this->tpl->getOnLoadCodeForAsynch();
        }
    }
        
    public function redrawHeaderAction()
    {
        echo $this->addHeaderAction(true);
        exit;
    }

    /**
    * View wiki page.
    */
    public function preview()
    {
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;
        $lng = $this->lng;
        $tpl = $this->tpl;
        $ilUser = $this->user;
        $ilSetting = $this->settings;
        $ui = $this->ui;


        // block/unblock
        if ($this->getPageObject()->getBlocked()) {
            ilUtil::sendInfo($lng->txt("wiki_page_status_blocked"));
        }


        $this->increaseViewCount();
                
        $this->addHeaderAction();
        
        // content
        if ($ilCtrl->getNextClass() != "ilnotegui") {
            $this->setSideBlock();
        }

        $wtpl = new ilTemplate(
            "tpl.wiki_page_view_main_column.html",
            true,
            true,
            "Modules/Wiki"
        );
        
        $callback = array($this, "observeNoteAction");
        
        // notes
        if (!$ilSetting->get("disable_comments") &&
            ilObjWiki::_lookupPublicNotes($this->getPageObject()->getParentId())) {
            $may_delete = ($ilSetting->get("comments_del_tutor", 1) &&
                $ilAccess->checkAccess("write", "", $_GET["ref_id"]));
            $wtpl->setVariable("NOTES", $this->getNotesHTML(
                $this->getPageObject(),
                true,
                ilObjWiki::_lookupPublicNotes($this->getPageObject()->getParentId()),
                $may_delete,
                $callback
            ));
        }


        // page content
        $this->setOutputMode(ilPageObjectGUI::PRESENTATION);
        $this->showEditToolbar();
        $this->setRenderPageContainer(true);
        $wtpl->setVariable("PAGE", $this->showPage());

        $tpl->setLoginTargetPar("wiki_" . $_GET["ref_id"] . $append);

        // last edited info
        include_once("./Services/User/classes/class.ilUserUtil.php");
        $wtpl->setVariable(
            "LAST_EDITED_INFO",
            $lng->txt("wiki_last_edited") . ": " .
            ilDatePresentation::formatDate(
                new ilDateTime($this->getPageObject()->getLastChange(), IL_CAL_DATETIME)
            ) . ", " .
            ilUserUtil::getNamePresentation(
                $this->getPageObject()->getLastChangeUser(),
                false,
                true,
                $ilCtrl->getLinkTarget($this, "preview")
            )
        );

        $tpl->setLoginTargetPar("wiki_" . $_GET["ref_id"] . $append);
        
        //highlighting
        if ($_GET["srcstring"] != "") {
            include_once './Services/Search/classes/class.ilUserSearchCache.php';
            $cache = ilUserSearchCache::_getInstance($ilUser->getId());
            $cache->switchSearchType(ilUserSearchCache::LAST_QUERY);
            $search_string = $cache->getQuery();
            
            // advanced search?
            if (is_array($search_string)) {
                $search_string = $search_string["lom_content"];
            }

            include_once("./Services/UIComponent/TextHighlighter/classes/class.ilTextHighlighterGUI.php");
            include_once("./Services/Search/classes/class.ilQueryParser.php");
            $p = new ilQueryParser($search_string);
            $p->parse();

            $words = $p->getQuotedWords();
            if (is_array($words)) {
                foreach ($words as $w) {
                    ilTextHighlighterGUI::highlight("ilCOPageContent", $w, $tpl);
                }
            }
            $this->fill_on_load_code = true;
        }
        
        return $message . $wtpl->get();
    }
    
    public function showPage()
    {
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        
        // content style
        /*		include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
                $tpl->setCurrentBlock("ContentStyle");
                $tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
                    ilObjStyleSheet::getContentStylePath(0));
                $tpl->parseCurrentBlock();
        */
        $this->setTemplateOutput(false);
        
        if (!$this->getAbstractOnly()) {
            $this->setPresentationTitle($this->getWikiPage()->getTitle());
            
            // wiki stats clean up
            // $this->increaseViewCount();
        }
    
        return parent::showPage();
    }
    
    protected function increaseViewCount()
    {
        $ilUser = $this->user;
        
        $this->getWikiPage()->increaseViewCnt();
        
        // enable object statistics
        require_once('Services/Tracking/classes/class.ilChangeEvent.php');
        ilChangeEvent::_recordReadEvent(
            "wiki",
            $this->getWikiPage()->getWikiRefId(),
            $this->getWikiPage()->getWikiId(),
            $ilUser->getId()
        );
        
        include_once "./Modules/Wiki/classes/class.ilWikiStat.php";
        ilWikiStat::handleEvent(ilWikiStat::EVENT_PAGE_READ, $this->getWikiPage());
    }

    /**
    * Finalizing output processing.
    */
    public function postOutputProcessing($a_output)
    {
        $ilCtrl = $this->ctrl;

        //echo htmlentities($a_output);
        include_once("./Modules/Wiki/classes/class.ilWikiUtil.php");

        $ilCtrl->setParameterByClass("ilobjwikigui", "from_page", ilWikiUtil::makeUrlTitle($_GET["page"]));
        if ($this->getEnabledHref() && $this->getOutputMode() !== self::EDIT) {
            $output = ilWikiUtil::replaceInternalLinks(
                $a_output,
                $this->getWikiPage()->getWikiId(),
                ($this->getOutputMode() == "offline")
            );
        } else {
            $output = $a_output;
        }
        $ilCtrl->setParameterByClass("ilobjwikigui", "from_page", $_GET["from_page"]);


        // metadata in print view
        if ($this->getOutputMode() == "print" && $this->wiki instanceof ilObjWiki) {
            include_once("./Services/Object/classes/class.ilObjectMetaDataGUI.php");
            $mdgui = new ilObjectMetaDataGUI($this->wiki, "wpg", $this->getId());
            $md = $mdgui->getKeyValueList();
            if ($md != "") {
                $output = str_replace("<!--COPage-PageTop-->", "<p>" . $md . "</p>", $output);
            }
        }


        return $output;
    }
    
    /**
    * All links to a specific page
    */
    public function whatLinksHere()
    {
        $tpl = $this->tpl;
        
        include_once("./Modules/Wiki/classes/class.ilWikiPagesTableGUI.php");
        
        $this->setSideBlock($_GET["wpg_id"]);
        $table_gui = new ilWikiPagesTableGUI(
            $this,
            "whatLinksHere",
            $this->getWikiPage()->getWikiId(),
            IL_WIKI_WHAT_LINKS_HERE,
            $_GET["wpg_id"]
        );
            
        $tpl->setContent($table_gui->getHTML());
    }

    public function getTabs($a_activate = "")
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;

        parent::getTabs($a_activate);
        
        if ($ilAccess->checkAccess("statistics_read", "", $_GET["ref_id"])) {
            $ilTabs->addTarget(
                "statistics",
                $this->ctrl->getLinkTargetByClass(
                    array("ilwikipagegui", "ilwikistatgui"),
                    "initial"
                ),
                "",
                "ilwikistatgui"
            );
        }
        
        $ilCtrl->setParameterByClass(
            "ilobjwikigui",
            "wpg_id",
            ilWikiPage::getPageIdForTitle(
                $this->getPageObject()->getParentId(),
                ilWikiUtil::makeDbTitle($_GET["page"])
            )
        );
        $ilCtrl->setParameterByClass("ilobjwikigui", "page", ilWikiUtil::makeUrlTitle($_GET["page"]));

        $ilTabs->addTarget(
            "wiki_what_links_here",
            $this->ctrl->getLinkTargetByClass(
                "ilwikipagegui",
                "whatLinksHere"
            ),
            "whatLinksHere"
        );
        //$ilTabs->addTarget("wiki_print_view",
        //	$this->ctrl->getLinkTargetByClass("ilobjwikigui",
        //	"printViewSelection"), "printViewSelection");
        $ilTabs->addTarget(
            "wiki_print_view",
            $this->ctrl->getLinkTargetByClass(
                "ilwikipagegui",
                "printViewSelection"
            ),
            "printViewSelection"
        );
    }

    /**
    * Delete wiki page confirmation screen.
    */
    public function deleteWikiPageConfirmationScreen()
    {
        $ilAccess = $this->access;
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        include_once("./Modules/Wiki/classes/class.ilWikiPerm.php");
        if (ilWikiPerm::check("delete_wiki_pages", $_GET["ref_id"])) {
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $confirmation_gui = new ilConfirmationGUI();
            $confirmation_gui->setFormAction($ilCtrl->getFormAction($this));
            $confirmation_gui->setHeaderText($lng->txt("wiki_page_deletion_confirmation"));
            $confirmation_gui->setCancel($lng->txt("cancel"), "cancelWikiPageDeletion");
            $confirmation_gui->setConfirm($lng->txt("delete"), "confirmWikiPageDeletion");
            
            $dtpl = new ilTemplate(
                "tpl.wiki_page_deletion_confirmation.html",
                true,
                true,
                "Modules/Wiki"
            );
                
            $dtpl->setVariable("PAGE_TITLE", $this->getWikiPage()->getTitle());
            
            // other pages that link to this page
            $dtpl->setVariable("TXT_OTHER_PAGES", $lng->txt("wiki_other_pages_linking"));
            $pages = ilWikiPage::getLinksToPage(
                $this->getWikiPage()->getWikiId(),
                $this->getWikiPage()->getId()
            );
            if (count($pages) > 0) {
                foreach ($pages as $page) {
                    $dtpl->setCurrentBlock("lpage");
                    $dtpl->setVariable("TXT_LINKING_PAGE", $page["title"]);
                    $dtpl->parseCurrentBlock();
                }
            } else {
                $dtpl->setCurrentBlock("lpage");
                $dtpl->setVariable("TXT_LINKING_PAGE", "-");
                $dtpl->parseCurrentBlock();
            }
            
            // contributors
            $dtpl->setVariable("TXT_CONTRIBUTORS", $lng->txt("wiki_contributors"));
            $contributors = ilWikiPage::getWikiPageContributors($this->getWikiPage()->getId());
            foreach ($contributors as $contributor) {
                $dtpl->setCurrentBlock("contributor");
                $dtpl->setVariable(
                    "TXT_CONTRIBUTOR",
                    $contributor["lastname"] . ", " . $contributor["firstname"]
                );
                $dtpl->parseCurrentBlock();
            }
            
            // notes/comments
            include_once("./Services/Notes/classes/class.ilNote.php");
            $cnt_note_users = ilNote::getUserCount(
                $this->getPageObject()->getParentId(),
                $this->getPageObject()->getId(),
                "wpg"
            );
            $dtpl->setVariable(
                "TXT_NUMBER_USERS_NOTES_OR_COMMENTS",
                $lng->txt("wiki_number_users_notes_or_comments")
            );
            $dtpl->setVariable("TXT_NR_NOTES_COMMENTS", $cnt_note_users);
            
            $confirmation_gui->addItem("", "", $dtpl->get());
            
            $tpl->setContent($confirmation_gui->getHTML());
        }
    }

    /**
    * Cancel wiki page deletion
    */
    public function cancelWikiPageDeletion()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        $ilCtrl->redirect($this, "preview");
    }
    
    /**
    * Delete the wiki page
    */
    public function confirmWikiPageDeletion()
    {
        $ilAccess = $this->access;
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        include_once("./Modules/Wiki/classes/class.ilWikiPerm.php");
        if (ilWikiPerm::check("delete_wiki_pages", $_GET["ref_id"])) {
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
    public function printViewSelection()
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        $ilToolbar = $this->toolbar;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;

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
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $pages = ilWikiPage::getAllWikiPages(ilObject::_lookupObjId($this->getWikiRefId()));

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
                . " (" . $lng->txt("wiki_pages") . ": " . count($pages) . ")", "wiki");
        $radg->addOption($op2);
        $op3 = new ilRadioOption($lng->txt("wiki_selected_pages"), "selection");
        $radg->addOption($op3);

        include_once("./Services/Form/classes/class.ilNestedListInputGUI.php");
        $nl = new ilNestedListInputGUI("", "obj_id");
        $op3->addSubItem($nl);

        foreach ($pages as $p) {
            $nl->addListNode(
                $p["id"],
                $p["title"],
                0,
                false,
                false,
                ilUtil::getImagePath("icon_pg.svg"),
                $lng->txt("wiki_page")
            );
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
        $ilTabs = $this->tabs;
        
        $pg_ids = $all_pages = array();
        
        // coming from type selection
        if (!is_array($_POST["wordr"])) {
            switch (trim($_POST["sel_type"])) {
                case "wiki":
                    include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
                    $all_pages = ilWikiPage::getAllWikiPages($this->getPageObject()->getWikiId());
                    foreach ($all_pages as $p) {
                        $pg_ids[] = $p["id"];
                    }
                    break;

                case "selection":
                    if (is_array($_POST["obj_id"])) {
                        $pg_ids = $_POST["obj_id"];
                    } else {
                        $pg_ids[] = $_GET["wpg_id"];
                    }
                    if (sizeof($pg_ids) > 1) {
                        break;
                    } else {
                        $_GET["wpg_id"] = array_pop($pg_ids);
                    }
                    // fallthrough

                // no order needed for single page
                // no break
                default:
                //case "page":
                    $this->ctrl->setParameterByClass("ilObjWikiGUI", "wpg_id", $_GET["wpg_id"]);
                    if ($a_pdf_export) {
                        $this->ctrl->redirectByClass("ilObjWikiGUI", "pdfExport");
                    } else {
                        $this->ctrl->redirectByClass("ilObjWikiGUI", "printView");
                    }
                    break;
            }
            
            if ($a_pdf_export) {
                $this->ctrl->setParameter($this, "pexp", 1);
            }
        }
        // refresh sorting
        else {
            $a_pdf_export = (bool) $_GET["pexp"];
        
            asort($_POST["wordr"]);
            $pg_ids = array_keys($_POST["wordr"]);
        }
        
        $ilTabs->clearTargets();
        $ilTabs->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "preview")
        );
        
        if (!sizeof($all_pages)) {
            include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
            $all_pages = ilWikiPage::getAllWikiPages($this->getPageObject()->getWikiId());
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
    public function blockWikiPage()
    {
        $ilAccess = $this->access;
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        include_once("./Modules/Wiki/classes/class.ilWikiPerm.php");
        if (ilWikiPerm::check("activate_wiki_protection", $_GET["ref_id"])) {
            $this->getPageObject()->setBlocked(true);
            $this->getPageObject()->update();

            ilUtil::sendSuccess($lng->txt("wiki_page_blocked"), true);
        }

        $ilCtrl->redirect($this, "preview");
    }

    /**
     * Unblock
     */
    public function unblockWikiPage()
    {
        $ilAccess = $this->access;
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        include_once("./Modules/Wiki/classes/class.ilWikiPerm.php");
        if (ilWikiPerm::check("activate_wiki_protection", $_GET["ref_id"])) {
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
    public function renameWikiPage()
    {
        $ilAccess = $this->access;
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        if (($ilAccess->checkAccess("edit_content", "", $_GET["ref_id"]) && !$this->getPageObject()->getBlocked())
            || $ilAccess->checkAccess("write", "", $_GET["ref_id"])) {
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
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

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
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;

        $this->initRenameForm();
        if ($this->form->checkInput()) {
            if (($ilAccess->checkAccess("edit_content", "", $_GET["ref_id"]) && !$this->getPageObject()->getBlocked())
                || $ilAccess->checkAccess("write", "", $_GET["ref_id"])) {
                $new_name = $this->form->getInput("new_page_name");
                
                $page_title = ilWikiUtil::makeDbTitle($new_name);
                $pg_id = ilWikiPage::_getPageIdForWikiTitle($this->getPageObject()->getWikiId(), $page_title);

                // we might get the same page id back here, if the page
                // name only differs in diacritics
                // see bug http://www.ilias.de/mantis/view.php?id=11226
                if ($pg_id > 0 && $pg_id != $this->getPageObject()->getId()) {
                    ilUtil::sendFailure($lng->txt("wiki_page_already_exists"));
                } else {
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
    
    public function activateWikiPageRating()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        $this->getPageObject()->setRating(true);
        $this->getPageObject()->update();
        
        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "preview");
    }
    
    public function deactivateWikiPageRating()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        $this->getPageObject()->setRating(false);
        $this->getPageObject()->update();
        
        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "preview");
    }
    
    
    public function observeNoteAction($a_wiki_id, $a_page_id, $a_type, $a_action, $a_note_id)
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
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
            
        $page = $this->getWikiPage();
        
        include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this, "updateAdvancedMetaData"));
        
        // :TODO:
        $form->setTitle($lng->txt("wiki_advmd_block_title") . ": " . $page->getTitle());
        
        include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordGUI.php');
        $this->record_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_EDITOR, 'wiki', $page->getWikiId(), 'wpg', $page->getId());
        $this->record_gui->setPropertyForm($form);
        $this->record_gui->parse();
        
        $form->addCommandButton("updateAdvancedMetaData", $lng->txt("save"));
        $form->addCommandButton("preview", $lng->txt("cancel"));
        
        return $form;
    }
        
    public function editAdvancedMetaData(ilPropertyFormGUI $a_form = null)
    {
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $ilAccess = $this->access;

        if (!$ilAccess->checkAccess("write", "", $this->wiki_ref_id) &&
            !$ilAccess->checkAccess("edit_page_meta", "", $this->wiki_ref_id)) {
            return;
        }


        $ilTabs->clearTargets();
        $ilTabs->setBackTarget(
            $lng->txt("back"),
            $ilCtrl->getLinkTarget($this, "preview")
        );
        
        if (!$a_form) {
            $a_form = $this->initAdvancedMetaDataForm();
        }
        
        $tpl->setContent($a_form->getHTML());
    }
    
    public function updateAdvancedMetaData()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilAccess = $this->access;

        if (!$ilAccess->checkAccess("write", "", $this->wiki_ref_id) &&
            !$ilAccess->checkAccess("edit_page_meta", "", $this->wiki_ref_id)) {
            return;
        }

        $form = $this->initAdvancedMetaDataForm();
    
        // needed for proper advanced MD validation
        $form->checkInput();
        if (!$this->record_gui->importEditFormPostValues()) {
            $this->editAdvancedMetaData($form); // #16470
            return false;
        }
                
        if ($this->record_gui->writeEditForm()) {
            ilUtil::sendSuccess($lng->txt("settings_saved"), true);
        }
        $ilCtrl->redirect($this, "preview");
    }
    
    public function hideAdvancedMetaData()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilAccess = $this->access;

        if (!$ilAccess->checkAccess("write", "", $this->wiki_ref_id) &&
            !$ilAccess->checkAccess("edit_page_meta", "", $this->wiki_ref_id)) {
            return;
        }

        $this->getPageObject()->hideAdvancedMetadata(true);
        $this->getPageObject()->update();
            
        ilUtil::sendSuccess($lng->txt("settings_saved"), true);
        $ilCtrl->redirect($this, "preview");
    }
    
    public function unhideAdvancedMetaData()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilAccess = $this->access;

        if (!$ilAccess->checkAccess("write", "", $this->wiki_ref_id) &&
            !$ilAccess->checkAccess("edit_page_meta", "", $this->wiki_ref_id)) {
            return;
        }

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
    public function edit()
    {
        $tpl = $this->tpl;
        $lng = $this->lng;

        self::initEditingJS($tpl);

        return parent::edit();
    }

    /**
     * Init wiki editing js
     *
     * @param ilTemplate $a_tpl template
     */
    public static function initEditingJS(ilGlobalTemplateInterface $a_tpl)
    {
        global $DIC;

        $lng = $DIC->language();

        $a_tpl->addJavascript("./Modules/Wiki/js/WikiEdit.js");
        $a_tpl->addOnLoadCode("il.Wiki.Edit.txt.page_exists = '" . $lng->txt("wiki_page_exists") . "';");
        $a_tpl->addOnLoadCode("il.Wiki.Edit.txt.new_page = '" . $lng->txt("wiki_new_page") . "';");
    }


    /**
     * Returns form to insert a wiki link per ajax
     */
    public function insertWikiLink()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->addCommandButton("addWikiLink", $lng->txt("wiki_add_link"));
        $form->addCommandButton("searchWikiLink", $lng->txt("search"));

        // Target page
        $tp = new ilTextInputGUI($this->lng->txt("wiki_target_page"), "target_page");
        $tp->setSize(18);
        $tp->setRequired(true);
        $tp->setInfo("...");
        $tp->setDataSource($ilCtrl->getLinkTarget($this, "insertWikiLinkAC", "", true));
        $form->addItem($tp);

        // Link text
        $lt = new ilTextInputGUI($this->lng->txt("wiki_link_text"), "link_text");
        $lt->setSize(18);
        $form->addItem($lt);

        //$form->setTitle($lng->txt("wiki_link"));

        echo $form->getHTML();
        exit;
    }

    /**
     * Auto complete for insert wiki link
     */
    public function insertWikiLinkAC()
    {
        $result = array();

        $term = $_GET["term"];

        // if page exists, make it first entry
        if (ilWikiPage::_wikiPageExists($this->getPageObject()->getParentId(), $term)) {
            $entry = new stdClass();
            $entry->value = $term;
            $entry->label = $term;
            $result[] = $entry;
        }

        $res = ilWikiPage::getPagesForSearch($this->getPageObject()->getParentId(), $term);

        $cnt = 0;
        foreach ($res as $r) {
            if ($result[0]->value == $r) {
                continue;
            }
            if ($cnt++ > 19) {
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
    public function searchWikiLinkAC()
    {
        $lng = $this->lng;

        $lng->loadLanguageModule("wiki");

        $tpl = new ilTemplate("tpl.wiki_ac_search_result.html", true, true, "Modules/Wiki");
        $term = trim($_GET["term"]);

        $pages = ilObjWiki::_performSearch($this->getPageObject()->getParentId(), $term);

        $found = array();
        foreach ($pages as $page) {
            $found[] = array("page_id" => $page["page_id"], "title" => ilWikiPage::lookupTitle($page["page_id"]));
        }

        // sort if all pages are listed
        if ($term == "") {
            $found = ilUtil::sortArray($found, "title", "asc");
        }

        foreach ($found as $f) {
            $tpl->setCurrentBlock("item");
            $tpl->setVariable("WIKI_TITLE", $f["title"]);
            $tpl->parseCurrentBlock();
        }

        if (count($pages) == 0) {
            $tpl->setVariable("INFOTEXT", str_replace("$1", $term, $lng->txt("wiki_no_page_found")));
        } elseif ($term == '') {
            $tpl->setVariable("INFOTEXT", $lng->txt("wiki_no_search_term"), $term);
        } else {
            $tpl->setVariable("INFOTEXT", str_replace("$1", $term, $lng->txt("wiki_pages_found")));
        }

        $tpl->setVariable("TXT_BACK", $lng->txt("back"));
        echo $tpl->get();
        exit;
    }

    //
    // exercise assignment
    //

    /**
     * Finalize and submit blog to exercise
     */
    protected function finalizeAssignment()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        include_once("./Modules/Exercise/AssignmentTypes/classes/class.ilExAssignmentTypes.php");
        include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
        $wiki_ass = ilExAssignmentTypes::getInstance()->getById(ilExAssignment::TYPE_WIKI_TEAM);

        $ass_id = (int) $_GET["ass"];
        $wiki_ass->submitWiki($ass_id, $this->user->getId(), $this->getWikiRefId());

        /*
        include_once "Modules/Exercise/classes/class.ilExSubmissionBaseGUI.php";
        include_once "Modules/Exercise/classes/class.ilExSubmissionObjectGUI.php";
        $exc_gui = ilExSubmissionObjectGUI::initGUIForSubmit($this->ass_id);
        $exc_gui->submitBlog($this->node_id);*/

        ilUtil::sendSuccess($lng->txt("wiki_finalized"), true);
        $ilCtrl->redirectByClass("ilObjWikiGUI", "gotoStartPage");
    }

    protected function downloadExcSubFile()
    {
        $ilUser = $this->user;

        $ass_id = (int) $_GET["ass"];
        $ass = new ilExAssignment($ass_id);
        $submission = new ilExSubmission($ass, $ilUser->getId());
        $submitted = $submission->getFiles();
        if (count($submitted) > 0) {
            $submitted = array_pop($submitted);

            $user_data = ilObjUser::_lookupName($submitted["user_id"]);
            $title = ilObject::_lookupTitle($submitted["obj_id"]) . " - " .
                $ass->getTitle() . " (Team " . $submission->getTeam()->getId() . ").zip";

            ilUtil::deliverFile($submitted["filename"], $title);
        }
    }

    /**
     * @return string
     */
    public function getCommentsHTMLExport()
    {
        return $this->getNotesHTML(
            $this->getPageObject(),
            false,
            ilObjWiki::_lookupPublicNotes($this->getPageObject()->getParentId()),
            false,
            null,
            true
        );
    }
}
