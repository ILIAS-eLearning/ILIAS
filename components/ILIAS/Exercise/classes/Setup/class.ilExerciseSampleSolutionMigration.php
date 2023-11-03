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


use ILIAS\ResourceStorage\Collection\ResourceCollection;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Migration;
use ILIAS\Setup\Objective;

class ilExerciseSampleSolutionMigration implements Migration
{
    protected \ilResourceStorageMigrationHelper $helper;

    public function getLabel(): string
    {
        return "Migration of exercise sample solutions to the resource storage service.";
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return 1000;
    }

    public function getPreconditions(Environment $environment): array
    {
        return \ilResourceStorageMigrationHelper::getPreconditions();
    }

    public function prepare(Environment $environment): void
    {
        $this->helper = new \ilResourceStorageMigrationHelper(
            new \ilExcSampleSolutionStakeholder(),
            $environment
        );
    }

    public function step(Environment $environment): void
    {
        $db = $this->helper->getDatabase();
        $r = $this->helper->getDatabase()->query(
            "SELECT id, exc_id, owner FROM exc_assignment JOIN object_data ON exc_id = obj_id WHERE solution_rid IS NULL LIMIT 1;"
        );
        $d = $this->helper->getDatabase()->fetchObject($r);
        $exec_id = (int)$d->exc_id;
        $assignment_id = (int)$d->id;
        $resource_owner_id = (int)$d->owner;
        $base_path = $this->buildAbsolutPath($exec_id, $assignment_id);
        $pattern = '/[^\.].*/m';
        $rid = "";
        if (is_dir($base_path)) {
            $rid = $this->helper->moveFirstFileOfPatternToStorage(
                $base_path,
                $pattern,
                $resource_owner_id
            );
        }
        $this->helper->getDatabase()->update(
            'exc_assignment',
            [
                'solution_rid' => ['text', (string) $rid]
            ],
            [
                'id' => ['integer', $assignment_id],
                'exc_id' => ['integer', $exec_id]
            ]
        );
    }

    public function getRemainingAmountOfSteps(): int
    {
        $r = $this->helper->getDatabase()->query(
            "SELECT count(id) AS amount FROM exc_assignment WHERE solution_rid IS NULL"
        );
        $d = $this->helper->getDatabase()->fetchObject($r);

        return (int)$d->amount;
    }

    protected function buildAbsolutPath(int $exec_id, int $assignment_id): string
    {
        return CLIENT_DATA_DIR
            . '/ilExercise/'
            . \ilFileSystemAbstractionStorage::createPathFromId(
                $exec_id,
                "exc"
            ) . "/feedb_$assignment_id/0";
    }
}
