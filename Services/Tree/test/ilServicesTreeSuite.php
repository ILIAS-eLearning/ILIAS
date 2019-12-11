<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilServicesTreeSuite extends PHPUnit_Framework_TestSuite
{
    public static function suite()
    {
        $suite = new ilServicesTreeSuite();
        
        include_once("./Services/Tree/test/ilTreeTest.php");
        $suite->addTestSuite("ilTreeTest");

        return $suite;
    }
}
