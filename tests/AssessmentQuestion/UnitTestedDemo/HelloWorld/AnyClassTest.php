<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\AssessmentQuestion\UnitTestedDemo\HelloWorld;

use ILIAS\AssessmentQuestion\AbstractBaseUnitTest;

require_once('./libs/composer/vendor/autoload.php');

/**
 * Class AnyClassTest
 *
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Services/AssessmentQuestion
 *
 * @group		ServicesAssessmentQuestion
 */
class AnyClassTest extends AbstractBaseUnitTest
{
	/**
	 * @test
	 */
	public function helloWorldIsEqualToHelloWorld()
	{
		$expected = 'Hello World!';
		$actual = 'Hello World!';
		
		$this->assertEquals($expected, $actual);
	}
	
	/**
	 * @test
	 *
	 * @expectedException \Exception
	 * @expectedExceptionCode 4711
	 * @expectedExceptionMessage Hello World!
	 */
	public function throwsExcpetion()
	{
		throw new \Exception('Hello World!', 4711);
	}
}
