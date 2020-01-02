<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class LSItemTest extends TestCase
{
    const TYPE = "type";
    const TITLE = "tile";
    const DESC = "description";
    const ICON_PATH = "icon_path";
    const IS_ONLINE = true;
    const ORDER_NUMBER = 10;
    const REF_ID = 30;

    /**
     * @var ilLSPostCondition
     */
    protected $post_condition;

    public function setUp()
    {
        $this->post_condition = new ilLSPostCondition(666, 1);
    }

    public function testCreate() : LSItem
    {
        $object = new LSItem(
            self::TYPE,
            self::TITLE,
            self::DESC,
            self::ICON_PATH,
            self::IS_ONLINE,
            self::ORDER_NUMBER,
            $this->post_condition,
            self::REF_ID
        );

        $this->assertEquals($object->getType(), self::TYPE);
        $this->assertEquals($object->getTitle(), self::TITLE);
        $this->assertEquals($object->getDescription(), self::DESC);
        $this->assertEquals($object->getIconPath(), self::ICON_PATH);
        $this->assertEquals($object->isOnline(), self::IS_ONLINE);
        $this->assertEquals($object->getOrderNumber(), self::ORDER_NUMBER);
        $this->assertEquals($object->getPostCondition(), $this->post_condition);
        $this->assertEquals($object->getRefId(), self::REF_ID);

        return $object;
    }

    /**
     * @depends testCreate
     */
    public function testWithOnline(LSItem $object)
    {
        $new_obj = $object->withOnline(false);

        $this->assertEquals($object->getType(), self::TYPE);
        $this->assertEquals($object->getTitle(), self::TITLE);
        $this->assertEquals($object->getDescription(), self::DESC);
        $this->assertEquals($object->getIconPath(), self::ICON_PATH);
        $this->assertEquals($object->isOnline(), self::IS_ONLINE);
        $this->assertEquals($object->getOrderNumber(), self::ORDER_NUMBER);
        $this->assertEquals($object->getPostCondition(), $this->post_condition);
        $this->assertEquals($object->getRefId(), self::REF_ID);

        $this->assertEquals($new_obj->getType(), self::TYPE);
        $this->assertEquals($new_obj->getTitle(), self::TITLE);
        $this->assertEquals($new_obj->getDescription(), self::DESC);
        $this->assertEquals($new_obj->getIconPath(), self::ICON_PATH);
        $this->assertEquals($new_obj->isOnline(), false);
        $this->assertEquals($new_obj->getOrderNumber(), self::ORDER_NUMBER);
        $this->assertEquals($new_obj->getPostCondition(), $this->post_condition);
        $this->assertEquals($new_obj->getRefId(), self::REF_ID);
    }

    /**
     * @depends testCreate
     */
    public function testWithOrderNumber(LSItem $object)
    {
        $new_obj = $object->withOrderNumber(20);

        $this->assertEquals($object->getType(), self::TYPE);
        $this->assertEquals($object->getTitle(), self::TITLE);
        $this->assertEquals($object->getDescription(), self::DESC);
        $this->assertEquals($object->getIconPath(), self::ICON_PATH);
        $this->assertEquals($object->isOnline(), self::IS_ONLINE);
        $this->assertEquals($object->getOrderNumber(), self::ORDER_NUMBER);
        $this->assertEquals($object->getPostCondition(), $this->post_condition);
        $this->assertEquals($object->getRefId(), self::REF_ID);

        $this->assertEquals($new_obj->getType(), self::TYPE);
        $this->assertEquals($new_obj->getTitle(), self::TITLE);
        $this->assertEquals($new_obj->getDescription(), self::DESC);
        $this->assertEquals($new_obj->getIconPath(), self::ICON_PATH);
        $this->assertEquals($new_obj->isOnline(), self::IS_ONLINE);
        $this->assertEquals($new_obj->getOrderNumber(), 20);
        $this->assertEquals($new_obj->getPostCondition(), $this->post_condition);
        $this->assertEquals($new_obj->getRefId(), self::REF_ID);
    }

    /**
     * @depends testCreate
     */
    public function testWithPostCondition(LSItem $object)
    {
        $pc = new ilLSPostCondition(555, 2);
        $new_obj = $object->withPostCondition($pc);

        $this->assertEquals($object->getType(), self::TYPE);
        $this->assertEquals($object->getTitle(), self::TITLE);
        $this->assertEquals($object->getDescription(), self::DESC);
        $this->assertEquals($object->getIconPath(), self::ICON_PATH);
        $this->assertEquals($object->isOnline(), self::IS_ONLINE);
        $this->assertEquals($object->getOrderNumber(), self::ORDER_NUMBER);
        $this->assertEquals($object->getPostCondition(), $this->post_condition);
        $this->assertEquals($object->getRefId(), self::REF_ID);

        $this->assertEquals($new_obj->getType(), self::TYPE);
        $this->assertEquals($new_obj->getTitle(), self::TITLE);
        $this->assertEquals($new_obj->getDescription(), self::DESC);
        $this->assertEquals($new_obj->getIconPath(), self::ICON_PATH);
        $this->assertEquals($new_obj->isOnline(), self::IS_ONLINE);
        $this->assertEquals($new_obj->getOrderNumber(), self::ORDER_NUMBER);
        $this->assertEquals($new_obj->getPostCondition(), $pc);
        $this->assertEquals($new_obj->getRefId(), self::REF_ID);
    }

    /**
     * @depends testCreate
     */
    public function testWrongValueInWithOnline(LSItem $object)
    {
        $this->expectException(TypeError::class);
        $object->withOnline("wrong_value");
    }

    /**
     * @depends testCreate
     */
    public function testWrongValueInWithOrderNumber(LSItem $object)
    {
        $this->expectException(TypeError::class);
        $object->withOrderNumber("wrong_value");
    }

    /**
     * @depends testCreate
     */
    public function testWrongValueInWithPostCondition(LSItem $object)
    {
        $this->expectException(TypeError::class);
        $object->withPostCondition("wrong_value");
    }
}
