<?php

class ilStudyProgrammeAutoMembershipsSourceTest extends PHPUnit_Framework_TestCase
{
	protected $backupGlobals = FALSE;

	public function setUp()
	{
		PHPUnit_Framework_Error_Deprecated::$enabled = FALSE;
		$this->prg_obj_id = 123;
		$this->source_type = ilStudyProgrammeAutoMembershipSource::TYPE_ROLE;
		$this->source_id = 666;
		$this->enbl = true;
		$this->usr_id = 6;
		$this->dat = new DateTimeImmutable('2019-06-05 15:25:12');
	}

	public function testConstruction()
	{
		$ams = new ilStudyProgrammeAutoMembershipSource(
			$this->prg_obj_id,
			$this->source_type,
			$this->source_id,
			$this->enbl,
			$this->usr_id,
			$this->dat
		);
		$this->assertInstanceOf(
			ilStudyProgrammeAutoMembershipSource::class,
			$ams
		);
		return $ams;
	}

	/**
	 * @depends testConstruction
	 */
	public function testGetPrgObjId($ams)
	{
		$this->assertEquals(
			$this->prg_obj_id,
			$ams->getPrgObjId()
		);
	}

	/**
	 * @depends testConstruction
	 */
	public function testGetSourceType($ams)
	{
		$this->assertEquals(
			$this->source_type,
			$ams->getSourceType()
		);
	}
	/**
	 * @depends testConstruction
	 */
	public function testGetSourceId($ams)
	{
		$this->assertEquals(
			$this->source_id,
			$ams->getSourceId()
		);
	}

	/**
	 * @depends testConstruction
	 */
	public function testGetLastEditorId($ams)
	{
		$this->assertEquals(
			$this->usr_id,
			$ams->getLastEditorId()
		);
	}

	/**
	 * @depends testConstruction
	 */
	public function testGetLastEdited($ams)
	{
		$this->assertEquals(
			$this->dat,
			$ams->getLastEdited()
		);
	}
}