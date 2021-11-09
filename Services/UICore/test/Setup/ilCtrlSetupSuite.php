<?php declare(strict_types = 1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

/**
 * Class ilCtrlSetupSuite
 *
 * @author  Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlSetupSuite extends TestSuite
{
    /**
     * @return self
     */
    public static function suite() : self
    {
        $suite = new self();

        require_once __DIR__ . '/Iterator/ilCtrlArrayIteratorTest.php';
        $suite->addTestSuite(ilCtrlArrayIteratorTest::class);

        require_once __DIR__ . '/Iterator/ilCtrlDirectoryIteratorTest.php';
        $suite->addTestSuite(ilCtrlDirectoryIteratorTest::class);

        require_once __DIR__ . '/Iterator/ilCtrlPluginIteratorTest.php';
        $suite->addTestSuite(ilCtrlPluginIteratorTest::class);

        require_once __DIR__ . '/Artifact/ilCtrlStructureCidGeneratorTest.php';
        $suite->addTestSuite(ilCtrlStructureCidGeneratorTest::class);

        require_once __DIR__ . '/Artifact/ilCtrlStructureReaderTest.php';
        $suite->addTestSuite(ilCtrlStructureReaderTest::class);

        return $suite;
    }
}