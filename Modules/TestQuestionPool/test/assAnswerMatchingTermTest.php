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
class assAnswerMatchingTermTest extends assBaseTestCase
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
        require_once './Modules/TestQuestionPool/classes/class.assAnswerMatchingTerm.php';

        // Act
        $instance = new assAnswerMatchingTerm();

        // Assert
        $this->assertInstanceOf('assAnswerMatchingTerm', $instance);
    }

    public function test_setGetText(): void
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

    public function test_setGetPicture(): void
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

    public function test_getUnsetPicture(): void
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

    public function test_setGetIdentifier(): void
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
