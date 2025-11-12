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

use ILIAS\Setup\Environment;
use ILIAS\Setup\Migration;

class ilMobLastChangeMigration implements Migration
{
    protected ilDBInterface $db;

    public function getLabel(): string
    {
        return 'Migration for setting date of last change of existing media objects to their creation date.';
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return 10000;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new \ilDatabaseInitializedObjective(),
            new \ilDatabaseUpdatedObjective()
        ];
    }

    public function prepare(Environment $environment): void
    {
        $this->db = $environment->getResource(Environment::RESOURCE_DATABASE);
    }

    public function step(Environment $environment): void
    {
        $res = $this->db->query(
            "SELECT mob_data.id AS mob_id, object_data.create_date AS date FROM mob_data" .
            " LEFT JOIN object_data ON object_data.obj_id = mob_data.id" .
            " WHERE last_change = 0 ORDER BY mob_id LIMIT 1"
        );
        if (!($row = $this->db->fetchAssoc($res))) {
            return;
        }
        $mob_id = (int) $row['mob_id'];
        $date = new DateTimeImmutable((string) $row['date']);

        $this->db->update(
            'mob_data',
            ['last_change' => ['integer', $date->getTimestamp()]],
            ['id' => ['integer', $mob_id]]
        );
    }

    public function getRemainingAmountOfSteps(): int
    {
        $res = $this->db->query(
            "SELECT COUNT(mob_data.id) AS count FROM mob_data JOIN object_data ON object_data.obj_id = mob_data.id" .
            " WHERE last_change = 0"
        );
        if ($row = $this->db->fetchAssoc($res)) {
            return $row['count'] ?? 0;
        }
        return 0;
    }
}
