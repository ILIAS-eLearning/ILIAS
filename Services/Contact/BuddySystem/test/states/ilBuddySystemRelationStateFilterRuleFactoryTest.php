<?php declare(strict_types=1);

class ilBuddySystemRelationStateFilterRuleFactoryTest extends ilBuddySystemBaseTest
{
    public function testGetInstance() : void
    {
        $this->assertInstanceOf(
            ilBuddySystemRelationStateFilterRuleFactory::class,
            ilBuddySystemRelationStateFilterRuleFactory::getInstance()
        );

        $this->assertEquals(
            ilBuddySystemRelationStateFilterRuleFactory::getInstance(),
            ilBuddySystemRelationStateFilterRuleFactory::getInstance()
        );
    }

    public function testGetFilterRuleByRelation() : void
    {
        $relation = $this->getMockBuilder(ilBuddySystemRelation::class)->disableOriginalConstructor()->getMock();
        $result = ilBuddySystemRelationStateFilterRuleFactory::getInstance()->getFilterRuleByRelation($relation);

        $this->assertInstanceOf(ilBuddySystemRelationStateNullFilterRule::class, $result);
    }
}
