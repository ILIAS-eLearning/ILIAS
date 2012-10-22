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
class assAnswerMatchingTest extends PHPUnit_Framework_TestCase
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
		require_once './Modules/TestQuestionPool/classes/class.assAnswerMatching.php';

		// Act
		$instance = new ASS_AnswerMatching();

		// Assert
		$this->assertInstanceOf('ASS_AnswerMatching',$instance);
	}

	public function test_setGetPoints()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerMatching.php';
		$instance = new ASS_AnswerMatching();
		$expected = 10;

		// Act
		$instance->setPoints($expected);
		$actual = $instance->getPoints();

		// Assert
		$this->assertEquals($expected,$actual);
	}

	public function test_setGetTermId()
	{
	// Arrange
	require_once './Modules/TestQuestionPool/classes/class.assAnswerMatching.php';
	$instance = new ASS_AnswerMatching();
	$expected = 10;

	// Act
	$instance->setTermId($expected);
	$actual = $instance->getTermId();

	// Assert
	$this->assertEquals($expected,$actual);
	}

	public function test_setGetPicture()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerMatching.php';
		$instance = new ASS_AnswerMatching();
		$expected = '/link/to/image?';

		// Act
		$instance->setPicture($expected);
		$actual = $instance->getPicture();

		// Assert
		$this->assertEquals($expected,$actual);
	}

	public function test_setGetPictureId()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerMatching.php';
		$instance = new ASS_AnswerMatching();
		$expected = 47;

		// Act
		$instance->setPictureId($expected);
		$actual = $instance->getPictureId();

		// Assert
		$this->assertEquals($expected,$actual);
	}

	public function test_setGetPictureId_NegativeShouldNotSetValue()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerMatching.php';
		$instance = new ASS_AnswerMatching();
		$expected = 0;

		// Act
		$instance->setPictureId(-47);
		$actual = $instance->getPictureId();

		// Assert
		$this->assertEquals($expected,$actual);
	}

	public function test_setGetDefinition()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerMatching.php';
		$instance = new ASS_AnswerMatching();
		$expected = 'Definition is this.';

		// Act
		$instance->setDefinition($expected);
		$actual = $instance->getDefinition();

		// Assert
		$this->assertEquals($expected,$actual);
	}

	public function test_setGetDefinitionId()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerMatching.php';
		$instance = new ASS_AnswerMatching();
		$expected = 10;

		// Act
		$instance->setDefinitionId($expected);
		$actual = $instance->getDefinitionId();

		// Assert
		$this->assertEquals($expected,$actual);
	}
}
