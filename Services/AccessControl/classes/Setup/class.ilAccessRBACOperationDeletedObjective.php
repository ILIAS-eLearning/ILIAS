<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

use ILIAS\Setup;
use ILIAS\Setup\Environment;

class ilAccessRBACOperationDeletedObjective implements Setup\Objective
{
    protected string $type;
    protected int $ops_id;

    public function __construct(string $type, int $ops_id)
    {
        $this->type = $type;
        $this->ops_id = $ops_id;
    }

    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    public function getLabel() : string
    {
        return "Delete rbac operation and rbac template for type $this->type and id $this->ops_id";
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

        $type_id = ilObject::_getObjectTypeIdByTitle($this->type);

        $sql =
            "DELETE FROM rbac_ta" . PHP_EOL
            . "WHERE typ_id = " . $db->quote($type_id, "integer") . PHP_EOL
            . "AND ops_id = " . $db->quote($this->ops_id, "integer") . PHP_EOL
        ;

        $db->manipulate($sql);

        $sql =
            "DELETE FROM rbac_templates" . PHP_EOL
            . "WHERE type = " . $db->quote($this->type, "text") . PHP_EOL
            . "ops_id = " . $db->quote($this->ops_id, "integer") . PHP_EOL
        ;

        $db->manipulate($sql);

        return $environment;
    }

    public function isApplicable(Environment $environment) : bool
    {
        return true;
    }
}
