<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;
use ILIAS\Modules\Test\test\AccessFileUploadAnswerTest;
use ILIAS\Modules\Test\test\AccessFileUploadPreviewTest;
use ILIAS\Modules\Test\test\AccessQuestionImageTest;
use ILIAS\Modules\Test\test\ReadableTest;
use ILIAS\Modules\Test\test\IncidentTest;

class ilModulesTestSuite extends TestSuite
{
    public static function suite()
    {
        if (defined('ILIAS_PHPUNIT_CONTEXT')) {
            include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
            ilUnitUtil::performInitialisation();
        } else {
            chdir(dirname(__FILE__));
            chdir('../../../');
        }

        $suite = new ilModulesTestSuite();

        $class_list = [
            ilassMarkTest::class => 'ilassMarkTest.php',
            ilassMarkSchemaTest::class => 'ilassMarkSchemaTest.php',
            ilTestFinalMarkLangVarBuilderTest::class => 'ilTestFinalMarkLangVarBuilderTest.php',
            AccessFileUploadAnswerTest::class => 'AccessFileUploadAnswerTest.php',
            AccessFileUploadPreviewTest::class => 'AccessFileUploadPreviewTest.php',
            AccessQuestionImageTest::class => 'AccessQuestionImageTest.php',
            IncidentTest::class => 'IncidentTest.php',
            ReadableTest::class => 'ReadableTest.php',
        ];

        foreach ($class_list as $class => $file) {
            include_once('./Modules/Test/test/' . $file);
            $suite->addTestSuite($class);
        }

        return $suite;
    }
}
