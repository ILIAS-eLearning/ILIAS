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
	var $short_text;
	var $nr;

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
		$this->setShortText($def_rec["short_text"]);
		$this->setNr($def_rec["nr"]);

		$this->page_object =& new ilPageObject("gdf", $this->id);
		$this->meta_data =& new ilMetaData("gdf", $this->id);
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

	function setShortText($a_text)
	{
		$this->short_text = $a_text;
	}

	function getShortText()
	{
		return $this->short_text;
	}

	function setNr($a_nr)
	{
		$this->nr = $a_nr;
	}

	function getNr()
	{
		return $this->nr;
	}

	function assignPageObject(&$a_page_object)
	{
		$this->page_object =& $a_page_object;
	}

	function &getPageObject()
	{
		return $this->page_object;
	}

	function create()
	{
		$term =& new ilGlossaryTerm($this->getTermId());

		// lock glossary_definition table
		$q = "LOCK TABLES glossary_definition WRITE";
		$this->ilias->db->query($q);

		// get maximum definition number
		$q = "SELECT max(nr) AS max_nr FROM glossary_definition WHERE term_id = '".$this->getTermId()."'";
		$max_set = $this->ilias->db->query($q);
		$max_rec = $max_set->fetchRow(DB_FETCHMODE_ASSOC);
		$max = (int) $max_rec["max_nr"];

		// insert new definition record
		$q = "INSERT INTO glossary_definition (term_id, short_text, nr)".
			" VALUES ('".$this->getTermId()."','".$this->getShortText()."', '".($max + 1)."')";
		$this->ilias->db->query($q);

		// unlock glossary definition table
		$q = "UNLOCK TABLES";
		$this->ilias->db->query($q);

		$this->setId($this->ilias->db->getLastInsertId());

		// get number
		$q = "SELECT nr FROM glossary_definition WHERE id = '".$this->id."'";
		$def_set = $this->ilias->db->query($q);
		$def_rec = $def_set->fetchRow(DB_FETCHMODE_ASSOC);
		$this->setNr($def_rec["nr"]);


		$this->meta_data->setId($this->getId());
		$this->meta_data->setType($this->getType());
		$this->meta_data->create();
		$this->page_object =& new ilPageObject("gdf");
		$this->page_object->setId($this->getId());
		$this->page_object->setParentId($term->getGlossaryId());
		$this->page_object->create();
	}

	function delete()
	{
		// lock glossary_definition table
		$q = "LOCK TABLES glossary_definition WRITE";
		$this->ilias->db->query($q);

		// be sure to get the right number
		$q = "SELECT * FROM glossary_definition WHERE id = '".$this->id."'";
		$def_set = $this->ilias->db->query($q);
		$def_rec = $def_set->fetchRow(DB_FETCHMODE_ASSOC);
		$this->setNr($def_rec["nr"]);

		// update numbers of other definitions
		$q = "UPDATE glossary_definition SET ".
			" nr = nr - 1 ".
			" WHERE term_id = '".$this->getTermId()."' ".
			" AND nr > ".$this->getNr();
		$this->ilias->db->query($q);

		// delete current definition
		$q = "DELETE FROM glossary_definition ".
			" WHERE id = '".$this->getId()."' ";
		$this->ilias->db->query($q);

		// unlock glossary_definition table
		$q = "UNLOCK TABLES";
		$this->ilias->db->query($q);

		// delete page and meta data
		$this->page_object->delete();
		$this->meta_data->delete();

	}


	function moveUp()
	{
		// lock glossary_definition table
		$q = "LOCK TABLES glossary_definition WRITE";
		$this->ilias->db->query($q);

		// be sure to get the right number
		$q = "SELECT * FROM glossary_definition WHERE id = '".$this->id."'";
		$def_set = $this->ilias->db->query($q);
		$def_rec = $def_set->fetchRow(DB_FETCHMODE_ASSOC);
		$this->setNr($def_rec["nr"]);

		if ($this->getNr() < 2)
		{
			$q = "UNLOCK TABLES";
			$this->ilias->db->query($q);
			return;
		}

		// update numbers of other definitions
		$q = "UPDATE glossary_definition SET ".
			" nr = nr + 1 ".
			" WHERE term_id = '".$this->getTermId()."' ".
			" AND nr = ".($this->getNr() - 1);
		$this->ilias->db->query($q);

		// delete current definition
		$q = "UPDATE glossary_definition SET ".
			" nr = nr - 1 ".
			" WHERE term_id = '".$this->getTermId()."' ".
			" AND id = ".$this->getId();
		$this->ilias->db->query($q);

		// unlock glossary_definition table
		$q = "UNLOCK TABLES";
		$this->ilias->db->query($q);

	}


	function moveDown()
	{
		// lock glossary_definition table
		$q = "LOCK TABLES glossary_definition WRITE";
		$this->ilias->db->query($q);

		// be sure to get the right number
		$q = "SELECT * FROM glossary_definition WHERE id = '".$this->id."'";
		$def_set = $this->ilias->db->query($q);
		$def_rec = $def_set->fetchRow(DB_FETCHMODE_ASSOC);
		$this->setNr($def_rec["nr"]);

		// get max number
		$q = "SELECT max(nr) as max_nr FROM glossary_definition WHERE term_id = '".$this->getTermId()."'";
		$max_set = $this->ilias->db->query($q);
		$max_rec = $max_set->fetchRow(DB_FETCHMODE_ASSOC);

		if ($this->getNr() >= $max_rec["max_nr"])
		{
			$q = "UNLOCK TABLES";
			$this->ilias->db->query($q);
			return;
		}

		// update numbers of other definitions
		$q = "UPDATE glossary_definition SET ".
			" nr = nr - 1 ".
			" WHERE term_id = '".$this->getTermId()."' ".
			" AND nr = ".($this->getNr() + 1);
		$this->ilias->db->query($q);

		// delete current definition
		$q = "UPDATE glossary_definition SET ".
			" nr = nr + 1 ".
			" WHERE term_id = '".$this->getTermId()."' ".
			" AND id = ".$this->getId();
		$this->ilias->db->query($q);

		// unlock glossary_definition table
		$q = "UNLOCK TABLES";
		$this->ilias->db->query($q);

	}


	function update()
	{
		$q = "UPDATE glossary_definition SET ".
			" term_id = '".$this->getTermId()."', ".
			" nr = '".$this->getNr()."', ".
			" short_text = '".$this->getShortText()."' ".
			" WHERE id = '".$this->getId()."'";
		$this->ilias->db->query($q);
	}

	function updateMetaData()
	{
		$this->meta_data->update();
	}

	/**
	* static
	*/
	function getDefinitionList($a_term_id)
	{
		$defs = array();
		$q = "SELECT * FROM glossary_definition WHERE term_id ='".$a_term_id."' ORDER BY nr";
		$def_set = $this->ilias->db->query($q);
		while ($def_rec = $def_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$defs[] = array("term_id" => $def_rec["term_id"],
				"page_id" => $def_rec["page_id"], "id" => $def_rec["id"],
				"short_text" => $def_rec["short_text"], "nr" => $def_rec["nr"]);
		}
		return $defs;
	}

} // END class ilGlossaryDefinition

?>
