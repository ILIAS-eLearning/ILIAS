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
 * GUI class for glossary terms
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilGlossaryTermGUI: ilTermDefinitionEditorGUI, ilGlossaryDefPageGUI, ilPropertyFormGUI
 * @ilCtrl_Calls ilGlossaryTermGUI: ilObjectMetaDataGUI
 */
class ilGlossaryTermGUI
{
    protected ilAdvancedMDRecordGUI $record_gui;
    protected int $ref_id;
    protected \ILIAS\Glossary\Editing\EditingGUIRequest $request;
    protected string $offline_directory;
    protected ilCtrl $ctrl;
    protected ilTabsGUI $tabs_gui;
    protected ilHelpGUI $help;
    protected \ILIAS\COPage\PageLinker $page_linker;
    public ilLanguage $lng;
    public ilGlobalTemplateInterface $tpl;
    public ilObjGlossary $glossary;
    public ilGlossaryTerm $term;
    protected ilLogger $log;
    protected ?ilObjGlossary $term_glossary = null;
    protected ilToolbarGUI $toolbar;
    protected \ILIAS\Style\Content\GUIService $content_style_gui;
    protected \ILIAS\Style\Content\DomainService $content_style_domain;

    public function __construct(
        int $a_id = 0
    ) {
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
        $this->request = $DIC->glossary()
            ->internal()
            ->gui()
            ->editing()
            ->request();

        $this->log = ilLoggerFactory::getLogger('glo');

        $this->toolbar = $DIC->toolbar();

        $this->ref_id = $this->request->getRefId();

        if ($a_id != 0) {
            $this->term = new ilGlossaryTerm($a_id);
            if (ilObject::_lookupObjectId($this->ref_id) == ilGlossaryTerm::_lookGlossaryID($a_id)) {
                $this->term_glossary = new ilObjGlossary($this->ref_id, true);
            } else {
                $this->term_glossary = new ilObjGlossary(ilGlossaryTerm::_lookGlossaryID($a_id), false);
            }
        }
        $cs = $DIC->contentStyle();
        $this->content_style_gui = $cs->gui();
        $this->content_style_domain = $cs->domain();
    }

