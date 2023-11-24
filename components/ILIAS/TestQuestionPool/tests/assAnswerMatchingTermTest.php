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
* @ingroup components\ILIASTestQuestionPool
*/
class assAnswerMatchingTermTest extends assBaseTestCase
{
    protected $backupGlobals = false;

    protected function setUp(): void
    {
        chdir(__DIR__ . '../../../../');
    }

    public function test_instantiateObjectSimple(): void
    {
        $instance = new assAnswerMatchingTerm();

        $this->assertInstanceOf(assAnswerMatchingTerm::class, $instance);
    }

    public function test_setGetText(): void
    {
        $instance = new assAnswerMatchingTerm();
        $expected = 'Text';

        $instance = $instance->withText($expected);
        $actual = $instance->getText();

        $this->assertEquals($expected, $actual);
    }

    public function test_setGetPicture(): void
    {
        $instance = new assAnswerMatchingTerm();
        $expected = 'path/to/picture?';

        $instance = $instance->withPicture($expected);
        $actual = $instance->getPicture();

        $this->assertEquals($expected, $actual);
    }

    public function test_getUnsetPicture(): void
    {
        $instance = new assAnswerMatchingTerm();
        $expected = null;

        $actual = $instance->getPicture();

        $this->assertEquals($expected, $actual);
    }

    public function test_setGetIdentifier(): void
    {
        $instance = new assAnswerMatchingTerm();
        $expected = 12345;

        $instance = $instance->withIdentifier($expected);
        $actual = $instance->getIdentifier();

        $this->assertEquals($expected, $actual);
    }
}
