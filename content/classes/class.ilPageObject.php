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

require_once("content/classes/class.ilMetaData.php");
require_once("content/classes/class.ilLMObject.php");
require_once("content/classes/class.ilPageParser.php");

/**
* Class ilPageObject
*
* Handles PageObjects of ILIAS Learning Modules (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilPageObject extends ilLMObject
{
	var $is_alias;
	var $origin_id;
	var $content;		// array of objects (ilParagraph or ilMediaObject)
	var $id;
	var $ilias;

	/**
	* Constructor
	* @access	public
	*/
	function ilPageObject($a_id = 0)
	{
		global $ilias;

		parent::ilLMObject();
		$this->setType("pg");
		$this->id = $a_id;
		$this->ilias =& $ilias;

		$this->is_alias = false;
		$this->content = array();

		if($a_id != 0)
		{
			$this->read();
		}
	}

	/**
	*
	*/
	function read()
	{
		parent::read();

		$query = "SELECT * FROM lm_page_object WHERE page_id = '".$this->id."'";
		$pg_set = $this->ilias->db->query($query);
		$this->page_record = $pg_set->fetchRow(DB_FETCHMODE_ASSOC);
		$this->xml_content = $this->page_record["content"];
		$page_parser = new ilPageParser($this, $this->xml_content);
		$page_parser->startParsing();

	}

	/**
	* set id
	*/
	function setId($a_id)
	{
		$this->id = $a_id;
	}

	function getId()
	{
		return $this->id;
	}

	/**
	* set wether page object is an alias
	*/
	function setAlias($a_is_alias)
	{
		$this->is_alias = $a_is_alias;
	}

	function isAlias()
	{
		return $this->is_alias;
	}

	function setOriginID($a_id)
	{
		return $this->origin_id = $a_id;
	}

	function getOriginID()
	{
		return $this->origin_id;
	}

	function getimportId()
	{
		return $this->meta_data->getImportIdentifierEntryID();
	}

	function appendContent(&$a_content_obj)
	{
		$this->content[] =& $a_content_obj;
	}

	function &getContent($a_nr = 0)
	{
		if($a_nr == 0)
		{
			return $this->content;
		}
		else
		{
			return $this->content[$a_nr];
		}
	}

	function getXMLContent($a_utf8_encoded = false, $a_short_mode = false)
	{
		$xml = "";
		reset($this->content);
		foreach($this->content as $co_object)
		{
			if (get_class($co_object) == "ilparagraph")
			{
				$xml .= $co_object->getXML($a_utf8_encoded, $a_short_mode);
			}
			if (get_class($co_object) == "illmtable")
			{
				$xml .= $co_object->getXML($a_utf8_encoded, $a_short_mode);
			}
		}
		$utfstr = ($a_utf8_encoded)
			? "encoding=\"UTF-8\""
			: "";
		return "<?xml version=\"1.0\" $utfstr ?><PageObject>".$xml."</PageObject>";
	}

	function create()
	{
		// create object
		parent::create();
		$query = "INSERT INTO lm_page_object (page_id, lm_id, content) VALUES ".
			"('".$this->getId()."', '".$this->getLMId()."','".$this->getXMLContent()."')";
		$this->ilias->db->query($query);
	}

	function update()
	{
		parent::update();
		$query = "UPDATE lm_page_object ".
			"SET content = '".$this->getXMLContent()."' ".
			"WHERE page_id = '".$this->getId()."'";
		$this->ilias->db->query($query);
//echo "<br>PageObject::update:".htmlentities($this->getXMLContent()).":";
	}

	/**
	* delete content object at position $a_pos
	*/
	function deleteContent($a_pos)
	{
		$cnt = 0;
		foreach($this->content as $content)
		{
			$cnt ++;
			if ($cnt > $a_pos)
			{
				$this->content[$cnt - 2] =& $this->content[$cnt - 1];
			}
		}
		unset($this->content[count($this->content) - 1]);
		$this->update();
	}


	function insertContent(&$a_cont_obj, $a_pos)
	{
		for($cnt = count($this->content); $cnt >= 0; $cnt--)
		{
			if($cnt >= $a_pos)
			{
				$this->content[$cnt] =& $this->content[$cnt - 1];
			}
		}
		$this->content[$a_pos - 1] =& $a_cont_obj;
		$this->update();
	}

	/**
	* static
	*/
	function getPageList($lm_id)
	{
		return ilLMObject::getObjectList($lm_id, "pg");
	}


	/**
	* move content object from position $a_pos to position $a_target
	*/
	function moveContent($a_pos, $a_target)
	{
		if($a_pos < $a_target)
		{
			$a_target--;
		}
		$content =& $this->content[$a_pos - 1];
		$this->deleteContent($a_pos);
		$this->insertContent($content, $a_target);
		$this->update();
	}

}
?>
