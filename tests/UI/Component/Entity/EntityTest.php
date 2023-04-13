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

use ILIAS\UI\Implementation\Component\Entity;
use ILIAS\UI\Implementation\Component\Symbol;
use ILIAS\UI\Implementation\Component\Button;
use ILIAS\UI\Implementation\Component\Link;
use ILIAS\UI\Implementation\Component\Image;
use ILIAS\UI\Implementation\Component\Dropdown;
use ILIAS\UI\Component as I;
use ILIAS\UI\Factory as UIFactory;

class EntityTest extends ILIAS_UI_TestBase
{
    public function getEntityFactory(): Entity\Factory
    {
        return new Entity\Factory();
    }

    public function testEntityFactory(): void
    {
        $entity = $this->getEntityFactory()->standard('primary', 'secondary');
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Entity\\Standard", $entity);
        $this->assertEquals('primary', $entity->getPrimaryIdentifier());
        $this->assertEquals('secondary', $entity->getSecondaryIdentifier());
    }

    public function testEntityBasicProperties(): void
    {
        $entity = $this->getEntityFactory()->standard('primary', 'secondary');
        $this->assertEquals('bc', $entity->withBlockingAvailabilityConditions('bc')->getBlockingAvailabilityConditions());
        $this->assertEquals('fp', $entity->withFeaturedProperties('fp')->getFeaturedProperties());
        $this->assertEquals('md', $entity->withMainDetails('md')->getMainDetails());
        $this->assertEquals('ps', $entity->withPersonalStatus('ps')->getPersonalStatus());
        $this->assertEquals('a', $entity->withAvailability('a')->getAvailability());
        $this->assertEquals('d', $entity->withDetails('d')->getDetails());
    }

    public function EntityAllowedIdentiferTypes(): array
    {
        $shy_button = new Button\Shy('the label', '#');
        $shy_link = new Link\Standard('the label', '#');
        $image =  new Image\Image(Image\Image::STANDARD, 'source', 'alt');
        $icon =  new Symbol\Icon\Standard('crs', 'label', 'large', false);
        return [
            [$shy_button],
            [$shy_link],
            [$image],
            [$icon],
            ['some string']
        ];
    }

    /**
     * @dataProvider EntityAllowedIdentiferTypes
     */
    public function testEntityIdentifiers($identifier): void
    {
        $entity = $this->getEntityFactory()->standard($identifier, $identifier);
        $this->assertEquals($identifier, $entity->getPrimaryIdentifier());
        $this->assertEquals($identifier, $entity->getSecondaryIdentifier());
    }

    public function testEntityActionProperties(): void
    {
        $glyph = new Symbol\Glyph\Glyph('laugh', 'some glyph');
        $tag = new Button\Tag('tag', '#');
        $shy = new Button\Shy('shy', '#');
        $entity = $this->getEntityFactory()->standard('primary', 'secondary')
            ->withPrioritizedReactions([$glyph, $tag])
            ->withReactions([$glyph])
            ->withActions(['act'=>$shy]);

        $this->assertEquals([$glyph, $tag], $entity->getPrioritizedReactions());
        $this->assertEquals([$glyph], $entity->getReactions());
        $this->assertEquals(['act'=>$shy], $entity->getActions());
    }

    public function testEntityComponentProperties(): void
    {
        $glyph = new Symbol\Glyph\Glyph('laugh', 'some glyph');
        $tag = new Button\Tag('tag', '#');
        $shy = new Button\Shy('shy', '#');
        $entity = $this->getEntityFactory()->standard('primary', 'secondary')
            ->withPrioritizedReactions([$glyph, $tag])
            ->withReactions([$glyph])
            ->withActions(['act'=>$shy]);

        $this->assertEquals([$glyph, $tag], $entity->getPrioritizedReactions());
        $this->assertEquals([$glyph], $entity->getReactions());
        $this->assertEquals(['act'=>$shy], $entity->getActions());
    }


    public function getUIFactory(): NoUIFactory
    {
        return new class () extends NoUIFactory {
            public function dropdown(): I\Dropdown\Factory
            {
                return new Dropdown\Factory();
            }
        };
    }
    public function testEntityRendering(): void
    {
        $glyph = new Symbol\Glyph\Glyph('laugh', 'some glyph');
        $tag = new Button\Tag('tag', '#');
        $shy = new Button\Shy('shy', '#');
        $entity = $this->getEntityFactory()->standard('primary', 'secondary')
            ->withPrioritizedReactions([$glyph, $tag])
            ->withReactions([$glyph])
            ->withActions(['act'=>$shy])
            ->withBlockingAvailabilityConditions('bc')
            ->withFeaturedProperties('fp')
            ->withMainDetails('md')
            ->withPersonalStatus('ps')
            ->withAvailability('a')
            ->withDetails('d');

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($entity));
        $expected = $this->brutallyTrimHTML('
<div class="c-entity __container">
    <div class="c-entity __blocking-conditions">bc</div>
    <div class="c-entity __actions">
        <div class="dropdown"><button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" id="id_2" aria-label="actions" aria-haspopup="true" aria-expanded="false" aria-controls="id_2_menu"><span class="caret"></span></button>
            <ul id="id_2_menu" class="dropdown-menu">
                <li><button class="btn btn-link" data-action="#" id="id_1">shy</button></li>
            </ul>
        </div>
    </div>
    <div class="c-entity __secondary-identifier">secondary</div>
    <div class="c-entity __primary-identifier">primary</div>
    <div class="c-entity __featured">fp</div>
    <div class="c-entity __personal-status">ps</div>
    <div class="c-entity __main-details">md</div>
    <div class="c-entity __availability">a</div>
    <div class="c-entity __details">d</div>
    <div class="c-entity __reactions">
        <a class="glyph" aria-label="some glyph"><span class="glyphicon il-glyphicon-laugh" aria-hidden="true"></span></a>
    </div>
    <div class="c-entity __featured-reactions">
        <a class="glyph" aria-label="some glyph"><span class="glyphicon il-glyphicon-laugh" aria-hidden="true"></span></a>
        <button class="btn btn-tag btn-tag-relevance-veryhigh" data-action="#" id="id_3">tag</button>
    </div>
</div>
        ');

        $this->assertEquals($expected, $html);
    }
}
