<?php

use PHPUnit\Framework\TestSuite;

/**
 * Context Test-Suite
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 * @version 1.0.0
 */
class ilServicesContextSuite extends TestSuite {
    public static function suite()
    {
        $suite = new ilServicesContextSuite();

        require_once("Services/Context/test/ilContextTest.php");

        $suite->addTestSuite("ilContextTest");

        return $suite;
    }
}
