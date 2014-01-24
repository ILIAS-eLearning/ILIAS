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
* Base class for creating meta data sets for object types
* If you need special element values, inherit from this class. E.g class.ilMDCourseCreator extends class.ilMDCreator
*
* @package ilias-core
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*/

class ilMDCreator
{
	var $md_obj = null;

	/*
	 * rbac_id ref_id of rbac object (e.g for page objects the obj_id of the content object)
	 */
	var $rbac_id;

	/*
	 * obj_id (e.g for structure objects the obj_id of the structure object)
	 */
	var $obj_id;

	/*
	 * type of the object (e.g st,pg,crs ...)
	 */
	var $obj_type;

	function ilMDCreator($a_rbac_id,$a_obj_id,$a_type)
	{
		include_once 'Services/Migration/DBUpdate_426/classes/class.ilMD.php';

		$this->rbac_id = $a_rbac_id;
		$this->obj_id = $a_obj_id;
		$this->obj_type = $a_type;

		$this->md_obj =& new ilMD($a_rbac_id,$a_obj_id,$a_type);
	}

	// SET/GET
	function setTitle($a_title)
	{
		$this->title = $a_title;
	}
	function getTitle()
	{
		return $this->title;
	}
	function setDescription($a_desc)
	{
		$this->description = $a_desc;
	}
	function getDescription()
	{
		return $this->description;
	}
	function setTitleLanguage($a_lng)
	{
		$this->title_lng = $a_lng;
	}
	function &getTitleLanguage()
	{
		include_once 'Services/Migration/DBUpdate_426/classes/class.ilMDLanguageItem.php';

		return new ilMDLanguageItem($this->title_lng);
	}
	function setDescriptionLanguage($a_lng)
	{
		$this->title_lng = $a_lng;
	}
	function &getDescriptionLanguage()
	{
		include_once 'Services/Migration/DBUpdate_426/classes/class.ilMDLanguageItem.php';

		return new ilMDLanguageItem($this->title_lng);
	}
	function setLanguage($a_lng)
	{
		$this->title_lng = $a_lng;
	}
	function &getLanguage()
	{
		include_once 'Services/Migration/DBUpdate_426/classes/class.ilMDLanguageItem.php';

		return new ilMDLanguageItem($this->title_lng);
	}
	function setKeyword($a_key)
	{
		$this->keyword = $a_key;
	}
	function getKeyword()
	{
		return $this->keyword;
	}


	function getRBACId()
	{
		return $this->rbac_id;
	}
	function getObjId()
	{
		return $this->obj_id;
	}
	function getObjType()
	{
		return $this->obj_type;
	}
	function setKeywordLanguage($a_lng)
	{
		$this->title_lng = $a_lng;
	}
	function &getKeywordLanguage()
	{
		include_once 'Services/Migration/DBUpdate_426/classes/class.ilMDLanguageItem.php';

		return new ilMDLanguageItem($this->title_lng);
	}
	function setCatalog($a_cat)
	{
		$this->catalog = $a_cat;
	}
	function getCatalog()
	{
		return $this->catalog ? $this->catalog : 'ILIAS';
	}
	function setEntry($a_entry)
	{
		$this->entry = $a_entry;
	}
	function getEntry()
	{
		return $this->entry ? $this->entry : 'il__'.$this->getObjType().'_'.$this->getObjId();
	}
	function setStructure($a_structure)
	{
		$this->structure = $a_structure;
	}
	function getStructure()
	{
		return $this->structure ? $this->structure : 'Hierarchical';
	}



	function create()
	{
		$this->__createGeneral();
	}

	// PROTECTED
	function __createGeneral()
	{
		$md_gen =& $this->md_obj->addGeneral();

		$md_gen->setStructure($this->getStructure());
		$md_gen->setTitle($this->getTitle());
		$md_gen->setTitleLanguage($this->getTitleLanguage());
		$md_gen->save();

		$md_ide =& $md_gen->addIdentifier();
		$md_ide->setCatalog($this->getCatalog());
		$md_ide->setEntry($this->getEntry());
		$md_ide->save();

		$md_lng =& $md_gen->addLanguage();
		$md_lng->setLanguage($this->getLanguage());
		$md_lng->save();

		$md_des =& $md_gen->addDescription();
		$md_des->setDescription($this->getDescription());
		$md_des->setDescriptionLanguage($this->getDescriptionLanguage());
		$md_des->save();

		$md_key =& $md_gen->addKeyword();
		$md_key->setKeyword($this->getKeyword());
		$md_key->setKeywordLanguage($this->getKeywordLanguage());
		$md_key->save();


		unset($md_gen);
		unset($md_ide);
		unset($md_lng);
		unset($md_des);
		unset($md_key);

		return true;
	}
}
?>