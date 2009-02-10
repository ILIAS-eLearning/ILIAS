<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2007 ILIAS open source, University of Cologne            |
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
* Class ilChangeEvent tracks change events on repository objects.
*
* The following events are considered to be a 'write event':
*  - The creation of a new repository object
*  - A change of the data or meta-data of an object
*  - A move, link, copy, deletion or undeletion of the object
* UI objects, which cause a 'write event', must call _recordWriteEvent(...)
* In most cases, UI objects let the user catch up with write events on the 
* object, when doing this call.
* 
* The following events are considered to be a 'read event':
*  - Opening a container object in the browser* 
*  - Opening / downloading / reading an object
* UI objects, which cause a 'read event', must call _recordReadEvent(...).
* In most cases, UI objects let the user catch up with write events on the 
* object, when doing this call.
*
* *reading the content of a container using WebDAV is not counted, because WebDAV
*  clients can't see all objects in a container.
*
* A user can catch up with write events, by calling __catchupWriteEvents(...).
*
* A user can query, if an object has changed, since the last time he has caught
* up with write events, by calling _lookupUncaughtWriteEvents(...).
*
*
* @author 		Werner Randelshofer <werner.randelshofer@hslu.ch>
* @version $Id: class.ilChangeEvent.php,v 1.02 2007/05/07 19:25:34 wrandels Exp $
*
*/
class ilChangeEvent
{
	/**
	 * Records a write event.
	 * 
	 * The parent object should be specified for the 'delete', 'undelete' and
	 * 'add' and 'remove' events.
	 *
	 * @param $obj_id int The object which was written to.
	 * @param $usr_id int The user who performed a write action.
	 * @param $action string The name of the write action.
	 *  'create', 'update', 'delete', 'add', 'remove', 'undelete'.        
	 * @param $parent_obj_id int The object id of the parent object.
	 *      If this is null, then the event is recorded for all parents
	 *      of the object. If this is not null, then the event is only 
	 *      recorded for the specified parent.
	 */
	function _recordWriteEvent($obj_id, $usr_id, $action, $parent_obj_id = null)
	{
		global $ilDB;
		
		if ($parent_obj_id == null)
		{
			$query = 'INSERT INTO write_event '.
				'(obj_id, parent_obj_id, usr_id, action, ts) '.
					'SELECT ?,r2.obj_id,?,?,'.$ilDB->now().' FROM object_reference r1 '.
					'JOIN tree t ON t.child = r1.ref_id '.
					'JOIN object_reference r2 ON r2.ref_id = t.parent '.
					'WHERE r1.obj_id = ? ';
			$sta = $ilDB->prepare($query,array('integer','integer','text','integer'));
			$res = $ilDB->execute($sta,array(
				$obj_id,
				$usr_id,
				$action,
				$obj_id));
		}
		else
		{
			$query = 'INSERT INTO write_event '.
				'(obj_id, parent_obj_id, usr_id, action, ts) '.
				'VALUES(?,?,?,?,'.$ilDB->now().')';
			$sta = $ilDB->prepare($query,array('integer','integer','integer','integer'));
			$res = $ilDB->execute($sta,array(
				$obj_id,
				$parent_obj_id,
				$usr_id,
				$action));
		}
		//error_log ('ilChangeEvent::_recordWriteEvent '.$q);
	}
	
	/**
	 * Records a read event and catches up with write events.
	 *
	 * @param $obj_id int The object which was read.
	 * @param $usr_id int The user who performed a read action.
	 * @param $catchupWriteEvents boolean If true, this function catches up with
	 * 	write events.
	 */
	function _recordReadEvent($obj_id, $usr_id, $isCatchupWriteEvents = true)
	{
		global $ilDB;
		include_once('Services/Tracking/classes/class.ilObjUserTracking.php');
		$validTimeSpan = ilObjUserTracking::_getValidTimeSpan();

		// Important: In the SQL statement below, it is important that ts
		//            is updated after spent_seconds is updated, because
		//            spent_seconds computes its value from the old value of ts.
		$q = "INSERT INTO read_event ".
				"(obj_id, usr_id, first_access, last_access, read_count) ".
				"VALUES (".
				$ilDB->quote($obj_id).",".
				$ilDB->quote($usr_id).",".
				"NOW(), NOW(), 1".
				") ".
			"ON DUPLICATE KEY ".
			"UPDATE ".
				"read_count=read_count+1, ".
				"spent_seconds = IF (TIME_TO_SEC(TIMEDIFF(NOW(),last_access))<=".$ilDB->quote($validTimeSpan).",spent_seconds+TIME_TO_SEC(TIMEDIFF(NOW(),last_access)),spent_seconds),".
				"last_access=NOW()".
			"";
		$r = $ilDB->query($q);
		//error_log ('ilChangeEvent::_recordReadEvent '.$q);
		
		if ($isCatchupWriteEvents)
		{
			ilChangeEvent::_catchupWriteEvents($obj_id, $usr_id);
		}
	}
	
