<?php

/**
 * @group needsInstalledILIAS
 */
class ilPrgInvalidateExpiredProgressesCronJobTest extends PHPUnit_Framework_TestCase
{
	protected $backupGlobals = FALSE;

	protected static $instances = [];
	protected static $users = [];

	public static function setUpBeforeClass() {
		PHPUnit_Framework_Error_Deprecated::$enabled = FALSE;

		global $DIC;
		if(!$DIC) {
			try {
				include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
				ilUnitUtil::performInitialisation();
			} catch(Exception $e) {

			}
		}
	}

	public function test_init()
	{
		$job = new ilPrgInvalidateExpiredProgressesCronJob();
		$this->assertInstanceOf(ilCronJob::class,$job);
		return $job;
	}

	/**
	 * @depends test_init
	 */
	public function test_run($job)
	{
		$usr1 = $this->newUser();
		$usr2 = $this->newUser();
		$usr3 = $this->newUser();
		$usr4 = $this->newUser();

		$prg1 = $this->newPrg();
		$prg2 = $this->newPrg();

		$prg1->putInTree(ROOT_FOLDER_ID);
		$prg1->addNode($prg2);

		$val_date_ref = new DateTime();
		$val_date_ref->sub(new DateInterval('P1D'));
		$prg1->setValidityOfQualificationDate($val_date_ref);
		$prg1->setStatus(ilStudyProgrammeSettings::STATUS_ACTIVE);
		$prg1->update();

		$prg2->setStatus(ilStudyProgrammeSettings::STATUS_ACTIVE);

		$assignment1 = $prg1->assignUser($usr1->getId(),6);
		$assignment2 = $prg1->assignUser($usr2->getId(),6);
		$assignment3 = $prg1->assignUser($usr3->getId(),6);
		$assignment4 = $prg1->assignUser($usr4->getId(),6);


		$progress = $prg2->getProgressForAssignment($assignment1->getId());
		$progress->markAccredited(6);

		$this->assertTrue($prg1->getProgressForAssignment($assignment1->getId())->isSuccessful());

		$progress = $prg1->getProgressForAssignment($assignment2->getId());
		$progress->markAccredited(6);

		$this->assertTrue($prg1->getProgressForAssignment($assignment2->getId())->isSuccessful());

		$prg1->setValidityOfQualificationDate(null);
		$prg1->update();

		$progress = $prg2->getProgressForAssignment($assignment3->getId());
		$progress->markAccredited(6);

		$progress = $prg1->getProgressForAssignment($assignment4->getId());
		$progress->markAccredited(6);


		$job->run();
		$prgrs = $prg1->getProgressForAssignment($assignment1->getId());
		$this->assertEquals(
			ilStudyProgrammeProgress::STATUS_COMPLETED,
			$prgrs->getStatus()
		);
		$this->assertTrue($prgrs->isInvalidated());
		$prgrs = $prg1->getProgressForAssignment($assignment2->getId());
		$this->assertEquals(
			ilStudyProgrammeProgress::STATUS_ACCREDITED,
			$prgrs->getStatus()
		);
		$this->assertTrue($prgrs->isInvalidated());
		$prgrs = $prg1->getProgressForAssignment($assignment3->getId());
		$this->assertEquals(
			ilStudyProgrammeProgress::STATUS_COMPLETED,
			$prgrs->getStatus()
		);
		$this->assertFalse($prgrs->isInvalidated());
		$prgrs = $prg1->getProgressForAssignment($assignment4->getId());
		$this->assertEquals(
			ilStudyProgrammeProgress::STATUS_ACCREDITED,
			$prgrs->getStatus()
		);
		$this->assertFalse($prgrs->isInvalidated());
	}

	public static function tearDownAfterClass()
	{
		foreach (self::$instances as $instance) {
			try{
				$instance->delete();
			} catch(Exception $e) {}
		}
		foreach (self::$users as $user) {
			$user->delete();
		}
	}


	protected function newUser() {
		$user = new ilObjUser();
		$user->create();
		self::$users[] = $user;
		return $user;
	}

	protected function newPrg()
	{
		$prg = ilObjStudyProgramme::createInstance();
		self::$instances[] = $prg;
		return $prg;
	}
}