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

use ILIAS\Glossary\Presentation;

/**
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilGlossaryPresentationGUI: ilNoteGUI, ilInfoScreenGUI, ilPresentationListTableGUI, ilGlossaryDefPageGUI
 */
class ilGlossaryPresentationGUI implements ilCtrlBaseClassInterface
{
    protected array $mobs;
    protected bool $fill_on_load_code;
    protected string $offline_dir;
    protected ilPropertyFormGUI $form;
    protected \ILIAS\Glossary\InternalService $service;
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
    protected string $requested_export_type;
    protected \ILIAS\Style\Content\Service $content_style_service;
    protected \ILIAS\Style\Content\GUIService $content_style_gui;
    protected \ILIAS\Style\Content\Object\ObjectFacade $content_style_domain;

    public function __construct(
        string $export_format = "",
        string $export_dir = ""
    ) {
        global $DIC;

        $this->export_format = $export_format;
        $this->setOfflineDirectory($export_dir);
        $this->offline = ($export_format != "");
        $this->access = $DIC->access();
        $this->nav_history = $DIC["ilNavigationHistory"];
        $this->toolbar = $DIC->toolbar();
        $this->user = $DIC->user();
        $this->help = $DIC["ilHelp"];
        $lng = $DIC->language();
        $tpl = $DIC->ui()->mainTemplate();
        $ilCtrl = $DIC->ctrl();
        $ilTabs = $DIC->tabs();

        $this->tabs_gui = $ilTabs;
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->ctrl->saveParameter($this, array("ref_id", "letter", "tax_node"));
        $this->service = $DIC->glossary()
                       ->internal();
        $this->content_style_service =
            $DIC->contentStyle();
        $this->initByRequest();
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
        $service = $this->service;
        $request = $service
            ->gui()
            ->presentation()
            ->request($query_params);

        $this->requested_ref_id = $request->getRefId();
        $this->term_id = $request->getTermId();
        $this->glossary_gui = $service->gui()->presentation()->ObjGlossaryGUI($this->requested_ref_id);
        $this->glossary = $this->glossary_gui->getGlossary();
        $this->requested_def_page_id = $request->getDefinitionPageId();
        $this->requested_search_str = $request->getSearchString();
        $this->requested_file_id = $request->getFileId();
        $this->requested_mob_id = $request->getMobId();
        $this->requested_export_type = $request->getExportType();


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
        $cmd = $this->ctrl->getCmd("listTerms");

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

            case "ilpresentationlisttablegui":
                $prtab = $this->getPresentationTable();
                $this->ctrl->forwardCommand($prtab);
                return;

            case "ilglossarydefpagegui":
                $page_gui = new ilGlossaryDefPageGUI($this->requested_def_page_id);
                $this->basicPageGuiInit($page_gui);
                $this->ctrl->forwardCommand($page_gui);
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
        $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_glo.svg"));

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

    public function listTerms(): string
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

        if (!$this->offlineMode()) {
            $ilNavigationHistory->addItem(
                $this->requested_ref_id,
                $this->ctrl->getLinkTarget($this, "listTerms"),
                "glo"
            );

            // alphabetical navigation
            $ai = new ilAlphabetInputGUI($lng->txt("glo_quick_navigation"), "first");

            $ai->setFixDBUmlauts(true);

            $first_letters = $this->glossary->getFirstLetters($this->tax_node);
            if (!in_array($this->requested_letter, $first_letters)) {
                $first_letters[] = ilUtil::stripSlashes($this->requested_letter);
            }
            $ai->setLetters($first_letters);

            $ai->setParentCommand($this, "chooseLetter");
            $ai->setHighlighted($this->requested_letter);
            $ilToolbar->addInputItem($ai, true);
        }

        $ret = $this->listTermByGiven();
        $ilCtrl->setParameter($this, "term_id", "");

        $ilTabs->activateTab("terms");

        // show taxonomy
        $this->showTaxonomy();

        $this->tpl->setPermanentLink("glo", $this->glossary->getRefId());

        return $ret;
    }

