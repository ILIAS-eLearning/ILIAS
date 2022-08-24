<?php

declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

/**
 * Class ilCtrlStructureSuite
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlStructureSuite extends TestSuite
{
    /**
     * @return static
     */
    public static function suite(): self
    {
        $suite = new self();

        require_once __DIR__ . '/ilCtrlStructureCidGeneratorTest.php';
        $suite->addTestSuite(ilCtrlStructureCidGeneratorTest::class);

        require_once __DIR__ . '/ilCtrlStructureHelperTest.php';
        $suite->addTestSuite(ilCtrlStructureHelperTest::class);

        require_once __DIR__ . '/ilCtrlStructureMapperTest.php';
        $suite->addTestSuite(ilCtrlStructureMapperTest::class);

        require_once __DIR__ . '/ilCtrlStructureReaderTest.php';
        $suite->addTestSuite(ilCtrlStructureReaderTest::class);

        require_once __DIR__ . '/ilCtrlStructureTest.php';
        $suite->addTestSuite(ilCtrlStructureTest::class);

        return $suite;
    }
}
