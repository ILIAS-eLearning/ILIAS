<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("mocks.php");
require_once("./Services/User/classes/class.ilObjUser.php");

/**
 * TestCase for the assignment of users to a programme.
 *
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 * @version 1.0.0
 */
class ilTrainingProgrammeProgressCalculationTest extends PHPUnit_Framework_TestCase {
	protected $backupGlobals = FALSE;

	protected function setUp() {
		PHPUnit_Framework_Error_Deprecated::$enabled = FALSE;

		require_once("./Modules/TrainingProgramme/classes/class.ilObjTrainingProgramme.php");

		include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		ilUnitUtil::performInitialisation();
		
		$this->root = ilObjTrainingProgramme::createInstance();
		$this->root->putInTree(ROOT_FOLDER_ID);
		$this->root->object_factory = new ilObjectFactoryWrapperMock();
		$this->root->setStatus(ilTrainingProgramme::STATUS_ACTIVE)
				   ->update();
		
		global $tree;
		$this->tree = $tree;
		
		global $ilUser;
		$this->user = $ilUser;
	}
	
	protected function newUser() {
		$user = new ilObjUser();
		$user->create();
		return $user;
	}
	
	protected function setUpNodes($top, $data) {
		if (!array_key_exists("points", $data)) {
			throw new Exception("Expected to find 'points' in data array.");
		}
		
		$top->setPoints($data["points"]);
		
		foreach ($data as $node_name => $data2) {
			if ($node_name == "points") {
				continue;
			}
			if (isset($this->$node_name)) {
				throw new Exception("Node $node_name already exists.");
			}
			
			if ($data2 == null) {
				$this->$node_name = new ilTrainingProgrammeLeafMock();
				$top->addLeaf($this->$node_name);
			}
			else {
				$this->$node_name = ilObjTrainingProgramme::createInstance();
				$this->$node_name->object_factory = new ilObjectFactoryWrapperMock();
				$this->root->setStatus(ilTrainingProgramme::STATUS_ACTIVE)
						   ->update();
				$top->addNode($this->$node_name);
				$this->setUpNodes($this->$node_name, $data2);
			}
		}
	}

	public function testProgress1() {
		$this->setUpNodes($this->root, array( "points" => 200
		, "node1" => array( "points" => 100 
				, "leaf11" => null
				, "leaf12" => null
				)
		, "node2" => array( "points" => 100 
				, "leaf21" => null
				, "leaf22" => null
				)
		));
		
		$user = $this->newUser();
		$user_id = $user->getId();
		$ass = $this->root->assignUser($user->getId());
		$ass_id = $ass->getId();
		
		$this->leaf11->markCompletedFor($user_id);
		$this->leaf21->markCompletedFor($user_id);
		
		$ass->calculateProgress();
		
		$this->assertEquals( ilTrainingProgrammeProgress::STATUS_COMPLETED
						   , $this->root->getProgressForAssignment($ass_id)->getStatus());
		$this->assertEquals( ilTrainingProgrammeProgress::STATUS_COMPLETED
						   , $this->node1->getProgressForAssignment($ass_id)->getStatus());
		$this->assertEquals( ilTrainingProgrammeProgress::STATUS_COMPLETED
						   , $this->node2->getProgressForAssignment($ass_id)->getStatus());
	}

	public function testProgress2() {
		$this->setUpNodes($this->root, array( "points" => 200
		, "node1" => array( "points" => 100 
				, "leaf11" => null
				, "leaf12" => null
				)
		, "node2" => array( "points" => 100 
				, "leaf21" => null
				, "leaf22" => null
				)
		));
		
		$user = $this->newUser();
		$user_id = $user->getId();
		$ass = $this->root->assignUser($user->getId());
		$ass_id = $ass->getId();
		
		$this->node1->getProgressForAssignment($ass_id)
					->markAccredited($this->user->getId());
		$this->leaf21->markCompletedFor($user_id);
		
		$ass->calculateProgress();
		
		$this->assertEquals( ilTrainingProgrammeProgress::STATUS_COMPLETED
						   , $this->root->getProgressForAssignment($ass_id)->getStatus());
		$this->assertEquals( ilTrainingProgrammeProgress::STATUS_ACCREDITED
						   , $this->node1->getProgressForAssignment($ass_id)->getStatus());
		$this->assertEquals( ilTrainingProgrammeProgress::STATUS_COMPLETED
						   , $this->node2->getProgressForAssignment($ass_id)->getStatus());
	}
	
	public function testProgress3() {
		$this->setUpNodes($this->root, array( "points" => 200
		, "node1" => array( "points" => 100 
				, "leaf11" => null
				, "leaf12" => null
				)
		, "node2" => array( "points" => 100 
				, "leaf21" => null
				, "leaf22" => null
				)
		));
		
		$user = $this->newUser();
		$user_id = $user->getId();
		$ass = $this->root->assignUser($user->getId());
		$ass_id = $ass->getId();
		
		$this->node1->getProgressForAssignment($ass_id)
					->markNotRelevant($this->user->getId());
		$this->root->getProgressForAssignment($ass_id)
				   ->setRequiredAmountOfPoints(100, $this->user->getId());
		$this->leaf21->markCompletedFor($user_id);
		
		$ass->calculateProgress();
		
		$this->assertEquals( ilTrainingProgrammeProgress::STATUS_COMPLETED
						   , $this->root->getProgressForAssignment($ass_id)->getStatus());
		$this->assertEquals( ilTrainingProgrammeProgress::STATUS_NOT_RELEVANT
						   , $this->node1->getProgressForAssignment($ass_id)->getStatus());
		$this->assertEquals( ilTrainingProgrammeProgress::STATUS_COMPLETED
						   , $this->node2->getProgressForAssignment($ass_id)->getStatus());
	}
	
	public function testProgress4() {
		$this->setUpNodes($this->root, array( "points" => 200
		, "node1" => array( "points" => 100 
				, "leaf11" => null
				, "leaf12" => null
				)
		, "node2" => array( "points" => 100 
				, "leaf21" => null
				, "leaf22" => null
				)
		));
		
		$user = $this->newUser();
		$user_id = $user->getId();
		$ass = $this->root->assignUser($user->getId());
		$ass_id = $ass->getId();
		
		$this->node1->getProgressForAssignment($ass_id)
					->markNotRelevant($this->user->getId());
		$this->leaf11->markCompletedFor($user_id);
		$this->leaf21->markCompletedFor($user_id);
		
		$ass->calculateProgress();
		
		$this->assertEquals( ilTrainingProgrammeProgress::STATUS_IN_PROGRESS
						   , $this->root->getProgressForAssignment($ass_id)->getStatus());
		$this->assertEquals( ilTrainingProgrammeProgress::STATUS_NOT_RELEVANT
						   , $this->node1->getProgressForAssignment($ass_id)->getStatus());
		$this->assertEquals( ilTrainingProgrammeProgress::STATUS_COMPLETED
						   , $this->node2->getProgressForAssignment($ass_id)->getStatus());
	}
}
