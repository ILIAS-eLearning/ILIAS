<?php
/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

/**
* Class gevDecentralTrainingCreationSuccessMail
*
* @author Richard Klees <richard.klees@concepts-and-training>
*/

require_once ("./Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingAutoMail.php");

class gevDecentralTrainingCreationSuccessMail extends gevDecentralTrainingAutoMail {
	public function getTemplateCategory() {
		return "SUCCESS";
	}
	
	public function _getDescription() {
		return "nach erfolgreicher Anlage eines dezentralen Trainings";
	}
}

?>