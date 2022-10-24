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
 * Class ilRbacAdmin
 *  Core functions for role based access control.
 *  Creation and maintenance of Relations.
 *  The main relations of Rbac are user <-> role (UR) assignment relation and the permission <-> role (PR) assignment relation.
 *  This class contains methods to 'create' and 'delete' instances of the (UR) relation e.g.: assignUser(), deassignUser()
 *  Required methods for the PR relation are grantPermission(), revokePermission()
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesAccessControl
 */
class ilRbacAdmin
{
    protected ilDBInterface $db;
    protected ilRbacReview $rbacreview;
    protected ilLogger $logger;

    /**
     * Constructor
     * @access    public
     */
    public function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->rbacreview = $DIC->rbac()->review();
        $this->logger = $DIC->logger()->ac();
    }

    public function setBlockedStatus(int $a_role_id, int $a_ref_id, bool $a_blocked_status): void
    {
        ilLoggerFactory::getLogger('crs')->logStack();
        $query = 'UPDATE rbac_fa set blocked = ' . $this->db->quote($a_blocked_status, 'integer') . ' ' .
            'WHERE rol_id = ' . $this->db->quote($a_role_id, 'integer') . ' ' .
            'AND parent = ' . $this->db->quote($a_ref_id, 'integer');
        $this->db->manipulate($query);
    }

    /**
     * deletes a user from rbac_ua
     * all user <-> role relations are deleted
     */
    public function removeUser(int $a_usr_id): void
    {
        foreach ($this->rbacreview->assignedRoles($a_usr_id) as $role_id) {
            $this->deassignUser($role_id, $a_usr_id);
        }
        $query = "DELETE FROM rbac_ua WHERE usr_id = " . $this->db->quote($a_usr_id, 'integer');
        $res = $this->db->manipulate($query);
    }

    /**
     * Deletes a role and deletes entries in rbac_pa, rbac_templates, rbac_ua, rbac_fa
     */
    public function deleteRole(int $a_rol_id, int $a_ref_id): void
    {
        if ($a_rol_id == SYSTEM_ROLE_ID) {
            $this->logger->logStack(ilLogLevel::DEBUG);
            throw new DomainException('System administrator role is not deletable.');
        }

        $mapping = ilLDAPRoleGroupMapping::_getInstance();
        $mapping->deleteRole($a_rol_id);

        // TODO: check assigned users before deletion
        // This is done in ilObjRole. Should be better moved to this place?

        // delete user assignements
        $query = "DELETE FROM rbac_ua " .
            "WHERE rol_id = " . $this->db->quote($a_rol_id, 'integer');
        $res = $this->db->manipulate($query);

        // delete permission assignments
        $query = "DELETE FROM rbac_pa " .
            "WHERE rol_id = " . $this->db->quote($a_rol_id, 'integer') . " ";
        $res = $this->db->manipulate($query);

        //delete rbac_templates and rbac_fa
        $this->deleteLocalRole($a_rol_id);
    }

    /**
     * Deletes a template from role folder and deletes all entries in rbac_templates, rbac_fa
     */
    public function deleteTemplate(int $a_obj_id): void
    {
        $query = 'DELETE FROM rbac_templates ' .
            'WHERE rol_id = ' . $this->db->quote($a_obj_id, 'integer');
        $res = $this->db->manipulate($query);

        $query = 'DELETE FROM rbac_fa ' .
            'WHERE rol_id = ' . $this->db->quote($a_obj_id, 'integer');
        $res = $this->db->manipulate($query);
    }

    /**
     * Deletes a local role and entries in rbac_fa and rbac_templates
     */
    public function deleteLocalRole(int $a_rol_id, int $a_ref_id = 0): void
    {
        // exclude system role from rbac
        if ($a_rol_id == SYSTEM_ROLE_ID) {
            $this->logger->logStack(ilLogLevel::NOTICE);
            $this->logger->notice('System administrator role is not deletable.');
            return;
        }

        $clause = '';
        if ($a_ref_id != 0) {
            $clause = 'AND parent = ' . $this->db->quote($a_ref_id, 'integer') . ' ';
        }

        $query = 'DELETE FROM rbac_fa ' .
            'WHERE rol_id = ' . $this->db->quote($a_rol_id, 'integer') . ' ' .
            $clause;
        $res = $this->db->manipulate($query);

        $query = 'DELETE FROM rbac_templates ' .
            'WHERE rol_id = ' . $this->db->quote($a_rol_id, 'integer') . ' ' .
            $clause;
        $res = $this->db->manipulate($query);
    }

    public function assignUserLimited(
        int $a_role_id,
        int $a_usr_id,
        int $a_limit,
        array $a_limited_roles = []
    ): void {
        $ilDB = $this->db;
        $ilAtomQuery = $this->db->buildAtomQuery();
        $ilAtomQuery->addTableLock('rbac_ua');
        $ilAtomQuery->addQueryCallable(
            function (ilDBInterface $ilDB) use (&$ret, $a_role_id, $a_usr_id, $a_limit, $a_limited_roles): void {
                $ret = true;
                $limit_query = 'SELECT COUNT(*) num FROM rbac_ua ' .
                    'WHERE ' . $ilDB->in('rol_id', (array) $a_limited_roles, false, 'integer');
                $res = $ilDB->query($limit_query);
                $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
                if ($row->num >= $a_limit) {
                    $ret = false;
                    return;
                }

                $query = "INSERT INTO rbac_ua (usr_id, rol_id) " .
                    "VALUES (" .
                    $ilDB->quote($a_usr_id, 'integer') . "," . $ilDB->quote($a_role_id, 'integer') .
                    ")";
                $res = $ilDB->manipulate($query);
            }
        );
        $ilAtomQuery->run();

        if (!$ret) {
            return;
        }

        $this->rbacreview->setAssignedCacheEntry($a_role_id, $a_usr_id, true);
        $mapping = ilLDAPRoleGroupMapping::_getInstance();
        $mapping->assign($a_role_id, $a_usr_id);
    }

    /**
     * Assigns an user to a role. Update of table rbac_ua
     */
    public function assignUser(int $a_rol_id, int $a_usr_id): void
    {
        // check if already assigned user id and role_id
        $alreadyAssigned = $this->rbacreview->isAssigned($a_usr_id, $a_rol_id);

        // enhanced: only if we haven't had this role for this user
        if (!$alreadyAssigned) {
            $query = "INSERT INTO rbac_ua (usr_id, rol_id) " .
                "VALUES (" . $this->db->quote($a_usr_id, 'integer') . "," . $this->db->quote(
                    $a_rol_id,
                    'integer'
                ) . ")";
            $res = $this->db->manipulate($query);

            $this->rbacreview->setAssignedCacheEntry($a_rol_id, $a_usr_id, true);
        }

        $mapping = ilLDAPRoleGroupMapping::_getInstance();
        $mapping->assign($a_rol_id, $a_usr_id);

        $ref_id = $this->rbacreview->getObjectReferenceOfRole($a_rol_id);
        $obj_id = ilObject::_lookupObjId($ref_id);
        $type = ilObject::_lookupType($obj_id);

        if (!$alreadyAssigned) {
            ilLoggerFactory::getInstance()->getLogger('ac')->debug('Raise event assign user');
            $GLOBALS['DIC']['ilAppEventHandler']->raise(
                'Services/AccessControl',
                'assignUser',
                array(
                    'obj_id' => $obj_id,
                    'usr_id' => $a_usr_id,
                    'role_id' => $a_rol_id,
                    'type' => $type
                )
            );
        }
    }

    /**
     * Deassigns a user from a role. Update of table rbac_ua
     */
    public function deassignUser(int $a_rol_id, int $a_usr_id): void
    {
        $query = "DELETE FROM rbac_ua " .
            "WHERE usr_id = " . $this->db->quote($a_usr_id, 'integer') . " " .
            "AND rol_id = " . $this->db->quote($a_rol_id, 'integer') . " ";
        $res = $this->db->manipulate($query);

        $this->rbacreview->setAssignedCacheEntry($a_rol_id, $a_usr_id, false);

        $mapping = ilLDAPRoleGroupMapping::_getInstance();
        $mapping->deassign($a_rol_id, $a_usr_id);

        if ($res) {
            $ref_id = $GLOBALS['DIC']['rbacreview']->getObjectReferenceOfRole($a_rol_id);
            $obj_id = ilObject::_lookupObjId($ref_id);
            $type = ilObject::_lookupType($obj_id);

            ilLoggerFactory::getInstance()->getLogger('ac')->debug('Raise event deassign user');
            $GLOBALS['DIC']['ilAppEventHandler']->raise('Services/AccessControl', 'deassignUser', array(
                'obj_id' => $obj_id,
                'usr_id' => $a_usr_id,
                'role_id' => $a_rol_id,
                'type' => $type,
            ));
        }
    }

    /**
     * Grants a permission to an object and a specific role. Update of table rbac_pa
     */
    public function grantPermission(int $a_rol_id, array $a_ops, int $a_ref_id): void
    {
        // exclude system role from rbac
        if ($a_rol_id == SYSTEM_ROLE_ID) {
            $this->logger->logStack(ilLogLevel::DEBUG);
            return;
        }

        // convert all values to integer
        foreach ($a_ops as $key => $operation) {
            $a_ops[$key] = (int) $operation;
        }

        $ops_ids = serialize($a_ops);

        $query = 'DELETE FROM rbac_pa ' .
            'WHERE rol_id = %s ' .
            'AND ref_id = %s';
        $res = $this->db->queryF(
            $query,
            array('integer', 'integer'),
            array($a_rol_id, $a_ref_id)
        );

        if ($a_ops === []) {
            return;
        }

        $query = "INSERT INTO rbac_pa (rol_id,ops_id,ref_id) " .
            "VALUES " .
            "(" . $this->db->quote($a_rol_id, 'integer') . "," . $this->db->quote(
                $ops_ids,
                'text'
            ) . "," . $this->db->quote($a_ref_id, 'integer') . ")";
        $res = $this->db->manipulate($query);
    }

    /**
     * Revokes permissions of an object of one role. Update of table rbac_pa.
     * Revokes all permission for all roles for that object (with this reference).
     * When a role_id is given this applies only to that role
     */
    public function revokePermission(int $a_ref_id, int $a_rol_id = 0, bool $a_keep_protected = true): void
    {
        if ($a_rol_id == SYSTEM_ROLE_ID) {
            $this->logger->logStack(ilLogLevel::DEBUG);
            return;
        }

        // bypass protected status of roles
        if ($a_keep_protected != true) {
            if ($a_rol_id) {
                $and1 = " AND rol_id = " . $this->db->quote($a_rol_id, 'integer') . " ";
            } else {
                $and1 = "";
            }

            $query = "DELETE FROM rbac_pa " .
                "WHERE ref_id = " . $this->db->quote($a_ref_id, 'integer') .
                $and1;
            $res = $this->db->manipulate($query);
            return;
        }

        // consider protected status of roles

        // in any case, get all roles in scope first
        $roles_in_scope = $this->rbacreview->getParentRoleIds($a_ref_id);

        if (!$a_rol_id) {
            $role_ids = [];

            foreach ($roles_in_scope as $role) {
                if ($role['protected'] == true) {
                    continue;
                }

                $role_ids[] = $role['obj_id'];
            }

            // return if no role in array
            if ($role_ids === []) {
                return;
            }

            $query = 'DELETE FROM rbac_pa ' .
                'WHERE ' . $this->db->in('rol_id', $role_ids, false, 'integer') . ' ' .
                'AND ref_id = ' . $this->db->quote($a_ref_id, 'integer');
            $res = $this->db->manipulate($query);
        } else {
            // exclude protected permission settings from revoking
            if ($roles_in_scope[$a_rol_id]['protected'] == true) {
                return;
            }

            $query = "DELETE FROM rbac_pa " .
                "WHERE ref_id = " . $this->db->quote($a_ref_id, 'integer') . " " .
                "AND rol_id = " . $this->db->quote($a_rol_id, 'integer') . " ";
            $res = $this->db->manipulate($query);
        }
    }

    /**
     * Revoke subtree permissions
     */
    public function revokeSubtreePermissions(int $a_ref_id, int $a_role_id): void
    {
        $query = 'DELETE FROM rbac_pa ' .
            'WHERE ref_id IN ' .
            '( ' . $GLOBALS['DIC']['tree']->getSubTreeQuery($a_ref_id, array('child')) . ' ) ' .
            'AND rol_id = ' . $this->db->quote($a_role_id, 'integer');

        $this->db->manipulate($query);
    }

    /**
     * Delete all template permissions of subtree nodes
     */
    public function deleteSubtreeTemplates(int $a_ref_id, int $a_rol_id): void
    {
        $query = 'DELETE FROM rbac_templates ' .
            'WHERE parent IN ( ' .
            $GLOBALS['DIC']['tree']->getSubTreeQuery($a_ref_id, array('child')) . ' ) ' .
            'AND rol_id = ' . $this->db->quote($a_rol_id, 'integer');

        $this->db->manipulate($query);

        $query = 'DELETE FROM rbac_fa ' .
            'WHERE parent IN ( ' .
            $GLOBALS['DIC']['tree']->getSubTreeQuery($a_ref_id, array('child')) . ' ) ' .
            'AND rol_id = ' . $this->db->quote($a_rol_id, 'integer');

        $this->db->manipulate($query);
    }

    /**
     * Revokes permissions of a LIST of objects of ONE role. Update of table rbac_pa.
     */
    public function revokePermissionList(array $a_ref_ids, int $a_rol_id): void
    {
        // exclude system role from rbac
        if ($a_rol_id == SYSTEM_ROLE_ID) {
            $this->logger->logStack(ilLogLevel::DEBUG);
            return;
        }
        $query = "DELETE FROM rbac_pa " .
            "WHERE " . $this->db->in('ref_id', $a_ref_ids, false, 'integer') . ' ' .
            "AND rol_id = " . $this->db->quote($a_rol_id, 'integer');
        $res = $this->db->manipulate($query);
    }

    /**
     * Copies template permissions and permission of one role to another.
     */
    public function copyRolePermissions(
        int $a_source_id,
        int $a_source_parent,
        int $a_dest_parent,
        int $a_dest_id,
        bool $a_consider_protected = true
    ): void {
        // Copy template permissions
        $this->copyRoleTemplatePermissions(
            $a_source_id,
            $a_source_parent,
            $a_dest_parent,
            $a_dest_id,
            $a_consider_protected
        );
        $ops = $this->rbacreview->getRoleOperationsOnObject($a_source_id, $a_source_parent);

        $this->revokePermission($a_dest_parent, $a_dest_id);
        $this->grantPermission($a_dest_id, $ops, $a_dest_parent);
    }

    /**
     * Copies template permissions of one role to another.
     * It's also possible to copy template permissions from/to RoleTemplateObject
     */
    public function copyRoleTemplatePermissions(
        int $a_source_id,
        int $a_source_parent,
        int $a_dest_parent,
        int $a_dest_id,
        bool $a_consider_protected = true
    ): void {
        // exclude system role from rbac
        if ($a_dest_id == SYSTEM_ROLE_ID) {
            $this->logger->logStack(ilLogLevel::DEBUG);
            return;
        }

        // Read operations
        $query = 'SELECT * FROM rbac_templates ' .
            'WHERE rol_id = ' . $this->db->quote($a_source_id, 'integer') . ' ' .
            'AND parent = ' . $this->db->quote($a_source_parent, 'integer');
        $res = $this->db->query($query);
        $operations = array();
        $rownum = 0;
        while ($row = $this->db->fetchObject($res)) {
            $operations[$rownum]['type'] = $row->type;
            $operations[$rownum]['ops_id'] = $row->ops_id;
            $rownum++;
        }

        // Delete target permissions
        $query = 'DELETE FROM rbac_templates WHERE rol_id = ' . $this->db->quote($a_dest_id, 'integer') . ' ' .
            'AND parent = ' . $this->db->quote($a_dest_parent, 'integer');
        $res = $this->db->manipulate($query);

        foreach ($operations as $op) {
            $query = 'INSERT INTO rbac_templates (rol_id,type,ops_id,parent) ' .
                'VALUES (' .
                $this->db->quote($a_dest_id, 'integer') . "," .
                $this->db->quote($op['type'], 'text') . "," .
                $this->db->quote($op['ops_id'], 'integer') . "," .
                $this->db->quote($a_dest_parent, 'integer') . ")";
            $this->db->manipulate($query);
        }

        // copy also protection status if applicable
        if ($a_consider_protected == true) {
            if ($this->rbacreview->isProtected($a_source_parent, $a_source_id)) {
                $this->setProtected($a_dest_parent, $a_dest_id, 'y');
            }
        }
    }

    /**
     * Copies the intersection of the template permissions of two roles to a
     * third role.
     */
    public function copyRolePermissionIntersection(
        int $a_source1_id,
        int $a_source1_parent,
        int $a_source2_id,
        int $a_source2_parent,
        int $a_dest_parent,
        int $a_dest_id
    ): void {
        // exclude system role from rbac
        if ($a_dest_id == SYSTEM_ROLE_ID) {
            $this->logger->logStack(ilLogLevel::DEBUG);
            return;
        }
        $query = "SELECT s1.type, s1.ops_id " .
            "FROM rbac_templates s1, rbac_templates s2 " .
            "WHERE s1.rol_id = " . $this->db->quote($a_source1_id, 'integer') . " " .
            "AND s1.parent = " . $this->db->quote($a_source1_parent, 'integer') . " " .
            "AND s2.rol_id = " . $this->db->quote($a_source2_id, 'integer') . " " .
            "AND s2.parent = " . $this->db->quote($a_source2_parent, 'integer') . " " .
            "AND s1.type = s2.type " .
            "AND s1.ops_id = s2.ops_id";

        $res = $this->db->query($query);
        $operations = array();
        $rowNum = 0;
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $operations[$rowNum]['type'] = $row->type;
            $operations[$rowNum]['ops_id'] = $row->ops_id;

            $rowNum++;
        }

        // Delete template permissions of target
        $query = 'DELETE FROM rbac_templates WHERE rol_id = ' . $this->db->quote($a_dest_id, 'integer') . ' ' .
            'AND parent = ' . $this->db->quote($a_dest_parent, 'integer');
        $res = $this->db->manipulate($query);

        $query = 'INSERT INTO rbac_templates (rol_id,type,ops_id,parent) ' .
            'VALUES (?,?,?,?)';
        $sta = $this->db->prepareManip($query, array('integer', 'text', 'integer', 'integer'));
        foreach ($operations as $set) {
            $this->db->execute($sta, array(
                $a_dest_id,
                $set['type'],
                $set['ops_id'],
                $a_dest_parent
            ));
        }
    }

    public function copyRolePermissionUnion(
        int $a_source1_id,
        int $a_source1_parent,
        int $a_source2_id,
        int $a_source2_parent,
        int $a_dest_id,
        int $a_dest_parent
    ): void {

        // exclude system role from rbac
        if ($a_dest_id == SYSTEM_ROLE_ID) {
            $this->logger->logStack(ilLogLevel::DEBUG);
            return;
        }
        $s1_ops = $this->rbacreview->getAllOperationsOfRole($a_source1_id, $a_source1_parent);
        $s2_ops = $this->rbacreview->getAllOperationsOfRole($a_source2_id, $a_source2_parent);
        $this->deleteRolePermission($a_dest_id, $a_dest_parent);

        foreach ($s1_ops as $type => $ops) {
            foreach ($ops as $op) {
                // insert all permission of source 1
                // #15469
                $query = 'INSERT INTO rbac_templates (rol_id,type,ops_id,parent) ' .
                    'VALUES( ' .
                    $this->db->quote($a_dest_id, 'integer') . ', ' .
                    $this->db->quote($type, 'text') . ', ' .
                    $this->db->quote($op, 'integer') . ', ' .
                    $this->db->quote($a_dest_parent, 'integer') . ' ' .
                    ')';
                $this->db->manipulate($query);
            }
        }

        // and the other direction...
        foreach ($s2_ops as $type => $ops) {
            foreach ($ops as $op) {
                if (!isset($s1_ops[$type]) || !in_array($op, $s1_ops[$type])) {
                    $query = 'INSERT INTO rbac_templates (rol_id,type,ops_id,parent) ' .
                        'VALUES( ' .
                        $this->db->quote($a_dest_id, 'integer') . ', ' .
                        $this->db->quote($type, 'text') . ', ' .
                        $this->db->quote($op, 'integer') . ', ' .
                        $this->db->quote($a_dest_parent, 'integer') . ' ' .
                        ')';
                    $this->db->manipulate($query);
                }
            }
        }
    }

    /**
     * Subtract role permissions
     */
    public function copyRolePermissionSubtract(
        int $a_source_id,
        int $a_source_parent,
        int $a_dest_id,
        int $a_dest_parent
    ): void {
        if ($a_dest_id == SYSTEM_ROLE_ID) {
            $this->logger->logStack(ilLogLevel::DEBUG);
            return;
        }
        $s1_ops = $this->rbacreview->getAllOperationsOfRole($a_source_id, $a_source_parent);
        $d_ops = $this->rbacreview->getAllOperationsOfRole($a_dest_id, $a_dest_parent);

        foreach ($s1_ops as $type => $ops) {
            foreach ($ops as $op) {
                if (isset($d_ops[$type]) && in_array($op, $d_ops[$type])) {
                    $query = 'DELETE FROM rbac_templates ' .
                        'WHERE rol_id = ' . $this->db->quote($a_dest_id, 'integer') . ' ' .
                        'AND type = ' . $this->db->quote($type, 'text') . ' ' .
                        'AND ops_id = ' . $this->db->quote($op, 'integer') . ' ' .
                        'AND parent = ' . $this->db->quote($a_dest_parent, 'integer');
                    $this->db->manipulate($query);
                }
            }
        }
    }

    /**
     * Deletes all entries of a template. If an object type is given for third parameter only
     * the entries for that object type are deleted
     * Update of table rbac_templates.
     */
    public function deleteRolePermission(
        int $a_rol_id,
        int $a_ref_id,
        ?string $a_type = null
    ): void {
        if ($a_rol_id == SYSTEM_ROLE_ID) {
            $this->logger->logStack(ilLogLevel::DEBUG);
            return;
        }
        $and_type = '';
        if ($a_type !== null) {
            $and_type = " AND type=" . $this->db->quote($a_type, 'text') . " ";
        }
        $query = 'DELETE FROM rbac_templates ' .
            'WHERE rol_id = ' . $this->db->quote($a_rol_id, 'integer') . ' ' .
            'AND parent = ' . $this->db->quote($a_ref_id, 'integer') . ' ' .
            $and_type;
        $res = $this->db->manipulate($query);
    }

    /**
     * Inserts template permissions in rbac_templates for an specific object type.
     *  Update of table rbac_templates
     */
    public function setRolePermission(int $a_rol_id, string $a_type, array $a_ops, int $a_ref_id): void
    {
        if ($a_rol_id == SYSTEM_ROLE_ID) {
            $this->logger->logStack();
            return;
        }
        foreach ($a_ops as $op) {
            $this->db->replace(
                'rbac_templates',
                [
                    'rol_id' => ['integer', $a_rol_id],
                    'type' => ['text', $a_type],
                    'ops_id' => ['integer', $op],
                    'parent' => ['integer', $a_ref_id]
                ],
                []
            );
        }
    }

    /**
     * Assigns a role to a role folder
     * A role folder is an object to store roles.
     * Every role is assigned to minimum one role folder
     * If the inheritance of a role is stopped, a new role template will created, and the role is assigned to
     * minimum two role folders. All roles with stopped inheritance need the flag '$a_assign = false'
     */
    public function assignRoleToFolder(
        int $a_rol_id,
        int $a_parent,
        string $a_assign = "y"
    ): void {
        if ($a_rol_id == SYSTEM_ROLE_ID) {
            throw new DomainException('System administrator role is not writeable.');
        }
        // if a wrong value is passed, always set assign to "n"
        if ($a_assign != "y") {
            $a_assign = "n";
        }

        // check if already assigned
        $query = 'SELECT rol_id FROM rbac_fa ' .
            'WHERE rol_id = ' . $this->db->quote($a_rol_id, 'integer') . ' ' .
            'AND parent = ' . $this->db->quote($a_parent, 'integer');
        $res = $this->db->query($query);
        if ($res->numRows()) {
            return;
        }
        $query = sprintf(
            'INSERT INTO rbac_fa (rol_id, parent, assign, protected) ' .
            'VALUES (%s,%s,%s,%s)',
            $this->db->quote($a_rol_id, 'integer'),
            $this->db->quote($a_parent, 'integer'),
            $this->db->quote($a_assign, 'text'),
            $this->db->quote('n', 'text')
        );
        $res = $this->db->manipulate($query);
    }

    /**
     * Assign an existing operation to an object
     *  Update of rbac_ta.
     */
    public function assignOperationToObject(int $a_type_id, int $a_ops_id): void
    {
        $query = "INSERT INTO rbac_ta (typ_id, ops_id) " .
            "VALUES(" . $this->db->quote($a_type_id, 'integer') . "," . $this->db->quote($a_ops_id, 'integer') . ")";
        $res = $this->db->manipulate($query);
    }

    /**
     * Deassign an existing operation from an object
     * Update of rbac_ta
     */
    public function deassignOperationFromObject(int $a_type_id, int $a_ops_id): void
    {
        $query = "DELETE FROM rbac_ta " .
            "WHERE typ_id = " . $this->db->quote($a_type_id, 'integer') . " " .
            "AND ops_id = " . $this->db->quote($a_ops_id, 'integer');
        $res = $this->db->manipulate($query);
    }

    /**
     * Set protected
     */
    public function setProtected(int $a_ref_id, int $a_role_id, string $a_value): void
    {
        // ref_id not used yet. protected permission acts 'global' for each role,
        // regardless of any broken inheritance before
        $query = 'UPDATE rbac_fa ' .
            'SET protected = ' . $this->db->quote($a_value, 'text') . ' ' .
            'WHERE rol_id = ' . $this->db->quote($a_role_id, 'integer');
        $res = $this->db->manipulate($query);
    }

    /**
     * Copy local roles
     * This method creates a copy of all local role.
     * Note: auto generated roles are excluded
     */
    public function copyLocalRoles(int $a_source_id, int $a_target_id): void
    {
        $real_local = [];
        foreach ($this->rbacreview->getRolesOfRoleFolder($a_source_id, false) as $role_data) {
            $title = ilObject::_lookupTitle($role_data);
            if (substr($title, 0, 3) == 'il_') {
                continue;
            }
            $real_local[] = $role_data;
        }
        if ($real_local === []) {
            return;
        }
        // Create role folder
        foreach ($real_local as $role) {
            $orig = new ilObjRole($role);
            $orig->read();

            $roleObj = new ilObjRole();
            $roleObj->setTitle($orig->getTitle());
            $roleObj->setDescription($orig->getDescription());
            $roleObj->setImportId($orig->getImportId());
            $roleObj->create();

            $this->assignRoleToFolder($roleObj->getId(), $a_target_id, "y");
            $this->copyRolePermissions($role, $a_source_id, $a_target_id, $roleObj->getId(), true);
        }
    }

    public function initIntersectionPermissions(
        int $a_ref_id,
        int $a_role_id,
        int $a_role_parent,
        int $a_template_id,
        int $a_template_parent
    ): void {
        if ($this->rbacreview->isProtected($a_role_parent, $a_role_id)) {
            // Assign object permissions
            $new_ops = $this->rbacreview->getOperationsOfRole(
                $a_role_id,
                ilObject::_lookupType($a_ref_id, true),
                $a_role_parent
            );

            // set new permissions for object
            $this->grantPermission(
                $a_role_id,
                (array) $new_ops,
                $a_ref_id
            );
            return;
        }
        if (!$a_template_id) {
            ilLoggerFactory::getLogger('ac')->info('No template id given. Aborting.');
            return;
        }
        // create template permission intersection
        $this->copyRolePermissionIntersection(
            $a_template_id,
            $a_template_parent,
            $a_role_id,
            $a_role_parent,
            $a_ref_id,
            $a_role_id
        );

        // assign role to folder
        $this->assignRoleToFolder(
            $a_role_id,
            $a_ref_id,
            'n'
        );

        // Assign object permissions
        $new_ops = $this->rbacreview->getOperationsOfRole(
            $a_role_id,
            ilObject::_lookupType($a_ref_id, true),
            $a_ref_id
        );

        // revoke existing permissions
        $this->revokePermission($a_ref_id, $a_role_id);

        // set new permissions for object
        $this->grantPermission(
            $a_role_id,
            (array) $new_ops,
            $a_ref_id
        );
    }

    /**
     * Apply didactic templates after object movement
     * @deprecated since version 5.1.0 will be removed with 5.4 and implemented using event handler
     * @todo       implement using event handler
     */
    protected function applyMovedObjectDidacticTemplates(int $a_ref_id, int $a_old_parent): void
    {
        $tpl_id = ilDidacticTemplateObjSettings::lookupTemplateId($a_ref_id);
        if (!$tpl_id) {
            return;
        }
        foreach (ilDidacticTemplateActionFactory::getActionsByTemplateId($tpl_id) as $action) {
            if ($action instanceof ilDidacticTemplateLocalRoleAction) {
                continue;
            }
            $action->setRefId($a_ref_id);
            $action->apply();
        }
    }

    /**
     * Adjust permissions of moved objects
     * - Delete permissions of parent roles that do not exist in new context
     * - Delete role templates of parent roles that do not exist in new context
     * - Add permissions for parent roles that did not exist in old context
     */
    public function adjustMovedObjectPermissions(int $a_ref_id, int $a_old_parent): void
    {
        global $DIC;

        $tree = $DIC['tree'];

        $new_parent = $tree->getParentId($a_ref_id);
        $old_context_roles = $this->rbacreview->getParentRoleIds($a_old_parent, false);
        $new_context_roles = $this->rbacreview->getParentRoleIds($new_parent, false);

        $for_addition = $for_deletion = array();
        foreach ($new_context_roles as $new_role_id => $new_role) {
            if (!isset($old_context_roles[$new_role_id])) {
                $for_addition[$new_role_id] = $new_role;
            } elseif ($new_role['parent'] != $old_context_roles[$new_role_id]['parent']) {
                // handle stopped inheritance
                $for_deletion[$new_role_id] = $new_role;
                $for_addition[$new_role_id] = $new_role;
            }
        }
        foreach ($old_context_roles as $old_role_id => $old_role) {
            if (!isset($new_context_roles[$old_role_id])) {
                $for_deletion[$old_role_id] = $old_role;
            }
        }

        if ($for_deletion === [] && $for_addition === []) {
            $this->applyMovedObjectDidacticTemplates($a_ref_id, $a_old_parent);
            return;
        }

        $rbac_log_active = ilRbacLog::isActive();
        if ($rbac_log_active) {
            $role_ids = array_unique(array_merge(array_keys($for_deletion), array_keys($for_addition)));
        }

        foreach ($nodes = $tree->getSubTree($tree->getNodeData($a_ref_id), true) as $node_data) {
            $node_id = (int) $node_data['child'];
            if ($rbac_log_active) {
                $log_old = ilRbacLog::gatherFaPa($node_id, $role_ids);
            }

            // If $node_data['type'] is not set, this means there is a tree entry without
            // object_reference and/or object_data entry
            // Continue in this case
            if (!($node_data['type'] ?? false)) {
                continue;
            }
            if (!$node_id) {
                continue;
            }

            foreach (array_keys($for_deletion) as $role_id) {
                $role_id = (int) $role_id;
                $this->deleteLocalRole($role_id, $node_id);
                $this->revokePermission($node_id, $role_id, false);
                //var_dump("<pre>",'REVOKE',$role_id,$node_id,$rolf_id,"</pre>");
            }
            foreach ($for_addition as $role_id => $role_data) {
                switch ($node_data['type']) {
                    case 'grp':
                        $tpl_id = ilObjGroup::lookupGroupStatusTemplateId((int) $node_data['obj_id']);
                        $this->initIntersectionPermissions(
                            $node_id,
                            $role_id,
                            (int) $role_data['parent'],
                            $tpl_id,
                            ROLE_FOLDER_ID
                        );
                        break;

                    case 'crs':
                        $tpl_id = ilObjCourse::lookupCourseNonMemberTemplatesId();
                        $this->initIntersectionPermissions(
                            $node_id,
                            $role_id,
                            (int) $role_data['parent'],
                            $tpl_id,
                            ROLE_FOLDER_ID
                        );
                        break;

                    default:
                        $this->grantPermission(
                            $role_id,
                            $ops = $this->rbacreview->getOperationsOfRole(
                                $role_id,
                                $node_data['type'],
                                (int) $role_data['parent']
                            ),
                            $node_id
                        );
                        break;

                }
            }

            if ($rbac_log_active) {
                $log_new = ilRbacLog::gatherFaPa($node_id, $role_ids);
                $log = ilRbacLog::diffFaPa($log_old, $log_new);
                ilRbacLog::add(ilRbacLog::MOVE_OBJECT, $node_id, $log);
            }
        }

        $this->applyMovedObjectDidacticTemplates($a_ref_id, $a_old_parent);
    }
}
