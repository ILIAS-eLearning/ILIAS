<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class ilLMPresentationGUI
*
* GUI class for learning module presentation
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilLMPresentationGUI: ilNoteGUI, ilInfoScreenGUI
* @ilCtrl_Calls ilLMPresentationGUI: ilLMPageGUI, ilGlossaryDefPageGUI, ilCommonActionDispatcherGUI
* @ilCtrl_Calls ilLMPresentationGUI: ilLearningProgressGUI, ilAssGenFeedbackPageGUI
* @ilCtrl_Calls ilLMPresentationGUI: ilRatingGUI
*
* @ingroup ModulesIliasLearningModule
*/
class ilLMPresentationGUI
{
    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilErrorHandling
     */
    protected $error;

    /**
     * @var ilNavigationHistory
     */
    protected $nav_history;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilSetting
     */
    protected $settings;

    /**
     * @var ilMainMenuGUI
     */
    protected $main_menu;

    /**
     * @var ilLocatorGUI
     */
    protected $locator;

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @var ilHelpGUI
     */
    protected $help;

    /**
     * @var ilObjLearningModule
     */
    protected $lm;

    public $tpl;
    public $lng;
    public $layout_doc;
    public $offline;
    public $offline_directory;

    /**
     * @var int
     */
    protected $current_page_id = 0;

    /**
     * @var int
     */
    protected $focus_id = 0;		// focus id is set e.g. from learning objectives course, we focus on a chapter/page

    /**
     * @var bool
     */
    protected $export_all_languages = false;

    /**
     * @var bool
     */
    public $chapter_has_no_active_page = false;

    /**
     * @var bool
     */
    public $deactivated_page = false;

    /**
     * @var string
     */
    protected $requested_back_pg;

    /**
     * @var string
     */
    protected $requested_search_string;

    /**
     * @var
     */
    protected $requested_focus_return;

    /**
     * @var int
     */
    protected $requested_ref_id;

    /**
     * @var int
     */
    protected $requested_obj_id;

    /**
     * @var string
     */
    protected $requested_obj_type;

    /**
     * @var string
     */
    protected $requested_transl;

    /**
     * @var string
     */
    protected $requested_frame;

    /**
     * @var \ilLMPresentationLinker
     */
    protected $linker;

    /**
     * @var ilLMPresentationService
     */
    protected $service;

    public function __construct(
        $a_export_format = "",
        $a_all_languages = false,
        $a_export_dir = "",
        bool $claim_repo_context = true
    ) {
        global $DIC;

        $this->offline = ($a_export_format != "");
        $this->export_all_languages = $a_all_languages;
        $this->export_format = $a_export_format;        // html/scorm
        $this->offline_directory = $a_export_dir;

        $this->tabs = $DIC->tabs();
        $this->user = $DIC->user();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->error = $DIC["ilErr"];
        $this->nav_history = $DIC["ilNavigationHistory"];
        $this->access = $DIC->access();
        $this->settings = $DIC->settings();
        $this->locator = $DIC["ilLocator"];
        $this->tree = $DIC->repositoryTree();
        $this->help = $DIC["ilHelp"];

        $lng = $DIC->language();
        $rbacsystem = $DIC->rbac()->system();
        $ilCtrl = $DIC->ctrl();
        $ilErr = $DIC["ilErr"];

        // load language vars
        $lng->loadLanguageModule("content");

        $this->lng = $lng;
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->frames = array();
        $this->ctrl = $ilCtrl;
        $this->ctrl->saveParameter($this, array("ref_id", "transl", "focus_id", "focus_return"));

        // note: using $DIC->http()->request()->getQueryParams() here will
        // fail, since the goto magic currently relies on setting $_GET
        $this->initByRequest($_GET);

        // check, if learning module is online
        if (!$rbacsystem->checkAccess("write", $this->requested_ref_id)) {
            if ($this->lm->getOfflineStatus()) {
                $ilErr->raiseError($lng->txt("permission_denied"), $ilErr->WARNING);
            }
        }

        if ($claim_repo_context) {
            $DIC->globalScreen()->tool()->context()->claim()->repository();

            // moved this into the if due to #0027200
            if ($this->service->getPresentationStatus()->isTocNecessary()) {
                $DIC->globalScreen()->tool()->context()->current()->addAdditionalData(
                    ilLMGSToolProvider::SHOW_TOC_TOOL,
                    true
                );
            }
        }
    }

    /**
     * Init services and this class by request params.
     *
     * The request params are usually retrieved by HTTP request, but
     * also adjusted during HTML exports, this is, why this method needs to be public.
     *
     * @param array $query_params request query params
     */
    public function initByRequest($query_params)
    {
        $this->service = new ilLMPresentationService(
            $this->user,
            $query_params,
            $this->offline,
            $this->export_all_languages,
            $this->export_format
        );

        $request = $this->service->getRequest();

        $this->requested_obj_type = $request->getRequestedObjType();
        $this->requested_ref_id = $request->getRequestedRefId();
        $this->requested_transl = $request->getRequestedTranslation();      // handled by presentation status
        $this->requested_obj_id = $request->getRequestedObjId();            // handled by navigation status
        $this->requested_back_pg = $request->getRequestedBackPage();
        $this->requested_frame = $request->getRequestedFrame();
        $this->requested_search_string = $request->getRequestedSearchString();
        $this->requested_focus_return = $request->getRequestedFocusReturn();
        $this->requested_mob_id = $request->getRequestedMobId();

        $this->lm_set = $this->service->getSettings();
        $this->lm_gui = $this->service->getLearningModuleGUI();
        $this->lm = $this->service->getLearningModule();
        $this->tracker = $this->service->getTracker();
        $this->linker = $this->service->getLinker();

        // language translation
        $this->lang = $this->service->getPresentationStatus()->getLang();

        $this->lm_tree = $this->service->getLMTree();
        $this->focus_id = $this->service->getPresentationStatus()->getFocusId();
    }

    /**
     * Inject template
     *
     * @param
     * @return
     */
    public function injectTemplate($tpl)
    {
        $this->tpl = $tpl;
    }

    /**
     * Get tracker
     * @return ilLMTracker
     */
    protected function getTracker()
    {
        return $this->service->getTracker();
    }

    /**
     * @throws ilCtrlException
     * @throws ilLMPresentationException
     */
    public function executeCommand()
    {
        $ilNavigationHistory = $this->nav_history;
        $ilAccess = $this->access;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        $ilErr = $this->error;

        // check read permission and parent conditions
        // todo: replace all this by ilAccess call
        if (!$ilAccess->checkAccess("read", "", $this->requested_ref_id) &&
            (!(($this->ctrl->getCmd() == "infoScreen" || $this->ctrl->getNextClass() == "ilinfoscreengui")
            && $ilAccess->checkAccess("visible", "", $this->requested_ref_id)))) {
            $ilErr->raiseError($lng->txt("permission_denied"), $ilErr->WARNING);
        }
        
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("layout", array("showPrintView"));


        $obj_id = $this->requested_obj_id;
        $this->ctrl->setParameter($this, "obj_id", $this->requested_obj_id);
        $ilNavigationHistory->addItem($this->requested_ref_id, $this->ctrl->getLinkTarget($this), "lm");
        $this->ctrl->setParameter($this, "obj_id", $obj_id);

        switch ($next_class) {
            case "ilnotegui":
                $ret = $this->layout();
                break;
                
            case "ilinfoscreengui":
                $ret = $this->outputInfoScreen();
                break;
                
            case "ilcommonactiondispatchergui":
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $gui->enableCommentsSettings(false);
                $this->ctrl->forwardCommand($gui);
                break;

            case "illmpagegui":
                $page_gui = $this->getLMPageGUI($this->requested_obj_id);
                $this->basicPageGuiInit($page_gui);
                $ret = $ilCtrl->forwardCommand($page_gui);
                break;
                
            case "ilglossarydefpagegui":
                $page_gui = new ilGlossaryDefPageGUI($this->requested_obj_id);
                $this->basicPageGuiInit($page_gui);
                $ret = $ilCtrl->forwardCommand($page_gui);
                break;
                
            case "illearningprogressgui":
                $this->initScreenHead("learning_progress");
                $new_gui = new ilLearningProgressGUI(
                    ilLearningProgressGUI::LP_CONTEXT_REPOSITORY,
                    $this->requested_ref_id,
                    $ilUser->getId()
                );
                $this->ctrl->forwardCommand($new_gui);
                break;
            
            case "ilratinggui":
                $rating_gui = new ilRatingGUI();
                $rating_gui->setObject($this->lm->getId(), "lm", $this->requested_obj_id, "lm");
                $this->ctrl->forwardCommand($rating_gui);
                break;

            default:
                if ($_GET["ntf"]) {
                    switch ($_GET["ntf"]) {
                        case 1:
                            ilNotification::setNotification(ilNotification::TYPE_LM, $this->user->getId(), $this->lm->getId(), false);
                            break;

                        case 2:
                            ilNotification::setNotification(ilNotification::TYPE_LM, $this->user->getId(), $this->lm->getId(), true);
                            break;

                        case 3:
                            ilNotification::setNotification(ilNotification::TYPE_LM_PAGE, $this->user->getId(), $this->getCurrentPageId(), false);
                            break;

                        case 4:
                            ilNotification::setNotification(ilNotification::TYPE_LM_PAGE, $this->user->getId(), $this->getCurrentPageId(), true);
                            break;
                    }
                    $ilCtrl->redirect($this, "layout");
                }
                $ret = $this->$cmd();
                break;
        }
    }


    
    /**
    * checks wether offline content generation is activated
    */
    public function offlineMode()
    {
        return $this->offline;
    }
    

    /**
    * get export format
    *
    * @return	string		export format
    */
    public function getExportFormat()
    {
        return $this->export_format;
    }

    /**
    * this dummy function is needed for offline package creation
    */
    public function nop()
    {
    }

    public function attrib2arr($a_attributes)
    {
        $attr = array();
        if (!is_array($a_attributes)) {
            return $attr;
        }
        foreach ($a_attributes as $attribute) {
            $attr[$attribute->name()] = $attribute->value();
        }
        return $attr;
    }

    /**
    * get frames of current frame set
    */
    public function getCurrentFrameSet()
    {
        return $this->frames;
    }
    
    /**
     * Determine layout
     * @return string
     */
    public function determineLayout() : string
    {
        return "standard";
    }
    
    public function resume()
    {
        $this->layout();
    }
        
