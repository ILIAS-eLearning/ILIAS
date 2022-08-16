<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;
use ILIAS\Modules\Test\test\CanAccessFileUploadAnswerTest;

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

        include_once("./Modules/Test/test/CanAccessFileUploadAnswerTest.php");
        $suite->addTestSuite(CanAccessFileUploadAnswerTest::class);
                
        return $suite;
    }
}
