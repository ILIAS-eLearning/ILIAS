<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

/**
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id$*
 */

class ilComponentsStyleSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new ilComponentsStyleSuite();
        // add each test class of the component
        $suite->addTestSuite(ilComponentsStyleSystemSuite::class);
        $suite->addTestSuite(ilComponentsStyleContentSuite::class);
        return $suite;
    }
}
