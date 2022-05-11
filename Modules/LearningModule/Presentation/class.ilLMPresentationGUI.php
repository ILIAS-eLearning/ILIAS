<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Class ilLMPresentationGUI
 * GUI class for learning module presentation
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilLMPresentationGUI: ilNoteGUI, ilInfoScreenGUI
 * @ilCtrl_Calls ilLMPresentationGUI: ilLMPageGUI, ilGlossaryDefPageGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilLMPresentationGUI: ilLearningProgressGUI, ilAssGenFeedbackPageGUI
 * @ilCtrl_Calls ilLMPresentationGUI: ilRatingGUI
 */
class ilLMPresentationGUI implements ilCtrlBaseClassInterface, ilCtrlSecurityInterface
{
    protected \ILIAS\Notes\DomainService $notes;
    protected \ILIAS\LearningModule\ReadingTime\ReadingTimeManager $reading_time_manager;
    protected string $requested_url;
    protected string $requested_type;
    protected ilLMTracker $tracker;
    protected ilTabsGUI $tabs;
    protected ilNestedListInputGUI $nl;
    protected ilSetting $lm_set;
    protected ilObjLearningModuleGUI $lm_gui;
    protected bool $fill_on_load_code;
    protected ilLMTree $lm_tree;
    protected array $frames;
    protected string $export_format;
    public string $lang;
    protected ilPropertyFormGUI $form;
    protected ilObjUser $user;
    protected ilRbacSystem $rbacsystem;
    protected ilCtrl $ctrl;
    protected ilNavigationHistory $nav_history;
    protected ilAccessHandler $access;
    protected ilSetting $settings;
    protected ilLocatorGUI $locator;
    protected ilTree $tree;
    protected ilHelpGUI $help;
    protected ilObjLearningModule $lm;
    public ilGlobalTemplateInterface $tpl;
    public ilLanguage $lng;
    public php4DOMDocument $layout_doc;
    public bool $offline;
    public string $offline_directory;
    protected bool $embed_mode = false;
    protected int $current_page_id = 0;
    protected ?int $focus_id = 0;        // focus id is set e.g. from learning objectives course, we focus on a chapter/page
    protected bool $export_all_languages = false;
    public bool $chapter_has_no_active_page = false;
    public bool $deactivated_page = false;
    protected string $requested_back_pg;
    protected string $requested_search_string;
    protected string $requested_focus_return;
    protected int $requested_ref_id;
    protected int $requested_obj_id;
    protected string $requested_obj_type;
    protected string $requested_transl;
    protected string $requested_frame;
    protected ilLMPresentationLinker $linker;
    protected ilLMPresentationService $service;
    protected \ILIAS\DI\UIServices $ui;
    protected ilToolbarGUI $toolbar;
    protected array $additional_content = [];
    protected string $requested_cmd = "";
    protected int $requested_pg_id = 0;
    protected string $requested_pg_type = "";
    protected int $requested_mob_id = 0;
    protected int $requested_notification_switch = 0;
    protected bool $abstract = false;
    protected ilObjectTranslation $ot;
    protected \ILIAS\Style\Content\Object\ObjectFacade $content_style_domain;
    protected \ILIAS\Style\Content\GUIService $content_style_gui;
    protected ?\ILIAS\Style\Content\Service $cs = null;

    public function __construct(
        string $a_export_format = "",
        bool $a_all_languages = false,
        string $a_export_dir = "",
        bool $claim_repo_context = true,
        array $query_params = null,
        bool $embed_mode = false
    ) {
        global $DIC;

        $this->offline = ($a_export_format != "");
        $this->export_all_languages = $a_all_languages;
        $this->export_format = $a_export_format;        // html/scorm
        $this->offline_directory = $a_export_dir;

        $this->tabs = $DIC->tabs();
        $this->toolbar = $DIC->toolbar();
        $this->user = $DIC->user();
        $this->rbacsystem = $DIC->rbac()->system();
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
        $this->ui = $DIC->ui();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->frames = array();
        $this->ctrl = $ilCtrl;
        $this->ctrl->saveParameter($this, array("ref_id", "transl", "focus_id", "focus_return"));

        $this->cs = $DIC->contentStyle();

        // note: using $DIC->http()->request()->getQueryParams() here will
        // fail, since the goto magic currently relies on setting $_GET
        $this->initByRequest($query_params, $embed_mode);

        // check, if learning module is online
        if (!$rbacsystem->checkAccess("write", $this->requested_ref_id)) {
            if ($this->lm->getOfflineStatus()) {
                $ilErr->raiseError($lng->txt("permission_denied"), $ilErr->WARNING);
            }
        }

        if ($claim_repo_context) {
            $DIC->globalScreen()->tool()->context()->claim()->repository();
        }

        if (!$ilCtrl->isAsynch()) {
            // moved this into the if due to #0027200
            if (!$embed_mode) {
                if ($this->service->getPresentationStatus()->isTocNecessary()) {
                    $DIC->globalScreen()->tool()->context()->current()->addAdditionalData(
                        ilLMGSToolProvider::SHOW_TOC_TOOL,
                        true
                    );
                }
            }
            $DIC->globalScreen()->tool()->context()->current()->addAdditionalData(
                ilLMGSToolProvider::SHOW_LINK_SLATES,
                true
            );
        }

        if ($embed_mode) {
            $ilCtrl->setParameter($this, "embed_mode", 1);
            $params = [
                "obj_id" => $this->requested_obj_id,
                "ref_id" => $this->lm->getRefId(),
                "frame" => ""
            ];
            $DIC->globalScreen()->tool()->context()->current()->addAdditionalData(
                \ilLMGSToolProvider::LM_QUERY_PARAMS,
                $params
            );
        }
        $this->reading_time_manager = new \ILIAS\LearningModule\ReadingTime\ReadingTimeManager();
        $this->notes = $DIC->notes()->domain();
    }

