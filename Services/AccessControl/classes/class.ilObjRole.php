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
 
/**
 * Class ilObjRole
 * @author     Stefan Meyer <meyer@leifos.com>
 * @ingroup    ServicesAccessControl
 */
class ilObjRole extends ilObject
{
    public const MODE_PROTECTED_DELETE_LOCAL_POLICIES = 1;
    public const MODE_PROTECTED_KEEP_LOCAL_POLICIES = 2;
    public const MODE_UNPROTECTED_DELETE_LOCAL_POLICIES = 3;
    public const MODE_UNPROTECTED_KEEP_LOCAL_POLICIES = 4;

    public const MODE_ADD_OPERATIONS = 1;
    public const MODE_READ_OPERATIONS = 2;
    public const MODE_REMOVE_OPERATIONS = 3;

    private ilLogger $logger;

    public ?int $parent = null;

    protected bool $allow_register = false;
    protected bool $assign_users = false;

    /**
     * Constructor
     * @access    public
     * @param int    reference_id or object_id
     * @param bool    treat the id as reference_id (true) or object_id (false)
     */
    public function __construct(int $a_id = 0, bool $a_call_by_reference = false)
    {
        global $DIC;

        $this->logger = $DIC->logger()->ac();
        $this->type = "role";
        parent::__construct($a_id, $a_call_by_reference);
    }

    public static function createDefaultRole(
        string $a_title,
        string $a_description,
        string $a_tpl_name,
        int $a_ref_id
    ) : ?ilObjRole {
        global $DIC;

        $ilDB = $DIC->database();

        // SET PERMISSION TEMPLATE OF NEW LOCAL CONTRIBUTOR ROLE
        $res = $ilDB->query("SELECT obj_id FROM object_data " .
            " WHERE type=" . $ilDB->quote("rolt", "text") .
            " AND title=" . $ilDB->quote($a_tpl_name, "text"));
        $tpl_id = 0;
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $tpl_id = (int) $row->obj_id;
        }
        if (!$tpl_id) {
            return null;
        }

        $role = new ilObjRole();
        $role->setTitle($a_title);
        $role->setDescription($a_description);
        $role->create();

        $GLOBALS['DIC']['rbacadmin']->assignRoleToFolder($role->getId(), $a_ref_id, 'y');
        $GLOBALS['DIC']['rbacadmin']->copyRoleTemplatePermissions(
            $tpl_id,
            ROLE_FOLDER_ID,
            $a_ref_id,
            $role->getId()
        );

