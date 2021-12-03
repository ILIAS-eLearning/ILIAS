<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjBlogGUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 *
 * @ilCtrl_Calls ilObjBlogGUI: ilBlogPostingGUI, ilWorkspaceAccessGUI, ilPortfolioPageGUI
 * @ilCtrl_Calls ilObjBlogGUI: ilInfoScreenGUI, ilNoteGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjBlogGUI: ilPermissionGUI, ilObjectCopyGUI, ilRepositorySearchGUI
 * @ilCtrl_Calls ilObjBlogGUI: ilExportGUI, ilObjStyleSheetGUI, ilBlogExerciseGUI, ilObjNotificationSettingsGUI
 */
class ilObjBlogGUI extends ilObject2GUI implements ilDesktopItemHandling
{
    /**
     * @var ilHelpGUI
     */
    protected $help;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilNavigationHistory
     */
    protected $nav_history;

    /**
     * @var ilMainMenuGUI
     */
    protected $main_menu;

    /**
     * @var ilRbacAdmin
     */
    protected $rbacadmin;

    protected $month; // [string]
    protected $items; // [array]
    protected $keyword; // [string]
    protected $author; // [int]
    protected $month_default; // [bool]

    /**
     * Goto link posting
     * @var int
     */
    protected $gtp;

    /**
     * edit command
     * @var string
     */
    protected $edt;

    /**
     * @var int
     */
    protected $blpg;

    /**
     * @var int
     */
    protected $old_nr;

    /**
     * user page? deprecated?
     * @var int
     */
    protected $ppage;

    /**
     * user page (necessary?)
     * @var int
     */
    protected $user_page;

    /**
     * @var bool		// note: this is currently set in ilPortfolioPageGUI, should use getter/setter
     */
    public $prtf_embed = false;

    /**
     * preview mode (fsc|emb)
     *
     * @var string
     */
    protected $prvm;

    /**
     * @var int
     */
    protected $ntf;

    /**
     * approved posting id
     * @var int
     */
    protected $apid;

    /**
     * @var string
     */
    protected $new_type;

    /**
     * portfolio id (quick editing in portfolio)
     * @var int
     */
    protected $prt_id;

    /**
     * @var \ILIAS\GlobalScreen\ScreenContext\ContextServices
     */
    protected $tool_context;

    public function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
    {
        global $DIC;

        $this->settings = $DIC->settings();
        $this->help = $DIC["ilHelp"];
        $this->tabs = $DIC->tabs();
        $this->nav_history = $DIC["ilNavigationHistory"];
        $this->user = $DIC->user();
        $this->toolbar = $DIC->toolbar();
        $this->tree = $DIC->repositoryTree();
        $this->locator = $DIC["ilLocator"];
        $this->main_menu = $DIC["ilMainMenu"];
        $this->rbacreview = $DIC->rbac()->review();
        $this->rbacadmin = $DIC->rbac()->admin();

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();

        $this->gtp = (int) $_GET["gtp"];
        $this->edt = $_GET["edt"];
        $this->blpg = (int) $_REQUEST["blpg"];
        $this->old_nr = (int) $_GET["old_nr"];
        $this->ppage = (int) $_GET["ppage"];
        $this->user_page = (int) $_REQUEST["user_page"];
        $this->new_type = ilUtil::stripSlashes($_REQUEST["new_type"]);
        $this->prvm = ilUtil::stripSlashes($_REQUEST["prvm"]);
        $this->ntf = (int) $_GET["ntf"];
        $this->apid = (int) $_GET["apid"];
        $this->month = ilUtil::stripSlashes($_REQUEST["bmn"]);
        $this->keyword = ilUtil::stripSlashes($_REQUEST["kwd"]);
        $this->author = (int) $_REQUEST["ath"];
        $this->prt_id = (int) $_REQUEST["prt_id"];

        $this->tool_context = $DIC->globalScreen()->tool()->context();

        parent::__construct($a_id, $a_id_type, $a_parent_node_id);
        
        if ($_REQUEST["blpg"] > 0 && ilBlogPosting::lookupBlogId($_REQUEST["blpg"]) != $this->object->getId()) {
            throw new ilException("Posting ID does not match blog.");
        }

        if ($this->object) {
            // gather postings by month
            $this->items = $this->buildPostingList($this->object->getId());
            if ($this->items) {
                // current month (if none given or empty)
                if (!$this->month || !$this->items[$this->month]) {
                    $this->month = array_keys($this->items);
                    $this->month = array_shift($this->month);
                    $this->month_default = true;
                }
            }

            $ilCtrl->setParameter($this, "bmn", $this->month);
        }
        
        $lng->loadLanguageModule("blog");
        $ilCtrl->saveParameter($this, "prvm");
    }

    public function getType()
    {
        return "blog";
    }

    /**
     * Get items
     *
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    
    protected function initCreationForms($a_new_type)
    {
        $forms = parent::initCreationForms($a_new_type);

        if ($this->id_type == self::WORKSPACE_NODE_ID) {
            unset($forms[self::CFORM_IMPORT]);
            unset($forms[self::CFORM_CLONE]);
        }
        
        return $forms;
    }
    
    protected function afterSave(ilObject $a_new_object)
    {
        $ilCtrl = $this->ctrl;
        
        ilUtil::sendSuccess($this->lng->txt("object_added"), true);
        $ilCtrl->redirect($this, "");
    }
    
    protected function setSettingsSubTabs($a_active)
    {
        global $DIC;

        $tree = $DIC->repositoryTree();
        $access = $DIC->access();

        // general properties
        $this->tabs_gui->addSubTab(
            "properties",
            $this->lng->txt("blog_properties"),
            $this->ctrl->getLinkTarget($this, 'edit')
        );
        
        $this->tabs_gui->addSubTab(
            "style",
            $this->lng->txt("obj_sty"),
            $this->ctrl->getLinkTarget($this, 'editStyleProperties')
        );

        // notification settings for blogs in courses and groups
        if ($this->id_type == self::REPOSITORY_NODE_ID) {
            $grp_ref_id = $tree->checkForParentType($this->object->getRefId(), 'grp');
            $crs_ref_id = $tree->checkForParentType($this->object->getRefId(), 'crs');

            if ((int) $grp_ref_id > 0 || (int) $crs_ref_id > 0) {
                if ($access->checkAccess('write', '', $this->ref_id)) {
                    $this->tabs_gui->addSubTab(
                        'notifications',
                        $this->lng->txt("notifications"),
                        $this->ctrl->getLinkTargetByClass("ilobjnotificationsettingsgui", '')
                    );
                }
            }
        }

        $this->tabs_gui->activateSubTab($a_active);
    }

    protected function initEditCustomForm(ilPropertyFormGUI $a_form)
    {
        $lng = $this->lng;
        $ilSetting = $this->settings;
        $obj_service = $this->getObjectService();
        
        $this->setSettingsSubTabs("properties");
        
        if ($this->id_type == self::REPOSITORY_NODE_ID) {
            $appr = new ilCheckboxInputGUI($lng->txt("blog_enable_approval"), "approval");
            $appr->setInfo($lng->txt("blog_enable_approval_info"));
            $a_form->addItem($appr);
        }
        
        $notes = new ilCheckboxInputGUI($lng->txt("blog_enable_notes"), "notes");
        $a_form->addItem($notes);
                
        if ($ilSetting->get('enable_global_profiles')) {
            $rss = new ilCheckboxInputGUI($lng->txt("blog_enable_rss"), "rss");
            $rss->setInfo($lng->txt("blog_enable_rss_info"));
            $a_form->addItem($rss);
        }
        
        
        // navigation
        
        $nav = new ilFormSectionHeaderGUI();
        $nav->setTitle($lng->txt("blog_settings_navigation"));
        $a_form->addItem($nav);
        
        $nav_mode = new ilRadioGroupInputGUI($lng->txt("blog_nav_mode"), "nav");
        $nav_mode->setRequired(true);
        $a_form->addItem($nav_mode);
        
        $opt = new ilRadioOption($lng->txt("blog_nav_mode_month_list"), ilObjBlog::NAV_MODE_LIST);
        $opt->setInfo($lng->txt("blog_nav_mode_month_list_info"));
        $nav_mode->addOption($opt);
        

        $mon_num = new ilNumberInputGUI($lng->txt("blog_nav_mode_month_list_num_month"), "nav_list_mon");
        $mon_num->setInfo($lng->txt("blog_nav_mode_month_list_num_month_info"));
        $mon_num->setSize(3);
        $mon_num->setMinValue(1);
        $opt->addSubItem($mon_num);

        $detail_num = new ilNumberInputGUI($lng->txt("blog_nav_mode_month_list_num_month_with_post"), "nav_list_mon_with_post");
        $detail_num->setInfo($lng->txt("blog_nav_mode_month_list_num_month_with_post_info"));
        //$detail_num->setRequired(true);
        $detail_num->setSize(3);
        //$detail_num->setMinValue(0);
        $opt->addSubItem($detail_num);

        $opt = new ilRadioOption($lng->txt("blog_nav_mode_month_single"), ilObjBlog::NAV_MODE_MONTH);
        $opt->setInfo($lng->txt("blog_nav_mode_month_single_info"));
        $nav_mode->addOption($opt);
                    
        $order_options = array();
        if ($this->object->getOrder()) {
            foreach ($this->object->getOrder() as $item) {
                $order_options[] = $lng->txt("blog_" . $item);
            }
        }
        
        if (!in_array($lng->txt("blog_navigation"), $order_options)) {
            $order_options[] = $lng->txt("blog_navigation");
        }
        
        if ($this->id_type == self::REPOSITORY_NODE_ID) {
            if (!in_array($lng->txt("blog_authors"), $order_options)) {
                $order_options[] = $lng->txt("blog_authors");
            }
            
            $auth = new ilCheckboxInputGUI($lng->txt("blog_enable_nav_authors"), "nav_authors");
            $auth->setInfo($lng->txt("blog_enable_nav_authors_info"));
            $a_form->addItem($auth);
        }
                
        $keyw = new ilCheckboxInputGUI($lng->txt("blog_enable_keywords"), "keywords");
        $keyw->setInfo($lng->txt("blog_enable_keywords_info"));
        $a_form->addItem($keyw);
        
        if (!in_array($lng->txt("blog_keywords"), $order_options)) {
            $order_options[] = $lng->txt("blog_keywords");
        }
        
        $order = new ilNonEditableValueGUI($lng->txt("blog_nav_sortorder"), "order");
        $order->setMultiValues($order_options);
        $order->setValue(array_shift($order_options));
        $order->setMulti(true, true, false);
        $a_form->addItem($order);
        
        
        // presentation (frame)
        
        $pres = new ilFormSectionHeaderGUI();
        $pres->setTitle($lng->txt("blog_presentation_frame"));
        $a_form->addItem($pres);

        if ($this->id_type == self::REPOSITORY_NODE_ID) {
            $obj_service->commonSettings()->legacyForm($a_form, $this->object)->addTileImage();
        }

        $ppic = new ilCheckboxInputGUI($lng->txt("blog_profile_picture"), "ppic");
        $a_form->addItem($ppic);
        
        if ($this->id_type == self::REPOSITORY_NODE_ID) {
            $ppic->setInfo($lng->txt("blog_profile_picture_repository_info"));
        }
        
        $blga_set = new ilSetting("blga");
        if ($blga_set->get("banner")) {
            ilFileInputGUI::setPersonalWorkspaceQuotaCheck(true);
            
            $dimensions = " (" . $blga_set->get("banner_width") . "x" .
                $blga_set->get("banner_height") . ")";
            
            $img = new ilImageFileInputGUI($lng->txt("blog_banner") . $dimensions, "banner");
            $a_form->addItem($img);
            
            // show existing file
            $file = $this->object->getImageFullPath(true);
            if ($file) {
                $img->setImage($file);
            }
        }
        
        /* #15000
        $bg_color = new ilColorPickerInputGUI($lng->txt("blog_background_color"), "bg_color");
        $a_form->addItem($bg_color);

        $font_color = new ilColorPickerInputGUI($lng->txt("blog_font_color"), "font_color");
        $a_form->addItem($font_color);
        */
        
        // presentation (overview)
        
        $list = new ilFormSectionHeaderGUI();
        $list->setTitle($lng->txt("blog_presentation_overview"));
        $a_form->addItem($list);


