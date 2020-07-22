<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Helper class to create new object types (object_data, RBAC)
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * $Id: class.ilObjFolderGUI.php 25134 2010-08-13 14:22:11Z smeyer $
 *
 * @ingroup ServicesMigration
 */
class ilDBUpdateNewObjectType
{
    const RBAC_OP_EDIT_PERMISSIONS = 1;
    const RBAC_OP_VISIBLE = 2;
    const RBAC_OP_READ = 3;
    const RBAC_OP_WRITE = 4;
    const RBAC_OP_DELETE = 6;
    const RBAC_OP_COPY = 99;

    protected static $initialPermissionDefinition = [
        'role' => [
            'User' => [
                'id' => 4,
                'ignore_for_authoring_objects' => true,
                'object' => [
                    self::RBAC_OP_VISIBLE,
                    self::RBAC_OP_READ,
                ]
            ]
        ],
        'rolt' => [
            'il_crs_admin' => [
                'object' => [
                    self::RBAC_OP_VISIBLE,
                    self::RBAC_OP_READ,
                    self::RBAC_OP_WRITE,
                    self::RBAC_OP_DELETE,
                    self::RBAC_OP_COPY,
                    self::RBAC_OP_EDIT_PERMISSIONS,
                ],
                'lp' => true,
                'create' => [
                    'crs',
                    'grp',
                    'fold',
                ]
            ],
            'il_crs_tutor' => [
                'object' => [
                    self::RBAC_OP_VISIBLE,
                    self::RBAC_OP_READ,
                    self::RBAC_OP_WRITE,
                    self::RBAC_OP_COPY,
                ],
                'create' => [
                    'crs',
                    'fold',
                ]
            ],
            'il_crs_member' => [
                'ignore_for_authoring_objects' => true,
                'object' => [
                    self::RBAC_OP_VISIBLE,
                    self::RBAC_OP_READ,
                ]
            ],
            'il_grp_admin' => [
                'object' => [
                    self::RBAC_OP_VISIBLE,
                    self::RBAC_OP_READ,
                    self::RBAC_OP_WRITE,
                    self::RBAC_OP_DELETE,
                    self::RBAC_OP_COPY,
                    self::RBAC_OP_EDIT_PERMISSIONS,
                ],
                'lp' => true,
                'create' => [
                    'grp',
                    'fold',
                ]
            ],
            'il_grp_member' => [
                'ignore_for_authoring_objects' => true,
                'object' => [
                    self::RBAC_OP_VISIBLE,
                    self::RBAC_OP_READ,
                ]
            ],
            'Author' => [
                'object' => [
                    self::RBAC_OP_VISIBLE,
                    self::RBAC_OP_READ,
                    self::RBAC_OP_WRITE,
                    self::RBAC_OP_DELETE,
                    self::RBAC_OP_COPY,
                    self::RBAC_OP_EDIT_PERMISSIONS,
                ],
                'lp' => true,
                'create' => [
                    'cat',
                    'crs',
                    'grp',
                    'fold',
                ]
            ],
            'Local Administrator' => [
                'object' => [
                    self::RBAC_OP_VISIBLE,
                    self::RBAC_OP_DELETE,
                    self::RBAC_OP_EDIT_PERMISSIONS,
                ],
                'create' => [
                    'cat',
                ]
            ],
        ]
    ];
    
    /**
     * Add new type to object data
     *
     * @param string $a_type_id
     * @param string $a_type_title
     * @return int insert id
     */
    public static function addNewType($a_type_id, $a_type_title)
    {
        global $ilDB;
        
        // check if it already exists
        $type_id = self::getObjectTypeId($a_type_id);
        if ($type_id) {
            return $type_id;
        }
        
        $type_id = $ilDB->nextId('object_data');
        
        $fields = array(
            'obj_id' => array('integer', $type_id),
            'type' => array('text', 'typ'),
            'title' => array('text', $a_type_id),
            'description' => array('text', $a_type_title),
            'owner' => array('integer', -1),
            'create_date' => array('timestamp', ilUtil::now()),
            'last_update' => array('timestamp', ilUtil::now())
        );
        $ilDB->insert('object_data', $fields);
        
        return $type_id;
    }
    
    /**
     * Add RBAC operations for type
     *
     * @param int $a_type_id
     * @param array $a_operations
     */
    public static function addRBACOperations($a_type_id, array $a_operations)
    {
        foreach ($a_operations as $ops_id) {
            if (self::isValidRBACOperation($ops_id)) {
                if ($ops_id == self::RBAC_OP_COPY) {
                    $ops_id = self::getCustomRBACOperationId('copy');
                }
                
                self::addRBACOperation($a_type_id, $ops_id);
            }
        }
    }
    
