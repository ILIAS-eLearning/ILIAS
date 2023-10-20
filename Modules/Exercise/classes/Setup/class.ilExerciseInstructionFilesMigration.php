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

//namespace ILIAS\Exercise\Setup;

use ILIAS\ResourceStorage\Collection\ResourceCollection;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Migration;
use ILIAS\Setup\Objective;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilExerciseInstructionFilesMigration implements Migration
{
    protected \ilResourceStorageMigrationHelper $helper;

    public function getLabel(): string
    {
        return "Migration of Exercise Instructions Files to the Resource Storage Service.";
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
            new \ilExcInstructionFilesStakeholder(),
            $environment
        );
    }

    public function step(Environment $environment): void
    {
        $db = $this->helper->getDatabase();
        $r = $this->helper->getDatabase()->query(
            "SELECT id, exc_id, owner FROM exc_assignment JOIN object_data ON exc_id = obj_id WHERE if_rcid IS NULL OR if_rcid = '' LIMIT 1;"
        );
        $d = $this->helper->getDatabase()->fetchObject($r);
        $exec_id = (int)$d->exc_id;
        $assignment_id = (int)$d->id;
        $resource_owner_id = (int)$d->owner;
        $base_path = $this->buildAbsolutPath($exec_id, $assignment_id);
        $collection_id = $this->helper->moveFilesOfPathToCollection(
            $base_path,
            $resource_owner_id
        );
        $this->helper->getDatabase()->update(
            'exc_assignment',
            [
                'if_rcid' => ['text', $collection_id]
            ],
            [
                'id' => ['integer', $assignment_id],
                'exc_id' => ['integer', $exec_id],
            ]
        );
    }

    public function getRemainingAmountOfSteps(): int
    {
        $r = $this->helper->getDatabase()->query(
            "SELECT count(id) AS amount FROM exc_assignment WHERE if_rcid IS NULL OR if_rcid = ''"
        );
        $d = $this->helper->getDatabase()->fetchObject($r);

        return (int)$d->amount;
    }

    protected function buildAbsolutPath(int $exec_id, int $assignment_id): string
    {
        return CLIENT_WEB_DIR
            . '/ilExercise/'
            . \ilFileSystemAbstractionStorage::createPathFromId(
                $exec_id,
                "exc"
            ) . "/ass_$assignment_id";
    }
}
