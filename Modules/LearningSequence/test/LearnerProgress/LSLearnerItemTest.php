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
 
use PHPUnit\Framework\TestCase;

class LSLearnerItemTest extends TestCase
{
    const TYPE = "type";
    const TITLE = "tile";
    const DESC = "description";
    const ICON_PATH = "icon_path";
    const IS_ONLINE = true;
    const ORDER_NUMBER = 10;
    const REF_ID = 30;
    const USER_ID = 6;
    const LP_STATUS = 2;
    const AVAILABILITY_STATUS = 3;

    protected ilLSPostCondition $post_condition;

    protected function setUp() : void
    {
        $this->post_condition = new ilLSPostCondition(666, 'always');
    }

    public function testCreate() : LSLearnerItem
    {
        $ls_item = new LSItem(
            self::TYPE,
            self::TITLE,
            self::DESC,
            self::ICON_PATH,
            self::IS_ONLINE,
            self::ORDER_NUMBER,
            $this->post_condition,
            self::REF_ID
        );

        $object = new LSLearnerItem(
            self::USER_ID,
            self::LP_STATUS,
            self::AVAILABILITY_STATUS,
            $ls_item
        );

        $this->assertEquals(self::USER_ID, $object->getUserId());
        $this->assertEquals(self::LP_STATUS, $object->getLearningProgressStatus());
        $this->assertEquals(self::AVAILABILITY_STATUS, $object->getAvailability());

        return $object;
    }

    /**
     * @depends testCreate
     */
    public function testTurnedOffWithPostCondition(LSItem $object) : void
    {
        $this->expectException(LogicException::class);
        $object->withPostCondition($this->post_condition);
    }

    /**
     * @depends testCreate
     */
    public function testTurnedOffWithOrderNumber(LSItem $object) : void
    {
        $this->expectException(LogicException::class);
        $object->withOrderNumber(self::ORDER_NUMBER);
    }

    /**
     * @depends testCreate
     */
    public function testTurnedOffWithOnline(LSItem $object) : void
    {
        $this->expectException(LogicException::class);
        $object->withOnline(self::IS_ONLINE);
    }
}
