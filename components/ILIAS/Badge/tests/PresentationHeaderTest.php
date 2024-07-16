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

namespace ILIAS\Badge\test;

use ILIAS\Badge\PresentationHeader;
use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;
use ilToolbarGUI;
use ILIAS\UI\Factory as UI;
use ILIAS\DI\UIServices;
use ILIAS\UI\Component\ViewControl\Factory as ViewControl;
use ILIAS\UI\Component\ViewControl\Mode;
use ilCtrl;
use ILIAS\UI\Component\Component;
use ilLanguage;

class PresentationHeaderTest extends TestCase
{
    public function testConstruct(): void
    {
        $container = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->getMock();
        $head = new PresentationHeader($container, 'Some class.');
        $this->assertInstanceOf(PresentationHeader::class, $head);
    }

    /**
     * @dataProvider showProvider
     */
    public function testShow(bool $additional = false): void
    {
        $mode = $this->getMockBuilder(Mode::class)->disableOriginalConstructor()->getMock();
        $mode->expects(self::once())->method('withActive')->with('tile_view')->willReturn($mode);

        $view_control = $this->getMockBuilder(ViewControl::class)->disableOriginalConstructor()->getMock();
        $view_control->expects(self::once())->method('mode')->with([
            'tile_view' => 'list URL',
            'table_view' => 'manage URL',
        ])->willReturn($mode);

        $additional_component = [];
        $consecutive_expected = [$mode];
        if ($additional) {
            $mock = $this->getMockBuilder(Component::class)->getMock();
            $additional_component[] = [$mock];
            $consecutive_expected[] = $mock;
        }

        $toolbar = $this->getMockBuilder(ilToolbarGUI::class)->disableOriginalConstructor()->getMock();
        $toolbar
            ->expects(self::exactly($additional + 1))
            ->method('addStickyItem')
            ->willReturnCallback(
                function ($component) use (&$consecutive_expected) {
                    $expected = array_shift($consecutive_expected);
                    $this->assertEquals($expected, $component);
                }
            );


        $factory = $this->getMockBuilder(UI::class)->disableOriginalConstructor()->getMock();
        $factory->expects(self::once())->method('viewControl')->willReturn($view_control);

        $ui = $this->getMockBuilder(UIServices::class)->disableOriginalConstructor()->getMock();
        $ui->method('factory')->willReturn($factory);

        $consecutive = [
            ['Some class.', 'listBadges', 'list URL'],
            ['Some class.', 'manageBadges', 'manage URL'],
        ];
        $ctrl = $this->getMockBuilder(ilCtrl::class)->disableOriginalConstructor()->getMock();
        $ctrl
            ->expects(self::exactly(2))
            ->method('getLinkTargetByClass')
            ->willReturnCallback(
                function ($class, $cmd) use (&$consecutive) {
                    list($expected_class, $expected_cmd, $ret) = array_shift($consecutive);
                    $this->assertEquals($class, $expected_class);
                    $this->assertEquals($cmd, $expected_cmd);
                    return $ret;
                }
            );

        $language = $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock();
        $language->method('txt')->willReturnCallback(static fn(string $name): string => $name);

        $container = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->getMock();
        $container->expects(self::once())->method('toolbar')->willReturn($toolbar);
        $container->method('ui')->willReturn($ui);
        $container->method('ctrl')->willReturn($ctrl);
        $container->method('language')->willReturn($language);

        $head = new PresentationHeader($container, 'Some class.');
        $head->show('tile_view', $additional_component[0][0] ?? null);
    }

    public static function showProvider(): array
    {
        return [
            'Without additional component' => [],
            'With additional component' => [true],
        ];
    }
}
