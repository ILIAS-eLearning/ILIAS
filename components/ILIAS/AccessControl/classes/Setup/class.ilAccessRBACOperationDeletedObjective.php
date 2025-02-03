<?php

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

declare(strict_types=1);

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

    public function getHash(): string
    {
        return hash("sha256", self::class);
    }

    public function getLabel(): string
    {
        return "Delete rbac operation and rbac template for type $this->type and id $this->ops_id";
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

        $sql =
            "DELETE FROM rbac_ta" . PHP_EOL
            . "WHERE typ_id = " . $db->quote($type_id, "integer") . PHP_EOL
            . "AND ops_id = " . $db->quote($this->ops_id, "integer") . PHP_EOL
        ;

        $db->manipulate($sql);

        $sql =
            "DELETE FROM rbac_templates" . PHP_EOL
            . "WHERE type = " . $db->quote($this->type, "text") . PHP_EOL
            . "AND ops_id = " . $db->quote($this->ops_id, "integer") . PHP_EOL
        ;

        $db->manipulate($sql);

        return $environment;
    }

    public function isApplicable(Environment $environment): bool
    {
        return true;
    }
}
