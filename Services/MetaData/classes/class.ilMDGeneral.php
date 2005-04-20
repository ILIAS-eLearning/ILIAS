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
* Meta Data class (element general)
*
* @author Stefan Meyer <smeyer@databay.de>
* @package ilias-core
* @version $Id$
*/
include_once 'class.ilMDBase.php';

class ilMDGeneral extends ilMDBase
{
	var $parent_obj = null;

	function ilMDGeneral(&$parent_obj,$a_id = null)
	{
		$this->parent_obj =& $parent_obj;

		parent::ilMDBase($this->parent_obj->getRBACId(),
						 $this->parent_obj->getObjId(),
						 $this->parent_obj->getObjType(),
						 'meta_general',
						 $a_id);

		if($a_id)
		{
			$this->read();
		}
	}

	// Subelements (Identifier, Language, Description, Keyword)
	function &getIdentifierIds()
	{
		include_once 'Services/MetaData/classes/class.ilMDIdentifier.php';

		return ilMDIdentifier::_getIds($this->getRBACId(),$this->getObjId(),$this->getMetaId(),$this->getMetaType());
	}
	function &getIdentifier($a_identifier_id)
	{
		include_once 'Services/MetaData/classes/class.ilMDIdentifier.php';
		
		if(!$a_identifier_id)
		{
			return false;
		}
		return new ilMDIdentifier($this,$a_identifier_id);
	}
	function &addIdentifier()
	{
		include_once 'Services/MetaData/classes/class.ilMDIdentifier.php';

		return new ilMDIdentifier($this);
	}
	function &getLanguageIds()
	{
		include_once 'Services/MetaData/classes/class.ilMDLanguage.php';

		return ilMDLanguage::_getIds($this->getRBACId(),$this->getObjId(),$this->getMetaId(),$this->getMetaType());
	}
	function &getLanguage($a_language_id)
	{
		include_once 'Services/MetaData/classes/class.ilMDLanguage.php';

		if(!$a_language_id)
		{
			return false;
		}
		return new ilMDLanguage($this,$a_language_id);
	}
	function &addLanguage()
	{
		include_once 'Services/MetaData/classes/class.ilMDLanguage.php';
		
		return new ilMDLanguage($this);
	}
	function &getDescriptionIds()
	{
		include_once 'Services/MetaData/classes/class.ilMDDescription.php';

		return ilMDDescription::_getIds($this->getRBACId(),$this->getObjId(),$this->getMetaId(),$this->getMetaType());
	}
	function &getDescription($a_description_id)
	{
		include_once 'Services/MetaData/classes/class.ilMDDescription.php';


		if(!$a_description_id)
		{
			return false;
		}
		return new ilMDDescription($this,$a_description_id);
	}
	function &addDescription()
	{
		include_once 'Services/MetaData/classes/class.ilMDDescription.php';

		return new ilMDDescription($this);
	}
	function &getKeywordIds()
	{
		include_once 'Services/MetaData/classes/class.ilMDKeyword.php';

		return ilMDKeyword::_getIds($this->getRBACId(),$this->getObjId(),$this->getMetaId(),$this->getMetaType());
	}
	function &getKeyword($a_keyword_id)
	{
		include_once 'Services/MetaData/classes/class.ilMDKeyword.php';
		
		if(!$a_keyword_id)
		{
			return false;
		}
		return new ilMDKeyword($this,$a_keyword_id);
	}
	function &addKeyword()
	{
		include_once 'Services/MetaData/classes/class.ilMDKeyword.php';

		return new ilMDKeyword($this);
	}



	// SET/GET
	function setStructure($a_structure)
	{
		switch($a_structure)
		{
			case 'Atomic':
			case 'Collection':
			case 'Networked':
			case 'Hierachical':
			case 'Linear':
				$this->structure = $a_structure;
				return true;

			default:
				return false;
		}
	}
	function getStructure()
	{
		return $this->structure;
	}
	function setTitle($a_title)
	{
		$this->title = $a_title;
	}
	function getTitle()
	{
		return $this->title;
	}
	function setTitleLanguage(&$lng_obj)
	{
		if(is_object($lng_obj))
		{
			$this->title_language = $lng_obj;
		}
	}
	function &getTitleLanguage()
	{
		return is_object($this->title_language) ? $this->title_language : false;
	}
	function getTitleLanguageCode()
	{
		return is_object($this->title_language) ? $this->title_language->getLanguageCode() : false;
	}

