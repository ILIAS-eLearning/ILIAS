<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * UI class for handling permissions that can be configured
 * having the write permission for an object
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesAccessControl
 */
class ilSettingsPermissionGUI
{
    protected $permissions = array();			// permissions selected by context
    protected $base_permissions = array();		// base permissions of the object type (ops_id -> permission)
    protected $base_permissions_by_op = array();// base permissions of the object type (permission -> ops_id)
    protected $role_required_permissions = array();
    protected $role_prohibited_permissions = array();

    /**
     * Constructor
     *
     * @param ilObjectGUI $a_gui_obj object gui object
     */
    public function __construct($a_gui_obj)
    {
        global $DIC;

        $objDefinition = $DIC['objDefinition'];
        $tpl = $DIC['tpl'];
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $rbacreview = $DIC['rbacreview'];

        $this->objDefinition = $objDefinition;
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->lng->loadLanguageModule("rbac");

        $this->ctrl = $ilCtrl;

        $this->gui_obj = $a_gui_obj;
        $this->obj = $a_gui_obj->object;
        $this->red_id = $this->obj->getRefId();


        foreach (ilRbacReview::_getOperationList($this->obj->getType()) as $p) {
            $this->base_permissions[$p["ops_id"]] = $p["operation"];
            $this->base_permissions_by_op[$p["operation"]] = $p["ops_id"];
        }

        $this->base_roles = $rbacreview->getParentRoleIds($this->obj->getRefId());
    }

    /**
     * Determine roles
     */
    public function determineRoles()
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];

        $roles = array();
        foreach ($this->base_roles as $k => $r) {
            $ops = $rbacreview->getActiveOperationsOfRole($this->obj->getRefId(), $r["rol_id"]);
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
     *
     * @param array $a_val permissions required to be listed
     */
    public function setRoleRequiredPermissions($a_val)
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
     *
     * @return array permissions required to be listed
     */
    public function getRoleRequiredPermissions()
    {
        return $this->role_required_permissions;
    }

    /**
     * Set role prohibited permissions (this permissions are prohibited for a role to be listed)
     *
     * @param array $a_val permissions prohibited to be listed
     */
    public function setRoleProhibitedPermissions($a_val)
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
     *
     * @return array permissions prohibited to be listed
     */
    public function getRoleProhibitedPermissions()
    {
        return $this->role_prohibited_permissions;
    }

    /**
     * Set permissions
     *
     * @param array $a_val array of operations (string) that should be offered
     */
    public function setPermissions($a_val)
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
     *
     * @return array array of operations (string) that should be offered
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd("showForm");
        if (in_array($cmd, array("showForm", "save"))) {
            $this->$cmd();
        }
    }

    /**
     * Show form
     */
    public function showForm()
    {
        $form = $this->initPermissionForm();
        $this->tpl->setContent($form->getHTML());
    }


    /**
     * Init permission form
     */
    public function initPermissionForm()
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();

        $roles = $this->determineRoles();
        $ops = array();
        foreach ($roles as $r) {
            $ops[$r["rol_id"]] = $rbacreview->getActiveOperationsOfRole($this->obj->getRefId(), $r["rol_id"]);
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
                $option = new ilCheckboxOption($r["title"], $k);
                $cb->addOption($option);
            }
            if (is_array($perm_roles[$this->base_permissions_by_op[$p]])) {
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
    public function save()
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        $rbacadmin = $DIC['rbacadmin'];

        $form = $this->initPermissionForm();
        if ($form->checkInput()) {
            foreach ($this->determineRoles() as $r) {
                // get active operations for role
                $ops = $rbacreview->getActiveOperationsOfRole($this->obj->getRefId(), $r["rol_id"]);

                // revode all permissions for the role
                $rbacadmin->revokePermission($this->obj->getRefId(), $r["rol_id"]);

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
                $rbacadmin->grantPermission(
                    $r["rol_id"],
                    array_unique($ops),
                    $this->obj->getRefId()
                );
            }

            ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
            $this->ctrl->redirect($this, "");
        } else {
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHtml());
        }
    }
}