    /**
     * Add RBAC operation
     *
     * @param int $a_type_id
     * @param int $a_ops_id
     * @return bool
     */
    public static function addRBACOperation($a_type_id, $a_ops_id)
    {
        global $ilDB;
        
        // check if it already exists
        $set = $ilDB->query('SELECT * FROM rbac_ta' .
            ' WHERE typ_id = ' . $ilDB->quote($a_type_id, 'integer') .
            ' AND ops_id = ' . $ilDB->quote($a_ops_id, 'integer'));
        if ($ilDB->numRows($set)) {
            return false;
        }
        
        $fields = array(
            'typ_id' => array('integer', $a_type_id),
            'ops_id' => array('integer', $a_ops_id)
        );
        $ilDB->insert('rbac_ta', $fields);
        return true;
    }

    /**
     * Check if rbac operation exists
     *
     * @param int $a_type_id type id
     * @param int $a_ops_id operation id
     * @return bool
     */
    public static function isRBACOperation($a_type_id, $a_ops_id)
    {
        global $ilDB;

        // check if it already exists
        $set = $ilDB->query('SELECT * FROM rbac_ta' .
            ' WHERE typ_id = ' . $ilDB->quote($a_type_id, 'integer') .
            ' AND ops_id = ' . $ilDB->quote($a_ops_id, 'integer'));
        if ($ilDB->numRows($set)) {
            return true;
        }
        return false;
    }

    /**
     * Delete rbac operation
     *
     * @param int $a_type
     * @param int $a_ops_id
     */
    public static function deleteRBACOperation($a_type, $a_ops_id)
    {
        global $ilDB;
        
        if (!$a_type || !$a_ops_id) {
            return;
        }
        
        $type_id = self::getObjectTypeId($a_type);
        if (!$type_id) {
            return;
        }

        $query = 'DELETE FROM rbac_ta WHERE ' .
            'typ_id = ' . $ilDB->quote($type_id, 'integer') . ' AND ' .
            'ops_id = ' . $ilDB->quote($a_ops_id, 'integer');
        $GLOBALS['ilLog']->write(__METHOD__ . ': ' . $query);
        $ilDB->manipulate($query);
        
        self::deleteRBACTemplateOperation($a_type, $a_ops_id);
    }
    
    /**
     * Delete operation for type in templates
     *
     * @param string $a_type
     * @param int $a_ops_id
     */
    public static function deleteRBACTemplateOperation($a_type, $a_ops_id)
    {
        global $ilDB;
        
        if (!$a_type || !$a_ops_id) {
            return;
        }

        $query = 'DELETE FROM rbac_templates WHERE ' .
            'type = ' . $ilDB->quote($a_type, 'text') . ' AND ' .
            'ops_id = ' . $ilDB->quote($a_ops_id, 'integer');
        $GLOBALS['ilLog']->write(__METHOD__ . ': ' . $query);
        $ilDB->manipulate($query);
    }

    /**
     * Check if given RBAC operation id is valid
     *
     * @param int $a_ops_id
     * @return bool
     */
    protected static function isValidRBACOperation($a_ops_id)
    {
        $valid = array(
            self::RBAC_OP_EDIT_PERMISSIONS,
            self::RBAC_OP_VISIBLE,
            self::RBAC_OP_READ,
            self::RBAC_OP_WRITE,
            self::RBAC_OP_DELETE,
            self::RBAC_OP_COPY
        );
        if (in_array($a_ops_id, $valid)) {
            return true;
        }
        return false;
    }
    
    /**
     * Get id of RBAC operation
     *
     * @param string $a_operation
     * @return int
     */
    public static function getCustomRBACOperationId($a_operation)
    {
        global $ilDB;
        
        $sql = 'SELECT ops_id' .
            ' FROM rbac_operations' .
            ' WHERE operation = ' . $ilDB->quote($a_operation, 'text');
        $res = $ilDB->query($sql);
        $row = $ilDB->fetchAssoc($res);
        return $row['ops_id'];
    }
    
