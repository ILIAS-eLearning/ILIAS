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

namespace ILIAS\Exercise\Team;

use ILIAS\Exercise\InternalDataService;

/**
 * Table exc_team_data: Team
 * Table il_exc_team: Team participants (holds the sequence due to historic reasons)
 */
class TeamDBRepository
{
    protected InternalDataService $data;
    protected \ilDBInterface $db;

    public function __construct(
        \ilDBInterface $db,
        InternalDataService $data
    ) {
        $this->db = $db;
        $this->data = $data;
    }

    public function create(): int
    {
        $id = $this->db->nextId("il_exc_team");
        $this->db->insert("exc_team_data", [
            "id" => ["integer", $id]
        ]);
        return $id;
    }

    public function addUser(int $team_id, int $ass_id, int $user_id): void
    {
        $this->db->insert("il_exc_team", [
            "id" => ["integer", $team_id],
            "ass_id" => ["integer", $ass_id],
            "user_id" => ["integer", $user_id]
        ]);
    }

    public function removeUser(int $team_id, int $ass_id, int $user_id): void
    {
        $this->db->manipulateF(
            "DELETE FROM il_exc_team WHERE " .
            " id = %s AND ass_id = %s AND user_id = %s",
            ["integer", "integer", "integer"],
            [
                $team_id,
                $ass_id,
                $user_id
            ]
        );
    }

    public function getMembers(int $team_id): \Generator
    {
        $set = $this->db->queryF(
            "SELECT * FROM il_exc_team " .
            " WHERE id = %s ",
            ["integer"],
            [$team_id]
        );
        while ($rec = $this->db->fetchAssoc($set)) {
            yield $this->data->teamMember(
                (int) $rec["id"],
                (int) $rec["ass_id"],
                (int) $rec["user_id"]
            );
        }
    }

    public function getTeamForMember(int $ass_id, int $user_id): ?int
    {
        $set = $this->db->queryF(
            "SELECT id FROM il_exc_team " .
            " WHERE ass_id = %s AND user_id = %s",
            ["integer", "integer"],
            [$ass_id, $user_id]
        );
        $rec = $this->db->fetchAssoc($set);
        if (isset($rec["id"])) {
            return (int) $rec["id"];
        }
        return null;
    }

    public function getAllMemberIdsOfAssignment(int $assignment_id): \Generator
    {
        $set = $this->db->queryF(
            "SELECT DISTINCT user_id FROM il_exc_team " .
            " WHERE ass_id = %s ",
            ["integer"],
            [$assignment_id]
        );
        while ($rec = $this->db->fetchAssoc($set)) {
            yield (int) $rec["user_id"];
        }
    }

    public function getUserTeamMap(int $assignment_id): array
    {
        $map = [];
        $set = $this->db->queryF(
            "SELECT * FROM il_exc_team " .
            " WHERE ass_id = %s ",
            ["integer"],
            [$assignment_id]
        );
        while ($rec = $this->db->fetchAssoc($set)) {
            $map[(int) $rec["user_id"]] = (int) $rec["id"];
        }
        return $map;
    }
}
