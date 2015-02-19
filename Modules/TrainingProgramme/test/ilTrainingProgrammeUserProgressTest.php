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
		$this->assertEquals( ilTrainingProgrammeProgress::STATUS_IN_PROGRESS
						   , $root_progress->getStatus());
		$this->assertEquals( $this->root->getPoints()
						   , $root_progress->getAmountOfPoints());
		$this->assertEquals(0, $root_progress->getCurrentAmountOfPoints());
		$this->assertEquals($this->root->getId(), $root_progress->getTrainingProgramme()->getId());
		$this->assertEquals($ass->getId(), $root_progress->getAssignment()->getId());
		$this->assertEquals($user->getId(), $root_progress->getUserId());
		$this->assertNull($root_progress->getLastChangeBy());
		$this->assertNull($root_progress->getCompletionBy());

		$node1_progresses = $this->node1->getProgressesOf($user->getId());
		$this->assertCount(1, $node1_progresses);
		$node1_progress = $node1_progresses[0];
		$this->assertEquals( ilTrainingProgrammeProgress::STATUS_IN_PROGRESS
						   , $node1_progress->getStatus());
		$this->assertEquals( $this->node1->getPoints()
						   , $node1_progress->getAmountOfPoints());
		$this->assertEquals(0, $node1_progress->getCurrentAmountOfPoints());
		$this->assertEquals($this->node1->getId(), $node1_progress->getTrainingProgramme()->getId());
		$this->assertEquals($ass->getId(), $node1_progress->getAssignment()->getId());
		$this->assertEquals($user->getId(), $node1_progress->getUserId());
		$this->assertNull($node1_progress->getLastChangeBy());
		$this->assertNull($node1_progress->getCompletionBy());

		$node2_progresses = $this->node2->getProgressesOf($user->getId());
		$this->assertCount(1, $node2_progresses);
		$node2_progress = $node2_progresses[0];
		$this->assertEquals( ilTrainingProgrammeProgress::STATUS_IN_PROGRESS
						   , $node2_progress->getStatus());
		$this->assertEquals( $this->node2->getPoints()
						   , $node2_progress->getAmountOfPoints());
		$this->assertEquals(0, $node2_progress->getCurrentAmountOfPoints());
		$this->assertEquals($this->node2->getId(), $node2_progress->getTrainingProgramme()->getId());
		$this->assertEquals($ass->getId(), $node2_progress->getAssignment()->getId());
		$this->assertEquals($user->getId(), $node2_progress->getUserId());
		$this->assertNull($node2_progress->getLastChangeBy());
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
		$this->assertEquals( ilTrainingProgrammeProgress::STATUS_IN_PROGRESS
						   , $root_progress->getStatus());
		
		$node1_progresses = $this->node1->getProgressesOf($user->getId());
		$node1_progress = $node1_progresses[0];
		$this->assertEquals( ilTrainingProgrammeProgress::STATUS_IN_PROGRESS
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
		$this->assertEquals( ilTrainingProgrammeProgress::STATUS_IN_PROGRESS
						   , $root_progress->getStatus());
		
		$node1_progresses = $this->node1->getProgressesOf($user->getId());
		$node1_progress = $node1_progresses[0];
		$this->assertEquals( ilTrainingProgrammeProgress::STATUS_IN_PROGRESS
						   , $node1_progress->getStatus());
		
		$node2_progresses = $this->node2->getProgressesOf($user->getId());
		$node2_progress = $node2_progresses[0];
		$this->assertEquals( ilTrainingProgrammeProgress::STATUS_NOT_RELEVANT
						   , $node2_progress->getStatus());
	}
	
	public function testUserSelection() {
		$this->setAllNodesActive();
		$this->assignNewUserToRoot();
		$tmp = $this->assignNewUserToRoot();
		$ass = $tmp[0];
		$user = $tmp[1];
		
		$root_progresses = $this->root->getProgressesOf($user->getId());
		$this->assertCount(1, $root_progresses);
	}

	public function testMarkAccredited() {
		$this->setAllNodesActive();
		$tmp = $this->assignNewUserToRoot();
		$ass = $tmp[0];
		$user = $tmp[1];
		
		$user2 = $this->newUser();
		$USER_ID = $user2->getId();
		
		$root_progress = array_shift($this->root->getProgressesOf($user->getId()));
		$node1_progress = array_shift($this->node1->getProgressesOf($user->getId()));
		$node2_progress = array_shift($this->node2->getProgressesOf($user->getId()));
		$ts_before_change = $node2_progress->getLastChange()->get(IL_CAL_DATETIME);
		$node2_progress->markAccredited($USER_ID);
		$ts_after_change = $node2_progress->getLastChange()->get(IL_CAL_DATETIME);
		$this->assertEquals(ilTrainingProgrammeProgress::STATUS_IN_PROGRESS, $root_progress->getStatus());
		$this->assertEquals(ilTrainingProgrammeProgress::STATUS_IN_PROGRESS, $node1_progress->getStatus());
		$this->assertEquals(ilTrainingProgrammeProgress::STATUS_ACCREDITED, $node2_progress->getStatus());
		$this->assertEquals($USER_ID, $node2_progress->getCompletionBy());
		$this->assertLessThanOrEqual($ts_before_change, $ts_after_change);
	}

	public function testMarkNotRelevant() {
		$this->setAllNodesActive();
		$tmp = $this->assignNewUserToRoot();
		$ass = $tmp[0];
		$user = $tmp[1];
		
		$user2 = $this->newUser();
		$USER_ID = $user2->getId();
		
		$root_progress = array_shift($this->root->getProgressesOf($user->getId()));
		$node1_progress = array_shift($this->node1->getProgressesOf($user->getId()));
		$node2_progress = array_shift($this->node2->getProgressesOf($user->getId()));
		$ts_before_change = $node2_progress->getLastChange()->get(IL_CAL_DATETIME);
		$node2_progress->markNotRelevant($USER_ID);
		$ts_after_change = $node2_progress->getLastChange()->get(IL_CAL_DATETIME);
		$this->assertEquals(ilTrainingProgrammeProgress::STATUS_IN_PROGRESS, $root_progress->getStatus());
		$this->assertEquals(ilTrainingProgrammeProgress::STATUS_IN_PROGRESS, $node1_progress->getStatus());
		$this->assertEquals(ilTrainingProgrammeProgress::STATUS_NOT_RELEVANT, $node2_progress->getStatus());
		$this->assertEquals($USER_ID, $node2_progress->getCompletionBy());
		$this->assertLessThanOrEqual($ts_before_change, $ts_after_change);
	}
	
	// Neues Moduls: Wird dem Studierenden-Studierenden inkl. Kurse, Punkte als "Nicht relevant" hinzugefügt.
	public function testNewNodesAreNotRelevant() {
		$this->setAllNodesActive();
		$tmp = $this->assignNewUserToRoot();
		$ass = $tmp[0];
		$user = $tmp[1];

		$node3 = ilObjTrainingProgramme::createInstance();
		$this->root->addNode($node3);
		
		$node3_progress = array_shift($node3->getProgressesOf($user->getId()));
		$this->assertNotNull($node3_progress);
		$this->assertEquals(ilTrainingProgrammeProgress::STATUS_NOT_RELEVANT, $node3_progress->getStatus());
	}
	
	public function testIndividualRequiredPoints() {
		$this->setAllNodesActive();
		$tmp = $this->assignNewUserToRoot();
		$ass1 = $tmp[0];
		$user1 = $tmp[1];
		
		
		$NEW_AMOUNT_OF_POINTS_1 = 205;
		$this->assertNotEquals($NEW_AMOUNT_OF_POINTS_1, ilTrainingProgramme::DEFAULT_POINTS);
	
		$node2_progress1 = array_shift($this->node2->getProgressesOf($user1->getId()));
		$node2_progress1->setRequiredAmountOfPoints($NEW_AMOUNT_OF_POINTS_1, $this->user->getId());

		$this->assertEquals($NEW_AMOUNT_OF_POINTS_1, $node2_progress1->getAmountOfPoints());
	}
	
	public function testMaximimPossibleAmountOfPoints1() {
		$this->setAllNodesActive();
		$tmp = $this->assignNewUserToRoot();
		$ass = $tmp[0];
		$user = $tmp[1];
		
		$root_progress = array_shift($this->root->getProgressesOf($user->getId()));
		$node1_progress = array_shift($this->node1->getProgressesOf($user->getId()));
		$node2_progress = array_shift($this->node2->getProgressesOf($user->getId()));
	
		$this->assertEquals(2 * ilTrainingProgramme::DEFAULT_POINTS, $root_progress->getMaximumPossibleAmountOfPoints());
		$this->assertEquals(ilTrainingProgramme::DEFAULT_POINTS, $node1_progress->getMaximumPossibleAmountOfPoints());
		$this->assertEquals(ilTrainingProgramme::DEFAULT_POINTS, $node2_progress->getMaximumPossibleAmountOfPoints());
	}
	
	public function testMaximimPossibleAmountOfPoints2() {
		$this->setAllNodesActive();
		$tmp = $this->assignNewUserToRoot();
		$ass = $tmp[0];
		$user = $tmp[1];
		
		$root_progress = array_shift($this->root->getProgressesOf($user->getId()));
		$node1_progress = array_shift($this->node1->getProgressesOf($user->getId()));
		$node2_progress = array_shift($this->node2->getProgressesOf($user->getId()));
	
		$this->assertEquals(2 * ilTrainingProgramme::DEFAULT_POINTS, $root_progress->getMaximumPossibleAmountOfPoints());
		$this->assertEquals(ilTrainingProgramme::DEFAULT_POINTS, $node1_progress->getMaximumPossibleAmountOfPoints());
		$this->assertEquals(ilTrainingProgramme::DEFAULT_POINTS, $node2_progress->getMaximumPossibleAmountOfPoints());
	}
	
	public function testCanBeCompleted1() {
		$this->setAllNodesActive();
		$tmp = $this->assignNewUserToRoot();
		$ass = $tmp[0];
		$user = $tmp[1];
		
		$root_progress = array_shift($this->root->getProgressesOf($user->getId()));
		$node1_progress = array_shift($this->node1->getProgressesOf($user->getId()));
		$node2_progress = array_shift($this->node2->getProgressesOf($user->getId()));
	
		$this->assertTrue($root_progress->canBeCompleted());
		$this->assertTrue($node1_progress->canBeCompleted());
		$this->assertTrue($node2_progress->canBeCompleted());
	}
	
	public function testCanBeCompleted2() {
		$NEW_AMOUNT_OF_POINTS = 3003;
		$this->assertGreaterThan(ilTrainingProgramme::DEFAULT_POINTS, $NEW_AMOUNT_OF_POINTS);
		
		$this->setAllNodesActive();
		$this->root->setPoints($NEW_AMOUNT_OF_POINTS)
				   ->update();
		$tmp = $this->assignNewUserToRoot();
		$ass = $tmp[0];
		$user = $tmp[1];
		
		$this->assertLessThan($NEW_AMOUNT_OF_POINTS, $this->node1->getPoints() + $this->node2->getPoints());
		
		$root_progress = array_shift($this->root->getProgressesOf($user->getId()));
		$this->assertFalse($root_progress->canBeCompleted());
	}
	
	public function testUserDeletionDeletesAssignments() {
		$this->setAllNodesActive();
		$tmp = $this->assignNewUserToRoot();
		$ass = $tmp[0];
		$user = $tmp[1];
		
		$user->delete();
		
		$root_progresses = $this->root->getProgressesOf($user->getId());
		$this->assertCount(0, $root_progresses);
		$node1_progresses = $this->root->getProgressesOf($user->getId());
		$this->assertCount(0, $node1_progresses);
		$node2_progresses = $this->root->getProgressesOf($user->getId());
		$this->assertCount(0, $node2_progresses);
	}
	
	// - Änderungen von Punkten bei bestehenden qua-Objekten werden nicht direkt übernommen
	public function testNoImplicitPointUpdate() {
		$this->setAllNodesActive();
		$tmp = $this->assignNewUserToRoot();
		$ass = $tmp[0];
		$user = $tmp[1];
		
		$NEW_AMOUNT_OF_POINTS = 201;
		$this->assertNotEquals($NEW_AMOUNT_OF_POINTS, ilTrainingProgramme::DEFAULT_POINTS);
		
		$this->root->setPoints($NEW_AMOUNT_OF_POINTS)
				   ->update();
		
		$root_progress = array_shift($this->root->getProgressesOf($user->getId()));
		$this->assertEquals(ilTrainingProgramme::DEFAULT_POINTS, $root_progress->getAmountOfPoints());
	}

	// Änderungen von Punkten bei bestehenden qua-Objekten werden nicht direkt übernommen,
	//  sondern dann bei bewusster Aktualisierung.
	public function testExplicitPointUpdate1() {
		$this->setAllNodesActive();
		$tmp = $this->assignNewUserToRoot();
		$ass = $tmp[0];
		$user = $tmp[1];
		
		$NEW_AMOUNT_OF_POINTS = 202;
		$this->assertNotEquals($NEW_AMOUNT_OF_POINTS, ilTrainingProgramme::DEFAULT_POINTS);
		
		$this->root->setPoints($NEW_AMOUNT_OF_POINTS)
				   ->update();
		
		$ass->updateFromProgram();
		$root_progress = array_shift($this->root->getProgressesOf($user->getId()));
		$this->assertEquals($NEW_AMOUNT_OF_POINTS, $root_progress->getAmountOfPoints());
	}

	// Änderungen von Punkten bei bestehenden qua-Objekten werden nicht direkt übernommen,
	// sondern dann bei bewusster Aktualisierung.
	// Similar to testExplicitPointUpdate1, but order of calls differs.
	public function testExplicitPointUpdate2() {
		$this->setAllNodesActive();
		$tmp = $this->assignNewUserToRoot();
		$ass = $tmp[0];
		$user = $tmp[1];
		
		$NEW_AMOUNT_OF_POINTS = 203;
		$this->assertNotEquals($NEW_AMOUNT_OF_POINTS, ilTrainingProgramme::DEFAULT_POINTS);
		
		$this->root->setPoints($NEW_AMOUNT_OF_POINTS)
				   ->update();
		
		$root_progress = array_shift($this->root->getProgressesOf($user->getId()));
		$ass->updateFromProgram();
		$this->assertEquals($NEW_AMOUNT_OF_POINTS, $root_progress->getAmountOfPoints());
	}

	// Änderungen von Punkten bei bestehenden qua-Objekten werden nicht direkt übernommen,
	//  sondern dann bei bewusster Aktualisierung (sofern nicht ein darüberliegenden 
	// Knotenpunkt manuell angepasst worden ist)
	public function testNoUpdateOnModifiedNodes() {
		$this->setAllNodesActive();
		$tmp = $this->assignNewUserToRoot();
		$ass1 = $tmp[0];
		$user1 = $tmp[1];
		
		$tmp = $this->assignNewUserToRoot();
		$ass2 = $tmp[0];
		$user2 = $tmp[1];
		
		$NEW_AMOUNT_OF_POINTS_1 = 205;
		$this->assertNotEquals($NEW_AMOUNT_OF_POINTS_1, ilTrainingProgramme::DEFAULT_POINTS);
		$NEW_AMOUNT_OF_POINTS_2 = 206;
		$this->assertNotEquals($NEW_AMOUNT_OF_POINTS_2, ilTrainingProgramme::DEFAULT_POINTS);
		$this->assertNotEquals($NEW_AMOUNT_OF_POINTS_1, $NEW_AMOUNT_OF_POINTS_2);
		
		$node2_progress1 = array_shift($this->node2->getProgressesOf($user1->getId()));
		$node2_progress2 = array_shift($this->node2->getProgressesOf($user2->getId()));
		
		$node2_progress1->setRequiredAmountOfPoints($NEW_AMOUNT_OF_POINTS_1, $this->user->getId());
		
		$this->node2->setPoints($NEW_AMOUNT_OF_POINTS_2)
					->update();
		$this->root->updateAllAssignments();
		
		$this->assertEquals($NEW_AMOUNT_OF_POINTS_1, $node2_progress1->getAmountOfPoints());
		$this->assertEquals($NEW_AMOUNT_OF_POINTS_2, $node2_progress2->getAmountOfPoints());
	}
}
