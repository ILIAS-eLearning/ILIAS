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

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;

/**
 * Class ilObjRoleTemplateGUI
 * @author       Stefan Meyer <meyer@leifos.com>
 * @version      $Id$
 * @ilCtrl_Calls ilObjRoleTemplateGUI:
 * @ingroup      ServicesAccessControl
 */
class ilObjRoleTemplateGUI extends ilObjectGUI
{
    private const FORM_KEY_TITLE = 'title';
    private const FORM_KEY_DESCRIPTION = 'description';
    private const FORM_KEY_ILIAS_ID = 'ilias_id';
    private const FORM_KEY_PROTECT = 'protect';

    private int $rolf_ref_id;

    protected ilRbacAdmin $rbac_admin;

    private GlobalHttpState $http;
    protected Factory $refinery;

    public function __construct($a_data, int $a_id, bool $a_call_by_reference)
    {
        global $DIC;

        $this->rbac_admin = $DIC->rbac()->admin();

        $this->type = "rolt";
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);
        $this->lng->loadLanguageModule('rbac');
        $this->rolf_ref_id = &$this->ref_id;
        $this->ctrl->saveParameter($this, "obj_id");
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
    }

    public function executeCommand(): void
    {
        $this->prepareOutput();

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                if (!$cmd) {
                    $cmd = "perm";
                }
                $cmd .= "Object";
                $this->$cmd();

                break;
        }
    }

    protected function getRoleTemplateForm(bool $is_role_creation_form = false): StandardForm
    {
        if ($this->creation_mode) {
            $this->ctrl->setParameter($this, 'new_type', 'rolt');
        }

        $ff = $this->ui_factory->input()->field();

        $title_validation_constraint = $this->refinery->custom()->constraint(
            fn(string $v): bool => preg_match('/^il_.*$/', $v) ? false : true,
            $this->lng->txt('msg_role_reserved_prefix')
        );

        $inputs = [
            self::FORM_KEY_TITLE => $ff->text($this->lng->txt('title'))
                ->withMaxLength(70)
                ->withRequired(true)
                ->withAdditionalTransformation($title_validation_constraint)
                ->withValue(
                    $is_role_creation_form ? ''
                    : ilObjRole::_getTranslation($this->object->getTitle())
                )->withDisabled($is_role_creation_form ? false : $this->object->isInternalTemplate()),
            self::FORM_KEY_DESCRIPTION => $ff->textarea($this->lng->txt('description'))
                ->withMaxLimit(4000)
                ->withValue($is_role_creation_form ? '' : $this->object->getDescription())
        ];

        if (!$is_role_creation_form) {
            $inputs[self::FORM_KEY_ILIAS_ID] = $ff->text($this->lng->txt('ilias_id'))
                ->withDisabled(true)
                ->withValue('il_' . IL_INST_ID . '_'
                    . $this->object->getType() . '_' . $this->object->getId());
        }

        $inputs[self::FORM_KEY_PROTECT] = $ff->checkbox($this->lng->txt('role_protect_permissions'))
            ->withValue(
                $is_role_creation_form
                    ? false
                    : $this->rbac_review->isProtected($this->rolf_ref_id, $this->object->getId())
            );

        return $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getFormActionByClass(
                self::class,
                $is_role_creation_form ? 'save' : 'update'
            ),
            $inputs
        )->withSubmitLabel(
            $is_role_creation_form ? $this->lng->txt('rolt_new') : $this->lng->txt('save')
        );
    }

    public function createObject(): void
    {
        if (!$this->rbac_system->checkAccess('create_rolt', $this->rolf_ref_id)) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $this->tabs_gui->setBackTarget(
            $this->lng->txt('cancel'),
            $this->ctrl->getParentReturnByClass(self::class)
        );

        $this->tpl->setContent(
            $this->ui_renderer->render(
                $this->ui_factory->panel()->standard(
                    $this->lng->txt('rolt_new'),
                    $this->getRoleTemplateForm(true)
                )
            )
        );
    }

    /**
     * Create new object
     */
    public function editObject(?ilPropertyFormGUI $form = null): void
    {
        $this->tabs_gui->activateTab('settings');

        if (!$this->rbac_system->checkAccess("write", $this->rolf_ref_id)) {
            $this->error->raiseError($this->lng->txt("msg_no_perm_write"), $this->error->MESSAGE);
        }

        $this->tpl->setContent(
            $this->ui_renderer->render(
                $this->ui_factory->panel()->standard(
                    $this->lng->txt('rolt_edit'),
                    $this->getRoleTemplateForm()
                )
            )
        );
    }

    public function saveObject(): void
    {
        if (!$this->rbac_system->checkAccess("create_rolt", $this->rolf_ref_id)) {
            $this->ilias->raiseError($this->lng->txt("msg_no_perm_create_rolt"), $this->ilias->error_obj->WARNING);
        }

        $form = $this->getRoleTemplateForm(true)->withRequest($this->request);
        $data = $form->getData();
        if ($data === null) {
            $this->tabs_gui->setBackTarget(
                $this->lng->txt('cancel'),
                $this->ctrl->getParentReturnByClass(self::class)
            );

            $this->tpl->setContent(
                $this->ui_renderer->render(
                    $this->ui_factory->panel()->standard(
                        $this->lng->txt('rolt_new'),
                        $form
                    )
                )
            );
            return;
        }

        $role_template = new ilObjRoleTemplate();
        $role_template->setTitle($data[self::FORM_KEY_TITLE]);
        $role_template->setDescription($data[self::FORM_KEY_DESCRIPTION]);
        $role_template->create();
        $this->rbac_admin->assignRoleToFolder($role_template->getId(), $this->rolf_ref_id, 'n');
        $this->rbac_admin->setProtected(
            $this->rolf_ref_id,
            $role_template->getId(),
            $data[self::FORM_KEY_PROTECT] ? 'y' : 'n'
        );
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("rolt_added"), true);
        $this->ctrl->setParameter($this, 'obj_id', $role_template->getId());
        $this->ctrl->redirect($this, 'perm');
    }

    public function updateObject(): void
    {
        if (!$this->rbac_system->checkAccess('write', $this->rolf_ref_id)) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_modify_rolt'), $this->error->WARNING);
        }

        $form = $this->getRoleTemplateForm()->withRequest($this->request);
        $data = $form->getData();
        if ($data === null) {
            $this->tpl->setContent(
                $this->ui_renderer->render(
                    $this->ui_factory->panel()->standard(
                        $this->lng->txt('rolt_edit'),
                        $form
                    )
                )
            );
            return;
        }

        if (!$this->object->isInternalTemplate()) {
            $this->object->setTitle($data[self::FORM_KEY_TITLE]);
        }

        $this->object->setDescription($data[self::FORM_KEY_DESCRIPTION]);
        $this->object->update();
        $this->rbac_admin->setProtected(
            $this->rolf_ref_id,
            $this->object->getId(),
            $data[self::FORM_KEY_PROTECT] ? 'y' : 'n'
        );
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("saved_successfully"), true);
        $this->ctrl->returnToParent($this);
    }

    protected function permObject(): void
    {
        if (!$this->rbac_system->checkAccess('edit_permission', $this->ref_id)) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_perm'), $this->error->MESSAGE);
            return;
        }
        $this->tabs_gui->activateTab('perm');

        $this->tpl->addBlockFile(
            'ADM_CONTENT',
            'adm_content',
            'tpl.rbac_template_permissions.html',
            'components/ILIAS/AccessControl'
        );

        $this->tpl->setVariable('PERM_ACTION', $this->ctrl->getFormAction($this));

        $acc = new ilAccordionGUI();
        $acc->setBehaviour(ilAccordionGUI::FORCE_ALL_OPEN);
        $acc->setId('template_perm_' . $this->ref_id);

        $subs = ilObjRole::getSubObjects('root', false);

        foreach ($subs as $subtype => $def) {
            $tbl = new ilObjectRoleTemplatePermissionTableGUI(
                $this,
                'perm',
                $this->ref_id,
                $this->obj_id,
                $subtype,
                false
            );
            $tbl->setShowChangeExistingObjects(false);
            $tbl->parse();

            $acc->addItem($def['translation'], $tbl->getHTML());
        }

        $this->tpl->setVariable('ACCORDION', $acc->getHTML());

        // Add options table
        $options = new ilObjectRoleTemplateOptionsTableGUI(
            $this,
            'perm',
            $this->ref_id,
            $this->obj_id,
            false
        );
        $options->setShowOptions(false);
        $options->addMultiCommand(
            'permSave',
            $this->lng->txt('save')
        );

        $options->parse();
        $this->tpl->setVariable('OPTIONS_TABLE', $options->getHTML());
    }

    /**
     * @todo fix custom transformation
     */
    protected function permSaveObject(): void
    {
        if (!$this->rbac_system->checkAccess('write', $this->rolf_ref_id)) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_perm'), $this->error->MESSAGE);
            return;
        }

        $template_permissions = [];
        if ($this->http->wrapper()->post()->has('template_perm')) {
            $custom_transformer = $this->refinery->custom()->transformation(
                function ($array) {
                    return $array;
                }
            );
            $template_permissions = $this->http->wrapper()->post()->retrieve(
                'template_perm',
                $custom_transformer
            );
        }

        $subs = ilObjRole::getSubObjects('root', false);
        foreach (array_keys($subs) as $subtype) {
            // Delete per object type
            $this->rbac_admin->deleteRolePermission($this->object->getId(), $this->ref_id, $subtype);
        }

        foreach ($template_permissions as $key => $ops_array) {
            $this->rbac_admin->setRolePermission($this->object->getId(), $key, $ops_array, $this->rolf_ref_id);
        }

        // update object data entry (to update last modification date)
        $this->object->update();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("saved_successfully"), true);
        $this->ctrl->redirect($this, "perm");
    }

    public function adoptPermSaveObject(): void
    {
        $source = 0;
        if ($this->http->wrapper()->post()->has('adopt')) {
            $source = $this->http->wrapper()->post()->retrieve(
                'adopt',
                $this->refinery->kindlyTo()->int()
            );
        }

        if (!$this->rbac_system->checkAccess('write', $this->rolf_ref_id)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_no_perm_perm'), true);
        } elseif ($this->obj_id == $source) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("msg_perm_adopted_from_itself"), true);
        } else {
            $this->rbac_admin->deleteRolePermission($this->obj_id, $this->rolf_ref_id);
            $parentRoles = $this->rbac_review->getParentRoleIds($this->rolf_ref_id, true);
            $this->rbac_admin->copyRoleTemplatePermissions(
                $source,
                $parentRoles[$source]["parent"],
                $this->rolf_ref_id,
                $this->obj_id
            );
            // update object data entry (to update last modification date)
            $this->object->update();

            // send info
            $title = ilObject::_lookupTitle($source);
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_perm_adopted_from1") . " '" . $title . "'.<br/>" . $this->lng->txt("msg_perm_adopted_from2"), true);
        }
        $this->ctrl->redirect($this, "perm");
    }

    public function getAdminTabs(): void
    {
        $this->getTabs();
    }

    /**
     * @inheritdoc
     */
    protected function getTabs(): void
    {
        $this->tabs_gui->setBackTarget($this->lng->txt('btn_back'), (string) $this->ctrl->getParentReturn($this));

        if ($this->rbac_system->checkAccess('write', $this->ref_id)) {
            $this->tabs_gui->addTab(
                'settings',
                $this->lng->txt('settings'),
                $this->ctrl->getLinkTarget($this, 'edit')
            );
        }
        if ($this->rbac_system->checkAccess('edit_permission', $this->ref_id)) {
            $this->tabs_gui->addTab(
                'perm',
                $this->lng->txt('default_perm_settings'),
                $this->ctrl->getLinkTarget($this, 'perm')
            );
        }
    }

    /**
     * @inheritdoc
     */
    protected function addAdminLocatorItems(bool $do_not_add_object = false): void
    {
        parent::addAdminLocatorItems(true);

        $query = $this->http->wrapper()->query();

        if ($query->has('ref_id')) {
            $ref_id = $query->retrieve('ref_id', $this->refinery->kindlyTo()->int());
            $this->locator->addItem(
                $this->lng->txt('obj_' . ilObject::_lookupType(
                    ilObject::_lookupObjId($ref_id)
                )),
                $this->ctrl->getLinkTargetByClass("ilobjrolefoldergui", "view")
            );
        }

        if ($query->has('obj_id')) {
            $this->locator->addItem(
                ilObjRole::_getTranslation($this->object->getTitle()),
                $this->ctrl->getLinkTarget($this, 'perm')
            );
        }
    }
} // END class.ilObjRoleTemplateGUI