	function setCoverage($a_coverage)
	{
		$this->coverage = $a_coverage;
	}
	function getCoverage()
	{
		return $this->coverage;
	}

	function setCoverageLanguage(&$lng_obj)
	{
		if(is_object($lng_obj))
		{
			$this->coverage_language = $lng_obj;
		}
	}
	function &getCoverageLanguage()
	{
		return is_object($this->coverage_language) ? $this->coverage_language : false;
	}
	function getCoverageLanguageCode()
	{
		return is_object($this->coverage_language) ? $this->coverage_language->getLanguageCode() : false;
	}


	function save()
	{
		if($this->db->autoExecute('il_meta_general',
								  $this->__getFields(),
								  DB_AUTOQUERY_INSERT))
		{
			$this->setMetaId($this->db->getLastInsertId());

			return $this->getMetaId();
		}
		return false;
	}

	function update()
	{
		if($this->getMetaId())
		{
			if($this->db->autoExecute('il_meta_general',
									  $this->__getFields(),
									  DB_AUTOQUERY_UPDATE,
									  "meta_general_id = '".$this->getMetaId()."'"))
			{
				return true;
			}
		}
		return false;
	}

	function delete()
	{
		if($this->getMetaId())
		{
			$query = "DELETE FROM il_meta_general ".
				"WHERE meta_general_id = '".$this->getMetaId()."'";
			
			$this->db->query($query);
			
			return true;
		}
		return false;
	}
			

	function __getFields()
	{
		return array('rbac_id'	=> $this->getRBACId(),
					 'obj_id'	=> $this->getObjId(),
					 'obj_type'	=> ilUtil::prepareDBString($this->getObjType()),
					 'general_structure'	=> ilUtil::prepareDBString($this->getStructure()),
					 'title'		=> ilUtil::prepareDBString($this->getTitle()),
					 'title_language' => ilUtil::prepareDBString($this->getTitleLanguageCode()),
					 'coverage' => ilUtil::prepareDBString($this->getCoverage()),
					 'coverage_language' => ilUtil::prepareDBString($this->getCoverageLanguageCode()));
	}

	function read()
	{
		include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

		if($this->getMetaId())
		{
			$query = "SELECT * FROM il_meta_general ".
				"WHERE meta_general_id = '".$this->getMetaId()."'";

			$res = $this->db->query($query);
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$this->setStructure(ilUtil::stripSlashes($row->general_structure));
				$this->setTitle(ilUtil::stripSlashes($row->title));
				$this->setTitleLanguage(new ilMDLanguageItem($row->title_language));
				$this->setCoverage(ilUtil::stripSlashes($row->coverage));
				$this->setCoverageLanguage(new ilMDLanguageItem($row->coverage_language));
			}
		}
		return true;
	}

	/*
	 * XML Export of all meta data
	 * @param object (xml writer) see class.ilMD2XML.php
	 * 
	 */
	function toXML(&$writer)
	{
		$writer->xmlStartTag('General',array('Structure' => $this->getStructure()));

		// Identifier
		foreach($this->getIdentifierIds() as $id)
		{
			$ide =& $this->getIdentifier($id);
			$ide->toXML($writer);
		}
		
		// TItle
		$writer->xmlElement('Title',array('Language' => $this->getTitleLanguageCode()),$this->getTitle());

		// Language
		foreach($this->getLanguageIds() as $id)
		{
			$lan =& $this->getLanguage($id);
			$lan->toXML($writer);
		}

		// Description
		foreach($this->getDescriptionIds() as $id)
		{
			$des =& $this->getDescription($id);
			$des->toXML($writer);
		}

		// Keyword
		foreach($this->getKeywordIds() as $id)
		{
			$key =& $this->getKeyword($id);
			$key->toXML($writer);
		}
		
		// Copverage
		if(strlen($this->getCoverage()))
		{
			$writer->xmlElement('Coverage',array('Language' => $this->getCoverageLanguageCode()),$this->getCoverage());
		}
		$writer->xmlEndTag('General');
	}

				

	// STATIC
	function _getId($a_rbac_id,$a_obj_id)
	{
		global $ilDB;

		$query = "SELECT meta_general_id FROM il_meta_general ".
			"WHERE rbac_id = '".$a_rbac_id."' ".
			"AND obj_id = '".$a_obj_id."' ORDER BY meta_general_id";


		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->meta_general_id;
		}
		return false;
	}
}
?>