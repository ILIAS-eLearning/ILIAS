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
class assAnswerMatchingTest extends assBaseTestCase
{
    protected $backupGlobals = false;

    protected function setUp(): void
    {
        chdir(dirname(__FILE__));
        chdir('../../../');
    }

    public function test_instantiateObjectSimple(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerMatching.php';

        // Act
        $instance = new ASS_AnswerMatching();

        // Assert
        $this->assertInstanceOf('ASS_AnswerMatching', $instance);
    }

    public function test_setGetPoints(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerMatching.php';
        $instance = new ASS_AnswerMatching();
        $expected = 10;

        // Act
        $instance->setPoints($expected);
        $actual = $instance->getPoints();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function test_setGetTermId(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerMatching.php';
        $instance = new ASS_AnswerMatching();
        $expected = 10;

        // Act
        $instance->setTermId($expected);
        $actual = $instance->getTermId();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function test_setGetPicture(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerMatching.php';
        $instance = new ASS_AnswerMatching();
        $expected = '/link/to/image?';

        // Act
        $instance->setPicture($expected);
        $actual = $instance->getPicture();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function test_setGetPictureId(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerMatching.php';
        $instance = new ASS_AnswerMatching();
        $expected = 47;

        // Act
        $instance->setPictureId($expected);
        $actual = $instance->getPictureId();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function test_setGetPictureId_NegativeShouldNotSetValue(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerMatching.php';
        $instance = new ASS_AnswerMatching();
        $expected = 0;

        // Act
        $instance->setPictureId(-47);
        $actual = $instance->getPictureId();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function test_setGetDefinition(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerMatching.php';
        $instance = new ASS_AnswerMatching();
        $expected = 'Definition is this.';

        // Act
        $instance->setDefinition($expected);
        $actual = $instance->getDefinition();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function test_setGetDefinitionId(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerMatching.php';
        $instance = new ASS_AnswerMatching();
        $expected = 10;

        // Act
        $instance->setDefinitionId($expected);
        $actual = $instance->getDefinitionId();

        // Assert
        $this->assertEquals($expected, $actual);
    }
}
