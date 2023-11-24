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
class assAnswerMatchingTest extends assBaseTestCase
{
    protected $backupGlobals = false;

    protected function setUp(): void
    {
        chdir(__DIR__ . '../../../../');
    }

    public function test_instantiateObjectSimple(): void
    {
        $instance = new ASS_AnswerMatching();

        $this->assertInstanceOf(ASS_AnswerMatching::class, $instance);
    }

    public function test_setGetPoints(): void
    {
        $instance = new ASS_AnswerMatching();
        $expected = 10;

        $instance->setPoints($expected);
        $actual = $instance->getPoints();

        $this->assertEquals($expected, $actual);
    }

    public function test_setGetTermId(): void
    {
        $instance = new ASS_AnswerMatching();
        $expected = 10;

        $instance->setTermId($expected);
        $actual = $instance->getTermId();

        $this->assertEquals($expected, $actual);
    }

    public function test_setGetPicture(): void
    {
        $instance = new ASS_AnswerMatching();
        $expected = '/link/to/image?';

        $instance->setPicture($expected);
        $actual = $instance->getPicture();

        $this->assertEquals($expected, $actual);
    }

    public function test_setGetPictureId(): void
    {
        $instance = new ASS_AnswerMatching();
        $expected = 47;

        $instance->setPictureId($expected);
        $actual = $instance->getPictureId();

        $this->assertEquals($expected, $actual);
    }

    public function test_setGetPictureId_NegativeShouldNotSetValue(): void
    {
        $instance = new ASS_AnswerMatching();
        $expected = 0;

        $instance->setPictureId(-47);
        $actual = $instance->getPictureId();

        $this->assertEquals($expected, $actual);
    }

    public function test_setGetDefinition(): void
    {
        $instance = new ASS_AnswerMatching();
        $expected = 'Definition is this.';

        $instance->setDefinition($expected);
        $actual = $instance->getDefinition();

        $this->assertEquals($expected, $actual);
    }

    public function test_setGetDefinitionId(): void
    {
        $instance = new ASS_AnswerMatching();
        $expected = 10;

        $instance->setDefinitionId($expected);
        $actual = $instance->getDefinitionId();

        $this->assertEquals($expected, $actual);
    }
}
