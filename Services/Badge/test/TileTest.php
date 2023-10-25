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
use ILIAS\Badge\BadgeParent;
use ILIAS\Badge\Modal;
use ILIAS\Badge\ModalContent;
use ILIAS\Badge\Tile;
use ILIAS\UI\Component\Button\Factory as Button;
use ILIAS\UI\Component\Button\Standard as StandardButton;
use ILIAS\UI\Component\Button\Button as ButtonComponent;
use ILIAS\UI\Component\Card\Factory as Card;
use ILIAS\UI\Component\Card\Standard as StandardCard;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Image\Factory as Image;
use ILIAS\UI\Component\Image\Image as ImageComponent;
use ILIAS\UI\Component\Modal\Factory as UIModal;
use ILIAS\UI\Component\Modal\Modal as ModalComponent;
use ILIAS\UI\Component\Modal\Lightbox;
use ILIAS\UI\Component\Modal\LightboxCardPage;
use ILIAS\UI\Factory as UI;
use PHPUnit\Framework\TestCase;
use ilBadge;
use ilBadgeAssignment;
use ilCtrl;
use ilLanguage;

class TileTest extends TestCase
{
    public function testConstruct(): void
    {
        $container = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->getMock();
        $parent = $this->getMockBuilder(BadgeParent::class)->disableOriginalConstructor()->getMock();
        $modal = $this->getMockBuilder(Modal::class)->disableOriginalConstructor()->getMock();
        $sign_file = static fn (string $x): string => '';
        $format_date = static fn (int $x): string => '';

        $this->assertInstanceOf(Tile::class, new Tile($container, $parent, $modal, $sign_file, $format_date));
    }

    public function testInDeck(): void
    {
        $modal_response = ['foo'];
        $gui_class_name = 'Im a class.';
        $signed_file = '/some-signed-file';
        $remove_text = 'Translated: badge_remove_from_profile';
        $url = 'Dummy URL';
        $badge_id = 583;
        $badge_title = 'Badge title';
        $badge_image_path = '/file-path';
        $badge_image_name = 'Dummy image';

        $badge = $this->getMockBuilder(ilBadge::class)->disableOriginalConstructor()->getMock();
        $assignment = $this->getMockBuilder(ilBadgeAssignment::class)->disableOriginalConstructor()->getMock();
        $parent_component = $this->getMockBuilder(Component::class)->getMock();
        $standard_button = $this->getMockBuilder(StandardButton::class)->disableOriginalConstructor()->getMock();
        $modified_card = $this->getMockBuilder(StandardCard::class)->disableOriginalConstructor()->getMock();
        $standard_card = $this->getMockBuilder(StandardCard::class)->disableOriginalConstructor()->getMock();
        $card = $this->getMockBuilder(Card::class)->disableOriginalConstructor()->getMock();
        $card_page = $this->getMockBuilder(LightboxCardPage::class)->disableOriginalConstructor()->getMock();
        $lightbox = $this->getMockBuilder(Lightbox::class)->disableOriginalConstructor()->getMock();
        $ui_modal = $this->getMockBuilder(UIModal::class)->disableOriginalConstructor()->getMock();
        $button = $this->getMockBuilder(Button::class)->disableOriginalConstructor()->getMock();
        $image_component = $this->getMockBuilder(ImageComponent::class)->disableOriginalConstructor()->getMock();
        $image = $this->getMockBuilder(Image::class)->disableOriginalConstructor()->getMock();
        $factory = $this->getMockBuilder(UI::class)->disableOriginalConstructor()->getMock();
        $ui = $this->getMockBuilder(UIServices::class)->disableOriginalConstructor()->getMock();
        $ctrl = $this->getMockBuilder(ilCtrl::class)->disableOriginalConstructor()->getMock();
        $language = $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock();
        $container = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->getMock();
        $parent = $this->getMockBuilder(BadgeParent::class)->disableOriginalConstructor()->getMock();
        $modal = $this->getMockBuilder(Modal::class)->disableOriginalConstructor()->getMock();
        $format_date = fn (int $x): string => 'Dummy';
        $sign_file = function (string $path) use ($signed_file, $badge_image_path): string {
            $this->assertSame($badge_image_path, $path);
            return $signed_file;
        };

        $badge->method('getParentMeta')->willReturn(['type' => 'crs']);
        $badge->method('getId')->willReturn($badge_id);
        $badge->method('getTitle')->willReturn($badge_title);
        $badge->method('getImagePath')->willReturn($badge_image_path);
        $badge->method('getImage')->willReturn($badge_image_name);

        $assignment->method('getPosition')->willReturn(8);

        $modified_card->expects(self::once())->method('withSections')->with([$parent_component, $standard_button])->willReturn($modified_card);
        $modified_card->expects(self::once())->method('withImage')->willReturn($modified_card);
        $modified_card->expects(self::once())->method('withTitleAction')->willReturn($modified_card);

        $standard_card->expects(self::once())->method('withHiddenSections')->with($modal_response)->willReturn($modified_card);

        $card->expects(self::once())->method('standard')->willReturn($standard_card);

        $ui_modal->expects(self::once())->method('lightbox')->with($card_page)->willReturn($lightbox);
        $ui_modal->expects(self::once())->method('lightBoxCardPage')->with($standard_card)->willReturn($card_page);

        $button->expects(self::once())->method('standard')->with($remove_text, $url)->willReturn($standard_button);

        $image_component->expects(self::once())->method('withAction')->willReturn($image_component);

        $image->expects(self::once())->method('responsive')->with($signed_file, $badge_image_name)->willReturn($image_component);

        $factory->method('card')->willReturn($card);
        $factory->method('modal')->willReturn($ui_modal);
        $factory->method('button')->willReturn($button);
        $factory->method('image')->willReturn($image);

        $ui->method('factory')->willReturn($factory);

        $ctrl->expects(self::once())->method('getLinkTargetByClass')->with($gui_class_name, 'deactivateInCard')->willReturn($url);
        $ctrl->expects(self::exactly(2))->method('setParameterByClass')->withConsecutive(
            [$gui_class_name, 'badge_id', (string) $badge_id],
            [$gui_class_name, 'badge_id', '']
        );

        $language->method('txt')->willReturnCallback(
            static fn (string $lang_key) => 'Translated: ' . $lang_key
        );

        $container->method('ui')->willReturn($ui);
        $container->method('ctrl')->willReturn($ctrl);
        $container->method('language')->willReturn($language);

        $parent->expects(self::once())->method('asComponent')->with($badge)->willReturn($parent_component);

        $modal->expects(self::once())->method('components')->willReturn($modal_response);

        $tile = new Tile($container, $parent, $modal, $sign_file, $format_date);

        $card_and_modal = $tile->inDeck($badge, $assignment, $gui_class_name);

        $this->assertSame($modified_card, $card_and_modal['card']);
        $this->assertSame($lightbox, $card_and_modal['modal']);
    }

