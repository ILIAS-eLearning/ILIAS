<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

require_once(__DIR__."/mocks.php");

/**
 * TestCase for the learning progress of users at a programme.
 * @group needsInstalledILIAS
 *        
 * @author Michael Herren <mh@studer-raimann.ch>
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 * @version 1.0.0
 */
class ilStudyProgrammeLPTest extends TestCase {
	protected $backupGlobals = FALSE;

	protected function setUp() : void {
		require_once("./Modules/StudyProgramme/classes/class.ilObjStudyProgramme.php");
		PHPUnit\Framework\Error\Deprecated::$enabled = false;

		global $DIC;
		if(!$DIC) {
			include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
			ilUnitUtil::performInitialisation();
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
		
		global $DIC;
		$ilUser = $DIC['ilUser'];
		$this->user = $ilUser;
	}
	
	protected function tearDown(): void {
		if ($this->root) {
			$this->root->delete();
		}
	}
	
	protected function newUser() {
		$user = new ilObjUser();
		$user->create();
		return $user;
	}

	protected function setAllNodesActive() {
		$this->root->setStatus(ilStudyProgrammeSettings::STATUS_ACTIVE)->update();
		$this->node1->setStatus(ilStudyProgrammeSettings::STATUS_ACTIVE)->update();
		$this->node2->setStatus(ilStudyProgrammeSettings::STATUS_ACTIVE)->update();
	}
	
	protected function assignNewUserToRoot() {
		$user = $this->newUser();
		return array($this->root->assignUser($user->getId(),6), $user);
	}

	public function testInitialLPActive() {
		$this->setAllNodesActive();
		$tmp = $this->assignNewUserToRoot();
		$ass = $tmp[0];
		$user = $tmp[1];
		
		require_once("Services/Tracking/classes/class.ilLPStatusWrapper.php");
		require_once("Services/Tracking/classes/class.ilLPStatus.php");
		$this->assertEquals( ilLPStatus::LP_STATUS_IN_PROGRESS_NUM
						   , ilLPStatusWrapper::_determineStatus($this->root->getId(), $user->getId())
						   );
		$this->assertEquals( ilLPStatus::LP_STATUS_IN_PROGRESS_NUM
						   , ilLPStatusWrapper::_determineStatus($this->node1->getId(), $user->getId())
						   );
		$this->assertEquals( ilLPStatus::LP_STATUS_IN_PROGRESS_NUM
						   , ilLPStatusWrapper::_determineStatus($this->node2->getId(), $user->getId())
						   );
	}
	
	public function testInitialLPDraft() {
		$this->root->setStatus(ilStudyProgrammeSettings::STATUS_ACTIVE)->update();
		$this->node1->setStatus(ilStudyProgrammeSettings::STATUS_ACTIVE)->update();
		$this->node2->setStatus(ilStudyProgrammeSettings::STATUS_DRAFT)->update();
		
		$tmp = $this->assignNewUserToRoot();
		$user = $tmp[1];
		$ass = $tmp[0];
		
		require_once("Services/Tracking/classes/class.ilLPStatusWrapper.php");
		require_once("Services/Tracking/classes/class.ilLPStatus.php");
		$this->assertEquals( ilLPStatus::LP_STATUS_IN_PROGRESS_NUM
						   , ilLPStatusWrapper::_determineStatus($this->root->getId(), $user->getId())
						   );
		$this->assertEquals( ilLPStatus::LP_STATUS_IN_PROGRESS_NUM
						   , ilLPStatusWrapper::_determineStatus($this->node1->getId(), $user->getId())
						   );
		$this->assertEquals( ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM
						   , ilLPStatusWrapper::_determineStatus($this->node2->getId(), $user->getId())
						   );
	}
	
	public function testInitialProgressOutdated() {
		$this->root->setStatus(ilStudyProgrammeSettings::STATUS_ACTIVE)->update();
		$this->node1->setStatus(ilStudyProgrammeSettings::STATUS_ACTIVE)->update();
		$this->node2->setStatus(ilStudyProgrammeSettings::STATUS_OUTDATED)->update();
		
		$tmp = $this->assignNewUserToRoot();
		$user = $tmp[1];
		$ass = $tmp[0];
		
		require_once("Services/Tracking/classes/class.ilLPStatusWrapper.php");
		require_once("Services/Tracking/classes/class.ilLPStatus.php");
		$this->assertEquals( ilLPStatus::LP_STATUS_IN_PROGRESS_NUM
						   , ilLPStatusWrapper::_determineStatus($this->root->getId(), $user->getId())
						   );
		$this->assertEquals( ilLPStatus::LP_STATUS_IN_PROGRESS_NUM
						   , ilLPStatusWrapper::_determineStatus($this->node1->getId(), $user->getId())
						   );
		$this->assertEquals( ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM
						   , ilLPStatusWrapper::_determineStatus($this->node2->getId(), $user->getId())
						   );
	}

	public function testMarkAccredited() {
		$this->setAllNodesActive();
		$tmp = $this->assignNewUserToRoot();
		$ass = $tmp[0];
		$user = $tmp[1];
		
		$user2 = $this->newUser();
		$USER_ID = $user2->getId();
		
		$node2_progress = array_shift($this->node2->getProgressesOf($user->getId()));
		$node2_progress->markAccredited($USER_ID);
		
		require_once("Services/Tracking/classes/class.ilLPStatusWrapper.php");
		require_once("Services/Tracking/classes/class.ilLPStatus.php");
		$this->assertEquals( ilLPStatus::LP_STATUS_COMPLETED_NUM
						   , ilLPStatusWrapper::_determineStatus($this->root->getId(), $user->getId())
						   );
		$this->assertEquals( ilLPStatus::LP_STATUS_IN_PROGRESS_NUM
						   , ilLPStatusWrapper::_determineStatus($this->node1->getId(), $user->getId())
						   );
		$this->assertEquals( ilLPStatus::LP_STATUS_COMPLETED_NUM
						   , ilLPStatusWrapper::_determineStatus($this->node2->getId(), $user->getId())
						   );
	}

	public function testUnmarkAccredited() {
		$this->setAllNodesActive();
		$tmp = $this->assignNewUserToRoot();
		$ass = $tmp[0];
		$user = $tmp[1];
		
		$user2 = $this->newUser();
		$USER_ID = $user2->getId();
		
		$node2_progress = array_shift($this->node2->getProgressesOf($user->getId()));
		$node2_progress->markAccredited($USER_ID);
		
		$this->assertEquals( ilLPStatus::LP_STATUS_COMPLETED_NUM
						   , ilLPStatusWrapper::_determineStatus($this->node2->getId(), $user->getId())
						   );
		
		$node2_progress->unmarkAccredited();

		$this->assertEquals( ilLPStatus::LP_STATUS_IN_PROGRESS_NUM
						   , ilLPStatusWrapper::_determineStatus($this->root->getId(), $user->getId())
						   );
		$this->assertEquals( ilLPStatus::LP_STATUS_IN_PROGRESS_NUM
						   , ilLPStatusWrapper::_determineStatus($this->node1->getId(), $user->getId())
						   );
		$this->assertEquals( ilLPStatus::LP_STATUS_IN_PROGRESS_NUM
						   , ilLPStatusWrapper::_determineStatus($this->node2->getId(), $user->getId())
						   );
	}


	public function testMarkNotRelevant() {
		$this->setAllNodesActive();
		$tmp = $this->assignNewUserToRoot();
		$ass = $tmp[0];
		$user = $tmp[1];
		
		$user2 = $this->newUser();
		$USER_ID = $user2->getId();
		
		$node2_progress = array_shift($this->node2->getProgressesOf($user->getId()));
		$node2_progress->markNotRelevant($USER_ID);

		$this->assertEquals( ilLPStatus::LP_STATUS_IN_PROGRESS_NUM
						   , ilLPStatusWrapper::_determineStatus($this->root->getId(), $user->getId())
						   );
		$this->assertEquals( ilLPStatus::LP_STATUS_IN_PROGRESS_NUM
						   , ilLPStatusWrapper::_determineStatus($this->node1->getId(), $user->getId())
						   );
		$this->assertEquals( ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM
						   , ilLPStatusWrapper::_determineStatus($this->node2->getId(), $user->getId())
						   );
	}

	public function testMarkFailed() {
		$this->setAllNodesActive();
		$tmp = $this->assignNewUserToRoot();
		$ass = $tmp[0];
		$user = $tmp[1];

		$user2 = $this->newUser();
		$USER_ID = $user2->getId();

		$node2_progress = array_shift($this->node2->getProgressesOf($user->getId()));
		$node2_progress->markFailed($USER_ID);

		$this->assertEquals( ilLPStatus::LP_STATUS_IN_PROGRESS_NUM
						   , ilLPStatusWrapper::_determineStatus($this->root->getId(), $user->getId())
						   );
		$this->assertEquals( ilLPStatus::LP_STATUS_IN_PROGRESS_NUM
						   , ilLPStatusWrapper::_determineStatus($this->node1->getId(), $user->getId())
						   );
		$this->assertEquals( ilLPStatus::LP_STATUS_FAILED_NUM
						   , ilLPStatusWrapper::_determineStatus($this->node2->getId(), $user->getId())
						   );
	}

	public function testMarkNotFailed() {
		$this->setAllNodesActive();
		$tmp = $this->assignNewUserToRoot();
		$ass = $tmp[0];
		$user = $tmp[1];

		$user2 = $this->newUser();
		$USER_ID = (int)$user2->getId();

		$node2_progress = array_shift($this->node2->getProgressesOf($user->getId()));
		$node2_progress->markFailed($USER_ID);

		$this->assertEquals( ilLPStatus::LP_STATUS_IN_PROGRESS_NUM
						   , ilLPStatusWrapper::_determineStatus($this->root->getId(), $user->getId())
						   );
		$this->assertEquals( ilLPStatus::LP_STATUS_IN_PROGRESS_NUM
						   , ilLPStatusWrapper::_determineStatus($this->node1->getId(), $user->getId())
						   );
		$this->assertEquals( ilLPStatus::LP_STATUS_FAILED_NUM
						   , ilLPStatusWrapper::_determineStatus($this->node2->getId(), $user->getId())
						   );

		$node2_progress->markNotFailed($USER_ID);
		$this->assertEquals( ilLPStatus::LP_STATUS_IN_PROGRESS_NUM
						   , ilLPStatusWrapper::_determineStatus($this->node2->getId(), $user->getId())
						   );
	}

	// Neues Moduls: Wird dem Studierenden-Studierenden inkl. Kurse, Punkte als "Nicht relevant" hinzugefügt.
	public function testNewNodesAreNotRelevant() {
		$this->setAllNodesActive();
		$tmp = $this->assignNewUserToRoot();
		$ass = $tmp[0];
		$user = $tmp[1];

		$node3 = ilObjStudyProgramme::createInstance();
		$this->root->addNode($node3);
		
		$node3_progress = array_shift($node3->getProgressesOf($user->getId()));
		$this->assertNotNull($node3_progress);
		$this->assertEquals( ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM
						   , ilLPStatusWrapper::_determineStatus($node3->getId(), $user->getId())
						   );
	}

	public function test_invalidate() {
		$progress_repo = ilStudyProgrammeDIC::dic()['model.Progress.ilStudyProgrammeProgressRepository'];
		$this->setAllNodesActive();
		$yesterday = new DateTime();
		$yesterday->sub(new DateInterval('P1D'));

		$tmp = $this->assignNewUserToRoot();
		$ass = $tmp[0];
		$user = $tmp[1];

		$node2_progress = array_shift($this->node2->getProgressesOf($user->getId()));
		$node2_progress->markAccredited(6);

		$this->assertEquals( ilLPStatus::LP_STATUS_COMPLETED_NUM
						   , ilLPStatusWrapper::_determineStatus($this->node2->getId(), $user->getId())
						   );
		$this->assertEquals( ilLPStatus::LP_STATUS_COMPLETED_NUM
						   , ilLPStatusWrapper::_determineStatus($this->root->getId(), $user->getId())
						   );
		$progress_repo->update(
			$progress_repo->readByIds((int)$this->root->getId(),(int)$ass->getId(),(int)$user->getId())
				->setValidityOfQualification($yesterday)
		);

		$root_progress = array_shift($this->root->getProgressesOf($user->getId()));
		$this->assertTrue($root_progress->isSuccessfulExpired());
		$this->assertFalse($root_progress->isInvalidated());
		$root_progress->invalidate();
		$root_progress = array_shift($this->root->getProgressesOf($user->getId()));
		$this->assertTrue($root_progress->isSuccessfulExpired());
		$this->assertTrue($root_progress->isInvalidated());
		$this->assertEquals( ilLPStatus::LP_STATUS_FAILED_NUM
						   , ilLPStatusWrapper::_determineStatus($this->root->getId(), $user->getId())
						   );
	}
}
