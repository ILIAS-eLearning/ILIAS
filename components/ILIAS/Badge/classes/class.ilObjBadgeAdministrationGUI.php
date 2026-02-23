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

use ILIAS\Badge\ilBadgeImageTemplateTableGUI;
use ILIAS\Badge\ilBadgeTypesTableGUI;
use ILIAS\Badge\ilObjectBadgeTableGUI;
use ILIAS\Badge\ilBadgeUserTableGUI;

/**
 * @ilCtrl_Calls ilObjBadgeAdministrationGUI: ilPermissionGUI, ilBadgeManagementGUI
 * @ilCtrl_IsCalledBy ilObjBadgeAdministrationGUI: ilAdministrationGUI
 */
class ilObjBadgeAdministrationGUI extends ilObjectGUI implements ilCtrlSecurityInterface
{
    private const string LIST_TYPES_ACTION = 'listTypes';
    private const string LIST_IMG_TPL_ACTION = 'listImageTemplates';
    private const string LIST_OBJ_BADGES_ACTION = 'listObjectBadges';
    private const string EDIT_SETTINGS_ACTION = 'editSettings';
    private const string SAVE_SETTINGS_ACTION = 'saveSettings';
    private const string SAVE_IMG_TPL_ACTION = 'saveImageTemplate';
    private const string UPDATE_IMG_TPL_ACTION = 'updateImageTemplate';
    private const string ADD_IMG_TPL_ACTION = 'addImageTemplate';
    private const string DELETE_IMG_TPL_ACTION = 'deleteImageTemplates';
    private const string DELETE_OBJ_BADGES_ACTION = 'deleteObjectBadges';
    private const string DEFAULT_ACTION = self::EDIT_SETTINGS_ACTION;
    private const string TABLE_ACTIONS = 'handleTableActions';
    public const string TABLE_ALL_OBJECTS_ACTION = 'ALL_OBJECTS';

    private ilRbacSystem $rbacsystem;
    private ilBadgeGUIRequest $badge_request;
    private ilTabsGUI $tabs;

    public function __construct(
        $a_data,
        int $a_id,
        bool $a_call_by_reference = true,
        bool $a_prepare_output = true
    ) {
        global $DIC;

        $this->rbacsystem = $DIC->rbac()->system();
        $this->ctrl = $DIC->ctrl();
        $this->access = $DIC->access();
        $this->lng = $DIC->language();
        $this->toolbar = $DIC->toolbar();
        $this->tpl = $DIC['tpl'];
        $this->tabs = $DIC->tabs();
        $this->type = 'bdga';
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->badge_request = new ilBadgeGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );

