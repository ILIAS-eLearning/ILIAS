<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("mocks.php");
require_once("./Services/User/classes/class.ilObjUser.php");

/**
 * TestCase for the assignment of users to a programme.
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 * @version 1.0.0
 */
class ilTrainingProgrammeUserAssignmentTest extends PHPUnit_Framework_TestCase {
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
		
		$this->root->addNode($this->node1);
		$this->root->addNode($this->node2);
		$this->node1->addLeaf($this->leaf1);
		$this->node2->addLeaf($this->leaf2);
		
		global $tree;
		$this->tree = $tree;
	}
	
	protected function newUser() {
		$user = new ilObjUser();
		$user->create();
		return $user;
	}
	
	/**
	 * @expectedException ilException
	 */ 
	public function testNoAssignmentWhenDraft() {
		$user = $this->newUser();
		$this->assertEquals(ilTrainingProgramme::STATUS_DRAFT, $this->root->getStatus());
		$this->root->assignUser($user->getId());
	}
	
	/**
	 * @expectedException ilException
	 */
	public function testNoAssignementWhenOutdated() {
		$user = $this->newUser();
		
		$this->root->setStatus(ilTrainingProgramme::STATUS_OUTDATED);
		$this->assertEquals(ilTrainingProgramme::STATUS_OUTDATED, $this->root->getStatus());
		$this->root->assignUser($user->getId());
	}
	
	/**
	 * @expectedException ilException
	 */
	public function testNoAssignementWhenNotCreated() {
		$user = $this->newUser();
		$prg = new ilObjTrainingProgramme();
		$prg->assignUser($user->getId());
	}
	
	public function testHasAssignmentOf() {
		$user1 = $this->newUser();
		$user2 = $this->newUser();
		
		$this->root->setStatus(ilTrainingProgramme::STATUS_ACTIVE);
		
		$this->root->assignUser($user1->getId());
		$this->assertTrue($this->root->hasAssignmentOf($user1->getId()));
		$this->assertTrue($this->node1->hasAssignmentOf($user1->getId()));
		$this->assertTrue($this->node2->hasAssignmentOf($user1->getId()));
		
		$this->assertFalse($this->root->hasAssignmentOf($user2->getId()));
		$this->assertFalse($this->node1->hasAssignmentOf($user2->getId()));
		$this->assertFalse($this->node2->hasAssignmentOf($user2->getId()));
	}
	
	public function testGetAmountOfAssignments() {
		$user1 = $this->newUser();
		$user2 = $this->newUser();
		$user3 = $this->newUser();
		$user4 = $this->newUser();
		
		$this->root->setStatus(ilTrainingProgramme::STATUS_ACTIVE);
		
		$this->assertEquals(0, $this->root->getAmountOfAssignmentsOf($user1->getId()));
		
		$this->root->assignUser($user2->getId());
		$this->assertEquals(1, $this->root->getAmountOfAssignmentsOf($user2->getId()));
		$this->assertEquals(1, $this->node1->getAmountOfAssignmentsOf($user2->getId()));
		$this->assertEquals(1, $this->node2->getAmountOfAssignmentsOf($user2->getId()));
	
		$this->root->assignUser($user3->getId());
		$this->root->assignUser($user3->getId());
		$this->assertEquals(2, $this->root->getAmountOfAssignmentsOf($user2->getId()));
		$this->assertEquals(2, $this->node1->getAmountOfAssignmentsOf($user2->getId()));
		$this->assertEquals(2, $this->node2->getAmountOfAssignmentsOf($user2->getId()));

		$this->root->assignUser($user4->getId());
		$this->node1->assignUser($user4->getId());
		$this->assertEquals(1, $this->root->getAmountOfAssignmentsOf($user2->getId()));
		$this->assertEquals(2, $this->node1->getAmountOfAssignmentsOf($user2->getId()));
		$this->assertEquals(1, $this->node2->getAmountOfAssignmentsOf($user2->getId()));
	}
	
	public function testGetAssignmentsOf() {
		$user1 = $this->newUser();
		$user2 = $this->newUser();
		
		$this->root->setStatus(ilTrainingProgramme::STATUS_ACTIVE);
		$this->node1->setStatus(ilTrainingProgramme::STATUS_ACTIVE);
		
		$this->assertEquals(0, count($this->root->getAssignmentsOf($user1->getId())));
		$this->assertEquals(0, count($this->node1->getAssignmentsOf($user1->getId())));
		$this->assertEquals(0, count($this->node2->getAssignmentsOf($user1->getId())));

		$this->root->assignUser($user2->getId());
		$this->node1->assignUser($user2->getId());
		
		$root_ass = $this->root->getAssignmentsOf($user2->getId());
		$node1_ass = $this->node1->getAssignmentsOf($user2->getId());
		$node2_ass = $this->node2->getAssignmentsOf($user2->getId());
		
		$this->assertEquals(1, count($root_ass));
		$this->assertEquals(2, count($node1_ass));
		$this->assertEquals(1, count($node2_ass));
		
		$this->assertEquals($this->root->getId(), $root_ass[0]->getTrainingProgramme()->getId());
		$this->assertEquals($this->root->getId(), $node2_ass[0]->getTrainingProgramme()->getId());
		
		$node1_ass_prg_ids = array_map(function($ass) {
			return $ass->getTrainingProgramme()->getId();
		}, $node1_ass);
		$this->asserContains($node1_ass_prg_ids, $this->root->getId());
		$this->assertContains($node1_ass_prg_ids, $this->node1->getId());
	}
	
	/**
	 * @expectedException ilException
	 */
	public function testRemoveOnRootNodeOnly1() {
		$user = $this->newUser();
		
		$this->root->setStatus(ilTrainingProgramme::STATUS_ACTIVE);
		
		$ass1 = $this->root->assignUser($user->getId());
		$this->node1->removeAssignment($ass1);
	}

	/**
	 * @expectedException ilException
	 */
	public function testRemoveOnRootNodeOnly2() {
		$user = $this->newUser();
		
		$this->node1->setStatus(ilTrainingProgramme::STATUS_ACTIVE);
		
		$ass1 = $this->node1->assignUser($user->getId());
		$this->root->removeAssignment($ass1);
	}
	
	public function testRemoveAssignment1() {
		$user = $this->newUser();
		
		$this->root->setStatus(ilTrainingProgramme::STATUS_ACTIVE);
		
		$ass1 = $this->root->assignUser($user->getId());
		$this->root->removeAssignment($ass1);
		$this->assertFalse($this->root->hasAssignmentOf($user->getId()));
	}
	
	public function testRemoveAssignment2() {
		$user = $this->newUser();
		
		$this->root->setStatus(ilTrainingProgramme::STATUS_ACTIVE);
		
		$ass1 = $this->root->assignUser($user->getId());
		$ass1->remove();
		$this->assertFalse($this->root->hasAssignmentOf($user->getId()));
	}
	
	public function testGetAssignments() {
		$user1 = $this->newUser();
		$user2 = $this->newUser();
		
		$this->root->setStatus(ilTrainingProgramme::STATUS_ACTIVE);
		$this->node1->setStatus(ilTrainingProgramme::STATUS_ACTIVE);
		
		$ass1 = $this->root->assignUser($user1->getId());
		$ass2 = $this->node1->assignUser($user2->getId());
		
		$asses = $this->node1->getAssignments();
		$ass_ids = array_map(function($ass) {
			return $ass->getId();
		});
		$this->assertContains($ass1->getId(), $asses);
		$this->assertContains($ass2->getId(), $asses);
	}
}
