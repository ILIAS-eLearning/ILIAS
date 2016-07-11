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
	function __construct($a_glo_id = 0)
	{
		global $DIC;

		$this->db = $DIC->database();

		$this->setGlossaryId($a_glo_id);
		if ($a_glo_id > 0)
		{
			$this->read();
		}
	}

	/**
	 * Set glossary id
	 *
	 * @param int $a_val glossary id
	 */
	function setGlossaryId($a_val)
	{
		$this->glo_id = $a_val;
	}

	/**
	 * Get glossary id
	 *
	 * @return int glossary id
	 */
	function getGlossaryId()
	{
		return $this->glo_id;
	}

	/**
	 * Set terms
	 *
	 * @param int[] $a_val term ids
	 */
	function setTerms($a_val)
	{
		$this->terms = $a_val;
	}

	/**
	 * Get terms
	 *
	 * @return int[] term ids
	 */
	function getTerms()
	{
		return $this->terms;
	}

	/**
	 * Add term
	 *
	 * @param
	 * @return
	 */
	function addTerm($a_term_id)
	{
		if (!in_array($a_term_id, $this->terms))
		{
			$this->terms[] = $a_term_id;
		}
	}


	/**
	 * Read
	 *
	 * @param
	 * @return
	 */
	function read()
	{
		$set = $this->db->query("SELECT * FROM glo_term_reference ".
			" WHERE glo_id = ".$this->db->quote($this->getGlossaryId(), "integer"));
		while ($rec = $this->db->fetchAssoc($set))
		{

		}
	}

	/**
	 *
	 *
	 * @param
	 * @return
	 */
	function update()
	{
		$this->delete();
		foreach ($this->getReferences() as $r)
		{
			$this->db->replace("glo_term_reference",
				array(
					"glo_id" => array("integer", $this->getId()),
					"term_id" => array("integer", $r),
				),
				array()
				);
		}
	}


	$ilDB->manipulate("UPDATE  SET ".
		"  = ".$ilDB->quote(, "").",".
		" WHERE  = ".$ilDB->quote(, "")
		);

}

?>