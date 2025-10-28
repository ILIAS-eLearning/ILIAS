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

use ILIAS\Setup\Agent\NullAgent;
use ILIAS\Setup\Objective;
use ILIAS\Setup\Metrics;
use ILIAS\Setup\Config;
use ilDatabaseUpdateStepsExecutedObjective;
use ilDatabaseUpdateStepsMetricsCollectedObjective;
use ILIAS\Setup\ObjectiveCollection;
use ilObjGeneralSettings;
use ilTreeAdminNodeAddedObjective;
use ilObjServerInfo;
use ilAccessRBACOperationDeletedObjective;
use ILIAS\Administration\Setup\Objective\DropPermissions;
use ILIAS\Administration\Setup\Objective\CopyPermissions;
use ilObjBenchmark;

/**
 * Class ilAdministrationSetupAgent
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilAdministrationSetupAgent extends NullAgent
{
    private const DROP_PERMISSIONS = [
        1, // ilTreeAdminNodeAddedObjective::RBAC_OP_EDIT_PERMISSIONS,
        2, // ilTreeAdminNodeAddedObjective::RBAC_OP_VISIBLE,
        4, // ilTreeAdminNodeAddedObjective::RBAC_OP_WRITE,
    ];

    public function getUpdateObjective(?Config $config = null): Objective
    {
        $drop_visible = new DropPermissions('adm', [2]); // ilTreeAdminNodeAddedObjective::RBAC_OP_VISIBLE
        return new ObjectiveCollection(
            'Administration',
            true,
            new ilDatabaseUpdateStepsExecutedObjective(new ilAdministrationDBUpdateSteps()),
            new ilTreeAdminNodeAddedObjective(ilObjGeneralSettings::TYPE, 'General Settings'),
            new ilTreeAdminNodeAddedObjective(ilObjServerInfo::TYPE, 'Server Info'),
            new AdminNodesVisibilityRemovedObjective(),
            new ExternalToolsRemovedObjective(),
            // First drop visible then copy permissions then drop all except READ.
            new DropPermissions('adm', self::DROP_PERMISSIONS, [
                new CopyPermissions('adm', ilObjGeneralSettings::TYPE, [$drop_visible]),
                new CopyPermissions('adm', ilObjServerInfo::TYPE, [$drop_visible]),
                new CopyPermissions('adm', 'cron', [$drop_visible]),
                new CopyPermissions('adm', ilObjBenchmark::TYPE, [$drop_visible]),
            ]),
        );
    }

    public function getStatusObjective(Metrics\Storage $storage): Objective
    {
        return new ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new ilAdministrationDBUpdateSteps());
    }
}
