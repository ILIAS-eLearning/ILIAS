<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Unit tests
*
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class assAnswerMatchingTermTest extends assBaseTestCase
{
    protected $backupGlobals = false;

    protected function setUp() : void
    {
        chdir(dirname(__FILE__));
        chdir('../../../');
    }

    public function test_instantiateObjectSimple() : void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerMatchingTerm.php';

        // Act
        $instance = new assAnswerMatchingTerm();

        // Assert
        $this->assertInstanceOf('assAnswerMatchingTerm', $instance);
    }

    public function test_setGetText() : void
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

    public function test_setGetPicture() : void
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

    public function test_getUnsetPicture() : void
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

    public function test_setGetIdentifier() : void
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
}
