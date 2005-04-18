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
* Meta Data class (element contribute)
*
* @author Stefan Meyer <smeyer@databay.de>
* @package ilias-core
* @version $Id$
*/
include_once 'class.ilMDBase.php';

class ilMDContribute extends ilMDBase
{
	var $parent_obj = null;

	function ilMDContribute(&$parent_obj,$a_id = null)
	{
		$this->parent_obj =& $parent_obj;

		parent::ilMDBase($this->parent_obj->getRBACId(),
						 $this->parent_obj->getObjId(),
						 $this->parent_obj->getObjType(),
						 'meta_contribute',
						 $a_id);

		$this->setParentType($this->parent_obj->getMetaType());
		$this->setParentId($this->parent_obj->getMetaId());

		if($a_id)
		{
			$this->read();
		}
	}


	// Subelements
	function &getEntityIds()
	{
		include_once 'Services/MetaData/classes/class.ilMDEntity.php';

		return ilMDEntity::_getIds($this->getRBACId(),$this->getObjId(),$this->getMetaId(),$this->getMetaType());
	}
	function &getEntity($a_entity_id)
	{
		include_once 'Services/MetaData/classes/class.ilMDEntity.php';
		
		if(!$a_entity_id)
		{
			return false;
		}
		return new ilMDEntity($this,$a_entity_id);
	}
	function &addEntity()
	{
		include_once 'Services/MetaData/classes/class.ilMDEntity.php';

		return new ilMDEntity($this);
	}

	// SET/GET
	function setRole($a_role)
	{
		$this->role = $a_role;
	}
	function getRole()
	{
		return $this->role;
	}
	function setDate($a_date)
	{
		$this->date = $a_date;
	}
	function getDate()
	{
		return $this->date;
	}


	function save()
	{
		if($this->db->autoExecute('il_meta_contribute',
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
			if($this->db->autoExecute('il_meta_contribute',
									  $this->__getFields(),
									  DB_AUTOQUERY_UPDATE,
									  "meta_contribute_id = '".$this->getMetaId()."'"))
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
			$query = "DELETE FROM il_meta_contribute ".
				"WHERE meta_contribute_id = '".$this->getMetaId()."'";
			
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
					 'role'	=> ilUtil::prepareDBString($this->getRole()),
					 'date' => ilUtil::prepareDBString($this->getDate()));
	}

	function read()
	{
		include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

		if($this->getMetaId())
		{
			$query = "SELECT * FROM il_meta_contribute ".
				"WHERE meta_contribute_id = '".$this->getMetaId()."'";

			$res = $this->db->query($query);
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$this->setRole($row->role);
				$this->setDate($row->date);
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
		$writer->xmlStartTag('Contribute',array('Role' => $this->getRole()));

		// Entities
		foreach($this->getEntityIds() as $id)
		{
			$ent =& $this->getEntity($id);
			$ent->toXML($writer);
		}
		$writer->xmlElement('Date',null,$this->getDate());
		$writer->xmlEndTag('Contribute');
	}


	// STATIC
	function _getIds($a_rbac_id,$a_obj_id,$a_parent_id,$a_parent_type)
	{
		global $ilDB;

		$query = "SELECT meta_contribute_id FROM il_meta_contribute ".
			"WHERE rbac_id = '".$a_rbac_id."' ".
			"AND obj_id = '".$a_obj_id."' ".
			"AND parent_id = '".$a_parent_id."' ".
			"AND parent_type = '".$a_parent_type."' ".
			"ORDER BY meta_contribute_id";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$ids[] = $row->meta_contribute_id;
		}
		return $ids ? $ids : array();
	}
}
?>