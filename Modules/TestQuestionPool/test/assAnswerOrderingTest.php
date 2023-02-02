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
