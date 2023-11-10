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

use Exception;
use ILIAS\DI\Container;
use ILIAS\DI\UIServices;
use ILIAS\Badge\Modal;
use ILIAS\Badge\ModalContent;
use ILIAS\UI\Component\Divider\Factory as Divider;
use ILIAS\UI\Component\Divider\Horizontal;
use ILIAS\UI\Component\Image\Factory as ImageFactory;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Item\Factory as ItemFactory;
use ILIAS\UI\Component\Item\Group;
use ILIAS\UI\Component\Item\Standard as Item;
use ILIAS\UI\Component\Listing\Factory as Listing;
use ILIAS\UI\Factory as UI;
use ILIAS\UI\Renderer;
use PHPUnit\Framework\TestCase;
use ilBadge;
use ilLanguage;

class ModalTest extends TestCase
{
    public function testConstruct(): void
    {
        $container = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->getMock();
        $modal = new Modal($container, static function (): void {
            throw new Exception('Should not be called.');
        });
        $this->assertInstanceOf(Modal::class, $modal);
    }

    public function testComponents(): void
    {
        $badge = $this->getMockBuilder(ilBadge::class)->disableOriginalConstructor()->getMock();
        $container = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->getMock();
        $content = $this->getMockBuilder(ModalContent::class)->disableOriginalConstructor()->getMock();
        $divider = $this->getMockBuilder(Divider::class)->getMock();
        $divider_component = $this->getMockBuilder(Horizontal::class)->getMock();
        $factory = $this->getMockBuilder(UI::class)->disableOriginalConstructor()->getMock();
        $group = $this->getMockBuilder(Group::class)->getMock();
        $image = $this->getMockBuilder(ImageFactory::class)->getMock();
        $image_component = $this->getMockBuilder(Image::class)->getMock();
        $item = $this->getMockBuilder(ItemFactory::class)->getMock();
        $item_component = $this->getMockBuilder(Item::class)->getMock();
        $language = $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock();
        $listing = $this->getMockBuilder(Listing::class)->getMock();
        $ui = $this->getMockBuilder(UIServices::class)->disableOriginalConstructor()->getMock();

        $factory->method('image')->willReturn($image);
        $factory->method('listing')->willReturn($listing);
        $factory->method('divider')->willReturn($divider);
        $factory->method('item')->willReturn($item);
        $item->method('group')->willReturn($group);
        $item->method('standard')->willReturn($item_component);
        $ui->method('factory')->willReturn($factory);
        $container->method('ui')->willReturn($ui);
        $container->method('language')->willReturn($language);
        $image->expects(self::once())->method('responsive')->willReturn($image_component);
        $divider->expects(self::once())->method('horizontal')->willReturn($divider_component);

        $properties = [
            'lorem' => 'ipsum',
            'dolor' => 'posuere',
        ];

        $item_component->method('withDescription')->willReturn($item_component);
        $item_component->method('withProperties')->with($properties)->willReturn($item_component);

        $content->method('badge')->willReturn($badge);
        $content->method('properties')->willReturn($properties);

        $modal = new Modal($container, fn(string $file): string => '/');

        $this->assertSame([$image_component, $divider_component, $item_component], $modal->components($content));
    }
}
