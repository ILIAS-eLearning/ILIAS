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

use ILIAS\DI\UIServices;
use ILIAS\UI\Component\Input\Field\FormInput;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;
use ILIAS\UI\Component\Input\Field\Section;

/**
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

    protected Factory $ui_factory;
    protected Renderer $ui_renderer;
    protected ilPDSelectedItemsBlockViewSettings $viewSettings;
    protected UIServices $ui;
    protected ilDashboardSidePanelSettingsRepository $side_panel_settings;

    public function __construct(
        $a_data,
        int $a_id,
        bool $a_call_by_reference = true,
        bool $a_prepare_output = true
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->rbac_system = $DIC->rbac()->system();
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

        $lng->loadLanguageModule('dash');

        $this->viewSettings = new ilPDSelectedItemsBlockViewSettings($DIC->user());

        $this->side_panel_settings = new ilDashboardSidePanelSettingsRepository();
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        if (!$this->rbac_system->checkAccess('visible,read', $this->object->getRefId())) {
            throw new ilPermissionException($this->lng->txt('no_permission'));
        }

        switch ($this->ctrl->getNextClass($this)) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                if (!$cmd || $cmd === 'view') {
                    $cmd = 'editSettings';
                }

                $this->$cmd();
                break;
        }
    }

    public function getAdminTabs(): void
    {
        if ($this->rbac_system->checkAccess('visible,read', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'settings',
                $this->ctrl->getLinkTarget($this, 'editSettings'),
                array('editSettings', 'view')
            );
        }

        if ($this->rbac_system->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'perm_settings',
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', 'perm'),
                array(),
                'ilpermissiongui'
            );
        }
    }

    public function editSettings(): void
    {
        if ($this->settings->get('rep_favourites', '0') !== '1') {
            $content[] = $this->ui_factory->messageBox()->info($this->lng->txt('favourites_disabled_info'));
        }

        if ($this->settings->get('mmbr_my_crs_grp', '0') !== '1') {
            $content[] = $this->ui_factory->messageBox()->info($this->lng->txt('memberships_disabled_info'));
        }
        $this->setSettingsSubTabs('general');
        $content[] = $this->getViewForm(self::VIEW_MODE_SETTINGS);
        $this->tpl->setContent($this->ui->renderer()->renderAsync($content));
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
                        fn(int $view): Section => $this->getViewByMode($mode, $view),
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
                static fn(string $id) =>
                    "document.getElementById('$id').setAttribute('data-checkbox', 'activeSorting$view');
                    document.addEventListener('DOMContentLoaded', function () {
                        il.Dashboard.handleUserInputForSortationsByView($view);
                    });"
            );
        $default_sorting = $this->ui_factory
            ->input()
            ->field()
            ->select($this->lng->txt("dash_default_sortation"), $options)
            ->withValue($this->viewSettings->getDefaultSortingByView($view))
            ->withRequired(true)
            ->withAdditionalOnLoadCode(
                static fn(string $id) =>
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

        $fields[self::DASH_ENABLE_PREFIX . 'favourites'] = $field->checkbox($lng->txt(self::DASH_ENABLE_PREFIX . "favourites"))
            ->withValue($this->viewSettings->enabledSelectedItems());
        // lookup refid by type
        $main_menu_objs = ilObject::_getObjectsByType('mme');
        $obj_id = array_pop($main_menu_objs)['obj_id'];
        $main_menu_refs = ilObject::_getAllReferences($obj_id);
        $ref_id = array_pop($main_menu_refs);

        $this->ctrl->setParameterByClass(ilMMSubItemGUI::class, "ref_id", $ref_id);
        $info_text = ($this->viewSettings->enabledMemberships())
            ? ''
            : $lng->txt('dash_member_main_alt') . ' ' . $this->ui->renderer()->render(
                $this->ui_factory->link()->standard(
                    $lng->txt('dash_click_here'),
                    $this->ctrl->getLinkTargetByClass(['ilAdministrationGUI', 'ilObjMainMenuGUI', 'ilmmsubitemgui'])
                )
            );
        $this->ctrl->clearParametersByClass(ilMMSubItemGUI::class);

        $fields[self::DASH_ENABLE_PREFIX . 'recommended_content'] = $field->checkbox($lng->txt(self::DASH_ENABLE_PREFIX . "recommended_content"))
                                                  ->withValue(true)
                                                  ->withDisabled(true);
        $fields[self::DASH_ENABLE_PREFIX . 'memberships'] = $field->checkbox($lng->txt(self::DASH_ENABLE_PREFIX . "memberships"), $info_text)
            ->withValue($this->viewSettings->enabledMemberships());


        $fields[self::DASH_ENABLE_PREFIX . 'learning_sequences'] = $field->checkbox($lng->txt(self::DASH_ENABLE_PREFIX . "learning_sequences"))
            ->withValue($this->viewSettings->enabledLearningSequences());

        $fields[self::DASH_ENABLE_PREFIX . 'study_programmes'] = $field->checkbox($lng->txt(self::DASH_ENABLE_PREFIX . "study_programmes"))
            ->withValue($this->viewSettings->enabledStudyProgrammes());

        $section1 = $field->section($this->maybeDisable($fields), $lng->txt('dash_main_panel'));

        $sp_fields = [];
        foreach ($this->side_panel_settings->getValidModules() as $mod) {
            $sp_fields[self::DASH_ENABLE_PREFIX . $mod] = $field->checkbox($lng->txt(self::DASH_ENABLE_PREFIX . $mod))
                ->withValue($this->side_panel_settings->isEnabled($mod));
        }

        $section2 = $field->section($this->maybeDisable($sp_fields), $lng->txt('dash_side_panel'));

        $form_action = $this->ctrl->getLinkTarget($this, 'saveSettings');
        return $this->ui_factory->input()->container()->form()->standard(
            $form_action,
            ['main_panel' => $section1, 'side_panel' => $section2]
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
        if ($this->canWrite()) {
            $form = $this->getViewForm(self::VIEW_MODE_SETTINGS);
            $form = $form->withRequest($this->request);
            $form_data = $form->getData();
            $this->viewSettings->enableSelectedItems(($form_data['main_panel'][self::DASH_ENABLE_PREFIX . 'favourites']));
            $this->viewSettings->enableMemberships(($form_data['main_panel'][self::DASH_ENABLE_PREFIX . 'memberships']));
            $this->viewSettings->enableRecommendedContent(($form_data['main_panel'][self::DASH_ENABLE_PREFIX . 'recommended_content']));
            $this->viewSettings->enableLearningSequences(($form_data['main_panel'][self::DASH_ENABLE_PREFIX . 'learning_sequences']));
            $this->viewSettings->enableStudyProgrammes(($form_data['main_panel'][self::DASH_ENABLE_PREFIX . 'study_programmes']));

            foreach ($this->side_panel_settings->getValidModules() as $mod) {
                $this->side_panel_settings->enable($mod, (bool) $form_data['side_panel'][self::DASH_ENABLE_PREFIX . $mod]);
            }

            $this->tpl->setOnScreenMessage(
                $this->tpl::MESSAGE_TYPE_SUCCESS,
                $this->lng->txt('settings_saved'),
                true
            );
        } else {
            $this->tpl->setOnScreenMessage(
                $this->tpl::MESSAGE_TYPE_FAILURE,
                $this->lng->txt('no_permission'),
                true
            );
        }

        $this->ctrl->redirect($this, 'editSettings');
    }


    public function setSettingsSubTabs(string $a_active): void
    {
        $tabs = $this->tabs_gui;
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        if ($this->rbac_system->checkAccess('visible,read', $this->object->getRefId())) {
            $tabs->addSubTab(
                'general',
                $lng->txt('general_settings'),
                $ctrl->getLinkTarget($this, 'editSettings')
            );

            $tabs->addSubTab(
                "presentation",
                $lng->txt('dash_presentation'),
                $ctrl->getLinkTarget($this, 'editPresentation')
            );

            $tabs->addSubTab(
                'sorting',
                $lng->txt('dash_sortation'),
                $ctrl->getLinkTarget($this, 'editSorting')
            );
        }

        $tabs->activateSubTab($a_active);
    }

    public function editPresentation(): void
    {
        $this->tabs_gui->activateTab('settings');
        $this->setSettingsSubTabs('presentation');

        $form = $this->getViewForm(self::VIEW_MODE_PRESENTATION);

        $this->tpl->setContent($this->ui->renderer()->renderAsync($form));
    }

    public function getViewSectionPresentation(int $view, string $title): Section
    {
        $lng = $this->lng;
        $ops = $this->viewSettings->getAvailablePresentationsByView($view);
        $pres_options = array_column(array_map(
            static fn(int $k, string $v): array => [$v, $lng->txt('dash_' . $v)],
            array_keys($ops),
            $ops
        ), 1, 0);
        $avail_pres = $this->ui_factory->input()->field()->multiSelect($lng->txt('dash_avail_presentation'), $pres_options)
                                 ->withValue($this->viewSettings->getActivePresentationsByView($view));
        $default_pres = $this->ui_factory->input()->field()->radio($lng->txt('dash_default_presentation'))
                                   ->withOption('list', $lng->txt('dash_list'))
                                   ->withOption('tile', $lng->txt('dash_tile'));
        $default_pres = $default_pres->withValue($this->viewSettings->getDefaultPresentationByView($view));
        return $this->ui_factory->input()->field()->section(
            $this->maybeDisable(['avail_pres' => $avail_pres, 'default_pres' => $default_pres]),
            $title
        );
    }

    protected function savePresentation(): void
    {
        $form = $this->getViewForm(self::VIEW_MODE_PRESENTATION);
        $form = $form->withRequest($this->request);
        $form_data = $form->getData();

        if (!$this->canWrite()) {
            $this->tpl->setOnScreenMessage(
                $this->tpl::MESSAGE_TYPE_FAILURE,
                $this->lng->txt('no_permission'),
                true
            );
            $this->editPresentation();
        }


        foreach ($form_data as $view => $view_data) {
            $this->viewSettings->storeViewPresentation(
                $view,
                $view_data['default_pres'],
                $view_data['avail_pres'] ?? []
            );
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
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

        return array_map(static fn(FormInput $field): FormInput => $field->withDisabled(true), $fields);
    }

    private function canWrite(): bool
    {
        return $this->rbac_system->checkAccess('write', $this->object->getRefId());
    }
}
