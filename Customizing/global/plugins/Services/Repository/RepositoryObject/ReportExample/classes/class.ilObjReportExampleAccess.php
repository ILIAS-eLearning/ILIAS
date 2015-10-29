<?php

require_once 'Services/ReportsRepository/classes/class.ilObjReportBaseAccess.php';


class ilObjReportExampleAccess extends ilObjReportBaseAccess {

	/**
	* {@inheritdoc}
	*/
	public function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "") {
		global $ilUser, $ilAccess;

		if ($a_user_id == "") {
			$a_user_id = $ilUser->getId();
		}

		switch ($a_permission) {
			case "read":
				if (!self::checkOnline($a_obj_id) &&
				!$ilAccess->checkAccessOfUser($a_user_id, "write", "", $a_ref_id))	{
					return false;
				}
				break;
		}

		return true;
	}

	/**
	* {@inheritdoc}
	*/
	static public function checkOnline($a_id) {
		global $ilDB;

		$set = $ilDB->query("SELECT is_online FROM rep_robj_rts ".
			" WHERE id = ".$ilDB->quote($a_id, "integer")
			);
		$rec  = $ilDB->fetchAssoc($set);
		return (boolean) $rec["is_online"];
	}

}