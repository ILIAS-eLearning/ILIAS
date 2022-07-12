<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

use ILIAS\Setup;
use ILIAS\Setup\Environment;

class ilAccessRolePermissionSetObjective implements Setup\Objective
{
    protected const RBAC_OP_COPY = 99;

    protected int $role_id;
    protected string $type;
    protected array $ops;
    protected int $ref_id;

    public function __construct(int $role_id, string $type, array $ops, int $ref_id)
    {
        $this->role_id = $role_id;
        $this->type = $type;
        $this->ops = $ops;
        $this->ref_id = $ref_id;
    }

    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    public function getLabel() : string
    {
        $ops = implode(",", $this->ops);
        return "Set role permission (role id=$this->role_id;type=$this->type;ops=$ops;ref id=$this->ref_id)";
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

        foreach ($this->ops as $ops_id) {
            if ($ops_id == self::RBAC_OP_COPY) {
                $ops_id = ilRbacReview::_getCustomRBACOperationId('copy');
            }

            $db->replace(
                'rbac_templates',
                [
                    'rol_id' => ['integer', $this->role_id],
                    'type' => ['text', $this->type],
                    'ops_id' => ['integer', $ops_id],
                    'parent' => ['integer', $this->ref_id]
                ],
                []
            );
        }

        return $environment;
    }

    public function isApplicable(Environment $environment) : bool
    {
        return true;
    }
}
