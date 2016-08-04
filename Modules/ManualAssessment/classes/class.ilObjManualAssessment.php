<?php
/**
 * For the purpose of streamlining the grading and learning-process status definition
 * outside of tests, SCORM courses e.t.c. the ManualAssessment is used.
 * It caries a LPStatus, which is set manually.
 *
 * @author Denis Klöpfer <denis.kloepfer@concepts-and-training.de>
 */


require_once 'Services/Object/classes/class.ilObject.php';

class ilObjManualAssessment extends ilObject {
	public function __construct($a_id = 0, $a_call_by_reference = true) {
		global $DIC;
		$this->type = "mass";
		parent::__construct($a_id, $a_call_by_reference);
	}
}