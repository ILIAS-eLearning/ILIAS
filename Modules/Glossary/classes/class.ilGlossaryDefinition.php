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

require_once("./Services/COPage/classes/class.ilPageObject.php");

/**
* Class ilGlossaryDefinition
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesGlossary
*/
class ilGlossaryDefinition
{
	var $ilias;
	var $lng;
	var $tpl;

	var $id;
	var $term_id;
	var $glo_id;
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
		global $ilDB;
		
		$q = "SELECT * FROM glossary_definition WHERE id = ".
			$ilDB->quote($this->id, "integer");
		$def_set = $ilDB->query($q);
		$def_rec = $ilDB->fetchAssoc($def_set);

		$this->setTermId($def_rec["term_id"]);
		$this->setShortText($def_rec["short_text"]);
		$this->setNr($def_rec["nr"]);

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

	/**
	* get title of content object
	*
	* @return	string		title
	*/
	function getTitle()
	{
		return $this->title;
	}

	/**
	* set title of content object
	*/
	function setTitle($a_title)
	{
		$this->title = $a_title;
	}

	/**
	* get description of content object
	*
	* @return	string		description
	*/
	function getDescription()
	{
		return $this->description;
	}

	/**
	* set description of content object
	*/
	function setDescription($a_description)
	{
		$this->description = $a_description;
	}

	function create($a_upload = false)
	{
		global $ilDB;
		
		$term =& new ilGlossaryTerm($this->getTermId());

		$this->setId($ilDB->nextId("glossary_definition"));
		
		// lock glossary_definition table
		ilDB::_lockTables(array('glossary_definition' => 'WRITE'));

		// get maximum definition number
		$q = "SELECT max(nr) AS max_nr FROM glossary_definition WHERE term_id = ".
			$ilDB->quote($this->getTermId(), "integer");
		$max_set = $ilDB->query($q);
		$max_rec = $ilDB->fetchAssoc($max_set);
		$max = (int) $max_rec["max_nr"];

		// insert new definition record
		$ilDB->manipulate("INSERT INTO glossary_definition (id, term_id, short_text, nr)".
			" VALUES (".
			$ilDB->quote($this->getId(), "integer").",".
			$ilDB->quote($this->getTermId(), "integer").",".
			$ilDB->quote($this->getShortText(), "text").", ".
			$ilDB->quote(($max + 1), "integer").")");

		// unlock glossary definition table
		ilDB::_unlockTables();

		// get number
		$q = "SELECT nr FROM glossary_definition WHERE id = ".
			$ilDB->quote($this->id, "integer");
		$def_set = $ilDB->query($q);
		$def_rec = $ilDB->fetchAssoc($def_set);
		$this->setNr($def_rec["nr"]);

		// meta data will be created by
		// import parser
		if (!$a_upload)
		{
			$this->createMetaData();
		}

		$this->page_object =& new ilPageObject("gdf");
		$this->page_object->setId($this->getId());
		$this->page_object->setParentId($term->getGlossaryId());
		$this->page_object->create();
	}