    /**
     * @dataProvider provideAsVariants
     */
    public function testAs(string $method, array $expected_components): void
    {
        $signed_file = '/some-signed-file';
        $badge_image_path = '/file-path';
        $badge_image_name = 'Dummy image';

        $badge = $this->getMockBuilder(ilBadge::class)->disableOriginalConstructor()->getMock();
        $modal_content = $this->getMockBuilder(ModalContent::class)->disableOriginalConstructor()->getMock();
        $container = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->getMock();
        $parent = $this->getMockBuilder(BadgeParent::class)->disableOriginalConstructor()->getMock();
        $modal = $this->getMockBuilder(Modal::class)->disableOriginalConstructor()->getMock();
        $format_date = function (int $x): void {
            throw new Exception('Should not be called.');
        };
        $sign_file = function (string $path) use ($signed_file, $badge_image_path): string {
            $this->assertSame($badge_image_path, $path);
            return $signed_file;
        };

        $badge->method('getImagePath')->willReturn($badge_image_path);
        $badge->method('getImage')->willReturn($badge_image_name);

        $modal_content->method('badge')->willReturn($badge);

        $tile = new Tile($container, $parent, $modal, $sign_file, $format_date);

        $components = $tile->$method($modal_content);

        $this->assertSame(count($expected_components), count($components));
        array_map($this->assertInstanceOf(...), $expected_components, $components);
    }

    public function provideAsVariants(): array
    {
        return [
            'Test asImage.' => ['asImage', [ModalComponent::class, ImageComponent::class]],
            'Test asTitle' => ['asTitle', [ModalComponent::class, ImageComponent::class, ButtonComponent::class]],
        ];
    }
}
