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
	function getPresentationTitle($a_mode = IL_CHAPTER_TITLE)
	{
		if($a_mode == IL_NO_HEADER)
		{
			return "";
		}

		if($a_mode == IL_PAGE_TITLE)
		{
			return $this->getTitle();
		}

		$tree = new ilTree($this->getLMId());
		$tree->setTableNames('lm_tree','lm_data');
		$tree->setTreeTablePK("lm_id");
		if ($tree->isInTree($this->getId()))
		{
			$pred_node = $tree->fetchPredecessorNode($this->getId(), "st");
			/*
			require_once("content/classes/class.ilStructureObject.php");
			$struct_obj =& new ilStructureObject($pred_node["obj_id"]);
			return $struct_obj->getTitle();*/
			return $pred_node["title"];
		}
		else
		{
			return $this->getTitle();
		}
	}

}
?>
