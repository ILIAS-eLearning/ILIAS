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
use ilDatabaseInitializedObjective;
use ilDBInterface;
use ilAccessRBACOperationDeletedObjective;
use ILIAS\Setup\Objective;
use ilDBConstants;

/**
 * ALL permissions are copied from the $src_type and all existing permissions are first deleted from $dest_type.
 * Only rbac_pa is modified.
 */
readonly class CopyPermissions implements Objective
{
    /**
     * @param Objective[] $additional_preconditions
     */
    public function __construct(private string $src_type, private string $dest_type, private array $additional_preconditions)
    {
    }

    public function getHash(): string
    {
        return hash("sha256", self::class . $this->src_type . ',' . $this->dest_type);
    }

    public function getLabel(): string
    {
        return sprintf('Copy permissions from %s to %s', $this->src_type, $this->dest_type);
    }

    public function isNotable(): bool
    {
        return true;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [new ilDatabaseInitializedObjective(), ...$this->additional_preconditions];
    }

    public function achieve(Environment $environment): Environment
    {
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);
        $src_ref_id = $this->firstRefIdOfType($db, $this->src_type);
        $dest_ref_id = $this->firstRefIdOfType($db, $this->dest_type);

        if (!$src_ref_id || !$dest_ref_id) {
            return $environment;
        }

        $db->queryF(
            'DELETE FROM rbac_pa WHERE ref_id = %s',
            [ilDBConstants::T_INTEGER],
            [$dest_ref_id]
        );
        $db->manipulateF(
            'INSERT INTO rbac_pa (ref_id, ops_id, rol_id) SELECT %s AS ref_id, ops_id, rol_id FROM rbac_pa WHERE ref_id = %s',
            [ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER],
            [$dest_ref_id, $src_ref_id]
        );

        return $environment;
    }

    public function isApplicable(Environment $environment): bool
    {
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);
        $src_ref = $this->firstRefIdOfType($db, $this->src_type);
        $ret = null !== $db->fetchAssoc($db->queryF('select 1 from rbac_pa where ref_id = %s', [ilDBConstants::T_INTEGER], [$src_ref]));

        return $ret;
    }

    private function firstRefIdOfType(ilDBInterface $db, string $type): int
    {
        $ref_query = 'SELECT ref_id FROM object_reference AS r INNER JOIN object_data AS o ON r.obj_id = o.obj_id where type = %s';
        return (int) ($db->fetchAssoc($db->queryF($ref_query, [ilDBConstants::T_TEXT], [$type]))['ref_id'] ?? 0);
    }
}
