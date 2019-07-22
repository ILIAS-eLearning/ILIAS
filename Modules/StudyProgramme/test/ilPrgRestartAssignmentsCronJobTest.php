<?php

/**
 * @group needsInstalledILIAS
 */
class ilPrgRestartAssignmentsCronJobTest extends PHPUnit_Framework_TestCase
{
	protected $backupGlobals = FALSE;

	protected $assignment_repo;

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

	public function setUp()
	{
		$this->assignment_repo =
			ilStudyProgrammeDIC::dic()['model.Assignment.ilStudyProgrammeAssignmentRepository'];
	}

	public function test_init()
	{
		$job = new ilPrgRestartAssignmentsCronJob();
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

		$prg = $this->newPrg();
		$prg->putInTree(ROOT_FOLDER_ID);
		$prg->setStatus(ilStudyProgrammeSettings::STATUS_ACTIVE);
		$prg->update();

		$assignment1 = $prg->assignUser($usr1->getId(),6);
		$assignment2 = $prg->assignUser($usr2->getId(),6);
		$assignment2_r = $assignment2->restartAssignment();
		$assignment3 = $prg->assignUser($usr3->getId(),6);
		$assignment4 = $prg->assignUser($usr4->getId(),6);

		$this->assertCount(1,$prg->getAssignmentsOf($usr1->getId()));
		$this->assertCount(2,$prg->getAssignmentsOf($usr2->getId()));
		$this->assertCount(1,$prg->getAssignmentsOf($usr3->getId()));
		$this->assertCount(1,$prg->getAssignmentsOf($usr4->getId()));

		$yesterday = new DateTime();
		$yesterday->sub(new DateInterval('P1D'));
		$tomorrow = new DateTime();
		$tomorrow->add(new DateInterval('P1D'));

		$m_ass1 = $this->assignment_repo->read($assignment1->getId());
		$this->assignment_repo->update($m_ass1->setRestartDate($yesterday));
		$m_ass2 = $this->assignment_repo->read($assignment2->getId());
		$this->assignment_repo->update($m_ass2->setRestartDate($yesterday));
		$m_ass3 = $this->assignment_repo->read($assignment3->getId());
		$this->assignment_repo->update($m_ass3->setRestartDate($tomorrow));

		$job->run();

		$this->assertCount(2,$prg->getAssignmentsOf($usr1->getId()));
		$this->assertCount(2,$prg->getAssignmentsOf($usr2->getId()));
		$this->assertCount(1,$prg->getAssignmentsOf($usr3->getId()));
		$this->assertCount(1,$prg->getAssignmentsOf($usr4->getId()));

		foreach ($prg->getAssignmentsOf($usr1->getId()) as $ass) {
			if($ass->getId() !== $assignment1->getId()) {
				$assignment1_r = $ass;
			}
			if($ass->getId() === $assignment1->getId()) {
				$assignment1 = $ass;
			}
		}
		$this->assertEquals($assignment1->getRestartedAssignmentId(),$assignment1_r->getId());
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