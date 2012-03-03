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

require_once "./Services/Object/classes/class.ilObject.php";
require_once "Services/WebDAV/classes/class.ilObjNull.php";
/**
* Class ilDAVLocks
*
* Handles locking of DAV objects.
* This class encapsulates the database table dav_lock.
*
* This class provides low-level functions, which do not check on existing
* locks, before a certain lock-operation is performed.
*
*
* @author Werner Randelshofer, Hochschule Luzern, werner.randelshofer@hslu.ch
* @version $Id: class.ilDAVLocks.php,v 1.0 2005/07/08 12:00:00 wrandelshofer Exp $
*
* @package webdav
*/
class ilDAVLocks
{
	private $table = 'dav_lock';

	/** Set this to true, to get debug output in the ILIAS log. */
	private $isDebug = false;
	
	public function ilDAVLocks()
	{
	}
	
	/**
	 * Creates a lock an object, unless there are locks on the object or its 
	 * parents, which prevent the creation of the lock.
	 *
	 * As described in RFC2518, chapter 7.1, a write lock prevents all principals whithout
	 * the lock from successfully executing a PUT, POST, PROPPATCH, LOCK, UNLOCK, MOVE,
	 * DELETE, or MKCOL on the locked resource. All other current methods, GET in particular,
	 * function independently of the lock.
	 * For a collection, the lock also affects the ability to add and remove members.
	 *
	 * @param int Reference id of the object to be locked.
	 * @param int The id of a node of the object. For example the id of a page of a
	 * learning module. Specify 0 if the object does not have multiple nodes.
	 * @param int ILIAS user id of the lock owner.
	 * @param string DAV user of the lock owner.
	 * @param string Lock token.
	 * @param int expiration timestamp for the lock.
	 * @param bool Depth of the lock. Must be 0 or 'infinity'.
	 * @param bool Scope of the lock. Must be 'exclusive' or 'shared'.
	 *
	 * @return true, if creation of lock succeeded, returns a string 
	 * if the creation failed.
	 */
	public function lockRef($refId, $iliasUserId, $davUser, $token, $expires, $depth, $scope)
	{
		$this->writelog('lockRef('.$refId.','.$iliasUserId.','.$davUser.','.$token.','.$expires.','.$depth.','.$scope.')');
		global $tree, $txt;

		$result = true;
		$data = $tree->getNodeData($refId);

		// Check whether a lock on the path to the object prevents the creation
		// of a new lock
		$locksOnPath = $this->getLocksOnPathRef($refId);

		if ($scope == 'exclusive' && count($locksOnPath) > 0) {
			$result = 'couldnt create exclusive lock due to existing lock on path '.var_export($locksOnPath,true);
		}

		foreach ($locksOnPath as $lock)
		{
			if ($lock['token'] == $token && 
				$lock['obj_id'] == $data['obj_id'] &&
				$lock['ilias_owner'] == $iliasUserId)
			{
				if ($this->updateLockWithoutCheckingObj($data['obj_id'], 0, $token, $expires))
				{
					return true;
				} 
				else 
				{
					return 'couldnt update lock';
				}
			}
		}

		if ($result === true)
		{
			foreach ($locksOnPath as $lock)
			{
				if ($lock['scope'] == 'exclusive' && 
					($lock['depth'] == 'infinity' || $lock['obj_id'] == $data['obj_id']) &&
					$lock['ilias_owner'] != $iliasUserId)
				{
					$result = 'couldnt create lock due to exclusive lock on path '.var_export($lock,true);
					break;
				}
			}
		}

		// Check whether a lock on the children (subtree) of the object prevents
		// the creation of a new lock
		if ($result === true && $depth == 'infinity')
		{
			// XXX - if lock has depth infinity, we must check for locks in the subtree
		}

		if ($result === true)
		{
			$result = $this->lockWithoutCheckingObj(
				$data['obj_id'], 0,
				$iliasUserId, $davUser, $token, $expires, $depth, $scope
				);
		}
		return $result;
	}

