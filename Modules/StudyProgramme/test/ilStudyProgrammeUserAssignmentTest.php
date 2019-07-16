<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__."/mocks.php");

/**
 * TestCase for the assignment of users to a programme.
 * @group needsInstalledILIAS
 *        
 * @author Michael Herren <mh@studer-raimann.ch>
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 * @version 1.0.0
 */
class ilStudyProgrammeUserAssignmentTest extends PHPUnit_Framework_TestCase {
	protected $backupGlobals = FALSE;

	protected function setUp() {
		PHPUnit_Framework_Error_Deprecated::$enabled = FALSE;

		global $DIC;
		if(!$DIC) {
			include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
			try {
				ilUnitUtil::performInitialisation();
			} catch(Exception $e) {}
		}
		
		$this->root = ilObjStudyProgramme::createInstance();
		$this->root->putInTree(ROOT_FOLDER_ID);
		$this->root->object_factory = new ilObjectFactoryWrapperMock();
		
		$this->node1 = ilObjStudyProgramme::createInstance();
		$this->node2 = ilObjStudyProgramme::createInstance();
		
		$this->leaf1 = new ilStudyProgrammeLeafMock();
		$this->leaf2 = new ilStudyProgrammeLeafMock();
		
		$this->root->addNode($this->node1);
		$this->root->addNode($this->node2);
		$this->node1->addLeaf($this->leaf1);
		$this->node2->addLeaf($this->leaf2);
		
		global $DIC;
		$tree = $DIC['tree'];
		$this->tree = $tree;
	}
	
	protected function tearDown() {
		if ($this->root) {
			$this->root->delete();
		}
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
		$this->assertEquals(ilStudyProgrammeSettings::STATUS_DRAFT, $this->root->getStatus());
		$this->root->assignUser($user->getId(),6);
	}
	
	/**
	 * @expectedException ilException
	 */
	public function testNoAssignmentWhenOutdated() {
		$user = $this->newUser();
		
		$this->root->setStatus(ilStudyProgrammeSettings::STATUS_OUTDATED);
		$this->assertEquals(ilStudyProgrammeSettings::STATUS_OUTDATED, $this->root->getStatus());
		$this->root->assignUser($user->getId(),6);
	}
	
	/**
	 * @expectedException ilException
	 */
	public function testNoAssignementWhenNotCreated() {
		$user = $this->newUser();
		$prg = new ilObjStudyProgramme();
		$prg->assignUser($user->getId(),6);
	}
	
	public function testUserId() {
		$user1 = $this->newUser();
		$this->root->setStatus(ilStudyProgrammeSettings::STATUS_ACTIVE);
		$ass = $this->root->assignUser($user1->getId(),6);
		$this->assertEquals($user1->getId(), $ass->getUserId());
	}
	
	public function testHasAssignmentOf() {
		$user1 = $this->newUser();
		$user2 = $this->newUser();
		
		$this->root->setStatus(ilStudyProgrammeSettings::STATUS_ACTIVE);
		
		$this->root->assignUser($user1->getId(),6);
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
		
		$this->root->setStatus(ilStudyProgrammeSettings::STATUS_ACTIVE);
		$this->node1->setStatus(ilStudyProgrammeSettings::STATUS_ACTIVE);
		$this->node2->setStatus(ilStudyProgrammeSettings::STATUS_ACTIVE);
		
		$this->assertEquals(0, $this->root->getAmountOfAssignmentsOf($user1->getId()));
		
		$this->root->assignUser($user2->getId(),6);
		$this->assertEquals(1, $this->root->getAmountOfAssignmentsOf($user2->getId()));
		$this->assertEquals(1, $this->node1->getAmountOfAssignmentsOf($user2->getId()));
		$this->assertEquals(1, $this->node2->getAmountOfAssignmentsOf($user2->getId()));
	
		$this->root->assignUser($user3->getId(),6);
		$this->root->assignUser($user3->getId(),6);
		$this->assertEquals(2, $this->root->getAmountOfAssignmentsOf($user3->getId()));
		$this->assertEquals(2, $this->node1->getAmountOfAssignmentsOf($user3->getId()));
		$this->assertEquals(2, $this->node2->getAmountOfAssignmentsOf($user3->getId()));

		$this->root->assignUser($user4->getId(),6);
		$this->node1->assignUser($user4->getId(),6);
		$this->assertEquals(1, $this->root->getAmountOfAssignmentsOf($user4->getId()));
		$this->assertEquals(2, $this->node1->getAmountOfAssignmentsOf($user4->getId()));
		$this->assertEquals(1, $this->node2->getAmountOfAssignmentsOf($user4->getId()));
	}
	
