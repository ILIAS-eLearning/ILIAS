<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Class ilWikiPage GUI class
 *
 * @author Alexander Killing <killing@leifos.de>
 *
 * @ilCtrl_Calls ilWikiPageGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMediaPoolTargetSelector
 * @ilCtrl_Calls ilWikiPageGUI: ilPublicUserProfileGUI, ilPageObjectGUI, ilNoteGUI
 * @ilCtrl_Calls ilWikiPageGUI: ilCommonActionDispatcherGUI, ilRatingGUI, ilWikiStatGUI
 * @ilCtrl_Calls ilWikiPageGUI: ilObjectMetaDataGUI, ilPropertyFormGUI
 */
class ilWikiPageGUI extends ilPageObjectGUI
{
    protected \ILIAS\Notes\Service $notes;
    protected \ILIAS\HTTP\Services $http;
    protected \ILIAS\Wiki\Editing\EditingGUIRequest $wiki_request;
    protected ?ilAdvancedMDRecordGUI $record_gui = null;
    protected bool $fill_on_load_code = false;
    protected int $wiki_ref_id = 0;
    protected ilSetting $settings;
    protected ilObjWiki $wiki;

    public function __construct(
        int $a_id = 0,
        int $a_old_nr = 0,
        int $a_wiki_ref_id = 0
    ) {
        global $DIC;

        $this->settings = $DIC->settings();
        $this->http = $DIC->http();

        // needed for notifications
        $this->setWikiRefId($a_wiki_ref_id);
        
        parent::__construct("wpg", $a_id, $a_old_nr);
        $this->getPageObject()->setWikiRefId($this->getWikiRefId());
        
        // content style
        $this->tpl->addCss(ilObjStyleSheet::getSyntaxStylePath());
        $this->wiki_request = $DIC
            ->wiki()
            ->internal()
            ->gui()
            ->editing()
            ->request();
        $this->notes = $DIC->notes();
    }
    
    public function setScreenIdComponent() : void
    {
        $ilHelp = $this->help;
        $ilHelp->setScreenIdComponent("copgwpg");
    }

    public function setWikiRefId(int $a_ref_id) : void
    {
        $this->wiki_ref_id = $a_ref_id;
    }

    public function getWikiRefId() : int
    {
        return $this->wiki_ref_id;
    }

    public function setWiki(ilObjWiki $a_val) : void
    {
        $this->wiki = $a_val;
    }
    
    public function getWiki() : ilObjWiki
    {
        return $this->wiki;
    }
    
