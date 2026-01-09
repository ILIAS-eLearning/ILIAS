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

use ILIAS\UI\Component\Input\Container\Form\Form;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

/**
 * @ilCtrl_Calls ilDclTableViewEditGUI: ilDclDetailedViewDefinitionGUI
 * @ilCtrl_Calls ilDclTableViewEditGUI: ilDclCreateViewDefinitionGUI
 * @ilCtrl_Calls ilDclTableViewEditGUI: ilDclEditViewDefinitionGUI
 */
class ilDclTableViewEditGUI
{
    protected ilDclTableViewGUI $parent_obj;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalPageTemplate $tpl;
    public ilDclTableView $tableview;
    protected ilPropertyFormGUI $form;
    protected ilDclTableViewEditFieldsTableGUI $table_gui;
    protected ilTabsGUI $tabs_gui;
    public ilDclTable $table;
    protected ilHelpGUI $help;
    protected ILIAS\HTTP\Services $http;
    protected ILIAS\Refinery\Factory $refinery;
    private Factory $ui_factory;
    private Renderer $ui_renderer;
    /**
     * @var int[]
     */
    private array $available_roles = [];

    public function __construct(ilDclTableViewGUI $parent_obj, ilDclTable $table, ilDclTableView $tableview)
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];
        $ilTabs = $DIC['ilTabs'];
        $locator = $DIC['ilLocator'];
        $this->table = $table;
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->parent_obj = $parent_obj;
        $this->tableview = $tableview;
        $this->tabs_gui = $ilTabs;
        $this->help = $DIC->help();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $ref_id = $this->table->getCollectionObject()->getRefId();
        foreach ($DIC->rbac()->review()->getParentRoleIds($ref_id) as $role) {
            if (
                $role['obj_id'] !== SYSTEM_ROLE_ID &&
                $DIC->rbac()->system()->checkPermission($ref_id, $role['obj_id'], 'visible')
            ) {
                $this->available_roles[$role['obj_id']] = ilObjRole::_getTranslation($role['title']);
            }
        }

        $this->ctrl->saveParameterByClass('ilDclTableEditGUI', 'table_id');
        $this->ctrl->saveParameter($this, 'tableview_id');
        if ($this->tableview->getTitle()) {
            $locator->addItem($this->tableview->getTitle(), $this->ctrl->getLinkTarget($this, 'show'));
        }
        $this->tpl->setLocator();
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd('show');
        $next_class = $this->ctrl->getNextClass($this);

        if (!$this->checkAccess($cmd)) {
            $this->permissionDenied();
        }

        $this->tabs_gui->clearTargets();
        $this->tabs_gui->clearSubTabs();
        $this->tabs_gui->setBackTarget(
            $this->lng->txt('dcl_tableviews'),
            $this->ctrl->getLinkTarget($this->parent_obj)
        );
        $this->tabs_gui->setBack2Target(
            $this->lng->txt('dcl_tables'),
            $this->ctrl->getLinkTarget($this->parent_obj->getParentObj())
        );

        switch ($next_class) {
            case 'ildcldetailedviewdefinitiongui':
                $this->help->setSubScreenId('detailed_view');
                $this->setTabs('detailed_view');
                $recordedit_gui = new ilDclDetailedViewDefinitionGUI($this->tableview->getId());
                $ret = $this->ctrl->forwardCommand($recordedit_gui);
                if ($ret != "") {
                    $this->tpl->setContent($ret);
                }
                break;
            case 'ildclcreateviewdefinitiongui':
                $this->help->setSubScreenId('record_create');
                $this->setTabs('create_view');
                $creation_gui = new ilDclCreateViewDefinitionGUI($this->tableview->getId());
                $this->ctrl->forwardCommand($creation_gui);
                break;
            case 'ildcleditviewdefinitiongui':
                $this->help->setSubScreenId('record_edit');
                $this->setTabs('edit_view');
                $edit_gui = new ilDclEditViewDefinitionGUI($this->tableview->getId());
                $this->ctrl->forwardCommand($edit_gui);
                break;
            default:
                switch ($cmd) {
                    case 'show':
                        if ($this->tableview->getId()) {
                            $this->ctrl->redirect($this, 'editGeneralSettings');
                        } else {
                            $this->ctrl->redirect($this, 'add');
                        }
                        break;
                    case 'add':
                        $this->help->setSubScreenId('create');
                        $this->tpl->setContent(
                            $this->lng->txt('dcl_new_view') . $this->ui_renderer->render($this->initForm(true))
                        );
                        break;
                    case 'editGeneralSettings':
                        $this->help->setSubScreenId('edit');
                        $this->setTabs('general_settings');
                        $this->tpl->setContent(
                            sprintf($this->lng->txt('dcl_edit_view'), $this->tableview->getTitle()) .
                            $this->ui_renderer->render($this->initForm())
                        );
                        break;
                    case 'editFieldSettings':
                        $this->help->setSubScreenId('overview');
                        $this->setTabs('field_settings');
                        $this->initTableGUI();
                        $this->tpl->setContent($this->table_gui->getHTML());
                        break;
                    default:
                        if ($cmd === 'create' || $cmd === 'update') {
                            $this->save($cmd === 'create');
                        } else {
                            $this->$cmd();
                        }
                        break;
                }
                break;
        }
    }

    protected function initForm(bool $create = false): Form
    {
        $settings['title'] = $this->ui_factory->input()->field()->text($this->lng->txt('title'))->withRequired(true);
        $settings['description'] = $this->ui_factory->input()->field()->textarea($this->lng->txt('description'));
        $roles = [];
        $settings['role_limitation'] = $this->ui_factory->input()->field()->optionalGroup(
            [
                'roles' => $this->ui_factory->input()->field()->multiSelect(
                    $this->lng->txt('roles'),
                    $this->available_roles,
                    $this->lng->txt('roles_desc')
                )
            ],
            $this->lng->txt('role_limitation')
        )->withValue(null);

        $inputs['settings'] = $this->ui_factory->input()->field()->section($settings, $this->lng->txt('general_settings'));

        if (!$create) {
            $inputs = $this->setValues($inputs);
        }

        return $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getFormAction($this, $create ? 'create' : 'update'),
            $inputs
        );
    }

    protected function setValues(array $inputs): array
    {
        $roles = [];
        foreach ($this->tableview->getRoles() as $role) {
            $role = (int) $role;
            if (in_array($role, array_keys($this->available_roles), true)) {
                $roles[] = $role;
            }
        }
        $inputs['settings'] = $inputs['settings']->withValue([
            'title' => $this->tableview->getTitle(),
            'description' => $this->tableview->getDescription(),
            'role_limitation' => $this->tableview->getRoleLimitation() ? ['roles' => $roles] : null
        ]);

        return $inputs;
    }

    protected function setTabs(string $active): void
    {
        $this->tabs_gui->addTab(
            'general_settings',
            $this->lng->txt('settings'),
            $this->ctrl->getLinkTarget($this, 'editGeneralSettings')
        );
        $this->tabs_gui->addTab(
            'create_view',
            $this->lng->txt('dcl_create_entry_rules'),
            $this->ctrl->getLinkTargetByClass('ilDclCreateViewDefinitionGUI', 'presentation')
        );
        $this->tabs_gui->addTab(
            'edit_view',
            $this->lng->txt('dcl_edit_entry_rules'),
            $this->ctrl->getLinkTargetByClass('ilDclEditViewDefinitionGUI', 'presentation')
        );
        $this->tabs_gui->addTab(
            'field_settings',
            $this->lng->txt('dcl_list_visibility_and_filter'),
            $this->ctrl->getLinkTarget($this, 'editFieldSettings')
        );
        $this->tabs_gui->addTab(
            'detailed_view',
            $this->lng->txt('dcl_detailed_view'),
            $this->ctrl->getLinkTargetByClass('ilDclDetailedViewDefinitionGUI', 'edit')
        );
        $this->tabs_gui->setTabActive($active);
    }

    public function save(bool $create = false): void
    {
        $form = $this->initForm($create)->withRequest($this->http->request());
        $data = $form->getData();
        if ($data !== null) {
            $this->tableview->setTitle($data['settings']['title']);
            $this->tableview->setDescription($data['settings']['description']);
            $this->tableview->setRoleLimitation($data['settings']['role_limitation'] !== null);
            $this->tableview->setRoles($data['settings']['role_limitation']['roles'] ?? []);
            if ($create) {
                $this->tableview->setTableId($this->table->getId());
                $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_SUCCESS, $this->lng->txt('dcl_msg_tableview_created'), true);
                $this->tableview->create();
                $this->ctrl->setParameter($this, 'tableview_id', $this->tableview->getId());
            } else {
                $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_SUCCESS, $this->lng->txt('dcl_msg_tableview_updated'), true);
                $this->tableview->update();
            }
            $this->ctrl->redirect($this, 'editGeneralSettings');
        }

        if ($create) {
            $this->help->setSubScreenId('create');
            $this->tpl->setContent($this->lng->txt('dcl_new_view') . $this->ui_renderer->render($form));
        } else {
            $this->help->setSubScreenId('edit');
            $this->setTabs('general_settings');
            $this->tpl->setContent(
                sprintf($this->lng->txt('dcl_edit_view'), $this->tableview->getTitle()) .
                $this->ui_renderer->render($form)
            );
        }
    }

    public function saveOverviewSettings(): void
    {
        /**
         * @var ilDclTableViewFieldSetting $setting
         */
        foreach ($this->tableview->getFieldSettings() as $setting) {
            //Checkboxes
            foreach (["Visible", "InFilter", "FilterChangeable"] as $attribute) {
                $key = $attribute . '_' . $setting->getField();
                if ($this->http->wrapper()->post()->has($key)) {
                    $checkbox_value = $this->http->wrapper()->post()->retrieve(
                        $key,
                        $this->refinery->kindlyTo()->string()
                    );
                    $setting->{'set' . $attribute}($checkbox_value === 'on');
                } else {
                    $setting->{'set' . $attribute}(false);
                }
            }

            //Filter Value
            $key = 'filter_' . $setting->getField();
            if ($this->http->wrapper()->post()->has($key)) {
                $setting->setFilterValue($this->http->wrapper()->post()->retrieve(
                    $key,
                    $this->refinery->kindlyTo()->string()
                ));
            } elseif ($this->http->wrapper()->post()->has($key . '_from') && $this->http->wrapper()->post()->has($key . '_to')) {
                $setting->setFilterValue(["from" => $this->http->wrapper()->post()->retrieve(
                    $key . '_from',
                    $this->refinery->kindlyTo()->string()
                ),
                                          "to" => $this->http->wrapper()->post()->retrieve(
                                              $key . '_to',
                                              $this->refinery->kindlyTo()->string()
                                          )
                ]);
            } else {
                $setting->setFilterValue(null);
            }

            $setting->update();
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('dcl_msg_tableview_updated'), true);
        $this->ctrl->saveParameter($this->parent_obj, 'tableview_id');
        $this->ctrl->redirect($this, 'editFieldSettings');
    }

    protected function initTableGUI(): void
    {
        $table = new ilDclTableViewEditFieldsTableGUI($this);
        $this->table_gui = $table;
    }

    protected function cancel(): void
    {
        $this->ctrl->setParameter($this->parent_obj, 'table_id', $this->table->getId());
        $this->ctrl->redirect($this->parent_obj);
    }

    public function confirmDelete(): void
    {
        //at least one view must exist
        $this->parent_obj->checkViewsLeft(1);

        $conf = new ilConfirmationGUI();
        $conf->setFormAction($this->ctrl->getFormAction($this));
        $conf->setHeaderText($this->lng->txt('dcl_tableview_confirm_delete'));

        $conf->addItem('tableview_id', (string) $this->tableview->getId(), $this->tableview->getTitle());

        $conf->setConfirm($this->lng->txt('delete'), 'delete');
        $conf->setCancel($this->lng->txt('cancel'), 'cancel');

        $this->tpl->setContent($conf->getHTML());
    }

    protected function delete(): void
    {
        $this->tableview->delete();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('dcl_msg_tableview_deleted'), true);
        $this->cancel();
    }

    public function permissionDenied(): void
    {
        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
        $this->ctrl->redirectByClass(
            [ilObjDataCollectionGUI::class, ilDclRecordListGUI::class],
            ilDclRecordListGUI::CMD_LIST_RECORDS
        );
    }

    protected function checkAccess(string $cmd): bool
    {
        if (in_array($cmd, ['add', 'create', 'cancel'])) {
            return ilObjDataCollectionAccess::hasAccessToEditTable(
                $this->parent_obj->getParentObj()->getDataCollectionObject()->getRefId(),
                $this->table->getId()
            );
        } else {
            return ilObjDataCollectionAccess::hasAccessTo(
                $this->parent_obj->getParentObj()->getDataCollectionObject()->getRefId(),
                $this->table->getId(),
                $this->tableview->getId()
            );
        }
    }

    public function copy(): void
    {
        $new_tableview = new ilDclTableView();
        $new_tableview->setTableId($this->table->getId());
        $new_tableview->cloneStructure($this->tableview, []);
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("dcl_tableview_copy"), true);
        $this->cancel();
    }
}
