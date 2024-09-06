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

class ilExercisePeerFeedbackMigration implements Migration
{
    protected \ilResourceStorageMigrationHelper $helper;

    public function getLabel(): string
    {
        return "Migration of peer feedback files to the resource storage service.";
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
            new \ilExcPeerReviewFileStakeholder(),
            $environment
        );
    }

    public function step(Environment $environment): void
    {
        $db = $this->helper->getDatabase();
        $r = $db->query(
            "SELECT pe.ass_id, pe.giver_id, pe.ass_id, pe.peer_id, od.owner, od.obj_id FROM exc_assignment_peer pe JOIN exc_assignment ass ON pe.ass_id = ass.id JOIN object_data od ON ass.exc_id = od.obj_id WHERE pe.migrated = 0 LIMIT 1;"
        );
        $d = $this->helper->getDatabase()->fetchObject($r);
        $exec_id = (int) $d->obj_id;
        $assignment_id = (int) $d->ass_id;
        $giver_id = (int) $d->giver_id;
        $peer_id = (int) $d->peer_id;
        $resource_owner_id = (int) $d->owner;
        $base_path = $this->buildAbsolutPath($exec_id, $assignment_id, $peer_id, $giver_id);

        if (is_dir($base_path)) {
            if ($dh = opendir($base_path)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file != '.' && $file != '..' && is_dir($base_path . '/' . $file)) {
                        if (is_numeric($file)) {
                            $crit_id = (int) $file;
                            $fb_dir = $base_path . "/" . $file;

                            $pattern = '/[^\.].*/m';
                            $rid = "";
                            if (is_dir($fb_dir)) {
                                $rid = $this->helper->moveFirstFileOfPatternToStorage(
                                    $fb_dir,
                                    $pattern,
                                    $resource_owner_id
                                );
                                if (!is_null($rid)) {
                                    $db->insert("exc_crit_file", [
                                        "ass_id" => ["integer", $assignment_id],
                                        "giver_id" => ["integer", $giver_id],
                                        "peer_id" => ["integer", $peer_id],
                                        "criteria_id" => ["integer", $crit_id],
                                        "rid" => ["text", $rid]
                                    ]);
                                }
                            }
                        }
                    }
                }
                closedir($dh);
            }
        }

        $this->helper->getDatabase()->update(
            'exc_assignment_peer',
            [
                'migrated' => ['integer', 1]
            ],
            [
                'ass_id' => ['integer', $assignment_id],
                'giver_id' => ['integer', $giver_id],
                'peer_id' => ['integer', $peer_id]
            ]
        );
    }

    public function getRemainingAmountOfSteps(): int
    {
        $r = $this->helper->getDatabase()->query(
            "SELECT count(pe.id) as amount FROM exc_assignment_peer pe JOIN exc_assignment ass ON pe.ass_id = ass.id JOIN object_data od ON ass.exc_id = od.obj_id WHERE pe.migrated = 0"
        );
        $d = $this->helper->getDatabase()->fetchObject($r);

        return (int) $d->amount;
    }

    protected function buildAbsolutPath(int $exec_id, int $assignment_id, int $peer_id, int $giver_id): string
    {
        // ilExercise/X/exc_*EXC_ID*/peer_up_*ASS_ID*/*TAKER_ID*/*GIVER_ID*/*CRIT_ID*/
        return CLIENT_DATA_DIR
            . '/ilExercise/'
            . \ilFileSystemAbstractionStorage::createPathFromId(
                $exec_id,
                "exc"
            ) . "/peer_up_$assignment_id/" . $peer_id . "/" . $giver_id;
    }
}
