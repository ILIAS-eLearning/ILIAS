<?php

/**
 * @group needsInstalledILIAS
 */
class ilStudyProgrammeAssignmentTest extends PHPUnit_Framework_TestCase
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
		$spa = new ilStudyProgrammeAssignment(123);
		$this->assertEquals($spa->getId(),123);
		return $spa;
	}

	/**
	 * @depends test_init_and_id
	 */
	public function test_root_id()
	{
		$spa = (new ilStudyProgrammeAssignment(123))->setRootId(321);
		$this->assertEquals($spa->getRootId(),321);
	}

	/**
	 * @depends test_init_and_id
	 */
	public function test_user_id()
	{
		$spa = (new ilStudyProgrammeAssignment(123))->setUserId(321);
		$this->assertEquals($spa->getUserId(),321);
	}

	/**
	 * @depends test_init_and_id
	 */
	public function test_last_change_by()
	{
		$spa = (new ilStudyProgrammeAssignment(123))->setLastChangeBy(6);
		$this->assertEquals($spa->getLastChangeBy(),6);
	}

	/**
	 * @depends test_init_and_id
	 * @expectedException ilException
	 */
	public function test_last_change_by_invalid()
	{
		$spa = (new ilStudyProgrammeAssignment(123))->setLastChangeBy(-1);
	}

	/**
	 * @depends test_init_and_id
	 */
	public function test_last_change()
	{
		$dl = new DateTime();
		$spa = (new ilStudyProgrammeAssignment(123))->setLastChange($dl);
		$this->assertEquals($spa->getLastChange()->format('Y-m-d H:i:s'),$dl->format('Y-m-d H:i:s'));
	}
}