    /**
    * generates frame layout
    */
    public function layout($a_xml = "main.xml", $doShow = true)
    {
        global $DIC;

        $tpl = $this->tpl;
        $ilSetting = $this->settings;
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        $layout = $this->determineLayout();

        // xmldocfile is deprecated! Use domxml_open_file instead.
        // But since using relative pathes with domxml under windows don't work,
        // we need another solution:
        $xmlfile = file_get_contents("./Modules/LearningModule/layouts/lm/" . $layout . "/" . $a_xml);

        if (!$doc = domxml_open_mem($xmlfile)) {
            throw new ilLMPresentationException("ilLMPresentation: XML File invalid. Error reading " .
                $layout . "/" . $a_xml . ".");
        }
        $this->layout_doc = $doc;
        //echo ":".htmlentities($xmlfile).":$layout:$a_xml:";

        // get current frame node
        $xpc = xpath_new_context($doc);
        $path = (empty($this->requested_frame) || ($this->requested_frame == "_blank"))
            ? "/ilLayout/ilFrame[1]"
            : "//ilFrame[@name='" . $this->requested_frame . "']";
        $result = xpath_eval($xpc, $path);
        $found = $result->nodeset;
        if (count($found) != 1) {
            throw new ilLMPresentationException("ilLMPresentation: XML File invalid. Found " . count($found) . " nodes for " .
                " path " . $path . " in " . $layout . "/" . $a_xml . ". LM Layout is " . $this->lm->getLayout());
        }
        $node = $found[0];

        // ProcessFrameset
        // node is frameset, if it has cols or rows attribute
        $attributes = $this->attrib2arr($node->attributes());

        $this->frames = array();

        // ProcessContentTag
        if ((empty($attributes["template"]) || !empty($this->requested_obj_type))
                && ($this->requested_frame != "_blank" || $this->requested_obj_type != "MediaObject")) {
            // we got a variable content frame (can display different
            // object types (PageObject, MediaObject, GlossarItem)
            // and contains elements for them)

            // determine object type
            if (empty($this->requested_obj_type)) {
                $obj_type = "PageObject";
            } else {
                $obj_type = $this->requested_obj_type;
            }

            // get object specific node
            $childs = $node->child_nodes();
            $found = false;
            foreach ($childs as $child) {
                if ($child->node_name() == $obj_type) {
                    $found = true;
                    $attributes = $this->attrib2arr($child->attributes());
                    $node = $child;
                    //echo "<br>2node:".$node->node_name();
                    break;
                }
            }
            if (!$found) {
                throw new ilLMPresentationException("ilLMPresentation: No template specified for frame '" .
                        $this->requested_frame . "' and object type '" . $obj_type . "'.");
            }
        }

        // get template
        $in_module = ($attributes["template_location"] == "module")
                ? true
                : false;
        /*			if ($in_module)
                    {
                        $this->tpl = new ilGlobalTemplate($attributes["template"], true, true, $in_module);
                        $this->tpl->setBodyClass("");
                    }
                    else
                    {
                        $this->tpl = $tpl;
                    }*/

        // set style sheets
        if (!$this->offlineMode()) {
            $this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
        } else {
            $style_name = $ilUser->getPref("style") . ".css";
            $this->tpl->setVariable("LOCATION_STYLESHEET", "./style/" . $style_name);
        }
            
        iljQueryUtil::initjQuery($this->tpl);
        iljQueryUtil::initjQueryUI($this->tpl);

        ilUIFramework::init($this->tpl);

        // to make e.g. advanced seletions lists work:
        //			$GLOBALS["tpl"] = $this->tpl;

        $childs = $node->child_nodes();
            
        foreach ($childs as $child) {
            $child_attr = $this->attrib2arr($child->attributes());

            switch ($child->node_name()) {
                    case "ilMainMenu":
                        // @todo 6.0
//						$this->ilMainMenu();
                        break;

                    case "ilTOC":
                        // @todo 6.0
//						$this->ilTOC($child_attr["target_frame"]);
                        break;

                    case "ilPage":
                        $this->renderPageTitle();
                        $this->setHeader();
                        $this->ilLMMenu();
                        $content = $this->getContent();
                        $content .= $this->ilLMNotes();
                        $this->tpl->setContent($content);
                        break;

                    case "ilGlossary":
                        $content = $this->ilGlossary($child);
                        break;

                    case "ilLMNavigation":
                        // @todo 6.0
//						$this->ilLMNavigation();
                        break;

                    case "ilMedia":
                        $this->media();
                        break;

                    case "ilLocator":
                        $this->ilLocator();
                        break;
                        
                    case "ilJavaScript":
                        $this->ilJavaScript(
                            $child_attr["inline"],
                            $child_attr["file"],
                            $child_attr["location"]
                        );
                        break;

                    case "ilLMMenu":
                        //$this->ilLMMenu();
                        break;

                    case "ilLMHead":
                        // @todo 6.0
//						$this->ilLMHead();
                        break;
                        
                    case "ilLMSubMenu":
                        $this->ilLMSubMenu();
                        break;
                        
                    case "ilLMNotes":
                        $this->ilLMNotes();
                        break;
                }
        }

        // TODO: Very dirty hack to force the import of JavaScripts in learning content in the FAQ frame (e.g. if jsMath is in the content)
        // Unfortunately there is no standardized way to do this somewhere else. Calling fillJavaScripts always in ilTemplate causes multiple additions of the the js files.
        // 19.7.2014: outcommented, since fillJavaScriptFiles is called in the next blocks, and the
        // following lines would add the js files two times
        //			if (strcmp($this->requested_frame, "topright") == 0) $this->tpl->fillJavaScriptFiles();
        //			if (strcmp($this->requested_frame, "right") == 0) $this->tpl->fillJavaScriptFiles();
        //			if (strcmp($this->requested_frame, "botright") == 0) $this->tpl->fillJavaScriptFiles();

        if (!$this->offlineMode()) {
            ilAccordionGUI::addJavaScript();
            ilAccordionGUI::addCss();

            $this->tpl->addJavascript("./Modules/LearningModule/js/LearningModule.js");
            $close_call = "il.LearningModule.setCloseHTML('" . ilGlyphGUI::get(ilGlyphGUI::CLOSE) . "');";
            $this->tpl->addOnLoadCode($close_call);
                
            //$store->set("cf_".$this->lm->getId());
                
            // handle initial content
            if ($this->requested_frame == "") {
                $store = new ilSessionIStorage("lm");
                $last_frame_url = $store->get("cf_" . $this->lm->getId());
                if ($last_frame_url != "") {
                    $this->tpl->addOnLoadCode("il.LearningModule.setLastFrameUrl('" . $last_frame_url . "', 'center_bottom');");
                }
                    
                if (in_array($layout, array("toc2windyn"))) {
                    $this->tpl->addOnLoadCode("il.LearningModule.setSaveUrl('" .
                            $ilCtrl->getLinkTarget($this, "saveFrameUrl", "", false, false) . "');
							il.LearningModule.openInitFrames();
							");
                }
                $this->tpl->addOnLoadCode("il.LearningModule.setTocRefreshUrl('" .
                        $ilCtrl->getLinkTarget($this, "refreshToc", "", false, false) . "');
							");
            }
                
            // from main menu
            //				$this->tpl->addJavascript("./Services/JavaScript/js/Basic.js");
            $this->tpl->addJavascript("./Services/Navigation/js/ServiceNavigation.js");
            ilYuiUtil::initConnection($this->tpl);

        // @todo 6.0
                //$this->tpl->fillJavaScriptFiles();
                //$this->tpl->fillScreenReaderFocus();
                //$this->tpl->fillCssFiles();
        } else {
            // reset standard css files
                /*
                $this->tpl->resetJavascript();
                $this->tpl->resetCss();
                $this->tpl->setBodyClass("ilLMNoMenu");*/

                /*
                foreach (ilObjContentObject::getSupplyingExportFiles() as $f)
                {
                    if ($f["type"] == "js")
                    {
                        $this->tpl->addJavascript($f["target"]);
                    }
                    if ($f["type"] == "css")
                    {
                        $this->tpl->addCSS($f["target"]);
                    }
                }
                $this->tpl->fillJavaScriptFiles(true);
                $this->tpl->fillCssFiles(true);*/
        }

        // @todo 6.0
        //$this->tpl->fillBodyClass();

        if ($doShow) {
            // (horrible) workaround for preventing template engine
            // from hiding paragraph text that is enclosed
            // in curly brackets (e.g. "{a}", see ilPageObjectGUI::showPage())

            // @todo 6.0
            /*$this->tpl->fillTabs();
            if ($this->fill_on_load_code)
            {
                $this->tpl->fillOnLoadCode();
            }
            $content =  $this->tpl->get();
            $content = str_replace("&#123;", "{", $content);
            $content = str_replace("&#125;", "}", $content);

            header('Content-type: text/html; charset=UTF-8');
            echo $content;*/
            $tpl->printToStdout();
        } else {
            /*$tpl->printToStdout();
            $this->tpl->fillLeftNav();
            $this->tpl->fillOnLoadCode();*/

            $content = $tpl->printToString();
        }

        return($content);
    }
    
    /**
     * Save frame url
     *
     * @param
     * @return
     */
    public function saveFrameUrl()
    {
        $store = new ilSessionIStorage("lm");
        if ($_GET["url"] != "") {
            $store->set("cf_" . $this->lm->getId(), $_GET["url"]);
        } else {
            $store->set("cf_" . $this->lm->getId(), $_GET["url"]);
        }
    }
    

    public function fullscreen()
    {
        return $this->media();
    }

    /**
     * @return string
     * @throws ilException
     */
    public function media()
    {
        $this->tpl = new ilGlobalTemplate("tpl.fullscreen.html", true, true, "Modules/LearningModule");
        //$GLOBALS["tpl"] = $this->tpl;

        // set style sheets
        $this->setContentStyles();
        $this->setSystemStyle();

        $this->ilMedia();
        if (!$this->offlineMode()) {
            $this->tpl->printToStdout();
        } else {
            return $this->tpl->printToString();
        }
        return "";
    }

    /**
     * @return string
     * @throws ilException
     */
    public function glossary()
    {
        $this->tpl = new ilGlobalTemplate("tpl.glossary_term_output.html", true, true, "Modules/LearningModule");
        //$GLOBALS["tpl"] = $this->tpl;
        $this->renderPageTitle();

        // set style sheets
        $this->setContentStyles();
        $this->setSystemStyle();

        $this->ilGlossary();
        if (!$this->offlineMode()) {
            $this->tpl->printToStdout();
        } else {
            return $this->tpl->printToString();
        }

        return "";
    }

    public function page()
    {
        global $DIC;

        $ilUser = $this->user;

        //if ($this->requested_frame != "_blank")
        //{
        //	$this->layout();
        //}
        //else
        //{
        $this->tpl = new ilGlobalTemplate("tpl.page_fullscreen.html", true, true, "Modules/LearningModule");
        $GLOBALS["tpl"] = $this->tpl;
        $this->renderPageTitle();

        $this->setContentStyles();

        // set style sheets
        if (!$this->offlineMode()) {
            $this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
        } else {
            $style_name = $ilUser->getPref("style") . ".css";
            ;
            $this->tpl->setVariable("LOCATION_STYLESHEET", "./style/" . $style_name);
        }

        $this->tpl->setVariable("PAGE_CONTENT", $this->getPageContent());
        if (!$this->offlineMode()) {
            $this->tpl->printToStdout();
        } else {
            return $this->tpl->get();
        }
        //}
    }

    /**
    * output main menu
    */
    public function ilMainMenu()
    {
        // LTI
        global $DIC;
        $ltiview = $DIC["lti"];
        if ($ltiview->isActive()) {
            $ilMainMenu = new LTI\ilMainMenuGUI("_top", false, $this->tpl);
        } else {
            $ilMainMenu = new ilMainMenuGUI("_top", false, $this->tpl);
        }

        if ($this->offlineMode()) {
            $this->tpl->touchBlock("pg_intro");
            $this->tpl->touchBlock("pg_outro");
            return;
        }

        $page_id = $this->getCurrentPageId();
        if ($page_id > 0) {
            $ilMainMenu->setLoginTargetPar("pg_" . $page_id . "_" . $this->lm->getRefId());
        }

        //$this->tpl->touchBlock("mm_intro");
        //$this->tpl->touchBlock("mm_outro");
        $this->tpl->touchBlock("pg_intro");
        $this->tpl->touchBlock("pg_outro");
        $this->tpl->setBodyClass("std");
        $this->tpl->setVariable("MAINMENU", $ilMainMenu->getHTML());
        // LTI
        $this->tpl->setVariable("MAINMENU_SPACER", $ilMainMenu->getSpacerClass());
    }

    /**
    * table of contents
    */
    public function ilTOC($a_get_explorer = false)
    {
        $fac = new ilLMTOCExplorerGUIFactory();
        $exp = $fac->getExplorer($this->service, "ilTOC");
        $exp->handleCommand();
        return $exp;
    }

    /**
     * Get lm presentationtitle
     *
     * @param
     * @return
     */
    public function getLMPresentationTitle()
    {
        return $this->service->getPresentationStatus()->getLMPresentationTitle();
    }


    /**
    * output learning module menu
    */
    public function ilLMMenu()
    {
        $this->renderTabs("content", $this->getCurrentPageId());
        /*$this->tpl->setVariable("MENU", $this->lm_gui->setilLMMenu($this->offlineMode()
            ,$this->getExportFormat(), "content", false, true, $this->getCurrentPageId(),
            $this->lang, $this->export_all_languages));*/
    }

    /**
    * output lm header
    */
    public function setHeader()
    {
        $this->tpl->setTitle($this->getLMPresentationTitle());
        $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_lm.svg"));
    }

    /**
    * output learning module submenu
    */
    public function ilLMSubMenu()
    {
        $rbacsystem = $this->rbacsystem;
        if ($this->abstract) {
            return;
        }

        $showViewInFrameset = true;
        
        if ($showViewInFrameset) {
            $buttonTarget = ilFrameTargetInfo::_getFrame("MainContent");
        } else {
            $buttonTarget = "_top";
        }


        $tpl_menu = new ilTemplate("tpl.lm_sub_menu.html", true, true, "Modules/LearningModule");

        $pg_id = $this->getCurrentPageId();
        if ($pg_id == 0) {
            return;
        }

        // edit learning module
        if (!$this->offlineMode()) {
            if ($rbacsystem->checkAccess("write", $this->requested_ref_id)) {
                $tpl_menu->setCurrentBlock("edit_page");
                $page_id = $this->getCurrentPageId();
                $tpl_menu->setVariable("EDIT_LINK", ILIAS_HTTP_PATH . "/ilias.php?baseClass=ilLMEditorGUI&ref_id=" . $this->requested_ref_id .
                    "&obj_id=" . $page_id . "&to_page=1");
                $tpl_menu->setVariable("EDIT_TXT", $this->lng->txt("edit_page"));
                $tpl_menu->setVariable("EDIT_TARGET", $buttonTarget);
                $tpl_menu->parseCurrentBlock();
            }

            $page_id = $this->getCurrentPageId();

            // permanent link
            $this->tpl->setPermanentLink("pg", "", $page_id . "_" . $this->lm->getRefId());
        }

        $this->tpl->setVariable("SUBMENU", $tpl_menu->get());
    }


    /**
     * Redraw header action
     */
    public function redrawHeaderAction()
    {
        echo $this->addHeaderAction(true);
        exit;
    }

    /**
     * Add header action
     */
    public function addHeaderAction($a_redraw = false)
    {
        if ($this->offline) {
            return;
        }
        $ilAccess = $this->access;
        $tpl = $this->tpl;

        $lm_id = $this->lm->getId();
        $pg_id = $this->getCurrentPageId();

        $this->lng->loadLanguageModule("content");

        $dispatcher = new ilCommonActionDispatcherGUI(
            ilCommonActionDispatcherGUI::TYPE_REPOSITORY,
            $ilAccess,
            $this->lm->getType(),
            $this->requested_ref_id,
            $this->lm->getId()
        );
        $dispatcher->setSubObject("pg", $this->getCurrentPageId());

        ilObjectListGUI::prepareJSLinks(
            $this->ctrl->getLinkTarget($this, "redrawHeaderAction", "", true),
            $this->ctrl->getLinkTargetByClass(array("ilcommonactiondispatchergui", "ilnotegui"), "", "", true, false),
            $this->ctrl->getLinkTargetByClass(array("ilcommonactiondispatchergui", "iltagginggui"), "", "", true, false),
            $this->tpl
        );

        $lg = $dispatcher->initHeaderAction();
        $lg->enableNotes(true);
        $lg->enableComments($this->lm->publicNotes(), false);
                
        if ($this->lm->hasRating() && !$this->offlineMode()) {
            $lg->enableRating(
                true,
                $this->lng->txt("lm_rating"),
                false,
                array("ilcommonactiondispatchergui", "ilratinggui")
            );
        }

        // notification
        if ($this->user->getId() != ANONYMOUS_USER_ID) {
            if (ilNotification::hasNotification(ilNotification::TYPE_LM, $this->user->getId(), $lm_id)) {
                $this->ctrl->setParameter($this, "ntf", 1);
                if (ilNotification::hasOptOut($lm_id)) {
                    $lg->addCustomCommand($this->ctrl->getLinkTarget($this), "cont_notification_deactivate_lm");
                }

                $lg->addHeaderIcon(
                    "not_icon",
                    ilUtil::getImagePath("notification_on.svg"),
                    $this->lng->txt("cont_notification_activated")
                );
            } else {
                $this->ctrl->setParameter($this, "ntf", 2);
                $lg->addCustomCommand($this->ctrl->getLinkTarget($this), "cont_notification_activate_lm");

                if (ilNotification::hasNotification(ilNotification::TYPE_LM_PAGE, $this->user->getId(), $pg_id)) {
                    $this->ctrl->setParameter($this, "ntf", 3);
                    $lg->addCustomCommand($this->ctrl->getLinkTarget($this), "cont_notification_deactivate_page");

                    $lg->addHeaderIcon(
                        "not_icon",
                        ilUtil::getImagePath("notification_on.svg"),
                        $this->lng->txt("cont_page_notification_activated")
                    );
                } else {
                    $this->ctrl->setParameter($this, "ntf", 4);
                    $lg->addCustomCommand($this->ctrl->getLinkTarget($this), "cont_notification_activate_page");

                    $lg->addHeaderIcon(
                        "not_icon",
                        ilUtil::getImagePath("notification_off.svg"),
                        $this->lng->txt("cont_notification_deactivated")
                    );
                }
            }
            $this->ctrl->setParameter($this, "ntf", "");
        }

        if (!$this->offline) {
            if ($ilAccess->checkAccess("write", "", $this->requested_ref_id)) {
                if ($this->getCurrentPageId() <= 0) {
                    $link = $this->ctrl->getLinkTargetByClass(["ilLMEditorGUI", "ilobjlearningmodulegui"], "chapters");
                } else {
                    $link = ILIAS_HTTP_PATH . "/ilias.php?baseClass=ilLMEditorGUI&ref_id=" . $this->requested_ref_id .
                        "&obj_id=" . $this->getCurrentPageId() . "&to_page=1";
                }
                $lg->addCustomCommand($link, "edit_page");
            }
        }


        if (!$a_redraw) {
            $this->tpl->setVariable("HEAD_ACTION", $lg->getHeaderAction($this->tpl));
        } else {
            // we need to add onload code manually (rating, comments, etc.)
            return $lg->getHeaderAction() .
                $tpl->getOnLoadCodeForAsynch();
        }
    }

    /**
    * output notes of page
    */
    public function ilLMNotes() : string
    {
        $ilAccess = $this->access;
        $ilSetting = $this->settings;

        // no notes in offline (export) mode
        if ($this->offlineMode()) {
            return "";
        }
        
        // output notes (on top)
        
        if (!$ilSetting->get("disable_notes")) {
            $this->addHeaderAction();
        }
        
        // now output comments
        
        if ($ilSetting->get("disable_comments")) {
            return "";
        }
        if (!$this->lm->publicNotes()) {
            return "";
        }

        $next_class = $this->ctrl->getNextClass($this);

        $pg_id = $this->getCurrentPageId();

        if ($pg_id == 0) {
            return "";
        }
        $notes_gui = new ilNoteGUI($this->lm->getId(), $this->getCurrentPageId(), "pg");
        
        if ($ilAccess->checkAccess("write", "", $this->requested_ref_id) &&
            $ilSetting->get("comments_del_tutor", 1)) {
            $notes_gui->enablePublicNotesDeletion(true);
        }
        
        $this->ctrl->setParameter($this, "frame", $this->requested_frame);
        $this->ctrl->setParameter($this, "obj_id", $this->requested_obj_id);
        
        $notes_gui->enablePrivateNotes();
        if ($this->lm->publicNotes()) {
            $notes_gui->enablePublicNotes();
        }

        $callback = array($this, "observeNoteAction");
        $notes_gui->addObserver($callback);

        if ($next_class == "ilnotegui") {
            $html = $this->ctrl->forwardCommand($notes_gui);
        } else {
            $html = $notes_gui->getNotesHTML();
        }
        return $html;
    }


    /**
    * locator
    */
    public function ilLocator($a_std_templ_loaded = false)
    {
        global $DIC;
        $ltiview = $DIC["lti"];
        $ilLocator = $this->locator;
        $tree = $this->tree;
        $ilCtrl = $this->ctrl;

        if (empty($this->requested_obj_id)) {
            $a_id = $this->lm_tree->getRootId();
        } else {
            $a_id = $this->requested_obj_id;
        }


        if (!$this->lm->cleanFrames()) {
            $frame_param = $this->requested_frame;
            $frame_target = "";
        } elseif (!$this->offlineMode()) {
            $frame_param = "";
            $frame_target = ilFrameTargetInfo::_getFrame("MainContent");
        } else {
            $frame_param = "";
            $frame_target = "_top";
        }

        if (!$this->offlineMode()) {
            // LTI
            if ($ltiview->isActive()) {
                // Do nothing, its complicated...
            } else {
                $ilLocator->addRepositoryItems();
                //$ilLocator->addItem("...", "");

                /*
                $par_id = $tree->getParentId($this->requested_ref_id);
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $par_id);
                $ilLocator->addItem(
                    ilObject::_lookupTitle(ilObject::_lookupObjId($par_id)),
                    $ilCtrl->getLinkTargetByClass("ilrepositorygui", "frameset"),
                    ilFrameTargetInfo::_getFrame("MainContent"), $par_id);
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->requested_ref_id);*/
            }
        } else {
            $ilLocator->setOffline(true);
        }

        if ($this->lm_tree->isInTree($a_id)) {
            $path = $this->lm_tree->getPathFull($a_id);

            foreach ($path as $key => $row) {
                if ($row["type"] != "pg") {
                    if ($row["child"] != $this->lm_tree->getRootId()) {
                        $ilLocator->addItem(
                            ilUtil::shortenText(
                                ilStructureObject::_getPresentationTitle(
                                    $row["child"],
                                    ilLMOBject::CHAPTER_TITLE,
                                    $this->lm->isActiveNumbering(),
                                    $this->lm_set->get("time_scheduled_page_activation"),
                                    false,
                                    0,
                                    $this->lang
                                ),
                                50,
                                true
                            ),
                            $this->linker->getLink("layout", $row["child"], $frame_param, "StructureObject"),
                            $frame_target
                        );
                    } else {
                        $ilLocator->addItem(
                            ilUtil::shortenText($this->getLMPresentationTitle(), 50, true),
                            $this->linker->getLink("layout", "", $frame_param),
                            $frame_target,
                            $this->requested_ref_id
                        );
                    }
                }
            }
        } else {		// lonely page
            $ilLocator->addItem(
                $this->getLMPresentationTitle(),
                $this->linker->getLink("layout", "", $this->requested_frame)
            );

            $lm_obj = ilLMObjectFactory::getInstance($this->lm, $a_id);

            $ilLocator->addItem(
                $lm_obj->getTitle(),
                $this->linker->getLink("layout", $a_id, $frame_param),
                $frame_target
            );
        }

        if (DEBUG) {
            $debug = "DEBUG: <font color=\"red\">" . $this->type . "::" . $this->id . "::" . $_GET["cmd"] . "</font><br/>";
        }

        //$prop_name = $this->objDefinition->getPropertyName($_GET["cmd"],$this->type);


        $this->tpl->setLocator();
    }

    /**
     * Get the current page id
     *
     * @return bool|int current page id
     */
    public function getCurrentPageId()
    {
        return $this->service->getNavigationStatus()->getCurrentPage();

        $ilUser = $this->user;

        if (!$this->offlineMode() && $this->current_page_id !== false) {
            return $this->current_page_id;
        }

        $this->chapter_has_no_active_page = false;
        $this->deactivated_page = false;
        
        // determine object id
        if (empty($this->requested_obj_id)) {
            $obj_id = $this->lm_tree->getRootId();
        } else {
            $obj_id = $this->requested_obj_id;
            $active = ilLMPage::_lookupActive(
                $obj_id,
                $this->lm->getType(),
                $this->lm_set->get("time_scheduled_page_activation")
            );

            if (!$active &&
                ilLMPageObject::_lookupType($obj_id) == "pg") {
                $this->deactivated_page = true;
            }
        }

        // obj_id not in tree -> it is a unassigned page -> return page id
        if (!$this->lm_tree->isInTree($obj_id)) {
            return $obj_id;
        }

        $curr_node = $this->lm_tree->getNodeData($obj_id);
        
        $active = ilLMPage::_lookupActive(
            $obj_id,
            $this->lm->getType(),
            $this->lm_set->get("time_scheduled_page_activation")
        );

        if ($curr_node["type"] == "pg" &&
            $active) {		// page in tree -> return page id
            $page_id = $curr_node["obj_id"];
        } else { 		// no page -> search for next page and return its id
            $succ_node = true;
            $active = false;
            $page_id = $obj_id;
            while ($succ_node && !$active) {
                $succ_node = $this->lm_tree->fetchSuccessorNode($page_id, "pg");
                $page_id = $succ_node["obj_id"];
                $active = ilLMPage::_lookupActive(
                    $page_id,
                    $this->lm->getType(),
                    $this->lm_set->get("time_scheduled_page_activation")
                );
            }

            if ($succ_node["type"] != "pg") {
                $this->chapter_has_no_active_page = true;
                return 0;
            }

            // if public access get first public page in chapter
            if ($ilUser->getId() == ANONYMOUS_USER_ID &&
               $this->lm_gui->object->getPublicAccessMode() == 'selected') {
                $public = ilLMObject::_isPagePublic($page_id);

                while ($public === false && $page_id > 0) {
                    $succ_node = $this->lm_tree->fetchSuccessorNode($page_id, 'pg');
                    $page_id = $succ_node['obj_id'];
                    $public = ilLMObject::_isPagePublic($page_id);
                }
            }
            
            // check whether page found is within "clicked" chapter
            if ($this->lm_tree->isInTree($page_id)) {
                $path = $this->lm_tree->getPathId($page_id);
                if (!in_array($this->requested_obj_id, $path)) {
                    $this->chapter_has_no_active_page = true;
                }
            }
        }

        $this->current_page_id = $page_id;
        return $page_id;
    }


    public function getLayoutLinkTargets()
    {
        if (!is_object($this->layout_doc)) {
            return array();
        }

        $xpc = xpath_new_context($this->layout_doc);

        $path = "/ilLayout/ilLinkTargets/LinkTarget";
        $res = xpath_eval($xpc, $path);
        $targets = array();
        for ($i = 0; $i < count($res->nodeset); $i++) {
            $type = $res->nodeset[$i]->get_attribute("Type");
            $frame = $res->nodeset[$i]->get_attribute("Frame");
            $onclick = $res->nodeset[$i]->get_attribute("OnClick");
            $targets[$type] = array("Type" => $type, "Frame" => $frame, "OnClick" => $onclick);
        }
        var_dump($targets);
        exit;
        return $targets;
    }

    /**
     * Set content style
     */
    protected function setContentStyles()
    {
        // content style

        $this->tpl->addCss(ilObjStyleSheet::getContentStylePath($this->lm->getStyleSheetId()));
        $this->tpl->addCss(ilObjStyleSheet::getSyntaxStylePath());

        /*
        $this->tpl->setCurrentBlock("ContentStyle");
        if (!$this->offlineMode())
        {
            $this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
                ilObjStyleSheet::getContentStylePath($this->lm->getStyleSheetId()));
        }
        else
        {
            $this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET", "content_style/content.css");
        }
        $this->tpl->parseCurrentBlock();

        // syntax style
        $this->tpl->setCurrentBlock("SyntaxStyle");
        if (!$this->offlineMode())
        {
            $this->tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
                ilObjStyleSheet::getSyntaxStylePath());
        }
        else
        {
            $this->tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
                "syntaxhighlight.css");
        }
        $this->tpl->parseCurrentBlock();*/
    }

    /**
     * Set system style
     */
    protected function setSystemStyle()
    {
        $this->tpl->addCss(ilUtil::getStyleSheetLocation());
    }




    /**
     * process <ilPage> content tag
     *
     * @param $a_page_node page node
     * @param int $a_page_id header / footer page id
     * @return string
     */
    public function getContent()
    {
        $this->fill_on_load_code = true;
        $this->setContentStyles();

        $tpl = new ilTemplate("tpl.lm_content.html", true, true, "Modules/LearningModule/Presentation");

        // call ilLMContentRendererGUI

        $navigation_renderer = new ilLMNavigationRendererGUI(
            $this->service,
            $this,
            $this->lng,
            $this->user,
            $this->tpl,
            $this->requested_obj_id,
            $this->requested_back_pg,
            $this->requested_frame
        );


        $tpl->setVariable("TOP_NAVIGATION", $navigation_renderer->renderTop());
        $tpl->setVariable("BOTTOM_NAVIGATION", $navigation_renderer->renderBottom());
        $tpl->setVariable("PAGE_CONTENT", $this->getPageContent());
        $tpl->setVariable("RATING", $this->renderRating());


        return $tpl->get();
    }

    /**
     * Get page content
     */
    protected function getPageContent()
    {
        $content_renderer = new ilLMContentRendererGUI(
            $this->service,
            $this,
            $this->lng,
            $this->ctrl,
            $this->access,
            $this->user,
            $this->help,
            $this->requested_obj_id
        );

        return $content_renderer->render();
    }

    /**
     * Render rating
     *
     * @return string
     */
    protected function renderRating()
    {
        // rating
        $rating = "";
        if ($this->lm->hasRatingPages() && !$this->offlineMode()) {
            $rating_gui = new ilRatingGUI();
            $rating_gui->setObject($this->lm->getId(), "lm", $this->getCurrentPageId(), "lm");
            $rating_gui->setYourRatingText($this->lng->txt("lm_rate_page"));

            /*
                $this->tpl->setVariable("VAL_RATING", $rating->getHTML(false, true,
                    "il.ExcPeerReview.saveComments(".$a_set["peer_id"].", %rating%)"));
            */

            $this->ctrl->setParameter($this, "pgid", $this->getCurrentPageId());
            $this->tpl->addOnLoadCode("il.LearningModule.setRatingUrl('" .
                $this->ctrl->getLinkTarget($this, "updatePageRating", "", true, false) .
                "')");
            $this->ctrl->setParameter($this, "pgid", "");

            $rating = '<div id="ilrtrpg" style="text-align:right">' .
                $rating_gui->getHtml(true, true, "il.LearningModule.saveRating(%rating%);") .
                "</div>";
        }
        return $rating;
    }


    
    public function updatePageRating()
    {
        $ilUser = $this->user;
        
        $pg_id = $_GET["pgid"];
        if (!$this->ctrl->isAsynch() || !$pg_id) {
            exit();
        }
                
        $rating = (int) $_POST["rating"];
        if ($rating) {
            ilRating::writeRatingForUserAndObject(
                $this->lm->getId(),
                "lm",
                $pg_id,
                "lm",
                $ilUser->getId(),
                $_POST["rating"]
            );
        } else {
            ilRating::resetRatingForUserAndObject(
                $this->lm->getId(),
                "lm",
                $pg_id,
                "lm",
                $ilUser->getId()
            );
        }
        
        $rating = new ilRatingGUI();
        $rating->setObject($this->lm->getId(), "lm", $pg_id, "lm", $ilUser->getId());
        $rating->setYourRatingText($this->lng->txt("lm_rate_page"));
        
        echo $rating->getHtml(true, true, "il.LearningModule.saveRating(%rating%);");
        
        echo $this->tpl->getOnLoadCodeForAsynch();
        exit();
    }

    /**
     * Basic page gui initialisation
     *
     * @param
     * @return
     */
    public function basicPageGuiInit($a_page_gui)
    {
        $a_page_gui->setStyleId(ilObjStyleSheet::getEffectiveContentStyleId(
            $this->lm->getStyleSheetId(),
            "lm"
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
            $this->ctrl->setParameter($this, "obj_id", $this->getCurrentPageId());		// see #22403
        }
        $a_page_gui->setFileDownloadLink($this->linker->getLink("downloadFile"));
        $a_page_gui->setSourcecodeDownloadScript($this->linker->getLink("sourcecodeDownload",
            $this->getCurrentPageId()));
        if (!$this->offlineMode()) {
            $this->ctrl->setParameter($this, "obj_id", $this->requested_obj_id);
        }
        $a_page_gui->setFullscreenLink($this->linker->getLink("fullscreen"));
    }


    /**
    * show glossary term
    */
    public function ilGlossary()
    {
        $ilCtrl = $this->ctrl;

        $term_gui = new ilGlossaryTermGUI($this->requested_obj_id);

        // content style
        $this->setContentStyles();

        $term_gui->setPageLinker($this->linker);

        $term_gui->setOfflineDirectory($this->getOfflineDirectory());
        if (!$this->offlineMode()) {
            $ilCtrl->setParameter($this, "pg_type", "glo");
        }
        $term_gui->output($this->offlineMode(), $this->tpl);


        if (!$this->offlineMode()) {
            $ilCtrl->setParameter($this, "pg_type", "");
        }
    }

    /**
    * output media
    */
    public function ilMedia()
    {
        $this->setContentStyles();

        $this->renderPageTitle();

        $this->tpl->setCurrentBlock("ilMedia");

        $med_links = ilMediaItem::_getMapAreasIntLinks($this->requested_mob_id);
        $link_xml = $this->linker->getLinkXML($med_links);

        $media_obj = new ilObjMediaObject($this->requested_mob_id);
        if (!empty($_GET["pg_id"])) {
            $pg_obj = $this->getLMPage($_GET["pg_id"], $_GET["pg_type"]);
            $pg_obj->buildDom();

            $xml = "<dummy>";
            // todo: we get always the first alias now (problem if mob is used multiple
            // times in page)
            $xml .= $pg_obj->getMediaAliasElement($this->requested_mob_id);
            $xml .= $media_obj->getXML(IL_MODE_OUTPUT);
            $xml .= $link_xml;
            $xml .= "</dummy>";
        } else {
            $xml = "<dummy>";
            // todo: we get always the first alias now (problem if mob is used multiple
            // times in page)
            $xml .= $media_obj->getXML(IL_MODE_ALIAS);
            $xml .= $media_obj->getXML(IL_MODE_OUTPUT);
            $xml .= $link_xml;
            $xml .= "</dummy>";
        }


        $xsl = file_get_contents("./Services/COPage/xsl/page.xsl");
        $args = array( '/_xml' => $xml, '/_xsl' => $xsl );
        $xh = xslt_create();

        if (!$this->offlineMode()) {
            $wb_path = ilUtil::getWebspaceDir("output") . "/";
        } else {
            $wb_path = "";
        }

        $mode = ($_GET["cmd"] == "fullscreen")
            ? "fullscreen"
            : "media";
        $enlarge_path = ilUtil::getImagePath("enlarge.svg", false, "output", $this->offlineMode());
        $fullscreen_link =
            $this->linker->getLink("fullscreen");
        $params = array('mode' => $mode, 'enlarge_path' => $enlarge_path,
            'link_params' => "ref_id=" . $this->lm->getRefId(),'fullscreen_link' => $fullscreen_link,
            'ref_id' => $this->lm->getRefId(), 'pg_frame' => $pg_frame, 'webspace_path' => $wb_path);
        $output = xslt_process($xh, "arg:/_xml", "arg:/_xsl", null, $args, $params);

        echo xslt_error($xh);
        xslt_free($xh);

        // unmask user html
        $this->tpl->setVariable("MEDIA_CONTENT", $output);
        
        // add js
        ilObjMediaObjectGUI::includePresentationJS($this->tpl);
    }

    /**
    * Puts JS into template
    */
    public function ilJavaScript($a_inline = "", $a_file = "", $a_location = "")
    {
        if ($a_inline != "") {
            $js_tpl = new ilTemplate($a_inline, true, false, $a_location);
            $js = $js_tpl->get();
            $this->tpl->setVariable("INLINE_JS", $js);
        }
    }

    /**
     * Get successor page
     *
     * @param
     * @return
     */
    public function getSuccessorPage()
    {
        $ilUser = $this->user;

        $page_id = $this->getCurrentPageId();

        if (empty($page_id)) {
            return 0;
        }

        // determine successor page_id
        $found = false;

        // empty chapter
        if ($this->chapter_has_no_active_page &&
            ilLMObject::_lookupType($this->requested_obj_id) == "st") {
            $c_id = $this->requested_obj_id;
        } else {
            if ($this->deactivated_page) {
                $c_id = $this->requested_obj_id;
            } else {
                $c_id = $page_id;
            }
        }
        while (!$found) {
            $succ_node = $this->lm_tree->fetchSuccessorNode($c_id, "pg");
            $c_id = $succ_node["obj_id"];

            $active = ilLMPage::_lookupActive(
                $c_id,
                $this->lm->getType(),
                $this->lm_set->get("time_scheduled_page_activation")
            );

            if ($succ_node["obj_id"] > 0 &&
                $ilUser->getId() == ANONYMOUS_USER_ID &&
                ($this->lm->getPublicAccessMode() == "selected" &&
                    !ilLMObject::_isPagePublic($succ_node["obj_id"]))) {
                $found = false;
            } elseif ($succ_node["obj_id"] > 0 && !$active) {
                // look, whether activation data should be shown
                $act_data = ilLMPage::_lookupActivationData((int) $succ_node["obj_id"], $this->lm->getType());
                if ($act_data["show_activation_info"] &&
                    (ilUtil::now() < $act_data["activation_start"])) {
                    $found = true;
                } else {
                    $found = false;
                }
            } else {
                $found = true;
            }
        }

        if ($found) {
            return $succ_node["obj_id"];
        }
        return 0;
    }


    public function processNodes(&$a_content, &$a_node)
    {
        $child_nodes = $a_node->child_nodes();
        foreach ($child_nodes as $child) {
            if ($child->node_name() == "ilFrame") {
                $attributes = $this->attrib2arr($child->attributes());
                // node is frameset, if it has cols or rows attribute
                if ((!empty($attributes["rows"])) || (!empty($attrubtes["cols"]))) {
                    // if framset has name, another http request is necessary
                    // (html framesets don't have names, so we need a wrapper frame)
                    if (!empty($attributes["name"])) {
                        unset($attributes["template"]);
                        unset($attributes["template_location"]);
                        $attributes["src"] =
                            $this->linker->getLink(
                                "layout",
                                $this->requested_obj_id,
                                $attributes["name"],
                                "",
                                "keep",
                                "",
                                $_GET["srcstring"]
                            );
                        $attributes["title"] = $this->lng->txt("cont_frame_" . $attributes["name"]);
                        $a_content .= $this->buildTag("", "frame", $attributes);
                        $this->frames[$attributes["name"]] = $attributes["name"];
                    //echo "<br>processNodes:add1 ".$attributes["name"];
                    } else {	// ok, no name means that we can easily output the frameset tag
                        $a_content .= $this->buildTag("start", "frameset", $attributes);
                        $this->processNodes($a_content, $child);
                        $a_content .= $this->buildTag("end", "frameset");
                    }
                } else {	// frame with
                    unset($attributes["template"]);
                    unset($attributes["template_location"]);
                    $attributes["src"] =
                        $this->linker->getLink(
                            "layout",
                            $this->requested_obj_id,
                            $attributes["name"],
                            "",
                            "keep",
                            "",
                            $_GET["srcstring"]
                        );
                    $attributes["title"] = $this->lng->txt("cont_frame_" . $attributes["name"]);
                    if ($attributes["name"] == "toc") {
                        $attributes["src"] .= "#" . $this->requested_obj_id;
                    } else {
                        // Handle Anchors
                        if ($_GET["anchor"] != "") {
                            $attributes["src"] .= "#" . rawurlencode($_GET["anchor"]);
                        }
                    }
                    $a_content .= $this->buildTag("", "frame", $attributes);
                    $this->frames[$attributes["name"]] = $attributes["name"];
                }
            }
        }
    }

    /**
    * generate a tag with given name and attributes
    *
    * @param	string		"start" | "end" | "" for starting or ending tag or complete tag
    * @param	string		element/tag name
    * @param	array		array of attributes
    */
    public function buildTag($type, $name, $attr = "")
    {
        $tag = "<";

        if ($type == "end") {
            $tag .= "/";
        }

        $tag .= $name;

        if (is_array($attr)) {
            foreach ($attr as $k => $v) {
                $tag .= " " . $k . "=\"$v\"";
            }
        }

        if ($type == "") {
            $tag .= "/";
        }

        $tag .= ">\n";

        return $tag;
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
    public function showInfoScreen()
    {
        $this->outputInfoScreen(true);
    }
    
    protected function initScreenHead($a_active_tab = "info")
    {
        $ilAccess = $this->access;
        $ilLocator = $this->locator;
        $ilUser = $this->user;
        
        $this->renderPageTitle();
        
        // set style sheets
        /*
        if (!$this->offlineMode())
        {
            $this->tpl->setStyleSheetLocation(ilUtil::getStyleSheetLocation());
        }
        else
        {
            $style_name = $ilUser->getPref("style").".css";;
            $this->tpl->setStyleSheetLocation("./".$style_name);
        }*/

        $this->tpl->loadStandardTemplate();
        $this->tpl->setTitle($this->getLMPresentationTitle());
        $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_lm.svg"));

        $this->renderTabs($a_active_tab, 0);
        /*$this->tpl->setVariable("TABS", $this->lm_gui->setilLMMenu($this->offlineMode()
            ,$this->getExportFormat(), $a_active_tab, true, false, 0,
            $this->lang, $this->export_all_languages));*/
        
        // Full locator, if read permission is given
        if ($ilAccess->checkAccess("read", "", $this->requested_ref_id)) {
            $this->ilLocator(true);
        } else {
            $ilLocator->addRepositoryItems();
            $this->tpl->setLocator();
        }
    }

    /**
    * info screen
    */
    public function outputInfoScreen($a_standard_locator = false)
    {
        $ilAccess = $this->access;

        $this->initScreenHead();
        
        $this->lng->loadLanguageModule("meta");

        $info = new ilInfoScreenGUI($this->lm_gui);
        $info->enablePrivateNotes();
        $info->enableLearningProgress();

        $info->enableNews();
        if ($ilAccess->checkAccess("write", "", $this->requested_ref_id)) {
            $news_set = new ilSetting("news");
            $enable_internal_rss = $news_set->get("enable_rss_for_internal");
            
            $info->enableNewsEditing();
            
            if ($enable_internal_rss) {
                $info->setBlockProperty("news", "settings", true);
            }
        }
        
        // add read / back button
        /*
        if ($ilAccess->checkAccess("read", "", $this->requested_ref_id))
        {
            if ($this->requested_obj_id > 0)
            {
                $this->ctrl->setParameter($this, "obj_id", $this->requested_obj_id);
                $info->addButton($this->lng->txt("back"),
                    $this->ctrl->getLinkTarget($this, "layout"));
            }
            else
            {
                $info->addButton($this->lng->txt("view"),
                    $this->ctrl->getLinkTarget($this, "layout"));
            }
        }*/
        
        // show standard meta data section
        $info->addMetaDataSections($this->lm->getId(), 0, $this->lm->getType());

        if ($this->offlineMode()) {
            $this->tpl->setContent($info->getHTML());
            return $this->tpl->get();
        } else {
            // forward the command
            $this->ctrl->forwardCommand($info);
            //$this->tpl->setContent("aa");
            $this->tpl->printToStdout();
        }
    }

    /**
    * show selection screen for print view
    */
    public function showPrintViewSelection()
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        
        if (!$this->lm->isActivePrintView() || !$this->lm->isActiveLMMenu()) {
            return;
        }


        $this->setContentStyles();
        $this->renderPageTitle();

        $this->tpl->loadStandardTemplate();

        $this->renderTabs("print", 0);
        /*$this->tpl->setVariable("TABS", $this->lm_gui->setilLMMenu($this->offlineMode()
            ,$this->getExportFormat(), "print", true,false, 0,
            $this->lang, $this->export_all_languages));*/
            
        $this->ilLocator(true);
        $this->tpl->addBlockFile(
            "ADM_CONTENT",
            "adm_content",
            "tpl.lm_print_selection.html",
            "Modules/LearningModule"
        );

        // set title header
        $this->tpl->setTitle($this->getLMPresentationTitle());
        $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_lm.svg"));

        /*$this->tpl->setVariable("TXT_BACK", $this->lng->txt("back"));
        $this->ctrl->setParameterByClass("illmpresentationgui", "obj_id", $this->requested_obj_id);
        $this->tpl->setVariable("LINK_BACK",
            $this->ctrl->getLinkTargetByClass("illmpresentationgui", ""));*/

        $this->ctrl->setParameterByClass("illmpresentationgui", "obj_id", $this->requested_obj_id);
        $this->tpl->setVariable("FORMACTION", $this->ctrl->getFormaction($this));

        $nodes = $this->lm_tree->getSubtree($this->lm_tree->getNodeData($this->lm_tree->getRootId()));
        $nodes = $this->filterNonAccessibleNode($nodes);

        if (!is_array($_POST["item"])) {
            if ($this->requested_obj_id != "") {
                $_POST["item"][$this->requested_obj_id] = "y";
            } else {
                $_POST["item"][1] = "y";
            }
        }

        $this->initPrintViewSelectionForm();

        foreach ($nodes as $node) {

            // check page activation
            $active = ilLMPage::_lookupActive(
                $node["obj_id"],
                $this->lm->getType(),
                $this->lm_set->get("time_scheduled_page_activation")
            );

            if ($node["type"] == "pg" &&
                !$active) {
                continue;
            }

            $text = $img_scr = $img_alt = "";
            $disabled = false;
            $checked = false;

            switch ($node["type"]) {
                // page
                case "pg":
                    $text =
                        ilLMPageObject::_getPresentationTitle(
                            $node["obj_id"],
                            $this->lm->getPageHeader(),
                            $this->lm->isActiveNumbering(),
                            $this->lm_set->get("time_scheduled_page_activation"),
                            false,
                            0,
                            $this->lang
                        );
                    
                    if ($ilUser->getId() == ANONYMOUS_USER_ID &&
                       $this->lm_gui->object->getPublicAccessMode() == "selected") {
                        if (!ilLMObject::_isPagePublic($node["obj_id"])) {
                            $disabled = true;
                            $text .= " (" . $this->lng->txt("cont_no_access") . ")";
                        }
                    }
                    $img_src = ilUtil::getImagePath("icon_pg.svg");
                    $img_alt = $lng->txt("icon") . " " . $lng->txt("pg");
                    break;

                // learning module
                case "du":
                    $text = $this->getLMPresentationTitle();
                    $img_src = ilUtil::getImagePath("icon_lm.svg");
                    $img_alt = $lng->txt("icon") . " " . $lng->txt("obj_lm");
                    break;

                // chapter
                case "st":
                    $text =
                        ilStructureObject::_getPresentationTitle(
                            $node["obj_id"],
                            ilLMOBject::CHAPTER_TITLE,
                            $this->lm->isActiveNumbering(),
                            $this->lm_set->get("time_scheduled_page_activation"),
                            false,
                            0,
                            $this->lang
                        );
                    if ($ilUser->getId() == ANONYMOUS_USER_ID &&
                       $this->lm_gui->object->getPublicAccessMode() == "selected") {
                        if (!ilLMObject::_isPagePublic($node["obj_id"])) {
                            $disabled = true;
                            $text .= " (" . $this->lng->txt("cont_no_access") . ")";
                        }
                    }
                    $img_src = ilUtil::getImagePath("icon_st.svg");
                    $img_alt = $lng->txt("icon") . " " . $lng->txt("st");
                    break;
            }

            if (!ilObjContentObject::_checkPreconditionsOfPage($this->lm->getRefId(), $this->lm->getId(), $node["obj_id"])) {
                $text .= " (" . $this->lng->txt("cont_no_access") . ")";
            }

            $this->nl->addListNode(
                $node["obj_id"],
                $text,
                $node["parent"],
                $checked,
                $disabled,
                $img_src,
                $img_alt
            );
        }

        
        // check for free page
        if ($this->requested_obj_id > 0 && !$this->lm_tree->isInTree($this->requested_obj_id)) {
            $text =
                ilLMPageObject::_getPresentationTitle(
                    $this->requested_obj_id,
                    $this->lm->getPageHeader(),
                    $this->lm->isActiveNumbering(),
                    $this->lm_set->get("time_scheduled_page_activation"),
                    false,
                    0,
                    $this->lang
                );
            
            if ($ilUser->getId() == ANONYMOUS_USER_ID &&
               $this->lm_gui->object->getPublicAccessMode() == "selected") {
                if (!ilLMObject::_isPagePublic($this->requested_obj_id)) {
                    $disabled = true;
                    $text .= " (" . $this->lng->txt("cont_no_access") . ")";
                }
            }
            $img_src = ilUtil::getImagePath("icon_pg.svg");
            $id = $this->requested_obj_id;

            $checked = true;

            $this->nl->addListNode(
                $id,
                $text,
                0,
                $checked,
                $disabled,
                $img_src,
                $img_alt
            );
        }

        $f = $this->form->getHTML();

        // submit toolbar
        $tb = new ilToolbarGUI();
        $tb->addFormButton($lng->txt("cont_show_print_view"), "showPrintView");
        $this->tpl->setVariable("TOOLBAR", $tb->getHTML());

        $this->tpl->setVariable("ITEM_SELECTION", $f);
        $this->tpl->printToStdout();
    }

    /**
     * @param array $nodes
     * @return array
     */
    protected function filterNonAccessibleNode($nodes)
    {
        $tracker = $this->getTracker();
        // if navigation is restricted based on correct answered questions
        // check if we have preceeding pages including unsanswered/incorrect answered questions
        if (!$this->offlineMode()) {
            if ($this->lm->getRestrictForwardNavigation()) {
                $nodes = array_filter($nodes, function ($node) use ($tracker) {
                    return !$tracker->hasPredIncorrectAnswers($node["child"]);
                });
            }
        }
        return $nodes;
    }


    /**
     * Init print view selection form.
     */
    public function initPrintViewSelectionForm()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->form = new ilPropertyFormGUI();

        // selection type
        $radg = new ilRadioGroupInputGUI($lng->txt("cont_selection"), "sel_type");
        $radg->setValue("page");
        $op1 = new ilRadioOption($lng->txt("cont_current_page"), "page");
        $radg->addOption($op1);
        $op2 = new ilRadioOption($lng->txt("cont_current_chapter"), "chapter");
        $radg->addOption($op2);
        $op3 = new ilRadioOption($lng->txt("cont_selected_pg_chap"), "selection");
        $radg->addOption($op3);

        $nl = new ilNestedListInputGUI("", "obj_id");
        $this->nl = $nl;
        $op3->addSubItem($nl);


        $this->form->addItem($radg);

        $this->form->addCommandButton("showPrintView", $lng->txt("cont_show_print_view"));
        $this->form->setOpenTag(false);
        $this->form->setCloseTag(false);

        $this->form->setTitle($lng->txt("cont_print_selection"));
        $this->form->setFormAction($ilCtrl->getFormAction($this));
    }

    /**
    * show print view
    */
    public function showPrintView()
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $tabs = $this->tabs;

        if (!$this->lm->isActivePrintView() || !$this->lm->isActiveLMMenu()) {
            return;
        }

        $this->renderPageTitle();

        $tabs->setBackTarget(
            $lng->txt("back"),
            $ilCtrl->getLinkTarget($this, "showPrintViewSelection")
        );
        
        $c_obj_id = $this->getCurrentPageId();
        // set values according to selection
        if ($_POST["sel_type"] == "page") {
            if (!is_array($_POST["obj_id"]) || !in_array($c_obj_id, $_POST["obj_id"])) {
                $_POST["obj_id"][] = $c_obj_id;
            }
        }
        if ($_POST["sel_type"] == "chapter" && $c_obj_id > 0) {
            $path = $this->lm_tree->getPathFull($c_obj_id);
            $chap_id = $path[1]["child"];
            if ($chap_id > 0) {
                $_POST["obj_id"][] = $chap_id;
            }
        }
        
        $this->setContentStyles();

        $tpl = new ilTemplate("tpl.lm_print_view.html", true, true, "Modules/LearningModule");

        // set title header
        $this->tpl->setTitle($this->getLMPresentationTitle());

        $nodes = $this->lm_tree->getSubtree($this->lm_tree->getNodeData($this->lm_tree->getRootId()));

        $act_level = 99999;
        $activated = false;

        $glossary_links = array();
        $output_header = false;
        $media_links = array();

        // get header and footer
        if ($this->lm->getFooterPage() > 0 && !$this->lm->getHideHeaderFooterPrint()) {
            if (ilLMObject::_exists($this->lm->getFooterPage())) {
                $page_object_gui = $this->getLMPageGUI($this->lm->getFooterPage());
                $page_object_gui->setStyleId(ilObjStyleSheet::getEffectiveContentStyleId(
                    $this->lm->getStyleSheetId(),
                    "lm"
                ));

    
                // determine target frames for internal links
                $page_object_gui->setLinkFrame($this->requested_frame);
                $page_object_gui->setOutputMode("print");
                $page_object_gui->setPresentationTitle("");
                $page_object_gui->setFileDownloadLink("#");
                $page_object_gui->setFullscreenLink("#");
                $page_object_gui->setSourceCodeDownloadScript("#");
                $footer_page_content = $page_object_gui->showPage();
            }
        }
        if ($this->lm->getHeaderPage() > 0 && !$this->lm->getHideHeaderFooterPrint()) {
            if (ilLMObject::_exists($this->lm->getHeaderPage())) {
                $page_object_gui = $this->getLMPageGUI($this->lm->getHeaderPage());
                $page_object_gui->setStyleId(ilObjStyleSheet::getEffectiveContentStyleId(
                    $this->lm->getStyleSheetId(),
                    "lm"
                ));

    
                // determine target frames for internal links
                $page_object_gui->setLinkFrame($this->requested_frame);
                $page_object_gui->setOutputMode("print");
                $page_object_gui->setPresentationTitle("");
                $page_object_gui->setFileDownloadLink("#");
                $page_object_gui->setFullscreenLink("#");
                $page_object_gui->setSourceCodeDownloadScript("#");
                $header_page_content = $page_object_gui->showPage();
            }
        }

        // add free selected pages
        if (is_array($_POST["obj_id"])) {
            foreach ($_POST["obj_id"] as $k) {
                if ($k > 0 && !$this->lm_tree->isInTree($k)) {
                    if (ilLMObject::_lookupType($k) == "pg") {
                        $nodes[] = array("obj_id" => $k, "type" => "pg", "free" => true);
                    }
                }
            }
        } else {
            ilUtil::sendFailure($lng->txt("cont_print_no_page_selected"), true);
            $ilCtrl->redirect($this, "showPrintViewSelection");
        }

        foreach ($nodes as $node_key => $node) {
            // check page activation
            $active = ilLMPage::_lookupActive(
                $node["obj_id"],
                $this->lm->getType(),
                $this->lm_set->get("time_scheduled_page_activation")
            );
            if ($node["type"] == "pg" && !$active) {
                continue;
            }
            
            // print all subchapters/subpages if higher chapter
            // has been selected
            if ($node["depth"] <= $act_level) {
                if (is_array($_POST["obj_id"]) && in_array($node["obj_id"], $_POST["obj_id"])) {
                    $act_level = $node["depth"];
                    $activated = true;
                } else {
                    $act_level = 99999;
                    $activated = false;
                }
            }
            if ($this->lm->getRestrictForwardNavigation()) {
                if ($this->getTracker()->hasPredIncorrectAnswers($node["obj_id"])) {
                    continue;
                }
            }
            if ($activated &&
                ilObjContentObject::_checkPreconditionsOfPage($this->lm->getRefId(), $this->lm->getId(), $node["obj_id"])) {
                // output learning module header
                if ($node["type"] == "du") {
                    $output_header = true;
                }
                
                // output chapter title
                if ($node["type"] == "st") {
                    if ($ilUser->getId() == ANONYMOUS_USER_ID &&
                       $this->lm_gui->object->getPublicAccessMode() == "selected") {
                        if (!ilLMObject::_isPagePublic($node["obj_id"])) {
                            continue;
                        }
                    }

                    $chap = new ilStructureObject($this->lm, $node["obj_id"]);
                    $tpl->setCurrentBlock("print_chapter");

                    $chapter_title = $chap->_getPresentationTitle(
                        $node["obj_id"],
                        $this->lm->isActiveNumbering(),
                        $this->lm_set->get("time_scheduled_page_activation"),
                        0,
                        $this->lang
                    );
                    $tpl->setVariable(
                        "CHAP_TITLE",
                        $chapter_title
                    );
                        
                    if ($this->lm->getPageHeader() == ilLMOBject::CHAPTER_TITLE) {
                        if ($nodes[$node_key + 1]["type"] == "pg") {
                            $tpl->setVariable(
                                "CHAP_HEADER",
                                $header_page_content
                            );
                            $did_chap_page_header = true;
                        }
                    }

                    $tpl->parseCurrentBlock();
                    $tpl->setCurrentBlock("print_block");
                    $tpl->parseCurrentBlock();
                }

                // output page
                if ($node["type"] == "pg") {
                    if ($ilUser->getId() == ANONYMOUS_USER_ID &&
                       $this->lm_gui->object->getPublicAccessMode() == "selected") {
                        if (!ilLMObject::_isPagePublic($node["obj_id"])) {
                            continue;
                        }
                    }

                    $tpl->setCurrentBlock("print_item");
                    
                    // get page
                    $page_id = $node["obj_id"];
                    $page_object_gui = $this->getLMPageGUI($page_id);
                    $page_object = $page_object_gui->getPageObject();
                    $page_object_gui->setStyleId(ilObjStyleSheet::getEffectiveContentStyleId(
                        $this->lm->getStyleSheetId(),
                        "lm"
                    ));


                    // get lm page
                    $lm_pg_obj = new ilLMPageObject($this->lm, $page_id);
                    $lm_pg_obj->setLMId($this->lm->getId());

                    // determine target frames for internal links
                    $page_object_gui->setLinkFrame($this->requested_frame);
                    $page_object_gui->setOutputMode("print");
                    $page_object_gui->setPresentationTitle("");
                    
                    if ($this->lm->getPageHeader() == ilLMObject::PAGE_TITLE || $node["free"] === true) {
                        $page_title = ilLMPageObject::_getPresentationTitle(
                            $lm_pg_obj->getId(),
                            $this->lm->getPageHeader(),
                            $this->lm->isActiveNumbering(),
                            $this->lm_set->get("time_scheduled_page_activation"),
                            false,
                            0,
                            $this->lang
                        );

                        // prevent page title after chapter title
                        // that have the same content
                        if ($this->lm->isActiveNumbering()) {
                            $chapter_title = trim(substr(
                                $chapter_title,
                                strpos($chapter_title, " ")
                            ));
                        }

                        if ($page_title != $chapter_title) {
                            $page_object_gui->setPresentationTitle($page_title);
                        }
                    }

                    // handle header / footer
                    $hcont = $header_page_content;
                    $fcont = $footer_page_content;

                    if ($this->lm->getPageHeader() == ilLMOBject::CHAPTER_TITLE) {
                        if ($did_chap_page_header) {
                            $hcont = "";
                        }
                        if ($nodes[$node_key + 1]["type"] == "pg" &&
                            !($nodes[$node_key + 1]["depth"] <= $act_level
                             && !in_array($nodes[$node_key + 1]["obj_id"], $_POST["obj_id"]))) {
                            $fcont = "";
                        }
                    }
                    
                    $page_object_gui->setFileDownloadLink("#");
                    $page_object_gui->setFullscreenLink("#");
                    $page_object_gui->setSourceCodeDownloadScript("#");
                    $page_content = $page_object_gui->showPage();
                    if ($this->lm->getPageHeader() != ilLMObject::PAGE_TITLE) {
                        $tpl->setVariable(
                            "CONTENT",
                            $hcont . $page_content . $fcont
                        );
                    } else {
                        $tpl->setVariable(
                            "CONTENT",
                            $hcont . $page_content . $fcont . "<br />"
                        );
                    }
                    $chapter_title = "";
                    $tpl->parseCurrentBlock();
                    $tpl->setCurrentBlock("print_block");
                    $tpl->parseCurrentBlock();

                    // get internal links
                    $int_links = ilInternalLink::_getTargetsOfSource($this->lm->getType() . ":pg", $node["obj_id"]);

                    $got_mobs = false;

                    foreach ($int_links as $key => $link) {
                        if ($link["type"] == "git" &&
                            ($link["inst"] == IL_INST_ID || $link["inst"] == 0)) {
                            $glossary_links[$key] = $link;
                        }
                        if ($link["type"] == "mob" &&
                            ($link["inst"] == IL_INST_ID || $link["inst"] == 0)) {
                            $got_mobs = true;
                            $mob_links[$key] = $link;
                        }
                    }

                    // this is not cool because of performance reasons
                    // unfortunately the int link table does not
                    // store the target frame (we want to append all linked
                    // images but not inline images (i.e. mobs with no target
                    // frame))
                    if ($got_mobs) {
                        $page_object->buildDom();
                        $links = $page_object->getInternalLinks();
                        foreach ($links as $link) {
                            if ($link["Type"] == "MediaObject"
                                && $link["TargetFrame"] != ""
                                && $link["TargetFrame"] != "None") {
                                $media_links[] = $link;
                            }
                        }
                    }
                }
            }
        }

        $annex_cnt = 0;
        $annexes = array();

        // glossary
        if (count($glossary_links) > 0 && !$this->lm->isActivePreventGlossaryAppendix()) {
            // sort terms
            $terms = array();
            
            foreach ($glossary_links as $key => $link) {
                $term = ilGlossaryTerm::_lookGlossaryTerm($link["id"]);
                $terms[$term . ":" . $key] = array("key" => $key, "link" => $link, "term" => $term);
            }
            $terms = ilUtil::sortArray($terms, "term", "asc");
            //ksort($terms);

            foreach ($terms as $t) {
                $link = $t["link"];
                $key = $t["key"];
                $defs = ilGlossaryDefinition::getDefinitionList($link["id"]);
                $def_cnt = 1;

                // output all definitions of term
                foreach ($defs as $def) {
                    // definition + number, if more than 1 definition
                    if (count($defs) > 1) {
                        $tpl->setCurrentBlock("def_title");
                        $tpl->setVariable(
                            "TXT_DEFINITION",
                            $this->lng->txt("cont_definition") . " " . ($def_cnt++)
                        );
                        $tpl->parseCurrentBlock();
                    }
                    $page_gui = new ilGlossaryDefPageGUI($def["id"]);
                    $page_gui->setTemplateOutput(false);
                    $page_gui->setOutputMode("print");

                    $tpl->setCurrentBlock("definition");
                    $page_gui->setFileDownloadLink("#");
                    $page_gui->setFullscreenLink("#");
                    $page_gui->setSourceCodeDownloadScript("#");
                    $output = $page_gui->showPage();
                    $tpl->setVariable("VAL_DEFINITION", $output);
                    $tpl->parseCurrentBlock();
                }

                // output term
                $tpl->setCurrentBlock("term");
                $tpl->setVariable(
                    "VAL_TERM",
                    $term = ilGlossaryTerm::_lookGlossaryTerm($link["id"])
                );
                $tpl->parseCurrentBlock();
            }

            // output glossary header
            $annex_cnt++;
            $tpl->setCurrentBlock("glossary");
            $annex_title = $this->lng->txt("cont_annex") . " " .
                chr(64 + $annex_cnt) . ": " . $this->lng->txt("glo");
            $tpl->setVariable("TXT_GLOSSARY", $annex_title);
            $tpl->parseCurrentBlock();

            $annexes[] = $annex_title;
        }

        // referenced images
        if (count($media_links) > 0) {
            foreach ($media_links as $media) {
                if (substr($media["Target"], 0, 4) == "il__") {
                    $arr = explode("_", $media["Target"]);
                    $id = $arr[count($arr) - 1];
                    
                    $med_obj = new ilObjMediaObject($id);
                    $med_item = $med_obj->getMediaItem("Standard");
                    if (is_object($med_item)) {
                        if (is_int(strpos($med_item->getFormat(), "image"))) {
                            $tpl->setCurrentBlock("ref_image");
                            
                            // image source
                            if ($med_item->getLocationType() == "LocalFile") {
                                $tpl->setVariable(
                                    "IMG_SOURCE",
                                    ilUtil::getWebspaceDir("output") . "/mobs/mm_" . $id .
                                    "/" . $med_item->getLocation()
                                );
                            } else {
                                $tpl->setVariable(
                                    "IMG_SOURCE",
                                    $med_item->getLocation()
                                );
                            }
                            
                            if ($med_item->getCaption() != "") {
                                $tpl->setVariable("IMG_TITLE", $med_item->getCaption());
                            } else {
                                $tpl->setVariable("IMG_TITLE", $med_obj->getTitle());
                            }
                            $tpl->parseCurrentBlock();
                        }
                    }
                }
            }
            
            // output glossary header
            $annex_cnt++;
            $tpl->setCurrentBlock("ref_images");
            $annex_title = $this->lng->txt("cont_annex") . " " .
                chr(64 + $annex_cnt) . ": " . $this->lng->txt("cont_ref_images");
            $tpl->setVariable("TXT_REF_IMAGES", $annex_title);
            $tpl->parseCurrentBlock();

            $annexes[] = $annex_title;
        }

        // output learning module title and toc
        if ($output_header) {
            $tpl->setCurrentBlock("print_header");
            $tpl->setVariable("LM_TITLE", $this->getLMPresentationTitle());
            if ($this->lm->getDescription() != "none") {
                $md = new ilMD($this->lm->getId(), 0, $this->lm->getType());
                $md_gen = $md->getGeneral();
                foreach ($md_gen->getDescriptionIds() as $id) {
                    $md_des = $md_gen->getDescription($id);
                    $description = $md_des->getDescription();
                }

                $tpl->setVariable(
                    "LM_DESCRIPTION",
                    $description
                );
            }
            $tpl->parseCurrentBlock();

            // output toc
            $nodes2 = $nodes;
            foreach ($nodes2 as $node2) {
                if ($node2["type"] == "st"
                    && ilObjContentObject::_checkPreconditionsOfPage($this->lm->getRefId(), $this->lm->getId(), $node2["obj_id"])) {
                    for ($j = 1; $j < $node2["depth"]; $j++) {
                        $tpl->setCurrentBlock("indent");
                        $tpl->setVariable("IMG_BLANK", ilUtil::getImagePath("browser/blank.png"));
                        $tpl->parseCurrentBlock();
                    }
                    $tpl->setCurrentBlock("toc_entry");
                    $tpl->setVariable(
                        "TXT_TOC_TITLE",
                        ilStructureObject::_getPresentationTitle(
                            $node2["obj_id"],
                            ilLMOBject::CHAPTER_TITLE,
                            $this->lm->isActiveNumbering(),
                            $this->lm_set->get("time_scheduled_page_activation"),
                            false,
                            0,
                            $this->lang
                        )
                    );
                    $tpl->parseCurrentBlock();
                }
            }

            // annexes
            foreach ($annexes as $annex) {
                $tpl->setCurrentBlock("indent");
                $tpl->setVariable("IMG_BLANK", ilUtil::getImagePath("browser/blank.png"));
                $tpl->parseCurrentBlock();
                $tpl->setCurrentBlock("toc_entry");
                $tpl->setVariable("TXT_TOC_TITLE", $annex);
                $tpl->parseCurrentBlock();
            }

            $tpl->setCurrentBlock("toc");
            $tpl->setVariable("TXT_TOC", $this->lng->txt("cont_toc"));
            $tpl->parseCurrentBlock();

            $tpl->setCurrentBlock("print_start_block");
            $tpl->parseCurrentBlock();
        }
        
        // output author information
        $md = new ilMD($this->lm->getId(), 0, $this->lm->getType());
        if (is_object($lifecycle = $md->getLifecycle())) {
            $sep = $author = "";
            foreach (($ids = $lifecycle->getContributeIds()) as $con_id) {
                $md_con = $lifecycle->getContribute($con_id);
                if ($md_con->getRole() == "Author") {
                    foreach ($ent_ids = $md_con->getEntityIds() as $ent_id) {
                        $md_ent = $md_con->getEntity($ent_id);
                        $author = $author . $sep . $md_ent->getEntity();
                        $sep = ", ";
                    }
                }
            }
            if ($author != "") {
                $this->lng->loadLanguageModule("meta");
                $tpl->setCurrentBlock("author");
                $tpl->setVariable("TXT_AUTHOR", $this->lng->txt("meta_author"));
                $tpl->setVariable("LM_AUTHOR", $author);
                $tpl->parseCurrentBlock();
            }
        }

        
        // output copyright information
        if (is_object($md_rights = $md->getRights())) {
            $copyright = $md_rights->getDescription();
            $copyright = ilMDUtils::_parseCopyright($copyright);

            if ($copyright != "") {
                $this->lng->loadLanguageModule("meta");
                $tpl->setCurrentBlock("copyright");
                $tpl->setVariable("TXT_COPYRIGHT", $this->lng->txt("meta_copyright"));
                $tpl->setVariable("LM_COPYRIGHT", $copyright);
                $tpl->parseCurrentBlock();
            }
        }

        $this->tpl->setContent($tpl->get());
        $this->tpl->printToStdout();
    }

    /**
    * download file of file lists
    */
    public function downloadFile()
    {
        $page_gui = $this->getLMPageGUI($this->getCurrentPageId());
        $page_gui->downloadFile();
    }

    /**
    * show download list
    */
    public function showDownloadList()
    {
        if (!$this->lm->isActiveDownloads() || !$this->lm->isActiveLMMenu()) {
            return;
        }

        $this->setContentStyles();
        $this->renderPageTitle();

        $this->tpl->loadStandardTemplate();

        $this->renderTabs("download", 0);

        $this->ilLocator(true);
        //$this->tpl->stopTitleFloating();
        $this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.lm_download_list.html", "Modules/LearningModule");

        // set title header
        $this->tpl->setTitle($this->getLMPresentationTitle());
        $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_lm.svg"));

        // output copyright information
        $md = new ilMD($this->lm->getId(), 0, $this->lm->getType());
        if (is_object($md_rights = $md->getRights())) {
            $copyright = $md_rights->getDescription();
            
            $copyright = ilMDUtils::_parseCopyright($copyright);

            if ($copyright != "") {
                $this->lng->loadLanguageModule("meta");
                $this->tpl->setCurrentBlock("copyright");
                $this->tpl->setVariable("TXT_COPYRIGHT", $this->lng->txt("meta_copyright"));
                $this->tpl->setVariable("LM_COPYRIGHT", $copyright);
                $this->tpl->parseCurrentBlock();
            }
        }


        $download_table = new ilLMDownloadTableGUI($this, "showDownloadList", $this->lm);
        $this->tpl->setVariable("DOWNLOAD_TABLE", $download_table->getHTML());
        $this->tpl->printToStdout();
    }

    
    /**
    * send download file (xml/html)
    */
    public function downloadExportFile()
    {
        if (!$this->lm->isActiveDownloads() || !$this->lm->isActiveLMMenu()) {
            return;
        }

        $base_type = explode("_", $_GET["type"]);
        $base_type = $base_type[0];
        $file = $this->lm->getPublicExportFile($base_type);
        if ($this->lm->getPublicExportFile($base_type) != "") {
            $dir = $this->lm->getExportDirectory($_GET["type"]);
            if (is_file($dir . "/" . $file)) {
                ilUtil::deliverFile($dir . "/" . $file, $file);
                exit;
            }
        }
    }

    /**
     * Get focused link (used in learning objectives courses)
     *
     * @param int $a_ref_id reference id of learning module
     * @param int $a_obj_id chapter or page id
     * @param int $a_return_ref_id return ref id
     *
     * @return string link
     */
    public function getFocusLink($a_ref_id, $a_obj_id, $a_return_ref_id)
    {
        return "ilias.php?baseClass=ilLMPresentationGUI&amp;ref_id=" . $a_ref_id . "&amp;obj_id=" . $a_obj_id . "&amp;focus_id=" .
            $a_obj_id . "&amp;focus_return=" . $a_return_ref_id;
    }

    /**
     * Show message screen
     *
     * @param
     * @return
     */
    public function showMessageScreen($a_content)
    {
        // content style
        $this->setContentStyles();

        $tpl = new ilTemplate("tpl.page_message_screen.html", true, true, "Modules/LearningModule");
        $tpl->setVariable("TXT_PAGE_NO_PUBLIC_ACCESS", $a_content);

        $this->tpl->setVariable("PAGE_CONTENT", $tpl->get());
    }


    /**
     * Show info message, if page is not accessible in public area
     */
    public function showNoPublicAccess()
    {
        $this->showMessageScreen($this->lng->txt("msg_page_no_public_access"));
    }

    /**
     * Show info message, if page is not accessible in public area
     */
    public function showNoPageAccess()
    {
        $this->showMessageScreen($this->lng->txt("msg_no_page_access"));
    }

    /**
     * Show message if navigation to page is not allowed due to unanswered
     * questions.
     */
    public function showNavRestrictionDueToQuestions()
    {
        $this->showMessageScreen($this->lng->txt("cont_no_page_access_unansw_q"));
    }

    
    public function getSourcecodeDownloadLink()
    {
        if (!$this->offlineMode()) {
            //$this->ctrl->setParameter($this, session_name(), session_id());
            $target = $this->ctrl->getLinkTarget($this, "");
            $target = ilUtil::appendUrlParameterString($target, session_name() . "=" . session_id());
            return $this->ctrl->getLinkTarget($this, "");
        } else {
            return "";
        }
    }


    
    /**
     * get offline directory
     * @return directory where to store offline files
     *
     * current used in code paragraph
     */
    public function getOfflineDirectory()
    {
        return $this->offline_directory;
    }
    
    /**
     * store paragraph into file directory
     * files/codefile_$pg_id_$paragraph_id/downloadtitle
     */
    public function handleCodeParagraph($page_id, $paragraph_id, $title, $text)
    {
        $directory = $this->getOfflineDirectory() . "/codefiles/" . $page_id . "/" . $paragraph_id;
        ilUtil::makeDirParents($directory);
        $file = $directory . "/" . $title;
        if (!($fp = @fopen($file, "w+"))) {
            die("<b>Error</b>: Could not open \"" . $file . "\" for writing" .
                " in <b>" . __FILE__ . "</b> on line <b>" . __LINE__ . "</b><br />");
        }
        chmod($file, 0770);
        fwrite($fp, $text);
        fclose($fp);
    }
    
    // #8613
    protected function renderPageTitle()
    {
        $this->tpl->setHeaderPageTitle($this->getLMPresentationTitle());
        // @todo 6.0
//		$this->tpl->fillWindowTitle();
//		$this->tpl->fillContentLanguage();
    }
    

    /**
     * Get lm page gui object
     *
     * @param
     * @return
     */
    public function getLMPageGUI($a_id)
    {
        if ($this->lang != "-" && ilPageObject::_exists("lm", $a_id, $this->lang)) {
            return new ilLMPageGUI($a_id, 0, false, $this->lang);
        }
        return new ilLMPageGUI($a_id);
    }

    /**
     * Get lm page object
     *
     * @param
     * @return
     */
    public function getLMPage($a_id, $a_type = "")
    {
        $type = ($a_type == "mep")
            ? "mep"
            : "lm";

        $lang = $this->lang;
        if (!ilPageObject::_exists($type, $a_id, $lang)) {
            $lang = "-";
        }

        switch ($type) {
            case "mep":
                return new ilMediaPoolPage($a_id, 0, $lang);
            default:
                return new ilLMPage($a_id, 0, $lang);
        }
    }

    /**
     * Refresh toc (called if questions have been answered correctly)
     */
    public function refreshToc()
    {
        $exp = $this->ilTOC(true);

        echo $exp->getHTML() .
            "<script>" . $exp->getOnLoadCode() . "</script>";
        exit;
    }

    /**
     * Generate new ilNote and send Notifications to the users informing that there are new comments in the LM
     * @param $a_lm_id
     * @param $a_page_id
     * @param $a_type
     * @param $a_action
     * @param $a_note_id
     */
    public function observeNoteAction($a_lm_id, $a_page_id, $a_type, $a_action, $a_note_id)
    {
        $note = new ilNote($a_note_id);
        $note = $note->getText();

        $notification = new ilLearningModuleNotification(
            ilLearningModuleNotification::ACTION_COMMENT,
            ilNotification::TYPE_LM_PAGE,
            $this->lm,
            $a_page_id,
            $note
        );

        $notification->send();
    }

    /**
     * Render tabs
     *
     * @param
     * @return
     */
    protected function renderTabs($active_tab, $current_page_id)
    {
        $menu_editor = new ilLMMenuEditor();
        $menu_editor->setObjId($this->lm->getId());

        $navigation_renderer = new ilLMMenuRendererGUI(
            $this->tabs,
            $current_page_id,
            $active_tab,
            (string) $this->getExportFormat(),
            $this->export_all_languages,
            $this->lm,
            $this->offlineMode(),
            $menu_editor,
            $this->lang,
            $this->ctrl,
            $this->access,
            $this->user,
            $this->lng
        );
        $navigation_renderer->render();
    }
}
