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
		if ($a_id == 0)
		{
			$this->read();
		}
	}

	/**
	* read data of content object
	*/
	function read()
	{
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
	}

	function setTerm($a_term)
	{
		$this->term = $a_term;
	}

	function setLanguage($a_language)
	{
		$this->language = $a_language;
	}

	function create()
	{
		$q = "INSERT INTO glossary_term (glo_id, term, language)".
			" VALUES ('".$this->glossary->getId()."', '".$this->term.
			"', '".$this->language."')";
		$this->ilias->db->query($q);
		$this->setId($this->ilias->db->getLastInsertId());
	}

} // END class ilGlossaryTerm

?>
