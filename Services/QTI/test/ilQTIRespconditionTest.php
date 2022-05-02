<?php declare(strict_types=1);

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
 ********************************************************************
 */

use PHPUnit\Framework\TestCase;

class ilQTIRespconditionTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIRespcondition::class, new ilQTIRespcondition());
    }

    /**
     * @dataProvider continues
     */
    public function testSetGetContinue(string $input, ?string $expected) : void
    {
        $instance = new ilQTIRespcondition();
        $instance->setContinue($input);
        $this->assertEquals($expected, $instance->getContinue());
    }

    public function testSetGetTitle() : void
    {
        $instance = new ilQTIRespcondition();
        $instance->setTitle('Some input.');
        $this->assertEquals('Some input.', $instance->getTitle());
    }

    public function testSetGetComment() : void
    {
        $instance = new ilQTIRespcondition();
        $instance->setComment('Some input.');
        $this->assertEquals('Some input.', $instance->getComment());
    }

    public function continues() : array
    {
        class_exists(ilQTIRespcondition::class); // Force autoload to define the constants.
        return [
            ['1', ilQTIRespcondition::CONTINUE_YES],
            ['yes', ilQTIRespcondition::CONTINUE_YES],
            ['2', ilQTIRespcondition::CONTINUE_NO],
            ['no', ilQTIRespcondition::CONTINUE_NO],
            ['Random input', null],
        ];
    }
}
