<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
* Resolves WBD-Errors on user/course update
*
* @author	Nils Haagen <nhaagen@concepts-and-training.de>
* @version	$Id$
*
*
*/
class ilWBDDataAppEventListener {


	public static function handleEvent($a_component, $a_event, $a_parameter) {
		// TODO: currently disabled because of incomplete db update.
		return;
		require_once("./Services/WBDData/classes/class.wbdErrorLog.php");
		$wbderrlog = new wbdErrorLog();

		if($a_component == 'Services/User' && $a_event == 'afterUpdate') {
			$id =  $a_parameter['user_obj']->getId();
			$wbderrlog->resolveWBDErrorsForUser($id);
		}

		if($a_component == 'Modules/Course' && $a_event == 'update') {
			$id =  $a_parameter['object']->getId();
			$wbderrlog->resolveWBDErrorsForCourse($id);
		}

	}

}
?>