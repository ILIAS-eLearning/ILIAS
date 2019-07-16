<?php

/**
 * @group needsInstalledILIAS
 */
class ilStudyProgrammeProgressTest extends PHPUnit_Framework_TestCase
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
		$spp = new ilStudyProgrammeProgress(123);
		$this->assertEquals($spp->getId(),123);
		return $spp;
	}

	/**
	 * @depends test_init_and_id
	 */
	public function test_assignmet_id()
	{
		$spp = (new ilStudyProgrammeProgress(123))->setAssignmentId(321);
		$this->assertEquals($spp->getAssignmentId(),321);
	}

	/**
	 * @depends test_init_and_id
	 */
	public function test_node_id()
	{
		$spp = (new ilStudyProgrammeProgress(123))->setNodeId(321);
		$this->assertEquals($spp->getNodeId(),321);
	}

	/**
	 * @depends test_init_and_id
	 */
	public function test_user_id()
	{
		$spp = (new ilStudyProgrammeProgress(123))->setUserId(321);
		$this->assertEquals($spp->getUserId(),321);
	}

	/**
	 * @depends test_init_and_id
	 */
	public function test_amount_of_points()
	{
		$spp = (new ilStudyProgrammeProgress(123))->setAmountOfPoints(321);
		$this->assertEquals($spp->getAmountOfPoints(),321);
	}

	/**
	 * @depends test_init_and_id
	 * @expectedException ilException
	 */
	public function test_amount_of_points_invalid()
	{
		$spp = (new ilStudyProgrammeProgress(123))->setAmountOfPoints(-321);
	}

	/**
	 * @depends test_init_and_id
	 */
	public function test_current_amount_of_points()
	{
		$spp = (new ilStudyProgrammeProgress(123))->setCurrentAmountOfPoints(321);
		$this->assertEquals($spp->getCurrentAmountOfPoints(),321);
	}

	public function status() {
		return [
			[1]
			,[2]
			,[3]
			,[4]
			,[5]
		];
	}

	/**
	 * @dataProvider status
	 */
	public function test_status($status)
	{
		$spp = (new ilStudyProgrammeProgress(123))->setStatus($status);
		$this->assertEquals($spp->getStatus(),$status);
	}

	/**
	 * @depends test_init_and_id
	 * @expectedException ilException
	 */
	public function test_status_invalid()
	{
		$spp = (new ilStudyProgrammeProgress(123))->setStatus(321);
	}

	/**
	 * @depends test_init_and_id
	 */
	public function test_completion_by()
	{
		$spp = (new ilStudyProgrammeProgress(123))->setCompletionBy(321);
		$this->assertEquals($spp->getCompletionBy(),321);
	}

	/**
	 * @depends test_init_and_id
	 */
	public function test_last_change_by()
	{
		$spp = (new ilStudyProgrammeProgress(123))->setLastChangeBy(6);
		$this->assertEquals($spp->getLastChangeBy(),6);
	}

	/**
	 * @depends test_init_and_id
	 * @expectedException ilException
	 */
	public function test_last_change_by_invalid()
	{
		$spp = (new ilStudyProgrammeProgress(123))->setLastChangeBy(-1);
	}

	/**
	 * @depends test_init_and_id
	 */
	public function test_assignmet_date()
	{
		$ad = new DateTime();
		$spp = (new ilStudyProgrammeProgress(123))->setAssignmentDate($ad);
		$this->assertEquals($spp->getAssignmentDate()->format('Y-m-d'),$ad->format('Y-m-d'));
	}

	/**
	 * @depends test_init_and_id
	 */
	public function test_completion_date()
	{
		$cd = new DateTime();
		$spp = (new ilStudyProgrammeProgress(123))->setCompletionDate($cd);
		$this->assertEquals($spp->getCompletionDate()->format('Y-m-d'),$cd->format('Y-m-d'));
		$spp = (new ilStudyProgrammeProgress(123))->setCompletionDate(null);
		$this->assertNull($spp->getCompletionDate());
	}

	/**
	 * @depends test_init_and_id
	 */
	public function test_deadline()
	{
		$dl = new DateTime();
		$spp = (new ilStudyProgrammeProgress(123))->setDeadline($dl);
		$this->assertEquals($spp->getDeadline()->format('Y-m-d'),$dl->format('Y-m-d'));
	}

	/**
	 * @depends test_init_and_id
	 */
	public function test_vq_date()
	{
		$dl = DateTime::createFromFormat('Ymd','20201011');
		$spp = (new ilStudyProgrammeProgress(123))->setValidityOfQualification($dl);
		$this->assertEquals($spp->getValidityOfQualification()->format('Ymd'),'20201011');
	}

	/**
	 * @depends test_vq_date
	 */
	public function test_invalidate() 
	{
		$spp = new ilStudyProgrammeProgress(123);
		$this->assertFalse($spp->isInvalidated());
		$past = DateTime::createFromFormat('Ymd','20180101');
		$spp->setValidityOfQualification($past);
		$spp->invalidate();
		$this->assertTrue($spp->isInvalidated());
	}

	/**
	 * @expectedException ilException
	 * @depends test_vq_date
	 */
	public function test_invalidate_non_expired_1()
	{
		$tomorrow = new DateTime();
		$tomorrow->add(new DateInterval('P1D'));
		$spp = (new ilStudyProgrammeProgress(123))->setValidityOfQualification($tomorrow)->invalidate();
	}

	/**
	 * @expectedException ilException
	 * @depends test_vq_date
	 */
	public function test_invalidate_non_expired_2()
	{
		$spp = (new ilStudyProgrammeProgress(123))->invalidate();
	}
}