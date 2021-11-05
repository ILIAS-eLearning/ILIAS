<?php

use PHPUnit\Framework\TestSuite;

/**
 * Class ilCtrlSetupSuite
 *
 * @author  Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlSetupSuite extends TestSuite
{
    public static function suite() : self
    {
        $suite = new self();

        require_once __DIR__ . '/ilCtrlStructureReaderTest.php';
        $suite->addTestSuite(ilCtrlStructureReaderTest::class);

        return $suite;
    }
}