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

namespace ILIAS\Administration\Setup;

use ILIAS\Setup\Environment;
use ilIniFilesLoadedObjective;
use ilDatabaseInitializedObjective;
use ilIniFile;
use ilDBInterface;
use ilAccessRBACOperationDeletedObjective;
use ILIAS\Setup\Objective;

class AdminNodesVisibilityRemovedObjective implements Objective
{
    /** @var int @see \ilTreeAdminNodeAddedObjective::RBAC_OP_VISIBLE */
    private const int RBAC_OP_VISIBLE = 2;

    public function getHash(): string
    {
        return hash("sha256", self::class);
    }

    public function getLabel(): string
    {
        return "Remove visibility permissions from admin nodes";
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
        foreach ($this->getTypesToChange($environment) as $type) {
            $objective = new ilAccessRBACOperationDeletedObjective($type, self::RBAC_OP_VISIBLE);
            $objective->achieve($environment);
        }
        return $environment;
    }

    public function isApplicable(Environment $environment): bool
    {
        return !empty($this->getTypesToChange($environment));
    }

    /**
     * Get the types of admin nodes where the visible permission needs to be removed
     * @return string[]
     */
    private function getTypesToChange(Environment $environment): array
    {
        $client_ini = $environment->getResource(Environment::RESOURCE_CLIENT_INI);
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);

        $folder_id = (int) $client_ini->readVariable('system', 'SYSTEM_FOLDER_ID');
        if (!$folder_id) {
            return [];
        }

        // get all types of admin nodes where the visible permission is still defined
        // exclude the user folder which gets a special treatment in a separate objective
        // exclude org units to keep the info screen of subunits visible without read access
        $query = "
            SELECT d1.`type`
            FROM tree t
            JOIN object_reference r ON r.ref_id = t.child
            JOIN object_data d1 ON d1.obj_id = r.obj_id
            JOIN object_data d2 ON d2.`type` = 'typ' AND d2.title = d1.`type`
            JOIN rbac_ta a ON a.typ_id = d2.obj_id AND a.ops_id = %s
            WHERE t.parent = %s
            AND d1.`type` <> 'usrf'
            AND d1.`type` <> 'orgu'
            ORDER BY  d1.`type`
        ";

        $result = $db->queryF($query, ['integer', 'integer'], [self::RBAC_OP_VISIBLE, $folder_id]);

        $types = [];
        foreach ($db->fetchAll($result) as $row) {
            $types[] = (string) $row['type'];
        }
        return $types;
    }
}
