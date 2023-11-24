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
 *********************************************************************/

use PHPUnit\Framework\TestSuite;

class ilModulesTestQuestionPoolSuite extends TestSuite
{
    public static function suite(): ilModulesTestQuestionPoolSuite
    {
        chdir(dirname(__FILE__));
        chdir('../../../../');

        require_once("./components/ILIAS/TestQuestionPool/tests/assBaseTestCase.php");

        $suite = new ilModulesTestQuestionPoolSuite();

        // Questiontypes & related classes
        // -------------------------------------------------------------------------------------------------------------

        // Type: Cloze
        require_once("./components/ILIAS/TestQuestionPool/tests/assClozeGapTest.php");
        $suite->addTestSuite("assClozeGapTest");

        require_once("./components/ILIAS/TestQuestionPool/tests/assClozeSelectGapTest.php");
        $suite->addTestSuite("assClozeSelectGapTest");

        require_once("./components/ILIAS/TestQuestionPool/tests/assClozeTestTest.php");
        $suite->addTestSuite("assClozeTestTest");

        require_once("./components/ILIAS/TestQuestionPool/tests/assClozeTestGUITest.php");
        $suite->addTestSuite("assClozeTestGUITest");

        // Type: ErrorText
        require_once("./components/ILIAS/TestQuestionPool/tests/assErrorTextTest.php");
        $suite->addTestSuite("assErrorTextTest");

        require_once("./components/ILIAS/TestQuestionPool/tests/assErrorTextGUITest.php");
        $suite->addTestSuite("assErrorTextGUITest");

        // Type: FileUpload
        require_once("./components/ILIAS/TestQuestionPool/tests/assFileUploadTest.php");
        $suite->addTestSuite("assFileUploadTest");

        require_once("./components/ILIAS/TestQuestionPool/tests/assFileUploadGUITest.php");
        $suite->addTestSuite("assFileUploadGUITest");

        // Type: Formula
        require_once("./components/ILIAS/TestQuestionPool/tests/assFormulaQuestionTest.php");
        $suite->addTestSuite("assFormulaQuestionTest");

        require_once("./components/ILIAS/TestQuestionPool/tests/assFormulaQuestionGUITest.php");
        $suite->addTestSuite("assFormulaQuestionGUITest");

        // Type: Imagemap
        require_once("./components/ILIAS/TestQuestionPool/tests/assImagemapQuestionTest.php");
        $suite->addTestSuite("assImagemapQuestionTest");

        // Type: MatchingQuestion
        require_once("./components/ILIAS/TestQuestionPool/tests/assMatchingQuestionTest.php");
        $suite->addTestSuite("assMatchingQuestionTest");

        require_once("./components/ILIAS/TestQuestionPool/tests/assMatchingQuestionGUITest.php");
        $suite->addTestSuite("assMatchingQuestionGUITest");

        // Type: MultipleChoice
        require_once("./components/ILIAS/TestQuestionPool/tests/assMultipleChoiceTest.php");
        $suite->addTestSuite("assMultipleChoiceTest");
        // Incompatible with local mode

        require_once("./components/ILIAS/TestQuestionPool/tests/assMultipleChoiceGUITest.php");
        $suite->addTestSuite("assMultipleChoiceGUITest");

        // Type: Numeric
        require_once("./components/ILIAS/TestQuestionPool/tests/assNumericTest.php");
        $suite->addTestSuite("assNumericTest");

        require_once("./components/ILIAS/TestQuestionPool/tests/assNumericGUITest.php");
        $suite->addTestSuite("assNumericGUITest");

        require_once("./components/ILIAS/TestQuestionPool/tests/assNumericRangeTest.php");
        $suite->addTestSuite("assNumericRangeTest");

        // Type: OrderingHorizontal
        require_once("./components/ILIAS/TestQuestionPool/tests/assOrderingHorizontalTest.php");
        $suite->addTestSuite("assOrderingHorizontalTest");

        require_once("./components/ILIAS/TestQuestionPool/tests/assOrderingHorizontalGUITest.php");
        $suite->addTestSuite("assOrderingHorizontalGUITest");

        // Type: Ordering
        require_once("./components/ILIAS/TestQuestionPool/tests/assOrderingQuestionTest.php");
        $suite->addTestSuite("assOrderingQuestionTest");

        require_once("./components/ILIAS/TestQuestionPool/tests/assOrderingQuestionGUITest.php");
        $suite->addTestSuite("assOrderingQuestionGUITest");

        // Type: SingleChoice
        require_once("./components/ILIAS/TestQuestionPool/tests/assSingleChoiceTest.php");
        $suite->addTestSuite("assSingleChoiceTest");
        // Incompatible with local mode

        require_once("./components/ILIAS/TestQuestionPool/tests/assSingleChoiceGUITest.php");
        $suite->addTestSuite("assSingleChoiceGUITest");

        // Type: Text (Essay)
        require_once("./components/ILIAS/TestQuestionPool/tests/assTextQuestionTest.php");
        $suite->addTestSuite("assTextQuestionTest");

        require_once("./components/ILIAS/TestQuestionPool/tests/assTextQuestionGUITest.php");
        $suite->addTestSuite("assTextQuestionGUITest");

        // Type: TextSubset
        require_once("./components/ILIAS/TestQuestionPool/tests/assTextSubsetTest.php");
        $suite->addTestSuite("assTextSubsetTest");

        require_once("./components/ILIAS/TestQuestionPool/tests/assTextSubsetGUITest.php");
        $suite->addTestSuite("assTextSubsetGUITest");

        // Type: LongMenu
        require_once("./components/ILIAS/TestQuestionPool/tests/assLongMenuTest.php");
        $suite->addTestSuite("assLongMenuTest");

        // Type: assKprimChoiceTest
        require_once("./components/ILIAS/TestQuestionPool/tests/assKprimChoiceTest.php");
        $suite->addTestSuite("assKprimChoiceTest");

        // Hints
        // -------------------------------------------------------------------------------------------------------------
        require_once("./components/ILIAS/TestQuestionPool/tests/ilAssQuestionHintTest.php");
        $suite->addTestSuite("ilAssQuestionHintTest");

        require_once("./components/ILIAS/TestQuestionPool/tests/ilAssQuestionHintListTest.php");
        $suite->addTestSuite("ilAssQuestionHintListTest");

        require_once("./components/ILIAS/TestQuestionPool/tests/ilAssQuestionHintRequestStatisticDataTest.php");
        $suite->addTestSuite("ilAssQuestionHintRequestStatisticDataTest");

        require_once("./components/ILIAS/TestQuestionPool/tests/ilAssQuestionHintTrackingTest.php");
        $suite->addTestSuite("ilAssQuestionHintTrackingTest");

        // Answertypes
        // -------------------------------------------------------------------------------------------------------------
        require_once("./components/ILIAS/TestQuestionPool/tests/assAnswerBinaryStateTest.php");
        $suite->addTestSuite("assAnswerBinaryStateTest");

        require_once("./components/ILIAS/TestQuestionPool/tests/assAnswerBinaryStateImageTest.php");
        $suite->addTestSuite("assAnswerBinaryStateImageTest");

        require_once("./components/ILIAS/TestQuestionPool/tests/assAnswerClozeTest.php");
        $suite->addTestSuite("assAnswerClozeTest");

        require_once("./components/ILIAS/TestQuestionPool/tests/assAnswerErrorTextTest.php");
        $suite->addTestSuite("assAnswerErrorTextTest");

        require_once("./components/ILIAS/TestQuestionPool/tests/assAnswerImagemapTest.php");
        $suite->addTestSuite("assAnswerImagemapTest");

        require_once("./components/ILIAS/TestQuestionPool/tests/assAnswerMatchingTest.php");
        $suite->addTestSuite("assAnswerMatchingTest");

        require_once("./components/ILIAS/TestQuestionPool/tests/assAnswerMatchingDefinitionTest.php");
        $suite->addTestSuite("assAnswerMatchingDefinitionTest");

        require_once("./components/ILIAS/TestQuestionPool/tests/assAnswerMatchingPairTest.php");
        $suite->addTestSuite("assAnswerMatchingPairTest");

        require_once("./components/ILIAS/TestQuestionPool/tests/assAnswerMatchingTermTest.php");
        $suite->addTestSuite("assAnswerMatchingTermTest");

        require_once("./components/ILIAS/TestQuestionPool/tests/assAnswerMultipleResponseTest.php");
        $suite->addTestSuite("assAnswerMultipleResponseTest");

        require_once("./components/ILIAS/TestQuestionPool/tests/assAnswerMultipleResponseImageTest.php");
        $suite->addTestSuite("assAnswerMultipleResponseImageTest");

        require_once("./components/ILIAS/TestQuestionPool/tests/assAnswerSimpleTest.php");
        $suite->addTestSuite("assAnswerSimpleTest");

        require_once("./components/ILIAS/TestQuestionPool/tests/assAnswerTrueFalseTest.php");
        $suite->addTestSuite("assAnswerTrueFalseTest");

        require_once("./components/ILIAS/TestQuestionPool/tests/ilAssQuestionSkillAssignmentRegistryTest.php");
        $suite->addTestSuite("ilAssQuestionSkillAssignmentRegistryTest");

        require_once("./components/ILIAS/TestQuestionPool/tests/assQuestionSuggestedSolutionTest.php");
        $suite->addTestSuite("assQuestionSuggestedSolutionTest");

        return $suite;
    }
}