	/**
	 * Creates a write lock.
	 *
	 * Important: This is a low-level function, which does not check on existing
	 * locks, before creating the lock data.
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
	public function lockWithoutCheckingDAV(&$objDAV, $iliasUserId, $davUser, $token, $expires, $depth, $scope)
	{
		$objId  = $objDAV->getObjectId();
		$nodeId = $objDAV->getNodeId();

		return $this->lockWithoutCheckingObj($objId, $nodeId, $iliasUserId, $davUser, $token, $expires, $depth, $scope);
	}
	/**
	 * Creates a write lock.
	 *
	 * Important: This is a low-level function, which does not check on existing
	 * locks, before creating the lock data.
	 *
	 * As described in RFC2518, chapter 7.1, a write lock prevents all principals whithout
	 * the lock from successfully executing a PUT, POST, PROPPATCH, LOCK, UNLOCK, MOVE,
	 * DELETE, or MKCOL on the locked resource. All other current methods, GET in particular,
	 * function independently of the lock.
	 * For a collection, the lock also affects the ability to add and remove members.
	 *
	 * @param int id of the object to be locked.
	 * @param int node The id of a node of the object. For example the id of a page of a
	 * learning module. Specify 0 if the object does not have multiple nodes.
.	 * @param int ILIAS user id of the lock owner.
	 * @param string DAV user of the lock owner.
	 * @param string Lock token.
	 * @param int expiration timestamp for the lock.
	 * @param bool Depth of the lock. Must be 0 or 'infinity'.
	 * @param bool Scope of the lock. Must be 'exclusive' or 'shared'.
	 *
	 * @return true, if creation of lock succeeded.
	 */
	public function lockWithoutCheckingObj($objId, $nodeId, $iliasUserId, $davUser, $token, $expires, $depth, $scope)
	{
		global $ilDB;
		
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
		
		$q = 'INSERT INTO '.$this->table
				.' SET obj_id   = '.$ilDB->quote($objId,'integer')
				.', node_id     = '.$ilDB->quote($nodeId,'integer')
				.', ilias_owner = '.$ilDB->quote($iliasUserId,'text')
				.', dav_owner   = '.$ilDB->quote($davUser,'text')
				.', token       = '.$ilDB->quote($token,'text')
				.', expires     = '.$ilDB->quote($expires,'integer')
				.', depth       = '.$ilDB->quote($depth,'integer')
				.', type        = \'w\''
				.', scope       = '.$ilDB->quote($scope,'text')
				;
		$this->writelog('lock query='.$q);
		$result = $ilDB->manipulate($q);
		return ! PEAR::isError($result);
	}
	/**
	 * Updates a write lock.
	 *
	 * Important: This is a low-level function, which does not check on existing
	 * locks, before updating the lock data.
	 *
	 * @param string Lock token.
	 * @param int expiration timestamp for the lock.
	 *
	 * @return true on success.
	 */
	public function updateLockWithoutCheckingDAV(&$objDAV, $token, $expires)
	{
		global $ilDB;
		$objId  = $objDAV->getObjectId();
		$nodeId = $objDAV->getNodeId();

		return $this->updateLockWithoutCheckingObj($objId, $nodeId, $token, $expires);
	}
	/**
	 * Updates a write lock.
	 *
	 * Important: This is a low-level function, which does not check on existing
	 * locks, before updating the lock data.
	 *
	 * @param string Lock token.
	 * @param int expiration timestamp for the lock.
	 *
	 * @return true on success.
	 */
	public function updateLockWithoutCheckingObj($objId, $nodeId, $token, $expires)
	{
		global $ilDB;
		
		$q = 'UPDATE '.$this->table
				.' SET expires = '.$ilDB->quote($expires,'integer')
				.' WHERE token = '.$ilDB->quote($token,'text')
				.' AND obj_id = '.$ilDB->quote($objId,'integer')
				.' AND node_id = '.$ilDB->quote($nodeId,'integer')
				;
		$aff = $ilDB->manipulate($q);
		return $aff > 0;
	}
	/**
	 * Discards a write lock.
	 *
	 * Important: This is a low-level function, which does not check on existing
	 * locks, before deleting the lock data.
	 *
	 * @param $objDAV DAV object to be locked.
	 * @param string Lock token.
	 *
	 * @return true on success.
	 */
	public function unlockWithoutCheckingDAV(&$objDAV, $token)
	{
		global $ilDB;
		$this->writelog('unlock('.$objDAV.','.$token.')');
		
		$objId  = $objDAV->getObjectId();
		$nodeId = $objDAV->getNodeId();
		
		// Unlock object
		// FIXME - Maybe we should delete all rows with the same token, not
		// just the ones with the same token, obj_id and node_id.
		$q = 'DELETE FROM '.$this->table
				.' WHERE token = '.$ilDB->quote($token,'text')
				.' AND obj_id = '.$ilDB->quote($objId,'integer')
				.' AND node_id = '.$ilDB->quote($nodeId,'integer')
				;
		$this->writelog('unlock query='.$q);
		$aff = $ilDB->manipulate($q);
		$success = $aff > 0;
		
		// clean up expired locks in 1 out of 100 unlock requests
		if (rand(1,100) == 1)
		{
			$this->cleanUp();
		}
		
		return $success;
	}
	
