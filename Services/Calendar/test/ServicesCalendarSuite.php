<?php
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPStan\Framework\TestSuite;

/**
 * class ServicesCalendarSuite
 *
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */

class ServicesCalendarSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new ilServicesCalendarSuite();

        include_once './Services/Calendar/test/class.ilCalendarRecurrenceCalculatorTest.php';
        $suite->addSuite('ilCalendarRecurrenceCalculatorTest');
    }
}