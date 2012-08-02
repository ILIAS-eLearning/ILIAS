<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

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
	
		// Questiontypes
		// -------------------------------------------------------------------------------------------------------------
		require_once("./Modules/TestQuestionPool/test/ilAssSingleChoiceTest.php");
		//$suite->addTestSuite("ilassSingleChoiceTest");
		// Incompatible with local mode
		
		require_once("./Modules/TestQuestionPool/test/ilAssMultipleChoiceTest.php");
		//$suite->addTestSuite("ilassMultipleChoiceTest");
		// Incompatible with local mode
		
		require_once("./Modules/TestQuestionPool/test/assErrorTextTest.php");
		$suite->addTestSuite("assErrorTextTest");

		// Answertypes
		// -------------------------------------------------------------------------------------------------------------
		require_once("./Modules/TestQuestionPool/test/assAnswerBinaryStateTest.php");
		$suite->addTestSuite("assAnswerBinaryStateTest");

		require_once("./Modules/TestQuestionPool/test/assAnswerBinaryStateImageTest.php");
		$suite->addTestSuite("assAnswerBinaryStateImageTest");

		require_once("./Modules/TestQuestionPool/test/assAnswerClozeTest.php");
		$suite->addTestSuite("assAnswerClozeTest");

		require_once("./Modules/TestQuestionPool/test/assAnswerErrorTextTest.php");
		$suite->addTestSuite("assAnswerErrorTextTest");

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
