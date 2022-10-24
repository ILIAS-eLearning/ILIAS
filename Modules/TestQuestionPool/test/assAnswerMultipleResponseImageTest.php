<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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

    protected function setUp(): void
    {
        chdir(dirname(__FILE__));
        chdir('../../../');
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerMultipleResponseImage.php';

        // Act
        $instance = new ASS_AnswerMultipleResponseImage();

        $this->assertInstanceOf('ASS_AnswerMultipleResponseImage', $instance);
    }

    public function test_setGetImage(): void
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
