<?php

class ilStudyProgrammeAutoCategoryTest extends PHPUnit_Framework_TestCase
{
	//protected $backupGlobals = FALSE;

	public function setUp()
	{
		PHPUnit_Framework_Error_Deprecated::$enabled = FALSE;
		$this->obj_id = 123;
		$this->cat_ref_id = 666;
		$this->title = 'some title';
		$this->usr_id = 6;
		$this->dat = new DateTimeImmutable('2019-06-05 15:25:12');
	}

	public function testConstruction()
	{
		$ac = new ilStudyProgrammeAutoCategory(
			$this->obj_id,
			$this->cat_ref_id,
			$this->title,
			$this->usr_id,
			$this->dat
		);
		$this->assertInstanceOf(
			ilStudyProgrammeAutoCategory::class,
			$ac
		);
		return $ac;
	}

	/**
	 * @depends testConstruction
	 */
	public function testGetObjId($ac)
	{
		$this->assertEquals(
			$this->obj_id,
			$ac->getObjId()
		);
	}

	/**
	 * @depends testConstruction
	 */
	public function testGetCategoryRefId($ac)
	{
		$this->assertEquals(
			$this->cat_ref_id,
			$ac->getCategoryRefId()
		);
	}

	/**
	 * @depends testConstruction
	 */
	public function testGetTitle($ac)
	{
		$this->assertEquals(
			$this->title,
			$ac->getTitle()
		);
	}

	/**
	 * @depends testConstruction
	 */
	public function testGetLastEditorId($ac)
	{
		$this->assertEquals(
			$this->usr_id,
			$ac->getLastEditorId()
		);
	}

	/**
	 * @depends testConstruction
	 */
	public function testGetLastEdited($ac)
	{
		$this->assertEquals(
			$this->dat,
			$ac->getLastEdited()
		);
	}
}
