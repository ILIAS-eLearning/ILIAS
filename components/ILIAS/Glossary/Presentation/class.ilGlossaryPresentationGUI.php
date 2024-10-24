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

/**
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilGlossaryPresentationGUI: ilNoteGUI, ilInfoScreenGUI, ilGlossaryDefPageGUI
 * @ilCtrl_Calls ilGlossaryPresentationGUI: ilPresentationFullGUI, ilGlossaryFlashcardGUI, ilGlossaryFlashcardBoxGUI
 * @ilCtrl_Calls ilGlossaryPresentationGUI: ilPresentationTableGUI
 */
class ilGlossaryPresentationGUI implements ilCtrlBaseClassInterface
{
    protected \ILIAS\Glossary\Taxonomy\TaxonomyManager $tax_manager;
    protected \ILIAS\COPage\Xsl\XslManager $xsl;
    protected \ILIAS\GlobalScreen\Services $global_screen;
    protected \ILIAS\Glossary\InternalGUIService $gui;
    protected \ILIAS\Glossary\InternalDomainService $domain;
    protected array $mobs;
    protected bool $fill_on_load_code;
    protected string $offline_dir;
    protected ilPropertyFormGUI $form;
    protected bool $offline;
    protected string $export_format;
    protected ilCtrl $ctrl;
    protected ilTabsGUI $tabs_gui;
    protected ilAccessHandler $access;
    protected ilNavigationHistory $nav_history;
    protected ilToolbarGUI $toolbar;
    protected \ilObjUser $user;
    protected \ilHelpGUI $help;
    protected \ilObjGlossary $glossary;
    protected \ilObjGlossaryGUI $glossary_gui;
    protected \ilGlobalTemplateInterface $tpl;
    protected \ilLanguage $lng;
    protected int $tax_node;
    protected int $tax_id;
    protected \ilObjTaxonomy $tax;
    protected int $term_id;
    protected int $requested_ref_id;
    protected string $requested_letter;
    protected int $requested_def_page_id;
    protected string $requested_search_str;
    protected string $requested_file_id;
    protected int $requested_mob_id;
    protected string $requested_table_glossary_download_list_action = "";
    /**
     * @var string[]
     */
    protected array $requested_table_glossary_download_file_ids = [];
    protected \ILIAS\Style\Content\Service $content_style_service;
    protected \ILIAS\Style\Content\GUIService $content_style_gui;
    protected \ILIAS\Style\Content\Object\ObjectFacade $content_style_domain;
    protected \ILIAS\UI\Factory $ui_fac;
    protected \ILIAS\UI\Renderer $ui_ren;

    public function __construct(
        string $export_format = "",
        string $export_dir = ""
    ) {
        global $DIC;

        $service = $DIC->glossary()->internal();

        $this->domain = $domain = $service->domain();
        $this->gui = $gui = $service->gui();

        $this->access = $DIC->access();
        $this->user = $domain->user();
        $this->lng = $DIC->language();

        $this->toolbar = $gui->toolbar();
        $this->help = $gui->help();
        $this->nav_history = $DIC["ilNavigationHistory"];
        $this->tpl = $gui->ui()->mainTemplate();
        $this->ctrl = $gui->ctrl();
        $this->tabs_gui = $gui->tabs();
        $this->global_screen = $gui->globalScreen();

        $this->export_format = $export_format;
        $this->setOfflineDirectory($export_dir);
        $this->offline = ($export_format != "");
        $this->ui_fac = $DIC->ui()->factory();
        $this->ui_ren = $DIC->ui()->renderer();
        $this->offline = ($export_format !== "");

        $this->ctrl->saveParameter($this, array("ref_id", "letter", "tax_node"));
        $this->content_style_service =
            $DIC->contentStyle();
        $this->initByRequest();
        $this->xsl = $DIC->copage()->internal()->domain()->xsl();
        $this->tax_manager = $domain->taxonomy($this->glossary);
    }

