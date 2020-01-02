<?php
/**
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilServicesInitSuite extends PHPUnit_Framework_TestSuite
{
    public static function suite()
    {
        $suite = new ilServicesInitSuite();

        require_once("Services/Init/test/ilInitialisationTest.php");

        $suite->addTestSuite("ilInitialisationTest");

        return $suite;
    }
}
