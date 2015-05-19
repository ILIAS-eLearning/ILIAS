<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/**
 * Class ilVCPool
 *
 * Manages a bunch of virtual classrooms, such that a consumer can request a VC
 * for a certain timespan and the class guarantees, that every vc is only used once
 * per timespan.
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 */

require_once("Services/Calendar/classes/class.ilDateTime.php");

class ilVCPool {
	const URL_POOL_TABLE = "vc_url_pool";
	const ASSIGNMENT_TABLE = "vc_assignment";
	
	static $instance; // ilVCPool
	
	protected function __construct() {
	}
	
	public function getInstance() {
		if (self::$instance === null) {
			self::$instance = new ilVCPool();
		}
		
		return self::$instance;
	}
	
	public function getDB() {
		global $ilDB;
		return $ilDB;
	}
	
	/**
	 * Get a VC of the given type for the given period.
	 *
	 * @param	string		$a_type
	 * @param	ilDateTime	$a_start
	 * @param	ilDateTime	$a_end
	 * @return	ilVCAssignment | null
	 */
	public function getVCAssignment($a_type, ilDateTime $a_start, ilDateTime $a_end) {
		assert(is_string($a_type));
		assert(ilDateTime::_before($a_start, $a_end));
		
		$ilDB = $this->getDB();
		
		$start = $ilDB->quote($a_start->get(IL_CAL_DATETIME), "datetime");
		$end = $ilDB->quote($a_end->get(IL_CAL_DATETIME), "datetime");
		
		$res = $ilDB->query("SELECT id, url, vc_type "
						   ."  FROM ".self::URL_POOL_TABLE
						   ." WHERE id NOT IN ("
						   ."         SELECT vc_id"
						   ."           FROM ".self::ASSIGNMENT_TABLE
						   ."          WHERE NOT (   ( ts_start < ".$start." AND ts_end < ".$start." )"
						   ."                     OR ( ts_start > ".$end." AND ts_end > ".$end." )"
						   ."                    )"
						   ."       )"
						   ." LIMIT 1"
						   );
		$rec = $ilDB->fetchAssoc($res);
		if (!$rec) {
			return null;
		}
		
		$ass_id = $ilDB->nextId(self::ASSIGNMENT_TABLE);
		$ilDB->manipulate("INSERT INTO ".self::ASSIGNMENT_TABLE." (id, vc_id, ts_start, ts_end) "
						 ." VALUES ( ".$ilDB->quote($ass_id, "integer")
						 ."        , ".$ilDB->quote($rec["id"], "integer")
						 ."        , ".$end
						 ."        , ".$start
						 ."        )"
						 );
		
		$vc = new ilVirtualClassroom((int)$rec["id"], $rec["url"], $rec["vc_type"]);
		return new ilVCAssignment((int)$ass_id, $vc, $a_start, $a_end);
	}
	
	/**
	 * Release an assignment.
	 */
	public function releaseVCAssignment(ilVCAssignment $a_assignment) {
		$ilDB = $this->getDB();
		
		$ilDB->manipulate("DELETE FROM ".self::ASSIGNMENT_TABLE
						." WHERE id = ".$ilDB->quote($a_assignment->getId(), "integer")
						);
	}
	
	/**
	 * Get an VC assignment by id.
	 *
	 * @param	int			$a_id
	 * @return	ilVCAssignment
	 *
	 * @throws	ilException		When id unknown.
	 */
	public function getVCAssignmentById($a_id) {
		assert(is_int($a_id));
		
		$ilDB = $this->getDB();
		
		$res = $ilDB->query("SELECT ass.id, ass.ts_start, ass.ts_end, vc.id as vc_id, vc.url, vc.vc_type"
						   ."  FROM ".self::ASSIGNMENT_TABLE." ass "
						   ."  JOIN ".self::ASSIGNMENT_TABLE." vc "
						   ."    ON ass.vc_id = vc.id"
						   ." WHERE ass.id = ".$ilDB->quote($a_id, "integer")
						   );
		
		$rec = $ilDB->fetchAssoc();
		
		if (!$rec) {
			throw new ilException("Could not find VC assignment with id '$a_id'.");
		}
		
		$vc = new ilVirtualClassroom((int)$rec["vc_id"], $rec["url"], $rec["vc_type"]);
		$begin = new ilDateTime($rec["ts_start"], IL_CAL_DATETIME);
		$end = new ilDateTime($rec["ts_end"], IL_CAL_DATETIME);
		return new ilVCAssignment((int)$rec["id"], $vc, $begin, $end);
	}
}

?>