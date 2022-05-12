<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

use ILIAS\Setup;
use ILIAS\Setup\Environment;

class ilAccessRBACOperationOrderUpdatedObjective implements Setup\Objective
{
    protected string $operation;
    protected int $pos;

    public function __construct(string $operation, int $pos)
    {
        $this->operation = $operation;
        $this->pos = $pos;
    }

    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    public function getLabel() : string
    {
        return "Update operation order (operation=$this->operation;pos=$this->pos)";
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

        $db->update(
            'rbac_operations',
            ['op_order' => ["integer", $this->pos]],
            ["operation" => ["text", $this->operation]]
        );

        return $environment;
    }

    public function isApplicable(Environment $environment) : bool
    {
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);

        $sql =
            "SELECT ops_id" . PHP_EOL
            . "FROM rbac_operations" . PHP_EOL
            . "WHERE operation = " . $db->quote($this->operation, "text") . PHP_EOL
        ;

        return $db->numRows($db->query($sql)) == 1;
    }
}
