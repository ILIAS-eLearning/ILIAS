<?php

/**
 * @group needsInstalledILIAS
 */
class ilStudyProgrammeSettingsTest extends PHPUnit_Framework_TestCase
{
	protected $backupGlobals = FALSE;

	public function setUp()
	{
		PHPUnit_Framework_Error_Deprecated::$enabled = FALSE;

		global $DIC;
		if(!$DIC) {
			include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
			try {
				ilUnitUtil::performInitialisation();
			} catch(\Exception $e) {}
		}
	}


	public function test_init_and_id()
	{
		$sps = new ilStudyProgrammeSettings(123);
		$this->assertEquals($sps->getObjId(),123);
		return $sps;
	}

	/**
	 * @depends test_init_and_id
	 */
	public function test_subtype_id($sps)
	{
		$this->assertEquals($sps->setSubtypeId(2)->getSubtypeId(),2);
	}

	/**
	 * @depends test_init_and_id
	 */
	public function test_points($sps)
	{
		$this->assertEquals($sps->setPoints(3)->getPoints(),3);
	}

	/**
	 * @depends test_init_and_id
	 * @expectedException ilException
	 */
	public function test_wrong_points($sps)
	{
		$sps->setPoints(-3);
	}

	/**
	 * @depends test_init_and_id
	 */
	public function test_lp_mode($sps)
	{
		$this->assertEquals(
			$sps->setLPMode(ilStudyProgrammeSettings::MODE_UNDEFINED)->getLPMode()
			,ilStudyProgrammeSettings::MODE_UNDEFINED
		);
		$this->assertEquals(
			$sps->setLPMode(ilStudyProgrammeSettings::MODE_POINTS)->getLPMode()
			,ilStudyProgrammeSettings::MODE_POINTS
		);
		$this->assertEquals(
			$sps->setLPMode(ilStudyProgrammeSettings::MODE_LP_COMPLETED)->getLPMode()
			,ilStudyProgrammeSettings::MODE_LP_COMPLETED
		);
	}

	/**
	 * @depends test_init_and_id
	 * @expectedException ilException
	 */
	public function test_wrong_lp_mode($sps)
	{
		$sps->setLPMode(4)->getLPMode();
	}

	/**
	 * @depends test_init_and_id
	 */
	public function test_status($sps)
	{
		$this->assertEquals(
			$sps->setStatus(ilStudyProgrammeSettings::STATUS_DRAFT)->getStatus()
			,ilStudyProgrammeSettings::STATUS_DRAFT
		);
		$this->assertEquals(
			$sps->setStatus(ilStudyProgrammeSettings::STATUS_ACTIVE)->getStatus()
			,ilStudyProgrammeSettings::STATUS_ACTIVE
		);
		$this->assertEquals(
			$sps->setStatus(ilStudyProgrammeSettings::STATUS_OUTDATED)->getStatus()
			,ilStudyProgrammeSettings::STATUS_OUTDATED
		);
	}

	/**
	 * @depends test_init_and_id
	 * @expectedException ilException
	 */
	public function test_wrong_status($sps)
	{
		$sps->setStatus(41);
	}

	/**
	 * @depends test_init_and_id
	 */
	public function test_deadline_period($sps)
	{
		$this->assertEquals($sps->setDeadlinePeriod(10)->getDeadlinePeriod(),10);
		$this->assertEquals($sps->setDeadlineDate(new \DateTime())->getDeadlinePeriod(),0);
	}

	/**
	 * @depends test_init_and_id
	 */
	public function test_deadline_date($sps)
	{
		$this->assertEquals($sps->setDeadlineDate(new \DateTime())->getDeadlineDate()->format('Ymd'),(new \DateTime())->format('Ymd'));
		$this->assertNull($sps->setDeadlinePeriod(10)->getDeadlineDate());
	}

	/**
	 * @depends test_init_and_id
	 */
	public function test_validity_qualification_period($sps)
	{
		$this->assertEquals($sps->setValidityOfQualificationPeriod(10)->getValidityOfQualificationPeriod(),10);
		$this->assertEquals($sps->setValidityOfQualificationDate(new \DateTime())->getValidityOfQualificationPeriod(),ilStudyProgrammeSettings::NO_VALIDITY_OF_QUALIFICATION_PERIOD);
	}

	/**
	 * @depends test_init_and_id
	 */
	public function test_validity_qualification_date($sps)
	{
		$this->assertEquals($sps->setValidityOfQualificationDate(new \DateTime())->getValidityOfQualificationDate()->format('Ymd'),(new \DateTime())->format('Ymd'));
		$this->assertNull($sps->setValidityOfQualificationPeriod(10)->getValidityOfQualificationDate());
	}

	/**
	 * @depends test_init_and_id
	 * @expectedException ilException
	 */
	public function test_wrong_vq_peiod($sps)
	{
		$sps->setValidityOfQualificationPeriod(-10);
	}

	/**
	 * @depends test_init_and_id
	 */
	public function test_restart_period($sps)
	{
		$this->assertEquals($sps->setRestartPeriod(10)->getRestartPeriod(),10);
	}

	/**
	 * @depends test_init_and_id
	 * @expectedException ilException
	 */
	public function test_wrong_restart_period($sps)
	{
		$sps->setRestartPeriod(-10);
	}
}