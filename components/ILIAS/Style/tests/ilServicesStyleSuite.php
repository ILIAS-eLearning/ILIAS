<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

/**
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id$*
 */

class ilServicesStyleSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new ilServicesStyleSuite();

        // add each test class of the component
        require_once(__DIR__ . "/../System/test/ilServicesStyleSystemSuite.php");
        require_once(__DIR__ . "/../Content/test/ilServicesStyleContentSuite.php");
        $suite->addTestSuite(ilServicesStyleSystemSuite::class);
        $suite->addTestSuite("ilServicesStyleContentSuite");
        return $suite;
    }
}
