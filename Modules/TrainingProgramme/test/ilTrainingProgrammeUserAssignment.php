<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("mocks.php");

/**
 * TestCase for the assignment of users to a programme.
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 * @version 1.0.0
 */
class ilTrainingProgrammeUserAssignment extends PHPUnit_Framework_TestCase {
	protected $backupGlobals = FALSE;

	protected function setUp() {
		PHPUnit_Framework_Error_Deprecated::$enabled = FALSE;

		require_once("./Modules/TrainingProgramme/classes/class.ilObjTrainingProgramme.php");

		include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		ilUnitUtil::performInitialisation();
		
		$this->root = ilObjTrainingProgramme::createInstance();
		$this->root->putInTree(ROOT_FOLDER_ID);
		$this->root->object_factory = new ilObjectFactoryWrapperMock();
		
		$this->node1 = ilObjTrainingProgramme::createInstance();
		$this->node2 = ilObjTrainingProgramme::createInstance();
		
		$this->leaf1 = new ilTrainingProgrammeLeafMock();
		$this->leaf2 = new ilTrainingProgrammeLeafMock();
		
		$this->root->addNode($node1);
		$this->root->addNode($node2);
		$this->node1->addLeaf($this->leaf1);
		$this->node2->addLeaf($this->leaf2);
		
		global $tree;
		$this->tree = $tree;
	}
	
	
}
