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
use CaT\Ente\ILIAS\Provider;
use CaT\Ente\ILIAS\SeparatedUnboundProvider;
use CaT\Ente\Simple;
use CaT\Ente\Simple\AttachString;
use CaT\Ente\Simple\AttachStringMemory;
use CaT\Ente\Simple\AttachInt;
use CaT\Ente\Simple\AttachIntMemory;

if (!class_exists("ilObject")) {
    require_once(__DIR__."/ilObject.php");
}

class Test_SeparatedUnboundProvider extends SeparatedUnboundProvider {
    public function componentTypes() {
        return [AttachString::class, AttachInt::class];
    }

    public function buildComponentsOf($component_type, Entity $entity) {
        assert(is_string($component_type));
        $this->callsTo_buildComponentsOf[] = $component_type;
        $object = $entity->object();
        $entity = $entity;
        if ($component_type == AttachString::class) {
            return [new AttachStringMemory($entity, "id: {$object->getId()}")];
        }
        if ($component_type == AttachInt::class) {
            return [new AttachIntMemory($entity, $object->getId())];
        }
        return [];
    }
}

class ILIAS_SeparatedUnboundProviderTest extends PHPUnit_Framework_TestCase {
    /**
     * @inheritdocs
     */
    protected function unboundProvider() {
        $this->owner = $this
            ->getMockBuilder(\ilObject::class)
            ->setMethods(["getId"])
            ->getMock();

        $this->owner_id = 42;
        $this->owner
            ->method("getId")
            ->willReturn($this->owner_id);

        $this->no_owner = $this
            ->getMockBuilder(\ilObject::class)
            ->setMethods(["getId"])
            ->getMock();

        $this->no_owner_id = 43;
        $this->no_owner
            ->method("getId")
            ->willReturn($this->no_owner_id);

        $this->unbound_provider_id = 23;
        $this->object_type = "object_type";

        $provider = new Test_SeparatedUnboundProvider($this->unbound_provider_id, $this->owner, $this->object_type);

        return $provider;
    }

    public function test_componentTypes() {
        $unbound_provider = $this->unboundProvider();
        $this->assertEquals([AttachString::class, AttachInt::class], $unbound_provider->componentTypes());
    }

    public function test_owner() {
        $owner = $this->unboundProvider()->owner();

        $this->assertInstanceOf(\ilObject::class, $owner);
        $this->assertEquals($this->owner_id, $owner->getId());
    }

    public function test_owners() {
        $owners = $this->unboundProvider()->owners();
        $this->assertCount(1, $owners);

        $owner = array_shift($owners);
        $this->assertInstanceOf(\ilObject::class, $owner);
        $this->assertEquals($this->owner_id, $owner->getId());
    }

    public function test_idFor() {
        $unbound_provider = $this->unboundProvider();
        $this->assertEquals($this->unbound_provider_id, $unbound_provider->idFor($this->owner));
    }

   public function test_idFor_throws() {
        $unbound_provider = $this->unboundProvider();

        try {
            $unbound_provider->idFor($this->no_owner);
            $raised = false;
        }
        catch (\InvalidArgumentException $e) {
            $raised = true;
        }
        $this->assertTrue($raised);
    }

    public function test_object_type() {
        $unbound_provider = $this->unboundProvider();
        $this->assertEquals($this->object_type, $unbound_provider->objectType());
    }
}
