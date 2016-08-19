<?php

/* Copyright (c) 2016 Denis Klöpfer <denis.kloepfer@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("./Services/Object/classes/class.ilObjectLP.php");

class ilManualAssessmentLP extends ilObjectLP {
 
	protected $members_ids = null;
	
	/**
	 * @inheritdoc
	 */
	public function getDefaultMode() {
		return ilLPObjSettings::LP_MODE_MANUAL_ASSESSMENT;
	}
	
	/**
	 * @inheritdoc
	 */
	public function getValidModes() {
		return array(ilLPObjSettings::LP_MODE_MANUAL_ASSESSMENT
					,ilLPObjSettings::LP_MODE_DEACTIVATED);
	}
	
	/**
	 * Get an array of member ids participating in the obnject coresponding to this. 
	 *
	 * @return int|string[]
	 */
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