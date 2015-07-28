<?php
/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

/**
* Class gevDecentralTrainingCreationFailureMail
*
* @author Richard Klees <richard.klees@concepts-and-training>
*/


require_once ("./Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingAutoMail.php");

class gevDecentralTrainingCreationFailureMail extends gevDecentralTrainingAutoMail {
	public function getTemplateCategory() {
		return "FAILURE";
	}
	
	public function _getDescription() {
		return "nach erfolgreicher Anlage eines dezentralen Trainings";
	}
	
	public function getBCC($a_recipient) {
		return array("goa-test@cat06.de");
	}
}

?>