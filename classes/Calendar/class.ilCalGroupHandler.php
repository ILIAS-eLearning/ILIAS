<?php
/**
 * Group-Handler
 * The Group-Handler contains the SQL functions for the ILIAS3-Calendar.
 *
 * version 1.0
 * @author Christoph Schulz-Sacharov <sch-sa@gmx.de>
 * @author MArtin Schumacher <ilias@auchich.de>
 * @author Mark Ulbrich <Mark_Ulbrich@web.de>
 **/
require_once "./classes/class.ilObjUser.php";

class GroupHandler
{
	function getGroups($userId) {
		$actUser = new ilObjUser($userId);
		$role = $actUser->data["Role"];
		$groupArray = null;
		$arrayIndex = 0;

		if($role == 2) {
			$dbtable = "dummy_groups";
			$where = "";
			$tempArray = array("ID"   => 0,
									 "term" => "Alle User");
			$groupArray[$arrayIndex] = $tempArray;
			$arrayIndex++;
		}
		else {
			$dbtable = "cal_user_group, dummy_groups";
			$where = "cal_user_group.userId=".$userId.
						  " AND cal_user_group.groupId=dummy_groups.groupId";
		}
		$orderBy = "dummy_groups.term ASC";

		$dbHandler = new ilCalDBHandler();
		$groupResultset = $dbHandler->select($dbtable, "dummy_groups.term as groupTerm, dummy_groups.groupId as groupId", $where, $orderBy);
		if ($groupResultset->numRows() > 0) {
			while($groupRecordSet = $groupResultset->fetchRow(DB_FETCHMODE_ASSOC)) {
				$tempArray = array("ID"   => $groupRecordSet["groupId"],
										 "term" => $groupRecordSet["groupTerm"]);
				$groupArray[$arrayIndex] = $tempArray;
				$arrayIndex++;
			}
		}
		
		return $groupArray;
	}
	
	function getAllUsers() {
		$userArray = null;
		$dbh = new ilCalDBHandler();
		$userResultset = $dbh->select("usr_data", "usr_id");
		if ($userResultset->numRows() > 0) {
			while($userRecordset = $userResultset->fetchRow(DB_FETCHMODE_ASSOC)) {
				$userArray[] = $userRecordset["usr_id"];
			}
		}
		return $userArray;
	}
	
	function getUserIDs($groupId) {
		$userArray = null;
		$dbtable = "cal_user_group";
		
		$where =   "cal_user_group.groupId=".$groupId;
					
		$dbHandler = new ilCalDBHandler();
		$userResultset = $dbHandler->select($dbtable, "cal_user_group.userId as userId", $where);
		if ($userResultset->numRows() > 0)
		{
			//$arrayIndex = 0;
			while($userRecordSet = $userResultset->fetchRow(DB_FETCHMODE_ASSOC)) {
				$userArray[$userRecordSet["userId"]] = $userRecordSet["userId"];
				//echo "GH->UserID: ".$userRecordSet["userId"]."<br>";
				//$arrayIndex++;
			}
		}
		
		return $userArray;
	}
}

?>
