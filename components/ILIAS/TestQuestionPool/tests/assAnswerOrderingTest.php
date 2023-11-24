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
class assAnswerOrderingTest extends assBaseTestCase
{
    protected $backupGlobals = false;

    protected function setUp(): void
    {
        chdir(__DIR__ . '/../../../../');
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $instance = new ilAssOrderingElement();

        $this->assertInstanceOf(ilAssOrderingElement::class, $instance);
    }

    public function test_setGetRandomId(): void
    {
        $instance = new ilAssOrderingElement();
        $expected = 13579;

        $instance->setRandomIdentifier($expected);
        $actual = $instance->getRandomIdentifier();

        $this->assertEquals($expected, $actual);
    }

    public function test_setGetAnswerId(): void
    {
        $instance = new ilAssOrderingElement();
        $expected = 13579;

        $instance->setId($expected);
        $actual = $instance->getId();

        $this->assertEquals($expected, $actual);
    }


    public function test_setGetOrdeingDepth(): void
    {
        $instance = new ilAssOrderingElement();
        $expected = 13579;

        $instance->setIndentation($expected);
        $actual = $instance->getIndentation();

        $this->assertEquals($expected, $actual);
    }
}
