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
use ILIAS\UI\Implementation\Component\Listing;
use ILIAS\UI\Implementation\Component\Legacy\Legacy;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\Component as I;

class EntityTest extends ILIAS_UI_TestBase
{
    protected function getEntityFactory(): Entity\Factory
    {
        return new Entity\Factory();
    }

    protected function legacy(string $string): Legacy
    {
        return new Legacy($string, (new SignalGenerator()));
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
        $this->assertEquals([$this->legacy('bc')], $entity->withBlockingAvailabilityConditions($this->legacy('bc'))->getBlockingAvailabilityConditions());
        $this->assertEquals([$this->legacy('fp')], $entity->withFeaturedProperties($this->legacy('fp'))->getFeaturedProperties());
        $this->assertEquals([$this->legacy('md')], $entity->withMainDetails($this->legacy('md'))->getMainDetails());
        $this->assertEquals([$this->legacy('ps')], $entity->withPersonalStatus($this->legacy('ps'))->getPersonalStatus());
        $this->assertEquals([$this->legacy('a')], $entity->withAvailability($this->legacy('a'))->getAvailability());
        $this->assertEquals([$this->legacy('d')], $entity->withDetails($this->legacy('d'))->getDetails());
    }

    public static function getEntityAllowedIdentiferTypes(): array
    {
        $shy_button = new Button\Shy('the label', '#');
        $shy_link = new Link\Standard('the label', '#');
        $image = new Image\Image(I\Image\Image::STANDARD, 'source', 'alt');
        $icon = new Symbol\Icon\Standard('crs', 'label', 'large', false);
        return [
            [$shy_button],
            [$shy_link],
            [$image],
            [$icon],
            ['some string']
        ];
    }