	/**
	 * Catches up with all write events which occured before the specified
	 * timestamp.
	 *
	 * @param $obj_id int The object.
	 * @param $usr_id int The user.
	 * @param $timestamp SQL timestamp.
	 */
	function _catchupWriteEvents($obj_id, $usr_id, $timestamp = null)
	{
		global $ilDB;
		
		
		$q = "INSERT INTO catch_write_events ".
			"(obj_id, usr_id, ts) ".
			"VALUES (".
			$ilDB->quote($obj_id).",".
			$ilDB->quote($usr_id).",";
		if ($timestamp == null)
		{
			$q .= "NOW()".
			") ON DUPLICATE KEY UPDATE ts=NOW()";
		}
		else {
			$q .= $ilDB->quote($timestamp).
			") ON DUPLICATE KEY UPDATE ts=".$ilDB->quote($timestamp);
		}
		//error_log ('ilChangeEvent::_catchupWriteEvents '.$q);
		$r = $ilDB->query($q);
	}
	/**
	 * Catches up with all write events which occured before the specified
	 * timestamp.
	 *
	 * THIS FUNCTION IS CURRENTLY NOT IN USE. BEFORE IT CAN BE USED, THE TABLE
	 * catch_read_events MUST BE CREATED.
	 *
	 *
	 *
	 * @param $obj_id int The object.
	 * @param $usr_id int The user.
	 * @param $timestamp SQL timestamp.
	 * /
	function _catchupReadEvents($obj_id, $usr_id, $timestamp = null)
	{
		global $ilDB;
		
		
		$q = "INSERT INTO catch_read_events ".
			"(obj_id, usr_id, action, ts) ".
			"VALUES (".
			$ilDB->quote($obj_id).",".
			$ilDB->quote($usr_id).",".
			$ilDB->quote('read').",";
		if ($timestamp == null)
		{
			$q .= "NOW()".
			") ON DUPLICATE KEY UPDATE ts=NOW()";
		}
		else {
			$q .= $ilDB->quote($timestamp).
			") ON DUPLICATE KEY UPDATE ts=".$ilDB->quote($timestamp);
		}
		
		$r = $ilDB->query($q);
	}
	*/
	
	
	/**
	 * Reads all write events which occured on the object
	 * which happened after the last time the user caught up with them.
	 *
	 * @param $obj_id int The object
	 * @param $usr_id int The user who is interested into these events.
	 * @return array with rows from table write_event
	 */
	public static function _lookupUncaughtWriteEvents($obj_id, $usr_id)
	{
		global $ilDB;
		
		$q = "SELECT ts ".
			"FROM catch_write_events ".
			"WHERE obj_id=".$ilDB->quote($obj_id)." ".
			"AND usr_id=".$ilDB->quote($usr_id);
		$r = $ilDB->query($q);
		$catchup = null;
		while ($row = $r->fetchRow(DB_FETCHMODE_ASSOC)) {
			$catchup = $row['ts'];
		}
		
		if($catchup == null)
		{
			$query = 'SELECT * FROM write_event '.
				'WHERE obj_id = ? '.
				'AND usr_id <> ? '.
				'ORDER BY ts DESC';
			$sta = $ilDB->prepare($query,array('integer','integer'));
			$res = $ilDB->execute($sta,array(
				$obj_id,
				$usr_id));
				
		}
		else
		{
			$query = 'SELECT * FROM write_event '.
				'WHERE obj_id = ? '.
				'AND usr_id <> ? '.
				'AND ts >= ? '.
				'ORDER BY ts DESC';

			$sta = $ilDB->prepare($query,array('integer','integer','timestamp'));
			$res = $ilDB->execute($sta,array(
				$obj_id,
				$usr_id,
				$catchup));
			
		}
		$events = array();
		while($row = $ilDB->fetchAssoc($res))
		{
			$events[] = $row;
		}
		return $events;
	}
	/**
	 * Returns the change state of the object for the specified user.
	 * which happened after the last time the user caught up with them.
	 *
	 * @param $obj_id int The object
	 * @param $usr_id int The user who is interested into these events.
	 * @return 0 = object is unchanged, 
	 *         1 = object is new,
	 *         2 = object has changed
	 */
	public static function _lookupChangeState($obj_id, $usr_id)
	{
		global $ilDB;
		
		$q = "SELECT ts ".
			"FROM catch_write_events ".
			"WHERE obj_id=".$ilDB->quote($obj_id)." ".
			"AND usr_id=".$ilDB->quote($usr_id);
		$r = $ilDB->query($q);
		$catchup = null;
		while ($row = $r->fetchRow(DB_FETCHMODE_ASSOC)) {
			$catchup = $row['ts'];
		}
			
		if($catchup == null)
		{
			$query = 'SELECT * FROM write_event '.
				'WHERE obj_id = ? '.
				'AND usr_id <> ? '.
				'ORDER BY ts DESC ';
			$ilDB->setLimit(1);
			$sta = $ilDB->prepare($query,array('integer','integer'));
			$res = $ilDB->execute($sta,array(
				$obj_id,
				$usr_id));
		}
		else
		{
			$query = 'SELECT * FROM write_event '.
				'WHERE obj_id = ? '.
				'AND usr_id <> ? '.
				'AND ts > ? '.
				'ORDER BY ts DESC ';
			$ilDB->setLimit(1);
			$sta = $ilDB->prepare($query,array('integer','integer','timestamp'));
			$res = $ilDB->execute($sta,array(
				$obj_id,
				$usr_id,
				$catchup));
			
		}

		$numRows = $res->numRows();
		if ($numRows > 0)
		{
			$row = $ilDB->fetchAssoc($res);
			// if we have write events, and user never catched one, report as new (1)
			// if we have write events, and user catched an old write event, report as changed (2)
			return ($catchup == null) ? 1 : 2;
		}
		else 
		{
			return 0; // user catched all write events, report as unchanged (0)
		}
	}
	/**
	 * Returns the changed state of objects which are children of the specified
	 * parent object.
	 *
	 * Note this gives a different result than calling _lookupChangeState of
	 * each child object. This is because, this function treats a catch on the
	 * write events on the parent as a catch up for all child objects.
	 * This difference was made, because it greatly improves performance
	 * of this function. 
	 *
	 * @param $parent_obj_id int The object id of the parent object.
	 * @param $usr_id int The user who is interested into these events.
	 * @return 0 = object has not been changed inside
	 *         1 = object has been changed inside
	 */
	public static function _lookupInsideChangeState($parent_obj_id, $usr_id)
	{
		global $ilDB;
		
		$q = "SELECT ts ".
			"FROM catch_write_events ".
			"WHERE obj_id=".$ilDB->quote($parent_obj_id)." ".
			"AND usr_id=".$ilDB->quote($usr_id);
		$r = $ilDB->query($q);
		$catchup = null;
		while ($row = $r->fetchRow(DB_FETCHMODE_ASSOC)) {
			$catchup = $row['ts'];
		}
		
		if($catchup == null)
		{
			$query = 'SELECT * FROM write_event '.
				'WHERE parent_obj_id = ? '.
				'AND usr_id <> ? '.
				'ORDER BY ts DESC ';
			$ilDB->setLimit(1);
			$sta = $ilDB->prepare($query,array('integer','integer'));
			$res = $ilDB->execute($sta,array(
				$parent_obj_id,
				$usr_id));
		}
		else
		{
			$query = 'SELECT * FROM write_event '.
				'WHERE parent_obj_id = ? '.
				'AND usr_id <> ? '.
				'AND ts > ? '.
				'ORDER BY ts DESC ';
			$ilDB->setLimit(1);
			$sta = $ilDB->prepare($query,array('integer','integer','timestamp'));
			$res = $ilDB->execute($sta,array(
				$parent_obj_id,
				$usr_id,
				$catchup));
			
		}
		$numRows = $res->numRows();
		if ($numRows > 0)
		{
			$row = $ilDB->fetchAssoc($res);
			// if we have write events, and user never catched one, report as new (1)
			// if we have write events, and user catched an old write event, report as changed (2)
			return ($catchup == null) ? 1 : 2;
		}
		else 
		{
			return 0; // user catched all write events, report as unchanged (0)
		}
	}
	/**
	 * Reads all read events which occured on the object 
	 * which happened after the last time the user caught up with them.
	 *
	 * NOTE: THIS FUNCTION NEEDS TO BE REWRITTEN. READ EVENTS ARE OF INTEREST
	 * AT REF_ID's OF OBJECTS. 
	 *
	 * @param $obj_id int The object
	 * @param $usr_id int The user who is interested into these events.
	 * /
	public static function _lookupUncaughtReadEvents($obj_id, $usr_id)
	{
		global $ilDB;
		
		$q = "SELECT ts ".
			"FROM catch_read_events ".
			"WHERE obj_id=".$ilDB->quote($obj_id)." ".
			"AND usr_id=".$ilDB->quote($usr_id);
		$r = $ilDB->query($q);
		$catchup = null;
		while ($row = $r->fetchRow(DB_FETCHMODE_ASSOC)) {
			$catchup = $row['ts'];
		}
		
		$q = "SELECT * ".
			"FROM read_event ".
			"WHERE obj_id=".$ilDB->quote($obj_id)." ".
			($catchup == null ? "" : "AND last_access > ".$ilDB->quote($catchup))." ".
			($catchup == null ? "" : "AND last_access > ".$ilDB->quote($catchup))." ".
			"ORDER BY last_access DESC";
		$r = $ilDB->query($q);
		$events = array();
		while ($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$events[] = $row;
		}
		return $events;
	}*/
	/**
	 * Reads all read events which occured on the object.
	 *
	 * @param $obj_id int The object
	 * @param $usr_id int Optional, the user who performed these events.
	 */
	public static function _lookupReadEvents($obj_id, $usr_id = null)
	{
		global $ilDB;
		
		if ($usr_id == null)
		{
			$q = "SELECT * ".
				"FROM read_event ".
				"WHERE obj_id=".$ilDB->quote($obj_id)." ".
				"ORDER BY last_access DESC";
		}
		else 
		{
			$q = "SELECT * ".
				"FROM read_event ".
				"WHERE obj_id=".$ilDB->quote($obj_id)." ".
				"AND usr_id=".$ilDB->quote($usr_id)." ".
				"ORDER BY last_access DESC";
		}
		$r = $ilDB->query($q);
		$events = array();
		while ($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$events[] = $row;
		}
		return $events;
	}

