<?php

declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

/**
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilServicesUICoreSuite extends TestSuite
{
    /**
     * @return self
     */
    public static function suite(): self
    {
        $suite = new self();

        require_once __DIR__ . '/Iterator/ilCtrlIteratorSuite.php';
        $suite->addTestSuite(ilCtrlIteratorSuite::class);

        require_once __DIR__ . '/Paths/ilCtrlPathSuite.php';
        $suite->addTestSuite(ilCtrlPathSuite::class);

        require_once __DIR__ . '/Setup/ilCtrlSetupSuite.php';
        $suite->addTestSuite(ilCtrlSetupSuite::class);

        require_once __DIR__ . '/Structure/ilCtrlStructureSuite.php';
        $suite->addTestSuite(ilCtrlStructureSuite::class);

        require_once __DIR__ . '/Token/ilCtrlTokenSuite.php';
        $suite->addTestSuite(ilCtrlTokenSuite::class);

        require_once __DIR__ . '/ilCtrlContextTest.php';
        $suite->addTestSuite(ilCtrlContextTest::class);

        require_once __DIR__ . '/ilCtrlTest.php';
        $suite->addTestSuite(ilCtrlTest::class);

        return $suite;
    }
}
