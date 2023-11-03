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

class ilExerciseTutorTeamFeedbackFileMigration implements Migration
{
    protected \ilResourceStorageMigrationHelper $helper;

    public function getLabel(): string
    {
        return "Migration of tutor team feedback files to the resource storage service.";
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
            new \ilExcTutorTeamFeedbackFileStakeholder(),
            $environment
        );
    }

    public function step(Environment $environment): void
    {
        $db = $this->helper->getDatabase();
        $r = $this->helper->getDatabase()->query(
            "SELECT ass.id, ass.exc_id, ob.owner, te.id team_id FROM exc_assignment ass JOIN object_data ob ON ass.exc_id = ob.obj_id JOIN il_exc_team te ON te.ass_id = ass.id JOIN exc_team_data td ON te.id = td.id WHERE td.feedback_rcid IS NULL OR td.feedback_rcid = '' LIMIT 1;"
        );
        $d = $this->helper->getDatabase()->fetchObject($r);
        $exec_id = (int)$d->exc_id;
        $assignment_id = (int)$d->id;
        $resource_owner_id = (int)$d->owner;
        $team_id = (int)$d->team_id;
        $base_path = $this->buildAbsolutPath($exec_id, $assignment_id, $team_id);
        if (is_dir($base_path)) {
            $collection_id = $this->helper->moveFilesOfPathToCollection(
                $base_path,
                $resource_owner_id
            );
        } else {
            $collection = $this->helper->getCollectionBuilder()->new($resource_owner_id);
            if ($this->helper->getCollectionBuilder()->store($collection)) {
                $collection_id = $collection->getIdentification()->serialize();
            } else {
                throw new ilException("Could not build collection");
            }
        }
        $this->helper->getDatabase()->update(
            'exc_team_data',
            [
                'feedback_rcid' => ['text', $collection_id]
            ],
            [
                'id' => ['integer', $team_id]
            ]
        );
    }

    public function getRemainingAmountOfSteps(): int
    {
        $r = $this->helper->getDatabase()->query(
            "SELECT count(id) AS amount FROM exc_team_data WHERE feedback_rcid IS NULL OR feedback_rcid = ''"
        );
        $d = $this->helper->getDatabase()->fetchObject($r);

        return (int)$d->amount;
    }

    protected function buildAbsolutPath(int $exec_id, int $assignment_id, int $user_id): string
    {
        return CLIENT_DATA_DIR
            . '/ilExercise/'
            . \ilFileSystemAbstractionStorage::createPathFromId(
                $exec_id,
                "exc"
            ) . "/feedb_$assignment_id/$user_id";
    }
}