    public function getUnsafeGetCommands() : array
    {
        return [];
    }

    public function getSafePostCommands() : array
    {
        return [
            "showPrintView",
        ];
    }

    /**
     * Init services and this class by request params.
     * The request params are usually retrieved by HTTP request, but
     * also adjusted during HTML exports, this is, why this method needs to be public.
     * @param array $query_params request query params
     */
    public function initByRequest(
        ?array $query_params = null,
        bool $embed_mode = false
    ) : void {
        global $DIC;

        $this->service = new ilLMPresentationService(
            $this->user,
            $query_params,
            $this->offline,
            $this->export_all_languages,
            $this->export_format,
            null,
            $embed_mode
        );

        $post = is_null($query_params)
            ? null
            : [];

        $request = $DIC->learningModule()
                       ->internal()
                       ->gui()
                       ->presentation()
                       ->request(
                           $query_params,
                           $post
                       );

        $this->requested_obj_type = $request->getObjType();
        $this->requested_ref_id = $request->getRefId();
        $this->requested_transl = $request->getTranslation();      // handled by presentation status
        $this->requested_obj_id = $request->getObjId();            // handled by navigation status
        $this->requested_back_pg = $request->getBackPage();
        $this->requested_frame = $request->getFrame();
        $this->requested_search_string = $request->getSearchString();
        $this->requested_focus_return = $request->getFocusReturn();
        $this->requested_mob_id = $request->getMobId();
        $this->requested_cmd = $request->getCmd();
        $this->requested_pg_id = $request->getPgId();
        $this->requested_pg_type = $request->getPgType();
        $this->requested_notification_switch = $request->getNotificationSwitch();
        $this->requested_type = $request->getType();
        $this->requested_url = $request->getUrl();

        $this->lm_set = $this->service->getSettings();
        $this->lm_gui = $this->service->getLearningModuleGUI();
        $this->lm = $this->service->getLearningModule();
        $this->tracker = $this->service->getTracker();
        $this->linker = $this->service->getLinker();
        $this->embed_mode = $embed_mode;
        if ($request->getEmbedMode()) {
            $this->embed_mode = true;
        }

        // language translation
        $this->lang = $this->service->getPresentationStatus()->getLang();

        $this->lm_tree = $this->service->getLMTree();
        $this->focus_id = $this->service->getPresentationStatus()->getFocusId();
        $this->ot = ilObjectTranslation::getInstance($this->lm->getId());
        $this->content_style_gui = $this->cs->gui();
        $this->content_style_domain = $this->cs->domain()->styleForRefId($this->lm->getRefId());
    }

    public function getService() : \ilLMPresentationService
    {
        return $this->service;
    }

    public function injectTemplate(ilGlobalTemplateInterface $tpl) : void
    {
        $this->tpl = $tpl;
    }

    protected function getTracker() : ilLMTracker
    {
        return $this->service->getTracker();
    }

