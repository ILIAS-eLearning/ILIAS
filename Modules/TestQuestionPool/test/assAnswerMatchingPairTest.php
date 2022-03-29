<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Unit tests
*
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class assAnswerMatchingPairTest extends assBaseTestCase
{
    protected $backupGlobals = false;

    protected function setUp() : void
    {
        chdir(dirname(__FILE__));
        chdir('../../../');
    }

    public function test_instantiateObjectSimple()
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerMatchingPair.php';

        // Act
        $instance = new assAnswerMatchingPair('test', 'testing', 0.0);

        // Assert
        $this->assertInstanceOf('assAnswerMatchingPair', $instance);
    }

    public function test_setGetTerm()
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerMatchingPair.php';
        $instance = new assAnswerMatchingPair('test', 'testing', 0.0);
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
        $instance = new assAnswerMatchingPair('test', 'testing', 0.0);
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
        $instance = new assAnswerMatchingPair('test', 'testing', 0.0);
        $expected = 3.0;

        // Act
        $instance->points = $expected;
        $actual = $instance->points;

        // Assert
        $this->assertEquals($expected, $actual);
    }
}
