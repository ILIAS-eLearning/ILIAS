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