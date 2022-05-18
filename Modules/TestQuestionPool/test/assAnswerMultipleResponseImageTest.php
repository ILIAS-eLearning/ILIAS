<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Unit tests
*
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class assAnswerMultipleResponseImageTest extends assBaseTestCase
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
        require_once './Modules/TestQuestionPool/classes/class.assAnswerMultipleResponseImage.php';

        // Act
        $instance = new ASS_AnswerMultipleResponseImage();

        $this->assertInstanceOf('ASS_AnswerMultipleResponseImage', $instance);
    }

    public function test_setGetImage() : void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerMultipleResponseImage.php';
        $instance = new ASS_AnswerMultipleResponseImage();
        $expected = 'c:\image.jpg';

        // Act
        $instance->setImage($expected);
        $actual = $instance->getImage();

        // Assert
        $this->assertEquals($expected, $actual);
    }
}