    /**
     * Add custom RBAC operation
     *
     * @param string $a_id
     * @param string $a_title
     * @param string $a_class
     * @param string $a_pos
     * @return int ops_id
     */
    public static function addCustomRBACOperation($a_id, $a_title, $a_class, $a_pos)
    {
        global $ilDB;
        
        // check if it already exists
        $ops_id = self::getCustomRBACOperationId($a_id);
        if ($ops_id) {
            return $ops_id;
        }
        
        if (!in_array($a_class, array('create', 'object', 'general'))) {
            return;
        }
        if ($a_class == 'create') {
            $a_pos = 9999;
        }
        
        $ops_id = $ilDB->nextId('rbac_operations');
        
        $fields = array(
            'ops_id' => array('integer', $ops_id),
            'operation' => array('text', $a_id),
            'description' => array('text', $a_title),
            'class' => array('text', $a_class),
            'op_order' => array('integer', $a_pos),
        );
        $ilDB->insert('rbac_operations', $fields);
        
        return $ops_id;
    }

    /**
     * Get id for object data type entry
     *
     * @param string $a_type
     * @return int
     */
    public static function getObjectTypeId($a_type)
    {
        global $ilDB;
        
        $sql = 'SELECT obj_id FROM object_data' .
            ' WHERE type = ' . $ilDB->quote('typ', 'text') .
            ' AND title = ' . $ilDB->quote($a_type, 'text');
        $res = $ilDB->query($sql);
        $row = $ilDB->fetchAssoc($res);
        return $row['obj_id'];
    }
    
    /**
     * Add create RBAC operations for parent object types
     *
     * @param string  $a_id
     * @param string $a_title
     * @param array $a_parent_types
     */
    public static function addRBACCreate($a_id, $a_title, array $a_parent_types)
    {
        $ops_id = self::addCustomRBACOperation($a_id, $a_title, 'create', 9999);
        
        foreach ($a_parent_types as $type) {
            $type_id = self::getObjectTypeId($type);
            if ($type_id) {
                self::addRBACOperation($type_id, $ops_id);
            }
        }
    }
    
    /**
     * Change order of operations
     *
     * @param string $a_operation
     * @param int $a_pos
     */
    public static function updateOperationOrder($a_operation, $a_pos)
    {
        global $ilDB;
        
        $ilDB->update(
            'rbac_operations',
            array('op_order' => array('integer', $a_pos)),
            array('operation' => array('text', $a_operation))
        );
    }
    
    /**
     * Create new admin object node
     *
     * @param string $a_id
     * @param string $a_title
     */
    public static function addAdminNode($a_obj_type, $a_title)
    {
        global $ilDB, $tree;
        
        if (self::getObjectTypeId($a_obj_type)) {
            return;
        }
        
        $obj_type_id = self::addNewType($a_obj_type, $a_title);

        $obj_id = $ilDB->nextId('object_data');
        $ilDB->manipulate("INSERT INTO object_data " .
            "(obj_id, type, title, description, owner, create_date, last_update) VALUES (" .
            $ilDB->quote($obj_id, "integer") . "," .
            $ilDB->quote($a_obj_type, "text") . "," .
            $ilDB->quote($a_title, "text") . "," .
            $ilDB->quote($a_title, "text") . "," .
            $ilDB->quote(-1, "integer") . "," .
            $ilDB->now() . "," .
            $ilDB->now() .
            ")");

        $ref_id = $ilDB->nextId('object_reference');
        $ilDB->manipulate("INSERT INTO object_reference " .
            "(obj_id, ref_id) VALUES (" .
            $ilDB->quote($obj_id, "integer") . "," .
            $ilDB->quote($ref_id, "integer") .
            ")");

        // put in tree
        require_once("Services/Tree/classes/class.ilTree.php");
        $tree = new ilTree(ROOT_FOLDER_ID);
        $tree->insertNode($ref_id, SYSTEM_FOLDER_ID);

        $rbac_ops = array(
            self::RBAC_OP_EDIT_PERMISSIONS,
            self::RBAC_OP_VISIBLE,
            self::RBAC_OP_READ,
            self::RBAC_OP_WRITE
        );
        self::addRBACOperations($obj_type_id, $rbac_ops);
    }
    
