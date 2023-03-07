<?php

declare(strict_types=1);

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

namespace ILIAS\Glossary\Flashcard;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class FlashcardTermDBRepository
{
    protected \ilDBInterface $db;

    public function __construct(
        \ilDBInterface $db
    ) {
        $this->db = $db;
    }

    public function getUserEntriesForBox(
        int $box_nr,
        int $user_id,
        int $glo_id
    ): array {
        $set = $this->db->queryF(
            "SELECT * FROM glo_flashcard_term " .
            " WHERE box_nr = %s AND user_id = %s AND glo_id = %s " .
            " ORDER BY last_access ASC ",
            ["integer", "integer", "integer"],
            [$box_nr, $user_id, $glo_id]
        );

        $entries = [];
        while ($rec = $this->db->fetchAssoc($set)) {
            $entries[] = [
                "term_id" => $rec["term_id"],
                "user_id" => $rec["user_id"],
                "glo_id" => $rec["glo_id"],
                "last_access" => $rec["last_access"],
                "box_nr" => $rec["box_nr"]
            ];
        }

        return $entries;
    }

    public function getAllUserEntries(
        int $user_id,
        int $glo_id
    ): array {
        $set = $this->db->queryF(
            "SELECT * FROM glo_flashcard_term " .
            " WHERE user_id = %s AND glo_id = %s " .
            " ORDER BY last_access ASC ",
            ["integer", "integer"],
            [$user_id, $glo_id]
        );

        $entries = [];
        while ($rec = $this->db->fetchAssoc($set)) {
            $entries[] = [
                "term_id" => $rec["term_id"],
                "user_id" => $rec["user_id"],
                "glo_id" => $rec["glo_id"],
                "last_access" => $rec["last_access"],
                "box_nr" => $rec["box_nr"]
            ];
        }

        return $entries;
    }

    public function getBoxNr(
        int $term_id,
        int $user_id,
        int $glo_id
    ): int {
        $set = $this->db->queryF(
            "SELECT box_nr FROM glo_flashcard_term " .
            " WHERE term_id = %s AND user_id = %s AND glo_id = %s ",
            ["integer", "integer", "integer"],
            [$term_id, $user_id, $glo_id]
        );

        if ($rec = $this->db->fetchAssoc($set)) {
            return (int) $rec["box_nr"];
        }

        return 0;
    }

    public function createEntry(
        int $term_id,
        int $user_id,
        int $glo_id,
        int $box_nr,
        string $date
    ): void {
        $this->db->insert("glo_flashcard_term", [
            "term_id" => ["integer", $term_id],
            "user_id" => ["integer", $user_id],
            "glo_id" => ["integer", $glo_id],
            "last_access" => ["date", $date],
            "box_nr" => ["integer", $box_nr]
        ]);
    }

    public function updateEntry(
        int $term_id,
        int $user_id,
        int $glo_id,
        int $box_nr,
        string $date
    ): void {
        $this->db->update("glo_flashcard_term", [
            "last_access" => ["date", $date],
            "box_nr" => ["integer", $box_nr]
        ], [
            "term_id" => ["integer", $term_id],
            "user_id" => ["integer", $user_id],
            "glo_id" => ["integer", $glo_id]
        ]);
    }

    public function deleteEntries(
        int $glo_id,
        int $user_id
    ): void {
        $q = "DELETE FROM glo_flashcard_term " .
            " WHERE glo_id = " . $this->db->quote($glo_id, "integer") .
            " AND user_id = " . $this->db->quote($user_id, "integer");
        $this->db->manipulate($q);
    }

    public function deleteAllUserEntries(
        int $user_id
    ): void {
        $q = "DELETE FROM glo_flashcard_term " .
            " WHERE user_id = " . $this->db->quote($user_id, "integer");
        $this->db->manipulate($q);
    }

    public function deleteAllGlossaryEntries(
        int $glo_id
    ): void {
        $q = "DELETE FROM glo_flashcard_term " .
            " WHERE glo_id = " . $this->db->quote($glo_id, "integer");
        $this->db->manipulate($q);
    }

    public function deleteAllTermEntries(
        int $term_id
    ): void {
        $q = "DELETE FROM glo_flashcard_term " .
            " WHERE term_id = " . $this->db->quote($term_id, "integer");
        $this->db->manipulate($q);
    }
}
