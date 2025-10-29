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
use ilDBConstants;

class ExternalToolsRemovedObjective implements Objective
{
    public function getHash(): string
    {
        return hash("sha256", self::class);
    }

    public function getLabel(): string
    {
        return "Remove external tools admin folder";
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
        /** @var ilDBInterface $db */
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);

        $query = "
            SELECT op.ops_id
            FROM rbac_operations op
            JOIN rbac_ta ta ON ta.ops_id = op.ops_id
            JOIN object_data d ON d.obj_id = ta.typ_id AND d.`type` = 'typ' AND d.title ='extt'
        ";

        $result = $db->query($query);
        while ($row = $db->fetchAssoc($result)) {
            $objective = new ilAccessRBACOperationDeletedObjective('extt', $row['ops_id']);
            $objective->achieve($environment);
        }

        $query = "
            SELECT r.ref_id, r.obj_id
            FROM object_reference r
            JOIN object_data d ON r.obj_id = d.obj_id
            WHERE d.`type` = 'extt';
        ";

        $result = $db->query($query);
        if ($row = $db->fetchAssoc($result)) {
            $db->manipulateF("DELETE FROM rbac_pa WHERE ref_id = %s", [ilDBConstants::T_INTEGER], [$row['ref_id']]);
            $db->manipulateF("DELETE FROM rbac_fa WHERE parent = %s", [ilDBConstants::T_INTEGER], [$row['ref_id']]);
            $db->manipulateF("DELETE FROM object_reference WHERE obj_id = %s", [ilDBConstants::T_INTEGER], [$row['obj_id']]);
            $db->manipulateF("DELETE FROM object_data WHERE obj_id = %s", [ilDBConstants::T_INTEGER], [$row['obj_id']]);
        }

        return $environment;
    }

    public function isApplicable(Environment $environment): bool
    {
        /** @var ilDBInterface $db */
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);

        $result = $db->query("SELECT obj_id FROM object_data WHERE `type` = 'extt'");
        return (bool) $db->fetchAssoc($result);
    }
}
