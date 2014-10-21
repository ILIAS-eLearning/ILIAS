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
			$ovs[1]->increment(ilDateTime::DAY, 1);
			$ov_cons[$key] = ilDatePresentation::formatPeriod($ovs[0], $ovs[1]);
		}
		
		
		return implode($delim, $ov_cons);
	}
}

?>