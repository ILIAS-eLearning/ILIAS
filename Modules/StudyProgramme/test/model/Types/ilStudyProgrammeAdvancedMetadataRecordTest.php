<?php

/**
 * @group needsInstalledILIAS
 */
class ilStudyProgrammeAdvancedMetadataRecordTest extends PHPUnit_Framework_TestCase
{
	protected $backupGlobals = FALSE;

	public function test_init_and_id()
	{
		$amd = new ilStudyProgrammeAdvancedMetadataRecord(123);
		$this->assertEquals($amd->getId(),123);
		return $amd;
	}

	/**
	 * @depends test_init_and_id
	 */
	public function test_type_id($amd)
	{
		$this->assertEquals(0,$amd->getTypeId());
		$amd->setTypeId(123);
		$this->assertEquals(123,$amd->getTypeId());
	}

	/**
	 * @depends test_init_and_id
	 */
	public function test_rec_id($amd)
	{
		$this->assertEquals(0,$amd->getRecId());
		$amd->setRecId(321);
		$this->assertEquals(321,$amd->getRecId());
	}
}