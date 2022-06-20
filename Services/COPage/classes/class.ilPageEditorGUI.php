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

use ILIAS\COPage\Editor\EditSessionRepository;
use ILIAS\COPage\Page\EditGUIRequest;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Page Editor GUI class
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilPageEditorGUI: ilPCParagraphGUI, ilPCTableGUI, ilPCTableDataGUI
 * @ilCtrl_Calls ilPageEditorGUI: ilPCMediaObjectGUI, ilPCListGUI, ilPCListItemGUI
 * @ilCtrl_Calls ilPageEditorGUI: ilPCFileListGUI, ilPCFileItemGUI, ilObjMediaObjectGUI
 * @ilCtrl_Calls ilPageEditorGUI: ilPCSourceCodeGUI, ilInternalLinkGUI, ilPCQuestionGUI
 * @ilCtrl_Calls ilPageEditorGUI: ilPCSectionGUI, ilPCDataTableGUI, ilPCResourcesGUI
 * @ilCtrl_Calls ilPageEditorGUI: ilPCMapGUI, ilPCPluggedGUI, ilPCTabsGUI, ilPCTabGUI, IlPCPlaceHolderGUI
 * @ilCtrl_Calls ilPageEditorGUI: ilPCContentIncludeGUI, ilPCLoginPageElementGUI
 * @ilCtrl_Calls ilPageEditorGUI: ilPCInteractiveImageGUI, ilPCProfileGUI, ilPCVerificationGUI
 * @ilCtrl_Calls ilPageEditorGUI: ilPCBlogGUI, ilPCQuestionOverviewGUI, ilPCSkillsGUI
 * @ilCtrl_Calls ilPageEditorGUI: ilPCConsultationHoursGUI, ilPCMyCoursesGUI, ilPCAMDPageListGUI
 * @ilCtrl_Calls ilPageEditorGUI: ilPCGridGUI, ilPCGridCellGUI, ilPageEditorServerAdapterGUI
 */
class ilPageEditorGUI
{
    protected ServerRequestInterface $http_request;
    protected EditGUIRequest $request;
    protected EditSessionRepository $edit_repo;
    protected ilPageContent $content_obj;
    protected ilPropertyFormGUI $form;
    protected string $page_back_title = "";
    protected ilPageObjectGUI $page_gui;
    protected string $int_link_return = "";
    protected ilTabsGUI $tabs_gui;
    protected ilHelpGUI $help;
    protected ilObjUser $user;
    protected ilAccessHandler $access;
    public ilGlobalTemplateInterface $tpl;
    public ilLanguage $lng;
    public ilCtrl $ctrl;
    public ilObjectDefinition $objDefinition;
    public ilPageObject $page;
    public string $target_script = "";
    public string $return_location = "";
    public string $header = "";
    public ?ilPageContent $cont_obj = null;
    public bool $enable_keywords = false;
    public bool $enable_anchors = false;
    protected ilLogger $log;
    protected \ILIAS\DI\UIServices $ui;
    protected \ILIAS\GlobalScreen\ScreenContext\ContextServices $tool_context;
    protected string $requested_hier_id;
    protected string $requested_pc_id;
    protected string $requested_pcid;           // one of these should go
    protected string $requested_pl_pc_id;       // placeholder pc id
    protected string $requested_ctype;
    protected string $requested_cname;
    protected int $requested_mob_id;

    public function __construct(
        ilPageObject $a_page_object,
        ilPageObjectGUI $a_page_object_gui
    ) {
        global $DIC;

        $this->help = $DIC["ilHelp"];
        $this->user = $DIC->user();
        $this->access = $DIC->access();
        $tpl = $DIC["tpl"];
        $lng = $DIC->language();
        $objDefinition = $DIC["objDefinition"];
        $ilCtrl = $DIC->ctrl();
        $ilTabs = $DIC->tabs();

        $this->ui = $DIC->ui();

        $this->request = $DIC->copage()->internal()->gui()->page()->editRequest();
        $this->requested_hier_id = $this->request->getHierId();
        $this->requested_pc_id = $this->request->getPCId();
        $this->requested_pl_pc_id = $this->request->getPlaceholderPCId();
        $this->requested_ctype = $this->request->getCType();
        $this->requested_cname = $this->request->getCName();
        $this->requested_mob_id = $this->request->getMobId();

        $this->log = ilLoggerFactory::getLogger('copg');

        $this->tool_context = $DIC->globalScreen()->tool()->context();

        // initiate variables
        $this->http_request = $DIC->http()->request();
        $this->ctrl = $ilCtrl;
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->objDefinition = $objDefinition;
        $this->tabs_gui = $ilTabs;
        $this->page = $a_page_object;
        $this->page_gui = $a_page_object_gui;

        $this->ctrl->saveParameter($this, array("hier_id", "pc_id"));

        $this->edit_repo = $DIC
            ->copage()
            ->internal()
            ->repo()
            ->edit();
    }

