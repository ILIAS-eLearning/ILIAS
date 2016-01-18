<?php
/**
 * Context Test-Suite
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 * @version 1.0.0
 */
class ilServicesInitSuite extends PHPUnit_Framework_TestSuite {
    public static function suite()
    {
        $suite = new ilServicesInitSuite();

        require_once("/Services/Context/test/ilInitialisationTest.php");

        $suite->addTestSuite("ilInitialisationTest");

        return $suite;
    }
} 