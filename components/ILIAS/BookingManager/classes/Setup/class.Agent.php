<?php

declare(strict_types=1);

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

namespace ILIAS\BookingManager\Setup;

use ILIAS\Setup;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class Agent extends Setup\Agent\NullAgent
{
    public function getUpdateObjective(?Setup\Config $config = null): Setup\Objective
    {
        return new Setup\ObjectiveCollection(
            "Updates of Modules/BookingManager",
            false,
            ...$this->getObjectives()
        );
    }

    protected function getObjectives(): array
    {
        $objectives = [];

        $objectives[] = new \ilAccessCustomRBACOperationAddedObjective(
            "manage_own_reservations",
            "Manage Own Reservations",
            "object",
            3110,
            ["book"]
        );

        $objectives[] = new \ilAccessCustomRBACOperationAddedObjective(
            "manage_all_reservations",
            "Manage All Reservations",
            "object",
            3850,
            ["book"]
        );

        $objectives[] = new AccessRBACOperationClonedObjective(
            "book",
            "read",
            "manage_own_reservations"
        );

        $objectives[] = new AccessRBACOperationClonedObjective(
            "book",
            "write",
            "manage_all_reservations"
        );

        // db update steps
        $objectives[] = new \ilDatabaseUpdateStepsExecutedObjective(new ilBookingManagerDBUpdateSteps());

        $objectives[] = new \ilDatabaseUpdateStepsExecutedObjective(new ilBookingManager8HotfixDBUpdateSteps());

        return $objectives;
    }

    public function getMigrations(): array
    {
        return [
            new \ilBookingManagerObjectInfoMigration(),
            new \ilBookingManagerBookingInfoMigration()
        ];
    }

}
