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

declare(strict_types=1);

use ILIAS\UI\Component\Input\Field\FormInput;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;
use ILIAS\UI\Component\Input\Field\Section;

/**
 * Dashboard settings
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilObjDashboardSettingsGUI: ilPermissionGUI
 * @ilCtrl_isCalledBy ilObjDashboardSettingsGUI: ilAdministrationGUI
 */
class ilObjDashboardSettingsGUI extends ilObjectGUI
{
    public const VIEW_MODE_SETTINGS = 'Settings';
    public const VIEW_MODE_PRESENTATION = 'Presentation';
    public const VIEW_MODE_SORTING = 'Sorting';
    public const DASH_SORT_PREFIX = 'dash_sort_by_';
    public const DASH_ENABLE_PREFIX = 'dash_enable_';

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

        if (!$this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
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
        $rbacsystem = $this->rbac_system;

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
        $form = $this->getViewForm(self::VIEW_MODE_SETTINGS);
        $this->tpl->setContent($ui->renderer()->renderAsync($form));
    }

    public function editSorting(): void
    {
        $this->tabs_gui->activateTab("settings");
        $this->setSettingsSubTabs("sorting");
        $form = $this->getViewForm(self::VIEW_MODE_SORTING);
        $this->tpl->setContent($this->ui->renderer()->renderAsync($form));
    }

    public function getViewForm(string $mode): StandardForm
    {
        switch ($mode) {
            case self::VIEW_MODE_PRESENTATION:
            case self::VIEW_MODE_SORTING:
                return $this->ui_factory->input()->container()->form()->standard(
                    $this->ctrl->getFormAction($this, 'save' . $mode),
                    array_map(
                        fn (int $view): Section => $this->getViewByMode($mode, $view),
                        $this->viewSettings->getPresentationViews()
                    )
                );
            case self::VIEW_MODE_SETTINGS:
            default:
                return $this->getSettingsForm();
        }
    }

    public function getViewSectionSorting(int $view, string $title): Section
    {
        $this->tpl->addJavaScript("Services/Dashboard/Administration/js/SortationUserInputHandler.js");
        $lng = $this->lng;
        $availabe_sort_options = $this->viewSettings->getAvailableSortOptionsByView($view);
        $options = array_reduce(
            $availabe_sort_options,
            static function (array $options, string $option) use ($lng): array {
                $options[$option] = $lng->txt(self::DASH_SORT_PREFIX . $option);
                return $options;
            },
            []
        );

        $available_sorting = $this->ui_factory
            ->input()
            ->field()
            ->multiSelect($this->lng->txt("dash_avail_sortation"), $options)
            ->withValue(
                $this->viewSettings->getActiveSortingsByView($view)
            )
            ->withAdditionalOnLoadCode(
                static fn (string $id) =>
                    "document.getElementById('$id').setAttribute('data-checkbox', 'activeSorting$view');
                    document.addEventListener('DOMContentLoaded', function () {
                        handleUserInputForSortationsByView($view);
                    });"
            );
        $default_sorting = $this->ui_factory
            ->input()
            ->field()
            ->select($this->lng->txt("dash_default_sortation"), $options)
            ->withValue($this->viewSettings->getDefaultSortingByView($view))
            ->withRequired(true)
            ->withAdditionalOnLoadCode(
                static fn (string $id) =>
                    "document.getElementById('$id').setAttribute('data-select', 'sorting$view');"
            );
        return $this->ui_factory->input()->field()->section(
            $this->maybeDisable(["avail_sorting" => $available_sorting, "default_sorting" => $default_sorting]),
            $title
        );
    }

