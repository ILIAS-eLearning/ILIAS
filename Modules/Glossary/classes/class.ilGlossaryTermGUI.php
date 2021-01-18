<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * GUI class for glossary terms
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @ilCtrl_Calls ilGlossaryTermGUI: ilTermDefinitionEditorGUI, ilGlossaryDefPageGUI, ilPropertyFormGUI
 * @ilCtrl_Calls ilGlossaryTermGUI: ilObjectMetaDataGUI
 */
class ilGlossaryTermGUI
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
     * @var ilHelpGUI
     */
    protected $help;

    /**
     * @var \ILIAS\COPage\PageLinker
     */
    protected $page_linker;

    public $lng;
    public $tpl;
    public $glossary;
    public $term;
    public $link_xml;

    /**
     * @var ilLogger
     */
    protected $log;

    /**
     * @var ilObjGlossary|null
     */
    protected $term_glossary = null;

    /**
    * Constructor
    * @access	public
    */
    public function __construct($a_id = 0)
    {
        global $DIC;

        $this->help = $DIC["ilHelp"];
        $lng = $DIC->language();
        $tpl = $DIC["tpl"];
        $ilCtrl = $DIC->ctrl();
        $ilTabs = $DIC->tabs();

        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->ctrl->saveParameter($this, array("term_id"));
        $this->tabs_gui = $ilTabs;

        $this->log = ilLoggerFactory::getLogger('glo');

        $this->ref_id = $_GET["ref_id"];

        if ($a_id != 0) {
            $this->term = new ilGlossaryTerm($a_id);
            if (ilObject::_lookupObjectId($this->ref_id) == ilGlossaryTerm::_lookGlossaryID($a_id)) {
                $this->term_glossary = new ilObjGlossary($this->ref_id, true);
            } else {
                $this->term_glossary = new ilObjGlossary(ilGlossaryTerm::_lookGlossaryID($a_id), false);
            }
        }
    }

    /**
    * execute command
    */
    public function executeCommand()
    {
        $ilTabs = $this->tabs_gui;
        
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->log->debug("glossary term, next class " . $next_class . ", cmd: " . $cmd);

        switch ($next_class) {
            case "iltermdefinitioneditorgui":
                //$this->ctrl->setReturn($this, "listDefinitions");
                $def_edit = new ilTermDefinitionEditorGUI();
                //$ret = $def_edit->executeCommand();
                $ret = $this->ctrl->forwardCommand($def_edit);
                $this->quickList("edit", $def_edit);
                break;

            case "ilpropertyformgui":
                $form = $this->getEditTermForm();
                $this->ctrl->forwardCommand($form);
                break;
            
            case "ilobjectmetadatagui":
                $this->setTabs();
                $ilTabs->activateTab('meta_data');
                $md_gui = new ilObjectMetaDataGUI(
                    $this->term_glossary,
                    'term',
                    $this->term->getId()
                );
                $this->ctrl->forwardCommand($md_gui);
                $this->quickList();
                break;
                
            default:
                $ret = $this->$cmd();
                break;
        }
    }

    /**
     * set offline directory to offdir
     *
     * @param offdir contains diretory where to store files
     */
    public function setOfflineDirectory($offdir)
    {
        $this->offline_directory = $offdir;
    }


    /**
     * get offline directory
     * @return directory where to store offline files
     */
    public function getOfflineDirectory()
    {
        return $this->offline_directory;
    }


    public function setGlossary($a_glossary)
    {
        $this->glossary = $a_glossary;
        if (!is_object($this->term_glossary)) {
            $this->term_glossary = $a_glossary;
        }
    }

    public function setPageLinker($page_linker)
    {
        $this->page_linker = $page_linker;
    }

    /**
    * form for new content object creation
    */
    public function create()
    {
        // deprecated
    }

    /**
    * save term
    */
    public function saveTerm()
    {
        // deprecated
    }


    /**
     * Edit term
     */
    public function editTerm(ilPropertyFormGUI $a_form = null)
    {
        $ilTabs = $this->tabs_gui;
        $ilCtrl = $this->ctrl;

        //		$this->getTemplate();
        $this->displayLocator();
        $this->setTabs();
        $ilTabs->activateTab("properties");
        
        $this->tpl->setTitle($this->lng->txt("cont_term") . ": " . $this->term->getTerm());
        $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_glo.svg"));

        if (!$a_form) {
            $a_form = $this->getEditTermForm();
        }
        
        $this->tpl->setContent($ilCtrl->getHTML($a_form));

        $this->quickList();
    }
    
    /**
     * Get edit term form
     *
     * @param
     * @return
     */
    public function getEditTermForm()
    {
        $ilTabs = $this->tabs_gui;
        $ilCtrl = $this->ctrl;

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "updateTerm"));
        $form->setTitle($this->lng->txt("cont_edit_term"));
        
        $term = new ilTextInputGUI($this->lng->txt("cont_term"), "term");
        $term->setRequired(true);
        $term->setValue($this->term->getTerm());
        $form->addItem($term);
        
        $lang = new ilSelectInputGUI($this->lng->txt("language"), "term_language");
        $lang->setRequired(true);
        $lang->setOptions(ilMDLanguageItem::_getLanguages());
        $lang->setValue($this->term->getLanguage());
        $form->addItem($lang);
        
        // taxonomy
        if ($this->term_glossary->getTaxonomyId() > 0) {
            $tax_node_assign = new ilTaxSelectInputGUI($this->term_glossary->getTaxonomyId(), "tax_node", true);

            $ta = new ilTaxNodeAssignment("glo", $this->term_glossary->getId(), "term", $this->term_glossary->getTaxonomyId());
            $assgnmts = $ta->getAssignmentsOfItem($this->term->getId());
            $node_ids = array();
            foreach ($assgnmts as $a) {
                $node_ids[] = $a["node_id"];
            }
            $tax_node_assign->setValue($node_ids);
            
            $form->addItem($tax_node_assign);
        }

        // advanced metadata
        $this->record_gui = new ilAdvancedMDRecordGUI(
            ilAdvancedMDRecordGUI::MODE_EDITOR,
            'glo',
            $this->term_glossary->getId(),
            'term',
            $this->term->getId()
        );
        $this->record_gui->setPropertyForm($form);
        $this->record_gui->parse();
        
        $form->addCommandButton("updateTerm", $this->lng->txt("save"));

        return $form;
    }
    


    /**
    * update term
    */
    public function updateTerm()
    {
        $form = $this->getEditTermForm();
        if ($form->checkInput() &&
            $this->record_gui->importEditFormPostValues()) {
            // update term
            $this->term->setTerm(ilUtil::stripSlashes($_POST["term"]));
            $this->term->setLanguage($_POST["term_language"]);
            $this->term->update();

            // update taxonomy assignment
            if ($this->term_glossary->getTaxonomyId() > 0) {
                $ta = new ilTaxNodeAssignment("glo", $this->term_glossary->getId(), "term", $this->term_glossary->getTaxonomyId());
                $ta->deleteAssignmentsOfItem($this->term->getId());
                if (is_array($_POST["tax_node"])) {
                    foreach ($_POST["tax_node"] as $node_id) {
                        $ta->addAssignment($node_id, $this->term->getId());
                    }
                }
            }

            $this->record_gui->writeEditForm();
            ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
            $this->ctrl->redirect($this, "editTerm");
        }
            
        $form->setValuesByPost();
        $this->editTerm($form);
    }

    /**
     * Get overlay html
     *
     * @param
     * @return
     */
    public function getOverlayHTML($a_close_el_id, $a_glo_ov_id = "", $a_lang = "", $a_outputmode = "offline")
    {
        $lng = $this->lng;
        
        if ($a_lang == "") {
            $a_lang = $lng->getLangKey();
        }

        $tpl = new ilTemplate("tpl.glossary_overlay.html", true, true, "Modules/Glossary");
        //		$this->output(true, $tpl);
        if ($a_outputmode == "preview") {
            $a_outputmode = "presentation";
        }
        if ($a_outputmode == "offline") {
            $this->output(true, $tpl, $a_outputmode);
        } else {
            $this->output(false, $tpl, $a_outputmode);
        }
        if ($a_glo_ov_id != "") {
            $tpl->setCurrentBlock("glovlink");
            $tpl->setVariable("TXT_LINK", $lng->txtlng("content", "cont_sco_glossary", $a_lang));
            $tpl->setVariable("ID_LINK", $a_glo_ov_id);
            $tpl->parseCurrentBlock();
        }
        $tpl->setVariable("TXT_CLOSE", $lng->txtlng("common", "close", $a_lang));
        $tpl->setVariable("ID_CLOSE", $a_close_el_id);
        return $tpl->get();
    }
    
    /**
     * output glossary term definitions
     *
     * used in ilLMPresentationGUI->ilGlossary()
     */
    public function output($a_offline = false, $a_tpl = "", $a_outputmode = "presentation")
    {
        if ($a_tpl != "") {
            $tpl = $a_tpl;
        } else {
            $tpl = $this->tpl;
        }

        $defs = ilGlossaryDefinition::getDefinitionList($this->term->getId());

        $tpl->setVariable("TXT_TERM", $this->term->getTerm());

        for ($j = 0; $j < count($defs); $j++) {
            $def = $defs[$j];
            $page_gui = new ilGlossaryDefPageGUI($def["id"]);
            $page_gui->setSourcecodeDownloadScript("ilias.php?baseClass=ilGlossaryPresentationGUI&amp;ref_id=" . $_GET["ref_id"]);
            if (!$a_offline) {
                //$page_gui->setFullscreenLink(
                //	"ilias.php?baseClass=ilGlossaryPresentationGUI&cmd=fullscreen&ref_id=".$_GET["ref_id"]);
            } else {
                $page_gui->setFullscreenLink("fullscreen.html");	// id is set by xslt
            }
            $page_gui->setFileDownloadLink("ilias.php?baseClass=ilGlossaryPresentationGUI&amp;cmd=downloadFile&amp;ref_id=" . $_GET["ref_id"]);

            if (!$a_offline) {
                $page_gui->setOutputMode($a_outputmode);
            } else {
                $page_gui->setOutputMode("offline");
                $page_gui->setOfflineDirectory($this->getOfflineDirectory());
            }

            //$page_gui->setOutputMode("edit");
            //$page_gui->setPresentationTitle($this->term->getTerm());
            $page_gui->setPageLinker($this->page_linker);
            $page_gui->setTemplateOutput(false);
            $output = $page_gui->presentation($page_gui->getOutputMode());

            if (count($defs) > 1) {
                $tpl->setCurrentBlock("definition_header");
                $tpl->setVariable(
                    "TXT_DEFINITION",
                    $this->lng->txt("cont_definition") . " " . ($j + 1)
                        );
                $tpl->parseCurrentBlock();
            }

            ilMathJax::getInstance()->includeMathJax($tpl);

            $tpl->setCurrentBlock("definition");
            $tpl->setVariable("PAGE_CONTENT", $output);
            $tpl->parseCurrentBlock();
        }
    }

    /**
    * get internal links
    */
    public function getInternalLinks()
    {
        $defs = ilGlossaryDefinition::getDefinitionList($this->term->getId());

        $term_links = array();
        for ($j = 0; $j < count($defs); $j++) {
            $def = $defs[$j];
            $page = new ilGlossaryDefPage($def["id"]);
            $page->buildDom();
            $page_links = $page->getInternalLinks();
            foreach ($page_links as $key => $page_link) {
                $term_links[$key] = $page_link;
            }
        }

        return $term_links;
    }

    /**
    * list definitions
    */
    public function listDefinitions()
    {
        $ilTabs = $this->tabs_gui;
        
        //		$this->getTemplate();
        $this->displayLocator();
        $this->setTabs();
        $ilTabs->activateTab("definitions");

        // content style
        $this->tpl->addCss(ilObjStyleSheet::getContentStylePath($this->term_glossary->getStyleSheetId()));
        $this->tpl->addCss(ilObjStyleSheet::getSyntaxStylePath());


        // load template for table
        $tpl = new ilTemplate("tpl.glossary_definition_list.html", true, true, "Modules/Glossary");

        $this->tpl->setTitle(
            $this->lng->txt("cont_term") . ": " . $this->term->getTerm()
        );
        $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_glo.svg"));

        $tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

        $tpl->setCurrentBlock("add_def");
        $tpl->setVariable(
            "TXT_ADD_DEFINITION",
            $this->lng->txt("cont_add_definition")
        );
        $tpl->setVariable("BTN_ADD", "addDefinition");
        $tpl->parseCurrentBlock();
//        $tpl->setCurrentBlock("def_list");

        $defs = ilGlossaryDefinition::getDefinitionList($_GET["term_id"]);

        $tpl->setVariable("TXT_TERM", $this->term->getTerm());

        for ($j = 0; $j < count($defs); $j++) {
            $def = $defs[$j];
            $page_gui = new ilGlossaryDefPageGUI($def["id"]);
            $page_gui->setStyleId($this->term_glossary->getStyleSheetId());
            $page_gui->setSourcecodeDownloadScript("ilias.php?baseClass=ilGlossaryPresentationGUI&amp;ref_id=" . $_GET["ref_id"]);
            $page_gui->setTemplateOutput(false);
            $output = $page_gui->preview();

            if (count($defs) > 1) {
                $tpl->setCurrentBlock("definition_header");
                $tpl->setVariable(
                    "TXT_DEFINITION",
                    $this->lng->txt("cont_definition") . " " . ($j + 1)
                        );
                $tpl->parseCurrentBlock();
            }

            if ($j > 0) {
                $tpl->setCurrentBlock("up");
                $tpl->setVariable("TXT_UP", $this->lng->txt("up"));
                $this->ctrl->setParameter($this, "def", $def["id"]);
                $tpl->setVariable(
                    "LINK_UP",
                    $this->ctrl->getLinkTarget($this, "moveUp")
                );
                $tpl->parseCurrentBlock();
            }

            if ($j + 1 < count($defs)) {
                $tpl->setCurrentBlock("down");
                $tpl->setVariable("TXT_DOWN", $this->lng->txt("down"));
                $this->ctrl->setParameter($this, "def", $def["id"]);
                $tpl->setVariable(
                    "LINK_DOWN",
                    $this->ctrl->getLinkTarget($this, "moveDown")
                );
                $tpl->parseCurrentBlock();
            }
            $tpl->setCurrentBlock("submit_btns");
            $tpl->setVariable("TXT_EDIT", $this->lng->txt("edit"));
            $this->ctrl->setParameter($this, "def", $def["id"]);
            $this->ctrl->setParameterByClass("ilTermDefinitionEditorGUI", "def", $def["id"]);
            $tpl->setVariable(
                "LINK_EDIT",
                $this->ctrl->getLinkTargetByClass(array("ilTermDefinitionEditorGUI", "ilGlossaryDefPageGUI"), "edit")
            );
            $tpl->setVariable("TXT_DELETE", $this->lng->txt("delete"));
            $tpl->setVariable(
                "LINK_DELETE",
                $this->ctrl->getLinkTarget($this, "confirmDefinitionDeletion")
            );
            $tpl->parseCurrentBlock();

            $tpl->setCurrentBlock("definition");
            $tpl->setVariable("PAGE_CONTENT", $output);
            $tpl->parseCurrentBlock();
        }

        $this->tpl->setContent($tpl->get());

        //$this->tpl->setCurrentBlock("def_list");
        //$this->tpl->parseCurrentBlock();

        $this->quickList();
    }


    /**
    * deletion confirmation screen
    */
    public function confirmDefinitionDeletion()
    {
        $ilTabs = $this->tabs_gui;

        //$this->getTemplate();
        $this->displayLocator();
        $this->setTabs();
        $ilTabs->activateTab("definitions");

        $this->tpl->addCss(ilObjStyleSheet::getContentStylePath($this->term_glossary->getStyleSheetId()));
        $this->tpl->addCss(ilObjStyleSheet::getSyntaxStylePath());

        $this->tpl->setTitle(
            $this->lng->txt("cont_term") . ": " . $this->term->getTerm()
        );
        $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_glo.svg"));

        $dtpl =  new ilTemplate("tpl.glossary_definition_delete.html", true, true, "Modules/Glossary");
        ilUtil::sendQuestion($this->lng->txt("info_delete_sure"));

        $this->tpl->setVariable("TXT_TERM", $this->term->getTerm());

        $definition = new ilGlossaryDefinition($_GET["def"]);
        $page_gui = new ilGlossaryDefPageGUI($definition->getId());
        $page_gui->setTemplateOutput(false);
        $page_gui->setStyleId($this->term_glossary->getStyleSheetId());
        $page_gui->setSourcecodeDownloadScript("ilias.php?baseClass=ilGlossaryPresentationGUI&amp;ref_id=" . $_GET["ref_id"]);
        $page_gui->setFileDownloadLink("ilias.php?baseClass=ilGlossaryPresentationGUI&amp;ref_id=" . $_GET["ref_id"]);
        $page_gui->setFullscreenLink("ilias.php?baseClass=ilGlossaryPresentationGUI&amp;ref_id=" . $_GET["ref_id"]);
        $output = $page_gui->preview();

        $dtpl->setCurrentBlock("definition");
        $dtpl->setVariable("PAGE_CONTENT", $output);
        $dtpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
        $dtpl->setVariable(
            "LINK_CANCEL",
            $this->ctrl->getLinkTarget($this, "cancelDefinitionDeletion")
        );
        $dtpl->setVariable("TXT_CONFIRM", $this->lng->txt("confirm"));
        $this->ctrl->setParameter($this, "def", $definition->getId());
        $dtpl->setVariable(
            "LINK_CONFIRM",
            $this->ctrl->getLinkTarget($this, "deleteDefinition")
        );
        $dtpl->parseCurrentBlock();

        $this->tpl->setContent($dtpl->get());
    }

    public function cancelDefinitionDeletion()
    {
        $this->ctrl->redirect($this, "listDefinitions");
    }


    public function deleteDefinition()
    {
        $definition = new ilGlossaryDefinition($_GET["def"]);
        $definition->delete();
        $this->ctrl->redirect($this, "listDefinitions");
    }


    /**
    * move definition upwards
    */
    public function moveUp()
    {
        $definition = new ilGlossaryDefinition($_GET["def"]);
        $definition->moveUp();
        $this->ctrl->redirect($this, "listDefinitions");
    }


    /**
    * move definition downwards
    */
    public function moveDown()
    {
        $definition = new ilGlossaryDefinition($_GET["def"]);
        $definition->moveDown();
        $this->ctrl->redirect($this, "listDefinitions");
    }


    /**
    * add definition
    */
    public function addDefinition()
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameterByClass("ilobjglossarygui", "term_id", $this->term->getId());
        $ilCtrl->redirectByClass("ilobjglossarygui", "addDefinition");
    }

    /**
    * cancel adding definition
    */
    public function cancel()
    {
        $this->ctrl->redirect($this, "listDefinitions");
    }

    /**
    * save definition
    */
    public function saveDefinition()
    {
        $def = new ilGlossaryDefinition();
        $def->setTermId($_GET["term_id"]);
        $def->setTitle(ilUtil::stripSlashes($_POST["title"]));#"content object ".$newObj->getId());		// set by meta_gui->save
        $def->setDescription(ilUtil::stripSlashes($_POST["desc"]));	// set by meta_gui->save
        $def->create();

        $this->ctrl->redirect($this, "listDefinitions");
    }

    /**
    * output tabs
    */
    public function setTabs()
    {
        $this->getTabs();
    }

    /**
    * display locator
    */
    public function displayLocator()
    {
        $gloss_loc = new ilGlossaryLocatorGUI();
        $gloss_loc->setTerm($this->term);
        $gloss_loc->setGlossary($this->glossary);
        //$gloss_loc->setDefinition($this->definition);
        $gloss_loc->display();
    }


    /**
    * get tabs
    */
    public function getTabs()
    {
        $lng = $this->lng;
        $ilHelp = $this->help;
        
        
        $ilHelp->setScreenIdComponent("glo_term");
        
        //echo ":".$_GET["term_id"].":";
        if ($_GET["term_id"] != "") {
            $this->tabs_gui->addTab(
                "properties",
                $lng->txt("term"),
                $this->ctrl->getLinkTarget($this, "editTerm")
            );
            
            $this->tabs_gui->addTab(
                "definitions",
                $lng->txt("cont_definitions"),
                $this->ctrl->getLinkTarget($this, "listDefinitions")
            );

            $this->tabs_gui->addTab(
                "usage",
                $lng->txt("cont_usage") . " (" . ilGlossaryTerm::getNumberOfUsages($_GET["term_id"]) . ")",
                $this->ctrl->getLinkTarget($this, "listUsages")
            );

            $mdgui = new ilObjectMetaDataGUI(
                $this->term_glossary,
                "term",
                $this->term->getId()
            );
            $mdtab = $mdgui->getTab();
            if ($mdtab) {
                $this->tabs_gui->addTab(
                    "meta_data",
                    $lng->txt("meta_data"),
                    $mdtab
                );
            }

            $this->tabs_gui->addNonTabbedLink(
                "presentation_view",
                $this->lng->txt("glo_presentation_view"),
                ILIAS_HTTP_PATH .
                "/goto.php?target=" .
                "git" .
                "_" . $_GET["term_id"] . "_" . $_GET["ref_id"] . "&client_id=" . CLIENT_ID,
                "_top"
        );
        }

        // back to glossary
        $this->tabs_gui->setBackTarget(
            $this->lng->txt("glossary"),
            $this->ctrl->getLinkTargetByClass("ilobjglossarygui", "listTerms")
        );
    }

    /**
    * redirect script
    *
    * @param	string		$a_target
    */
    public static function _goto($a_target, $a_ref_id = "")
    {
        global $DIC;

        $rbacsystem = $DIC->rbac()->system();
        $ilErr = $DIC["ilErr"];
        $lng = $DIC->language();
        $ilAccess = $DIC->access();

        $glo_id = ilGlossaryTerm::_lookGlossaryID($a_target);//::_lookupContObjID($a_target);
        
        // get all references
        if ($a_ref_id > 0) {
            $ref_ids = array($a_ref_id);
        } else {
            $ref_ids = ilObject::_getAllReferences($glo_id);
        }

        // check read permissions
        foreach ($ref_ids as $ref_id) {
            // Permission check
            if ($ilAccess->checkAccess("read", "", $ref_id)) {
                $_GET["baseClass"] = "ilGlossaryPresentationGUI";
                $_GET["term_id"] = $a_target;
                $_GET["ref_id"] = $ref_id;
                $_GET["cmd"] = "listDefinitions";
                include_once("ilias.php");
                exit;
            }
        }
        if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
            ilUtil::sendFailure(sprintf(
                $lng->txt("msg_no_perm_read_item"),
                ilObject::_lookupTitle($glo_id)
            ), true);
            ilObjectGUI::_gotoRepositoryRoot();
        }


        $ilErr->raiseError($lng->txt("msg_no_perm_read_lm"), $ilErr->FATAL);
    }

    /**
     * List usage
     */
    public function listUsages()
    {
        $ilTabs = $this->tabs_gui;
        $tpl = $this->tpl;

        //$this->displayLocator();
        //		$this->getTemplate();
        $this->displayLocator();
        $this->setTabs();
        $ilTabs->activateTab("usage");
        
        $this->tpl->setTitle($this->lng->txt("cont_term") . ": " . $this->term->getTerm());
        $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_glo.svg"));

        $tab = new ilTermUsagesTableGUI($this, "listUsages", $_GET["term_id"]);
        
        $tpl->setContent($tab->getHTML());
        
        $this->quickList();
    }
    
    /**
     * Set quick term list cmd into left navigation URL
     */
    public function quickList()
    {
        $tpl = $this->tpl;

        $tab = new ilTermQuickListTableGUI($this, "editTerm");
        $tpl->setLeftNavContent($tab->getHTML());
    }
}
