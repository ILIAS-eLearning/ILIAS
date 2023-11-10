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

use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;
use ILIAS\Badge\BadgeParent;
use ILIAS\UI\Component\Listing\Descriptive;
use ILIAS\UI\Component\Legacy\Legacy;
use ilBadge;
use ILIAS\UI\Factory as UI;
use ILIAS\DI\UIServices;
use ilLanguage;
use ilAccess;
use ILIAS\UI\Component\Listing\Factory as Listing;
use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Link\Factory as Link;
use ILIAS\UI\Component\Link\Standard as StandardLink;

class BadgeParentTest extends TestCase
{
    public function testConstruct(): void
    {
        $container = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->getMock();
        $parent = new BadgeParent($container);
        $this->assertInstanceOf(BadgeParent::class, $parent);
    }

    public function testShowWithParentReadRight(): void
    {
        $rendered = 'Rendered components';

        $access = $this->getMockBuilder(ilAccess::class)->disableOriginalConstructor()->getMock();
        $badge = $this->getMockBuilder(ilBadge::class)->disableOriginalConstructor()->getMock();
        $container = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->getMock();
        $descriptive = $this->getMockBuilder(Descriptive::class)->getMock();
        $factory = $this->getMockBuilder(UI::class)->disableOriginalConstructor()->getMock();
        $language = $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock();
        $legacy = $this->getMockBuilder(Legacy::class)->disableOriginalConstructor()->getMock();
        $link = $this->getMockBuilder(Link::class)->disableOriginalConstructor()->getMock();
        $listing = $this->getMockBuilder(Listing::class)->disableOriginalConstructor()->getMock();
        $renderer = $this->getMockBuilder(Renderer::class)->disableOriginalConstructor()->getMock();
        $standard_link = $this->getMockBuilder(StandardLink::class)->disableOriginalConstructor()->getMock();
        $ui = $this->getMockBuilder(UIServices::class)->disableOriginalConstructor()->getMock();

        $language->method('txt')->willReturnCallback(static fn(string $name): string => $name . ' translated');

        $listing->expects(self::once())->method('descriptive')->with([
            'object translated' => $legacy,
        ])->willReturn($descriptive);

        $link->method('standard')->with('Lorem', 'Some URL.')->willReturn($standard_link);

        $factory->method('listing')->willReturn($listing);
        $factory->method('link')->willReturn($link);
        $factory->expects(self::once())->method('legacy')->with($rendered)->willReturn($legacy);

        $renderer->expects(self::once())->method('render')->willReturn($rendered);

        $ui->method('factory')->willReturn($factory);
        $ui->method('renderer')->willReturn($renderer);

        $access->expects(self::once())->method('checkAccess')->with('read', '', 89)->willReturn(true);

        $container->method('ui')->willReturn($ui);
        $container->method('language')->willReturn($language);
        $container->method('access')->willReturn($access);

        $parent_id = 6879;
        $badge->method('getParentId')->willReturn($parent_id);
        $badge->method('getParentMeta')->willReturn([
            'id' => $parent_id,
            'type' => 'crs',
            'title' => 'Lorem',
        ]);

        $icon = function (int $id, string $size, string $type): string {
            $this->assertSame([6879, 'big', 'crs'], [$id, $size, $type]);
            return 'Some image path.';
        };

        $references_of = static fn(): array => [89];

        $link_to = function (int $ref_id): string {
            $this->assertSame(89, $ref_id);
            return 'Some URL.';
        };

        $parent = new BadgeParent($container, $icon, $references_of, $link_to);
        $this->assertSame($descriptive, $parent->asComponent($badge));
    }

    public function testShowWithoutParentReadRight(): void
    {
        $rendered = 'Rendered components';

        $access = $this->getMockBuilder(ilAccess::class)->disableOriginalConstructor()->getMock();
        $badge = $this->getMockBuilder(ilBadge::class)->disableOriginalConstructor()->getMock();
        $container = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->getMock();
        $descriptive = $this->getMockBuilder(Descriptive::class)->getMock();
        $factory = $this->getMockBuilder(UI::class)->disableOriginalConstructor()->getMock();
        $language = $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock();
        $legacy = $this->getMockBuilder(Legacy::class)->disableOriginalConstructor()->getMock();
        $listing = $this->getMockBuilder(Listing::class)->disableOriginalConstructor()->getMock();
        $parent_link = $this->getMockBuilder(Legacy::class)->disableOriginalConstructor()->getMock();
        $renderer = $this->getMockBuilder(Renderer::class)->disableOriginalConstructor()->getMock();
        $ui = $this->getMockBuilder(UIServices::class)->disableOriginalConstructor()->getMock();

        $language->method('txt')->willReturnCallback(static fn(string $name): string => $name . ' translated');

        $listing->expects(self::once())->method('descriptive')->with([
            'object translated' => $legacy,
        ])->willReturn($descriptive);

        $factory->method('listing')->willReturn($listing);
        $factory->method('legacy')->withConsecutive(['Lorem'], [$rendered])->willReturnOnConsecutiveCalls($parent_link, $legacy);

        $renderer->expects(self::once())->method('render')->willReturn($rendered);

        $ui->method('factory')->willReturn($factory);
        $ui->method('renderer')->willReturn($renderer);

        $access->expects(self::once())->method('checkAccess')->with('read', '', 89)->willReturn(false);

        $container->method('ui')->willReturn($ui);
        $container->method('language')->willReturn($language);
        $container->method('access')->willReturn($access);

        $parent_id = 6879;
        $badge->method('getParentId')->willReturn($parent_id);
        $badge->method('getParentMeta')->willReturn([
            'id' => $parent_id,
            'type' => 'crs',
            'title' => 'Lorem',
        ]);

        $icon = function (int $id, string $size, string $type): string {
            $this->assertSame([6879, 'big', 'crs'], [$id, $size, $type]);
            return 'Some image path.';
        };

        $references_of = static fn(): array => [89];

        $link_to = function (int $ref_id): string {
            $this->assertSame(89, $ref_id);
            return 'Some URL.';
        };

        $parent = new BadgeParent($container, $icon, $references_of, $link_to);
        $this->assertSame($descriptive, $parent->asComponent($badge));
    }
}
