<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Unit tests
*
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class assAnswerBinaryStateImageTest extends assBaseTestCase
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
        require_once './Modules/TestQuestionPool/classes/class.assAnswerBinaryStateImage.php';

        // Act
        $instance = new ASS_AnswerBinaryStateImage();

        $this->assertInstanceOf('ASS_AnswerBinaryStateImage', $instance);
    }

    public function test_setGetImage() : void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerBinaryStateImage.php';
        $instance = new ASS_AnswerBinaryStateImage();
        $expected = 'image';
        // Act
        $instance->setImage($expected);
        $actual = $instance->getImage();

        $this->assertEquals($expected, $actual);
    }
}
