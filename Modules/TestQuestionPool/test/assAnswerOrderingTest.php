<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
* Unit tests
*
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class assAnswerOrderingTest extends assBaseTestCase
{
    protected $backupGlobals = false;

    protected function setUp(): void
    {
        chdir(dirname(__FILE__));
        chdir('../../../');
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/questions/class.ilAssOrderingElement.php';

        // Act
        $instance = new ilAssOrderingElement();

        $this->assertInstanceOf('ilAssOrderingElement', $instance);
    }

    public function test_setGetRandomId(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/questions/class.ilAssOrderingElement.php';
        $instance = new ilAssOrderingElement();
        $expected = 13579;

        // Act
        $instance->setRandomIdentifier($expected);
        $actual = $instance->getRandomIdentifier();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function test_setGetAnswerId(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/questions/class.ilAssOrderingElement.php';
        $instance = new ilAssOrderingElement();
        $expected = 13579;

        // Act
        $instance->setId($expected);
        $actual = $instance->getId();

        // Assert
        $this->assertEquals($expected, $actual);
    }


    public function test_setGetOrdeingDepth(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/questions/class.ilAssOrderingElement.php';
        $instance = new ilAssOrderingElement();
        $expected = 13579;

        // Act
        $instance->setIndentation($expected);
        $actual = $instance->getIndentation();

        // Assert
        $this->assertEquals($expected, $actual);
    }
}
