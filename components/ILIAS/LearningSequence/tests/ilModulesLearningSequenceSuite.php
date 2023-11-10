<?php

declare(strict_types=1);

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
 *********************************************************************/

use PHPUnit\Framework\TestSuite;

class ilModulesLearningSequenceSuite extends TestSuite
{
    public static function suite(): ilModulesLearningSequenceSuite
    {
        $suite = new ilModulesLearningSequenceSuite();

        // add each test class of the component
        require_once("./components/ILIAS/LearningSequence/tests/Activation/ilLearningSequenceActivationTest.php");
        require_once("./components/ILIAS/LearningSequence/tests/Activation/ilLearningSequenceActivationDBTest.php");
        require_once("./components/ILIAS/LearningSequence/tests/LearnerProgress/ilLearnerProgressDBTest.php");
        require_once("./components/ILIAS/LearningSequence/tests/LearnerProgress/ilLSLPTest.php");
        require_once("./components/ILIAS/LearningSequence/tests/LearnerProgress/ilLSLPEventHandlerTest.php");
        require_once("./components/ILIAS/LearningSequence/tests/LearnerProgress/LSLearnerItemTest.php");
        require_once("./components/ILIAS/LearningSequence/tests/LSItems/ilLSItemsDBTest.php");
        require_once("./components/ILIAS/LearningSequence/tests/LSItems/LSItemTest.php");
        require_once("./components/ILIAS/LearningSequence/tests/PostConditions/ilLSPostConditionTest.php");
        require_once("./components/ILIAS/LearningSequence/tests/PostConditions/ilLSPostConditionDBTest.php");
        require_once("./components/ILIAS/LearningSequence/tests/Settings/ilLearningSequenceSettingsTest.php");
        require_once("./components/ILIAS/LearningSequence/tests/Settings/ilLearningSequenceSettingsDBTest.php");
        require_once("./components/ILIAS/LearningSequence/tests/LSControlBuilderTest.php");
        require_once("./components/ILIAS/LearningSequence/tests/LSLocatorBuilderTest.php");
        require_once("./components/ILIAS/LearningSequence/tests/LSTOCBuilderTest.php");
        require_once("./components/ILIAS/LearningSequence/tests/LSUrlBuilderTest.php");
        require_once("./components/ILIAS/LearningSequence/tests/LSItems/LSItemTest.php");
        require_once("./components/ILIAS/LearningSequence/tests/LearnerProgress/LSLearnerItemTest.php");
        require_once("./components/ILIAS/LearningSequence/tests/GlobalSettings/GlobalSettingsTest.php");
        require_once("./components/ILIAS/LearningSequence/tests/LearnerProgress/ilLSLPEventHandlerTest.php");

        $suite->addTestSuite("ilLearningSequenceActivationTest");
        $suite->addTestSuite("ilLearningSequenceActivationDBTest");
        $suite->addTestSuite("ilLearnerProgressDBTest");
        $suite->addTestSuite("ilLSLPTest");
        $suite->addTestSuite("ilLSLPEventHandlerTest");
        $suite->addTestSuite("LSLearnerItemTest");
        $suite->addTestSuite("ilLSItemsDBTest");
        $suite->addTestSuite("LSItemTest");
        $suite->addTestSuite("ilLSPostConditionTest");
        $suite->addTestSuite("ilLSPostConditionDBTest");
        $suite->addTestSuite("ilLearningSequenceSettingsTest");
        $suite->addTestSuite("ilLearningSequenceSettingsDBTest");
        $suite->addTestSuite("LSControlBuilderTest");
        $suite->addTestSuite("LSLocatorBuilderTest");
        $suite->addTestSuite("LSTOCBuilderTest");
        $suite->addTestSuite("LSUrlBuilderTest");
        $suite->addTestSuite("LSItemTest");
        $suite->addTestSuite("LSLearnerItemTest");
        $suite->addTestSuite("GlobalSettingsTest");
        $suite->addTestSuite("ilLSLPEventHandlerTest");

        return $suite;
    }
}
