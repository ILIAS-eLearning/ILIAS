<?php

use PHPUnit\Framework\TestSuite;

/**
 * Class ilCtrlPathSuite
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlPathSuite extends TestSuite
{
    public static function suite() : self
    {
        $suite = new self();



        return $suite;
    }
}