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
class ilECSParticipantSetting
{
	// :TODO: what types are needed?
	const IMPORT_UNCHANGED = 0;
	const IMPORT_RCRS = 1;
	const IMPORT_CRS = 2;
	const IMPORT_CMS = 3;
	
	private $server_id = 0;
	private $mid = 0;
	private $export = false;
	private $import = false;
	private $import_type = 1;
	private $title = '';
	private $cname = '';


	private $exists = false;

	
	/**
	 * Constructor
	 *
	 * @access private
	 * 
	 */
	public function __construct($a_server_id,$mid)
	{
	 	$this->server_id = $a_server_id;
		$this->mid = $mid;
		$this->read();
	}
	
	/**
	 * Get server id
	 * @return int
	 */
	public function getServerId()
	{
		return $this->server_id;
	}

	public function setMid($a_mid)
	{
		$this->mid = $a_mid;
	}

	public function getMid()
	{
		return $this->mid;
	}

	public function enableExport($a_status)
	{
		$this->export = $a_status;
	}

	public function isExportEnabled()
	{
		return (bool) $this->export;
	}

	public function enableImport($a_status)
	{
		$this->import = $a_status;
	}

	public function isImportEnabled()
	{
		return $this->import;
	}

	public function setImportType($a_type)
	{
		if($a_type != self::IMPORT_UNCHANGED)
		{
			$this->import_type = $a_type;
		}
	}

	public function getImportType()
	{
		return $this->import_type;
	}

	public function setTitle($a_title)
	{
		$this->title = $a_title;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function getCommunityName()
	{
		return $this->cname;
	}

	public function setCommunityName($a_name)
	{
		$this->cname = $a_name;
	}

	private function exists()
	{
		return $this->exists;
	}

	/**
	 * Update
	 * Calls create automatically when no entry exists
	 */
	public function update()
	{
		global $ilDB;

		if(!$this->exists())
		{
			return $this->create();
		}
		$query = 'UPDATE ecs_part_settings '.
			'SET '.
			'sid = '.$ilDB->quote((int) $this->getServerId(),'integer').', '.
			'mid = '.$ilDB->quote((int) $this->getMid(),'integer').', '.
			'export = '.$ilDB->quote((int) $this->isExportEnabled(),'integer').', '.
			'import = '.$ilDB->quote((int) $this->isImportEnabled(),'integer').', '.
			'import_type = '.$ilDB->quote((int) $this->getImportType(),'integer').', '.
			'title = '.$ilDB->quote($this->getTitle(),'text').', '.
			'cname = '.$ilDB->quote($this->getCommunityName(),'text').' '.
			'WHERE sid = '.$ilDB->quote((int) $this->getServerId(),'integer').' '.
			'AND mid  = '.$ilDB->quote((int) $this->getMid(),'integer');
		$aff = $ilDB->manipulate($query);
		return true;
	}

	private function create()
	{
		global $ilDB;

		$query = 'INSERT INTO ecs_part_settings '.
			'(sid,mid,export,import,import_type,title,cname) '.
			'VALUES( '.
			$ilDB->quote($this->getServerId(),'integer').', '.
			$ilDB->quote($this->getMid(),'integer').', '.
			$ilDB->quote((int) $this->isExportEnabled(),'integer').', '.
			$ilDB->quote((int) $this->isImportEnabled(),'integer').', '.
			$ilDB->quote((int) $this->getImportType(),'integer').', '.
			$ilDB->quote($this->getTitle(),'text').', '.
			$ilDB->quote($this->getCommunityName(),'text').' '.
			')';
		$aff = $ilDB->manipulate($query);
		return true;

	}

	/**
	 * Delete one participant entry
	 * @global <type> $ilDB
	 * @return <type>
	 */
	public function delete()
	{
		global $ilDB;

		$query = 'DELETE FROM ecs_part_settings '.
			'WHERE sid = '.$ilDB->quote($this->getServerId(),'integer').' '.
			'AND mid = '.$ilDB->quote($this->getMid(),'integer');
		$ilDB->manipulate($query);
		return true;
	}

	/**
	 * Read stored entry
	 * @return <type>
	 */
	public function read()
	{
		global $ilDB;

		$query = 'SELECT * FROM ecs_part_settings '.
			'WHERE sid = '.$ilDB->quote($this->getServerId(),'integer').' '.
			'AND mid = '.$ilDB->quote($this->getMid(),'integer');

		$res = $ilDB->query($query);

		$this->exists = ($res->numRows() ? true : false);

		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->enableExport($row->export);
			$this->enableImport($row->import);
			$this->setImportType($row->import_type);
			$this->setTitle($row->title);
			$this->setCommunityName($row->cname);
		}
		return true;
	}
	
	public static function deleteByServerId($a_server_id)
	{
		global $ilDB;

		$query = 'DELETE FROM ecs_events'.
			' WHERE sid = '.$ilDB->quote($a_server_id,'integer');
		$ilDB->manipulate($query);
		return true;
	}
}
?>