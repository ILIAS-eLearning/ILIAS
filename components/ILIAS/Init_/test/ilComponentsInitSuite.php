<?php

use PHPUnit\Framework\TestSuite;

/**
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilComponentsInitSuite extends TestSuite
{
    public static function suite(): self
    {
        $suite = new ilComponentsInitSuite();

        require_once __DIR__ . '/InitCtrlServiceTest.php';
        $suite->addTestSuite(InitCtrlServiceTest::class);

        return $suite;
    }
}
