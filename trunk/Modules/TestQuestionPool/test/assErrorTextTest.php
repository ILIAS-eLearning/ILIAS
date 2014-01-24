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

			require_once './Services/UICore/classes/class.ilCtrl.php';
			$ilCtrl_mock = $this->getMock('ilCtrl');
			$ilCtrl_mock->expects( $this->any() )->method( 'saveParameter' );
			$ilCtrl_mock->expects( $this->any() )->method( 'saveParameterByClass' );
			global $ilCtrl;
			$ilCtrl = $ilCtrl_mock;

			require_once './Services/Language/classes/class.ilLanguage.php';
			$lng_mock = $this->getMock('ilLanguage', array('txt'), array(), '', false);
			//$lng_mock->expects( $this->once() )->method( 'txt' )->will( $this->returnValue('Test') );
			global $lng;
			$lng = $lng_mock;

			$ilias_mock = new stdClass();
			$ilias_mock->account = new stdClass();
			$ilias_mock->account->id = 6;
			$ilias_mock->account->fullname = 'Esther Tester';
			global $ilias;
			$ilias = $ilias_mock;
		}
	}

	public function test_instantiateObjectSimple()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assErrorText.php';

		// Act
		$instance = new assErrorText();

		// Assert
		$this->assertInstanceOf('assErrorText', $instance);
	}

	public function test_getErrorsFromText()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assErrorText.php';
		$instance = new assErrorText();

		$errortext = '
			Eine ((Kündigung)) kommt durch zwei gleichlautende Willenserklärungen zustande.
			Ein Vertrag kommt durch ((drei gleichlaute)) Willenserklärungen zustande.
			Ein Kaufvertrag an der Kasse im #Supermarkt kommt durch das legen von Ware auf das
			Kassierband und den Kassiervorgang zustande. Dies nennt man ((konsequentes)) Handeln.';

		$expected = array(
			'passages'  => array( 0 => 'Kündigung',  1=> 'drei gleichlaute', 3 => 'konsequentes'),
			'words'     => array( 2 => 'Supermarkt')
		);

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
			Ein Kaufvertrag an der Kasse im Supermarkt [kommt] durch das legen von Ware auf das
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
			Ein Kaufvertrag an der Kasse im #Supermarkt kommt durch das legen von Ware auf das
			Kassierband und den Kassiervorgang zustande. Dies nennt man ((konsequentes)) Handeln.';

		$expected = array(
			'passages'  => array( 0 => 'Kündigung',  1=> 'drei gleichlaute', 3 => 'konsequentes'),
			'words'     => array( 2 => 'Supermarkt')
		);

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

		$errordata = array ('passages' => array ( 0 => 'drei Matrosen'), 'words' => array());
		require_once "./Modules/TestQuestionPool/classes/class.assAnswerErrorText.php";
		$expected = new assAnswerErrorText($errordata['passages'][0], '', 0.0);

		// Act
		$instance->setErrorData($errordata);

		$all_errors = $instance->getErrorData();
		$actual = $all_errors[0];

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_setErrordata_oldErrordataPresent()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assErrorText.php';
		$instance = new assErrorText();

		$errordata = array ('passages' => array ( 0 => 'drei Matrosen'), 'words' => array());
		require_once "./Modules/TestQuestionPool/classes/class.assAnswerErrorText.php";
		$expected = new assAnswerErrorText($errordata['passages'][0], '', 0);
		$instance->errordata = $expected;

		// Act
		$instance->setErrorData($errordata);

		$all_errors = $instance->getErrorData();
		$actual = $all_errors[0];

		// Assert
		$this->assertEquals($expected, $actual);
	}
}
