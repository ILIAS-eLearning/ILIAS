<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilServicesTrackingSuite extends PHPUnit_Framework_TestSuite
{
    public static function suite()
    {
        $suite = new ilServicesTrackingSuite();
        
        include_once("./Services/Tracking/test/ilTrackingTest.php");
        $suite->addTestSuite("ilTrackingTest");

        return $suite;
    }
}
