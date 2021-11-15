<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Glossary\Presentation;

/**
 * Glossary presentation
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @ilCtrl_Calls ilGlossaryPresentationGUI: ilNoteGUI, ilInfoScreenGUI, ilPresentationListTableGUI, ilGlossaryDefPageGUI
 */
class ilGlossaryPresentationGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTabsGUI
     */
    protected $tabs_gui;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilErrorHandling
     */
    protected $error;

    /**
     * @var ilNavigationHistory
     */
    protected $nav_history;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var \ilObjUser
     */
    protected $user;

    /**
     * @var \ilHelpGUI
     */
    protected $help;

    /**
     * @var \ilObjGlossary
     */
    protected $glossary;

    /**
     * @var \ilObjGlossaryGUI
     */
    protected $glossary_gui;

    /**
     * @var \ilTemplate
     */
    protected $tpl;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * @var int taxonomy node id
     */
    protected $tax_node;

    /**
     * @var int taxonomy id
     */
    protected $tax_id;

    /**
     * @var \ilObjTaxonomy
     */
    protected $tax;


    /**
     * @var Presentation\GlossaryPresentationService
     */
    protected $service;

    /**
     * @var int
     */
    protected $term_id;

    /**
     * @var int
     */
    protected $requested_ref_id;

    /**
     * @var string
     */
    protected $requested_letter;

    /**
     * @var int
     */
    protected $requested_def_page_id;

    /**
     * @var string
     */
    protected $requested_search_str;

    /**
     * @var string
     */
    protected $requested_file_id;

    /**
     * @var int
     */
    protected $requested_mob_id;

    /**
     * @var string
     */
    protected $requested_export_type;


    /**
    * Constructor
    * @access	public
    */
    public function __construct($export_format = "", $export_dir = "")
    {
        global $DIC;

        $this->export_format = $export_format;
        $this->setOfflineDirectory($export_dir);
        $this->offline = ($export_format != "");
        $this->access = $DIC->access();
        $this->error = $DIC["ilErr"];
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

        // note: using $DIC->http()->request()->getQueryParams() here will
        // fail, since the goto magic currently relies on setting $_GET
        $this->initByRequest($_GET);
    }

    /**
     * Init services and this class by request params.
     *
     * The request params are usually retrieved by HTTP request, but
     * also adjusted during HTML exports, this is, why this method needs to be public.
     *
     * @param $query_params
     * @throws ilGlossaryException
     */
    public function initByRequest(array $query_params)
    {
        $this->service = new Presentation\GlossaryPresentationService(
            $this->user,
            $query_params,
            $this->offline
        );

        $request = $this->service->getRequest();

        $this->requested_ref_id = $request->getRequestedRefId();
        $this->term_id = $request->getRequestedTermId();
        $this->glossary_gui = $this->service->getGlossaryGUI();
        $this->glossary = $this->service->getGlossary();
        $this->requested_def_page_id = $request->getRequestedDefinitionPageId();
        $this->requested_search_str = $request->getRequestedSearchString();
        $this->requested_file_id = $request->getRequestedFileId();
        $this->requested_mob_id = $request->getRequestedMobId();
        $this->requested_export_type = (string) $query_params["type"];


        // determine term id and check whether it is valid (belongs to
        // current glossary or a virtual (online) sub-glossary)
        $glo_ids = $this->glossary->getAllGlossaryIds();
        if (!is_array($glo_ids)) {
            $glo_ids = array($glo_ids);
        }
        $term_glo_id = ilGlossaryTerm::_lookGlossaryID($this->term_id);
        if (!in_array($term_glo_id, $glo_ids) && !ilGlossaryTermReferences::isReferenced($glo_ids, $this->term_id)) {
            if ((int) $this->term_id > 0) {
                throw new ilGlossaryException("Term ID does not match the glossary.");
            }
            $this->term_id = 0;
        }

        $this->tax_node = 0;
        $this->tax_id = $this->glossary->getTaxonomyId();
        if ($this->tax_id > 0 && $this->glossary->getShowTaxonomy()) {
            $this->tax = new ilObjTaxonomy($this->tax_id);
        }
        $requested_tax_node = $request->getRequestedTaxNode();
        if ((int) $requested_tax_node > 1 && $this->tax->getTree()->readRootId() != $requested_tax_node) {
            $this->tax_node = $requested_tax_node;
        }

        $this->requested_letter = $request->getRequestedLetter();
    }

    /**
     * Inject template
     */
    public function injectTemplate($tpl)
    {
        $this->tpl = $tpl;
    }

    /**
    * set offline mode (content is generated for offline package)
    */
    public function setOfflineMode($a_offline = true)
    {
        $this->offline = $a_offline;
    }
    
    /**
    * checks wether offline content generation is activated
    */
    public function offlineMode()
    {
        return $this->offline;
    }

    /**
    * Set offline directory.
    */
    public function setOfflineDirectory($a_dir)
    {
        $this->offline_dir = $a_dir;
    }
    
    
    /**
    * Get offline directory.
    */
    public function getOfflineDirectory()
    {
        return $this->offline_dir;
    }


    /**
     * execute command
     */
    public function executeCommand()
    {
        $lng = $this->lng;
        $ilAccess = $this->access;
        $ilErr = $this->error;
        
        $lng->loadLanguageModule("content");

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("listTerms");

        // check write permission
        if (!$ilAccess->checkAccess("read", "", $this->requested_ref_id) &&
            !($ilAccess->checkAccess("visible", "", $this->requested_ref_id) &&
                ($cmd == "infoScreen" || strtolower($next_class) == "ilinfoscreengui"))) {
            $ilErr->raiseError($lng->txt("permission_denied"), $ilErr->MESSAGE);
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
                break;

            case "ilglossarydefpagegui":
                $page_gui = new ilGlossaryDefPageGUI($this->requested_def_page_id);
                $this->basicPageGuiInit($page_gui);
                $this->ctrl->forwardCommand($page_gui);
                break;

            default:
                $ret = $this->$cmd();
                break;
        }
        $this->tpl->printToStdout();
    }

    public function prepareOutput()
    {
        $this->tpl->loadStandardTemplate();
        $title = $this->glossary->getTitle();

        $this->tpl->setTitle($title);
        $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_glo.svg"));

        $this->setLocator();
    }


    /**
     * Basic page gui initialisation
     *
     * @param
     * @return
     */
    public function basicPageGuiInit(\ilPageObjectGUI $a_page_gui)
    {
        $a_page_gui->setStyleId(ilObjStyleSheet::getEffectiveContentStyleId(
            $this->glossary->getStyleSheetId(),
            "glo"
        ));
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

    /**
     * List all terms
     */
    public function listTerms()
    {
        $ilNavigationHistory = $this->nav_history;
        $ilAccess = $this->access;
        $lng = $this->lng;
        $ilToolbar = $this->toolbar;
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs_gui;
        $ilErr = $this->error;

        
        if (!$ilAccess->checkAccess("read", "", $this->requested_ref_id)) {
            $ilErr->raiseError($lng->txt("permission_denied"), $ilErr->MESSAGE);
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
            if (!is_array($first_letters)) {
                $first_letters = [];
            }
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
    public function listTermByGiven()
    {
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;
        $lng = $this->lng;
        $tpl = $this->tpl;
        
        if (!$ilAccess->checkAccess("read", "", $this->requested_ref_id)) {
            $ilErr->raiseError($lng->txt("permission_denied"), $ilErr->MESSAGE);
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
    }

    /**
     * Set content styles
     */
    protected function setContentStyles()
    {
        $tpl = $this->tpl;

        if (!$this->offlineMode()) {
            $tpl->addCss(ilObjStyleSheet::getContentStylePath(ilObjStyleSheet::getEffectiveContentStyleId(
                $this->glossary->getStyleSheetId(),
                "glo"
            )));
            $tpl->addCss(ilObjStyleSheet::getSyntaxStylePath());
        } else {
            $tpl->addCss("content.css");
            $tpl->addCss("syntaxhighlight.css");
        }
    }

    /**
     * Get presentation table
     *
     * @param
     * @return
     */
    public function getPresentationTable()
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
    
    /**
     * Apply filter
     */
    public function applyFilter()
    {
        $ilTabs = $this->tabs_gui;

        $prtab = $this->getPresentationTable();
        $prtab->resetOffset();
        $prtab->writeFilterToSession();
        $this->listTerms();
    }
    
    /**
     * Reset filter
     * (note: this function existed before data table filter has been introduced
     */
    public function resetFilter()
    {
        $prtab = $this->getPresentationTable();
        $prtab->resetOffset();
        $prtab->resetFilter();
        $this->listTerms();
    }

    /**
    * list definitions of a term
    */
    public function listDefinitions($a_ref_id = 0, $a_term_id = 0, $a_get_html = false, $a_page_mode = ilPageObjectGUI::PRESENTATION)
    {
        $ilUser = $this->user;
        $ilAccess = $this->access;
        $lng = $this->lng;
        $ilErr = $this->error;
        $tpl = $this->tpl;

        if ($a_ref_id == 0) {
            $ref_id = (int) $this->requested_ref_id;
        } else {
            $ref_id = $a_ref_id;
        }
        if ($a_term_id == 0) {
            $term_id = $this->term_id;
        } else {
            $term_id = $a_term_id;
        }
        
        if (!$ilAccess->checkAccess("read", "", $ref_id)) {
            $ilErr->raiseError($lng->txt("permission_denied"), $ilErr->MESSAGE);
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
            for ($j = 1; $j <= count($defs); $j++) {
                $def_tpl->setCurrentBlock("toc_item");
                $def_tpl->setVariable("TOC_DEF_NR", $j);
                $def_tpl->setVariable("TOC_DEF", $lng->txt("cont_definition"));
                $def_tpl->parseCurrentBlock();
            }
            $def_tpl->setCurrentBlock("toc");
            $def_tpl->parseCurrentBlock();
        }

        for ($j = 0; $j < count($defs); $j++) {
            $def = $defs[$j];
            $page_gui = new ilGlossaryDefPageGUI($def["id"]);
            $this->basicPageGuiInit($page_gui);
            $page_gui->setGlossary($this->glossary);
            $page_gui->setOutputMode($a_page_mode);
            $page_gui->setStyleId($this->glossary->getStyleSheetId());
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
                
                if ($type[0] == 'lm') {
                    if ($type[1] == 'pg') {
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
            if (is_array($words)) {
                foreach ($words as $w) {
                    ilTextHighlighterGUI::highlight("ilGloContent", $w, $tpl);
                }
            }
            $this->fill_on_load_code = true;
        }
        $tpl->setContent($def_tpl->get());
        if ($this->offlineMode()) {
            return $tpl->printToString();
        } elseif ($a_get_html) {
            return $def_tpl->get();
        }
    }
    
    /**
     * Definitions tabs
     *
     * @param
     * @return
     */
    public function showDefinitionTabs($a_act)
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
    

    /**
    * show fullscreen view
    */
    public function fullscreen()
    {
        $html = $this->media("fullscreen");
        return $html;
    }

    /**
    * show media object
    */
    public function media($a_mode = "media")
    {
        $this->tpl = new ilGlobalTemplate("tpl.fullscreen.html", true, true, "Services/COPage");
        $this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
        $this->tpl->setVariable(
            "LOCATION_CONTENT_STYLESHEET",
            ilObjStyleSheet::getContentStylePath($this->glossary->getStyleSheetId())
        );

        //$int_links = $page_object->getInternalLinks();
        $med_links = ilMediaItem::_getMapAreasIntLinks($this->requested_mob_id);

        // later
        //$link_xml = $this->getLinkXML($med_links, $this->getLayoutLinkTargets());

        $link_xlm = "";

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
            $wb_path = ilUtil::getWebspaceDir("output") . "/";
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
            'ref_id' => $this->requested_ref_id, 'pg_frame' => $pg_frame, 'webspace_path' => $wb_path);
        $output = xslt_process($xh, "arg:/_xml", "arg:/_xsl", null, $args, $params);
        echo xslt_error($xh);
        xslt_free($xh);

        // unmask user html
        $this->tpl->setVariable("MEDIA_CONTENT", $output);

        $this->tpl->parseCurrentBlock();
        if ($this->offlineMode()) {
            $html = $this->tpl->get();
            return $html;
        }
    }

    /**
    * show download list
    */
    public function showDownloadList()
    {
        $ilAccess = $this->access;
        $lng = $this->lng;
        $ilTabs = $this->tabs_gui;

        if (!$ilAccess->checkAccess("read", "", $this->requested_ref_id)) {
            $ilErr->raiseError($lng->txt("permission_denied"), $ilErr->MESSAGE);
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
        $this->tpl->addBlockfile("DOWNLOAD_TABLE", "download_table", "tpl.table.html");

        // load template for table content data
        $this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.download_file_row.html", "Modules/Glossary");

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

                $css_row = ilUtil::switchColor($i++, "tblrow1", "tblrow2");
                $this->tpl->setVariable("CSS_ROW", $css_row);

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
        } //if is_array
        else {
            $this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
            $this->tpl->setVariable("NUM_COLS", 5);
            $this->tpl->parseCurrentBlock();
        }

        //$this->tpl->show();
    }

    /**
    * send download file (xml/html)
    */
    public function downloadExportFile()
    {
        $ilAccess = $this->access;
        $ilErr = $this->error;
        $lng = $this->lng;
        
        if (!$ilAccess->checkAccess("read", "", $this->requested_ref_id)) {
            $ilErr->raiseError($lng->txt("permission_denied"), $ilErr->error_obj->MESSAGE);
        }

        $file = $this->glossary->getPublicExportFile($this->requested_export_type);
        if ($this->glossary->getPublicExportFile($this->requested_export_type) != "") {
            $dir = $this->glossary->getExportDirectory($this->requested_export_type);
            if (is_file($dir . "/" . $file)) {
                ilUtil::deliverFile($dir . "/" . $file, $file);
                exit;
            }
        }
        $ilErr->raiseError($this->lng->txt("file_not_found"), $ilErr->MESSAGE);
    }

    /**
    * set Locator
    *
    * @param	object	tree object
    * @param	integer	reference id
    * @access	public
    */
    public function setLocator($a_tree = "", $a_id = "")
    {
        $gloss_loc = new ilGlossaryLocatorGUI();
        $gloss_loc->setMode("presentation");
        if (!empty($this->term_id)) {
            $term = new ilGlossaryTerm($this->term_id);
            $gloss_loc->setTerm($term);
        }
        $gloss_loc->setGlossary($this->glossary);
        //$gloss_loc->setDefinition($this->definition);
        $gloss_loc->display();
    }

    /**
    * download file of file lists
    */
    public function downloadFile()
    {
        $ilAccess = $this->access;
        $ilErr = $this->error;
        $lng = $this->lng;
        
        if (!$ilAccess->checkAccess("read", "", $this->requested_ref_id)) {
            $ilErr->raiseError($lng->txt("permission_denied"), $ilErr->MESSAGE);
        }

        $file = explode("_", $this->requested_file_id);
        $fileObj = new ilObjFile($file[count($file) - 1], false);
        $fileObj->sendFile();
        exit;
    }

    /**
    * output tabs
    */
    public function setTabs()
    {
        $this->getTabs();
    }


    /**
    * handles links for learning module presentation
    */
    public function getLink(
        $a_ref_id,
        $a_cmd = "",
        $a_term_id = "",
        $a_def_id = "",
        $a_frame = "",
        $a_type = ""
    ) {
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
                    if ($a_obj_id != "") {
                        switch ($a_type) {
                            case "MediaObject":
                                $this->ctrl->setParameter($this, "mob_id", $a_obj_id);
                                break;
                                
                            default:
                                $this->ctrl->setParameter($this, "def_id", $a_def_id);
                                break;
                        }
                    }
                    if ($a_type != "") {
                        $this->ctrl->setParameter($this, "obj_type", $a_type);
                    }
                    $link = $this->ctrl->getLinkTarget($this, $a_cmd);
//					$link = str_replace("&", "&amp;", $link);
                    break;
            }
        } else {	// handle offline links
            switch ($a_cmd) {
                case "downloadFile":
                    break;
                    
                case "fullscreen":
                    $link = "fullscreen.html";		// id is handled by xslt
                    break;
                    
                case "layout":
                    break;
                    
                case "glossary":
                    $link = "term_" . $a_obj_id . ".html";
                    break;
                
                case "media":
                    $link = "media_" . $a_obj_id . ".html";
                    break;
                    
                default:
                    break;
            }
        }
        $this->ctrl->clearParameters($this);
        return $link;
    }

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
        $ilTabs = $this->tabs_gui;

        $ilCtrl->saveParameter($this, "term_id");
        
        if ((int) $this->term_id == 0) {
            $this->setTabs();
            $ilTabs->activateTab("print_view");
        } else {
            $tpl->setTitleIcon(ilUtil::getImagePath("icon_glo.svg"));
            $term = new ilGlossaryTerm((int) $this->term_id);
            $tpl->setTitle($this->lng->txt("cont_term") . ": " . $term->getTerm());
            $this->showDefinitionTabs("print_view");
        }

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

        $terms = $this->glossary->getTermList();

        $this->form = new ilPropertyFormGUI();
        //$this->form->setTarget("print_view");
        $this->form->setFormAction($ilCtrl->getFormAction($this));
        
        // selection type
        $radg = new ilRadioGroupInputGUI($lng->txt("cont_selection"), "sel_type");
        $radg->setValue("glossary");
        
        // current term
        if ((int) $this->term_id > 0) {
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
                $si->setValue((int) $this->tax_node);
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

    /**
     * Print View
     *
     * @param
     * @return
     */
    public function printView()
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

        $terms = array();
        switch ($_POST["sel_type"]) {
            case "glossary":
                $ts = $this->glossary->getTermList();
                foreach ($ts as $t) {
                    $terms[] = $t["id"];
                }
                break;
                
            case "sel_topic":
                $t_id = $this->glossary->getTaxonomyId();
                $items = ilObjTaxonomy::getSubTreeItems("glo", $this->glossary->getId(), "term", $t_id, (int) $_POST["topic"]);
                foreach ($items as $i) {
                    if ($i["item_type"] == "term") {
                        $terms[] = $i["item_id"];
                    }
                }
                break;

            case "selection":
                if (is_array($_POST["obj_id"])) {
                    $terms = $_POST["obj_id"];
                } else {
                    $terms = array();
                }
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

    /**
    * get tabs
    */
    public function getTabs()
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

                if ($ilAccess->checkAccess("write", "", (int) $this->requested_ref_id) ||
                    $ilAccess->checkAccess("edit_content", "", (int) $this->requested_ref_id)) {
                    $this->tabs_gui->addNonTabbedLink(
                        "editing_view",
                        $lng->txt("glo_editing_view"),
                        "ilias.php?baseClass=ilGlossaryEditorGUI&amp;ref_id=" . (int) $this->requested_ref_id,
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
    * not very nice to set cmdClass/Cmd manually, if everything
    * works through ilCtrl in the future this may be changed
    */
    public function infoScreen()
    {
        $this->ctrl->setCmd("showSummary");
        $this->ctrl->setCmdClass("ilinfoscreengui");
        $this->outputInfoScreen();
    }

    /**
    * info screen
    */
    public function outputInfoScreen()
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
    }
    
    /**
     * Choose first letter
     *
     * @param
     * @return
     */
    public function chooseLetter()
    {
        $ilCtrl = $this->ctrl;
        
        $ilCtrl->redirect($this, "listTerms");
    }
    
    /**
     * Show taxonomy
     * @throws ilCtrlException
     */
    public function showTaxonomy()
    {
        global $DIC;

        $tpl = $this->tpl;
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
                        $ctrl->getCurrentClassPath()
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
                if (!$tax_exp->handleCommand()) {
                    //$tpl->setLeftNavContent($tax_exp->getHTML());
                    //$tpl->setLeftContent($tax_exp->getHTML()."&nbsp;");
                }
                return;
            }
        }
    }
}