    /**
     * Init services and this class by request params.
     *
     * The request params are usually retrieved by HTTP request, but
     * also adjusted during HTML exports, this is, why this method needs to be public.
     * @throws ilGlossaryException
     */
    public function initByRequest(?array $query_params = null): void
    {
        $request = $this->gui
            ->presentation()
            ->request($query_params);

        $this->requested_ref_id = $request->getRefId();
        $this->term_id = $request->getTermId();
        $this->glossary_gui = $this->gui->presentation()->ObjGlossaryGUI($this->requested_ref_id);
        $this->glossary = $this->glossary_gui->getGlossary();
        $this->requested_def_page_id = $request->getDefinitionPageId();
        $this->requested_search_str = $request->getSearchString();
        $this->requested_file_id = $request->getFileId();
        $this->requested_mob_id = $request->getMobId();
        $this->requested_table_glossary_download_list_action = $request->getTableGlossaryDownloadListAction();
        $this->requested_table_glossary_download_file_ids = $request->getTableGlossaryDownloadFileIds();


        // determine term id and check whether it is valid (belongs to
        // current glossary or a virtual (online) sub-glossary)
        $glo_ids = $this->glossary->getAllGlossaryIds();
        $term_glo_id = ilGlossaryTerm::_lookGlossaryID($this->term_id);
        if (!in_array($term_glo_id, $glo_ids) && !ilGlossaryTermReferences::isReferenced($glo_ids, $this->term_id)) {
            if ($this->term_id > 0) {
                throw new ilGlossaryException("Term ID does not match the glossary.");
            }
            $this->term_id = 0;
        }

        $this->tax_node = 0;
        $this->tax_id = $this->glossary->getTaxonomyId();
        if ($this->tax_id > 0 && $this->glossary->getShowTaxonomy()) {
            $this->tax = new ilObjTaxonomy($this->tax_id);
        }
        $requested_tax_node = $request->getTaxNode();
        if ($requested_tax_node > 1 && $this->tax->getTree()->readRootId() != $requested_tax_node) {
            $this->tax_node = $requested_tax_node;
        }

        $this->requested_letter = $request->getLetter();

        $this->content_style_domain = $this->content_style_service->domain()->styleForRefId($this->glossary->getRefId());
        $this->content_style_gui = $this->content_style_service->gui();
    }

    public function injectTemplate(ilGlobalTemplateInterface $tpl): void
    {
        $this->tpl = $tpl;
    }

    /**
    * set offline mode (content is generated for offline package)
    */
    public function setOfflineMode(bool $a_offline = true): void
    {
        $this->offline = $a_offline;
    }

    /**
    * checks wether offline content generation is activated
    */
    public function offlineMode(): bool
    {
        return $this->offline;
    }

    public function setOfflineDirectory(string $a_dir): void
    {
        $this->offline_dir = $a_dir;
    }

    public function getOfflineDirectory(): string
    {
        return $this->offline_dir;
    }

    /**
     * execute command
     */
    public function executeCommand(): void
    {
        $lng = $this->lng;
        $ilAccess = $this->access;

        $lng->loadLanguageModule("content");

        $next_class = $this->ctrl->getNextClass($this);
        if ($this->glossary->isActiveFlashcards()) {
            $cmd = $this->ctrl->getCmd("showFlashcards");
        } else {
            $cmd = $this->ctrl->getCmd("listTerms");
        }

        // check write permission
        if (!$ilAccess->checkAccess("read", "", $this->requested_ref_id) &&
            !($ilAccess->checkAccess("visible", "", $this->requested_ref_id) &&
                ($cmd == "infoScreen" || strtolower($next_class) == "ilinfoscreengui"))) {
            throw new ilPermissionException($lng->txt("permission_denied"));
        }

        if ($cmd != "listDefinitions") {
            $this->prepareOutput();
        }

        switch ($next_class) {
            case "ilnotegui":
                $this->setTabs();
                $ret = $this->listDefinitions();
                break;

            case "ilinfoscreengui":
                $ret = $this->outputInfoScreen();
                break;

            case "ilpresentationfullgui":
                $this->setTabs();
                $this->showTaxonomy();
                $full_gui = $this->gui->presentation()
                                ->PresentationFullGUI($this, $this->glossary, $this->offlineMode(), $this->tax_node);
                $this->ctrl->forwardCommand($full_gui);
                break;

            case "ilpresentationtablegui":
                $this->setTabs();
                $this->showTaxonomy();
                $pt_gui = $this->gui->presentation()
                    ->PresentationTableGUI($this, $this->glossary, $this->offlineMode(), $this->tax_node);
                $this->ctrl->forwardCommand($pt_gui);
                break;

            case "ilglossarydefpagegui":
                $page_gui = new ilGlossaryDefPageGUI($this->requested_def_page_id);
                $this->basicPageGuiInit($page_gui);
                $this->ctrl->forwardCommand($page_gui);
                break;

            case "ilglossaryflashcardgui":
                $flash_gui = new ilGlossaryFlashcardGUI();
                $this->ctrl->forwardCommand($flash_gui);
                break;

            case "ilglossaryflashcardboxgui":
                $flash_box_gui = new ilGlossaryFlashcardBoxGUI();
                $this->ctrl->forwardCommand($flash_box_gui);
                break;

            default:
                $this->$cmd();
                break;
        }
        $this->tpl->printToStdout();
    }

