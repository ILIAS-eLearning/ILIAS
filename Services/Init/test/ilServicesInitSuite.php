<?php

use PHPUnit\Framework\TestSuite;

/**
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilServicesInitSuite extends TestSuite {
    public static function suite()
    {
        $suite = new ilServicesInitSuite();

        require_once("Services/Init/test/ilInitialisationTest.php");

        $suite->addTestSuite("ilInitialisationTest");

        return $suite;
    }
} 