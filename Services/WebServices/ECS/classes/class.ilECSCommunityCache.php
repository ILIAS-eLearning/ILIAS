<?php
/**
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
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup ServicesWebServicesECS
*/

class ilECSCommunityCache
{
	protected static $instance = null;

	protected $sid = 0;
	protected $cid = 0;
	protected $own_id = 0;
	protected $cname = '';
	protected $mids = array();

	protected $entryExists = false;
	

	/**
	 * Singleton constructor
	 * @param int $sid
	 * @param int $cid
	 */
	protected function __construct($sid,$cid)
	{
		$this->sid = $sid;
		$this->cid = $cid;

		$this->read();
	}

	/**
	 * Get instance
	 * @param int $a_sid
	 * @param int $a_cid
	 * @return ilECSCommunityCache
	 */
	public static function getInstance($a_sid,$a_cid)
	{
		if(isset(self::$instance[$a_sid][$a_cid]))
		{
			return self::$instance[$a_sid][$a_cid];
		}
		return self::$instance[$a_sid][$a_cid] = new ilECSCommunityCache($a_sid, $a_cid);
	}



	public function getServerId()
	{
		return $this->sid;
	}

	public function getCommunityId()
	{
		return $this->cid;
	}

	public function setOwnId($a_id)
	{
		$this->own_id = $a_id;
	}

	public function getOwnId()
	{
		return $this->own_id;
	}

	public function setCommunityName($a_name)
	{
		$this->cname = $a_name;
	}

	public function getCommunityName()
	{
		return $this->cname;
	}

	public function setMids($a_mids)
	{
		$this->mids = $a_mids;
	}

	public function getMids()
	{
		return $this->mids;
	}

	/**
	 * Create or update ecs community
	 * @global ilDB $ilDB
	 * @return bool
	 */
	public function update()
	{
		global $ilDB;

		if(!$this->entryExists)
		{
			return $this->create();
		}

		$query = 'UPDATE ecs_community '.
			'SET own_id = '.$ilDB->quote($this->getOwnId(),'integer').', '.
			'cname = '.$ilDB->quote($this->getCommunityName(),'text').', '.
			'mids = '.$ilDB->quote(serialize($this->getMids()),'text').' '.
			'WHERE sid = '.$ilDB->quote($this->getServerId(),'integer').' '.
			'AND cid = '.$ilDB->quote($this->getCommunityId(),'integer');
		$ilDB->manipulate($query);
		return true;
	}



	/**
	 * Create new dataset
	 * @global ilDB $ilDB
	 */
	protected function create()
	{
		global $ilDB;

		$query = 'INSERT INTO ecs_community (sid,cid,own_id,cname,mids) '.
			'VALUES( '.
			$ilDB->quote($this->getServerId(),'integer').', '.
			$ilDB->quote($this->getCommunityId(),'integer').', '.
			$ilDB->quote($this->getOwnId(),'integer').', '.
			$ilDB->quote($this->getCommunityName(), 'text').', '.
			$ilDB->quote(serialize($this->getMids()),'text').' '.
			')';
		$ilDB->manipulate($query);
		return true;
	}

	/**
	 * Read dataset
	 * @global ilDB $ilDB
	 * @return bool
	 */
	protected function read()
	{
		global $ilDB;

		$this->entryExists = false;

		$query = 'SELECT * FROM ecs_community '.
			'WHERE sid = '.$ilDB->quote($this->getServerId(),'integer').' '.
			'AND cid = '.$ilDB->quote($this->getCommunityId(),'integer');
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->entryExists = true;
			$this->setOwnId($row->own_id);
			$this->setCommunityName($row->cname);
			$this->setMids(unserialize($row->mids));
		}
		return true;
	}
	
	public static function deleteByServerId($a_server_id)
	{
		global $ilDB;

		$query = 'DELETE FROM ecs_community'.
			' WHERE sid = '.$ilDB->quote($a_server_id,'integer');
		$ilDB->manipulate($query);
		return true;
	}
}
?>
