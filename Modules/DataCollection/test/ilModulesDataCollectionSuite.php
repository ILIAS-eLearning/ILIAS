<?php

/**
 * Class ilModulesDataCollectionSuite
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilModulesDataCollectionSuite extends PHPUnit_Framework_TestSuite
{
    public static function suite()
    {
        PHPUnit_Framework_Error_Deprecated::$enabled = false;

        $suite = new ilModulesDataCollectionSuite();

        // add each test class of the component
        require_once("./Modules/DataCollection/test/ilObjDataCollectionTest.php");

        $suite->addTestSuite("ilObjDataCollectionTest");

        return $suite;
    }
}
