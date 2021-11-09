<?php declare(strict_types = 1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

/**
 * Class ilServicesUICoreSuite
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilServicesUICoreSuite extends TestSuite
{
    /**
     * @return self
     */
    public static function suite() : self
    {
        $suite = new self();

        require_once __DIR__ . '/Setup/ilCtrlSetupSuite.php';
        $suite->addTestSuite(ilCtrlSetupSuite::class);

        require_once __DIR__ . '/Paths/ilCtrlPathSuite.php';
        $suite->addTestSuite(ilCtrlPathSuite::class);

        return $suite;
    }
}