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
class FlashcardBoxDBRepository
{
    protected \ilDBInterface $db;

    public function __construct(
        \ilDBInterface $db
    ) {
        $this->db = $db;
    }

    public function getEntry(
        int $box_nr,
        int $user_id,
        int $glo_id
    ): array {
        $set = $this->db->queryF(
            "SELECT * FROM glo_flashcard_box " .
            " WHERE box_nr = %s AND user_id = %s AND glo_id = %s ",
            ["integer", "integer", "integer"],
            [$box_nr, $user_id, $glo_id]
        );

        $entry = [];
        if ($rec = $this->db->fetchAssoc($set)) {
            $entry = [
                "box_nr" => $rec["box_nr"],
                "user_id" => $rec["user_id"],
                "glo_id" => $rec["glo_id"],
                "last_access" => $rec["last_access"],
            ];
        }

        return $entry;
    }

    public function createOrUpdateEntry(
        int $box_nr,
        int $user_id,
        int $glo_id,
        string $date
    ): void {
        $this->db->replace(
            "glo_flashcard_box",
            [
            "box_nr" => ["integer", $box_nr],
            "user_id" => ["integer", $user_id],
            "glo_id" => ["integer", $glo_id]
            ],
            [
            "last_access" => ["date", $date]
            ]
        );
    }

    public function deleteEntries(
        int $glo_id,
        int $user_id
    ): void {
        $q = "DELETE FROM glo_flashcard_box " .
            " WHERE glo_id = " . $this->db->quote($glo_id, "integer") .
            " AND user_id = " . $this->db->quote($user_id, "integer");
        $this->db->manipulate($q);
    }

    public function deleteAllUserEntries(
        int $user_id
    ): void {
        $q = "DELETE FROM glo_flashcard_box " .
            " WHERE user_id = " . $this->db->quote($user_id, "integer");
        $this->db->manipulate($q);
    }

    public function deleteAllGlossaryEntries(
        int $glo_id
    ): void {
        $q = "DELETE FROM glo_flashcard_box " .
            " WHERE glo_id = " . $this->db->quote($glo_id, "integer");
        $this->db->manipulate($q);
    }
}
