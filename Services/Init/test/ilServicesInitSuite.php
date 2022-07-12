<?php

use PHPUnit\Framework\TestSuite;

/**
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilServicesInitSuite extends TestSuite
{
    public static function suite() : self
    {
        $suite = new ilServicesInitSuite();

        require_once __DIR__ . '/InitCtrlServiceTest.php';
        $suite->addTestSuite(InitCtrlServiceTest::class);

        return $suite;
    }
}
