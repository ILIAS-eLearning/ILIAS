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
	var $parent_obj = null;

	function ilMDAnnotation(&$parent_obj,$a_id = null)
	{
		$this->parent_obj =& $parent_obj;

		parent::ilMDBase($this->parent_obj->getRBACId(),
						 $this->parent_obj->getObjId(),
						 $this->parent_obj->getObjType(),
						 'meta_annotation',
						 $a_id);

		if($a_id)
		{
			$this->setId($a_id);
			$this->read();
		}
	}

	// SET/GET
	function setId($a_id)
	{
		$this->id = $a_id;
	}
	function getId()
	{
		return $this->id;
	}
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
			$this->description_language = $lng_obj->getLanguageCode();
		}
	}
	function getDescriptionLanguage()
	{
		return $this->description_language;
	}

	function save()
	{
		if($this->db->autoExecute('il_meta_annotation',
								  $this->__getFields(),
								  DB_AUTOQUERY_INSERT))
		{
			$this->setId($this->db->getLastInsertId());

			return $this->getId();
		}
		return false;
	}

	function update()
	{
		if($this->getId())
		{
			if($this->db->autoExecute('il_meta_annotation',
									  $this->__getFields(),
									  DB_AUTOQUERY_UPDATE,
									  "meta_annotation_id = '".$this->getId()."'"))
			{
				return true;
			}
		}
		return false;
	}

	function delete()
	{
		if($this->getId())
		{
			$query = "DELETE FROM il_meta_annotation ".
				"WHERE meta_annotation_id = '".$this->getId()."'";
			
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
					 'entity'	=> ilUtil::prepareDBString($this->getEntity()),
					 'date'		=> ilUtil::prepareDBString($this->getDate()),
					 'description' => ilUtil::prepareDBString($this->getDescription()),
					 'description_language' => ilUtil::prepareDBString($this->getDescriptionLanguage()));
	}

	function read()
	{
		if($this->getId())
		{
			$query = "SELECT * FROM il_meta_annotation ".
				"WHERE meta_annotation_id = '".$this->getId()."'";

			$res = $this->db->query($query);
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$this->setEntity($row->entity);
				$this->setDate($row->date);
				$this->setDescription($row->description);
				$this->description_language = $row->description_language;
			}
		}
		return true;
	}
				

	// STATIC
	function _getIds($a_rbac_id,$a_obj_id)
	{
		global $ilDB;

		$query = "SELECT meta_annotation_id FROM il_meta_annotation ".
			"WHERE rbac_id = '".$a_rbac_id."' ".
			"AND obj_id = '".$a_obj_id."'";


		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$ids[] = $row->meta_annotation_id;
		}
		return $ids ? $ids : array();
	}
}
?>