<?php
// BEGIN WebDAV
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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

require_once "classes/class.ilObject.php";
require_once "Services/WebDAV/classes/class.ilObjNull.php";
/**
* Class ilDAVLocks
*
* Handles locking of DAV objects.
* This class encapsulates the database table dav_lock.
*
* @author Werner Randelshofer, Hochschule Luzern, werner.randelshofer@hslu.ch
* @version $Id: class.ilDAVLocks.php,v 1.0 2005/07/08 12:00:00 wrandelshofer Exp $
*
* @package webdav
*/
class ilDAVLocks
{
	private $table = 'dav_lock';
	
	public function ilDAVLocks()
	{
	}
	
	/**
	 * Creates a write lock.
	 *
	 * As described in RFC2518, chapter 7.1, a write lock prevents all principals whithout
	 * the lock from successfully executing a PUT, POST, PROPPATCH, LOCK, UNLOCK, MOVE,
	 * DELETE, or MKCOL on the locked resource. All other current methods, GET in particular,
	 * function independently of the lock.
	 * For a collection, the lock also affects the ability to add and remove members.
	 *
	 * @param $objDAV DAV object to be locked.
	 * @param int ILIAS user id of the lock owner.
	 * @param string DAV user of the lock owner.
	 * @param string Lock token.
	 * @param int expiration timestamp for the lock.
	 * @param bool Depth of the lock. Must be 0 or 'infinity'.
	 * @param bool Scope of the lock. Must be 'exclusive' or 'shared'.
	 *
	 * @return true, if creation of lock succeeded.
	 */
	public function lock(&$objDAV, $iliasUserId, $davUser, $token, $expires, $depth, $scope)
	{
		global $ilDB;
		
		$objId  = $objDAV->getObjectId();
		$nodeId = $objDAV->getNodeId();
		
		switch ($depth)
		{
			case 'infinity' : $depth = -1; 
				break;
			case 0 :
				$depth = 0;
				break;
			default : 
				trigger_error('invalid depth '.$depth,E_ERROR); 
				return;
		}
		
		switch ($scope)
		{
			case 'exclusive' : $scope = 'x'; break;
			case 'shared' : $scope = 's'; break;
			default : trigger_error('invalid scope '.$scope,E_ERROR); return;
		}
		$this->writelog('ilDAVLocks.lock depth='.$depth.' scope='.$scope);
		
		$q = 'INSERT INTO '.$this->table
				.' SET obj_id   = '.$ilDB->quote($objId)
				.', node_id     = '.$ilDB->quote($nodeId)
				.', ilias_owner = '.$ilDB->quote($iliasUserId)
				.', dav_owner   = '.$ilDB->quote($davUser)
				.', token       = '.$ilDB->quote($token)
				.', expires     = '.$ilDB->quote($expires)
				.', depth       = '.$ilDB->quote($depth)
				.', type        = \'w\''
				.', scope       = '.$ilDB->quote($scope)
				;
		$this->writelog('lock query='.$q);
		$ilDB->query($q);
		return true;
	}
	/**
	 * Updates a write lock.
	 *
	 * @param string Lock token.
	 * @param int expiration timestamp for the lock.
	 *
	 * @return true on success.
	 */
	public function updateLock(&$objDAV, $token, $expires)
	{
		global $ilDB;
		$objId  = $objDAV->getObjectId();
		$nodeId = $objDAV->getNodeId();
		
		$q = 'UPDATE '.$this->table
				.' SET expires = '.$ilDB->quote($expires)
				.' WHERE token = '.$ilDB->quote($token)
				.' AND obj_id = '.$ilDB->quote($objId)
				.' AND node_id = '.$ilDB->quote($nodeId)
				;
		$ilDB->query($q);
		return mysql_affected_rows() > 0;
	}
	/**
	 * Discards a write lock.
	 *
	 * @param $objDAV DAV object to be locked.
	 * @param string Lock token.
	 *
	 * @return true on success.
	 */
	public function unlock(&$objDAV, $token)
	{
		global $ilDB;
		$this->writelog('unlock('.$objDAV.','.$token.')');
		
		$objId  = $objDAV->getObjectId();
		$nodeId = $objDAV->getNodeId();
		
		// Unlock object
		// FIXME - Maybe we should delete all rows with the same token, not
		// just the ones with the same token, obj_id and node_id.
		$q = 'DELETE FROM '.$this->table
				.' WHERE token = '.$ilDB->quote($token)
				.' AND obj_id = '.$ilDB->quote($objId)
				.' AND node_id = '.$ilDB->quote($nodeId)
				;
		$this->writelog('unlock query='.$q);
		$ilDB->query($q);
		$success = mysql_affected_rows() > 0;
		
		// clean up expired locks in 1 out of 100 unlock requests
		if (rand(1,100) == 1)
		{
			ilDAVLocks::cleanUp();
		}
		
		return $success;
	}
	

	
	/**
	 * Returns the lock with the specified token on the specified object.
	 *
	 * @param $objDAV DAV object to get the lock for.
	 * @param string Lock token.
	 * @return An array of associative arrays for all the locks that were found.
	 *         Each associative array has the following keys:
	 *         'ilias_owner' => user id,
	 *         'dav_owner' => user name,
	 *         'token' => locktoken
	 *         'expires' => expiration timestamp
	 *         'depth' => 0 or 'infinity'
	 *         'scope' => 'exclusive' or 'shared'
	 */
	public function getLock(&$objDAV,$token)
	{
		global $ilDB;
		$this->writelog('getLocks('.$objDAV.')');
		$objId  = $objDAV->getObjectId();
		$nodeId = $objDAV->getNodeId();
		
		$q = 'SELECT ilias_owner, dav_owner, expires, depth, scope'
				.' FROM '.$this->table
				.' WHERE obj_id = '.$ilDB->quote($objId)
				.' AND node_id = '.$ilDB->quote($nodeId)
				.' AND token = '.$ilDB->quote($token)
				;
		$this->writelog('getLocks('.$objDAV.') query='.$q);
		$r = $ilDB->query($q);
		
		$result = array();		
		while ($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($row['depth'] == -1) $row['depth'] = 'infinity';
			$row['scope'] = ($row['scope'] == 'x') ? 'exclusive' : 'shared';
			$row['token'] = $token;
			$result = $row;
		}
		return $result;
	}
	/**
	 * Returns all locks on the specified object. This method does not take into
	 * account inherited locks from parent objects.
	 *
	 * @param $objDAV DAV object to get the locks for.
	 * @return An array of associative arrays for all the locks that were found.
	 *         Each associative array has the following keys:
	 *         'ilias_owner' => user id,
	 *         'dav_owner' => user name,
	 *         'token' => locktoken
	 *         'expires' => expiration timestamp
	 *         'depth' => 0 or 'infinity'
	 *         'scope' => 'exclusive' or 'shared'
	 */
	public function getLocks(&$objDAV)
	{
		global $ilDB;
		$this->writelog('getLocks('.$objDAV.')');
		$objId  = $objDAV->getObjectId();
		$nodeId = $objDAV->getNodeId();
		
		$q = 'SELECT ilias_owner, dav_owner, token, expires, depth, scope'
				.' FROM '.$this->table
				.' WHERE obj_id = '.$ilDB->quote($objId)
				.' AND node_id = '.$ilDB->quote($nodeId)
				.' AND expires > '.$ilDB->quote(time())
				;
		$this->writelog('getLocks('.$objDAV.') query='.$q);
		$r = $ilDB->query($q);
		
		$result = array();		
		while ($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($row['depth'] == -1) $row['depth'] = 'infinity';
			$row['scope'] = ($row['scope'] == 'x') ? 'exclusive' : 'shared';
			$result[] = $row;
		}
		return $result;
	}
	/**
	 * Returns all locks on the specified object id. This method does not take into
	 * account inherited locks from parent objects.
	 *
	 * @param $objId object ID to get the locks for.
	 * @return An array of associative arrays for all the locks that were found.
	 *         Each associative array has the following keys:
	 *         'ilias_owner' => user id,
	 *         'dav_owner' => user name,
	 *         'token' => locktoken
	 *         'expires' => expiration timestamp
	 *         'depth' => 0 or 'infinity'
	 *         'scope' => 'exclusive' or 'shared'
	 */
	public function getLocksOnObject($objId)
	{
		global $ilDB;
		$this->writelog('getLocks('.$objDAV.')');
		$nodeId = 0;
		
		$q = 'SELECT ilias_owner, dav_owner, token, expires, depth, scope'
				.' FROM '.$this->table
				.' WHERE obj_id = '.$ilDB->quote($objId)
				.' AND node_id = '.$ilDB->quote($nodeId)
				.' AND expires > '.$ilDB->quote(time())
				;
		$this->writelog('getLocks('.$objDAV.') query='.$q);
		$r = $ilDB->query($q);
		
		$result = array();		
		while ($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($row['depth'] == -1) $row['depth'] = 'infinity';
			$row['scope'] = ($row['scope'] == 'x') ? 'exclusive' : 'shared';
			$result[] = $row;
		}
		return $result;
	}
	/**
	 * Returns all locks on the specified object path.
	 *
	 * @param $pathDAV Array with DAV objects to get the locks for.
	 * @return An array of associative arrays for all the locks that were found.
	 *         Each associative array has the following keys:
	 *         'obj_id' => object id
	 *         'node_id' => node id
	 *         'ilias_owner' => user id,
	 *         'dav_owner' => user name,
	 *         'token' => locktoken
	 *         'expires' => expiration timestamp
	 *         'depth' => 0 or 'infinity'
	 *         'scope' => 'exclusive' or 'shared'
	 */
	public function getLocksOnPath(&$pathDAV)
	{
		global $ilDB;
		$this->writelog('getLocks('.$pathDAV.')');
		
		$q = 'SELECT obj_id, node_id, ilias_owner, dav_owner, token, expires, depth, scope'
					.' FROM '.$this->table
					.' WHERE expires > '.$ilDB->quote(time())
					.' AND ('
					;
		$isFirst = true;
		foreach ($pathDAV as $objDAV)
		{
			$objId  = $objDAV->getObjectId();
			$nodeId = $objDAV->getNodeId();
			if ($isFirst) 
			{
				$isFirst = false;
			} else {
				$q .= ' OR ';
			}
			$q .= '(obj_id = '.$objId.' AND node_id = '.$nodeId.')';
		}
		$q .= ')';
				
		$this->writelog('getLocksOnPath('.$objDAV.') query='.$q);
		$r = $ilDB->query($q);
		
		$result = array();		
		while ($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($row['depth'] == -1) $row['depth'] = 'infinity';
			$row['scope'] = ($row['scope'] == 'x') ? 'exclusive' : 'shared';
			$result[] = $row;
		}
		return $result;
	}
	/**	
	 * System maintenance: get rid of locks that have expired over
	 * an hour ago. Since we have no index over the 'expires' column,
	 * this causes a (very slow) table space scan.
	 */
	public static function cleanUp()
	{
		global $ilDB, $tree;

		// 1. Get rid of locks that have expired over an hour ago
		$old = time() - 3600;
		$q = 'DELETE'
			.' FROM '.$this->table
			.' WHERE expires < '.$ilDB->quote($old)
		;
		$ilDB->query($q);
		
		// 2. Get rid of null resources which are not associated to
		//    a lock due to step 1, or due to a database inconsistency
		//    because we are working with non-transactional tables
		$q = 'SELECT dat.obj_id '
				.' FROM object_data AS dat'
				.' LEFT JOIN '.$this->table.' AS lck'
				.' ON dat.obj_id = lck.obj_id'
				.' WHERE dat.type = \'null\''
				.' AND lck.obj_id IS NULL'
				.' FOR UPDATE'
				;
		
            	$r = $ilDB->query($q);
		while ($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$references = ilObject::_getAllReferences($row['obj_id']);
			$obj =& new ilObjNull($row['obj_id'], false);
			if (count($references) == 0)
			{
				$obj->delete();
			} else {
				foreach ($references as $refId)
				{
					$obj->setRefId($refId);
					$obj->delete();
					$nodeData = $tree->getNodeData($refId);
					$tree->deleteTree($nodeData);
				}
			}
		}
	}
	/**
	 * Writes a message to the logfile.,
	 *
	 * @param  message String.
	 * @return void.
	 */
	protected function writelog($message) 
	{
		global $log, $ilias;
		$log->write(
			$ilias->account->getLogin()
			.' DAV ilDAVLocks.'.str_replace("\n",";",$message)
		);
		/*
		if ($this->logFile) 
		{
			$fh = fopen($this->logFile, 'a');
			fwrite($fh, date('Y-m-d h:i:s '));
			fwrite($fh, str_replace("\n",";",$message));
			fwrite($fh, "\n\n");
			fclose($fh);		
		}*/
	}
}
// END WebDAV
?>
