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
use ILIAS\Setup\CLI\IOWrapper;

class PRGUpdateRestartedSourceMigration implements Setup\Migration
{
    private const DEFAULT_AMOUNT_OF_STEPS = 1;
    private ilDBInterface $db;

    /**
     * @var IOWrapper
     */
    private mixed $io;

    public function getLabel(): string
    {
        return "Update 'Assigned By' for restarted assignments";
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return self::DEFAULT_AMOUNT_OF_STEPS;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilDatabaseInitializedObjective(),
            new ilDatabaseUpdatedObjective()
        ];
    }

    public function prepare(Environment $environment): void
    {
        $this->db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);
    }

    /**
     * @throws Exception
     */
    public function step(Environment $environment): void
    {
        $query = "UPDATE prg_usr_assignments SET last_change_by = " . ilPRGAssignment::AUTO_ASSIGNED_BY_RESTART . PHP_EOL
            . "WHERE id in (SELECT restarted_assignment_id FROM prg_usr_assignments WHERE restarted_assignment_id != -1)" . PHP_EOL
            . "AND restarted_assignment_id NOT IN (SELECT id FROM prg_usr_assignments WHERE last_change_by = " . ilPRGAssignment::AUTO_ASSIGNED_BY_RESTART . ")";
        $this->db->manipulate($query);
    }

    public function getRemainingAmountOfSteps(): int
    {
        $query = "SELECT count(restarted_assignment_id) AS cnt FROM prg_usr_assignments" . PHP_EOL
            . " WHERE restarted_assignment_id != -1" . PHP_EOL
            . "AND restarted_assignment_id NOT IN (SELECT id FROM prg_usr_assignments WHERE last_change_by = " . ilPRGAssignment::AUTO_ASSIGNED_BY_RESTART . ");";

        $result = $this->db->query($query);
        $row = $this->db->fetchAssoc($result);
        return (int) $row['cnt'] > 0 ? 1 : 0;
    }
}