    /**
     * @dataProvider getEntityAllowedIdentiferTypes
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
            ->withPrioritizedReactions($glyph, $tag)
            ->withReactions($glyph, $glyph, $glyph)
            ->withManagingActions($shy);

        $this->assertEquals([$glyph, $tag], $entity->getPrioritizedReactions());
        $this->assertEquals([$glyph,$glyph,$glyph], $entity->getReactions());
        $this->assertEquals([$shy], $entity->getManagingActions());
    }

    public function testEntityComponentProperties(): void
    {
        $glyph = new Symbol\Glyph\Glyph('laugh', 'some glyph');
        $tag = new Button\Tag('tag', '#');
        $shy = new Button\Shy('shy', '#');
        $entity = $this->getEntityFactory()->standard('primary', 'secondary')
            ->withPrioritizedReactions($glyph, $tag)
            ->withReactions($glyph)
            ->withManagingActions($shy);

        $this->assertEquals([$glyph, $tag], $entity->getPrioritizedReactions());
        $this->assertEquals([$glyph], $entity->getReactions());
        $this->assertEquals([$shy], $entity->getManagingActions());
    }

    public function testEntityWorkflowButtons(): void
    {
        $workflow_factory = $this->getUIFactory()->listing()->workflow();
        $dummy_step = $workflow_factory->step('', '');

        // Creating Workflow Steps
        $steps = [
            $workflow_factory->step("Upload video file", "Upload an .mp4 file or start a recording.", "#")
                ->withAvailability($dummy_step::NOT_ANYMORE)->withStatus($dummy_step::SUCCESSFULLY),
            $workflow_factory->step("Cut video", "Trim or remove parts of the video.", "#")
                ->withAvailability($dummy_step::AVAILABLE)->withStatus($dummy_step::NOT_STARTED),
            $workflow_factory->step("Add subtitles", "You must upload or generate subtitles for every video.", "#")
                ->withAvailability($dummy_step::AVAILABLE)->withStatus($dummy_step::NOT_STARTED),
            $workflow_factory->step("Publish", "Set who can see this video.", "#")
                ->withAvailability($dummy_step::NOT_AVAILABLE)->withStatus($dummy_step::NOT_AVAILABLE),
        ];

        $video_workflow = $workflow_factory->linear("Video Curation", $steps);

        $entity = $this->getEntityFactory()->standard('primary', 'secondary')
            ->withWorkflow($video_workflow);

        $rendered_entity = $this->getDefaultRenderer()->render($entity);

        $this->assertStringContainsString("Cut video</button>", $rendered_entity);
        $this->assertStringContainsString("Add subtitles</button>", $rendered_entity);
        $this->assertStringNotContainsString("Upload video file</button>", $rendered_entity);
        $this->assertStringNotContainsString("Publish</button>", $rendered_entity);
    }

    public function getUIFactory(): NoUIFactory
    {
        return new class () extends NoUIFactory {
            public function dropdown(): I\Dropdown\Factory
            {
                return new Dropdown\Factory();
            }
            public function button(): I\Button\Factory
            {
                return new Button\Factory();
            }
            public function listing(): I\Listing\Factory
            {
                return new Listing\Factory();
            }
        };
    }
    public function testEntityRendering(): void
    {
        $glyph = new Symbol\Glyph\Glyph('laugh', 'some glyph');
        $tag = new Button\Tag('tag', '#');
        $shy = new Button\Shy('shy', '#');
        $entity = $this->getEntityFactory()->standard('primary', 'secondary')
            ->withPrioritizedReactions($glyph, $tag)
            ->withReactions($glyph, $glyph)
            ->withManagingActions($shy, $shy)
            ->withBlockingAvailabilityConditions($this->legacy('bc'))
            ->withFeaturedProperties($this->legacy('fp'))
            ->withMainDetails($this->legacy('md'))
            ->withPersonalStatus($this->legacy('ps'))
            ->withAvailability($this->legacy('a'))
            ->withDetails($this->legacy('d'));

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($entity));
        $expected = $this->brutallyTrimHTML('
<section aria-labelledby="id_1" class="c-entity__container">
   <div class="c-entity__featured-headerbar l-bar__container">
      <div class="l-bar__space-keeper l-bar__space-keeper--space-between">
         <div class="l-bar__group">
            <div class="l-bar__element">
               <div class="c-entity__blocking-conditions l-bar__element">bc</div>
               <div id="id_1" class="c-entity__primary-identifier">primary</div>
            </div>
         </div>
         <div class="c-entity__actions-container l-bar__group">
            <div class="c-entity__actions-manage l-bar__element">
               <div class="dropdown" id="id_10">
                  <button class="btn btn-default dropdown-toggle" type="button" aria-label="actions" aria-haspopup="true" aria-expanded="false" aria-controls="id_10_menu"><span class="caret"></span></button>
                  <ul id="id_10_menu" class="dropdown-menu">
                     <li><button class="btn btn-link" data-action="#" id="id_8">shy</button></li>
                     <li><button class="btn btn-link" data-action="#" id="id_9">shy</button></li>
                  </ul>
               </div>
            </div>
         </div>
      </div>
   </div>
   <div class="c-entity__secondary-identifier --string ">secondary</div>
   <div class="c-entity__featured l-bar__element">fp</div>
   <div class="c-entity__personal-status">ps</div>
   <div class="c-entity__main-details">md</div>
   <div class="c-entity__availability">a</div>
   <div class="c-entity__details">d</div>
   <div class="c-entity__reactionbar l-bar__container">
      <div class="l-bar__space-keeper l-bar__space-keeper--space-between">
         <div class="l-bar__group">
            <div class="l-bar__element">
               <div class="c-entity__reactions"><a class="glyph" aria-label="some glyph"><span class="glyphicon il-glyphicon-laugh" aria-hidden="true"></span></a><a class="glyph" aria-label="some glyph"><span class="glyphicon il-glyphicon-laugh" aria-hidden="true"></span></a></div>
            </div>
         </div>
         <div class="l-bar__group">
            <div class="c-entity__featured-reactions"><a class="glyph" aria-label="some glyph"><span class="glyphicon il-glyphicon-laugh" aria-hidden="true"></span></a><button class="btn btn-tag btn-tag-relevance-veryhigh" data-action="#" id="id_11">tag</button></div>
         </div>
      </div>
   </div>
</section>
');
        $this->assertEquals($expected, $html);
    }
}
