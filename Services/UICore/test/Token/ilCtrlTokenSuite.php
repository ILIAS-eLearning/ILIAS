<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

/**
 * Class ilCtrlTokenSuite
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlTokenSuite extends TestSuite
{
    /**
     * @return self
     */
    public static function suite() : self
    {
        $suite = new self();

        require_once __DIR__ . '/ilCtrlTokenTest.php';
        $suite->addTestSuite(ilCtrlTokenTest::class);

        require_once __DIR__ . '/ilCtrlTokenRepositoryTest.php';
        $suite->addTestSuite(ilCtrlTokenRepositoryTest::class);

        return $suite;
    }
}
