<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilModulesTestQuestionPoolSuite extends PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		if (defined('ILIAS_PHPUNIT_CONTEXT'))
		{
			include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
			ilUnitUtil::performInitialisation();
		}
		else
		{
			chdir( dirname( __FILE__ ) );
			chdir('../../../');
		}

		$suite = new ilModulesTestQuestionPoolSuite();
	
		// Questiontypes & related classes
		// -------------------------------------------------------------------------------------------------------------

		// Type: Question (Base class)
		require_once("./Modules/TestQuestionPool/test/assQuestionTest.php");
		$suite->addTestSuite("assQuestionTest");

		require_once("./Modules/TestQuestionPool/test/assQuestionGUITest.php");
		$suite->addTestSuite("assQuestionGUITest");

		// Type: Cloze
		require_once("./Modules/TestQuestionPool/test/assClozeGapTest.php");
		$suite->addTestSuite("assClozeGapTest");

		require_once("./Modules/TestQuestionPool/test/assClozeSelectGapTest.php");
		$suite->addTestSuite("assClozeSelectGapTest");

		require_once("./Modules/TestQuestionPool/test/assClozeTestTest.php");
		$suite->addTestSuite("assClozeTestTest");

		require_once("./Modules/TestQuestionPool/test/assClozeTestGUITest.php");
		$suite->addTestSuite("assClozeTestGUITest");

		// Type: ErrorText
		require_once("./Modules/TestQuestionPool/test/assErrorTextTest.php");
		$suite->addTestSuite("assErrorTextTest");

		require_once("./Modules/TestQuestionPool/test/assErrorTextGUITest.php");
		$suite->addTestSuite("assErrorTextGUITest");

		// Type: FileUpload
		require_once("./Modules/TestQuestionPool/test/assFileUploadTest.php");
		$suite->addTestSuite("assFileUploadTest");

		require_once("./Modules/TestQuestionPool/test/assFileUploadGUITest.php");
		$suite->addTestSuite("assFileUploadGUITest");

		// Type: Flash
		require_once("./Modules/TestQuestionPool/test/assFlashQuestionTest.php");
		$suite->addTestSuite("assFlashQuestionTest");

		require_once("./Modules/TestQuestionPool/test/assFlashQuestionGUITest.php");
		$suite->addTestSuite("assFlashQuestionGUITest");

		// Type: Formula
		require_once("./Modules/TestQuestionPool/test/assFormulaQuestionTest.php");
		$suite->addTestSuite("assFormulaQuestionTest");

		require_once("./Modules/TestQuestionPool/test/assFormulaQuestionGUITest.php");
		$suite->addTestSuite("assFormulaQuestionGUITest");

		// Type: Imagemap
		require_once("./Modules/TestQuestionPool/test/assImagemapQuestionTest.php");
		$suite->addTestSuite("assImagemapQuestionTest");

		require_once("./Modules/TestQuestionPool/test/assImagemapQuestionGUITest.php");
		$suite->addTestSuite("assImagemapQuestionGUITest");

		// Zype: JavaApplet
		require_once("./Modules/TestQuestionPool/test/assJavaAppletTest.php");
		$suite->addTestSuite("assJavaAppletTest");

		require_once("./Modules/TestQuestionPool/test/assJavaAppletGUITest.php");
		$suite->addTestSuite("assJavaAppletGUITest");

		// Type: MatchingQuestion
		require_once("./Modules/TestQuestionPool/test/assMatchingQuestionTest.php");
		$suite->addTestSuite("assMatchingQuestionTest");

		require_once("./Modules/TestQuestionPool/test/assMatchingQuestionGUITest.php");
		$suite->addTestSuite("assMatchingQuestionGUITest");

		// Type: MultipleChoice
		require_once("./Modules/TestQuestionPool/test/assMultipleChoiceTest.php");
		//$suite->addTestSuite("assMultipleChoiceTest");
		// Incompatible with local mode

		require_once("./Modules/TestQuestionPool/test/assMultipleChoiceGUITest.php");
		$suite->addTestSuite("assMultipleChoiceGUITest");

		// Type: Numeric
		require_once("./Modules/TestQuestionPool/test/assNumericTest.php");
		$suite->addTestSuite("assNumericTest");

		require_once("./Modules/TestQuestionPool/test/assNumericGUITest.php");
		$suite->addTestSuite("assNumericGUITest");

		require_once("./Modules/TestQuestionPool/test/assNumericRangeTest.php");
		$suite->addTestSuite("assNumericRangeTest");

		// Type: OrderingHorizontal
		require_once("./Modules/TestQuestionPool/test/assOrderingHorizontalTest.php");
		$suite->addTestSuite("assOrderingHorizontalTest");

		require_once("./Modules/TestQuestionPool/test/assOrderingHorizontalGUITest.php");
		$suite->addTestSuite("assOrderingHorizontalGUITest");

		// Type: Ordering
		require_once("./Modules/TestQuestionPool/test/assOrderingQuestionTest.php");
		$suite->addTestSuite("assOrderingQuestionTest");

		require_once("./Modules/TestQuestionPool/test/assOrderingQuestionGUITest.php");
		$suite->addTestSuite("assOrderingQuestionGUITest");

		// Type: SingleChoice
		require_once("./Modules/TestQuestionPool/test/assSingleChoiceTest.php");
		//$suite->addTestSuite("assSingleChoiceTest");
		// Incompatible with local mode

		require_once("./Modules/TestQuestionPool/test/assSingleChoiceGUITest.php");
		$suite->addTestSuite("assSingleChoiceGUITest");

		// Type: Text (Essay)
		require_once("./Modules/TestQuestionPool/test/assTextQuestionTest.php");
		$suite->addTestSuite("assTextQuestionTest");

		require_once("./Modules/TestQuestionPool/test/assTextQuestionGUITest.php");
		$suite->addTestSuite("assTextQuestionGUITest");

		// Type: TextSubset
		require_once("./Modules/TestQuestionPool/test/assTextSubsetTest.php");
		$suite->addTestSuite("assTextSubsetTest");

		require_once("./Modules/TestQuestionPool/test/assTextSubsetGUITest.php");
		$suite->addTestSuite("assTextSubsetGUITest");

		// Input GUIs
		// -------------------------------------------------------------------------------------------------------------
		require_once("./Modules/TestQuestionPool/test/ilAnswerWizardInputGUITest.php");
		$suite->addTestSuite("ilAnswerWizardInputGUITest");

		// Hints
		// -------------------------------------------------------------------------------------------------------------
		require_once("./Modules/TestQuestionPool/test/ilAssQuestionHintTest.php");
		$suite->addTestSuite("ilAssQuestionHintTest");

		require_once("./Modules/TestQuestionPool/test/ilAssQuestionHintAbstractTest.php");
		$suite->addTestSuite("ilAssQuestionHintAbstractTest");

		require_once("./Modules/TestQuestionPool/test/ilAssQuestionHintGUITest.php");
		$suite->addTestSuite("ilAssQuestionHintGUITest");

		require_once("./Modules/TestQuestionPool/test/ilAssQuestionHintListTest.php");
		$suite->addTestSuite("ilAssQuestionHintListTest");

		require_once("./Modules/TestQuestionPool/test/ilAssQuestionHintRequestGUITest.php");
		$suite->addTestSuite("ilAssQuestionHintRequestGUITest");

		require_once("./Modules/TestQuestionPool/test/ilAssQuestionHintRequestStatisticDataTest.php");
		$suite->addTestSuite("ilAssQuestionHintRequestStatisticDataTest");

		require_once("./Modules/TestQuestionPool/test/ilAssQuestionHintsGUITest.php");
		$suite->addTestSuite("ilAssQuestionHintsGUITest");

		require_once("./Modules/TestQuestionPool/test/ilAssQuestionHintsOrderingClipboardTest.php");
		$suite->addTestSuite("ilAssQuestionHintsOrderingClipboardTest");

		require_once("./Modules/TestQuestionPool/test/ilAssQuestionHintsTableGUITest.php");
		$suite->addTestSuite("ilAssQuestionHintsTableGUITest");

		require_once("./Modules/TestQuestionPool/test/ilAssQuestionHintTrackingTest.php");
		$suite->addTestSuite("ilAssQuestionHintTrackingTest");

		// Answertypes
		// -------------------------------------------------------------------------------------------------------------
		require_once("./Modules/TestQuestionPool/test/assAnswerBinaryStateTest.php");
		$suite->addTestSuite("assAnswerBinaryStateTest");

		require_once("./Modules/TestQuestionPool/test/assAnswerBinaryStateImageTest.php");
		$suite->addTestSuite("assAnswerBinaryStateImageTest");

		require_once("./Modules/TestQuestionPool/test/assAnswerClozeTest.php");
		$suite->addTestSuite("assAnswerClozeTest");

		require_once("./Modules/TestQuestionPool/test/assAnswerErrorTextTest.php");
		$suite->addTestSuite("assAnswerErrorTextTest"); // mbecker: 100% Coverage @ 03.08.2012

		require_once("./Modules/TestQuestionPool/test/assAnswerImagemapTest.php");
		$suite->addTestSuite("assAnswerImagemapTest");

		require_once("./Modules/TestQuestionPool/test/assAnswerMatchingTest.php");
		$suite->addTestSuite("assAnswerMatchingTest");

		require_once("./Modules/TestQuestionPool/test/assAnswerMatchingDefinitionTest.php");
		$suite->addTestSuite("assAnswerMatchingDefinitionTest");

		require_once("./Modules/TestQuestionPool/test/assAnswerMatchingPairTest.php");
		$suite->addTestSuite("assAnswerMatchingPairTest");

		require_once("./Modules/TestQuestionPool/test/assAnswerMatchingTermTest.php");
		$suite->addTestSuite("assAnswerMatchingTermTest");

		require_once("./Modules/TestQuestionPool/test/assAnswerMultipleResponseTest.php");
		$suite->addTestSuite("assAnswerMultipleResponseTest");

		require_once("./Modules/TestQuestionPool/test/assAnswerMultipleResponseImageTest.php");
		$suite->addTestSuite("assAnswerMultipleResponseImageTest");

		require_once("./Modules/TestQuestionPool/test/assAnswerOrderingTest.php");
		$suite->addTestSuite("assAnswerOrderingTest");

		require_once("./Modules/TestQuestionPool/test/assAnswerSimpleTest.php");
		$suite->addTestSuite("assAnswerSimpleTest");

		require_once("./Modules/TestQuestionPool/test/assAnswerTrueFalseTest.php");
		$suite->addTestSuite("assAnswerTrueFalseTest");

		return $suite;
	}
}
?>
