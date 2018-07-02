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

class CachedRepositoryTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->original_repo = $this->createMock(Ente\Repository::class);
        $this->cached_repo = new Ente\CachedRepository($this->original_repo);
    }

    public function test_caches_provider_for_entity() {
        $entity = $this->createMock(Ente\Entity::class);
        $entity_id = 42;
        $entity
            ->method("id")
            ->willReturn($entity_id);

        $provider = $this->createMock(Ente\Provider::class);

        $this->original_repo
            ->expects($this->once())
            ->method("providersForEntity")
            ->with($entity, null)
            ->willReturn([$provider]);

        $res1 = $this->cached_repo->providersForEntity($entity);
        $res2 = $this->cached_repo->providersForEntity($entity);

        $this->assertEquals([new Ente\CachedProvider($provider)], $res1);
        $this->assertEquals([new Ente\CachedProvider($provider)], $res2);
    }

    public function test_componentsForEntity_calls_providersForEntity() {
        $entity = $this->createMock(Ente\Entity::class);
        $entity_id = 42;
        $entity
            ->method("id")
            ->willReturn($entity_id);

        $cached = $this->getMockBuilder(Ente\CachedRepository::class)
            ->setMethods(["providersForEntity"])
            ->disableOriginalConstructor()
            ->getMock();

        $component_type = "COMPONENT_TYPE";
        $components = ["COMPONENT"];

        $provider = $this->createMock(Ente\Provider::class);
        $provider
            ->expects($this->once())
            ->method("componentsOfType")
            ->with($component_type)
            ->willReturn($components);

        $cached
            ->expects($this->once())
            ->method("providersForEntity")
            ->with($entity, $component_type)
            ->willReturn([$provider]);

        $res = $cached->componentsForEntity($entity, $component_type);

        $this->assertEquals($components, $res);
    }

    public function test_providersForEntities_evaluates_component_type_internally() {
        $entity = $this->createMock(Ente\Entity::class);
        $entity_id = 42;
        $entity
            ->method("id")
            ->willReturn($entity_id);

        $component_type1 = "C1";
        $component_type2 = "C2";

        $provider1 = $this->createMock(Ente\Provider::class);
        $provider2 = $this->createMock(Ente\Provider::class);

        $this->original_repo
            ->expects($this->once())
            ->method("providersForEntity")
            ->with($entity, null)
            ->willReturn([$provider1, $provider2]);

        $provider1
            ->expects($this->exactly(2))
            ->method("componentTypes")
            ->willReturn([$component_type1, $component_type2]);
        $provider2
            ->expects($this->exactly(2))
            ->method("componentTypes")
            ->willReturn([$component_type2]);

        $res1 = $this->cached_repo->providersForEntity($entity, $component_type1);
        $res2 = $this->cached_repo->providersForEntity($entity, $component_type2);

        $this->assertEquals([new Ente\CachedProvider($provider1)], $res1);
        $this->assertEquals([new Ente\CachedProvider($provider1), new Ente\CachedProvider($provider2)], $res2);
    }
}
