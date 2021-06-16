<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

class ilServicesComponentSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new ilServicesComponentSuite();
    
        require_once("./Services/Component/test/ilComponentDefinitionReaderTest.php");
        $suite->addTestSuite("ilComponentDefinitionReaderTest");

        return $suite;
    }
}