    /**
     * Clone RBAC-settings between operations
     *
     * @param string $a_obj_type
     * @param int $a_source_op_id
     * @param int $a_target_op_id
     */
    public static function cloneOperation($a_obj_type, $a_source_op_id, $a_target_op_id)
    {
        global $ilDB;
        
        // rbac_pa
        $sql = "SELECT rpa.*" .
            " FROM rbac_pa rpa" .
            " JOIN object_reference ref ON (ref.ref_id = rpa.ref_id)" .
            " JOIN object_data od ON (od.obj_id = ref.obj_id AND od.type = " . $ilDB->quote($a_obj_type) . ")" .
            // see ilUtil::_getObjectsByOperations()
            " WHERE (" . $ilDB->like("ops_id", "text", "%i:" . $a_source_op_id . "%") .
            " OR " . $ilDB->like("ops_id", "text", "%:\"" . $a_source_op_id . "\";%") . ")";
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $ops = unserialize($row["ops_id"]);
            // the query above could match by array KEY, we need extra checks
            if (in_array($a_source_op_id, $ops) && !in_array($a_target_op_id, $ops)) {
                $ops[] = $a_target_op_id;
                
                $ilDB->manipulate("UPDATE rbac_pa" .
                    " SET ops_id = " . $ilDB->quote(serialize($ops), "text") .
                    " WHERE rol_id = " . $ilDB->quote($row["rol_id"], "integer") .
                    " AND ref_id = " . $ilDB->quote($row["ref_id"], "integer"));
            }
        }
        