        $post_num = new ilNumberInputGUI($lng->txt("blog_list_num_postings"), "ov_list_post_num");
        $post_num->setInfo($lng->txt("blog_list_num_postings_info"));
        $post_num->setSize(3);
        $post_num->setMinValue(1);
        $post_num->setRequired(true);
        $a_form->addItem($post_num);
        
        $abs_shorten = new ilCheckboxInputGUI($lng->txt("blog_abstract_shorten"), "abss");
        $a_form->addItem($abs_shorten);
        
        $abs_shorten_len = new ilNumberInputGUI($lng->txt("blog_abstract_shorten_length"), "abssl");
        $abs_shorten_len->setSize(5);
        $abs_shorten_len->setRequired(true);
        $abs_shorten_len->setSuffix($lng->txt("blog_abstract_shorten_characters"));
        $abs_shorten_len->setMinValue(50, true);
        $abs_shorten->addSubItem($abs_shorten_len);
        
        $abs_img = new ilCheckboxInputGUI($lng->txt("blog_abstract_image"), "absi");
        $abs_img->setInfo($lng->txt("blog_abstract_image_info"));
        $a_form->addItem($abs_img);
        
        $abs_img_width = new ilNumberInputGUI($lng->txt("blog_abstract_image_width"), "absiw");
        $abs_img_width->setSize(5);
        $abs_img_width->setRequired(true);
        $abs_img_width->setSuffix($lng->txt("blog_abstract_image_pixels"));
        $abs_img_width->setMinValue(32, true);
        $abs_img->addSubItem($abs_img_width);
        
