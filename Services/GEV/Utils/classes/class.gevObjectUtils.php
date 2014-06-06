<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Utilities for objects.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

class gevObjectUtils {
	static protected $instances = array();

	static public function getRefId($a_obj_id) {
		global $ilDB;
		$res = $ilDB->query("SELECT ref_id FROM object_reference WHERE obj_id = ".$ilDB->quote($a_obj_id));
		if ($ret = $ilDB->fetchAssoc($res)) {
			return $ret["ref_id"];
		}
		return null;
	}
	
	static public function getAllRefIds($a_obj_id) {
		global $ilDB;
		$res = $ilDB->query("SELECT ref_id FROM object_reference WHERE obj_id = ".$ilDB->quote($a_obj_id));
		$ret = array();
		while ($val = $ilDB->fetchAssoc($res)) {
			$ret[] = $val["ref_id"];
		}
		return $ret;
	}
	
	static public function checkAccessOfUser($a_user_id, $a_permission, $a_command, $a_obj_id, $a_type = "", $a_tree_id = "") {
		global $ilAccess;
		$ref_ids = self::getAllRefIds($a_obj_id);
		foreach ($ref_ids as $ref_id) {
			if ($ilAccess->checkAccessOfUser($a_user_id, $a_permission, $a_command, $ref_id)){//, $a_type, $a_obj_id, $a_tree_id)) {
				return true;
			}
		}
		return false;
	}
	
	static public function getObjId($a_ref_id) {
		global $ilDB;
		$res = $ilDB->query("SELECT obj_id FROM object_reference WHERE ref_id = ".$ilDB->quote($a_ref_id));
		if ($ret = $ilDB->fetchAssoc($res)) {
			return $ret["obj_id"];
		}
		return null;
	}
}

?>