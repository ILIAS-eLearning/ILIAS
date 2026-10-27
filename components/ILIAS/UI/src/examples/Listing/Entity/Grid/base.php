<?php

declare(strict_types=1);

namespace ILIAS\UI\Examples\Listing\Entity\Grid;

use Generator;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Entity\Entity;
use ILIAS\UI\Component\Entity\EntityRetrieval;

/**
 * ---
 * description: >
 *      A card-style grid presenting many entity objects side by side.
 *
 * expected output: >
 *      ILIAS shows a grid of entities looking like cards. Each card has a thumbnail image, title and some video related
 *      properties and actions. The grid reacts flexibly to the available space. If there is a lot of space, the grid
 *      will have more columns. With little available space, the cards will stack.
 * ---
 */
function base(): string
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $glyph_eye_closed = $f->symbol()->glyph()->eyeclosed();
    $glyph_with_text = $renderer->render($glyph_eye_closed) . ' offline';
    $card_data = [
        ['Snowboarding for beginners - How to avoid falling on your face', 'assets/ui-examples/images/Image/ski_widescreen-thumbnail.jpg', "Bobby's School of Snowboarding Austria", null, 'This is the perfect start for anyone wanting to get on a snowboard. We talk the best gear and the best locations for a beginner. And no worries - it is not expensive: Renting equipment will work just fine. Then we end with some first exercises to get the stability needed to tackle your first slope', '23 min', false, '01.01.2026'],
        ['The History of Bridges', 'assets/ui-examples/images/Image/sanfrancisco_widescreen-thumbnail.jpg', 'BBC England', $glyph_with_text, 'One of the most monumental achievements of human kind is the invention of bridges. Crossing streets, rivers and sometimes oceans became a huge pillar for our our modern infrastructure. This documentary looks at the different types of bridges and how they have been developed and engineered throughout different cultures and centuries', '90 min', true, '01.11.2026'],
        ['Mountains through the ages - the formation of giants', 'assets/ui-examples/images/Image/mountains_widescreen-thumbnail.jpg', 'ARD/ZDF, Canal Plus', null, "A fascinating look on the forces of nature that are able to move unimaginable tons of rocks. Find out how seemingly immovable landscape has transformed drastically through the incredible forces set free by earthquakes, vulcanos and water. This award-winning documentary traces the movement of the world's greatest mountain ranges throughout millions of years.", '45 min', false, '11.10.2026'],
        ['Snowboarding for beginners - How to avoid falling on your face', 'assets/ui-examples/images/Image/ski_widescreen-thumbnail.jpg', "Bobby's School of Snowboarding Austria", null, 'This is the perfect start for anyone wanting to get on a snowboard. We talk the best gear and the best locations for a beginner. And no worries - it is not expensive: Renting equipment will work just fine. Then we end with some first exercises to get the stability needed to tackle your first slope', '23 min', false, '28.02.2026'],
        ['The History of Bridges', 'assets/ui-examples/images/Image/sanfrancisco_widescreen-thumbnail.jpg', 'BBC England', $glyph_with_text, 'One of the most monumental achievements of human kind is the invention of bridges. Crossing streets, rivers and sometimes oceans became a huge pillar for our our modern infrastructure. This documentary looks at the different types of bridges and how they have been developed and engineered throughout different cultures and centuries', '90 min', true, '15.12.2025'],
        ['Mountains through the ages - the formation of giants', 'assets/ui-examples/images/Image/mountains_widescreen-thumbnail.jpg', 'ARD/ZDF, Canal Plus', null, "A fascinating look on the forces of nature that are able to move unimaginable tons of rocks. Find out how seemingly immovable landscape has transformed drastically through the incredible forces set free by earthquakes, vulcanos and water. This award-winning documentary traces the movement of the world's greatest mountain ranges throughout millions of years.", '45 min', false, '09.06.2026']
    ];

    $listing = $f->listing()->entity()->grid(new GridEntityRetrieval($card_data));

    return $renderer->render($listing);
}

class GridEntityRetrieval implements EntityRetrieval
{
    public function __construct(
        private readonly array $data,
    ) {
    }

    public function getEntities(
        UIFactory $ui_factory,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters,
    ): Generator {
        foreach ($this->data as $index => $record) {
            yield $this->mapRecord($ui_factory, $index, $record);
        }
    }

    public function getEntitiesByIds(
        UIFactory $ui_factory,
        Order $order,
        array $entity_ids,
    ): Generator {
        foreach ($entity_ids as $entity_id) {
            if (!isset($this->data[$entity_id])) {
                continue;
            }
            yield $this->mapRecord($ui_factory, $entity_id, $this->data[$entity_id]);
        }
    }

    protected function mapRecord(UIFactory $ui_factory, int|string $id, array $record): Entity
    {
        $glyph_user = $ui_factory->symbol()->glyph()->user()
            ->withLabel('Created by');

        $glyph_calendar = $ui_factory->symbol()->glyph()->calendar()
            ->withLabel('Upload date');

        $glyph_duration = $ui_factory->symbol()->glyph()->time()
            ->withLabel('Duration');

        list($title, $thumbnail_url, $creator, $availability, $description, $duration, $add_workflow, $date) = $record;
        $managing_actions = [
            $ui_factory->button()->shy('Edit', '#'),
            $ui_factory->button()->shy('Move', '#'),
        ];
        $entity = $ui_factory->entity()->standard(
            $id,
            $ui_factory->link()->standard($title, ''),
            $ui_factory->image()->responsive($thumbnail_url, $title)->withAction('#')
        )
            ->withFeaturedProperties(
                $ui_factory->listing()->property()
                    ->withProperty($glyph_user, $creator)
            )
            ->withManagingActions(...$managing_actions)
            ->withMainDetails(
                $ui_factory->listing()->property()
                    ->withProperty($glyph_duration, $description, false)
                    ->withProperty($glyph_duration, $duration)
                    ->withProperty($glyph_calendar, $date)
            )
            ->withPrioritizedReactions($ui_factory->button()->shy('Like', '#')->withSymbol($ui_factory->symbol()->glyph()->like()))
        ;
        if ($availability) {
            $entity = $entity->withBlockingAvailabilityConditions(
                $ui_factory->listing()->property()
                    ->withProperty('Status', $ui_factory->legacy()->content($availability), false)
            );
        }
        if ($add_workflow) {
            $workflow_factory = $ui_factory->listing()->workflow();
            $dummy_step = $workflow_factory->step('', '');

            $steps = [
                $workflow_factory->step('Upload video file', 'Upload an .mp4 file or start a recording.', '#')
                    ->withAvailability($dummy_step::NOT_ANYMORE)->withStatus($dummy_step::SUCCESSFULLY),
                $workflow_factory->step('Cut video', 'Trim or remove parts of the video.', '#')
                    ->withAvailability($dummy_step::NOT_ANYMORE)->withStatus($dummy_step::NOT_STARTED),
                $workflow_factory->step('Add subtitles', 'You must upload or generate subtitles for every video.', '#')
                    ->withAvailability($dummy_step::AVAILABLE)->withStatus($dummy_step::SUCCESSFULLY),
                $workflow_factory->step('Publish', 'Set who can see this video.', '#')
                    ->withAvailability($dummy_step::AVAILABLE)->withStatus($dummy_step::NOT_STARTED),
            ];

            $video_workflow = $workflow_factory->linear('Video Curation', $steps);

            $entity = $entity->withWorkflow($video_workflow);
        }

        return $entity;
    }
}
