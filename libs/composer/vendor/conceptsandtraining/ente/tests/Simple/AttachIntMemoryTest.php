<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

use CaT\Ente\ProviderHelper;
use CaT\Ente\Simple\Entity;
use CaT\Ente\Simple\AttachInt;
use CaT\Ente\Simple\AttachIntMemory;

class Simple_AttachIntMemoryTest extends PHPUnit_Framework_TestCase {
    use ProviderHelper; 

    public function setUp() {
        $this->id = rand();
        $this->entity = new Entity($this->id);
        $this->component = new AttachIntMemory($this->entity, -1  * $this->id);
    }

    public function test_entity() {
        $this->assertEquals($this->entity, $this->component->entity());
    }

    public function test_attachedInt() {
        $this->assertEquals(-1 * $this->id, $this->component->attachedInt());
    }

    public function test_componentTypes() {
        $this->assertEquals([AttachInt::class], $this->componentTypesOf($this->component));
    }
}
