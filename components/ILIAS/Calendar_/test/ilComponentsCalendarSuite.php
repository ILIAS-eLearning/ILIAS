<?php

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

/**
 * class ServicesCalendarSuite
 *
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */

class ilComponentsCalendarSuite extends TestSuite
{
    public static function suite(): self
    {
        $suite = new ilComponentsCalendarSuite();
        include_once substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . '/components/ILIAS/Calendar_/test/class.ilCalendarRecurrenceCalculationTest.php';
        $suite->addTestSuite(ilCalendarRecurrenceCalculationTest::class);
        return $suite;
    }
}
