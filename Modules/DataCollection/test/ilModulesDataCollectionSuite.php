<?php

use PHPUnit\Framework\TestSuite;

/**
 * Class ilModulesDataCollectionSuite
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilModulesDataCollectionSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new ilModulesDataCollectionSuite();

        // add each test class of the component
        require_once("./Modules/DataCollection/test/ilObjDataCollectionTest.php");

        $suite->addTestSuite("ilObjDataCollectionTest");

        return $suite;
    }
}