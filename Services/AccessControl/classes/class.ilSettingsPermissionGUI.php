<?php

declare(strict_types=1);

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

/**
 * UI class for handling permissions that can be configured
 * having the write permission for an object
 * @author  Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesAccessControl
 */
class ilSettingsPermissionGUI
{
    protected array $permissions = array();            // permissions selected by context
    protected array $base_permissions = array();        // base permissions of the object type (ops_id -> permission)
    protected array $base_permissions_by_op = array();// base permissions of the object type (permission -> ops_id)
    protected array $role_required_permissions = array();
    protected array $role_prohibited_permissions = array();
    protected array $base_roles = [];

    private object $obj;

    protected ilRbacReview $review;
    protected ilRbacAdmin $admin;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;

    public function __construct(object $a_gui_obj)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule("rbac");
        $this->ctrl = $DIC->ctrl();
        $this->obj = $a_gui_obj->getObject();
        $this->review = $DIC->rbac()->review();
        $this->admin = $DIC->rbac()->admin();
        $this->tpl = $DIC->ui()->mainTemplate();

        foreach (ilRbacReview::_getOperationList($this->obj->getType()) as $p) {
            $this->base_permissions[$p["ops_id"]] = $p["operation"];
            $this->base_permissions_by_op[$p["operation"]] = $p["ops_id"];
        }

        $this->base_roles = $this->review->getParentRoleIds($this->obj->getRefId());
    }

    /**
     * Determine roles
     */
    public function determineRoles(): array
    {
        $roles = array();
        foreach ($this->base_roles as $k => $r) {
            $ops = $this->review->getActiveOperationsOfRole($this->obj->getRefId(), (int) $r["rol_id"]);
            $use = true;
            foreach ($this->getRoleRequiredPermissions() as $o) {
                if (!in_array($o, $ops)) {
                    $use = false;
                }
            }
            foreach ($this->getRoleProhibitedPermissions() as $o) {
                if (in_array($o, $ops)) {
                    $use = false;
                }
            }
            if ($use) {
                $roles[$k] = $r;
            }
        }
        return $roles;
    }

    /**
     * Set role required permissions (this permissions are required for a role to be listed)
     */
    public function setRoleRequiredPermissions(array $a_val): void
    {
        if (is_array($a_val)) {
            foreach ($a_val as $p) {
                if (in_array($p, $this->base_permissions)) {
                    $this->role_required_permissions[] = $this->base_permissions_by_op[$p];
                }
            }
        }
    }

    /**
     * Get role required permissions
     * @return array permissions required to be listed
     */
    public function getRoleRequiredPermissions(): array
    {
        return $this->role_required_permissions;
    }

    /**
     * Set role prohibited permissions (this permissions are prohibited for a role to be listed)
     * @param array $a_val permissions prohibited to be listed
     */
    public function setRoleProhibitedPermissions(array $a_val): void
    {
        if (is_array($a_val)) {
            foreach ($a_val as $p) {
                if (in_array($p, $this->base_permissions)) {
                    $this->role_prohibited_permissions[] = $this->base_permissions_by_op[$p];
                }
            }
        }
    }

    /**
     * Get role prohibited permissions
     * @return array permissions prohibited to be listed
     */
    public function getRoleProhibitedPermissions(): array
    {
        return $this->role_prohibited_permissions;
    }

    /**
     * Set permissions
     * @param array $a_val array of operations (string) that should be offered
     */
    public function setPermissions(array $a_val): void
    {
        if (is_array($a_val)) {
            foreach ($a_val as $p) {
                if (in_array($p, $this->base_permissions)) {
                    $this->permissions[$this->base_permissions_by_op[$p]] = $p;
                }
            }
        }
    }

    /**
     * Get permissions
     * @return array array of operations (string) that should be offered
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * Execute command
     */
    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd("showForm");
        if (in_array($cmd, array("showForm", "save"))) {
            $this->$cmd();
        }
    }

    /**
     * Show form
     */
    public function showForm(): void
    {
        $form = $this->initPermissionForm();
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Init permission form
     */
    public function initPermissionForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $roles = $this->determineRoles();
        $ops = array();
        foreach ($roles as $r) {
            $ops[(int) $r["rol_id"]] = $this->review->getActiveOperationsOfRole($this->obj->getRefId(), (int) $r["rol_id"]);
        }

        // for each permission, collect all roles that have the permission activated
        $perm_roles = array();
        foreach ($ops as $r => $o2) {
            foreach ($o2 as $o) {
                $perm_roles[$o][] = $r;
            }
        }

        // for each permission
        foreach ($this->getPermissions() as $p) {
            // roles
            $cb = new ilCheckboxGroupInputGUI($this->lng->txt($p), $p);
            reset($roles);
            foreach ($roles as $k => $r) {
                $option = new ilCheckboxOption(ilObjRole::_getTranslation($r["title"]), (string) $k);
                $cb->addOption($option);
            }
            if (isset($perm_roles[$this->base_permissions_by_op[$p]])) {
                $cb->setValue($perm_roles[$this->base_permissions_by_op[$p]]);
            }
            $form->addItem($cb);
        }

        $form->addCommandButton("save", $this->lng->txt("save"));

        $form->setTitle($this->lng->txt("rbac_permissions"));
        $form->setFormAction($this->ctrl->getFormAction($this));
        return $form;
    }

    /**
     * Save  form
     */
    public function save(): void
    {
        $form = $this->initPermissionForm();
        if ($form->checkInput()) {
            foreach ($this->determineRoles() as $r) {
                // get active operations for role
                $ops = $this->review->getActiveOperationsOfRole($this->obj->getRefId(), $r["rol_id"]);

                // revode all permissions for the role
                $this->admin->revokePermission($this->obj->getRefId(), $r["rol_id"]);

                // for all permissions of the form...
                foreach ($this->getPermissions() as $p) {
                    $roles = $form->getInput($p);
                    if (!is_array($roles)) {
                        $roles = array();
                    }
                    $o = $this->base_permissions_by_op[$p];

                    // ... if in original operations, but not checked, remove it from operations
                    if (in_array($o, $ops) && !in_array($r["rol_id"], $roles)) {
                        if (($key = array_search($o, $ops)) !== false) {
                            unset($ops[$key]);
                        }
                    }

                    // ...if not in original operations, but checked, add to operations
                    if (!in_array($o, $ops) && in_array($r["rol_id"], $roles)) {
                        $ops[] = $o;
                    }
                }
                // now grant resulting permissions
                $this->admin->grantPermission(
                    $r["rol_id"],
                    array_unique($ops),
                    $this->obj->getRefId()
                );
            }

            $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
            $this->ctrl->redirect($this, "");
        } else {
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHTML());
        }
    }
}
