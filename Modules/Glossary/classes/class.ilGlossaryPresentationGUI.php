<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/Object/classes/class.ilObjectGUI.php");
require_once("./Modules/Glossary/classes/class.ilObjGlossary.php");
require_once("./Modules/Glossary/classes/class.ilGlossaryTermGUI.php");
require_once("./Modules/Glossary/classes/class.ilGlossaryDefinition.php");
require_once("./Modules/Glossary/classes/class.ilTermDefinitionEditorGUI.php");
require_once("./Services/COPage/classes/class.ilPCParagraph.php");

/**
* Class ilGlossaryPresentationGUI
*
* GUI class for glossary presentation
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilGlossaryPresentationGUI: ilNoteGUI, ilInfoScreenGUI, ilPresentationListTableGUI
*
* @ingroup ModulesGlossary
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
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilHelpGUI
     */
    protected $help;

    public $admin_tabs;
    public $glossary;
    public $tpl;
    public $lng;

    /**
    * Constructor
    * @access	public
    */
    public function __construct()
    {
        global $DIC;

        $this->access = $DIC->access();
        $this->error = $DIC["ilErr"];
        $this->nav_history = $DIC["ilNavigationHistory"];
        $this->toolbar = $DIC->toolbar();
        $this->user = $DIC->user();
        $this->help = $DIC["ilHelp"];
        $lng = $DIC->language();
        $tpl = $DIC["tpl"];
        $ilCtrl = $DIC->ctrl();
        $ilTabs = $DIC->tabs();

        $this->tabs_gui = $ilTabs;
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->offline = false;
        $this->ctrl->saveParameter($this, array("ref_id", "letter", "tax_node"));

        // Todo: check lm id
        include_once("./Modules/Glossary/classes/class.ilObjGlossaryGUI.php");
        $this->glossary_gui = new ilObjGlossaryGUI("", $_GET["ref_id"], true, "");
        $this->glossary = $this->glossary_gui->object;

        // determine term id and check whether it is valid (belongs to
        // current glossary or a virtual (online) sub-glossary)
        $this->term_id = (int) $_GET["term_id"];
        $glo_ids = $this->glossary->getAllGlossaryIds();
        if (!is_array($glo_ids)) {
            $glo_ids = array($glo_ids);
        }
        $term_glo_id = ilGlossaryTerm::_lookGlossaryID($this->term_id);
        include_once("./Modules/Glossary/classes/class.ilGlossaryTermReferences.php");
        if (!in_array($term_glo_id, $glo_ids) && !ilGlossaryTermReferences::isReferenced($glo_ids, $this->term_id)) {
            if ((int) $this->term_id > 0) {
                include_once("./Modules/Glossary/exceptions/class.ilGlossaryException.php");
                throw new ilGlossaryException("Term ID does not match the glossary.");
            }
            $this->term_id = "";
        }
        
        $this->tax_node = 0;
        $this->tax_id = $this->glossary->getTaxonomyId();
        if ($this->tax_id > 0 && $this->glossary->getShowTaxonomy()) {
            include_once("./Services/Taxonomy/classes/class.ilObjTaxonomy.php");
            $this->tax = new ilObjTaxonomy($this->tax_id);
        }
        if ((int) $_GET["tax_node"] > 1 && $this->tax->getTree()->readRootId() != $_GET["tax_node"]) {
            $this->tax_node = (int) $_GET["tax_node"];
        }
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
        if (!$ilAccess->checkAccess("read", "", $_GET["ref_id"]) &&
            !($ilAccess->checkAccess("visible", "", $_GET["ref_id"]) &&
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

            default:
                $ret = $this->$cmd();
                break;
        }
        $this->tpl->show();
    }

    public function prepareOutput()
    {
        $this->tpl->getStandardTemplate();
        $title = $this->glossary->getTitle();

        $this->tpl->setTitle($title);
        $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_glo.svg"));

        $this->setLocator();
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

        
        if (!$ilAccess->checkAccess("read", "", $_GET["ref_id"])) {
            $ilErr->raiseError($lng->txt("permission_denied"), $ilErr->MESSAGE);
        }
        
        if (!$this->offlineMode()) {
            $ilNavigationHistory->addItem(
                $_GET["ref_id"],
                $this->ctrl->getLinkTarget($this, "listTerms"),
                "glo"
            );
            
            // alphabetical navigation
            include_once("./Services/Form/classes/class.ilAlphabetInputGUI.php");
            $ai = new ilAlphabetInputGUI($lng->txt("glo_quick_navigation"), "first");

            $ai->setFixDBUmlauts(true);

            $first_letters = $this->glossary->getFirstLetters($this->tax_node);
            if (!is_array($first_letters)) {
                $first_letters = [];
            }
            if (!in_array($_GET["letter"], $first_letters)) {
                $first_letters[] = ilUtil::stripSlashes($_GET["letter"]);
            }
            $ai->setLetters($first_letters);

            $ai->setParentCommand($this, "chooseLetter");
            $ai->setHighlighted($_GET["letter"]);
            $ilToolbar->addInputItem($ai, true);
        }
        
        $ret = $this->listTermByGiven();
        $ilCtrl->setParameter($this, "term_id", "");
        
        $ilTabs->activateTab("terms");
        
        // show taxonomy
        $this->showTaxonomy();
        
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
        
        if (!$ilAccess->checkAccess("read", "", $_GET["ref_id"])) {
            $ilErr->raiseError($lng->txt("permission_denied"), $ilErr->MESSAGE);
        }

        $this->lng->loadLanguageModule("meta");

        $this->setTabs();
        
        // load template for table
        //		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");

        $oldoffset = (is_numeric($_GET["oldoffset"]))?$_GET["oldoffset"]:$_GET["offset"];

        if ($this->glossary->getPresentationMode() == "full_def") {
            // content style
            $this->tpl->setCurrentBlock("ContentStyle");
            if (!$this->offlineMode()) {
                $this->tpl->setVariable(
                    "LOCATION_CONTENT_STYLESHEET",
                    ilObjStyleSheet::getContentStylePath($this->glossary->getStyleSheetId())
                );
            } else {
                $this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET", "content.css");
            }
            $this->tpl->parseCurrentBlock();

            // syntax style
            $this->tpl->setCurrentBlock("SyntaxStyle");
            if (!$this->offlineMode()) {
                $this->tpl->setVariable(
                    "LOCATION_SYNTAX_STYLESHEET",
                    ilObjStyleSheet::getSyntaxStylePath()
                );
            } else {
                $this->tpl->setVariable(
                    "LOCATION_SYNTAX_STYLESHEET",
                    "syntaxhighlight.css"
                );
            }
            $this->tpl->parseCurrentBlock();
        }

        $table = $this->getPresentationTable();

        if (!$this->offlineMode()) {
            //			$tpl->setContent($table->getHTML());
            $tpl->setContent($ilCtrl->getHTML($table));
        } else {
            $this->tpl->setVariable("ADM_CONTENT", $table->getHTML());
            return $this->tpl->get();
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
        include_once("./Modules/Glossary/classes/class.ilPresentationListTableGUI.php");
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
    public function listDefinitions($a_ref_id = 0, $a_term_id = 0, $a_get_html = false, $a_page_mode = IL_PAGE_PRESENTATION)
    {
        $ilUser = $this->user;
        $ilAccess = $this->access;
        $lng = $this->lng;
        $ilErr = $this->error;

        if ($a_ref_id == 0) {
            $ref_id = (int) $_GET["ref_id"];
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
        if ($this->glossary->getPresentationMode() != "full_def") {
            $this->showDefinitionTabs("term_content");
        }

        $term = new ilGlossaryTerm($term_id);
        
        if (!$a_get_html) {
            $tpl = $this->tpl;

            require_once("./Modules/Glossary/classes/class.ilGlossaryDefPageGUI.php");
            $tpl->getStandardTemplate();
            //			$this->setTabs();

            if ($this->offlineMode()) {
                $style_name = $ilUser->prefs["style"] . ".css";
                ;
                $tpl->setVariable("LOCATION_STYLESHEET", "./" . $style_name);
            } else {
                $this->setLocator();
            }

            // content style
            $tpl->setCurrentBlock("ContentStyle");
            if (!$this->offlineMode()) {
                $tpl->setVariable(
                    "LOCATION_CONTENT_STYLESHEET",
                    ilObjStyleSheet::getContentStylePath($this->glossary->getStyleSheetId())
                );
            } else {
                $tpl->setVariable("LOCATION_CONTENT_STYLESHEET", "content.css");
            }
            $tpl->parseCurrentBlock();

            // syntax style
            $tpl->setCurrentBlock("SyntaxStyle");
            if (!$this->offlineMode()) {
                $tpl->setVariable(
                    "LOCATION_SYNTAX_STYLESHEET",
                    ilObjStyleSheet::getSyntaxStylePath()
                );
            } else {
                $tpl->setVariable(
                    "LOCATION_SYNTAX_STYLESHEET",
                    "syntaxhighlight.css"
                );
            }
            $tpl->parseCurrentBlock();

            $tpl->setTitleIcon(ilUtil::getImagePath("icon_glo.svg"));
            $tpl->setTitle($this->lng->txt("cont_term") . ": " . $term->getTerm());
            
            // advmd block
            $cmd = null;
            if ($ilAccess->checkAccess("write", "", $_GET["ref_id"])) {
                $cmd = array("edit" => $this->ctrl->getLinkTargetByClass(array("ilglossaryeditorgui", "ilobjglossarygui", "ilglossarytermgui", "ilobjectmetadatagui"), ""));
            }
            include_once "Services/Object/classes/class.ilObjectMetaDataGUI.php";
            $mdgui = new ilObjectMetaDataGUI($this->glossary, "term", $term->getId());
            $tpl->setRightContent($mdgui->getBlockHTML($cmd));

            // load template for table
            $tpl->addBlockfile("ADM_CONTENT", "def_list", "tpl.glossary_definition_list.html", "Modules/Glossary");
        } else {
            $tpl = new ilTemplate("tpl.glossary_definition_list.html", true, true, "Modules/Glossary");
        }

        $defs = ilGlossaryDefinition::getDefinitionList($term_id);
        $tpl->setVariable("TXT_TERM", $term->getTerm());
        $this->mobs = array();

        // toc
        if (count($defs) > 1 && $a_page_mode == IL_PAGE_PRESENTATION) {
            $tpl->setCurrentBlock("toc");
            for ($j = 1; $j <= count($defs); $j++) {
                $tpl->setCurrentBlock("toc_item");
                $tpl->setVariable("TOC_DEF_NR", $j);
                $tpl->setVariable("TOC_DEF", $lng->txt("cont_definition"));
                $tpl->parseCurrentBlock();
            }
            $tpl->setCurrentBlock("toc");
            $tpl->parseCurrentBlock();
        }

        for ($j = 0; $j < count($defs); $j++) {
            $def = $defs[$j];
            $page_gui = new ilGlossaryDefPageGUI($def["id"]);
            $page_gui->setGlossary($this->glossary);
            $page_gui->setOutputMode($a_page_mode);
            $page_gui->setStyleId($this->glossary->getStyleSheetId());
            $page = $page_gui->getPageObject();

            // internal links
            $page->buildDom();
            $int_links = $page->getInternalLinks();
            $link_xml = $this->getLinkXML($int_links);
            $page_gui->setLinkXML($link_xml);

            if ($this->offlineMode()) {
                $page_gui->setOutputMode("offline");
                $page_gui->setOfflineDirectory($this->getOfflineDirectory());
            }
            $page_gui->setSourcecodeDownloadScript($this->getLink($ref_id));
            $page_gui->setFullscreenLink($this->getLink($ref_id, "fullscreen", $term_id, $def["id"]));

            $page_gui->setTemplateOutput(false);
            $page_gui->setRawPageContent(true);
            $page_gui->setFileDownloadLink($this->getLink($ref_id, "downloadFile"));
            if (!$this->offlineMode()) {
                $output = $page_gui->showPage();
            } else {
                $output = $page_gui->presentation($page_gui->getOutputMode());
            }

            if (count($defs) > 1) {
                $tpl->setCurrentBlock("definition_header");
                $tpl->setVariable(
                    "TXT_DEFINITION",
                    $this->lng->txt("cont_definition") . " " . ($j + 1)
                );
                $tpl->setVariable("DEF_NR", ($j + 1));
                $tpl->parseCurrentBlock();
            }
            
            $tpl->setCurrentBlock("definition");
            $tpl->setVariable("PAGE_CONTENT", $output);
            $tpl->parseCurrentBlock();
        }
        
        // display possible backlinks
        $sources = ilInternalLink::_getSourcesOfTarget('git', $_GET['term_id'], 0);
        
        if ($sources) {
            $backlist_shown = false;
            foreach ($sources as $src) {
                $type = explode(':', $src['type']);
                
                if ($type[0] == 'lm') {
                    if ($type[1] == 'pg') {
                        $title = ilLMPageObject::_getPresentationTitle($src['id']);
                        $lm_id = ilLMObject::_lookupContObjID($src['id']);
                        $lm_title = ilObject::_lookupTitle($lm_id);
                        $tpl->setCurrentBlock('backlink_item');
                        $ref_ids = ilObject::_getAllReferences($lm_id);
                        $access = false;
                        foreach ($ref_ids as $rid) {
                            if ($ilAccess->checkAccess("read", "", $rid)) {
                                $access = true;
                            }
                        }
                        if ($access) {
                            $tpl->setCurrentBlock("backlink_item");
                            $tpl->setVariable("BACKLINK_LINK", ILIAS_HTTP_PATH . "/goto.php?target=" . $type[1] . "_" . $src['id']);
                            $tpl->setVariable("BACKLINK_ITEM", $lm_title . ": " . $title);
                            $tpl->parseCurrentBlock();
                            $backlist_shown = true;
                        }
                    }
                }
            }
            if ($backlist_shown) {
                $tpl->setCurrentBlock("backlink_list");
                $tpl->setVariable("BACKLINK_TITLE", $this->lng->txt('glo_term_used_in'));
                $tpl->parseCurrentBlock();
            }
        }

        if (!$a_get_html) {
            $tpl->setCurrentBlock("perma_link");
            $tpl->setVariable("PERMA_LINK", ILIAS_HTTP_PATH .
                "/goto.php?target=" .
                "git" .
                "_" . $term_id . "_" . $ref_id . "&client_id=" . CLIENT_ID);
            $tpl->setVariable("TXT_PERMA_LINK", $this->lng->txt("perma_link"));
            $tpl->setVariable("PERMA_TARGET", "_top");
            $tpl->parseCurrentBlock();

            // show taxonomy
            $this->showTaxonomy();
        }

        // highlighting?
        if ($_GET["srcstring"] != "" && !$this->offlineMode()) {
            include_once './Services/Search/classes/class.ilUserSearchCache.php';
            $cache = ilUserSearchCache::_getInstance($ilUser->getId());
            $cache->switchSearchType(ilUserSearchCache::LAST_QUERY);
            $search_string = $cache->getQuery();

            include_once("./Services/UIComponent/TextHighlighter/classes/class.ilTextHighlighterGUI.php");
            include_once("./Services/Search/classes/class.ilQueryParser.php");
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

        if ($this->offlineMode() || $a_get_html) {
            return $tpl->get();
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
            $this->ctrl->setParameter($this, "offset", $_GET["offset"]);
            if (!empty($_REQUEST["term"])) {
                $this->ctrl->setParameter($this, "term", $_REQUEST["term"]);
                $this->ctrl->setParameter($this, "oldoffset", $_GET["oldoffset"]);
                $back = $ilCtrl->getLinkTarget($this, "searchTerms");
            } else {
                $back = $ilCtrl->getLinkTarget($this, "listTerms");
            }
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
            include_once("./Modules/Glossary/classes/class.ilGlossaryTerm.php");
            if (ilGlossaryTerm::_lookGlossaryID($this->term_id) == $this->glossary->getId()) {
                $ilTabs->addNonTabbedLink(
                    "editing_view",
                    $lng->txt("glo_editing_view"),
                    $ilCtrl->getLinkTargetByClass(array("ilglossaryeditorgui", "ilobjglossarygui", "ilglossarytermgui"), "listDefinitions")
                );
                //"ilias.php?baseClass=ilGlossaryEditorGUI&amp;ref_id=".$_GET["ref_id"]."&amp;edit_term=".$this->term_id);
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
        $this->tpl = new ilTemplate("tpl.fullscreen.html", true, true, "Services/COPage");
        include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
        $this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
        $this->tpl->setVariable(
            "LOCATION_CONTENT_STYLESHEET",
            ilObjStyleSheet::getContentStylePath($this->glossary->getStyleSheetId())
        );

        //$int_links = $page_object->getInternalLinks();
        $med_links = ilMediaItem::_getMapAreasIntLinks($_GET["mob_id"]);

        // later
        //$link_xml = $this->getLinkXML($med_links, $this->getLayoutLinkTargets());

        $link_xlm = "";

        require_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
        $media_obj = new ilObjMediaObject($_GET["mob_id"]);

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
            $this->getLink($_GET["ref_id"], "fullscreen");
        $this->ctrl->clearParameters($this);

        $params = array('mode' => $mode, 'enlarge_path' => $enlarge_path,
            'link_params' => "ref_id=" . $_GET["ref_id"],'fullscreen_link' => $fullscreen_link,
            'ref_id' => $_GET["ref_id"], 'pg_frame' => $pg_frame, 'webspace_path' => $wb_path);
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

        if (!$ilAccess->checkAccess("read", "", $_GET["ref_id"])) {
            $ilErr->raiseError($lng->txt("permission_denied"), $ilErr->MESSAGE);
        }

        $this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.glo_download_list.html", "Modules/Glossary");

        $this->setTabs();
        $ilTabs->activateTab("download");
        
        // set title header
        $this->tpl->setTitle($this->glossary->getTitle());
        $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_glo.svg"));

        // create table
        require_once("./Services/Table/classes/class.ilTableGUI.php");
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
        $header_params = array("ref_id" => $_GET["ref_id"], "obj_id" => $_GET["obj_id"],
            "cmd" => "showDownloadList", "cmdClass" => strtolower(get_class($this)));
        $tbl->setHeaderVars($cols, $header_params);
        $tbl->setColumnWidth(array("10%", "30%", "20%", "20%","20%"));
        $tbl->disable("sort");

        // control
        $tbl->setOrderColumn($_GET["sort_by"]);
        $tbl->setOrderDirection($_GET["sort_order"]);
        $tbl->setLimit($_GET["limit"]);
        $tbl->setOffset($_GET["offset"]);
        $tbl->setMaxCount($this->maxcount);		// ???

        // $this->tpl->setVariable("COLUMN_COUNTS", 5);

        // footer
        //$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
        $tbl->disable("footer");

        $tbl->setMaxCount(count($export_files));
        $export_files = array_slice($export_files, $_GET["offset"], $_GET["limit"]);

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
        
        if (!$ilAccess->checkAccess("read", "", $_GET["ref_id"])) {
            $ilErr->raiseError($lng->txt("permission_denied"), $ilErr->error_obj->MESSAGE);
        }

        $file = $this->glossary->getPublicExportFile($_GET["type"]);
        if ($this->glossary->getPublicExportFile($_GET["type"]) != "") {
            $dir = $this->glossary->getExportDirectory($_GET["type"]);
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
        //$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html", "Services/Locator");
        require_once("./Modules/Glossary/classes/class.ilGlossaryLocatorGUI.php");
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
        
        if (!$ilAccess->checkAccess("read", "", $_GET["ref_id"])) {
            $ilErr->raiseError($lng->txt("permission_denied"), $ilErr->MESSAGE);
        }

        $file = explode("_", $_GET["file_id"]);
        include_once("./Modules/File/classes/class.ilObjFile.php");
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
    * get link targets
    */
    public function getLinkXML($a_int_links)
    {
        if ($a_layoutframes == "") {
            $a_layoutframes = array();
        }
        $link_info = "<IntLinkInfos>";
        foreach ($a_int_links as $int_link) {
            //echo "<br>+".$int_link["Type"]."+".$int_link["TargetFrame"]."+".$int_link["Target"]."+";
            $target = $int_link["Target"];
            if (substr($target, 0, 4) == "il__") {
                $target_arr = explode("_", $target);
                $target_id = $target_arr[count($target_arr) - 1];
                $type = $int_link["Type"];
                $targetframe = ($int_link["TargetFrame"] != "")
                    ? $int_link["TargetFrame"]
                    : "None";
                    
                // anchor
                $anc = $anc_add = "";
                if ($int_link["Anchor"] != "") {
                    $anc = $int_link["Anchor"];
                    $anc_add = "_" . rawurlencode($int_link["Anchor"]);
                }

                if ($targetframe == "New") {
                    $ltarget = "_blank";
                } else {
                    $ltarget = "";
                }
                $lcontent = "";
                switch ($type) {
                    case "PageObject":
                    case "StructureObject":
                        $lm_id = ilLMObject::_lookupContObjID($target_id);
                        $cont_obj = $this->content_object;
                        if ($type == "PageObject") {
                            $href = "./goto.php?target=pg_" . $target_id . $anc_add;
                        } else {
                            $href = "./goto.php?target=st_" . $target_id;
                        }
                        //$ltarget = "ilContObj".$lm_id;
                        break;

                    case "GlossaryItem":
                        if (ilGlossaryTerm::_lookGlossaryID($target_id) == $this->glossary->getId()) {
                            if ($this->offlineMode()) {
                                $href = "term_" . $target_id . ".html";
                            } else {
                                $this->ctrl->setParameter($this, "term_id", $target_id);
                                $href = $this->ctrl->getLinkTarget($this, "listDefinitions");
                                $href = str_replace("&", "&amp;", $href);
                            }
                        } else {
                            $href = "./goto.php?target=git_" . $target_id;
                        }
                        break;

                    case "MediaObject":
                        if ($this->offlineMode()) {
                            $href = "media_" . $target_id . ".html";
                        } else {
                            $this->ctrl->setParameter($this, "obj_type", $type);
                            $this->ctrl->setParameter($this, "mob_id", $target_id);
                            $href = $this->ctrl->getLinkTarget($this, "media");
                            $href = str_replace("&", "&amp;", $href);
                        }
                        break;

                    case "RepositoryItem":
                        $obj_type = ilObject::_lookupType($target_id, true);
                        $obj_id = ilObject::_lookupObjId($target_id);
                        $href = "./goto.php?target=" . $obj_type . "_" . $target_id;
                        $t_frame = ilFrameTargetInfo::_getFrame("MainContent", $obj_type);
                        $ltarget = $t_frame;
                        break;
                        
                    case "WikiPage":
                        include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
                        $href = ilWikiPage::getGotoForWikiPageTarget($target_id);
                        break;

                    case "User":
                        $obj_type = ilObject::_lookupType($target_id);
                        if ($obj_type == "usr") {
                            include_once("./Services/User/classes/class.ilUserUtil.php");
                            $back = $this->ctrl->getLinkTarget($this, "listDefinitions");
                            //var_dump($back); exit;
                            $this->ctrl->setParameterByClass("ilpublicuserprofilegui", "user_id", $target_id);
                            $this->ctrl->setParameterByClass(
                                "ilpublicuserprofilegui",
                                "back_url",
                                rawurlencode($back)
                            );
                            $href = "";
                            include_once("./Services/User/classes/class.ilUserUtil.php");
                            if (ilUserUtil::hasPublicProfile($target_id)) {
                                $href = $this->ctrl->getLinkTargetByClass("ilpublicuserprofilegui", "getHTML");
                            }
                            $this->ctrl->setParameterByClass("ilpublicuserprofilegui", "user_id", "");
                            $lcontent = ilUserUtil::getNamePresentation($target_id, false, false);
                        }
                        break;

                }
                
                $anc_par = 'Anchor="' . $anc . '"';
                
                $link_info .= "<IntLinkInfo Target=\"$target\" Type=\"$type\" " .
                    "TargetFrame=\"$targetframe\" LinkHref=\"$href\" LinkTarget=\"$ltarget\" LinkContent=\"$lcontent\" $anc_par/>";
                
                $this->ctrl->clearParameters($this);
            }
        }
        $link_info .= "</IntLinkInfos>";

        return $link_info;
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

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();
        $this->form->setTarget("print_view");
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
            include_once("./Services/Taxonomy/classes/class.ilTaxAssignInputGUI.php");
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

        include_once("./Services/Form/classes/class.ilNestedListInputGUI.php");
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

        if (!$ilAccess->checkAccess("read", "", $_GET["ref_id"])) {
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
                include_once("./Services/Taxonomy/classes/class.ilObjTaxonomy.php");
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

        $tpl = new ilTemplate("tpl.main.html", true, true);
        $tpl->setVariable("LOCATION_STYLESHEET", ilObjStyleSheet::getContentPrintStyle());
        
        /*
                // syntax style
                $this->tpl->setCurrentBlock("SyntaxStyle");
                $this->tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
                    ilObjStyleSheet::getSyntaxStylePath());
                $this->tpl->parseCurrentBlock();

                // content style
                $this->tpl->setCurrentBlock("ContentStyle");
                $this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
                    ilObjStyleSheet::getContentStylePath($this->glossary->getStyleSheetId()));
                $this->tpl->parseCurrentBlock();*/

        include_once("./Services/jQuery/classes/class.iljQueryUtil.php");
        iljQueryUtil::initjQuery($tpl);
        
        // determine target frames for internal links

        foreach ($terms as $t_id) {
            $page_content .= $this->listDefinitions($_GET["ref_id"], $t_id, true, IL_PAGE_PRINT);
        }
        $tpl->setVariable("CONTENT", $page_content .
        '<script type="text/javascript" language="javascript1.2">
		<!--
			il.Util.addOnLoad(function () {
				il.Util.print();
			});
		//-->
		</script>');
        $tpl->show(false);
        exit;
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
        
        $oldoffset = (is_numeric($_GET["oldoffset"]))?$_GET["oldoffset"]:$_GET["offset"];

        if (!$this->offlineMode()) {
            if ($this->ctrl->getCmd() != "listDefinitions") {
                if ($ilAccess->checkAccess("read", "", $_GET["ref_id"])) {
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

                $this->tabs_gui->addTab(
                    "print_view",
                    $lng->txt("cont_print_view"),
                    $ilCtrl->getLinkTarget($this, "printViewSelection")
                );

                // glossary menu
                if ($ilAccess->checkAccess("read", "", $_GET["ref_id"])) {
                    //if ($this->glossary->isActiveGlossaryMenu())
                    //{
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

                if ($ilAccess->checkAccess("write", "", (int) $_GET["ref_id"]) ||
                    $ilAccess->checkAccess("edit_content", "", (int) $_GET["ref_id"])) {
                    include_once("./Modules/Glossary/classes/class.ilGlossaryTerm.php");
                    $this->tabs_gui->addNonTabbedLink(
                        "editing_view",
                        $lng->txt("glo_editing_view"),
                        "ilias.php?baseClass=ilGlossaryEditorGUI&amp;ref_id=" . (int) $_GET["ref_id"],
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
    
    public function download_paragraph()
    {
        include_once("./Modules/Glossary/classes/class.ilGlossaryDefPage.php");
        $pg_obj = new ilGlossaryDefPage($_GET["pg_id"]);
        $pg_obj->send_paragraph($_GET["par_id"], $_GET["downloadtitle"]);
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
    * info screen call from inside learning module
    */
    /*
    function showInfoScreen()
    {
        $this->outputInfoScreen(true);
    }*/

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

        include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");

        $info = new ilInfoScreenGUI($this->glossary_gui);
        $info->enablePrivateNotes();
        //$info->enableLearningProgress();

        $info->enableNews();
        if ($ilAccess->checkAccess("write", "", $_GET["ref_id"])) {
            $info->enableNewsEditing();
            $news_set = new ilSetting("news");
            $enable_internal_rss = $news_set->get("enable_rss_for_internal");
            if ($enable_internal_rss) {
                $info->setBlockProperty("news", "settings", true);
            }
        }

        // add read / back button
        if ($ilAccess->checkAccess("read", "", $_GET["ref_id"])) {
            /*
            if ($_GET["obj_id"] > 0)
            {
                $this->ctrl->setParameter($this, "obj_id", $_GET["obj_id"]);
                $info->addButton($this->lng->txt("back"),
                    $this->ctrl->getLinkTarget($this, "layout"));
            }
            else
            {
                $info->addButton($this->lng->txt("view"),
                    $this->ctrl->getLinkTarget($this, "layout"));
            }*/
        }
        
        // show standard meta data section
        $info->addMetaDataSections($this->glossary->getId(), 0, $this->glossary->getType());
        
        include_once("./Modules/Glossary/classes/class.ilObjGlossaryGUI.php");
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
     *
     * @param
     * @return
     */
    public function showTaxonomy()
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        if (!$this->offlineMode() && $this->glossary->getShowTaxonomy()) {
            include_once("./Services/Taxonomy/classes/class.ilObjTaxonomy.php");
            $tax_ids = ilObjTaxonomy::getUsageOfObject($this->glossary->getId());
            if (count($tax_ids) > 0) {
                include_once("./Services/Taxonomy/classes/class.ilTaxonomyExplorerGUI.php");
                $tax_exp = new ilTaxonomyExplorerGUI(
                    $this,
                    "showTaxonomy",
                    $tax_ids[0],
                    "ilglossarypresentationgui",
                    "listTerms"
                );
                if (!$tax_exp->handleCommand()) {
                    //$tpl->setLeftNavContent($tax_exp->getHTML());
                    $tpl->setLeftContent($tax_exp->getHTML() . "&nbsp;");
                }
                return;
                
                
                include_once("./Services/Taxonomy/classes/class.ilObjTaxonomyGUI.php");
                $tpl->setLeftNavContent(ilObjTaxonomyGUI::getTreeHTML(
                    $tax_ids[0],
                    "ilglossarypresentationgui",
                    "listTerms",
                    $lng->txt("cont_all_topics")
                ));
            }
        }
    }
}
