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
class assAnswerBinaryStateTest extends PHPUnit_Framework_TestCase
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
        require_once './Modules/TestQuestionPool/classes/class.assAnswerBinaryState.php';

        // Act
        $instance = new ASS_AnswerBinaryState();

        $this->assertInstanceOf('ASS_AnswerBinaryState', $instance);
    }

    public function test_setGetState_shouldReturnUnchangedState()
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerBinaryState.php';
        $instance = new ASS_AnswerBinaryState();
        $expected = 1;

        // Act
        $instance->setState($expected);
        $actual = $instance->getState();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function test_isStateChecked_shouldReturnActualState()
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerBinaryState.php';
        $instance = new ASS_AnswerBinaryState();
        $expected = 1;

        // Act
        $instance->setState($expected);
        $actual = $instance->isStateChecked();

        // Assert
        $this->assertEquals($expected, $actual);

    }

    public function test_isStateSet_shouldReturnActualState()
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerBinaryState.php';
        $instance = new ASS_AnswerBinaryState();
        $expected = 1;

        // Act
        $instance->setState($expected);
        $actual = $instance->isStateSet();

        // Assert
        $this->assertEquals($expected, $actual);

    }

    public function test_isStateUnset_shouldReturnActualState()
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerBinaryState.php';
        $instance = new ASS_AnswerBinaryState();
        $expected = 1;

        // Act
        $instance->setState($expected);
        $actual = !$instance->isStateUnset();

        // Assert
        $this->assertEquals($expected, $actual);

    }

    public function test_isStateUnchecked_shouldReturnActualState()
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerBinaryState.php';
        $instance = new ASS_AnswerBinaryState();
        $expected = 1;

        // Act
        $instance->setState($expected);
        $actual = !$instance->isStateUnchecked();

        // Assert
        $this->assertEquals($expected, $actual);
    }

	public function test_setChecked_shouldAlterState()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerBinaryState.php';
		$instance = new ASS_AnswerBinaryState();
		$expected = 0;
		$instance->setState($expected);

		// Act
		$instance->setChecked();
		$actual = $instance->isStateUnchecked();

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_setUnchecked_shouldAlterState()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerBinaryState.php';
		$instance = new ASS_AnswerBinaryState();
		$expected = 1;
		$instance->setState($expected);

		// Act
		$instance->setUnchecked();
		$actual = $instance->isStateUnchecked();

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_setSet_shouldAlterState()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerBinaryState.php';
		$instance = new ASS_AnswerBinaryState();
		$expected = 0;
		$instance->setState($expected);

		// Act
		$instance->setSet();
		$actual = $instance->isStateUnchecked();

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_setUnset_shouldAlterState()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerBinaryState.php';
		$instance = new ASS_AnswerBinaryState();
		$expected = 1;
		$instance->setState($expected);

		// Act
		$instance->setUnset();
		$actual = $instance->isStateUnchecked();

		// Assert
		$this->assertEquals($expected, $actual);
	}
}