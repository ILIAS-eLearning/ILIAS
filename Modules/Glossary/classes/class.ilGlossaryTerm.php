<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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
* @ingroup ModulesGlossary
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
	var $import_id;

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
	* read glossary term data
	*/
	function read()
	{
		global $ilDB;
		
		$q = "SELECT * FROM glossary_term WHERE id = ".
			$ilDB->quote($this->id, "integer");
		$term_set = $ilDB->query($q);
		$term_rec = $ilDB->fetchAssoc($term_set);

		$this->setTerm($term_rec["term"]);
		$this->setImportId($term_rec["import_id"]);
		$this->setLanguage($term_rec["language"]);
		$this->setGlossaryId($term_rec["glo_id"]);

	}

	/**
	* get current term id for import id (static)
	*
	* @param	int		$a_import_id		import id
	*
	* @return	int		id
	*/
	function _getIdForImportId($a_import_id)
	{
		global $ilDB;
		
		$q = "SELECT * FROM glossary_term WHERE import_id = ".
			$ilDB->quote($a_import_id, "integer").
			" ORDER BY create_date DESC";
		$term_set = $ilDB->query($q);
		while ($term_rec = $ilDB->fetchAssoc($term_set))
		{
			$glo_id = ilGlossaryTerm::_lookGlossaryID($term_rec["id"]);

			if (ilObject::_hasUntrashedReference($glo_id))
			{
				return $term_rec["id"];
			}
		}

		return 0;
	}
	
	
	/**
	* checks wether a glossary term with specified id exists or not
	*
	* @param	int		$id		id
	*
	* @return	boolean		true, if glossary term exists
	*/
	function _exists($a_id)
	{
		global $ilDB;
		
		include_once("./Services/COPage/classes/class.ilInternalLink.php");
		if (is_int(strpos($a_id, "_")))
		{
			$a_id = ilInternalLink::_extractObjIdOfTarget($a_id);
		}

		$q = "SELECT * FROM glossary_term WHERE id = ".
			$ilDB->quote($a_id, "integer");
		$obj_set = $ilDB->query($q);
		if ($obj_rec = $ilDB->fetchAssoc($obj_set))
		{
			return true;
		}
		else
		{
			return false;
		}

	}


	/**
	* set glossary term id (= glossary item id)
	*
	* @param	int		$a_id		glossary term id
	*/
	function setId($a_id)
	{
		$this->id = $a_id;
	}


	/**
	* get term id (= glossary item id)
	*
	* @return	int		glossary term id
	*/
	function getId()
	{
		return $this->id;
	}

	/**
	* set glossary object
	*
	* @param	object		$a_glossary		glossary object
	*/
	function setGlossary(&$a_glossary)
	{
		$this->glossary =& $a_glossary;
		$this->setGlossaryId($a_glossary->getId());
	}


	/**
	* set glossary id
	*
	* @param	int		$a_glo_id		glossary id
	*/
	function setGlossaryId($a_glo_id)
	{
		$this->glo_id = $a_glo_id;
	}


	/**
	* get glossary id
	*
	* @return	int		glossary id
	*/
	function getGlossaryId()
	{
		return $this->glo_id;
	}


	/**
	* set term
	*
	* @param	string		$a_term		term
	*/
	function setTerm($a_term)
	{
		$this->term = $a_term;
	}


	/**
	* get term
	*
	* @return	string		term
	*/
	function getTerm()
	{
		return $this->term;
	}


	/**
	* set language
	*
	* @param	string		$a_language		two letter language code
	*/
	function setLanguage($a_language)
	{
		$this->language = $a_language;
	}

	/**
	* get language
	* @return	string		two letter language code
	*/
	function getLanguage()
	{
		return $this->language;
	}


	/**
	* set import id
	*/
	function setImportId($a_import_id)
	{
		$this->import_id = $a_import_id;
	}


	/**
	* get import id
	*/
	function getImportId()
	{
		return $this->import_id;
	}


	/**
	* create new glossary term
	*/
	function create()
	{
		global $ilDB;
		
		$this->setId($ilDB->nextId("glossary_term"));
		$ilDB->manipulate("INSERT INTO glossary_term (id, glo_id, term, language, import_id, create_date, last_update)".
			" VALUES (".
			$ilDB->quote($this->getId(), "integer").", ".
			$ilDB->quote($this->getGlossaryId(), "integer").", ".
			$ilDB->quote($this->term, "text").", ".
			$ilDB->quote($this->language, "text").",".
			$ilDB->quote($this->getImportId(), "text").",".
			$ilDB->now().", ".
			$ilDB->now().")");
	}


	/**
	* delete glossary term (and all its definition objects)
	*/
	function delete()
	{
		global $ilDB;
		
		require_once("./Modules/Glossary/classes/class.ilGlossaryDefinition.php");
		$defs = ilGlossaryDefinition::getDefinitionList($this->getId());
		foreach($defs as $def)
		{
			$def_obj =& new ilGlossaryDefinition($def["id"]);
			$def_obj->delete();
		}
		$ilDB->manipulate("DELETE FROM glossary_term ".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer"));
	}


	/**
	* update glossary term
	*/
	function update()
	{
		global $ilDB;
		
		$ilDB->manipulate("UPDATE glossary_term SET ".
			" glo_id = ".$ilDB->quote($this->getGlossaryId(), "integer").", ".
			" term = ".$ilDB->quote($this->getTerm(), "text").", ".
			" import_id = ".$ilDB->quote($this->getImportId(), "text").", ".
			" language = ".$ilDB->quote($this->getLanguage(), "text").", ".
			" last_update = ".$ilDB->now()." ".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer"));
	}

	/**
	* get glossary id form term id
	*/
	function _lookGlossaryID($term_id)
	{
		global $ilDB;

		$query = "SELECT * FROM glossary_term WHERE id = ".
			$ilDB->quote($term_id, "integer");
		$obj_set = $ilDB->query($query);
		$obj_rec = $ilDB->fetchAssoc($obj_set);

		return $obj_rec["glo_id"];
	}

	/**
	* get glossary term
	*/
	function _lookGlossaryTerm($term_id)
	{
		global $ilDB;

		$query = "SELECT * FROM glossary_term WHERE id = ".
			$ilDB->quote($term_id, "integer");
		$obj_set = $ilDB->query($query);
		$obj_rec = $ilDB->fetchAssoc($obj_set);

		return $obj_rec["term"];
	}
	
	/**
	* lookup term language
	*/
	function _lookLanguage($term_id)
	{
		global $ilDB;

		$query = "SELECT * FROM glossary_term WHERE id = ".
			$ilDB->quote($term_id, "integer");
		$obj_set = $ilDB->query($query);
		$obj_rec = $ilDB->fetchAssoc($obj_set);

		return $obj_rec["language"];
	}

	/**
	* static
	* 
	* @access	public
	* @param 	integer/array	$a_glo_id	array of glossary ids for meta glossaries
	* @param	string			$searchterm	searchstring
	*/
	function getTermList($a_glo_id, $searchterm = "")
	{
		global $ilDB;
		
		$terms = array();
		
		$searchterm = (!empty ($searchterm))
			? " AND ".$ilDB->like("term", "text", "%".$searchterm."%")." "
			: "";
		
		// meta glossary
		if (is_array($a_glo_id))
		{
			$where = $ilDB->in("glo_id", $a_glo_id, false, "integer");
		}
		else
		{
			$where = " glo_id = ".$ilDB->quote($a_glo_id, "integer")." ";
		}
		
		$q = "SELECT * FROM glossary_term WHERE ".$where.$searchterm." ORDER BY language, term";
		$term_set = $ilDB->query($q);

		while ($term_rec = $ilDB->fetchAssoc($term_set))
		{
			$terms[] = array("term" => $term_rec["term"],
				"language" => $term_rec["language"], "id" => $term_rec["id"], "glo_id" => $term_rec["glo_id"]);
		}
		return $terms;
	}

	/**
	* export xml
	*/
	function exportXML(&$a_xml_writer, $a_inst)
	{

		$attrs = array();
		$attrs["Language"] = $this->getLanguage();
		$attrs["Id"] = "il_".IL_INST_ID."_git_".$this->getId();
		$a_xml_writer->xmlStartTag("GlossaryItem", $attrs);

		$attrs = array();
		$a_xml_writer->xmlElement("GlossaryTerm", $attrs, $this->getTerm());

		$defs = ilGlossaryDefinition::getDefinitionList($this->getId());

		foreach($defs as $def)
		{
			$definition = new ilGlossaryDefinition($def["id"]);
			$definition->exportXML($a_xml_writer, $a_inst);
		}

		$a_xml_writer->xmlEndTag("GlossaryItem");
	}

} // END class ilGlossaryTerm

?>
