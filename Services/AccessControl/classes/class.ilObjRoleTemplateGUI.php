<?php declare(strict_types=1);

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

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory;

/**
 * Class ilObjRoleTemplateGUI
 * @author       Stefan Meyer <meyer@leifos.com>
 * @version      $Id$
 * @ilCtrl_Calls ilObjRoleTemplateGUI:
 * @ingroup      ServicesAccessControl
 */
class ilObjRoleTemplateGUI extends ilObjectGUI
{
    private const FORM_MODE_EDIT = 1;
    private const FORM_MODE_CREATE = 2;

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

    public function executeCommand() : void
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

    protected function initFormRoleTemplate(int $a_mode = self::FORM_MODE_CREATE) : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();

        if ($this->creation_mode) {
            $this->ctrl->setParameter($this, "new_type", 'rolt');
        }

        $form->setFormAction($this->ctrl->getFormAction($this));

        if ($a_mode == self::FORM_MODE_CREATE) {
            $form->setTitle($this->lng->txt('rolt_new'));
            $form->addCommandButton('save', $this->lng->txt('rolt_new'));
        } else {
            $form->setTitle($this->lng->txt('rolt_edit'));
            $form->addCommandButton('update', $this->lng->txt('save'));
        }
        $form->addCommandButton('cancel', $this->lng->txt('cancel'));

        $title = new ilTextInputGUI($this->lng->txt('title'), 'title');
        if ($a_mode != self::FORM_MODE_CREATE) {
            if ($this->object->isInternalTemplate()) {
                $title->setDisabled(true);
            }
            $title->setValue(ilObjRole::_getTranslation($this->object->getTitle()));
        }
        $title->setSize(40);
        $title->setMaxLength(70);
        $title->setRequired(true);
        $form->addItem($title);

        $desc = new ilTextAreaInputGUI($this->lng->txt('description'), 'desc');

        if ($a_mode != self::FORM_MODE_CREATE) {
            $desc->setValue($this->object->getDescription());
        }
        $desc->setCols(40);
        $desc->setRows(3);
        $form->addItem($desc);

        if ($a_mode != self::FORM_MODE_CREATE) {
            $ilias_id = new ilNonEditableValueGUI($this->lng->txt("ilias_id"), "ilias_id");
            $ilias_id->setValue('il_' . IL_INST_ID . '_' . ilObject::_lookupType($this->object->getId()) . '_' . $this->object->getId());
            $form->addItem($ilias_id);
        }

        $pro = new ilCheckboxInputGUI($this->lng->txt('role_protect_permissions'), 'protected');
        $pro->setChecked($GLOBALS['DIC']['rbacreview']->isProtected(
            $this->rolf_ref_id,
            $this->object->getId()
        ));
        $pro->setValue((string) 1);
        $form->addItem($pro);

        return $form;
    }

    public function createObject(ilPropertyFormGUI $form = null) : void
    {
        if (!$this->rbac_system->checkAccess("create_rolt", $this->rolf_ref_id)) {
            $this->error->raiseError($this->lng->txt("permission_denied"), $this->error->MESSAGE);
        }
        if ($form === null) {
            $form = $this->initFormRoleTemplate(self::FORM_MODE_CREATE);
        }
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Create new object
     */
    public function editObject(ilPropertyFormGUI $form = null) : void
    {
        $this->tabs_gui->activateTab('settings');

        if (!$this->rbac_system->checkAccess("write", $this->rolf_ref_id)) {
            $this->error->raiseError($this->lng->txt("msg_no_perm_write"), $this->error->MESSAGE);
        }

        if ($form === null) {
            $form = $this->initFormRoleTemplate(self::FORM_MODE_EDIT);
        }
        $this->tpl->setContent($form->getHTML());
    }

    public function updateObject() : void
    {
        // check write access
        if (!$this->rbac_system->checkAccess("write", $this->rolf_ref_id)) {
            $this->error->raiseError($this->lng->txt("msg_no_perm_modify_rolt"), $this->error->WARNING);
        }

        $form = $this->initFormRoleTemplate(self::FORM_MODE_EDIT);
        if ($form->checkInput()) {
            if (!$this->object->isInternalTemplate()) {
                $this->object->setTitle($form->getInput('title'));
            }
            $this->object->setDescription($form->getInput('desc'));
            $this->rbac_admin->setProtected(
                $this->rolf_ref_id,
                $this->object->getId(),
                $form->getInput('protected') ? 'y' : 'n'
            );
            $this->object->update();
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("saved_successfully"), true);
            $this->ctrl->returnToParent($this);
        }

        $form->setValuesByPost();
        $this->editObject($form);
    }

    public function saveObject() : void
    {
        if (!$this->rbac_system->checkAccess("create_rolt", $this->rolf_ref_id)) {
            $this->ilias->raiseError($this->lng->txt("msg_no_perm_create_rolt"), $this->ilias->error_obj->WARNING);
        }
        $form = $this->initFormRoleTemplate();
        if ($form->checkInput()) {
            $roltObj = new ilObjRoleTemplate();
            $roltObj->setTitle($form->getInput('title'));
            $roltObj->setDescription($form->getInput('desc'));
            $roltObj->create();
            $this->rbac_admin->assignRoleToFolder($roltObj->getId(), $this->rolf_ref_id, 'n');
            $this->rbac_admin->setProtected(
                $this->rolf_ref_id,
                $roltObj->getId(),
                $form->getInput('protected') ? 'y' : 'n'
            );

            $this->tpl->setOnScreenMessage('success', $this->lng->txt("rolt_added"), true);
            // redirect to permission screen
            $this->ctrl->setParameter($this, 'obj_id', $roltObj->getId());
            $this->ctrl->redirect($this, 'perm');
        }
        $form->setValuesByPost();
        $this->createObject($form);
    }

    protected function permObject() : void
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
            'Services/AccessControl'
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
    protected function permSaveObject() : void
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
        // delete all existing template entries
        //$rbacadmin->deleteRolePermission($this->object->getId(), $this->ref_id);
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

    public function adoptPermSaveObject() : void
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

    public function getAdminTabs() : void
    {
        $this->getTabs();
    }

    /**
     * @inheritdoc
     */
    protected function getTabs() : void
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

    public function cancelObject() : void
    {
        $this->ctrl->redirectByClass("ilobjrolefoldergui", "view");
    }

    /**
     * @inheritdoc
     */
    protected function addAdminLocatorItems(bool $do_not_add_object = false) : void
    {
        parent::addAdminLocatorItems(true);

        $this->locator->addItem(
            ilObject::_lookupTitle(
                ilObject::_lookupObjId($this->object->getRefId())
            ),
            $this->ctrl->getLinkTargetByClass("ilobjrolefoldergui", "view")
        );
    }
} // END class.ilObjRoleTemplateGUI
