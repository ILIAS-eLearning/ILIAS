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
class assAnswerMatchingTermTest extends PHPUnit_Framework_TestCase
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
		require_once './Modules/TestQuestionPool/classes/class.assAnswerMatchingTerm.php';

		// Act
		$instance = new assAnswerMatchingTerm();

		// Assert
		$this->assertInstanceOf('assAnswerMatchingTerm', $instance);
	}

	public function test_setGetText()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerMatchingTerm.php';
		$instance = new assAnswerMatchingTerm();
		$expected = 'Text';

		// Act
		$instance->text = $expected;
		$actual = $instance->text;

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_setGetPicture()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerMatchingTerm.php';
		$instance = new assAnswerMatchingTerm();
		$expected = 'path/to/picture?';

		// Act
		$instance->picture = $expected;
		$actual = $instance->picture;

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_getUnsetPicture()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerMatchingTerm.php';
		$instance = new assAnswerMatchingTerm();
		$expected = null;

		// Act
		$actual = $instance->picture;

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_setGetIdentifier()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerMatchingTerm.php';
		$instance = new assAnswerMatchingTerm();
		$expected = 12345;

		// Act
		$instance->identifier = $expected;
		$actual = $instance->identifier;

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_setGetHokum()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerMatchingTerm.php';
		$instance = new assAnswerMatchingTerm();
		$expected = null;

		// Act
		$instance->hokum = 'Hokum Value';
		$actual = $instance->hokum;

		// Assert
		$this->assertEquals($expected, $actual);
	}
}
