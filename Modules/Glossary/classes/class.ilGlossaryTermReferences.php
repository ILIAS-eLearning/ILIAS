<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Glossary term reference
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesGlossary
 */
class ilGlossaryTermReferences
{
    /**
     * @var int glossary id
     */
    protected $glo_id;

    /**
     * @var int[] (term ids)
     */
    protected $terms = array();

    /**
     * @var ilDB
     */
    protected $db;

    /**
     * __construct
     *
     * @param int $a_glo_id glossary id
     */
    public function __construct($a_glo_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();

        $this->setGlossaryId($a_glo_id);
        if ($a_glo_id > 0) {
            $this->read();
        }
    }

    /**
     * Set glossary id
     *
     * @param int $a_val glossary id
     */
    public function setGlossaryId($a_val)
    {
        $this->glo_id = $a_val;
    }

    /**
     * Get glossary id
     *
     * @return int glossary id
     */
    public function getGlossaryId()
    {
        return $this->glo_id;
    }

    /**
     * Set terms
     *
     * @param int[] $a_val term ids
     */
    public function setTerms($a_val)
    {
        $this->terms = $a_val;
    }

    /**
     * Get terms
     *
     * @return int[] term ids
     */
    public function getTerms()
    {
        return $this->terms;
    }

    /**
     * Add term
     *
     * @param int term id
     */
    public function addTerm($a_term_id)
    {
        if (!in_array($a_term_id, $this->terms)) {
            $this->terms[] = $a_term_id;
        }
    }

    /**
     * Delete term
     *
     * @param $a_term_id
     */
    public function deleteTerm($a_term_id)
    {
        foreach ($this->terms as $k => $v) {
            if ($v == $a_term_id) {
                unset($this->terms[$k]);
            }
        }
    }


    /**
     * Read
     */
    public function read()
    {
        $set = $this->db->query("SELECT term_id FROM glo_term_reference " .
            " WHERE glo_id = " . $this->db->quote($this->getGlossaryId(), "integer"));
        while ($rec = $this->db->fetchAssoc($set)) {
            $this->addTerm($rec["term_id"]);
        }
    }

    /**
     * Update
     */
    public function update()
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
    public function delete()
    {
        $this->db->manipulate(
            "DELETE FROM glo_term_reference WHERE " .
            " glo_id = " . $this->db->quote($this->getGlossaryId(), "integer")
        );
    }

    /**
     * Delete all references of a term
     *
     * @param int $a_term_id term id
     */
    public static function deleteReferencesOfTerm($a_term_id)
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
     *
     * @param int $a_glossary_id
     * @return bool
     */
    public static function hasReferences($a_glossary_id)
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
     * Is a term referenced by one or multiple glossaries
     * @param int|int[] $a_glo_id
     * @param int $a_term_id
     * @return bool
     */
    public static function isReferenced($a_glo_id, $a_term_id)
    {
        global $DIC;

        $db = $DIC->database();
        if (!is_array($a_glo_id)) {
            $a_glo_id = array($a_glo_id);
        }
        $set = $db->query(
            $q = "SELECT * FROM glo_term_reference " .
            " WHERE " . $db->in("glo_id", $a_glo_id, false, "integer") .
            " AND term_id = " . $db->quote($a_term_id, "integer")
        );
        if ($rec = $db->fetchAssoc($set)) {
            return true;
        }
        return false;
    }

    /**
     * Lookup references of a term
     *
     * @param int $a_term_id term id
     * @return int[] glossary ids
     */
    public static function lookupReferencesOfTerm($a_term_id)
    {
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
