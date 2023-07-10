<?php

declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

use ILIAS\Setup;
use ILIAS\Setup\Environment;

class ilAccessInitialPermissionGuidelineAppliedObjective implements Setup\Objective
{
    protected const RBAC_OP_EDIT_PERMISSIONS = 1;
    protected const RBAC_OP_VISIBLE = 2;
    protected const RBAC_OP_READ = 3;
    protected const RBAC_OP_WRITE = 4;
    protected const RBAC_OP_DELETE = 6;
    protected const RBAC_OP_COPY = 99;

    protected array $initial_permission_definition = [
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

    protected string $object_type;
    protected bool $has_learning_progress;
    protected bool $used_for_authoring;

    public function __construct(
        string $object_type,
        bool $has_learning_progress = false,
        bool $used_for_authoring = false
    ) {
        $this->object_type = $object_type;
        $this->has_learning_progress = $has_learning_progress;
        $this->used_for_authoring = $used_for_authoring;
    }

    public function getHash(): string
    {
        return hash("sha256", self::class);
    }

    public function getLabel(): string
    {
        return "Apply initial permission guideline";
    }

    public function isNotable(): bool
    {
        return true;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilIniFilesLoadedObjective(),
            new ilDatabaseInitializedObjective()
        ];
    }

    public function achieve(Environment $environment): Environment
    {
        $client_ini = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI);
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);

        $role_folder_id = (int) $client_ini->readVariable("system", "ROLE_FOLDER_ID");

        $learning_progress_permissions = [];
        if ($this->has_learning_progress) {
            $learning_progress_permissions = array_filter([
                ilRbacReview::_getCustomRBACOperationId("read_learning_progress"),
                ilRbacReview::_getCustomRBACOperationId("edit_learning_progress")

            ]);
        }

        foreach ($this->initial_permission_definition as $role_type => $roles) {
            foreach ($roles as $role_title => $definition) {
                if (
                    $this->used_for_authoring &&
                    array_key_exists('ignore_for_authoring_objects', $definition) &&
                    $definition['ignore_for_authoring_objects']
                ) {
                    continue;
                }

                if (array_key_exists('id', $definition) && is_numeric($definition['id'])) {
                    // According to JF (2018-07-02), some roles have to be selected by if, not by title
                    $query = "SELECT obj_id FROM object_data WHERE type = %s AND obj_id = %s";
                    $query_types = ['text', 'integer'];
                    $query_values = [$role_type, $definition['id']];
                } else {
                    $query = "SELECT obj_id FROM object_data WHERE type = %s AND title = %s";
                    $query_types = ['text', 'text'];
                    $query_values = [$role_type, $role_title];
                }

                $res = $db->queryF($query, $query_types, $query_values);
                if (1 == $db->numRows($res)) {
                    $row = $db->fetchAssoc($res);
                    $role_id = (int) $row['obj_id'];

                    $operation_ids = [];

                    if (array_key_exists('object', $definition) && is_array($definition['object'])) {
                        $operation_ids = array_merge($operation_ids, $definition['object']);
                    }

                    if (array_key_exists('lp', $definition) && $definition['lp']) {
                        $operation_ids = array_merge($operation_ids, $learning_progress_permissions);
                    }

                    foreach (array_filter(array_map('intval', $operation_ids)) as $ops_id) {
                        if ($ops_id == self::RBAC_OP_COPY) {
                            $ops_id = ilRbacReview::_getCustomRBACOperationId('copy');
                        }

                        $db->replace(
                            'rbac_templates',
                            [
                                'rol_id' => ['integer', $role_id],
                                'type' => ['text', $this->object_type],
                                'ops_id' => ['integer', $ops_id],
                                'parent' => ['integer', $role_folder_id]
                            ],
                            []
                        );
                    }

                    if (array_key_exists('create', $definition) && is_array($definition['create'])) {
                        foreach ($definition['create'] as $container_object_type) {
                            foreach (ilRbacReview::_getCustomRBACOperationId("create_" . $this->object_type) as $ops_id) {
                                if ($ops_id == self::RBAC_OP_COPY) {
                                    $ops_id = ilRbacReview::_getCustomRBACOperationId('copy');
                                }

                                $db->replace(
                                    'rbac_templates',
                                    [
                                        'rol_id' => ['integer', $role_id],
                                        'type' => ['text', $container_object_type],
                                        'ops_id' => ['integer', $ops_id],
                                        'parent' => ['integer', $role_folder_id]
                                    ],
                                    []
                                );
                            }
                        }
                    }
                }
            }
        }


        return $environment;
    }

    public function isApplicable(Environment $environment): bool
    {
        if (!ilObject::_getObjectTypeIdByTitle($this->object_type)) {
            throw new Exception("Something went wrong, there MUST be valid id for object_type " . $this->object_type);
        }

        if (!ilRbacReview::_getCustomRBACOperationId("create_" . $this->object_type)) {
            throw new Exception(
                "Something went wrong, missing CREATE operation id for object type " . $this->object_type
            );
        }

        return true;
    }
}
