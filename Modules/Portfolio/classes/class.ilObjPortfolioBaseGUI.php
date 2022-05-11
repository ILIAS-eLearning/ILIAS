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

use ILIAS\Portfolio\StandardGUIRequest;
use ILIAS\Portfolio\PortfolioPrintViewProviderGUI;

/**
 * Portfolio view gui base class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
abstract class ilObjPortfolioBaseGUI extends ilObject2GUI
{
    protected StandardGUIRequest $port_request;
    protected ilHelpGUI $help;
    protected int $user_id = 0;
    protected array $additional = array();
    protected array $perma_link = [];
    protected int $page_id = 0;
    protected string $page_mode; // preview|edit
    protected int $requested_ppage;
    protected int $requested_user_page;
    protected string $requested_back_url = "";
    protected \ILIAS\DI\UIServices $ui;
    protected \ILIAS\HTTP\Services $http;
    protected \ILIAS\Style\Content\GUIService $content_style_gui;
    protected \ILIAS\Style\Content\Object\ObjectFacade $content_style_domain;

    public function __construct(
        int $a_id = 0,
        int $a_id_type = self::REPOSITORY_NODE_ID,
        int $a_parent_node_id = 0
    ) {
        global $DIC;

        $this->user = $DIC->user();
        $this->locator = $DIC["ilLocator"];
        $this->toolbar = $DIC->toolbar();
        $this->settings = $DIC->settings();
        $this->tree = $DIC->repositoryTree();
        $this->help = $DIC["ilHelp"];
        $this->tpl = $DIC["tpl"];
        $ilUser = $DIC->user();
        $this->ui = $DIC->ui();

        $this->port_request = $DIC->portfolio()
            ->internal()
            ->gui()
            ->standardRequest();
        
        $this->http = $DIC->http();
        
        parent::__construct($a_id, $a_id_type, $a_parent_node_id);

        $this->user_id = $ilUser->getId();
        
        $this->lng->loadLanguageModule("prtf");
        $this->lng->loadLanguageModule("user");
        $this->lng->loadLanguageModule("obj");

        $this->requested_ppage = $this->port_request->getPortfolioPageId();
        $this->requested_user_page = $this->port_request->getUserPage();

        // temp sanitization, should be done smarter in the future
        $back = str_replace("&amp;", ":::", $this->port_request->getBackUrl());
        $back = preg_replace(
            "/[^a-zA-Z0-9_\.\?=:\s]/",
            "",
            $back
        );
        $this->requested_back_url = str_replace(":::", "&amp;", $back);

        $this->ctrl->setParameterByClass("ilobjportfoliogui", "back_url", rawurlencode($this->requested_back_url));
        $cs = $DIC->contentStyle();
        $this->content_style_gui = $cs->gui();
        $this->content_style_domain = $cs->domain()->styleForRefId($this->object->getRefId());
    }
    
    protected function addLocatorItems() : void
    {
        $ilLocator = $this->locator;
        
        if ($this->object) {
            $ilLocator->addItem(
                strip_tags($this->object->getTitle()),
                $this->ctrl->getLinkTarget($this, "view")
            );
        }
                
        if ($this->page_id > 0) {
            $page = $this->getPageInstance($this->page_id);
            $title = $page->getTitle();
            if ($page->getType() === ilPortfolioPage::TYPE_BLOG) {
                $title = ilObject::_lookupTitle($title);
            }
            $this->ctrl->setParameterByClass($this->getPageGUIClassName(), "ppage", $this->page_id);
            $ilLocator->addItem(
                $title,
                $this->ctrl->getLinkTargetByClass($this->getPageGUIClassName(), "edit")
            );
        }
    }
    
    protected function determinePageCall() : bool
    {
        // edit
        if ($this->requested_ppage > 0) {
            if (!$this->checkPermissionBool("write")) {
                $this->ctrl->redirect($this, "view");
            }
            
            $this->page_id = $this->requested_ppage;
            $this->page_mode = "edit";
            $this->ctrl->setParameter($this, "ppage", $this->page_id);
            return true;
        }

        // preview
        $this->page_id = $this->requested_user_page;
        $this->page_mode = "preview";
        $this->ctrl->setParameter($this, "user_page", $this->page_id);
        return false;
    }
    
    protected function handlePageCall(string $a_cmd) : void
    {
        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "view")
        );
        
        if (!$this->page_id) {
            $this->ctrl->redirect($this, "view");
        }

        $page_gui = $this->getPageGUIInstance($this->page_id);

        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($page_gui, "edit")
        );

        // needed for editor
        $page_gui->setStyleId($this->content_style_domain->getEffectiveStyleId());
        
        $ret = $this->ctrl->forwardCommand($page_gui);

        if ($ret != "" && $ret !== true) {
            // preview (fullscreen)
            if ($this->page_mode === "preview") {
                // embedded call which did not generate any output (e.g. calendar month navigation)
                if ($ret != ilPortfolioPageGUI::EMBEDDED_NO_OUTPUT) {
                    // suppress (portfolio) notes for blog postings
                    $this->preview(false, $ret, ($a_cmd !== "previewEmbedded"));
                } else {
                    $this->preview(false);
                }
            }
            // edit
            else {
                $this->setContentStyleSheet();
                if (is_string($ret)) {
                    $this->tpl->setContent($ret);
                }
            }
        }
    }
    
    /**
    * Set Additonal Information (used in public profile?)
    */
    public function setAdditional(array $a_additional) : void
    {
        $this->additional = $a_additional;
    }

    public function getAdditional() : array
    {
        return $this->additional;
    }
        
    /**
     * Set custom perma link (used in public profile?)
     */
    public function setPermaLink(
        int $a_obj_id,
        string $a_type
    ) : void {
        $this->perma_link = array("obj_id" => $a_obj_id, "type" => $a_type);
    }
        
    
    //
    // CREATE/EDIT
    //
    
    protected function setSettingsSubTabs(string $a_active) : void
    {
        // #17455
        $this->lng->loadLanguageModule($this->getType());
        
        // general properties
        $this->tabs_gui->addSubTab(
            "properties",
            $this->lng->txt($this->getType() . "_properties"),
            $this->ctrl->getLinkTarget($this, 'edit')
        );
        
        $this->tabs_gui->addSubTab(
            "style",
            $this->lng->txt("obj_sty"),
            $this->ctrl->getLinkTargetByClass("ilobjectcontentstylesettingsgui", "")
        );
        
        $this->tabs_gui->activateSubTab($a_active);
    }
        
    protected function initEditCustomForm(ilPropertyFormGUI $a_form) : void
    {
        $this->setSettingsSubTabs("properties");


        // profile picture
        $ppic = new ilCheckboxInputGUI($this->lng->txt("prtf_profile_picture"), "ppic");
        $a_form->addItem($ppic);

        $prfa_set = new ilSetting("prfa");
        if ($prfa_set->get("banner")) {
            $dimensions = " (" . $prfa_set->get("banner_width") . "x" .
                $prfa_set->get("banner_height") . ")";

            $img = new ilImageFileInputGUI($this->lng->txt("prtf_banner") . $dimensions, "banner");
            $a_form->addItem($img);

            // show existing file
            $file = $this->object->getImageFullPath(true);
            if ($file) {
                $img->setImage($file);
            }
        }

        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('obj_features'));
        $a_form->addItem($section);

        // comments
        $comments = new ilCheckboxInputGUI($this->lng->txt("prtf_public_comments"), "comments");
        $a_form->addItem($comments);

        /* #15000
        $bg_color = new ilColorPickerInputGUI($this->lng->txt("prtf_background_color"), "bg_color");
        $a_form->addItem($bg_color);

        $font_color = new ilColorPickerInputGUI($this->lng->txt("prtf_font_color"), "font_color");
        $a_form->addItem($font_color);
        */
    }
    
    protected function getEditFormCustomValues(array &$a_values) : void
    {
        $a_values["comments"] = $this->object->hasPublicComments();
        $a_values["ppic"] = $this->object->hasProfilePicture();
        /*
        $a_values["bg_color"] = $this->object->getBackgroundColor();
        $a_values["font_color"] = $this->object->getFontColor();
        */
    }
    
    protected function updateCustom(ilPropertyFormGUI $form) : void
    {
        $this->object->setPublicComments($form->getInput("comments"));
        $this->object->setProfilePicture($form->getInput("ppic"));
        /*
        $this->object->setBackgroundColor($a_form->getInput("bg_color"));
        $this->object->setFontcolor($a_form->getInput("font_color"));
        */
        
        $prfa_set = new ilSetting("prfa");

        if ($_FILES["banner"]["tmp_name"]) {
            $this->object->uploadImage($_FILES["banner"]);
        } elseif ($prfa_set->get('banner') || $form->getItemByPostVar("banner")->getDeletionFlag()) {
            $this->object->deleteImage();
        }
    }
    
    
    //
    // PAGES
    //
    
    abstract protected function getPageInstance(
        ?int $a_page_id = null,
        ?int $a_portfolio_id = null
    ) : ilPortfolioPage;
    
    abstract protected function getPageGUIInstance(int $a_page_id) : ilPortfolioPageGUI;
    
    abstract public function getPageGUIClassName() : string;
        
    /**
     * Show list of portfolio pages
     */
    public function view() : void
    {
        $ilToolbar = $this->toolbar;
        $ilSetting = $this->settings;
        $lng = $this->lng;
        
        if (!$this->checkPermissionBool("write")) {
            $this->ctrl->redirect($this, "infoScreen");
        }
        
        $this->tabs_gui->activateTab("pages");
        

        $button = ilLinkButton::getInstance();
        $button->setCaption("prtf_add_page");
        $button->setUrl($this->ctrl->getLinkTarget($this, "addPage"));
        $ilToolbar->addStickyItem($button);

        if (!$ilSetting->get('disable_wsp_blogs')) {
            $button = ilLinkButton::getInstance();
            $button->setCaption("prtf_add_blog");
            $button->setUrl($this->ctrl->getLinkTarget($this, "addBlog"));
            $ilToolbar->addStickyItem($button);
        }


        // #16571
        $modal_html = "";
        if ($this->getType() === "prtf") {
            $ilToolbar->addSeparator();

            $ui = $this->ui;

            if ($this->object->isCommentsExportPossible()) {
                $this->lng->loadLanguageModule("note");
                $comment_export_helper = new \ILIAS\Notes\Export\ExportHelperGUI();
                $comment_modal = $comment_export_helper->getCommentIncludeModalDialog(
                    $this->lng->txt("export_html"),
                    $this->lng->txt("note_html_export_include_comments"),
                    $this->ctrl->getLinkTarget($this, "export"),
                    $this->ctrl->getLinkTarget($this, "exportWithComments")
                );
                $button = $ui->factory()->button()->standard($this->lng->txt("export_html"), '')
                             ->withOnClick($comment_modal->getShowSignal());
                $ilToolbar->addComponent($button);
                $modal_html = $ui->renderer()->render($comment_modal);
            } else {
                $button = ilLinkButton::getInstance();
                $button->setCaption("export_html");
                $button->setUrl($this->ctrl->getLinkTarget($this, "export"));
                $ilToolbar->addButtonInstance($button);
            }

            $print_view = $this->getPrintView();
            $modal_elements = $print_view->getModalElements($this->ctrl->getLinkTarget(
                $this,
                "printSelection"
            ));
            $modal_html .= $ui->renderer()->render($modal_elements->modal);
            $ilToolbar->addComponent($modal_elements->button);
        }
        
        $table = new ilPortfolioPageTableGUI($this, "view");
        

        $this->tpl->setContent($table->getHTML() . $modal_html);
    }

    public function getPrintView() : \ILIAS\Export\PrintProcessGUI
    {
        $obj_ids = $this->port_request->getObjIds();
        if (count($obj_ids) === 0) {
            $obj_ids = null;
        }
        /** @var ilObjPortfolio $port */
        $port = $this->object;
        $provider = new PortfolioPrintViewProviderGUI(
            $this->lng,
            $this->ctrl,
            $port,
            false,
            $obj_ids
        );
        return new \ILIAS\Export\PrintProcessGUI(
            $provider,
            $this->http,
            $this->ui,
            $this->lng
        );
    }

    /**
     * Show portfolio page creation form
     */
    protected function addPage() : void
    {
        $ilHelp = $this->help;

        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "view")
        );

        $ilHelp->setScreenIdComponent("prtf");
        $ilHelp->setScreenId("add_page");


        $form = $this->initPageForm("create");
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Init portfolio page form
     */
    public function initPageForm(string $a_mode = "create") : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));

        // title
        $ti = new ilTextInputGUI($this->lng->txt("title"), "title");
        $ti->setMaxLength(200);
        $ti->setRequired(true);
        $form->addItem($ti);

        // save and cancel commands
        if ($a_mode === "create") {
            $templates = ilPageLayout::activeLayouts(ilPageLayout::MODULE_PORTFOLIO);
            if ($templates) {
                $use_template = new ilRadioGroupInputGUI($this->lng->txt("prtf_use_page_layout"), "tmpl");
                $use_template->setRequired(true);
                $form->addItem($use_template);

                $opt = new ilRadioOption($this->lng->txt("none"), 0);
                $use_template->addOption($opt);

                foreach ($templates as $templ) {
                    $templ->readObject();

                    $opt = new ilRadioOption($templ->getTitle() . $templ->getPreview(), $templ->getId());
                    $use_template->addOption($opt);
                }
            }
            
            $form->setTitle($this->lng->txt("prtf_add_page") . ": " .
                $this->object->getTitle());
            $form->addCommandButton("savePage", $this->lng->txt("save"));
            $form->addCommandButton("view", $this->lng->txt("cancel"));
        }
        
        return $form;
    }
        
    /**
     * Create new portfolio page
     */
    public function savePage() : void
    {
        $form = $this->initPageForm("create");
        if ($form->checkInput() && $this->checkPermissionBool("write")) {
            $page = $this->getPageInstance();
            $page->setType(ilPortfolioPage::TYPE_PAGE);
            $page->setTitle($form->getInput("title"));
            
            // use template as basis
            $layout_id = $form->getInput("tmpl");
            if ($layout_id) {
                $layout_obj = new ilPageLayout($layout_id);
                $page->setXMLContent($layout_obj->getXMLContent());
            }
            
            $page->create();

            $this->tpl->setOnScreenMessage('success', $this->lng->txt("prtf_page_created"), true);
            $this->ctrl->redirect($this, "view");
        }

        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "view")
        );

        $form->setValuesByPost();
        $this->tpl->setContent($form->getHTML());
    }
    
    /**
     * Show portfolio blog page creation form
     */
    protected function addBlog() : void
    {
        $ilHelp = $this->help;

        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "view")
        );

        $ilHelp->setScreenIdComponent("prtf");
        $ilHelp->setScreenId("add_blog");

        $form = $this->initBlogForm();
        $this->tpl->setContent($form->getHTML());
    }
    
    abstract protected function initBlogForm() : ilPropertyFormGUI;
    
    abstract protected function saveBlog() : void;
    
    /**
     * Save ordering of portfolio pages
     */
    public function savePortfolioPagesOrdering() : void
    {
        if (!$this->checkPermissionBool("write")) {
            return;
        }

        $title_changes = array();

        $order = $this->port_request->getOrder();
        $titles = $this->port_request->getTitles();
        if (count($order) > 0) {
            foreach ($order as $k => $v) {
                $page = $this->getPageInstance(ilUtil::stripSlashes($k));
                if ($titles[$k]) {
                    $new_title = $titles[$k];
                    if ($page->getTitle() != $new_title) {
                        $title_changes[$page->getId()] = array("old" => $page->getTitle(), "new" => $new_title);
                        $page->setTitle($new_title);
                    }
                }
                $page->setOrderNr(ilUtil::stripSlashes($v));
                $page->update();
            }
            ilPortfolioPage::fixOrdering($this->object->getId());
        }

        $this->object->fixLinksOnTitleChange($title_changes);

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "view");
    }

    public function confirmPortfolioPageDeletion() : void
    {
        $prtf_pages = $this->port_request->getPortfolioPageIds();

        if (count($prtf_pages) === 0) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "view");
        } else {
            $this->tabs_gui->activateTab("pages");
            
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($this->ctrl->getFormAction($this));
            $cgui->setHeaderText($this->lng->txt("prtf_sure_delete_portfolio_pages"));
            $cgui->setCancel($this->lng->txt("cancel"), "view");
            $cgui->setConfirm($this->lng->txt("delete"), "deletePortfolioPages");

            foreach ($prtf_pages as $id) {
                $page = $this->getPageInstance($id);
                if ($page->getPortfolioId() !== $this->object->getId()) {
                    continue;
                }

                $title = $page->getTitle();
                if ($page->getType() === ilPortfolioPage::TYPE_BLOG) {
                    $title = $this->lng->txt("obj_blog") . ": " . ilObject::_lookupTitle((int) $title);
                }
                $cgui->addItem("prtf_pages[]", $id, $title);
            }

            $this->tpl->setContent($cgui->getHTML());
        }
    }

    public function deletePortfolioPages() : void
    {
        if (!$this->checkPermissionBool("write")) {
            return;
        }

        $page_ids = $this->port_request->getPortfolioPageIds();
        foreach ($page_ids as $id) {
            $page = $this->getPageInstance($id);
            $page->delete();
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("prtf_portfolio_page_deleted"), true);
        $this->ctrl->redirect($this, "view");
    }
    
    public function preview(
        bool $a_return = false,
        bool $a_content = false,
        bool $a_show_notes = true
    ) : string {
        $ilSetting = $this->settings;
        $ilUser = $this->user;
        
        $portfolio_id = $this->object->getId();
        $user_id = $this->object->getOwner();

        $content = "";
        
        $this->tabs_gui->clearTargets();
            
        $pages = ilPortfolioPage::getAllPortfolioPages($portfolio_id);
        $current_page = $this->requested_user_page;
        
        // validate current page
        if ($pages && $current_page) {
            $found = false;
            foreach ($pages as $page) {
                if ($page["id"] == $current_page) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $current_page = null;
            }
        }

        // display first page of portfolio if none given
        if (!$current_page && $pages) {
            $current_page = $pages;
            $current_page = array_shift($current_page);
            $current_page = $current_page["id"];
        }
        
        // #13788 - keep page after login
        if ($this->user_id === ANONYMOUS_USER_ID &&
            $this->getType() === "prtf") {
            $this->tpl->setLoginTargetPar("prtf_" . $this->object->getId() . "_" . $current_page);
        }
        
        $back_caption = "";
                        
        // public profile
        if ($this->requested_back_url != "") {
            $back = $this->requested_back_url;
        } elseif (strtolower($this->port_request->getBaseClass()) !== "ilpublicuserprofilegui" &&
            $this->user_id && $this->user_id !== ANONYMOUS_USER_ID) {
            if (!$this->checkPermissionBool("write")) {
                // shared
                if ($this->getType() === "prtf") {
                    $this->ctrl->setParameterByClass("ilportfoliorepositorygui", "shr_id", $this->object->getOwner());
                    $back = $this->ctrl->getLinkTargetByClass(array("ildashboardgui", "ilportfoliorepositorygui"), "showOther");
                    $this->ctrl->setParameterByClass("ilportfoliorepositorygui", "shr_id", "");
                }
                // listgui / parent container
                else {
                    // #12819
                    $tree = $this->tree;
                    $parent_id = $tree->getParentId($this->node_id);
                    $back = ilLink::_getStaticLink($parent_id);
                }
            }
            // owner
            else {
                $back = $this->ctrl->getLinkTarget($this, "view");
                if ($this->getType() === "prtf") {
                    $back_caption = $this->lng->txt("prtf_back_to_portfolio_owner");
                } else {
                    // #19316
                    $this->lng->loadLanguageModule("prtt");
                    $back_caption = $this->lng->txt("prtt_edit");
                }
            }
        }
        
        // render tabs
        $current_blog = null;
        if (count($pages) > 1) {
            foreach ($pages as $p) {
                if ($p["type"] == ilPortfolioPage::TYPE_BLOG) {
                    // needed for blog comments (see below)
                    if ($p["id"] == $current_page) {
                        $current_blog = (int) $p["title"];
                    }
                    $p["title"] = ilObjBlog::_lookupTitle($p["title"]);
                }
                
                $this->ctrl->setParameter($this, "user_page", $p["id"]);
                $this->tabs_gui->addTab(
                    "user_page_" . $p["id"],
                    $p["title"],
                    $this->ctrl->getLinkTarget($this, "preview")
                );
            }
            
            $this->tabs_gui->activateTab("user_page_" . $current_page);
        }
        
        $this->ctrl->setParameter($this, "user_page", $current_page);
        
        if (!$a_content) {
            // #18291
            if ($current_page) {
                // get current page content
                $page_gui = $this->getPageGUIInstance($current_page);
                $page_gui->setEmbedded(true);

                $content = $this->ctrl->getHTML($page_gui);
            }
        } else {
            $content = $a_content;
        }
        
        if ($a_return && $this->checkPermissionBool("write")) {
            return $content;
        }
                        
        // blog posting comments are handled within the blog
        $notes = "";
        if ($a_show_notes && $this->object->hasPublicComments() && !$current_blog && $current_page) {
            $note_gui = new ilNoteGUI($portfolio_id, $current_page, "pfpg");

            $note_gui->setRepositoryMode(false);
            $note_gui->enablePublicNotes(true);
            $note_gui->enablePrivateNotes(false);
            
            $note_gui->enablePublicNotesDeletion(($this->user_id === $user_id) &&
                $ilSetting->get("comments_del_tutor", '1'));
                        
            $next_class = $this->ctrl->getNextClass($this);
            if ($next_class === "ilnotegui") {
                $notes = $this->ctrl->forwardCommand($note_gui);
            } else {
                $notes = $note_gui->getCommentsHTML();
            }
        }
            
        if ($this->perma_link === null) {
            if ($this->getType() === "prtf") {
                $this->tpl->setPermanentLink($this->getType(), $this->object->getId(), "_" . $current_page);
            } else {
                $this->tpl->setPermanentLink($this->getType(), $this->object->getRefId());
            }
        } else {
            $this->tpl->setPermanentLink($this->perma_link["type"], $this->perma_link["obj_id"]);
        }
        
        // #18208 - see ilPortfolioTemplatePageGUI::getPageContentUserId()
        if ($this->getType() === "prtt" && !$this->checkPermissionBool("write")) {
            $user_id = $ilUser->getId();
        }

        /** @var ilObjPortfolioBase $obj */
        $obj = $this->object;
        self::renderFullscreenHeader($obj, $this->tpl, $user_id);
        
        // #13564
        $this->ctrl->setParameter($this, "user_page", "");
        //$this->tpl->setTitleUrl($this->ctrl->getLinkTarget($this, "preview"));
        $this->ctrl->setParameter($this, "user_page", $this->page_id);
        
        // blog pages do their own (page) style handling
        if (!$current_blog) {
            $content = '<div id="ilCOPageContent" class="ilc_page_cont_PageContainer">' .
                '<div class="ilc_page_Page">' .
                    $content .
                '</div></div>';
                                        
            $this->setContentStyleSheet($this->tpl);
        }

        $this->showEditButton($current_page);

        // #10717
        $this->tpl->setContent($content .
            '<div class="ilClearFloat">' . $notes . '</div>');
        return "";
    }

    protected function showEditButton(
        int $page_id
    ) : void {
        $page_class = ($this->getType() === "prtt")
            ? "ilPortfolioTemplatePageGUI"
            : "ilportfoliopagegui";
        $button = null;
        if (ilPortfolioPage::lookupType($page_id) === ilPortfolioPage::TYPE_PAGE) {
            $this->ctrl->setParameterByClass($page_class, "ppage", $page_id);
            $button = $this->ui->factory()->button()->standard(
                $this->lng->txt("edit"),
                $this->ctrl->getLinkTargetByClass($page_class, "edit")
            );
        } elseif ($this->getType() !== "prtt") {
            if ($page_id > 0) {
                $this->ctrl->setParameterByClass("ilobjbloggui", "ppage", $page_id);
                $this->ctrl->setParameterByClass(
                    "ilobjbloggui",
                    "prt_id",
                    $this->port_request->getPortfolioId()
                );
                $button = $this->ui->factory()->button()->standard(
                    $this->lng->txt("edit"),
                    $this->ctrl->getLinkTargetByClass([$page_class, "ilobjbloggui"], "render")
                );
            }
        } else {    // portfolio template, blog page cannot be edited -> link to overview
            $button = $this->ui->factory()->button()->standard(
                $this->lng->txt("edit"),
                $this->ctrl->getLinkTargetByClass(["ilobjportfoliotemplategui"], "view")
            );
        }
        if ($button && $this->checkPermissionBool("write")) {
            $this->tpl->setHeaderActionMenu($this->ui->renderer()->render($button));
        }
    }

    /**
     * Render banner, user name
     */
    public static function renderFullscreenHeader(
        ilObjPortfolioBase $a_portfolio,
        ilGlobalTemplateInterface $a_tpl,
        int $a_user_id,
        bool $a_export = false
    ) : void {
        global $DIC;

        $ilUser = $DIC->user();

        if (!$a_export) {
            ilChangeEvent::_recordReadEvent(
                $a_portfolio->getType(),
                ($a_portfolio->getType() === "prtt")
                    ? $a_portfolio->getRefId()
                    : $a_portfolio->getId(),
                $a_portfolio->getId(),
                $ilUser->getId()
            );
        }

        $name = ilObjUser::_lookupName($a_user_id);
        $name = $name["lastname"] . ", " . (($t = $name["title"]) ? $t . " " : "") . $name["firstname"];

        // show banner?
        $banner = $banner_width = $banner_height = false;
        $prfa_set = new ilSetting("prfa");
        if ($prfa_set->get("banner")) {
            $banner = ilWACSignedPath::signFile($a_portfolio->getImageFullPath());
            $banner_width = $prfa_set->get("banner_width");
            $banner_height = $prfa_set->get("banner_height");
            if ($a_export) {
                $banner = basename($banner);
            }
        }

        // profile picture
        $ppic = null;
        if ($a_portfolio->hasProfilePicture()) {
            $ppic = ilObjUser::_getPersonalPicturePath($a_user_id, "xsmall", true, true);
            if ($a_export) {
                $ppic = basename($ppic);
            }
        }

        $a_tpl->resetHeaderBlock(false);
        // $a_tpl->setBackgroundColor($a_portfolio->getBackgroundColor());
        // @todo fix this
        $a_tpl->setBanner($banner);
        $a_tpl->setTitleIcon($ppic);
        $a_tpl->setTitle($a_portfolio->getTitle());
        // $a_tpl->setTitleColor($a_portfolio->getFontColor());
        $a_tpl->setDescription($name);

        // to get rid of locator in portfolio template preview
        $a_tpl->setVariable("LOCATOR", "");

        // :TODO: obsolete?
        // $a_tpl->setBodyClass("std ilExternal ilPortfolio");
    }
            
    public function export(
        bool $a_with_comments = false
    ) : void {
        $port_export = new \ILIAS\Portfolio\Export\PortfolioHtmlExport($this);
        $port_export->includeComments($a_with_comments);
        $zip = $port_export->exportHtml();

        ilFileDelivery::deliverFileLegacy($zip, $this->object->getTitle() . ".zip", '', false, true);
    }
    
    public function exportWithComments() : void
    {
        $this->export(true);
    }
    
    /**
     * Select target portfolio for page(s) copy
     */
    public function copyPageForm(
        ilPropertyFormGUI $a_form = null
    ) : void {
        $prtf_pages = $this->port_request->getPortfolioPageIds();

        if (count($prtf_pages) === 0) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "view");
        } else {
            $this->tabs_gui->activateTab("pages");
            
            if (!$a_form) {
                $a_form = $this->initCopyPageForm();
            }
        
            foreach ($prtf_pages as $page_id) {
                $item = new ilHiddenInputGUI("prtf_pages[]");
                $item->setValue($page_id);
                $a_form->addItem($item);
            }
            
            $this->tpl->setContent($a_form->getHTML());
        }
    }
    
    public function copyPage() : void
    {
        $form = $this->initCopyPageForm();
        if ($form->checkInput()) {
            // existing
            if ($form->getInput("target") === "old") {
                $portfolio_id = $form->getInput("prtf");
            }
            // new
            else {
                $portfolio = new ilObjPortfolio();
                $portfolio->setTitle($form->getInput("title"));
                $portfolio->create();
                $portfolio_id = $portfolio->getId();
            }
            
            // copy page(s)
            $page_ids = $this->port_request->getPortfolioPageIds();
            foreach ($page_ids as $page_id) {
                $source = $this->getPageInstance($page_id);
                $target = $this->getPageInstance(null, $portfolio_id);
                $target->setXMLContent($source->copyXmlContent(true)); // copy mobs
                $target->setType($source->getType());
                $target->setTitle($source->getTitle());
                $target->create();
            }
                
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("prtf_pages_copied"), true);
            $this->ctrl->redirect($this, "view");
        }
        
        $form->setValuesByPost();
        $this->copyPageForm($form);
    }
    
    abstract protected function initCopyPageFormOptions(ilPropertyFormGUI $a_form) : void;
    
    public function initCopyPageForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt("prtf_copy_page"));
        
        $this->initCopyPageFormOptions($form);

        $form->addCommandButton("copyPage", $this->lng->txt("save"));
        $form->addCommandButton("view", $this->lng->txt("cancel"));
        
        return $form;
    }
    
    
    ////
    //// Style related functions
    ////
    
    public function setContentStyleSheet(
        ilGlobalTemplateInterface $a_tpl = null
    ) : void {
        $tpl = $this->tpl;

        if ($a_tpl) {
            $ctpl = $a_tpl;
        } else {
            $ctpl = $tpl;
        }

        $this->content_style_gui->addCss(
            $ctpl,
            $this->object->getRefId(),
            $this->object->getId()
        );
    }
}
