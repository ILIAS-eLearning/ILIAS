<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/LearningModule/classes/class.ilObjLearningModule.php");
require_once("./Services/MainMenu/classes/class.ilMainMenuGUI.php");
require_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");

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

    public $lm;
    public $tpl;
    public $lng;
    public $layout_doc;
    public $offline;
    public $offline_directory;
    protected $current_page_id = false;
    protected $focus_id = 0;		// focus id is set e.g. from learning objectives course, we focus on a chapter/page
    protected $export_all_languages = false;

    public function __construct()
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->error = $DIC["ilErr"];
        $this->nav_history = $DIC["ilNavigationHistory"];
        $this->access = $DIC->access();
        $this->settings = $DIC->settings();
        $this->locator = $DIC["ilLocator"];
        $this->tree = $DIC->repositoryTree();
        $this->help = $DIC["ilHelp"];
        $ilUser = $DIC->user();
        $lng = $DIC->language();
        $tpl = $DIC["tpl"];
        $rbacsystem = $DIC->rbac()->system();
        $ilCtrl = $DIC->ctrl();
        $ilErr = $DIC["ilErr"];

        // load language vars
        $lng->loadLanguageModule("content");

        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->offline = false;
        $this->frames = array();
        $this->ctrl = $ilCtrl;
        $this->ctrl->saveParameter($this, array("ref_id", "transl", "focus_id", "focus_return"));
        $this->lm_set = new ilSetting("lm");

        include_once("./Modules/LearningModule/classes/class.ilObjLearningModuleGUI.php");
        $this->lm_gui = new ilObjLearningModuleGUI($data, $_GET["ref_id"], true, false);
        $this->lm = $this->lm_gui->object;
        
        // language translation
        include_once("./Services/Object/classes/class.ilObjectTranslation.php");
        $this->ot = ilObjectTranslation::getInstance($this->lm->getId());
        //include_once("./Services/COPage/classes/class.ilPageMultiLang.php");
        //$this->ml = new ilPageMultiLang("lm", $this->lm->getId());
        $this->lang = "-";
        if ($this->ot->getContentActivated()) {
            $langs = $this->ot->getLanguages();
            if (isset($langs[$_GET["transl"]]) || $_GET["transl"] == $this->ot->getMasterLanguage()) {
                $this->lang = $_GET["transl"];
            } elseif (isset($langs[$ilUser->getCurrentLanguage()])) {
                $this->lang = $ilUser->getCurrentLanguage();
            }
            if ($this->lang == $this->ot->getMasterLanguage()) {
                $this->lang = "-";
            }
        }

        // check, if learning module is online
        if (!$rbacsystem->checkAccess("write", $_GET["ref_id"])) {
            if ($this->lm->getOfflineStatus()) {
                $ilErr->raiseError($lng->txt("permission_denied"), $ilErr->WARNING);
            }
        }

        include_once("./Modules/LearningModule/classes/class.ilLMTree.php");
        $this->lm_tree = ilLMTree::getInstance($this->lm->getId());

        /*$this->lm_tree = new ilTree($this->lm->getId());
        $this->lm_tree->setTableNames('lm_tree','lm_data');
        $this->lm_tree->setTreeTablePK("lm_id");*/

        if ((int) $_GET["focus_id"] > 0 && $this->lm_tree->isInTree((int) $_GET["focus_id"])) {
            $this->focus_id = (int) $_GET["focus_id"];
        }
    }


    /**
    * execute command
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
        if (!$ilAccess->checkAccess("read", "", $_GET["ref_id"]) &&
            (!(($this->ctrl->getCmd() == "infoScreen" || $this->ctrl->getNextClass() == "ilinfoscreengui")
            && $ilAccess->checkAccess("visible", "", $_GET["ref_id"])))) {
            $ilErr->raiseError($lng->txt("permission_denied"), $ilErr->WARNING);
        }
        
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("layout", array("showPrintView"));

        $cmd = (isset($_POST['cmd']['citation']))
            ? "ilCitation"
            : $cmd;

        $obj_id = $_GET["obj_id"];
        $this->ctrl->setParameter($this, "obj_id", $_GET["obj_id"]);
        $ilNavigationHistory->addItem($_GET["ref_id"], $this->ctrl->getLinkTarget($this), "lm");
        $this->ctrl->setParameter($this, "obj_id", $obj_id);

        switch ($next_class) {
            case "ilnotegui":
                $ret = $this->layout();
                break;
                
            case "ilinfoscreengui":
                $ret = $this->outputInfoScreen();
                break;
                
            case "ilcommonactiondispatchergui":
                include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $gui->enableCommentsSettings(false);
                $this->ctrl->forwardCommand($gui);
                break;

            case "illmpagegui":
                include_once("./Modules/LearningModule/classes/class.ilLMPageGUI.php");
                $page_gui = $this->getLMPageGUI($_GET["obj_id"]);
                $this->basicPageGuiInit($page_gui);
                $ret = $ilCtrl->forwardCommand($page_gui);
                break;
                
            case "ilglossarydefpagegui":
                include_once("./Modules/Glossary/classes/class.ilGlossaryDefPageGUI.php");
                $page_gui = new ilGlossaryDefPageGUI($_GET["obj_id"]);
                $this->basicPageGuiInit($page_gui);
                $ret = $ilCtrl->forwardCommand($page_gui);
                break;
                
            case "illearningprogressgui":
                $this->initScreenHead("learning_progress");
                include_once './Services/Tracking/classes/class.ilLearningProgressGUI.php';
                $new_gui = new ilLearningProgressGUI(ilLearningProgressGUI::LP_CONTEXT_REPOSITORY, $_GET["ref_id"], $ilUser->getId());
                $this->ctrl->forwardCommand($new_gui);
                break;
            
            case "ilratinggui":
                include_once("./Services/Rating/classes/class.ilRatingGUI.php");
                $rating_gui = new ilRatingGUI();
                $rating_gui->setObject($this->lm->getId(), "lm", $_GET["obj_id"], "lm");
                $this->ctrl->forwardCommand($rating_gui);
                break;

            default:
                $ret = $this->$cmd();
                break;
        }
    }


    /**
    * set offline mode (content is generated for offline package)
    */
    public function setOfflineMode($a_offline = true, $a_all_languages = false)
    {
        $this->offline = $a_offline;
        $this->export_all_languages = $a_all_languages;
    }
    
    
    /**
    * checks wether offline content generation is activated
    */
    public function offlineMode()
    {
        return $this->offline;
    }
    
    /**
    * set export format
    *
    * @param	string		$a_format		"html" / "scorm"
    */
    public function setExportFormat($a_format)
    {
        $this->export_format = $a_format;
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

    /**
    *   calls export of digilib-object
    *   at this point other lm-objects can be exported
    *
    *   @param
    *   @access public
    *   @return
    */
    public function export()
    {
    }

    /**
     * Get tracker
     *
     * @return ilLMTracker tracker instance
     */
    public function getTracker()
    {
        include_once("./Modules/LearningModule/classes/class.ilLMTracker.php");
        $tracker = ilLMTracker::getInstance($this->lm->getRefId());
        $tracker->setCurrentPage($this->getCurrentPageId());
        return $tracker;
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
    */
    public function determineLayout()
    {
        if ($this->getExportFormat() == "scorm") {
            $layout = "1window";
        } else {
            $layout = $this->lm->getLayout();
            if ($this->lm->getLayoutPerPage()) {
                $pg_id = $this->getCurrentPageId();
                if (!in_array($_GET["frame"], array("", "_blank")) && $_GET["from_page"] > 0) {
                    $pg_id = (int) $_GET["from_page"];
                }

                // this is needed, e.g. lm is toc2win, page is 3window and media linked to media frame
                if (in_array($_GET["cmd"], array("media", "glossary")) && $_GET["back_pg"] > 0) {
                    $pg_id = (int) $_GET["back_pg"];
                }

                if ($pg_id > 0) {
                    $lay = ilLMObject::lookupLayout($pg_id);
                    if ($lay != "") {
                        $layout = $lay;
                    }
                }
            }
        }
        
        return $layout;
    }
    
    public function resume()
    {
        $ilUser = $this->user;
        
        if ($ilUser->getId() != ANONYMOUS_USER_ID && $_GET["focus_id"] == "") {
            include_once("./Modules/LearningModule/classes/class.ilObjLearningModuleAccess.php");
            $last_accessed_page = ilObjLearningModuleAccess::_getLastAccessedPage((int) $_GET["ref_id"], $ilUser->getId());

            // if last accessed page was final page do nothing, start over
            if ($last_accessed_page &&
                $last_accessed_page != $this->lm_tree->getLastActivePage()) {
                $_GET["obj_id"] = $last_accessed_page;
            }
        }
            
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
            include_once("./Modules/LearningModule/exceptions/class.ilLMPresentationException.php");
            throw new ilLMPresentationException("ilLMPresentation: XML File invalid. Error reading " .
                $layout . "/" . $a_xml . ".");
        }
        $this->layout_doc = $doc;
        //echo ":".htmlentities($xmlfile).":$layout:$a_xml:";

        // get current frame node
        $xpc = xpath_new_context($doc);
        $path = (empty($_GET["frame"]) || ($_GET["frame"] == "_blank"))
            ? "/ilLayout/ilFrame[1]"
            : "//ilFrame[@name='" . $_GET["frame"] . "']";
        $result = xpath_eval($xpc, $path);
        $found = $result->nodeset;
        if (count($found) != 1) {
            include_once("./Modules/LearningModule/exceptions/class.ilLMPresentationException.php");
            throw new ilLMPresentationException("ilLMPresentation: XML File invalid. Found " . count($found) . " nodes for " .
                " path " . $path . " in " . $layout . "/" . $a_xml . ". LM Layout is " . $this->lm->getLayout());
        }
        $node = $found[0];

        // ProcessFrameset
        // node is frameset, if it has cols or rows attribute
        $attributes = $this->attrib2arr($node->attributes());

        $this->frames = array();
        if ((!empty($attributes["rows"])) || (!empty($attributes["cols"]))) {
            $content .= $this->buildTag("start", "frameset", $attributes);
            //$this->frames = array();
            $this->processNodes($content, $node);
            $content .= $this->buildTag("end", "frameset");
            $this->tpl = new ilTemplate("tpl.frameset.html", true, true, "Modules/LearningModule");
            $this->renderPageTitle();
            $this->tpl->setVariable("FS_CONTENT", $content);
            if (!$doshow) {
                $content = $this->tpl->get();
            }
        } else {	// node is frame -> process the content tags
            // ProcessContentTag
            //if ((empty($attributes["template"]) || !empty($_GET["obj_type"])))
            if ((empty($attributes["template"]) || !empty($_GET["obj_type"]))
                && ($_GET["frame"] != "_blank" || $_GET["obj_type"] != "MediaObject")) {
                // we got a variable content frame (can display different
                // object types (PageObject, MediaObject, GlossarItem)
                // and contains elements for them)

                // determine object type
                if (empty($_GET["obj_type"])) {
                    $obj_type = "PageObject";
                } else {
                    $obj_type = $_GET["obj_type"];
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
                    include_once("./Modules/LearningModule/exceptions/class.ilLMPresentationException.php");
                    throw new ilLMPresentationException("ilLMPresentation: No template specified for frame '" .
                        $_GET["frame"] . "' and object type '" . $obj_type . "'.");
                }
            }

            // get template
            $in_module = ($attributes["template_location"] == "module")
                ? true
                : false;
            if ($in_module) {
                $this->tpl = new ilTemplate($attributes["template"], true, true, $in_module);
                $this->tpl->setBodyClass("");
            } else {
                $this->tpl = $tpl;
            }

            // set style sheets
            if (!$this->offlineMode()) {
                $this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
            } else {
                $style_name = $ilUser->getPref("style") . ".css";
                $this->tpl->setVariable("LOCATION_STYLESHEET", "./style/" . $style_name);
            }
            
            include_once("./Services/jQuery/classes/class.iljQueryUtil.php");

            iljQueryUtil::initjQuery($this->tpl);
            iljQueryUtil::initjQueryUI($this->tpl);

            include_once("./Services/UICore/classes/class.ilUIFramework.php");
            ilUIFramework::init($this->tpl);

            // to make e.g. advanced seletions lists work:
            $GLOBALS["tpl"] = $this->tpl;

            $childs = $node->child_nodes();
            
            foreach ($childs as $child) {
                $child_attr = $this->attrib2arr($child->attributes());

                switch ($child->node_name()) {
                    case "ilMainMenu":
                        $this->ilMainMenu();
                        //$this->renderPageTitle();
                        break;

                    case "ilTOC":
                        $this->ilTOC($child_attr["target_frame"]);
                        break;

                    case "ilPage":
                        $this->renderPageTitle();
                        switch ($this->lm->getType()) {
                            case "lm":
                                unset($_SESSION["tr_id"]);
                                unset($_SESSION["bib_id"]);
                                unset($_SESSION["citation"]);
                                $content = $this->ilPage($child);
                                break;
                        }
                        break;

                    case "ilGlossary":
                        $content = $this->ilGlossary($child);
                        break;

                    case "ilLMNavigation":
                        $this->ilLMNavigation();
                        break;

                    case "ilMedia":
                        $this->ilMedia();
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
                        $this->ilLMMenu();
                        break;

                    case "ilLMHead":
                        $this->ilLMHead();
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
            //			if (strcmp($_GET["frame"], "topright") == 0) $this->tpl->fillJavaScriptFiles();
            //			if (strcmp($_GET["frame"], "right") == 0) $this->tpl->fillJavaScriptFiles();
            //			if (strcmp($_GET["frame"], "botright") == 0) $this->tpl->fillJavaScriptFiles();

            if (!$this->offlineMode()) {
                include_once("./Services/Accordion/classes/class.ilAccordionGUI.php");
                ilAccordionGUI::addJavaScript();
                ilAccordionGUI::addCss();

                
                $this->tpl->addJavascript("./Modules/LearningModule/js/LearningModule.js");
                include_once("./Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php");
                $close_call = "il.LearningModule.setCloseHTML('" . ilGlyphGUI::get(ilGlyphGUI::CLOSE) . "');";
                $this->tpl->addOnLoadCode($close_call);
                
                //$store->set("cf_".$this->lm->getId());
                
                // handle initial content
                if ($_GET["frame"] == "") {
                    include_once("./Services/Authentication/classes/class.ilSessionIStorage.php");
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
                $this->tpl->fillJavaScriptFiles();
                $this->tpl->fillScreenReaderFocus();

                $this->tpl->fillCssFiles();
            } else {
                // reset standard css files
                $this->tpl->resetJavascript();
                $this->tpl->resetCss();
                $this->tpl->setBodyClass("ilLMNoMenu");
                
                include_once("./Modules/LearningModule/classes/class.ilObjContentObject.php");
                foreach (ilObjContentObject::getSupplyingExportFiles() as $f) {
                    if ($f["type"] == "js") {
                        $this->tpl->addJavascript($f["target"]);
                    }
                    if ($f["type"] == "css") {
                        $this->tpl->addCSS($f["target"]);
                    }
                }
                $this->tpl->fillJavaScriptFiles(true);
                $this->tpl->fillCssFiles(true);
            }


            $this->tpl->fillBodyClass();
        }

        if ($doShow) {
            // (horrible) workaround for preventing template engine
            // from hiding paragraph text that is enclosed
            // in curly brackets (e.g. "{a}", see ilPageObjectGUI::showPage())
            
            $this->tpl->fillTabs();
            if ($this->fill_on_load_code) {
                $this->tpl->fillOnLoadCode();
                //$this->tpl->fillViewAppendInlineCss();
            }
            $content = $this->tpl->get();
            $content = str_replace("&#123;", "{", $content);
            $content = str_replace("&#125;", "}", $content);

            header('Content-type: text/html; charset=UTF-8');
            echo $content;
        } else {
            $this->tpl->fillLeftNav();
            $this->tpl->fillOnLoadCode();
            //$this->tpl->fillViewAppendInlineCss();
            $content = $this->tpl->get();
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
        include_once("./Services/Authentication/classes/class.ilSessionIStorage.php");
        $store = new ilSessionIStorage("lm");
        if ($_GET["url"] != "") {
            $store->set("cf_" . $this->lm->getId(), $_GET["url"]);
        } else {
            $store->set("cf_" . $this->lm->getId(), $_GET["url"]);
        }
    }
    

    public function fullscreen()
    {
        return $this->layout("fullscreen.xml", !$this->offlineMode());
    }

    public function media()
    {
        if ($_GET["frame"] != "_blank") {
            return $this->layout("main.xml", !$this->offlineMode());
        } else {
            return $this->layout("fullscreen.xml", !$this->offlineMode());
        }
    }

    public function glossary()
    {
        global $DIC;

        $ilUser = $this->user;
        
        if ($_GET["frame"] != "_blank") {
            $this->layout();
        } else {
            $this->tpl = new ilTemplate("tpl.glossary_term_output.html", true, true, true);
            $GLOBALS["tpl"] = $this->tpl;
            $this->renderPageTitle();

            // set style sheets
            if (!$this->offlineMode()) {
                $this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
            } else {
                $style_name = $ilUser->getPref("style") . ".css";
                ;
                $this->tpl->setVariable("LOCATION_STYLESHEET", "./style/" . $style_name);
            }

            $this->ilGlossary($child);
            if (!$this->offlineMode()) {
                $this->tpl->show();
            } else {
                return $this->tpl->get();
            }
        }
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
            include_once './Services/LTI/classes/class.ilMainMenuGUI.php';
            $ilMainMenu = new LTI\ilMainMenuGUI("_top", false, $this->tpl);
        } else {
            include_once './Services/MainMenu/classes/class.ilMainMenuGUI.php';
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
        include_once("./Modules/LearningModule/classes/class.ilLMTOCExplorerGUI.php");
        $exp = new ilLMTOCExplorerGUI($this, "ilTOC", $this, $this->lang, $this->focus_id, $this->export_all_languages);
        $exp->setMainTemplate($this->tpl);
        $exp->setTracker($this->getTracker());
        // determine highlighted and force open nodes
        $page_id = $this->getCurrentPageId();
        if ($this->deactivated_page) {
            $page_id = $_GET["obj_id"];
        }
        if ($page_id > 0) {
            $exp->setPathOpen((int) $page_id);
        }
        // empty chapter
        if ($this->chapter_has_no_active_page &&
            ilLMObject::_lookupType($_GET["obj_id"]) == "st") {
            $exp->setHighlightNode($_GET["obj_id"]);
        } else {
            if ($this->lm->getTOCMode() == "pages") {
                if ($this->deactivated_page) {
                    $exp->setHighlightNode($_GET["obj_id"]);
                } else {
                    $exp->setHighlightNode($page_id);
                }
            } else {
                $exp->setHighlightNode($this->lm_tree->getParentId($page_id));
            }
        }
        if ($this->offlineMode()) {
            $exp->setOfflineMode(true);
        }
        if (!$exp->handleCommand()) {

            if ($a_get_explorer) {
                return $exp;
            } else {
                $this->tpl->setCurrentBlock("il_toc");
                $this->tpl->setVariable("EXPLORER", $exp->getHTML());
                $this->tpl->parseCurrentBlock();
            }
        }
    }

    /**
     * Get lm presentationtitle
     *
     * @param
     * @return
     */
    public function getLMPresentationTitle()
    {
        if ($this->offlineMode() && $this->lang != "" && $this->lang != "-") {
            include_once("./Services/Object/classes/class.ilObjectTranslation.php");
            $ot = ilObjectTranslation::getInstance($this->lm->getId());
            $data = $ot->getLanguages();
            $ltitle = $data[$this->lang]["title"];
            if ($ltitle != "") {
                return $ltitle;
            }
        }
        return $this->lm->getTitle();
    }


    /**
    * output learning module menu
    */
    public function ilLMMenu()
    {
        $this->tpl->setVariable("MENU", $this->lm_gui->setilLMMenu(
            $this->offlineMode(),
            $this->getExportFormat(),
            "content",
            false,
            true,
            $this->getCurrentPageId(),
            $this->lang,
            $this->export_all_languages
        ));
    }

    /**
    * output lm header
    */
    public function ilLMHead()
    {
        $this->tpl->setCurrentBlock("header_image");
        if ($this->offlineMode()) {
            $this->tpl->setVariable("IMG_HEADER", "./images/icon_lm.svg");
        } else {
            $this->tpl->setVariable("IMG_HEADER", ilUtil::getImagePath("icon_lm.svg"));
        }
        $this->tpl->parseCurrentBlock();
        $this->tpl->setCurrentBlock("lm_head");
        $this->tpl->setVariable("HEADER", $this->getLMPresentationTitle());
        $this->tpl->parseCurrentBlock();
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


        include_once("./Services/UICore/classes/class.ilTemplate.php");
        $tpl_menu = new ilTemplate("tpl.lm_sub_menu.html", true, true, true);

        $pg_id = $this->getCurrentPageId();
        if ($pg_id == 0) {
            return;
        }

        // edit learning module
        if (!$this->offlineMode()) {
            if ($rbacsystem->checkAccess("write", $_GET["ref_id"])) {
                $tpl_menu->setCurrentBlock("edit_page");
                $page_id = $this->getCurrentPageId();
                $tpl_menu->setVariable("EDIT_LINK", ILIAS_HTTP_PATH . "/ilias.php?baseClass=ilLMEditorGUI&ref_id=" . $_GET["ref_id"] .
                    "&obj_id=" . $page_id . "&to_page=1");
                $tpl_menu->setVariable("EDIT_TXT", $this->lng->txt("edit_page"));
                $tpl_menu->setVariable("EDIT_TARGET", $buttonTarget);
                $tpl_menu->parseCurrentBlock();
            }

            $page_id = $this->getCurrentPageId();
            
            include_once("./Services/PermanentLink/classes/class.ilPermanentLinkGUI.php");
            $plinkgui = new ilPermanentLinkGUI(
                "pg",
                $page_id . "_" . $this->lm->getRefId(),
                "",
                "_top"
            );

            $title = $this->getLMPresentationTitle();
            $pg_title = ilLMPageObject::_getPresentationTitle(
                $page_id,
                $this->lm->getPageHeader(),
                $this->lm->isActiveNumbering(),
                $this->lm_set->get("time_scheduled_page_activation"),
                false,
                0,
                $this->lang
            );
            if ($pg_title != "") {
                $title .= ": " . $pg_title;
            }
            
            $plinkgui->setTitle($title);
                
            $tpl_menu->setCurrentBlock("perma_link");
            $tpl_menu->setVariable("PERMA_LINK", $plinkgui->getHTML());
            $tpl_menu->parseCurrentBlock();
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
        $ilAccess = $this->access;
        $tpl = $this->tpl;
        
        include_once "Services/Object/classes/class.ilCommonActionDispatcherGUI.php";
        $dispatcher = new ilCommonActionDispatcherGUI(
            ilCommonActionDispatcherGUI::TYPE_REPOSITORY,
            $ilAccess,
            $this->lm->getType(),
            $_GET["ref_id"],
            $this->lm->getId()
        );
        $dispatcher->setSubObject("pg", $this->getCurrentPageId());

        include_once "Services/Object/classes/class.ilObjectListGUI.php";
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
    public function ilLMNotes()
    {
        $ilAccess = $this->access;
        $ilSetting = $this->settings;
        
        
        // no notes in offline (export) mode
        if ($this->offlineMode()) {
            return;
        }
        
        // output notes (on top)
        
        if (!$ilSetting->get("disable_notes")) {
            $this->addHeaderAction();
        }
        
        // now output comments
        
        if ($ilSetting->get("disable_comments")) {
            return;
        }

        if (!$this->lm->publicNotes()) {
            return;
        }
        
        $next_class = $this->ctrl->getNextClass($this);

        include_once("Services/Notes/classes/class.ilNoteGUI.php");
        $pg_id = $this->getCurrentPageId();
        if ($pg_id == 0) {
            return;
        }
        
        $notes_gui = new ilNoteGUI($this->lm->getId(), $this->getCurrentPageId(), "pg");
        
        if ($ilAccess->checkAccess("write", "", $_GET["ref_id"]) &&
            $ilSetting->get("comments_del_tutor", 1)) {
            $notes_gui->enablePublicNotesDeletion(true);
        }
        
        $this->ctrl->setParameter($this, "frame", $_GET["frame"]);
        $this->ctrl->setParameter($this, "obj_id", $_GET["obj_id"]);
        
        $notes_gui->enablePrivateNotes();
        if ($this->lm->publicNotes()) {
            $notes_gui->enablePublicNotes();
        }

        if ($next_class == "ilnotegui") {
            $html = $this->ctrl->forwardCommand($notes_gui);
        } else {
            $html = $notes_gui->getNotesHTML();
        }
        $this->tpl->setVariable("NOTES", $html);
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

        require_once("./Modules/LearningModule/classes/class.ilStructureObject.php");

        if (empty($_GET["obj_id"])) {
            $a_id = $this->lm_tree->getRootId();
        } else {
            $a_id = $_GET["obj_id"];
        }

        if (!$a_std_templ_loaded) {
            $this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
        }
        
        if (!$this->lm->cleanFrames()) {
            $frame_param = $_GET["frame"];
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
                $ilLocator->addItem("...", "");

                $par_id = $tree->getParentId($_GET["ref_id"]);
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $par_id);
                $ilLocator->addItem(
                    ilObject::_lookupTitle(ilObject::_lookupObjId($par_id)),
                    $ilCtrl->getLinkTargetByClass("ilrepositorygui", "frameset"),
                    ilFrameTargetInfo::_getFrame("MainContent"),
                    $par_id
                );
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
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
                                    IL_CHAPTER_TITLE,
                                    $this->lm->isActiveNumbering(),
                                    $this->lm_set->get("time_scheduled_page_activation"),
                                    false,
                                    0,
                                    $this->lang
                                ),
                                50,
                                true
                            ),
                            $this->getLink($_GET["ref_id"], "layout", $row["child"], $frame_param, "StructureObject"),
                            $frame_target
                        );
                    } else {
                        $ilLocator->addItem(
                            ilUtil::shortenText($this->getLMPresentationTitle(), 50, true),
                            $this->getLink($_GET["ref_id"], "layout", "", $frame_param),
                            $frame_target,
                            $_GET["ref_id"]
                        );
                    }
                }
            }
        } else {		// lonely page
            $ilLocator->addItem(
                $this->getLMPresentationTitle(),
                $this->getLink($_GET["ref_id"], "layout", "", $_GET["frame"])
            );

            require_once("./Modules/LearningModule/classes/class.ilLMObjectFactory.php");
            $lm_obj = ilLMObjectFactory::getInstance($this->lm, $a_id);

            $ilLocator->addItem(
                $lm_obj->getTitle(),
                $this->getLink($_GET["ref_id"], "layout", $a_id, $frame_param),
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
        $ilUser = $this->user;

        if (!$this->offlineMode() && $this->current_page_id !== false) {
            return $this->current_page_id;
        }

        include_once("./Modules/LearningModule/classes/class.ilLMPage.php");
        
        $this->chapter_has_no_active_page = false;
        $this->deactivated_page = false;
        
        // determine object id
        if (empty($_GET["obj_id"])) {
            $obj_id = $this->lm_tree->getRootId();
        } else {
            $obj_id = $_GET["obj_id"];
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
                if (!in_array($_GET["obj_id"], $path)) {
                    $this->chapter_has_no_active_page = true;
                }
            }
        }

        $this->current_page_id = $page_id;
        return $page_id;
    }

    public function ilCitation()
    {
        $page_id = $this->getCurrentPageId();
        $this->tpl = new ilTemplate("tpl.page.html", true, true, true);
        $this->ilLocator();
        $this->tpl->setVariable("MENU", $this->lm_gui->setilCitationMenu());

        include_once("./Modules/LearningModule/classes/class.ilLMPage.php");

        $this->pg_obj = $this->getLMPage($page_id);
        $xml = $this->pg_obj->getXMLContent();
        $this->lm_gui->showCitation($xml);
        $this->tpl->show();
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

        return $targets;
    }

    /**
     * process <ilPage> content tag
     *
     * @param $a_page_node page node
     * @param int $a_page_id header / footer page id
     * @return string
     */
    public function ilPage(&$a_page_node, $a_page_id = 0)
    {
        $access = $this->access;

        $ilUser = $this->user;
        $ilHelp = $this->help;


        $ilHelp = $this->help;
        $ilHelp->setScreenIdComponent("lm");
        $ilHelp->setScreenId("content");
        $ilHelp->setSubScreenId("content");

        $this->fill_on_load_code = true;


        // check page id
        $requested_page_lm = ilLMPage::lookupParentId($this->getCurrentPageId(), "lm");
        if ($requested_page_lm != $this->lm->getId()) {
            if ($_REQUEST["frame"] == "") {
                $this->showNoPageAccess();
                return "";
            } else {
                $read_access = false;
                foreach (ilObject::_getAllReferences($requested_page_lm) as $ref_id) {
                    if ($access->checkAccess("read", "", $ref_id)) {
                        $read_access = true;
                    }
                }
                if (!$read_access) {
                    $this->showNoPageAccess();
                    return "";
                }
            }
        }

        // check if page is (not) visible in public area
        if ($ilUser->getId() == ANONYMOUS_USER_ID &&
           $this->lm_gui->object->getPublicAccessMode() == 'selected') {
            $public = ilLMObject::_isPagePublic($this->getCurrentPageId());

            if (!$public) {
                return $this->showNoPublicAccess($this->getCurrentPageId());
            }
        }

        if (!ilObjContentObject::_checkPreconditionsOfPage($this->lm->getRefId(), $this->lm->getId(), $this->getCurrentPageId())) {
            return $this->showPreconditionsOfPage($this->getCurrentPageId());
        }

        // if navigation is restricted based on correct answered questions
        // check if we have preceeding pages including unsanswered/incorrect answered questions
        if (!$this->offlineMode()) {
            if ($this->lm->getRestrictForwardNavigation()) {
                if ($this->getTracker()->hasPredIncorrectAnswers($this->getCurrentPageId())) {
                    $this->showNavRestrictionDueToQuestions();
                    return;
                }
            }
        }


        require_once("./Modules/LearningModule/classes/class.ilLMPageGUI.php");
        require_once("./Modules/LearningModule/classes/class.ilLMPageObject.php");
        
        // page id is e.g. > 0 when footer or header page is processed
        if ($a_page_id == 0) {
            $page_id = $this->getCurrentPageId();
            //echo ":".$page_id.":";
            // highlighting?
            
            if ($_GET["srcstring"] != "" && !$this->offlineMode()) {
                include_once './Services/Search/classes/class.ilUserSearchCache.php';
                $cache = ilUserSearchCache::_getInstance($ilUser->getId());
                $cache->switchSearchType(ilUserSearchCache::LAST_QUERY);
                $search_string = $cache->getQuery();
                
                // advanced search?
                if (is_array($search_string)) {
                    $search_string = $search_string["lom_content"];
                }
    
                include_once("./Services/UIComponent/TextHighlighter/classes/class.ilTextHighlighterGUI.php");
                include_once("./Services/Search/classes/class.ilQueryParser.php");
                $p = new ilQueryParser($search_string);
                $p->parse();
                
                $words = $p->getQuotedWords();
                if (is_array($words)) {
                    foreach ($words as $w) {
                        ilTextHighlighterGUI::highlight("ilLMPageContent", $w, $this->tpl);
                    }
                }

                $this->fill_on_load_code = true;
            }
        } else {
            $page_id = $a_page_id;
        }

        // content style
        $this->tpl->setCurrentBlock("ContentStyle");
        if (!$this->offlineMode()) {
            $this->tpl->setVariable(
                "LOCATION_CONTENT_STYLESHEET",
                ilObjStyleSheet::getContentStylePath($this->lm->getStyleSheetId())
            );
        } else {
            $this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET", "content_style/content.css");
        }
        $this->tpl->parseCurrentBlock();

        // no active page found in chapter
        if ($this->chapter_has_no_active_page &&
            ilLMObject::_lookupType($_GET["obj_id"]) == "st") {
            $mtpl = new ilTemplate(
                "tpl.no_content_message.html",
                true,
                true,
                "Modules/LearningModule"
            );
            $mtpl->setVariable("MESSAGE", $this->lng->txt("cont_no_page_in_chapter"));
            //$mtpl->setVariable("SRC_ICON", ilUtil::getImagePath("icon_st.svg",
            //	false, "output", $this->offlineMode()));
            $mtpl->setVariable(
                "ITEM_TITLE",
                ilLMObject::_lookupTitle($_GET["obj_id"])
            );
            $this->tpl->setVariable("PAGE_CONTENT", $mtpl->get());
            return $mtpl->get();
        }
        
        // current page is deactivated
        if ($this->deactivated_page) {
            $mtpl = new ilTemplate(
                "tpl.no_content_message.html",
                true,
                true,
                "Modules/LearningModule"
            );
            $m = $this->lng->txt("cont_page_currently_deactivated");
            $act_data = ilLMPage::_lookupActivationData((int) $_GET["obj_id"], $this->lm->getType());
            if ($act_data["show_activation_info"] &&
                (ilUtil::now() < $act_data["activation_start"])) {
                $m .= "<p>" . sprintf(
                    $this->lng->txt("cont_page_activation_on"),
                    ilDatePresentation::formatDate(
                        new ilDateTime($act_data["activation_start"], IL_CAL_DATETIME)
                    )
                ) .
                    "</p>";
            }
            $mtpl->setVariable("MESSAGE", $m);
            //$mtpl->setVariable("SRC_ICON", ilUtil::getImagePath("icon_pg.svg",
            //	false, "output", $this->offlineMode()));
            $mtpl->setVariable(
                "ITEM_TITLE",
                ilLMObject::_lookupTitle($_GET["obj_id"])
            );
            $this->tpl->setVariable("PAGE_CONTENT", $mtpl->get());
            return $mtpl->get();
        }

        // check if page is out of focus
        $focus_mess = "";
        if ($this->focus_id > 0) {
            $path = $this->lm_tree->getPathId($page_id);

            // out of focus
            if (!in_array($this->focus_id, $path)) {
                $mtpl = new ilTemplate(
                    "tpl.out_of_focus_message.html",
                    true,
                    true,
                    "Modules/LearningModule"
                );
                $mtpl->setVariable("MESSAGE", $this->lng->txt("cont_out_of_focus_message"));
                $mtpl->setVariable("TXT_SHOW_CONTENT", $this->lng->txt("cont_show_content_after_focus"));

                if ($_GET["focus_return"] == "" || ilObject::_lookupType((int) $_GET["focus_return"], true) != "crs") {
                    $mtpl->setVariable("TXT_BACK_BEGINNING", $this->lng->txt("cont_to_focus_beginning"));
                    $this->ctrl->setParameter($this, "obj_id", $this->focus_id);
                    $mtpl->setVariable("LINK_BACK_TO_BEGINNING", $this->ctrl->getLinkTarget($this, "layout"));
                    $this->ctrl->setParameter($this, "obj_id", $_GET["obj_id"]);
                } else {
                    $mtpl->setVariable("TXT_BACK_BEGINNING", $this->lng->txt("cont_to_focus_return_crs"));
                    include_once("./Services/Link/classes/class.ilLink.php");
                    $mtpl->setVariable("LINK_BACK_TO_BEGINNING", ilLink::_getLink((int) $_GET["focus_return"]));
                }

                $this->ctrl->setParameter($this, "focus_id", "");
                $mtpl->setVariable("LINK_SHOW_CONTENT", $this->ctrl->getLinkTarget($this, "layout"));
                $this->ctrl->setParameter($this, "focus_id", $_GET["focus_id"]);

                $focus_mess = $mtpl->get();
            } else {
                $sp = $this->getSuccessorPage();
                $path2 = array();
                if ($sp > 0) {
                    $path2 = $this->lm_tree->getPathId($this->getSuccessorPage());
                }
                if ($sp == 0 || !in_array($this->focus_id, $path2)) {
                    $mtpl = new ilTemplate(
                        "tpl.out_of_focus_message.html",
                        true,
                        true,
                        "Modules/LearningModule"
                    );
                    $mtpl->setVariable("MESSAGE", $this->lng->txt("cont_out_of_focus_message_last_page"));
                    $mtpl->setVariable("TXT_SHOW_CONTENT", $this->lng->txt("cont_show_content_after_focus"));

                    if ($_GET["focus_return"] == "" || ilObject::_lookupType((int) $_GET["focus_return"], true) != "crs") {
                        $mtpl->setVariable("TXT_BACK_BEGINNING", $this->lng->txt("cont_to_focus_beginning"));
                        $this->ctrl->setParameter($this, "obj_id", $this->focus_id);
                        $mtpl->setVariable("LINK_BACK_TO_BEGINNING", $this->ctrl->getLinkTarget($this, "layout"));
                        $this->ctrl->setParameter($this, "obj_id", $_GET["obj_id"]);
                    } else {
                        $mtpl->setVariable("TXT_BACK_BEGINNING", $this->lng->txt("cont_to_focus_return_crs"));
                        include_once("./Services/Link/classes/class.ilLink.php");
                        $mtpl->setVariable("LINK_BACK_TO_BEGINNING", ilLink::_getLink((int) $_GET["focus_return"]));
                    }

                    $this->ctrl->setParameter($this, "focus_id", "");
                    $mtpl->setVariable("LINK_SHOW_CONTENT", $this->ctrl->getLinkTarget($this, "layout"));
                    $this->ctrl->setParameter($this, "focus_id", $_GET["focus_id"]);

                    $focus_mess = $mtpl->get();
                }
            }
        }
        
        // no page found
        if ($page_id == 0) {
            $cont = $this->lng->txt("cont_no_page");
            $this->tpl->setVariable("PAGE_CONTENT", $cont);
            return $cont;
        }
        

        $page_object_gui = $this->getLMPageGUI($page_id);
        $this->basicPageGuiInit($page_object_gui);
        $page_object = $page_object_gui->getPageObject();
        $page_object->buildDom();
        $page_object->registerOfflineHandler($this);
        
        $int_links = $page_object->getInternalLinks();



        $page_object_gui->setTemplateOutput(false);
        
        // Update personal desktop items
        $ilUser->setDesktopItemParameters($this->lm->getRefId(), $this->lm->getType(), $page_id);

        // Update course items
        include_once './Modules/Course/classes/class.ilCourseLMHistory.php';
        ilCourseLMHistory::_updateLastAccess($ilUser->getId(), $this->lm->getRefId(), $page_id);

        // read link targets
        $link_xml = $this->getLinkXML($int_links, $this->getLayoutLinkTargets());
        $link_xml .= $this->getLinkTargetsXML();

        // get lm page object
        $lm_pg_obj = new ilLMPageObject($this->lm, $page_id);
        $lm_pg_obj->setLMId($this->lm->getId());
        //$pg_obj->setParentId($this->lm->getId());
        $page_object_gui->setLinkXML($link_xml);
        
        // determine target frames for internal links
        //$pg_frame = $_GET["frame"];
        $page_object_gui->setLinkFrame($_GET["frame"]);
        
        // page title and tracking (not for header or footer page)
        if ($page_id == 0 || ($page_id != $this->lm->getHeaderPage() &&
            $page_id != $this->lm->getFooterPage())) {
            $page_object_gui->setPresentationTitle(
                ilLMPageObject::_getPresentationTitle(
                    $lm_pg_obj->getId(),
                    $this->lm->getPageHeader(),
                    $this->lm->isActiveNumbering(),
                    $this->lm_set->get("time_scheduled_page_activation"),
                    false,
                    0,
                    $this->lang
                )
            );

            // track access
            if ($page_id != 0 && !$this->offlineMode()) {
                $this->getTracker()->trackAccess($page_id, $ilUser->getId());
            }
        } else {
            $page_object_gui->setEnabledPageFocus(false);
            $page_object_gui->getPageConfig()->setEnableSelfAssessment(false);
        }

        // ADDED FOR CITATION
        $page_object_gui->setLinkParams("ref_id=" . $this->lm->getRefId());
        $page_object_gui->setTemplateTargetVar("PAGE_CONTENT");
        $page_object_gui->setSourcecodeDownloadScript($this->getSourcecodeDownloadLink());

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


        $ret = $page_object_gui->presentation($page_object_gui->getOutputMode());
                
        // process header
        if ($this->lm->getHeaderPage() > 0 &&
            $page_id != $this->lm->getHeaderPage() &&
            ($page_id == 0 || $page_id != $this->lm->getFooterPage())) {
            if (ilLMObject::_exists($this->lm->getHeaderPage())) {
                $head = $this->ilPage($a_page_node, $this->lm->getHeaderPage());
            }
        }

        // process footer
        if ($this->lm->getFooterPage() > 0 &&
            $page_id != $this->lm->getFooterPage() &&
            ($page_id == 0 || $page_id != $this->lm->getHeaderPage())) {
            if (ilLMObject::_exists($this->lm->getFooterPage())) {
                $foot = $this->ilPage($a_page_node, $this->lm->getFooterPage());
            }
        }
        
        // rating
        $rating = "";
        if ($this->lm->hasRatingPages() && !$this->offlineMode()) {
            include_once("./Services/Rating/classes/class.ilRatingGUI.php");
            $rating_gui = new ilRatingGUI();
            $rating_gui->setObject($this->lm->getId(), "lm", $page_id, "lm");
            $rating_gui->setYourRatingText($this->lng->txt("lm_rate_page"));
            
            /*
                $this->tpl->setVariable("VAL_RATING", $rating->getHTML(false, true,
                    "il.ExcPeerReview.saveComments(".$a_set["peer_id"].", %rating%)"));
            */
                        
            $this->ctrl->setParameter($this, "pgid", $page_id);
            $this->tpl->addOnLoadCode("il.LearningModule.setRatingUrl('" .
                $this->ctrl->getLinkTarget($this, "updatePageRating", "", true, false) .
                "')");
            $this->ctrl->setParameter($this, "pgid", "");
            
            $rating = '<div id="ilrtrpg" style="text-align:right">' .
                $rating_gui->getHtml(true, true, "il.LearningModule.saveRating(%rating%);") .
                "</div>";
        }
        
        $this->tpl->setVariable("PAGE_CONTENT", $rating . $head . $focus_mess . $ret . $foot);
        //echo htmlentities("-".$ret."-");
        return $head . $focus_mess . $ret . $foot;
    }
    
    public function updatePageRating()
    {
        $ilUser = $this->user;
        
        $pg_id = $_GET["pgid"];
        if (!$this->ctrl->isAsynch() || !$pg_id) {
            exit();
        }
                
        include_once './Services/Rating/classes/class.ilRating.php';
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
        
        include_once './Services/Rating/classes/class.ilRatingGUI.php';
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
        include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
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
        $a_page_gui->setFileDownloadLink($this->getLink($_GET["ref_id"], "downloadFile"));
        if (!$this->offlineMode()) {
            $this->ctrl->setParameter($this, "obj_id", $_GET["obj_id"]);
        }
        $a_page_gui->setFullscreenLink($this->getLink($_GET["ref_id"], "fullscreen"));
    }

    /**
    * show preconditions of the page
    */
    public function showPreconditionsOfPage()
    {
        $conds = ilObjContentObject::_getMissingPreconditionsOfPage($this->lm->getRefId(), $this->lm->getId(), $this->getCurrentPageId());
        $topchap = ilObjContentObject::_getMissingPreconditionsTopChapter($this->lm->getRefId(), $this->lm->getId(), $this->getCurrentPageId());

        $page_id = $this->getCurrentPageId();

        // content style
        $this->tpl->setCurrentBlock("ContentStyle");
        if (!$this->offlineMode()) {
            $this->tpl->setVariable(
                "LOCATION_CONTENT_STYLESHEET",
                ilObjStyleSheet::getContentStylePath($this->lm->getStyleSheetId())
            );
        } else {
            $this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET", "content_style/content.css");
        }
        $this->tpl->parseCurrentBlock();

        $this->tpl->addBlockFile("PAGE_CONTENT", "pg_content", "tpl.page_preconditions.html", true);
        
        // list all missing preconditions
        include_once("./Services/Repository/classes/class.ilRepositoryExplorer.php");
        foreach ($conds as $cond) {
            include_once("./Services/Link/classes/class.ilLink.php");
            $obj_link = ilLink::_getLink($cond["trigger_ref_id"]);
            $this->tpl->setCurrentBlock("condition");
            $this->tpl->setVariable("VAL_ITEM", ilObject::_lookupTitle($cond["trigger_obj_id"]));
            $this->tpl->setVariable("LINK_ITEM", $obj_link);
            if ($cond["operator"] == "passed") {
                $cond_str = $this->lng->txt("passed");
            } else {
                $cond_str = $this->lng->txt("condition_" . $cond["operator"]);
            }
            $this->tpl->setVariable("VAL_CONDITION", $cond_str . " " . $cond["value"]);
            $this->tpl->parseCurrentBlock();
        }
        $this->tpl->setCurrentBlock("pg_content");
        
        $this->tpl->setVariable(
            "TXT_MISSING_PRECONDITIONS",
            sprintf(
                $this->lng->txt("cont_missing_preconditions"),
                ilLMObject::_lookupTitle($topchap)
            )
        );
        $this->tpl->setVariable("TXT_ITEM", $this->lng->txt("object"));
        $this->tpl->setVariable("TXT_CONDITION", $this->lng->txt("condition"));
        
        // output skip chapter link
        $parent = $this->lm_tree->getParentId($topchap);
        $childs = $this->lm_tree->getChildsByType($parent, "st");
        $next = "";
        $j = -2;
        $i = 1;
        foreach ($childs as $child) {
            if ($child["child"] == $topchap) {
                $j = $i;
            }
            if ($i++ == ($j + 1)) {
                $succ_node = $this->lm_tree->fetchSuccessorNode($child["child"], "pg");
            }
        }
        if ($succ_node != "") {
            $framestr = (!empty($_GET["frame"]))
                ? "frame=" . $_GET["frame"] . "&"
                : "";

            $showViewInFrameset = true;
            $link = "<br /><a href=\"" .
                $this->getLink($this->lm->getRefId(), "layout", $succ_node["obj_id"], $_GET["frame"]) .
                "\">" . $this->lng->txt("cont_skip_chapter") . "</a>";
            $this->tpl->setVariable("LINK_SKIP_CHAPTER", $link);
        }
        
        $this->tpl->parseCurrentBlock();
    }
    
    /**
    * get xml for links
    */
    public function getLinkXML($a_int_links, $a_layoutframes)
    {
        $ilCtrl = $this->ctrl;

        // Determine whether the view of a learning resource should
        // be shown in the frameset of ilias, or in a separate window.
        $showViewInFrameset = true;

        if ($a_layoutframes == "") {
            $a_layoutframes = array();
        }
        $link_info = "<IntLinkInfos>";
        foreach ($a_int_links as $int_link) {
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
                $lcontent = "";
                switch ($type) {
                    case "PageObject":
                    case "StructureObject":
                        $lm_id = ilLMObject::_lookupContObjID($target_id);
                        if ($lm_id == $this->lm->getId() ||
                            ($targetframe != "None" && $targetframe != "New")) {
                            $ltarget = $a_layoutframes[$targetframe]["Frame"];
                            //$nframe = ($ltarget == "")
                            //	? $_GET["frame"]
                            //	: $ltarget;
                            $nframe = ($ltarget == "")
                                ? ""
                                : $ltarget;
                            if ($ltarget == "") {
                                if ($showViewInFrameset) {
                                    $ltarget = "_parent";
                                } else {
                                    $ltarget = "_top";
                                }
                            }
                            // scorm always in 1window view and link target
                            // is always same frame
                            if ($this->getExportFormat() == "scorm" &&
                                $this->offlineMode()) {
                                $ltarget = "";
                            }
                            $href =
                                $this->getLink(
                                    $_GET["ref_id"],
                                    "layout",
                                    $target_id,
                                    $nframe,
                                    $type,
                                    "append",
                                    $anc
                                );
                            if ($lm_id == "") {
                                $href = "";
                            }
                        } else {
                            if (!$this->offlineMode()) {
                                if ($type == "PageObject") {
                                    $href = "./goto.php?target=pg_" . $target_id . $anc_add;
                                } else {
                                    $href = "./goto.php?target=st_" . $target_id;
                                }
                            } else {
                                if ($type == "PageObject") {
                                    $href = ILIAS_HTTP_PATH . "/goto.php?target=pg_" . $target_id . $anc_add . "&amp;client_id=" . CLIENT_ID;
                                } else {
                                    $href = ILIAS_HTTP_PATH . "/goto.php?target=st_" . $target_id . "&amp;client_id=" . CLIENT_ID;
                                }
                            }
                            if ($targetframe != "New") {
                                $ltarget = ilFrameTargetInfo::_getFrame("MainContent");
                            } else {
                                $ltarget = "_blank";
                            }
                        }
                        break;

                    case "GlossaryItem":
                        if ($targetframe == "None") {
                            $targetframe = "Glossary";
                        }
                        $ltarget = $a_layoutframes[$targetframe]["Frame"];
                        $nframe = ($ltarget == "")
                            ? $_GET["frame"]
                            : $ltarget;
                        $href =
                            $this->getLink($_GET["ref_id"], $a_cmd = "glossary", $target_id, $nframe, $type);
                        break;

                    case "MediaObject":
                        $ltarget = $a_layoutframes[$targetframe]["Frame"];
                        $nframe = ($ltarget == "")
                            ? $_GET["frame"]
                            : $ltarget;
                        $href =
                            $this->getLink($_GET["ref_id"], $a_cmd = "media", $target_id, $nframe, $type);
                        break;

                    case "RepositoryItem":
                        $obj_type = ilObject::_lookupType($target_id, true);
                        $obj_id = ilObject::_lookupObjId($target_id);
                        if (!$this->offlineMode()) {
                            $href = "./goto.php?target=" . $obj_type . "_" . $target_id;
                        } else {
                            $href = ILIAS_HTTP_PATH . "/goto.php?target=" . $obj_type . "_" . $target_id . "&amp;client_id=" . CLIENT_ID;
                        }
                        $ltarget = ilFrameTargetInfo::_getFrame("MainContent");
                        break;
                        
                    case "WikiPage":
                        include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
                        $href = ilWikiPage::getGotoForWikiPageTarget($target_id);
                        break;

                    case "File":
                        if (!$this->offlineMode()) {
                            $ilCtrl->setParameter($this, "obj_id", $this->getCurrentPageId());
                            $ilCtrl->setParameter($this, "file_id", "il__file_" . $target_id);
                            $href = $ilCtrl->getLinkTarget($this, "downloadFile");
                            $ilCtrl->setParameter($this, "file_id", "");
                            $ilCtrl->setParameter($this, "obj_id", $_GET["obj_id"]);
                        }
                        break;

                    case "User":
                        $obj_type = ilObject::_lookupType($target_id);
                        if ($obj_type == "usr") {
                            include_once("./Services/User/classes/class.ilUserUtil.php");
                            $back = $this->ctrl->getLinkTarget($this, "layout");
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

                if ($href != "") {
                    $link_info .= "<IntLinkInfo Target=\"$target\" Type=\"$type\" " .
                        "TargetFrame=\"$targetframe\" LinkHref=\"$href\" LinkTarget=\"$ltarget\" LinkContent=\"$lcontent\" $anc_par/>";
                }

                // set equal link info for glossary links of target "None" and "Glossary"
                /*
                if ($targetframe=="None" && $type=="GlossaryItem")
                {
                    $link_info.="<IntLinkInfo Target=\"$target\" Type=\"$type\" ".
                        "TargetFrame=\"Glossary\" LinkHref=\"$href\" LinkTarget=\"$ltarget\" />";
                }*/
            }
        }
        $link_info .= "</IntLinkInfos>";
        return $link_info;
    }

    /**
    * Get XMl for Link Targets
    */
    public function getLinkTargetsXML()
    {
        $link_info = "<LinkTargets>";
        foreach ($this->getLayoutLinkTargets() as $k => $t) {
            $link_info .= "<LinkTarget TargetFrame=\"" . $t["Type"] . "\" LinkTarget=\"" . $t["Frame"] . "\" OnClick=\"" . $t["OnClick"] . "\" />";
        }
        $link_info .= "</LinkTargets>";
        return $link_info;
    }

    /**
    * show glossary term
    */
    public function ilGlossary()
    {
        $ilCtrl = $this->ctrl;

        require_once("./Modules/Glossary/classes/class.ilGlossaryTermGUI.php");
        $term_gui = new ilGlossaryTermGUI($_GET["obj_id"]);

        // content style
        $this->tpl->setCurrentBlock("ContentStyle");
        if (!$this->offlineMode()) {
            $this->tpl->setVariable(
                "LOCATION_CONTENT_STYLESHEET",
                ilObjStyleSheet::getContentStylePath($this->lm->getStyleSheetId())
            );
        } else {
            $this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET", "content_style/content.css");
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

        $int_links = $term_gui->getInternalLinks();
        $link_xml = $this->getLinkXML($int_links, $this->getLayoutLinkTargets());
        $link_xml .= $this->getLinkTargetsXML();
        $term_gui->setLinkXML($link_xml);

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
        $ilUser = $this->user;

        $this->tpl->setCurrentBlock("ContentStyle");
        if (!$this->offlineMode()) {
            $this->tpl->setVariable(
                "LOCATION_CONTENT_STYLESHEET",
                ilObjStyleSheet::getContentStylePath($this->lm->getStyleSheetId())
            );
        } else {
            $this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET", "content_style/content.css");
        }
        $this->tpl->parseCurrentBlock();

        $this->renderPageTitle();
        
        // set style sheets
        if (!$this->offlineMode()) {
            $this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
        } else {
            $style_name = $ilUser->getPref("style") . ".css";
            ;
            $this->tpl->setVariable("LOCATION_STYLESHEET", "./style/" . $style_name);
        }

        $this->tpl->setCurrentBlock("ilMedia");

        //$int_links = $page_object->getInternalLinks();
        $med_links = ilMediaItem::_getMapAreasIntLinks($_GET["mob_id"]);
        $link_xml = $this->getLinkXML($med_links, $this->getLayoutLinkTargets());
        $link_xml .= $this->getLinkTargetsXML();
        //echo "<br><br>".htmlentities($link_xml);
        require_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
        $media_obj = new ilObjMediaObject($_GET["mob_id"]);
        if (!empty($_GET["pg_id"])) {
            require_once("./Modules/LearningModule/classes/class.ilLMPage.php");
            $pg_obj = $this->getLMPage($_GET["pg_id"]);
            $pg_obj->buildDom();

            $xml = "<dummy>";
            // todo: we get always the first alias now (problem if mob is used multiple
            // times in page)
            $xml .= $pg_obj->getMediaAliasElement($_GET["mob_id"]);
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

        //echo htmlentities($xml); exit;

        // todo: utf-header should be set globally
        //header('Content-type: text/html; charset=UTF-8');

        $xsl = file_get_contents("./Services/COPage/xsl/page.xsl");
        $args = array( '/_xml' => $xml, '/_xsl' => $xsl );
        $xh = xslt_create();

        //echo "<b>XML:</b>".htmlentities($xml);
        // determine target frames for internal links
        //$pg_frame = $_GET["frame"];
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
            $this->getLink($this->lm->getRefId(), "fullscreen");
        $params = array('mode' => $mode, 'enlarge_path' => $enlarge_path,
            'link_params' => "ref_id=" . $this->lm->getRefId(),'fullscreen_link' => $fullscreen_link,
            'ref_id' => $this->lm->getRefId(), 'pg_frame' => $pg_frame, 'webspace_path' => $wb_path);
        $output = xslt_process($xh, "arg:/_xml", "arg:/_xsl", null, $args, $params);
        echo xslt_error($xh);
        xslt_free($xh);

        // unmask user html
        $this->tpl->setVariable("MEDIA_CONTENT", $output);
        
        // add js
        include_once("./Services/MediaObjects/classes/class.ilObjMediaObjectGUI.php");
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
            ilLMObject::_lookupType($_GET["obj_id"]) == "st") {
            $c_id = $_GET["obj_id"];
        } else {
            if ($this->deactivated_page) {
                $c_id = $_GET["obj_id"];
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


    /**
    * inserts sequential learning module navigation
    * at template variable LMNAVIGATION_CONTENT
    */
    public function ilLMNavigation()
    {
        $ilUser = $this->user;

        include_once("./Modules/LearningModule/classes/class.ilLMPage.php");
        
        include_once("./Services/Accessibility/classes/class.ilAccessKeyGUI.php");
        
        $page_id = $this->getCurrentPageId();

        if (empty($page_id)) {
            return;
        }

        // process navigation for free page
        if (!$this->lm_tree->isInTree($page_id)) {
            if ($this->offlineMode() || $_GET["back_pg"] == "") {
                return;
            }
            $limpos = strpos($_GET["back_pg"], ":");
            if ($limpos > 0) {
                $back_pg = substr($_GET["back_pg"], 0, $limpos);
            } else {
                $back_pg = $_GET["back_pg"];
            }
            if (!$this->lm->cleanFrames()) {
                $back_href =
                    $this->getLink(
                        $this->lm->getRefId(),
                        "layout",
                        $back_pg,
                        $_GET["frame"],
                        "",
                        "reduce"
                    );
                $back_target = "";
            } else {
                $back_href =
                    $this->getLink(
                        $this->lm->getRefId(),
                        "layout",
                        $back_pg,
                        "",
                        "",
                        "reduce"
                    );
                $back_target = 'target="' . ilFrameTargetInfo::_getFrame("MainContent") . '" ';
            }
            $back_img =
                ilUtil::getImagePath("nav_arr2_L.png", false, "output", $this->offlineMode());
            $this->tpl->setCurrentBlock("ilLMNavigation_Prev");
            $this->tpl->setVariable("IMG_PREV", $back_img);
            $this->tpl->setVariable("HREF_PREV", $back_href);
            $this->tpl->setVariable("FRAME_PREV", $back_target);
            $this->tpl->setVariable("TXT_PREV", $this->lng->txt("back"));
            $this->tpl->setVariable("ALT_PREV", $this->lng->txt("back"));
            $this->tpl->setVariable(
                "PREV_ACC_KEY",
                ilAccessKeyGUI::getAttribute(ilAccessKey::PREVIOUS)
            );
            $this->tpl->setVariable("SPACER_PREV", $this->offlineMode()
                ? "images/spacer.png"
                : ilUtil::getImagePath("spacer.png"));
            $this->tpl->parseCurrentBlock();
            $this->tpl->setCurrentBlock("ilLMNavigation_Prev2");
            $this->tpl->setVariable("IMG_PREV2", $back_img);
            $this->tpl->setVariable("HREF_PREV2", $back_href);
            $this->tpl->setVariable("FRAME_PREV2", $back_target);
            $this->tpl->setVariable("TXT_PREV2", $this->lng->txt("back"));
            $this->tpl->setVariable("ALT_PREV2", $this->lng->txt("back"));
            $this->tpl->setVariable("SPACER_PREV2", $this->offlineMode()
                ? "images/spacer.png"
                : ilUtil::getImagePath("spacer.png"));
            $this->tpl->parseCurrentBlock();
            return;
        }

        // determine successor page_id
        $found = false;
        
        // empty chapter
        if ($this->chapter_has_no_active_page &&
            ilLMObject::_lookupType($_GET["obj_id"]) == "st") {
            $c_id = $_GET["obj_id"];
        } else {
            if ($this->deactivated_page) {
                $c_id = $_GET["obj_id"];
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

        $succ_str = ($succ_node !== false)
            ? " -> " . $succ_node["obj_id"] . "_" . $succ_node["type"]
            : "";

        // determine predecessor page id
        $found = false;
        if ($this->deactivated_page) {
            $c_id = $_GET["obj_id"];
        } else {
            $c_id = $page_id;
        }
        while (!$found) {
            $pre_node = $this->lm_tree->fetchPredecessorNode($c_id, "pg");
            $c_id = $pre_node["obj_id"];
            $active = ilLMPage::_lookupActive(
                $c_id,
                $this->lm->getType(),
                $this->lm_set->get("time_scheduled_page_activation")
            );
            if ($pre_node["obj_id"] > 0 &&
                $ilUser->getId() == ANONYMOUS_USER_ID &&
                ($this->lm->getPublicAccessMode() == "selected" &&
                !ilLMObject::_isPagePublic($pre_node["obj_id"]))) {
                $found = false;
            } elseif ($pre_node["obj_id"] > 0 && !$active) {
                // look, whether activation data should be shown
                $act_data = ilLMPage::_lookupActivationData((int) $pre_node["obj_id"], $this->lm->getType());
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
        
        $pre_str = ($pre_node !== false)
            ? $pre_node["obj_id"] . "_" . $pre_node["type"] . " -> "
            : "";

        // determine target frame
        $framestr = (!empty($_GET["frame"]))
            ? "frame=" . $_GET["frame"] . "&"
            : "";


        // Determine whether the view of a learning resource should
        // be shown in the frameset of ilias, or in a separate window.
        $showViewInFrameset = true;

        if ($pre_node != "") {
            // get presentation title
            $prev_title = ilLMPageObject::_getPresentationTitle(
                $pre_node["obj_id"],
                $this->lm->getPageHeader(),
                $this->lm->isActiveNumbering(),
                $this->lm_set->get("time_scheduled_page_activation"),
                false,
                0,
                $this->lang,
                true
            );
            $prev_title = ilUtil::shortenText($prev_title, 50, true);
            $prev_img =
                ilUtil::getImagePath("nav_arr_L.png", false, "output", $this->offlineMode());

            if (!$this->lm->cleanFrames()) {
                $prev_href =
                    $this->getLink($this->lm->getRefId(), "layout", $pre_node["obj_id"], $_GET["frame"]);
                $prev_target = "";
            } elseif ($showViewInFrameset && !$this->offlineMode()) {
                $prev_href =
                    $this->getLink($this->lm->getRefId(), "layout", $pre_node["obj_id"]);
                $prev_target = 'target="' . ilFrameTargetInfo::_getFrame("MainContent") . '" ';
            } else {
                $prev_href =
                    $this->getLink($this->lm->getRefId(), "layout", $pre_node["obj_id"]);
                $prev_target = 'target="_top" ';
            }
            
            if ($ilUser->getId() == ANONYMOUS_USER_ID &&
               ($this->lm->getPublicAccessMode() == 'selected' && !ilLMObject::_isPagePublic($pre_node["obj_id"]))) {
                $output = $this->lng->txt("msg_page_not_public");
            }
            
            $this->tpl->setCurrentBlock("ilLMNavigation_Prev");
            $this->tpl->setVariable("IMG_PREV", $prev_img);
            $this->tpl->setVariable("HREF_PREV", $prev_href);
            $this->tpl->setVariable("FRAME_PREV", $prev_target);
            $this->tpl->setVariable("TXT_PREV", $prev_title);
            $this->tpl->setVariable("ALT_PREV", $this->lng->txt("previous"));
            $this->tpl->setVariable("SPACER_PREV", $this->offlineMode()
                ? "images/spacer.png"
                : ilUtil::getImagePath("spacer.png"));
            $this->tpl->setVariable(
                "PREV_ACC_KEY",
                ilAccessKeyGUI::getAttribute(ilAccessKey::PREVIOUS)
            );
            $this->tpl->parseCurrentBlock();
            $this->tpl->setCurrentBlock("ilLMNavigation_Prev2");
            $this->tpl->setVariable("IMG_PREV2", $prev_img);
            $this->tpl->setVariable("HREF_PREV2", $prev_href);
            $this->tpl->setVariable("FRAME_PREV2", $prev_target);
            $this->tpl->setVariable("TXT_PREV2", $prev_title);
            $this->tpl->setVariable("ALT_PREV2", $this->lng->txt("previous"));
            $this->tpl->setVariable("SPACER_PREV2", $this->offlineMode()
                ? "images/spacer.png"
                : ilUtil::getImagePath("spacer.png"));
            $this->tpl->parseCurrentBlock();
        }
        if ($succ_node != "") {
            // get presentation title
            $succ_title = ilLMPageObject::_getPresentationTitle(
                $succ_node["obj_id"],
                $this->lm->getPageHeader(),
                $this->lm->isActiveNumbering(),
                $this->lm_set->get("time_scheduled_page_activation"),
                false,
                0,
                $this->lang,
                true
            );
            $succ_title = ilUtil::shortenText($succ_title, 50, true);
            $succ_img =
                ilUtil::getImagePath("nav_arr_R.png", false, "output", $this->offlineMode());
            if (!$this->lm->cleanFrames()) {
                $succ_href =
                    $this->getLink($this->lm->getRefId(), "layout", $succ_node["obj_id"], $_GET["frame"]);
                $succ_target = "";
            } elseif ($showViewInFrameset && !$this->offlineMode()) {
                $succ_href =
                    $this->getLink($this->lm->getRefId(), "layout", $succ_node["obj_id"]);
                $succ_target = ' target="' . ilFrameTargetInfo::_getFrame("MainContent") . '" ';
            } else {
                $succ_href =
                    $this->getLink($this->lm->getRefId(), "layout", $succ_node["obj_id"]);
                $succ_target = ' target="_top" ';
            }
            
            if ($ilUser->getId() == ANONYMOUS_USER_ID &&
               ($this->lm->getPublicAccessMode() == 'selected' && !ilLMObject::_isPagePublic($pre_node["obj_id"]))) {
                $output = $this->lng->txt("msg_page_not_public");
            }

            $this->tpl->setCurrentBlock("ilLMNavigation_Next");
            $this->tpl->setVariable("IMG_SUCC", $succ_img);
            $this->tpl->setVariable("HREF_SUCC", $succ_href);
            $this->tpl->setVariable("FRAME_SUCC", $succ_target);
            $this->tpl->setVariable("TXT_SUCC", $succ_title);
            $this->tpl->setVariable("ALT_SUCC", $this->lng->txt("next"));
            $this->tpl->setVariable("SPACER_SUCC", $this->offlineMode()
                ? "images/spacer.png"
                : ilUtil::getImagePath("spacer.png"));
            $this->tpl->setVariable(
                "NEXT_ACC_KEY",
                ilAccessKeyGUI::getAttribute(ilAccessKey::NEXT)
            );
            $this->tpl->parseCurrentBlock();
            $this->tpl->setCurrentBlock("ilLMNavigation_Next2");
            $this->tpl->setVariable("IMG_SUCC2", $succ_img);
            $this->tpl->setVariable("HREF_SUCC2", $succ_href);
            $this->tpl->setVariable("FRAME_SUCC2", $succ_target);
            $this->tpl->setVariable("TXT_SUCC2", $succ_title);
            $this->tpl->setVariable("ALT_SUCC2", $this->lng->txt("next"));
            $this->tpl->setVariable("SPACER_SUCC2", $this->offlineMode()
                ? "images/spacer.png"
                : ilUtil::getImagePath("spacer.png"));
            $this->tpl->parseCurrentBlock();

            // check if successor page is not restricted
            if (!$this->offlineMode()) {
                if ($this->lm->getRestrictForwardNavigation()) {
                    if ($this->getTracker()->hasPredIncorrectAnswers($succ_node["obj_id"])) {
                        $this->tpl->addOnLoadCode("$('.ilc_page_rnav_RightNavigation').addClass('ilNoDisplay');");
                    }
                }
            }
        }
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
                            $this->getLink(
                                $this->lm->getRefId(),
                                "layout",
                                $_GET["obj_id"],
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
                        $this->getLink(
                            $this->lm->getRefId(),
                            "layout",
                            $_GET["obj_id"],
                            $attributes["name"],
                            "",
                            "keep",
                            "",
                            $_GET["srcstring"]
                        );
                    $attributes["title"] = $this->lng->txt("cont_frame_" . $attributes["name"]);
                    if ($attributes["name"] == "toc") {
                        $attributes["src"] .= "#" . $_GET["obj_id"];
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
    * table of contents
    */
    public function showTableOfContents()
    {
        $ilUser = $this->user;

        if (!$this->lm->isActiveTOC() || !$this->lm->isActiveLMMenu()) {
            return;
        }

        //$this->tpl = new ilTemplate("tpl.lm_toc.html", true, true, true);
        $this->tpl->setCurrentBlock("ContentStyle");
        if (!$this->offlineMode()) {
            $this->tpl->setVariable(
                "LOCATION_CONTENT_STYLESHEET",
                ilObjStyleSheet::getContentStylePath($this->lm->getStyleSheetId())
            );
        } else {
            $this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET", "content_style/content.css");
        }
        $this->tpl->parseCurrentBlock();

        $this->renderPageTitle();

        // set style sheets
        if (!$this->offlineMode()) {
            $this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
        } else {
            $style_name = $ilUser->getPref("style") . ".css";
            ;
            $this->tpl->setVariable("LOCATION_STYLESHEET", "./style/" . $style_name);
        }

        //$this->tpl->addBlockFile("CONTENT", "content", "tpl.lm_toc.html", true);
        //var_dump($GLOBALS["tpl"]); echo "<br><br>";
        //exit;
        $this->tpl->getStandardTemplate();
        $this->ilLocator(true);

        $a_global_tabs = !$this->offlineMode();

        $this->tpl->setVariable("TABS", $this->lm_gui->setilLMMenu(
            $this->offlineMode(),
            $this->getExportFormat(),
            "toc",
            $a_global_tabs,
            false,
            0,
            $this->lang,
            $this->export_all_languages
        ));

        // set title header
        $this->tpl->setVariable("TXT_TOC", $this->lng->txt("cont_toc"));
        $this->tpl->setTitle($this->getLMPresentationTitle());
        $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_lm.svg"));

        include_once("./Modules/LearningModule/classes/class.ilLMTableOfContentsExplorerGUI.php");
        $exp = new ilLMTableOfContentsExplorerGUI($this, "showTableOfContents", $this, $this->lang, $this->export_all_languages);
        $exp->setMainTemplate($this->tpl);
        $exp->setTracker($this->getTracker());
        if (!$exp->handleCommand()) {
            // determine highlighted and force open nodes
            $page_id = $this->getCurrentPageId();
            if ($this->deactivated_page) {
                $page_id = $_GET["obj_id"];
            }

            // highlight current node
            if (!$this->offlineMode()) {
                // empty chapter
                if ($this->chapter_has_no_active_page &&
                    ilLMObject::_lookupType($_GET["obj_id"]) == "st") {
                    $exp->setHighlightNode($_GET["obj_id"]);
                } else {
                    if ($this->lm->getTOCMode() == "pages") {
                        if ($this->deactivated_page) {
                            $exp->setHighlightNode($_GET["obj_id"]);
                        } else {
                            $exp->setHighlightNode($page_id);
                        }
                    } else {
                        $exp->setHighlightNode($this->lm_tree->getParentId($page_id));
                    }
                }
            }
            if ($this->offlineMode()) {
                $exp->setOfflineMode(true);
            }
            //	var_dump($exp->getHTML()."_");
            $this->tpl->setVariable("ADM_CONTENT", $exp->getHTML());
        }
        if ($this->offlineMode()) {
            // reset standard css files
            $this->tpl->resetJavascript();
            $this->tpl->resetCss();
            $this->tpl->setBodyClass("ilLMNoMenu");

            include_once("./Modules/LearningModule/classes/class.ilObjContentObject.php");
            foreach (ilObjContentObject::getSupplyingExportFiles() as $f) {
                if ($f["type"] == "js") {
                    $this->tpl->addJavascript($f["target"]);
                }
                if ($f["type"] == "css") {
                    $this->tpl->addCSS($f["target"]);
                }
            }
            $this->tpl->fillJavaScriptFiles(true);
            $this->tpl->fillOnLoadCode();
            //var_dump(htmlentities($this->tpl->get())); exit;
            return $this->tpl->get();
        } else {
            $this->tpl->show();
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
        if (!$this->offlineMode()) {
            $this->tpl->setStyleSheetLocation(ilUtil::getStyleSheetLocation());
        } else {
            $style_name = $ilUser->getPref("style") . ".css";
            ;
            $this->tpl->setStyleSheetLocation("./" . $style_name);
        }

        $this->tpl->getStandardTemplate();
        $this->tpl->setTitle($this->getLMPresentationTitle());
        $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_lm.svg"));

        $this->tpl->setVariable("TABS", $this->lm_gui->setilLMMenu(
            $this->offlineMode(),
            $this->getExportFormat(),
            $a_active_tab,
            true,
            false,
            0,
            $this->lang,
            $this->export_all_languages
        ));
        
        // Full locator, if read permission is given
        if ($ilAccess->checkAccess("read", "", $_GET["ref_id"])) {
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

        include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");

        $info = new ilInfoScreenGUI($this->lm_gui);
        $info->enablePrivateNotes();
        $info->enableLearningProgress();

        $info->enableNews();
        if ($ilAccess->checkAccess("write", "", $_GET["ref_id"])) {
            $news_set = new ilSetting("news");
            $enable_internal_rss = $news_set->get("enable_rss_for_internal");
            
            $info->enableNewsEditing();
            
            if ($enable_internal_rss) {
                $info->setBlockProperty("news", "settings", true);
            }
        }
        
        // add read / back button
        /*
        if ($ilAccess->checkAccess("read", "", $_GET["ref_id"]))
        {
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
            $this->tpl->show();
        }
    }

    /**
    * show selection screen for print view
    */
    public function showPrintViewSelection()
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        
        include_once("./Modules/LearningModule/classes/class.ilLMPage.php");
        if (!$this->lm->isActivePrintView() || !$this->lm->isActiveLMMenu()) {
            return;
        }
        
        include_once("./Modules/LearningModule/classes/class.ilStructureObject.php");

        //$this->tpl = new ilTemplate("tpl.lm_toc.html", true, true, true);
        $this->tpl->setCurrentBlock("ContentStyle");
        if (!$this->offlineMode()) {
            $this->tpl->setVariable(
                "LOCATION_CONTENT_STYLESHEET",
                ilObjStyleSheet::getContentStylePath($this->lm->getStyleSheetId())
            );
        } else {
            $this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET", "content_style/content.css");
        }
        $this->tpl->parseCurrentBlock();

        $this->renderPageTitle();
        $this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
        $this->tpl->getStandardTemplate();
        
        $this->tpl->setVariable("TABS", $this->lm_gui->setilLMMenu(
            $this->offlineMode(),
            $this->getExportFormat(),
            "print",
            true,
            false,
            0,
            $this->lang,
            $this->export_all_languages
        ));
            
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
        $this->ctrl->setParameterByClass("illmpresentationgui", "obj_id", $_GET["obj_id"]);
        $this->tpl->setVariable("LINK_BACK",
            $this->ctrl->getLinkTargetByClass("illmpresentationgui", ""));*/

        $this->ctrl->setParameterByClass("illmpresentationgui", "obj_id", $_GET["obj_id"]);
        $this->tpl->setVariable("FORMACTION", $this->ctrl->getFormaction($this));

        $nodes = $this->lm_tree->getSubtree($this->lm_tree->getNodeData($this->lm_tree->getRootId()));
        $nodes = $this->filterNonAccessibleNode($nodes);

        if (!is_array($_POST["item"])) {
            if ($_GET["obj_id"] != "") {
                $_POST["item"][$_GET["obj_id"]] = "y";
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
                            IL_CHAPTER_TITLE,
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
        if ($_GET["obj_id"] > 0 && !$this->lm_tree->isInTree($_GET["obj_id"])) {
            $text =
                ilLMPageObject::_getPresentationTitle(
                    $_GET["obj_id"],
                    $this->lm->getPageHeader(),
                    $this->lm->isActiveNumbering(),
                    $this->lm_set->get("time_scheduled_page_activation"),
                    false,
                    0,
                    $this->lang
                );
            
            if ($ilUser->getId() == ANONYMOUS_USER_ID &&
               $this->lm_gui->object->getPublicAccessMode() == "selected") {
                if (!ilLMObject::_isPagePublic($_GET["obj_id"])) {
                    $disabled = true;
                    $text .= " (" . $this->lng->txt("cont_no_access") . ")";
                }
            }
            $img_src = ilUtil::getImagePath("icon_pg.svg");
            $id = $_GET["obj_id"];

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
        $this->tpl->show();
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

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
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

        include_once("./Services/Form/classes/class.ilNestedListInputGUI.php");
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

        include_once("./Modules/LearningModule/classes/class.ilLMPage.php");
        
        if (!$this->lm->isActivePrintView() || !$this->lm->isActiveLMMenu()) {
            return;
        }

        $this->renderPageTitle();
        
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
        
        //var_dump($_GET);
        //var_dump($_POST);
        // set style sheets
        if (!$this->offlineMode()) {
            $this->tpl->setVariable("LOCATION_STYLESHEET", ilObjStyleSheet::getContentPrintStyle());
        } else {
            $style_name = $ilUser->getPref("style") . ".css";
            ;
            $this->tpl->setVariable("LOCATION_STYLESHEET", "./style/" . $style_name);
        }

        // content style
        $this->tpl->setCurrentBlock("ContentStyle");
        if (!$this->offlineMode()) {
            $this->tpl->setVariable(
                "LOCATION_CONTENT_STYLESHEET",
                ilObjStyleSheet::getContentStylePath($this->lm->getStyleSheetId())
            );
        } else {
            $this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET", "content_style/content.css");
        }
        $this->tpl->parseCurrentBlock();

        // syntax style
        $this->tpl->setCurrentBlock("SyntaxStyle");
        $this->tpl->setVariable(
            "LOCATION_SYNTAX_STYLESHEET",
            ilObjStyleSheet::getSyntaxStylePath()
        );
        $this->tpl->parseCurrentBlock();

        //$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
        $this->tpl->addBlockFile("CONTENT", "content", "tpl.lm_print_view.html", true);

        // set title header
        $this->tpl->setVariable("HEADER", $this->getLMPresentationTitle());

        $nodes = $this->lm_tree->getSubtree($this->lm_tree->getNodeData($this->lm_tree->getRootId()));

        include_once("./Modules/LearningModule/classes/class.ilLMPageGUI.php");
        include_once("./Modules/LearningModule/classes/class.ilLMPageObject.php");
        include_once("./Modules/LearningModule/classes/class.ilStructureObject.php");

        $act_level = 99999;
        $activated = false;

        $glossary_links = array();
        $output_header = false;
        $media_links = array();

        // get header and footer
        if ($this->lm->getFooterPage() > 0 && !$this->lm->getHideHeaderFooterPrint()) {
            if (ilLMObject::_exists($this->lm->getFooterPage())) {
                $page_object_gui = $this->getLMPageGUI($this->lm->getFooterPage());
                include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
                $page_object_gui->setStyleId(ilObjStyleSheet::getEffectiveContentStyleId(
                    $this->lm->getStyleSheetId(),
                    "lm"
                ));

    
                // determine target frames for internal links
                $page_object_gui->setLinkFrame($_GET["frame"]);
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
                include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
                $page_object_gui->setStyleId(ilObjStyleSheet::getEffectiveContentStyleId(
                    $this->lm->getStyleSheetId(),
                    "lm"
                ));

    
                // determine target frames for internal links
                $page_object_gui->setLinkFrame($_GET["frame"]);
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
                    $this->tpl->setCurrentBlock("print_chapter");

                    $chapter_title = $chap->_getPresentationTitle(
                        $node["obj_id"],
                        $this->lm->isActiveNumbering(),
                        $this->lm_set->get("time_scheduled_page_activation"),
                        0,
                        $this->lang
                    );
                    $this->tpl->setVariable(
                        "CHAP_TITLE",
                        $chapter_title
                    );
                        
                    if ($this->lm->getPageHeader() == IL_CHAPTER_TITLE) {
                        if ($nodes[$node_key + 1]["type"] == "pg") {
                            $this->tpl->setVariable(
                                "CHAP_HEADER",
                                $header_page_content
                            );
                            $did_chap_page_header = true;
                        }
                    }

                    $this->tpl->parseCurrentBlock();
                    $this->tpl->setCurrentBlock("print_block");
                    $this->tpl->parseCurrentBlock();
                }

                // output page
                if ($node["type"] == "pg") {
                    if ($ilUser->getId() == ANONYMOUS_USER_ID &&
                       $this->lm_gui->object->getPublicAccessMode() == "selected") {
                        if (!ilLMObject::_isPagePublic($node["obj_id"])) {
                            continue;
                        }
                    }

                    $this->tpl->setCurrentBlock("print_item");
                    
                    // get page
                    $page_id = $node["obj_id"];
                    $page_object_gui = $this->getLMPageGUI($page_id);
                    $page_object = $page_object_gui->getPageObject();
                    include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
                    $page_object_gui->setStyleId(ilObjStyleSheet::getEffectiveContentStyleId(
                        $this->lm->getStyleSheetId(),
                        "lm"
                    ));


                    // get lm page
                    $lm_pg_obj = new ilLMPageObject($this->lm, $page_id);
                    $lm_pg_obj->setLMId($this->lm->getId());

                    // determine target frames for internal links
                    $page_object_gui->setLinkFrame($_GET["frame"]);
                    $page_object_gui->setOutputMode("print");
                    $page_object_gui->setPresentationTitle("");
                    
                    if ($this->lm->getPageHeader() == IL_PAGE_TITLE || $node["free"] === true) {
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

                    if ($this->lm->getPageHeader() == IL_CHAPTER_TITLE) {
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
                    if ($this->lm->getPageHeader() != IL_PAGE_TITLE) {
                        $this->tpl->setVariable(
                            "CONTENT",
                            $hcont . $page_content . $fcont
                        );
                    } else {
                        $this->tpl->setVariable(
                            "CONTENT",
                            $hcont . $page_content . $fcont . "<br />"
                        );
                    }
                    $chapter_title = "";
                    $this->tpl->parseCurrentBlock();
                    $this->tpl->setCurrentBlock("print_block");
                    $this->tpl->parseCurrentBlock();

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
            include_once("./Modules/Glossary/classes/class.ilGlossaryTerm.php");
            include_once("./Modules/Glossary/classes/class.ilGlossaryDefinition.php");

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
                        $this->tpl->setCurrentBlock("def_title");
                        $this->tpl->setVariable(
                            "TXT_DEFINITION",
                            $this->lng->txt("cont_definition") . " " . ($def_cnt++)
                        );
                        $this->tpl->parseCurrentBlock();
                    }
                    include_once("./Modules/Glossary/classes/class.ilGlossaryDefPageGUI.php");
                    $page_gui = new ilGlossaryDefPageGUI($def["id"]);
                    $page_gui->setTemplateOutput(false);
                    $page_gui->setOutputMode("print");

                    $this->tpl->setCurrentBlock("definition");
                    $page_gui->setFileDownloadLink("#");
                    $page_gui->setFullscreenLink("#");
                    $page_gui->setSourceCodeDownloadScript("#");
                    $output = $page_gui->showPage();
                    $this->tpl->setVariable("VAL_DEFINITION", $output);
                    $this->tpl->parseCurrentBlock();
                }

                // output term
                $this->tpl->setCurrentBlock("term");
                $this->tpl->setVariable(
                    "VAL_TERM",
                    $term = ilGlossaryTerm::_lookGlossaryTerm($link["id"])
                );
                $this->tpl->parseCurrentBlock();
            }

            // output glossary header
            $annex_cnt++;
            $this->tpl->setCurrentBlock("glossary");
            $annex_title = $this->lng->txt("cont_annex") . " " .
                chr(64 + $annex_cnt) . ": " . $this->lng->txt("glo");
            $this->tpl->setVariable("TXT_GLOSSARY", $annex_title);
            $this->tpl->parseCurrentBlock();

            $annexes[] = $annex_title;
        }

        // referenced images
        if (count($media_links) > 0) {
            include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
            include_once("./Services/MediaObjects/classes/class.ilMediaItem.php");

            foreach ($media_links as $media) {
                if (substr($media["Target"], 0, 4) == "il__") {
                    $arr = explode("_", $media["Target"]);
                    $id = $arr[count($arr) - 1];
                    
                    $med_obj = new ilObjMediaObject($id);
                    $med_item = $med_obj->getMediaItem("Standard");
                    if (is_object($med_item)) {
                        if (is_int(strpos($med_item->getFormat(), "image"))) {
                            $this->tpl->setCurrentBlock("ref_image");
                            
                            // image source
                            if ($med_item->getLocationType() == "LocalFile") {
                                $this->tpl->setVariable(
                                    "IMG_SOURCE",
                                    ilUtil::getWebspaceDir("output") . "/mobs/mm_" . $id .
                                    "/" . $med_item->getLocation()
                                );
                            } else {
                                $this->tpl->setVariable(
                                    "IMG_SOURCE",
                                    $med_item->getLocation()
                                );
                            }
                            
                            if ($med_item->getCaption() != "") {
                                $this->tpl->setVariable("IMG_TITLE", $med_item->getCaption());
                            } else {
                                $this->tpl->setVariable("IMG_TITLE", $med_obj->getTitle());
                            }
                            $this->tpl->parseCurrentBlock();
                        }
                    }
                }
            }
            
            // output glossary header
            $annex_cnt++;
            $this->tpl->setCurrentBlock("ref_images");
            $annex_title = $this->lng->txt("cont_annex") . " " .
                chr(64 + $annex_cnt) . ": " . $this->lng->txt("cont_ref_images");
            $this->tpl->setVariable("TXT_REF_IMAGES", $annex_title);
            $this->tpl->parseCurrentBlock();

            $annexes[] = $annex_title;
        }

        // output learning module title and toc
        if ($output_header) {
            $this->tpl->setCurrentBlock("print_header");
            $this->tpl->setVariable("LM_TITLE", $this->getLMPresentationTitle());
            if ($this->lm->getDescription() != "none") {
                include_once("Services/MetaData/classes/class.ilMD.php");
                $md = new ilMD($this->lm->getId(), 0, $this->lm->getType());
                $md_gen = $md->getGeneral();
                foreach ($md_gen->getDescriptionIds() as $id) {
                    $md_des = $md_gen->getDescription($id);
                    $description = $md_des->getDescription();
                }

                $this->tpl->setVariable(
                    "LM_DESCRIPTION",
                    $description
                );
            }
            $this->tpl->parseCurrentBlock();

            // output toc
            $nodes2 = $nodes;
            foreach ($nodes2 as $node2) {
                if ($node2["type"] == "st"
                    && ilObjContentObject::_checkPreconditionsOfPage($this->lm->getRefId(), $this->lm->getId(), $node2["obj_id"])) {
                    for ($j = 1; $j < $node2["depth"]; $j++) {
                        $this->tpl->setCurrentBlock("indent");
                        $this->tpl->setVariable("IMG_BLANK", ilUtil::getImagePath("browser/blank.png"));
                        $this->tpl->parseCurrentBlock();
                    }
                    $this->tpl->setCurrentBlock("toc_entry");
                    $this->tpl->setVariable(
                        "TXT_TOC_TITLE",
                        ilStructureObject::_getPresentationTitle(
                            $node2["obj_id"],
                            IL_CHAPTER_TITLE,
                            $this->lm->isActiveNumbering(),
                            $this->lm_set->get("time_scheduled_page_activation"),
                            false,
                            0,
                            $this->lang
                        )
                    );
                    $this->tpl->parseCurrentBlock();
                }
            }

            // annexes
            foreach ($annexes as $annex) {
                $this->tpl->setCurrentBlock("indent");
                $this->tpl->setVariable("IMG_BLANK", ilUtil::getImagePath("browser/blank.png"));
                $this->tpl->parseCurrentBlock();
                $this->tpl->setCurrentBlock("toc_entry");
                $this->tpl->setVariable("TXT_TOC_TITLE", $annex);
                $this->tpl->parseCurrentBlock();
            }

            $this->tpl->setCurrentBlock("toc");
            $this->tpl->setVariable("TXT_TOC", $this->lng->txt("cont_toc"));
            $this->tpl->parseCurrentBlock();

            $this->tpl->setCurrentBlock("print_start_block");
            $this->tpl->parseCurrentBlock();
        }
        
        // output author information
        include_once 'Services/MetaData/classes/class.ilMD.php';
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
                $this->tpl->setCurrentBlock("author");
                $this->tpl->setVariable("TXT_AUTHOR", $this->lng->txt("meta_author"));
                $this->tpl->setVariable("LM_AUTHOR", $author);
                $this->tpl->parseCurrentBlock();
            }
        }

        
        // output copyright information
        if (is_object($md_rights = $md->getRights())) {
            $copyright = $md_rights->getDescription();
            include_once('Services/MetaData/classes/class.ilMDUtils.php');
            $copyright = ilMDUtils::_parseCopyright($copyright);

            if ($copyright != "") {
                $this->lng->loadLanguageModule("meta");
                $this->tpl->setCurrentBlock("copyright");
                $this->tpl->setVariable("TXT_COPYRIGHT", $this->lng->txt("meta_copyright"));
                $this->tpl->setVariable("LM_COPYRIGHT", $copyright);
                $this->tpl->parseCurrentBlock();
            }
        }

        $this->tpl->show(false);
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
    * download source code paragraph
    */
    public function download_paragraph()
    {
        require_once("./Modules/LearningModule/classes/class.ilLMPage.php");
        $pg_obj = $this->getLMPage($_GET["pg_id"]);
        $pg_obj->send_paragraph($_GET["par_id"], $_GET["downloadtitle"]);
    }
    
    /**
    * show download list
    */
    public function showDownloadList()
    {
        if (!$this->lm->isActiveDownloads() || !$this->lm->isActiveLMMenu()) {
            return;
        }

        //$this->tpl = new ilTemplate("tpl.lm_toc.html", true, true, true);
        $this->tpl->setCurrentBlock("ContentStyle");
        if (!$this->offlineMode()) {
            $this->tpl->setVariable(
                "LOCATION_CONTENT_STYLESHEET",
                ilObjStyleSheet::getContentStylePath($this->lm->getStyleSheetId())
            );
        } else {
            $this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET", "content_style/content.css");
        }
        $this->tpl->parseCurrentBlock();

        $this->renderPageTitle();
        $this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
        $this->tpl->getStandardTemplate();
        
        $this->tpl->setVariable("TABS", $this->lm_gui->setilLMMenu(
            $this->offlineMode(),
            $this->getExportFormat(),
            "download",
            true,
            false,
            0,
            $this->lang,
            $this->export_all_languages
        ));

        $this->ilLocator(true);
        //$this->tpl->stopTitleFloating();
        $this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.lm_download_list.html", "Modules/LearningModule");

        // set title header
        $this->tpl->setTitle($this->getLMPresentationTitle());
        $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_lm.svg"));

        /*
        $this->tpl->setVariable("TXT_BACK", $this->lng->txt("back"));
        $this->ctrl->setParameter($this, "obj_id", $_GET["obj_id"]);
        $this->tpl->setVariable("LINK_BACK",
            $this->ctrl->getLinkTarget($this, "")); */

        // output copyright information
        include_once 'Services/MetaData/classes/class.ilMD.php';
        $md = new ilMD($this->lm->getId(), 0, $this->lm->getType());
        if (is_object($md_rights = $md->getRights())) {
            $copyright = $md_rights->getDescription();
            
            include_once('Services/MetaData/classes/class.ilMDUtils.php');
            $copyright = ilMDUtils::_parseCopyright($copyright);

            if ($copyright != "") {
                $this->lng->loadLanguageModule("meta");
                $this->tpl->setCurrentBlock("copyright");
                $this->tpl->setVariable("TXT_COPYRIGHT", $this->lng->txt("meta_copyright"));
                $this->tpl->setVariable("LM_COPYRIGHT", $copyright);
                $this->tpl->parseCurrentBlock();
            }
        }


        include_once("./Modules/LearningModule/classes/class.ilLMDownloadTableGUI.php");
        $download_table = new ilLMDownloadTableGUI($this, "showDownloadList", $this->lm);
        $this->tpl->setVariable("DOWNLOAD_TABLE", $download_table->getHTML());
        $this->tpl->show();
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
    * handles links for learning module presentation
    */
    public function getLink(
        $a_ref_id,
        $a_cmd = "",
        $a_obj_id = "",
        $a_frame = "",
        $a_type = "",
        $a_back_link = "append",
        $a_anchor = "",
        $a_srcstring = ""
    ) {
        $ilCtrl = $this->ctrl;
        
        if ($a_cmd == "") {
            $a_cmd = "layout";
        }
        
        // handling of free pages
        $cur_page_id = $this->getCurrentPageId();
        $back_pg = $_GET["back_pg"];
        if ($a_obj_id != "" && !$this->lm_tree->isInTree($a_obj_id) && $cur_page_id != "" &&
            $a_back_link == "append") {
            if ($back_pg != "") {
                $back_pg = $cur_page_id . ":" . $back_pg;
            } else {
                $back_pg = $cur_page_id;
            }
        } else {
            if ($a_back_link == "reduce") {
                $limpos = strpos($_GET["back_pg"], ":");

                if ($limpos > 0) {
                    $back_pg = substr($back_pg, strpos($back_pg, ":") + 1);
                } else {
                    $back_pg = "";
                }
            } elseif ($a_back_link != "keep") {
                $back_pg = "";
            }
        }
        
        // handle online links
        if (!$this->offlineMode()) {
            if ($_GET["from_page"] == "") {
                // added if due to #23216 (from page has been set in lots of usual navigation links)
                if (!in_array($a_frame, array("", "_blank"))) {
                    $this->ctrl->setParameter($this, "from_page", $cur_page_id);
                }
            } else {
                // faq link on page (in faq frame) includes faq link on other page
                // if added due to bug #11007
                if (!in_array($a_frame, array("", "_blank"))) {
                    $this->ctrl->setParameter($this, "from_page", $_GET["from_page"]);
                }
            }
            
            if ($a_anchor != "") {
                $this->ctrl->setParameter($this, "anchor", rawurlencode($a_anchor));
            }
            if ($a_srcstring != "") {
                $this->ctrl->setParameter($this, "srcstring", $a_srcstring);
            }
            switch ($a_cmd) {
                case "fullscreen":
                    $link = $this->ctrl->getLinkTarget($this, "fullscreen", "", false, false);
                    break;
                
                default:
                    
                    if ($back_pg != "") {
                        $this->ctrl->setParameter($this, "back_pg", $back_pg);
                    }
                    if ($a_frame != "") {
                        $this->ctrl->setParameter($this, "frame", $a_frame);
                    }
                    if ($a_obj_id != "") {
                        switch ($a_type) {
                            case "MediaObject":
                                $this->ctrl->setParameter($this, "mob_id", $a_obj_id);
                                break;
                                
                            default:
                                $this->ctrl->setParameter($this, "obj_id", $a_obj_id);
                                $link .= "&amp;obj_id=" . $a_obj_id;
                                break;
                        }
                    }
                    if ($a_type != "") {
                        $this->ctrl->setParameter($this, "obj_type", $a_type);
                    }
                    $link = $this->ctrl->getLinkTarget($this, $a_cmd, $a_anchor);
//					$link = str_replace("&", "&amp;", $link);
                    
                    $this->ctrl->setParameter($this, "frame", "");
                    $this->ctrl->setParameter($this, "obj_id", "");
                    $this->ctrl->setParameter($this, "mob_id", "");
                    break;
            }
        } else {	// handle offline links
            $lang_suffix = "";
            if ($this->export_all_languages) {
                if ($this->lang != "" && $this->lang != "-") {
                    $lang_suffix = "_" . $this->lang;
                }
            }

            switch ($a_cmd) {
                case "downloadFile":
                    break;
                    
                case "fullscreen":
                    $link = "fullscreen.html";		// id is handled by xslt
                    break;
                    
                case "layout":
                
                    if ($a_obj_id == "") {
                        $a_obj_id = $this->lm_tree->getRootId();
                        $pg_node = $this->lm_tree->fetchSuccessorNode($a_obj_id, "pg");
                        $a_obj_id = $pg_node["obj_id"];
                    }
                    if ($a_type == "StructureObject") {
                        $pg_node = $this->lm_tree->fetchSuccessorNode($a_obj_id, "pg");
                        $a_obj_id = $pg_node["obj_id"];
                    }
                    if ($a_frame != "" && $a_frame != "_blank") {
                        if ($a_frame != "toc") {
                            $link = "frame_" . $a_obj_id . "_" . $a_frame . $lang_suffix . ".html";
                        } else {	// don't save multiple toc frames (all the same)
                            $link = "frame_" . $a_frame . $lang_suffix . ".html";
                        }
                    } else {
                        //if ($nid = ilLMObject::_lookupNID($this->lm->getId(), $a_obj_id, "pg"))
                        if ($nid = ilLMPageObject::getExportId($this->lm->getId(), $a_obj_id)) {
                            $link = "lm_pg_" . $nid . $lang_suffix . ".html";
                        } else {
                            $link = "lm_pg_" . $a_obj_id . $lang_suffix . ".html";
                        }
                    }
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
     * Show message screen
     *
     * @param
     * @return
     */
    public function showMessageScreen($a_content)
    {
        // content style
        $this->tpl->setCurrentBlock("ContentStyle");
        if (!$this->offlineMode()) {
            $this->tpl->setVariable(
                "LOCATION_CONTENT_STYLESHEET",
                ilObjStyleSheet::getContentStylePath($this->lm->getStyleSheetId())
            );
        } else {
            $this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET", "content_style/content.css");
        }
        $this->tpl->parseCurrentBlock();

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
     * set offline directory to offdir
     *
     * @param offdir contains diretory where to store files
     *
     * current used in code paragraph
     */
    public function setOfflineDirectory($offdir)
    {
        $this->offline_directory = $offdir;
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
        $this->tpl->fillWindowTitle();
        $this->tpl->fillContentLanguage();
    }
    

    /**
     * Get lm page gui object
     *
     * @param
     * @return
     */
    public function getLMPageGUI($a_id)
    {
        include_once("./Modules/LearningModule/classes/class.ilLMPage.php");
        include_once("./Modules/LearningModule/classes/class.ilLMPageGUI.php");
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
    public function getLMPage($a_id)
    {
        include_once("./Modules/LearningModule/classes/class.ilLMPage.php");
        if ($this->lang != "-" && ilPageObject::_exists("lm", $a_id, $this->lang)) {
            return new ilLMPage($a_id, 0, $this->lang);
        }
        return new ilLMPage($a_id);
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
}