	/**
	 * Returns the lock with the specified token on the specified DAV object.
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
	public function getLockDAV(&$objDAV,$token)
	{
		global $ilDB;
		$this->writelog('getLocks('.$objDAV.')');
		$objId  = $objDAV->getObjectId();
		$nodeId = $objDAV->getNodeId();
		
		$q = 'SELECT ilias_owner, dav_owner, expires, depth, scope'
				.' FROM '.$this->table
				.' WHERE obj_id = '.$ilDB->quote($objId,'integer')
				.' AND node_id = '.$ilDB->quote($nodeId,'integer')
				.' AND token = '.$ilDB->quote($token,'text')
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
	public function getLocksOnObjectDAV(&$objDAV)
	{
		$objId  = $objDAV->getObjectId();
		$nodeId = $objDAV->getNodeId();

		return $this->getLocksOnObjectObj($objId, $nodeId);
	}
	/**
	 * Returns all locks on the specified object id. This method does not take into
	 * account inherited locks from parent objects.
	 *
	 * @param $objId object ID to get the locks for.
	 * @param int node a node of the object. For example the id of a page of a
	 * learning module. Specify 0 if the object does not have multiple nodes.
	 * @return An array of associative arrays for all the locks that were found.
	 *         Each associative array has the following keys:
	 *         'ilias_owner' => user id,
	 *         'dav_owner' => user name,
	 *         'token' => locktoken
	 *         'expires' => expiration timestamp
	 *         'depth' => 0 or 'infinity'
	 *         'scope' => 'exclusive' or 'shared'
	 */
	public function getLocksOnObjectObj($objId, $nodeId = 0)
	{
		global $ilDB;
		$this->writelog('getLocks('.$objDAV.')');
		$nodeId = 0;
		$q = 'SELECT ilias_owner, dav_owner, token, expires, depth, scope'
				.' FROM '.$this->table
				.' WHERE obj_id = '.$ilDB->quote($objId,'integer')
				.' AND node_id = '.$ilDB->quote($nodeId,'integer')
				.' AND expires > '.$ilDB->quote(time(),'integer')
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
	public function getLocksOnPathDAV(&$pathDAV)
	{
		global $ilDB;
		$this->writelog('getLocksOnPathDAV');
		
		$q = 'SELECT obj_id, node_id, ilias_owner, dav_owner, token, expires, depth, scope'
					.' FROM '.$this->table
					.' WHERE expires > '.$ilDB->quote(time(),'integer')
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
			$q .= '(obj_id = '.$ilDB->quote($objId,'integer').' AND node_id = '.$ilDB->quote($nodeId,'integer').')';
		}
		$q .= ')';
				
		$this->writelog('getLocksOnPathDAV('.$objDAV.') query='.$q);
		$r = $ilDB->query($q);
		
		$result = array();		
		while ($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($row['depth'] == -1) $row['depth'] = 'infinity';
			$row['scope'] = ($row['scope'] == 'x') ? 'exclusive' : 'shared';
			$result[] = $row;
		}
		$this->writelog('getLocksOnPathDAV:'.var_export($result,true));
		return $result;
	}
	/**
	 * Returns all locks on the specified object, specified by a reference id.
	 *
	 * @param $refId The reference id of the object
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
	public function getLocksOnPathRef($refId)
	{
		global $ilDB, $tree;
		$this->writelog('getLocksOnPathRef('.$refId.')');

		$pathFull = $tree->getPathFull($refId);
		
		$q = 'SELECT obj_id, node_id, ilias_owner, dav_owner, token, expires, depth, scope'
					.' FROM '.$this->table
					.' WHERE expires > '.$ilDB->quote(time(),'integer')
					.' AND ('
					;
		$isFirst = true;
		foreach ($pathFull as $pathItem)
		{
			$objId  = $pathItem['obj_id'];
			$nodeId = 0;
			if ($isFirst) 
			{
				$isFirst = false;
			} else {
				$q .= ' OR ';
			}
			$q .= '(obj_id = '.$ilDB->quote($objId,'integer').' AND node_id = '.$ilDB->quote($nodeId,'integer').')';
		}
		$q .= ')';
				
		$this->writelog('getLocksOnPathRef('.$refId.') query='.$q);
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
	public function cleanUp()
	{
		global $ilDB, $tree;

		// 1. Get rid of locks that have expired over an hour ago
		$old = time() - 3600;
		$q = 'DELETE'
			.' FROM '.$this->table
			.' WHERE expires < '.$ilDB->quote($old,'integer')
		;
		$ilDB->manipulate($q);
		
		// 2. Get rid of null resources which are not associated to
		//    a lock due to step 1, or due to a database inconsistency
		//    because we are working with non-transactional tables
		$q = 'SELECT dat.obj_id '
				.' FROM object_data AS dat'
				.' LEFT JOIN '.$this->table.' lck'
				.' ON dat.obj_id = lck.obj_id'
				.' WHERE dat.type = '.$ilDB->quote('null','text')
				.' AND lck.obj_id IS NULL'
				;
/*	TODO: smeyer.' FOR UPDATE' */
		
            	$r = $ilDB->query($q);
		while ($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$references = ilObject::_getAllReferences($row['obj_id']);
			$obj = new ilObjNull($row['obj_id'], false);
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
		if ($this->isDebug)
		{
			$log->write(
				$ilias->account->getLogin()
				.' DAV ilDAVLocks.'.str_replace("\n",";",$message)
			);
		}
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
