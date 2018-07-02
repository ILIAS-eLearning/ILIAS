<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

use CaT\Ente\ILIAS\Entity;

if (!class_exists("ilObject")) {
    require_once(__DIR__."/ilObject.php");
}

class ILIAS_EntityTest extends PHPUnit_Framework_TestCase {
    public function test_callsGetObjId() {
        $mock = $this
            ->getMockBuilder(ilObject::class)
            ->setMethods(["getId"])
            ->getMock();

        $id = 10;

        $mock
            ->expects($this->once())
            ->method("getId")
            ->willReturn($id);      

        $e = new Entity($mock);

        $this->assertEquals($id, $e->id());
    }

    public function test_object() {
        $mock = $this
            ->getMockBuilder(ilObject::class)
            ->getMock();

        $e = new Entity($mock);

        $this->assertEquals($mock, $e->object());
    }
}
