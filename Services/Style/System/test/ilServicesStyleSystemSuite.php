<?php
/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */




/**
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id$*
 */

class ilServicesStyleSystemSuite extends PHPUnit_Framework_TestSuite
{
    public static function suite()
    {
        $suite = new ilServicesStyleSystemSuite();

        $base_dir = "./Services/Style/System/test/";

        $dir = opendir($base_dir);
        while ($file = readdir($dir)) {
            if (strpos($file, 'Test.php') !== false) {
                include_once($base_dir . $file);
                $test_class = str_replace(".php", "", $file);
                $suite->addTestSuite($test_class);
            }
        }
        return $suite;
    }
}