        // rbac_templates
        $tmp = array();
        $sql = "SELECT rol_id, parent, ops_id" .
            " FROM rbac_templates" .
            " WHERE type = " . $ilDB->quote($a_obj_type, "text") .
            " AND (ops_id = " . $ilDB->quote($a_source_op_id, "integer") .
            " OR ops_id = " . $ilDB->quote($a_target_op_id) . ")";
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $tmp[$row["rol_id"]][$row["parent"]][] = $row["ops_id"];
        }
        
        foreach ($tmp as $role_id => $parents) {
            foreach ($parents as $parent_id => $ops_ids) {
                // only if the target op is missing
                if (sizeof($ops_ids) < 2 && in_array($a_source_op_id, $ops_ids)) {
                    $ilDB->manipulate("INSERT INTO rbac_templates" .
                        " (rol_id, type, ops_id, parent)" .
                        " VALUES " .
                        "(" . $ilDB->quote($role_id, "integer") .
                        "," . $ilDB->quote($a_obj_type, "text") .
                        "," . $ilDB->quote($a_target_op_id, "integer") .
                        "," . $ilDB->quote($parent_id, "integer") .
                        ")");
                }
            }
        }
    }
    
    /**
     * Migrate varchar column to text/clob
     *
     * @param string $a_table_name
     * @param string $a_column_name
     * @return bool
     */
    public static function varchar2text($a_table_name, $a_column_name)
    {
        global $ilDB;
        
        $tmp_column_name = $a_column_name . "_tmp_clob";
        
        if (!$ilDB->tableColumnExists($a_table_name, $a_column_name) ||
            $ilDB->tableColumnExists($a_table_name, $tmp_column_name)) {
            return false;
        }
        
        // oracle does not support ALTER TABLE varchar2 to CLOB

        $ilAtomQuery = $ilDB->buildAtomQuery();
        $ilAtomQuery->addTableLock($a_table_name);

        $ilAtomQuery->addQueryCallable(
            function (ilDBInterface $ilDB) use ($a_table_name, $a_column_name, $tmp_column_name) {
                $def = array(
                    'type' => 'clob',
                    'notnull' => false
                );
                $ilDB->addTableColumn($a_table_name, $tmp_column_name, $def);

                $ilDB->manipulate('UPDATE ' . $a_table_name . ' SET ' . $tmp_column_name . ' = ' . $a_column_name);

                $ilDB->dropTableColumn($a_table_name, $a_column_name);

                $ilDB->renameTableColumn($a_table_name, $tmp_column_name, $a_column_name);
            }
        );

        $ilAtomQuery->run();
        
        return true;
    }
    
    /**
     * Add new RBAC template
     *
     * @param string $a_obj_type
     * @param string $a_id
     * @param string $a_description
     * @param int|array $a_op_ids
     */
    public static function addRBACTemplate($a_obj_type, $a_id, $a_description, $a_op_ids)
    {
        global $ilDB;
        
        $new_tpl_id = $ilDB->nextId('object_data');

        $ilDB->manipulateF(
            "INSERT INTO object_data (obj_id, type, title, description," .
            " owner, create_date, last_update) VALUES (%s, %s, %s, %s, %s, %s, %s)",
            array("integer", "text", "text", "text", "integer", "timestamp", "timestamp"),
            array($new_tpl_id, "rolt", $a_id, $a_description, -1, ilUtil::now(), ilUtil::now())
        );
                
        $ilDB->manipulateF(
            "INSERT INTO rbac_fa (rol_id, parent, assign, protected)" .
            " VALUES (%s, %s, %s, %s)",
            array("integer", "integer", "text", "text"),
            array($new_tpl_id, 8, "n", "n")
        );
        
        if ($a_op_ids) {
            if (!is_array($a_op_ids)) {
                $a_op_ids = array($a_op_ids);
            }
            foreach ($a_op_ids as $op_id) {
                $ilDB->manipulateF(
                    "INSERT INTO rbac_templates (rol_id, type, ops_id, parent)" .
                " VALUES (%s, %s, %s, %s)",
                    array("integer", "text", "integer", "integer"),
                    array($new_tpl_id, $a_obj_type, $op_id, 8)
                );
            }
        }
    }

    public static function setRolePermission(int $a_rol_id, string $a_type, array $a_ops, int $a_ref_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        foreach ($a_ops as $ops_id) {
            if ($ops_id == self::RBAC_OP_COPY) {
                $ops_id = self::getCustomRBACOperationId('copy');
            }

            $ilDB->replace(
                'rbac_templates',
                [
                    'rol_id' => ['integer', $a_rol_id],
                    'type' => ['text', $a_type],
                    'ops_id' => ['integer', $ops_id],
                    'parent' => ['integer', $a_ref_id]
                ],
                []
            );
        }
    }


    /**
     * This method will apply the 'Initial Permissions Guideline' when introducing new object types.
     * This method does not apply permissions to existing obejcts in the ILIAS repository ('change existing objects').
     * @param string $objectType
     * @param bool $hasLearningProgress A boolean flag whether or not the object type supports learning progress
     * @param bool $usedForAuthoring A boolean flag to tell whether or not the object type is mainly used for authoring
     * @see https://www.ilias.de/docu/goto_docu_wiki_wpage_2273_1357.html
     */
    public static function applyInitialPermissionGuideline(string $objectType, bool $hasLearningProgress = false, bool $usedForAuthoring = false)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $objectTypeId = self::getObjectTypeId($objectType);
        if (!$objectTypeId) {
            die("Something went wrong, there MUST be valid id for object_type " . $objectType);
        }

        $objectCreateOperationId = ilDBUpdateNewObjectType::getCustomRBACOperationId('create_' . $objectType);
        if (!$objectCreateOperationId) {
            die("Something went wrong, missing CREATE operation id for object type " . $objectType);
        }

        $globalRoleFolderId = 8; // Maybe there is another way to determine this id

        $learningProgressPermissions = [];
        if ($hasLearningProgress) {
            $learningProgressPermissions = array_filter([
                self::getCustomRBACOperationId('read_learning_progress'),
                self::getCustomRBACOperationId('edit_learning_progress'),
            ]);
        }

        foreach (self::$initialPermissionDefinition as $roleType => $roles) {
            foreach ($roles as $roleTitle => $definition) {
                if (
                    true === $usedForAuthoring &&
                    array_key_exists('ignore_for_authoring_objects', $definition) &&
                    true === $definition['ignore_for_authoring_objects']
                ) {
                    continue;
                }

                if (array_key_exists('id', $definition) && is_numeric($definition['id'])) {
                    // According to JF (2018-07-02), some roles have to be selected by if, not by title
                    $query = "SELECT obj_id FROM object_data WHERE type = %s AND obj_id = %s";
                    $queryTypes = ['text', 'integer'];
                    $queryValues = [$roleType, $definition['id']];
                } else {
                    $query = "SELECT obj_id FROM object_data WHERE type = %s AND title = %s";
                    $queryTypes = ['text', 'text'];
                    $queryValues = [$roleType, $roleTitle];
                }

                $res = $ilDB->queryF($query, $queryTypes, $queryValues);
                if (1 == $ilDB->numRows($res)) {
                    $row = $ilDB->fetchAssoc($res);
                    $roleId = (int) $row['obj_id'];

                    $operationIds = [];

                    if (array_key_exists('object', $definition) && is_array($definition['object'])) {
                        $operationIds = array_merge($operationIds, (array) $definition['object']);
                    }

                    if (array_key_exists('lp', $definition) && true === $definition['lp']) {
                        $operationIds = array_merge($operationIds, $learningProgressPermissions);
                    }

                    self::setRolePermission(
                        $roleId,
                        $objectType,
                        array_filter(array_map('intval', $operationIds)),
                        $globalRoleFolderId
                    );

                    if (array_key_exists('create', $definition) && is_array($definition['create'])) {
                        foreach ($definition['create'] as $containerObjectType) {
                            self::setRolePermission(
                                $roleId,
                                $containerObjectType,
                                [
                                    $objectCreateOperationId
                                ],
                                $globalRoleFolderId
                            );
                        }
                    }
                }
            }
        }
    }
}
