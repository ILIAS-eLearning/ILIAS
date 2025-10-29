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

namespace ILIAS\Administration\Setup\Objective;

use ILIAS\Setup\Environment;
use ilIniFilesLoadedObjective;
use ilDatabaseInitializedObjective;
use ilIniFile;
use ilDBInterface;
use ilAccessRBACOperationDeletedObjective;
use ILIAS\Setup\Objective;
use ilDBConstants;

readonly class DropPermissions implements Objective
{
    /**
     * @param int[] $operations
     * @param Objective[] $additional_preconditions
     */
    public function __construct(
        private string $type,
        private array $operations,
        private array $additional_preconditions = []
    ) {
    }

    public function getHash(): string
    {
        return hash("sha256", self::class . $this->type . ',' . join(',', $this->operations));
    }

    public function getLabel(): string
    {
        return 'Drop permissions ' . join(', ', $this->operations) . ' from ' . $this->type;
    }

    public function isNotable(): bool
    {
        return true;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilDatabaseInitializedObjective(),
            ...$this->additional_preconditions,
        ];
    }

    public function achieve(Environment $environment): Environment
    {
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);
        $operations = $this->operations($environment);
        $ref_id = $this->firstRefIdOfType($db, $this->type);
        if (!$ref_id) {
            return $environment;
        }

        foreach ($operations as $operation) {
            $objective = new ilAccessRBACOperationDeletedObjective($this->type, $operation);
            $objective->achieve($environment);
        }

        // rbac_pa is not cleanup by ilAccessRBACOperationDeletedObjective::class.
        $update_row = $db->prepare('UPDATE rbac_pa SET ops_id = ? WHERE rol_id = ? AND ref_id = ?', [ilDBConstants::T_TEXT, ilDBConstants::T_INTEGER]);
        $delete_row = $db->prepare('DELETE FROM rbac_pa WHERE rol_id = ? AND ref_id = ?', [ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER]);
        foreach ($db->fetchAll($db->queryF('SELECT * FROM rbac_pa WHERE ref_id = %s', [ilDBConstants::T_INTEGER], [$ref_id])) as $row) {
            $ops_id = unserialize($row['ops_id'], ['allowed_classes' => false]);
            $ops_id = array_values(array_filter($ops_id, fn($op) => !in_array($op, $operations, false)));
            if ($ops_id === []) {
                $db->execute($delete_row, [$row['rol_id'], $ref_id]);
            } else {
                $db->execute($update_row, [serialize($ops_id), $row['rol_id'], $ref_id]);
            }
        }

        return $environment;
    }

    public function isApplicable(Environment $environment): bool
    {
        return $this->operations($environment) !== [];
    }

    /**
     * @return int[]
     */
    private function operations(Environment $environment): array
    {
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);

        $s = $db->query(
            'SELECT ops_id FROM rbac_ta WHERE ' .
            $db->in('ops_id', $this->operations, false, ilDBConstants::T_INTEGER) .
            ' AND typ_id IN (SELECT obj_id FROM object_data WHERE title = ' .
            $db->quote($this->type, ilDBConstants::T_TEXT) .
            ' AND type = "typ")'
        );

        return array_map('intval', array_column($db->fetchAll($s), 'ops_id'));
    }

    private function firstRefIdOfType(ilDBInterface $db, string $type): int
    {
        $ref_query = 'SELECT ref_id FROM object_reference AS r INNER JOIN object_data AS o ON r.obj_id = o.obj_id where type = %s';
        return (int) ($db->fetchAssoc($db->queryF($ref_query, [ilDBConstants::T_TEXT], [$type]))['ref_id'] ?? 0);
    }
}
