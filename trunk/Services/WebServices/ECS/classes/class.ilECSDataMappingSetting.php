<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ingroup ServicesWebServicesECS
*/
class ilECSDataMappingSetting
{

	const MAPPING_EXPORT = 1;
	const MAPPING_IMPORT_CRS = 2;
	const MAPPING_IMPORT_RCRS = 3;

	private $server_id = 0;
	private $mapping_type = 0;
	private $ecs_field = 0;
	private $advmd_id = 0;


 	/**
	 * constructor
	 * @access public
	 * 
	 */
	public function __construct($a_server_id = 0,$mapping_type = 0,$ecs_field = '')
	{
		$this->setServerId($a_server_id);
		$this->setMappingType($mapping_type);
		$this->setECSField($ecs_field);
	}

	/**
	 * set server id
	 * @param int $a_server_id
	 */
	public function setServerId($a_server_id)
	{
		$this->server_id = $a_server_id;
	}

	/**
	 * Get server id
	 */
	public function getServerId()
	{
		return $this->server_id;
	}

	/**
	 *
	 * @param string $ecs_field
	 */
	public function setECSField($ecs_field)
	{
		$this->ecs_field = $ecs_field;
	}

	/**
	 * Get ecs field
	 */
	public function getECSField()
	{
		return $this->ecs_field;
	}

	/**
	 * Set mapping type
	 * @param int $mapping_type
	 */
	public function setMappingType($mapping_type)
	{
		$this->mapping_type = $mapping_type;
	}

	/**
	 * Get mapping type
	 */
	public function getMappingType()
	{
		return $this->mapping_type;
	}


	/**
	 *
	 * @return int
	 */
	public function getAdvMDId()
	{
		return $this->advmd_id;
	}

	public function setAdvMDId($a_id)
	{
		$this->advmd_id = $a_id;
	}

	/**
	 * Save mappings
	 *
	 * @access public
	 */
	public function save()
	{
		global $ilDB;

		$query = 'SELECT * FROM ecs_data_mapping '.
			'WHERE sid = '.$ilDB->quote($this->getServerId(),'integer').' '.
			'AND mapping_type = '.$ilDB->quote($this->getMappingType(),'integer').' '.
			'AND ecs_field = '.$ilDB->quote($this->getECSField(),'text');
		$res = $ilDB->query($query);
		if($res->numRows())
		{
			$this->update();
		}
		else
		{
			$this->create();
		}
	}

	/**
	 * Update setting
	 * @global ilDB $ilDB 
	 */
	protected function update()
	{
		global $ilDB;

		$query = 'UPDATE ecs_data_mapping '.
			'SET advmd_id = '.$ilDB->db->quote($this->getAdvMDId(),'integer').' '.
			'WHERE sid = '.$ilDB->quote($this->getServerId(),'integer').' '.
			'AND mapping_type = '.$ilDB->quote($this->getMappingType(),'integer').' '.
			'AND ecs_field = '.$ilDB->quote($this->getECSField(),'text');
		$ilDB->manipulate($query);
	}

	protected function create()
	{
		global $ilDB;

		$query = 'INSERT INTO ecs_data_mapping (sid,mapping_type,ecs_field,advmd_id) '.
			'VALUES('.
			$ilDB->quote($this->getServerId(), 'integer') . ', ' .
			$ilDB->quote($this->getMappingType(),'integer').', '.
			$ilDB->quote($this->getECSField(),'text').', '.
			$ilDB->quote($this->getAdvMDId(),'integer').' ) ';
		$res = $ilDB->manipulate($query);
		return true;
	}


	/**
	 * Read settings
	 *
	 * @access private
	 * 
	 */
	private function read()
	{
		global $ilDB;
		
		if($this->getServerId() and $this->getMappingType() and $this->getECSField())
		{
			$query = 'SELECT * FROM ecs_data_mapping '.
				'WHERE sid = '.$ilDB->quote($this->getServerId(),'integer').' '.
				'AND mapping_type = '.$ilDB->quote($this->getMappingType(),'integer').' '.
				'AND ecs_field = '.$ilDB->quote($this->getECSField(),'text');
			$res = $ilDB->query($query);
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$this->setAdvMDId($row->advmd_id);
			}
		}
	}
	
	public static function deleteByServerId($a_server_id)
	{
		global $ilDB;

		$query = 'DELETE FROM ecs_data_mapping'.
			' WHERE sid = '.$ilDB->quote($a_server_id,'integer');
		$ilDB->manipulate($query);
		return true;
	}
}
?>