    /**
     * set header title
     */
    public function setHeader(string $a_header) : void
    {
        $this->header = $a_header;
    }

    public function getHeader() : string
    {
        return $this->header;
    }

    public function returnToContext() : void
    {
        $this->ctrl->returnToParent($this);
    }

    public function setIntLinkReturn(string $a_return) : void
    {
        $this->int_link_return = $a_return;
    }

    public function setPageBackTitle(string $a_title) : void
    {
        $this->page_back_title = $a_title;
    }

    /**
     * execute command
     */
    public function executeCommand() : string
    {
        $ilCtrl = $this->ctrl;
        $ilHelp = $this->help;
        $this->log->debug("begin =========================");
        $ctype = "";
        $cont_obj = null;

        $ret = "";
        $add_type = "";

        // Step BC (basic command determination)
        // determine cmd, cmdClass, hier_id and pc_id
        $cmd = $this->ctrl->getCmd("displayPage");
        $cmdClass = strtolower($this->ctrl->getCmdClass());

        $hier_id = $this->requested_hier_id;
        $pc_id = $this->requested_pc_id;
        $new_hier_id = $this->request->getString("new_hier_id");
        if ($new_hier_id != "") {
            $hier_id = $new_hier_id;
        }

        $this->log->debug("step BC: cmd:$cmd, cmdClass:$cmdClass, hier_id: $hier_id, pc_id: $pc_id");

        // Step EC (exec_ command handling)
        // handle special exec_ commands, modify pc, hier_id
        if (substr($cmd, 0, 5) == "exec_") {
            // check whether pc id is given
            $pca = explode(":", $this->ctrl->getCmd());
            $pc_id = $pca[1];
            $cmd = explode("_", $pca[0]);
            unset($cmd[0]);
            $hier_id = implode("_", $cmd);
            $cmd = $this->request->getString("command" . $hier_id);
        }
        $this->log->debug("step EC: cmd:$cmd, hier_id: $hier_id, pc_id: $pc_id");

        // Step CC (handle table container (and similar) commands
        // ... strip "c" "r" of table ids from hierarchical id
        $first_hier_character = substr($hier_id, 0, 1);
        if ($first_hier_character == "c" ||
            $first_hier_character == "r" ||
            $first_hier_character == "g" ||
            $first_hier_character == "i") {
            $hier_id = substr($hier_id, 1);
        }
        $this->log->debug("step CC: cmd:$cmd, hier_id: $hier_id, pc_id: $pc_id");

        // Step B (build dom, and ids in XML)
        $this->page->buildDom();
        $this->page->addHierIDs();


        // Step CS (strip base command)
        $com = null;
        if ($cmdClass != "ilfilesystemgui") {
            $com = explode("_", $cmd);
            $cmd = $com[0];
        }
        $this->log->debug("step CS: cmd:$cmd");


        // Step NC (determine next class)
        $next_class = $this->ctrl->getNextClass($this);
        $this->log->debug("step NC: next class: " . $next_class);


        // Step PH (placeholder handling, placeholders from preview mode come without hier_id)
        if ($next_class == "ilpcplaceholdergui" && $hier_id == "" && $this->requested_pl_pc_id != "") {
            $hid = $this->page->getHierIdsForPCIds(array($this->requested_pl_pc_id));
            $hier_id = $hid[$this->requested_pl_pc_id];
        }
        $this->log->debug("step PH: next class: " . $next_class);

        if ($com[0] == "insert" || $com[0] == "create") {
            // Step CM (creation mode handling)
            $cmd = $com[0];
            $ctype = $com[1] ?? "";				// note ctype holds type if cmdclass is empty, but also subcommands if not (e.g. applyFilter in ilpcmediaobjectgui)
            $add_type = $this->request->getString("pluginName");
            if ($ctype == "mob") {
                $ctype = "media";
            }

            $this->log->debug("step CM: cmd: " . $cmd . ", ctype: " . $ctype . ", add_type: " . $add_type);
        } else {
            $this->log->debug("step LM: cmd: " . $cmd . ", cmdClass: " . $cmdClass);

            // Step PR (get content object and return to parent)
            $this->log->debug("before PR: cmdClass: $cmdClass, nextClass: $next_class" .
                ", hier_id: " . $hier_id . ", pc_id: " . $pc_id . ")");
            // note: ilinternallinkgui for page: no cont_obj is received
            // ilinternallinkgui for mob: cont_obj is received
            if ($this->requested_ctype == "" && $this->requested_cname == "" &&
                $cmd != "insertFromClipboard" && $cmd != "pasteFromClipboard" &&
                $cmd != "setMediaMode" && $cmd != "copyLinkedMediaToClipboard" &&
                $cmd != "activatePage" && $cmd != "deactivatePage" &&
                $cmd != "copyLinkedMediaToMediaPool" && $cmd != "showSnippetInfo" &&
                $cmd != "delete" && $cmd != "paste" &&
                $cmd != "cancelDeleteSelected" && $cmd != "confirmedDeleteSelected" &&
                $cmd != "copy" && $cmd != "cut" &&
                ($cmd != "displayPage" || $this->request->getString("editImagemapForward_x") != "") &&
                $cmd != "activate" && $cmd != "characteristic" &&
                $cmd != "assignCharacteristic" &&
                $cmdClass != "ilrepositoryselector2inputgui" &&
                $cmdClass != "ilpageeditorserveradaptergui" &&
                $cmd != "cancelCreate" && $cmd != "popup" &&
                $cmdClass != "ileditclipboardgui" && $cmd != "addChangeComment" &&
                ($cmdClass != "ilinternallinkgui" || ($next_class == "ilpcmediaobjectgui"))) {
                $cont_obj = $this->page->getContentObject($hier_id, $pc_id);
                if (!is_object($cont_obj)) {
                    $this->log->debug("returnToParent");
                    $ilCtrl->returnToParent($this);
                }
                $ctype = $cont_obj->getType();
            }
        }

        // Step NC (handle empty next class)
        if ($this->requested_ctype != "" || $this->requested_cname != "") {
            $ctype = $this->requested_ctype;
            if ($this->requested_cname != "") {
                $pc_def = ilCOPagePCDef::getPCDefinitionByName($this->requested_cname);
                $ctype = $pc_def["pc_type"];
            }
            $pc_id = $this->requested_pc_id;
            $hier_id = $this->requested_hier_id;
            if (!in_array($cmd, ["insert", "create"])) {
                $cont_obj = $this->page->getContentObject($hier_id, $pc_id);
            }
        }
        // this fixes e.g. #31214
        if ($pc_id != "" && $hier_id == "") {
            $hier_id = $this->page->getHierIdForPcId($pc_id);
        }
        if ($ctype != "media" || !is_object($cont_obj)) {
            if ($this->getHeader() != "") {
                $this->tpl->setTitle($this->getHeader());
            }
        }

        $this->cont_obj = $cont_obj;


        $this->ctrl->setParameter($this, "hier_id", $hier_id);
        $this->ctrl->setParameter($this, "pc_id", $pc_id);
        $this->ctrl->setCmd($cmd);
        if ($next_class == "") {
            $pc_def = ilCOPagePCDef::getPCDefinitionByType($ctype);
            if (is_array($pc_def)) {
                $this->ctrl->setCmdClass($pc_def["pc_gui_class"]);
            }
            $next_class = $this->ctrl->getNextClass($this);
        }
        $this->log->debug("step NC2: next_class: $next_class");

        // ... do not do this while imagemap editing is ongoing
        // Step IM (handle image map editing)
        if ($cmd == "displayPage" &&
            $this->request->getString("editImagemapForward_x") == ""
            && $this->request->getString("imagemap_x") == "") {
            $next_class = "";
        }


        switch ($next_class) {
            case "ilinternallinkgui":
                $link_gui = new ilInternalLinkGUI(
                    $this->page_gui->getPageConfig()->getIntLinkHelpDefaultType(),
                    $this->page_gui->getPageConfig()->getIntLinkHelpDefaultId(),
                    $this->page_gui->getPageConfig()->getIntLinkHelpDefaultIdIsRef()
                );
                $link_gui->setFilterWhiteList(
                    $this->page_gui->getPageConfig()->getIntLinkFilterWhiteList()
                );
                foreach ($this->page_gui->getPageConfig()->getIntLinkFilters() as $filter) {
                    $link_gui->filterLinkType($filter);
                }
                $link_gui->setReturn($this->int_link_return);

                $ret = $this->ctrl->forwardCommand($link_gui);
                break;

            // PC Media Object
            case "ilpcmediaobjectgui":
                $this->tabs_gui->clearTargets();
                $this->tabs_gui->setBackTarget(
                    $this->page_gui->page_back_title,
                    $ilCtrl->getLinkTarget($this->page_gui, "edit")
                );
                $pcmob_gui = new ilPCMediaObjectGUI($this->page, $cont_obj, $hier_id, $pc_id);
                $pcmob_gui->setStyleId($this->page_gui->getStyleId());
                $pcmob_gui->setSubCmd($ctype);
                $pcmob_gui->setEnabledMapAreas($this->page_gui->getPageConfig()->getEnableInternalLinks());
                $ret = $this->ctrl->forwardCommand($pcmob_gui);
                $ilHelp->setScreenIdComponent("copg_media");
                break;

            // only for "linked" media
            case "ilobjmediaobjectgui":
                $this->tabs_gui->clearTargets();
                $this->tabs_gui->setBackTarget(
                    $this->lng->txt("back"),
                    (string) $ilCtrl->getParentReturn($this)
                );
                $mob_gui = new ilObjMediaObjectGUI("", $this->requested_mob_id, false, false);
                $mob_gui->getTabs();
                $mob_gui->setEnabledMapAreas($this->page_gui->getPageConfig()->getEnableInternalLinks());
                $this->tpl->setTitle($this->lng->txt("mob") . ": " .
                    ilObject::_lookupTitle($this->requested_mob_id));
                $ret = $this->ctrl->forwardCommand($mob_gui);
                break;

            // Question
            case "ilpcquestiongui":
                $pc_question_gui = new ilPCQuestionGUI($this->page, $cont_obj, $hier_id, $pc_id);
                $pc_question_gui->setSelfAssessmentMode($this->page_gui->getPageConfig()->getEnableSelfAssessment());
                $pc_question_gui->setPageConfig($this->page_gui->getPageConfig());

                if ($this->page_gui->getPageConfig()->getEnableSelfAssessment()) {
                    $this->tabs_gui->clearTargets();
                    $ilHelp->setScreenIdComponent("copg_pcqst");
                    $this->tabs_gui->setBackTarget(
                        $this->lng->txt("back"),
                        (string) $ilCtrl->getParentReturn($this)
                    );
                    $ret = $this->ctrl->forwardCommand($pc_question_gui);
                } else {
                    $cmd = $this->ctrl->getCmd();
                    $pc_question_gui->$cmd();
                    $this->ctrl->redirectByClass(array("ilobjquestionpoolgui", get_class($cont_obj)), "editQuestion");
                }
                break;
                    
            // Plugged Component
            case "ilpcpluggedgui":
                $this->tabs_gui->clearTargets();
                $plugged_gui = new ilPCPluggedGUI(
                    $this->page,
                    $cont_obj,
                    $hier_id,
                    $add_type,
                    $pc_id
                );
                $ret = $this->ctrl->forwardCommand($plugged_gui);
                break;

            case "ilpageeditorserveradaptergui":
                $adapter = new ilPageEditorServerAdapterGUI(
                    $this->page_gui,
                    $this->ctrl,
                    $this->ui,
                    $this->http_request
                );
                $this->ctrl->forwardCommand($adapter);
                break;

            default:
                
                // generic calls to gui classes
                if (ilCOPagePCDef::isPCGUIClassName($next_class, true)) {
                    $this->log->debug("Generic Call");
                    $pc_def = ilCOPagePCDef::getPCDefinitionByGUIClassName($next_class);
                    $this->tabs_gui->clearTargets();
                    $this->tabs_gui->setBackTarget(
                        $this->page_gui->page_back_title,
                        $ilCtrl->getLinkTarget($this->page_gui, "edit")
                    );
                    $ilHelp->setScreenIdComponent("copg_" . $pc_def["pc_type"]);
                    //ilCOPagePCDef::requirePCGUIClassByName($pc_def["name"]);
                    $gui_class_name = $pc_def["pc_gui_class"];
                    $pc_gui = new $gui_class_name($this->page, $cont_obj, $hier_id, $pc_id);
                    if ($pc_def["style_classes"]) {
                        $pc_gui->setStyleId($this->page_gui->getStyleId());
                    }
                    $pc_gui->setPageConfig($this->page_gui->getPageConfig());
                    $ret = $this->ctrl->forwardCommand($pc_gui);
                } else {
                    $this->log->debug("Call ilPageEditorGUI command.");
                    // cmd belongs to ilPageEditorGUI
                    
                    if ($cmd == "pasteFromClipboard") {
                        //$ret = $this->pasteFromClipboard($hier_id);
                        $this->pasteFromClipboard($hier_id);
                    } elseif ($cmd == "paste") {
                        //$ret = $this->paste($hier_id);
                        $this->paste($hier_id);
                    } else {
                        $ret = $this->$cmd();
                    }
                }
                break;

        }

        $this->log->debug("end --------------------");

        return (string) $ret;
    }

