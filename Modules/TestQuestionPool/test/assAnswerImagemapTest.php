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
class assAnswerImagemapTest extends assBaseTestCase
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
        require_once './Modules/TestQuestionPool/classes/class.assAnswerImagemap.php';

        // Act
        $instance = new ASS_AnswerImagemap();

        // Assert
        $this->assertInstanceOf('ASS_AnswerImagemap', $instance);
    }

    public function test_setGetCoords(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerImagemap.php';
        $instance = new ASS_AnswerImagemap();

        // Act
        $expected = "12345";
        $instance->setCoords($expected);
        $actual = $instance->getCoords();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function test_setGetArea(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerImagemap.php';
        $instance = new ASS_AnswerImagemap();

        // Act
        $expected = "12345";
        $instance->setArea($expected);
        $actual = $instance->getArea();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function test_setGetPointsUnchecked(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerImagemap.php';
        $instance = new ASS_AnswerImagemap();

        // Act
        $expected = "12345";
        $instance->setPointsUnchecked($expected);
        $actual = $instance->getPointsUnchecked();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function test_setGetPointsUnchecked_shouldNullifyOnNonNumericPoints(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assAnswerImagemap.php';
        $instance = new ASS_AnswerImagemap();

        // Act
        $expected = 0.0;
        $instance->setPointsUnchecked('Günther');
        $actual = $instance->getPointsUnchecked();

        // Assert
        $this->assertEquals($expected, $actual);
    }
}