    public function prepareOutput(): void
    {
        $this->tpl->loadStandardTemplate();
        $title = $this->glossary->getTitle();

        $this->tpl->setTitle($title);
        $this->tpl->setTitleIcon(ilUtil::getImagePath("standard/icon_glo.svg"));

        $this->setLocator();
    }


    /**
     * Basic page gui initialisation
     */
    public function basicPageGuiInit(
        \ilPageObjectGUI $a_page_gui
    ): void {
        $a_page_gui->setStyleId($this->content_style_domain->getEffectiveStyleId());
        if (!$this->offlineMode()) {
            $a_page_gui->setOutputMode("presentation");
            $this->fill_on_load_code = true;
        } else {
            $a_page_gui->setOutputMode("offline");
            $a_page_gui->setOfflineDirectory($this->getOfflineDirectory());
            $this->fill_on_load_code = false;
        }
        if (!$this->offlineMode()) {
            $this->ctrl->setParameter($this, "pg_id", $a_page_gui->getId());
        }
        $a_page_gui->setFileDownloadLink($this->getLink($this->requested_ref_id, "downloadFile"));
        $a_page_gui->setFullscreenLink($this->getLink($this->requested_ref_id, "fullscreen"));
    }

    public function listTerms(): void
    {
        $ilNavigationHistory = $this->nav_history;
        $ilAccess = $this->access;
        $lng = $this->lng;
        $ilToolbar = $this->toolbar;
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs_gui;

        if (!$ilAccess->checkAccess("read", "", $this->requested_ref_id)) {
            throw new ilPermissionException($lng->txt("permission_denied"));
        }

        $ret = "";
        if ($this->glossary->getPresentationMode() == "full_def") {
            $this->listTermByGivenAsPanel();
        } else {
            if (!$this->offlineMode()) {
                $ilNavigationHistory->addItem(
                    $this->requested_ref_id,
                    $this->ctrl->getLinkTarget($this, "listTerms"),
                    "glo"
                );
            }
            $this->listTermByGivenAsTable();
        }
    }

    /**
     * list glossary terms
     */
    public function listTermByGivenAsTable(): void
    {
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;
        $lng = $this->lng;
        $tpl = $this->tpl;

        if (!$ilAccess->checkAccess("read", "", $this->requested_ref_id)) {
            throw new ilPermissionException($lng->txt("permission_denied"));
        }

        $ilCtrl->redirectByClass("ilPresentationTableGUI", "show");
    }

