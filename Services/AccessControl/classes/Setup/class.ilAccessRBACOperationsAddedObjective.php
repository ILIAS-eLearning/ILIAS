<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

use ILIAS\Setup;
use ILIAS\Setup\Environment;

class ilAccessRbacOperationsAddedObjective implements Setup\Objective
{
    protected const RBAC_OP_EDIT_PERMISSIONS = 1;
    protected const RBAC_OP_VISIBLE = 2;
    protected const RBAC_OP_READ = 3;
    protected const RBAC_OP_WRITE = 4;
    protected const RBAC_OP_DELETE = 6;
    protected const RBAC_OP_COPY = 99;

    protected array $valid_operations = [
        self::RBAC_OP_EDIT_PERMISSIONS,
        self::RBAC_OP_VISIBLE,
        self::RBAC_OP_READ,
        self::RBAC_OP_WRITE,
        self::RBAC_OP_DELETE,
        self::RBAC_OP_COPY
    ];

    protected int $type_id;
    protected array $operations;

    public function __construct(int $type_id, array $operations = [])
    {
        $this->type_id = $type_id;
        $this->operations = $operations;
    }

    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    public function getLabel() : string
    {
        $operations = implode(",", $this->operations);
        return "Add rbac operations (type id=$this->type_id;operations=$operations)";
    }

    public function isNotable() : bool
    {
        return true;
    }

    public function getPreconditions(Environment $environment) : array
    {
        return [
            new ilDatabaseInitializedObjective()
        ];
    }

    public function achieve(Environment $environment) : Environment
    {
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);

        foreach ($this->operations as $ops_id) {
            if (!$this->isValidRbacOperation($ops_id)) {
                continue;
            }

            if ($ops_id == self::RBAC_OP_COPY) {
                $ops_id = ilRbacReview::_getCustomRBACOperationId("copy");
            }

            if (ilRbacReview::_isRBACOperation($this->type_id, $ops_id)) {
                continue;
            }

            $values = [
                "typ_id" => ["integer", $this->type_id],
                "ops_id" => ["integer", $ops_id]
            ];

            $db->insert("rbac_ta", $values);
        }

        return $environment;
    }

    public function isApplicable(Environment $environment) : bool
    {
        return true;
    }

    protected function isValidRbacOperation(int $ops_id) : bool
    {
        return in_array($ops_id, $this->valid_operations);
    }
}
