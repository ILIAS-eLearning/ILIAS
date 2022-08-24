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

use ILIAS\GlobalScreen\ScreenContext\ContextServices;

/**
 * Portfolio view gui class
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_Calls ilObjPortfolioGUI: ilPortfolioPageGUI, ilPageObjectGUI
 * @ilCtrl_Calls ilObjPortfolioGUI: ilWorkspaceAccessGUI, ilNoteGUI
 * @ilCtrl_Calls ilObjPortfolioGUI: ilObjectContentStyleSettingsGUI, ilPortfolioExerciseGUI
 */
class ilObjPortfolioGUI extends ilObjPortfolioBaseGUI
{
    protected \ILIAS\Notes\GUIService $notes_gui;
    protected ilWorkspaceAccessHandler $ws_access;
    protected ContextServices $tool_context;
    protected ilPortfolioDeclarationOfAuthorship $declaration_authorship;

    public function __construct(int $a_id = 0)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->help = $DIC["ilHelp"];
        $this->settings = $DIC->settings();
        $this->access = $DIC->access();
        $this->user = $DIC->user();
        $this->ctrl = $DIC->ctrl();
        $this->ui = $DIC->ui();

        $this->tool_context = $DIC->globalScreen()->tool()->context();

        parent::__construct($a_id, self::PORTFOLIO_OBJECT_ID, 0);
        $this->declaration_authorship = new ilPortfolioDeclarationOfAuthorship();

