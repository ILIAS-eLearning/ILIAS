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
    protected \ILIAS\COPage\PC\PCDefinition $pc_definition;
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
        $this->tabs_gui = $ilTabs;
        $this->page = $a_page_object;
        $this->page_gui = $a_page_object_gui;

        $this->ctrl->saveParameter($this, array("hier_id", "pc_id"));

        $this->edit_repo = $DIC
            ->copage()
            ->internal()
            ->repo()
            ->edit();
        $this->pc_definition = $DIC
            ->copage()
            ->internal()
            ->domain()
            ->pc()
            ->definition();
    }

    /**
     * set header title
     */
    public function setHeader(string $a_header): void
    {
        $this->header = $a_header;
    }

    public function getHeader(): string
    {
        return $this->header;
    }

    public function returnToContext(): void
    {
        $this->ctrl->returnToParent($this);
    }

    public function setIntLinkReturn(string $a_return): void
    {
        $this->int_link_return = $a_return;
    }

    public function setPageBackTitle(string $a_title): void
    {
        $this->page_back_title = $a_title;
    }

    private function hasRequestedComponent(): bool
    {
        return $this->requested_ctype !== "" || $this->requested_cname !== "";
    }

    private function isNewComponentCommand(string $cmd): bool
    {
        return in_array($cmd, ["insert", "create"], true);
    }

    protected function isMatchingContentObjectGUI(string $next_class, string $ctype): bool
    {
        $pc_def = $this->pc_definition->getPCDefinitionByType($ctype);
        return is_array($pc_def) && strtolower($pc_def["pc_gui_class"]) === $next_class;
    }

    private function resolveHierId(string $next_class): string
    {
        $new_hier_id = $this->request->getString("new_hier_id");
        $hier_id = $new_hier_id !== "" ? $new_hier_id : $this->requested_hier_id;

        if ($next_class === "ilpcplaceholdergui" && $hier_id === "" && $this->requested_pl_pc_id !== "") {
            $hier_ids = $this->page->getHierIdsForPCIds([$this->requested_pl_pc_id]);
            $hier_id = $hier_ids[$this->requested_pl_pc_id];
        }

        return $hier_id;
    }

    private function resolveRequestedComponentTarget(): array
    {
        $ctype = $this->requested_ctype;
        if ($this->requested_cname !== "") {
            $pc_def = $this->pc_definition->getPCDefinitionByName($this->requested_cname);
            $ctype = $pc_def["pc_type"];
        }

        return [$ctype, $this->requested_hier_id, $this->requested_pc_id];
    }

    private function getContentObjectForTarget(
        string $next_class,
        string $cmd,
        string $hier_id,
        string $pc_id
    ): ?ilPageContent {
        if (!$this->pc_definition->isPCGUIClassName($next_class, true) || $this->isNewComponentCommand($cmd)) {
            return null;
        }

        $cont_obj = $this->page->getContentObject($hier_id, $pc_id);
        if (!is_object($cont_obj)) {
            if ($cmd === "upload") {
                return null;
            }
            $this->log->debug("returnToParent");
            $this->ctrl->returnToParent($this);
        }

        return $cont_obj;
    }

    /**
     * execute command
     */
    public function executeCommand(): string
    {
        $ilCtrl = $this->ctrl;
        $ilHelp = $this->help;
        $this->log->debug("begin =========================");
        $ctype = "";
        $ret = "";

        $cmd = $this->ctrl->getCmd("displayPage");
        $has_requested_component = $this->hasRequestedComponent();
        $pc_id = $this->requested_pc_id;
        $this->page->buildDom();
        $this->page->addHierIDs();

        $next_class = $this->ctrl->getNextClass($this);
        $hier_id = $this->resolveHierId($next_class);
        if ($has_requested_component) {
            [$ctype, $hier_id, $pc_id] = $this->resolveRequestedComponentTarget();
        }
        $cont_obj = $this->getContentObjectForTarget($next_class, $cmd, $hier_id, $pc_id);
        if (is_object($cont_obj)) {
            $ctype = $cont_obj->getType();
        }
        // this fixes e.g. #31214
        if ($pc_id != "" && $hier_id == "") {
            $hier_id = $this->page->getHierIdForPcId($pc_id);
        }
        if (($ctype !== "media" || !is_object($cont_obj)) && $this->getHeader() !== "") {
            $this->tpl->setTitle($this->getHeader());
        }

        $this->cont_obj = $cont_obj;

        $this->ctrl->setParameter($this, "hier_id", $hier_id);
        $this->ctrl->setParameter($this, "pc_id", $pc_id);
        if ($has_requested_component) {
            $this->ctrl->setParameter($this, "ctype", $ctype);
        }

        if (is_object($cont_obj) && !$this->isMatchingContentObjectGUI($next_class, $ctype)) {
            $this->log->debug("returnToParent due to mismatching content object GUI");
            $ilCtrl->returnToParent($this);
        }
        $this->log->debug("step NC2: next_class: $next_class");

        if ($next_class == "") {
            $pc_def = $this->pc_definition->getPCDefinitionByType($ctype);
            if (is_array($pc_def)) {
                if ($ctype === "plug") {
                    $this->ctrl->setParameterByClass(
                        $pc_def["pc_gui_class"],
                        "pluginName",
                        $this->request->getString("pluginName")
                    );
                }
                $this->ctrl->redirectByClass($pc_def["pc_gui_class"], $this->ctrl->getCmd());
            }
            $next_class = $this->ctrl->getNextClass($this);
        }

        // ... do not do this while imagemap editing is ongoing
        // Step IM (handle image map editing)
        if ($cmd == "displayPage" && $next_class !== "ilpcmediaobjectgui" &&
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
                    $this->request->getString("pluginName"),
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
                if ($this->pc_definition->isPCGUIClassName($next_class, true)) {
                    $this->log->debug("Generic Call");
                    $pc_def = $this->pc_definition->getPCDefinitionByGUIClassName($next_class);
                    $this->tabs_gui->clearTargets();
                    $this->tabs_gui->setBackTarget(
                        $this->page_gui->page_back_title,
                        $ilCtrl->getLinkTarget($this->page_gui, "edit")
                    );
                    $ilHelp->setScreenIdComponent("copg_" . $pc_def["pc_type"]);
                    $gui_class_name = $pc_def["pc_gui_class"];
                    $pc_gui = new $gui_class_name($this->page, $cont_obj, $hier_id, $pc_id);
                    if ($pc_def["style_classes"]) {
                        $pc_gui->setStyleId($this->page_gui->getStyleId());
                    }
                    $pc_gui->setPageConfig($this->page_gui->getPageConfig());
                    $ret = $this->ctrl->forwardCommand($pc_gui);
                } else {
                    $ret = $this->$cmd();
                }
                break;
        }

        $this->log->debug("end --------------------");

        return (string) $ret;
    }

    public function activatePage(): void
    {
        $this->page_gui->activatePage();
    }

    public function deactivatePage(): void
    {
        $this->page_gui->deactivatePage();
    }

    /**
     * copy linked media object to clipboard
     */
    public function copyLinkedMediaToClipboard(): void
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
     * insert object from clipboard
     * @throws ilDateTimeException
     */
    public function insertFromClipboard(): void
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
    public function displayPage(): void
    {
        $this->ctrl->returnToParent($this);
    }
}
