<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/


/**
* Class ilGlossaryTerm
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilGlossaryTerm
{
	var $ilias;
	var $lng;
	var $tpl;

	var $id;
	var $glossary;
	var $term;
	var $language;
	var $glo_id;

	/**
	* Constructor
	* @access	public
	*/
	function ilGlossaryTerm($a_id = 0)
	{
		global $lng, $ilias, $tpl;

		$this->lng =& $lng;
		$this->ilias =& $ilias;
		$this->tpl =& $tpl;

		$this->id = $a_id;
		$this->type = "term";
		if ($a_id != 0)
		{
			$this->read();
		}
	}

	/**
	* read data of content object
	*/
	function read()
	{
		$q = "SELECT * FROM glossary_term WHERE id = '".$this->id."'";
		$term_set = $this->ilias->db->query($q);
		$term_rec = $term_set->fetchRow(DB_FETCHMODE_ASSOC);

		$this->setTerm($term_rec["term"]);
		$this->setLanguage($term_rec["language"]);
		$this->setGlossaryId($term_rec["glo_id"]);

	}

	function setId($a_id)
	{
		$this->id = $a_id;
	}

	function getId()
	{
		return $this->id;
	}

	/**
	*
	*/
	function setGlossary(&$a_glossary)
	{
		$this->glossary =& $a_glossary;
		$this->setGlossaryId($a_glossary->getId());
	}

	function setGlossaryId($a_glo_id)
	{
		$this->glo_id = $a_glo_id;
	}

	function getGlossaryId()
	{
		return $this->glo_id;
	}

	function setTerm($a_term)
	{
		$this->term = $a_term;
	}

	function getTerm()
	{
		return $this->term;
	}


	function setLanguage($a_language)
	{
		$this->language = $a_language;
	}

	function getLanguage()
	{
		return $this->language;
	}

	function create()
	{
		$q = "INSERT INTO glossary_term (glo_id, term, language)".
			" VALUES ('".$this->getGlossaryId()."', '".$this->term.
			"', '".$this->language."')";
		$this->ilias->db->query($q);
		$this->setId($this->ilias->db->getLastInsertId());
	}

	function update()
	{
		$q = "UPDATE glossary_term SET ".
			" glo_id = '".$this->getGlossaryId()."', ".
			" term = '".$this->getTerm()."', ".
			" language = '".$this->getLanguage()."' ".
			" WHERE id = '".$this->getId()."'";
		$this->ilias->db->query($q);
	}

	/**
	* static
	*/
	function getTermList($a_glo_id)
	{
		$terms = array();
		$q = "SELECT * FROM glossary_term WHERE glo_id ='".$a_glo_id."' ORDER BY term";
		$term_set = $this->ilias->db->query($q);
		while ($term_rec = $term_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$terms[] = array("term" => $term_rec["term"],
				"language" => $term_rec["language"], "id" => $term_rec["id"]);
		}
		return $terms;
	}

} // END class ilGlossaryTerm

?>
