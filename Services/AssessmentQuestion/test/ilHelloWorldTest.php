<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/AssessmentQuestion/test/ilAbstractAssessmentQuestionUnitTest.php';

/**
 * Class HelloWorldTest
 *
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Services/AssessmentQuestion
 */
class ilHelloWorldTest extends ilAbstractAssessmentQuestionUnitTest
{
	public function testHelloWorld()
	{
		$expected = 'Hello World!';
		$actual = 'Hello World!';
		
		$this->assertEquals($expected, $actual);
	}
	
	/**
	 * @expectedException Exception
	 */
	public function testThrowsException()
	{
		throw new Exception();
	}
}
