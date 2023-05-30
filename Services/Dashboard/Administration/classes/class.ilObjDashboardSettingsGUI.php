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

use ILIAS\DI\Container;
use ILIAS\DI\UIServices;
use ILIAS\UI\Component\Input\Container\Form\Standard;
use ILIAS\UI\Component\Input\Field\FormInput;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

/**
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilObjDashboardSettingsGUI: ilPermissionGUI
 * @ilCtrl_isCalledBy ilObjDashboardSettingsGUI: ilAdministrationGUI
 */
class ilObjDashboardSettingsGUI extends ilObjectGUI
{
    protected readonly Factory $ui_factory;
    protected readonly Renderer $ui_renderer;
    protected readonly ilPDSelectedItemsBlockViewSettings $viewSettings;
    protected readonly UIServices $ui;
    protected readonly ilDashboardSidePanelSettingsRepository $side_panel_settings;
    protected readonly ilRbacSystem $rbacsystem;

    /**
     * @param mixed $data
     */
    public function __construct(
        $data,
        int $id,
        bool $call_by_reference = true,
        bool $prepare_output = true
    ) {
        /** @var Container $DIC */
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

        $this->type = ilObjDashboardSettings::TYPE;
        parent::__construct($data, $id, $call_by_reference, $prepare_output);

        $lng->loadLanguageModule('dash');

        $this->viewSettings = new ilPDSelectedItemsBlockViewSettings($DIC->user());

        $this->side_panel_settings = new ilDashboardSidePanelSettingsRepository();
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        if (!$this->rbacsystem->checkAccess('visible,read', $this->object->getRefId())) {
            throw new ilPermissionException($this->lng->txt('no_permission'));
        }

        switch ($next_class) {
            case strtolower(ilPermissionGUI::class):
                $this->tabs_gui->setTabActive('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;
            default:
                if (!$cmd) {
                    $this->view();
                }
                $this->$cmd();
                break;
        }
    }

    public function getAdminTabs(): void
    {
        if ($this->rbacsystem->checkAccess('visible,read', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'settings',
                $this->ctrl->getLinkTarget($this, 'view'),
                'view'
            );
        }

        if ($this->rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'perm_settings',
                $this->ctrl->getLinkTargetByClass(ilPermissionGUI::class, 'perm'),
                [],
                ilPermissionGUI::class
            );
        }
    }

    public function view(): void
    {
        $this->setSettingsSubTabs('general');
        $this->tpl->setContent($this->ui->renderer()->renderAsync($this->initForm()));
    }

    /**
     * @deprecated use view() instead
     */
    final public function editSettings(): void
    {
        $this->view();
    }

    public function initForm(): Standard
    {
        $fields['enable_favourites'] = $this->ui->factory()->input()->field()
            ->checkbox($this->lng->txt('dash_enable_favourites'))
            ->withValue($this->viewSettings->enabledSelectedItems());

        $info_text = ($this->viewSettings->enabledMemberships())
            ? ''
            : $this->lng->txt('dash_member_main_alt') . ' ' . $this->ui->renderer()->render(
                $this->ui->factory()->link()->standard(
                    $this->lng->txt('dash_click_here'),
                    $this->ctrl->getLinkTargetByClass([ilAdministrationGUI::class, ilObjMainMenuGUI::class, ilMMSubItemGUI::class])
                )
            );

        $fields['enable_memberships'] = $this->ui->factory()->input()->field()
            ->checkbox($this->lng->txt('dash_enable_memberships'), $info_text)
            ->withValue($this->viewSettings->enabledMemberships());

        $section1 = $this->ui->factory()->input()->field()->section(
            $this->maybeDisable($fields),
            $this->lng->txt('dash_main_panel')
        );

        $sp_fields = [];
        foreach ($this->side_panel_settings->getValidModules() as $mod) {
            $sp_fields['enable_' . $mod] = $this->ui->factory()->input()->field()
                ->checkbox($this->lng->txt('dash_enable_' . $mod))
                ->withValue($this->side_panel_settings->isEnabled($mod));
        }

        $section2 = $this->ui->factory()->input()->field()->section(
            $this->maybeDisable($sp_fields),
            $this->lng->txt('dash_side_panel')
        );

        $form_action = $this->ctrl->getLinkTarget($this, 'saveSettings');
        return $this->ui->factory()->input()->container()->form()->standard(
            $form_action,
            ['main_panel' => $section1, 'side_panel' => $section2]
        );
    }

    public function saveSettings(): void
    {
        if (!$this->canWrite()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_permission'), true);
            $this->ctrl->redirect($this, 'view');
        }

        $form = $this->initForm();
        $form = $form->withRequest($this->request);
        $form_data = $form->getData();
        $this->viewSettings->enableSelectedItems(($form_data['main_panel']['enable_favourites'] ?? '') !== '');
        $this->viewSettings->enableMemberships(($form_data['main_panel']['enable_memberships'] ?? '') !== '');

        foreach ($this->side_panel_settings->getValidModules() as $mod) {
            $this->side_panel_settings->enable($mod, (bool) $form_data['side_panel']['enable_' . $mod]);
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'view');
    }

