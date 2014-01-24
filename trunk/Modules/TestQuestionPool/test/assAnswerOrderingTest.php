<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/** 
* Unit tests
* 
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class assAnswerOrderingTest extends PHPUnit_Framework_TestCase
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

	public function test_instantiateObject_shouldReturnInstance()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerOrdering.php';

		// Act
		$instance = new ASS_AnswerOrdering();

		$this->assertInstanceOf('ASS_AnswerOrdering', $instance);
	}

	public function test_setGetRandomId()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerOrdering.php';
		$instance = new ASS_AnswerOrdering();
		$expected = 13579;

		// Act
		$instance->setRandomID($expected);
		$actual = $instance->getRandomID();

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_setGetAnswerId()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerOrdering.php';
		$instance = new ASS_AnswerOrdering();
		$expected = 13579;

		// Act
		$instance->setAnswerId($expected);
		$actual = $instance->getAnswerId();

		// Assert
		$this->assertEquals($expected, $actual);
	}


	public function test_setGetOrdeingDepth()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerOrdering.php';
		$instance = new ASS_AnswerOrdering();
		$expected = 13579;

		// Act
		$instance->setOrderingDepth($expected);
		$actual = $instance->getOrderingDepth();

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_getAdditionalOrderingFieldsByRandomId()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerOrdering.php';
		$instance = new ASS_AnswerOrdering();
		$random_id = 13579;

		//require_once './Services/PEAR/lib/MDB2.php';
		require_once './Services/Database/classes/class.ilDB.php';
		$ildb_mock = $this->getMock('ilDBMySQL', array('queryF', 'fetchAssoc'), array(), '', false, false);
		$ildb_mock->expects( $this->once() )
				  ->method( 'queryF' )
				  ->with( $this->equalTo('SELECT * FROM qpl_a_ordering WHERE random_id = %s'),
						  $this->equalTo(array('integer')),
						  $this->equalTo(array($random_id))
					)
				  ->will( $this->returnValue('Test') );
		$ildb_mock->expects( $this->exactly(2) )
				  ->method( 'fetchAssoc' )
				  ->with( $this->equalTo('Test') )
				  ->will( $this->onConsecutiveCalls(array('answer_id' => 123, 'depth' => 456), false ) );
		global $ilDB;
		$ilDB = $ildb_mock;

		// Act
		$instance->getAdditionalOrderingFieldsByRandomId($random_id);

		// Assert
		$this->assertEquals(123, $instance->getAnswerId());
		$this->assertEquals(456, $instance->getOrderingDepth());
	}
}
