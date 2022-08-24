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

/**
 * Glossary term reference
 * @author Alexander Killing <killing@leifos.de>
 */
class ilGlossaryTermReferences
{
    protected int $glo_id;
    /** @var int[] (term ids) */
    protected array $terms = array();
    protected ilDBInterface $db;

    public function __construct(int $a_glo_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();

        $this->setGlossaryId($a_glo_id);
        if ($a_glo_id > 0) {
            $this->read();
        }
    }

    public function setGlossaryId(int $a_val): void
    {
        $this->glo_id = $a_val;
    }

    public function getGlossaryId(): int
    {
        return $this->glo_id;
    }

    /**
     * @param int[] $a_val term ids
     */
    public function setTerms(array $a_val): void
    {
        $this->terms = $a_val;
    }

    /**
     * @return int[] term ids
     */
    public function getTerms(): array
    {
        return $this->terms;
    }

    public function addTerm(int $a_term_id): void
    {
        if (!in_array($a_term_id, $this->terms)) {
            $this->terms[] = $a_term_id;
        }
    }

    public function deleteTerm(int $a_term_id): void
    {
        foreach ($this->terms as $k => $v) {
            if ($v == $a_term_id) {
                unset($this->terms[$k]);
            }
        }
    }

    public function read(): void
    {
        $set = $this->db->query("SELECT term_id FROM glo_term_reference " .
            " WHERE glo_id = " . $this->db->quote($this->getGlossaryId(), "integer"));
        while ($rec = $this->db->fetchAssoc($set)) {
            $this->addTerm($rec["term_id"]);
        }
    }

    public function update(): void
    {
        $this->delete();
        foreach ($this->getTerms() as $t) {
            $this->db->replace(
                "glo_term_reference",
                array(
                    "glo_id" => array("integer", $this->getGlossaryId()),
                    "term_id" => array("integer", $t),
                ),
                array()
            );
        }
    }

    /**
     * Delete references (of glossary)
     */
    public function delete(): void
    {
        $this->db->manipulate(
            "DELETE FROM glo_term_reference WHERE " .
            " glo_id = " . $this->db->quote($this->getGlossaryId(), "integer")
        );
    }

    /**
     * Delete all references of a term
     */
    public static function deleteReferencesOfTerm(int $a_term_id): void
    {
        global $DIC;

        $db = $DIC->database();
        $db->manipulate(
            "DELETE FROM glo_term_reference WHERE " .
            " term_id = " . $db->quote($a_term_id, "integer")
        );
    }


    /**
     * Check if a glossary uses references
     */
    public static function hasReferences(int $a_glossary_id): bool
    {
        global $DIC;

        $db = $DIC->database();
        $set = $db->query(
            "SELECT * FROM glo_term_reference  " .
            " WHERE glo_id = " . $db->quote($a_glossary_id, "integer")
        );
        if ($rec = $db->fetchAssoc($set)) {
            return true;
        }
        return false;
    }

    /**
     * Is a term referenced by a set of glossaries
     * @param int[] $a_glo_id glossary ids
     * @param int $a_term_id
     * @return bool
     */
    public static function isReferenced(
        array $a_glo_id,
        int $a_term_id
    ): bool {
        global $DIC;

        $db = $DIC->database();
        $set = $db->query(
            "SELECT * FROM glo_term_reference " .
            " WHERE " . $db->in("glo_id", $a_glo_id, false, "integer") .
            " AND term_id = " . $db->quote($a_term_id, "integer")
        );
        if ($db->fetchAssoc($set)) {
            return true;
        }
        return false;
    }

    /**
     * @param int $a_term_id term id
     * @return int[] glossary ids
     */
    public static function lookupReferencesOfTerm(
        int $a_term_id
    ): array {
        global $DIC;

        $db = $DIC->database();
        $set = $db->query(
            $q = "SELECT DISTINCT glo_id FROM glo_term_reference " .
            " WHERE term_id = " . $db->quote($a_term_id, "integer")
        );
        $glos = array();
        while ($rec = $db->fetchAssoc($set)) {
            $glos[] = $rec["glo_id"];
        }
        return $glos;
    }
}