    public function executeCommand() : void
    {
        $ilTabs = $this->tabs_gui;
        
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->log->debug("glossary term, next class " . $next_class . ", cmd: " . $cmd);

        switch ($next_class) {
            case "iltermdefinitioneditorgui":
                $def_edit = new ilTermDefinitionEditorGUI();
                $this->ctrl->forwardCommand($def_edit);
                $this->quickList();
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

    public function setOfflineDirectory(string $offdir) : void
    {
        $this->offline_directory = $offdir;
    }

    public function getOfflineDirectory() : string
    {
        return $this->offline_directory;
    }


    public function setGlossary(ilObjGlossary $a_glossary) : void
    {
        $this->glossary = $a_glossary;
        if (!is_object($this->term_glossary)) {
            $this->term_glossary = $a_glossary;
        }
    }

    public function setPageLinker(\ILIAS\COPage\PageLinker $page_linker) : void
    {
        $this->page_linker = $page_linker;
    }

    public function editTerm(
        ilPropertyFormGUI $a_form = null
    ) : void {
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
    
    public function getEditTermForm() : ilPropertyFormGUI
    {
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

    public function updateTerm() : void
    {
        $form = $this->getEditTermForm();
        if ($form->checkInput() &&
            $this->record_gui->importEditFormPostValues()) {
            // update term
            $this->term->setTerm($form->getInput("term"));
            $this->term->setLanguage($form->getInput("term_language"));
            $this->term->update();

            // update taxonomy assignment
            if ($this->term_glossary->getTaxonomyId() > 0) {
                $ta = new ilTaxNodeAssignment("glo", $this->term_glossary->getId(), "term", $this->term_glossary->getTaxonomyId());
                $ta->deleteAssignmentsOfItem($this->term->getId());
                foreach ($this->request->getTaxNodes() as $node_id) {
                    $ta->addAssignment($node_id, $this->term->getId());
                }
            }

            $this->record_gui->writeEditForm();
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
            $this->ctrl->redirect($this, "editTerm");
        }
            
        $form->setValuesByPost();
        $this->editTerm($form);
    }

    public function getOverlayHTML(
        string $a_close_el_id,
        string $a_glo_ov_id = "",
        string $a_lang = "",
        string $a_outputmode = "offline"
    ) : string {
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
     * used in ilLMPresentationGUI->ilGlossary()
     */
    public function output(
        bool $a_offline = false,
        ilGlobalTemplateInterface $a_tpl = null,
        string $a_outputmode = "presentation"
    ) : void {
        if ($a_tpl != null) {
            $tpl = $a_tpl;
        } else {
            $tpl = $this->tpl;
        }

        $defs = ilGlossaryDefinition::getDefinitionList($this->term->getId());

        $tpl->setVariable("TXT_TERM", $this->term->getTerm());

        for ($j = 0, $jMax = count($defs); $j < $jMax; $j++) {
            $def = $defs[$j];
            $page_gui = new ilGlossaryDefPageGUI($def["id"]);
            $page_gui->setSourcecodeDownloadScript("ilias.php?baseClass=ilGlossaryPresentationGUI&amp;ref_id=" . $this->ref_id);
            if ($a_offline) {
                $page_gui->setFullscreenLink("fullscreen.html");	// id is set by xslt
            }
            $page_gui->setFileDownloadLink("ilias.php?baseClass=ilGlossaryPresentationGUI&amp;cmd=downloadFile&amp;ref_id=" . $this->ref_id);

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

    public function getInternalLinks() : array
    {
        $defs = ilGlossaryDefinition::getDefinitionList($this->term->getId());

        $term_links = array();
        for ($j = 0, $jMax = count($defs); $j < $jMax; $j++) {
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

    public function listDefinitions() : void
    {
        $ilTabs = $this->tabs_gui;
        
        //		$this->getTemplate();
        $this->displayLocator();
        $this->setTabs();
        $ilTabs->activateTab("definitions");

        // content style
        $this->content_style_gui->addCss(
            $this->tpl,
            $this->term_glossary->getRefId(),
            $this->term_glossary->getId()
        );
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

        $defs = ilGlossaryDefinition::getDefinitionList(
            $this->request->getTermId()
        );

        $tpl->setVariable("TXT_TERM", $this->term->getTerm());

        for ($j = 0, $jMax = count($defs); $j < $jMax; $j++) {
            $def = $defs[$j];
            $page_gui = new ilGlossaryDefPageGUI($def["id"]);
            $page_gui->setStyleId(
                $this->content_style_domain->styleForObjId(
                    $this->term_glossary->getId()
                )->getEffectiveStyleId()
            );
            $page_gui->setSourcecodeDownloadScript("ilias.php?baseClass=ilGlossaryPresentationGUI&amp;ref_id=" . $this->ref_id);
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

        // remove default "edit" entry from page preview
        $this->toolbar->setItems([]);

        $this->tpl->setContent($tpl->get());
        $this->quickList();
    }

    public function confirmDefinitionDeletion() : void
    {
        $ilTabs = $this->tabs_gui;

        //$this->getTemplate();
        $this->displayLocator();
        $this->setTabs();
        $ilTabs->activateTab("definitions");

        $this->content_style_gui->addCss(
            $this->tpl,
            $this->term_glossary->getRefId(),
            $this->term_glossary->getId()
        );
        $this->tpl->addCss(ilObjStyleSheet::getSyntaxStylePath());

        $this->tpl->setTitle(
            $this->lng->txt("cont_term") . ": " . $this->term->getTerm()
        );
        $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_glo.svg"));

        $dtpl = new ilTemplate("tpl.glossary_definition_delete.html", true, true, "Modules/Glossary");
        $this->tpl->setOnScreenMessage('question', $this->lng->txt("info_delete_sure"));

        $this->tpl->setVariable("TXT_TERM", $this->term->getTerm());

        $definition = new ilGlossaryDefinition($this->request->getDefinitionId());
        $page_gui = new ilGlossaryDefPageGUI($definition->getId());
        $page_gui->setTemplateOutput(false);
        $page_gui->setStyleId(
            $this->content_style_domain->styleForObjId(
                $this->term_glossary->getId()
            )->getEffectiveStyleId()
        );
        $page_gui->setSourcecodeDownloadScript("ilias.php?baseClass=ilGlossaryPresentationGUI&amp;ref_id=" . $this->ref_id);
        $page_gui->setFileDownloadLink("ilias.php?baseClass=ilGlossaryPresentationGUI&amp;ref_id=" . $this->ref_id);
        $page_gui->setFullscreenLink("ilias.php?baseClass=ilGlossaryPresentationGUI&amp;ref_id=" . $this->ref_id);
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

    public function cancelDefinitionDeletion() : void
    {
        $this->ctrl->redirect($this, "listDefinitions");
    }


    public function deleteDefinition() : void
    {
        $definition = new ilGlossaryDefinition($this->request->getDefinitionId());
        $definition->delete();
        $this->ctrl->redirect($this, "listDefinitions");
    }

    public function moveUp() : void
    {
        $definition = new ilGlossaryDefinition($this->request->getDefinitionId());
        $definition->moveUp();
        $this->ctrl->redirect($this, "listDefinitions");
    }

    public function moveDown() : void
    {
        $definition = new ilGlossaryDefinition($this->request->getDefinitionId());
        $definition->moveDown();
        $this->ctrl->redirect($this, "listDefinitions");
    }

    public function addDefinition() : void
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameterByClass("ilobjglossarygui", "term_id", $this->term->getId());
        $ilCtrl->redirectByClass("ilobjglossarygui", "addDefinition");
    }

    public function cancel() : void
    {
        $this->ctrl->redirect($this, "listDefinitions");
    }

    public function setTabs() : void
    {
        $this->getTabs();
    }

    public function displayLocator() : void
    {
        $gloss_loc = new ilGlossaryLocatorGUI();
        $gloss_loc->setTerm($this->term);
        $gloss_loc->setGlossary($this->glossary);
        $gloss_loc->display();
    }

    public function getTabs() : void
    {
        $lng = $this->lng;
        $ilHelp = $this->help;

        $ilHelp->setScreenIdComponent("glo_term");
        
        if ($this->request->getTermId() > 0) {
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
                $lng->txt("cont_usage") . " (" . ilGlossaryTerm::getNumberOfUsages($this->request->getTermId()) . ")",
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
                "_" . $this->request->getTermId() . "_" . $this->request->getRefId() . "&client_id=" . CLIENT_ID,
                "_top"
            );
        }

        // back to glossary
        $this->tabs_gui->setBackTarget(
            $this->lng->txt("glossary"),
            $this->ctrl->getLinkTargetByClass("ilobjglossarygui", "listTerms")
        );
    }

    public static function _goto(
        string $a_target,
        int $a_ref_id = 0
    ) : void {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();

        $ctrl = $DIC->ctrl();
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
                $ctrl->setParameterByClass("ilGlossaryPresentationGUI", "term_id", $a_target);
                $ctrl->setParameterByClass("ilGlossaryPresentationGUI", "ref_id", $ref_id);
                $ctrl->redirectByClass("ilGlossaryPresentationGUI", "listDefinitions");
            }
        }
        if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
            $main_tpl->setOnScreenMessage('failure', sprintf(
                $lng->txt("msg_no_perm_read_item"),
                ilObject::_lookupTitle($glo_id)
            ), true);
            ilObjectGUI::_gotoRepositoryRoot();
        }

        throw new ilPermissionException($lng->txt("msg_no_perm_read_lm"));
    }

    public function listUsages() : void
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

        $tab = new ilTermUsagesTableGUI($this, "listUsages", $this->request->getTermId());
        
        $tpl->setContent($tab->getHTML());
        
        $this->quickList();
    }
    
    /**
     * Set quick term list cmd into left navigation URL
     */
    public function quickList() : void
    {
        $tpl = $this->tpl;

        $tab = new ilTermQuickListTableGUI($this, "editTerm");
        $tpl->setLeftNavContent($tab->getHTML());
    }
}
