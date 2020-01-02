<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilServicesUICoreSuite extends PHPUnit_Framework_TestSuite
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

        $suite = new ilServicesUICoreSuite();
    
        include_once("./Services/UICore/test/ilTemplateTest.php");
        $suite->addTestSuite("ilTemplateTest");

        return $suite;
    }
}