    public function getSettingsForm(): StandardForm
    {
        $field = $this->ui->factory()->input()->field();
        $lng = $this->lng;

        $side_panel = $this->side_panel_settings;

        $fields[self::DASH_ENABLE_PREFIX . "favourites"] = $field->checkbox($lng->txt(self::DASH_ENABLE_PREFIX . "favourites"))
            ->withValue($this->viewSettings->enabledSelectedItems());
        $info_text = ($this->viewSettings->enabledMemberships())
            ? ""
            : $lng->txt("dash_member_main_alt") . " " . $this->ui->renderer()->render(
                $this->ui->factory()->link()->standard(
                    $lng->txt("dash_click_here"),
                    $this->ctrl->getLinkTargetByClass(["ilAdministrationGUI", "ilObjMainMenuGUI", "ilmmsubitemgui"])
                )
            );

        $fields[self::DASH_ENABLE_PREFIX . "recommended_content"] = $field->checkbox($lng->txt(self::DASH_ENABLE_PREFIX . "recommended_content"))
                                                  ->withValue(true)
                                                  ->withDisabled(true);
        $fields[self::DASH_ENABLE_PREFIX . "memberships"] = $field->checkbox($lng->txt(self::DASH_ENABLE_PREFIX . "memberships"), $info_text)
            ->withValue($this->viewSettings->enabledMemberships());


        $fields[self::DASH_ENABLE_PREFIX . "learning_sequences"] = $field->checkbox($lng->txt(self::DASH_ENABLE_PREFIX . "learning_sequences"))
            ->withValue($this->viewSettings->enabledLearningSequences());

        $fields[self::DASH_ENABLE_PREFIX . "study_programmes"] = $field->checkbox($lng->txt(self::DASH_ENABLE_PREFIX . "study_programmes"))
            ->withValue($this->viewSettings->enabledStudyProgrammes());

        // main panel
        $section1 = $field->section($this->maybeDisable($fields), $lng->txt("dash_main_panel"));

        $sp_fields = [];
        foreach ($side_panel->getValidModules() as $mod) {
            $sp_fields[self::DASH_ENABLE_PREFIX . $mod] = $field->checkbox($lng->txt(self::DASH_ENABLE_PREFIX . $mod))
                ->withValue($side_panel->isEnabled($mod));
        }

        // side panel
        $section2 = $field->section($this->maybeDisable($sp_fields), $lng->txt("dash_side_panel"));

        $form_action = $this->ctrl->getLinkTarget($this, "saveSettings");
        return $this->ui->factory()->input()->container()->form()->standard(
            $form_action,
            ["main_panel" => $section1, "side_panel" => $section2]
        );
    }

    public function getViewByMode(string $mode, int $view): Section
    {
        switch ($mode) {
            case self::VIEW_MODE_SORTING:
                return $this->getViewSectionSorting(
                    $view,
                    $this->lng->txt("dash_" . $this->viewSettings->getViewName($view))
                );
            case self::VIEW_MODE_PRESENTATION:
            default:
                return $this->getViewSectionPresentation(
                    $view,
                    $this->lng->txt("dash_" . $this->viewSettings->getViewName($view))
                );
        }
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

        $form = $this->getViewForm(self::VIEW_MODE_SETTINGS);
        $form = $form->withRequest($request);
        $form_data = $form->getData();
        $this->viewSettings->enableSelectedItems(($form_data['main_panel'][self::DASH_ENABLE_PREFIX . 'favourites']));
        $this->viewSettings->enableMemberships(($form_data['main_panel'][self::DASH_ENABLE_PREFIX . 'memberships']));
        $this->viewSettings->enableRecommendedContent(($form_data['main_panel'][self::DASH_ENABLE_PREFIX . 'recommended_content']));
        $this->viewSettings->enableLearningSequences(($form_data['main_panel'][self::DASH_ENABLE_PREFIX . 'learning_sequences']));
        $this->viewSettings->enableStudyProgrammes(($form_data['main_panel'][self::DASH_ENABLE_PREFIX . 'study_programmes']));

        foreach ($side_panel->getValidModules() as $mod) {
            $side_panel->enable($mod, (bool) $form_data['side_panel'][self::DASH_ENABLE_PREFIX . $mod]);
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
        $ilCtrl->redirect($this, "editSettings");
    }


    public function setSettingsSubTabs(string $a_active): void
    {
        $rbacsystem = $this->rbac_system;

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

            $tabs->addSubTab(
                "sorting",
                $lng->txt("dash_sortation"),
                $ctrl->getLinkTarget($this, "editSorting")
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

        $form = $this->getViewForm(self::VIEW_MODE_PRESENTATION);

        $this->tpl->setContent($this->ui->renderer()->renderAsync($form));
    }

    public function getViewSectionPresentation(int $view, string $title): Section
    {
        $lng = $this->lng;
        $ops = $this->viewSettings->getAvailablePresentationsByView($view);
        $pres_options = array_column(array_map(
            static fn (int $k, string $v): array => [$v, $lng->txt("dash_" . $v)],
            array_keys($ops),
            $ops
        ), 1, 0);
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

    protected function savePresentation(): void
    {
        $request = $this->request;
        $lng = $this->lng;
        $ctrl = $this->ctrl;

        $form = $this->getViewForm(self::VIEW_MODE_PRESENTATION);
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
                $view_data['avail_pres'] ?? []
            );
        }
        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        $this->editPresentation();
    }

    public function saveSorting(): void
    {
        if (!$this->canWrite()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_permission'), true);
            $this->editSorting();
        }

        $form = $this->getViewForm(self::VIEW_MODE_SORTING);
        $form = $form->withRequest($this->request);
        $form_data = $form->getData();

        foreach ($form_data as $view => $view_data) {
            if (isset($view_data['default_sorting'])) {
                if (!is_array($view_data['avail_sorting'] ?? null)) {
                    $view_data['avail_sorting'] = [$view_data['default_sorting']];
                }
                $this->viewSettings->storeViewSorting(
                    $view,
                    $view_data['default_sorting'],
                    $view_data['avail_sorting']
                );
            }
        }
        $this->editSorting();
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
        return $this->rbac_system->checkAccess('write', $this->object->getRefId());
    }
}
