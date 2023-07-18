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
use ILIAS\UI\Component\Input\Container\Form\Standard;
use ILIAS\UI\Component\Input\Field\FormInput;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

/**
 * @ilCtrl_Calls ilObjDashboardSettingsGUI: ilPermissionGUI
 * @ilCtrl_isCalledBy ilObjDashboardSettingsGUI: ilAdministrationGUI
 */
class ilObjDashboardSettingsGUI extends ilObjectGUI
{
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
        $content[] = $this->initForm();
        $this->tpl->setContent($this->ui->renderer()->renderAsync($content));
    }

    public function initForm(): Standard
    {
        $lng = $this->lng;

        $fields['enable_favourites'] = $this->ui_factory->input()->field()->checkbox($lng->txt('dash_enable_favourites'))
            ->withValue($this->viewSettings->enabledSelectedItems());
        $info_text = ($this->viewSettings->enabledMemberships())
            ? ''
            : $lng->txt('dash_member_main_alt') . ' ' . $this->ui->renderer()->render(
                $this->ui_factory->link()->standard(
                    $lng->txt('dash_click_here'),
                    $this->ctrl->getLinkTargetByClass(['ilAdministrationGUI', 'ilObjMainMenuGUI', 'ilmmsubitemgui'])
                )
            );

        $fields['enable_memberships'] = $this->ui_factory->input()->field()->checkbox($lng->txt('dash_enable_memberships'), $info_text)
            ->withValue($this->viewSettings->enabledMemberships());

        $section1 = $this->ui_factory->input()->field()->section($this->maybeDisable($fields), $lng->txt('dash_main_panel'));

        $sp_fields = [];
        foreach ($this->side_panel_settings->getValidModules() as $mod) {
            $sp_fields['enable_' . $mod] = $this->ui_factory->input()->field()->checkbox($lng->txt('dash_enable_' . $mod))
                ->withValue($this->side_panel_settings->isEnabled($mod));
        }

        $section2 = $this->ui_factory->input()->field()->section($this->maybeDisable($sp_fields), $lng->txt('dash_side_panel'));

        $form_action = $this->ctrl->getLinkTarget($this, 'saveSettings');
        return $this->ui_factory->input()->container()->form()->standard(
            $form_action,
            ['main_panel' => $section1, 'side_panel' => $section2]
        );
    }

    public function saveSettings(): void
    {
        if ($this->canWrite()) {
            $form = $this->initForm();
            $form = $form->withRequest($this->request);
            $form_data = $form->getData();
            $this->viewSettings->enableSelectedItems($form_data['main_panel']['enable_favourites']);
            $this->viewSettings->enableMemberships($form_data['main_panel']['enable_memberships']);

            foreach ($this->side_panel_settings->getValidModules() as $mod) {
                $this->side_panel_settings->enable($mod, (bool) $form_data['side_panel']['enable_' . $mod]);
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

            if ($this->viewSettings->enabledSelectedItems()) {
                $tabs->addSubTab(
                    'view_favourites',
                    $lng->txt('dash_view_favourites'),
                    $ctrl->getLinkTarget($this, 'editViewFavourites')
                );
            }

            if ($this->viewSettings->enabledMemberships()) {
                $tabs->addSubTab(
                    'view_courses_groups',
                    $lng->txt('dash_view_courses_groups'),
                    $ctrl->getLinkTarget($this, 'editViewCoursesGroups')
                );
            }
        }

        $tabs->activateSubTab($a_active);
    }

    protected function editViewCoursesGroups(): void
    {
        if ($this->settings->get('mmbr_my_crs_grp', '0') !== '1') {
            $content[] = $this->ui_factory->messageBox()->info($this->lng->txt('memberships_disabled_info'));
        }
        $this->tabs_gui->activateTab('settings');
        $this->setSettingsSubTabs('view_courses_groups');

        $content[] = $this->getViewSettingsForm($this->viewSettings->getMembershipsView());
        $this->tpl->setContent($this->ui_renderer->render($content));
    }

    protected function getViewSettingsForm(int $view): Standard
    {
        $lng = $this->lng;
        $ui_factory = $this->ui_factory;

        if ($view === $this->viewSettings->getSelectedItemsView()) {
            $save_cmd = 'saveViewFavourites';
        } else {
            $save_cmd = 'saveViewCoursesGroups';
        }

        $ops = $this->viewSettings->getAvailablePresentationsByView($view);
        $pres_options = array_column(array_map(static fn ($v) => [$v, $lng->txt('dash_' . $v)], $ops), 1, 0);
        $avail_pres = $this->ui_factory->input()->field()->multiSelect($lng->txt('dash_avail_presentation'), $pres_options)
            ->withValue($this->viewSettings->getActivePresentationsByView($view));
        $default_pres = $this->ui_factory->input()->field()->radio($lng->txt('dash_default_presentation'))
            ->withOption('list', $lng->txt('dash_list'))
            ->withOption('tile', $lng->txt('dash_tile'));
        $default_pres = $default_pres->withValue($this->viewSettings->getDefaultPresentationByView($view));
        $sec_presentation = $this->ui_factory->input()->field()->section(
            $this->maybeDisable(['avail_pres' => $avail_pres, 'default_pres' => $default_pres]),
            $lng->txt('dash_presentation')
        );

        $ops = $this->viewSettings->getAvailableSortOptionsByView($view);
        $sortation_options = array_column(array_map(static fn ($v) => [$v, $lng->txt('dash_sort_by_' . $v)], $ops), 1, 0);
        $avail_sort = $this->ui_factory->input()->field()->multiSelect($lng->txt('dash_avail_sortation'), $sortation_options)
            ->withValue($this->viewSettings->getActiveSortingsByView($view));
        $default_sort = $this->ui_factory->input()->field()->radio($lng->txt('dash_default_sortation'));
        foreach ($sortation_options as $k => $text) {
            $default_sort = $default_sort->withOption($k, $text);
        }
        $default_sort = $default_sort->withValue($this->viewSettings->getDefaultSortingByView($view));
        $sec_sortation = $this->ui_factory->input()->field()->section(
            $this->maybeDisable(['avail_sort' => $avail_sort, 'default_sort' => $default_sort]),
            $lng->txt('dash_sortation')
        );

        $form = $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getFormAction($this, $save_cmd),
            ['presentation' => $sec_presentation, 'sortation' => $sec_sortation]
        );

        return $form;
    }


    protected function saveViewCoursesGroups(): void
    {
        $this->saveViewSettings(
            $this->viewSettings->getMembershipsView(),
            'editViewCoursesGroups'
        );
    }

    protected function editViewFavourites(): void
    {
        if ($this->settings->get('rep_favourites', '0') !== '1') {
            $content[] = $this->ui_factory->messageBox()->info($this->lng->txt('favourites_disabled_info'));
        }
        $this->tabs_gui->activateTab('settings');
        $this->setSettingsSubTabs('view_favourites');

        $content[] = $this->getViewSettingsForm($this->viewSettings->getSelectedItemsView());
        $this->tpl->setContent($this->ui_renderer->render($content));
    }

    protected function saveViewFavourites(): void
    {
        $this->saveViewSettings(
            $this->viewSettings->getSelectedItemsView(),
            'editViewFavourites'
        );
    }

    protected function saveViewSettings(int $view, string $redirect_cmd): void
    {
        $request = $this->request;
        $lng = $this->lng;
        $ctrl = $this->ctrl;

        if (!$this->canWrite()) {
            $this->tpl->setOnScreenMessage(
                $this->tpl::MESSAGE_TYPE_FAILURE,
                $this->lng->txt('no_permission'),
                true
            );
            $ctrl->redirect($this, $redirect_cmd);
        }

        $form = $this->getViewSettingsForm($view);
        $form = $form->withRequest($request);
        $form_data = $form->getData();
        $this->viewSettings->storeViewSorting(
            $view,
            $form_data['sortation']['default_sort'],
            $form_data['sortation']['avail_sort'] ?: []
        );
        $this->viewSettings->storeViewPresentation(
            $view,
            $form_data['presentation']['default_pres'],
            $form_data['presentation']['avail_pres'] ?: []
        );

        $this->tpl->setOnScreenMessage('success', $lng->txt('msg_obj_modified'), true);
        $ctrl->redirect($this, $redirect_cmd);
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

        return array_map(static fn (FormInput $field): FormInput => $field->withDisabled(true), $fields);
    }

    private function canWrite(): bool
    {
        return $this->rbac_system->checkAccess('write', $this->object->getRefId());
    }
}
