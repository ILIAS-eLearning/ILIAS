<?php

/**
 * @group needsInstalledILIAS
 */
class ilStudyProgrammeProgressRepositoryTest extends PHPUnit_Framework_TestCase
{
	protected $backupGlobals = FALSE;
	protected static $created = [];

	protected function setUp() {
		PHPUnit_Framework_Error_Deprecated::$enabled = FALSE;

		global $DIC;
		if(!$DIC) {
			include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
			ilUnitUtil::performInitialisation();
		}
		global $DIC;
		$this->db = $DIC['ilDB'];
	}

	public function test_init()
	{
		$repo = new ilStudyProgrammeProgressDBRepository($this->db);
		$this->assertInstanceOf(ilStudyProgrammeProgressRepository::class,$repo);
		return $repo;
	}

	/**
	 * @depends test_init
	 */
	public function test_create($repo)
	{
		$prg = new ilStudyProgrammeSettings(1);
		$prg->setPoints(123);
		$ass = new ilStudyProgrammeAssignment(20);
		$ass->setUserId(30);
		$prgs = $repo->createFor($prg,$ass);
		self::$created[$prgs->getId()] = $prgs;
		$this->assertEquals($prgs->getNodeId(),1);
		$this->assertEquals($prgs->getAssignmentId(),20);
		$this->assertEquals($prgs->getUserId(),30);
		$this->assertEquals($prgs->getAmountOfPoints(),123);
		$this->assertEquals($prgs->getCurrentAmountOfPoints(),0);
		$this->assertEquals($prgs->getStatus(),ilStudyProgrammeProgress::STATUS_IN_PROGRESS);
		$this->assertEquals($prgs->getAssignmentDate()->get(IL_CAL_DATE),(new DateTime())->format('Y-m-d'));
		$this->assertNull($prgs->getCompletionBy());
		$this->assertNull($prgs->getDeadline());

		$prg = new ilStudyProgrammeSettings(1);
		$prg->setPoints(123);
		$ass = new ilStudyProgrammeAssignment(21);
		$ass->setUserId(30);
		$prgs = $repo->createFor($prg,$ass);
		self::$created[$prgs->getId()] = $prgs;

		$prg = new ilStudyProgrammeSettings(1);
		$prg->setPoints(123);
		$ass = new ilStudyProgrammeAssignment(22);
		$ass->setUserId(31);
		$prgs = $repo->createFor($prg,$ass);
		self::$created[$prgs->getId()] = $prgs;

		$prg = new ilStudyProgrammeSettings(2);
		$prg->setPoints(123);
		$ass = new ilStudyProgrammeAssignment(23);
		$ass->setUserId(31);
		$prgs = $repo->createFor($prg,$ass);
		self::$created[$prgs->getId()] = $prgs;

		$prg = new ilStudyProgrammeSettings(2);
		$prg->setPoints(123);
		$ass = new ilStudyProgrammeAssignment(24);
		$ass->setUserId(32);
		$prgs = $repo->createFor($prg,$ass);
		self::$created[$prgs->getId()] = $prgs;
	}

