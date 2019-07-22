<?php

/**
 * @group needsInstalledILIAS
 */
class ilStudyProgrammeTypeTranslationTest extends PHPUnit_Framework_TestCase
{
	protected $backupGlobals = FALSE;

	public function test_init_and_id()
	{
		$tt = new ilStudyProgrammeTypeTranslation(123);
		$this->assertEquals($tt->getId(),123);
		return $tt;
	}

	/**
	 * @depends test_init_and_id
	 */
	public function test_prg_type_id($tt)
	{
		$this->assertEquals(0,$tt->getPrgTypeId());
		$tt->setPrgTypeId(123);
		$this->assertEquals(123,$tt->getPrgTypeId());
	}


	/**
	 * @depends test_init_and_id
	 */
	public function test_lang($tt)
	{
		$this->assertEquals('',$tt->getLang());
		$tt->setLang('de');
		$this->assertEquals('de',$tt->getLang());
	}

	/**
	 * @depends test_init_and_id
	 */
	public function test_member($tt)
	{
		$this->assertEquals('',$tt->getMember());
		$tt->setMember('a_member');
		$this->assertEquals('a_member',$tt->getMember());
	}

	/**
	 * @depends test_init_and_id
	 */
	public function test_value($tt)
	{
		$this->assertEquals('',$tt->getValue());
		$tt->setValue('a_value');
		$this->assertEquals('a_value',$tt->getValue());
	}
}