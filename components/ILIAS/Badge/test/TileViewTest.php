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

use ILIAS\DI\Container;
use ILIAS\HTTP\Services;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use ILIAS\Badge\Tile;
use ILIAS\Badge\PresentationHeader;
use ILIAS\Badge\TileView;
use ilObjUser;
use ILIAS\UI\Factory as UI;
use ILIAS\DI\UIServices;
use ILIAS\UI\Component\Deck\Deck;
use ilCtrl;
use ILIAS\UI\Component\ViewControl\Factory as ViewControl;
use ILIAS\UI\Component\ViewControl\Sortation;
use ILIAS\UI\Renderer;
use ilLanguage;

class TileViewTest extends TestCase
{
    public function testConstruct(): void
    {
        $container = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->getMock();
        $tile = $this->getMockBuilder(Tile::class)->disableOriginalConstructor()->getMock();
        $head = $this->getMockBuilder(PresentationHeader::class)->disableOriginalConstructor()->getMock();

        $tile = new TileView($container, 'Some class.', $tile, $head);
        $this->assertInstanceOf(TileView::class, $tile);
    }

    public function testShow(): void
    {
        $request = $this->getMockBuilder(ServerRequest::class)->disableOriginalConstructor()->getMock();
        $request->method('getQueryParams')->willReturn([]);

        $http = $this->getMockBuilder(Services::class)->disableOriginalConstructor()->getMock();
        $http->method('request')->willReturn($request);

        $user = $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->getMock();

        $deck = $this->getMockBuilder(Deck::class)->disableOriginalConstructor()->getMock();

        $sortation = $this->getMockBuilder(Sortation::class)->disableOriginalConstructor()->getMock();
        $sortation->method('withTargetURL')->willReturn($sortation);
        $sortation->method('withLabel')->willReturn($sortation);

        $view_control = $this->getMockBuilder(ViewControl::class)->disableOriginalConstructor()->getMock();
        $view_control->method('sortation')->willReturn($sortation);

        $factory = $this->getMockBuilder(UI::class)->disableOriginalConstructor()->getMock();
        $factory->method('deck')->willReturn($deck);
        $factory->method('viewControl')->willReturn($view_control);

        $renderer = $this->getMockBuilder(Renderer::class)->disableOriginalConstructor()->getMock();
        $renderer->method('render')->willReturn('');

        $ui = $this->getMockBuilder(UIServices::class)->disableOriginalConstructor()->getMock();
        $ui->method('factory')->willReturn($factory);
        $ui->method('renderer')->willReturn($renderer);

        $ctrl = $this->getMockBuilder(ilCtrl::class)->disableOriginalConstructor()->getMock();

        $language = $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock();
        $language->method('txt')->willReturnCallback(static fn (string $name): string => $name);

        $container = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->getMock();
        $container->method('http')->willReturn($http);
        $container->method('user')->willReturn($user);
        $container->method('ui')->willReturn($ui);
        $container->method('ctrl')->willReturn($ctrl);
        $container->method('language')->willReturn($language);

        $tile = $this->getMockBuilder(Tile::class)->disableOriginalConstructor()->getMock();
        $head = $this->getMockBuilder(PresentationHeader::class)->disableOriginalConstructor()->getMock();

        $assignments_of_user = static fn () => [];
        $tile = new TileView($container, 'Some class.', $tile, $head, $assignments_of_user);

        $this->assertEquals('', $tile->show());
    }
}
