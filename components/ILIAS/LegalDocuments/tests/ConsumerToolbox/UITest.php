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

declare(strict_types=1);

namespace ILIAS\LegalDocuments\test\ConsumerToolbox;

use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\UI\Factory as UIFactory;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\ConsumerToolbox\UI;
use ilGlobalTemplateInterface;
use ilLanguage;

require_once __DIR__ . '/../ContainerMock.php';

class UITest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(UI::class, new UI('foo', $this->mock(UIFactory::class), $this->mock(ilGlobalTemplateInterface::class), $this->mock(ilLanguage::class)));
    }

    public function testCreate(): void
    {
        $ui_factory = $this->mock(UIFactory::class);
        $this->assertSame($ui_factory, (new UI('foo', $ui_factory, $this->mock(ilGlobalTemplateInterface::class), $this->mock(ilLanguage::class)))->create());
    }

    public function testMainTemplate(): void
    {
        $template = $this->mock(ilGlobalTemplateInterface::class);
        $this->assertSame($template, (new UI('foo', $this->mock(UIFactory::class), $template, $this->mock(ilLanguage::class)))->mainTemplate());
    }

    public function testTxt(): void
    {
        $language = $this->mockMethod(ilLanguage::class, 'txt', ['ldoc_foo'], 'baz');
        $consecutive = [
            ['bar_foo', false],
            ['ldoc_foo', true]
        ];
        $language
            ->expects(self::exactly(2))
            ->method('exists')
            ->willReturnCallback(
                function (string $txt) use (&$consecutive) {
                    [$expected, $return] = array_shift($consecutive);
                    $this->assertEquals($expected, $txt);
                    return $return;
                }
            );
        $instance = new UI('bar', $this->mock(UIFactory::class), $this->mock(ilGlobalTemplateInterface::class), $language);
        $this->assertSame('baz', $instance->txt('foo'));
    }

    public function testTxtFallback(): void
    {
        $consecutive = ['bar_foo', 'ldoc_foo'];
        $language = $this->mockMethod(ilLanguage::class, 'txt', ['foo'], 'baz');
        $language
            ->expects(self::exactly(2))
            ->method('exists')
            ->willReturnCallback(
                function (string $txt) use (&$consecutive) {
                    $this->assertEquals(array_shift($consecutive), $txt);
                    return false;
                }
            );

        $instance = new UI('bar', $this->mock(UIFactory::class), $this->mock(ilGlobalTemplateInterface::class), $language);
        $this->assertSame('baz', $instance->txt('foo'));
    }
}
