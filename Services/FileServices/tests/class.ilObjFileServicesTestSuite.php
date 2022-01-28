<?php

use PHPUnit\Framework\TestSuite;

/**
 * Class ilObjFileServicesTestSuite
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilObjFileServicesTestSuite extends TestSuite
{
    public static function suite() : self
    {
        $suite = new self();
        // currently no tests
        return $suite;
    }
}
