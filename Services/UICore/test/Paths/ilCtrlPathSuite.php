<?php

use PHPUnit\Framework\TestSuite;

/**
 * Class ilCtrlPathSuite
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlPathSuite extends TestSuite
{
    public static function suite() : self
    {
        $suite = new self();

        require_once __DIR__ . '/ilCtrlArrayClassPathTest.php';
        $suite->addTestSuite(ilCtrlArrayClassPathTest::class);

        require_once __DIR__ . '/ilCtrlSingleClassPathTest.php';
        $suite->addTestSuite(ilCtrlSingleClassPathTest::class);

        return $suite;
    }
}