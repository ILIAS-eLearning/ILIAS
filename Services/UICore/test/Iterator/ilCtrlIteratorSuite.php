<?php declare(strict_types = 1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

/**
 * Class ilCtrlIteratorSuite
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlIteratorSuite extends TestSuite
{
    /**
     * @return self
     */
    public static function suite() : self
    {
        $suite = new self();

        require_once __DIR__ . '/ilCtrlArrayIteratorTest.php';
        $suite->addTestSuite(ilCtrlArrayIteratorTest::class);

        return $suite;
    }
}
