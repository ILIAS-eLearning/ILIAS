<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

class ilServicesTrackingSuite extends TestSuite
{
    public static function suite() : self
    {
        $suite = new self();
        include_once './Services/Tracking/test/ilTrackingCollectionTest.php';
        $suite->addTestSuite(ilTrackingCollectionTest::class);
        return $suite;
    }
}
