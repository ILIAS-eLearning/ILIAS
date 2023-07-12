<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;
use ILIAS\Modules\Test\test\AccessFileUploadAnswerTest;
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

        include_once("./Modules/Test/test/ilassMarkTest.php");
        $suite->addTestSuite("ilassMarkTest");

        include_once("./Modules/Test/test/ilassMarkSchemaTest.php");
        $suite->addTestSuite("ilassMarkSchemaTest");

        include_once("./Modules/Test/test/ilTestFinalMarkLangVarBuilderTest.php");
        $suite->addTestSuite("ilTestFinalMarkLangVarBuilderTest");

        include_once("./Modules/Test/test/AccessFileUploadAnswerTest.php");
        $suite->addTestSuite(AccessFileUploadAnswerTest::class);

        include_once("./Modules/Test/test/AccessQuestionImageTest.php");
        $suite->addTestSuite(AccessQuestionImageTest::class);

        include_once("./Modules/Test/test/IncidentTest.php");
        $suite->addTestSuite(IncidentTest::class);

        include_once("./Modules/Test/test/ReadableTest.php");
        $suite->addTestSuite(ReadableTest::class);

        return $suite;
    }
}
