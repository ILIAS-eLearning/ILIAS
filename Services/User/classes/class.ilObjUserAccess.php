<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObjectAccess.php");
require_once('./Services/WebAccessChecker/interfaces/interface.ilWACCheckingClass.php');

/**
 * Class ilObjUserAccess
 *
 *
 * @author        Alex Killing <alex.killing@gmx.de>
 * @author        Fabian Schmid <fs@studer-raimann.ch>
 *
 * @version       $Id$
 *
 * @ingroup       ServicesUser
 */
class ilObjUserAccess extends ilObjectAccess implements ilWACCheckingClass {

	function _getCommands() {
		die();
	}


	function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "") {
		die();
	}


	/**
	 * check whether goto script will succeed
	 */
	function _checkGoto($a_target) {
		return true;
	}


	/**
	 * @param ilWACPath $ilWACPath
	 *
	 * @return bool
	 */
	public function canBeDelivered(ilWACPath $ilWACPath) {
		global $ilUser, $ilSetting;

		preg_match("/usr_(\\d*).*/ui", $ilWACPath->getFileName(), $matches);
		$usr_id = $matches[1];

		// check if own image is viewed
		if ($usr_id == $ilUser->getId()) {
			return true;
		}

		// check if image is in the public profile
		$public_upload = ilObjUser::_lookupPref($usr_id, 'public_upload');
		if ($public_upload != 'y') {
			return false;
		}

		// check the publication status of the profile
		$public_profile = ilObjUser::_lookupPref($usr_id, 'public_profile');

		if ($public_profile == 'g' and $ilSetting->get('enable_global_profiles') and $ilSetting->get('pub_section')) {
			// globally public
			return true;
		} elseif (($public_profile == 'y' or $public_profile == 'g') and $ilUser->getId() != ANONYMOUS_USER_ID && $ilUser->getId() != 0) {
			// public for logged in users
			return true;
		} else {
			// not public
			return false;
		}
	}
}
