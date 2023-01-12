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
 * Portfolio template view gui class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 *
 * @ilCtrl_Calls ilObjPortfolioTemplateGUI: ilPortfolioTemplatePageGUI, ilPageObjectGUI, ilNoteGUI
 * @ilCtrl_Calls ilObjPortfolioTemplateGUI: ilObjectCopyGUI, ilInfoScreenGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjPortfolioTemplateGUI: ilPermissionGUI, ilExportGUI, ilObjectContentStyleSettingsGUI
 * @ilCtrl_Calls ilObjPortfolioTemplateGUI: ilObjectMetaDataGUI
.php
 */
class ilObjPortfolioTemplateGUI extends ilObjPortfolioBaseGUI
{
    protected ilNavigationHistory $nav_history;
    protected ilHelpGUI $help;
    protected ilTabsGUI $tabs;

    public function __construct(
        int $a_id = 0,
        int $a_id_type = self::REPOSITORY_NODE_ID,
        int $a_parent_node_id = 0
    ) {
        parent::__construct($a_id, $a_id_type, $a_parent_node_id);
        global $DIC;

        $this->nav_history = $DIC["ilNavigationHistory"];
        $this->help = $DIC["ilHelp"];
        $this->tabs = $DIC->tabs();
        $this->toolbar = $DIC->toolbar();
        $this->user = $DIC->user();
    }

    public function getType(): string
    {
        return "prtt";
    }

    public function executeCommand(): void
    {
        $ilNavigationHistory = $this->nav_history;

        // add entry to navigation history
        if (!$this->getCreationMode() &&
            $this->getAccessHandler()->checkAccess("read", "", $this->node_id)) {
            $link = $this->ctrl->getLinkTarget($this, "view");
            $ilNavigationHistory->addItem($this->node_id, $link, "prtt");
        }

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("view");
        switch ($next_class) {
            case 'ilportfoliotemplatepagegui':
                $this->determinePageCall(); // has to be done before locator!
                $this->prepareOutput();
                $this->handlePageCall($cmd);
                break;

            case "ilnotegui":
                $this->preview();
                break;

            case "ilinfoscreengui":
                $this->prepareOutput();
                $this->addHeaderAction();
                $this->infoScreenForward();
                break;

            case "ilcommonactiondispatchergui":
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->prepareOutput();
                $this->ctrl->forwardCommand($gui);
                break;

            case "ilpermissiongui":
                $this->prepareOutput();
                $this->tabs_gui->activateTab("id_permissions");
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            case "ilobjectcopygui":
                $this->prepareOutput();
                $cp = new ilObjectCopyGUI($this);
                $cp->setType("prtt");
                $this->ctrl->forwardCommand($cp);
                break;

            case 'ilexportgui':
                $this->prepareOutput();
                $this->tabs_gui->activateTab("export");
                $exp_gui = new ilExportGUI($this);
                $exp_gui->addFormat("xml");
                $this->ctrl->forwardCommand($exp_gui);
                break;

            case "ilobjectcontentstylesettingsgui":
                $this->checkPermission("write");
                $this->prepareOutput();
                $this->addHeaderAction();
                $this->tabs_gui->activateTab("settings");
                $this->setSettingsSubTabs("style");
                $settings_gui = $this->content_style_gui
                    ->objectSettingsGUIForRefId(
                        null,
                        $this->object->getRefId()
                    );
                $this->ctrl->forwardCommand($settings_gui);
                break;

            case "ilobjectmetadatagui":
                $this->checkPermission("write");
                $this->prepareOutput();
                $this->addHeaderAction();
                $this->tabs_gui->activateTab("advmd");
                $md_gui = new ilObjectMetaDataGUI($this->object, "pfpg");
                $this->ctrl->forwardCommand($md_gui);
                break;

            default:
                $this->addHeaderAction();
                ilObject2GUI::executeCommand();
        }
    }

