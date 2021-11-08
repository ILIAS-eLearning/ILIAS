<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/bootstrap.php';

/**
 * Class ilServicesCronSuite
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilServicesCronSuite extends TestSuite
{
    /**
     * @return self
     * @throws ReflectionException
     */
    public static function suite() : self
    {
        $suite = new self();

        require_once __DIR__ . '/CronJobEntityTest.php';
        $suite->addTestSuite(CronJobEntityTest::class);

        require_once __DIR__ . '/CronJobScheduleTest.php';
        $suite->addTestSuite(CronJobScheduleTest::class);

        return $suite;
    }
}
