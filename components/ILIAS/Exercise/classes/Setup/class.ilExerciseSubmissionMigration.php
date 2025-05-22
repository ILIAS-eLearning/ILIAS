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

class ilExerciseSubmissionMigration implements Migration
{
    protected \ilResourceStorageMigrationHelper $helper;

    public function getLabel(): string
    {
        return "Migration of exercise submission to the resource storage service.";
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
            new \ilExcSubmissionStakeholder(),
            $environment
        );
    }

    public function step(Environment $environment): void
    {
        $db = $this->helper->getDatabase();
        $r = $db->query(
            "SELECT er.returned_id, er.obj_id, er.ass_id, od.owner, er.user_id, er.team_id FROM exc_returned er JOIN object_data od ON er.obj_id = od.obj_id WHERE er.rid IS NULL LIMIT 1;"
        );
        $d = $this->helper->getDatabase()->fetchObject($r);
        $exec_id = (int) $d->obj_id;
        $assignment_id = (int) $d->ass_id;
        $returned_id = (int) $d->returned_id;
        $resource_owner_id = (int) $d->owner;
        $user_id = ((int) $d->team_id) > 0
            ? (int) $d->team_id
            : (int) $d->user_id;
        $base_path = $this->buildAbsolutPath($exec_id, $assignment_id, $user_id);
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
            'exc_returned',
            [
                'rid' => ['text', (string) $rid]
            ],
            [
                'ass_id' => ['integer', $assignment_id],
                'returned_id' => ['integer', $returned_id]
            ]
        );
    }

    public function getRemainingAmountOfSteps(): int
    {
        $r = $this->helper->getDatabase()->query(
            "SELECT count(er.returned_id) as amount FROM exc_returned er JOIN object_data od ON er.obj_id = od.obj_id WHERE er.rid IS NULL;"
        );
        $d = $this->helper->getDatabase()->fetchObject($r);

        return (int) $d->amount;
    }

    protected function buildAbsolutPath(int $exec_id, int $assignment_id, int $user_id): string
    {
        // ilExercise/X/exc_*EXC_ID*/subm_*ASS_ID*/*USER_ID*/*TIMESTAMP*_filename.pdf
        return CLIENT_DATA_DIR
            . '/ilExercise/'
            . \ilFileSystemAbstractionStorage::createPathFromId(
                $exec_id,
                "exc"
            ) . "/subm_$assignment_id/" . $user_id;
    }
}
