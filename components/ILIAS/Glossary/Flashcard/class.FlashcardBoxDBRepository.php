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

namespace ILIAS\Glossary\Flashcard;

use ILIAS\Glossary\InternalDataService;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class FlashcardBoxDBRepository
{
    public function __construct(
        protected \ilDBInterface $db,
        protected InternalDataService $data_service
    ) {
    }

    protected function getFromRecord(array $rec): Box
    {
        return $this->data_service->flashcardBox(
            (int) $rec["box_nr"],
            (int) $rec["user_id"],
            (int) $rec["glo_id"],
            $rec["last_access"]
        );
    }

    public function getEntry(
        int $box_nr,
        int $user_id,
        int $glo_id
    ): ?Box {
        $set = $this->db->queryF(
            "SELECT * FROM glo_flashcard_box " .
            " WHERE box_nr = %s AND user_id = %s AND glo_id = %s ",
            ["integer", "integer", "integer"],
            [$box_nr, $user_id, $glo_id]
        );

        if ($rec = $this->db->fetchAssoc($set)) {
            return $this->getFromRecord($rec);
        }

        return null;
    }

    public function createOrUpdateEntry(
        Box $box
    ): void {
        $this->db->replace(
            "glo_flashcard_box",
            [
                "box_nr" => ["integer", $box->getBoxNr()],
                "user_id" => ["integer", $box->getUserId()],
                "glo_id" => ["integer", $box->getGloId()]
            ],
            [
                "last_access" => ["date", $box->getLastAccess()]
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
