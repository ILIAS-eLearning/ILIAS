<?php

include_once 'Modules/Course/classes/class.ilCourseMailTemplateTutorContext.php';

class ilCourseMailTemplateTutorContextTest extends \PHPUnit_Framework_TestCase
{
	public function testNonExistingPlaceholderWontBeResolved()
	{
		$mailTemplateContext = new ilCourseMailTemplateTutorContext();

		$result = $mailTemplateContext->resolveSpecificPlaceholder('TEST_PLACEHOLDER', array());

		$this->assertEquals($result, '');
	}
}
