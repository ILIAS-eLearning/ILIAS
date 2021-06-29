<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

class ilServicesComponentSuite extends TestSuite
{
    public static function suite() : self
    {
        $suite = new self();
    
        require_once("./Services/Component/test/ilComponentDefinitionReaderTest.php");
        $suite->addTestSuite(ilComponentDefinitionReaderTest::class);

        require_once("./Services/Component/test/Setup/ilComponentDefinitionInfoProcessorTest.php");
        $suite->addTestSuite(ilComponentDefinitionInfoProcessorTest::class);

        require_once("./Services/Component/test/Setup/ilPluginSlotDefinitionProcessorTest.php");
        $suite->addTestSuite(ilPluginSlotDefinitionProcessorTest::class);

        require_once("./Services/Component/test/ilArtifactComponentDataDBTest.php");
        $suite->addTestSuite(ilArtifactComponentDataDBTest::class);

        return $suite;
    }
}