    public function activatePage() : void
    {
        $this->page_gui->activatePage();
    }

    public function deactivatePage() : void
    {
        $this->page_gui->deactivatePage();
    }

    /**
     * set media and editing mode
     */
    public function setMediaMode() : void
    {
        $ilUser = $this->user;

        $ilUser->writePref(
            "ilPageEditor_MediaMode",
            $this->request->getString("media_mode")
        );
        $ilUser->writePref(
            "ilPageEditor_HTMLMode",
            $this->request->getString("html_mode")
        );
        $js_mode = $this->request->getString("js_mode");
        if ($ilUser->getPref("ilPageEditor_JavaScript") != $js_mode) {
            // not nice, should be solved differently in the future
            if ($this->page->getParentType() == "lm") {
                $this->ctrl->setParameterByClass("illmpageobjectgui", "reloadTree", "y");
            }
        }
        $ilUser->writePref("ilPageEditor_JavaScript", $js_mode);
        
        // again not so nice...
        if ($this->page->getParentType() == "lm") {
            $this->ctrl->redirectByClass("illmpageobjectgui", "edit");
        } else {
            $this->ctrl->returnToParent($this);
        }
    }
    
    /**
     * copy linked media object to clipboard
     */
    public function copyLinkedMediaToClipboard() : void
    {
        $ilUser = $this->user;
        
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("copied_to_clipboard"), true);
        $ilUser->addObjectToClipboard(
            $this->requested_mob_id,
            "mob",
            ilObject::_lookupTitle($this->requested_mob_id)
        );
        $this->ctrl->returnToParent($this);
    }

    /**
     * copy linked media object to media pool
     */
    public function copyLinkedMediaToMediaPool() : void
    {
        $this->ctrl->setParameterByClass("ilmediapooltargetselector", "mob_id", $this->requested_mob_id);
        $this->ctrl->redirectByClass("ilmediapooltargetselector", "listPools");
    }
    
    /**
     * add change comment to history
     */
    public function addChangeComment() : void
    {
        ilHistory::_createEntry(
            $this->page->getId(),
            "update",
            [],
            $this->page->getParentType() . ":pg",
            $this->request->getString("change_comment"),
            true
        );
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("cont_added_comment"), true);
        $this->ctrl->returnToParent($this);
    }

    /**
     * Confirm
     */
    public function delete() : void
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;

        $targets = $this->request->getIds();

        if (count($targets) == 0) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("no_checkbox"), true);
            $this->ctrl->returnToParent($this);
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("copg_confirm_el_deletion"));
            $cgui->setCancel($lng->txt("cancel"), "cancelDeleteSelected");
            $cgui->setConfirm($lng->txt("confirm"), "confirmedDeleteSelected");
            foreach ($targets as $t) {
                $cgui->addHiddenItem("ids[]", $t);
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    public function cancelDeleteSelected() : void
    {
        $this->ctrl->returnToParent($this);
    }

    public function confirmedDeleteSelected() : void
    {
        $targets = $this->request->getIds();
        if (count($targets) > 0) {
            $updated = $this->page->deleteContents(
                $targets,
                true,
                $this->page_gui->getPageConfig()->getEnableSelfAssessment()
            );
            if ($updated !== true) {
                $this->edit_repo->setPageError($updated);
            } else {
                $this->edit_repo->clearPageError();
            }
        }
        $this->ctrl->returnToParent($this);
    }

    /**
     * Copy selected items
     */
    public function copy() : void
    {
        $lng = $this->lng;

        $ids = $this->request->getIds();
        if (count($ids) > 0) {
            $this->page->copyContents($ids);
            $this->tpl->setOnScreenMessage('success', $lng->txt("cont_sel_el_copied_use_paste"), true);
        }
        $this->ctrl->returnToParent($this);
    }

    /**
     * Cut selected items
     */
    public function cut() : void
    {
        $lng = $this->lng;

        $ids = $this->request->getIds();
        if (count($ids)) {
            $updated = $this->page->cutContents($ids);
            if ($updated !== true) {
                $this->edit_repo->setPageError($updated);
            } else {
                $this->edit_repo->clearPageError();
            }
            $this->tpl->setOnScreenMessage('success', $lng->txt("cont_sel_el_cut_use_paste"), true);
        }
        $this->ctrl->returnToParent($this);
    }

    /**
     * paste from clipboard (redirects to clipboard)
     */
    public function paste(string $a_hier_id) : void
    {
        $this->page->pasteContents($a_hier_id, $this->page_gui->getPageConfig()->getEnableSelfAssessment());
        //ilEditClipboard::setAction("");
        $this->ctrl->returnToParent($this);
    }

    /**
     * (de-)activate selected items
     */
    public function activate() : void
    {
        $ids = $this->request->getIds();
        if (count($ids) > 0) {
            $updated = $this->page->switchEnableMultiple(
                $ids,
                true,
                $this->page_gui->getPageConfig()->getEnableSelfAssessment()
            );
            if ($updated !== true) {
                $this->edit_repo->setPageError($updated);
            } else {
                $this->edit_repo->clearPageError();
            }
        }
        $this->ctrl->returnToParent($this);
    }

    /**
     * Assign characeristic to text blocks/sections
     */
    public function characteristic() : void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;

        $ids = $this->request->getIds();
        if (count($ids) > 0) {
            $types = array();
            
            // check what content element types have been selected
            foreach ($ids as $t) {
                $tarr = explode(":", $t);
                $cont_obj = $this->page->getContentObject($tarr[0], $tarr[1]);
                if (is_object($cont_obj) && $cont_obj->getType() == "par") {
                    $types["par"] = "par";
                }
                if (is_object($cont_obj) && $cont_obj->getType() == "sec") {
                    $types["sec"] = "sec";
                }
            }
        
            if (count($types) == 0) {
                $this->tpl->setOnScreenMessage('failure', $lng->txt("cont_select_par_or_section"), true);
                $this->ctrl->returnToParent($this);
            } else {
                $this->initCharacteristicForm($ids, $types);
                $tpl->setContent($this->form->getHTML());
            }
        } else {
            $this->ctrl->returnToParent($this);
        }
    }

    /**
     * Init map creation/update form
     */
    public function initCharacteristicForm(
        array $a_target,
        array $a_types
    ) : void {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        
        // edit form
        $this->form = new ilPropertyFormGUI();
        $this->form->setTitle($this->lng->txt("cont_choose_characteristic"));
        
        if ($a_types["par"] == "par") {
            $select_prop = new ilSelectInputGUI(
                $this->lng->txt("cont_choose_characteristic_text"),
                "char_par"
            );
            $options = ilPCParagraphGUI::_getCharacteristics($this->page_gui->getStyleId());
            $select_prop->setOptions($options);
            $this->form->addItem($select_prop);
        }
        if ($a_types["sec"] == "sec") {
            $select_prop = new ilSelectInputGUI(
                $this->lng->txt("cont_choose_characteristic_section"),
                "char_sec"
            );
            $options = ilPCSectionGUI::_getCharacteristics($this->page_gui->getStyleId());
            $select_prop->setOptions($options);
            $this->form->addItem($select_prop);
        }
        
        foreach ($a_target as $t) {
            $hidden = new ilHiddenInputGUI("target[]");
            $hidden->setValue($t);
            $this->form->addItem($hidden);
        }

        $this->form->setFormAction($ilCtrl->getFormAction($this));
        $this->form->addCommandButton("assignCharacteristic", $lng->txt("save"));
        $this->form->addCommandButton("showPage", $lng->txt("cancel"));
    }

    public function assignCharacteristic() : void
    {
        $char_par = $this->request->getString("char_par");
        $char_sec = $this->request->getString("char_sec");

        $updated = $this->page->assignCharacteristic(
            $this->request->getStringArray("target"),
            $char_par,
            $char_sec,
            ""
        );
        if ($updated !== true) {
            $this->edit_repo->setPageError($updated);
        } else {
            $this->edit_repo->clearPageError();
        }
        $this->ctrl->returnToParent($this);
    }

    /**
     * paste from clipboard (redirects to clipboard)
     */
    public function pasteFromClipboard(string $a_hier_id) : void
    {
        $ilCtrl = $this->ctrl;
        //var_dump($a_hier_id);
        $ilCtrl->setParameter($this, "hier_id", $a_hier_id);
        $ilCtrl->setParameterByClass(
            "ilEditClipboardGUI",
            "returnCommand",
            rawurlencode($ilCtrl->getLinkTarget(
                $this,
                "insertFromClipboard",
                "",
                false,
                false
            ))
        );
        //echo ":".$ilCtrl->getLinkTarget($this, "insertFromClipboard").":";
        $ilCtrl->redirectByClass("ilEditClipboardGUI", "getObject");
    }

    /**
     * insert object from clipboard
     * @throws ilDateTimeException
     */
    public function insertFromClipboard() : void
    {
        $ids = ilEditClipboardGUI::_getSelectedIDs();

        $hier_id = $this->page->getHierIdForPcId($this->requested_pc_id);
        if ($hier_id == "") {
            $hier_id = "pg";
        }

        if ($ids != "") {
            foreach ($ids as $id2) {
                $id = explode(":", $id2);
                $type = $id[0];
                $id = $id[1];
                if ($type == "mob") {
                    $this->content_obj = new ilPCMediaObject($this->page);
                    $this->content_obj->readMediaObject($id);
                    $this->content_obj->createAlias($this->page, $hier_id);
                    $this->page->update();
                }
                if ($type == "incl") {
                    $this->content_obj = new ilPCContentInclude($this->page);
                    $this->content_obj->create($this->page, $hier_id);
                    $this->content_obj->setContentType("mep");
                    $this->content_obj->setContentId($id);
                    $this->page->update();
                }
            }
        }
        $this->ctrl->returnToParent($this);
    }

    /**
     * Default for POST reloads and missing
     */
    public function displayPage() : void
    {
        $this->ctrl->returnToParent($this);
    }
    
    /**
     * Show snippet info
     */
    public function showSnippetInfo() : void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilAccess = $this->access;
        $ilCtrl = $this->ctrl;
        
        $stpl = new ilTemplate("tpl.snippet_info.html", true, true, "Services/COPage");
        
        $mep_pools = ilMediaPoolItem::getPoolForItemId(
            $this->request->getString("ci_id")
        );
        foreach ($mep_pools as $mep_id) {
            $ref_ids = ilObject::_getAllReferences($mep_id);
            $edit_link = false;
            foreach ($ref_ids as $rid) {
                if (!$edit_link && $ilAccess->checkAccess("write", "", $rid)) {
                    $stpl->setCurrentBlock("edit_link");
                    $stpl->setVariable("TXT_EDIT", $lng->txt("edit"));
                    $stpl->setVariable(
                        "HREF_EDIT",
                        "./goto.php?target=mep_" . $rid
                    );
                    $stpl->parseCurrentBlock();
                }
            }
            $stpl->setCurrentBlock("pool");
            $stpl->setVariable("TXT_MEDIA_POOL", $lng->txt("obj_mep"));
            $stpl->setVariable("VAL_MEDIA_POOL", ilObject::_lookupTitle($mep_id));
            $stpl->parseCurrentBlock();
        }
        
        $stpl->setVariable("TXT_TITLE", $lng->txt("title"));
        $stpl->setVariable(
            "VAL_TITLE",
            ilMediaPoolPage::lookupTitle($this->request->getString("ci_id"))
        );
        $stpl->setVariable("TXT_BACK", $lng->txt("back"));
        $stpl->setVariable(
            "HREF_BACK",
            $ilCtrl->getLinkTarget($this->page_gui, "edit")
        );
        $tpl->setContent($stpl->get());
    }
}
