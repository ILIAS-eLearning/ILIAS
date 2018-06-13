<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

use CaT\Ente;

class CachedProviderTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->original_provider = $this->createMock(Ente\Provider::class);
        $this->cached_provider = new Ente\CachedProvider($this->original_provider);
    }

    public function test_componentTypes_passthru() {
        $component_types = ["A", "B"];

        $this->original_provider
            ->expects($this->exactly(2))
            ->method("componentTypes")
            ->willReturn($component_types);

        $res1 = $this->cached_provider->componentTypes();
        $res2 = $this->cached_provider->componentTypes();

        $this->assertEquals($component_types, $res1);
        $this->assertEquals($component_types, $res2);
    }

    public function test_entity_passthru() {
        $entity = $this->createMock(Ente\Entity::class);

        $this->original_provider
            ->expects($this->exactly(2))
            ->method("entity")
            ->willReturn($entity);

        $res1 = $this->cached_provider->entity();
        $res2 = $this->cached_provider->entity();

        $this->assertEquals($entity, $res1);
        $this->assertEquals($entity, $res2);
    }

    public function test_componentsOfType_calls_once() {
        $component_type = "CTYPE";
        $c1 = $this->createMock(Ente\Component::class);
        $c2 = $this->createMock(Ente\Component::class);

        $this->original_provider
            ->expects($this->once())
            ->method("componentsOfType")
            ->with($component_type)
            ->willReturn([$c1, $c2]);

        $res1 = $this->cached_provider->componentsOfType($component_type);
        $res2 = $this->cached_provider->componentsOfType($component_type);

        $this->assertEquals([$c1, $c2], $res1);
        $this->assertEquals([$c1, $c2], $res2);
    }

    public function test_componentsOfType_calls_once_per_component_type() {
        $component_type1 = "CT1";
        $component_type2 = "CT2";
        $c1 = $this->createMock(Ente\Component::class);
        $c2 = $this->createMock(Ente\Component::class);

        $this->original_provider
            ->expects($this->exactly(2))
            ->method("componentsOfType")
            ->withConsecutive
                ( [$component_type1]
                , [$component_type2]
                )
            ->will($this->onConsecutiveCalls
                ( [$c1]
                , [$c2]
                ));

        $res1 = $this->cached_provider->componentsOfType($component_type1);
        $res2 = $this->cached_provider->componentsOfType($component_type1);
        $res3 = $this->cached_provider->componentsOfType($component_type2);
        $res4 = $this->cached_provider->componentsOfType($component_type2);

        $this->assertEquals([$c1], $res1);
        $this->assertEquals([$c1], $res2);
        $this->assertEquals([$c1], $res3);
        $this->assertEquals([$c1], $res4);
    }
}
