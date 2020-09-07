<?php
/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

/**
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id$*
 */

class ilServicesStyleSuite extends PHPUnit_Framework_TestSuite
{
    public static function suite()
    {
        $suite = new ilServicesStyleSuite();

        // add each test class of the component
        include_once("./Services/Style/System/test/ilServicesStyleSystemSuite.php");
        $suite->addTestSuite("ilServicesStyleSystemSuite");
        return $suite;
    }
}