    /**
     * list glossary terms
     */
    public function listTermByGivenAsPanel(): void
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->redirectByClass("ilPresentationFullGUI", "show");
    }

    protected function setContentStyles(): void
    {
        $tpl = $this->tpl;

        if (!$this->offlineMode()) {
            $this->content_style_gui->addCss($tpl, $this->glossary->getRefId());
            $tpl->addCss(ilObjStyleSheet::getSyntaxStylePath());
        } else {
            $tpl->addCss("content.css");
            $tpl->addCss("syntaxhighlight.css");
        }
    }

    /**
     * Get presentation table
     */
    public function getPresentationTable(): ilPresentationTableGUI
    {
        $pres_table = $this->gui->presentation()->PresentationTableGUI(
            $this,
            $this->glossary,
            $this->offlineMode(),
            $this->tax_node
        );
        return $pres_table;
    }

    /**
    * list definitions of a term
    */
    public function listDefinitions(
        int $a_ref_id = 0,
        int $a_term_id = 0,
        bool $a_get_html = false,
        bool $render_term = true,
        string $a_page_mode = ilPageObjectGUI::PRESENTATION,
        bool $render_page_container = true
    ): string {
        $ilUser = $this->user;
        $ilAccess = $this->access;
        $lng = $this->lng;
        $tpl = $this->tpl;

        if ($a_ref_id == 0) {
            $ref_id = $this->requested_ref_id;
        } else {
            $ref_id = $a_ref_id;
        }
        if ($a_term_id == 0) {
            $term_id = $this->term_id;
        } else {
            $term_id = $a_term_id;
        }

        if (!$ilAccess->checkAccess("read", "", $ref_id)) {
            throw new ilPermissionException($lng->txt("permission_denied"));
        }

        try {
            $term = new ilGlossaryTerm($term_id);
        } catch (Exception $e) {
            return "";
        }

        if (!$a_get_html) {
            $tpl->loadStandardTemplate();

            $this->setContentStyles();

            if (!$this->offlineMode()) {
                $this->setLocator();
            }

            $tpl->setTitleIcon(ilUtil::getImagePath("standard/icon_glo.svg"));
            $tpl->setTitle($this->lng->txt("cont_term") . ": " . $term->getTerm());

            // advmd block
            $cmd = null;
            if ($ilAccess->checkAccess("write", "", $this->requested_ref_id)) {
                $cmd = array("edit" => $this->ctrl->getLinkTargetByClass(array("ilglossaryeditorgui", "ilobjglossarygui", "ilglossarytermgui", "ilobjectmetadatagui"), ""));
            }
            $mdgui = new ilObjectMetaDataGUI($this->glossary, "term", $term->getId());
            $tpl->setRightContent($mdgui->getBlockHTML($cmd));
        }

        $def_tpl = new ilTemplate("tpl.glossary_definition_list.html", true, true, "components/ILIAS/Glossary");

        if ($render_page_container) {
            $def_tpl->touchBlock("page_container_1");
            $def_tpl->touchBlock("page_container_2");
        }

        if ($render_term) {
            $def_tpl->setVariable("TXT_TERM", $term->getTerm());
        }
        $this->mobs = array();

        $page_gui = new ilGlossaryDefPageGUI($term_id);
        $this->basicPageGuiInit($page_gui);
        $page_gui->setGlossary($this->glossary);
        $page_gui->setOutputMode($a_page_mode);
        $page_gui->setStyleId($this->content_style_domain->getEffectiveStyleId());
        $page = $page_gui->getPageObject();

        // internal links
        $page->buildDom();

        if ($this->offlineMode()) {
            $page_gui->setOutputMode("offline");
            $page_gui->setOfflineDirectory($this->getOfflineDirectory());
        }
        $page_gui->setFullscreenLink($this->getLink($ref_id, "fullscreen", $term_id));

        $page_gui->setTemplateOutput(false);
        $page_gui->setRawPageContent(true);
        if (!$this->offlineMode()) {
            $output = $page_gui->showPage();
        } else {
            $output = $page_gui->presentation($page_gui->getOutputMode());
        }

        $def_tpl->setCurrentBlock("definition");
        $def_tpl->setVariable("PAGE_CONTENT", $output);
        $def_tpl->parseCurrentBlock();

        // display possible backlinks
        $sources = ilInternalLink::_getSourcesOfTarget('git', $this->term_id, 0);

        if ($sources) {
            $backlist_shown = false;
            foreach ($sources as $src) {
                $type = explode(':', $src['type']);

                if ($type[0] == 'lm' && $type[1] == 'pg') {
                    $title = ilLMPageObject::_getPresentationTitle($src['id']);
                    $lm_id = ilLMObject::_lookupContObjID($src['id']);
                    $lm_title = ilObject::_lookupTitle($lm_id);
                    $def_tpl->setCurrentBlock('backlink_item');
                    $ref_ids = ilObject::_getAllReferences($lm_id);
                    $access = false;
                    foreach ($ref_ids as $rid) {
                        if ($ilAccess->checkAccess("read", "", $rid)) {
                            $access = true;
                        }
                    }
                    if ($access) {
                        $def_tpl->setCurrentBlock("backlink_item");
                        $def_tpl->setVariable("BACKLINK_LINK", ILIAS_HTTP_PATH . "/goto.php?target=" . $type[1] . "_" . $src['id']);
                        $def_tpl->setVariable("BACKLINK_ITEM", $lm_title . ": " . $title);
                        $def_tpl->parseCurrentBlock();
                        $backlist_shown = true;
                    }
                }
            }
            if ($backlist_shown) {
                $def_tpl->setCurrentBlock("backlink_list");
                $def_tpl->setVariable("BACKLINK_TITLE", $this->lng->txt('glo_term_used_in'));
                $def_tpl->parseCurrentBlock();
            }
        }

        if (!$a_get_html) {
            $tpl->setPermanentLink("git", null, $term_id . "_" . $ref_id);

            // show taxonomy
            $this->showTaxonomy();
        }

        // highlighting?
        if ($this->requested_search_str != "" && !$this->offlineMode()) {
            $cache = ilUserSearchCache::_getInstance($ilUser->getId());
            $cache->switchSearchType(ilUserSearchCache::LAST_QUERY);
            $search_string = $cache->getQuery();

            $p = new ilQueryParser($search_string);
            $p->parse();

            $words = $p->getQuotedWords();
            foreach ($words as $w) {
                ilTextHighlighterGUI::highlight("ilGloContent", $w, $tpl);
            }
            $this->fill_on_load_code = true;
        }
        $tpl->setContent($def_tpl->get());
        if ($this->offlineMode()) {
            return $tpl->printToString();
        } elseif ($a_get_html) {
            return $def_tpl->get();
        }
        return "";
    }

    public function fullscreen(): string
    {
        $html = $this->media("fullscreen");
        return $html;
    }

    /**
     * show media object
     */
    public function media(string $a_mode = "media"): string
    {
        $this->tpl = new ilGlobalTemplate("tpl.fullscreen.html", true, true, "components/ILIAS/COPage");
        $this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
        $this->tpl->setVariable(
            "LOCATION_CONTENT_STYLESHEET",
            ilObjStyleSheet::getContentStylePath($this->content_style_domain->getEffectiveStyleId())
        );

        //$int_links = $page_object->getInternalLinks();
        $med_links = ilMediaItem::_getMapAreasIntLinks($this->requested_mob_id);

        // later
        //$link_xml = $this->getLinkXML($med_links, $this->getLayoutLinkTargets());

        $link_xml = "";

        $media_obj = new ilObjMediaObject($this->requested_mob_id);

        $xml = "<dummy>";
        // todo: we get always the first alias now (problem if mob is used multiple
        // times in page)
        $xml .= $media_obj->getXML(IL_MODE_ALIAS);
        $xml .= $media_obj->getXML(IL_MODE_OUTPUT);
        $xml .= $link_xml;
        $xml .= "</dummy>";

        if (!$this->offlineMode()) {
            $enlarge_path = ilUtil::getImagePath("media/enlarge.svg", false, "output");
            $wb_path = ilFileUtils::getWebspaceDir("output") . "/";
        } else {
            $enlarge_path = "images/media/enlarge.svg";
            $wb_path = "";
        }

        $mode = $a_mode;

        $this->ctrl->setParameter($this, "obj_type", "MediaObject");
        $fullscreen_link =
            $this->getLink($this->requested_ref_id, "fullscreen");
        $this->ctrl->clearParameters($this);

        $params = array('mode' => $mode, 'enlarge_path' => $enlarge_path,
            'link_params' => "ref_id=" . $this->requested_ref_id,'fullscreen_link' => $fullscreen_link,
                        'enable_html_mob' => ilObjMediaObject::isTypeAllowed("html") ? "y" : "n",
            'ref_id' => $this->requested_ref_id, 'pg_frame' => "", 'webspace_path' => $wb_path);
        $output = $this->xsl->process($xml, $params);

        // unmask user html
        $this->tpl->setVariable("MEDIA_CONTENT", $output);

        $this->tpl->parseCurrentBlock();
        if ($this->offlineMode()) {
            $html = $this->tpl->get();
            return $html;
        }
        return "";
    }

    /**
     * show download list
     */
    public function showDownloadList(): void
    {
        $ilAccess = $this->access;
        $lng = $this->lng;
        $ilTabs = $this->tabs_gui;

        if (!$ilAccess->checkAccess("read", "", $this->requested_ref_id)) {
            throw new ilPermissionException($lng->txt("permission_denied"));
        }

        $this->setTabs();
        $ilTabs->activateTab("download");

        // set title header
        $this->tpl->setTitle($this->glossary->getTitle());
        $this->tpl->setTitleIcon(ilUtil::getImagePath("standard/icon_glo.svg"));

        $table = $this->domain->table()->getDownloadListTable($this->glossary)->getComponent();

        $this->tpl->setContent($this->ui_ren->render($table));
    }

    /**
     * send download file (xml/html)
     */
    public function downloadExportFile(): void
    {
        $ilAccess = $this->access;
        $lng = $this->lng;

        if (!$ilAccess->checkAccess("read", "", $this->requested_ref_id)) {
            throw new ilPermissionException($lng->txt("permission_denied"));
        }

        $file_type = "";
        if (($this->requested_table_glossary_download_list_action == "downloadExportFile")
            && !empty($this->requested_table_glossary_download_file_ids)) {
            $file_id = $this->requested_table_glossary_download_file_ids[0];
            $file_type = explode(":", $file_id)[0];
        }

        $file = $this->glossary->getPublicExportFile($file_type);
        if ($file != "") {
            $dir = $this->glossary->getExportDirectory($file_type);
            if (is_file($dir . "/" . $file)) {
                ilFileDelivery::deliverFileLegacy($dir . "/" . $file, $file);
                exit;
            }
        }
        throw new ilGlossaryException($lng->txt("file_not_found"));
    }

    public function setLocator(): void
    {
        $gloss_loc = new ilGlossaryLocatorGUI();
        $gloss_loc->setMode("presentation");
        if (!empty($this->term_id)) {
            $term = new ilGlossaryTerm($this->term_id);
            $gloss_loc->setTerm($term);
        }
        $gloss_loc->setGlossary($this->glossary);
        $gloss_loc->display();
    }

    public function downloadFile(): void
    {
        $ilAccess = $this->access;
        $lng = $this->lng;

        if (!$ilAccess->checkAccess("read", "", $this->requested_ref_id)) {
            throw new ilPermissionException($lng->txt("permission_denied"));
        }

        $file = explode("_", $this->requested_file_id);
        $fileObj = new ilObjFile($file[count($file) - 1], false);
        $fileObj->sendFile();
        exit;
    }

    public function setTabs(): void
    {
        $this->getTabs();
    }

    public function getLink(
        int $a_ref_id,
        string $a_cmd = "",
        int $a_term_id = 0,
        string $a_frame = "",
        string $a_type = ""
    ): string {
        $link = "";
        if ($a_cmd == "") {
            $a_cmd = "layout";
        }
        //$script = "glossary_presentation.php";

        // handle online links
        if (!$this->offlineMode()) {
            //$link = $script."?ref_id=".$a_ref_id;
            switch ($a_cmd) {
                case "fullscreen":
                    $this->ctrl->setParameter($this, "term_id", $a_term_id);
                    $link = $this->ctrl->getLinkTarget($this, "fullscreen");
                    break;

                default:
                    $link .= "&amp;cmd=" . $a_cmd;
                    if ($a_frame != "") {
                        $this->ctrl->setParameter($this, "frame", $a_frame);
                    }
                    if ($a_type != "") {
                        $this->ctrl->setParameter($this, "obj_type", $a_type);
                    }
                    $link = $this->ctrl->getLinkTarget($this, $a_cmd);
                    break;
            }
        } else {	// handle offline links
            switch ($a_cmd) {
                case "fullscreen":
                    $link = "fullscreen.html";		// id is handled by xslt
                    break;

                case "glossary":
                    $link = "term_" . $a_term_id . ".html";
                    break;

                case "downloadFile":
                case "layout":
                default:
                    break;
            }
        }
        $this->ctrl->clearParameters($this);
        return $link;
    }

    public function printViewSelection(): void
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $ilTabs = $this->tabs_gui;

        $this->setTabs();
        $ilTabs->activateTab("print_view");

        $this->initPrintViewSelectionForm();

        $tpl->setContent($this->form->getHTML());
    }

    /**
     * Init print view selection form.
     */
    public function initPrintViewSelectionForm(): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $terms = $this->glossary->getTermList();

        $this->form = new ilPropertyFormGUI();
        //$this->form->setTarget("print_view");
        $this->form->setFormAction($ilCtrl->getFormAction($this));

        // selection type
        $radg = new ilRadioGroupInputGUI($lng->txt("cont_selection"), "sel_type");
        $radg->setValue("glossary");

        // whole glossary
        $op1 = new ilRadioOption($lng->txt("cont_whole_glossary")
                . " (" . $lng->txt("cont_terms") . ": " . count($terms) . ")", "glossary");
        $radg->addOption($op1);

        // selected topic
        if (($t_id = $this->glossary->getTaxonomyId()) > 0 && $this->glossary->getShowTaxonomy()) {
            $op3 = new ilRadioOption($lng->txt("cont_selected_topic"), "sel_topic");
            $radg->addOption($op3);

            // topic drop down
            $si = new ilTaxAssignInputGUI(
                $t_id,
                false,
                $lng->txt("cont_topic"),
                "topic",
                false
            );
            if ($this->tax_node > 0) {
                $si->setValue($this->tax_node);
            }
            $op3->addSubItem($si);
        }

        // selected terms
        $op2 = new ilRadioOption($lng->txt("cont_selected_terms"), "selection");
        $radg->addOption($op2);

        $nl = new ilNestedListInputGUI("", "obj_id");
        $op2->addSubItem($nl);
        foreach ($terms as $t) {
            $nl->addListNode((string) $t["id"], (string) $t["term"], "0", false, false);
        }

        $this->form->addItem($radg);

        $this->form->addCommandButton("printView", $lng->txt("cont_show_print_view"));
        $this->form->setPreventDoubleSubmission(false);

        $this->form->setTitle($lng->txt("cont_print_selection"));
    }

    public function printView(): void
    {
        $ilAccess = $this->access;
        $tpl = $this->tpl;

        $this->tabs_gui->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "printViewSelection")
        );

        if (!$ilAccess->checkAccess("read", "", $this->requested_ref_id)) {
            return;
        }

        $this->initPrintViewSelectionForm();
        $this->form->checkInput();

        $terms = array();
        switch ($this->form->getInput("sel_type")) {
            case "glossary":
                $ts = $this->glossary->getTermList();
                foreach ($ts as $t) {
                    $terms[] = $t["id"];
                }
                break;

            case "sel_topic":
                $t_id = $this->glossary->getTaxonomyId();
                $items = ilObjTaxonomy::getSubTreeItems("glo", $this->glossary->getId(), "term", $t_id, (int) $this->form->getInput("topic"));
                foreach ($items as $i) {
                    if ($i["item_type"] == "term") {
                        $terms[] = $i["item_id"];
                    }
                }
                break;

            case "selection":
                $terms = $this->form->getInput("obj_id");
                break;
        }

        //$tpl->addCss(ilObjStyleSheet::getContentPrintStyle());
        $tpl->addOnLoadCode("il.Util.print();");

        // determine target frames for internal links

        $page_content = "";
        foreach ($terms as $t_id) {
            $page_content .= $this->listDefinitions($this->requested_ref_id, $t_id, true, true, ilPageObjectGUI::PRINTING);
        }
        $tpl->setContent($page_content);
    }

    public function getTabs(): void
    {
        $ilAccess = $this->access;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilHelp = $this->help;

        $ilHelp->setScreenIdComponent("glo");

        if (!$this->offlineMode()) {
            if ($this->ctrl->getCmd() != "listDefinitions") {
                if ($ilAccess->checkAccess("read", "", $this->requested_ref_id)) {
                    if ($this->glossary->isActiveFlashcards()) {
                        $this->tabs_gui->addTab(
                            "flashcards",
                            $lng->txt("glo_flashcards"),
                            $ilCtrl->getLinkTarget($this, "showFlashcards")
                        );
                    }
                    $this->tabs_gui->addTab(
                        "terms",
                        $lng->txt("content"),
                        $ilCtrl->getLinkTarget($this, "listTerms")
                    );
                }

                $this->tabs_gui->addTab(
                    "info",
                    $lng->txt("info_short"),
                    $ilCtrl->getLinkTarget($this, "infoScreen")
                );


                // glossary menu
                if ($ilAccess->checkAccess("read", "", $this->requested_ref_id)) {
                    $this->tabs_gui->addTab(
                        "print_view",
                        $lng->txt("cont_print_view"),
                        $ilCtrl->getLinkTarget($this, "printViewSelection")
                    );

                    // download links
                    if ($this->glossary->isActiveDownloads()) {
                        $this->tabs_gui->addTab(
                            "download",
                            $lng->txt("download"),
                            $ilCtrl->getLinkTarget($this, "showDownloadList")
                        );
                    }
                    //}
                }

                if ($ilAccess->checkAccess("write", "", $this->requested_ref_id) ||
                    $ilAccess->checkAccess("edit_content", "", $this->requested_ref_id)) {
                    $this->tabs_gui->addNonTabbedLink(
                        "editing_view",
                        $lng->txt("glo_editing_view"),
                        "ilias.php?baseClass=ilGlossaryEditorGUI&amp;ref_id=" . $this->requested_ref_id,
                        "_top"
                    );
                }
            }
        } else {
            $this->tabs_gui->addTarget(
                "cont_back",
                "index.html#term_" . $this->term_id,
                "",
                ""
            );
        }
    }

    /**
     * this one is called from the info button in the repository
     */
    public function infoScreen(): void
    {
        $this->ctrl->redirectByClass(ilInfoScreenGUI::class, "showSummary");
    }

    public function outputInfoScreen(): string
    {
        $ilAccess = $this->access;
        $ilTabs = $this->tabs_gui;

        $this->setTabs();
        $ilTabs->activateTab("info");
        $this->lng->loadLanguageModule("meta");

        $info = new ilInfoScreenGUI($this->glossary_gui);
        $info->enablePrivateNotes();
        //$info->enableLearningProgress();

        $info->enableNews();
        if ($ilAccess->checkAccess("write", "", $this->requested_ref_id)) {
            $info->enableNewsEditing();
            $news_set = new ilSetting("news");
            $enable_internal_rss = $news_set->get("enable_rss_for_internal");
            if ($enable_internal_rss) {
                $info->setBlockProperty("news", "settings", true);
            }
        }

        // show standard meta data section
        $info->addMetaDataSections($this->glossary->getId(), 0, $this->glossary->getType());

        ilObjGlossaryGUI::addUsagesToInfo($info, $this->glossary->getId());

        if ($this->offlineMode()) {
            $this->tpl->setContent($info->getHTML());
            return $this->tpl->get();
        } else {
            // forward the command
            $this->ctrl->forwardCommand($info);
        }
        return "";
    }

    public function showTaxonomy(): void
    {
        $ctrl = $this->ctrl;

        if ($this->offlineMode() || !$this->tax_manager->showInPresentation()) {
            return;
        }

        $tax_id = $this->tax_manager->getTaxonomyId();

        $tool_context = $this->global_screen->tool()->context()->current();

        $tool_context->addAdditionalData(
            ilTaxonomyGSToolProvider::SHOW_TAX_TREE,
            true
        );
        $tool_context->addAdditionalData(
            ilTaxonomyGSToolProvider::TAX_TREE_GUI_PATH,
            [self::class]
        );
        $tool_context->addAdditionalData(
            ilTaxonomyGSToolProvider::TAX_ID,
            $tax_id
        );
        $tool_context->addAdditionalData(
            ilTaxonomyGSToolProvider::TAX_TREE_CMD,
            "listTerms"
        );
        $tool_context->addAdditionalData(
            ilTaxonomyGSToolProvider::TAX_TREE_PARENT_CMD,
            "showTaxonomy"
        );
    }

    public function showFlashcards(): void
    {
        $ilTabs = $this->tabs_gui;
        $ilNavigationHistory = $this->nav_history;

        $this->setTabs();
        $ilTabs->activateTab("flashcards");
        $ilNavigationHistory->addItem(
            $this->requested_ref_id,
            $this->ctrl->getLinkTarget($this, "showFlashcards"),
            "glo"
        );
        $flashcards = new ilGlossaryFlashcardGUI();
        $flashcards->listBoxes();
    }
}