        $this->lng->loadLanguageModule('badge');
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this) ?? '';
        $cmd = $this->ctrl->getCmd() ?? '';

        $this->prepareOutput();

        switch (strtolower($next_class)) {
            case strtolower(ilPermissionGUI::class):
                $this->tabs_gui->setTabActive('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            case strtolower(ilBadgeManagementGUI::class):
                $this->assertActive();
                $this->tabs_gui->setTabActive('activity');
                $gui = new ilBadgeManagementGUI($this->ref_id, $this->obj_id, $this->type);
                $this->ctrl->forwardCommand($gui);
                break;

            default:
                if ($cmd === '' || $cmd === null || $cmd === 'view' || !method_exists($this, $cmd . 'Cmd')) {
                    $cmd = self::DEFAULT_ACTION;
                }
                $cmd .= 'Cmd';

                if ($this->badge_request->getBadgeIdFromUrl()) {
                    $this->ctrl->setParameter($this, 'tid', $this->badge_request->getBadgeIdFromUrl());
                }

                $this->$cmd();
                break;
        }
    }

    public function getAdminTabs(): void
    {
        if ($this->rbacsystem->checkAccess('read', $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                'settings',
                $this->lng->txt('settings'),
                $this->ctrl->getLinkTarget($this, self::EDIT_SETTINGS_ACTION)
            );

            if (ilBadgeHandler::getInstance()->isActive()) {
                $this->tabs_gui->addTab(
                    'types',
                    $this->lng->txt('badge_types'),
                    $this->ctrl->getLinkTarget($this, self::LIST_TYPES_ACTION)
                );

                $this->tabs_gui->addTab(
                    'imgtmpl',
                    $this->lng->txt('badge_image_templates'),
                    $this->ctrl->getLinkTarget($this, self::LIST_IMG_TPL_ACTION)
                );

                $this->tabs_gui->addTab(
                    'activity',
                    $this->lng->txt('badge_activity_badges'),
                    $this->ctrl->getLinkTargetByClass('ilbadgemanagementgui', '')
                );

                $this->tabs_gui->addTab(
                    'obj_badges',
                    $this->lng->txt('badge_object_badges'),
                    $this->ctrl->getLinkTarget($this, self::LIST_OBJ_BADGES_ACTION)
                );
            }
        }

        if ($this->rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                'perm_settings',
                $this->lng->txt('perm_settings'),
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', 'perm')
            );
        }
    }

    public function getSafePostCommands(): array
    {
        return [];
    }

    private function getTableAction(): ?string
    {
        return $this->http->wrapper()->query()->retrieve(
            'tid_table_action',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->string(),
                $this->refinery->always(null)
            ])
        );
    }

    private function handleTableActionsCmd(): void
    {
        match ($this->getTableAction()) {
            'badge_type_activate' => $this->activateTypes(),
            'badge_type_deactivate' => $this->deactivateTypes(),
            'badge_image_template_editImageTemplate', 'obj_badge_user' => $this->editImageTemplate(),
            'obj_badge_activate' => $this->activateObjectBadges(),
            'obj_badge_deactivate' => $this->deactivateObjectBadges(),
            'obj_badge_show_users' => $this->listObjectBadgeUsers(),
            'badge_image_template_delete' => $this->confirmDeleteImageTemplates(),
            'obj_badge_delete' => $this->confirmDeleteObjectBadges(),
            default => $this->ctrl->redirect($this, self::DEFAULT_ACTION),
        };
    }

    public function getUnsafeGetCommands(): array
    {
        return [self::TABLE_ACTIONS];
    }

    private function assertActive(): void
    {
        if (!ilBadgeHandler::getInstance()->isActive()) {
            $this->ctrl->redirect($this, self::DEFAULT_ACTION);
        }
    }

    //
    // settings
    //

    private function editSettingsCmd(?ilPropertyFormGUI $a_form = null): void
    {
        $this->tabs_gui->setTabActive('settings');

        if (!$a_form) {
            $a_form = $this->initFormSettings();
        }

        $this->tpl->setContent($a_form->getHTML());
    }

    private function saveSettingsCmd(): void
    {
        $this->checkPermission('write');

        $form = $this->initFormSettings();
        if ($form->checkInput()) {
            $handler = ilBadgeHandler::getInstance();
            $handler->setActive((bool) $form->getInput('act'));

            $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_SUCCESS, $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, self::EDIT_SETTINGS_ACTION);
        }

        $form->setValuesByPost();
        $this->editSettingsCmd($form);
    }

    private function initFormSettings(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, self::SAVE_SETTINGS_ACTION));
        $form->setTitle($this->lng->txt('badge_settings'));

        if ($this->access->checkAccess('write', '', $this->object->getRefId())) {
            $form->addCommandButton(self::SAVE_SETTINGS_ACTION, $this->lng->txt('save'));
            $form->addCommandButton(self::EDIT_SETTINGS_ACTION, $this->lng->txt('cancel'));
        }

        $act = new ilCheckboxInputGUI($this->lng->txt('badge_service_activate'), 'act');
        $act->setInfo($this->lng->txt('badge_service_activate_info'));
        $form->addItem($act);

        $handler = ilBadgeHandler::getInstance();
        $act->setChecked($handler->isActive());

        return $form;
    }

    //
    // types
    //

    private function listTypesCmd(): void
    {
        $this->assertActive();
        $this->tabs_gui->setTabActive('types');

        $tpl = new ilBadgeTypesTableGUI($this->access->checkAccess('write', '', $this->object->getRefId()));
        $tpl->renderTable(ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTarget($this, self::TABLE_ACTIONS));
    }

    private function activateTypes(): void
    {
        $this->assertActive();

        $tmpl_ids = $this->badge_request->getMultiActionBadgeIdsFromUrl();
        if ($this->checkPermissionBool('write') && count($tmpl_ids) > 0) {
            $handler = ilBadgeHandler::getInstance();
            $change_state = [];
            foreach ($handler->getInactiveTypes() as $type) {
                if (!in_array($type, $tmpl_ids)) {
                    $change_state[] = $type;
                }
            }

            if (current($tmpl_ids) === self::TABLE_ALL_OBJECTS_ACTION) {
                $handler->setInactiveTypes([]);
            } else {
                $handler->setInactiveTypes($change_state);
            }

            $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_SUCCESS, $this->lng->txt('settings_saved'), true);
        }

        $this->ctrl->redirect($this, self::LIST_TYPES_ACTION);
    }

    private function deactivateTypes(): void
    {
        $this->assertActive();

        $tmpl_ids = $this->badge_request->getMultiActionBadgeIdsFromUrl();
        if ($this->checkPermissionBool('write') && count($tmpl_ids) > 0) {
            $handler = ilBadgeHandler::getInstance();
            $change_state = [];
            foreach ($handler->getInactiveTypes() as $type) {
                if (!in_array($type, $tmpl_ids)) {
                    $change_state[] = $type;
                }
            }

            $res = [];
            if (current($tmpl_ids) === self::TABLE_ALL_OBJECTS_ACTION) {
                $types = $handler->getAvailableTypes(false);
                foreach ($types as $id => $type) {
                    $res[] = $id;
                }
                $handler->setInactiveTypes($res);
            } else {
                $handler->setInactiveTypes($change_state);
            }
            $inactive = array_merge($handler->getInactiveTypes(), $tmpl_ids);
            $handler->setInactiveTypes($inactive);

            $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_SUCCESS, $this->lng->txt('settings_saved'), true);
        }

        $this->ctrl->redirect($this, self::LIST_TYPES_ACTION);
    }

    //
    // images templates
    //

    private function listImageTemplatesCmd(): void
    {
        $this->assertActive();
        $this->tabs_gui->setTabActive('imgtmpl');

        if ($this->checkPermissionBool('write')) {
            $this->toolbar->addButton(
                $this->lng->txt('badge_add_template'),
                $this->ctrl->getLinkTarget($this, self::ADD_IMG_TPL_ACTION)
            );
        }

        $template_table = new ilBadgeImageTemplateTableGUI(
            $this->access->checkAccess('write', '', $this->object->getRefId())
        );
        $template_table->renderTable(ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTarget($this, self::TABLE_ACTIONS));
    }

    private function addImageTemplateCmd(
        ?ilPropertyFormGUI $a_form = null
    ): void {
        $this->checkPermission('write');

        $this->assertActive();
        $this->tabs_gui->setTabActive('imgtmpl');

        if (!$a_form) {
            $a_form = $this->initImageTemplateForm('create');
        }

        $this->tpl->setContent($a_form->getHTML());
    }

    private function initImageTemplateForm(
        string $a_mode
    ): ilPropertyFormGUI {
        $form = new ilPropertyFormGUI();
        if ($a_mode === 'create') {
            $form->setFormAction($this->ctrl->getFormAction($this, self::SAVE_IMG_TPL_ACTION));
        } else {
            $form->setFormAction($this->ctrl->getFormAction($this, self::UPDATE_IMG_TPL_ACTION));
        }
        $form->setTitle($this->lng->txt('badge_image_template_form'));

        $title = new ilTextInputGUI($this->lng->txt('title'), 'title');
        $title->setMaxLength(255);
        $title->setRequired(true);
        $form->addItem($title);

        $img = new ilImageFileInputGUI($this->lng->txt('image'), 'img');
        $img->setSuffixes(['png', 'svg']);
        if ($a_mode === 'create') {
            $img->setRequired(true);
        }
        $img->setUseCache(false);
        $img->setAllowDeletion(false);
        $form->addItem($img);

        $types_mode = new ilRadioGroupInputGUI($this->lng->txt('badge_template_types'), 'tmode');
        $types_mode->setRequired(true);
        $types_mode->setValue('all');
        $form->addItem($types_mode);

        $type_all = new ilRadioOption($this->lng->txt('badge_template_types_all'), 'all');
        $types_mode->addOption($type_all);

        $type_spec = new ilRadioOption($this->lng->txt('badge_template_types_specific'), 'spec');
        $types_mode->addOption($type_spec);

        $types = new ilCheckboxGroupInputGUI($this->lng->txt('badge_types'), 'type');
        $types->setRequired(true);
        $type_spec->addSubItem($types);

        foreach (ilBadgeHandler::getInstance()->getAvailableTypes(false) as $id => $type) {
            $types->addOption(new ilCheckboxOption($type->getCaption(), $id));
        }

        if ($a_mode === 'create') {
            $form->addCommandButton(self::SAVE_IMG_TPL_ACTION, $this->lng->txt('save'));
        } else {
            $form->addCommandButton(self::UPDATE_IMG_TPL_ACTION, $this->lng->txt('save'));
        }
        $form->addCommandButton(self::LIST_IMG_TPL_ACTION, $this->lng->txt('cancel'));

        return $form;
    }

    private function saveImageTemplateCmd(): void
    {
        $this->checkPermission('write');

        $form = $this->initImageTemplateForm('create');
        if ($form->checkInput()) {
            $tmpl = new ilBadgeImageTemplate();
            $tmpl->setTitle($form->getInput('title'));
            $tmpl->setTypes($form->getInput('type'));
            $tmpl->create();

            $tmpl->processImageUpload($tmpl);

            $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_SUCCESS, $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, self::LIST_IMG_TPL_ACTION);
        }

        $form->setValuesByPost();
        $this->addImageTemplateCmd($form);
    }

    private function editImageTemplate(
        ?ilPropertyFormGUI $a_form = null
    ): void {
        $this->checkPermission('write');

        $this->assertActive();
        $this->tabs_gui->setTabActive('imgtmpl');

        $tmpl_ids = $this->badge_request->getMultiActionBadgeIdsFromUrl();
        if (count($tmpl_ids) !== 1) {
            $tmpl_id = $this->badge_request->getTemplateId();
            if (!$tmpl_id) {
                $this->ctrl->redirect($this, self::LIST_IMG_TPL_ACTION);
            }

            $tmpl_ids = [$tmpl_id];
        }

        $template_id = (int) array_pop($tmpl_ids);
        $this->ctrl->setParameter($this, 'tid', $template_id);

        $tmpl = new ilBadgeImageTemplate($template_id);

        if (!$a_form) {
            $a_form = $this->initImageTemplateForm('edit');
            $this->setImageTemplateFormValues($a_form, $tmpl);
        }

        $this->tpl->setContent($a_form->getHTML());
    }

    private function setImageTemplateFormValues(
        ilPropertyFormGUI $a_form,
        ilBadgeImageTemplate $a_tmpl
    ): void {
        $a_form->getItemByPostVar('title')->setValue($a_tmpl->getTitle());
        if ($a_tmpl->getImageRid() !== null) {
            $img = $a_tmpl->getImageFromResourceId();
            $a_form->getItemByPostVar('img')->setImage($img);
            $a_form->getItemByPostVar('img')->setValue($a_tmpl->getImageRid());
        } else {
            $a_form->getItemByPostVar('img')->setImage($a_tmpl->getImagePath());
            $a_form->getItemByPostVar('img')->setValue($a_tmpl->getImage());
        }

        if ($a_tmpl->getTypes()) {
            $a_form->getItemByPostVar('tmode')->setValue('spec');
            $a_form->getItemByPostVar('type')->setValue($a_tmpl->getTypes());
        } else {
            $a_form->getItemByPostVar('tmode')->setValue('all');
        }
    }

    private function updateImageTemplateCmd(): void
    {
        $this->checkPermission('write');

        $tmpl_id = $this->badge_request->getTemplateId();
        if (!$tmpl_id) {
            $this->ctrl->redirect($this, self::LIST_IMG_TPL_ACTION);
        }

        $this->ctrl->setParameter($this, 'tid', $tmpl_id);

        $tmpl = new ilBadgeImageTemplate($tmpl_id);

        $form = $this->initImageTemplateForm('update');
        if ($form->checkInput()) {
            $tmpl->setTitle($form->getInput('title'));

            if ($form->getInput('tmode') !== 'all') {
                $tmpl->setTypes($form->getInput('type'));
            } else {
                $tmpl->setTypes(null);
            }

            $tmpl->update();

            $tmpl->processImageUpload($tmpl);

            $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_SUCCESS, $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, self::LIST_IMG_TPL_ACTION);
        }

        $this->setImageTemplateFormValues($form, $tmpl);
        $form->setValuesByPost();
        $this->editImageTemplate($form);
    }

    private function confirmDeleteImageTemplates(): void
    {
        $this->checkPermission('write');

        $tmpl_ids = $this->badge_request->getBadgeAssignableUsers();
        if ($tmpl_ids === [self::TABLE_ALL_OBJECTS_ACTION]) {
            $tmpl_ids = [];
            foreach (ilBadgeImageTemplate::getInstances() as $template) {
                $tmpl_ids[] = $template->getId();
            }
        }

        if (!$tmpl_ids) {
            $this->ctrl->redirect($this, self::LIST_IMG_TPL_ACTION);
        }

        $this->tabs->clearTargets();
        $this->tabs->setBackTarget(
            $this->lng->txt('back'),
            $this->ctrl->getLinkTarget($this, self::LIST_IMG_TPL_ACTION)
        );

        $confirmation_gui = new ilConfirmationGUI();
        $confirmation_gui->setFormAction($this->ctrl->getFormAction($this));
        $confirmation_gui->setHeaderText($this->lng->txt('badge_template_deletion_confirmation'));
        $confirmation_gui->setCancel($this->lng->txt('cancel'), self::LIST_IMG_TPL_ACTION);
        $confirmation_gui->setConfirm($this->lng->txt('delete'), self::DELETE_IMG_TPL_ACTION);

        foreach ($tmpl_ids as $tmpl_id) {
            $tmpl = new ilBadgeImageTemplate($tmpl_id);
            $confirmation_gui->addItem('id[]', $tmpl_id, $tmpl->getTitle());
        }

        $this->tpl->setContent($confirmation_gui->getHTML());
    }

    private function deleteImageTemplatesCmd(): void
    {
        $tmpl_ids = $this->badge_request->getIds();

        if ($this->checkPermissionBool('write') && count($tmpl_ids) > 0) {
            if (current($tmpl_ids) === self::TABLE_ALL_OBJECTS_ACTION) {
                $tmpl_ids = [];
                foreach (ilBadgeImageTemplate::getInstances() as $template) {
                    $tmpl_ids[] = $template->getId();
                }
            }
            foreach ($tmpl_ids as $tmpl_id) {
                $tmpl = new ilBadgeImageTemplate((int) $tmpl_id);
                $tmpl->delete();
            }
            $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_SUCCESS, $this->lng->txt('badge_deletion'), true);
        } else {
            $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_FAILURE, $this->lng->txt('badge_select_one'), true);
        }

        $this->ctrl->redirect($this, self::LIST_IMG_TPL_ACTION);
    }

    //
    // object badges
    //

    private function listObjectBadgeUsers(): void
    {
        $parent_obj_id = $this->badge_request->getParentId();
        if (!$parent_obj_id && $this->badge_request->getBadgeIdFromUrl()) {
            // In this case, we want't to list the users that have been awarded a specific badge
            $badge = new ilBadge($this->badge_request->getBadgeIdFromUrl());
            $parent_obj_id = $badge->getParentId();
        }
        if (!$parent_obj_id) {
            $this->ctrl->redirect($this, self::LIST_OBJ_BADGES_ACTION);
        }

        $this->assertActive();

        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget(
            $this->lng->txt('back'),
            $this->ctrl->getLinkTarget($this, self::LIST_OBJ_BADGES_ACTION)
        );

        $this->ctrl->saveParameter($this, 'pid');

        $tbl = new ilBadgeUserTableGUI(null, null, $parent_obj_id, $this->badge_request->getBadgeId());
        $tbl->renderTable(ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTarget($this, self::TABLE_ACTIONS));
    }

    private function listObjectBadgesCmd(): void
    {
        $this->assertActive();
        $this->tabs_gui->setTabActive('obj_badges');

        $tbl = new ilObjectBadgeTableGUI($this, $this->access->checkAccess('write', '', $this->object->getRefId()));
        $tbl->renderTable(ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTarget($this, self::TABLE_ACTIONS));
    }

    //
    // see ilBadgeManagementGUI
    //

    private function getObjectBadgesFromMultiAction(): array
    {
        $badge_ids = $this->badge_request->getMultiActionBadgeIdsFromUrl();
        if (!$badge_ids ||
            !$this->access->checkAccess('write', '', $this->object->getRefId())) {
            $this->ctrl->redirect($this, self::LIST_OBJ_BADGES_ACTION);
        }

        return $badge_ids;
    }

    private function toggleObjectBadges(bool $a_status): void
    {
        $badge_ids = $this->getObjectBadgesFromMultiAction();
        if (current($badge_ids) === self::TABLE_ALL_OBJECTS_ACTION) {
            $filter = ['type' => '' , 'title' => '', 'object' => ''];
            $badge_ids = [];
            foreach (ilBadge::getObjectInstances($filter) as $badge_item) {
                $badge_ids[] = $badge_item['id'];
            }
        }

        foreach ($badge_ids as $badge_id) {
            $badge = new ilBadge($badge_id);
            $badge->setActive($a_status);
            $badge->update();
        }

        $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_SUCCESS, $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, self::LIST_OBJ_BADGES_ACTION);
    }

    private function activateObjectBadges(): void
    {
        $this->toggleObjectBadges(true);
    }

    private function deactivateObjectBadges(): void
    {
        $this->toggleObjectBadges(false);
    }

    private function confirmDeleteObjectBadges(): void
    {
        $badge_ids = $this->badge_request->getMultiActionBadgeIdsFromUrl();

        $this->tabs->clearTargets();
        $this->tabs->setBackTarget(
            $this->lng->txt('back'),
            $this->ctrl->getLinkTarget($this, self::LIST_OBJ_BADGES_ACTION)
        );

        $confirmation_gui = new ilConfirmationGUI();
        $confirmation_gui->setFormAction($this->ctrl->getFormAction($this));
        $confirmation_gui->setHeaderText($this->lng->txt('badge_deletion_confirmation'));
        $confirmation_gui->setCancel($this->lng->txt('cancel'), self::LIST_OBJ_BADGES_ACTION);
        $confirmation_gui->setConfirm($this->lng->txt('delete'), self::DELETE_OBJ_BADGES_ACTION);

        if ($badge_ids === [self::TABLE_ALL_OBJECTS_ACTION]) {
            $badge_ids = [];
            $filter = [
                'type' => '',
                'title' => '',
                'object' => ''
            ];
            foreach (ilBadge::getObjectInstances($filter) as $badge_item) {
                $badge_ids[] = $badge_item['id'];
            }
        }
        foreach ($badge_ids as $badge_id) {
            $badge = new ilBadge($badge_id);
            $parent = $badge->getParentMeta();

            $container = '(' . $parent['type'] . '/' .
                $parent['id'] . ') ' .
                $parent['title'];
            if ($parent['deleted']) {
                $container .= ' <span class="il_ItemAlertProperty">' . $this->lng->txt('deleted') . '</span>';
            }

            $confirmation_gui->addItem(
                'id[]',
                $badge_id,
                $container . ' - ' .
                $badge->getTitle() .
                ' (' . count(ilBadgeAssignment::getInstancesByBadgeId($badge_id)) . ')'
            );
        }

        $this->tpl->setContent($confirmation_gui->getHTML());
    }

    private function deleteObjectBadgesCmd(): void
    {
        $badge_ids = $this->badge_request->getMultiActionBadgeIdsFromPost();

        foreach ($badge_ids as $badge_id) {
            $badge = new ilBadge($badge_id);
            $badge->delete();
        }

        $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_SUCCESS, $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, self::LIST_OBJ_BADGES_ACTION);
    }
}
