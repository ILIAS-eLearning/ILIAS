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
 * Helper class to create new object types (object_data, RBAC)
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * $Id: class.ilObjFolderGUI.php 25134 2010-08-13 14:22:11Z smeyer $
 *
 * @ingroup ServicesMigration
 */
class ilDBUpdateNewObjectType
{
    public const RBAC_OP_EDIT_PERMISSIONS = 1;
    public const RBAC_OP_VISIBLE = 2;
    public const RBAC_OP_READ = 3;
    public const RBAC_OP_WRITE = 4;
    public const RBAC_OP_DELETE = 6;
    public const RBAC_OP_COPY = 99;

    protected static array $initialPermissionDefinition = [
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
     * @deprecated use Services/Object/classes/Setup/class.ilObjectNewTypeAddedObjective.php instead
     */
    public static function addNewType(string $type_id, string $type_title): int
    {
        global $ilDB;
        $db = $ilDB;

        // check if it already exists
        $id = ilObject::_getObjectTypeIdByTitle($type_id);
        if ($id) {
            return $id;
        }

        $id = $db->nextId("object_data");

        $values = [
            'obj_id' => ['integer', $id],
            'type' => ['text', 'typ'],
            'title' => ['text', $type_id],
            'description' => ['text', $type_title],
            'owner' => ['integer', -1],
            'create_date' => ['timestamp', date("Y-m-d H:i:s")],
            'last_update' => ['timestamp', date("Y-m-d H:i:s")]
        ];

        $db->insert("object_data", $values);

        return $id;
    }

    /**
     * Add RBAC operations for type
     *
     * @deprecated use Services/AccessControl/classes/Setup/class.ilAccessRBACOperationsAddedObjective.php instead
     */
    public static function addRBACOperations(int $type_id, array $operations): void
    {
        foreach ($operations as $ops_id) {
            if (self::isValidRBACOperation($ops_id)) {
                if ($ops_id == self::RBAC_OP_COPY) {
                    $ops_id = ilRbacReview::_getCustomRBACOperationId('copy');
                }

                self::addRBACOperation($type_id, $ops_id);
            }
        }
    }

    /**
     * Add RBAC operation
     *
     * @deprecated use Services/AccessControl/classes/Setup/class.ilAccessRBACOperationsAddedObjective.php instead
     */
    public static function addRBACOperation(int $type_id, int $ops_id): bool
    {
        global $ilDB;

        $sql =
            "SELECT typ_id" . PHP_EOL
            . "FROM rbac_ta" . PHP_EOL
            . "WHERE typ_id = " . $ilDB->quote($type_id, "integer") . PHP_EOL
            . "AND ops_id = " . $ilDB->quote($ops_id, "integer") . PHP_EOL
        ;
        $res = $ilDB->query($sql);

        if ($ilDB->numRows($res)) {
            return false;
        }

        $fields = [
            'typ_id' => ['integer', $type_id],
            'ops_id' => ['integer', $ops_id]
        ];
        $ilDB->insert('rbac_ta', $fields);

        return true;
    }

    /**
     * Check if rbac operation exists
     *
     * @deprecated use ilRbacReview::_isRBACOperation instead
     */
    public static function isRBACOperation(int $type_id, int $ops_id): bool
    {
        global $ilDB;

        $sql =
            "SELECT typ_id" . PHP_EOL
            . "FROM rbac_ta" . PHP_EOL
            . "WHERE typ_id = " . $ilDB->quote($type_id, "integer") . PHP_EOL
            . "AND ops_id = " . $ilDB->quote($ops_id, "integer") . PHP_EOL
        ;

        return (bool) $ilDB->numRows($ilDB->query($sql));
    }

    /**
     * Delete rbac operation
     *
     * @deprecated use Services/AccessControl/classes/Setup/class.ilAccessRBACOperationDeletedObjective.php instead
     */
    public static function deleteRBACOperation(string $type, int $ops_id): void
    {
        global $ilDB;

        if (!$type || !$ops_id) {
            return;
        }

        $type_id = ilObject::_getObjectTypeIdByTitle($type);
        if (!$type_id) {
            return;
        }

        $sql =
            "DELETE FROM rbac_ta" . PHP_EOL
            . "WHERE typ_id = " . $ilDB->quote($type_id, "integer") . PHP_EOL
            . "AND ops_id = " . $ilDB->quote($ops_id, "integer") . PHP_EOL
        ;
        $GLOBALS['ilLog']->write(__METHOD__ . ': ' . $sql);
        $ilDB->manipulate($sql);

        self::deleteRBACTemplateOperation($type, $ops_id);
    }

    /**
     * Delete operation for type in templates
     *
     * @deprecated use Services/AccessControl/classes/Setup/class.ilAccessRBACOperationDeletedObjective.php instead
     */
    public static function deleteRBACTemplateOperation(string $type, int $ops_id): void
    {
        global $ilDB;

        if (!$type || !$ops_id) {
            return;
        }

        $sql =
            "DELETE FROM rbac_templates" . PHP_EOL
            . "WHERE type = " . $ilDB->quote($type, "text") . PHP_EOL
            . "ops_id = " . $ilDB->quote($ops_id, "integer") . PHP_EOL
        ;
        $GLOBALS['ilLog']->write(__METHOD__ . ': ' . $sql);
        $ilDB->manipulate($sql);
    }

    /**
     * Check if given RBAC operation id is valid
     *
     * @deprecated
     */
    protected static function isValidRBACOperation(int $ops_id): bool
    {
        $valid = [
            self::RBAC_OP_EDIT_PERMISSIONS,
            self::RBAC_OP_VISIBLE,
            self::RBAC_OP_READ,
            self::RBAC_OP_WRITE,
            self::RBAC_OP_DELETE,
            self::RBAC_OP_COPY
        ];

        return in_array($ops_id, $valid);
    }

    /**
     * Get id of RBAC operation
     *
     * @deprecated use ilRbacReview::_getCustomRBACOperationId instead
     */
    public static function getCustomRBACOperationId(string $operation): ?int
    {
        global $ilDB;

        $sql =
            "SELECT ops_id" . PHP_EOL
            . "FROM rbac_operations" . PHP_EOL
            . "WHERE operation = " . $ilDB->quote($operation, "text") . PHP_EOL
        ;

        $res = $ilDB->query($sql);
        if ($ilDB->numRows($res) == 0) {
            return null;
        }

        $row = $ilDB->fetchAssoc($res);
        return (int) $row["ops_id"] ?? null;
    }

    /**
     * Add custom RBAC operation
     *
     * @deprecated use Services/AccessControl/classes/Setup/class.ilAccessCustomRBACOperationAddedObjective.php instead
     */
    public static function addCustomRBACOperation(string $id, string $title, string $class, int $pos): int
    {
        global $ilDB;

        // check if it already exists
        $ops_id = ilRbacReview::_getCustomRBACOperationId($id);
        if ($ops_id) {
            return $ops_id;
        }

        if (!in_array($class, array('create', 'object', 'general'))) {
            throw new InvalidArgumentException("Class type '$class' is not supportet by RBAC system.");
        }
        if ($class == 'create') {
            $pos = 9999;
        }

        $ops_id = $ilDB->nextId('rbac_operations');

        $fields = [
            'ops_id' => ['integer', $ops_id],
            'operation' => ['text', $id],
            'description' => ['text', $title],
            'class' => ['text', $class],
            'op_order' => ['integer', $pos]
        ];
        $ilDB->insert('rbac_operations', $fields);

        return $ops_id;
    }

    /**
     * Get id for object data type entry
     *
     * @deprecated use ilObject::_getObjectTypeIdByTitle() instead
     */
    public static function getObjectTypeId(string $type): ?int
    {
        global $ilDB;

        $sql =
            "SELECT obj_id FROM object_data" . PHP_EOL
            . "WHERE type = 'typ'" . PHP_EOL
            . "AND title = " . $ilDB->quote($type, 'text') . PHP_EOL
        ;

        $res = $ilDB->query($sql);
        if ($ilDB->numRows($res) == 0) {
            return null;
        }

        $row = $ilDB->fetchAssoc($res);
        return (int) $row['obj_id'] ?? null;
    }

    /**
     * Add create RBAC operations for parent object types
     *
     * @deprecated use Services/AccessControl/classes/Setup/class.ilAccessCustomRBACOperationAddedObjective.php instead
     *             use 'create' for class param
     */
    public static function addRBACCreate(string $id, string $title, array $parent_types): void
    {
        $ops_id = self::addCustomRBACOperation($id, $title, 'create', 9999);

        foreach ($parent_types as $type) {
            $type_id = ilObject::_getObjectTypeIdByTitle($type);
            if ($type_id) {
                self::addRBACOperation($type_id, $ops_id);
            }
        }
    }

    /**
     * Change order of operations
     *
     * @deprecated use Services/AccessControl/classes/Setup/class.ilAccessRBACOperationOrderUpdatedObjective.php instead
     */
    public static function updateOperationOrder(string $operation, int $pos): void
    {
        global $ilDB;

        $ilDB->update(
            'rbac_operations',
            ['op_order' => ['integer', $pos]],
            ['operation' => ['text', $operation]]
        );
    }

    /**
     * Create new admin object node
     *
     * @deprecated use Services/Tree/classes/Setup/class.ilTreeAdminNodeAddedObjective.php instead
     */
    public static function addAdminNode(string $obj_type, string $title): void
    {
        global $ilDB, $tree;

        if (ilObject::_getObjectTypeIdByTitle($obj_type)) {
            return;
        }

        $obj_type_id = self::addNewType($obj_type, $title);
        $obj_id = $ilDB->nextId('object_data');
        $values = [
            'obj_id' => ['integer', $obj_id],
            'type' => ['text', $obj_type],
            'title' => ['text', $title],
            'description' => ['text', $title],
            'owner' => ['integer', -1],
            'create_date' => ['timestamp', date("Y-m-d H:i:s")],
            'last_update' => ['timestamp', date("Y-m-d H:i:s")]
        ];
        $ilDB->insert("object_data", $values);


        $ref_id = $ilDB->nextId("object_reference");
        $values = [
            "obj_id" => ["integer", $obj_id],
            "ref_id" => ["integer", $ref_id]
        ];
        $ilDB->insert("object_reference", $values);

        // put in tree
        require_once("Services/Tree/classes/class.ilTree.php");
        $tree = new ilTree(ROOT_FOLDER_ID);
        $tree->insertNode($ref_id, SYSTEM_FOLDER_ID);

        $rbac_ops = [
            self::RBAC_OP_EDIT_PERMISSIONS,
            self::RBAC_OP_VISIBLE,
            self::RBAC_OP_READ,
            self::RBAC_OP_WRITE
        ];

        self::addRBACOperations($obj_type_id, $rbac_ops);
    }

    /**
     * Clone RBAC-settings between operations
     *
     * @deprecated use Services/AccessControl/classes/Setup/class.ilAccessRBACOperationClonedObjective.php instead
     */
    public static function cloneOperation(string $obj_type, int $source_op_id, int $target_op_id): void
    {
        global $ilDB;
        $db = $ilDB;

        $sql =
            "SELECT rpa.rol_id, rpa.ops_id, rpa.ref_id" . PHP_EOL
            . "FROM rbac_pa rpa" . PHP_EOL
            . "JOIN object_reference ref ON (ref.ref_id = rpa.ref_id)" . PHP_EOL
            . "JOIN object_data od ON (od.obj_id = ref.obj_id AND od.type = " . $db->quote($obj_type, "text") . ")" . PHP_EOL
            . "WHERE (" . $db->like("ops_id", "text", "%i:" . $source_op_id . "%") . PHP_EOL
            . "OR " . $db->like("ops_id", "text", "%:\"" . $source_op_id . "\";%") . ")" . PHP_EOL
        ;

        $res = $db->query($sql);
        while ($row = $db->fetchAssoc($res)) {
            $ops = unserialize($row["ops_id"]);
            // the query above could match by array KEY, we need extra checks
            if (in_array($source_op_id, $ops) && !in_array($target_op_id, $ops)) {
                $ops[] = $target_op_id;

                $sql =
                    "UPDATE rbac_pa" . PHP_EOL
                    . "SET ops_id = " . $db->quote(serialize($ops), "text") . PHP_EOL
                    . "WHERE rol_id = " . $db->quote($row["rol_id"], "integer") . PHP_EOL
                    . "AND ref_id = " . $db->quote($row["ref_id"], "integer") . PHP_EOL
                ;

                $db->manipulate($sql);
            }
        }

        // rbac_templates
        $tmp = [];
        $sql =
            "SELECT rol_id, parent, ops_id" . PHP_EOL
            . "FROM rbac_templates" . PHP_EOL
            . "WHERE type = " . $db->quote($obj_type, "text") . PHP_EOL
            . "AND (ops_id = " . $db->quote($source_op_id, "integer") . PHP_EOL
            . "OR ops_id = " . $db->quote($target_op_id, "integer") . ")" . PHP_EOL
        ;

        $res = $db->query($sql);
        while ($row = $db->fetchAssoc($res)) {
            $tmp[$row["rol_id"]][$row["parent"]][] = $row["ops_id"];
        }

        foreach ($tmp as $role_id => $parents) {
            foreach ($parents as $parent_id => $ops_ids) {
                // only if the target op is missing
                if (count($ops_ids) < 2 && in_array($source_op_id, $ops_ids)) {
                    $values = [
                        "rol_id" => ["integer", $role_id],
                        "type" => ["text", $obj_type],
                        "ops_id" => ["integer", $target_op_id],
                        "parent" => ["integer", $parent_id]
                    ];

                    $db->insert("rbac_templates", $values);
                }
            }
        }
    }

    /**
     * @deprecated use Services/AccessControl/classes/Setup/class.ilAccessRolePermissionSetObjective.php instead
     */
    public static function setRolePermission(int $a_rol_id, string $a_type, array $a_ops, int $a_ref_id): void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        foreach ($a_ops as $ops_id) {
            if ($ops_id == self::RBAC_OP_COPY) {
                $ops_id = ilRbacReview::_getCustomRBACOperationId('copy');
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
     * @param bool $hasLearningProgress A boolean flag whether the object type supports learning progress
     * @param bool $usedForAuthoring A boolean flag to tell whether the object type is mainly used for authoring
     * @see https://www.ilias.de/docu/goto_docu_wiki_wpage_2273_1357.html
     * @deprecated use Services/AccessControl/classes/Setup/class.ilAccessInitialPermissionGuidelineAppliedObjective.php instead
     */
    public static function applyInitialPermissionGuideline(
        string $objectType,
        bool $hasLearningProgress = false,
        bool $usedForAuthoring = false
    ): void {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $objectTypeId = ilObject::_getObjectTypeIdByTitle($objectType);
        if (!$objectTypeId) {
            die("Something went wrong, there MUST be valid id for object_type " . $objectType);
        }

        $objectCreateOperationId = ilRbacReview::_getCustomRBACOperationId('create_' . $objectType);
        if (!$objectCreateOperationId) {
            die("Something went wrong, missing CREATE operation id for object type " . $objectType);
        }

        $globalRoleFolderId = 8; // Maybe there is another way to determine this id

        $learningProgressPermissions = [];
        if ($hasLearningProgress) {
            $learningProgressPermissions = array_filter([
                ilRbacReview::_getCustomRBACOperationId('read_learning_progress'),
                ilRbacReview::_getCustomRBACOperationId('edit_learning_progress'),
            ]);
        }

        foreach (self::$initialPermissionDefinition as $roleType => $roles) {
            foreach ($roles as $roleTitle => $definition) {
                if (
                    $usedForAuthoring &&
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
                        $operationIds = array_merge($operationIds, $definition['object']);
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
