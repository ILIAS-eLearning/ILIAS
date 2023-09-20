<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

class ilComponentsTrackingSuite extends TestSuite
{
    public static function suite(): self
    {
        $suite = new self();
        include_once substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . '/components/ILIAS/Tracking_/test/ilTrackingCollectionTest.php';
        $suite->addTestSuite(ilTrackingCollectionTest::class);
        return $suite;
    }
}