	function delete()
	{
		global $ilDB;
		
		// lock glossary_definition table
		ilDB::_lockTables(array('glossary_definition' => 'WRITE'));

		// be sure to get the right number
		$q = "SELECT * FROM glossary_definition WHERE id = ".
			$ilDB->quote($this->id, "integer");
		$def_set = $ilDB->query($q);
		$def_rec = $ilDB->fetchAssoc($def_set);
		$this->setNr($def_rec["nr"]);

		// update numbers of other definitions
		$ilDB->manipulate("UPDATE glossary_definition SET ".
			" nr = nr - 1 ".
			" WHERE term_id = ".$ilDB->quote($this->getTermId(), "integer")." ".
			" AND nr > ".$ilDB->quote($this->getNr(), "integer"));

		// delete current definition
		$$ilDB->manipulate("DELETE FROM glossary_definition ".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer"));

		// unlock glossary_definition table
		ilDB::_unlockTables();

		// delete page and meta data
		$this->page_object->delete();

		// delete meta data
		$this->deleteMetaData();
/*
		$nested = new ilNestedSetXML();
		$nested->init($this->getId(), $this->getType());
		$nested->deleteAllDBData();
*/
	}


	function moveUp()
	{
		global $ilDB;
		
		// lock glossary_definition table
		ilDB::_lockTables(array('glossary_definition' => 'WRITE'));

		// be sure to get the right number
		$q = "SELECT * FROM glossary_definition WHERE id = ".
			$ilDB->quote($this->id, "integer");
		$def_set = $ilDB->query($q);
		$def_rec = $ilDB->fetchAssoc($def_set);
		$this->setNr($def_rec["nr"]);

		if ($this->getNr() < 2)
		{
			ilDB::_unlockTables();
			return;
		}

		// update numbers of other definitions
		$ilDB->manipulate("UPDATE glossary_definition SET ".
			" nr = nr + 1 ".
			" WHERE term_id = ".$ilDB->quote($this->getTermId(), "integer")." ".
			" AND nr = ".$ilDB->quote(($this->getNr() - 1), "integer"));

		// delete current definition
		$ilDB->manipulate("UPDATE glossary_definition SET ".
			" nr = nr - 1 ".
			" WHERE term_id = ".$ilDB->quote($this->getTermId(), "integer")." ".
			" AND id = ".$ilDB->quote($this->getId(), "integer"));

		// unlock glossary_definition table
		ilDB::_unlockTables();
	}


	function moveDown()
	{
		global $ilDB;
		
		// lock glossary_definition table
		ilDB::_lockTables(array('glossary_definition' => 'WRITE'));

		// be sure to get the right number
		$q = "SELECT * FROM glossary_definition WHERE id = ".
			$ilDB->quote($this->id, "integer");
		$def_set = $ilDB->query($q);
		$def_rec = $ilDB->fetchAssoc($def_set);
		$this->setNr($def_rec["nr"]);

		// get max number
		$q = "SELECT max(nr) as max_nr FROM glossary_definition WHERE term_id = ".
			$ilDB->quote($this->getTermId(), "integer");
		$max_set = $ilDB->query($q);
		$max_rec = $ilDB->fetchAssoc($max_set);

		if ($this->getNr() >= $max_rec["max_nr"])
		{
			ilDB::_unlockTables();
			return;
		}

		// update numbers of other definitions
		$ilDB->manipulate("UPDATE glossary_definition SET ".
			" nr = nr - 1 ".
			" WHERE term_id = ".$ilDB->quote($this->getTermId(), "integer")." ".
			" AND nr = ".$ilDB->quote(($this->getNr() + 1), "integer"));

		// delete current definition
		$ilDB->manipulate("UPDATE glossary_definition SET ".
			" nr = nr + 1 ".
			" WHERE term_id = ".$ilDB->quote($this->getTermId(), "integer")." ".
			" AND id = ".$ilDB->quote($this->getId(), "integer"));

		// unlock glossary_definition table
		ilDB::_unlockTables();

	}


	function update()
	{
		global $ilDB;
		
		$this->updateMetaData();

		$ilDB->manipulate("UPDATE glossary_definition SET ".
			" term_id = ".$ilDB->quote($this->getTermId(), "integer").", ".
			" nr = ".$ilDB->quote($this->getNr(), "integer").", ".
			" short_text = ".$ilDB->quote($this->getShortText(), "text")." ".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer"));
	}

	function updateShortText()
	{
		$this->page_object->buildDom();
		$text = $this->page_object->getFirstParagraphText();

		//$this->setShortText(ilUtil::shortenText($text, 180, true));
		$text = str_replace("<br/>", "<br>", $text);
		$text = strip_tags($text, "<br>");
		if (is_int(strpos(substr($text, 175, 10), "[tex]")))
		{
			$offset = 5;
		}
		$short = ilUtil::shortenText($text, 180 + $offset, true);
		
		// make short text longer, if tex end tag is missing
		$ltexs = strrpos($short, "[tex]");
		$ltexe = strrpos($short, "[/tex]");
		if ($ltexs > $ltexe)
		{
			$ltexe = strpos($text, "[/tex]", $ltexs);
			if ($ltexe > 0)
			{
				$short = ilUtil::shortenText($text, $ltexe+6, true);
			}
		}
		$this->setShortText($short);
		$this->update();
	}

	/**
	* static
	*/
	function getDefinitionList($a_term_id)
	{
		global $ilDB;
		
	    $defs = array();
		$q = "SELECT * FROM glossary_definition WHERE term_id = ".
			$ilDB->quote($a_term_id, "integer").
			" ORDER BY nr";
		$def_set = $ilDB->query($q);
		while ($def_rec = $ilDB->fetchAssoc($def_set))
		{
			$defs[] = array("term_id" => $def_rec["term_id"],
				"page_id" => $def_rec["page_id"], "id" => $def_rec["id"],
				"short_text" => strip_tags($def_rec["short_text"], "<br>"),
				"nr" => $def_rec["nr"]);
		}
		return $defs;
	}

	/**
	* export xml
	*/
	function exportXML(&$a_xml_writer, $a_inst)
	{
		$attrs = array();
		$a_xml_writer->xmlStartTag("Definition", $attrs);

		$this->exportXMLMetaData($a_xml_writer);
		$this->exportXMLDefinition($a_xml_writer, $a_inst);

		$a_xml_writer->xmlEndTag("Definition");
	}


	/**
	* export content objects meta data to xml (see ilias_co.dtd)
	*
	* @param	object		$a_xml_writer	ilXmlWriter object that receives the
	*										xml data
	*/
	function exportXMLMetaData(&$a_xml_writer)
	{
		$glo_id = ilGlossaryTerm::_lookGlossaryID($this->getTermId());
		include_once("Services/MetaData/classes/class.ilMD2XML.php");
		$md2xml = new ilMD2XML($glo_id, $this->getId(), $this->getType());
		$md2xml->setExportMode(true);
		$md2xml->startExport();
		$a_xml_writer->appendXML($md2xml->getXML());
	}

	/**
	*
	*/
	function modifyExportIdentifier($a_tag, $a_param, $a_value)
	{
		if ($a_tag == "Identifier" && $a_param == "Entry")
		{
			$a_value = "il_".IL_INST_ID."_gdf_".$this->getId();
		}

		return $a_value;
	}


	/**
	* export page objects meta data to xml (see ilias_co.dtd)
	*
	* @param	object		$a_xml_writer	ilXmlWriter object that receives the
	*										xml data
	*/
	function exportXMLDefinition(&$a_xml_writer, $a_inst = 0)
	{

		$this->page_object->buildDom();
		$this->page_object->insertInstIntoIDs($a_inst);
		$this->mobs_contained = $this->page_object->collectMediaObjects(false);
		$this->files_contained = $this->page_object->collectFileItems();
		$xml = $this->page_object->getXMLFromDom(false, false, false, "", true);
		$xml = str_replace("&","&amp;", $xml);
		$a_xml_writer->appendXML($xml);

		$this->page_object->freeDom();
	}

	/**
	* create meta data entry
	*/
	function createMetaData()
	{
		include_once 'Services/MetaData/classes/class.ilMDCreator.php';

		global $ilUser;

		$glo_id = ilGlossaryTerm::_lookGlossaryID($this->getTermId());
		$lang = ilGlossaryTerm::_lookLanguage($this->getTermId());
		$md_creator = new ilMDCreator($glo_id,$this->getId(),$this->getType());
		$md_creator->setTitle($this->getTitle());
		$md_creator->setTitleLanguage($lang);
		$md_creator->setDescription($this->getDescription());
		$md_creator->setDescriptionLanguage($lang);
		$md_creator->setKeywordLanguage($lang);
		$md_creator->setLanguage($lang);
		$md_creator->create();

		return true;
	}

	/**
	* update meta data entry
	*/
	function updateMetaData()
	{
		include_once("Services/MetaData/classes/class.ilMD.php");
		include_once("Services/MetaData/classes/class.ilMDGeneral.php");
		include_once("Services/MetaData/classes/class.ilMDDescription.php");

		$glo_id = ilGlossaryTerm::_lookGlossaryID($this->getTermId());
		$md =& new ilMD($glo_id, $this->getId(), $this->getType());
		$md_gen =& $md->getGeneral();
		$md_gen->setTitle($this->getTitle());

		// sets first description (maybe not appropriate)
		$md_des_ids =& $md_gen->getDescriptionIds();
		if (count($md_des_ids) > 0)
		{
			$md_des =& $md_gen->getDescription($md_des_ids[0]);
			$md_des->setDescription($this->getDescription());
			$md_des->update();
		}
		$md_gen->update();
	}

	/**
	* delete meta data entry
	*/
	function deleteMetaData()
	{
		// Delete meta data
		include_once('Services/MetaData/classes/class.ilMD.php');
		$glo_id = ilGlossaryTerm::_lookGlossaryID($this->getTermId());
		$md = new ilMD($glo_id, $this->getId(), $this->getType());
		$md->deleteAll();
	}

	/**
	* Meta data update listener
	*
	* Important note: Do never call create() or update()
	* method of ilObject here. It would result in an
	* endless loop: update object -> update meta -> update
	* object -> ...
	* Use static _writeTitle() ... methods instead.
	*
	* Even if this is not stored to db, it should be stored to the object
	* e.g. for during import parsing
	*
	* @param	string		$a_element
	*/
	function MDUpdateListener($a_element)
	{
		include_once 'Services/MetaData/classes/class.ilMD.php';

		switch($a_element)
		{
			case 'General':

				// Update Title and description
				$glo_id = ilGlossaryTerm::_lookGlossaryID($this->getTermId());
				$md =& new ilMD($glo_id, $this->getId(), $this->getType());
				$md_gen = $md->getGeneral();

				//ilObject::_writeTitle($this->getId(),$md_gen->getTitle());
				$this->setTitle($md_gen->getTitle());

				foreach($md_gen->getDescriptionIds() as $id)
				{
					$md_des = $md_gen->getDescription($id);
					//ilObject::_writeDescription($this->getId(),$md_des->getDescription());
					$this->setDescription($md_des->getDescription());
					break;
				}

				break;

			default:
		}
		return true;
	}

	/**
	* Looks up term id for a definition id
	*
	* @param	int		$a_def_id		definition id
	*/
	function _lookupTermId($a_def_id)
	{
		global $ilDB;
		
		$q = "SELECT * FROM glossary_definition WHERE id = ".
			$ilDB->quote($a_def_id, "integer");
		$def_set = $ilDB->query($q);
		$def_rec = $ilDB->fetchAssoc($def_set);

		return $def_rec["term_id"];
	}
} // END class ilGlossaryDefinition

?>
