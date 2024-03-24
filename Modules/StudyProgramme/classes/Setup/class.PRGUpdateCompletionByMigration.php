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

class PRGUpdateCompletionByMigration implements Setup\Migration
{
    private const DEFAULT_AMOUNT_OF_STEPS = 10000;
    private ilDBInterface $db;

    /**
     * @var IOWrapper
     */
    private mixed $io;

    public function getLabel(): string
    {
        return "Update 'Completion By' for Course(References)";
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return self::DEFAULT_AMOUNT_OF_STEPS;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilIniFilesLoadedObjective(),
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
        $query = 'SELECT assignment_id, prg_id, usr_id, completion_by, target_obj_id FROM prg_usr_progress' . PHP_EOL
            . 'JOIN object_data ON prg_usr_progress.completion_by = object_data.obj_id' . PHP_EOL
            . 'JOIN container_reference ON object_data.obj_id = container_reference.obj_id' . PHP_EOL
            . 'WHERE object_data.type = "crsr" LIMIT 1';
        $result = $this->db->query($query);
        $row = $this->db->fetchAssoc($result);

        $crs_id = $row['target_obj_id'];

        $query = "UPDATE prg_usr_progress SET completion_by = " . $crs_id . PHP_EOL
            . "WHERE assignment_id =" . $row['assignment_id'] . PHP_EOL
            . "AND prg_id =" . $row['prg_id'] . PHP_EOL
            . "AND usr_id =" . $row['usr_id'];
        $this->db->manipulate($query);
    }

    public function getRemainingAmountOfSteps(): int
    {
        $query = 'SELECT count(completion_by) AS cnt FROM prg_usr_progress' . PHP_EOL
            . 'JOIN object_data ON prg_usr_progress.completion_by = object_data.obj_id' . PHP_EOL
            . 'WHERE object_data.type = "crsr"';

        $result = $this->db->query($query);
        $row = $this->db->fetchAssoc($result);
        return (int) $row['cnt'];
    }
}
