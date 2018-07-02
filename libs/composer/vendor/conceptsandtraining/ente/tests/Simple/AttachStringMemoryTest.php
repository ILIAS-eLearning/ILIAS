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
use CaT\Ente\Simple\AttachString;
use CaT\Ente\Simple\AttachStringMemory;

class Simple_AttachStringMemoryTest extends PHPUnit_Framework_TestCase {
    use ProviderHelper; 

    public function setUp() {
        $this->id = rand();
        $this->entity = new Entity($this->id);
        $this->component = new AttachStringMemory($this->entity, "id: {$this->id}");
    }

    public function test_entity() {
        $this->assertEquals($this->entity, $this->component->entity());
    }

    public function test_attachedString() {
        $this->assertEquals("id: {$this->id}", $this->component->attachedString());
    }

    public function test_componentTypes() {
        $this->assertEquals([AttachString::class], $this->componentTypesOf($this->component));
    }
}
