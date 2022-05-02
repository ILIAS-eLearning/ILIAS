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
 *********************************************************************/

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
