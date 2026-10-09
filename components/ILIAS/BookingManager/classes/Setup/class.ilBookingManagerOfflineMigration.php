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

use ILIAS\BookingManager\Setup\ilBookingManagerDBUpdateSteps;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Migration;

class ilBookingManagerOfflineMigration implements Migration
{
    protected ilDBInterface $db;

    public function getLabel(): string
    {
        return 'Migrate booking pool offline status from booking_settings.pool_offline to object_data.offline.';
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return 1000;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [new ilDatabaseUpdateStepsExecutedObjective(new ilBookingManagerDBUpdateSteps())];
    }

    public function prepare(Environment $environment): void
    {
        $this->db = $environment->getResource(Environment::RESOURCE_DATABASE);
    }

    public function step(Environment $environment): void
    {
        $table = 'booking_settings';
        if (!$this->db->tableColumnExists($table, 'pool_offline')) {
            return;
        }

        $result = $this->db->queryF(
            <<<SQL
                SELECT object_data.obj_id
                FROM object_data
                INNER JOIN $table ON object_data.obj_id = $table.booking_pool_id
                WHERE object_data.type = %s
                AND $table.pool_offline = %s
                AND (object_data.offline IS NULL OR object_data.offline <> %s)
                LIMIT 1
            SQL,
            [ilDBConstants::T_TEXT, ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER],
            ['book', 1, 1]
        );

        $row = $this->db->fetchAssoc($result);
        if (!isset($row['obj_id'])) {
            return;
        }

        $this->db->update(
            ilObject::TABLE_OBJECT_DATA,
            [
                'offline' => [ilDBConstants::T_INTEGER, 1]
            ],
            [
                'obj_id' => [ilDBConstants::T_INTEGER, (int) $row['obj_id']]
            ]
        );

        $this->db->update(
            $table,
            [
                'pool_offline' => [ilDBConstants::T_INTEGER, null]
            ],
            [
                'booking_pool_id' => [ilDBConstants::T_INTEGER, (int) $row['obj_id']]
            ]
        );
    }

    public function getRemainingAmountOfSteps(): int
    {
        if (!$this->db->tableColumnExists('booking_settings', 'pool_offline')) {
            return 0;
        }

        $result = $this->db->queryF(
            <<<SQL
                SELECT COUNT(object_data.obj_id) AS count
                FROM object_data
                INNER JOIN booking_settings ON object_data.obj_id = booking_settings.booking_pool_id
                WHERE object_data.type = %s
                AND booking_settings.pool_offline = %s
                AND (object_data.offline IS NULL OR object_data.offline <> %s)
            SQL,
            [ilDBConstants::T_TEXT, ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER],
            ['book', 1, 1]
        );

        return (int) ($this->db->fetchObject($result)?->count ?? 0);
    }
}
