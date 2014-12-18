<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* General usefull stuff.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

class gevGeneralUtils {
	/**
	 * Take a list of dates where each entry is a date. Folds the dates
	 * to a string, where consecutive dates are outputted as A. - B.M.Y.
	 * Dates are taken to be starting dates of overnights according to
	 * Accomodationspackage. That is, an end-day is always appended.
	 *
	 * Example:
	 *  input is 21.03, 22.03, 23.03, 25.03, 27.03
	 *  output then is: 21. - 24.03, 25.03 - 26.03, 27.03. - 28.03
	 */
	static public function foldConsecutiveOvernights($a_dates, $delim = ", ") {
		require_once("Services/Calendar/classes/class.ilDateTime.php");
		require_once("Services/Calendar/classes/class.ilDatePresentation.php");

		// will contain arrays with start and end of consecutive sequences
		// of overnights, where 0 => start and 1 => null|end
		$ovs_cons = array();
		// the consecutive sequence we are currently working on
		$ovs_cur = null;
		
		foreach ($a_dates as $ov) {
			// base case after start
			if ($ovs_cur === null) {
				$cp = new ilDate($ov->get(IL_CAL_DATE), IL_CAL_DATE);
				$ovs_cur = array($ov, $cp);
				continue;
			}

			// check last overnight
			$cur_p1 = new ilDate($ovs_cur[1]->get(IL_CAL_DATE), IL_CAL_DATE);
			$cur_p1->increment(ilDateTime::DAY, 1);
			
			if ($ov->get(IL_CAL_DATE) == $cur_p1->get(IL_CAL_DATE)) {
				// the current night directly follows the last night
				// and therefore belongs to the the current sequence
				$ovs_cur[1] = $ov;
			}
			else {
				// the current night does not directly follow the other night
				// and therefore starts a new sequence
				$ovs_cons[] = $ovs_cur;
				$cp = new ilDate($ov->get(IL_CAL_DATE), IL_CAL_DATE);
				$ovs_cur = array($ov, $cp);
			}
		}
		
		// the last sequence needs to be inserted as well.
		if ($ovs_cur !== null) {
			$ovs_cons[] = $ovs_cur;
		}
		
		// adjust the sequences. since convention in Accomodations package is
		// to give the starting day for an overnight, the enddates of the
		// consecutive sequences must be adopted accordingly.
		foreach ($ovs_cons as $key => $ovs) {
			$ovs[1]->increment(ilDateTime::DAY, 1);
			$ov_cons[$key] = ilDatePresentation::formatPeriod($ovs[0], $ovs[1]);
		}
		
		
		return implode($delim, $ov_cons);
	}

	/**
	 * Take a list of dates where each entry is a date. Folds the dates
	 * to a string, where consecutive dates are outputted as A. - B.M.Y.
	 *
	 * Example:
	 *  input is 21.03, 22.03, 23.03, 25.03, 27.03
	 *  output then is: 21. - 23.03, 25.03, 27.03.
	 */
	static public function foldConsecutiveDays($a_dates, $delim = ", ") {
		require_once("Services/Calendar/classes/class.ilDateTime.php");
		require_once("Services/Calendar/classes/class.ilDatePresentation.php");

		// will contain arrays with start and end of consecutive sequences
		// of overnights, where 0 => start and 1 => null|end
		$ovs_cons = array();
		// the consecutive sequence we are currently working on
		$ovs_cur = null;
		
		foreach ($a_dates as $ov) {
			// base case after start
			if ($ovs_cur === null) {
				$cp = new ilDate($ov->get(IL_CAL_DATE), IL_CAL_DATE);
				$ovs_cur = array($ov, $cp);
				continue;
			}

			// check last overnight
			$cur_p1 = new ilDate($ovs_cur[1]->get(IL_CAL_DATE), IL_CAL_DATE);
			$cur_p1->increment(ilDateTime::DAY, 1);
			
			if ($ov->get(IL_CAL_DATE) == $cur_p1->get(IL_CAL_DATE)) {
				// the current night directly follows the last night
				// and therefore belongs to the the current sequence
				$ovs_cur[1] = $ov;
			}
			else {
				// the current night does not directly follow the other night
				// and therefore starts a new sequence
				$ovs_cons[] = $ovs_cur;
				$cp = new ilDate($ov->get(IL_CAL_DATE), IL_CAL_DATE);
				$ovs_cur = array($ov, $cp);
			}
		}
		
		// the last sequence needs to be inserted as well.
		if ($ovs_cur !== null) {
			$ovs_cons[] = $ovs_cur;
		}
		
		// adjust the sequences. since convention in Accomodations package is
		// to give the starting day for an overnight, the enddates of the
		// consecutive sequences must be adopted accordingly.
		foreach ($ovs_cons as $key => $ovs) {
			$ov_cons[$key] = ilDatePresentation::formatPeriod($ovs[0], $ovs[1]);
		}
		
		
		return implode($delim, $ov_cons);
	}


	/**
	 * get a list of user-ids with roles in $a_role_titles
	 * 
	 *
	 * parameter: array $a_role_title 
	 * returns: array usr_id => usr_infos
	*/
	static public function getUsersWithGlobalRole($a_role_titles = array()){
		global $ilDB;
		require_once("Services/AccessControl/classes/class.ilRbacReview.php");
		$rbac_review = new ilRbacReview();
		$roles = $rbac_review->getGlobalRoles();
		$global_roles = array();

		include_once("./Services/User/classes/class.ilUserQuery.php");

			
		$res = $ilDB->query("SELECT obj_id, title FROM object_data "
							   ." WHERE ".$ilDB->in("obj_id", $roles, false, "integer")
							   );
		
		while ($rec = $ilDB->fetchAssoc($res)) {
			$global_roles[$rec["obj_id"]] = $rec["title"];
		}

		$flipped_global_roles = array_flip($global_roles);

		$users = array();

		foreach ($a_role_titles as $role_title){
			//get role-id of role
			if(! array_key_exists($role_title, $flipped_global_roles)){
				throw new Exception("no global role '$role_title'");
			}
			$role_id = $flipped_global_roles[$role_title];


			$usr_data = ilUserQuery::getUserListData(
				'login', //			ilUtil::stripSlashes($this->getOrderField()),
				'asc' , //ilUtil::stripSlashes($this->getOrderDirection()),
				0, //ilUtil::stripSlashes($this->getOffset()),
				0, //ilUtil::stripSlashes($this->getLimit()),
				'',
				'',
				null,
				false,
				false,
				0,
				$role_id
			);

			foreach ($usr_data['set'] as $usr) {
				//$users[$usr['login']] = $usr;
				$users[$usr['usr_id']] = $usr;
			}

		}

		return $users;
	}


}

?>