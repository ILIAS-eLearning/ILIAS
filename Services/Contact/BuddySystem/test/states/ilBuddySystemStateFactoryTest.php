<?php declare(strict_types=1);

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
 ********************************************************************
 */

class ilBuddySystemStateFactoryTest extends ilBuddySystemBaseTest
{
    private ilBuddySystemRelationStateFactory $stateFactory;

    protected function setUp() : void
    {
        $lng = $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock();
        $lng->method('txt')->willReturnCallback(static function (string $keyword) : string {
            return $keyword;
        });

        ilBuddySystemRelationStateFactory::getInstance($lng)->reset();
        $this->stateFactory = ilBuddySystemRelationStateFactory::getInstance($lng);
    }
    
    public function testInitialStateEqualsUnlinkedRelation() : void
    {
        $this->assertInstanceOf(
            ilBuddySystemUnlinkedRelationState::class,
            $this->stateFactory->getInitialState()
        );
    }

    public function testStatesCanBeReceivedAsOptionMapIncludingInitalState() : void
    {
        $allStateClasses = array_map(static function (ilBuddySystemRelationState $state) : string {
            return get_class($state);
        }, $this->stateFactory->getValidStates());

        $allStatesOptions = array_keys($this->stateFactory->getStatesAsOptionArray(true));
        $this->assertCount(
            0,
            array_diff($allStateClasses, $allStatesOptions),
            'Option array is missing at least one state.'
        );
        $this->assertCount(
            0,
            array_diff($allStatesOptions, $allStateClasses),
            'Option array contains at least one unexpected state'
        );
    }

    public function testStatesCanBeReceivedAsOptionMapExcludingInitalState() : void
    {
        $initalState = $this->stateFactory->getInitialState();

        $allStateClasses = array_map(static function (ilBuddySystemRelationState $state) : string {
            return get_class($state);
        }, $this->stateFactory->getValidStates());

        $statesWithoutInitialOptions = array_keys($this->stateFactory->getStatesAsOptionArray(false));
        $this->assertCount(
            1,
            array_diff($allStateClasses, $statesWithoutInitialOptions),
            'Option array is missing at least one state when retrieved without initial state'
        );
        $this->assertEquals(
            get_class($initalState),
            implode('', array_diff($allStateClasses, $statesWithoutInitialOptions)),
            'Only the initial sate is expected to be missing in the options array when retrieved without initial state'
        );
        $this->assertCount(
            0,
            array_diff($statesWithoutInitialOptions, $allStateClasses),
            'Option array contains at least one unexpected state when retrieved without initial state'
        );
    }
}