    /**
     * @throws ilCtrlException
     * @throws ilLMPresentationException
     * @throws ilPermissionException
     */
    public function executeCommand() : void
    {
        $ilNavigationHistory = $this->nav_history;
        $ilAccess = $this->access;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;

        // check read permission and parent conditions
        // todo: replace all this by ilAccess call
        if (!$ilAccess->checkAccess("read", "", $this->requested_ref_id) &&
            (!(($this->ctrl->getCmd() == "infoScreen" || $this->ctrl->getNextClass() == "ilinfoscreengui")
                && $ilAccess->checkAccess("visible", "", $this->requested_ref_id)))) {
            throw new ilPermissionException($lng->txt("permission_denied"));
        }

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("layout");

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

            case "ilassgenfeedbackpagegui":
                $page_gui = new ilAssGenFeedbackPageGUI($this->requested_pg_id);
                //$this->basicPageGuiInit($page_gui);
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
                if ($this->requested_notification_switch > 0) {
                    switch ($this->requested_notification_switch) {
                        case 1:
                            ilNotification::setNotification(
                                ilNotification::TYPE_LM,
                                $this->user->getId(),
                                $this->lm->getId(),
                                false
                            );
                            break;

                        case 2:
                            ilNotification::setNotification(
                                ilNotification::TYPE_LM,
                                $this->user->getId(),
                                $this->lm->getId(),
                                true
                            );
                            break;

                        case 3:
                            ilNotification::setNotification(
                                ilNotification::TYPE_LM_PAGE,
                                $this->user->getId(),
                                $this->getCurrentPageId(),
                                false
                            );
                            break;

                        case 4:
                            ilNotification::setNotification(
                                ilNotification::TYPE_LM_PAGE,
                                $this->user->getId(),
                                $this->getCurrentPageId(),
                                true
                            );
                            break;
                    }
                    $ilCtrl->redirect($this, "layout");
                }
                $ret = $this->$cmd();
                break;
        }
    }

    /**
     * checks whether offline content generation is activated
     */
    public function offlineMode() : bool
    {
        return $this->offline;
    }

    public function getExportFormat() : string
    {
        return $this->export_format;
    }

    /**
     * this dummy function is needed for offline package creation
     */
    public function nop() : void
    {
    }

    public function attrib2arr(?array $a_attributes) : array
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
    public function getCurrentFrameSet() : array
    {
        return $this->frames;
    }

    /**
     * Determine layout
     */
    public function determineLayout() : string
    {
        return "standard";
    }

    /**
     * @throws ilLMPresentationException
     */
    public function resume() : void
    {
        $this->layout();
    }

    public function layout(
        string $a_xml = "main.xml",
        bool $doShow = true
    ) : string {
        $content = "";
        $tpl = $this->tpl;
        $ilUser = $this->user;
        $layout = $this->determineLayout();

        // xmldocfile is deprecated! Use domxml_open_file instead.
        // But since using relative pathes with domxml under windows don't work,
        // we need another solution:
        $xmlfile = file_get_contents("./Modules/LearningModule/layouts/lm/" . $layout . "/" . $a_xml);
    
        $doc = domxml_open_mem($xmlfile);
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

        // set style sheets
        if (!$this->offlineMode()) {
            $this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
        } else {
            $style_name = $ilUser->getPref("style") . ".css";
            $this->tpl->setVariable("LOCATION_STYLESHEET", "./style/" . $style_name);
        }

        // to make e.g. advanced seletions lists work:
        //			$GLOBALS["tpl"] = $this->tpl;

        $childs = $node->child_nodes();

        foreach ($childs as $child) {
            $child_attr = $this->attrib2arr($child->attributes());

            switch ($child->node_name()) {

                case "ilPage":
                    $this->renderPageTitle();
                    $this->setHeader();
                    $this->ilLMMenu();
                    $this->addHeaderAction();
                    $content = $this->getContent();
                    $content .= $this->ilLMNotes();
                    $additional = $this->ui->renderer()->render($this->additional_content);
                    $this->tpl->setContent($content . $additional);
                    break;

                case "ilGlossary":
                    $this->ilGlossary();
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

        $this->addResourceFiles();

        if ($doShow) {
            $tpl->printToStdout();
        } else {
            $content = $tpl->printToString();
        }

        return ($content);
    }

    protected function addResourceFiles() : void
    {
        iljQueryUtil::initjQuery($this->tpl);
        iljQueryUtil::initjQueryUI($this->tpl);
        ilUIFramework::init($this->tpl);

        if (!$this->offlineMode()) {
            ilAccordionGUI::addJavaScript();
            ilAccordionGUI::addCss();

            $this->tpl->addJavaScript("./Modules/LearningModule/js/LearningModule.js");
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
    
                $this->tpl->addOnLoadCode("il.LearningModule.setSaveUrl('" .
                    $this->ctrl->getLinkTarget($this, "saveFrameUrl", "", false, false) . "');
                        il.LearningModule.openInitFrames();
                        ");

                $this->tpl->addOnLoadCode("il.LearningModule.setTocRefreshUrl('" .
                    $this->ctrl->getLinkTarget($this, "refreshToc", "", false, false) . "');
                        ");
            }

            // from main menu
            //				$this->tpl->addJavascript("./Services/JavaScript/js/Basic.js");
            $this->tpl->addJavaScript("./Services/Navigation/js/ServiceNavigation.js");
            ilYuiUtil::initConnection($this->tpl);
        }
    }

    public function saveFrameUrl() : void
    {
        $store = new ilSessionIStorage("lm");
        $store->set("cf_" . $this->lm->getId(), $this->requested_url);
    }

    public function fullscreen() : string
    {
        return $this->media();
    }

    public function media() : string
    {
        $this->tpl = new ilGlobalTemplate("tpl.fullscreen.html", true, true, "Modules/LearningModule");

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

    public function glossary() : string
    {
        $this->tpl = new ilGlobalTemplate("tpl.glossary_term_output.html", true, true, "Modules/LearningModule");
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

    public function page() : string
    {
        $ilUser = $this->user;
        $this->tpl = new ilGlobalTemplate("tpl.page_fullscreen.html", true, true, "Modules/LearningModule");
        $GLOBALS["tpl"] = $this->tpl;
        $this->renderPageTitle();

        $this->setContentStyles();

        // set style sheets
        if (!$this->offlineMode()) {
            $this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
        } else {
            $style_name = $ilUser->getPref("style") . ".css";
            $this->tpl->setVariable("LOCATION_STYLESHEET", "./style/" . $style_name);
        }

        $this->tpl->setVariable("PAGE_CONTENT", $this->getPageContent());
        if (!$this->offlineMode()) {
            $this->tpl->printToStdout();
        } else {
            return $this->tpl->get();
        }
        return "";
    }

    /**
     * table of contents
     */
    public function ilTOC() : ilLMTOCExplorerGUI
    {
        $fac = new ilLMTOCExplorerGUIFactory();
        $exp = $fac->getExplorer($this->service, "ilTOC");
        $exp->handleCommand();
        return $exp;
    }

    public function getLMPresentationTitle() : string
    {
        return $this->service->getPresentationStatus()->getLMPresentationTitle();
    }

    public function ilLMMenu() : void
    {
        $this->renderTabs("content", $this->getCurrentPageId());
    }

    public function setHeader() : void
    {
        $this->tpl->setTitle($this->getLMPresentationTitle());
        $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_lm.svg"));
    }

    /**
     * output learning module submenu
     */
    public function ilLMSubMenu() : void
    {
        $rbacsystem = $this->rbacsystem;
        if ($this->abstract) {
            return;
        }

        $buttonTarget = ilFrameTargetInfo::_getFrame("MainContent");

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
                $tpl_menu->setVariable(
                    "EDIT_LINK",
                    ILIAS_HTTP_PATH . "/ilias.php?baseClass=ilLMEditorGUI&ref_id=" . $this->requested_ref_id .
                    "&obj_id=" . $page_id . "&to_page=1"
                );
                $tpl_menu->setVariable("EDIT_TXT", $this->lng->txt("edit_page"));
                $tpl_menu->setVariable("EDIT_TARGET", $buttonTarget);
                $tpl_menu->parseCurrentBlock();
            }

            $page_id = $this->getCurrentPageId();

            // permanent link
            $this->tpl->setPermanentLink("pg", 0, $page_id . "_" . $this->lm->getRefId());
        }

        $this->tpl->setVariable("SUBMENU", $tpl_menu->get());
    }

    public function redrawHeaderAction() : void
    {
        echo $this->getHeaderAction(true);
        exit;
    }

    public function addHeaderAction() : void
    {
        $this->tpl->setVariable("HEAD_ACTION", $this->getHeaderAction());
    }

    public function getHeaderAction(
        bool $a_redraw = false
    ) : string {
        if ($this->offline) {
            return "";
        }
        $ilAccess = $this->access;
        $ilSetting = $this->settings;
        $tpl = $this->tpl;

        $lm_id = $this->lm->getId();
        $pg_id = $this->getCurrentPageId();

        $this->lng->loadLanguageModule("content");

        $dispatcher = new ilCommonActionDispatcherGUI(
            ilCommonActionDispatcherGUI::TYPE_REPOSITORY,
            $ilAccess,
            $this->lm->getType(),
            $this->lm->getRefId(),
            $this->lm->getId()
        );
        $dispatcher->setSubObject("pg", $this->getCurrentPageId());

        $this->ctrl->setParameter($this, "embed_mode", $this->embed_mode);
        $this->ctrl->setParameterByClass("ilnotegui", "embed_mode", $this->embed_mode);
        $this->ctrl->setParameterByClass("iltagginggui", "embed_mode", $this->embed_mode);
        ilObjectListGUI::prepareJsLinks(
            $this->ctrl->getLinkTarget($this, "redrawHeaderAction", "", true),
            "",
            $this->ctrl->getLinkTargetByClass(
                array("ilcommonactiondispatchergui", "iltagginggui"),
                "",
                "",
                true,
                false
            ),
            $this->tpl
        );

        $lg = $dispatcher->initHeaderAction();
        if (!$ilSetting->get("disable_notes")) {
            $lg->enableNotes(true);
            if (!$this->embed_mode) {
                $lg->enableComments($this->lm->publicNotes(), false);
            }
        }

        if ($this->lm->hasRating() && !$this->offlineMode()) {
            $lg->enableRating(
                true,
                $this->lng->txt("lm_rating"),
                false,
                array("ilcommonactiondispatchergui", "ilratinggui")
            );
        }

        // notification
        if ($this->user->getId() != ANONYMOUS_USER_ID && !$this->embed_mode) {
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
            return $lg->getHeaderAction($this->tpl);
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
        $notes_gui->setUseObjectTitleHeader(false);

        if ($ilAccess->checkAccess("write", "", $this->requested_ref_id) &&
            $ilSetting->get("comments_del_tutor", '1')) {
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
            $html = $notes_gui->getCommentsHTML();
        }
        return $html;
    }

    public function ilLocator() : void
    {
        global $DIC;
        $ltiview = $DIC["lti"];
        $ilLocator = $this->locator;

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
                            ilStr::shortenTextExtended(
                                ilStructureObject::_getPresentationTitle(
                                    $row["child"],
                                    ilLMObject::CHAPTER_TITLE,
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
                            ilStr::shortenTextExtended($this->getLMPresentationTitle(), 50, true),
                            $this->linker->getLink("layout", 0, $frame_param),
                            $frame_target,
                            $this->requested_ref_id
                        );
                    }
                }
            }
        } else {        // lonely page
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

        $this->tpl->setLocator();
    }

    /**
     * Get the current page id
     */
    public function getCurrentPageId() : ?int
    {
        return $this->service->getNavigationStatus()->getCurrentPage();
    }

    /**
     * Set content style
     */
    protected function setContentStyles() : void
    {
        // content style
        $this->content_style_gui->addCss(
            $this->tpl,
            $this->lm->getRefId()
        );
        $this->tpl->addCss(ilObjStyleSheet::getSyntaxStylePath());
    }

    /**
     * Set system style
     */
    protected function setSystemStyle() : void
    {
        $this->tpl->addCss(ilUtil::getStyleSheetLocation());
    }

    public function getContent(
        bool $skip_nav = false
    ) : string {
        $this->fill_on_load_code = true;
        $this->setContentStyles();

        $tpl = new ilTemplate("tpl.lm_content.html", true, true, "Modules/LearningModule/Presentation");

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

        if (!$skip_nav) {
            $tpl->setVariable("TOP_NAVIGATION", $navigation_renderer->renderTop());
            $tpl->setVariable("BOTTOM_NAVIGATION", $navigation_renderer->renderBottom());
        }
        $tpl->setVariable("PAGE_CONTENT", $this->getPageContent());
        $tpl->setVariable("RATING", $this->renderRating());

        return $tpl->get();
    }

    protected function getPageContent() : string
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

    protected function renderRating() : string
    {
        // rating
        $rating = "";
        if ($this->lm->hasRatingPages() && !$this->offlineMode()) {
            $rating_gui = new ilRatingGUI();
            $rating_gui->setObject($this->lm->getId(), "lm", $this->getCurrentPageId(), "lm");
            $rating_gui->setYourRatingText($this->lng->txt("lm_rate_page"));

            $this->ctrl->setParameter($this, "pgid", $this->getCurrentPageId());
            $this->tpl->addOnLoadCode("il.LearningModule.setRatingUrl('" .
                $this->ctrl->getLinkTarget($this, "updatePageRating", "", true, false) .
                "')");
            $this->ctrl->setParameter($this, "pgid", "");

            $rating = '<div id="ilrtrpg" style="text-align:right">' .
                $rating_gui->getHTML(true, true, "il.LearningModule.saveRating(%rating%);") .
                "</div>";
        }
        return $rating;
    }

    public function updatePageRating() : void
    {
        $ilUser = $this->user;

        $pg_id = $this->requested_pg_id;
        if (!$this->ctrl->isAsynch() || !$pg_id) {
            exit();
        }

        $rating = $this->service->getRequest()->getRating();
        if ($rating) {
            ilRating::writeRatingForUserAndObject(
                $this->lm->getId(),
                "lm",
                $pg_id,
                "lm",
                $ilUser->getId(),
                $rating
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
        $rating->setObject($this->lm->getId(), "lm", $pg_id, "lm");
        $rating->setYourRatingText($this->lng->txt("lm_rate_page"));

        echo $rating->getHTML(true, true, "il.LearningModule.saveRating(%rating%);");

        echo $this->tpl->getOnLoadCodeForAsynch();
        exit();
    }

    public function basicPageGuiInit(\ilPageObjectGUI $a_page_gui) : void
    {
        $a_page_gui->setStyleId(
            $this->content_style_domain->getEffectiveStyleId()
        );
        if (!$this->offlineMode()) {
            $a_page_gui->setOutputMode("presentation");
            $this->fill_on_load_code = true;
        } else {
            $a_page_gui->setOutputMode("offline");
            $a_page_gui->setOfflineDirectory($this->getOfflineDirectory());
            $this->fill_on_load_code = false;
        }
        if (!$this->offlineMode()) {
            $this->ctrl->setParameter($this, "obj_id", $this->getCurrentPageId());        // see #22403
        }
        $a_page_gui->setFileDownloadLink($this->linker->getLink("downloadFile"));
        $a_page_gui->setSourcecodeDownloadScript($this->linker->getLink(
            "sourcecodeDownload",
            $this->getCurrentPageId()
        ));
        if (!$this->offlineMode()) {
            $this->ctrl->setParameter($this, "obj_id", $this->requested_obj_id);
        }
        $a_page_gui->setFullscreenLink($this->linker->getLink("fullscreen"));
        $a_page_gui->setSourcecodeDownloadScript($this->linker->getLink("download_paragraph"));
    }

    public function ilGlossary() : void
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

    public function ilMedia() : void
    {
        $pg_frame = "";
        $this->setContentStyles();

        $this->renderPageTitle();

        $this->tpl->setCurrentBlock("ilMedia");

        $med_links = ilMediaItem::_getMapAreasIntLinks($this->requested_mob_id);
        $link_xml = $this->linker->getLinkXML($med_links);

        $media_obj = new ilObjMediaObject($this->requested_mob_id);
        if (!empty($this->requested_pg_id)) {
            $pg_obj = $this->getLMPage($this->requested_pg_id, $this->requested_pg_type);
            $pg_obj->buildDom();

            $xml = "<dummy>";
            // todo: we get always the first alias now (problem if mob is used multiple
            // times in page)
            $xml .= $pg_obj->getMediaAliasElement($this->requested_mob_id);
        } else {
            $xml = "<dummy>";
            // todo: we get always the first alias now (problem if mob is used multiple
            // times in page)
            $xml .= $media_obj->getXML(IL_MODE_ALIAS);
        }
        $xml .= $media_obj->getXML(IL_MODE_OUTPUT);
        $xml .= $link_xml;
        $xml .= "</dummy>";

        $xsl = file_get_contents("./Services/COPage/xsl/page.xsl");
        $args = array('/_xml' => $xml, '/_xsl' => $xsl);
        $xh = xslt_create();

        if (!$this->offlineMode()) {
            $wb_path = ilFileUtils::getWebspaceDir("output") . "/";
        } else {
            $wb_path = "";
        }

        $mode = ($this->requested_cmd == "fullscreen")
            ? "fullscreen"
            : "media";
        $enlarge_path = ilUtil::getImagePath("enlarge.svg", false, "output", $this->offlineMode());
        $fullscreen_link =
            $this->linker->getLink("fullscreen");
        $params = array('mode' => $mode,
                        'enlarge_path' => $enlarge_path,
                        'link_params' => "ref_id=" . $this->lm->getRefId(),
                        'fullscreen_link' => $fullscreen_link,
                        'ref_id' => $this->lm->getRefId(),
                        'pg_frame' => $pg_frame,
                        'webspace_path' => $wb_path
        );
        $output = xslt_process($xh, "arg:/_xml", "arg:/_xsl", null, $args, $params);

        xslt_free($xh);

        // unmask user html
        $this->tpl->setVariable("MEDIA_CONTENT", $output);

        // add js
        ilObjMediaObjectGUI::includePresentationJS($this->tpl);
    }

    public function ilJavaScript(
        string $a_inline = "",
        string $a_file = "",
        string $a_location = ""
    ) : void {
        if ($a_inline != "") {
            $js_tpl = new ilTemplate($a_inline, true, false, $a_location);
            $js = $js_tpl->get();
            $this->tpl->setVariable("INLINE_JS", $js);
        }
    }

    /**
     * this one is called from the info button in the repository
     * not very nice to set cmdClass/Cmd manually, if everything
     * works through ilCtrl in the future this may be changed
     */
    public function infoScreen() : void
    {
        $this->ctrl->setCmd("showSummary");
        $this->ctrl->setCmdClass("ilinfoscreengui");
        $this->outputInfoScreen();
    }

    /**
     * info screen call from inside learning module
     */
    public function showInfoScreen() : void
    {
        $this->outputInfoScreen();
    }

    protected function initScreenHead(
        string $a_active_tab = "info"
    ) : void {
        $ilAccess = $this->access;
        $ilLocator = $this->locator;

        $this->renderPageTitle();

        $this->tpl->loadStandardTemplate();
        $this->tpl->setTitle($this->getLMPresentationTitle());
        $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_lm.svg"));

        $this->renderTabs($a_active_tab, 0);

        // Full locator, if read permission is given
        if ($ilAccess->checkAccess("read", "", $this->requested_ref_id)) {
            $this->ilLocator();
        } else {
            $ilLocator->addRepositoryItems();
            $this->tpl->setLocator();
        }
    }

    /**
     * info screen
     */
    public function outputInfoScreen() : string
    {
        $ilAccess = $this->access;

        $this->initScreenHead();

        $this->lng->loadLanguageModule("meta");

        $info = new ilInfoScreenGUI($this->lm_gui);
        $info->enablePrivateNotes();
        //$info->enableLearningProgress();
        $info->enableNews();
        if ($ilAccess->checkAccess("write", "", $this->requested_ref_id)) {
            $news_set = new ilSetting("news");
            $enable_internal_rss = $news_set->get("enable_rss_for_internal");

            $info->enableNewsEditing();

            if ($enable_internal_rss) {
                $info->setBlockProperty("news", "settings", true);
            }
        }

        // show standard meta data section
        $info->addMetaDataSections($this->lm->getId(), 0, $this->lm->getType());

        $this->lng->loadLanguageModule("copg");
        $est_reading_time = $this->reading_time_manager->getReadingTime($this->lm->getId());
        if (!is_null($est_reading_time)) {
            $info->addProperty(
                $this->lng->txt("copg_est_reading_time"),
                sprintf($this->lng->txt("copg_x_minutes"), $est_reading_time)
            );
        }

        if ($this->offlineMode()) {
            $this->tpl->setContent($info->getHTML());
            return $this->tpl->get();
        } else {
            // forward the command
            $this->ctrl->forwardCommand($info);
            //$this->tpl->setContent("aa");
            $this->tpl->printToStdout();
        }
        return "";
    }

    /**
     * show selection screen for print view
     */
    public function showPrintViewSelection() : void
    {
        $ilUser = $this->user;
        $lng = $this->lng;

        if (!$this->lm->isActivePrintView() || !$this->lm->isActiveLMMenu()) {
            return;
        }

        $disabled = false;
        $img_alt = "";

        $tpl = new ilTemplate("tpl.lm_print_selection.html", true, true, "Modules/LearningModule");

        $this->ctrl->setParameterByClass("illmpresentationgui", "obj_id", $this->requested_obj_id);
        $tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

        $nodes = $this->lm_tree->getSubTree($this->lm_tree->getNodeData($this->lm_tree->getRootId()));
        $nodes = $this->filterNonAccessibleNode($nodes);

        /* this was written to _POST["item"] before, but never used?
        $items = $this->service->getRequest()->getItems();
        if (count($items) == 0) {
            if ($this->requested_obj_id != "") {
                $items[$this->requested_obj_id] = "y";
            } else {
                $items[1] = "y";
            }
        }*/

        $this->initPrintViewSelectionForm();

        foreach ($nodes as $node) {
            $img_src = "";
            $disabled = false;
            $img_alt = "";

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

            $text = "";
            $img_alt = "";
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

                    if ($ilUser->getId() === ANONYMOUS_USER_ID &&
                        $this->lm_gui->getObject()->getPublicAccessMode() == "selected") {
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
                            ilLMObject::CHAPTER_TITLE,
                            $this->lm->isActiveNumbering(),
                            $this->lm_set->get("time_scheduled_page_activation"),
                            false,
                            0,
                            $this->lang
                        );
                    if ($ilUser->getId() === ANONYMOUS_USER_ID &&
                        $this->lm_gui->getObject()->getPublicAccessMode() == "selected") {
                        if (!ilLMObject::_isPagePublic($node["obj_id"])) {
                            $disabled = true;
                            $text .= " (" . $this->lng->txt("cont_no_access") . ")";
                        }
                    }
                    $img_src = ilUtil::getImagePath("icon_st.svg");
                    $img_alt = $lng->txt("icon") . " " . $lng->txt("st");
                    break;
            }

            if (!ilObjContentObject::_checkPreconditionsOfPage(
                $this->lm->getRefId(),
                $this->lm->getId(),
                $node["obj_id"]
            )) {
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

            if ($ilUser->getId() === ANONYMOUS_USER_ID &&
                $this->lm_gui->getObject()->getPublicAccessMode() == "selected") {
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

        $tpl->setVariable("ITEM_SELECTION", $f);

        $modal = $this->ui->factory()->modal()->roundtrip(
            $this->lng->txt("cont_print_view"),
            $this->ui->factory()->legacy($tpl->get())
        );
        echo $this->ui->renderer()->render($modal);
        exit();
    }

    protected function filterNonAccessibleNode(
        array $nodes
    ) : array {
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

    public function initPrintViewSelectionForm() : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->form = new ilPropertyFormGUI();
        $this->form->setForceTopButtons(true);

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

        $this->form->setTitle(" ");
        $this->form->setFormAction($ilCtrl->getFormAction($this));
    }

    public function showPrintView() : void
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $tabs = $this->tabs;
        $header_page_content = "";
        $footer_page_content = "";
        $chapter_title = "";
        $did_chap_page_header = false;
        $description = "";

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
        $sel_type = $this->service->getRequest()->getSelectedType();
        $sel_obj_ids = $this->service->getRequest()->getSelectedObjIds();
        if ($sel_type == "page") {
            if (!in_array($c_obj_id, $sel_obj_ids)) {
                $sel_obj_ids[] = $c_obj_id;
            }
        }
        if ($sel_type == "chapter" && $c_obj_id > 0) {
            $path = $this->lm_tree->getPathFull($c_obj_id);
            $chap_id = $path[1]["child"];
            if ($chap_id > 0) {
                $sel_obj_ids[] = $chap_id;
            }
        }

        $this->setContentStyles();

        $tpl = new ilTemplate("tpl.lm_print_view.html", true, true, "Modules/LearningModule");

        // set title header
        $this->tpl->setTitle($this->getLMPresentationTitle());

        $nodes = $this->lm_tree->getSubTree($this->lm_tree->getNodeData($this->lm_tree->getRootId()));

        $act_level = 99999;
        $activated = false;

        $glossary_links = array();
        $output_header = false;
        $media_links = array();

        // get header and footer
        if ($this->lm->getFooterPage() > 0 && !$this->lm->getHideHeaderFooterPrint()) {
            if (ilLMObject::_exists($this->lm->getFooterPage())) {
                $page_object_gui = $this->getLMPageGUI($this->lm->getFooterPage());
                $page_object_gui->setStyleId(
                    $this->content_style_domain->getEffectiveStyleId()
                );

                // determine target frames for internal links
                $page_object_gui->setLinkFrame($this->requested_frame);
                $page_object_gui->setOutputMode("print");
                $page_object_gui->setPresentationTitle("");
                $page_object_gui->setFileDownloadLink("#");
                $page_object_gui->setFullscreenLink("#");
                $page_object_gui->setSourcecodeDownloadScript("#");
                $footer_page_content = $page_object_gui->showPage();
            }
        }
        if ($this->lm->getHeaderPage() > 0 && !$this->lm->getHideHeaderFooterPrint()) {
            if (ilLMObject::_exists($this->lm->getHeaderPage())) {
                $page_object_gui = $this->getLMPageGUI($this->lm->getHeaderPage());
                $page_object_gui->setStyleId(
                    $this->content_style_domain->getEffectiveStyleId()
                );

                // determine target frames for internal links
                $page_object_gui->setLinkFrame($this->requested_frame);
                $page_object_gui->setOutputMode("print");
                $page_object_gui->setPresentationTitle("");
                $page_object_gui->setFileDownloadLink("#");
                $page_object_gui->setFullscreenLink("#");
                $page_object_gui->setSourcecodeDownloadScript("#");
                $header_page_content = $page_object_gui->showPage();
            }
        }

        // add free selected pages
        if (count($sel_obj_ids) > 0) {
            foreach ($sel_obj_ids as $k) {
                if ($k > 0 && !$this->lm_tree->isInTree($k)) {
                    if (ilLMObject::_lookupType($k) == "pg") {
                        $nodes[] = array("obj_id" => $k, "type" => "pg", "free" => true);
                    }
                }
            }
        } else {
            $this->tpl->setOnScreenMessage('failure', $lng->txt("cont_print_no_page_selected"), true);
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
                if (in_array($node["obj_id"], $sel_obj_ids)) {
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
                ilObjContentObject::_checkPreconditionsOfPage(
                    $this->lm->getRefId(),
                    $this->lm->getId(),
                    $node["obj_id"]
                )) {
                // output learning module header
                if ($node["type"] == "du") {
                    $output_header = true;
                }

                // output chapter title
                if ($node["type"] == "st") {
                    if ($ilUser->getId() === ANONYMOUS_USER_ID &&
                        $this->lm_gui->getObject()->getPublicAccessMode() == "selected") {
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

                    if ($this->lm->getPageHeader() == ilLMObject::CHAPTER_TITLE) {
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
                if ($node["type"] === "pg") {
                    if ($ilUser->getId() === ANONYMOUS_USER_ID &&
                        $this->lm_gui->getObject()->getPublicAccessMode() === "selected") {
                        if (!ilLMObject::_isPagePublic($node["obj_id"])) {
                            continue;
                        }
                    }

                    $tpl->setCurrentBlock("print_item");

                    // get page
                    $page_id = $node["obj_id"];
                    $page_object_gui = $this->getLMPageGUI($page_id);
                    $page_object = $page_object_gui->getPageObject();
                    $page_object_gui->setStyleId(
                        $this->content_style_domain->getEffectiveStyleId()
                    );

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

                    if ($this->lm->getPageHeader() == ilLMObject::CHAPTER_TITLE) {
                        if ($did_chap_page_header) {
                            $hcont = "";
                        }
                        if ($nodes[$node_key + 1]["type"] == "pg" &&
                            !($nodes[$node_key + 1]["depth"] <= $act_level
                                && !in_array($nodes[$node_key + 1]["obj_id"], $sel_obj_ids))) {
                            $fcont = "";
                        }
                    }

                    $page_object_gui->setFileDownloadLink("#");
                    $page_object_gui->setFullscreenLink("#");
                    $page_object_gui->setSourcecodeDownloadScript("#");
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
            $terms = ilArrayUtil::sortArray($terms, "term", "asc");
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
                    $page_gui->setSourcecodeDownloadScript("#");
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
                                    ilFileUtils::getWebspaceDir("output") . "/mobs/mm_" . $id .
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
                    && ilObjContentObject::_checkPreconditionsOfPage(
                        $this->lm->getRefId(),
                        $this->lm->getId(),
                        $node2["obj_id"]
                    )) {
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
                            ilLMObject::CHAPTER_TITLE,
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
    public function downloadFile() : void
    {
        $page_gui = $this->getLMPageGUI($this->getCurrentPageId());
        $page_gui->downloadFile();
    }

    /**
     * show download list
     */
    public function showDownloadList() : void
    {
        if (!$this->lm->isActiveDownloads() || !$this->lm->isActiveLMMenu()) {
            return;
        }
        $tpl = new ilTemplate("tpl.lm_download_list.html", true, true, "Modules/LearningModule");

        // output copyright information
        $md = new ilMD($this->lm->getId(), 0, $this->lm->getType());
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

        $download_table = new ilLMDownloadTableGUI($this, "showDownloadList", $this->lm);
        $tpl->setVariable("DOWNLOAD_TABLE", $download_table->getHTML());
        //$this->tpl->printToStdout();

        $modal = $this->ui->factory()->modal()->roundtrip(
            $this->lng->txt("download"),
            $this->ui->factory()->legacy($tpl->get())
        );
        echo $this->ui->renderer()->render($modal);
        exit();
    }

    /**
     * send download file (xml/html)
     */
    public function downloadExportFile() : void
    {
        if (!$this->lm->isActiveDownloads() || !$this->lm->isActiveLMMenu()) {
            return;
        }

        $type = $this->requested_type;
        $base_type = explode("_", $type);
        $base_type = $base_type[0];
        $file = $this->lm->getPublicExportFile($base_type);
        if ($this->lm->getPublicExportFile($base_type) != "") {
            $dir = $this->lm->getExportDirectory($type);
            if (is_file($dir . "/" . $file)) {
                ilFileDelivery::deliverFileLegacy($dir . "/" . $file, $file);
                exit;
            }
        }
    }

    /**
     * Get focused link (used in learning objectives courses)
     * @param int $a_ref_id        reference id of learning module
     * @param int $a_obj_id        chapter or page id
     * @param int $a_return_ref_id return ref id
     */
    public function getFocusLink(
        int $a_ref_id,
        int $a_obj_id,
        int $a_return_ref_id
    ) : string {
        return "ilias.php?baseClass=ilLMPresentationGUI&amp;ref_id=" . $a_ref_id . "&amp;obj_id=" . $a_obj_id . "&amp;focus_id=" .
            $a_obj_id . "&amp;focus_return=" . $a_return_ref_id;
    }

    public function showMessageScreen(
        string $a_content
    ) : void {
        // content style
        $this->setContentStyles();

        $tpl = new ilTemplate("tpl.page_message_screen.html", true, true, "Modules/LearningModule");
        $tpl->setVariable("TXT_PAGE_NO_PUBLIC_ACCESS", $a_content);

        $this->tpl->setVariable("PAGE_CONTENT", $tpl->get());
    }

    /**
     * Show info message, if page is not accessible in public area
     */
    public function showNoPublicAccess() : void
    {
        $this->showMessageScreen($this->lng->txt("msg_page_no_public_access"));
    }

    /**
     * Show info message, if page is not accessible in public area
     */
    public function showNoPageAccess() : void
    {
        $this->showMessageScreen($this->lng->txt("msg_no_page_access"));
    }

    /**
     * Show message if navigation to page is not allowed due to unanswered
     * questions.
     */
    public function showNavRestrictionDueToQuestions() : void
    {
        $this->showMessageScreen($this->lng->txt("cont_no_page_access_unansw_q"));
    }

    public function getSourcecodeDownloadLink() : string
    {
        if (!$this->offlineMode()) {
            return $this->ctrl->getLinkTarget($this, "");
        } else {
            return "";
        }
    }

    public function getOfflineDirectory() : string
    {
        return $this->offline_directory;
    }

    /**
     * store paragraph into file directory
     * files/codefile_$pg_id_$paragraph_id/downloadtitle
     */
    public function handleCodeParagraph(
        int $page_id,
        int $paragraph_id,
        string $title,
        string $text
    ) : void {
        $directory = $this->getOfflineDirectory() . "/codefiles/" . $page_id . "/" . $paragraph_id;
        ilFileUtils::makeDirParents($directory);
        $file = $directory . "/" . $title;
        if (!($fp = fopen($file, "w+"))) {
            die("<b>Error</b>: Could not open \"" . $file . "\" for writing" .
                " in <b>" . __FILE__ . "</b> on line <b>" . __LINE__ . "</b><br />");
        }
        chmod($file, 0770);
        fwrite($fp, $text);
        fclose($fp);
    }

    // #8613
    protected function renderPageTitle() : void
    {
        $this->tpl->setHeaderPageTitle($this->getLMPresentationTitle());
    }

    public function getLMPageGUI(int $a_id) : ilLMPageGUI
    {
        if ($this->lang != "-" && ilPageObject::_exists("lm", $a_id, $this->lang)) {
            return new ilLMPageGUI($a_id, 0, false, $this->lang);
        }
        if ($this->lang != "-" && ilPageObject::_exists("lm", $a_id, $this->ot->getFallbackLanguage())) {
            return new ilLMPageGUI($a_id, 0, false, $this->ot->getFallbackLanguage());
        }
        return new ilLMPageGUI($a_id);
    }

    public function getLMPage(
        int $a_id,
        string $a_type = ""
    ) : ilPageObject {
        $type = ($a_type == "mep")
            ? "mep"
            : "lm";

        $lang = $this->lang;
        if (!ilPageObject::_exists($type, $a_id, $lang)) {
            $lang = "-";
            if ($this->lang != "-" && ilPageObject::_exists($type, $a_id, $this->ot->getFallbackLanguage())) {
                $lang = $this->ot->getFallbackLanguage();
            }
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
    public function refreshToc() : void
    {
        $exp = $this->ilTOC();

        echo $exp->getHTML() .
            "<script>" . $exp->getOnLoadCode() . "</script>";
        exit;
    }

    /**
     * Generate new ilNote and send Notifications to the users informing
     * that there are new comments in the LM.
     */
    public function observeNoteAction(
        int $a_lm_id,
        int $a_page_id,
        string $a_type,
        string $a_action,
        int $a_note_id
    ) : void {
        $note = $this->notes->getById($a_note_id);
        $text = $note->getText();

        $notification = new ilLearningModuleNotification(
            ilLearningModuleNotification::ACTION_COMMENT,
            ilNotification::TYPE_LM_PAGE,
            $this->lm,
            $a_page_id,
            $text
        );

        $notification->send();
    }

    // render menu
    protected function renderTabs(
        string $active_tab,
        int $current_page_id
    ) : void {
        $menu_editor = new ilLMMenuEditor();
        $menu_editor->setObjId($this->lm->getId());

        $navigation_renderer = new ilLMMenuRendererGUI(
            $this->getService(),
            $this->tabs,
            $this->toolbar,
            $current_page_id,
            $active_tab,
            $this->getExportFormat(),
            $this->export_all_languages,
            $this->lm,
            $this->offlineMode(),
            $menu_editor,
            $this->lang,
            $this->ctrl,
            $this->access,
            $this->user,
            $this->lng,
            $this->tpl,
            function ($additional_content) {
                $this->additional_content[] = $additional_content;
            }
        );
        $navigation_renderer->render();
    }

    /**
     * Get HTML (called by kiosk mode through ilCtrl)
     */
    public function getHTML(array $pars) : string
    {
        $this->addResourceFiles();
        switch ($pars["cmd"]) {
            case "layout":
                $tpl = new ilTemplate("tpl.embedded_view.html", true, true, "Modules/LearningModule");
                $tpl->setVariable("HEAD_ACTION", $this->getHeaderAction());
                $tpl->setVariable("PAGE_RATING", $this->renderRating());
                $tpl->setVariable("PAGE", $this->getContent(true));
                $tpl->setVariable("COMMENTS", $this->ilLMNotes());
                return $tpl->get();
        }
        return "";
    }
}