        $abs_img_height = new ilNumberInputGUI($lng->txt("blog_abstract_image_height"), "absih");
        $abs_img_height->setSize(5);
        $abs_img_height->setRequired(true);
        $abs_img_height->setSuffix($lng->txt("blog_abstract_image_pixels"));
        $abs_img_height->setMinValue(32, true);
        $abs_img->addSubItem($abs_img_height);
    }

    protected function getEditFormCustomValues(array &$a_values)
    {
        if ($this->id_type == self::REPOSITORY_NODE_ID) {
            $a_values["approval"] = $this->object->hasApproval();
            $a_values["nav_authors"] = $this->object->hasAuthors();
        }
        $a_values["keywords"] = $this->object->hasKeywords();
        $a_values["notes"] = $this->object->getNotesStatus();
        $a_values["ppic"] = $this->object->hasProfilePicture();
        /*
        $a_values["bg_color"] = $this->object->getBackgroundColor();
        $a_values["font_color"] = $this->object->getFontColor();
        */
        $a_values["banner"] = $this->object->getImage();
        $a_values["rss"] = $this->object->hasRSS();
        $a_values["abss"] = $this->object->hasAbstractShorten();
        $a_values["absi"] = $this->object->hasAbstractImage();
        $a_values["nav"] = $this->object->getNavMode();
        $a_values["nav_list_mon_with_post"] = $this->object->getNavModeListMonthsWithPostings();
        $a_values["nav_list_mon"] = $this->object->getNavModeListMonths();
        $a_values["ov_list_post_num"] = $this->object->getOverviewPostings();
        
        // #13420
        $a_values["abssl"] = $this->object->getAbstractShortenLength() ? $this->object->getAbstractShortenLength() : ilObjBlog::ABSTRACT_DEFAULT_SHORTEN_LENGTH;
        $a_values["absiw"] = $this->object->getAbstractImageWidth() ? $this->object->getAbstractImageWidth() : ilObjBlog::ABSTRACT_DEFAULT_IMAGE_WIDTH;
        $a_values["absih"] = $this->object->getAbstractImageHeight() ? $this->object->getAbstractImageHeight() : ilObjBlog::ABSTRACT_DEFAULT_IMAGE_HEIGHT;
    }

    protected function updateCustom(ilPropertyFormGUI $a_form)
    {
        $lng = $this->lng;
        $obj_service = $this->getObjectService();
        
        if ($this->id_type == self::REPOSITORY_NODE_ID) {
            $this->object->setApproval($a_form->getInput("approval"));
            $this->object->setAuthors($a_form->getInput("nav_authors"));
            $obj_service->commonSettings()->legacyForm($a_form, $this->object)->saveTileImage();
        }
        $this->object->setKeywords($a_form->getInput("keywords"));
        $this->object->setNotesStatus($a_form->getInput("notes"));
        $this->object->setProfilePicture($a_form->getInput("ppic"));
        /*
        $this->object->setBackgroundColor($a_form->getInput("bg_color"));
        $this->object->setFontColor($a_form->getInput("font_color"));
        */
        $this->object->setRSS($a_form->getInput("rss"));
        $this->object->setAbstractShorten($a_form->getInput("abss"));
        $this->object->setAbstractShortenLength($a_form->getInput("abssl"));
        $this->object->setAbstractImage($a_form->getInput("absi"));
        $this->object->setAbstractImageWidth($a_form->getInput("absiw"));
        $this->object->setAbstractImageHeight($a_form->getInput("absih"));
        $this->object->setNavMode($a_form->getInput("nav"));
        $this->object->setNavModeListMonthsWithPostings($a_form->getInput("nav_list_mon_with_post"));
        $this->object->setNavModeListMonths($a_form->getInput("nav_list_mon"));
        $this->object->setOverviewPostings($a_form->getInput("ov_list_post_num"));

        $order = $a_form->getInput("order");
        foreach ($order as $idx => $value) {
            if ($value == $lng->txt("blog_navigation")) {
                $order[$idx] = "navigation";
            } elseif ($value == $lng->txt("blog_keywords")) {
                $order[$idx] = "keywords";
            } else {
                $order[$idx] = "authors";
            }
        }
        $this->object->setOrder($order);
        // banner field is optional
        $banner = $a_form->getItemByPostVar("banner");
        if ($banner) {
            if ($_FILES["banner"]["tmp_name"]) {
                $this->object->uploadImage($_FILES["banner"]);
            } elseif ($banner->getDeletionFlag()) {
                $this->object->deleteImage();
            }
        }
    }

    public function setTabs()
    {
        $lng = $this->lng;
        $ilHelp = $this->help;

        if ($this->id_type == self::WORKSPACE_NODE_ID) {
            $this->ctrl->setParameter($this, "wsp_id", $this->node_id);
        }
        
        $ilHelp->setScreenIdComponent("blog");

        if ($this->checkPermissionBool("read")) {
            $this->tabs_gui->addTab(
                "content",
                $lng->txt("content"),
                $this->ctrl->getLinkTarget($this, "")
            );
        }
        if ($this->checkPermissionBool("read") && !$this->prtf_embed) {
            $this->tabs_gui->addTab(
                "id_info",
                $lng->txt("info_short"),
                $this->ctrl->getLinkTargetByClass(array("ilobjbloggui", "ilinfoscreengui"), "showSummary")
            );
        }
        
        if ($this->checkPermissionBool("write")) {
            $this->tabs_gui->addTab(
                "settings",
                $lng->txt("settings"),
                $this->ctrl->getLinkTarget($this, "edit")
            );

            if (!$this->prtf_embed) {
                if ($this->id_type == self::REPOSITORY_NODE_ID) {
                    $this->tabs_gui->addTab(
                        "contributors",
                        $lng->txt("blog_contributors"),
                        $this->ctrl->getLinkTarget($this, "contributors")
                    );
                }

                if ($this->id_type == self::REPOSITORY_NODE_ID) {
                    $this->tabs_gui->addTab(
                        "export",
                        $lng->txt("export"),
                        $this->ctrl->getLinkTargetByClass("ilexportgui", "")
                    );
                }
            }
        }

        if (!$this->prtf_embed) {
            if ($this->mayContribute()) {
                $this->tabs_gui->addNonTabbedLink(
                    "preview",
                    $lng->txt("blog_preview"),
                    $this->ctrl->getLinkTarget($this, "preview")
                );
            }
            parent::setTabs();
        }
    }

    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ilNavigationHistory = $this->nav_history;

        $this->triggerAssignmentTool();

        // goto link to blog posting
        if ($this->gtp > 0) {
            $page_id = $this->gtp;
            if (ilBlogPosting::exists($this->object_id, $page_id)) {
                // #12312
                $ilCtrl->setCmdClass("ilblogpostinggui");
                $ilCtrl->setParameterByClass("ilblogpostinggui", "blpg", $page_id);
                if ($this->edt == "edit") {
                    $ilCtrl->redirectByClass("ilblogpostinggui", "edit");
                } else {
                    $ilCtrl->redirectByClass("ilblogpostinggui", "previewFullscreen");
                }
            } else {
                ilUtil::sendFailure($lng->txt("blog_posting_not_found"));
            }
        }
        
        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd();
        
        if ($this->id_type == self::REPOSITORY_NODE_ID) {
            // add entry to navigation history
            if (!$this->getCreationMode() &&
                $this->getAccessHandler()->checkAccess("read", "", $this->node_id)) {
                // see #22067
                $link = $ilCtrl->getLinkTargetByClass(["ilrepositorygui", "ilObjBlogGUI"], "preview");
                $ilNavigationHistory->addItem($this->node_id, $link, "blog");
            }
        }
        
        switch ($next_class) {
            case 'ilblogpostinggui':
                if (!$this->prtf_embed) {
                    $tpl->loadStandardTemplate();
                }

                if (!$this->checkPermissionBool("read") && !$this->prtf_embed) {
                    ilUtil::sendInfo($lng->txt("no_permission"));
                    return;
                }

                // #9680
                if ($this->id_type == self::REPOSITORY_NODE_ID) {
                    $this->setLocator();
                } else {
                    ilFileInputGUI::setPersonalWorkspaceQuotaCheck(true);
                }
                $ilTabs->setBackTarget(
                    $lng->txt("back"),
                    $ilCtrl->getLinkTarget($this, "")
                );

                $style_sheet_id = ilObjStyleSheet::getEffectiveContentStyleId(
                    $this->object->getStyleSheetId(),
                    "blog"
                );

                $bpost_gui = new ilBlogPostingGUI(
                    $this->node_id,
                    $this->getAccessHandler(),
                    $this->blpg,
                    $this->old_nr,
                    ($this->object->getNotesStatus() && !$this->disable_notes),
                    $this->mayEditPosting($this->blpg),
                    $style_sheet_id
                );

                // keep preview mode through notes gui (has its own commands)
                switch ($cmd) {
                    // blog preview
                    case "previewFullscreen":
                        $ilCtrl->setParameter($this, "prvm", "fsc");
                        break;

                    // blog in portfolio
                    case "previewEmbedded":
                        $ilCtrl->setParameter($this, "prvm", "emb");
                        break;

                    // edit
                    default:
                        $this->setContentStyleSheet();
                        

                        if (!$this->prtf_embed) {
                            $this->ctrl->setParameterByClass("ilblogpostinggui", "blpg", $this->blpg);
                            $this->tabs_gui->addNonTabbedLink(
                                "preview",
                                $lng->txt("blog_preview"),
                                $this->ctrl->getLinkTargetByClass("ilblogpostinggui", "previewFullscreen")
                            );
                            $this->ctrl->setParameterByClass("ilblogpostinggui", "blpg", "");
                        } else {
                            $this->ctrl->setParameterByClass("ilobjportfoliogui", "user_page", $this->ppage);
                            $this->tabs_gui->addNonTabbedLink(
                                "preview",
                                $lng->txt("blog_preview"),
                                $this->ctrl->getLinkTargetByClass("ilobjportfoliogui", "preview")
                            );
                            $this->ctrl->setParameterByClass("ilobjportfoliogui", "user_page", "");
                        }
                        break;
                }

                // keep preview mode through notes gui
                if ($this->prvm) {
                    $cmd = "preview" . (($this->prvm == "fsc") ? "Fullscreen" : "Embedded");
                }
                $ret = $ilCtrl->forwardCommand($bpost_gui); // needs prvm setParameter call
                if (in_array($cmd, array("previewFullscreen", "previewEmbedded"))) {
                    // resets prvm parameter
                    $this->renderToolbarNavigation($this->items, true);
                }
                if ($ret != "") {

                    // $is_owner = $this->object->getOwner() == $ilUser->getId();
                    $is_owner = $this->mayContribute();
                    $is_active = $bpost_gui->getBlogPosting()->getActive();
                    
                    // do not show inactive postings
                    if (($cmd == "previewFullscreen" || $cmd == "previewEmbedded")
                        && !$is_owner && !$is_active) {
                        $this->ctrl->redirect($this, "preview");
                    }
                    
                    switch ($cmd) {
                        // blog preview
                        case "previewFullscreen":
                            $this->addHeaderActionForCommand($cmd);
                            $this->filterInactivePostings();
                            $nav = $this->renderNavigation("preview", $cmd);
                            $this->renderFullScreen($ret, $nav);
                            break;
                            
                        // blog in portfolio
                        case "previewEmbedded":
                            $this->filterInactivePostings();
                            $nav = $this->renderNavigation("gethtml", $cmd);
                            return $this->buildEmbedded($ret, $nav);
                        
                        // ilias/editor
                        default:
                            // infos about draft status / snippet
                            $info = array();
                            if (!$is_active) {
                                // single author blog (owner) in personal workspace
                                if ($this->id_type == self::WORKSPACE_NODE_ID) {
                                    $info[] = $lng->txt("blog_draft_info");
                                } else {
                                    $info[] = $lng->txt("blog_draft_info_contributors");
                                }
                            }
                            if ($cmd != "history" && $is_active && empty($info)) {
                                $info[] = $lng->txt("blog_new_posting_info");
                                $public_action = true;
                            }
                            if ($this->object->hasApproval() && !$bpost_gui->getBlogPosting()->isApproved()) {
                                // #9737
                                $info[] = $lng->txt("blog_posting_edit_approval_info");
                            }
                            //TODO can we get rid of this conditional? hasMessage belongs to the old ilBlogGlobalTemplate class
                            //if(sizeof($info) && !$tpl->hasMessage("info")) // #15121
                            //{
                                if ($public_action) {
                                    ilUtil::sendSuccess(implode("<br />", $info));
                                } else {
                                    ilUtil::sendInfo(implode("<br />", $info));
                                }
                            //}
                            // revert to edit cmd to avoid confusion
                            $this->addHeaderActionForCommand("render");
                            $tpl->setContent($ret);
                            $nav = $this->renderNavigation("render", $cmd, null, $is_owner);
                            $tpl->setRightContent($nav);
                            break;
                    }
                }
                break;
                
            case "ilinfoscreengui":
                $this->prepareOutput();
                $this->addHeaderActionForCommand("render");
                $this->infoScreenForward();
                break;
            
            case "ilnotegui":
                $this->preview();
                break;
            
            case "ilcommonactiondispatchergui":
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;
            
            case "ilpermissiongui":
                $this->prepareOutput();
                $ilTabs->activateTab("id_permissions");
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;
            
            case "ilobjectcopygui":
                $this->prepareOutput();
                $cp = new ilObjectCopyGUI($this);
                $cp->setType("blog");
                $this->ctrl->forwardCommand($cp);
                break;
            
            case 'ilrepositorysearchgui':
                $this->prepareOutput();
                $ilTabs->activateTab("contributors");
                $rep_search = new ilRepositorySearchGUI();
                $rep_search->setTitle($this->lng->txt("blog_add_contributor"));
                $rep_search->setCallback($this, 'addContributor', $this->object->getAllLocalRoles($this->node_id));
                $this->ctrl->setReturn($this, 'contributors');
                $ret = &$this->ctrl->forwardCommand($rep_search);
                break;
            
            case 'ilexportgui':
                $this->prepareOutput();
                $ilTabs->activateTab("export");
                $exp_gui = new ilExportGUI($this);
                $exp_gui->addFormat("xml");
                $exp_gui->addFormat("html", null, $this, "buildExportFile"); // #13419
                $ret = $ilCtrl->forwardCommand($exp_gui);
                break;
            
            case "ilobjstylesheetgui":
                $this->ctrl->setReturn($this, "editStyleProperties");
                $style_gui = new ilObjStyleSheetGUI("", $this->object->getStyleSheetId(), false, false);
                $style_gui->omitLocator();
                if ($cmd == "create" || $this->new_type == "sty") {
                    $style_gui->setCreationMode(true);
                }

                if ($cmd == "confirmedDelete") {
                    $this->object->setStyleSheetId(0);
                    $this->object->update();
                }

                $ret = $this->ctrl->forwardCommand($style_gui);

                if ($cmd == "save" || $cmd == "copyStyle" || $cmd == "importStyle") {
                    $style_id = $ret;
                    $this->object->setStyleSheetId($style_id);
                    $this->object->update();
                    $this->ctrl->redirectByClass("ilobjstylesheetgui", "edit");
                }
                break;
                
            case "ilblogexercisegui":
                $this->ctrl->setReturn($this, "render");
                $gui = new ilBlogExerciseGUI($this->node_id);
                $this->ctrl->forwardCommand($gui);
                break;

            case 'ilobjnotificationsettingsgui':
                $this->prepareOutput();
                $ilTabs->activateTab("settings");
                $this->setSettingsSubTabs("notifications");
                $gui = new ilObjNotificationSettingsGUI($this->object->getRefId());
                $this->ctrl->forwardCommand($gui);
                break;

            default:
                if ($cmd != "gethtml") {
                    // desktop item handling, must be toggled before header action
                    if ($cmd == "addToDesk" || $cmd == "removeFromDesk") {
                        $this->{$cmd . "Object"}();
                        if ($this->prvm) {
                            $cmd = "preview";
                        } else {
                            $cmd = "render";
                        }
                        $ilCtrl->setCmd($cmd);
                    }
                    $this->addHeaderActionForCommand($cmd);
                }
                if (!$this->prtf_embed) {
                    return parent::executeCommand();
                } else {
                    $this->setTabs();

                    if (!$cmd) {
                        $cmd = "render";
                    }
                    return $this->$cmd();
                }
        }
        
        return true;
    }

    /**
     * Trigger assignment tool
     *
     * @param
     */
    protected function triggerAssignmentTool()
    {
        $be = new ilBlogExercise($this->node_id);
        $be_gui = new ilBlogExerciseGUI($this->node_id);
        $assignments = $be->getAssignmentsOfBlog();
        if (count($assignments) > 0) {
            $ass_ids = array_map(function ($i) {
                return $i["ass_id"];
            }, $assignments);
            $this->tool_context->current()->addAdditionalData(ilExerciseGSToolProvider::SHOW_EXC_ASSIGNMENT_INFO, true);
            $this->tool_context->current()->addAdditionalData(ilExerciseGSToolProvider::EXC_ASS_IDS, $ass_ids);
            $this->tool_context->current()->addAdditionalData(
                ilExerciseGSToolProvider::EXC_ASS_BUTTONS,
                $be_gui->getActionButtons()
            );
        }
    }

    /**
    * this one is called from the info button in the repository
    * not very nice to set cmdClass/Cmd manually, if everything
    * works through ilCtrl in the future this may be changed
    */
    public function infoScreen()
    {
        $this->ctrl->setCmd("showSummary");
        $this->ctrl->setCmdClass("ilinfoscreengui");
        $this->infoScreenForward();
    }
    
    /**
    * show information screen
    */
    public function infoScreenForward()
    {
        $ilTabs = $this->tabs;
        
        $ilTabs->activateTab("id_info");

        $this->checkPermission("visible");

        $info = new ilInfoScreenGUI($this);

        if ($this->id_type != self::WORKSPACE_NODE_ID) {
            $info->enablePrivateNotes();
        }
        
        if ($this->checkPermissionBool("read")) {
            $info->enableNews();
        }

        // no news editing for files, just notifications
        $info->enableNewsEditing(false);
        if ($this->checkPermissionBool("write")) {
            $news_set = new ilSetting("news");
            $enable_internal_rss = $news_set->get("enable_rss_for_internal");
            
            if ($enable_internal_rss) {
                $info->setBlockProperty("news", "settings", true);
                $info->setBlockProperty("news", "public_notifications_option", true);
            }
        }
        
        // standard meta data
        $info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());
        
        if ($this->id_type == self::WORKSPACE_NODE_ID) {
            $info->addProperty($this->lng->txt("perma_link"), $this->getPermanentLinkWidget());
        }
        
        $this->ctrl->forwardCommand($info);
    }
    
    /**
     * Create new posting
     */
    public function createPosting()
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        
        $title = trim(ilUtil::stripSlashes($_POST["title"]));
        if ($title) {
            // create new posting
            $posting = new ilBlogPosting();
            $posting->setTitle($title);
            $posting->setBlogId($this->object->getId());
            $posting->setActive(false);
            $posting->setAuthor($ilUser->getId());
            $posting->create();
            
            // switch month list to current month (will include new posting)
            $ilCtrl->setParameter($this, "bmn", date("Y-m"));
            
            $ilCtrl->setParameterByClass("ilblogpostinggui", "blpg", $posting->getId());
            $ilCtrl->redirectByClass("ilblogpostinggui", "edit");
        } else {
            ilUtil::sendFailure($this->lng->txt("msg_no_title"), true);
            $ilCtrl->redirect($this, "render");
        }
    }
    
    // --- ObjectGUI End
    
    
    /**
     * Render object context
     */
    public function render()
    {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilToolbar = new ilToolbarGUI();
        $ilUser = $this->user;
        $tree = $this->tree;

        if (!$this->checkPermissionBool("read")) {
            ilUtil::sendInfo($lng->txt("no_permission"));
            return;
        }

        $ilTabs->activateTab("content");
        
        // toolbar
        if ($this->mayContribute()) {
            $ilToolbar->setFormAction($ilCtrl->getFormAction($this, "createPosting"));

            $title = new ilTextInputGUI($lng->txt("title"), "title");
            $ilToolbar->addStickyItem($title, $lng->txt("title"));
            
            $button = ilSubmitButton::getInstance();
            $button->setCaption("blog_add_posting");
            $button->setCommand("createPosting");
            $ilToolbar->addStickyItem($button);
            
            // #18763
            $first = array_shift((array_keys($this->items)));
            if ($first != $this->month) {
                $ilToolbar->addSeparator();
                                
                $ilCtrl->setParameter($this, "bmn", $first);
                $url = $ilCtrl->getLinkTarget($this, "");
                $ilCtrl->setParameter($this, "bmn", $this->month);
                
                $button = ilLinkButton::getInstance();
                $button->setCaption("blog_show_latest");
                $button->setUrl($url);
                $ilToolbar->addButtonInstance($button);
            }
        }
                                
        // $is_owner = ($this->object->getOwner() == $ilUser->getId());
        $is_owner = $this->mayContribute();
        
        $list_items = $this->getListItems($is_owner);
        
        $list = $nav = "";
        if ($list_items) {
            $list = $this->renderList($list_items, "preview", null, $is_owner);
            $nav = $this->renderNavigation("render", "preview", null, $is_owner);
        }
        
        $this->setContentStyleSheet();
                    
        $tpl->setContent($message . $ilToolbar->getHTML() . $list);
        $tpl->setRightContent($nav);
    }

    /**
     * Return embeddable HTML chunk
     *
     * @return string
     */
    public function getHTML()
    {
        $ilUser = $this->user;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        // getHTML() is called by ilRepositoryGUI::show()
        if ($this->id_type == self::REPOSITORY_NODE_ID) {
            return;
        }
        
        // there is no way to do a permissions check here, we have no wsp
        
        $this->filterInactivePostings();
        
        $list_items = $this->getListItems();
        
        $list = $nav = "";
        if ($list_items) {
            $list = $this->renderList($list_items, "previewEmbedded");
            $nav = $this->renderNavigation("gethtml", "previewEmbedded");
        }
        // quick editing in portfolio
        elseif ($this->prt_id) {
            // see renderList()
            if (ilObject::_lookupOwner($this->prt_id) == $ilUser->getId()) {
                // see ilPortfolioPageTableGUI::fillRow()
                $ilCtrl->setParameterByClass("ilportfoliopagegui", "ppage", $this->user_page);
                $link = $ilCtrl->getLinkTargetByClass(array("ilportfoliopagegui", "ilobjbloggui"), "render");
                $ilCtrl->setParameterByClass("ilportfoliopagegui", "ppage", "");
                
                $btn = ilLinkButton::getInstance();
                $btn->setCaption(sprintf($lng->txt("prtf_edit_embedded_blog"), $this->object->getTitle()), false);
                $btn->setUrl($link);
                $btn->setPrimary(true);
                
                $list = $btn->render();
            }
        }
        
        return $this->buildEmbedded($list, $nav);
    }
    
    /**
     * Filter blog postings by month, keyword or author
     *
     * @param bool $a_show_inactive
     * @return array
     */
    protected function getListItems($a_show_inactive = false)
    {
        if ($this->author) {
            $list_items = array();
            foreach ($this->items as $month => $items) {
                foreach ($items as $id => $item) {
                    if ($item["author"] == $this->author ||
                        (is_array($item["editors"]) && in_array($this->author, $item["editors"]))) {
                        $list_items[$id] = $item;
                    }
                }
            }
        } elseif ($this->keyword) {
            $list_items = $this->filterItemsByKeyword($this->items, $this->keyword);
        } else {
            $max = $this->object->getOverviewPostings();
            if ($this->month_default && $max) {
                $list_items = array();
                foreach ($this->items as $month => $postings) {
                    foreach ($postings as $id => $item) {
                        if (!$a_show_inactive &&
                            !ilBlogPosting::_lookupActive($id, "blp")) {
                            continue;
                        }
                        $list_items[$id] = $item;
                        
                        if (sizeof($list_items) >= $max) {
                            break(2);
                        }
                    }
                }
            } else {
                $list_items = $this->items[$this->month];
            }
        }
        return $list_items;
    }
    
    /**
     * Render fullscreen presentation
     */
    public function preview()
    {
        global $DIC;
        
        $lng = $DIC->language();
        $toolbar = $DIC->toolbar();
        
        if (!$this->checkPermissionBool("read")) {
            ilUtil::sendInfo($lng->txt("no_permission"));
            return;
        }

        $this->filterInactivePostings();
        
        $list_items = $this->getListItems();
        
        $list = $nav = "";
        if ($list_items) {
            $list = $this->renderList($list_items, "previewFullscreen");
            $nav = $this->renderNavigation("preview", "previewFullscreen");
            $this->renderToolbarNavigation($this->items);
            $list .= $toolbar->getHTML();
        }
                        
        $this->renderFullScreen($list, $nav);
    }
        
    /**
     * Build and deliver export file
     */
    public function export()
    {
        $zip = $this->buildExportFile();
        
        ilUtil::deliverFile($zip, $this->object->getTitle() . ".zip", '', false, true);
    }
    
    
    // --- helper functions
    
    /**
     * Combine content (list/posting) and navigation to html chunk
     *
     * @param string $a_content
     * @param string $a_nav
     * @return string
     */
    protected function buildEmbedded($a_content, $a_nav)
    {
        $wtpl = new ilTemplate("tpl.blog_embedded.html", true, true, "Modules/Blog");
        $wtpl->setVariable("VAL_LIST", $a_content);
        $wtpl->setVariable("VAL_NAVIGATION", $a_nav);
        return $wtpl->get();
    }
    
    /**
     * Build fullscreen context
     *
     * @param string $a_content
     * @param string $a_navigation
     */
    public function renderFullScreen($a_content, $a_navigation)
    {
        $tpl = $this->tpl;
        $ilUser = $this->user;
        $ilTabs = $this->tabs;
        $ilLocator = $this->locator;

        $owner = $this->object->getOwner();
        
        $ilTabs->clearTargets();
        $ilLocator->clearItems();
        $tpl->setLocator();
        
        $back_caption = "";
        
        // back (edit)
        if ($owner == $ilUser->getId()) {
            // from shared/deeplink
            if ($this->id_type == self::WORKSPACE_NODE_ID) {
                $back = "ilias.php?baseClass=ilDashboardGUI&cmd=jumpToWorkspace&wsp_id=" . $this->node_id;
            }
            // from editor (#10073)
            elseif ($this->mayContribute()) {
                $this->ctrl->setParameter($this, "prvm", "");
                if ($this->blpg == 0) {
                    $back = $this->ctrl->getLinkTarget($this, "");
                } else {
                    $this->ctrl->setParameterByClass("ilblogpostinggui", "bmn", $this->month);
                    $this->ctrl->setParameterByClass("ilblogpostinggui", "blpg", $this->blpg);
                    $back = $this->ctrl->getLinkTargetByClass("ilblogpostinggui", "preview");
                }
                $this->ctrl->setParameter($this, "prvm", $this->prvm);
            }
            
            $back_caption = $this->lng->txt("blog_back_to_blog_owner");
        }
        // back
        elseif ($ilUser->getId() && $ilUser->getId() != ANONYMOUS_USER_ID) {
            // workspace (always shared)
            if ($this->id_type == self::WORKSPACE_NODE_ID) {
                $back = "ilias.php?baseClass=ilDashboardGUI&cmd=jumpToWorkspace&dsh=" . $owner;
            }
            // contributor
            elseif ($this->mayContribute()) {
                $back = $this->ctrl->getLinkTarget($this, "");
                $back_caption = $this->lng->txt("blog_back_to_blog_owner");
            }
            // listgui / parent container
            else {
                $tree = $this->tree;
                $parent_id = $tree->getParentId($this->node_id);
                $back = ilLink::_getStaticLink($parent_id);
            }
        }
        
        $ilMainMenu = $this->main_menu;
        $ilMainMenu->setMode(ilMainMenuGUI::MODE_TOPBAR_ONLY);
        $ilMainMenu->setTopBarBack($back, $back_caption);
        
        $this->renderFullscreenHeader($tpl, $owner);
            
        // #13564
        $this->ctrl->setParameter($this, "bmn", "");
        //$tpl->setTitleUrl($this->ctrl->getLinkTarget($this, "preview"));
        $this->ctrl->setParameter($this, "bmn", $this->month);
                
        $this->setContentStyleSheet();
    
        // content
        $tpl->setContent($a_content);
        $tpl->setRightContent($a_navigation);
    }
    
    /**
     * Render banner, user name
     *
     * @param object  $a_tpl
     * @param int $a_user_id
     * @param bool $a_export_path
     */
    public function renderFullscreenHeader($a_tpl, $a_user_id, $a_export = false)
    {
        $ilUser = $this->user;
        
        if (!$a_export) {
            ilChangeEvent::_recordReadEvent(
                $this->object->getType(),
                $this->node_id,
                $this->object->getId(),
                $ilUser->getId()
            );
        }
        
        // repository blogs are multi-author
        $name = null;
        if ($this->id_type != self::REPOSITORY_NODE_ID) {
            $name = ilObjUser::_lookupName($a_user_id);
            $name = $name["lastname"] . ", " . ($t = $name["title"] ? $t . " " : "") . $name["firstname"];
        }
        
        // show banner?
        $banner = false;
        $blga_set = new ilSetting("blga");
        if ($blga_set->get("banner")) {
            $banner = ilWACSignedPath::signFile($this->object->getImageFullPath());
            $banner_width = $blga_set->get("banner_width");
            $banner_height = $blga_set->get("banner_height");
            if ($a_export) {
                $banner = basename($banner);
            }
        }
        
        $ppic = null;
        if ($this->object->hasProfilePicture()) {
            // repository (multi-user)
            if ($this->id_type == self::REPOSITORY_NODE_ID) {
                // #15030
                if ($this->blpg > 0 && !$a_export) {
                    $post = new ilBlogPosting($this->blpg);
                    $author_id = $post->getAuthor();
                    if ($author_id) {
                        $ppic = ilObjUser::_getPersonalPicturePath($author_id, "xsmall", true, true);
                        
                        $name = ilObjUser::_lookupName($author_id);
                        $name = $name["lastname"] . ", " . ($t = $name["title"] ? $t . " " : "") . $name["firstname"];
                    }
                }
            }
            // workspace (author == owner)
            else {
                $ppic = ilObjUser::_getPersonalPicturePath($a_user_id, "xsmall", true, true);
                if ($a_export) {
                    $ppic = basename($ppic);
                }
            }
        }
        
        $a_tpl->resetHeaderBlock(false);
        $a_tpl->setBanner($banner, $banner_width, $banner_height, $a_export);
        $a_tpl->setTitleIcon($ppic);
        $a_tpl->setTitle($this->object->getTitle());
        $a_tpl->setDescription($name);
        
        // to get rid of locator in repository preview
        $a_tpl->setVariable("LOCATOR", "");
    }
    
    /**
     * Gather all blog postings
     *
     * @param int $a_obj_id
     * @return array
     */
    protected function buildPostingList($a_obj_id)
    {
        $author_found = false;
        
        $items = array();
        foreach (ilBlogPosting::getAllPostings($a_obj_id) as $posting) {
            if ($this->author &&
                ($posting["author"] == $this->author ||
                (is_array($posting["editors"]) && in_array($this->author, $posting["editors"])))) {
                $author_found = true;
            }
            
            $month = substr($posting["created"]->get(IL_CAL_DATE), 0, 7);
            $items[$month][$posting["id"]] = $posting;
        }
        
        if ($this->author && !$author_found) {
            $this->author = null;
        }
        
        return $items;
    }
    
    /**
     * Build posting month list
     *
     * @param array $items
     * @param string $a_cmd
     * @param bool $a_link_template
     * @param bool $a_show_inactive
     * @param string $a_export_directory
     * @return string
     */
    public function renderList(array $items, $a_cmd = "preview", $a_link_template = null, $a_show_inactive = false, $a_export_directory = null)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        
        $wtpl = new ilTemplate("tpl.blog_list.html", true, true, "Modules/Blog");

        // quick editing in portfolio
        if ($this->prt_id > 0 &&
            stristr($a_cmd, "embedded")) {
            if (ilObject::_lookupOwner($this->prt_id) == $ilUser->getId()) {
                // see ilPortfolioPageTableGUI::fillRow()
                $ilCtrl->setParameterByClass("ilportfoliopagegui", "ppage", $this->user_page);
                $link = $ilCtrl->getLinkTargetByClass(array("ilportfoliopagegui", "ilobjbloggui"), "render");
                $ilCtrl->setParameterByClass("ilportfoliopagegui", "ppage", "");
                        
                $list = new ilAdvancedSelectionListGUI();
                $list->setListTitle($lng->txt("action"));
                $list->addItem(
                    sprintf($lng->txt("prtf_edit_embedded_blog"), $this->object->getTitle()),
                    "",
                    $link
                );
                
                $wtpl->setCurrentBlock("prtf_edit_bl");
                $wtpl->setVariable("PRTF_BLOG_EDIT", $list->getHTML());
                $wtpl->parseCurrentBlock();
            }
        }
                                        
        $is_admin = $this->isAdmin();
        
        $last_month = null;
        $is_empty = true;
        foreach ($items as $item) {
            // only published items
            $is_active = ilBlogPosting::_lookupActive($item["id"], "blp");
            if (!$is_active && !$a_show_inactive) {
                continue;
            }
            
            $is_empty = false;
            
            if (!$this->keyword && !$this->author) {
                $month = substr($item["created"]->get(IL_CAL_DATE), 0, 7);
            }
            
            if (!$last_month || $last_month != $month) {
                if ($last_month) {
                    $wtpl->setCurrentBlock("month_bl");
                    $wtpl->parseCurrentBlock();
                }
                                
                // title according to current "filter"/navigation
                if ($this->keyword) {
                    $title = $lng->txt("blog_keyword") . ": " . $this->keyword;
                } elseif ($this->author) {
                    $title = $lng->txt("blog_author") . ": " . ilUserUtil::getNamePresentation($this->author);
                } else {
                    $title = ilCalendarUtil::_numericMonthToString((int) substr($month, 5)) .
                            " " . substr($month, 0, 4);
                    
                    $last_month = $month;
                }
                                
                $wtpl->setVariable("TXT_CURRENT_MONTH", $title);
            }
            
            if (!$a_link_template) {
                $ilCtrl->setParameterByClass("ilblogpostinggui", "bmn", $this->month);
                $ilCtrl->setParameterByClass("ilblogpostinggui", "blpg", $item["id"]);
                $preview = $ilCtrl->getLinkTargetByClass("ilblogpostinggui", $a_cmd);
            } else {
                $preview = $this->buildExportLink($a_link_template, "posting", $item["id"]);
            }

            // actions
            $posting_edit = $this->mayEditPosting($item["id"], $item["author"]);
            if (($posting_edit || $is_admin) && !$a_link_template && $a_cmd == "preview") {
                $alist = new ilAdvancedSelectionListGUI();
                $alist->setId($item["id"]);
                $alist->setListTitle($lng->txt("actions"));
                                                
                if ($is_active && $this->object->hasApproval() && !$item["approved"]) {
                    if ($is_admin) {
                        $ilCtrl->setParameter($this, "apid", $item["id"]);
                        $alist->addItem(
                            $lng->txt("blog_approve"),
                            "approve",
                            $ilCtrl->getLinkTarget($this, "approve")
                        );
                        $ilCtrl->setParameter($this, "apid", "");
                    }
                    
                    $wtpl->setVariable("APPROVAL", $lng->txt("blog_needs_approval"));
                }
                
                if ($posting_edit) {
                    $alist->addItem(
                        $lng->txt("edit_content"),
                        "edit",
                        $ilCtrl->getLinkTargetByClass("ilblogpostinggui", "edit")
                    );
                    
                    // #11858
                    if ($is_active) {
                        $alist->addItem(
                            $lng->txt("blog_toggle_draft"),
                            "deactivate",
                            $ilCtrl->getLinkTargetByClass("ilblogpostinggui", "deactivatePageToList")
                        );
                    } else {
                        $alist->addItem(
                            $lng->txt("blog_toggle_final"),
                            "activate",
                            $ilCtrl->getLinkTargetByClass("ilblogpostinggui", "activatePageToList")
                        );
                    }
                    
                    $alist->addItem(
                        $lng->txt("rename"),
                        "rename",
                        $ilCtrl->getLinkTargetByClass("ilblogpostinggui", "edittitle")
                    );
                    
                    if ($this->object->hasKeywords()) { // #13616
                        $alist->addItem(
                            $lng->txt("blog_edit_keywords"),
                            "keywords",
                            $ilCtrl->getLinkTargetByClass("ilblogpostinggui", "editKeywords")
                        );
                    }
                    
                    $alist->addItem(
                        $lng->txt("blog_edit_date"),
                        "editdate",
                        $ilCtrl->getLinkTargetByClass("ilblogpostinggui", "editdate")
                    );
                    $alist->addItem(
                        $lng->txt("delete"),
                        "delete",
                        $ilCtrl->getLinkTargetByClass("ilblogpostinggui", "deleteBlogPostingConfirmationScreen")
                    );
                } elseif ($is_admin) {
                    // #10513
                    if ($is_active) {
                        $ilCtrl->setParameter($this, "apid", $item["id"]);
                        $alist->addItem(
                            $lng->txt("blog_toggle_draft_admin"),
                            "deactivate",
                            $ilCtrl->getLinkTarget($this, "deactivateAdmin")
                        );
                        $ilCtrl->setParameter($this, "apid", "");
                    }
                    
                    $alist->addItem(
                        $lng->txt("delete"),
                        "delete",
                        $ilCtrl->getLinkTargetByClass("ilblogpostinggui", "deleteBlogPostingConfirmationScreen")
                    );
                }

                $wtpl->setCurrentBlock("actions");
                $wtpl->setVariable("ACTION_SELECTOR", $alist->getHTML());
                $wtpl->parseCurrentBlock();
            }
            
            // comments
            if ($this->object->getNotesStatus() && !$a_link_template && !$this->disable_notes) {
                // count (public) notes
                $count = sizeof(ilNote::_getNotesOfObject(
                    $this->obj_id,
                    $item["id"],
                    "blp",
                    IL_NOTE_PUBLIC
                ));
                
                if ($a_cmd != "preview") {
                    $wtpl->setCurrentBlock("comments");
                    $wtpl->setVariable("TEXT_COMMENTS", $lng->txt("blog_comments"));
                    $wtpl->setVariable("URL_COMMENTS", $preview);
                    $wtpl->setVariable("COUNT_COMMENTS", $count);
                    $wtpl->parseCurrentBlock();
                }
                /* we disabled comments in edit mode (should always be done via pagegui)
                else
                {
                    $hash = ilCommonActionDispatcherGUI::buildAjaxHash(ilCommonActionDispatcherGUI::TYPE_WORKSPACE,
                        $this->node_id, "blog", $this->obj_id, "blp", $item["id"]);
                    $notes_link = "#\" onclick=\"".ilNoteGUI::getListCommentsJSCall($hash);
                }
                */
            }
                            
            // permanent link
            if ($a_cmd != "preview" && $a_cmd != "previewEmbedded") {
                if ($this->id_type == self::WORKSPACE_NODE_ID) {
                    $goto = $this->getAccessHandler()->getGotoLink($this->node_id, $this->obj_id, "_" . $item["id"]);
                } else {
                    $goto = ilLink::_getStaticLink($this->node_id, $this->getType(), true, "_" . $item["id"]);
                }
                $wtpl->setCurrentBlock("permalink");
                $wtpl->setVariable("URL_PERMALINK", $goto);
                $wtpl->setVariable("TEXT_PERMALINK", $lng->txt("blog_permanent_link"));
                $wtpl->parseCurrentBlock();
            }
                        
            $snippet = ilBlogPostingGUI::getSnippet(
                $item["id"],
                $this->object->hasAbstractShorten(),
                $this->object->getAbstractShortenLength(),
                "&hellip;",
                $this->object->hasAbstractImage(),
                $this->object->getAbstractImageWidth(),
                $this->object->getAbstractImageHeight(),
                $a_export_directory
            );
            
            if ($snippet) {
                $wtpl->setCurrentBlock("more");
                $wtpl->setVariable("URL_MORE", $preview);
                $wtpl->setVariable("TEXT_MORE", $lng->txt("blog_list_more"));
                $wtpl->parseCurrentBlock();
            }
            

            
            if (!$is_active) {
                $wtpl->setCurrentBlock("draft_text");
                $wtpl->setVariable("DRAFT_TEXT", $lng->txt("blog_draft_text"));
                $wtpl->parseCurrentBlock();
                $wtpl->setVariable("DRAFT_CLASS", " ilBlogListItemDraft");
            }

            $wtpl->setCurrentBlock("posting");
            
            $author = "";
            if ($this->id_type == self::REPOSITORY_NODE_ID) {
                $authors = array();
                
                $author_id = $item["author"];
                if ($author_id) {
                    $authors[] = ilUserUtil::getNamePresentation($author_id);
                }
                
                if (is_array($item["editors"])) {
                    foreach ($item["editors"] as $editor_id) {
                        $authors[] = ilUserUtil::getNamePresentation($editor_id);
                    }
                }
                
                if ($authors) {
                    $author = implode(", ", $authors) . " - ";
                }
            }
            
            // title
            $wtpl->setVariable("URL_TITLE", $preview);
            $wtpl->setVariable("TITLE", $item["title"]);

            $kw = ilBlogPosting::getKeywords($this->obj_id, $item["id"]);
            natcasesort($kw);
            $keywords = (count($kw) > 0)
                ? "<br>" . $this->lng->txt("keywords") . ": " . implode(", ", $kw)
                : "";

            $wtpl->setVariable("DATETIME", $author .
                ilDatePresentation::formatDate($item["created"]) . $keywords);

            // content
            $wtpl->setVariable("CONTENT", $snippet);

            $wtpl->parseCurrentBlock();
        }
        
        // permalink
        if ($a_cmd == "previewFullscreen") {
            $this->tpl->setPermanentLink(
                "blog",
                $this->node_id,
                ($this->id_type == self::WORKSPACE_NODE_ID)
                ? "_wsp"
                : ""
            );
        }
        
        if (!$is_empty || $a_show_inactive) {
            return $wtpl->get();
        }
    }

    /**
     * Build export link
     *
     * @param
     * @return
     */
    protected function buildExportLink($a_template, $a_type, $a_id)
    {
        return \ILIAS\Blog\Export\BlogHtmlExport::buildExportLink($a_template, $a_type, $a_id, $this->getKeywords(false));
    }


    /**
     * Build navigation by date block
     *
     * @param array $a_items
     * @param string $a_list_cmd
     * @param string $a_posting_cmd
     * @param bool $a_link_template
     * @param bool $a_show_inactive
     * @return string
     */
    protected function renderNavigationByDate(array $a_items, $a_list_cmd = "render", $a_posting_cmd = "preview", $a_link_template = null, $a_show_inactive = false, $a_blpg = 0)
    {
        $ilCtrl = $this->ctrl;

        $blpg = ($a_blpg > 0)
            ? $a_blpg
            : $this->blpg;


        // gather page active status
        foreach ($a_items as $month => $postings) {
            foreach (array_keys($postings) as $id) {
                $active = ilBlogPosting::_lookupActive($id, "blp");
                if (!$a_show_inactive && !$active) {
                    unset($a_items[$month][$id]);
                } else {
                    $a_items[$month][$id]["active"] = $active;
                }
            }
            if (!sizeof($a_items[$month])) {
                unset($a_items[$month]);
            }
        }
        
        // list month (incl. postings)
        if ($this->object->getNavMode() == ilObjBlog::NAV_MODE_LIST || $a_link_template) {
            //$max_detail_postings = $this->object->getNavModeListPostings();
            $max_months = $this->object->getNavModeListMonths();

            $wtpl = new ilTemplate("tpl.blog_list_navigation_by_date.html", true, true, "Modules/Blog");

            $ilCtrl->setParameter($this, "blpg", "");

            $counter = $mon_counter = $last_year = 0;
            foreach ($a_items as $month => $postings) {
                if (!$a_link_template && $max_months && $mon_counter >= $max_months) {
                    break;
                }
                
                $add_year = false;
                $year = substr($month, 0, 4);
                if (!$last_year || $year != $last_year) {
                    // #13562
                    $add_year = true;
                    $last_year = $year;
                }
                
                $mon_counter++;

                $month_name = ilCalendarUtil::_numericMonthToString((int) substr($month, 5));

                if (!$a_link_template) {
                    $ilCtrl->setParameter($this, "bmn", $month);
                    $month_url = $ilCtrl->getLinkTarget($this, $a_list_cmd);
                } else {
                    $month_url = $this->buildExportLink($a_link_template, "list", $month);
                }

                // list postings for month
                //if($counter < $max_detail_postings)
                if ($mon_counter <= $this->object->getNavModeListMonthsWithPostings()) {
                    if ($add_year) {
                        $wtpl->setCurrentBlock("navigation_year_details");
                        $wtpl->setVariable("YEAR", $year);
                        $wtpl->parseCurrentBlock();
                    }
                    
                    foreach ($postings as $id => $posting) {
                        //if($max_detail_postings && $counter >= $max_detail_postings)
                        //{
                        //	break;
                        //}

                        $counter++;

                        $caption = /* ilDatePresentation::formatDate($posting["created"], IL_CAL_DATETIME).
                            ", ".*/ $posting["title"];

                        if (!$a_link_template) {
                            $ilCtrl->setParameterByClass("ilblogpostinggui", "bmn", $month);
                            $ilCtrl->setParameterByClass("ilblogpostinggui", "blpg", $id);
                            $url = $ilCtrl->getLinkTargetByClass("ilblogpostinggui", $a_posting_cmd);
                        } else {
                            $url = $this->buildExportLink($a_link_template, "posting", $id);
                        }

                        if (!$posting["active"]) {
                            $wtpl->setVariable("NAV_ITEM_DRAFT", $this->lng->txt("blog_draft"));
                        } elseif ($this->object->hasApproval() && !$posting["approved"]) {
                            $wtpl->setVariable("NAV_ITEM_APPROVAL", $this->lng->txt("blog_needs_approval"));
                        }

                        $wtpl->setCurrentBlock("navigation_item");
                        $wtpl->setVariable("NAV_ITEM_URL", $url);
                        $wtpl->setVariable("NAV_ITEM_CAPTION", $caption);
                        $wtpl->parseCurrentBlock();
                    }

                    $wtpl->setCurrentBlock("navigation_month_details");
                    $wtpl->setVariable("NAV_MONTH", $month_name);
                    $wtpl->setVariable("URL_MONTH", $month_url);
                    $wtpl->parseCurrentBlock();
                }
                // summarized month
                else {
                    if ($add_year) {
                        $wtpl->setCurrentBlock("navigation_year");
                        $wtpl->setVariable("YEAR", $year);
                        $wtpl->parseCurrentBlock();
                    }
                    
                    $wtpl->setCurrentBlock("navigation_month");
                    $wtpl->setVariable("MONTH_NAME", $month_name);
                    $wtpl->setVariable("URL_MONTH", $month_url);
                    $wtpl->setVariable("MONTH_COUNT", sizeof($postings));
                    $wtpl->parseCurrentBlock();
                }
            }

            $ilCtrl->setParameter($this, "bmn", $this->month);
            $ilCtrl->setParameterByClass("ilblogpostinggui", "bmn", "");

            return $wtpl->get();
        }
        // single month
        else {
            $wtpl = new ilTemplate("tpl.blog_list_navigation_month.html", true, true, "Modules/Blog");

            $ilCtrl->setParameter($this, "blpg", "");

            $month_options = array();
            foreach ($a_items as $month => $postings) {
                $month_name = ilCalendarUtil::_numericMonthToString((int) substr($month, 5)) .
                    " " . substr($month, 0, 4);

                $month_options[$month] = $month_name;
                
                if ($month == $this->month) {
                    if (!$a_link_template) {
                        $ilCtrl->setParameter($this, "bmn", $month);
                        $month_url = $ilCtrl->getLinkTarget($this, $a_list_cmd);
                    } else {
                        $month_url = $this->buildExportLink($a_link_template, "list", $month);
                    }
                    
                    foreach ($postings as $id => $posting) {
                        $caption = /* ilDatePresentation::formatDate($posting["created"], IL_CAL_DATETIME).
                            ", ".*/ $posting["title"];

                        if (!$a_link_template) {
                            $ilCtrl->setParameterByClass("ilblogpostinggui", "bmn", $month);
                            $ilCtrl->setParameterByClass("ilblogpostinggui", "blpg", $id);
                            $url = $ilCtrl->getLinkTargetByClass("ilblogpostinggui", $a_posting_cmd);
                        } else {
                            $url = $this->buildExportLink($a_link_template, "posting", $id);
                        }

                        if (!$posting["active"]) {
                            $wtpl->setVariable("NAV_ITEM_DRAFT", $this->lng->txt("blog_draft"));
                        } elseif ($this->object->hasApproval() && !$posting["approved"]) {
                            $wtpl->setVariable("NAV_ITEM_APPROVAL", $this->lng->txt("blog_needs_approval"));
                        }

                        $wtpl->setCurrentBlock("navigation_item");
                        $wtpl->setVariable("NAV_ITEM_URL", $url);
                        $wtpl->setVariable("NAV_ITEM_CAPTION", $caption);
                        $wtpl->parseCurrentBlock();
                    }

                    $wtpl->setCurrentBlock("navigation_month_details");
                    if ($blpg > 0) {
                        $wtpl->setVariable("NAV_MONTH", $month_name);
                        $wtpl->setVariable("URL_MONTH", $month_url);
                    }
                    $wtpl->parseCurrentBlock();
                }
            }
            
            if ($blpg == 0) {
                $wtpl->setCurrentBlock("option_bl");
                foreach ($month_options as $value => $caption) {
                    $wtpl->setVariable("OPTION_VALUE", $value);
                    $wtpl->setVariable("OPTION_CAPTION", $caption);
                    if ($value == $this->month) {
                        $wtpl->setVariable("OPTION_SEL", ' selected="selected"');
                    }
                    $wtpl->parseCurrentBlock();
                }
                
                $wtpl->setVariable("FORM_ACTION", $ilCtrl->getFormAction($this, $a_list_cmd));
            }
            
            $ilCtrl->setParameter($this, "bmn", $this->month);
            $ilCtrl->setParameterByClass("ilblogpostinggui", "bmn", "");
            
            return $wtpl->get();
        }
    }
    
    /**
     * Build navigation by keywords block
     *
     * @param string $a_list_cmd
     * @param bool $a_show_inactive
     * @return string
     */
    protected function renderNavigationByKeywords(
        $a_list_cmd = "render",
        $a_show_inactive = false,
        $a_link_template = false,
        $a_blpg = 0
    ) {
        $ilCtrl = $this->ctrl;

        $blpg = ($a_blpg > 0)
            ? $a_blpg
            : $this->blpg;

        $keywords = $this->getKeywords($a_show_inactive, $blpg);
        if ($keywords) {
            $wtpl = new ilTemplate("tpl.blog_list_navigation_keywords.html", true, true, "Modules/Blog");
                            
            $max = max($keywords);

            $wtpl->setCurrentBlock("keyword");
            foreach ($keywords as $keyword => $counter) {
                if (!$a_link_template) {
                    $ilCtrl->setParameter($this, "kwd", urlencode($keyword)); // #15885
                    $url = $ilCtrl->getLinkTarget($this, $a_list_cmd);
                    $ilCtrl->setParameter($this, "kwd", "");
                } else {
                    $url = $this->buildExportLink($a_link_template, "keyword", $keyword);
                }

                $wtpl->setVariable("TXT_KEYWORD", $keyword);
                $wtpl->setVariable("CLASS_KEYWORD", ilTagging::getRelevanceClass($counter, $max));
                $wtpl->setVariable("URL_KEYWORD", $url);
                $wtpl->parseCurrentBlock();
            }
        
            return $wtpl->get();
        }
    }
        
    protected function renderNavigationByAuthors(array $a_items, $a_list_cmd = "render", $a_show_inactive = false)
    {
        $ilCtrl = $this->ctrl;
        
        $authors = array();
        foreach ($a_items as $month => $items) {
            foreach ($items as $item) {
                if (($a_show_inactive || ilBlogPosting::_lookupActive($item["id"], "blp"))) {
                    if ($item["author"]) {
                        $authors[] = $item["author"];
                    }
                    
                    if (is_array($item["editors"])) {
                        foreach ($item["editors"] as $editor_id) {
                            if ($editor_id != $item["author"]) {
                                $authors[] = $editor_id;
                            }
                        }
                    }
                }
            }
        }

        $authors = array_unique($authors);
        if (sizeof($authors) > 1) {
            $list = array();
            foreach ($authors as $user_id) {
                if ($user_id) {
                    $ilCtrl->setParameter($this, "ath", $user_id);
                    $url = $ilCtrl->getLinkTarget($this, $a_list_cmd);
                    $ilCtrl->setParameter($this, "ath", "");

                    $name = ilUserUtil::getNamePresentation($user_id, true);
                    $idx = trim(strip_tags($name)) . "///" . $user_id;  // #10934
                    $list[$idx] = array($name, $url);
                }
            }
            ksort($list);
            
            $wtpl = new ilTemplate("tpl.blog_list_navigation_authors.html", true, true, "Modules/Blog");
            
            $wtpl->setCurrentBlock("author");
            foreach ($list as $author) {
                $wtpl->setVariable("TXT_AUTHOR", $author[0]);
                $wtpl->setVariable("URL_AUTHOR", $author[1]);
                $wtpl->parseCurrentBlock();
            }
            
            return $wtpl->get();
        }
    }

    /**
     * Toolbar navigation
     *
     * @param
     * @return
     */
    public function renderToolbarNavigation($a_items, $single_posting = false)
    {
        global $DIC;

        $toolbar = $DIC->toolbar();
        $lng = $DIC->language();
        $ctrl = $DIC->ctrl();

        $f = $DIC->ui()->factory();

        $cmd = ($this->prtf_embed)
            ? "previewEmbedded"
            : "previewFullscreen";

        if ($single_posting) {	// single posting view
            $latest_posting = $this->getLatestPosting($a_items);
            if ($latest_posting != "" && $this->blpg != $latest_posting) {
                $ctrl->setParameterByClass("ilblogpostinggui", "blpg", $latest_posting);
                $mb = $f->button()->standard(
                    $lng->txt("blog_latest_posting"),
                    $ctrl->getLinkTargetByClass("ilblogpostinggui", $cmd)
                );
            } else {
                $mb = $f->button()->standard($lng->txt("blog_latest_posting"), "#")->withUnavailableAction();
            }

            $prev_posting = $this->getPreviousPosting($a_items);
            if ($prev_posting != "") {
                $ctrl->setParameterByClass("ilblogpostinggui", "blpg", $prev_posting);
                $pb = $f->button()->standard(
                    $lng->txt("previous"),
                    $ctrl->getLinkTargetByClass("ilblogpostinggui", $cmd)
                );
            } else {
                $pb = $f->button()->standard($lng->txt("previous"), "#")->withUnavailableAction();
            }

            $next_posting = $this->getNextPosting($a_items);
            if ($next_posting != "") {
                $ctrl->setParameterByClass("ilblogpostinggui", "blpg", $next_posting);
                $nb = $f->button()->standard(
                    $lng->txt("next"),
                    $ctrl->getLinkTargetByClass("ilblogpostinggui", $cmd)
                );
            } else {
                $nb = $f->button()->standard($lng->txt("next"), "#")->withUnavailableAction();
            }
            $ctrl->setParameter($this, "blpg", $this->blpg);
            $vc = $f->viewControl()->section($pb, $mb, $nb);
            $toolbar->addComponent($vc);
            if ($this->mayContribute() && $this->mayEditPosting($this->blpg)) {
                $ctrl->setParameter($this, "prvm", "");
                $ctrl->setParameterByClass("ilblogpostinggui", "blpg", $this->blpg);
                $link = $link = $ctrl->getLinkTargetByClass("ilblogpostinggui", "edit");
                $toolbar->addSeparator();
                $toolbar->addComponent($f->button()->standard($lng->txt("blog_edit_posting"), $link));
            }
        } else {		// month view
            $latest_month = $this->getLatestMonth($a_items);
            if ($latest_month != "" && $this->month != $latest_month) {
                $ctrl->setParameter($this, "bmn", $latest_month);
                $mb = $f->button()->standard(
                    $lng->txt("blog_latest_posting"),
                    $ctrl->getLinkTarget($this, "preview")
                );
            } else {
                $mb = $f->button()->standard($lng->txt("blog_latest_posting"), "#")->withUnavailableAction();
            }

            $prev_month = $this->getPreviousMonth($a_items);
            if ($prev_month != "") {
                $ctrl->setParameter($this, "bmn", $prev_month);
                $pb = $f->button()->standard($lng->txt("previous"), $ctrl->getLinkTarget($this, "preview"));
            } else {
                $pb = $f->button()->standard($lng->txt("previous"), "#")->withUnavailableAction();
            }

            $next_month = $this->getNextMonth($a_items);
            if ($next_month != "") {
                $ctrl->setParameter($this, "bmn", $next_month);
                $nb = $f->button()->standard($lng->txt("next"), $ctrl->getLinkTarget($this, "preview"));
            } else {
                $nb = $f->button()->standard($lng->txt("next"), "#")->withUnavailableAction();
            }
            $ctrl->setParameter($this, "bmn", $this->month);
            $vc = $f->viewControl()->section($pb, $mb, $nb);
            $toolbar->addComponent($vc);

            if ($this->mayContribute()) {
                $ctrl->setParameter($this, "prvm", "");

                $ctrl->setParameter($this, "bmn", "");
                $ctrl->setParameter($this, "blpg", "");
                $link = $ctrl->getLinkTarget($this, "");
                $ctrl->setParameter($this, "blpg", $this->blpg);
                $ctrl->setParameter($this, "bmn", $this->month);
                $toolbar->addSeparator();
                $toolbar->addComponent($f->button()->standard($lng->txt("edit"), $link));
            }
        }
    }

    /**
     * Get next month
     *
     * @param array $a_items item array
     * @return string
     */
    public function getNextMonth($a_items)
    {
        reset($a_items);
        $found = "";
        foreach ($a_items as $month => $items) {
            if ($month > $this->month) {
                $found = $month;
            }
        }
        return $found;
    }

    /**
     * Get next month
     *
     * @param array $a_items item array
     * @return string
     */
    public function getPreviousMonth($a_items)
    {
        reset($a_items);
        $found = "";
        foreach ($a_items as $month => $items) {
            if ($month < $this->month && $found == "") {
                $found = $month;
            }
        }
        return $found;
    }

    /**
     * Get next month
     *
     * @param array $a_items item array
     * @return string
     */
    public function getLatestMonth($a_items)
    {
        reset($a_items);
        return key($a_items);
    }

    /**
     * Get next posting
     *
     * @param array $a_items item array
     * @return int page id
     */
    public function getNextPosting($a_items)
    {
        reset($a_items);
        $found = "";
        $next_blpg = 0;
        foreach ($a_items as $month => $items) {
            foreach ($items as $item) {
                if ($item["id"] == $this->blpg) {
                    $found = true;
                }
                if (!$found) {
                    $next_blpg = $item["id"];
                }
            }
        }
        return $next_blpg;
    }

    /**
     * Get previous posting
     *
     * @param array $a_items item array
     * @return int page id
     */
    public function getPreviousPosting($a_items)
    {
        reset($a_items);
        $found = "";
        $prev_blpg = 0;
        foreach ($a_items as $month => $items) {
            foreach ($items as $item) {
                if ($found && $prev_blpg == "") {
                    $prev_blpg = $item["id"];
                }
                if ($item["id"] == $this->blpg) {
                    $found = true;
                }
            }
        }
        return $prev_blpg;
    }

    /**
     * Get previous posting
     *
     * @param array $a_items item array
     * @return int page id
     */
    public function getLatestPosting($a_items)
    {
        reset($a_items);
        $month = current($a_items);
        if (is_array($month)) {
            return current($month)["id"];
        }
        return false;
    }

    /**
     * Build navigation blocks
     *
     * @param string $a_list_cmd
     * @param string $a_posting_cmd
     * @param bool $a_link_template
     * @param bool $a_show_inactive
     * @param int $a_blpg blog page id
     * @return string
     */
    public function renderNavigation($a_list_cmd = "render", $a_posting_cmd = "preview", $a_link_template = null, $a_show_inactive = false, $a_blpg = 0)
    {
        $ilCtrl = $this->ctrl;
        $ilSetting = $this->settings;
        $a_items = $this->items;

        $blpg = ($a_blpg > 0)
            ? $a_blpg
            : $this->blpg;

        if ($this->object->getOrder()) {
            $order = array_flip($this->object->getOrder());
        } else {
            $order = array(
                "navigation" => 0
                ,"keywords" => 2
                ,"authors" => 1
            );
        }
                
        $wtpl = new ilTemplate("tpl.blog_list_navigation.html", true, true, "Modules/Blog");
            
        $blocks = array();
        
        // by date
        if (sizeof($a_items)) {
            $blocks[$order["navigation"]] = array(
                $this->lng->txt("blog_navigation"),
                $this->renderNavigationByDate($a_items, $a_list_cmd, $a_posting_cmd, $a_link_template, $a_show_inactive, $a_blpg)
            );
        }
        
        if ($this->object->hasKeywords()) {
            // keywords
            $may_edit_keywords = ($blpg > 0 &&
                $this->mayEditPosting($blpg) &&
                $a_list_cmd != "preview" &&
                $a_list_cmd != "gethtml" &&
                !$a_link_template);
            $keywords = $this->renderNavigationByKeywords($a_list_cmd, $a_show_inactive, $a_link_template, $a_blpg);
            if ($keywords || $may_edit_keywords) {
                if (!$keywords) {
                    $keywords = $this->lng->txt("blog_no_keywords");
                }
                $cmd = null;
                $blocks[$order["keywords"]] = array(
                    $this->lng->txt("blog_keywords"),
                    $keywords,
                    $cmd
                        ? array($cmd, $this->lng->txt("blog_edit_keywords"))
                        : null
                );
            }
        }

        // is not part of (html) export
        if (!$a_link_template) {
            // authors
            if ($this->id_type == self::REPOSITORY_NODE_ID &&
                $this->object->hasAuthors()) {
                $authors = $this->renderNavigationByAuthors($a_items, $a_list_cmd, $a_show_inactive);
                if ($authors) {
                    $blocks[$order["authors"]] = array($this->lng->txt("blog_authors"), $authors);
                }
            }
        
            // rss
            if ($this->object->hasRSS() &&
                $ilSetting->get('enable_global_profiles') &&
                $a_list_cmd == "preview") {
                // #10827
                $blog_id = $this->node_id;
                if ($this->id_type != self::WORKSPACE_NODE_ID) {
                    $blog_id .= "_cll";
                }
                $url = ILIAS_HTTP_PATH . "/feed.php?blog_id=" . $blog_id .
                    "&client_id=" . rawurlencode(CLIENT_ID);

                $wtpl->setVariable("RSS_BUTTON", ilRSSButtonGUI::get(ilRSSButtonGUI::ICON_RSS, $url));
            }
        }
        
        if (sizeof($blocks)) {
            global $DIC;

            $ui_factory = $DIC->ui()->factory();
            $ui_renderer = $DIC->ui()->renderer();

            ksort($blocks);
            foreach ($blocks as $block) {
                $title = $block[0];

                $content = $block[1];

                $secondary_panel = $ui_factory->panel()->secondary()->legacy($title, $ui_factory->legacy($content));

                if (isset($block[2]) && is_array($block[2])) {
                    $link = $ui_factory->button()->shy($block[2][1], $block[2][0]);
                    $secondary_panel = $secondary_panel->withFooter($link);
                }

                $wtpl->setCurrentBlock("block_bl");
                $wtpl->setVariable("BLOCK", $ui_renderer->render($secondary_panel));
                $wtpl->parseCurrentBlock();
            }
        }
        
        return $wtpl->get();
    }
    
    /**
     * Get keywords for single posting or complete blog
     *
     * @param bool $a_show_inactive
     * @param int $a_posting_id
     * @return array
     */
    public function getKeywords($a_show_inactive, $a_posting_id = null)
    {
        $keywords = array();
        if ($a_posting_id) {
            foreach (ilBlogPosting::getKeywords($this->obj_id, $a_posting_id) as $keyword) {
                $keywords[$keyword]++;
            }
        } else {
            foreach ($this->items as $month => $items) {
                foreach ($items as $item) {
                    if ($a_show_inactive || ilBlogPosting::_lookupActive($item["id"], "blp")) {
                        foreach (ilBlogPosting::getKeywords($this->obj_id, $item["id"]) as $keyword) {
                            $keywords[$keyword]++;
                        }
                    }
                }
            }
        }
        
        // #15881
        $tmp = array();
        foreach ($keywords as $keyword => $counter) {
            $tmp[] = array("keyword" => $keyword, "counter" => $counter);
        }
        $tmp = ilUtil::sortArray($tmp, "keyword", "ASC");
        
        $keywords = array();
        foreach ($tmp as $item) {
            $keywords[$item["keyword"]] = $item["counter"];
        }
        return $keywords;
    }
    
    /**
     * Build export file
     *
     * @return string
     */
    public function buildExportFile()
    {
        // create export file
        ilExport::_createExportDirectory($this->object->getId(), "html", "blog");
        $exp_dir = ilExport::_getExportDirectory($this->object->getId(), "html", "blog");

        $subdir = $this->object->getType() . "_" . $this->object->getId();

        $blog_export = new \ILIAS\Blog\Export\BlogHtmlExport($this, $exp_dir, $subdir);
        return $blog_export->exportHTML();
    }

    public function getNotesSubId()
    {
        return $this->blpg;
    }
    
    public function disableNotes($a_value = false)
    {
        $this->disable_notes = (bool) $a_value;
    }
        
    protected function addHeaderActionForCommand($a_cmd)
    {
        $ilUser = $this->user;
        $ilCtrl = $this->ctrl;
        
        // preview?
        if ($a_cmd == "preview" || $a_cmd == "previewFullscreen" || $this->prvm) {
            // notification
            if ($ilUser->getId() != ANONYMOUS_USER_ID) {
                if (!$this->prvm) {
                    $ilCtrl->setParameter($this, "prvm", "fsc");
                }
                $this->insertHeaderAction($this->initHeaderAction(null, null, true));
                if (!$this->prvm) {
                    $ilCtrl->setParameter($this, "prvm", "");
                }
            }
        } else {
            return parent::addHeaderAction();
        }
    }
    
    protected function initHeaderAction($sub_type = null, $sub_id = null, $a_is_preview = false)
    {
        $ilUser = $this->user;
        $ilCtrl = $this->ctrl;
        
        if (!$this->obj_id) {
            return false;
        }

        $sub_type = $sub_id = null;
        if ($this->blpg > 0) {
            $sub_type = "blp";
            $sub_id = $this->blpg;
        }
                
        $lg = parent::initHeaderAction($sub_type, $sub_id);
        
        if ($a_is_preview) {
            $lg->enableComments(false);
            $lg->enableNotes(false);
            $lg->enableTags(false);
            
            if (ilNotification::hasNotification(ilNotification::TYPE_BLOG, $ilUser->getId(), $this->obj_id)) {
                $ilCtrl->setParameter($this, "ntf", 1);
                $link = $ilCtrl->getLinkTarget($this, "setNotification");
                $ilCtrl->setParameter($this, "ntf", "");
                if (ilNotification::hasOptOut($this->obj_id)) {
                    $lg->addCustomCommand($link, "blog_notification_toggle_off");
                }
                
                $lg->addHeaderIcon(
                    "not_icon",
                    ilUtil::getImagePath("notification_on.svg"),
                    $this->lng->txt("blog_notification_activated")
                );
            } else {
                $ilCtrl->setParameter($this, "ntf", 2);
                $link = $ilCtrl->getLinkTarget($this, "setNotification");
                $ilCtrl->setParameter($this, "ntf", "");
                $lg->addCustomCommand($link, "blog_notification_toggle_on");
                
                $lg->addHeaderIcon(
                    "not_icon",
                    ilUtil::getImagePath("notification_off.svg"),
                    $this->lng->txt("blog_notification_deactivated")
                );
            }
            
            // #11758
            if ($this->mayContribute()) {
                $ilCtrl->setParameter($this, "prvm", "");
                
                $ilCtrl->setParameter($this, "bmn", "");
                $ilCtrl->setParameter($this, "blpg", "");
                $link = $ilCtrl->getLinkTarget($this, "");
                $ilCtrl->setParameter($this, "blpg", $sub_id);
                $ilCtrl->setParameter($this, "bmn", $this->month);
                $lg->addCustomCommand($link, "blog_edit"); // #11868
                                
                if ($sub_id && $this->mayEditPosting($sub_id)) {
                    $link = $ilCtrl->getLinkTargetByClass("ilblogpostinggui", "edit");
                    $lg->addCustomCommand($link, "blog_edit_posting");
                }
                
                $ilCtrl->setParameter($this, "prvm", "fsc");
            }
            
            $ilCtrl->setParameter($this, "ntf", "");
        }
        
        return $lg;
    }
    
    protected function setNotification()
    {
        $ilUser = $this->user;
        $ilCtrl = $this->ctrl;
        
        switch ($this->ntf) {
            case 1:
                ilNotification::setNotification(ilNotification::TYPE_BLOG, $ilUser->getId(), $this->obj_id, false);
                break;
            
            case 2:
                ilNotification::setNotification(ilNotification::TYPE_BLOG, $ilUser->getId(), $this->obj_id, true);
                break;
        }
        
        $ilCtrl->redirect($this, "preview");
    }

    /**
     * Get title for blog posting (used in ilNotesGUI)
     *
     * @param int $a_blog_id
     * @param int $a_posting_id
     * @return string
     */
    public static function lookupSubObjectTitle($a_blog_id, $a_posting_id)
    {
        // page might be deleted, so setting halt on errors to false
        $post = new ilBlogPosting($a_posting_id);
        if ($post->getBlogId() == $a_blog_id) {
            return $post->getTitle();
        }
    }
    
    /**
     * Filter inactive items from items list
     *
     * @return array
     */
    protected function filterInactivePostings()
    {
        foreach ($this->items as $month => $postings) {
            foreach ($postings as $id => $item) {
                if (!ilBlogPosting::_lookupActive($id, "blp")) {
                    unset($this->items[$month][$id]);
                } elseif ($this->object->hasApproval() && !$item["approved"]) {
                    unset($this->items[$month][$id]);
                }
            }
            if (!sizeof($this->items[$month])) {
                unset($this->items[$month]);
            }
        }
        
        if ($this->items && !isset($this->items[$this->month])) {
            $this->month = array_shift(array_keys($this->items));
        }
    }
        
    public function filterItemsByKeyWord(array $a_items, $a_keyword)
    {
        $res = array();
        foreach ($a_items as $month => $items) {
            foreach ($items as $item) {
                if (in_array(
                    $a_keyword,
                    ilBlogPosting::getKeywords($this->obj_id, $item["id"])
                )) {
                    $res[] = $item;
                }
            }
        }
        return $res;
    }
    
    /**
     * Check if user has admin access (approve, may edit & deactivate all postings)
     *
     * @return bool
     */
    protected function isAdmin()
    {
        return ($this->checkPermissionBool("redact") ||
                $this->checkPermissionBool("write"));
    }
    
    /**
     * Check if user may edit posting
     *
     * @param int $a_posting_id
     * @param int $a_author_id
     * @return boolean
     */
    protected function mayEditPosting($a_posting_id, $a_author_id = null)
    {
        $ilUser = $this->user;
        
        // single author blog (owner) in personal workspace
        if ($this->id_type == self::WORKSPACE_NODE_ID) {
            return $this->checkPermissionBool("write");
        }
        
        // repository blogs
        
        // redact allows to edit all postings
        if ($this->checkPermissionBool("redact")) {
            return true;
        }
            
        // contribute gives access to own postings
        if ($this->checkPermissionBool("contribute")) {
            // check owner of posting
            if (!$a_author_id) {
                $post = new ilBlogPosting($a_posting_id);
                $a_author_id = $post->getAuthor();
            }
            if ($ilUser->getId() == $a_author_id) {
                return true;
            } else {
                return false;
            }
            
            return true;
        }
        return false;
    }
    
    /**
     * Check if user may contribute at all
     *
     * @return boolean
     */
    protected function mayContribute()
    {
        // single author blog (owner) in personal workspace
        if ($this->id_type == self::WORKSPACE_NODE_ID) {
            return $this->checkPermissionBool("write");
        }
            
        return ($this->checkPermissionBool("redact") ||
            $this->checkPermissionBool("contribute"));
    }
    
    public function addLocatorItems()
    {
        $ilLocator = $this->locator;
        
        if (is_object($this->object)) {
            $ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, ""), "", $this->node_id);
        }
    }
    
    public function approve()
    {
        if ($this->isAdmin() && $this->apid > 0) {
            $post = new ilBlogPosting($this->apid);
            $post->setApproved(true);
            $post->setBlogNodeId($this->node_id, ($this->id_type == self::WORKSPACE_NODE_ID));
            $post->update(true, false, true, "new"); // #13434
            
            ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
        }
                
        $this->ctrl->redirect($this, "render");
    }
    
    
    //
    // contributors
    //
    
    public function contributors()
    {
        $ilTabs = $this->tabs;
        $ilToolbar = $this->toolbar;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;
        
        if (!$this->checkPermissionBool("write")) {
            return;
        }
        
        $ilTabs->activateTab("contributors");
    
        $local_roles = $this->object->getAllLocalRoles($this->node_id);
        
        // add member
        ilRepositorySearchGUI::fillAutoCompleteToolbar(
            $this,
            $ilToolbar,
            array(
                'auto_complete_name' => $lng->txt('user'),
                'submit_name' => $lng->txt('add'),
                'add_search' => true,
                'add_from_container' => $this->node_id,
                'user_type' => $local_roles
            ),
            true
        );

        $other_roles = $this->object->getRolesWithContributeOrRedact($this->node_id);
        if ($other_roles) {
            ilUtil::sendInfo(sprintf($lng->txt("blog_contribute_other_roles"), implode(", ", $other_roles)));
        }
        
        $tbl = new ilContributorTableGUI($this, "contributors", $this->object->getAllLocalRoles($this->node_id));
        
        $tpl->setContent($tbl->getHTML());
    }
    
    /**
     * Autocomplete submit
     */
    public function addUserFromAutoComplete()
    {
        $lng = $this->lng;

        $user_login = ilUtil::stripSlashes($_POST['user_login']);
        $user_type = ilUtil::stripSlashes($_POST["user_type"]);

        if (!strlen(trim($user_login))) {
            ilUtil::sendFailure($lng->txt('msg_no_search_string'));
            return $this->contributors();
        }
        $users = explode(',', $user_login);
                
        $user_ids = array();
        foreach ($users as $user) {
            $user_id = ilObjUser::_lookupId($user);

            if (!$user_id) {
                ilUtil::sendFailure($lng->txt('user_not_known'));
                return $this->contributors();
            }
            
            $user_ids[] = $user_id;
        }
    
        return $this->addContributor($user_ids, $user_type);
    }
        
    /**
     * Centralized method to add contributors
     *
     * @param array $a_user_ids
     */
    public function addContributor($a_user_ids = array(), $a_user_type = null)
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $rbacreview = $this->rbacreview;
        $rbacadmin = $this->rbacadmin;
        
        if (!$this->checkPermissionBool("write")) {
            return;
        }
        
        if (!count($a_user_ids) || !$a_user_type) {
            ilUtil::sendFailure($lng->txt("no_checkbox"));
            return $this->contributors();
        }
        
        // get contributor role
        $local_roles = array_keys($this->object->getAllLocalRoles($this->node_id));
        if (!in_array($a_user_type, $local_roles)) {
            ilUtil::sendFailure($lng->txt("missing_perm"));
            return $this->contributors();
        }
        
        foreach ($a_user_ids as $user_id) {
            if (!$rbacreview->isAssigned($user_id, $a_user_type)) {
                $rbacadmin->assignUser($a_user_type, $user_id);
            }
        }

        ilUtil::sendSuccess($lng->txt("settings_saved"), true);
        $ilCtrl->redirect($this, "contributors");
    }
    
    /**
     * Used in ilContributorTableGUI
     */
    public function confirmRemoveContributor()
    {
        $ids = ilUtil::stripSlashesRecursive($_POST["id"]);
        
        if (!is_array($ids)) {
            ilUtil::sendFailure($this->lng->txt("select_one"), true);
            $this->ctrl->redirect($this, "contributors");
        }
        
        $confirm = new ilConfirmationGUI();
        $confirm->setHeaderText($this->lng->txt('blog_confirm_delete_contributors'));
        $confirm->setFormAction($this->ctrl->getFormAction($this, 'removeContributor'));
        $confirm->setConfirm($this->lng->txt('delete'), 'removeContributor');
        $confirm->setCancel($this->lng->txt('cancel'), 'contributors');
        
        foreach ($ids as $user_id) {
            $confirm->addItem(
                'id[]',
                $user_id,
                ilUserUtil::getNamePresentation($user_id, false, false, "", true)
            );
        }
        
        $this->tpl->setContent($confirm->getHTML());
    }
    
    /**
     * Used in ilContributorTableGUI
     */
    public function removeContributor()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $rbacadmin = $this->rbacadmin;
        
        $ids = ilUtil::stripSlashesRecursive($_POST["id"]);
        
        if (!is_array($ids)) {
            ilUtil::sendFailure($lng->txt("select_one"), true);
            $ilCtrl->redirect($this, "contributors");
        }
        
        // get contributor role
        $local_roles = array_keys($this->object->getAllLocalRoles($this->node_id));
        if (!$local_roles) {
            ilUtil::sendFailure($lng->txt("missing_perm"));
            return $this->contributors();
        }
        
        foreach ($ids as $user_id) {
            foreach ($local_roles as $role_id) {
                $rbacadmin->deassignUser($role_id, $user_id);
            }
        }
                
        ilUtil::sendSuccess($lng->txt("settings_saved"), true);
        $this->ctrl->redirect($this, "contributors");
    }

    public function deactivateAdmin()
    {
        if ($this->checkPermissionBool("write") && $this->apid > 0) {
            // ilBlogPostingGUI::deactivatePage()
            $post = new ilBlogPosting($this->apid);
            $post->setApproved(false);
            $post->setActive(false);
            $post->update(true, false, false);
            
            ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
        }
                
        $this->ctrl->redirect($this, "render");
    }
    
    
    ////
    //// Style related functions
    ////
    
    public function setContentStyleSheet($a_tpl = null)
    {
        $tpl = $this->tpl;

        if ($a_tpl != null) {
            $ctpl = $a_tpl;
        } else {
            $ctpl = $tpl;
        }

        $ctpl->setCurrentBlock("ContentStyle");
        $ctpl->setVariable(
            "LOCATION_CONTENT_STYLESHEET",
            ilObjStyleSheet::getContentStylePath($this->object->getStyleSheetId())
        );
        $ctpl->parseCurrentBlock();
    }
    
    public function editStyleProperties()
    {
        $this->checkPermission("write");
        
        $this->tabs_gui->activateTab("settings");
        $this->setSettingsSubTabs("style");
        
        $form = $this->initStylePropertiesForm();
        $this->tpl->setContent($form->getHTML());
    }
    
    public function initStylePropertiesForm()
    {
        $ilSetting = $this->settings;
                        
        $this->lng->loadLanguageModule("style");

        $form = new ilPropertyFormGUI();
        
        $fixed_style = $ilSetting->get("fixed_content_style_id");
        $style_id = $this->object->getStyleSheetId();

        if ($fixed_style > 0) {
            $st = new ilNonEditableValueGUI($this->lng->txt("style_current_style"));
            $st->setValue(ilObject::_lookupTitle($fixed_style) . " (" .
                $this->lng->txt("global_fixed") . ")");
            $form->addItem($st);
        } else {
            $st_styles = ilObjStyleSheet::_getStandardStyles(
                true,
                false,
                $this->ref_id
            );

            $st_styles[0] = $this->lng->txt("default");
            ksort($st_styles);

            if ($style_id > 0) {
                // individual style
                if (!ilObjStyleSheet::_lookupStandard($style_id)) {
                    $st = new ilNonEditableValueGUI($this->lng->txt("style_current_style"));
                    $st->setValue(ilObject::_lookupTitle($style_id));
                    $form->addItem($st);

                    // delete command
                    $form->addCommandButton("editStyle", $this->lng->txt("style_edit_style"));
                    $form->addCommandButton("deleteStyle", $this->lng->txt("style_delete_style"));
                }
            }

            if ($style_id <= 0 || ilObjStyleSheet::_lookupStandard($style_id)) {
                $style_sel = new ilSelectInputGUI(
                    $this->lng->txt("style_current_style"),
                    "style_id"
                );
                $style_sel->setOptions($st_styles);
                $style_sel->setValue($style_id);
                $form->addItem($style_sel);

                $form->addCommandButton("saveStyleSettings", $this->lng->txt("save"));
                $form->addCommandButton("createStyle", $this->lng->txt("sty_create_ind_style"));
            }
        }
        
        $form->setTitle($this->lng->txt("blog_style"));
        $form->setFormAction($this->ctrl->getFormAction($this));
        
        return $form;
    }

    public function createStyle()
    {
        $this->ctrl->redirectByClass("ilobjstylesheetgui", "create");
    }
        
    public function editStyle()
    {
        $this->ctrl->redirectByClass("ilobjstylesheetgui", "edit");
    }

    public function deleteStyle()
    {
        $this->ctrl->redirectByClass("ilobjstylesheetgui", "delete");
    }

    public function saveStyleSettings()
    {
        $ilSetting = $this->settings;
    
        if ($ilSetting->get("fixed_content_style_id") <= 0 &&
            (ilObjStyleSheet::_lookupStandard($this->object->getStyleSheetId())
            || $this->object->getStyleSheetId() == 0)) {
            $this->object->setStyleSheetId((int) $_POST["style_id"]);
            $this->object->update();
            
            ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
        }
        $this->ctrl->redirect($this, "editStyleProperties");
    }
    
    /**
     * Deep link
     *
     * @param string $a_target
     */
    public static function _goto($a_target)
    {
        global $DIC;

        $ilCtrl = $DIC->ctrl();
        $access = $DIC->access();

        if (substr($a_target, -3) == "wsp") {
            $id = explode("_", $a_target);

            $ilCtrl->setTargetScript("ilias.php");
            $ilCtrl->initBaseClass("ilSharedResourceGUI");
            $ilCtrl->setParameterByClass("ilSharedResourceGUI", "wsp_id", $id[0]);

            if (sizeof($id) >= 2) {
                if (is_numeric($id[1])) {
                    $ilCtrl->setParameterByClass("ilSharedResourceGUI", "gtp", $id[1]);
                } else {
                    $ilCtrl->setParameterByClass("ilSharedResourceGUI", "kwd", $id[1]);
                }
                if ($id[2] == "edit") {
                    $ilCtrl->setParameterByClass("ilSharedResourceGUI", "edt", $id[2]);
                }
            }
            $ilCtrl->redirectByClass("ilSharedResourceGUI", "");
        } else {
            $id = explode("_", $a_target);

            $ilCtrl->setTargetScript("ilias.php");
            $ilCtrl->initBaseClass("ilRepositoryGUI");
            $ilCtrl->setParameterByClass("ilRepositoryGUI", "ref_id", $id[0]);

            if (sizeof($id) >= 2) {
                if (is_numeric($id[1])) {
                    $ilCtrl->setParameterByClass("ilRepositoryGUI", "gtp", $id[1]);
                } else {
                    $ilCtrl->setParameterByClass("ilRepositoryGUI", "kwd", $id[1]);
                }

                if ($id[2] == "edit") {
                    $ilCtrl->setParameterByClass("ilRepositoryGUI", "edt", $id[2]);
                }
            }
            if ($access->checkAccess("read", "", $id[0])) {
                $ilCtrl->redirectByClass("ilRepositoryGUI", "preview");
            }
            if ($access->checkAccess("visible", "", $id[0])) {
                $ilCtrl->redirectByClass("ilRepositoryGUI", "infoScreen");
            }
        }
    }
}
