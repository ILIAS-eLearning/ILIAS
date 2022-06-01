<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Unit tests
*
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class assNumericRangeTest extends assBaseTestCase
{
    protected $backupGlobals = false;

    protected function setUp() : void
    {
        chdir(dirname(__FILE__));
        chdir('../../../');
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assNumericRange.php';

        // Act
        $instance = new assNumericRange();

        $this->assertInstanceOf('assNumericRange', $instance);
    }

    public function test_setGetLowerLimit_shouldReturnUnchangedLowerLimit() : void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assNumericRange.php';
        $instance = new assNumericRange();
        $expected = 1.00;

        // Act
        $instance->setLowerLimit($expected);
        $actual = $instance->getLowerLimit();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function test_setGetUpperLimit_shouldReturnUnchangedUpperLimit() : void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assNumericRange.php';
        $instance = new assNumericRange();
        $expected = 10.00;

        // Act
        $instance->setUpperLimit($expected);
        $actual = $instance->getUpperLimit();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function test_setGetOrder_shouldReturnUnchangedOrder() : void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assNumericRange.php';
        $instance = new assNumericRange();
        $expected = 10;

        // Act
        $instance->setOrder($expected);
        $actual = $instance->getOrder();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function test_setPoints_shouldReturnUnchangedPoints() : void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assNumericRange.php';
        $instance = new assNumericRange();
        $expected = 10;

        // Act
        $instance->setPoints($expected);
        $actual = $instance->getPoints();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function test_contains_shouldReturnTrueIfValueIsContained() : void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assNumericRange.php';
        $instance = new assNumericRange();
        $instance->setLowerLimit(1.00);
        $instance->setUpperLimit(10.00);
        $expected = true;

        // Act
        $actual = $instance->contains(5.00);

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function test_contains_shouldReturnFalseIfValueIsNotContained() : void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assNumericRange.php';
        $instance = new assNumericRange();
        $instance->setLowerLimit(1.00);
        $instance->setUpperLimit(10.00);
        $expected = false;

        // Act
        $actual = $instance->contains(15.00);

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function test_contains_shouldReturnFalseIfValueIsHokum() : void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assNumericRange.php';
        $instance = new assNumericRange();
        $instance->setLowerLimit(1.00);
        $instance->setUpperLimit(10.00);
        $expected = false;

        // Act
        $actual = $instance->contains('Günther');

        // Assert
        $this->assertEquals($expected, $actual);
    }
}
