<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/** 
* Unit tests
* 
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class assAnswerBinaryStateImageTest extends PHPUnit_Framework_TestCase
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
		require_once './Modules/TestQuestionPool/classes/class.assAnswerBinaryStateImage.php';

		// Act
		$instance = new ASS_AnswerBinaryStateImage();

		$this->assertInstanceOf('ASS_AnswerBinaryStateImage', $instance);
	}

	public function test_setGetImage()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerBinaryStateImage.php';
		$instance = new ASS_AnswerBinaryStateImage();
		$expected = 'image';
		// Act
		$instance->setImage($expected);
		$actual = $instance->getImage();

		$this->assertEquals($expected, $actual );
	}
}
