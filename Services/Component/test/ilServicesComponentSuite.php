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

        require_once("./Services/Component/test/ilComponentInfoTest.php");
        $suite->addTestSuite(ilComponentInfoTest::class);

        require_once("./Services/Component/test/ilPluginSlotInfoTest.php");
        $suite->addTestSuite(ilPluginSlotInfoTest::class);

        require_once("./Services/Component/test/ilPluginInfoTest.php");
        $suite->addTestSuite(ilPluginInfoTest::class);

        require_once("./Services/Component/test/Setup/ilComponentDefinitionInfoProcessorTest.php");
        $suite->addTestSuite(ilComponentDefinitionInfoProcessorTest::class);

        require_once("./Services/Component/test/Setup/ilComponentBuildPluginInfoObjectiveTest.php");
        $suite->addTestSuite(ilComponentBuildPluginInfoObjectiveTest::class);

        require_once("./Services/Component/test/ilArtifactComponentRepositoryTest.php");
        $suite->addTestSuite(ilArtifactComponentRepositoryTest::class);

        require_once("./Services/Component/test/ilPluginStateDBOverIlDBInterfaceTest.php");
        $suite->addTestSuite(ilPluginStateDBOverIlDBInterfaceTest::class);

        require_once("./Services/Component/test/Settings/ilPluginsOverviewTableTest.php");
        $suite->addTestSuite(ilPluginsOverviewTableTest::class);

        return $suite;
    }
}