        $ops = $GLOBALS['DIC']['rbacreview']->getOperationsOfRole(
            $role->getId(),
            ilObject::_lookupType($a_ref_id, true),
            $a_ref_id
        );
        $GLOBALS['DIC']['rbacadmin']->grantPermission(
            $role->getId(),
            $ops,
            $a_ref_id
        );
        return $role;
    }

    public function validate() : bool
    {
        global $DIC;

        $ilErr = $DIC['ilErr'];

        if (substr($this->getTitle(), 0, 3) == 'il_') {
            $ilErr->setMessage('msg_role_reserved_prefix');
            return false;
        }
        return true;
    }

    public function getPresentationTitle() : string
    {
        return ilObjRole::_getTranslation($this->getTitle());
    }

    public function toggleAssignUsersStatus(bool $a_assign_users) : void
    {
        $this->assign_users = $a_assign_users;
    }

    public function getAssignUsersStatus() : bool
    {
        return $this->assign_users;
    }

    public static function _getAssignUsersStatus(int $a_role_id) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "SELECT assign_users FROM role_data WHERE role_id = " . $ilDB->quote($a_role_id, 'integer') . " ";
        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchObject($res)) {
            return (bool) $row->assign_users;
        }
        return false;
    }

    /**
     * loads "role" from database
     * @access private
     */
    public function read() : void
    {
        $query = "SELECT * FROM role_data WHERE role_id= " . $this->db->quote($this->id, 'integer') . " ";
        $res = $this->db->query($query);
        if ($res->numRows() > 0) {
            $row = $this->db->fetchAssoc($res);
            $this->setAllowRegister((bool) $row['allow_register']);
            $this->toggleAssignUsersStatus((bool) ($row['assign_user'] ?? false));
        } else {
            $this->logger->logStack(ilLogLevel::ERROR);
            throw new ilObjectException('There is no dataset with id: ' . $this->id);
        }
        parent::read();
    }

    public function update() : bool
    {
        $query = "UPDATE role_data SET " .
            "allow_register= " . $this->db->quote($this->allow_register, 'integer') . ", " .
            "assign_users = " . $this->db->quote($this->getAssignUsersStatus(), 'integer') . " " .
            "WHERE role_id= " . $this->db->quote($this->id, 'integer') . " ";
        $res = $this->db->manipulate($query);

        parent::update();

        $this->read();

        return true;
    }

    public function create() : int
    {
        global $DIC;

        $this->id = parent::create();
        $query = "INSERT INTO role_data " .
            "(role_id,allow_register,assign_users) " .
            "VALUES " .
            "(" . $this->db->quote($this->id, 'integer') . "," .
            $this->db->quote($this->getAllowRegister(), 'integer') . "," .
            $this->db->quote($this->getAssignUsersStatus(), 'integer') . ")";
        $res = $this->db->query($query);

        return $this->id;
    }

    public function setAllowRegister(bool $a_allow_register) : void
    {
        $this->allow_register = $a_allow_register;
    }

    public function getAllowRegister() : bool
    {
        return $this->allow_register;
    }

    /**
     * get all roles that are activated in user registration
     */
    public static function _lookupRegisterAllowed() : array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "SELECT * FROM role_data " .
            "JOIN object_data ON object_data.obj_id = role_data.role_id " .
            "WHERE allow_register = 1";
        $res = $ilDB->query($query);

        $roles = [];
        while ($role = $ilDB->fetchAssoc($res)) {
            $roles[] = array("id" => (int) $role["obj_id"],
                             "title" => (string) $role["title"],
                             "auth_mode" => (string) $role['auth_mode']
            );
        }
        return $roles;
    }

    /**
     * check whether role is allowed in user registration or not
     **/
    public static function _lookupAllowRegister(int $a_role_id) : bool
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM role_data " .
            " WHERE role_id =" . $ilDB->quote($a_role_id, 'integer');

        $res = $ilDB->query($query);
        if ($role_rec = $ilDB->fetchAssoc($res)) {
            if ($role_rec["allow_register"]) {
                return true;
            }
        }
        return false;
    }

    /**
     * set reference id of parent object
     * this is neccessary for non RBAC protected objects!!!
     */
    public function setParent(int $a_parent_ref) : void
    {
        $this->parent = $a_parent_ref;
    }

    /**
     * get reference id of parent object
     */
    public function getParent() : ?int
    {
        return $this->parent;
    }

    /**
     * delete role and all related data
     * @access    public
     * @return    bool    true if all object data were removed; false if only a references were removed
     */
    public function delete() : bool
    {
        global $DIC;

        // Temporary bugfix
        if ($this->rbac_review->hasMultipleAssignments($this->getId())) {
            $this->logger->warning('Found role with multiple assignments: role_id: ' . $this->getId());
            $this->logger->warning('Aborted deletion of role.');
            return false;
        }

        if ($this->rbac_review->isAssignable($this->getId(), $this->getParent())) {
            $this->logger->debug('Handling assignable role...');
            // do not delete a global role, if the role is the last
            // role a user is assigned to.
            //
            // Performance improvement: In the code section below, we
            // only need to consider _global_ roles. We don't need
            // to check for _local_ roles, because a user who has
            // a local role _always_ has a global role too.
            $last_role_user_ids = array();
            if ($this->getParent() == ROLE_FOLDER_ID) {
                ilLoggerFactory::getLogger('ac')->debug('Handling global role...');
                // The role is a global role: check if
                // we find users who aren't assigned to any
                // other global role than this one.
                $user_ids = $this->rbac_review->assignedUsers($this->getId());

                foreach ($user_ids as $user_id) {
                    // get all roles each user has
                    $role_ids = $this->rbac_review->assignedRoles($user_id);

                    // is last role?
                    if (count($role_ids) == 1) {
                        $last_role_user_ids[] = $user_id;
                    }
                }
            }

            // users with last role found?
            if ($last_role_user_ids !== []) {
                $user_names = array();
                foreach ($last_role_user_ids as $user_id) {
                    // GET OBJECT TITLE
                    $user_names[] = ilObjUser::_lookupLogin($user_id);
                }

                // TODO: This check must be done in rolefolder object because if multiple
                // roles were selected the other roles are still deleted and the system does not
                // give any feedback about this.
                $users = implode(', ', $user_names);
                $this->logger->info('Cannot delete last global role of users.');
                $this->ilias->raiseError($this->lng->txt("msg_user_last_role1") . " " .
                    $users . "<br/>" . $this->lng->txt("msg_user_last_role2"), $this->ilias->error_obj->WARNING);
            } else {
                $this->logger->debug('Starting deletion of assignable role: role_id: ' . $this->getId());
                $this->rbac_admin->deleteRole($this->getId(), $this->getParent());

                // Delete ldap role group mappings
                ilLDAPRoleGroupMappingSettings::_deleteByRole($this->getId());

                // delete object_data entry
                parent::delete();

                // delete role_data entry
                $query = "DELETE FROM role_data WHERE role_id = " . $this->db->quote($this->getId(), 'integer');
                $res = $this->db->manipulate($query);
            }
        } else {
            $this->logger->debug('Starting deletion of linked role: role_id ' . $this->getId());
            // linked local role: INHERITANCE WAS STOPPED, SO DELETE ONLY THIS LOCAL ROLE
            $this->rbac_admin->deleteLocalRole($this->getId(), $this->getParent());
        }
        return true;
    }

    /**
     * Get number of users assigned to role
     */
    public function getCountMembers() : int
    {
        return count($this->rbac_review->assignedUsers($this->getId()));
    }

    public static function _getTranslation(string $a_role_title) : string
    {
        global $DIC;

        $lng = $DIC->language();
        $objDefinition = $DIC['objDefinition'];

        $role_title = self::_removeObjectId($a_role_title);

        if (preg_match("/^il_([a-z]{1,4})_./", $role_title, $type)) {
            //BT ID 0032909: language variables for roles from plugins were not resolved properly
            if ($objDefinition->isPlugin($type[1])) {
                return ilObjectPlugin::lookupTxtById($type[1], $role_title);
            }
            return $lng->txt($role_title);
        }
        return $a_role_title;
    }

    /**
     * @todo rename of remove method
     */
    public static function _removeObjectId(string $a_role_title) : string
    {
        $role_title_parts = explode('_', $a_role_title);

        $test2 = (int) ($role_title_parts[3] ?? 0);
        if ($test2 > 0) {
            unset($role_title_parts[3]);
        }

        return implode('_', $role_title_parts);
    }

    /**
     * Get and sort sub object types
     */
    public static function getSubObjects(string $a_obj_type, bool $a_add_admin_objects) : array
    {
        global $DIC;
        /**
         * @var ilObjectDefinition $objDefinition
         */
        $objDefinition = $DIC['objDefinition'];
        $lng = $DIC->language();
        $subs = $objDefinition->getSubObjectsRecursively($a_obj_type, true, $a_add_admin_objects);

        $filter = array();
        $sorted = array();

        if (!ilECSSetting::ecsConfigured()) {
            $filter = array_merge($filter, ilECSUtils::getPossibleRemoteTypes(false));
            $filter[] = 'rtst';
        }

        foreach ($subs as $subtype => $def) {
            if (in_array($def["name"], $filter)) {
                continue;
            }

            if ($objDefinition->isPlugin($subtype)) {
                $translation = ilObjectPlugin::lookupTxtById($subtype, "obj_" . $subtype);
            } elseif ($objDefinition->isSystemObject($subtype)) {
                $translation = $lng->txt("obj_" . $subtype);
            } else {
                $translation = $lng->txt('objs_' . $subtype);
            }

            $sorted[$subtype] = $def;
            $sorted[$subtype]['translation'] = $translation;
        }

        return ilArrayUtil::sortArray($sorted, 'translation', 'asc', true, true);
    }

    public static function _updateAuthMode(array $a_roles) : void
    {
        global $DIC;

        $ilDB = $DIC->database();
        foreach ($a_roles as $role_id => $auth_mode) {
            $query = "UPDATE role_data SET " .
                "auth_mode= " . $ilDB->quote($auth_mode, 'text') . " " .
                "WHERE role_id= " . $ilDB->quote($role_id, 'integer') . " ";
            $res = $ilDB->manipulate($query);
        }
    }

    public static function _getAuthMode(int $a_role_id) : string
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT auth_mode FROM role_data " .
            "WHERE role_id= " . $ilDB->quote($a_role_id, 'integer') . " ";
        $res = $ilDB->query($query);
        $row = $ilDB->fetchAssoc($res);

        return $row['auth_mode'];
    }

    /**
     * Get roles by auth mode
     * @access public
     * @param string auth mode
     * @return int[]
     */
    public static function _getRolesByAuthMode(string $a_auth_mode) : array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM role_data " .
            "WHERE auth_mode = " . $ilDB->quote($a_auth_mode, 'text');
        $res = $ilDB->query($query);
        $roles = array();
        while ($row = $ilDB->fetchObject($res)) {
            $roles[] = $row->role_id;
        }
        return $roles;
    }

    /**
     * Reset auth mode to default
     */
    public static function _resetAuthMode(string $a_auth_mode) : void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "UPDATE role_data SET auth_mode = 'default' WHERE auth_mode = " . $ilDB->quote($a_auth_mode, 'text');
        $res = $ilDB->manipulate($query);
    }

    public function __getPermissionDefinitions() : array
    {
        $operation_info = $this->rbac_review->getOperationAssignment();
        $rbac_objects = $rbac_operations = [];
        foreach ($operation_info as $info) {
            if ($this->obj_definition->getDevMode($info['type'])) {
                continue;
            }
            $rbac_objects[$info['typ_id']] = array("obj_id" => $info['typ_id'],
                                                   "type" => $info['type']
            );

            // handle plugin permission texts
            $txt = $this->obj_definition->isPlugin($info['type'])
                ? ilObjectPlugin::lookupTxtById($info['type'], $info['type'] . "_" . $info['operation'])
                : $this->lng->txt($info['type'] . "_" . $info['operation']);
            if (substr($info['operation'], 0, 7) == "create_" &&
                $this->obj_definition->isPlugin(substr($info['operation'], 7))) {
                $txt = ilObjectPlugin::lookupTxtById(
                    substr($info['operation'], 7),
                    $info['type'] . "_" . $info['operation']
                );
            }
            $rbac_operations[$info['typ_id']][$info['ops_id']] = array(
                "ops_id" => $info['ops_id'],
                "title" => $info['operation'],
                "name" => $txt
            );
        }
        return array($rbac_objects, $rbac_operations);
    }

    public static function isAutoGenerated(int $a_role_id) : bool
    {
        return substr(ilObject::_lookupTitle($a_role_id), 0, 3) == 'il_';
    }

    /**
     * Change existing objects
     * @param array filter Filter of object types (array('all') => change all objects
     */
    public function changeExistingObjects(
        int $a_start_node,
        int $a_mode,
        array $a_filter,
        array $a_exclusion_filter = array(),
        int $a_operation_mode = self::MODE_READ_OPERATIONS,
        array $a_operation_stack = []
    ) : void {
        // Get node info of subtree
        $nodes = $this->tree->getRbacSubtreeInfo($a_start_node);

        // get local policies
        $all_local_policies = $this->rbac_review->getObjectsWithStopedInheritance($this->getId());

        // filter relevant roles
        $local_policies = array();
        foreach ($all_local_policies as $lp) {
            if (isset($nodes[$lp])) {
                $local_policies[] = $lp;
            }
        }

        // Delete deprecated policies
        switch ($a_mode) {
            case self::MODE_UNPROTECTED_DELETE_LOCAL_POLICIES:
            case self::MODE_PROTECTED_DELETE_LOCAL_POLICIES:
                $local_policies = $this->deleteLocalPolicies($a_start_node, $local_policies, $a_filter);
                break;
        }
        $this->adjustPermissions(
            $a_mode,
            $nodes,
            $local_policies,
            $a_filter,
            $a_exclusion_filter,
            $a_operation_mode,
            $a_operation_stack
        );
    }

    protected function deleteLocalPolicies(int $a_start, array $a_policies, array $a_filter) : array
    {
        global $DIC;
        $rbacadmin = $DIC['rbacadmin'];

        $local_policies = array();
        foreach ($a_policies as $policy) {
            if ($policy == $a_start || $policy == SYSTEM_FOLDER_ID) {
                $local_policies[] = $policy;
                continue;
            }
            if (!in_array('all', $a_filter) && !in_array(
                ilObject::_lookupType(ilObject::_lookupObjId($policy)),
                $a_filter
            )) {
                $local_policies[] = $policy;
                continue;
            }
            $rbacadmin->deleteLocalRole($this->getId(), $policy);
        }
        return $local_policies;
    }

    /**
     */
    protected function adjustPermissions(
        int $a_mode,
        array $a_nodes,
        array $a_policies,
        array $a_filter,
        array $a_exclusion_filter = array(),
        int $a_operation_mode = self::MODE_READ_OPERATIONS,
        array $a_operation_stack = []
    ) : void {
        $operation_stack = array();
        $policy_stack = array();
        $node_stack = array();

        $start_node = current($a_nodes);
        $node_stack[] = $start_node;
        $this->updatePolicyStack($policy_stack, $start_node['child']);

        if ($a_operation_mode == self::MODE_READ_OPERATIONS) {
            $this->updateOperationStack($operation_stack, $start_node['child'], true);
        } else {
            $operation_stack = $a_operation_stack;
        }

        $this->logger->debug('adjust permissions operation stack');
        $this->logger->dump($operation_stack, ilLogLevel::DEBUG);

        $rbac_log_active = ilRbacLog::isActive();

        $local_policy = false;
        foreach ($a_nodes as $node) {
            $cmp_node = end($node_stack);
            while ($relation = $this->tree->getRelationOfNodes($node, $cmp_node)) {
                switch ($relation) {
                    case ilTree::RELATION_NONE:
                    case ilTree::RELATION_SIBLING:
                        $this->logger->debug('Handling sibling/none relation.');
                        array_pop($operation_stack);
                        array_pop($policy_stack);
                        array_pop($node_stack);
                        $cmp_node = end($node_stack);
                        $local_policy = false;
                        break;

                    case ilTree::RELATION_CHILD:
                    case ilTree::RELATION_EQUALS:
                    case ilTree::RELATION_PARENT:
                    default:
                        $this->logger->debug('Handling child/equals/parent ' . $relation);
                        break 2;
                }
            }

            if ($local_policy) {
                continue;
            }

            // Start node => set permissions and continue
            if ($node['child'] == $start_node['child']) {
                if ($this->isHandledObjectType($a_filter, $a_exclusion_filter, $node['type'])) {
                    if ($rbac_log_active) {
                        $rbac_log_roles = $this->rbac_review->getParentRoleIds($node['child'], false);
                        $rbac_log_old = ilRbacLog::gatherFaPa((int) $node['child'], array_keys($rbac_log_roles));
                    }

                    // Set permissions
                    $perms = end($operation_stack);
                    $this->changeExistingObjectsGrantPermissions(
                        $this->getId(),
                        (array) ($perms[$node['type']] ?? []),
                        $node['child'],
                        $a_operation_mode
                    );

                    if ($rbac_log_active) {
                        $rbac_log_new = ilRbacLog::gatherFaPa((int) $node['child'], array_keys($rbac_log_roles));
                        $rbac_log = ilRbacLog::diffFaPa($rbac_log_old, $rbac_log_new);
                        ilRbacLog::add(ilRbacLog::EDIT_TEMPLATE_EXISTING, $node['child'], $rbac_log);
                    }
                }
                continue;
            }

            // Node has local policies => update permission stack and continue
            if (in_array($node['child'], $a_policies) && $node['child'] != SYSTEM_FOLDER_ID) {
                $local_policy = true;
                $this->updatePolicyStack($policy_stack, $node['child']);
                $this->updateOperationStack($operation_stack, $node['child']);
                $node_stack[] = $node;
                continue;
            }

            // Continue if this object type is not in filter
            if (!$this->isHandledObjectType($a_filter, $a_exclusion_filter, $node['type'])) {
                continue;
            }

            if ($rbac_log_active) {
                $rbac_log_roles = $this->rbac_review->getParentRoleIds($node['child'], false);
                $rbac_log_old = ilRbacLog::gatherFaPa((int) $node['child'], array_keys($rbac_log_roles));
            }

            // Node is course or group => create permission intersection
            if (
                ($a_mode == self::MODE_UNPROTECTED_DELETE_LOCAL_POLICIES || $a_mode == self::MODE_UNPROTECTED_KEEP_LOCAL_POLICIES) &&
                ($node['type'] == 'crs' || $node['type'] == 'grp')
            ) {
                // Copy role permission intersection
                $perms = end($operation_stack);
                $this->createPermissionIntersection($policy_stack, $perms[$node['type']], $node['child'], $node['type']);
                if ($this->updateOperationStack($operation_stack, $node['child'])) {
                    $this->updatePolicyStack($policy_stack, $node['child']);
                    $node_stack[] = $node;
                }
            }

            // Set permission
            $perms = end($operation_stack);
            $this->changeExistingObjectsGrantPermissions(
                $this->getId(),
                (array) $perms[$node['type']],
                $node['child'],
                $a_operation_mode
            );
            if ($rbac_log_active) {
                $rbac_log_new = ilRbacLog::gatherFaPa((int) $node['child'], array_keys($rbac_log_roles));
                $rbac_log = ilRbacLog::diffFaPa($rbac_log_old, $rbac_log_new);
                ilRbacLog::add(ilRbacLog::EDIT_TEMPLATE_EXISTING, $node['child'], $rbac_log);
            }
        }
    }

    protected function changeExistingObjectsGrantPermissions(
        int $a_role_id,
        array $a_permissions,
        int $a_ref_id,
        int $a_operation_mode
    ) : void {
        global $DIC;

        $admin = $DIC->rbac()->admin();
        $review = $DIC->rbac()->review();
        if ($a_operation_mode == self::MODE_READ_OPERATIONS) {
            $admin->grantPermission(
                $a_role_id,
                $a_permissions,
                $a_ref_id
            );
        } elseif ($a_operation_mode == self::MODE_ADD_OPERATIONS) {
            $current_operations = $review->getRoleOperationsOnObject(
                $a_role_id,
                $a_ref_id
            );
            $this->logger->debug('Current operations');
            $this->logger->dump($current_operations);

            $new_ops = array_unique(array_merge($a_permissions, $current_operations));
            $this->logger->debug('New operations');
            $this->logger->dump($new_ops);

            $admin->grantPermission(
                $a_role_id,
                $new_ops,
                $a_ref_id
            );
        } elseif ($a_operation_mode == self::MODE_REMOVE_OPERATIONS) {
            $current_operations = $review->getRoleOperationsOnObject(
                $a_role_id,
                $a_ref_id
            );
            $this->logger->debug('Current operations');
            $this->logger->dump($current_operations);

            $new_ops = array_diff($current_operations, $a_permissions);

            $admin->grantPermission(
                $a_role_id,
                $new_ops,
                $a_ref_id
            );
        }
    }

    protected function isHandledObjectType(array $a_filter, array $a_exclusion_filter, string $a_type) : bool
    {
        if (in_array($a_type, $a_exclusion_filter)) {
            return false;
        }

        if (in_array('all', $a_filter)) {
            return true;
        }
        return in_array($a_type, $a_filter);
    }

    /**
     * Update operation stack
     */
    protected function updateOperationStack(
        array &$a_stack,
        int $a_node,
        bool $a_init = false
    ) : bool {
        $has_policies = null;

        if ($a_node == ROOT_FOLDER_ID) {
            $has_policies = true;
            $policy_origin = ROLE_FOLDER_ID;
        } else {
            $has_policies = $this->rbac_review->getLocalPolicies($a_node);
            $policy_origin = $a_node;

            if ($a_init) {
                $parent_roles = $this->rbac_review->getParentRoleIds($a_node, false);
                if ($parent_roles[$this->getId()]) {
                    $a_stack[] = $this->rbac_review->getAllOperationsOfRole(
                        $this->getId(),
                        $parent_roles[$this->getId()]['parent']
                    );
                }
                return true;
            }
        }

        if (!$has_policies) {
            return false;
        }

        $a_stack[] = $this->rbac_review->getAllOperationsOfRole(
            $this->getId(),
            $policy_origin
        );
        return true;
    }

    protected function updatePolicyStack(array &$a_stack, int $a_node) : bool
    {
        $has_policies = null;

        if ($a_node == ROOT_FOLDER_ID) {
            $has_policies = true;
            $policy_origin = ROLE_FOLDER_ID;
        } else {
            $has_policies = $this->rbac_review->getLocalPolicies($a_node);
            $policy_origin = $a_node;
        }

        if (!$has_policies) {
            return false;
        }

        $a_stack[] = $policy_origin;
        return true;
    }

    /**
     * Create permission intersection
     */
    protected function createPermissionIntersection(
        array $policy_stack,
        array $a_current_ops,
        int $a_id,
        string $a_type
    ) : void {
        static $course_non_member_id = null;
        static $group_non_member_id = null;
        static $group_open_id = null;
        static $group_closed_id = null;

        $template_id = 0;
        // Get template id
        switch ($a_type) {
            case 'grp':
                $type = ilObjGroup::lookupGroupTye(ilObject::_lookupObjId($a_id));
                switch ($type) {
                    case ilGroupConstants::GRP_TYPE_CLOSED:
                        if (!$group_closed_id) {
                            $query = "SELECT obj_id FROM object_data WHERE type='rolt' AND title='il_grp_status_closed'";
                            $res = $this->db->query($query);
                            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                                $group_closed_id = $row->obj_id;
                            }
                        }
                        $template_id = $group_closed_id;
                        #var_dump("GROUP CLOSED id:" . $template_id);
                        break;

                    case ilGroupConstants::GRP_TYPE_OPEN:
                    default:
                        if (!$group_open_id) {
                            $query = "SELECT obj_id FROM object_data WHERE type='rolt' AND title='il_grp_status_open'";
                            $res = $this->db->query($query);
                            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                                $group_open_id = $row->obj_id;
                            }
                        }
                        $template_id = $group_open_id;
                        break;
                }
                break;

            case 'crs':
                if (!$course_non_member_id) {
                    $query = "SELECT obj_id FROM object_data WHERE type='rolt' AND title='il_crs_non_member'";
                    $res = $this->db->query($query);
                    while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                        $course_non_member_id = $row->obj_id;
                    }
                }
                $template_id = $course_non_member_id;
                break;
        }

        // Create intersection template permissions
        if ($template_id) {
            $this->rbac_admin->copyRolePermissionIntersection(
                $template_id,
                ROLE_FOLDER_ID,
                $this->getId(),
                end($policy_stack),
                $a_id,
                $this->getId()
            );
        } else {
        }
        if ($a_id && !$GLOBALS['DIC']['rbacreview']->isRoleAssignedToObject($this->getId(), $a_id)) {
            $this->rbac_admin->assignRoleToFolder($this->getId(), $a_id, "n");
        }
    }
}
