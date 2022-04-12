<?php

use PHPUnit\Framework\TestSuite as TestSuite;

/**
 * Test suite for advanced meta data
 */
class ilServicesAdvancedMetaDataSuite extends TestSuite
{
    public static function suite() : self
    {
        $suite = new self();

        include_once './Services/AdvancedMetaData/test/record/ilAdvancedMDRecordObjectOrderingsTest.php';
        $suite->addTestSuite(ilAdvancedMDRecordObjectOrderingsTest::class);

        return $suite;
    }
}
