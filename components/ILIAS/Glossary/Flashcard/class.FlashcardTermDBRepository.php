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
class FlashcardTermDBRepository
{
    public function __construct(
        protected \ilDBInterface $db,
        protected InternalDataService $data_service
    ) {
    }

    protected function getFromRecord(array $rec): Term
    {
        return $this->data_service->flashcardTerm(
            (int) $rec["term_id"],
            (int) $rec["user_id"],
            (int) $rec["glo_id"],
            (int) $rec["box_nr"],
            $rec["last_access"]
        );
    }

    public function getEntry(
        int $term_id,
        int $user_id,
        int $glo_id
    ): ?Term {
        $set = $this->db->queryF(
            "SELECT * FROM glo_flashcard_term " .
            " WHERE term_id = %s AND user_id = %s AND glo_id = %s ",
            ["integer", "integer", "integer"],
            [$term_id, $user_id, $glo_id]
        );

        if ($rec = $this->db->fetchAssoc($set)) {
            return $this->getFromRecord($rec);
        }

        return null;
    }

    /**
     * @return Term[]
     */
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
            $entries[] = $this->getFromRecord($rec);
        }

        return $entries;
    }

    /**
     * @return Term[]
     */
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
            $entries[] = $this->getFromRecord($rec);
        }

        return $entries;
    }

    public function createEntry(
        Term $term
    ): void {
        $this->db->insert("glo_flashcard_term", [
            "term_id" => ["integer", $term->getTermId()],
            "user_id" => ["integer", $term->getUserId()],
            "glo_id" => ["integer", $term->getGloId()],
            "last_access" => ["date", $term->getLastAccess()],
            "box_nr" => ["integer", $term->getBoxNr()]
        ]);
    }

    public function updateEntry(
        Term $term
    ): void {
        $this->db->update("glo_flashcard_term", [
            "last_access" => ["date", $term->getLastAccess()],
            "box_nr" => ["integer", $term->getBoxNr()]
        ], [
            "term_id" => ["integer", $term->getTermId()],
            "user_id" => ["integer", $term->getUserId()],
            "glo_id" => ["integer", $term->getGloId()]
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
