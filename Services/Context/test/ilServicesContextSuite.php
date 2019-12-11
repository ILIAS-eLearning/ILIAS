<?php
/**
 * Context Test-Suite
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 * @version 1.0.0
 */
class ilServicesContextSuite extends PHPUnit_Framework_TestSuite
{
    public static function suite()
    {
        $suite = new ilServicesContextSuite();

        require_once("Services/Context/test/ilContextTest.php");

        $suite->addTestSuite("ilContextTest");

        return $suite;
    }
}
