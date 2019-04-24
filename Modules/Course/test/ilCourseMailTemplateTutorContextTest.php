<?php

use PHPUnit\Framework\TestCase;

include_once 'Modules/Course/classes/class.ilCourseMailTemplateTutorContext.php';

/**
 * Class ilCourseMailTemplateTutorContextTest
 * @group needsInstalledILIAS
 */
class ilCourseMailTemplateTutorContextTest extends TestCase
{
	public function testNonExistingPlaceholderWontBeResolved()
	{
		$mailTemplateContext = new ilCourseMailTemplateTutorContext();

		$result = $mailTemplateContext->resolveSpecificPlaceholder('TEST_PLACEHOLDER', array());

		$this->assertEquals($result, '');
	}
}
