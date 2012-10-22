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
class assAnswerMatchingPairTest extends PHPUnit_Framework_TestCase
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
		require_once './Modules/TestQuestionPool/classes/class.assAnswerMatchingPair.php';

		// Act
		$instance = new assAnswerMatchingPair();

		// Assert
		$this->assertInstanceOf('assAnswerMatchingPair', $instance);
	}

	public function test_setGetTerm()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerMatchingPair.php';
		$instance = new assAnswerMatchingPair();
		$expected = 'Term';

		// Act
		$instance->term = $expected;
		$actual = $instance->term;

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_setGetDefinition()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerMatchingPair.php';
		$instance = new assAnswerMatchingPair();
		$expected = 'Definition';

		// Act
		$instance->definition = $expected;
		$actual = $instance->definition;

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_setGetPoints()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerMatchingPair.php';
		$instance = new assAnswerMatchingPair();
		$expected = 'Definition';

		// Act
		$instance->points = $expected;
		$actual = $instance->points;

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_setGetHokum()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerMatchingPair.php';
		$instance = new assAnswerMatchingPair();
		$expected = null;

		// Act
		$instance->hokum = 'Hokum Value';
		$actual = $instance->hokum;

		// Assert
		$this->assertEquals($expected, $actual);
	}


}
