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

require_once("content/classes/Pages/class.ilPageObject.php");

/**
* Class ilGlossaryDefinition
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilGlossaryDefinition
{
	var $ilias;
	var $lng;
	var $tpl;

	var $id;
	var $term_id;
	var $glo_id;
	var $meta_data;
	var $page_object;

	/**
	* Constructor
	* @access	public
	*/
	function ilGlossaryDefinition($a_id = 0)
	{
		global $lng, $ilias, $tpl;

		$this->lng =& $lng;
		$this->ilias =& $ilias;
		$this->tpl =& $tpl;

		$this->id = $a_id;
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
		$q = "SELECT * FROM glossary_definition WHERE id = '".$this->id."'";
		$def_set = $this->ilias->db->query($q);
		$def_rec = $def_set->fetchRow(DB_FETCHMODE_ASSOC);

		$this->setTermId($def_rec["term_id"]);
		$this->setPageId($def_rec["language"]);

		$this->page_object =& new ilPageObject("gdf", $this->id);
	}

	function setId($a_id)
	{
		$this->id = $a_id;
	}

	function getId()
	{
		return $this->id;
	}

	function assignMetaData(&$a_meta_data)
	{
		$this->meta_data =& $a_meta_data;
	}

	function &getMetaData()
	{
		return $this->meta_data;
	}

	function getType()
	{
		return "gdf";
	}

	function setTermId($a_term_id)
	{
		$this->term_id = $a_term_id;
	}

	function getTermId()
	{
		return $this->term_id;
	}

	function &getPageObject()
	{
		return $this->page_object;
	}

	function create()
	{
		$term =& new ilGlossaryTerm($this->getTermId());

		$q = "INSERT INTO glossary_definition (term_id)".
			" VALUES ('".$this->getTermId()."')";

		$this->ilias->db->query($q);
		$this->setId($this->ilias->db->getLastInsertId());

		$this->meta_data->setId($this->getId());
		$this->meta_data->setType($this->getType());
		$this->meta_data->create();
		$this->page_object =& new ilPageObject("gdf");
		$this->page_object->setId($this->getId());
		$this->page_object->setParentId($term->getGlossaryId());
		$this->page_object->create();
	}

	function update()
	{
		$q = "UPDATE glossary_definition SET ".
			" term_id = '".$this->getTermId()."', ".
			" page_id = '".$this->getPageId()."' ".
			" WHERE id = '".$this->getId()."'";
		$this->ilias->db->query($q);
	}

	/**
	* static
	*/
	function getDefinitionList($a_term_id)
	{
		$defs = array();
		$q = "SELECT * FROM glossary_definition WHERE term_id ='".$a_term_id."'";
		$def_set = $this->ilias->db->query($q);
		while ($def_rec = $def_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$defs[] = array("term_id" => $term_rec["term_id"],
				"page_id" => $term_rec["page_id"], "id" => $term_rec["id"]);
		}
		return $defs;
	}

} // END class ilGlossaryDefinition

?>
