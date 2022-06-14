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
 *********************************************************************/

namespace ILIAS\Tests\Data\RFC;

use ILIAS\Data\Result\Ok;
use ILIAS\Data\Result\Error;
use ILIAS\Data\LanguageTag;
use ILIAS\Data\RFC\LanguageTagDefinition;
use PHPUnit\Framework\TestCase;

class LanguageTagTest extends TestCase
{
    public function testConstructSuccessful() : void
    {
        $definition = $this->getMockBuilder(LanguageTagDefinition::class)->disableOriginalConstructor()->getMock();
        $definition->expects(self::once())->method('parse')->with('hej')->willReturn(new Ok('hej'));

        $this->assertInstanceOf(LanguageTag::class, new LanguageTag($definition, 'hej'));
    }

    public function testConstructWithInvalidLanguageTag() : void
    {
        $this->expectExceptionMessage('Not valid.');

        $definition = $this->getMockBuilder(LanguageTagDefinition::class)->disableOriginalConstructor()->getMock();
        $definition->expects(self::once())->method('parse')->with('hej')->willReturn(new Error('Not valid.'));
        new LanguageTag($definition, 'hej');
    }

    public function testValue() : void
    {
        $definition = $this->getMockBuilder(LanguageTagDefinition::class)->disableOriginalConstructor()->getMock();
        $definition->expects(self::once())->method('parse')->with('hej')->willReturn(new Ok('ho'));

        $this->assertEquals('ho', (new LanguageTag($definition, 'hej'))->value());
    }
}
