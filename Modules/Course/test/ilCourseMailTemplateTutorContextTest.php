<?php

use PHPUnit\Framework\TestCase;


/**
 * Class ilCourseMailTemplateTutorContextTest
 */
class ilCourseMailTemplateTutorContextTest //extends TestCase
{
    public function testNonExistingPlaceholderWontBeResolved()
    {
        $mailTemplateContext = new ilCourseMailTemplateTutorContext();

        $result = $mailTemplateContext->resolveSpecificPlaceholder('TEST_PLACEHOLDER', array());

        $this->assertEquals($result, '');
    }
}
