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

class ilAccessRBACOperationOrderUpdatedObjective implements Setup\Objective
{
    protected string $operation;
    protected int $pos;

    public function __construct(string $operation, int $pos)
    {
        $this->operation = $operation;
        $this->pos = $pos;
    }

    public function getHash(): string
    {
        return hash("sha256", self::class);
    }

    public function getLabel(): string
    {
        return "Update operation order (operation=$this->operation;pos=$this->pos)";
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

        $db->update(
            'rbac_operations',
            ['op_order' => ["integer", $this->pos]],
            ["operation" => ["text", $this->operation]]
        );

        return $environment;
    }

    public function isApplicable(Environment $environment): bool
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
