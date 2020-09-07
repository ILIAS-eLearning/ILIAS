<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilServicesXHTMLPageSuite extends PHPUnit_Framework_TestSuite
{
    public static function suite()
    {
        $suite = new ilServicesXHTMLPageSuite();
        
        include_once("./Services/XHTMLPage/test/ilXHTMLPageTest.php");
        $suite->addTestSuite("ilXHTMLPageTest");

        return $suite;
    }
}
