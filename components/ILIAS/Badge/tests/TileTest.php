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
use ILIAS\DI\UIServices;
use ILIAS\Badge\BadgeParent;
use ILIAS\Badge\Modal;
use ILIAS\Badge\ModalContent;
use ILIAS\Badge\Tile;
use ILIAS\UI\Component\Button\Button as ButtonComponent;
use ILIAS\UI\Component\Image\Image as ImageComponent;
use ILIAS\UI\Component\Modal\Modal as ModalComponent;
use PHPUnit\Framework\TestCase;
use ilBadge;
use ilCtrl;
use ilLanguage;
use ILIAS\ResourceStorage\Services;
use PHPUnit\Framework\Attributes\DataProvider;

class TileTest extends TestCase
{
    public function testConstruct(): void
    {
        $container = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->getMock();
        $parent = $this->getMockBuilder(BadgeParent::class)->disableOriginalConstructor()->getMock();
        $modal = $this->getMockBuilder(Modal::class)->disableOriginalConstructor()->getMock();
        $sign_file = static fn(string $x): string => '';
        $format_date = static fn(int $x): string => '';

        $this->assertInstanceOf(Tile::class, new Tile($container, $parent, $modal, $sign_file, $format_date));
    }

    #[DataProvider('provideAsVariants')]
    public function testAs(string $method, array $expected_components): void
    {
        $signed_file = '/some-signed-file';
        $badge_image_path = '/file-path';
        $badge_image_name = 'Dummy image';
        $badge_image_rid_name = '43242-324234-324234-234233';
        $badge = $this->getMockBuilder(ilBadge::class)->disableOriginalConstructor()->getMock();
        $modal_content = $this->getMockBuilder(ModalContent::class)->disableOriginalConstructor()->getMock();
        $container = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->getMock();
        $parent = $this->getMockBuilder(BadgeParent::class)->disableOriginalConstructor()->getMock();
        $modal = $this->getMockBuilder(Modal::class)->disableOriginalConstructor()->getMock();
        $ui = $this->getMockBuilder(UIServices::class)->disableOriginalConstructor()->getMock();
        $ctrl = $this->getMockBuilder(ilCtrl::class)->disableOriginalConstructor()->getMock();
        $language = $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock();
        $resource_storage = $this->getMockBuilder(Services::class)->disableOriginalConstructor()->getMock();
        $language->method('txt')->willReturnCallback(
            static fn(string $lang_key) => 'Translated: ' . $lang_key
        );
        $container->method('ui')->willReturn($ui);
        $container->method('ctrl')->willReturn($ctrl);
        $container->method('language')->willReturn($language);
        $container->method('resourceStorage')->willReturn($resource_storage);
        $format_date = function (int $x): void {
            throw new \RuntimeException('Should not be called.');
        };
        $sign_file = function (string $path) use ($signed_file, $badge_image_path): string {
            return $signed_file;
        };

        $badge->method('getImagePath')->willReturn($badge_image_path);
        $badge->method('getImage')->willReturn($badge_image_name);
        $badge->method('getImageRid')->willReturn($badge_image_rid_name);
        $modal_content->method('badge')->willReturn($badge);

        $tile = new Tile($container, $parent, $modal, $sign_file, $format_date);

        $components = $tile->$method($modal_content);

        $this->assertCount(\count($expected_components), $components);
        array_map($this->assertInstanceOf(...), $expected_components, $components);
    }

    public static function provideAsVariants(): array
    {
        return [
            'Test asImage' => ['asImage', [ModalComponent::class, ImageComponent::class]],
            'Test asTitle' => ['asTitle', [ModalComponent::class, ButtonComponent::class]],
            'Test asTitleWithLeadingImage' => [
                'asTitleWithLeadingImage',
                [ModalComponent::class, ImageComponent::class, ButtonComponent::class]
            ],
        ];
    }
}
