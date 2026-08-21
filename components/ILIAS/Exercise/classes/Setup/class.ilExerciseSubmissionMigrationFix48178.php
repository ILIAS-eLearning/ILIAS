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

class ilExerciseSubmissionMigrationFix48178 implements Migration
{
    protected \ilResourceStorageMigrationHelper $helper;

    public function getLabel(): string
    {
        return "Fixes mantis issue #48178. Run only after ilExerciseSubmissionMigration.";
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

    public function getSql(bool $count = false): string
    {
        $sub_filename = "SUBSTRING_INDEX(er.filename, '/', -1)";
        if (!$count) {
            $fields = " DISTINCT er.user_id, er.ass_id, er.team_id ";
        } else {
            $fields = " COUNT(DISTINCT er.user_id, er.ass_id, er.team_id) amount ";
        }
        $sql = "SELECT $fields
FROM exc_returned er
JOIN il_resource_revision rev ON rev.rid = er.rid
JOIN il_resource_info i ON i.rid = rev.rid AND i.version_number = rev.version_number
WHERE er.rid IS NOT NULL AND er.rid <> ''
    AND rev.title <> $sub_filename
    AND er.filename IS NOT NULL AND er.filename <> '' ORDER BY `er`.`returned_id` DESC";

        return $sql;
    }

    public function step(Environment $environment): void
    {
        $db = $this->helper->getDatabase();
        $set = $db->query($this->getSql());
        while ($rec = $db->fetchAssoc($set)) {
            $this->fixCase((int) $rec["user_id"], (int) $rec["ass_id"], (int) $rec["team_id"]);
        }
    }

    public function getRemainingAmountOfSteps(): int
    {
        $r = $this->helper->getDatabase()->query(
            $this->getSql(true)
        );
        $d = $this->helper->getDatabase()->fetchObject($r);

        return (int) $d->amount;
    }

    protected function fixCase(int $user_id, int $ass_id, int $team_id): void
    {
        $db = $this->helper->getDatabase();
        $sql = "SELECT er.returned_id, rev.title, SUBSTRING_INDEX(er.filename, '/', -1) subname, rev.rid
            FROM exc_returned er
            LEFT JOIN il_resource_revision rev ON rev.rid = er.rid
            LEFT JOIN il_resource_info i ON i.rid = rev.rid AND i.version_number = rev.version_number
            WHERE er.user_id = %s AND er.ass_id = %s AND er.team_id = %s
                AND (rev.title <> SUBSTRING_INDEX(er.filename, '/', -1) OR rev.title IS NULL)
                AND er.filename IS NOT NULL AND er.filename <> ''";
        $set = $db->queryF(
            $sql,
            ["integer", "integer", "integer"],
            [$user_id, $ass_id, $team_id]
        );
        $returned_records = [];
        $mapping = [];

        // get mappings of valid title and rid
        while ($rec = $db->fetchAssoc($set)) {
            $returned_records[] = $rec;
            if (isset($rec["rid"]) && $rec["rid"] != "" && isset($rec["title"]) && $rec["title"] != "") {
                $mapping[$rec["title"]] = $rec["rid"];  // title and rid belong together
            }
        }

        // calculate corrections
        $correction = [];
        $cnt_attached_rids = 0;
        foreach ($returned_records as $rec) {
            if (isset($mapping[$rec["subname"]]) && !in_array($mapping[$rec["subname"]], $correction)) {
                $correction[$rec["returned_id"]] = $mapping[$rec["subname"]];
                $cnt_attached_rids++;
            } else {
                $correction[$rec["returned_id"]] = "";
            }
        }

        // we need mappings and the number of mappings must match the number of corrections with rid
        if (count($mapping) > 0 && count($mapping) === $cnt_attached_rids) {
            foreach ($correction as $id => $rid) {
                $db->update(
                    "exc_returned",
                    [
                    "rid" => ["text", $rid]
                ],
                    [    // where
                        "returned_id" => ["integer", $id]
                    ]
                );
            }
        }
    }
}
