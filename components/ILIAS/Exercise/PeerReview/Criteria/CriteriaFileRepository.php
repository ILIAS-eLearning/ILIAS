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

namespace ILIAS\Exercise\PeerReview\Criteria;

use ILIAS\Repository\IRSS\IRSSWrapper;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\Exercise\InternalDataService;

class CriteriaFileRepository
{
    protected \ilLogger $log;

    public function __construct(
        protected IRSSWrapper $irss,
        protected InternalDataService $data,
        protected \ilDBInterface $db
    ) {
        global $DIC;

        $this->log = $DIC->logger()->exc();
    }

    public function deliverFileOfReview(
        int $ass_id,
        int $giver_id,
        int $peer_id,
        int $criteria_id
    ): void {
        $rid = $this->getFileRidOfReview($ass_id, $giver_id, $peer_id, $criteria_id);
        $this->irss->deliverFile($rid);
    }

    public function getFileRidOfReview(int $ass_id, int $giver_id, int $peer_id, int $criteria_id): string
    {
        $set = $this->db->queryF(
            "SELECT rid FROM exc_crit_file " .
            " WHERE ass_id = %s AND giver_id = %s AND peer_id = %s and criteria_id = %s",
            ["integer", "integer", "integer", "integer"],
            [$ass_id, $giver_id, $peer_id, $criteria_id]
        );
        $rec = $this->db->fetchAssoc($set);
        return $rec["rid"] ?? "";
    }

    public function getFile(
        int $ass_id,
        int $giver_id,
        int $peer_id,
        int $citeria_id
    ): ?CriteriaFile {
        $rid = $this->getFileRidOfReview($ass_id, $giver_id, $peer_id, $citeria_id);
        if ($rid === "") {
            return null;
        }
        $info = $this->irss->getResourceInfo($rid);
        return $this->data->criteriaFile(
            $ass_id,
            $giver_id,
            $peer_id,
            $citeria_id,
            $rid,
            $info->getTitle()
        );
    }

    public function getStream(string $rid): ?FileStream
    {
        return $this->irss->stream($rid);
    }


    public function addFromLegacyUpload(
        int $ass_id,
        array $file,
        ResourceStakeholder $stakeholder,
        int $giver_id,
        int $peer_id,
        int $criteria_id
    ): void {
        $rid = $this->irss->importFileFromLegacyUpload($file, $stakeholder);
        $this->db->replace(
            "exc_crit_file",
            [
                     "ass_id" => ["integer", $ass_id],
                     "giver_id" => ["integer", $giver_id],
                     "peer_id" => ["integer", $peer_id],
                     "criteria_id" => ["integer", $criteria_id]
            ],
            [
                "rid" => ["text", $rid]
            ]
        );

    }

    public function delete(
        int $ass_id,
        ResourceStakeholder $stakeholder,
        int $giver_id,
        int $peer_id,
        int $criteria_id
    ): void {
        $rid = $this->getFileRidOfReview($ass_id, $giver_id, $peer_id, $criteria_id);
        if ($rid !== "") {
            $this->irss->deleteResource($rid, $stakeholder);
        }
        $this->db->manipulateF(
            "DELETE FROM exc_crit_file WHERE " .
            " ass_id = %s AND giver_id = %s AND peer_id = %s and criteria_id = %s ",
            ["integer", "integer", "integer", "integer"],
            [$ass_id, $giver_id, $peer_id, $criteria_id]
        );
    }
}
