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

require_once("content/classes/class.ilLMObject.php");
require_once("content/classes/Pages/class.ilPageObject.php");

define ("IL_CHAPTER_TITLE", "st_title");
define ("IL_PAGE_TITLE", "pg_title");
define ("IL_NO_HEADER", "none");

/**
* Class ilLMPageObject
*
* Handles Page Objects of ILIAS Learning Modules
*
* Note: This class has a member variable that contains an instance
* of class ilPageObject and provides the method getPageObject() to access
* this instance. ilPageObject handles page objects and their content.
* Page objects can be assigned to different container like learning modules
* or glossaries definitions. This class, ilLMPageObject, provides additional
* methods for the handling of page objects in learning modules.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilLMPageObject extends ilLMObject
{
	var $is_alias;
	var $origin_id;
	var $id;
	var $ilias;
	var $dom;
	var $page_object;

	/**
	* Constructor
	* @access	public
	*/
	function ilLMPageObject(&$a_content_obj, $a_id = 0)
	{
		global $ilias;

		parent::ilLMObject($a_content_obj, $a_id);
		$this->setType("pg");
		$this->id = $a_id;
		$this->ilias =& $ilias;

		$this->is_alias = false;
		$this->contains_int_link = false;
		$this->mobs_contained  = array();
		$this->files_contained  = array();

		if($a_id != 0)
		{
			$this->read();
		}
	}

	function _ilLMPageObject()
	{
		if(is_object($this->page_object))
		{
			unset($this->page_object);
		}
	}

	/**
	*
	*/
	function read()
	{
		parent::read();

		$this->page_object =& new ilPageObject($this->content_object->getType(), $this->id);
	}

	function create($a_upload = false)
	{
		parent::create($a_upload);
		if(!is_object($this->page_object))
		{
			$this->page_object =& new ilPageObject($this->content_object->getType());
		}
		$this->page_object->setId($this->getId());
		$this->page_object->setParentId($this->getLMId());
		$this->page_object->create($a_upload);
	}

	/**
	*
	*/
	function assignPageObject(&$a_page_obj)
	{
		$this->page_object =& $a_page_obj;
	}

	/**
	*
	*/
	function &getPageObject()
	{
		return $this->page_object;
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

	// only for page aliases
	function setOriginID($a_id)
	{
		return $this->origin_id = $a_id;
	}

	// only for page aliases
	function getOriginID()
	{
		return $this->origin_id;
	}

	/**
	* static
	*/
	function getPageList($lm_id)
	{
		return ilLMObject::getObjectList($lm_id, "pg");
	}


	/**
	* presentation title doesn't have to be page title, it may be
	* chapter title + page title or chapter title only, depending on settings
	*
	* @param	string	$a_mode		IL_CHAPTER_TITLE | IL_PAGE_TITLE | IL_NO_HEADER
	*/
	function _getPresentationTitle($a_pg_id, $a_mode = IL_CHAPTER_TITLE)
	{
		global $ilDB;

		// select
		$query = "SELECT * FROM lm_data WHERE obj_id = '".$a_pg_id."'";
		$pg_set = $ilDB->query($query);
		$pg_rec = $pg_set->fetchRow(DB_FETCHMODE_ASSOC);

		if($a_mode == IL_NO_HEADER)
		{
			return "";
		}

		if($a_mode == IL_PAGE_TITLE)
		{
			return $pg_rec["title"];
		}

		$tree = new ilTree($pg_rec["lm_id"]);
		$tree->setTableNames('lm_tree','lm_data');
		$tree->setTreeTablePK("lm_id");

		if ($tree->isInTree($pg_rec["obj_id"]))
		{
			$pred_node = $tree->fetchPredecessorNode($pg_rec["obj_id"], "st");
			/*
			require_once("content/classes/class.ilStructureObject.php");
			$struct_obj =& new ilStructureObject($pred_node["obj_id"]);
			return $struct_obj->getTitle();*/
			return $pred_node["title"];
		}
		else
		{
			return $pg_rec["title"];
		}
	}

	/**
	* export page object to xml (see ilias_co.dtd)
	*
	* @param	object		$a_xml_writer	ilXmlWriter object that receives the
	*										xml data
	*/
	function exportXML(&$a_xml_writer, $a_mode = "normal", $a_inst = 0)
	{
		$attrs = array();
		$a_xml_writer->xmlStartTag("PageObject", $attrs);

		switch ($a_mode)
		{
			case "normal":
				// MetaData
				$this->exportXMLMetaData($a_xml_writer);

				// PageContent
				$this->exportXMLPageContent($a_xml_writer, $a_inst);
				break;

			case "alias":
				$attrs = array();
				$attrs["OriginId"] = "il_".$a_inst.
					"_pg_".$this->getId();
				$a_xml_writer->xmlElement("PageAlias", $attrs);
				break;
		}

		// Layout
		// not implemented

		$a_xml_writer->xmlEndTag("PageObject");
	}


	/**
	* export page objects meta data to xml (see ilias_co.dtd)
	*
	* @param	object		$a_xml_writer	ilXmlWriter object that receives the
	*										xml data
	*/
	function exportXMLMetaData(&$a_xml_writer)
	{
		$nested = new ilNestedSetXML();
		$nested->setParameterModifier($this, "insertInstInMeta");
		$a_xml_writer->appendXML($nested->export($this->getId(),
			$this->getType()));
	}

	function insertInstInMeta($a_tag, $a_param, $a_value)
	{
		if ($a_tag == "Identifier" && $a_param = "Entry")
		{
			$a_value = ilUtil::insertInstIntoID($a_value);
		}

		return $a_value;
	}


	/**
	* export page objects meta data to xml (see ilias_co.dtd)
	*
	* @param	object		$a_xml_writer	ilXmlWriter object that receives the
	*										xml data
	*/
	function exportXMLPageContent(&$a_xml_writer, $a_inst = 0)
	{
//echo "exportxmlpagecontent:$a_inst:<br>";
		$cont_obj =& $this->getContentObject();
		//$page_obj = new ilPageObject($cont_obj->getType(), $this->getId());

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
	* get ids of all media objects within the page
	*
	* note: this method must be called afer exportXMLPageContent
	*/
	function getMediaObjectIds()
	{
		return $this->mobs_contained;
	}

	/**
	* get ids of all file items within the page
	*
	* note: this method must be called afer exportXMLPageContent
	*/
	function getFileItemIds()
	{
		return $this->files_contained;
	}

	/**
	* redirect script
	*
	* @param	string		$a_target
	*/
	function _goto($a_target)
	{
		global $rbacsystem, $ilias;

		// determine learning object
		$query = "SELECT * FROM lm_data WHERE obj_id = '".$a_target."'";
		$pg_set = $ilDB->query($query);
		$pg_rec = $pg_set->fetchRow(DB_FETCHMODE_ASSOC);
		$lm_id = $pg_rec["lm_id"];

		// get all references
		$ref_ids = _ilObject::_getAllReferences($lm_id);

		// check read permissions
		foreach ($ref_ids as $ref_id)
		{
			if ($rbacsystem->checkAccess("read", $ref_id))
			{
				ilUtil::redirect("content/lm_presentation.php?ref_id=$ref_id".
					"&obj_id=$a_target");
			}
		}

		$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->WARNING);
	}

}
?>
