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

/**
 * Dashboard settings
 *
 * @author Alex Killing <killing@leifos.de>
 *
 * @ilCtrl_Calls ilObjDashboardSettingsGUI: ilPermissionGUI
 * @ilCtrl_isCalledBy ilObjDashboardSettingsGUI: ilAdministrationGUI
 */
class ilObjDashboardSettingsGUI extends ilObjectGUI
{
    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var ilErrorHandling
     */
    protected $error;

    /**
     * @var \ILIAS\UI\Factory
     */
    protected $ui_factory;

    /**
     * @var \ILIAS\UI\Renderer
     */
    protected $ui_renderer;

    /**
     * @var ilPDSelectedItemsBlockViewSettings
     */
    protected $viewSettings;

    /**
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $request;

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @var \ilDashboardSidePanelSettingsRepository
     */
    protected $side_panel_settings;

    /**
     * Contructor
     *
     * @access public
     */
    public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->error = $DIC["ilErr"];
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

    /**
     * Execute command
     *
     * @access public
     *
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        if (!$this->rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $perm_gui = new ilPermissionGUI($this);
                $ret = $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                if (!$cmd || $cmd == 'view') {
                    $cmd = "editSettings";
                }

                $this->$cmd();
                break;
        }
        return true;
    }

    /**
     * Get tabs
     *
     * @access public
     *
     */
    public function getAdminTabs()
    {
        $rbacsystem = $this->rbacsystem;
        $ilAccess = $this->access;

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

    /**
    * Edit personal desktop settings.
    */
    public function editSettings()
    {
        if ($this->settings->get('rep_favourites', '0') !== '1') {
            $content[] = $this->ui->factory()->messageBox()->info($this->lng->txt('favourites_disabled_info'));
        }

        if ($this->settings->get('mmbr_my_crs_grp', '0') !== '1') {
            $content[] = $this->ui->factory()->messageBox()->info($this->lng->txt('memberships_disabled_info'));
        }
        $this->setSettingsSubTabs('general');
        $content[] = $this->initForm();
        $this->tpl->setContent($this->ui->renderer()->renderAsync($content));
    }

    /**
     * Init  form.
     * @return \ILIAS\UI\Component\Input\Container\Form\Standard
     */
    public function initForm()
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
            ["main_panel" => $section1, "side_panel" => $section2]
        );
    }

    /**
    * Save personal desktop settings
    */
    public function saveSettings()
    {
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;
        $side_panel = $this->side_panel_settings;
        
        if (!$this->canWrite()) {
            ilUtil::sendFailure($this->lng->txt('no_permission'), true);
            $ilCtrl->redirect($this, "editSettings");
        }

        $request = $this->request;

        $form = $this->initForm();
        $form = $form->withRequest($request);
        $form_data = $form->getData();
        $this->viewSettings->enableSelectedItems($form_data['main_panel']['enable_favourites']);
        $this->viewSettings->enableMemberships($form_data['main_panel']['enable_memberships']);

        foreach ($side_panel->getValidModules() as $mod) {
            $side_panel->enable($mod, (bool) $form_data['side_panel']['enable_' . $mod]);
        }


        ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
        $ilCtrl->redirect($this, "editSettings");
    }
    

    /**
     * Get tabs
     *
     * @access public
     *
     */
    public function setSettingsSubTabs($a_active)
    {
        $rbacsystem = $this->rbacsystem;
        $ilAccess = $this->access;

        $tabs = $this->tabs_gui;
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        if ($rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $tabs->addSubtab(
                "general",
                $lng->txt("general_settings"),
                $ctrl->getLinkTarget($this, "editSettings")
            );

            if ($this->viewSettings->enabledSelectedItems()) {
                $tabs->addSubtab(
                    "view_favourites",
                    $lng->txt("dash_view_favourites"),
                    $ctrl->getLinkTarget($this, "editViewFavourites")
                );
            }

            if ($this->viewSettings->enabledMemberships()) {
                $tabs->addSubtab(
                    "view_courses_groups",
                    $lng->txt("dash_view_courses_groups"),
                    $ctrl->getLinkTarget($this, "editViewCoursesGroups")
                );
            }
        }

        $tabs->activateSubtab($a_active);
    }

    /**
     * Edit settings of courses and groups overview
     */
    protected function editViewCoursesGroups()
    {
        if ($this->settings->get('mmbr_my_crs_grp', '0') !== '1') {
            $content[] = $this->ui->factory()->messageBox()->info($this->lng->txt('memberships_disabled_info'));
        }
        $this->tabs_gui->activateTab("settings");
        $this->setSettingsSubTabs("view_courses_groups");

        $content[] = $this->getViewSettingsForm($this->viewSettings->getMembershipsView());
        $this->tpl->setContent($this->ui_renderer->render($content));
    }

    /**
     * Get view courses and groups settings form
     *
     * @return \ILIAS\UI\Component\Input\Container\Form\Standard
     */
    protected function getViewSettingsForm(int $view)
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;
        $ui_factory = $this->ui_factory;

        if ($view == $this->viewSettings->getSelectedItemsView()) {
            $save_cmd = "saveViewFavourites";
        } else {
            $save_cmd = "saveViewCoursesGroups";
        }

        // presentation
        $ops = $this->viewSettings->getAvailablePresentationsByView($view);
        $pres_options = array_column(array_map(function ($k, $v) use ($lng) {
            return [$v, $lng->txt("dash_" . $v)];
        }, array_keys($ops), $ops), 1, 0);
        $avail_pres = $ui_factory->input()->field()->multiselect($lng->txt("dash_avail_presentation"), $pres_options)
            ->withValue($this->viewSettings->getActivePresentationsByView($view));
        $default_pres = $ui_factory->input()->field()->radio($lng->txt("dash_default_presentation"))
            ->withOption('list', $lng->txt("dash_list"))
            ->withOption('tile', $lng->txt("dash_tile"));
        $default_pres = $default_pres->withValue((string) $this->viewSettings->getDefaultPresentationByView($view));
        $sec_presentation = $ui_factory->input()->field()->section(
            $this->maybeDisable(["avail_pres" => $avail_pres, "default_pres" => $default_pres]),
            $lng->txt("dash_presentation")
        );

        // sortation
        $ops = $this->viewSettings->getAvailableSortOptionsByView($view);
        $sortation_options = array_column(array_map(function ($k, $v) use ($lng) {
            return [$v, $lng->txt("dash_sort_by_" . $v)];
        }, array_keys($ops), $ops), 1, 0);
        $avail_sort = $ui_factory->input()->field()->multiselect($lng->txt("dash_avail_sortation"), $sortation_options)
            ->withValue($this->viewSettings->getActiveSortingsByView($view));
        $default_sort = $ui_factory->input()->field()->radio($lng->txt("dash_default_sortation"));
        foreach ($sortation_options as $k => $text) {
            $default_sort = $default_sort->withOption($k, $text);
        }
        $default_sort = $default_sort->withValue((string) $this->viewSettings->getDefaultSortingByView($view));
        $sec_sortation = $ui_factory->input()->field()->section(
            $this->maybeDisable(["avail_sort" => $avail_sort, "default_sort" => $default_sort]),
            $lng->txt("dash_sortation")
        );

        $form = $ui_factory->input()->container()->form()->standard(
            $ctrl->getFormAction($this, $save_cmd),
            ["presentation" => $sec_presentation, "sortation" => $sec_sortation]
        );

        return $form;
    }


    /**
     * Save settings of courses and groups overview
     */
    protected function saveViewCoursesGroups()
    {
        $this->saveViewSettings(
            $this->viewSettings->getMembershipsView(),
            "editViewCoursesGroups"
        );
    }

    /**
     * Edit favourites view
     */
    protected function editViewFavourites()
    {
        if ($this->settings->get('rep_favourites', "0") !== '1') {
            $content[] = $this->ui->factory()->messageBox()->info($this->lng->txt('favourites_disabled_info'));
        }
        $this->tabs_gui->activateTab("settings");
        $this->setSettingsSubTabs("view_favourites");

        $content[] = $this->getViewSettingsForm($this->viewSettings->getSelectedItemsView());
        $this->tpl->setContent($this->ui_renderer->render($content));
    }

    /**
     * Save settings of favourites overview
     */
    protected function saveViewFavourites()
    {
        $this->saveViewSettings(
            $this->viewSettings->getSelectedItemsView(),
            "editViewFavourites"
        );
    }

    /**
     * Save settings of favourites overview
     */
    protected function saveViewSettings(int $view, string $redirect_cmd)
    {
        $request = $this->request;
        $lng = $this->lng;
        $ctrl = $this->ctrl;

        if (!$this->canWrite()) {
            ilUtil::sendFailure($this->lng->txt('no_permission'), true);
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

        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        $ctrl->redirect($this, $redirect_cmd);
    }

    /**
     * @param FormInput[] $fields
     * @return FormInput[]
     */
    private function maybeDisable(array $fields) : array
    {
        if ($this->canWrite()) {
            return $fields;
        }

        return array_map(static function (FormInput $field) : FormInput {
            return $field->withDisabled(true);
        }, $fields);
    }

    private function canWrite() : bool
    {
        return $this->rbacsystem->checkAccess('write', $this->object->getRefId());
    }
}
