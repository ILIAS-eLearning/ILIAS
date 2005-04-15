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
* Meta Data class (element classification)
*
* @package ilias-core
* @version $Id$
*/
include_once 'class.ilMDBase.php';

class ilMDClassification extends ilMDBase
{
	var $parent_obj = null;

	function ilMDClassification(&$parent_obj,$a_id = null)
	{
		$this->parent_obj =& $parent_obj;

		parent::ilMDBase($this->parent_obj->getRBACId(),
						 $this->parent_obj->getObjId(),
						 $this->parent_obj->getObjType(),
						 'meta_classification',
						 $a_id);

		if($a_id)
		{
			$this->read();
		}
	}

	// METHODS OF CLIENT OBJECTS (TaxonPath, Keyword)
	function &getTaxonPathIds()
	{
		include_once 'Services/MetaData/classes/class.ilMDTaxonPath.php';

		return ilMDTaxonPath::_getIds($this->getRBACId(),$this->getObjId(),$this->getMetaId(),$this->getMetaType());
	}
	function &getTaxonPath($a_taxon_path_id)
	{
		include_once 'Services/MetaData/classes/class.ilMDTaxonPath.php';

		if(!$a_taxon_path_id)
		{
			return false;
		}
		return new ilMDTaxonPath($this,$a_taxon_path_id);
	}
	function &addTaxonPath()
	{
		include_once 'Services/MetaData/classes/class.ilMDTaxonPath.php';

		return new ilMDTaxonPath($this);
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
	function setPurpose($a_purpose)
	{
		$this->purpose = $a_purpose;
	}
	function getPurpose()
	{
		return $this->purpose;
	}
	function setDescription($a_description)
	{
		$this->description = $a_description;
	}
	function getDescription()
	{
		return $this->description;
	}
	function setDescriptionLanguage(&$lng_obj)
	{
		if(is_object($lng_obj))
		{
			$this->description_language = $lng_obj;
		}
	}
	function &getDescriptionLanguage()
	{
		return is_object($this->description_language) ? $this->description_language : false;
	}
	function getDescriptionLanguageCode()
	{
		return is_object($this->description_language) ? $this->description_language->getLanguageCode() : false;
	} 


	function save()
	{
		if($this->db->autoExecute('il_meta_classification',
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
			if($this->db->autoExecute('il_meta_classification',
									  $this->__getFields(),
									  DB_AUTOQUERY_UPDATE,
									  "meta_classification_id = '".$this->getMetaId()."'"))
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
			$query = "DELETE FROM il_meta_classification ".
				"WHERE meta_classification_id = '".$this->getMetaId()."'";
			
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
					 'purpose'	=> ilUtil::prepareDBString($this->getPurpose()),
					 'description' => ilUtil::prepareDBString($this->getDescription()),
					 'description_language' => ilUtil::prepareDBString($this->getDescriptionLanguageCode()));
	}

	function read()
	{
		include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

		if($this->getMetaId())
		{
			$query = "SELECT * FROM il_meta_classification ".
				"WHERE meta_classification_id = '".$this->getMetaId()."'";

			$res = $this->db->query($query);
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$this->setPurpose($row->purpose);
				$this->setDescription($row->description);
				$this->description_language = new ilMDLanguageItem($row->description_language);
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
		$writer->xmlStartTag('Classification',array('Purpose' => $this->getPurpose()));

		// Taxon Path
		foreach($this->getTaxonPathIds() as $id)
		{
			$tax =& $this->getTaxonPath($id);
			$tax->toXML($writer);
		}
		// Description
		$writer->xmlElement('Description',array('Language' => $this->getDescriptionLanguageCode()),$this->getDescription());
		
		// Keyword
		foreach($this->getKeywordIds() as $id)
		{
			$key =& $this->getKeyword($id);
			$key->toXML($writer);
		}
		$writer->xmlEndTag('Classification');
	}

				

	// STATIC
	function _getIds($a_rbac_id,$a_obj_id)
	{
		global $ilDB;

		$query = "SELECT meta_classification_id FROM il_meta_classification ".
			"WHERE rbac_id = '".$a_rbac_id."' ".
			"AND obj_id = '".$a_obj_id."' ORDER BY meta_classification_id";


		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$ids[] = $row->meta_classification_id;
		}
		return $ids ? $ids : array();
	}
}
?>