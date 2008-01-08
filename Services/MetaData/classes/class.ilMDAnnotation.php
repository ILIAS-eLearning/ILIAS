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
* Meta Data class (element annotation)
*
* @package ilias-core
* @version $Id$
*/
include_once 'class.ilMDBase.php';

class ilMDAnnotation extends ilMDBase
{
	function ilMDAnnotation($a_rbac_id = 0,$a_obj_id = 0,$a_obj_type = '')
	{
		parent::ilMDBase($a_rbac_id,
						 $a_obj_id,
						 $a_obj_type);
	}

	// SET/GET
	function setEntity($a_entity)
	{
		$this->entity = $a_entity;
	}
	function getEntity()
	{
		return $this->entity;
	}
	function setDate($a_date)
	{
	    $this->date = $a_date;
	}
	function getDate()
	{
		return $this->date;
	}
	function setDescription($a_desc)
	{
		$this->description = $a_desc;
	}
	function getDescription()
	{
		return $this->description;
	}
	function setDescriptionLanguage($lng_obj)
	{
		if(is_object($lng_obj))
		{
			$this->description_language =& $lng_obj;
		}
	}
	function &getDescriptionLanguage()
	{
		return $this->description_language;
	}
	function getDescriptionLanguageCode()
	{
		if(is_object($this->description_language))
		{
			return $this->description_language->getLanguageCode();
		}
		return false;
	}

	function save()
	{
		if($this->db->autoExecute('il_meta_annotation',
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
		global $ilDB;
		
		if($this->getMetaId())
		{
			if($this->db->autoExecute('il_meta_annotation',
									  $this->__getFields(),
									  DB_AUTOQUERY_UPDATE,
									  "meta_annotation_id = ".$ilDB->quote($this->getMetaId())))
			{
				return true;
			}
		}
		return false;
	}

	function delete()
	{
		global $ilDB;
		
		if($this->getMetaId())
		{
			$query = "DELETE FROM il_meta_annotation ".
				"WHERE meta_annotation_id = ".$ilDB->quote($this->getMetaId());
			
			$this->db->query($query);
			
			return true;
		}
		return false;
	}
			

	function __getFields()
	{
		return array('rbac_id'	=> $this->getRBACId(),
					 'obj_id'	=> $this->getObjId(),
					 'obj_type'	=> $this->getObjType(),
					 'entity'	=> $this->getEntity(),
					 'date'		=> $this->getDate(),
					 'description' => $this->getDescription(),
					 'description_language' => $this->getDescriptionLanguageCode());
	}

	function read()
	{
		global $ilDB;
		
		include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

		if($this->getMetaId())
		{
			$query = "SELECT * FROM il_meta_annotation ".
				"WHERE meta_annotation_id = ".$ilDB->quote($this->getMetaId());

			$res = $this->db->query($query);
			while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
			{
				$this->setRBACId($row->rbac_id);
				$this->setObjId($row->obj_id);
				$this->setObjType($row->obj_type);
				$this->setEntity($row->entity);
				$this->setDate($row->date);
				$this->setDescription($row->description);
				$this->description_language =& new ilMDLanguageItem($row->description_language);
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
		$writer->xmlStartTag('Annotation');
		$writer->xmlElement('Entity',null,$this->getEntity());
		$writer->xmlElement('Date',null,$this->getDate());
		$writer->xmlElement('Description',array('Language' => $this->getDescriptionLanguageCode()
												? $this->getDescriptionLanguageCode()
												: 'en'),
							$this->getDescription());
		$writer->xmlEndTag('Annotation');
	}

				

	// STATIC
	function _getIds($a_rbac_id,$a_obj_id)
	{
		global $ilDB;

		$query = "SELECT meta_annotation_id FROM il_meta_annotation ".
			"WHERE rbac_id = ".$ilDB->quote($a_rbac_id)." ".
			"AND obj_id = ".$ilDB->quote($a_obj_id);


		$res = $ilDB->query($query);
		while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			$ids[] = $row->meta_annotation_id;
		}
		return $ids ? $ids : array();
	}
}
?>