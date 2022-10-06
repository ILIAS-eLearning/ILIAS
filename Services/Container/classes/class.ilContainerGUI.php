<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\GlobalScreen\Services;
use ILIAS\UI\Component\Input\Container\Filter\Standard;
use ILIAS\DI\UIServices;
use ILIAS\Repository\Clipboard\ClipboardManager;
use ILIAS\Container\StandardGUIRequest;
use ILIAS\Container\Content\ViewManager;

/**
 * Class ilContainerGUI
 * This is a base GUI class for all container objects in ILIAS:
 * root folder, course, group, category, folder
 * @author Alexander Killing <killing@leifos.de>
 * @author  Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilContainerGUI extends ilObjectGUI implements ilDesktopItemHandling
{
    protected \ILIAS\Style\Content\GUIService $content_style_gui;
    protected ilRbacSystem $rbacsystem;
    protected ilRbacReview $rbacreview;
    protected ilTabsGUI $tabs;
    protected ilErrorHandling $error;
    protected ilObjectDefinition $obj_definition;
    protected ilRbacAdmin $rbacadmin;
    protected ilPropertyFormGUI $form;
    protected ilLogger $log;
    protected ilObjectDataCache $obj_data_cache;
    protected Services $global_screen;
    protected ilAppEventHandler $app_event_handler;
    public int $bl_cnt = 1;        // block counter
    public bool $multi_download_enabled = false;
    protected UIServices $ui;
    protected ilContainerFilterService $container_filter_service;
    protected ?ilContainerUserFilter $container_user_filter = null;
    protected ?Standard $ui_filter = null;
    protected bool $edit_order = false;
    protected bool $adminCommands = false;
    protected string $requested_redirectSource = "";
    protected int $current_position = 0;
    protected ClipboardManager $clipboard;
    protected StandardGUIRequest $std_request;
    protected ViewManager $view_manager;
    protected ilComponentFactory $component_factory;
    protected \ILIAS\Style\Content\DomainService $content_style_domain;

    public function __construct(
        $a_data,
        int $a_id,
        bool $a_call_by_reference = true,
        bool $a_prepare_output = true
    ) {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];
        $this->ctrl = $DIC->ctrl();
        $this->tabs = $DIC->tabs();
        $this->settings = $DIC->settings();
        $this->user = $DIC->user();
        $this->access = $DIC->access();
        $this->tree = $DIC->repositoryTree();
        $this->error = $DIC["ilErr"];
        $this->obj_definition = $DIC["objDefinition"];
        $this->rbacadmin = $DIC->rbac()->admin();
        $this->rbacreview = $DIC->rbac()->review();
        $this->log = $DIC["ilLog"];
        $this->obj_data_cache = $DIC["ilObjDataCache"];
        $this->toolbar = $DIC->toolbar();
        $this->app_event_handler = $DIC["ilAppEventHandler"];
        $this->ui = $DIC->ui();
        $this->global_screen = $DIC->globalScreen();
        $this->component_factory = $DIC["component.factory"];
        $rbacsystem = $DIC->rbac()->system();
        $lng = $DIC->language();

        $this->rbacsystem = $rbacsystem;

        $lng->loadLanguageModule("cntr");
        $lng->loadLanguageModule('cont');

        // prepare output things should generally be made in executeCommand
        // method (maybe dependent on current class/command
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);

        $this->clipboard = $DIC
            ->repository()
            ->internal()
            ->domain()
            ->clipboard();
        $this->std_request = $DIC
            ->container()
            ->internal()
            ->gui()
            ->standardRequest();
        $this->requested_redirectSource = $this->std_request->getRedirectSource();
        $this->view_manager = $DIC
            ->container()
            ->internal()
            ->domain()
            ->content()
            ->view();

        $this->container_filter_service = new ilContainerFilterService();
        $this->initFilter();
        $cs = $DIC->contentStyle();
        $this->content_style_gui = $cs->gui();
        $this->content_style_domain = $cs->domain();
    }

    public function executeCommand(): void
    {
        $tpl = $this->tpl;

        $next_class = $this->ctrl->getNextClass();
        $cmd = $this->ctrl->getCmd("render");

        switch ($next_class) {
            // page editing
            case "ilcontainerpagegui":
                if ($this->requested_redirectSource !== "ilinternallinkgui") {
                    $ret = $this->forwardToPageObject();
                    $tpl->setContent($ret);
                } else {
                    return;
                }
                break;

            default:
                $this->prepareOutput();
                $cmd .= "Object";
                $this->$cmd();
                break;
        }
    }

    protected function getEditFormValues(): array
    {
        $values = parent::getEditFormValues();

        $values['didactic_type'] =
            'dtpl_' . ilDidacticTemplateObjSettings::lookupTemplateId($this->object->getRefId());

        return $values;
    }

    protected function afterUpdate(): void
    {
        // check if template is changed
        $current_tpl_id = ilDidacticTemplateObjSettings::lookupTemplateId(
            $this->object->getRefId()
        );
        $new_tpl_id = $this->getDidacticTemplateVar('dtpl');

        if ($new_tpl_id !== $current_tpl_id) {
            // redirect to didactic template confirmation
            $this->ctrl->setReturn($this, 'edit');
            $this->ctrl->setCmdClass('ildidactictemplategui');
            $this->ctrl->setCmd('confirmTemplateSwitch');
            $dtpl_gui = new ilDidacticTemplateGUI($this, $new_tpl_id);
            $this->ctrl->forwardCommand($dtpl_gui);
            return;
        }
        parent::afterUpdate();
    }


    public function forwardToPageObject(): string
    {
        $lng = $this->lng;
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $cmd = $ilCtrl->getCmd();
        if (in_array($cmd, ["displayMediaFullscreen", "downloadFile", "displayMedia"])) {
            $this->checkPermission("read");
        } else {
            $this->checkPermission("write");
        }

        $ilTabs->clearTargets();

        if ($this->requested_redirectSource === "ilinternallinkgui") {
            exit;
        }

        $ilTabs->setBackTarget(
            $lng->txt("back"),
            $this->ctrl->getLinkTargetByClass("ilcontainerpagegui", "edit")
        );

        // page object

        $lng->loadLanguageModule("content");
        $this->content_style_gui->addCss($this->tpl, $this->object->getRefId());
        // $this->tpl->setCurrentBlock("SyntaxStyle");
        $this->tpl->addCss(ilObjStyleSheet::getSyntaxStylePath());
        // $this->tpl->parseCurrentBlock();

        if (!ilContainerPage::_exists(
            "cont",
            $this->object->getId()
        )) {
            // doesn't exist -> create new one
            $new_page_object = new ilContainerPage();
            $new_page_object->setParentId($this->object->getId());
            $new_page_object->setId($this->object->getId());
            $new_page_object->createFromXML();
        }

        // get page object
        $page_gui = new ilContainerPageGUI($this->object->getId());
        $style = $this->content_style_domain->styleForRefId($this->object->getRefId());
        $page_gui->setStyleId(
            $style->getEffectiveStyleId()
        );

        $page_gui->setTemplateTargetVar("ADM_CONTENT");
        $page_gui->setFileDownloadLink("");
        //$page_gui->setLinkParams($this->ctrl->getUrlParameterString()); // todo
        $page_gui->setPresentationTitle("");
        $page_gui->setTemplateOutput(false);

        // style tab
        $page_gui->setTabHook($this, "addPageTabs");

        return $this->ctrl->forwardCommand($page_gui);
    }

    public function addPageTabs(): void
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;

        $ilTabs->addTarget(
            "obj_sty",
            $ilCtrl->getLinkTargetByClass("ilObjectContentStyleSettingsGUI", ''),
            "editStyleProperties",
            "ilobjectcontentstylesettingsgui"
        );
    }

    public function getContainerPageHTML(): string
    {
        $ilSetting = $this->settings;
        $ilUser = $this->user;

        if (!$ilSetting->get("enable_cat_page_edit") || $this->object->filteredSubtree()) {
            return "";
        }

        // if page does not exist, return nothing
        if (!ilPageUtil::_existsAndNotEmpty(
            "cont",
            $this->object->getId()
        )) {
            return "";
        }
        $this->content_style_gui->addCss($this->tpl, $this->object->getRefId());
        $this->tpl->setCurrentBlock("SyntaxStyle");
        $this->tpl->setVariable(
            "LOCATION_SYNTAX_STYLESHEET",
            ilObjStyleSheet::getSyntaxStylePath()
        );
        $this->tpl->parseCurrentBlock();

        // get page object
        $ot = ilObjectTranslation::getInstance($this->object->getId());
        $lang = $ot->getEffectiveContentLang($ilUser->getCurrentLanguage(), "cont");
        $page_gui = new ilContainerPageGUI($this->object->getId(), 0, $lang);
        $style = $this->content_style_domain->styleForRefId($this->object->getRefId());
        $page_gui->setStyleId($style->getEffectiveStyleId());

        $page_gui->setPresentationTitle("");
        $page_gui->setTemplateOutput(false);
        $page_gui->setHeader("");
        $ret = $page_gui->showPage();

        //$ret = "<div style='background-color: white; padding:5px; margin-bottom: 30px;'>".$ret."</div>";

        //$ret =& $page_gui->executeCommand();
        return $ret;
    }

    public function prepareOutput(bool $show_subobjects = true): bool
    {
        if (parent::prepareOutput($show_subobjects)) {    // return false in admin mode
            if ($show_subobjects === true && $this->getCreationMode() === false) {
                ilMemberViewGUI::showMemberViewSwitch($this->object->getRefId());
            }
        }
        return true;
    }

    protected function setTitleAndDescription(): void
    {
        if (ilContainer::_lookupContainerSetting($this->object->getId(), "hide_header_icon_and_title")) {
            $this->tpl->setTitle($this->object->getTitle(), true);
        } else {
            $this->tpl->setTitle($this->object->getTitle());
            $this->tpl->setDescription($this->object->getLongDescription());

            // set tile icon
            $icon = ilObject::_getIcon($this->object->getId(), "big", $this->object->getType());
            $this->tpl->setTitleIcon($icon, $this->lng->txt("obj_" . $this->object->getType()));

            $lgui = ilObjectListGUIFactory::_getListGUIByType($this->object->getType());
            $lgui->initItem($this->object->getRefId(), $this->object->getId(), $this->object->getType());
            $this->tpl->setAlertProperties($lgui->getAlertProperties());
        }
    }

    protected function showPossibleSubObjects(): void
    {
        if ($this->isActiveAdministrationPanel() || $this->isActiveOrdering()) {
            return;
        }
        $gui = new ilObjectAddNewItemGUI($this->object->getRefId());
        $gui->render();
    }

    public function getContentGUI(): ilContainerContentGUI
    {
        $view_mode = $this->object->getViewMode();
        if ($this->object->filteredSubtree()) {
            $view_mode = ilContainer::VIEW_SIMPLE;
        }
        switch ($view_mode) {
            // all items in one block
            case ilContainer::VIEW_SIMPLE:
                $container_view = new ilContainerSimpleContentGUI($this);
                break;

            case ilContainer::VIEW_OBJECTIVE:
                $container_view = new ilContainerObjectiveGUI($this);
                break;

                // all items in one block
            case ilContainer::VIEW_SESSIONS:
            case ilCourseConstants::IL_CRS_VIEW_TIMING: // not nice this workaround
                $container_view = new ilContainerSessionsContentGUI($this);
                break;

                // all items in one block
            case ilContainer::VIEW_BY_TYPE:
            default:
                $container_view = new ilContainerByTypeContentGUI($this, $this->container_user_filter);
                break;
        }
        return $container_view;
    }

    public function renderObject(): void
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $ilSetting = $this->settings;
        $user = $this->user;
        $toolbar = $this->toolbar;
        $lng = $this->lng;

        $container_view = $this->getContentGUI();

        $this->setContentSubTabs();
        if ($this->isActiveAdministrationPanel()) {
            $ilTabs->activateSubTab("manage");
        } else {
            $ilTabs->activateSubTab("view_content");
        }

        $container_view->setOutput();

        $this->adminCommands = $container_view->adminCommands;

        $is_container_cmd = strtolower($this->std_request->getCmdClass()) === strtolower(get_class($this))
            || ($this->std_request->getCmdClass() === "");

        // it is important not to show the subobjects/admin panel here, since
        // we will create nested forms in case, e.g. a news/calendar item is added
        if ($is_container_cmd) {
            $this->showAdministrationPanel();
            $this->showPossibleSubObjects();

            if (is_object($this->object) &&
                $user->getId() !== ANONYMOUS_USER_ID &&
                $this->rbacsystem->checkAccess("write", $this->object->getRefId())
            ) {
                if ($ilSetting->get("enable_cat_page_edit")) {
                    if (!$this->isActiveAdministrationPanel() &&
                        !$this->isActiveOrdering() &&
                        $this->supportsPageEditor()
                    ) {
                        $toolbar->addButton(
                            $lng->txt("cntr_text_media_editor"),
                            $ilCtrl->getLinkTarget($this, "editPageFrame")
                        );
                    }
                }
            }
        }

        $this->showContainerFilter();

        $this->showPermanentLink();

        // add tree updater javascript
        if ($this->requested_ref_id > 1 && $ilSetting->get("rep_tree_synchronize")) {
            $ilCtrl->setParameter($this, "active_node", $this->requested_ref_id);
        }
    }

    protected function supportsPageEditor(): bool
    {
        return true;
    }

    /**
     * render the object
     */
    public function renderBlockAsynchObject(): void
    {
        $container_view = $this->getContentGUI();
        echo $container_view->getSingleTypeBlockAsynch(
            $this->std_request->getType()
        );
        exit;
    }

    public function setContentSubTabs(): void
    {
        $this->addStandardContainerSubTabs();
    }

    public function showAdministrationPanel(): void
    {
        global $DIC;

        $ilAccess = $this->access;
        $lng = $this->lng;

        $main_tpl = $DIC->ui()->mainTemplate();

        $lng->loadLanguageModule('cntr');

        if ($this->clipboard->hasEntries()) {
            // #11545
            $main_tpl->setPageFormAction($this->ctrl->getFormAction($this));

            $toolbar = new ilToolbarGUI();
            $this->ctrl->setParameter($this, "type", "");
            $this->ctrl->setParameter($this, "item_ref_id", "");

            $toolbar->addFormButton(
                $this->lng->txt('paste_clipboard_items'),
                'paste'
            );

            $toolbar->addFormButton(
                $this->lng->txt('clear_clipboard'),
                'clear'
            );

            $main_tpl->addAdminPanelToolbar($toolbar, true, false);
        } elseif ($this->isActiveAdministrationPanel()) {
            // #11545
            $main_tpl->setPageFormAction($this->ctrl->getFormAction($this));

            $toolbar = new ilToolbarGUI();
            $this->ctrl->setParameter($this, "type", "");
            $this->ctrl->setParameter($this, "item_ref_id", "");

            if ($this->object->gotItems()) {
                $toolbar->setLeadingImage(
                    ilUtil::getImagePath("arrow_upright.svg"),
                    $lng->txt("actions")
                );
                $toolbar->addFormButton(
                    $this->lng->txt('delete_selected_items'),
                    'delete'
                );
                $toolbar->addFormButton(
                    $this->lng->txt('move_selected_items'),
                    'cut'
                );
                $toolbar->addFormButton(
                    $this->lng->txt('copy_selected_items'),
                    'copy'
                );
                $toolbar->addFormButton(
                    $this->lng->txt('link_selected_items'),
                    'link'
                );
                // add download button if multi download enabled
                $folder_set = new ilSetting('fold');
                if ((bool) $folder_set->get('enable_multi_download') === true) {
                    $toolbar->addSeparator();
                    $toolbar->addFormButton(
                        $this->lng->txt('download_selected_items'),
                        'download'
                    );
                }
            }
            if ($this->object->getType() === 'crs' || $this->object->getType() === 'grp') {
                if ($this->object->gotItems()) {
                    $toolbar->addSeparator();
                }

                $toolbar->addButton(
                    $this->lng->txt('cntr_adopt_content'),
                    $this->ctrl->getLinkTargetByClass(
                        'ilObjectCopyGUI',
                        'adoptContent'
                    )
                );
            }

            $main_tpl->addAdminPanelToolbar(
                $toolbar,
                $this->object->gotItems() && !$this->clipboard->hasEntries(),
                $this->object->gotItems() && !$this->clipboard->hasEntries()
            );

            // form action needed, see http://www.ilias.de/mantis/view.php?id=9630
            if ($this->object->gotItems()) {
                $main_tpl->setPageFormAction($this->ctrl->getFormAction($this));
            }
        } elseif ($this->edit_order) {
            if ($this->object->gotItems() && $ilAccess->checkAccess("write", "", $this->object->getRefId())) {
                if ($this->isActiveOrdering()) {
                    // #11843
                    $main_tpl->setPageFormAction($this->ctrl->getFormAction($this));

                    $toolbar = new ilToolbarGUI();
                    $this->ctrl->setParameter($this, "type", "");
                    $this->ctrl->setParameter($this, "item_ref_id", "");

                    $toolbar->addFormButton(
                        $this->lng->txt('sorting_save'),
                        'saveSorting'
                    );

                    $main_tpl->addAdminPanelToolbar($toolbar, true, false);
                }
            }
        }
        // bugfix mantis 24559
        // undoing an erroneous change inside mantis 23516 by adding "Download Multiple Objects"-functionality for non-admins
        // as they don't have the possibility to use the multi-download-capability of the manage-tab
        elseif ($this->isMultiDownloadEnabled()) {
            // bugfix mantis 0021272
            $ref_id = $this->requested_ref_id;
            $num_files = $this->tree->getChildsByType($ref_id, "file");
            $num_folders = $this->tree->getChildsByType($ref_id, "fold");
            if (count($num_files) > 0 || count($num_folders) > 0) {
                // #11843
                $GLOBALS['tpl']->setPageFormAction($this->ctrl->getFormAction($this));

                $toolbar = new ilToolbarGUI();
                $this->ctrl->setParameter($this, "type", "");
                $this->ctrl->setParameter($this, "item_ref_id", "");

                $toolbar->addFormButton(
                    $this->lng->txt('download_selected_items'),
                    'download'
                );

                $GLOBALS['tpl']->addAdminPanelToolbar(
                    $toolbar,
                    $this->object->gotItems(),
                    $this->object->gotItems()
                );
            } else {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt('msg_no_downloadable_objects'), true);
            }
        }
    }

    public function showPermanentLink(): void
    {
        global $DIC;

        $tpl = $DIC->ui()->mainTemplate();

        $tpl->setPermanentLink(
            $this->object->getType(),
            $this->object->getRefId(),
            "",
            "_top"
        );
    }

    public function editPageFrameObject(): void
    {
        $this->ctrl->redirectByClass([static::class, "ilcontainerpagegui"], "edit");
    }

    public function cancelPageContentObject(): void
    {
        $this->ctrl->redirect($this, "");
    }

    public function showLinkListObject(): void
    {
        $lng = $this->lng;
        $tree = $this->tree;

        $cnt = [];

        $tpl = new ilGlobalTemplate(
            "tpl.container_link_help.html",
            true,
            true,
            "Services/Container"
        );

        $type_ordering = [
            "cat",
            "fold",
            "crs",
            "grp",
            "chat",
            "frm",
            "lres",
            "glo",
            "webr",
            "file",
            "exc",
            "tst",
            "svy",
            "mep",
            "qpl",
            "spl"
        ];

        $childs = $tree->getChilds($this->requested_ref_id);
        foreach ($childs as $child) {
            if (in_array($child["type"], ["lm", "sahs", "htlm"])) {
                $cnt["lres"]++;
            } else {
                $cnt[$child["type"]]++;
            }
        }

        $tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
        $tpl->setVariable("TXT_HELP_HEADER", $lng->txt("help"));
        foreach ($type_ordering as $type) {
            $tpl->setCurrentBlock("row");
            if ($type !== "lres") {
                $tpl->setVariable(
                    "TYPE",
                    $lng->txt("objs_" . $type) .
                    " (" . ((int) $cnt[$type]) . ")"
                );
            } else {
                $tpl->setVariable(
                    "TYPE",
                    $lng->txt("obj_lrss") .
                    " (" . ((int) $cnt["lres"]) . ")"
                );
            }
            $tpl->setVariable("TXT_LINK", "[list-" . $type . "]");
            $tpl->parseCurrentBlock();
        }
        $tpl->printToStdout();
        exit;
    }

    public function clearAdminCommandsDetermination(): void
    {
        $this->adminCommands = false;
    }

    public function addHeaderRow(
        ilTemplate $a_tpl,
        string $a_type,
        bool $a_show_image = true
    ): void {
        $icon = ilUtil::getImagePath("icon_" . $a_type . ".svg");
        $title = $this->lng->txt("objs_" . $a_type);

        if ($a_show_image) {
            $a_tpl->setCurrentBlock("container_header_row_image");
            $a_tpl->setVariable("HEADER_IMG", $icon);
            $a_tpl->setVariable("HEADER_ALT", $title);
        } else {
            $a_tpl->setCurrentBlock("container_header_row");
        }

        $a_tpl->setVariable("BLOCK_HEADER_CONTENT", $title);
        $a_tpl->parseCurrentBlock();
        //$a_tpl->touchBlock("container_row");
    }

    public function addStandardRow(
        ilTemplate $a_tpl,
        string $a_html,
        int $a_item_ref_id = null,
        int $a_item_obj_id = null,
        string $a_image_type = ""
    ): void {
        $ilSetting = $this->settings;

        $nbsp = true;
        if ($ilSetting->get("icon_position_in_lists") === "item_rows") {
            $icon = ilUtil::getImagePath("icon_" . $a_image_type . ".svg");
            $alt = $this->lng->txt("obj_" . $a_image_type);

            if ($ilSetting->get('custom_icons')) {
                global $DIC;
                /** @var ilObjectCustomIconFactory $customIconFactory */
                $customIconFactory = $DIC['object.customicons.factory'];
                $customIcon = $customIconFactory->getPresenterByObjId($a_item_obj_id, $a_image_type);

                if ($customIcon->exists()) {
                    $icon = $customIcon->getFullPath();
                }
            }

            $a_tpl->setCurrentBlock("block_row_image");
            $a_tpl->setVariable("ROW_IMG", $icon);
            $a_tpl->setVariable("ROW_ALT", $alt);
            $a_tpl->parseCurrentBlock();
            $nbsp = false;
        }

        if ($this->isActiveAdministrationPanel()) {
            $a_tpl->setCurrentBlock("block_row_check");
            $a_tpl->setVariable("ITEM_ID", $a_item_ref_id);
            $a_tpl->parseCurrentBlock();
            $nbsp = false;
        }
        if ($this->isActiveAdministrationPanel() &&
            ilContainerSortingSettings::_lookupSortMode($this->object->getId()) === ilContainer::SORT_MANUAL) {
            $a_tpl->setCurrentBlock('block_position');
            $a_tpl->setVariable('POS_TYPE', $a_image_type);
            $a_tpl->setVariable('POS_ID', $a_item_ref_id);
            $a_tpl->setVariable('POSITION', sprintf('%.1f', $this->current_position++));
            $a_tpl->parseCurrentBlock();
        }
        if ($nbsp) {
            $a_tpl->setVariable("ROW_NBSP", "&nbsp;");
        }
        $a_tpl->setCurrentBlock("container_standard_row");
        $a_tpl->setVariable("BLOCK_ROW_CONTENT", $a_html);
        $a_tpl->parseCurrentBlock();
        $a_tpl->touchBlock("container_row");
    }

    public function addMessageRow(
        ilTemplate $a_tpl,
        string $a_message,
        string $a_type
    ): void {
        $type = $this->lng->txt("obj_" . $a_type);
        $a_message = str_replace("[type]", $type, $a_message);

        $a_tpl->setVariable("ROW_NBSP", "&nbsp;");

        $a_tpl->setCurrentBlock("container_standard_row");
        $a_tpl->setVariable(
            "BLOCK_ROW_CONTENT",
            $a_message
        );
        $a_tpl->parseCurrentBlock();
        $a_tpl->touchBlock("container_row");
    }

    public function setPageEditorTabs(): void
    {
        $lng = $this->lng;

        if (!$this->isActiveAdministrationPanel()
            || strtolower($this->ctrl->getCmdClass()) !== "ilcontainerpagegui") {
            return;
        }

        $lng->loadLanguageModule("content");
        //$tabs_gui = new ilTabsGUI();
        //$tabs_gui->setSubTabs();

        // back to upper context
        $this->tabs_gui->setBackTarget(
            $this->lng->txt("obj_cat"),
            $this->ctrl->getLinkTarget($this, ""),
            ilFrameTargetInfo::_getFrame("MainContent")
        );

        $this->tabs_gui->addTarget(
            "edit",
            $this->ctrl->getLinkTargetByClass("ilcontainerpagegui", "view"),
            ["", "view"],
            "ilcontainerpagegui"
        );

        //$this->tpl->setTabs($tabs_gui->getHTML());
    }

    /**
     * Add standard container subtabs for view, manage, oderdering and text/media editor link
     */
    public function addStandardContainerSubTabs(
        bool $a_include_view = true
    ): void {
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;

        if (!is_object($this->object)) {
            return;
        }

        if ($a_include_view && $this->rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            if (!$this->isActiveAdministrationPanel()) {
                $ilTabs->addSubTab("view_content", $lng->txt("view"), $ilCtrl->getLinkTargetByClass(static::class, "view"));
            } else {
                $ilTabs->addSubTab(
                    "view_content",
                    $lng->txt("view"),
                    $ilCtrl->getLinkTarget($this, "disableAdministrationPanel")
                );
            }
        }

        if ($ilUser->getId() !== ANONYMOUS_USER_ID &&
            (
                $this->adminCommands ||
                (is_object($this->object) &&
                    ($this->rbacsystem->checkAccess("write", $this->object->getRefId())))
                ||
                (is_object($this->object) &&
                    ($this->object->getHiddenFilesFound())) ||
                $this->clipboard->hasEntries()
            )
        ) {
            if ($this->isActiveAdministrationPanel()) {
                $ilTabs->addSubTab("manage", $lng->txt("cntr_manage"), $ilCtrl->getLinkTarget($this, ""));
            } else {
                $ilTabs->addSubTab(
                    "manage",
                    $lng->txt("cntr_manage"),
                    $ilCtrl->getLinkTarget($this, "enableAdministrationPanel")
                );
            }
        }
        if (is_object($this->object) &&
            $ilUser->getId() !== ANONYMOUS_USER_ID &&
            $this->rbacsystem->checkAccess("write", $this->object->getRefId()) /* &&
            $this->object->getOrderType() == ilContainer::SORT_MANUAL */ // always on because of custom block order
        ) {
            $ilTabs->addSubTab("ordering", $lng->txt("cntr_ordering"), $ilCtrl->getLinkTarget($this, "editOrder"));
        }
    }

    protected function getTabs(): void
    {
        $rbacsystem = $this->rbacsystem;
        $ilCtrl = $this->ctrl;

        // edit permissions
        if ($rbacsystem->checkAccess('edit_permission', $this->ref_id)) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass([get_class($this), 'ilpermissiongui'], "perm"),
                ["perm", "info", "owner"],
                'ilpermissiongui'
            );
            if ($ilCtrl->getNextClass() === "ilpermissiongui") {
                $this->tabs_gui->activateTab("perm_settings");
            }
        }

        // show clipboard
        if (strtolower($this->std_request->getBaseClass()) === "ilrepositorygui" &&
            $this->clipboard->hasEntries()) {
            $this->tabs_gui->addTarget(
                "clipboard",
                $this->ctrl->getLinkTarget($this, "clipboard"),
                "clipboard",
                get_class($this)
            );
        }
    }

    //*****************
    // COMMON METHODS (may be overwritten in derived classes
    // if special handling is necessary)
    //*****************

    public function enableAdministrationPanelObject(): void
    {
        $this->view_manager->setAdminView();
        $this->ctrl->redirect($this, "render");
    }

    public function disableAdministrationPanelObject(): void
    {
        $this->view_manager->setContentView();
        $this->ctrl->redirect($this, "render");
    }

    public function editOrderObject(): void
    {
        $ilTabs = $this->tabs;

        $this->edit_order = true;
        $this->view_manager->setContentView();
        $this->renderObject();

        $ilTabs->activateSubTab("ordering");
    }

    // Check if ordering is enabled
    public function isActiveOrdering(): bool
    {
        return $this->edit_order;
    }

    public function isActiveItemOrdering(): bool
    {
        if ($this->isActiveOrdering()) {
            return (ilContainerSortingSettings::_lookupSortMode($this->object->getId()) === ilContainer::SORT_MANUAL);
        }
        return false;
    }


    // bugfix mantis 24559
    // undoing an erroneous change inside mantis 23516 by adding "Download Multiple Objects"-functionality for non-admins
    // as they don't have the possibility to use the multi-download-capability of the manage-tab
    public function enableMultiDownloadObject(): void
    {
        $this->multi_download_enabled = true;
        $this->renderObject();
    }

    public function isMultiDownloadEnabled(): bool
    {
        return $this->multi_download_enabled;
    }

    /**
     * cut object(s) out from a container and write the information to clipboard
     * @access    public
     */
    public function cutObject(): void
    {
        $rbacsystem = $this->rbacsystem;
        $ilCtrl = $this->ctrl;
        $ilErr = $this->error;

        $ids = $this->std_request->getSelectedIds();
        $no_cut = [];

        if (count($ids) === 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "");
        }

        // FOR ALL OBJECTS THAT SHOULD BE COPIED
        foreach ($ids as $ref_id) {
            // GET COMPLETE NODE_DATA OF ALL SUBTREE NODES
            $node_data = $this->tree->getNodeData($ref_id);
            $subtree_nodes = $this->tree->getSubTree($node_data);

            $all_node_data[] = $node_data;
            $all_subtree_nodes[] = $subtree_nodes;

            // CHECK DELETE PERMISSION OF ALL OBJECTS IN ACTUAL SUBTREE
            foreach ($subtree_nodes as $node) {
                if ($node['type'] === 'rolf') {
                    continue;
                }

                if (!$rbacsystem->checkAccess('delete', $node["ref_id"])) {
                    $no_cut[] = $node["ref_id"];
                }
            }
        }
        // IF THERE IS ANY OBJECT WITH NO PERMISSION TO 'delete'
        if (count($no_cut)) {
            $titles = [];
            foreach ($no_cut as $cut_id) {
                $titles[] = ilObject::_lookupTitle(ilObject::_lookupObjId($cut_id));
            }
            $ilErr->raiseError(
                $this->lng->txt("msg_no_perm_cut") . " " . implode(',', $titles),
                $ilErr->MESSAGE
            );
        }
        $this->clipboard->setParent($this->requested_ref_id);
        $this->clipboard->setCmd($ilCtrl->getCmd());
        $this->clipboard->setRefIds($this->std_request->getSelectedIds());

        $this->tpl->setOnScreenMessage('info', $this->lng->txt("msg_cut_clipboard"), true);

        $this->initAndDisplayMoveIntoObjectObject();
    }

    /**
     * Copy object(s) out from a container and write the information to clipboard
     * It is not possible to copy multiple objects at once.
     */
    public function copyObject(): void
    {
        $rbacsystem = $this->rbacsystem;
        $ilCtrl = $this->ctrl;
        $objDefinition = $this->obj_definition;
        $ilErr = $this->error;

        $no_copy = [];

        $ids = $this->std_request->getSelectedIds();

        if (count($ids) === 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "");
        }

        // FOR ALL OBJECTS THAT SHOULD BE COPIED
        $containers = 0;
        foreach ($ids as $ref_id) {
            // GET COMPLETE NODE_DATA OF ALL SUBTREE NODES
            $node_data = $this->tree->getNodeData($ref_id);

            // count containers
            if ($objDefinition->isContainer($node_data["type"])) {
                $containers++;
            }

            $subtree_nodes = $this->tree->getSubTree($node_data);

            $all_node_data[] = $node_data;
            $all_subtree_nodes[] = $subtree_nodes;

            // CHECK COPY PERMISSION OF ALL OBJECTS IN ACTUAL SUBTREE
            foreach ($subtree_nodes as $node) {
                if ($node['type'] === 'rolf') {
                    continue;
                }

                if (!$rbacsystem->checkAccess('visible,read,copy', $node["ref_id"])) {
                    $no_copy[] = $node["ref_id"];
                }
            }
        }

        if ($containers > 0 && count($this->std_request->getSelectedIds()) > 1) {
            $ilErr->raiseError($this->lng->txt("cntr_container_only_on_their_own"), $ilErr->MESSAGE);
        }

        // IF THERE IS ANY OBJECT WITH NO PERMISSION TO 'delete'
        if (is_array($no_copy) && count($no_copy)) {
            $titles = [];
            foreach ($no_copy as $copy_id) {
                $titles[] = ilObject::_lookupTitle(ilObject::_lookupObjId($copy_id));
            }
            $ilErr->raiseError(
                $this->lng->txt("msg_no_perm_copy") . " " . implode(',', $titles),
                $ilErr->MESSAGE
            );
        }

        // if we have a single container, set it as source id and redirect to ilObjectCopyGUI
        $ids = $this->std_request->getSelectedIds();
        if (count($ids) === 1) {
            $ilCtrl->setParameterByClass("ilobjectcopygui", "source_id", $ids[0]);
        } else {
            $ilCtrl->setParameterByClass("ilobjectcopygui", "source_ids", implode("_", $ids));
        }
        $ilCtrl->redirectByClass("ilobjectcopygui", "initTargetSelection");

        $this->clipboard->setParent($this->requested_ref_id);
        $this->clipboard->setCmd($ilCtrl->getCmd());
        $this->clipboard->setRefIds($ids);

        $this->tpl->setOnScreenMessage('info', $this->lng->txt("msg_copy_clipboard"), true);

        $this->initAndDisplayCopyIntoMultipleObjectsObject();
    }

    public function downloadObject(): void
    {
        $ilErr = $this->error;
        // This variable determines whether the task has been initiated by a folder's action drop-down to prevent a folder
        // duplicate inside the zip.
        $initiated_by_folder_action = false;

        $ids = $this->std_request->getSelectedIds();

        if (count($ids) === 0) {
            $object = ilObjectFactory::getInstanceByRefId($this->requested_ref_id);
            $object_type = $object->getType();
            if ($object_type === "fold") {
                $ids = [$this->requested_ref_id];
                $initiated_by_folder_action = true;
            } else {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"), true);
                $this->ctrl->redirect($this, "");
            }
        }

        $download_job = new ilDownloadContainerFilesBackgroundTask(
            $GLOBALS['DIC']->user()->getId(),
            $ids,
            $initiated_by_folder_action
        );

        $download_job->setBucketTitle($this->getBucketTitle());
        if ($download_job->run()) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_bt_download_started'), true);
        }
        $GLOBALS['DIC']->ctrl()->redirect($this);
    }

    public function getBucketTitle(): string
    {
        return ilFileUtils::getASCIIFilename($this->object->getTitle());
    }

    /**
     * create an new reference of an object in tree
     * it's like a hard link of unix
     */
    public function linkObject(): void
    {
        $rbacsystem = $this->rbacsystem;
        $ilCtrl = $this->ctrl;
        $ilErr = $this->error;

        $no_cut = [];
        $no_link = [];

        $ids = $this->std_request->getSelectedIds();

        if (count($ids) === 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "");
        }

        // CHECK ACCESS
        foreach ($ids as $ref_id) {
            if (!$rbacsystem->checkAccess('delete', $ref_id)) {
                $no_cut[] = $ref_id;
            }

            $object = ilObjectFactory::getInstanceByRefId($ref_id);

            if (!$this->obj_definition->allowLink($object->getType())) {
                $no_link[] = $object->getType();
            }
        }

        // NO ACCESS
        if (count($no_cut)) {
            $ilErr->raiseError(
                $this->lng->txt("msg_no_perm_link") . " " .
                implode(',', $no_cut),
                $ilErr->MESSAGE
            );
        }

        if (count($no_link)) {
            //#12203
            $ilErr->raiseError($this->lng->txt("msg_obj_no_link"), $ilErr->MESSAGE);
        }

        $this->clipboard->setParent($this->requested_ref_id);
        $this->clipboard->setCmd($ilCtrl->getCmd());
        $this->clipboard->setRefIds($ids);

        $suffix = 'p';
        if (count($this->clipboard->getRefIds()) === 1) {
            $suffix = 's';
        }
        $this->tpl->setOnScreenMessage('info', $this->lng->txt("msg_link_clipboard_" . $suffix), true);

        $this->initAndDisplayLinkIntoMultipleObjectsObject();
    }

    /**
     * clear clipboard and go back to last object
     */
    public function clearObject(): void
    {
        $this->clipboard->clear();

        //var_dump($this->getReturnLocation("clear",$this->ctrl->getLinkTarget($this)),get_class($this));

        // only redirect if clipboard was cleared
        if ($this->ctrl->getCmd() === "clear") {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_clear_clipboard"), true);
            // fixed mantis 0018474: Clear Clipboard redirects to Subtab View, instead of Subtab "Edit Multiple"
            $this->ctrl->redirect($this, 'render');
        }
    }

    public function performPasteIntoMultipleObjectsObject(): void
    {
        $rbacsystem = $this->rbacsystem;
        $rbacadmin = $this->rbacadmin;
        $rbacreview = $this->rbacreview;
        $ilLog = $this->log;
        $tree = $this->tree;
        $ilObjDataCache = $this->obj_data_cache;
        $ilUser = $this->user;
        $ilErr = $this->error;
        $lng = $this->lng;
        $ui = $this->ui;

        $exists = [];
        $is_child = [];
        $not_allowed_subobject = [];
        $no_paste = [];

        $command = $this->clipboard->getCmd();
        if (!in_array($command, ['cut', 'link', 'copy'])) {
            $message = __METHOD__ . ": cmd was neither 'cut', 'link' nor 'copy'; may be a hack attempt!";
            $ilErr->raiseError($message, $ilErr->WARNING);
        }

        $nodes = $this->std_request->getNodes();

        if (count($nodes) === 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_at_least_one_object'));
            switch ($command) {
                case 'link':
                case 'copy':
                case 'cut':
                    $this->showPasteTreeObject();
                    break;
            }
            return;
        }

        // this loop does all checks
        $folder_objects_cache = [];
        foreach ($this->clipboard->getRefIds() as $ref_id) {
            $obj_data = ilObjectFactory::getInstanceByRefId($ref_id);
            $current_parent_id = $tree->getParentId($obj_data->getRefId());

            foreach ($nodes as $folder_ref_id) {
                if (!array_key_exists($folder_ref_id, $folder_objects_cache)) {
                    $folder_objects_cache[$folder_ref_id] = ilObjectFactory::getInstanceByRefId($folder_ref_id);
                }

                // CHECK ACCESS
                if (!$rbacsystem->checkAccess('create', $folder_ref_id, $obj_data->getType())) {
                    $no_paste[] = sprintf(
                        $this->lng->txt('msg_no_perm_paste_object_in_folder'),
                        $obj_data->getTitle() . ' [' . $obj_data->getRefId() . ']',
                        $folder_objects_cache[$folder_ref_id]->getTitle(
                        ) . ' [' . $folder_objects_cache[$folder_ref_id]->getRefId() . ']'
                    );
                }

                // CHECK IF REFERENCE ALREADY EXISTS
                if ($folder_ref_id == $current_parent_id) {
                    $exists[] = sprintf(
                        $this->lng->txt('msg_obj_exists_in_folder'),
                        $obj_data->getTitle() . ' [' . $obj_data->getRefId() . ']',
                        $folder_objects_cache[$folder_ref_id]->getTitle(
                        ) . ' [' . $folder_objects_cache[$folder_ref_id]->getRefId() . ']'
                    );
                }

                // CHECK IF PASTE OBJECT SHALL BE CHILD OF ITSELF
                if ($ref_id == $folder_ref_id || $tree->isGrandChild($ref_id, $folder_ref_id)) {
                    $is_child[] = sprintf(
                        $this->lng->txt('msg_paste_object_not_in_itself'),
                        $obj_data->getTitle() . ' [' . $obj_data->getRefId() . ']'
                    );
                }

                // CHECK IF OBJECT IS ALLOWED TO CONTAIN PASTED OBJECT AS SUBOBJECT
                if (!array_key_exists(
                    $obj_data->getType(),
                    $folder_objects_cache[$folder_ref_id]->getPossibleSubObjects()
                )) {
                    $not_allowed_subobject[] = sprintf(
                        $this->lng->txt('msg_obj_may_not_contain_objects_of_type'),
                        $folder_objects_cache[$folder_ref_id]->getTitle(
                        ) . ' [' . $folder_objects_cache[$folder_ref_id]->getRefId() . ']',
                        $lng->txt('obj_' . $obj_data->getType())
                    );
                }
            }
        }

        ////////////////////////////
        // process checking results
        $error = "";
        if ($command !== "copy" && count($exists)) {
            $error .= implode('<br />', $exists);
        }

        if (count($is_child)) {
            $error .= $error !== '' ? '<br />' : '';
            $error .= implode('<br />', $is_child);
        }

        if (count($not_allowed_subobject)) {
            $error .= $error !== '' ? '<br />' : '';
            $error .= implode('<br />', $not_allowed_subobject);
        }

        if (count($no_paste)) {
            $error .= $error !== '' ? '<br />' : '';
            $error .= implode('<br />', $no_paste);
        }

        if ($error !== '') {
            $this->tpl->setOnScreenMessage('failure', $error);
            switch ($command) {
                case 'link':
                case 'copy':
                case 'cut':
                    $this->showPasteTreeObject();
                    break;
            }
            return;
        }

        // log pasteObject call
        $ilLog->write(__METHOD__ . ", cmd: " . $command);

        ////////////////////////////////////////////////////////
        // everything ok: now paste the objects to new location

        // to prevent multiple actions via back/reload button
        $ref_ids = $this->clipboard->getRefIds();
        $this->clipboard->clear();


        // process COPY command
        if ($command === 'copy') {
            foreach ($nodes as $folder_ref_id) {
                foreach ($ref_ids as $ref_id) {
                    $revIdMapping = [];

                    $oldNode_data = $tree->getNodeData($ref_id);
                    if ($oldNode_data['parent'] == $folder_ref_id) {
                        $newTitle = ilObjFileAccess::_appendNumberOfCopyToFilename($oldNode_data['title'], null);
                        $newRef = $this->cloneNodes($ref_id, $folder_ref_id, $refIdMapping, $newTitle);
                    } else {
                        $newRef = $this->cloneNodes($ref_id, $folder_ref_id, $refIdMapping, null);
                    }

                    // BEGIN ChangeEvent: Record copy event.
                    $old_parent_data = $tree->getParentNodeData($ref_id);
                    $newNode_data = $tree->getNodeData($newRef);
                    ilChangeEvent::_recordReadEvent(
                        $oldNode_data['type'],
                        $ref_id,
                        $oldNode_data['obj_id'],
                        $ilUser->getId()
                    );
                    ilChangeEvent::_recordWriteEvent(
                        $newNode_data['obj_id'],
                        $ilUser->getId(),
                        'add',
                        $ilObjDataCache->lookupObjId((int) $folder_ref_id)
                    );
                    ilChangeEvent::_catchupWriteEvents($newNode_data['obj_id'], $ilUser->getId());
                    // END PATCH ChangeEvent: Record cut event.
                }
            }

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_cloned'), true);
        } // END COPY

        // process CUT command
        if ($command === 'cut') {
            foreach ($nodes as $folder_ref_id) {
                foreach ($ref_ids as $ref_id) {
                    // Store old parent
                    $old_parent = $tree->getParentId($ref_id);
                    $tree->moveTree($ref_id, $folder_ref_id);
                    $rbacadmin->adjustMovedObjectPermissions($ref_id, $old_parent);

                    ilConditionHandler::_adjustMovedObjectConditions($ref_id);

                    // BEGIN ChangeEvent: Record cut event.
                    $node_data = $tree->getNodeData($ref_id);
                    $old_parent_data = $tree->getNodeData($old_parent);
                    ilChangeEvent::_recordWriteEvent(
                        $node_data['obj_id'],
                        $ilUser->getId(),
                        'remove',
                        $old_parent_data['obj_id']
                    );
                    ilChangeEvent::_recordWriteEvent(
                        $node_data['obj_id'],
                        $ilUser->getId(),
                        'add',
                        $ilObjDataCache->lookupObjId((int) $folder_ref_id)
                    );
                    ilChangeEvent::_catchupWriteEvents($node_data['obj_id'], $ilUser->getId());
                    // END PATCH ChangeEvent: Record cut event.
                }

                // prevent multiple iterations for cut cmommand
                break;
            }

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_cut_copied'), true);
        } // END CUT

        // process LINK command
        if ($command === 'link') {
            $subnodes = [];
            $linked_to_folders = [];

            $rbac_log_active = ilRbacLog::isActive();

            foreach ($nodes as $folder_ref_id) {
                $linked_to_folders[$folder_ref_id] = $ilObjDataCache->lookupTitle(
                    $ilObjDataCache->lookupObjId((int) $folder_ref_id)
                );

                foreach ($ref_ids as $ref_id) {
                    // get node data
                    $top_node = $tree->getNodeData($ref_id);

                    // get subnodes of top nodes
                    $subnodes[$ref_id] = $tree->getSubTree($top_node);
                }

                // now move all subtrees to new location
                foreach ($subnodes as $key => $subnode) {
                    // first paste top_node....
                    $obj_data = ilObjectFactory::getInstanceByRefId($key);
                    $new_ref_id = $obj_data->createReference();
                    $obj_data->putInTree($folder_ref_id);
                    $obj_data->setPermissions($folder_ref_id);

                    // rbac log
                    if ($rbac_log_active) {
                        $rbac_log_roles = $rbacreview->getParentRoleIds($new_ref_id, false);
                        $rbac_log = ilRbacLog::gatherFaPa($new_ref_id, array_keys($rbac_log_roles), true);
                        ilRbacLog::add(ilRbacLog::LINK_OBJECT, $new_ref_id, $rbac_log, $key);
                    }

                    // BEGIN ChangeEvent: Record link event.
                    $node_data = $tree->getNodeData($new_ref_id);
                    ilChangeEvent::_recordWriteEvent(
                        $node_data['obj_id'],
                        $ilUser->getId(),
                        'add',
                        $ilObjDataCache->lookupObjId((int) $folder_ref_id)
                    );
                    ilChangeEvent::_catchupWriteEvents($node_data['obj_id'], $ilUser->getId());
                    // END PATCH ChangeEvent: Record link event.
                }

                $ilLog->write(__METHOD__ . ', link finished');
            }

            $links = [];
            if (count($linked_to_folders)) {
                foreach ($linked_to_folders as $ref_id => $title) {
                    $links[] = $ui->factory()->link()->standard($title, ilLink::_getLink($ref_id));
                }
            }

            $suffix = 'p';
            if (count($ref_ids) === 1) {
                $suffix = 's';
            }

            $mbox = $ui->factory()->messageBox()->success(
                $this->lng->txt('mgs_objects_linked_to_the_following_folders_' . $suffix)
            )
                       ->withLinks($links);

            $this->tpl->setOnScreenMessage('success', $ui->renderer()->render($mbox), true);
        } // END LINK

        // clear clipboard
        $this->clearObject();

        $this->ctrl->returnToParent($this);
    }

    public function initAndDisplayLinkIntoMultipleObjectsObject(): void
    {
        $this->showPasteTreeObject();
    }

    public function showPasteTreeObject(): void
    {
        $ilTabs = $this->tabs;
        $ilErr = $this->error;

        $ilTabs->setTabActive('view_content');

        if (!in_array($this->clipboard->getCmd(), ['link', 'copy', 'cut'])) {
            $message = __METHOD__ . ": Unknown action.";
            $ilErr->raiseError($message, $ilErr->WARNING);
        }
        $cmd = $this->clipboard->getCmd();

        //
        $exp = $this->getTreeSelectorGUI($cmd);
        if ($exp->handleCommand()) {
            return;
        }
        $output = $exp->getHTML();

        $txt_var = ($cmd === "copy")
            ? "copy"
            : "paste";

        // toolbars
        $t = new ilToolbarGUI();
        $t->setFormAction($this->ctrl->getFormAction($this, "performPasteIntoMultipleObjects"));

        $b = ilSubmitButton::getInstance();
        $b->setCaption($txt_var);
        $b->setCommand("performPasteIntoMultipleObjects");

        //$t->addFormButton($this->lng->txt($txt_var), "performPasteIntoMultipleObjects");
        $t->addStickyItem($b);

        $t->addSeparator();
        $this->lng->loadLanguageModule('obj');
        $t->addFormButton($this->lng->txt("obj_insert_into_clipboard"), "keepObjectsInClipboard");

        $t->addFormButton($this->lng->txt("cancel"), "cancelMoveLink");
        $t->setCloseFormTag(false);
        $t->setLeadingImage(ilUtil::getImagePath("arrow_upright.svg"), " ");
        $output = $t->getHTML() . $output;
        $t->setLeadingImage(ilUtil::getImagePath("arrow_downright.svg"), " ");
        $t->setCloseFormTag(true);
        $t->setOpenFormTag(false);
        $output .= "<br />" . $t->getHTML();

        $this->tpl->setContent($output);
    }

    /**
     * Cancel move|link
     * empty clipboard and return to parent
     */
    public function cancelMoveLinkObject(): void
    {
        $ilCtrl = $this->ctrl;

        $this->clipboard->clear();
        $ilCtrl->returnToParent($this);
    }

    public function keepObjectsInClipboardObject(): void
    {
        $ilCtrl = $this->ctrl;

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("obj_inserted_clipboard"), true);
        $ilCtrl->returnToParent($this);
    }

    public function initAndDisplayCopyIntoMultipleObjectsObject(): void
    {
        $this->showPasteTreeObject();
    }

    public function initAndDisplayMoveIntoObjectObject(): void
    {
        $this->showPasteTreeObject();
    }

    /**
     * paste object from clipboard to current place
     * Depending on the chosen command the object(s) are linked, copied or moved
     */
    public function pasteObject(): void
    {
        $rbacsystem = $this->rbacsystem;
        $rbacadmin = $this->rbacadmin;
        $ilLog = $this->log;
        $tree = $this->tree;
        $ilUser = $this->user;
        $ilCtrl = $this->ctrl;
        $ilErr = $this->error;

        $exists = [];
        $no_paste = [];
        $is_child = [];
        $not_allowed_subobject = [];


        if (!in_array($this->clipboard->getCmd(), ["cut", "link", "copy"])) {
            $message = get_class(
                $this
            ) . "::pasteObject(): cmd was neither 'cut','link' or 'copy'; may be a hack attempt!";
            $ilErr->raiseError($message, $ilErr->WARNING);
        }

        // this loop does all checks
        foreach ($this->clipboard->getRefIds() as $ref_id) {
            $obj_data = ilObjectFactory::getInstanceByRefId($ref_id);

            // CHECK ACCESS
            if (!$rbacsystem->checkAccess('create', $this->object->getRefId(), $obj_data->getType())) {
                $no_paste[] = $ref_id;
                $no_paste_titles[] = $obj_data->getTitle();
            }

            // CHECK IF REFERENCE ALREADY EXISTS
            if ($this->object->getRefId() === $this->tree->getParentId($obj_data->getRefId())) {
                $exists[] = $ref_id;
                break;
            }

            // CHECK IF PASTE OBJECT SHALL BE CHILD OF ITSELF
            if ($this->tree->isGrandChild($ref_id, $this->object->getRefId())) {
                $is_child[] = ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id));
            }

            if ($ref_id == $this->object->getRefId()) {
                $is_child[] = ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id));
            }

            // CHECK IF OBJECT IS ALLOWED TO CONTAIN PASTED OBJECT AS SUBOBJECT
            $obj_type = $obj_data->getType();

            if (!array_key_exists($obj_type, $this->object->getPossibleSubObjects())) {
                $not_allowed_subobject[] = $obj_data->getType();
            }
        }

        ////////////////////////////
        // process checking results
        // BEGIN WebDAV: Copying an object into the same container is allowed
        if (count($exists) > 0 && $this->clipboard->getCmd() !== "copy") {
            // END WebDAV: Copying an object into the same container is allowed
            $ilErr->raiseError($this->lng->txt("msg_obj_exists"), $ilErr->MESSAGE);
        }

        if (count($is_child) > 0) {
            $ilErr->raiseError(
                $this->lng->txt("msg_not_in_itself") . " " . implode(',', $is_child),
                $ilErr->MESSAGE
            );
        }

        if (count($not_allowed_subobject) > 0) {
            $ilErr->raiseError(
                $this->lng->txt("msg_may_not_contain") . " " . implode(',', $not_allowed_subobject),
                $ilErr->MESSAGE
            );
        }

        if (count($no_paste) > 0) {
            $ilErr->raiseError(
                $this->lng->txt("msg_no_perm_paste") . " " .
                implode(',', $no_paste),
                $ilErr->MESSAGE
            );
        }

        // log pasteObject call
        $ilLog->write("ilObjectGUI::pasteObject(), cmd: " . $this->clipboard->getCmd());

        ////////////////////////////////////////////////////////
        // everything ok: now paste the objects to new location

        // to prevent multiple actions via back/reload button
        $ref_ids = $this->clipboard->getRefIds();

        // save cmd for correct message output after clearing the clipboard
        $last_cmd = $this->clipboard->getCmd();

        // BEGIN WebDAV: Support a copy command in the repository
        // process COPY command
        if ($this->clipboard->getCmd() === "copy") {
            $this->clipboard->clear();

            // new implementation, redirects to ilObjectCopyGUI
            $ilCtrl->setParameterByClass("ilobjectcopygui", "target", $this->object->getRefId());
            if (count($ref_ids) === 1) {
                $ilCtrl->setParameterByClass("ilobjectcopygui", "source_id", $ref_ids[0]);
            } else {
                $ilCtrl->setParameterByClass("ilobjectcopygui", "source_ids", implode("_", $ref_ids));
            }
            $ilCtrl->redirectByClass("ilobjectcopygui", "saveTarget");

            $ilLog->write("ilObjectGUI::pasteObject(), copy finished");
        }
        // END WebDAV: Support a Copy command in the repository

        // process CUT command
        if ($this->clipboard->getCmd() === "cut") {
            foreach ($ref_ids as $ref_id) {
                // Store old parent
                $old_parent = $tree->getParentId($ref_id);
                $this->tree->moveTree($ref_id, $this->object->getRefId());
                $rbacadmin->adjustMovedObjectPermissions($ref_id, $old_parent);

                ilConditionHandler::_adjustMovedObjectConditions($ref_id);

                // BEGIN ChangeEvent: Record cut event.
                $node_data = $tree->getNodeData($ref_id);
                $old_parent_data = $tree->getNodeData($old_parent);
                ilChangeEvent::_recordWriteEvent(
                    $node_data['obj_id'],
                    $ilUser->getId(),
                    'remove',
                    $old_parent_data['obj_id']
                );
                ilChangeEvent::_recordWriteEvent(
                    $node_data['obj_id'],
                    $ilUser->getId(),
                    'add',
                    $this->object->getId()
                );
                ilChangeEvent::_catchupWriteEvents($node_data['obj_id'], $ilUser->getId());
                // END PATCH ChangeEvent: Record cut event.
            }
        } // END CUT

        // process LINK command
        $ref_id = 0;
        $subnodes = [];
        if ($this->clipboard->getCmd() === "link") {
            foreach ($ref_ids as $ref_id) {
                // get node data
                $top_node = $this->tree->getNodeData($ref_id);

                // get subnodes of top nodes
                $subnodes[$ref_id] = $this->tree->getSubTree($top_node);
            }

            // now move all subtrees to new location
            foreach ($subnodes as $key => $subnode) {
                // first paste top_node....
                $obj_data = ilObjectFactory::getInstanceByRefId($ref_id);
                $new_ref_id = $obj_data->createReference();
                $obj_data->putInTree($this->requested_ref_id);
                $obj_data->setPermissions($this->requested_ref_id);

                // BEGIN ChangeEvent: Record link event.
                $node_data = $tree->getNodeData($new_ref_id);
                ilChangeEvent::_recordWriteEvent(
                    $node_data['obj_id'],
                    $ilUser->getId(),
                    'add',
                    $this->object->getId()
                );
                ilChangeEvent::_catchupWriteEvents($node_data['obj_id'], $ilUser->getId());
                // END PATCH ChangeEvent: Record link event.
            }

            $ilLog->write("ilObjectGUI::pasteObject(), link finished");
        } // END LINK


        // clear clipboard
        $this->clearObject();

        if ($last_cmd === "cut") {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_cut_copied"), true);
        } // BEGIN WebDAV: Support a copy command in repository
        elseif ($last_cmd === "copy") {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_cloned"), true);
        } elseif ($last_cmd === 'link') {
            // END WebDAV: Support copy command in repository
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_linked"), true);
        }

        $this->ctrl->returnToParent($this);
    }

    // show clipboard
    public function clipboardObject(): void
    {
        $ilErr = $this->error;
        $ilLog = $this->log;
        $ilTabs = $this->tabs;
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $ilTabs->activateTab("clipboard");

        // function should not be called if clipboard is empty
        if (!$this->clipboard->hasEntries()) {
            $message = sprintf('%s::clipboardObject(): Illegal access. Clipboard variable is empty!', get_class($this));
            $ilLog->write($message, $ilLog->FATAL);
            $ilErr->raiseError($this->lng->txt("permission_denied"), $ilErr->WARNING);
        }

        $data = [];
        foreach ($this->clipboard->getRefIds() as $ref_id) {
            if (!$tmp_obj = ilObjectFactory::getInstanceByRefId($ref_id, false)) {
                continue;
            }

            $data[] = [
                "type" => $tmp_obj->getType(),
                "type_txt" => $this->lng->txt("obj_" . $tmp_obj->getType()),
                "title" => $tmp_obj->getTitle(),
                "cmd" => ($this->clipboard->getCmd() === "cut") ? $this->lng->txt("move") : $this->lng->txt(
                    $this->clipboard->getCmd()
                ),
                "ref_id" => $ref_id,
                "obj_id" => $tmp_obj->getId()
            ];

            unset($tmp_obj);
        }

        $tab = new ilObjClipboardTableGUI($this, "clipboard");
        $tab->setData($data);
        $tpl->setContent($tab->getHTML());

        if (count($data) > 0) {
            $ilToolbar->setFormAction($ilCtrl->getFormAction($this));
            $ilToolbar->addFormButton(
                $lng->txt("insert_object_here"),
                "paste"
            );
            $ilToolbar->addFormButton(
                $lng->txt("clear_clipboard"),
                "clear"
            );
        }
    }

    public function isActiveAdministrationPanel(): bool
    {
        // #10081
        if ($this->view_manager->isAdminView() &&
            $this->object->getRefId() &&
            !$this->rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            return false;
        }

        return $this->view_manager->isAdminView();
    }

    public function setColumnSettings(ilColumnGUI $column_gui): void
    {
        $ilAccess = $this->access;
        parent::setColumnSettings($column_gui);

        $column_gui->setRepositoryItems(
            $this->object->getSubItems($this->isActiveAdministrationPanel(), true)
        );

        //if ($ilAccess->checkAccess("write", "", $this->object->getRefId())
        //	&& $this->allowBlocksConfigure())
        if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $column_gui->setBlockProperty("news", "settings", '1');
            //$column_gui->setBlockProperty("news", "public_notifications_option", true);
            $column_gui->setBlockProperty("news", "default_visibility_option", '1');
            $column_gui->setBlockProperty("news", "hide_news_block_option", '1');
        }

        if ($this->isActiveAdministrationPanel()) {
            $column_gui->setAdminCommands(true);
        }
    }

    /**
     * Standard is to allow blocks moving
     */
    public function allowBlocksMoving(): bool
    {
        return true;
    }

    /**
     * Standard is to allow blocks configuration
     */
    public function allowBlocksConfigure(): bool
    {
        return true;
    }


    /**
     * Clone all object
     * Overwritten method for copying container objects
     */
    public function cloneAllObject(): void
    {
        $ilCtrl = $this->ctrl;

        $ilAccess = $this->access;
        $ilErr = $this->error;
        $rbacsystem = $this->rbacsystem;

        $new_type = $this->std_request->getNewType();
        $ref_id = $this->requested_ref_id;
        $clone_source = $this->std_request->getCloneSource();

        if (!$rbacsystem->checkAccess('create', $ref_id, $new_type)) {
            $ilErr->raiseError($this->lng->txt('permission_denied'));
        }
        if (!$clone_source) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->createObject();
            return;
        }
        if (!$ilAccess->checkAccess('write', '', $clone_source, $new_type)) {
            $ilErr->raiseError($this->lng->txt('permission_denied'));
        }

        $options = $this->std_request->getCopyOptions();
        $orig = ilObjectFactory::getInstanceByRefId($clone_source);
        $result = $orig->cloneAllObject(
            $_COOKIE[session_name()],
            $_COOKIE['ilClientId'],
            $new_type,
            $ref_id,
            $clone_source,
            $options
        );

        if (ilCopyWizardOptions::_isFinished($result['copy_id'])) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("object_duplicated"), true);
            $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $result['ref_id']);
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("object_copy_in_progress"), true);
            $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $ref_id);
        }
        $ilCtrl->redirectByClass("ilrepositorygui", "");
    }

    public function saveSortingObject(): void
    {
        $sorting = ilContainerSorting::_getInstance($this->object->getId());

        // Allow comma
        $positions = $this->std_request->getPositions();

        $sorting->savePost($positions);
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('cntr_saved_sorting'), true);
        $this->ctrl->redirect($this, "editOrder");
    }

    // BEGIN WebDAV: Support a copy command in the repository

    /**
     * Recursively clones all nodes of the RBAC tree.
     */
    public function cloneNodes(
        int $srcRef,
        int $dstRef,
        array &$mapping,
        string $newName = null
    ): int {
        $tree = $this->tree;

        // clone the source node
        $srcObj = ilObjectFactory::getInstanceByRefId($srcRef);
        $newRef = $srcObj->cloneObject($dstRef)->getRefId();

        // We must immediately apply a new name to the object, to
        // prevent confusion of WebDAV clients about having two objects with identical
        // name in the repository.
        if (!is_null($newName)) {
            $newObj = ilObjectFactory::getInstanceByRefId($newRef);
            $newObj->setTitle($newName);
            $newObj->update();
            unset($newObj);
        }
        unset($srcObj);
        $mapping[$newRef] = $srcRef;

        // clone all children of the source node
        $children = $tree->getChilds($srcRef);
        foreach ($tree->getChilds($srcRef) as $child) {
            // Don't clone role folders, because it does not make sense to clone local roles
            // FIXME - Maybe it does make sense (?)
            if ($child["type"] !== 'rolf') {
                $this->cloneNodes($child["ref_id"], $newRef, $mapping);
            } elseif (count($rolf = $tree->getChildsByType($newRef, "rolf"))) {
                $mapping[$rolf[0]["ref_id"]] = $child["ref_id"];
            }
        }
        return $newRef;
    }
    // END PATCH WebDAV: Support a copy command in the repository

    // Modify list gui for presentation in container
    public function modifyItemGUI(
        ilObjectListGUI $a_item_list_gui,
        array $a_item_data
    ): void {
        /* not really implemented buildPath does not exist
        $lng = $this->lng;

        if ($a_show_path) {
            $a_item_list_gui->addCustomProperty(
                $lng->txt('path'),
                ilContainer::buildPath($a_item_data['ref_id'], $this->object->getRefId()),
                false,
                true
            );
        }
        */
    }

    /**
     * build path
     */
    public static function _buildPath(
        int $a_ref_id,
        int $a_course_ref_id
    ): string {
        global $DIC;

        $tree = $DIC->repositoryTree();
        $path = "";

        $path_arr = $tree->getPathFull($a_ref_id, $a_course_ref_id);
        $counter = 0;
        foreach ($path_arr as $data) {
            if ($counter++) {
                $path .= " > ";
            }
            $path .= $data['title'];
        }

        return $path;
    }


    public function editStylePropertiesObject(): void
    {
        $this->content_style_gui
            ->redirectToObjectSettings();
    }

    protected function showContainerPageTabs(): void
    {
        $ctrl = $this->ctrl;
        $tabs = $this->tabs;
        $page_gui = new ilContainerPageGUI($this->object->getId());
        $style_id = $this->content_style_domain
            ->styleForRefId($this->object->getRefId())
            ->getEffectiveStyleId();
        if (ilObject::_lookupType($style_id) === "sty") {
            $page_gui->setStyleId($style_id);
        } else {
            $style_id = 0;
        }
        $page_gui->setTabHook($this, "addPageTabs");
        $ctrl->getHTML($page_gui);
        $tabs->setTabActive("obj_sty");
        $tabs->setBackTarget($this->lng->txt('back'), ilLink::_getLink($this->ref_id));
    }

    public function getAsynchItemListObject(): void
    {
        $ref_id = $this->std_request->getCmdRefId();
        $obj_id = ilObject::_lookupObjId($ref_id);
        $type = ilObject::_lookupType($obj_id);

        // this should be done via container-object->getSubItem in the future
        $data = [
            "child" => $ref_id,
            "ref_id" => $ref_id,
            "obj_id" => $obj_id,
            "type" => $type
        ];
        $item_list_gui = ilObjectListGUIFactory::_getListGUIByType($type);
        $item_list_gui->setContainerObject($this);

        $item_list_gui->enableComments(true);
        $item_list_gui->enableNotes(true);
        $item_list_gui->enableTags(true);

        $this->modifyItemGUI($item_list_gui, $data);
        $html = $item_list_gui->getListItemHTML(
            $ref_id,
            $obj_id,
            "",
            "",
            true,
            true
        );

        // include plugin slot for async item list
        foreach ($this->component_factory->getActivePluginsInSlot("uihk") as $plugin) {
            $gui_class = $plugin->getUIClassInstance();
            $resp = $gui_class->getHTML("Services/Container", "async_item_list", ["html" => $html]);
            if ((string) $resp["mode"] !== ilUIHookPluginGUI::KEEP) {
                $html = $gui_class->modifyHTML($html, $resp);
            }
        }

        echo $html;
        exit;
    }

    protected function showPasswordInstructionObject(
        bool $a_init = true
    ): void {
        global $DIC;
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;

        if ($a_init) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('webdav_pwd_instruction'));
            $this->initFormPasswordInstruction();
        }

        $uri_builder = new ilWebDAVUriBuilder($DIC->http()->request());
        $href = $uri_builder->getUriToMountInstructionModalByRef($this->object->getRefId());

        $btn = ilButton::getInstance();
        $btn->setCaption('mount_webfolder');
        $btn->setOnClick("triggerWebDAVModal('$href')");
        $ilToolbar->addButtonInstance($btn);

        $tpl->setContent($this->form->getHTML());
    }

    /**
     * Init password form
     */
    protected function initFormPasswordInstruction(): ilPropertyFormGUI
    {
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this));

        // new password
        $ipass = new ilPasswordInputGUI($this->lng->txt("desired_password"), "new_password");
        $ipass->setRequired(true);

        $this->form->addItem($ipass);
        $this->form->addCommandButton("savePassword", $this->lng->txt("save"));
        $this->form->addCommandButton('cancel', $this->lng->txt('cancel'));

        $this->form->setTitle($this->lng->txt("chg_ilias_and_webfolder_password"));
        $this->form->setFormAction($this->ctrl->getFormAction($this));

        return $this->form;
    }

    protected function savePasswordObject(): void
    {
        $ilUser = $this->user;

        $form = $this->initFormPasswordInstruction();
        if ($form->checkInput()) {
            $ilUser->resetPassword($this->form->getInput('new_password'), $this->form->getInput('new_password'));
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('webdav_pwd_instruction_success'), true);
            $this->showPasswordInstructionObject(false);
            return;
        }
        $form->setValuesByPost();
        $this->showPasswordInstructionObject();
    }

    /**
     * Redraw a list item (ajax)
     */
    public function redrawListItemObject(): void
    {
        $tpl = $this->tpl;

        $html = null;

        $child_ref_id = $this->std_request->getChildRefId();
        $parent_ref_id = $this->std_request->getParentRefId();

        $item_data = $this->object->getSubItems(false, false, $child_ref_id);
        $container_view = $this->getContentGUI();

        // list item is session material (not part of "_all"-items - see below)
        $event_items = ilEventItems::_getItemsOfContainer($this->object->getRefId());
        if (in_array($child_ref_id, $event_items)) {
            foreach ($this->object->items["sess"] as $id) {
                $items = ilObjectActivation::getItemsByEvent($id['obj_id']);
                foreach ($items as $event_item) {
                    if ($event_item["child"] == $child_ref_id) {
                        // sessions
                        if ($parent_ref_id > 0) {
                            $event_item["parent"] = $parent_ref_id;
                        }
                        $html = $container_view->renderItem($event_item);
                    }
                }
            }
        }

        // "normal" list item
        if (!$html) {
            foreach ($this->object->items["_all"] as $id) {
                if ($id["child"] == $child_ref_id) {
                    $html = $container_view->renderItem($id);
                }
            }
        }

        if ($html) {
            echo $html;

            // we need to add onload code manually (rating, comments, etc.)
            echo $tpl->getOnLoadCodeForAsynch();
        }

        exit;
    }

    protected function initEditForm(): ilPropertyFormGUI
    {
        $lng = $this->lng;
        $lng->loadLanguageModule($this->object->getType());

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "update"));
        $form->setTitle($this->lng->txt($this->object->getType() . "_edit"));

        $this->initFormTitleDescription($form);

        $this->initEditCustomForm($form);

        $form->addCommandButton("update", $this->lng->txt("save"));

        return $form;
    }

    /**
     * Init title/description for edit form
     */
    public function initFormTitleDescription(ilPropertyFormGUI $form): void
    {
        $trans = null;
        if ($this->getCreationMode() === false) {
            /** @var ilObjectTranslation $trans */
            $trans = $this->object->getObjectTranslation();
        }
        $title = new ilTextInputGUI($this->lng->txt("title"), "title");
        $title->setRequired(true);
        $title->setSize(min(40, ilObject::TITLE_LENGTH));
        $title->setMaxLength(ilObject::TITLE_LENGTH);
        $form->addItem($title);

        if ($this->getCreationMode() === false && count($trans->getLanguages()) > 1) {
            $languages = ilMDLanguageItem::_getLanguages();
            $title->setInfo(
                $this->lng->txt("language") . ": " . $languages[$trans->getDefaultLanguage()] .
                ' <a href="' . $this->ctrl->getLinkTargetByClass("ilobjecttranslationgui", "") .
                '">&raquo; ' . $this->lng->txt("obj_more_translations") . '</a>'
            );

            unset($languages);
        }
        $desc = new ilTextAreaInputGUI($this->lng->txt("description"), "desc");
        $desc->setRows(2);
        $desc->setCols(40);
        $form->addItem($desc);

        if ($this->getCreationMode() === false) {
            $title->setValue($trans->getDefaultTitle());
            $desc->setValue($trans->getDefaultDescription());
        }
    }

    /**
     * Append sorting settings to property form
     */
    protected function initSortingForm(
        ilPropertyFormGUI $form,
        array $a_sorting_settings
    ): ilPropertyFormGUI {
        $settings = new ilContainerSortingSettings($this->object->getId());
        $sort = new ilRadioGroupInputGUI($this->lng->txt('sorting_header'), "sorting");

        if (in_array(ilContainer::SORT_INHERIT, $a_sorting_settings)) {
            $sort_inherit = new ilRadioOption();
            $sort_inherit->setTitle(
                $this->lng->txt('sort_inherit_prefix') .
                ' (' . ilContainerSortingSettings::sortModeToString(
                    ilContainerSortingSettings::lookupSortModeFromParentContainer(
                        $this->object->getId()
                    )
                ) . ') '
            );
            $sort_inherit->setValue((string) ilContainer::SORT_INHERIT);
            $sort_inherit->setInfo($this->lng->txt('sorting_info_inherit'));
            $sort->addOption($sort_inherit);
        }
        if (in_array(ilContainer::SORT_TITLE, $a_sorting_settings)) {
            $sort_title = new ilRadioOption(
                $this->lng->txt('sorting_title_header'),
                (string) ilContainer::SORT_TITLE
            );
            $sort_title->setInfo($this->lng->txt('sorting_info_title'));

            $this->initSortingDirectionForm($settings, $sort_title, 'title');
            $sort->addOption($sort_title);
        }
        if (in_array(ilContainer::SORT_CREATION, $a_sorting_settings)) {
            $sort_activation = new ilRadioOption(
                $this->lng->txt('sorting_creation_header'),
                (string) ilContainer::SORT_CREATION
            );
            $sort_activation->setInfo($this->lng->txt('sorting_creation_info'));
            $this->initSortingDirectionForm($settings, $sort_activation, 'creation');
            $sort->addOption($sort_activation);
        }
        if (in_array(ilContainer::SORT_ACTIVATION, $a_sorting_settings)) {
            $sort_activation = new ilRadioOption($this->lng->txt('crs_sort_activation'), (string) ilContainer::SORT_ACTIVATION);
            $sort_activation->setInfo($this->lng->txt('crs_sort_timing_info'));
            $this->initSortingDirectionForm($settings, $sort_activation, 'activation');
            $sort->addOption($sort_activation);
        }
        if (in_array(ilContainer::SORT_MANUAL, $a_sorting_settings)) {
            $sort_manual = new ilRadioOption(
                $this->lng->txt('sorting_manual_header'),
                (string) ilContainer::SORT_MANUAL
            );
            $sort_manual->setInfo($this->lng->txt('sorting_info_manual'));
            $this->initManualSortingOptionForm($settings, $sort_manual, "manual", $a_sorting_settings);
            $sort->addOption($sort_manual);
        }

        // Handle moved containers and there possibly invalid values
        if (in_array($settings->getSortMode(), $a_sorting_settings)) {
            $sort->setValue((string) $settings->getSortMode());
        } else {
            $sort->setValue((string) ilContainer::SORT_TITLE);
        }
        $form->addItem($sort);

        return $form;
    }

    /**
     * Add list presentation settings to form
     */
    protected function initListPresentationForm(ilPropertyFormGUI $form): ilPropertyFormGUI
    {
        $lpres = new ilRadioGroupInputGUI($this->lng->txt('cont_list_presentation'), "list_presentation");

        $item_list = new ilRadioOption($this->lng->txt('cont_item_list'), "");
        //$item_list->setInfo($this->lng->txt('cont_item_list_info'));
        $lpres->addOption($item_list);

        $tile_view = new ilRadioOption($this->lng->txt('cont_tile_view'), "tile");
        //$tile_view->setInfo($this->lng->txt('cont_tile_view_info'));
        $lpres->addOption($tile_view);

        // tile size

        $si = new ilRadioGroupInputGUI($this->lng->txt("cont_tile_size"), "tile_size");
        foreach ($this->object->getTileSizes() as $key => $txt) {
            $op = new ilRadioOption($txt, $key);
            $si->addOption($op);
        }
        $tile_view->addSubItem($si);
        $si->setValue(
            (string) ((int) ilContainer::_lookupContainerSetting($this->object->getId(), "tile_size"))
        );

        $lpres->setValue(
            ilContainer::_lookupContainerSetting($this->object->getId(), "list_presentation")
        );

        $form->addItem($lpres);

        return $form;
    }

    protected function saveListPresentation(ilPropertyFormGUI $form): void
    {
        $val = ($form->getInput('list_presentation') === "tile")
            ? "tile"
            : "";
        ilContainer::_writeContainerSetting($this->object->getId(), "list_presentation", $val);
        ilContainer::_writeContainerSetting(
            $this->object->getId(),
            "tile_size",
            (string) ((int) $form->getInput('tile_size'))
        );
    }

    /**
     * Add sorting direction
     */
    protected function initSortingDirectionForm(
        ilContainerSortingSettings $sorting_settings,
        ilRadioOption $element,
        string $a_prefix
    ): ilRadioOption {
        if ($a_prefix === 'manual') {
            $txt = $this->lng->txt('sorting_new_items_direction');
        } else {
            $txt = $this->lng->txt('sorting_direction');
        }

        $direction = new ilRadioGroupInputGUI($txt, $a_prefix . '_sorting_direction');
        $direction->setValue((string) $sorting_settings->getSortDirection());
        $direction->setRequired(true);

        // asc
        $asc = new ilRadioOption(
            $this->lng->txt('sorting_asc'),
            (string) ilContainer::SORT_DIRECTION_ASC
        );
        $direction->addOption($asc);

        // desc
        $desc = new ilRadioOption(
            $this->lng->txt('sorting_desc'),
            (string) ilContainer::SORT_DIRECTION_DESC
        );
        $direction->addOption($desc);

        $element->addSubItem($direction);

        return $element;
    }

    /**
     * Add manual sorting options
     */
    protected function initManualSortingOptionForm(
        ilContainerSortingSettings $settings,
        ilRadioOption $element,
        string $a_prefix,
        array $a_sorting_settings
    ): ilRadioOption {
        $position = new ilRadioGroupInputGUI(
            $this->lng->txt('sorting_new_items_position'),
            $a_prefix . '_new_items_position'
        );
        $position->setValue((string) $settings->getSortNewItemsPosition());
        $position->setRequired(true);

        //new items insert on top
        $new_top = new ilRadioOption(
            $this->lng->txt('sorting_new_items_at_top'),
            (string) ilContainer::SORT_NEW_ITEMS_POSITION_TOP
        );

        $position->addOption($new_top);

        //new items insert at bottom
        $new_bottom = new ilRadioOption(
            $this->lng->txt('sorting_new_items_at_bottom'),
            (string) ilContainer::SORT_NEW_ITEMS_POSITION_BOTTOM
        );

        $position->addOption($new_bottom);

        $element->addSubItem($position);

        $order = new ilRadioGroupInputGUI($this->lng->txt('sorting_new_items_order'), $a_prefix . '_new_items_order');
        $order->setValue((string) $settings->getSortNewItemsOrder());
        $order->setRequired(true);

        if (in_array(ilContainer::SORT_TITLE, $a_sorting_settings)) {
            //new items sort in alphabetical order
            $new_title = new ilRadioOption(
                $this->lng->txt('sorting_title_header'),
                (string) ilContainer::SORT_NEW_ITEMS_ORDER_TITLE
            );

            $order->addOption($new_title);
        }

        if (in_array(ilContainer::SORT_CREATION, $a_sorting_settings)) {
            //new items sort by creation date
            $new_creation = new ilRadioOption(
                $this->lng->txt('sorting_creation_header'),
                (string) ilContainer::SORT_NEW_ITEMS_ORDER_CREATION
            );

            $order->addOption($new_creation);
        }

        if (in_array(ilContainer::SORT_ACTIVATION, $a_sorting_settings)) {
            //new items by activation
            $new_activation = new ilRadioOption(
                $this->lng->txt('crs_sort_activation'),
                (string) ilContainer::SORT_NEW_ITEMS_ORDER_ACTIVATION
            );

            $order->addOption($new_activation);
        }

        $element->addSubItem($order);

        $this->initSortingDirectionForm($settings, $element, 'manual');

        return $element;
    }

    protected function saveSortingSettings(ilPropertyFormGUI $form): void
    {
        $settings = new ilContainerSortingSettings($this->object->getId());
        $settings->setSortMode((int) $form->getInput("sorting"));

        switch ($form->getInput('sorting')) {
            case ilContainer::SORT_TITLE:
                $settings->setSortDirection((int) $form->getInput('title_sorting_direction'));
                break;
            case ilContainer::SORT_ACTIVATION:
                $settings->setSortDirection((int) $form->getInput('activation_sorting_direction'));
                break;
            case ilContainer::SORT_CREATION:
                $settings->setSortDirection((int) $form->getInput('creation_sorting_direction'));
                break;
            case ilContainer::SORT_MANUAL:
                $settings->setSortNewItemsPosition($form->getInput('manual_new_items_position'));
                $settings->setSortNewItemsOrder($form->getInput('manual_new_items_order'));
                $settings->setSortDirection((int) $form->getInput('manual_sorting_direction'));
                break;
        }

        $settings->update();
    }

    /**
     * Show trash content of object
     */
    public function trashObject(): void
    {
        $tpl = $this->tpl;

        $this->tabs_gui->activateTab('trash');

        $trash_table = new ilTrashTableGUI($this, 'trash', $this->object->getRefId());
        $trash_table->init();
        $trash_table->parse();

        $trash_table->setFilterCommand('trashApplyFilter');
        $trash_table->setResetCommand('trashResetFilter');

        $tpl->setContent($trash_table->getHTML());
    }

    public function trashApplyFilterObject(): void
    {
        $this->trashHandleFilter(true, false);
    }

    public function trashResetFilterObject(): void
    {
        $this->trashHandleFilter(false, true);
    }

    protected function trashHandleFilter(bool $action_apply, bool $action_reset): void
    {
        $trash_table = new ilTrashTableGUI($this, 'trash', $this->object->getRefId());
        $trash_table->init();
        $trash_table->resetOffset();
        if ($action_reset) {
            $trash_table->resetFilter();
        }
        if ($action_apply) {
            $trash_table->writeFilterToSession();
        }
        $this->trashObject();
    }

    public function removeFromSystemObject(): void
    {
        $ru = new ilRepositoryTrashGUI($this);
        $ru->removeObjectsFromSystem($this->std_request->getTrashIds());
        $this->ctrl->redirect($this, "trash");
    }

    protected function restoreToNewLocationObject(ilPropertyFormGUI $form = null): void
    {
        $this->tabs_gui->activateTab('trash');

        $ru = new ilRepositoryTrashGUI($this);
        $ru->restoreToNewLocation();
    }

    /**
     * Get objects back from trash
     */
    public function undeleteObject(): void
    {
        $ru = new ilRepositoryTrashGUI($this);
        $ru->restoreObjects(
            $this->requested_ref_id,
            $this->std_request->getTrashIds()
        );
        $this->ctrl->redirect($this, "trash");
    }

    public function confirmRemoveFromSystemObject(): void
    {
        $lng = $this->lng;
        if (count($this->std_request->getTrashIds()) == 0) {
            $this->tpl->setOnScreenMessage('failure', $lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "trash");
        }

        $ru = new ilRepositoryTrashGUI($this);
        $ru->confirmRemoveFromSystemObject($this->std_request->getTrashIds());
    }

    protected function getTreeSelectorGUI(string $cmd): ilTreeExplorerGUI
    {
        $exp = new ilRepositorySelectorExplorerGUI($this, "showPasteTree");
        // TODO: The study programme 'prg' is not included here, as the
        // ilRepositorySelectorExplorerGUI only handles static rules for
        // parent-child-relations and not the dynamic relationsships
        // required for the SP (see #16909).
        $exp->setTypeWhiteList(["root", "cat", "grp", "crs", "fold"]);

        // Not all types are allowed in the LearningSequence
        // Extend whitelist, if all selected types are possible subojects of LSO
        if (in_array($this->clipboard->getCmd(), ["link", "cut"])) {
            $lso_types = array_keys($this->obj_definition->getSubObjects('lso'));
            $refs = $this->clipboard->getRefIds();
            $allow_lso = true;
            foreach ($refs as $item_ref_id) {
                $type = ilObject::_lookupType($item_ref_id, true);
                if (!in_array($type, $lso_types)) {
                    $allow_lso = false;
                }
            }
            if ($allow_lso) {
                $whitelist = $exp->getTypeWhiteList();
                $whitelist[] = 'lso';
                $exp->setTypeWhiteList($whitelist);
            }
        }

        if ($cmd === "link") {
            $exp->setSelectMode("nodes", true);
        } else {
            $exp->setSelectMode("nodes[]", false);
        }
        return $exp;
    }

    public function setSideColumnReturn(): void
    {
        $this->ctrl->setReturn($this, "");
    }

    protected function initFilter(): void
    {
        global $DIC;

        if (!$this->object || !ilContainer::_lookupContainerSetting($this->object->getId(), "filter", '0')) {
            return;
        }
        $filter_service = $this->container_filter_service;
        $request = $DIC->http()->request();

        $filter = $filter_service->util()->getFilterForRefId(
            $this->ref_id,
            $DIC->ctrl()->getLinkTarget($this, "render", "", true),
            $this->isActiveAdministrationPanel()
        );

        $filter_data = $DIC->uiService()->filter()->getData($filter);

        $this->container_user_filter = $filter_service->userFilter($filter_data);
        $this->ui_filter = $filter;
    }

    protected function showContainerFilter(): void
    {
        global $DIC;
        if (!is_null($this->ui_filter)) {
            $renderer = $DIC->ui()->renderer();

            $main_tpl = $this->tpl;
            $main_tpl->setFilter($renderer->render($this->ui_filter));
            if ($this->container_user_filter->isEmpty() && !ilContainer::_lookupContainerSetting(
                $this->object->getId(),
                "filter_show_empty",
                '0'
            )) {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt("cont_filter_empty"));
            }
        }
    }

    public function getAdminTabs(): void
    {
        if ($this->checkPermissionBool("visible,read")) {
            $this->tabs_gui->addTab(
                'view',
                $this->lng->txt('view'),
                $this->ctrl->getLinkTarget($this, 'view')
            );
        }

        // Always show container trash
        $this->tabs_gui->addTab(
            'trash',
            $this->lng->txt('trash'),
            $this->ctrl->getLinkTarget($this, 'trash')
        );

        if ($this->checkPermissionBool("edit_permission")) {
            $this->tabs_gui->addTab(
                'perm_settings',
                $this->lng->txt('perm_settings'),
                $this->ctrl->getLinkTargetByClass(
                    [
                        get_class($this),
                        'ilpermissiongui'
                    ],
                    'perm'
                )
            );
        }
    }

    public function competencesObject(): void
    {
        $ctrl = $this->ctrl;

        $ctrl->redirectByClass(["ilContainerSkillGUI", "ilContSkillPresentationGUI"]);
    }
}