	public function testGetAssignmentsOf() {
		$user1 = $this->newUser();
		$user2 = $this->newUser();
		
		$this->root->setStatus(ilStudyProgrammeSettings::STATUS_ACTIVE);
		$this->node1->setStatus(ilStudyProgrammeSettings::STATUS_ACTIVE);
		
		$this->assertEquals(0, count($this->root->getAssignmentsOf($user1->getId())));
		$this->assertEquals(0, count($this->node1->getAssignmentsOf($user1->getId())));
		$this->assertEquals(0, count($this->node2->getAssignmentsOf($user1->getId())));

		$this->root->assignUser($user2->getId(),6);
		$this->node1->assignUser($user2->getId(),6);
		
		$root_ass = $this->root->getAssignmentsOf($user2->getId());
		$node1_ass = $this->node1->getAssignmentsOf($user2->getId());
		$node2_ass = $this->node2->getAssignmentsOf($user2->getId());
		
		$this->assertEquals(1, count($root_ass));
		$this->assertEquals(2, count($node1_ass));
		$this->assertEquals(1, count($node2_ass));
		
		$this->assertEquals($this->root->getId(), $root_ass[0]->getStudyProgramme()->getId());
		$this->assertEquals($this->root->getId(), $node2_ass[0]->getStudyProgramme()->getId());
		
		$node1_ass_prg_ids = array_map(function($ass) {
			return $ass->getStudyProgramme()->getId();
		}, $node1_ass);
		$this->assertContains($this->root->getId(), $node1_ass_prg_ids);
		$this->assertContains($this->node1->getId(), $node1_ass_prg_ids);
	}
	
	/**
	 * @expectedException ilException
	 */
	public function testRemoveOnRootNodeOnly1() {
		$user = $this->newUser();
		
		$this->root->setStatus(ilStudyProgrammeSettings::STATUS_ACTIVE);
		
		$ass1 = $this->root->assignUser($user->getId(),6);
		$this->node1->removeAssignment($ass1);
	}

	/**
	 * @expectedException ilException
	 */
	public function testRemoveOnRootNodeOnly2() {
		$user = $this->newUser();
		
		$this->node1->setStatus(ilStudyProgrammeSettings::STATUS_ACTIVE);
		
		$ass1 = $this->node1->assignUser($user->getId(),6);
		$this->root->removeAssignment($ass1);
	}
	
	public function testRemoveAssignment1() {
		$user = $this->newUser();
		
		$this->root->setStatus(ilStudyProgrammeSettings::STATUS_ACTIVE);
		
		$ass1 = $this->root->assignUser($user->getId(),6);
		$this->root->removeAssignment($ass1);
		$this->assertFalse($this->root->hasAssignmentOf($user->getId()));
	}
	
	public function testRemoveAssignment2() {
		$user = $this->newUser();
		
		$this->root->setStatus(ilStudyProgrammeSettings::STATUS_ACTIVE);
		
		$ass1 = $this->root->assignUser($user->getId(),6);
		$ass1->deassign();
		$this->assertFalse($this->root->hasAssignmentOf($user->getId()));
	}
	
	public function testGetAssignments() {
		$user1 = $this->newUser();
		$user2 = $this->newUser();
		
		$this->root->setStatus(ilStudyProgrammeSettings::STATUS_ACTIVE);
		$this->node1->setStatus(ilStudyProgrammeSettings::STATUS_ACTIVE);
		
		$ass1 = $this->root->assignUser($user1->getId(),6);
		$ass2 = $this->node1->assignUser($user2->getId(),6);
		
		$asses = $this->node1->getAssignments();
		$ass_ids = array_map(function($ass) {
			return $ass->getId();
		}, $asses);
		//$this->assertContains($ass1->getId(), $ass_ids);
		$this->assertContains($ass2->getId(), $ass_ids);
	}

	public function testNoRestartDate() {
		$user1 = $this->newUser();

		$prg1 = ilObjStudyProgramme::createInstance();
		$prg2 = ilObjStudyProgramme::createInstance();

		$prg1->putInTree(ROOT_FOLDER_ID);
		$prg1->addNode($prg2);
		$prg1->setValidityOfQualificationPeriod(110);
		$prg1->update();

		$prg1->setStatus(ilStudyProgrammeSettings::STATUS_ACTIVE);
		$prg2->setStatus(ilStudyProgrammeSettings::STATUS_ACTIVE);

		$ass1 = $prg1->assignUser($user1->getId(),6);
		$progress2 = $prg2->getProgressForAssignment($ass1->getId());				
		$progress2->markAccredited(6);
		$progress1 = $prg1->getProgressForAssignment($ass1->getId());
		$this->assertTrue($progress1->isSuccessful());

		$ass1_new = array_shift($prg1->getAssignmentsOf($user1->getId()));
		$this->assertNull($ass1_new->getRestartDate());
	}

