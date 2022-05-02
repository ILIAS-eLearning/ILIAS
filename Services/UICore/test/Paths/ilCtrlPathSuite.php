<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

/**
 * Class ilCtrlPathSuite
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlPathSuite extends TestSuite
{
    /**
     * @return self
     */
    public static function suite() : self
    {
        $suite = new self();

        require_once __DIR__ . '/ilCtrlAbstractPathTest.php';
        $suite->addTestSuite(ilCtrlAbstractPathTest::class);

        require_once __DIR__ . '/ilCtrlArrayClassPathTest.php';
        $suite->addTestSuite(ilCtrlArrayClassPathTest::class);

        require_once __DIR__ . '/ilCtrlExistingPathTest.php';
        $suite->addTestSuite(ilCtrlExistingPathTest::class);

        require_once __DIR__ . '/ilCtrlPathFactoryTest.php';
        $suite->addTestSuite(ilCtrlPathFactoryTest::class);

        require_once __DIR__ . '/ilCtrlSingleClassPathTest.php';
        $suite->addTestSuite(ilCtrlSingleClassPathTest::class);

        return $suite;
    }
}
