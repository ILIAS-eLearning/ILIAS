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

include_once './Services/WebServices/ECS/classes/class.ilECSCommunityCache.php';

/**
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup ServicesWebServicesECS
*/

class ilECSCommunitiesCache
{
	private static $instance = null;

	private $communities = array();

	/**
	 * Singleton constructor
	 */
	protected function  __construct()
	{
		$this->read();
	}

	/**
	 * Singleton instance
	 * @return ilECSCommunitiesCache
	 */
	public static function getInstance()
	{
		if(isset(self::$instance))
		{
			return self::$instance;
		}
		return self::$instance = new ilECSCommunitiesCache();
	}

	/**
	 * Read comunities
	 */
	private function read()
	{
		global $ilDB;

		$query = 'SELECT sid,cid FROM ecs_community ';
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->communities[] = ilECSCommunityCache::getInstance($row->sid, $row->cid);
		}
		return true;
	}
}

?>
