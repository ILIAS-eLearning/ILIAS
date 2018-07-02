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
use CaT\Ente\ILIAS\SharedUnboundProvider;
use CaT\Ente\Simple;
use CaT\Ente\Simple\AttachString;
use CaT\Ente\Simple\AttachStringMemory;
use CaT\Ente\Simple\AttachInt;
use CaT\Ente\Simple\AttachIntMemory;

if (!class_exists("ilObject")) {
    require_once(__DIR__."/ilObject.php");
}

class Test_SharedUnboundProvider extends SharedUnboundProvider {
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

class ILIAS_SharedUnboundProviderTest extends PHPUnit_Framework_TestCase {
    /**
     * @inheritdocs
     */
    protected function unboundProvider() {
        $this->owner1 = $this
            ->getMockBuilder(\ilObject::class)
            ->setMethods(["getId"])
            ->getMock();

        $this->owner1_id = 42;
        $this->owner1
            ->method("getId")
            ->willReturn($this->owner1_id);

        $this->owner2 = $this
            ->getMockBuilder(\ilObject::class)
            ->setMethods(["getId"])
            ->getMock();

        $this->owner2_id = 43;
        $this->owner2
            ->method("getId")
            ->willReturn($this->owner2_id);

        $this->unbound_provider_id1 = 23;
        $this->unbound_provider_id2 = 24;
        $this->object_type = "object_type";

        $owners =
            [ $this->unbound_provider_id1 => $this->owner1
            , $this->unbound_provider_id2 => $this->owner2
            ];

        $provider = new Test_SharedUnboundProvider($owners, $this->object_type);

        return $provider;
    }

    public function test_componentTypes() {
        $unbound_provider = $this->unboundProvider();
        $this->assertEquals([AttachString::class, AttachInt::class], $unbound_provider->componentTypes());
    }

    public function test_owner() {
        $owners = $this->unboundProvider()->owners();
        $this->assertCount(2, $owners);

        $owner1 = array_shift($owners);
        $this->assertInstanceOf(\ilObject::class, $owner1);
        $this->assertEquals($this->owner1_id, $owner1->getId());

        $owner2 = array_shift($owners);
        $this->assertInstanceOf(\ilObject::class, $owner2);
        $this->assertEquals($this->owner2_id, $owner2->getId());
    }

    public function test_idFor() {
        $unbound_provider = $this->unboundProvider();
        $this->assertEquals($this->unbound_provider_id1, $unbound_provider->idFor($this->owner1));
        $this->assertEquals($this->unbound_provider_id2, $unbound_provider->idFor($this->owner2));
    }

   public function test_idFor_throws() {
        $unbound_provider = $this->unboundProvider();
        $other = $this
            ->getMockBuilder(\ilObject::class)
            ->setMethods(["getId"])
            ->getMock();

        $other_id = 23;
        $other
            ->method("getId")
            ->willReturn($other_id);

        try {
            $unbound_provider->idFor($other);
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
