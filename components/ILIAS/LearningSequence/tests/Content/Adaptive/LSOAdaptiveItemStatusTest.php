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
use ILIAS\LearningSequence\Content\Adaptive\LSOAdaptiveItemStatus;
use ILIAS\LearningSequence\Content\Condition\StaticInputConfigurationIssueDetail;
use ILIAS\UI\Component\Button\Tag;
use ILIAS\UI\Component\Listing\Factory as ListingFactory;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Implementation\Component as CImpl;

class LSOAdaptiveItemStatusTest extends TestCase
{
    protected LSOAdaptiveItemStatus $item_status;

    protected function setUp(): void
    {
        $lng = $this->createStub(ilLanguage::class);
        $lng->method('txt')->willReturnArgument(0);

        $listing_factory = $this->createStub(ListingFactory::class);
        $listing_factory->method('property')->willReturnCallback(
            static fn() => new CImpl\Listing\Property()
        );

        $ui_factory = $this->createStub(UIFactory::class);
        $ui_factory->method('button')->willReturn(new CImpl\Button\Factory());
        $ui_factory->method('listing')->willReturn($listing_factory);

        $this->item_status = new LSOAdaptiveItemStatus($ui_factory, $lng);
    }

    /**
     * @param StaticInputConfigurationIssueDetail[] $issue_details
     */
    protected function getRecord(
        bool $is_online = true,
        string $start_object = '',
        string $end_object = '',
        bool $has_conflicting_input_configuration = false,
        array $issue_details = [],
        bool $has_structural_predecessor = true,
        bool $has_structural_successor = true
    ): ilObjLearningSequenceContentData {
        return new ilObjLearningSequenceContentData(
            10,
            20,
            'title',
            'description',
            'crs',
            'icon_path',
            'href',
            $is_online,
            $start_object,
            $end_object,
            '',
            '',
            [],
            [],
            $has_conflicting_input_configuration,
            $issue_details,
            $has_structural_predecessor,
            $has_structural_successor
        );
    }

    /**
     * @return array<string, string> relevance by label
     */
    protected function getTags(ilObjLearningSequenceContentData $record): array
    {
        $relevance_by_label = [];
        foreach ($this->item_status->getTags($record) as $tag) {
            $this->assertSame('', $tag->getAction());
            $relevance_by_label[$tag->getLabel()] = $tag->getRelevance();
        }
        return $relevance_by_label;
    }

    public function testOnlineItemInTheMiddleOfThePath(): void
    {
        $this->assertSame(['table_online' => Tag::REL_LOW], $this->getTags($this->getRecord()));
    }

    public function testOfflineItem(): void
    {
        $this->assertSame(
            ['lso_adaptive_offline' => Tag::REL_MID],
            $this->getTags($this->getRecord(is_online: false))
        );
    }

    public function testStartAndEndObject(): void
    {
        $this->assertSame(
            [
                'table_online' => Tag::REL_LOW,
                'lso_adaptive_start' => Tag::REL_LOW,
                'lso_adaptive_end' => Tag::REL_LOW
            ],
            $this->getTags($this->getRecord(start_object: 'start', end_object: 'end'))
        );
    }

    public function testUnreachableDeadEnd(): void
    {
        $this->assertSame(
            [
                'table_online' => Tag::REL_LOW,
                'lso_adaptive_dead_end' => Tag::REL_HIGH,
                'lso_adaptive_entry_point' => Tag::REL_LOW
            ],
            $this->getTags($this->getRecord(
                has_structural_predecessor: false,
                has_structural_successor: false
            ))
        );
    }

    public function testMisconfiguredItem(): void
    {
        $this->assertSame(
            [
                'table_online' => Tag::REL_LOW,
                'lso_adaptive_misconfigured' => Tag::REL_HIGH
            ],
            $this->getTags($this->getRecord(has_conflicting_input_configuration: true))
        );
    }

    public function testNoConfigurationIssuesForAWellConfiguredItem(): void
    {
        $this->assertNull($this->item_status->getConfigurationIssues($this->getRecord()));
    }

    public function testConfigurationIssueWithoutDetails(): void
    {
        $this->assertSame(
            'lso_adaptive_misconfigured_info',
            $this->item_status->getConfigurationIssues($this->getRecord(has_conflicting_input_configuration: true))
        );
    }

    public function testConfigurationIssuesWithDetails(): void
    {
        $issues = $this->item_status->getConfigurationIssues($this->getRecord(
            has_conflicting_input_configuration: true,
            issue_details: [
                new StaticInputConfigurationIssueDetail(10, 'issue_one', 'issue_one_info', ['objects' => ['b', 'a']]),
                new StaticInputConfigurationIssueDetail(10, 'issue_two')
            ]
        ));

        $this->assertInstanceOf(CImpl\Listing\Property::class, $issues);
        $this->assertSame(
            [
                ['issue_one', 'issue_one issue_one_info objects: a, b', false],
                ['issue_two', 'issue_two', false]
            ],
            $issues->getItems()
        );
    }
}
