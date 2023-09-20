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

class ilComponentsLearningSequenceSuite extends TestSuite
{
    public static function suite(): ilComponentsLearningSequenceSuite
    {
        $suite = new ilComponentsLearningSequenceSuite();

        // add each test class of the component
        require_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . "/components/ILIAS/LearningSequence_/test/Activation/ilLearningSequenceActivationTest.php");
        require_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . "/components/ILIAS/LearningSequence_/test/Activation/ilLearningSequenceActivationDBTest.php");
        require_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . "/components/ILIAS/LearningSequence_/test/LearnerProgress/ilLearnerProgressDBTest.php");
        require_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . "/components/ILIAS/LearningSequence_/test/LearnerProgress/ilLSLPTest.php");
        require_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . "/components/ILIAS/LearningSequence_/test/LearnerProgress/ilLSLPEventHandlerTest.php");
        require_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . "/components/ILIAS/LearningSequence_/test/LearnerProgress/LSLearnerItemTest.php");
        require_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . "/components/ILIAS/LearningSequence_/test/LSItems/ilLSItemsDBTest.php");
        require_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . "/components/ILIAS/LearningSequence_/test/LSItems/LSItemTest.php");
        require_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . "/components/ILIAS/LearningSequence_/test/PostConditions/ilLSPostConditionTest.php");
        require_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . "/components/ILIAS/LearningSequence_/test/PostConditions/ilLSPostConditionDBTest.php");
        require_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . "/components/ILIAS/LearningSequence_/test/Settings/ilLearningSequenceSettingsTest.php");
        require_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . "/components/ILIAS/LearningSequence_/test/Settings/ilLearningSequenceSettingsDBTest.php");
        require_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . "/components/ILIAS/LearningSequence_/test/LSControlBuilderTest.php");
        require_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . "/components/ILIAS/LearningSequence_/test/LSLocatorBuilderTest.php");
        require_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . "/components/ILIAS/LearningSequence_/test/LSTOCBuilderTest.php");
        require_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . "/components/ILIAS/LearningSequence_/test/LSUrlBuilderTest.php");
        require_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . "/components/ILIAS/LearningSequence_/test/LSItems/LSItemTest.php");
        require_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . "/components/ILIAS/LearningSequence_/test/LearnerProgress/LSLearnerItemTest.php");
        require_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . "/components/ILIAS/LearningSequence_/test/GlobalSettings/GlobalSettingsTest.php");
        require_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . "/components/ILIAS/LearningSequence_/test/LearnerProgress/ilLSLPEventHandlerTest.php");

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
