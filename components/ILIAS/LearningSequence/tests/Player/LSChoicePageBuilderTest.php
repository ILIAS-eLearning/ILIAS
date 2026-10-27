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

use PHPUnit\Framework\TestCase;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\ILIASObject\Properties\CoreProperties\TileImage\TileImage;
use ILIAS\LearningSequence\Player\LSChoicePageBuilder;
use ILIAS\UI\Component\Listing\Factory as ListingFactory;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Implementation\Component as CImpl;

class LSChoicePageBuilderTest extends TestCase
{
    protected const GOTO_COMMAND = 'goto';
    protected const OBJ_ID = 42;

    protected UIFactory $ui_factory;
    protected ilLanguage $lng;
    protected ilObjectDataCache $obj_data_cache;
    protected LSUrlBuilder $url_builder;

    protected function setUp(): void
    {
        $this->lng = $this->createStub(ilLanguage::class);
        $this->lng->method('txt')->willReturnArgument(0);

        $this->obj_data_cache = $this->createStub(ilObjectDataCache::class);
        $this->obj_data_cache->method('lookupObjId')->willReturn(self::OBJ_ID);

        $listing_factory = $this->createStub(ListingFactory::class);
        $listing_factory->method('property')->willReturnCallback(
            static fn() => new CImpl\Listing\Property()
        );

        $ui_factory = $this->createStub(UIFactory::class);
        $ui_factory->method('button')->willReturn(new CImpl\Button\Factory());
        $ui_factory->method('card')->willReturn(new CImpl\Card\Factory());
        $ui_factory->method('image')->willReturn(new CImpl\Image\Factory());
        $ui_factory->method('listing')->willReturn($listing_factory);
        $ui_factory->method('symbol')->willReturn(new CImpl\Symbol\Factory(
            new CImpl\Symbol\Icon\Factory(),
            new CImpl\Symbol\Glyph\Factory($this->createStub(\ILIAS\Language\Language::class)),
            new CImpl\Symbol\Avatar\Factory()
        ));
        $ui_factory->method('deck')->willReturnCallback(
            static fn(array $cards) => new CImpl\Deck\Deck($cards, CImpl\Deck\Deck::SIZE_S)
        );
        $this->ui_factory = $ui_factory;

        $this->url_builder = new LSUrlBuilder((new DataFactory())->uri('https://ilias.de/somepath'));
    }

    protected function getBuilder(): LSChoicePageBuilder
    {
        $tile_image = $this->createStub(TileImage::class);
        $tile_image->method('getImage')->willReturnCallback(
            static fn(ILIAS\UI\Component\Image\Factory $image_factory) => $image_factory->responsive('tile.jpg', '')
        );

        return new class (
            $this->ui_factory,
            $this->lng,
            $this->obj_data_cache,
            $this->url_builder,
            self::GOTO_COMMAND,
            $tile_image
        ) extends LSChoicePageBuilder {
            public function __construct(
                UIFactory $ui_factory,
                ilLanguage $lang,
                ilObjectDataCache $obj_data_cache,
                LSUrlBuilder $url_builder,
                string $goto_command,
                protected TileImage $tile_image
            ) {
                parent::__construct($ui_factory, $lang, $obj_data_cache, $url_builder, $goto_command);
            }

            protected function getObjectTileImage(LSLearnerItem $item): TileImage
            {
                return $this->tile_image;
            }
        };
    }

    protected function getItem(string $title, string $description): LSLearnerItem
    {
        $ls_item = new LSItem(
            'crs',
            $title,
            $description,
            'icon_path',
            true,
            10,
            new ilLSPostCondition(30, 'always'),
            30,
            3
        );
        return new LSLearnerItem(6, 2, 3, $ls_item);
    }

    protected function getStartUrl(): string
    {
        return $this->url_builder->getHref(self::GOTO_COMMAND, self::OBJ_ID);
    }

    public function testBuildReturnsOnlyADeckOfRepositoryObjectCards(): void
    {
        $content = $this->getBuilder()->build([
            $this->getItem('first', 'description'),
            $this->getItem('second', '')
        ]);

        $this->assertCount(1, $content);
        $this->assertInstanceOf(CImpl\Deck\Deck::class, $content[0]);
        $this->assertCount(2, $content[0]->getCards());
        $this->assertContainsOnlyInstancesOf(ILIAS\UI\Component\Card\RepositoryObject::class, $content[0]->getCards());
    }

    public function testCardUsesTheTileImageOfTheObject(): void
    {
        $card = $this->getBuilder()->buildCard($this->getItem('first', ''));
        $this->assertInstanceOf(CImpl\Card\RepositoryObject::class, $card);

        $this->assertEquals('tile.jpg', $card->getImage()->getSource());
        $this->assertEquals('first', $card->getImage()->getAlt());
    }

    public function testCardLinksTheTitleButNotTheImage(): void
    {
        $card = $this->getBuilder()->buildCard($this->getItem('first', ''));
        $this->assertInstanceOf(CImpl\Card\RepositoryObject::class, $card);

        $this->assertEquals('first', $card->getTitle());
        $this->assertEquals($this->getStartUrl(), $card->getTitleAction());
        $this->assertEmpty($card->getImage()->getAction());
    }

    public function testCardShowsTheObjectIcon(): void
    {
        $card = $this->getBuilder()->buildCard($this->getItem('first', ''));
        $this->assertInstanceOf(CImpl\Card\RepositoryObject::class, $card);

        $icon = $card->getObjectIcon();
        $this->assertInstanceOf(CImpl\Symbol\Icon\Custom::class, $icon);
        $this->assertEquals('icon_path', $icon->getIconPath());
        $this->assertEquals('obj_crs', $icon->getLabel());
        $this->assertEquals(Icon::MEDIUM, $icon->getSize());
    }

    public function testCardWithDescription(): void
    {
        $card = $this->getBuilder()->buildCard($this->getItem('first', ' description '));
        $this->assertInstanceOf(CImpl\Card\RepositoryObject::class, $card);

        $sections = $card->getSections();
        $this->assertCount(2, $sections);
        $this->assertInstanceOf(CImpl\Listing\Property::class, $sections[0]);
        $this->assertEquals([['description', 'description', false]], $sections[0]->getItems());
        $this->assertInstanceOf(ILIAS\UI\Component\Button\Standard::class, $sections[1]);
        $this->assertEquals(LSChoicePageBuilder::START_LABEL, $sections[1]->getLabel());
        $this->assertEquals($this->getStartUrl(), $sections[1]->getAction());
    }

    public function testCardWithoutDescription(): void
    {
        $card = $this->getBuilder()->buildCard($this->getItem('second', ''));
        $this->assertInstanceOf(CImpl\Card\RepositoryObject::class, $card);

        $sections = $card->getSections();
        $this->assertCount(1, $sections);
        $this->assertInstanceOf(ILIAS\UI\Component\Button\Standard::class, $sections[0]);
    }

    public function testCardEscapesTheTitle(): void
    {
        $card = $this->getBuilder()->buildCard($this->getItem('<b>first</b>', ''));
        $this->assertInstanceOf(CImpl\Card\RepositoryObject::class, $card);

        $this->assertEquals('&lt;b&gt;first&lt;/b&gt;', $card->getTitle());
    }
}