    protected function setTabs(): void
    {
        $ilHelp = $this->help;

        $ilHelp->setScreenIdComponent("prtt");

        if ($this->checkPermissionBool("write")) {
            $this->tabs_gui->addTab(
                "pages",
                $this->lng->txt("content"),
                $this->ctrl->getLinkTarget($this, "view")
            );
        }

        if ($this->checkPermissionBool("read")) {
            $this->tabs_gui->addTab(
                "id_info",
                $this->lng->txt("info_short"),
                $this->ctrl->getLinkTargetByClass(array("ilobjportfoliotemplategui", "ilinfoscreengui"), "showSummary")
            );
        }

        if ($this->checkPermissionBool("write")) {
            $this->tabs_gui->addTab(
                "settings",
                $this->lng->txt("settings"),
                $this->ctrl->getLinkTarget($this, "edit")
            );

            $mdgui = new ilObjectMetaDataGUI($this->object, "pfpg");
            $mdtab = $mdgui->getTab();
            if ($mdtab) {
                $this->tabs_gui->addTab(
                    "advmd",
                    $this->lng->txt("meta_data"),
                    $mdtab
                );
            }

            $this->tabs_gui->addTab(
                "export",
                $this->lng->txt("export"),
                $this->ctrl->getLinkTargetByClass("ilexportgui", "")
            );
        }

        if ($this->checkPermissionBool("read")) {
            $this->tabs_gui->addNonTabbedLink(
                "preview",
                $this->lng->txt("user_profile_preview"),
                $this->ctrl->getLinkTarget($this, "preview")
            );
        }

        // will add permissions if needed
        ilObject2GUI::setTabs();
    }

    /**
     * this one is called from the info button in the repository
     */
    public function infoScreen(): void
    {
        $this->ctrl->setCmd("showSummary");
        $this->ctrl->setCmdClass("ilinfoscreengui");
        $this->infoScreenForward();
    }

    public function infoScreenForward(): void
    {
        $ilTabs = $this->tabs;
        $ilToolbar = $this->toolbar;

        $ilTabs->activateTab("id_info");

        $this->checkPermission("visible");

        if ($this->checkPermissionBool("read")) {
            $this->lng->loadLanguageModule("cntr");

            $button = ilLinkButton::getInstance();
            $button->setPrimary(true);
            $button->setCaption("prtf_create_portfolio_from_template");
            $button->setUrl($this->ctrl->getLinkTarget($this, "createfromtemplate"));
            $ilToolbar->addButtonInstance($button);
        }

        $info = new ilInfoScreenGUI($this);

        $info->enablePrivateNotes();

        if ($this->checkPermissionBool("read")) {
            $info->enableNews();
        }

        // no news editing for files, just notifications
        $info->enableNewsEditing(false);
        if ($this->checkPermissionBool("write")) {
            $news_set = new ilSetting("news");
            $enable_internal_rss = $news_set->get("enable_rss_for_internal");

            if ($enable_internal_rss) {
                $info->setBlockProperty("news", "settings", true);
                $info->setBlockProperty("news", "public_notifications_option", true);
            }
        }

        // standard meta data
        $info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());

