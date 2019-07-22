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
		$this->tps = $this->createMock(ilOrgUnitObjectTypePositionSetting::class);
		$this->tps->method('getActivationDefault')
			->willReturn(true);
	}

	public function test_init()
	{
		$repo = new ilStudyProgrammeSettingsDBRepository(
			$this->db,
			$this->tps
		);
		$this->assertInstanceOf(ilStudyProgrammeSettingsRepository::class,$repo);
		return $repo;
	}

	/**
	 * @depends test_init
	 */
	public function test_create($repo)
	{
		$set = $repo->createFor(-1);
		$this->assertEquals($set->getSubtypeId(),ilStudyProgrammeSettings::DEFAULT_SUBTYPE);
		$this->assertEquals($set->getStatus(),ilStudyProgrammeSettings::STATUS_DRAFT);
		$this->assertEquals($set->getLPMode(),ilStudyProgrammeSettings::MODE_UNDEFINED);
		$this->assertEquals($set->getPoints(),ilStudyProgrammeSettings::DEFAULT_POINTS);
		$this->assertEquals($set->getDeadlinePeriod(),0);
		$this->assertNull($set->getDeadlineDate());
		$this->assertEquals($set->getValidityOfQualificationPeriod(),ilStudyProgrammeSettings::NO_VALIDITY_OF_QUALIFICATION_PERIOD);
		$this->assertNull($set->getValidityOfQualificationDate());
		$this->assertEquals($set->getRestartPeriod(),ilStudyProgrammeSettings::NO_RESTART);
	}

	/**
	 * @depends test_create
	 */
	public function test_edit_and_update()
	{
		$repo = new ilStudyProgrammeSettingsDBRepository(
			$this->db,
			$this->tps
		);
		$set = $repo->read(-1);
		$this->assertEquals($set->getSubtypeId(),ilStudyProgrammeSettings::DEFAULT_SUBTYPE);
		$this->assertEquals($set->getStatus(),ilStudyProgrammeSettings::STATUS_DRAFT);
		$this->assertEquals($set->getLPMode(),ilStudyProgrammeSettings::MODE_UNDEFINED);
		$this->assertEquals($set->getPoints(),ilStudyProgrammeSettings::DEFAULT_POINTS);
		$this->assertEquals($set->getDeadlinePeriod(),0);
		$this->assertNull($set->getDeadlineDate());
		$this->assertEquals($set->getValidityOfQualificationPeriod(),ilStudyProgrammeSettings::NO_VALIDITY_OF_QUALIFICATION_PERIOD);
		$this->assertNull($set->getValidityOfQualificationDate());
		$this->assertEquals($set->getRestartPeriod(),ilStudyProgrammeSettings::NO_RESTART);

		$repo = new ilStudyProgrammeSettingsDBRepository(
			$this->db,
			$this->tps
		);
		ilStudyProgrammeSettingsDBRepository::clearCache();
		$set = $repo->read(-1);
		$this->assertEquals($set->getSubtypeId(),ilStudyProgrammeSettings::DEFAULT_SUBTYPE);
		$this->assertEquals($set->getStatus(),ilStudyProgrammeSettings::STATUS_DRAFT);
		$this->assertEquals($set->getLPMode(),ilStudyProgrammeSettings::MODE_UNDEFINED);
		$this->assertEquals($set->getPoints(),ilStudyProgrammeSettings::DEFAULT_POINTS);
		$this->assertEquals($set->getDeadlinePeriod(),0);
		$this->assertNull($set->getDeadlineDate());
		$this->assertEquals($set->getValidityOfQualificationPeriod(),ilStudyProgrammeSettings::NO_VALIDITY_OF_QUALIFICATION_PERIOD);
		$this->assertNull($set->getValidityOfQualificationDate());
		$this->assertEquals($set->getRestartPeriod(),ilStudyProgrammeSettings::NO_RESTART);

		$set->setSubtypeId(123)
			->setStatus(ilStudyProgrammeSettings::STATUS_ACTIVE)
			->setLPMode(ilStudyProgrammeSettings::MODE_POINTS)
			->setPoints(10)
			->setDeadlinePeriod(10)
			->setValidityOfQualificationPeriod(20)
			->setRestartPeriod(30);
		$repo->update($set);
		ilStudyProgrammeSettingsDBRepository::clearCache();
		$set = $repo->read(-1);
		$this->assertEquals($set->getSubtypeId(),123);
		$this->assertEquals($set->getStatus(),ilStudyProgrammeSettings::STATUS_ACTIVE);
		$this->assertEquals($set->getLPMode(),ilStudyProgrammeSettings::MODE_POINTS);
		$this->assertEquals($set->getPoints(),10);
		$this->assertEquals($set->getDeadlinePeriod(),10);
		$this->assertNull($set->getDeadlineDate());
		$this->assertEquals($set->getValidityOfQualificationPeriod(),20);
		$this->assertNull($set->getValidityOfQualificationDate());
		$this->assertEquals($set->getRestartPeriod(),30);

		$set->setSubtypeId(123)
			->setDeadlineDate(new DateTime())
			->setValidityOfQualificationDate(DateTime::createFromFormat('Ymd','20200101'))
			->setRestartPeriod(ilStudyProgrammeSettings::NO_RESTART);
		$repo->update($set);
		ilStudyProgrammeSettingsDBRepository::clearCache();
		$set = $repo->read(-1);
		$this->assertEquals($set->getDeadlinePeriod(),0);
		$this->assertEquals($set->getDeadlineDate()->format('Ymd'),(new DateTime())->format('Ymd'));
		$this->assertEquals($set->getValidityOfQualificationDate()->format('Ymd'),'20200101');
		$this->assertEquals($set->getValidityOfQualificationPeriod(),ilStudyProgrammeSettings::NO_VALIDITY_OF_QUALIFICATION_PERIOD);
		$this->assertEquals($set->getRestartPeriod(),ilStudyProgrammeSettings::NO_RESTART);

		$repo = new ilStudyProgrammeSettingsDBRepository(
			$this->db,
			$this->tps
		);
		ilStudyProgrammeSettingsDBRepository::clearCache();
		$set = $repo->read(-1);
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
		$repo = new ilStudyProgrammeSettingsDBRepository(
			$this->db,
			$this->tps
		);
		$set = $repo->read(-1);
		$this->assertEquals($set->getSubtypeId(),123);
		$this->assertEquals($set->getStatus(),ilStudyProgrammeSettings::STATUS_ACTIVE);
		$this->assertEquals($set->getLPMode(),ilStudyProgrammeSettings::MODE_POINTS);
		$this->assertEquals($set->getPoints(),10);
		$repo->delete($set);
		$repo->read(-1);
	}


}