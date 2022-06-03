<?php

use PHPUnit\Framework\TestSuite;

/**
 * Class ilModulesDataCollectionSuite
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilModulesDataCollectionSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new ilModulesDataCollectionSuite();

        return $suite;
    }
}
