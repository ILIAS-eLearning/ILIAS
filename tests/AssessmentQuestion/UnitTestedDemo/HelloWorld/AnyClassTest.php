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
	public function testAnyClassGetsCorrectAnyMemberDefault()
	{
		$expected = 'Hello World!';
		
		$any = new AnyClass();
		$actual = $any->getAnyMember();
		
		$this->assertEquals($expected, $actual);
	}
	
	public function testAnyClassGetsCorrectAnyMemberAfterSet()
	{
		$expected = 'Another World :)';
		
		$any = new AnyClass();
		$any->setAnyMember($expected);
		$actual = $any->getAnyMember();
		
		$this->assertEquals($expected, $actual);
	}
}
