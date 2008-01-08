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
* Meta Data class (element requirement)
*
* @package ilias-core
* @version $Id$
*/
include_once 'class.ilMDBase.php';

class ilMDRequirement extends ilMDBase
{
	function ilMDRequirement($a_rbac_id = 0,$a_obj_id = 0,$a_obj_type = '')
	{
		parent::ilMDBase($a_rbac_id,
						 $a_obj_id,
						 $a_obj_type);
	}

	// SET/GET
	function setOrCompositeId($a_or_composite_id)
	{
		$this->or_composite_id = (int) $a_or_composite_id;
	}
	function getOrCompositeId()
	{
		return (int) $this->or_composite_id;
	}


	function setOperatingSystemName($a_val)
	{
		switch($a_val)
		{
			case 'PC-DOS':
			case 'MS-Windows':
			case 'MacOS':
			case 'Unix':
			case 'Multi-OS':
			case 'None':
				$this->operating_system_name = $a_val;
				return true;

			default:
				return false;
		}
	}
	function getOperatingSystemName()
	{
		return $this->operating_system_name;
	}
	function setOperatingSystemMinimumVersion($a_val)
	{
		$this->operating_system_minimum_version = $a_val;
	}
	function getOperatingSystemMinimumVersion()
	{
		return $this->operating_system_minimum_version;
	}
	function setOperatingSystemMaximumVersion($a_val)
	{
		$this->operating_system_maximum_version = $a_val;
	}
	function getOperatingSystemMaximumVersion()
	{
		return $this->operating_system_maximum_version;
	}
	function setBrowserName($a_val)
	{
		switch($a_val)
		{
			case 'Any':
			case 'NetscapeCommunicator':
			case 'MS-InternetExplorer':
			case 'Opera':
			case 'Amaya':
			case 'Mozilla':
				$this->browser_name = $a_val;
				return true;

			default:
				return false;
		}
	}
	function getBrowserName()
	{
		return $this->browser_name;
	}
	function setBrowserMinimumVersion($a_val)
	{
		$this->browser_minimum_version = $a_val;
	}
	function getBrowserMinimumVersion()
	{
		return $this->browser_minimum_version;
	}
	function setBrowserMaximumVersion($a_val)
	{
		$this->browser_maximum_version = $a_val;
	}
	function getBrowserMaximumVersion()
	{
		return $this->browser_maximum_version;
	}

	function save()
	{
		if($this->db->autoExecute('il_meta_requirement',
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
			if($this->db->autoExecute('il_meta_requirement',
									  $this->__getFields(),
									  DB_AUTOQUERY_UPDATE,
									  "meta_requirement_id = ".$ilDB->quote($this->getMetaId())))
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
			$query = "DELETE FROM il_meta_requirement ".
				"WHERE meta_requirement_id = ".$ilDB->quote($this->getMetaId());
			
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
					 'operating_system_name'	=> $this->getOperatingSystemName(),
					 'operating_system_minimum_version' => $this->getOperatingSystemMinimumVersion(),
					 'operating_system_maximum_version' => $this->getOperatingSystemMaximumVersion(),
					 'browser_name'	=> $this->getBrowserName(),
					 'browser_minimum_version' => $this->getBrowserMinimumVersion(),
					 'browser_maximum_version' => $this->getBrowserMaximumVersion(),
					 'or_composite_id' => $this->getOrCompositeId());
	}

	function read()
	{
		global $ilDB;
		
		include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

		if($this->getMetaId())
		{
			$query = "SELECT * FROM il_meta_requirement ".
				"WHERE meta_requirement_id = ".$ilDB->quote($this->getMetaId());

			$res = $this->db->query($query);
			while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
			{
				$this->setRBACId($row->rbac_id);
				$this->setObjId($row->obj_id);
				$this->setObjType($row->obj_type);
				$this->setParentId($row->parent_id);
				$this->setParentType($row->parent_type);
				$this->setOperatingSystemName($row->operating_system_name);
				$this->setOperatingSystemMinimumVersion($row->operating_system_minimum_version);
				$this->setOperatingSystemMaximumVersion($row->operating_system_maximum_version);
				$this->setBrowserName($row->browser_name);
				$this->setBrowserMinimumVersion($row->browser_minimum_version);
				$this->setBrowserMaximumVersion($row->browser_maximum_version);
				$this->setOrCompositeId($row->or_composite_id);
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
		$writer->xmlStartTag('Requirement');
		$writer->xmlStartTag('Type');
			
		if(strlen($this->getOperatingSystemName()))
		{
			$writer->xmlElement('OperatingSystem',array('Name' => $this->getOperatingSystemName()
														? $this->getOperatingSystemName()
														: 'None',
														'MinimumVersion' => $this->getOperatingSystemMinimumVersion(),
														'MaximumVersion' => $this->getOperatingSystemMaximumVersion()));
		}
		else
		{
			$writer->xmlElement('Browser',array('Name' => $this->getBrowserName()
												? $this->getBrowserName()
												: 'Any',
												'MinimumVersion' => $this->getBrowserMinimumVersion(),
												'MaximumVersion' => $this->getBrowserMaximumVersion()));
		}
		$writer->xmlEndTag('Type');
		$writer->xmlEndTag('Requirement');
		
	}


	// STATIC
	function _getIds($a_rbac_id,$a_obj_id,$a_parent_id,$a_parent_type,$a_or_composite_id = 0)
	{
		global $ilDB;

		$query = "SELECT meta_requirement_id FROM il_meta_requirement ".
			"WHERE rbac_id = ".$ilDB->quote($a_rbac_id)." ".
			"AND obj_id = ".$ilDB->quote($a_obj_id)." ".
			"AND parent_id = ".$ilDB->quote($a_parent_id)." ".
			"AND parent_type = ".$ilDB->quote($a_parent_type)." ".
			"AND or_composite_id = ".$ilDB->quote($a_or_composite_id);

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			$ids[] = $row->meta_requirement_id;
		}
		return $ids ? $ids : array();
	}
}
?>