	/**
	 * @depends test_create
	 */
	public function test_save_and_load()
	{
		$repo = new ilStudyProgrammeProgressDBRepository($this->db);
		$prgs = current(self::$created);
		$prgs = $repo->read($prgs->getId());
		$this->assertEquals($prgs->getNodeId(),1);
		$this->assertEquals($prgs->getAssignmentId(),20);
		$this->assertEquals($prgs->getUserId(),30);
		$this->assertEquals($prgs->getAmountOfPoints(),123);
		$this->assertEquals($prgs->getCurrentAmountOfPoints(),0);
		$this->assertEquals($prgs->getStatus(),ilStudyProgrammeProgress::STATUS_IN_PROGRESS);
		$this->assertNull($prgs->getCompletionBy());
		$this->assertNull($prgs->getDeadline());

		$prgs->setAmountOfPoints(234)
			->setCurrentAmountOfPoints(345)
			->setStatus(ilStudyProgrammeProgress::STATUS_ACCREDITED)
			->setCompletionBy(6)
			->setDeadline(new ilDateTime('2018-01-01',IL_CAL_DATE));
		$repo->update($prgs);
		
		$repo = new ilStudyProgrammeProgressDBRepository($this->db);
		$prgs = $repo->read($prgs->getId());
		$this->assertEquals($prgs->getNodeId(),1);
		$this->assertEquals($prgs->getAssignmentId(),20);
		$this->assertEquals($prgs->getUserId(),30);
		$this->assertEquals($prgs->getAmountOfPoints(),234);
		$this->assertEquals($prgs->getCurrentAmountOfPoints(),345);
		$this->assertEquals($prgs->getStatus(),ilStudyProgrammeProgress::STATUS_ACCREDITED);
		$this->assertEquals($prgs->getCompletionBy(),6);
		$this->assertEquals($prgs->getDeadline()->get(IL_CAL_DATE),'2018-01-01');
	}

	/**
	 * @depends test_save_and_load
	 */
	public function test_query_ByIds()
	{
	
		$repo = new ilStudyProgrammeProgressDBRepository($this->db);
		$prgs = $repo->readByIds(1,20,30);
		$this->assertEquals($prgs->getNodeId(),1);
		$this->assertEquals($prgs->getAssignmentId(),20);
		$this->assertEquals($prgs->getUserId(),30);
		$this->assertEquals($prgs->getAmountOfPoints(),234);
		$this->assertEquals($prgs->getCurrentAmountOfPoints(),345);
		$this->assertEquals($prgs->getStatus(),ilStudyProgrammeProgress::STATUS_ACCREDITED);
		$this->assertEquals($prgs->getCompletionBy(),6);
		$this->assertEquals($prgs->getDeadline()->get(IL_CAL_DATE),'2018-01-01');
	}

	/**
	 * @depends test_save_and_load
	 */
	public function test_query_ByPrgIdUsrId()
	{
		$repo = new ilStudyProgrammeProgressDBRepository($this->db);
		$prgss = $repo->readByPrgIdAndUserId(1,30);
		$this->assertCount(2, $prgss);
		$assignments = [];
		foreach ($prgss as $prgs) {
			$this->assertEquals($prgs->getNodeId(),1);
			$this->assertEquals($prgs->getUserId(),30);
			$assignments[] = $prgs->getAssignmentId();
		}
		$this->assertEquals([20,21],$assignments);
	}

	/**
	 * @depends test_save_and_load
	 */
	public function test_query_ByPrgId()
	{
		$repo = new ilStudyProgrammeProgressDBRepository($this->db);
		$prgss = $repo->readByPrgId(1);
		$this->assertCount(3, $prgss);
		$assignments = [];
		foreach ($prgss as $prgs) {
			$this->assertEquals($prgs->getNodeId(),1);
			$assignments[] = $prgs->getAssignmentId();
			if(in_array($prgs->getAssignmentId(), [20,21])) {
				$this->assertEquals($prgs->getUserId(),30);
				continue;
			}
			if($prgs->getAssignmentId() === 22) {
				$this->assertEquals($prgs->getUserId(),31);
				continue;
			}
			$this->assertFalse('unexpected assignment id');
		}
		$this->assertEquals([20,21,22],$assignments);
	}

	public static function tearDownAfterClass()
	{
		global $DIC;
		if(!$DIC) {
			include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
			ilUnitUtil::performInitialisation();
		}
		global $DIC;
		$db = $DIC['ilDB'];
		if(count(self::$created) > 0) {
			$db->manipulate(
				'DELETE FROM '.ilStudyProgrammeProgressDBRepository::TABLE
				.'	WHERE'
				.'	'.$db->in(
						ilStudyProgrammeProgressDBRepository::FIELD_ID,
						array_keys(self::$created),
						false,
						'integer'
					)

			);
		}
	}
}