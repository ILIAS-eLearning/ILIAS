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

namespace ILIAS\Exercise\SampleSolution;

use ILIAS\Exercise\IRSS\CollectionWrapper;
use ILIAS\ResourceStorage\Collection\ResourceCollection;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;

class SampleSolutionRepository
{
    protected CollectionWrapper $wrapper;
    protected CollectionWrapper $collection;
    protected \ilDBInterface $db;

    public function __construct(
        CollectionWrapper $wrapper,
        \ilDBInterface $db
    ) {
        $this->db = $db;
        $this->wrapper = $wrapper;
    }

    public function getIdStringForAssId(int $ass_id): string
    {
        $set = $this->db->queryF(
            "SELECT solution_rid FROM exc_assignment " .
            " WHERE id = %s ",
            ["integer"],
            [$ass_id]
        );
        $rec = $this->db->fetchAssoc($set);
        return ($rec["solution_rid"] ?? "");
    }

    public function hasFile(int $ass_id): bool
    {
        $rid = $this->getIdStringForAssId($ass_id);
        return ($rid !== "");
    }

    public function deliverFile(int $ass_id): void
    {
        $rid = $this->getIdStringForAssId($ass_id);
        $this->wrapper->deliverFile($rid);
    }

    public function importFromLegacyUpload(
        int $ass_id,
        array $file_input,
        ResourceStakeholder $stakeholder
    ): string {
        $rcid = $this->wrapper->importFileFromLegacyUpload(
            $file_input,
            $stakeholder
        );
        if ($rcid !== "") {
            $this->db->update(
                "exc_assignment",
                [
                "fb_file" => ["text", $file_input["name"]],
                "solution_rid" => ["text", $rcid]
            ],
                [    // where
                    "id" => ["integer", $ass_id]
                ]
            );
        }
        return $rcid;
    }

    public function clone(
        int $from_ass_id,
        int $to_ass_id
    ): void {
        $from_rid = $this->getIdStringForAssId($from_ass_id);
        $to_rid = $this->wrapper->cloneResource($from_rid);
        if ($to_rid !== "") {
            $this->db->update(
                "exc_assignment",
                [
                "solution_rid" => ["text", $to_rid]
            ],
                [    // where
                    "id" => ["integer", $to_ass_id]
                ]
            );
        }
    }
}
