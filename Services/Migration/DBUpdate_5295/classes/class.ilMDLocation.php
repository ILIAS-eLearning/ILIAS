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
* Meta Data class (element location)
*
* @package ilias-core
* @version $Id$
*/
include_once 'class.ilMDBase.php';

class ilMDLocation extends ilMDBase
{
	// SET/GET
	function setLocation($a_location)
	{
		$this->location = $a_location;
	}
	function getLocation()
	{
		return $this->location;
	}
	function setLocationType($a_location_type)
	{
		$this->location_type = $a_location_type;
	}
	function getLocationType()
	{
		return $this->location_type;
	}

	function save()
	{
		global $DIC;

		$ilDB = $DIC['ilDB'];
		
		$fields = $this->__getFields();
		$fields['meta_location_id'] = array('integer',$next_id = $ilDB->nextId('il_meta_location'));
		
		if($this->db->insert('il_meta_location',$fields))
		{
			$this->setMetaId($next_id);
			return $this->getMetaId();
		}
		return false;
	}

	function update()
	{
		global $DIC;

		$ilDB = $DIC['ilDB'];
		
		if($this->getMetaId())
		{
			if($this->db->update('il_meta_location',
									$this->__getFields(),
									array("meta_location_id" => array('integer',$this->getMetaId()))))
			{
				return true;
			}
		}
		return false;
	}

	function delete()
	{
		global $DIC;

		$ilDB = $DIC['ilDB'];
		
		if($this->getMetaId())
		{
			$query = "DELETE FROM il_meta_location ".
				"WHERE meta_location_id = ".$ilDB->quote($this->getMetaId() ,'integer');
			$res = $ilDB->manipulate($query);
			
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
					 'location'	=> array('text',$this->getLocation()),
					 'location_type' => array('text',$this->getLocationType()));
	}

	function read()
	{
		global $DIC;

		$ilDB = $DIC['ilDB'];
		
		include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDLanguageItem.php';

		if($this->getMetaId())
		{
			$query = "SELECT * FROM il_meta_location ".
				"WHERE meta_location_id = ".$ilDB->quote($this->getMetaId() ,'integer');

			$res = $this->db->query($query);
			while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
			{
				$this->setRBACId($row->rbac_id);
				$this->setObjId($row->obj_id);
				$this->setObjType($row->obj_type);
				$this->setParentId($row->parent_id);
				$this->setParentType($row->parent_type);
				$this->setLocation($row->location);
				$this->setLocationType($row->location_type);
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
		$writer->xmlElement('Location',array('Type' => $this->getLocationType()
											 ? $this->getLocationType()
											 : 'LocalFile'),
							$this->getLocation());
	}


	// STATIC
	static function _getIds($a_rbac_id,$a_obj_id,$a_parent_id,$a_parent_type)
	{
		global $DIC;

		$ilDB = $DIC['ilDB'];

		$query = "SELECT meta_location_id FROM il_meta_location ".
			"WHERE rbac_id = ".$ilDB->quote($a_rbac_id ,'integer')." ".
			"AND obj_id = ".$ilDB->quote($a_obj_id ,'integer')." ".
			"AND parent_id = ".$ilDB->quote($a_parent_id ,'integer')." ".
			"AND parent_type = ".$ilDB->quote($a_parent_type ,'text');

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
		{
			$ids[] = $row->meta_location_id;
		}
		return $ids ? $ids : array();
	}
}
?>