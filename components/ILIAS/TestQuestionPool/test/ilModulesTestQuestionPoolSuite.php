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

        require_once("./components/ILIAS/TestQuestionPool/test/assBaseTestCase.php");

        $suite = new ilModulesTestQuestionPoolSuite();

        // Questiontypes & related classes
        // -------------------------------------------------------------------------------------------------------------

        // Type: Cloze
        require_once("./components/ILIAS/TestQuestionPool/test/assClozeGapTest.php");
        $suite->addTestSuite("assClozeGapTest");

        require_once("./components/ILIAS/TestQuestionPool/test/assClozeSelectGapTest.php");
        $suite->addTestSuite("assClozeSelectGapTest");

        require_once("./components/ILIAS/TestQuestionPool/test/assClozeTestTest.php");
        $suite->addTestSuite("assClozeTestTest");

        require_once("./components/ILIAS/TestQuestionPool/test/assClozeTestGUITest.php");
        $suite->addTestSuite("assClozeTestGUITest");

        // Type: ErrorText
        require_once("./components/ILIAS/TestQuestionPool/test/assErrorTextTest.php");
        $suite->addTestSuite("assErrorTextTest");

        require_once("./components/ILIAS/TestQuestionPool/test/assErrorTextGUITest.php");
        $suite->addTestSuite("assErrorTextGUITest");

        // Type: FileUpload
        require_once("./components/ILIAS/TestQuestionPool/test/assFileUploadTest.php");
        $suite->addTestSuite("assFileUploadTest");

        require_once("./components/ILIAS/TestQuestionPool/test/assFileUploadGUITest.php");
        $suite->addTestSuite("assFileUploadGUITest");

        // Type: Formula
        require_once("./components/ILIAS/TestQuestionPool/test/assFormulaQuestionTest.php");
        $suite->addTestSuite("assFormulaQuestionTest");

        require_once("./components/ILIAS/TestQuestionPool/test/assFormulaQuestionGUITest.php");
        $suite->addTestSuite("assFormulaQuestionGUITest");

        // Type: Imagemap
        require_once("./components/ILIAS/TestQuestionPool/test/assImagemapQuestionTest.php");
        $suite->addTestSuite("assImagemapQuestionTest");

        // Type: MatchingQuestion
        require_once("./components/ILIAS/TestQuestionPool/test/assMatchingQuestionTest.php");
        $suite->addTestSuite("assMatchingQuestionTest");

        require_once("./components/ILIAS/TestQuestionPool/test/assMatchingQuestionGUITest.php");
        $suite->addTestSuite("assMatchingQuestionGUITest");

        // Type: MultipleChoice
        require_once("./components/ILIAS/TestQuestionPool/test/assMultipleChoiceTest.php");
        $suite->addTestSuite("assMultipleChoiceTest");
        // Incompatible with local mode

        require_once("./components/ILIAS/TestQuestionPool/test/assMultipleChoiceGUITest.php");
        $suite->addTestSuite("assMultipleChoiceGUITest");

        // Type: Numeric
        require_once("./components/ILIAS/TestQuestionPool/test/assNumericTest.php");
        $suite->addTestSuite("assNumericTest");

        require_once("./components/ILIAS/TestQuestionPool/test/assNumericGUITest.php");
        $suite->addTestSuite("assNumericGUITest");

        require_once("./components/ILIAS/TestQuestionPool/test/assNumericRangeTest.php");
        $suite->addTestSuite("assNumericRangeTest");

        // Type: OrderingHorizontal
        require_once("./components/ILIAS/TestQuestionPool/test/assOrderingHorizontalTest.php");
        $suite->addTestSuite("assOrderingHorizontalTest");

        require_once("./components/ILIAS/TestQuestionPool/test/assOrderingHorizontalGUITest.php");
        $suite->addTestSuite("assOrderingHorizontalGUITest");

        // Type: Ordering
        require_once("./components/ILIAS/TestQuestionPool/test/assOrderingQuestionTest.php");
        $suite->addTestSuite("assOrderingQuestionTest");

        require_once("./components/ILIAS/TestQuestionPool/test/assOrderingQuestionGUITest.php");
        $suite->addTestSuite("assOrderingQuestionGUITest");

        // Type: SingleChoice
        require_once("./components/ILIAS/TestQuestionPool/test/assSingleChoiceTest.php");
        $suite->addTestSuite("assSingleChoiceTest");
        // Incompatible with local mode

        require_once("./components/ILIAS/TestQuestionPool/test/assSingleChoiceGUITest.php");
        $suite->addTestSuite("assSingleChoiceGUITest");

        // Type: Text (Essay)
        require_once("./components/ILIAS/TestQuestionPool/test/assTextQuestionTest.php");
        $suite->addTestSuite("assTextQuestionTest");

        require_once("./components/ILIAS/TestQuestionPool/test/assTextQuestionGUITest.php");
        $suite->addTestSuite("assTextQuestionGUITest");

        // Type: TextSubset
        require_once("./components/ILIAS/TestQuestionPool/test/assTextSubsetTest.php");
        $suite->addTestSuite("assTextSubsetTest");

        require_once("./components/ILIAS/TestQuestionPool/test/assTextSubsetGUITest.php");
        $suite->addTestSuite("assTextSubsetGUITest");

        // Type: LongMenu
        require_once("./components/ILIAS/TestQuestionPool/test/assLongMenuTest.php");
        $suite->addTestSuite("assLongMenuTest");

        // Type: assKprimChoiceTest
        require_once("./components/ILIAS/TestQuestionPool/test/assKprimChoiceTest.php");
        $suite->addTestSuite("assKprimChoiceTest");

        // Hints
        // -------------------------------------------------------------------------------------------------------------
        require_once("./components/ILIAS/TestQuestionPool/test/ilAssQuestionHintTest.php");
        $suite->addTestSuite("ilAssQuestionHintTest");

        require_once("./components/ILIAS/TestQuestionPool/test/ilAssQuestionHintAbstractTest.php");
        $suite->addTestSuite("ilAssQuestionHintAbstractTest");

        require_once("./components/ILIAS/TestQuestionPool/test/ilAssQuestionHintListTest.php");
        $suite->addTestSuite("ilAssQuestionHintListTest");

        require_once("./components/ILIAS/TestQuestionPool/test/ilAssQuestionHintRequestStatisticDataTest.php");
        $suite->addTestSuite("ilAssQuestionHintRequestStatisticDataTest");

        require_once("./components/ILIAS/TestQuestionPool/test/ilAssQuestionHintTrackingTest.php");
        $suite->addTestSuite("ilAssQuestionHintTrackingTest");

        // Answertypes
        // -------------------------------------------------------------------------------------------------------------
        require_once("./components/ILIAS/TestQuestionPool/test/assAnswerBinaryStateTest.php");
        $suite->addTestSuite("assAnswerBinaryStateTest");

        require_once("./components/ILIAS/TestQuestionPool/test/assAnswerBinaryStateImageTest.php");
        $suite->addTestSuite("assAnswerBinaryStateImageTest");

        require_once("./components/ILIAS/TestQuestionPool/test/assAnswerClozeTest.php");
        $suite->addTestSuite("assAnswerClozeTest");

        require_once("./components/ILIAS/TestQuestionPool/test/assAnswerErrorTextTest.php");
        $suite->addTestSuite("assAnswerErrorTextTest");

        require_once("./components/ILIAS/TestQuestionPool/test/assAnswerImagemapTest.php");
        $suite->addTestSuite("assAnswerImagemapTest");

        require_once("./components/ILIAS/TestQuestionPool/test/assAnswerMatchingTest.php");
        $suite->addTestSuite("assAnswerMatchingTest");

        require_once("./components/ILIAS/TestQuestionPool/test/assAnswerMatchingDefinitionTest.php");
        $suite->addTestSuite("assAnswerMatchingDefinitionTest");

        require_once("./components/ILIAS/TestQuestionPool/test/assAnswerMatchingPairTest.php");
        $suite->addTestSuite("assAnswerMatchingPairTest");

        require_once("./components/ILIAS/TestQuestionPool/test/assAnswerMatchingTermTest.php");
        $suite->addTestSuite("assAnswerMatchingTermTest");

        require_once("./components/ILIAS/TestQuestionPool/test/assAnswerMultipleResponseTest.php");
        $suite->addTestSuite("assAnswerMultipleResponseTest");

        require_once("./components/ILIAS/TestQuestionPool/test/assAnswerMultipleResponseImageTest.php");
        $suite->addTestSuite("assAnswerMultipleResponseImageTest");

        require_once("./components/ILIAS/TestQuestionPool/test/assAnswerOrderingTest.php");
        $suite->addTestSuite("assAnswerOrderingTest");

        require_once("./components/ILIAS/TestQuestionPool/test/assAnswerSimpleTest.php");
        $suite->addTestSuite("assAnswerSimpleTest");

        require_once("./components/ILIAS/TestQuestionPool/test/assAnswerTrueFalseTest.php");
        $suite->addTestSuite("assAnswerTrueFalseTest");

        require_once("./components/ILIAS/TestQuestionPool/test/ilAssQuestionSkillAssignmentRegistryTest.php");
        $suite->addTestSuite("ilAssQuestionSkillAssignmentRegistryTest");

        require_once("./components/ILIAS/TestQuestionPool/test/assQuestionSuggestedSolutionTest.php");
        $suite->addTestSuite("assQuestionSuggestedSolutionTest");

        return $suite;
    }
}