        $this->ctrl->forwardCommand($info);
    }


    //
    // CREATE/EDIT
    //

    protected function initDidacticTemplate(ilPropertyFormGUI $form): ilPropertyFormGUI
    {
        $ilUser = $this->user;

        $all = ilObjPortfolio::getPortfoliosOfUser($ilUser->getId());
        if (count($all)) {
            $opts = array("" => $this->lng->txt("please_select"));
            foreach ($all as $item) {
                $opts[$item["id"]] = $item["title"];
            }
            $prtf = new ilSelectInputGUI($this->lng->txt("prtf_create_template_from_portfolio"), "prtf");
            $prtf->setInfo($this->lng->txt("prtf_create_template_from_portfolio_info"));
            $prtf->setOptions($opts);
            $form->addItem($prtf);
        }

        return $form;
    }

    protected function afterSave(ilObject $new_object): void
    {
        if ($this->port_request->getPortfolioId() > 0) {
            $source = new ilObjPortfolio($this->port_request->getPortfolioId(), false);

            /** @var ilObjPortfolioBase $obj */
            $obj = $new_object;
            ilObjPortfolio::clonePagesAndSettings($source, $obj);
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("prtt_portfolio_created"), true);
        $this->ctrl->setParameter($this, "prt_id", $new_object->getId());
        $this->ctrl->redirect($this, "view");
    }

    protected function initEditCustomForm(ilPropertyFormGUI $a_form): void
    {
        $obj_service = $this->object_service;
        // activation/availability

        $this->lng->loadLanguageModule('rep');

        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('rep_activation_availability'));
        $a_form->addItem($section);

        // additional info only with multiple references
        $act_obj_info = $act_ref_info = "";
        if (count(ilObject::_getAllReferences($this->object->getId())) > 1) {
            $act_obj_info = ' ' . $this->lng->txt('rep_activation_online_object_info');
            $act_ref_info = $this->lng->txt('rep_activation_access_ref_info');
        }

        $online = new ilCheckboxInputGUI($this->lng->txt('rep_activation_online'), 'online');
        $online->setInfo($this->lng->txt('prtt_activation_online_info') . $act_obj_info);
        $a_form->addItem($online);

        $dur = new ilDateDurationInputGUI($this->lng->txt("rep_visibility_until"), "access_period");
        $dur->setShowTime(true);
        $dur->setEndText($this->lng->txt('rep_activation_limited_end'));
        $a_form->addItem($dur);

        $visible = new ilCheckboxInputGUI($this->lng->txt('rep_activation_limited_visibility'), 'access_visiblity');
        $visible->setInfo($this->lng->txt('prtt_activation_limited_visibility_info'));
        $dur->addSubItem($visible);

        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('obj_presentation'));
        $a_form->addItem($section);

        // tile image
        $obj_service->commonSettings()->legacyForm($a_form, $this->object)->addTileImage();


        parent::initEditCustomForm($a_form);

        ilObjectServiceSettingsGUI::initServiceSettingsForm(
            $this->object->getId(),
            $a_form,
            array(
                ilObjectServiceSettingsGUI::CUSTOM_METADATA
            )
        );

        $tit = $a_form->getItemByPostVar("title");
        $tit->setInfo($this->lng->txt('prtt_title_info'));
    }

    protected function getEditFormCustomValues(array &$a_values): void
    {
        $a_values["online"] = $this->object->isOnline();
        $a_values["access_period"]["start"] = $this->object->getActivationStartDate()
            ? new ilDateTime($this->object->getActivationStartDate(), IL_CAL_UNIX)
            : null;
        $a_values["access_period"]["end"] = $this->object->getActivationEndDate()
            ? new ilDateTime($this->object->getActivationEndDate(), IL_CAL_UNIX)
            : null;
        $a_values["access_visiblity"] = $this->object->getActivationVisibility();

        $a_values['cont_custom_md'] = ilContainer::_lookupContainerSetting(
            $this->object->getId(),
            ilObjectServiceSettingsGUI::CUSTOM_METADATA,
            false
        );

        parent::getEditFormCustomValues($a_values);
    }

    protected function updateCustom(ilPropertyFormGUI $form): void
    {
        $obj_service = $this->object_service;

        $this->object->setOnline($form->getInput("online"));

        // activation
        $period = $form->getItemByPostVar("access_period");
        if ($period->getStart() && $period->getEnd()) {
            $this->object->setActivationLimited(true);
            $this->object->setActivationVisibility($form->getInput("access_visiblity"));
            $this->object->setActivationStartDate($period->getStart()->get(IL_CAL_UNIX));
            $this->object->setActivationEndDate($period->getEnd()->get(IL_CAL_UNIX));
        } else {
            $this->object->setActivationLimited(false);
        }

        parent::updateCustom($form);

        $obj_service->commonSettings()->legacyForm($form, $this->object)->saveTileImage();

        ilObjectServiceSettingsGUI::updateServiceSettingsForm(
            $this->object->getId(),
            $form,
            array(
                ilObjectServiceSettingsGUI::CUSTOM_METADATA
            )
        );
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
    ): ilPortfolioTemplatePage {
        if (!$a_portfolio_id && $this->object) {
            $a_portfolio_id = $this->object->getId();
        }
        $page = new ilPortfolioTemplatePage((int) $a_page_id);
        $page->setPortfolioId($a_portfolio_id);
        return $page;
    }

    /**
     * Get portfolio template page gui instance
     */
    protected function getPageGUIInstance(
        int $a_page_id
    ): ilPortfolioTemplatePageGUI {
        $page_gui = new ilPortfolioTemplatePageGUI(
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
        return "ilportfoliotemplatepagegui";
    }

    protected function initCopyPageFormOptions(ilPropertyFormGUI $a_form): void
    {
        // always existing prtft
        $hi = new ilHiddenInputGUI("target");
        $hi->setValue("old");
        $a_form->addItem($hi);

        $options = array();
        $all = ilObjPortfolioTemplate::getAvailablePortfolioTemplates("write");
        foreach ($all as $id => $title) {
            $options[$id] = $title;
        }
        $prtf = new ilSelectInputGUI($this->lng->txt("obj_prtt"), "prtf");
        $prtf->setRequired(true);
        $prtf->setOptions($options);
        $a_form->addItem($prtf);
    }


    //
    // BLOG
    //

    /**
     * Init blog template page form
     */
    protected function initBlogForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));

        $obj = new ilTextInputGUI($this->lng->txt("title"), "blog");
        $obj->setRequired(true);
        $form->addItem($obj);

        // save and cancel commands
        $form->setTitle($this->lng->txt("prtf_add_blog") . ": " .
            $this->object->getTitle());
        $form->addCommandButton("saveBlog", $this->lng->txt("save"));
        $form->addCommandButton("view", $this->lng->txt("cancel"));

        return $form;
    }

    protected function saveBlog(): void
    {
        $form = $this->initBlogForm();
        if ($form->checkInput() && $this->checkPermissionBool("write")) {
            $page = $this->getPageInstance();
            $page->setType(ilPortfolioTemplatePage::TYPE_BLOG_TEMPLATE);
            $page->setTitle($form->getInput("blog"));
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
    // TRANSMOGRIFIER
    //

    public function preview(
        bool $a_return = false,
        bool $a_content = false,
        bool $a_show_notes = true
    ): string {
        if (!$this->checkPermissionBool("write") &&
            $this->checkPermissionBool("read")) {
            $this->lng->loadLanguageModule("cntr");

            $button = ilLinkButton::getInstance();
            $button->setPrimary(true);
            $button->setCaption("prtf_create_portfolio_from_template");
            $button->setUrl($this->ctrl->getLinkTarget($this, "createfromtemplate"));
            $this->tpl->setHeaderActionMenu($button->render());
        }

        return parent::preview($a_return, $a_content, $a_show_notes);
    }

    public function createFromTemplateOld(): void
    {
        $this->ctrl->setParameterByClass("ilobjportfoliogui", "prtt_pre", $this->object->getId());
        $this->ctrl->redirectByClass(array("ilDashboardGUI", "ilportfoliorepositorygui", "ilobjportfoliogui"), "create");
    }

    public function createFromTemplate(): void
    {
        $this->ctrl->setParameterByClass("ilobjportfoliogui", "prtt_pre", $this->object->getId());
        $this->ctrl->redirectByClass(array("ilDashboardGUI", "ilportfoliorepositorygui", "ilobjportfoliogui"), "createFromTemplateDirect");
    }

    public static function _goto(string $a_target): void
    {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;

        $access = $DIC->access();
        $ctrl = $DIC->ctrl();

        $id = explode("_", $a_target);
        $ref_id = $id[0];

        $ctrl->setParameterByClass("ilRepositoryGUI", "ref_id", $ref_id);
        if ($access->checkAccess("read", "", $ref_id)) {
            $ctrl->redirectByClass("ilRepositoryGUI", "preview");
        } else {
            $ctrl->redirectByClass("ilRepositoryGUI", "infoScreen");
        }
    }
}
