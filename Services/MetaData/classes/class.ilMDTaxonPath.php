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
* Meta Data class (element taxon_path)
*
* @package ilias-core
* @version $Id$
*/
include_once 'class.ilMDBase.php';

class ilMDTaxonPath extends ilMDBase
{
	var $parent_obj = null;

	function ilMDTaxonPath(&$parent_obj,$a_id = null)
	{
		$this->parent_obj =& $parent_obj;

		parent::ilMDBase($this->parent_obj->getRBACId(),
						 $this->parent_obj->getObjId(),
						 $this->parent_obj->getObjType(),
						 'meta_taxon_path',
						 $a_id);

		$this->setParentType($this->parent_obj->getMetaType());
		$this->setParentId($this->parent_obj->getMetaId());

		if($a_id)
		{
			$this->read();
		}
	}

	// METHODS OF CHILD OBJECTS (Taxon)
	function &getTaxonIds()
	{
		include_once 'Services/MetaData/classes/class.ilMDTaxon.php';

		return ilMDTaxon::_getIds($this->getRBACId(),$this->getObjId(),$this->getMetaId(),$this->getMetaType());
	}
	function &getTaxon($a_taxon_id)
	{
		include_once 'Services/MetaData/classes/class.ilMDTaxon.php';

		if(!$a_taxon_id)
		{
			return false;
		}
		return new ilMDTaxon($this,$a_taxon_id);
	}
	function &addTaxon()
	{
		include_once 'Services/MetaData/classes/class.ilMDTaxon.php';

		return new ilMDTaxon($this);
	}

	// SET/GET
	function setSource($a_source)
	{
		$this->source = $a_source;
	}
	function getSource()
	{
		return $this->source;
	}
	function setSourceLanguage(&$lng_obj)
	{
		if(is_object($lng_obj))
		{
			$this->source_language = $lng_obj;
		}
	}
	function &getSourceLanguage()
	{
		return is_object($this->source_language) ? $this->source_language : false;
	}
	function getSourceLanguageCode()
	{
		return is_object($this->source_language) ? $this->source_language->getLanguageCode() : false;
	} 


	function save()
	{
		if($this->db->autoExecute('il_meta_taxon_path',
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
			if($this->db->autoExecute('il_meta_taxon_path',
									  $this->__getFields(),
									  DB_AUTOQUERY_UPDATE,
									  "meta_taxon_path_id = '".$this->getMetaId()."'"))
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
			$query = "DELETE FROM il_meta_taxon_path ".
				"WHERE meta_taxon_path_id = '".$this->getMetaId()."'";
			
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
					 'parent_type' => $this->getParentType(),
					 'parent_id' => $this->getParentId(),
					 'source'	=> ilUtil::prepareDBString($this->getSource()),
					 'source_language' => ilUtil::prepareDBString($this->getSourceLanguageCode()));
	}

	function read()
	{
		include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

		if($this->getMetaId())
		{
			$query = "SELECT * FROM il_meta_taxon_path ".
				"WHERE meta_taxon_path_id = '".$this->getMetaId()."'";

			$res = $this->db->query($query);
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$this->setSource($row->source);
				$this->source_language = new ilMDLanguageItem($row->source_language);
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
		$writer->xmlStartTag('TaxonPath');

		$writer->xmlElement('Source',array('Language' => $this->getSourceLanguageCode()),$this->getSource());

		// Taxon
		foreach($this->getTaxonIds() as $id)
		{
			$tax =& $this->getTaxon($id);
			$tax->toXML($writer);
		}
		
		$writer->xmlEndTag('TaxonPath');
	}

				

	// STATIC
	function _getIds($a_rbac_id,$a_obj_id,$a_parent_id,$a_parent_type)
	{
		global $ilDB;

		$query = "SELECT meta_taxon_path_id FROM il_meta_taxon_path ".
			"WHERE rbac_id = '".$a_rbac_id."' ".
			"AND obj_id = '".$a_obj_id."' ".
			"AND parent_id = '".$a_parent_id."' ".
			"AND parent_type = '".$a_parent_type."' ORDER BY meta_taxon_path_id";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$ids[] = $row->meta_taxon_path_id;
		}
		return $ids ? $ids : array();
	}
}
?>