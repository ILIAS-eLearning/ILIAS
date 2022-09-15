<?php

declare(strict_types=1);

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

class ilBuddySystemStateFactoryTest extends ilBuddySystemBaseTest
{
    private ilBuddySystemRelationStateFactory $stateFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $lng = $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock();
        $lng->method('txt')->willReturnCallback(static function (string $keyword): string {
            return $keyword;
        });

        ilBuddySystemRelationStateFactory::getInstance($lng)->reset();
        $this->stateFactory = ilBuddySystemRelationStateFactory::getInstance($lng);
    }

    public function testInitialStateEqualsUnlinkedRelation(): void
    {
        $this->assertInstanceOf(
            ilBuddySystemUnlinkedRelationState::class,
            $this->stateFactory->getInitialState()
        );
    }

    public function testStatesCanBeReceivedAsOptionMap(): void
    {
        $validStates = $this->stateFactory->getValidStates();
        $this->assertThat(count($validStates), $this->greaterThan(0));

        foreach ($this->stateFactory->getValidStates() as $state) {
            $tableFilterStateMapper = $this->stateFactory->getTableFilterStateMapper($state);

            $otions = $tableFilterStateMapper->optionsForState();
            $this->assertThat(count($otions), $this->greaterThan(0));

            array_walk($otions, function (string $value, string $key): void {
                $this->assertNotEmpty($value, 'Option value for table filter must not be empty');
                $this->assertNotEmpty($key, 'Option key for table filter must not be empty');
            });
        }
    }

    public function testRelationsCanBeFilteredByState(): void
    {
        $validStates = $this->stateFactory->getValidStates();
        $this->assertThat(count($validStates), $this->greaterThan(0));

        foreach ($this->stateFactory->getValidStates() as $state) {
            $tableFilterStateMapper = $this->stateFactory->getTableFilterStateMapper($state);

            $otions = $tableFilterStateMapper->optionsForState();
            $this->assertThat(count($otions), $this->greaterThan(0));

            array_walk($otions, function (string $value, string $key) use ($tableFilterStateMapper, $state): void {
                if ($state instanceof ilBuddySystemRequestedRelationState) {
                    if ($key === get_class($state) . '_a') {
                        $relation = $this->getMockBuilder(ilBuddySystemRelation::class)->disableOriginalConstructor()->getMock();
                        $relation->method('isOwnedByActor')->willReturn(false);

                        $this->assertFalse($tableFilterStateMapper->filterMatchesRelation($key, $relation));

                        $relation = $this->getMockBuilder(ilBuddySystemRelation::class)->disableOriginalConstructor()->getMock();
                        $relation->method('isOwnedByActor')->willReturn(true);
                        $this->assertTrue($tableFilterStateMapper->filterMatchesRelation($key, $relation));
                    } elseif ($key === get_class($state) . '_p') {
                        $relation = $this->getMockBuilder(ilBuddySystemRelation::class)->disableOriginalConstructor()->getMock();
                        $relation->method('isOwnedByActor')->willReturn(true);

                        $this->assertFalse($tableFilterStateMapper->filterMatchesRelation($key, $relation));

                        $relation = $this->getMockBuilder(ilBuddySystemRelation::class)->disableOriginalConstructor()->getMock();
                        $relation->method('isOwnedByActor')->willReturn(false);
                    }
                } else {
                    $relation = $this->getMockBuilder(ilBuddySystemRelation::class)->disableOriginalConstructor()->getMock();
                    $this->assertTrue($tableFilterStateMapper->filterMatchesRelation($key, $relation));
                }
            });
        }
    }
}
