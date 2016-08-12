<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("./Services/Object/classes/class.ilObjectLP.php");

class ilManualAssessmentLP extends ilObjectLP {
	/**
	 * @var ilObjStudyProgramme|null
	 */
	protected $members_ids = null;
	
	public function getDefaultMode() {
		return ilLPObjSettings::LP_MODE_MANUAL_ASSESSMENT;
	}
	
	public function getValidModes() {
		return array
			( ilLPObjSettings::LP_MODE_MANUAL_ASSESSMENT
			);
	}
	
	public function getMembers($a_search = true) {
		if($this->members_ids === null ) {
			global $DIC;
			require_once("Modules/ManualAssessment/classes/class.ilObjManualAssessment.php");
			$mass = new ilObjManualAssessment($this->obj_id, false);
			$this->members_ids = $mass->loadMembers()->membersIds();
		}
		return $this->members_ids;
	}
}

?>