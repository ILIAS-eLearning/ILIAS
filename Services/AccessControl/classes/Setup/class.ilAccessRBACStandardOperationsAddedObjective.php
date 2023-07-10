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
 ********************************************************************
 */

use ILIAS\Setup;
use ILIAS\Setup\Environment;

class ilAccessRbacStandardOperationsAddedObjective implements Setup\Objective
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

    protected string $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function getHash(): string
    {
        return hash("sha256", self::class . "::" . $this->type);
    }

    public function getLabel(): string
    {
        $operations = implode(",", $this->valid_operations);
        return "Add standard rbac operations (type=$this->type;operations=$operations)";
    }

    public function isNotable(): bool
    {
        return true;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilDatabaseInitializedObjective()
        ];
    }

    public function achieve(Environment $environment): Environment
    {
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);
        $type_id = ilObject::_getObjectTypeIdByTitle($this->type, $db);

        foreach ($this->valid_operations as $ops_id) {
            if ($ops_id == self::RBAC_OP_COPY) {
                $ops_id = ilRbacReview::_getCustomRBACOperationId("copy", $db);
            }

            if (ilRbacReview::_isRBACOperation($type_id, $ops_id, $db)) {
                continue;
            }

            $values = [
                "typ_id" => ["integer", $type_id],
                "ops_id" => ["integer", $ops_id]
            ];

            $db->insert("rbac_ta", $values);
        }

        return $environment;
    }

    public function isApplicable(Environment $environment): bool
    {
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);
        $type_id = ilObject::_getObjectTypeIdByTitle($this->type, $db);

        foreach ($this->valid_operations as $ops_id) {
            if ($ops_id == self::RBAC_OP_COPY) {
                $ops_id = ilRbacReview::_getCustomRBACOperationId("copy", $db);
            }

            if (!ilRbacReview::_isRBACOperation($type_id, $ops_id, $db)) {
                return true;
            }
        }
        return false;
    }
}
