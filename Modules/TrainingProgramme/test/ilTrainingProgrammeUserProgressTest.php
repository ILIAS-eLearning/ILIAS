<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("mocks.php");
require_once("./Services/User/classes/class.ilObjUser.php");

/**
 * TestCase for the progress of users at a programme.
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 * @version 1.0.0
 */
class ilTrainingProgrammeUserProgressTest extends PHPUnit_Framework_TestCase {
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
		
		global $ilUser;
		$this->user = $ilUser;
	}
	
	protected function newUser() {
		$user = new ilObjUser();
		$user->create();
		return $user;
	}

	protected function setAllNodesActive() {
		$this->root->setStatus(ilTrainingProgramme::STATUS_ACTIVE);
		$this->node1->setStatus(ilTrainingProgramme::STATUS_ACTIVE);
		$this->node2->setStatus(ilTrainingProgramme::STATUS_ACTIVE);
	}
	
	protected function assignNewUserToRoot() {
		$user = $this->newUser();
		return array($this->root->assignUser($user->getId()), $user);
	}

	public function testInitialProgressActive() {
		$this->setAllNodesActive();
		$tmp = $this->assignNewUserToRoot();
		$ass = $tmp[0];
		$user = $tmp[1];
		
		$root_progresses = $this->root->getProgressesOf($user->getId());
		$this->assertCount(1, $root_progresses);
		$root_progress = $root_progresses[0];
		$this->assertEquals( ilTrainingProgrammeProgress::STATUS_PROGRESS
						   , $root_progress->getStatus());
		$this->assertEquals( $this->root->getPoints()
						   , $root_progress->getAmountOfPoints());
		$this->assertEquals(0, $root_progress->getCurrentAmountOfPoints());
		$this->assertEquals($this->root->getId(), $root_progress->getTrainingProgramme()->getId());
		$this->assertEquals($ass->getId(), $root_progress->getAssignment()->getId());
		$this->assertEquals($user->getId(), $root_progress->getUserId());
		$this->assertEquals($this->user->getId(), $root_progress->getLastChangeBy());
		$this->assertNull($root_progress->getCompletionBy());

		$node1_progresses = $this->node1->getProgressesOf($user->getId());
		$this->assertCount(1, $node1_progresses);
		$node1_progress = $node1_progresses[0];
		$this->assertEquals( ilTrainingProgrammeProgress::STATUS_PROGRESS
						   , $node1_progress->getStatus());
		$this->assertEquals( $this->node1->getPoints()
						   , $node1_progress->getAmountOfPoints());
		$this->assertEquals(0, $node1_progress->getCurrentAmountOfPoints());
		$this->assertEquals($this->node1->getId(), $node1_progress->getTrainingProgramme()->getId());
		$this->assertEquals($ass->getId(), $node1_progress->getAssignment()->getId());
		$this->assertEquals($user->getId(), $node1_progress->getUserId());
		$this->assertEquals($this->user->getId(), $node1_progress->getLastChangeBy());
		$this->assertNull($node1_progress->getCompletionBy());

		$node2_progresses = $this->node2->getProgressesOf($user->getId());
		$this->assertCount(1, $node2_progresses);
		$node2_progress = $node2_progresses[0];
		$this->assertEquals( ilTrainingProgrammeProgress::STATUS_PROGRESS
						   , $node2_progress->getStatus());
		$this->assertEquals( $this->node2->getPoints()
						   , $node2_progress->getAmountOfPoints());
		$this->assertEquals(0, $node2_progress->getCurrentAmountOfPoints());
		$this->assertEquals($this->node2->getId(), $node2_progress->getTrainingProgramme()->getId());
		$this->assertEquals($ass->getId(), $node2_progress->getAssignment()->getId());
		$this->assertEquals($user->getId(), $node2_progress->getUserId());
		$this->assertEquals($this->user->getId(), $node2_progress->getLastChangeBy());
		$this->assertNull($node2_progress->getCompletionBy());
	}
	
	public function testInitialProgressDraft() {
		$this->root->setStatus(ilTrainingProgramme::STATUS_ACTIVE);
		$this->node1->setStatus(ilTrainingProgramme::STATUS_ACTIVE);
		$this->node2->setStatus(ilTrainingProgramme::STATUS_DRAFT);
		
		$tmp = $this->assignNewUserToRoot();
		$user = $tmp[1];
		$ass = $tmp[0];
		
		$root_progresses = $this->root->getProgressesOf($user->getId());
		$root_progress = $root_progresses[0];
		$this->assertEquals( ilTrainingProgrammeProgress::STATUS_PROGRESS
						   , $root_progress->getStatus());
		
		$node1_progresses = $this->node1->getProgressesOf($user->getId());
		$node1_progress = $node1_progresses[0];
		$this->assertEquals( ilTrainingProgrammeProgress::STATUS_PROGRESS
						   , $node1_progress->getStatus());
		
		$node2_progresses = $this->node2->getProgressesOf($user->getId());
		$node2_progress = $node2_progresses[0];
		$this->assertEquals( ilTrainingProgrammeProgress::STATUS_NOT_RELEVANT
						   , $node2_progress->getStatus());
	}
	
	public function testInitialProgressOutdated() {
		$this->root->setStatus(ilTrainingProgramme::STATUS_ACTIVE);
		$this->node1->setStatus(ilTrainingProgramme::STATUS_ACTIVE);
		$this->node2->setStatus(ilTrainingProgramme::STATUS_DRAFT);
		
		$tmp = $this->assignNewUserToRoot();
		$user = $tmp[1];
		$ass = $tmp[0];
		
		$root_progresses = $this->root->getProgressesOf($user->getId());
		$root_progress = $root_progresses[0];
		$this->assertEquals( ilTrainingProgrammeProgress::STATUS_PROGRESS
						   , $root_progress->getStatus());
		
		$node1_progresses = $this->node1->getProgressesOf($user->getId());
		$node1_progress = $node1_progresses[0];
		$this->assertEquals( ilTrainingProgrammeProgress::STATUS_PROGRESS
						   , $node1_progress->getStatus());
		
		$node2_progresses = $this->node2->getProgressesOf($user->getId());
		$node2_progress = $node2_progresses[0];
		$this->assertEquals( ilTrainingProgrammeProgress::STATUS_NOT_RELEVANT
						   , $node2_progress->getStatus());
	}
}