        $this->ctrl->saveParameter($this, "exc_back_ref_id");
        $this->notes_gui = $DIC->notes()->gui();
    }

    public function getType(): string
    {
        return "prtf";
    }

    protected function checkPermissionBool(
        string $perm,
        string $cmd = "",
        string $type = "",
        ?int $node_id = null
    ): bool {
        if ($perm === "create") {
            return true;
        }
        if (!$node_id) {
            $node_id = $this->obj_id;
        }
        return $this->access_handler->checkAccess($perm, "", $node_id);
    }

    public function executeCommand(): void
    {
        $this->checkPermission("read");

        $this->setTitleAndDescription();

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("view");

        // we have to init the note js handling here, might go to
        // a better place in the future
        $this->notes_gui->initJavascript(
            $this->ctrl->getLinkTargetByClass(
                array("ilnotegui"),
                "",
                "",
                true,
                false
            )
        );

        // trigger assignment tool
        $this->triggerAssignmentTool();

        switch ($next_class) {
            case "ilworkspaceaccessgui":
                if ($this->checkPermissionBool("write")) {
                    $this->setTabs();
                    $this->tabs_gui->activateTab("share");

                    $this->tpl->setPermanentLink("prtf", $this->object->getId());

                    $wspacc = new ilWorkspaceAccessGUI($this->object->getId(), $this->access_handler, true);
                    $wspacc->setBlockingMessage($this->getOfflineMessage());
                    $this->ctrl->forwardCommand($wspacc);
                }
                break;

            case 'ilportfoliopagegui':
                if ($this->determinePageCall()) {
                    // only in edit mode
                    $this->addLocator();
                }
                $this->handlePageCall($cmd);
                break;

            case "ilnotegui":
                $this->preview();
                break;

                /*
            case "ilobjstylesheetgui":
                $this->ctrl->setReturn($this, "editStyleProperties");
                $style_gui = new ilObjStyleSheetGUI("", $this->object->getStyleSheetId(), false, false);
                $style_gui->enableWrite(true);
                $style_gui->omitLocator();
                if ($cmd == "create" || $this->port_request->getNewType() == "sty") {
                    $style_gui->setCreationMode(true);
                }

                if ($cmd == "confirmedDelete") {
                    $this->object->setStyleSheetId(0);
                    $this->object->update();
                }

                $ret = $this->ctrl->forwardCommand($style_gui);

                if ($cmd == "save" || $cmd == "copyStyle" || $cmd == "importStyle") {
                    $style_id = $ret;
                    $this->object->setStyleSheetId($style_id);
                    $this->object->update();
                    $this->ctrl->redirectByClass("ilobjstylesheetgui", "edit");
                }
                break;*/

            case "ilobjectcontentstylesettingsgui":
                $this->checkPermission("write");
                $this->addLocator();
                $this->setTabs();
                $this->tabs_gui->activateTab("settings");
                $this->setSettingsSubTabs("style");
                $settings_gui = $this->content_style_gui
                    ->objectSettingsGUIForObjId(
                        null,
                        $this->object->getId()
                    );
                $this->ctrl->forwardCommand($settings_gui);
                break;

            case "ilportfolioexercisegui":
                $this->ctrl->setReturn($this, "view");
                $gui = new ilPortfolioExerciseGUI($this->user_id, $this->object->getId());
                $this->ctrl->forwardCommand($gui);
                break;

            default:

                if ($cmd !== "preview") {
                    $this->addLocator();
                    $this->setTabs();
                }
                $this->$cmd();
                break;
        }
    }

    protected function triggerAssignmentTool(): void
    {
        if (!is_object($this->object) || $this->object->getId() <= 0) {
            return;
        }
        $pe = new ilPortfolioExercise($this->user_id, $this->object->getId());
        $pe_gui = new ilPortfolioExerciseGUI($this->user_id, $this->object->getId());
        $assignments = $pe->getAssignmentsOfPortfolio();
        if (count($assignments) > 0) {
            $ass_ids = array_map(static function ($i) {
                return $i["ass_id"];
            }, $assignments);
            $this->tool_context->current()->addAdditionalData(ilExerciseGSToolProvider::SHOW_EXC_ASSIGNMENT_INFO, true);
            $this->tool_context->current()->addAdditionalData(ilExerciseGSToolProvider::EXC_ASS_IDS, $ass_ids);
            $this->tool_context->current()->addAdditionalData(ilExerciseGSToolProvider::EXC_ASS_BUTTONS, $pe_gui->getActionButtons());
        }
    }

    protected function setTabs(): void
    {
        $ilHelp = $this->help;

        $ilHelp->setScreenIdComponent("prtf");

        if ($this->checkPermissionBool("write")) {
            $this->tabs_gui->addTab(
                "pages",
                $this->lng->txt("content"),
                $this->ctrl->getLinkTarget($this, "view")
            );

            $this->tabs_gui->addTab(
                "settings",
                $this->lng->txt("settings"),
                $this->ctrl->getLinkTarget($this, "edit")
            );

            $this->tabs_gui->addNonTabbedLink(
                "preview",
                $this->lng->txt("user_profile_preview"),
                $this->ctrl->getLinkTarget($this, "preview")
            );

            $this->lng->loadLanguageModule("wsp");
            $this->tabs_gui->addTab(
                "share",
                $this->lng->txt("wsp_permissions"),
                $this->ctrl->getLinkTargetByClass("ilworkspaceaccessgui", "share")
            );
        }
    }

    protected function addLocator(): void
    {
        if (!$this->creation_mode) {
            $this->ctrl->setParameter($this, "prt_id", $this->object->getId());
        }

        $this->addLocatorItems();

        $this->tpl->setLocator();
    }

    protected function setTitleAndDescription(): void
    {
        // parent::setTitleAndDescription();

        $title = $this->lng->txt("portfolio");
        if ($this->object) {
            $title .= ": " . $this->object->getTitle();
        }
        $this->tpl->setTitle($title);
        $this->tpl->setTitleIcon(
            ilUtil::getImagePath("icon_prtf.svg"),
            $this->lng->txt("portfolio")
        );

        if ($this->object &&
            !$this->object->isOnline()) {
            $this->tpl->setAlertProperties(array(
                array("alert" => true,
                    "property" => $this->lng->txt("status"),
                    "value" => $this->lng->txt("offline"))
            ));
        }
    }


    //
    // CREATE/EDIT
    //

    public function create(): void
    {
        $tpl = $this->tpl;
        $ilErr = $this->error;

        $new_type = $this->port_request->getNewType();

        // add new object to custom parent container
        $this->ctrl->saveParameter($this, "crtptrefid");
        // use forced callback after object creation
        $this->ctrl->saveParameter($this, "crtcb");

        if (!$this->checkPermissionBool("create", "", $new_type)) {
            $ilErr->raiseError($this->lng->txt("permission_denied"), $ilErr->MESSAGE);
        } else {
            $this->lng->loadLanguageModule($new_type);
            $this->ctrl->setParameter($this, "new_type", $new_type);

            $forms = $this->initCreationForms($new_type);

            // copy form validation error: do not show other creation forms
            if ($this->port_request->getCopyFormProcess() && isset($forms[self::CFORM_CLONE])) {
                $forms = array(self::CFORM_CLONE => $forms[self::CFORM_CLONE]);
            }
            $tpl->setContent($this->getCreateInfoMessage() . $this->getCreationFormsHTML($forms));
        }
    }

    protected function getCreateInfoMessage(): string
    {
        global $DIC;

        $ui = $DIC->ui();
        $ilSetting = $DIC->settings();

        $message = "";
        // page type: blog
        if (!$ilSetting->get('disable_wsp_blogs')) {
            $options = array();
            $tree = new ilWorkspaceTree($this->user_id);
            $root = $tree->readRootId();
            if ($root) {
                $root = $tree->getNodeData($root);
                foreach ($tree->getSubTree($root) as $node) {
                    if ($node["type"] == "blog") {
                        $options[$node["obj_id"]] = $node["title"];
                    }
                }
                asort($options);
            }
            if (!count($options)) {

                // #18147
                $this->lng->loadLanguageModule('pd');
                $url = $this->ctrl->getLinkTargetByClass("ilDashboardGUI", "jumpToWorkspace");
                $text = $this->lng->txt("mm_personal_and_shared_r");

                $text = sprintf($this->lng->txt("prtf_no_blogs_info"), $text);

                $mbox = $ui->factory()->messageBox()->info($text)
                    ->withLinks([$ui->factory()->link()->standard(
                        $this->lng->txt("mm_personal_and_shared_r"),
                        $url
                    )]);

                $message = $ui->renderer()->render($mbox);
            }
        }
        return $message;
    }


    protected function initCreationForms(string $new_type): array
    {
        return array(self::CFORM_NEW => $this->initCreateForm($new_type));
    }

    protected function initCreateForm(string $new_type): ilPropertyFormGUI
    {
        $ilSetting = $this->settings;

        $this->ctrl->setParameter($this, "new_type", $this->getType());

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));

        // title
        $ti = new ilTextInputGUI($this->lng->txt("title"), "title");
        $ti->setSize(min(40, ilObject::TITLE_LENGTH));
        $ti->setMaxLength(ilObject::TITLE_LENGTH);
        $ti->setRequired(true);
        $form->addItem($ti);

        $main = new ilRadioGroupInputGUI($this->lng->txt("prtf_creation_mode"), "mode");
        $main->setValue("mode_scratch");
        $form->addItem($main);

        $opt_scratch = new ilRadioOption($this->lng->txt("prtf_creation_mode_scratch"), "mode_scratch");
        $main->addOption($opt_scratch);


        // 1st page

        $type = new ilRadioGroupInputGUI($this->lng->txt("prtf_first_page_title"), "ptype");
        $type->setRequired(true);
        $opt_scratch->addSubItem($type);

        $type_page = new ilRadioOption($this->lng->txt("page"), "page");
        $type->addOption($type_page);

        // page type: page
        $tf = new ilTextInputGUI($this->lng->txt("title"), "fpage");
        $tf->setMaxLength(128);
        $tf->setSize(40);
        $tf->setRequired(true);
        $type_page->addSubItem($tf);

        // page templates
        $templates = ilPageLayout::activeLayouts(ilPageLayout::MODULE_PORTFOLIO);
        if ($templates) {
            $options = array(0 => $this->lng->txt("none"));
            foreach ($templates as $templ) {
                $templ->readObject();
                $options[$templ->getId()] = $templ->getTitle();
            }

            $use_template = new ilSelectInputGUI($this->lng->txt("prtf_use_page_layout"), "tmpl");
            $use_template->setRequired(true);
            $use_template->setOptions($options);
            $type_page->addSubItem($use_template);
        }

        // page type: blog
        if (!$ilSetting->get('disable_wsp_blogs')) {
            $options = array();
            $tree = new ilWorkspaceTree($this->user_id);
            $root = $tree->readRootId();
            if ($root) {
                $root = $tree->getNodeData($root);
                foreach ($tree->getSubTree($root) as $node) {
                    if ($node["type"] == "blog") {
                        $options[$node["obj_id"]] = $node["title"];
                    }
                }
                asort($options);
            }
            if (count($options)) {
                $type_blog = new ilRadioOption($this->lng->txt("obj_blog"), "blog");
                $type->addOption($type_blog);

                $obj = new ilSelectInputGUI($this->lng->txt("obj_blog"), "blog");
                $obj->setRequired(true);
                $obj->setOptions(array("" => $this->lng->txt("please_select")) + $options);
                $type_blog->addSubItem($obj);
            } else {
                $type->setValue("page");
            }
        }


        // portfolio templates

        $opt_tmpl = new ilRadioOption($this->lng->txt("prtf_creation_mode_template"), "mode_tmpl");
        $main->addOption($opt_tmpl);

        $templates = ilObjPortfolioTemplate::getAvailablePortfolioTemplates();
        if (!count($templates)) {
            $opt_tmpl->setDisabled(true);
        } else {
            $tmpl = new ilSelectInputGUI($this->lng->txt("obj_prtt"), "prtt");
            $tmpl->setRequired(true);
            $tmpl->setOptions(array("" => $this->lng->txt("please_select")) + $templates);
            $opt_tmpl->addSubItem($tmpl);

            // incoming from repository
            $template_id = $this->port_request->getPortfolioTemplateId();
            if ($template_id > 0) {
                $tmpl->setValue($template_id);
                $main->setValue("mode_tmpl");
            }
        }


        $form->setTitle($this->lng->txt("prtf_create_portfolio"));
        $form->addCommandButton("save", $this->lng->txt("create"));
        $form->addCommandButton("toRepository", $this->lng->txt("cancel"));

        return $form;
    }

    public function save(): void
    {
        $form = $this->initCreateForm("prtf");
        // trigger portfolio template "import" process
        if ($form->checkInput() && $form->getInput("mode") == "mode_tmpl") {
            $this->createFromTemplateDirect(
                $form->getInput("title"),
                $this->port_request->getPortfolioTemplate()
            );
            return;
        }

        parent::save();
    }

    protected function afterSave(ilObject $new_object): void
    {
        // create 1st page / blog
        $page = $this->getPageInstance(null, $new_object->getId());
        if ($this->port_request->getPageType() === "page") {
            $page->setType(ilPortfolioPage::TYPE_PAGE);
            $page->setTitle($this->port_request->getPageTitle());

            // use template as basis
            $layout_id = $this->port_request->getTemplateId();
            if ($layout_id) {
                $layout_obj = new ilPageLayout($layout_id);
                $page->setXMLContent($layout_obj->getXMLContent());
            }
        } else {
            $page->setType(ilPortfolioPage::TYPE_BLOG);
            $page->setTitle($this->port_request->getBlogTitle());
        }
        $page->create();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("prtf_portfolio_created"), true);
        $this->ctrl->setParameter($this, "prt_id", $new_object->getId());
        $this->ctrl->redirect($this, "view");
    }

    protected function toRepository(): void
    {
        $ilAccess = $this->access;

        // return to exercise (portfolio assignment)
        $exc_ref_id = $this->port_request->getExerciseRefId();
        if ($exc_ref_id &&
            $ilAccess->checkAccess("read", "", $exc_ref_id)) {
            ilUtil::redirect(ilLink::_getLink($exc_ref_id, "exc"));
        }

        $this->ctrl->redirectByClass("ilportfoliorepositorygui", "show");
    }

    protected function initEditForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));

        // title
        $ti = new ilTextInputGUI($this->lng->txt("title"), "title");
        $ti->setSize(min(40, ilObject::TITLE_LENGTH));
        $ti->setMaxLength(ilObject::TITLE_LENGTH);
        $ti->setRequired(true);
        $ti->setValue($this->object->getTitle());
        $form->addItem($ti);

        /* description
        $ta = new ilTextAreaInputGUI($this->lng->txt("description"), "desc");
        $ta->setCols(40);
        $ta->setRows(2);
        $ta->setValue($this->object->getDescription());
        $form->addItem($ta);
        */

        // :TODO: online
        $online = new ilCheckboxInputGUI($this->lng->txt("online"), "online");
        $online->setChecked($this->object->isOnline());
        $form->addItem($online);

        $this->initEditCustomForm($form);

        $form->setTitle($this->lng->txt("prtf_edit_portfolio"));
        $form->addCommandButton("update", $this->lng->txt("save"));
        $form->addCommandButton("view", $this->lng->txt("cancel"));

        return $form;
    }

    protected function getEditFormCustomValues(array &$a_values): void
    {
        $a_values["online"] = $this->object->isOnline();

        parent::getEditFormCustomValues($a_values);
    }

    protected function updateCustom(ilPropertyFormGUI $form): void
    {
        $this->object->setOnline($form->getInput("online"));

        // if portfolio is not online, it cannot be default
        if (!$form->getInput("online")) {
            ilObjPortfolio::setUserDefault($this->user_id, 0);
        }

        parent::updateCustom($form);
    }


    //
    // PAGES
    //

    /**
     * Get portfolio template page instance
     */
    protected function getPageInstance(
        ?int $a_page_id = null,
        ?int $a_portfolio_id = null
    ): ilPortfolioPage {
        // #11531
        if (!$a_portfolio_id && $this->object) {
            $a_portfolio_id = $this->object->getId();
        }
        $page = new ilPortfolioPage((int) $a_page_id);
        $page->setPortfolioId($a_portfolio_id);
        return $page;
    }

    /**
     * Get portfolio template page gui instance
     */
    protected function getPageGUIInstance(
        int $a_page_id
    ): ilPortfolioPageGUI {
        $page_gui = new ilPortfolioPageGUI(
            $this->object->getId(),
            $a_page_id,
            0,
            $this->object->hasPublicComments()
        );
        $page_gui->setAdditional($this->getAdditional());
        return $page_gui;
    }

    public function getPageGUIClassName(): string
    {
        return "ilportfoliopagegui";
    }

    protected function initCopyPageFormOptions(ilPropertyFormGUI $a_form): void
    {
        $a_tgt = new ilRadioGroupInputGUI($this->lng->txt("target"), "target");
        $a_tgt->setRequired(true);
        $a_form->addItem($a_tgt);

        $old = new ilRadioOption($this->lng->txt("prtf_existing_portfolio"), "old");
        $a_tgt->addOption($old);

        $options = array();
        $all = ilObjPortfolio::getPortfoliosOfUser($this->user_id);
        foreach ($all as $item) {
            $options[$item["id"]] = $item["title"];
        }
        $prtf = new ilSelectInputGUI($this->lng->txt("portfolio"), "prtf");
        $prtf->setRequired(true);
        $prtf->setOptions($options);
        $old->addSubItem($prtf);

        $new = new ilRadioOption($this->lng->txt("prtf_new_portfolio"), "new");
        $a_tgt->addOption($new);

        $tf = new ilTextInputGUI($this->lng->txt("title"), "title");
        $tf->setMaxLength(128);
        $tf->setSize(40);
        $tf->setRequired(true);
        $new->addSubItem($tf);
    }


    //
    // BLOG
    //

    protected function initBlogForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));

        $options = array();
        $tree = new ilWorkspaceTree($this->user_id);
        $root = $tree->readRootId();
        if ($root) {
            $root = $tree->getNodeData($root);
            foreach ($tree->getSubTree($root, true, ["blog"]) as $node) {
                $options[$node["obj_id"]] = $node["title"];
            }
            asort($options);
        }

        // add blog
        $radg = new ilRadioGroupInputGUI($this->lng->txt("obj_blog"), "creation_mode");
        $radg->setInfo($this->lng->txt(""));
        $radg->setValue("new");
        $radg->setInfo($this->lng->txt(""));

        $op1 = new ilRadioOption($this->lng->txt("prtf_add_new_blog"), "new", $this->lng->txt("prtf_add_new_blog_info"));
        $radg->addOption($op1);
        $form->addItem($radg);

        // Blog title
        $ti = new ilTextInputGUI($this->lng->txt("title"), "title");
        $ti->setRequired(true);
        $op1->addSubItem($ti);


        if (count($options)) {
            $op2 = new ilRadioOption($this->lng->txt("prtf_add_existing_blog"), "existing");
            $radg->addOption($op2);

            $obj = new ilSelectInputGUI($this->lng->txt("obj_blog"), "blog");
            $obj->setOptions($options);
            $op2->addSubItem($obj);
        }

        $form->setTitle($this->lng->txt("prtf_add_blog") . ": " .
            $this->object->getTitle());
        $form->addCommandButton("saveBlog", $this->lng->txt("save"));
        $form->addCommandButton("view", $this->lng->txt("cancel"));

        return $form;
    }

    /**
     * Create new portfolio blog page
     */
    protected function saveBlog(): void
    {
        global $DIC;

        $ilUser = $DIC->user();

        $form = $this->initBlogForm();
        if ($form->checkInput() && $this->checkPermissionBool("write")) {
            if ($form->getInput("creation_mode") == "existing") {
                $page = $this->getPageInstance();
                $page->setType(ilPortfolioPage::TYPE_BLOG);
                $page->setTitle($form->getInput("blog"));
            } else {
                $blog = new ilObjBlog();
                $blog->setTitle($form->getInput("title"));
                $blog->create();

                $tree = new ilWorkspaceTree($ilUser->getId());

                // @todo: see also e.g. ilExSubmissionObjectGUI->getOverviewContentBlog, this needs refactoring, consumer should not
                // be responsibel to handle this
                if (!$tree->getRootId()) {
                    $tree->createTreeForUser($ilUser->getId());
                }

                $access_handler = new ilWorkspaceAccessHandler($tree);
                $node_id = $tree->insertObject($tree->readRootId(), $blog->getId());
                $access_handler->setPermissions($tree->readRootId(), $node_id);

                $page = $this->getPageInstance();
                $page->setType(ilPortfolioPage::TYPE_BLOG);
                $page->setTitle($blog->getId());
            }
            $page->create(false);

            $this->tpl->setOnScreenMessage('success', $this->lng->txt("prtf_blog_page_created"), true);
            $this->ctrl->redirect($this, "view");
        }

        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "view")
        );

        $form->setValuesByPost();
        $this->tpl->setContent($form->getHTML());
    }


    //
    // CREATE FROM TEMPLATE
    //

    protected function createPortfolioFromTemplate(
        ilPropertyFormGUI $a_form = null
    ): void {
        $title = $this->port_request->getPortfolioTitle();
        $prtt_id = $this->port_request->getPortfolioTemplate();

        // valid template?
        $templates = array_keys(ilObjPortfolioTemplate::getAvailablePortfolioTemplates());
        if (!count($templates) || !in_array($prtt_id, $templates)) {
            $this->toRepository();
        }
        unset($templates);

        $this->ctrl->setParameter($this, "prtt", $prtt_id);

        if (!$a_form) {
            $a_form = $this->initCreatePortfolioFromTemplateForm($prtt_id, $title);
        }
        if ($a_form) {
            $this->tpl->setContent($a_form->getHTML());
        } else {
            $this->createPortfolioFromTemplateProcess(false);
        }
    }

    protected function initCreatePortfolioFromTemplateForm(
        int $a_prtt_id,
        string $a_title
    ): ?ilPropertyFormGUI {
        $ilSetting = $this->settings;
        $ilUser = $this->user;

        $blog_options = [];

        $exc_id = $this->port_request->getExerciseRefId();
        $ass_id = $this->port_request->getExcAssId();
        if ($exc_id > 0) {
            $this->ctrl->setParameter($this, "exc_id", $exc_id);
            $this->ctrl->setParameter($this, "ass_id", $ass_id);
        }

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));

        $tmpl = new ilNonEditableValueGUI($this->lng->txt("obj_prtt"));
        $tmpl->setValue(ilObject::_lookupTitle($a_prtt_id));
        $form->addItem($tmpl);

        $title = new ilNonEditableValueGUI($this->lng->txt("title"), "pt");
        $title->setValue($a_title);
        $form->addItem($title);

        // gather user blogs
        if (!$ilSetting->get('disable_wsp_blogs')) {
            $blog_options = array();
            $tree = new ilWorkspaceTree($this->user_id);
            $root = $tree->readRootId();
            if ($root) {
                $root = $tree->getNodeData($root);
                foreach ($tree->getSubTree($root, true, ["blog"]) as $node) {
                    $blog_options[$node["obj_id"]] = $node["title"];
                }
                asort($blog_options);
            }
        }

        $has_form_content = false;

        $pskills = array_keys(ilPersonalSkill::getSelectedUserSkills($ilUser->getId()));
        $skill_ids = array();

        foreach (ilPortfolioTemplatePage::getAllPortfolioPages($a_prtt_id) as $page) {
            switch ($page["type"]) {
                case ilPortfolioPage::TYPE_PAGE:
                    // skills
                    $source_page = new ilPortfolioTemplatePage($page["id"]);
                    $source_page->buildDom(true);
                    $skill_ids = $this->getSkillsToPortfolioAssignment($pskills, $skill_ids, $source_page);

                    if (count($skill_ids)) {
                        $has_form_content = true;
                    }
                    break;

                case ilPortfolioTemplatePage::TYPE_BLOG_TEMPLATE:
                    if (!$ilSetting->get('disable_wsp_blogs')) {
                        $has_form_content = true;

                        $field_id = "blog_" . $page["id"];

                        $blog = new ilRadioGroupInputGUI($this->lng->txt("obj_blog") . ": " .
                            $page["title"], $field_id);
                        $blog->setRequired(true);
                        $blog->setValue("blog_create");
                        $form->addItem($blog);

                        $new_blog = new ilRadioOption($this->lng->txt("prtf_template_import_blog_create"), "blog_create");
                        $blog->addOption($new_blog);

                        $title = new ilTextInputGUI($this->lng->txt("title"), $field_id . "_create_title");
                        $title->setRequired(true);
                        $new_blog->addSubItem($title);

                        if (count($blog_options)) {
                            $reuse_blog = new ilRadioOption($this->lng->txt("prtf_template_import_blog_reuse"), "blog_resuse");
                            $blog->addOption($reuse_blog);

                            $obj = new ilSelectInputGUI($this->lng->txt("obj_blog"), $field_id . "_reuse_blog");
                            $obj->setRequired(true);
                            $obj->setOptions(array("" => $this->lng->txt("please_select")) + $blog_options);
                            $reuse_blog->addSubItem($obj);
                        }

                        $blog->addOption(new ilRadioOption($this->lng->txt("prtf_template_import_blog_ignore"), "blog_ignore"));
                    }
                    break;
            }
        }

        if ($skill_ids) {
            $skills = new ilCheckboxGroupInputGUI($this->lng->txt("skills"), "skill_ids");
            $skills->setInfo($this->lng->txt("prtf_template_import_new_skills"));
            $skills->setValue($skill_ids);
            foreach ($skill_ids as $skill_id) {
                $skills->addOption(new ilCheckboxOption(ilSkillTreeNode::_lookupTitle($skill_id), $skill_id));
            }
            $form->addItem($skills);
        }
        // no dialog needed, go ahead
        if (!$has_form_content) {
            return null;
        }

        $form->setTitle($this->lng->txt("prtf_creation_mode") . ": " . $this->lng->txt("prtf_creation_mode_template"));
        $form->addCommandButton("createPortfolioFromTemplateProcess", $this->lng->txt("continue"));
        $form->addCommandButton("toRepository", $this->lng->txt("cancel"));

        return $form;
    }

    protected function createPortfolioFromTemplateProcess(
        bool $a_process_form = true
    ): void {
        $ilSetting = $this->settings;

        $title = $this->port_request->getPortfolioTitle();
        $prtt_id = $this->port_request->getPortfolioTemplate();

        // valid template?
        $templates = array_keys(ilObjPortfolioTemplate::getAvailablePortfolioTemplates());
        if (!count($templates) || !in_array($prtt_id, $templates)) {
            $this->toRepository();
        }
        unset($templates);

        // build page recipe (aka import form values)
        $recipe = null;
        if ($a_process_form) {
            $this->ctrl->setParameter($this, "prtt", $prtt_id);

            $form = $this->initCreatePortfolioFromTemplateForm($prtt_id, $title);
            if ($form->checkInput()) {
                foreach (ilPortfolioTemplatePage::getAllPortfolioPages($prtt_id) as $page) {
                    if (($page["type"] == ilPortfolioTemplatePage::TYPE_BLOG_TEMPLATE) && !$ilSetting->get('disable_wsp_blogs')) {
                        $field_id = "blog_" . $page["id"];
                        switch ($form->getInput($field_id)) {
                            case "blog_create":
                                $recipe[$page["id"]] = array("blog",
                                                             "create",
                                                             trim($form->getInput($field_id . "_create_title"))
                                );
                                break;

                            case "blog_resuse":
                                $recipe[$page["id"]] = array("blog",
                                                             "reuse",
                                                             (int) $form->getInput($field_id . "_reuse_blog")
                                );
                                break;

                            case "blog_ignore":
                                $recipe[$page["id"]] = array("blog", "ignore");
                                break;
                        }
                    }
                }

                $recipe["skills"] = (array) $form->getInput("skill_ids");
            } else {
                $form->setValuesByPost();
                $this->createPortfolioFromTemplate($form);
                return;
            }
        }

        $source = new ilObjPortfolioTemplate($prtt_id, false);

        // create portfolio
        $target = new ilObjPortfolio();
        $target->setTitle($title);
        $target->create();
        $target_id = $target->getId();

        ilObjPortfolioTemplate::clonePagesAndSettings($source, $target, $recipe);

        // link portfolio to exercise assignment
        $this->linkPortfolioToAssignment($target_id);

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("prtf_portfolio_created_from_template"), true);
        $this->ctrl->setParameter($this, "prt_id", $target_id);
        $this->ctrl->redirect($this, "preview");
    }

    /**
     * Create portfolio template direct
     */
    protected function createFromTemplateDirect(
        string $title = "",
        int $prtt_id = 0
    ): void {
        if ($prtt_id === 0) {
            $prtt_id = $this->port_request->getPortfolioTemplateId();
        }
        if ($title === "") {
            $title = ilObject::_lookupTitle($prtt_id);
        }

        // valid template?
        $templates = array_keys(ilObjPortfolioTemplate::getAvailablePortfolioTemplates());
        if (!count($templates) || !in_array($prtt_id, $templates)) {
            $this->toRepository();
        }
        unset($templates);

        $source = new ilObjPortfolioTemplate($prtt_id, false);

        // create portfolio
        $target = new ilObjPortfolio();
        $target->setTitle($title);
        $target->create();
        $target_id = $target->getId();

        ilObjPortfolioTemplate::clonePagesAndSettings($source, $target, null, true);

        // link portfolio to exercise assignment
        //$this->linkPortfolioToAssignment($target_id);

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("prtf_portfolio_created_from_template"), true);
        $this->ctrl->setParameter($this, "prt_id", $target_id);
        $this->ctrl->redirect($this, "preview");
    }


    public static function _goto(string $a_target): void
    {
        global $DIC;

        $ctrl = $DIC->ctrl();

        $id = explode("_", $a_target);

        $ctrl->setParameterByClass("ilobjportfoliogui", "prt_id", $id[0]);
        if (count($id) === 2) {
            $ctrl->setParameterByClass("ilobjportfoliogui", "user_page", $id[1]);
        }
        $ctrl->redirectByClass(["ilsharedresourceGUI", "ilobjportfoliogui"], "preview");
    }

    public function createPortfolioFromAssignment(): void
    {
        $ilUser = $this->user;
        $ilSetting = $this->settings;

        $recipe = [];

        $title = $this->port_request->getPortfolioTitle();
        $prtt_id = $this->port_request->getPortfolioTemplate();

        // get assignment template
        $ass_template_id = 0;
        $ass_id = $this->port_request->getExcAssId();
        if ($ass_id > 0) {
            $ass = new ilExAssignment($ass_id);
            $ass_template_id = ilObject::_lookupObjectId($ass->getPortfolioTemplateId());
        }

        if ($prtt_id > 0) {
            $templates = array_keys(ilObjPortfolioTemplate::getAvailablePortfolioTemplates());
            if (!count($templates) || !in_array($prtt_id, $templates)) {
                if ($ass_template_id !== $prtt_id) {
                    $this->toRepository();
                }
            }

            //skills manipulation
            $pskills = array_keys(ilPersonalSkill::getSelectedUserSkills($ilUser->getId()));
            $skill_ids = array();

            $recipe = array();
            foreach (ilPortfolioTemplatePage::getAllPortfolioPages($prtt_id) as $page) {
                switch ($page["type"]) {
                    case ilPortfolioTemplatePage::TYPE_BLOG_TEMPLATE:
                        if (!$ilSetting->get('disable_wsp_blogs')) {
                            $recipe[$page["id"]] = array("blog", "create", $page['title']);
                        }
                        break;
                    case ilPortfolioPage::TYPE_PAGE:
                        $source_page = new ilPortfolioTemplatePage($page["id"]);
                        $source_page->buildDom(true);
                        $skill_ids = $this->getSkillsToPortfolioAssignment($pskills, $skill_ids, $source_page);
                        break;
                }
            }

            if ($skill_ids) {
                $recipe["skills"] = $skill_ids;
            }
        }

        // create portfolio
        $target = new ilObjPortfolio();
        $target->setTitle($title);
        $target->create();
        $target_id = $target->getId();

        if ($prtt_id) {
            $source = new ilObjPortfolioTemplate($prtt_id, false);
            ilObjPortfolioTemplate::clonePagesAndSettings($source, $target, $recipe);
        }

        // link portfolio to exercise assignment
        $this->linkPortfolioToAssignment($target_id);

        $this->ctrl->setParameter($this, "prt_id", $target_id);
        if ($prtt_id) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("prtf_portfolio_created_from_template"), true);
            $this->ctrl->redirect($this, "preview");
        } else {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("prtf_portfolio_created"), true);
            $this->ctrl->redirect($this, "view");
        }
    }

    public function linkPortfolioToAssignment(int $a_target_id): void
    {
        $ilAccess = $this->access;
        $ilUser = $this->user;

        $exc_ref_id = $this->port_request->getExerciseRefId();
        $ass_id = $this->port_request->getExcAssId();

        if ($exc_ref_id &&
            $ass_id &&
            $ilAccess->checkAccess("read", "", $exc_ref_id)) {
            $exc = new ilObjExercise($exc_ref_id);
            $ass = new ilExAssignment($ass_id);
            if ($ass->getExerciseId() === $exc->getId() &&
                $ass->getType() === ilExAssignment::TYPE_PORTFOLIO) {
                // #16205
                $sub = new ilExSubmission($ass, $ilUser->getId());
                $sub->addResourceObject($a_target_id);
            }
        }
    }

    public function getSkillsToPortfolioAssignment(
        array $a_pskills,
        array $a_skill_ids,
        ilPortfolioTemplatePage $a_source_page
    ): array {
        $dom = $a_source_page->getDom();
        if ($dom instanceof php4DOMDocument) {
            $dom = $dom->myDOMDocument;
        }
        $xpath = new DOMXPath($dom);
        $nodes = $xpath->query("//PageContent/Skills");
        foreach ($nodes as $node) {
            $skill_id = $node->getAttribute("Id");
            if (!in_array($skill_id, $a_pskills)) {
                $a_skill_ids[] = $skill_id;
            }
        }
        unset($nodes, $xpath, $dom);

        return $a_skill_ids;
    }

    /**
     * @throws \ILIAS\HTTP\Response\Sender\ResponseSendingException
     */
    public function printSelection(): void
    {
        $view = $this->getPrintView();
        $view->sendForm();
    }

    /**
     * @param bool $a_dev_mode
     * @throws \ILIAS\HTTP\Response\Sender\ResponseSendingException
     */
    public function showPrintView(
    ): void {
        $printview = $this->getPrintView();
        $printview->sendPrintView();
    }

    /**
     * Get offline message for sharing tab
     */
    protected function getOfflineMessage(): string
    {
        $ui = $this->ui;
        $lng = $this->lng;
        $ctrl = $this->ctrl;

        if (!$this->object->isOnline()) {
            $f = $ui->factory();
            $renderer = $ui->renderer();

            $buttons = [$f->button()->standard(
                $lng->txt("prtf_set_online"),
                $ctrl->getLinkTarget($this, "setOnlineAndShare")
            )];

            return $renderer->render($f->messageBox()->info($lng->txt("prtf_no_offline_share_info"))
                ->withButtons($buttons));
        }
        return "";
    }

    /**
     * Set online and switch to share screen
     */
    protected function setOnlineAndShare(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        if (ilObjPortfolio::_lookupOwner($this->object->getId()) === $this->user_id) {
            $this->object->setOnline(true);
            $this->object->update();
            $this->tpl->setOnScreenMessage('success', $lng->txt("prtf_has_been_set_online"), true);
        }
        $ilCtrl->redirectByClass("ilworkspaceaccessgui", "");
    }
}
