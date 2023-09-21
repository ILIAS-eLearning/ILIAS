<?php

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

/**
 * class ServicesCalendarSuite
 *
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */

class ilServicesCalendarSuite extends TestSuite
{
    public static function suite(): self
    {
        $suite = new ilServicesCalendarSuite();
        include_once './components/ILIAS/Calendar_/test/class.ilCalendarRecurrenceCalculationTest.php';
        $suite->addTestSuite(ilCalendarRecurrenceCalculationTest::class);
        return $suite;
    }
}
