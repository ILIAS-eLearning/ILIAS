<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ILIAS\KioskMode\State;

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

    /**
     * @var ilLSPostCondition
     */
    protected $post_condition;

    public function setUp()
    {
        $this->post_condition = new ilLSPostCondition(666, 1);
    }

    public function testCreate() : LSLearnerItem
    {
        $ui_reflection = new ReflectionClass(State::class);
        $methods = array_map(
            function ($m) {
                return $m->getName();
            },
            $ui_reflection->getMethods()
        );

        $kiosk_state = $this->getMockBuilder(State::class)
            ->setMethods($methods)
            ->getMock()
        ;

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
            $kiosk_state,
            $ls_item
        );

        $this->assertEquals($object->getUserId(), self::USER_ID);
        $this->assertEquals($object->getLearningProgressStatus(), self::LP_STATUS);
        $this->assertEquals($object->getAvailability(), self::AVAILABILITY_STATUS);
        $this->assertEquals($object->getState(), $kiosk_state);

        return $object;
    }

    /**
     * @depends testCreate
     */
    public function testTurnedOffWithPostCondition(LSItem $object)
    {
        $this->expectException(LogicException::class);
        $object->withPostCondition($this->post_condition);
    }

    /**
     * @depends testCreate
     */
    public function testTurnedOffWithOrderNumber(LSItem $object)
    {
        $this->expectException(LogicException::class);
        $object->withOrderNumber(self::ORDER_NUMBER);
    }

    /**
     * @depends testCreate
     */
    public function testTurnedOffWithOnline(LSItem $object)
    {
        $this->expectException(LogicException::class);
        $object->withOnline(self::IS_ONLINE);
    }
}