    public function setSettingsSubTabs(string $active): void
    {
        if ($this->rbacsystem->checkAccess('visible,read', $this->object->getRefId())) {
            $this->tabs_gui->addSubTab(
                'general',
                $this->lng->txt('general_settings'),
                $this->ctrl->getLinkTarget($this, 'view')
            );

            if ($this->viewSettings->enabledSelectedItems()) {
                $this->tabs_gui->addSubTab(
                    'view_favourites',
                    $this->lng->txt('dash_view_favourites'),
                    $this->ctrl->getLinkTarget($this, 'editViewFavourites')
                );
            }

            if ($this->viewSettings->enabledMemberships()) {
                $this->tabs_gui->addSubTab(
                    'view_courses_groups',
                    $this->lng->txt('dash_view_courses_groups'),
                    $this->ctrl->getLinkTarget($this, 'editViewCoursesGroups')
                );
            }
        }

        $this->tabs_gui->activateSubTab($active);
    }

    protected function editViewCoursesGroups(): void
    {
        $this->tabs_gui->activateTab('settings');
        $this->setSettingsSubTabs('view_courses_groups');

        $form = $this->getViewSettingsForm($this->viewSettings->getMembershipsView());

        $this->tpl->setContent($this->ui_renderer->render($form));
    }

    protected function getViewSettingsForm(int $view): Standard
    {
        $ui_factory = $this->ui_factory;

        if ($view === $this->viewSettings->getSelectedItemsView()) {
            $save_cmd = 'saveViewFavourites';
        } else {
            $save_cmd = 'saveViewCoursesGroups';
        }

        $ops = $this->viewSettings->getAvailablePresentationsByView($view);
        $lng = $this->lng;
        $pres_options = array_column(array_map(
            static function ($k, $v) use ($lng) {
                return [$v, $lng->txt('dash_' . $v)];
            },
            array_keys($ops),
            $ops
        ), 1, 0);
        $avail_pres = $ui_factory->input()->field()->multiSelect($lng->txt('dash_avail_presentation'), $pres_options)
            ->withValue($this->viewSettings->getActivePresentationsByView($view));
        $default_pres = $ui_factory->input()->field()->radio($lng->txt('dash_default_presentation'))
            ->withOption('list', $lng->txt('dash_list'))
            ->withOption('tile', $lng->txt('dash_tile'));
        $default_pres = $default_pres->withValue($this->viewSettings->getDefaultPresentationByView($view));
        $sec_presentation = $ui_factory->input()->field()->section(
            $this->maybeDisable(['avail_pres' => $avail_pres, 'default_pres' => $default_pres]),
            $lng->txt('dash_presentation')
        );

        $ops = $this->viewSettings->getAvailableSortOptionsByView($view);
        $sortation_options = array_column(array_map(
            static function ($k, $v) use ($lng) {
                return [$v, $lng->txt('dash_sort_by_' . $v)];
            },
            array_keys($ops),
            $ops
        ), 1, 0);
        $avail_sort = $ui_factory->input()->field()->multiSelect($this->lng->txt('dash_avail_sortation'), $sortation_options)
            ->withValue($this->viewSettings->getActiveSortingsByView($view));
        $default_sort = $ui_factory->input()->field()->radio($this->lng->txt('dash_default_sortation'));
        foreach ($sortation_options as $k => $text) {
            $default_sort = $default_sort->withOption($k, $text);
        }
        $default_sort = $default_sort->withValue($this->viewSettings->getDefaultSortingByView($view));
        $sec_sortation = $ui_factory->input()->field()->section(
            $this->maybeDisable(['avail_sort' => $avail_sort, 'default_sort' => $default_sort]),
            $this->lng->txt('dash_sortation')
        );

        $form = $ui_factory->input()->container()->form()->standard(
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
        $this->tabs_gui->activateTab('settings');
        $this->setSettingsSubTabs('view_favourites');
        $this->tpl->setContent(
            $this->ui_renderer->render($this->getViewSettingsForm($this->viewSettings->getSelectedItemsView()))
        );
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
        if (!$this->canWrite()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_permission'), true);
            $this->ctrl->redirect($this, $redirect_cmd);
        }

        $form = $this->getViewSettingsForm($view)->withRequest($this->request);
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

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
        $this->ctrl->redirect($this, $redirect_cmd);
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

        return array_map(
            static function (FormInput $field): FormInput {
                return $field->withDisabled(true);
            },
            $fields
        );
    }

    private function canWrite(): bool
    {
        return $this->rbacsystem->checkAccess('write', $this->object->getRefId());
    }
}