	public function testRestartDate() {
		$user1 = $this->newUser();

		$prg1 = ilObjStudyProgramme::createInstance();
		$prg2 = ilObjStudyProgramme::createInstance();

		$prg1->putInTree(ROOT_FOLDER_ID);
		$prg1->addNode($prg2);
		$prg1->setValidityOfQualificationPeriod(110);
		$prg1->setRestartPeriod(10);
		$prg1->update();

		$prg1->setStatus(ilStudyProgrammeSettings::STATUS_ACTIVE);
		$prg2->setStatus(ilStudyProgrammeSettings::STATUS_ACTIVE);

		$ass1 = $prg1->assignUser($user1->getId(),6);
		$progress2 = $prg2->getProgressForAssignment($ass1->getId());				
		$progress2->markAccredited(6);
		$progress1 = $prg1->getProgressForAssignment($ass1->getId());
		$this->assertTrue($progress1->isSuccessful());

		$ass1_new = array_shift($prg1->getAssignmentsOf($user1->getId()));
		$val_date = new DateTime();
		$val_date->add(new DateInterval('P100D'));
		$this->assertEquals(
			$val_date->format('Ymd'),
			$ass1_new->getRestartDate()->format('Ymd')
		);
	}


	public function testDeleteOfProgrammeRemovesEntriesInPrgUsrAssignment() {
		$user1 = $this->newUser();
		$this->root->setStatus(ilStudyProgrammeSettings::STATUS_ACTIVE);
		$ass = $this->root->assignUser($user1->getId(),6);

		$root_id  = $this->root->getId();
		$this->root->delete();
		$this->root = null;
		
		global $DIC;
		$ilDB = $DIC['ilDB'];
		$res = $ilDB->query( "SELECT COUNT(*) cnt "
							." FROM ".ilStudyProgrammeAssignmentDBRepository::TABLE
							." WHERE root_prg_id = ".$root_id
							);
		$rec = $ilDB->fetchAssoc($res);
		$this->assertEquals(0, $rec["cnt"]);
	}

	public function testDeassignRemovesEntriesInPrgUsrAssignment() {
		$user = $this->newUser();
		$this->root->setStatus(ilStudyProgrammeSettings::STATUS_ACTIVE);
		$ass1 = $this->root->assignUser($user->getId(),6);
		$ass1->deassign();
		
		global $DIC;
		$ilDB = $DIC['ilDB'];
		$res = $ilDB->query( "SELECT COUNT(*) cnt "
							." FROM ".ilStudyProgrammeAssignmentDBRepository::TABLE
							." WHERE id = ".$ass1->getId()
							);
		$rec = $ilDB->fetchAssoc($res);
		$this->assertEquals(0, $rec["cnt"]);
	}

	public function testRstartAssignment()
	{
		$user1 = $this->newUser();

		$prg1 = ilObjStudyProgramme::createInstance();
		$prg2 = ilObjStudyProgramme::createInstance();

		$prg1->putInTree(ROOT_FOLDER_ID);
		$prg1->addNode($prg2);

		$prg1->setStatus(ilStudyProgrammeSettings::STATUS_ACTIVE);
		$prg2->setStatus(ilStudyProgrammeSettings::STATUS_ACTIVE);

		$ass1 = $prg1->assignUser($user1->getId(),6);

		$progress1 = $prg1->getProgressForAssignment($ass1->getId());
		$progress1->markFailed(6);

		$this->assertEquals(
			$prg1->getProgressForAssignment($ass1->getId())->getStatus(),
			ilStudyProgrammeProgress::STATUS_FAILED
		);
		$this->assertEquals(
			$prg2->getProgressForAssignment($ass1->getId())->getStatus(),
			ilStudyProgrammeProgress::STATUS_IN_PROGRESS
		);
		
		$ass2 = $ass1->restartAssignment();
		$this->assertEquals(
			$prg1->getProgressForAssignment($ass2->getId())->getStatus(),
			ilStudyProgrammeProgress::STATUS_IN_PROGRESS
		);
		$this->assertEquals(
			$prg2->getProgressForAssignment($ass2->getId())->getStatus(),
			ilStudyProgrammeProgress::STATUS_IN_PROGRESS
		);

		$this->assertNotEquals($ass2->getId(),$ass1->getId());
		$this->assertNotEquals(
			$prg1->getProgressForAssignment($ass2->getId()),
			$prg1->getProgressForAssignment($ass1->getId())
		);
		$this->assertNotEquals(
			$prg2->getProgressForAssignment($ass2->getId()),
			$prg2->getProgressForAssignment($ass1->getId())
		);

		$assignments = $prg1->getAssignmentsOf($user1->getId());
		$this->assertCount(2,$assignments);
		foreach ($assignments as $ass) {
			if($ass->getId() === $ass1->getId()) {
				$this->assertEquals(
					$ass->getRestartedAssignmentId(),
					$ass2->getId()
				);
			} elseif($ass->getId() === $ass2->getId()) {
				$this->assertEquals(
					$ass->getRestartedAssignmentId(),
					ilStudyProgrammeAssignment::NO_RESTARTED_ASSIGNMENT
				);
			} else {
				$this->assertFalse('there are more assignments than expected');
			}
		}
	}
	
}
