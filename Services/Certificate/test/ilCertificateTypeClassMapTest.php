<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once 'Services/Certificate/classes/Cron/class.ilCertificateTypeClassMap.php';
require_once 'Services/Exceptions/classes/class.ilException.php';

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateTypeClassMapTest extends ilCertificateBaseTestCase
{
	/**
	 * @var
	 */
	private $classMap;

	public function setUp(): void
	{
		$this->classMap = new ilCertificateTypeClassMap();
	}

	public function testFetchCoursePlaceHolderClass()
	{
		$class = $this->classMap->getPlaceHolderClassNameByType('crs');

		$this->assertEquals('ilCoursePlaceholderValues', $class);
	}

	public function testFetchTestPlaceHolderClass()
	{
		$class = $this->classMap->getPlaceHolderClassNameByType('tst');

		$this->assertEquals('ilTestPlaceHolderValues', $class);
	}

	public function testFetchExercisePlaceHolderClass()
	{
		$class = $this->classMap->getPlaceHolderClassNameByType('exc');

		$this->assertEquals('ilExercisePlaceHolderValues', $class);
	}

	public function testFetchScormPlaceHolderClass()
	{
		$class = $this->classMap->getPlaceHolderClassNameByType('sahs');

		$this->assertEquals('ilScormPlaceholderValues', $class);
	}

	/**
	 * 
	 */
	public function testFetchUnknownClassWillResultInException()
	{
		$this->expectException(\ilException::class);

		$class = $this->classMap->getPlaceHolderClassNameByType('something');

		$this->fail('Should never happen. No Exception thrown?');
	}

	public function testIsCourseExisting()
	{
		$result = $this->classMap->typeExistsInMap('crs');

		$this->assertTrue($result);
	}

	public function testIsTestExisting()
	{
		$result = $this->classMap->typeExistsInMap('tst');

		$this->assertTrue($result);
	}

	public function testIsExerciseExisting()
	{
		$result = $this->classMap->typeExistsInMap('exc');

		$this->assertTrue($result);
	}

	public function testUnknownTypeIsNotExisting()
	{
		$result = $this->classMap->typeExistsInMap('something');

		$this->assertFalse($result);
	}
}
