<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 ********************************************************************
 */

use PHPUnit\Framework\TestSuite;

class ilServicesComponentSuite extends TestSuite
{
    public static function suite(): self
    {
        $suite = new self();

        require_once("./components/ILIAS/Component/tests/ilComponentDefinitionReaderTest.php");
        $suite->addTestSuite(ilComponentDefinitionReaderTest::class);

        require_once("./components/ILIAS/Component/tests/ilComponentInfoTest.php");
        $suite->addTestSuite(ilComponentInfoTest::class);

        require_once("./components/ILIAS/Component/tests/ilPluginSlotInfoTest.php");
        $suite->addTestSuite(ilPluginSlotInfoTest::class);

        require_once("./components/ILIAS/Component/tests/ilPluginInfoTest.php");
        $suite->addTestSuite(ilPluginInfoTest::class);

        require_once("./components/ILIAS/Component/tests/Setup/ilComponentDefinitionInfoProcessorTest.php");
        $suite->addTestSuite(ilComponentDefinitionInfoProcessorTest::class);

        require_once("./components/ILIAS/Component/tests/Setup/ilComponentBuildPluginInfoObjectiveTest.php");
        $suite->addTestSuite(ilComponentBuildPluginInfoObjectiveTest::class);

        require_once("./components/ILIAS/Component/tests/ilArtifactComponentRepositoryTest.php");
        $suite->addTestSuite(ilArtifactComponentRepositoryTest::class);

        require_once("./components/ILIAS/Component/tests/ilPluginStateDBOverIlDBInterfaceTest.php");
        $suite->addTestSuite(ilPluginStateDBOverIlDBInterfaceTest::class);

        require_once("./components/ILIAS/Component/tests/Settings/ilPluginsOverviewTableTest.php");
        $suite->addTestSuite(ilPluginsOverviewTableTest::class);

        return $suite;
    }
}
