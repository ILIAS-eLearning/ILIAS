<?php

require_once("./Services/User/classes/class.ilUDFClaimingPlugin.php");

/**
 * User data for the generali
 * 
 * @author: Richard Klees
 * @version: $Id$
 */

class ilGEVUserDataPlugin extends ilUDFClaimingPlugin {
	public function getPluginName() {
		return "GEVUserData";
	}
	
	public function checkPermission($a_user_id, $a_context_type, $a_context_id, $a_action_id, $a_action_sub_id) {
		return false;
	}
}

?>