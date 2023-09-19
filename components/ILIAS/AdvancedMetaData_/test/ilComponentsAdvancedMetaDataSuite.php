<?php

use PHPUnit\Framework\TestSuite as TestSuite;

/**
 * Test suite for advanced meta data
 */
class ilComponentsAdvancedMetaDataSuite extends TestSuite
{
    public static function suite(): self
    {
        $suite = new self();

        include_once './components/ILIAS/AdvancedMetaData_/test/record/ilAdvancedMDRecordObjectOrderingsTest.php';
        $suite->addTestSuite(ilAdvancedMDRecordObjectOrderingsTest::class);

        return $suite;
    }
}
