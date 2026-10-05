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
        $pattern = '/[^\.].*/m';
        $latest_flat_file = is_dir($base_path)
            ? $this->getLatestFileInDirectory($base_path, $pattern)
            : null;

        if (is_dir($base_path)) {
            if ($dh = opendir($base_path)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file != '.' && $file != '..' && is_dir($base_path . '/' . $file)) {
                        if (is_numeric($file)) {
                            $crit_id = (int) $file;
                            $fb_dir = $base_path . "/" . $file;

                            $rid = null;
                            if (is_dir($fb_dir)) {
                                if ($this->getExistingRid(
                                    $assignment_id,
                                    $giver_id,
                                    $peer_id,
                                    $crit_id
                                ) !== null) {
                                    continue;
                                }
                                $latest_file = $this->getLatestFileOfPattern($fb_dir, $pattern);
                                if ($latest_file !== null) {
                                    $rid = $this->helper->movePathToStorage(
                                        $latest_file,
                                        $resource_owner_id
                                    );
                                }
                                if ($rid !== null) {
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

        if ($latest_flat_file !== null && $this->getExistingRid(
            $assignment_id,
            $giver_id,
            $peer_id,
            0
        ) === null) {
            $rid = $this->helper->movePathToStorage(
                $latest_flat_file,
                $resource_owner_id
            );
            if ($rid !== null) {
                $db->insert("exc_crit_file", [
                    "ass_id" => ["integer", $assignment_id],
                    "giver_id" => ["integer", $giver_id],
                    "peer_id" => ["integer", $peer_id],
                    "criteria_id" => ["integer", 0],
                    "rid" => ["text", $rid]
                ]);
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

    protected function getExistingRid(
        int $assignment_id,
        int $giver_id,
        int $peer_id,
        int $criteria_id
    ): ?string {
        $set = $this->helper->getDatabase()->queryF(
            "SELECT rid FROM exc_crit_file "
            . "WHERE ass_id = %s AND giver_id = %s AND peer_id = %s AND criteria_id = %s",
            ["integer", "integer", "integer", "integer"],
            [$assignment_id, $giver_id, $peer_id, $criteria_id]
        );
        $record = $this->helper->getDatabase()->fetchAssoc($set);

        return $record['rid'] ?? null;
    }

    protected function getLatestFileInDirectory(string $path, string $pattern): ?string
    {
        $latest_path = null;
        $latest_mtime = null;

        foreach (new DirectoryIterator($path) as $file_info) {
            if (
                !$file_info->isFile()
                || preg_match($pattern, $file_info->getFilename()) !== 1
            ) {
                continue;
            }
            $file_path = $file_info->getRealPath();
            if ($file_path === false) {
                continue;
            }
            $file_mtime = $file_info->getMTime();
            if ($this->isLaterFile(
                $file_mtime,
                $file_path,
                $latest_mtime,
                $latest_path
            )) {
                $latest_mtime = $file_mtime;
                $latest_path = $file_path;
            }
        }

        return $latest_path;
    }

    protected function getLatestFileOfPattern(string $path, string $pattern): ?string
    {
        $latest_path = null;
        $latest_mtime = null;
        $iterator = new RecursiveRegexIterator(
            new RecursiveDirectoryIterator($path),
            $pattern,
            RecursiveRegexIterator::MATCH
        );

        foreach ($iterator as $file_info) {
            if (!$file_info->isFile()) {
                continue;
            }
            $file_path = $file_info->getRealPath();
            if ($file_path === false) {
                continue;
            }
            $file_mtime = $file_info->getMTime();
            if ($this->isLaterFile(
                $file_mtime,
                $file_path,
                $latest_mtime,
                $latest_path
            )) {
                $latest_mtime = $file_mtime;
                $latest_path = $file_path;
            }
        }

        return $latest_path;
    }

    protected function isLaterFile(
        int $mtime,
        string $path,
        ?int $latest_mtime,
        ?string $latest_path
    ): bool {
        return $latest_mtime === null
            || $mtime > $latest_mtime
            || ($mtime === $latest_mtime && strcmp($path, (string) $latest_path) > 0);
    }
}
