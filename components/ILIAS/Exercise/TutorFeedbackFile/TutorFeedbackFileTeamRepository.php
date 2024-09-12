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

namespace ILIAS\Exercise\TutorFeedbackFile;

use ILIAS\Exercise\IRSS\IRSSWrapper;
use ILIAS\ResourceStorage\Collection\ResourceCollection;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use _PHPStan_95cdbe577\Nette\Neon\Exception;
use ILIAS\Exercise\IRSS\ResourceInformation;

class TutorFeedbackFileTeamRepository implements TutorFeedbackFileRepositoryInterface
{
    protected IRSSWrapper $wrapper;
    protected IRSSWrapper $collection;
    protected \ilDBInterface $db;

    public function __construct(
        IRSSWrapper $wrapper,
        \ilDBInterface $db
    ) {
        $this->db = $db;
        $this->wrapper = $wrapper;
    }

    protected function getTeamId(int $ass_id, int $user_id): int
    {
        $set = $this->db->queryF(
            "SELECT id FROM il_exc_team " .
            " WHERE ass_id = %s AND user_id = %s",
            ["integer", "integer"],
            [$ass_id, $user_id]
        );
        if ($rec = $this->db->fetchAssoc($set)) {
            return (int) $rec["id"];
        }
        return 0;
    }

    public function createCollection(int $ass_id, int $user_id): void
    {
        $team_id = $this->getTeamId($ass_id, $user_id);
        if ($team_id === 0) {
            return;
        }
        $new_id = $this->wrapper->createEmptyCollection();
        $this->db->update(
            "exc_team_data",
            [
            "feedback_rcid" => ["text", $new_id]
        ],
            [    // where
                "id" => ["integer", $team_id]
            ]
        );
    }

    public function getParticipantIdForRcid(int $ass_id, string $rcid): int
    {
        $set = $this->db->queryF(
            "SELECT id FROM exc_team_data " .
            " WHERE feedback_rcid = %s",
            ["text"],
            [$rcid]
        );
        $rec = $this->db->fetchAssoc($set);
        return (int) ($rec["id"] ?? 0);
    }


    public function getIdStringForAssIdAndUserId(int $ass_id, int $user_id): string
    {
        $team_id = $this->getTeamId($ass_id, $user_id);
        if ($team_id === 0) {
            return "";
        }
        $set = $this->db->queryF(
            "SELECT feedback_rcid FROM exc_team_data " .
            " WHERE id = %s",
            ["integer"],
            [$team_id]
        );
        $rec = $this->db->fetchAssoc($set);
        return ($rec["if_rcid"] ?? "");
    }

    public function hasCollection(int $ass_id, int $user_id): bool
    {
        $rcid = $this->getIdStringForAssIdAndUserId($ass_id, $user_id);
        return ($rcid !== "");
    }

    public function getCollection(int $ass_id, int $user_id): ?ResourceCollection
    {
        $rcid = $this->getIdStringForAssIdAndUserId($ass_id, $user_id);
        if ($rcid !== "") {
            return $this->wrapper->getCollectionForIdString($rcid);
        }
        return null;
    }

    public function count(int $ass_id, int $user_id): int
    {
        if (!is_null($collection = $this->getCollection($ass_id, $user_id))) {
            return $collection->count();
        }
        return 0;
    }

    public function deliverFile($ass_id, $participant_id, $file): void
    {
        /** @var ResourceInformation $info */
        foreach ($this->getCollectionResourcesInfo($ass_id, $participant_id) as $info) {
            if ($file === $info->getTitle()) {
                $this->wrapper->deliverFile($info->getRid());
            }
        }
        throw new \ilExerciseException("Resource $file not found.");
    }

    public function getFilenameForRid(int $ass_id, int $part_id, string $rid): string
    {
        foreach ($this->getCollectionResourcesInfo($ass_id, $part_id) as $info) {
            if ($rid === $info->getRid()) {
                $this->wrapper->deliverFile($info->getRid());
                return $info->getTitle();
            }
        }
        return "";
    }

    public function getCollectionResourcesInfo(
        int $ass_id,
        int $user_id
    ): \Generator {
        $collection = $this->getCollection($ass_id, $user_id);
        return $this->wrapper->getCollectionResourcesInfo($collection);
    }

    public function deleteCollection(
        int $ass_id,
        int $user_id,
        ResourceStakeholder $stakeholder
    ): void {
        throw new \ilExerciseException("Collection cannot be deleted for user in team assignment $ass_id.");
    }
}
