<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

/** 
* Unit tests
* 
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class assAnswerClozeTest extends PHPUnit_Framework_TestCase
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

	public function test_constructorShouldReturnInstance()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';

		// Act
		$instance = new assAnswerCloze();

		// Assert
		$this->assertNotNull($instance);
	}
	
	public function test_setGetLowerBound()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';
		$instance = new assAnswerCloze('2');
		
		// Act
		$expected = '1';
		$instance->setLowerBound($expected);
		$actual = $instance->getLowerBound();
		
		// Assert
		$this->assertEquals($expected, $actual);
	}
	
	public function test_setGetLowerBond_GreaterThanAnswerShouldSetAnswertext()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';
		$instance = new assAnswerCloze('2');

		// Act
		$expected = '2';
		$instance->setLowerBound(4);
		$actual = $instance->getLowerBound();

		// Assert
		$this->assertEquals($expected, $actual);		
	}
	
	public function test_setGetLowerBound_nonNumericShouldSetAnswertext()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';
		$instance = new assAnswerCloze('2');

		// Act
		$expected = '2';
		$instance->setLowerBound('test');
		$actual = $instance->getLowerBound();

		// Assert
		$this->assertEquals($expected, $actual);
	}
	
	public function test_setGetUpperBound()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';
		$instance = new assAnswerCloze('1');

		// Act
		$expected = '3';
		$instance->setUpperBound($expected);
		$actual = $instance->getUpperBound();

		// Assert
		$this->assertEquals($expected, $actual);		
	}
	
	public function test_setGetUpperBound_smallerThanAnswerShouldSetAnswertext()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';
		$instance = new assAnswerCloze('4');

		// Act
		$expected = '4';
		$instance->setUpperBound(2);
		$actual = $instance->getUpperBound();

		// Assert
		$this->assertEquals($expected, $actual);
	}
	
	public function test_setGetUpperBound_nonNumericShouldSetAnswertext()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';
		$instance = new assAnswerCloze('4');

		// Act
		$expected = '4';
		$instance->setUpperBound('test');
		$actual = $instance->getUpperBound();

		// Assert
		$this->assertEquals($expected, $actual);		
	}
}
