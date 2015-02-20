<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/**
 * Mock classes for tests.
 *
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 * @version 1.0.0
 */

require_once("Services/Tracking/classes/class.ilLPStatus.php");

/**
 * Mock for leaf in program.
 */
require_once("Modules/TrainingProgramme/classes/interfaces/interface.ilTrainingProgrammeLeaf.php");
require_once("Services/Object/classes/class.ilObject2.php");

class ilTrainingProgrammeLeafMock extends ilObject2 implements ilTrainingProgrammeLeaf {
	public function __construct($a_id = 0, $a_call_by_reference = true) {
		parent::__construct($a_id, $a_call_by_reference);
		if ($a_id == 0) {
			parent::create();
		}
	}
	
	// from ilObject2
	public function initType() {
		$this->type = "mock";
	}
	
	// from ilTrainingProgrammeLeaf
	public function getParentId() {
		global $tree;
		if (!$tree->isInTree($this->getRefId())) {
			return null;
		}
		
		$nd = $tree->getParentNodeData($this->getRefId());
		return $nd["obj_id"];
	}
	
	// Mark this leaf as completed for a user.
	public function markCompletedFor($a_user_id) {
		global $ilAppEventHandler;

		$ilAppEventHandler->raise("Services/Tracking", "updateStatus", array(
			"obj_id" => $this->getId(),
			"usr_id" => $a_user_id,
			"status" => ilLPStatus::LP_STATUS_COMPLETED_NUM,
			"percentage" => 100
			));
	}
}

/**
 * Mock for object factory
 */
require_once("Modules/TrainingProgramme/classes/class.ilObjectFactoryWrapper.php");

class ilObjectFactoryWrapperMock extends ilObjectFactoryWrapper {
	public function getInstanceByRefId($a_ref_id, $stop_on_error = true) {
		return new ilTrainingProgrammeLeafMock($a_ref_id);
	}
}