    /**
     * @throws ilCtrlException
     */
    public function executeCommand() : string
    {
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs_gui;
        $ilUser = $this->user;
        $ilAccess = $this->access;
        $lng = $this->lng;
        $tpl = $this->tpl;

        $next_class = $this->ctrl->getNextClass($this);

        $head_title = ilObject::_lookupTitle(ilObject::_lookupObjId($this->requested_ref_id)) .
            ": " . $this->getWikiPage()->getTitle();
        $tpl->setHeaderPageTitle($head_title);
        // see #13804
        if ($this->wiki_request->getPage() !== "") {
            $tpl->setPermanentLink(
                "wiki",
                $this->requested_ref_id,
                "wpage_" . $this->getPageObject()->getId() . "_" . $this->requested_ref_id,
                "",
                $head_title
            );
        } else {
            $tpl->setPermanentLink("wiki", $this->requested_ref_id);
        }


        switch ($next_class) {
            case "ilnotegui":
                $this->getTabs();
                $ilTabs->setTabActive("pg");
                return $this->preview();

            case "ilratinggui":
                // for rating side block
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
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                if (!is_null($gui)) {
                    $gui->enableCommentsSettings(false);
                    $gui->setRatingCallback(
                        $this,
                        "preview"
                    );
                    $this->ctrl->forwardCommand($gui);
                }
                break;
            
            case "ilwikistatgui":
                if ($ilAccess->checkAccess("statistics_read", "", $this->wiki_ref_id)) {
                    $this->tabs_gui->clearTargets(); // see ilObjWikiGUI::getTabs()
                    $this->getTabs("statistics");

                    $gui = new ilWikiStatGUI(
                        $this->getPageObject()->getParentId(),
                        $this->getPageObject()->getId()
                    );
                    $this->ctrl->forwardCommand($gui);
                }
                break;
            case 'ilobjectmetadatagui':
                
                if (!$ilAccess->checkAccess("write", "", $this->wiki_ref_id)) {
                    $this->tpl->setOnScreenMessage('failure', $lng->txt("permission_denied"), true);
                    $ilCtrl->redirect($this, "preview");
                }
                return parent::executeCommand();

            case "ilpropertyformgui":
                // only case is currently adv metadata internal link in info settings, see #24497
                $form = $this->initAdvancedMetaDataForm();
                $ilCtrl->forwardCommand($form);
                break;

            default:

                if (strtolower($ilCtrl->getNextClass()) === "ilpageeditorgui") {
                    self::initEditingJS($this->tpl);
                }

                if ($this->wiki_request->getNotification() > 0) {
                    switch ($this->wiki_request->getNotification()) {
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
        return "";
    }

    public function setWikiPage(ilWikiPage $a_wikipage) : void
    {
        $this->setPageObject($a_wikipage);
    }

    public function getWikiPage() : ilWikiPage
    {
        /** @var ilWikiPage $wp */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getPageObject();
    }

    /**
     * Get wiki page gui for id and title
     */
    public static function getGUIForTitle(
        int $a_wiki_id,
        string $a_title,
        int $a_old_nr = 0,
        int $a_wiki_ref_id = 0
    ) : ilWikiPageGUI {
        $id = ilWikiPage::getPageIdForTitle($a_wiki_id, $a_title);
        return new ilWikiPageGUI($id, $a_old_nr, $a_wiki_ref_id);
    }
    
    public function setSideBlock() : void
    {
        ilObjWikiGUI::renderSideBlock(
            $this->getWikiPage()->getId(),
            $this->wiki_ref_id,
            $this->getWikiPage()
        );
    }
    
    public function addHeaderAction(
        bool $a_redraw = false
    ) : string {
        $ilUser = $this->user;
        $ilAccess = $this->access;
        
        $wiki_id = $this->getPageObject()->getParentId();
        $page_id = $this->getPageObject()->getId();
        
        $dispatcher = new ilCommonActionDispatcherGUI(
            ilCommonActionDispatcherGUI::TYPE_REPOSITORY,
            $ilAccess,
            "wiki",
            $this->requested_ref_id,
            $wiki_id
        );
        $dispatcher->setSubObject("wpg", $page_id);

        ilObjectListGUI::prepareJsLinks(
            $this->ctrl->getLinkTarget($this, "redrawHeaderAction", "", true),
            "",
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
        if ($ilUser->getId() !== ANONYMOUS_USER_ID) {
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
        return "";
    }
        
    public function redrawHeaderAction() : void
    {
        echo $this->addHeaderAction(true);
        exit;
    }

    public function preview() : string
    {
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;
        $lng = $this->lng;
        $tpl = $this->tpl;
        $ilUser = $this->user;
        $ilSetting = $this->settings;
        $append = "";
        $message = "";


        // block/unblock
        if ($this->getPageObject()->getBlocked()) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("wiki_page_status_blocked"));
        }


        $this->increaseViewCount();
                
        $this->addHeaderAction();
        
        // content
        if ($ilCtrl->getNextClass() !== "ilnotegui") {
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
            $may_delete = ($ilSetting->get("comments_del_tutor", '1') &&
                $ilAccess->checkAccess("write", "", $this->requested_ref_id));
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

        $tpl->setLoginTargetPar("wiki_" . $this->requested_ref_id . $append);

        // last edited info
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

        $tpl->setLoginTargetPar("wiki_" . $this->requested_ref_id . $append);
        
        //highlighting
        if ($this->wiki_request->getSearchString()) {
            $cache = ilUserSearchCache::_getInstance($ilUser->getId());
            $cache->switchSearchType(ilUserSearchCache::LAST_QUERY);
            $search_string = $cache->getQuery();
            
            // advanced search?
            if (is_array($search_string)) {
                $search_string = $search_string["lom_content"];
            }

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
    
    public function showPage() : string
    {
        if ($this->getOutputMode() === ilPageObjectGUI::PRESENTATION) {
            $this->initToolbar();
        }
        $this->setTemplateOutput(false);
        
        if (!$this->getAbstractOnly()) {
            $this->setPresentationTitle($this->getWikiPage()->getTitle());

            // wiki stats clean up
            // $this->increaseViewCount();
        }
    
        return parent::showPage();
    }

    protected function initToolbar() : void
    {
        $toolbar = $this->toolbar;

        $print_view = $this->getPrintView();
        $modal_elements = $print_view->getModalElements($this->ctrl->getLinkTarget(
            $this,
            "printViewSelection"
        ));
        $toolbar->addComponent($modal_elements->button);
        $toolbar->addComponent($modal_elements->modal);
    }

    protected function getPrintView() : \ILIAS\Export\PrintProcessGUI
    {
        $provider = new \ILIAS\Wiki\WikiPrintViewProviderGUI(
            $this->lng,
            $this->ctrl,
            $this->getWikiPage()->getWikiRefId(),
            []
        );

        return new \ILIAS\Export\PrintProcessGUI(
            $provider,
            $this->http,
            $this->ui,
            $this->lng
        );
    }

    protected function increaseViewCount() : void
    {
        $ilUser = $this->user;
        
        $this->getWikiPage()->increaseViewCnt();
        
        // enable object statistics
        ilChangeEvent::_recordReadEvent(
            "wiki",
            $this->getWikiPage()->getWikiRefId(),
            $this->getWikiPage()->getWikiId(),
            $ilUser->getId()
        );

        ilWikiStat::handleEvent(ilWikiStat::EVENT_PAGE_READ, $this->getWikiPage());
    }

    public function postOutputProcessing(string $a_output) : string
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameterByClass(
            "ilobjwikigui",
            "from_page",
            ilWikiUtil::makeUrlTitle($this->wiki_request->getPage())
        );
        if ($this->getEnabledHref() && $this->getOutputMode() !== self::EDIT) {
            $output = ilWikiUtil::replaceInternalLinks(
                $a_output,
                $this->getWikiPage()->getWikiId(),
                ($this->getOutputMode() === "offline")
            );
        } else {
            $output = $a_output;
        }
        $ilCtrl->setParameterByClass(
            "ilobjwikigui",
            "from_page",
            $this->wiki_request->getFromPage()
        );


        // metadata in print view
        if ($this->getOutputMode() === "print" && $this->wiki instanceof ilObjWiki) {
            $mdgui = new ilObjectMetaDataGUI($this->wiki, "wpg", $this->getId());
            $md = $mdgui->getKeyValueList();
            if ($md !== "") {
                $output = str_replace("<!--COPage-PageTop-->", "<p>" . $md . "</p>", $output);
            }
        }


        return $output;
    }
    
    public function whatLinksHere() : void
    {
        $tpl = $this->tpl;
        
        $this->setSideBlock();
        $table_gui = new ilWikiPagesTableGUI(
            $this,
            "whatLinksHere",
            $this->getWikiPage()->getWikiId(),
            IL_WIKI_WHAT_LINKS_HERE,
            $this->wiki_request->getWikiPageId()
        );
            
        $tpl->setContent($table_gui->getHTML());
    }

    public function getTabs(
        string $a_activate = ""
    ) : void {
        $ilTabs = $this->tabs_gui;
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;

        parent::getTabs($a_activate);
        
        if ($ilAccess->checkAccess("statistics_read", "", $this->requested_ref_id)) {
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
                ilWikiUtil::makeDbTitle($this->wiki_request->getPage())
            )
        );
        $ilCtrl->setParameterByClass(
            "ilobjwikigui",
            "page",
            ilWikiUtil::makeUrlTitle($this->wiki_request->getPage())
        );

        $ilTabs->addTarget(
            "wiki_what_links_here",
            $this->ctrl->getLinkTargetByClass(
                "ilwikipagegui",
                "whatLinksHere"
            ),
            "whatLinksHere"
        );
    }

    public function deleteWikiPageConfirmationScreen() : void
    {
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        if (ilWikiPerm::check("delete_wiki_pages", $this->requested_ref_id)) {
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
            $cnt_note_users = $this->notes->domain()->getUserCount(
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

    public function cancelWikiPageDeletion() : void
    {
        $ilCtrl = $this->ctrl;
        
        $ilCtrl->redirect($this, "preview");
    }
    
    public function confirmWikiPageDeletion() : void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        if (ilWikiPerm::check("delete_wiki_pages", $this->requested_ref_id)) {
            $this->getPageObject()->delete();
            
            $this->tpl->setOnScreenMessage('success', $lng->txt("wiki_page_deleted"), true);
        }
        
        $ilCtrl->redirectByClass("ilobjwikigui", "allPages");
    }

    ////
    //// Print view selection
    ////

    public function printViewSelection() : void
    {
        $view = $this->getPrintView();
        $view->sendForm();
    }

    public function printViewOrder() : void
    {
        $this->printViewOrderList();
    }
    
    protected function printViewOrderList(
    ) : void {
        $ilTabs = $this->tabs_gui;
        
        $pg_ids = $all_pages = array();
        
        // coming from type selection
        $ordering = $this->wiki_request->getPrintOrdering();
        if (count($ordering) === 0) {
            switch ($this->wiki_request->getSelectedPrintType()) {
                case "wiki":
                    $all_pages = ilWikiPage::getAllWikiPages($this->getPageObject()->getWikiId());
                    foreach ($all_pages as $p) {
                        $pg_ids[] = $p["id"];
                    }
                    break;

                case "selection":
                    $pg_ids = $this->wiki_request->getWikiPageIds();
                    if (count($pg_ids) === 0) {
                        $pg_ids = [$this->wiki_request->getWikiPageId()];
                    }
                    if (count($pg_ids) > 1) {
                        break;
                    } else {
                        $wiki_page_id = array_pop($pg_ids);
                    }
                    $this->ctrl->setParameterByClass(
                        "ilObjWikiGUI",
                        "wpg_id",
                        $wiki_page_id
                    );
                    $this->ctrl->redirectByClass("ilObjWikiGUI", "printView");
                    break;

                default:
                    $this->ctrl->setParameterByClass(
                        "ilObjWikiGUI",
                        "wpg_id",
                        $this->wiki_request->getWikiPageId()
                    );
                    $this->ctrl->redirectByClass("ilObjWikiGUI", "printView");
                    break;
            }
        }
        // refresh sorting
        else {
            asort($ordering);
            $pg_ids = array_keys($ordering);
        }
        
        $ilTabs->clearTargets();
        $ilTabs->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "preview")
        );
        
        if (!count($all_pages)) {
            $all_pages = ilWikiPage::getAllWikiPages($this->getPageObject()->getWikiId());
        }
        
        $tbl = new ilWikiExportOrderTableGUI(
            $this,
            "printViewOrderList",
            $all_pages,
            $pg_ids
        );
        $this->tpl->setContent($tbl->getHTML());
    }
    

    ////
    //// Block/Unblock
    ////

    public function blockWikiPage() : void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        if (ilWikiPerm::check("activate_wiki_protection", $this->requested_ref_id)) {
            $this->getPageObject()->setBlocked(true);
            $this->getPageObject()->update();

            $this->tpl->setOnScreenMessage('success', $lng->txt("wiki_page_blocked"), true);
        }

        $ilCtrl->redirect($this, "preview");
    }

    public function unblockWikiPage() : void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        if (ilWikiPerm::check("activate_wiki_protection", $this->requested_ref_id)) {
            $this->getPageObject()->setBlocked(false);
            $this->getPageObject()->update();

            $this->tpl->setOnScreenMessage('success', $lng->txt("wiki_page_unblocked"), true);
        }

        $ilCtrl->redirect($this, "preview");
    }
    

    ////
    //// Rename
    ////

    public function renameWikiPage() : void
    {
        $ilAccess = $this->access;
        $tpl = $this->tpl;

        if (($ilAccess->checkAccess(
            "edit_content",
            "",
            $this->requested_ref_id
        ) && !$this->getPageObject()->getBlocked())
            || $ilAccess->checkAccess("write", "", $this->requested_ref_id)) {
            $this->initRenameForm();
            $tpl->setContent($this->form->getHTML());
        }
    }

    protected function initRenameForm() : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

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

    public function renamePage() : void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;

        $this->initRenameForm();
        if ($this->form->checkInput()) {
            if (($ilAccess->checkAccess("edit_content", "", $this->requested_ref_id) && !$this->getPageObject()->getBlocked())
                || $ilAccess->checkAccess("write", "", $this->requested_ref_id)) {
                $new_name = $this->form->getInput("new_page_name");
                
                $page_title = ilWikiUtil::makeDbTitle($new_name);
                $pg_id = ilWikiPage::_getPageIdForWikiTitle($this->getPageObject()->getWikiId(), $page_title);

                // we might get the same page id back here, if the page
                // name only differs in diacritics
                // see bug http://www.ilias.de/mantis/view.php?id=11226
                if ($pg_id > 0 && $pg_id != $this->getPageObject()->getId()) {
                    $this->tpl->setOnScreenMessage('failure', $lng->txt("wiki_page_already_exists"));
                } else {
                    $new_name = $this->getPageObject()->rename($new_name);
                    $ilCtrl->setParameterByClass("ilobjwikigui", "page", ilWikiUtil::makeUrlTitle($new_name));
                    $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
                    $ilCtrl->redirect($this, "preview");
                }
            }
        }

        $this->form->setValuesByPost();
        $tpl->setContent($this->form->getHTML());
    }
    
    ////
    /// Rating
    ////
    
    public function activateWikiPageRating() : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        $this->getPageObject()->setRating(true);
        $this->getPageObject()->update();
        
        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "preview");
    }
    
    public function deactivateWikiPageRating() : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        $this->getPageObject()->setRating(false);
        $this->getPageObject()->update();
        
        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "preview");
    }
    
    
    public function observeNoteAction(
        int $a_wiki_id,
        int $a_page_id,
        string $a_type,
        string $a_action,
        int $a_note_id
    ) : void {

        // #10040 - get note text
        $note = $this->notes->domain()->getById($a_note_id);
        $text = $note->getText();
        
        ilWikiUtil::sendNotification("comment", ilNotification::TYPE_WIKI_PAGE, $this->getWikiRefId(), $a_page_id, $text);
    }
        
    public function updateStatsRating(
        int $a_wiki_id,
        string $a_wiki_type,
        int $a_page_id,
        string $a_page_type
    ) : void {
        ilWikiStat::handleEvent(ilWikiStat::EVENT_PAGE_RATING, $this->getWikiPage());
    }
    
    
    //
    // advanced meta data
    //
    
    protected function initAdvancedMetaDataForm() : ilPropertyFormGUI
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
            
        $page = $this->getWikiPage();
        
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this, "updateAdvancedMetaData"));
        
        // :TODO:
        $form->setTitle($lng->txt("wiki_advmd_block_title") . ": " . $page->getTitle());
        
        $this->record_gui = new ilAdvancedMDRecordGUI(
            ilAdvancedMDRecordGUI::MODE_EDITOR,
            'wiki',
            $page->getWikiId(),
            'wpg',
            $page->getId()
        );
        $this->record_gui->setPropertyForm($form);
        $this->record_gui->parse();
        
        $form->addCommandButton("updateAdvancedMetaData", $lng->txt("save"));
        $form->addCommandButton("preview", $lng->txt("cancel"));
        
        return $form;
    }
        
    public function editAdvancedMetaData(
        ilPropertyFormGUI $a_form = null
    ) : void {
        $ilTabs = $this->tabs_gui;
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
    
    public function updateAdvancedMetaData() : void
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
            return;
        }
                
        if ($this->record_gui->writeEditForm()) {
            $this->tpl->setOnScreenMessage('success', $lng->txt("settings_saved"), true);
        }
        $ilCtrl->redirect($this, "preview");
    }
    
    public function hideAdvancedMetaData() : void
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
            
        $this->tpl->setOnScreenMessage('success', $lng->txt("settings_saved"), true);
        $ilCtrl->redirect($this, "preview");
    }
    
    public function unhideAdvancedMetaData() : void
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
            
        $this->tpl->setOnScreenMessage('success', $lng->txt("settings_saved"), true);
        $ilCtrl->redirect($this, "preview");
    }

    public function edit() : string
    {
        $tpl = $this->tpl;
        $lng = $this->lng;

        self::initEditingJS($tpl);

        return parent::edit();
    }

    public static function initEditingJS(ilGlobalTemplateInterface $a_tpl) : void
    {
        global $DIC;

        $lng = $DIC->language();

        $a_tpl->addJavaScript("./Modules/Wiki/js/WikiEdit.js");
        $a_tpl->addOnLoadCode("il.Wiki.Edit.txt.page_exists = '" . $lng->txt("wiki_page_exists") . "';");
        $a_tpl->addOnLoadCode("il.Wiki.Edit.txt.new_page = '" . $lng->txt("wiki_new_page") . "';");
    }


    /**
     * Returns form to insert a wiki link per ajax
     */
    public function insertWikiLink() : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

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
    public function insertWikiLinkAC() : void
    {
        $result = array();

        $term = $this->wiki_request->getTerm();

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

        echo json_encode($result, JSON_THROW_ON_ERROR);
        exit;
    }

    /**
     * Search wiki link list
     */
    public function searchWikiLinkAC() : void
    {
        $lng = $this->lng;

        $lng->loadLanguageModule("wiki");

        $tpl = new ilTemplate("tpl.wiki_ac_search_result.html", true, true, "Modules/Wiki");
        $term = $this->wiki_request->getTerm();

        $pages = ilObjWiki::_performSearch($this->getPageObject()->getParentId(), $term);

        $found = array();
        foreach ($pages as $page) {
            $found[] = array("page_id" => $page["page_id"], "title" => ilWikiPage::lookupTitle($page["page_id"]));
        }

        // sort if all pages are listed
        if ($term === "") {
            $found = ilArrayUtil::sortArray($found, "title", "asc");
        }

        foreach ($found as $f) {
            $tpl->setCurrentBlock("item");
            $tpl->setVariable("WIKI_TITLE", $f["title"]);
            $tpl->parseCurrentBlock();
        }

        if (count($pages) === 0) {
            $tpl->setVariable("INFOTEXT", str_replace("$1", $term, $lng->txt("wiki_no_page_found")));
        } elseif ($term === '') {
            $tpl->setVariable("INFOTEXT", $lng->txt("wiki_no_search_term"));
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
    protected function finalizeAssignment() : void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $wiki_ass = ilExAssignmentTypes::getInstance()->getById(ilExAssignment::TYPE_WIKI_TEAM);

        $ass_id = $this->wiki_request->getAssignmentId();
        $wiki_ass->submitWiki($ass_id, $this->user->getId(), $this->getWikiRefId());

        $this->tpl->setOnScreenMessage('success', $lng->txt("wiki_finalized"), true);
        $ilCtrl->redirectByClass("ilObjWikiGUI", "gotoStartPage");
    }

    protected function downloadExcSubFile() : void
    {
        $ilUser = $this->user;

        $ass_id = $this->wiki_request->getAssignmentId();
        $ass = new ilExAssignment($ass_id);
        $submission = new ilExSubmission($ass, $ilUser->getId());
        $submitted = $submission->getFiles();
        if (count($submitted) > 0) {
            $submitted = array_pop($submitted);

            $user_data = ilObjUser::_lookupName($submitted["user_id"]);
            $title = ilObject::_lookupTitle($submitted["obj_id"]) . " - " .
                $ass->getTitle() . " (Team " . $submission->getTeam()->getId() . ").zip";

            ilFileDelivery::deliverFileLegacy($submitted["filename"], $title);
        }
    }

    public function getCommentsHTMLExport() : string
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
