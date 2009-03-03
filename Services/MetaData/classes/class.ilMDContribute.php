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
	function ilMDContribute($a_rbac_id = 0,$a_obj_id = 0,$a_obj_type = '')
	{
		parent::ilMDBase($a_rbac_id,
						 $a_obj_id,
						 $a_obj_type);
	}


	// Subelements
	function &getEntityIds()
	{
		include_once 'Services/MetaData/classes/class.ilMDEntity.php';

		return ilMDEntity::_getIds($this->getRBACId(),$this->getObjId(),$this->getMetaId(),'meta_contribute');
	}
	function &getEntity($a_entity_id)
	{
		include_once 'Services/MetaData/classes/class.ilMDEntity.php';
		
		if(!$a_entity_id)
		{
			return false;
		}
		$ent =& new ilMDEntity();
		$ent->setMetaId($a_entity_id);

		return $ent;
	}
	function &addEntity()
	{
		include_once 'Services/MetaData/classes/class.ilMDEntity.php';

		$ent = new ilMDEntity($this->getRBACId(),$this->getObjId(),$this->getObjType());
		$ent->setParentId($this->getMetaId());
		$ent->setParentType('meta_contribute');

		return $ent;
	}

	// SET/GET
	function setRole($a_role)
	{
		switch($a_role)
		{
			case 'Author':
			case 'Publisher':
			case 'Unknown':
			case 'Initiator':
			case 'Terminator':
			case 'Editor':
			case 'GraphicalDesigner':
			case 'TechnicalImplementer':
			case 'ContentProvider':
			case 'TechnicalValidator':
			case 'EducationalValidator':
			case 'ScriptWriter':
			case 'InstructionalDesigner':
			case 'SubjectMatterExpert':
			case 'Creator':
			case 'Validator':
				$this->role = $a_role;
				return true;

			default:
				return false;
		}
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
		global $ilDB;

		$fields = $this->__getFields();
		$fields['meta_contribute_id'] = array('integer',$next_id = $ilDB->nextId('il_meta_contribute'));
		
		if($this->db->insert('il_meta_contribute',$fields))
		{
			$this->setMetaId($next_id);
			return $this->getMetaId();
		}
		return false;
	}

	function update()
	{
		global $ilDB;
		
		if($this->getMetaId())
		{
			if($this->db->update('il_meta_contribute',
									$this->__getFields(),
									array("meta_contribute_id" => array('integer',$this->getMetaId()))))
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
			$query = "DELETE FROM il_meta_contribute ".
				"WHERE meta_contribute_id = ".$ilDB->quote($this->getMetaId() ,'integer');
			$res = $ilDB->manipulate($query);
			
			foreach($this->getEntityIds() as $id)
			{
				$ent = $this->getEntity($id);
				$ent->delete();
			}
			return true;
		}
		return false;
	}
			

	function __getFields()
	{
		return array('rbac_id'	=> array('integer',$this->getRBACId()),
					 'obj_id'	=> array('integer',$this->getObjId()),
					 'obj_type'	=> array('text',$this->getObjType()),
					 'parent_type' => array('text',$this->getParentType()),
					 'parent_id' => array('integer',$this->getParentId()),
					 'role'	=> array('text',$this->getRole()),
					 'date' => array('text',$this->getDate()));
	}

	function read()
	{
		global $ilDB;
		
		include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

		if($this->getMetaId())
		{
			$query = "SELECT * FROM il_meta_contribute ".
				"WHERE meta_contribute_id = ".$ilDB->quote($this->getMetaId() ,'integer');

			$res = $this->db->query($query);
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$this->setRBACId($row->rbac_id);
				$this->setObjId($row->obj_id);
				$this->setObjType($row->obj_type);
				$this->setParentId($row->parent_id);
				$this->setParentType($row->parent_type);
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
		$writer->xmlStartTag('Contribute',array('Role' => $this->getRole()
												? $this->getRole()
												: 'Author'));

		// Entities
		$entities = $this->getEntityIds();
		foreach($entities as $id)
		{
			$ent =& $this->getEntity($id);
			$ent->toXML($writer);
		}
		if(!count($entities))
		{
			include_once 'Services/MetaData/classes/class.ilMDEntity.php';
			$ent = new ilMDEntity($this->getRBACId(),$this->getObjId());
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
			"WHERE rbac_id = ".$ilDB->quote($a_rbac_id ,'integer')." ".
			"AND obj_id = ".$ilDB->quote($a_obj_id ,'integer')." ".
			"AND parent_id = ".$ilDB->quote($a_parent_id ,'integer')." ".
			"AND parent_type = ".$ilDB->quote($a_parent_type ,'text');

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$ids[] = $row->meta_contribute_id;
		}
		return $ids ? $ids : array();
	}
	
	/**
	 * Lookup authors
	 *
	 * @access public
	 * @static
	 *
	 * @param int rbac_id
	 * @param int obj_id
	 * @param string obj_type
	 * @return array string authors
	 */
	public static function _lookupAuthors($a_rbac_id,$a_obj_id,$a_obj_type)
	{
		global $ilDB;
		
		// Ask for 'author' later to use indexes 
		$query = "SELECT entity,ent.parent_type,role FROM il_meta_entity ent ".
			"JOIN il_meta_contribute con ON ent.parent_id = con.meta_contribute_id ".
			"WHERE  ent.rbac_id = ".$ilDB->quote($a_rbac_id ,'integer')." ".
			"AND ent.obj_id = ".$ilDB->quote($a_obj_id ,'integer')." ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if($row->role == 'Author' and $row->parent_type == 'meta_contribute')
			{
				$authors[] = trim($row->entity);
			}
		}
		return $authors ? $authors : array();
	}
}
?>