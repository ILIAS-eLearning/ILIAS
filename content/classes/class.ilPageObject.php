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
require_once("content/classes/class.ilPageContent.php");

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
	var $dom;

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

		// todo: this is for testing only
//echo htmlentities($this->xml_content);
		$this->dom =& domxml_open_mem(utf8_encode($this->xml_content));

		$page_parser = new ilPageParser($this, $this->xml_content);
		$page_parser->startParsing();

	}

	function &getDom()
	{
		return $this->dom;
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

	function &getContent($a_cont_cnt = "")
	{
		if($a_cont_cnt == "")
		{
			return $this->content;
		}
		else
		{
			$cnt = explode("_", $a_cont_cnt);
			if(isset($cnt[1]))		// content is within a container (e.g. table)
			{
				$cnt_0 = $cnt[0];
				unset($cnt[0]);
				return $this->content[$cnt_0 - 1]->getContent(implode($cnt, "_"));
			}
			else
			{
				return $this->content[$cnt[0] - 1];		// content is in page directly
			}
		}
	}

	function getXMLContent($a_utf8_encoded = false, $a_short_mode = false, $a_incl_ed_ids = false)
	{
		$xml = "";
		reset($this->content);
		foreach($this->content as $co_object)
		{
			if (get_class($co_object) == "ilparagraph")
			{
				$xml .= $co_object->getXML($a_utf8_encoded, $a_short_mode, $a_incl_ed_ids);
			}
			if (get_class($co_object) == "illmtable")
			{
				$xml .= $co_object->getXML($a_utf8_encoded, $a_short_mode, $a_incl_ed_ids);
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
		$pos = explode("_", $a_pos);
		if(isset($pos[1]))		// object of child container should be deleted
		{
			$pos_0 = $pos[0];
			unset($pos[0]);
			$this->content[$pos_0 - 1]->deleteContent(implode($pos, "_"));
		}
		else		// direct child should be deleted
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
		}
		$this->update();
	}


	function insertContent(&$a_cont_obj, $a_pos, $a_mode = IL_AFTER_PRED)
	{
		$pos = explode("_", $a_pos);
		if(isset($pos[1]))		// content should be child of a container
		{
			$pos_0 = $pos[0];
			unset($pos[0]);
			$this->content[$pos_0 - 1]->insertContent($a_cont_obj, implode($pos, "_"), $a_mode);
//echo "content:".htmlentities($this->content[$pos_0 - 1]->getXML());
		}
		else		// content should be child of page
		{
			for($cnt = count($this->content); $cnt >= 0; $cnt--)
			{
				if($cnt >= $pos[0])
				{
					$this->content[$cnt] =& $this->content[$cnt - 1];
				}
			}
			$this->content[$pos[0] - 1] =& $a_cont_obj;
		}
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
	* move content object from position $a_source before position $a_target
	* (both hierarchical content ids)
	*/
	function moveContentBefore($a_source, $a_target)
	{
		// check if source and target are within same
		// container context and source precedes target
		// -> target position must be decreased by one
		// because source is deleted
		if(ilPageContent::haveSameContainer($a_source, $a_target))
		{
			$source = explode("_", $a_source);
			$target = explode("_", $a_target);
			$child_source = $source[count($source) - 1];
			$child_target = $target[count($target) - 1];
//echo "same container";
			if($child_source <= $child_target)
			{
//echo "decreased!:".$a_target.":";
				$a_target = ilPageContent::decEdId($a_target);
//echo $a_target.":<br>";
			}
		}
		$content =& $this->getContent($a_source);
		$this->deleteContent($a_source);
		$this->insertContent($content, $a_target, IL_BEFORE_SUCC);
		$this->update();
	}

}
?>