	/**
	 * Activates change event tracking.
	 *
	 * @return mixed true on success, a string with an error message on failure.
	 */
	public static function _activate() {
		if (ilChangeEvent::_isActive())
		{
			return 'change event tracking is already active';
		}
		else
		{
			global $ilDB;

			// Insert initial data into table write_event
			// We need to do this here, because we need
			// to catch up write events that occured while the change event tracking was
			// deactivated.

			// IGNORE isn't supported in oracle
			$query = 'INSERT INTO write_event '.
				'(obj_id,parent_obj_id,usr_id,action,ts) '.
				'SELECT r1.obj_id,r2.obj_id,d.owner,?,d.create_date '.
				'FROM object_data AS d '.
				'LEFT JOIN write_event w ON d.obj_id = w.obj_id '.
				'JOIN object_reference AS r1 ON d.obj_id=r1.obj_id '.
				'JOIN tree t ON t.child=r1.ref_id '.
				'JOIN object_reference r2 on r2.ref_id=t.parent '.
				'WHERE w.obj_id IS NULL';

			$sta = $ilDB->prepareManip($query,array('text'));
			$res = $ilDB->execute($sta,array('create'));
			
			
			if ($ilDB->isError($res) || $ilDB->isError($res->result))
			{
				return 'couldn\'t insert initial data into table "write_event": '.
				(($ilDB->isError($r->result)) ? $r->result->getMessage() : $r->getMessage());
			}


			global $ilias;
			$ilias->setSetting('enable_change_event_tracking', '1');

			return $res;
		}
	}

	/**
	 * Deactivates change event tracking.
	 *
	 * @return mixed true on success, a string with an error message on failure.
	 */
	public static function _deactivate() {
		global $ilias;
		$ilias->setSetting('enable_change_event_tracking', '0');
		
	}

	/**
	 * Returns true, if change event tracking is active.
	 *
	 * @return mixed true on success, a string with an error message on failure.
	 */
	public static function _isActive() {
		global $ilias;
		return $ilias->getSetting('enable_change_event_tracking', '0') == '1';
		
	}
	
	/**
	 * Delete object entries
	 *
	 * @return
	 * @static
	 */
	public static function _delete($a_obj_id)
	{
		global $ilDB;
		
		$query = "DELETE FROM write_event WHERE obj_id = ? ";
		$sta = $ilDB->prepare($query,array('integer'));
		$res = $ilDB->execute($sta,array($a_obj_id));
		
		$query = "DELETE FROM read_event WHERE obj_id = ? ";
		$sta = $ilDB->prepare($query,array('integer'));
		$res = $ilDB->execute($sta,array($a_obj_id));
		return true;
	}
}
?>
