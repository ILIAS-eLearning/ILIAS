<?php

declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

use ILIAS\UI\Component\Input\Field\FormInput;

/**
 * Dashboard settings
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilObjDashboardSettingsGUI: ilPermissionGUI
 * @ilCtrl_isCalledBy ilObjDashboardSettingsGUI: ilAdministrationGUI
 */
class ilObjDashboardSettingsGUI extends ilObjectGUI
{
    private ilRbacSystem $rbacsystem;
    protected ILIAS\UI\Factory $ui_factory;
    protected ILIAS\UI\Renderer $ui_renderer;
    protected ilPDSelectedItemsBlockViewSettings $viewSettings;
    protected ILIAS\DI\UIServices $ui;
    protected ilDashboardSidePanelSettingsRepository $side_panel_settings;

    public function __construct(
        $a_data,
        int $a_id,
        bool $a_call_by_reference = true,
        bool $a_prepare_output = true
    ) {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;

        $this->lng = $DIC->language();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->access = $DIC->access();
        $this->ctrl = $DIC->ctrl();
        $this->settings = $DIC->settings();
        $lng = $DIC->language();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->request = $DIC->http()->request();
        $this->ui = $DIC->ui();

        $this->type = 'dshs';
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $lng->loadLanguageModule("dash");

        $this->viewSettings = new ilPDSelectedItemsBlockViewSettings($GLOBALS['DIC']->user());

        $this->side_panel_settings = new ilDashboardSidePanelSettingsRepository();
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        if (!$this->rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            throw new ilPermissionException($this->lng->txt('no_permission'));
        }

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                if (!$cmd || $cmd === 'view') {
                    $cmd = "editSettings";
                }

                $this->$cmd();
                break;
        }
    }

    public function getAdminTabs(): void
    {
        $rbacsystem = $this->rbacsystem;

        if ($rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "settings",
                $this->ctrl->getLinkTarget($this, "editSettings"),
                array("editSettings", "view")
            );
        }

        if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm"),
                array(),
                'ilpermissiongui'
            );
        }
    }

    public function editSettings(): void
    {
        $this->setSettingsSubTabs("general");
        $ui = $this->ui;
        $form = $this->initForm();
        $this->tpl->setContent($ui->renderer()->renderAsync($form));
    }

    public function initForm(): \ILIAS\UI\Component\Input\Container\Form\Standard
    {
        $ui = $this->ui;
        $f = $ui->factory();
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $side_panel = $this->side_panel_settings;

        $fields["enable_favourites"] = $f->input()->field()->checkbox($lng->txt("dash_enable_favourites"))
            ->withValue($this->viewSettings->enabledSelectedItems());
        $info_text = ($this->viewSettings->enabledMemberships())
            ? ""
            : $lng->txt("dash_member_main_alt") . " " . $ui->renderer()->render(
                $ui->factory()->link()->standard(
                    $lng->txt("dash_click_here"),
                    $ctrl->getLinkTargetByClass(["ilAdministrationGUI", "ilObjMainMenuGUI", "ilmmsubitemgui"])
                )
            );

        $fields["enable_memberships"] = $f->input()->field()->checkbox($lng->txt("dash_enable_memberships"), $info_text)
            ->withValue($this->viewSettings->enabledMemberships());

        $fields["enable_recommended_content"] = $f->input()->field()->checkbox($lng->txt("dash_enable_recommended_content"))
            ->withValue($this->viewSettings->enabledRecommendedContent());

        $fields["enable_learning_sequences"] = $f->input()->field()->checkbox($lng->txt("dash_enable_learning_sequences"))
            ->withValue($this->viewSettings->enabledLearningSequences());

        $fields["enable_study_programmes"] = $f->input()->field()->checkbox($lng->txt("dash_enable_study_programmes"))
            ->withValue($this->viewSettings->enabledStudyProgrammes());

        // main panel
        $section1 = $f->input()->field()->section($this->maybeDisable($fields), $lng->txt("dash_main_panel"));

        $sp_fields = [];
        foreach ($side_panel->getValidModules() as $mod) {
            $sp_fields["enable_" . $mod] = $f->input()->field()->checkbox($lng->txt("dash_enable_" . $mod))
                ->withValue($side_panel->isEnabled($mod));
        }

        // side panel
        $section2 = $f->input()->field()->section($this->maybeDisable($sp_fields), $lng->txt("dash_side_panel"));

        $form_action = $ctrl->getLinkTarget($this, "saveSettings");
        return $f->input()->container()->form()->standard(
            $form_action,
            ["main_panel" => $section1, "side_panel" => $section2, 'sortation' => $this->getSortation()]
        );
    }

    public function getPresentationForm() : \ILIAS\UI\Component\Input\Container\Form\Standard
    {
        return $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getFormAction($this, 'savePresentation'),
            array_map(
                function (int $view) {
                    return $this->getViewPresentation(
                        $view,
                        $this->lng->txt("dash_presentation_" . $this->viewSettings->getViewName($view))
                    );
                },
                $this->viewSettings->getPresentationViews()
            )
        );
    }

    public function saveSettings(): void
    {
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;
        $side_panel = $this->side_panel_settings;

        if (!$this->canWrite()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_permission'), true);
            $ilCtrl->redirect($this, "editSettings");
        }

        $request = $this->request;

        $form = $this->initForm();
        $form = $form->withRequest($request);
        $form_data = $form->getData();
        $this->viewSettings->enableSelectedItems(($form_data['main_panel']['enable_favourites']));
        $this->viewSettings->enableMemberships(($form_data['main_panel']['enable_memberships']));
        $this->viewSettings->enableRecommendedContent(($form_data['main_panel']['enable_recommended_content']));
        $this->viewSettings->enableLearningSequences(($form_data['main_panel']['enable_learning_sequences']));
        $this->viewSettings->enableStudyProgrammes(($form_data['main_panel']['enable_study_programmes']));

        foreach ($side_panel->getValidModules() as $mod) {
            $side_panel->enable($mod, (bool) $form_data['side_panel']['enable_' . $mod]);
        }

        $this->viewSettings->storeSorting(
            $form_data['sortation']['default_sort'],
            $form_data['sortation']['avail_sort'] ?: []
        );


        $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
        $ilCtrl->redirect($this, "editSettings");
    }


    public function setSettingsSubTabs(string $a_active): void
    {
        $rbacsystem = $this->rbacsystem;

        $tabs = $this->tabs_gui;
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        if ($rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $tabs->addSubTab(
                "general",
                $lng->txt("general_settings"),
                $ctrl->getLinkTarget($this, "editSettings")
            );

            $tabs->addSubTab(
                "presentation",
                $lng->txt("dash_presentation"),
                $ctrl->getLinkTarget($this, "editPresentation")
            );
        }

        $tabs->activateSubTab($a_active);
    }

    public function editPresentation(): void
    {
        $lng = $this->lng;
        $ui_factory = $this->ui_factory;

        $this->tabs_gui->activateTab("settings");
        $this->setSettingsSubTabs("presentation");

        $form = $this->getPresentationForm();

        $this->tpl->setContent($this->ui->renderer()->renderAsync($form));
    }

    public function getViewPresentation(int $view, string $title): \ILIAS\UI\Component\Input\Field\Section
    {
        $lng = $this->lng;
        $ops = $this->viewSettings->getAvailablePresentationsByView($view);
        $pres_options = array_column(array_map(static function ($k, $v) use ($lng) {
            return [$v, $lng->txt("dash_" . $v)];
        }, array_keys($ops), $ops), 1, 0);
        $avail_pres = $this->ui_factory->input()->field()->multiSelect($lng->txt("dash_avail_presentation"), $pres_options)
                                 ->withValue($this->viewSettings->getActivePresentationsByView($view));
        $default_pres = $this->ui_factory->input()->field()->radio($lng->txt("dash_default_presentation"))
                                   ->withOption('list', $lng->txt("dash_list"))
                                   ->withOption('tile', $lng->txt("dash_tile"));
        $default_pres = $default_pres->withValue($this->viewSettings->getDefaultPresentationByView($view));
        return $this->ui_factory->input()->field()->section(
            $this->maybeDisable(["avail_pres" => $avail_pres, "default_pres" => $default_pres]),
            $title
        );
    }

    public function getSortation(): \ILIAS\UI\Component\Input\Field\Section
    {
        $lng = $this->lng;
        $ops = $this->viewSettings->getAvailableSortOptions();
        $sortation_options = array_column(array_map(static function ($k, $v) use ($lng) {
            return [$v, $lng->txt("dash_sort_by_" . $v)];
        }, array_keys($ops), $ops), 1, 0);
        $avail_sort = $this->ui_factory->input()->field()->multiSelect($lng->txt("dash_avail_sortation"), $sortation_options)
                                 ->withValue($this->viewSettings->getActiveSortings());
        $default_sort = $this->ui_factory->input()->field()->radio($lng->txt("dash_default_sortation"));
        foreach ($sortation_options as $k => $text) {
            $default_sort = $default_sort->withOption($k, $text);
        }
        $default_sort = $default_sort->withValue($this->viewSettings->getDefaultSorting());
        return $this->ui_factory->input()->field()->section(
            $this->maybeDisable(["avail_sort" => $avail_sort, "default_sort" => $default_sort]),
            $lng->txt("dash_sortation")
        );
    }

    protected function savePresentation() : void
    {
        $request = $this->request;
        $lng = $this->lng;
        $ctrl = $this->ctrl;

        $form = $this->getPresentationForm();
        $form = $form->withRequest($request);
        $form_data = $form->getData();

        if (!$this->canWrite()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_permission'), true);
            $this->editPresentation();
        }


        foreach ($form_data as $view => $view_data) {
            $this->viewSettings->storeViewPresentation(
                $view,
                $view_data['default_pres'],
                $view_data['avail_pres'] ?: []
            );
        }
        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        $this->editPresentation();
    }

    /**
     * @param FormInput[] $fields
     * @return FormInput[]
     */
    private function maybeDisable(array $fields): array
    {
        if ($this->canWrite()) {
            return $fields;
        }

        return array_map(static function (FormInput $field): FormInput {
            return $field->withDisabled(true);
        }, $fields);
    }

    private function canWrite(): bool
    {
        return $this->rbacsystem->checkAccess('write', $this->object->getRefId());
    }
}
