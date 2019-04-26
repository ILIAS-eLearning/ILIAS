<?php

/**
 * @group needsInstalledILIAS
 */
class ilStudyProgrammeSettingsRepositoryTest extends PHPUnit_Framework_TestCase
{
	protected $backupGlobals = FALSE;
	protected static $created;

	protected function setUp() {
		PHPUnit_Framework_Error_Deprecated::$enabled = FALSE;

		global $DIC;
		if(!$DIC) {
			try{
				include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
				ilUnitUtil::performInitialisation();
			} catch(Exception $e) {}
		}
		global $DIC;
		$this->db = $DIC['ilDB'];
	}

	public function test_init()
	{
		$repo = new ilStudyProgrammeSettingsDBRepository($this->db);
		$this->assertInstanceOf(ilStudyProgrammeSettingsRepository::class,$repo);
		return $repo;
	}

	/**
	 * @depends test_init
	 */
	public function test_create($repo)
	{
		$set = $repo->createFor(1);
		$this->assertEquals($set->getSubtypeId(),ilStudyProgrammeSettings::DEFAULT_SUBTYPE);
		$this->assertEquals($set->getStatus(),ilStudyProgrammeSettings::STATUS_DRAFT);
		$this->assertEquals($set->getLPMode(),ilStudyProgrammeSettings::MODE_UNDEFINED);
		$this->assertEquals($set->getPoints(),ilStudyProgrammeSettings::DEFAULT_POINTS);
		$this->assertEquals($set->getDeadlinePeriod(),0);
		$this->assertNull($set->getDeadlineDate());
	}

	/**
	 * @depends test_create
	 */
	public function test_edit_and_update()
	{
		$repo = new ilStudyProgrammeSettingsDBRepository($this->db);
		$set = $repo->read(1);
		$this->assertEquals($set->getSubtypeId(),ilStudyProgrammeSettings::DEFAULT_SUBTYPE);
		$this->assertEquals($set->getStatus(),ilStudyProgrammeSettings::STATUS_DRAFT);
		$this->assertEquals($set->getLPMode(),ilStudyProgrammeSettings::MODE_UNDEFINED);
		$this->assertEquals($set->getPoints(),ilStudyProgrammeSettings::DEFAULT_POINTS);
		$this->assertEquals($set->getDeadlinePeriod(),0);
		$this->assertNull($set->getDeadlineDate());

		$repo = new ilStudyProgrammeSettingsDBRepository($this->db);
		ilStudyProgrammeSettingsDBRepository::clearCache();
		$set = $repo->read(1);
		$this->assertEquals($set->getSubtypeId(),ilStudyProgrammeSettings::DEFAULT_SUBTYPE);
		$this->assertEquals($set->getStatus(),ilStudyProgrammeSettings::STATUS_DRAFT);
		$this->assertEquals($set->getLPMode(),ilStudyProgrammeSettings::MODE_UNDEFINED);
		$this->assertEquals($set->getPoints(),ilStudyProgrammeSettings::DEFAULT_POINTS);
		$this->assertEquals($set->getDeadlinePeriod(),0);
		$this->assertNull($set->getDeadlineDate());

		$set->setSubtypeId(123)
			->setStatus(ilStudyProgrammeSettings::STATUS_ACTIVE)
			->setLPMode(ilStudyProgrammeSettings::MODE_POINTS)
			->setPoints(10)
			->setDeadlinePeriod(10);
		$repo->update($set);
		ilStudyProgrammeSettingsDBRepository::clearCache();
		$set = $repo->read(1);
		$this->assertEquals($set->getSubtypeId(),123);
		$this->assertEquals($set->getStatus(),ilStudyProgrammeSettings::STATUS_ACTIVE);
		$this->assertEquals($set->getLPMode(),ilStudyProgrammeSettings::MODE_POINTS);
		$this->assertEquals($set->getPoints(),10);
		$this->assertEquals($set->getDeadlinePeriod(),10);
		$this->assertNull($set->getDeadlineDate());

		$set->setSubtypeId(123)
			->setDeadlineDate(new \ilDateTime('2020-01-01',IL_CAL_DATE));
		$repo->update($set);
		ilStudyProgrammeSettingsDBRepository::clearCache();
		$set = $repo->read(1);
		$this->assertEquals($set->getDeadlinePeriod(),0);
		$this->assertEquals($set->getDeadlineDate()->get(IL_CAL_DATE),'2020-01-01');

		$repo = new ilStudyProgrammeSettingsDBRepository($this->db);
		ilStudyProgrammeSettingsDBRepository::clearCache();
		$set = $repo->read(1);
		$this->assertEquals($set->getSubtypeId(),123);
		$this->assertEquals($set->getStatus(),ilStudyProgrammeSettings::STATUS_ACTIVE);
		$this->assertEquals($set->getLPMode(),ilStudyProgrammeSettings::MODE_POINTS);
		$this->assertEquals($set->getPoints(),10);
	}

	/**
	 * @depends test_edit_and_update
	 * @expectedException \LogicException
	 */
	public function test_delete() {
		$repo = new ilStudyProgrammeSettingsDBRepository($this->db);
		$set = $repo->read(1);
		$this->assertEquals($set->getSubtypeId(),123);
		$this->assertEquals($set->getStatus(),ilStudyProgrammeSettings::STATUS_ACTIVE);
		$this->assertEquals($set->getLPMode(),ilStudyProgrammeSettings::MODE_POINTS);
		$this->assertEquals($set->getPoints(),10);
		$repo->delete($set);
		$repo->read(1);
	}


}