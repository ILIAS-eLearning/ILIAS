<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/** 
* Unit tests for assErrorTextTest
* 
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class assErrorTextTest extends PHPUnit_Framework_TestCase
{
	protected $backupGlobals = FALSE;

	protected function setUp()
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
	}

	public function test_instantiateObjectSimple()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assErrorText.php';
		
		// Act
		$instance = new assErrorText();
		
		// Assert
		$this->assertNotNull($instance);
	}

	public function test_getErrorsFromText()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assErrorText.php';
		$instance = new assErrorText();
		
		$errortext = '
			Eine ((Kündigung)) kommt durch zwei gleichlautende Willenserklärungen zustande.
			Ein Vertrag kommt durch ((drei gleichlaute)) Willenserklärungen zustande.
			Ein Kaufvertrag an der Kasse im Supermarkt kommt durch das legen von Ware auf das 
			Kassierband und den Kassiervorgang zustande. Dies nennt man ((konsequentes)) Handeln.';
		
		$expected = array( 0 => 'Kündigung', 1 => 'drei gleichlaute', 2 => 'konsequentes' );
		
		// Act
		$actual = $instance->getErrorsFromText($errortext);
		
		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_getErrorsFromText_noMatch()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assErrorText.php';
		$instance = new assErrorText();

		$errortext = '
			Eine Kündigung)) kommt durch zwei gleichlautende (Willenserklärungen) zustande.
			Ein Vertrag kommt durch (drei gleichlaute) Willenserklärungen zustande.
			Ein Kaufvertrag an der Kasse im Supermarkt [kommt] durch das #legen von Ware auf das 
			Kassierband und den [[Kassiervorgang]] zustande. Dies nennt man *konsequentes Handeln.';

		$expected = array();

		// Act
		$actual = $instance->getErrorsFromText($errortext);

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_getErrorsFromText_emptyArgShouldPullInternal()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assErrorText.php';
		$instance = new assErrorText();

		$errortext = '
			Eine ((Kündigung)) kommt durch zwei gleichlautende Willenserklärungen zustande.
			Ein Vertrag kommt durch ((drei gleichlaute)) Willenserklärungen zustande.
			Ein Kaufvertrag an der Kasse im Supermarkt kommt durch das legen von Ware auf das 
			Kassierband und den Kassiervorgang zustande. Dies nennt man ((konsequentes)) Handeln.';

		$expected = array( 0 => 'Kündigung', 1 => 'drei gleichlaute', 2 => 'konsequentes' );

		// Act
		$instance->setErrorText($errortext);
		$actual = $instance->getErrorsFromText('');

		// Assert
		$this->assertEquals($expected, $actual);
	}
	
	public function test_setErrordata_newError()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assErrorText.php';
		$instance = new assErrorText();
		
		$errordata = array ('error1');
		require_once "./Modules/TestQuestionPool/classes/class.assAnswerErrorText.php";
		$expected = new assAnswerErrorText($errordata[0], '', 0.0);
		
		// Act
		$instance->setErrorData($errordata);
		
		$all_errors = $instance->getErrorData();
		$actual = $all_errors[0];
		
		// Assert
		$this->assertEquals($expected, $actual);		
	}
	
	public function test_setErrordata_oldErrordataPresent()
	{
		$this->markTestIncomplete('No good way to prepopulate errordata to make this test meaningful.');
		return;
		
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assErrorText.php';
		$instance = new assErrorText();

		$errordata = array ('error1');
		require_once "./Modules/TestQuestionPool/classes/class.assAnswerErrorText.php";
		$expected = new assAnswerErrorText($errordata[0], 'correct1', 10.0);
		$instance->errordata = $expected;
		
		// Act
		$instance->setErrorData($errordata);

		$all_errors = $instance->getErrorData();
		$actual = $all_errors[0];

		// Assert
		$this->assertEquals($expected, $actual);
	}	
	

}