    /**
     * list glossary terms
     */
    public function listTermByGiven(): string
    {
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;
        $lng = $this->lng;
        $tpl = $this->tpl;

        if (!$ilAccess->checkAccess("read", "", $this->requested_ref_id)) {
            throw new ilPermissionException($lng->txt("permission_denied"));
        }

        $this->lng->loadLanguageModule("meta");

        $this->setTabs();

        // load template for table
        //		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");

        if ($this->glossary->getPresentationMode() == "full_def") {
            $this->setContentStyles();
        }

        $table = $this->getPresentationTable();

        if (!$this->offlineMode()) {
            $tpl->setContent($ilCtrl->getHTML($table));
        } else {
            $this->tpl->setVariable("ADM_CONTENT", $table->getHTML());
            return $this->tpl->printToString();
        }
        return "";
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
    public function getPresentationTable(): ilPresentationListTableGUI
    {
        $table = new ilPresentationListTableGUI(
            $this,
            "listTerms",
            $this->glossary,
            $this->offlineMode(),
            $this->tax_node,
            $this->glossary->getTaxonomyId()
        );
        return $table;
    }

    public function applyFilter(): void
    {
        $prtab = $this->getPresentationTable();
        $prtab->resetOffset();
        $prtab->writeFilterToSession();
        $this->listTerms();
    }

    public function resetFilter(): void
    {
        $prtab = $this->getPresentationTable();
        $prtab->resetOffset();
        $prtab->resetFilter();
        $this->listTerms();
    }

    /**
    * list definitions of a term
    */
    public function listDefinitions(
        int $a_ref_id = 0,
        int $a_term_id = 0,
        bool $a_get_html = false,
        string $a_page_mode = ilPageObjectGUI::PRESENTATION
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

        // tabs
        if ($this->glossary->getPresentationMode() != "full_def" &&
            $a_page_mode != ilPageObjectGUI::PRINTING) {
            $this->showDefinitionTabs("term_content");
        }

        $term = new ilGlossaryTerm($term_id);

        if (!$a_get_html) {
            $tpl->loadStandardTemplate();

            $this->setContentStyles();

            if (!$this->offlineMode()) {
                $this->setLocator();
            }

            $tpl->setTitleIcon(ilUtil::getImagePath("icon_glo.svg"));
            $tpl->setTitle($this->lng->txt("cont_term") . ": " . $term->getTerm());

            // advmd block
            $cmd = null;
            if ($ilAccess->checkAccess("write", "", $this->requested_ref_id)) {
                $cmd = array("edit" => $this->ctrl->getLinkTargetByClass(array("ilglossaryeditorgui", "ilobjglossarygui", "ilglossarytermgui", "ilobjectmetadatagui"), ""));
            }
            $mdgui = new ilObjectMetaDataGUI($this->glossary, "term", $term->getId());
            $tpl->setRightContent($mdgui->getBlockHTML($cmd));
        }

        $def_tpl = new ilTemplate("tpl.glossary_definition_list.html", true, true, "Modules/Glossary");

        $defs = ilGlossaryDefinition::getDefinitionList($term_id);
        $def_tpl->setVariable("TXT_TERM", $term->getTerm());
        $this->mobs = array();

        // toc
        if (count($defs) > 1 && $a_page_mode == ilPageObjectGUI::PRESENTATION) {
            $def_tpl->setCurrentBlock("toc");
            for ($j = 1, $jMax = count($defs); $j <= $jMax; $j++) {
                $def_tpl->setCurrentBlock("toc_item");
                $def_tpl->setVariable("TOC_DEF_NR", $j);
                $def_tpl->setVariable("TOC_DEF", $lng->txt("cont_definition"));
                $def_tpl->parseCurrentBlock();
            }
            $def_tpl->setCurrentBlock("toc");
            $def_tpl->parseCurrentBlock();
        }

        for ($j = 0, $jMax = count($defs); $j < $jMax; $j++) {
            $def = $defs[$j];
            $page_gui = new ilGlossaryDefPageGUI($def["id"]);
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
            $page_gui->setFullscreenLink($this->getLink($ref_id, "fullscreen", $term_id, $def["id"]));

            $page_gui->setTemplateOutput(false);
            $page_gui->setRawPageContent(true);
            if (!$this->offlineMode()) {
                $output = $page_gui->showPage();
            } else {
                $output = $page_gui->presentation($page_gui->getOutputMode());
            }

            if (count($defs) > 1) {
                $def_tpl->setCurrentBlock("definition_header");
                $def_tpl->setVariable(
                    "TXT_DEFINITION",
                    $this->lng->txt("cont_definition") . " " . ($j + 1)
                );
                $def_tpl->setVariable("DEF_NR", ($j + 1));
                $def_tpl->parseCurrentBlock();
            }

            $def_tpl->setCurrentBlock("definition");
            $def_tpl->setVariable("PAGE_CONTENT", $output);
            $def_tpl->parseCurrentBlock();
        }

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
            $tpl->setPermanentLink("git", $term_id, "", ILIAS_HTTP_PATH .
                "/goto.php?target=" .
                "git" .
                "_" . $term_id . "_" . $ref_id . "&client_id=" . CLIENT_ID);

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

    public function showDefinitionTabs(string $a_act): void
    {
        $ilTabs = $this->tabs_gui;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilHelp = $this->help;

        if (!$this->offlineMode()) {
            $ilHelp->setScreenIdComponent("glo");

            $ilCtrl->setParameter($this, "term_id", "");
            $back = $ilCtrl->getLinkTarget($this, "listTerms");
            $ilCtrl->setParameter($this, "term_id", $this->term_id);
            $ilCtrl->saveParameter($this, "term_id");

            $ilTabs->setBackTarget($this->lng->txt("obj_glo"), $back);

            $ilTabs->addTab(
                "term_content",
                $lng->txt("content"),
                $ilCtrl->getLinkTarget($this, "listDefinitions")
            );

            $ilTabs->addTab(
                "print_view",
                $lng->txt("print_view"),
                $ilCtrl->getLinkTarget($this, "printViewSelection")
            );

            $ilCtrl->setParameterByClass("ilglossarytermgui", "term_id", $this->term_id);
            if (ilGlossaryTerm::_lookGlossaryID($this->term_id) == $this->glossary->getId()) {
                $ilTabs->addNonTabbedLink(
                    "editing_view",
                    $lng->txt("glo_editing_view"),
                    $ilCtrl->getLinkTargetByClass(array("ilglossaryeditorgui", "ilobjglossarygui", "ilglossarytermgui"), "listDefinitions")
                );
                //"ilias.php?baseClass=ilGlossaryEditorGUI&amp;ref_id=".$this->requested_ref_id."&amp;edit_term=".$this->term_id);
            }
            $ilTabs->activateTab($a_act);
        }
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
        $this->tpl = new ilGlobalTemplate("tpl.fullscreen.html", true, true, "Services/COPage");
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

        $xsl = file_get_contents("./Services/COPage/xsl/page.xsl");
        $args = array( '/_xml' => $xml, '/_xsl' => $xsl );
        $xh = xslt_create();

        if (!$this->offlineMode()) {
            $enlarge_path = ilUtil::getImagePath("enlarge.svg", false, "output");
            $wb_path = ilFileUtils::getWebspaceDir("output") . "/";
        } else {
            $enlarge_path = "images/enlarge.svg";
            $wb_path = "";
        }

        $mode = $a_mode;

        $this->ctrl->setParameter($this, "obj_type", "MediaObject");
        $fullscreen_link =
            $this->getLink($this->requested_ref_id, "fullscreen");
        $this->ctrl->clearParameters($this);

        $params = array('mode' => $mode, 'enlarge_path' => $enlarge_path,
            'link_params' => "ref_id=" . $this->requested_ref_id,'fullscreen_link' => $fullscreen_link,
            'ref_id' => $this->requested_ref_id, 'pg_frame' => "", 'webspace_path' => $wb_path);
        $output = xslt_process($xh, "arg:/_xml", "arg:/_xsl", null, $args, $params);
        xslt_free($xh);

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

        $this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.glo_download_list.html", "Modules/Glossary");

        $this->setTabs();
        $ilTabs->activateTab("download");

        // set title header
        $this->tpl->setTitle($this->glossary->getTitle());
        $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_glo.svg"));

        // create table
        $tbl = new ilTableGUI();

        // load files templates
        $this->tpl->addBlockFile("DOWNLOAD_TABLE", "download_table", "tpl.table.html");

        // load template for table content data
        $this->tpl->addBlockFile("TBL_CONTENT", "tbl_content", "tpl.download_file_row.html", "Modules/Glossary");

        $export_files = array();
        $types = array("xml", "html");
        foreach ($types as $type) {
            if ($this->glossary->getPublicExportFile($type) != "") {
                $dir = $this->glossary->getExportDirectory($type);
                if (is_file($this->glossary->getExportDirectory($type) . "/" .
                    $this->glossary->getPublicExportFile($type))) {
                    $size = filesize($this->glossary->getExportDirectory($type) . "/" .
                        $this->glossary->getPublicExportFile($type));
                    $export_files[] = array("type" => $type,
                        "file" => $this->glossary->getPublicExportFile($type),
                        "size" => $size);
                }
            }
        }

        $num = 0;

        $tbl->setTitle($this->lng->txt("download"));

        $tbl->setHeaderNames(array($this->lng->txt("cont_format"),
            $this->lng->txt("cont_file"),
            $this->lng->txt("size"), $this->lng->txt("date"),
            ""));

        $cols = array("format", "file", "size", "date", "download");
        $header_params = array("ref_id" => $this->requested_ref_id,
            "cmd" => "showDownloadList", "cmdClass" => strtolower(get_class($this)));
        $tbl->setHeaderVars($cols, $header_params);
        $tbl->setColumnWidth(array("10%", "30%", "20%", "20%","20%"));
        $tbl->disable("sort");
        // footer
        $tbl->disable("footer");
        $tbl->setMaxCount(count($export_files));

        $tbl->render();
        if (count($export_files) > 0) {
            $i = 0;
            foreach ($export_files as $exp_file) {
                $this->tpl->setCurrentBlock("tbl_content");
                $this->tpl->setVariable("TXT_FILENAME", $exp_file["file"]);

                $this->tpl->setVariable("TXT_SIZE", $exp_file["size"]);
                $this->tpl->setVariable("TXT_FORMAT", strtoupper($exp_file["type"]));
                $this->tpl->setVariable("CHECKBOX_ID", $exp_file["type"] . ":" . $exp_file["file"]);

                $file_arr = explode("__", $exp_file["file"]);
                $this->tpl->setVariable("TXT_DATE", date("Y-m-d H:i:s", $file_arr[0]));

                $this->tpl->setVariable("TXT_DOWNLOAD", $this->lng->txt("download"));
                $this->ctrl->setParameter($this, "type", $exp_file["type"]);
                $this->tpl->setVariable(
                    "LINK_DOWNLOAD",
                    $this->ctrl->getLinkTarget($this, "downloadExportFile")
                );

                $this->tpl->parseCurrentBlock();
            }
        } else {
            $this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
            $this->tpl->setVariable("NUM_COLS", 5);
            $this->tpl->parseCurrentBlock();
        }
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

        $file = $this->glossary->getPublicExportFile($this->requested_export_type);
        if ($this->glossary->getPublicExportFile($this->requested_export_type) != "") {
            $dir = $this->glossary->getExportDirectory($this->requested_export_type);
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
        int $a_def_id = 0,
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
                    $this->ctrl->setParameter($this, "def_id", $a_def_id);
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

        $ilCtrl->saveParameter($this, "term_id");

        if ($this->term_id == 0) {
            $this->setTabs();
            $ilTabs->activateTab("print_view");
        } else {
            $tpl->setTitleIcon(ilUtil::getImagePath("icon_glo.svg"));
            $term = new ilGlossaryTerm($this->term_id);
            $tpl->setTitle($this->lng->txt("cont_term") . ": " . $term->getTerm());
            $this->showDefinitionTabs("print_view");
        }

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

        // current term
        if ($this->term_id > 0) {
            $op1 = new ilRadioOption($lng->txt("cont_current_term"), "term");
            $radg->addOption($op1);
            $radg->setValue("term");
        }

        // whole glossary
        $op2 = new ilRadioOption($lng->txt("cont_whole_glossary")
                . " (" . $lng->txt("cont_terms") . ": " . count($terms) . ")", "glossary");
        $radg->addOption($op2);

        // selected topic
        if (($t_id = $this->glossary->getTaxonomyId()) > 0 && $this->glossary->getShowTaxonomy()) {
            $op4 = new ilRadioOption($lng->txt("cont_selected_topic"), "sel_topic");
            $radg->addOption($op4);

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
            $op4->addSubItem($si);
        }

        // selected terms
        $op3 = new ilRadioOption($lng->txt("cont_selected_terms"), "selection");
        $radg->addOption($op3);

        $nl = new ilNestedListInputGUI("", "obj_id");
        $op3->addSubItem($nl);
        //var_dump($terms);
        foreach ($terms as $t) {
            $nl->addListNode($t["id"], $t["term"], 0, false, false);
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

            case "term":
                $terms = array($this->term_id);
                break;
        }

        //$tpl->addCss(ilObjStyleSheet::getContentPrintStyle());
        $tpl->addOnLoadCode("il.Util.print();");

        // determine target frames for internal links

        $page_content = "";
        foreach ($terms as $t_id) {
            $page_content .= $this->listDefinitions($this->requested_ref_id, $t_id, true, ilPageObjectGUI::PRINTING);
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
                    $this->tabs_gui->addTab(
                        "terms",
                        $lng->txt("cont_terms"),
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
        $this->ctrl->setCmd("showSummary");
        $this->ctrl->setCmdClass("ilinfoscreengui");
        $this->outputInfoScreen();
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

    public function chooseLetter(): void
    {
        $ilCtrl = $this->ctrl;
        $ilCtrl->redirect($this, "listTerms");
    }

    public function showTaxonomy(): void
    {
        global $DIC;
        $ctrl = $this->ctrl;
        if (!$this->offlineMode() && $this->glossary->getShowTaxonomy()) {
            $tax_ids = ilObjTaxonomy::getUsageOfObject($this->glossary->getId());
            if (count($tax_ids) > 0) {
                $tax_id = $tax_ids[0];
                $DIC->globalScreen()->tool()->context()->current()
                    ->addAdditionalData(
                        ilTaxonomyGSToolProvider::SHOW_TAX_TREE,
                        true
                    );
                $DIC->globalScreen()->tool()->context()->current()
                    ->addAdditionalData(
                        ilTaxonomyGSToolProvider::TAX_TREE_GUI_PATH,
                        [self::class]
                    );
                $DIC->globalScreen()->tool()->context()->current()
                    ->addAdditionalData(
                        ilTaxonomyGSToolProvider::TAX_ID,
                        $tax_id
                    );
                $DIC->globalScreen()->tool()->context()->current()
                    ->addAdditionalData(
                        ilTaxonomyGSToolProvider::TAX_TREE_CMD,
                        "listTerms"
                    );
                $DIC->globalScreen()->tool()->context()->current()
                    ->addAdditionalData(
                        ilTaxonomyGSToolProvider::TAX_TREE_PARENT_CMD,
                        "showTaxonomy"
                    );

                $tax_exp = new ilTaxonomyExplorerGUI(
                    get_class($this),
                    "showTaxonomy",
                    $tax_id,
                    "ilglossarypresentationgui",
                    "listTerms"
                );
                /*
                if (!$tax_exp->handleCommand()) {
                    //$tpl->setLeftNavContent($tax_exp->getHTML());
                    //$tpl->setLeftContent($tax_exp->getHTML()."&nbsp;");
                }*/
            }
        }
    }
}
