<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

use CaT\Ente;

/**
 * This testcases must be passed by a Repository.
 */
abstract class RepositoryTest extends PHPUnit_Framework_TestCase {
    /**
     * To make this interesting, the repository should at least contain one
     * provider that provides for some entity.
     *
     * @return Repository
     */
    abstract protected function repository();

    /**
     * The entities the repository has a provider for.
     *
     * @return  Entity[]
     */
    abstract protected function hasProvidersForEntities();

    /**
     * The component types the repository has providers for.
     *
     * @return  string[]
     */
    abstract protected function hasProvidersForComponentTypes();

    // TEST

    /**
     * @dataProvider has_providers_for_entities
     */
    public function test_providers_for_entity($entity) {
        $providers = $this->repository()->providersForEntity($entity);
        foreach ($providers as $provider) {
            $this->assertEquals($entity->id(), $provider->entity()->id());
        }
    }

    /**
     * @dataProvider has_providers_for_entities_and_component_types
     */
    public function test_providers_for_entity_filtered($entity, $component_type) {
        $providers = $this->repository()->providersForEntity($entity, $component_type);
        foreach ($providers as $provider) {
            $this->assertEquals($entity->id(), $provider->entity()->id());
            $this->assertContains($component_type, $provider->componentTypes());
        }
    }

    // DATA PROVIDERS

    public function has_providers_for_entities() {
        foreach ($this->hasProvidersForEntities() as $entity) {
            yield [$entity];
        }
    }

    public function has_providers_for_component_types() {
        foreach ($this->hasProvidersForComponentTypes() as $component_type) {
            yield [$component_type];
        }
    }

    public function has_providers_for_entities_and_component_types() {
        foreach ($this->hasProvidersForEntities() as $entity) {
            foreach ($this->hasProvidersForComponentTypes() as $component_type) {
                yield [$entity, $component_type];
            }
        }
    }
}
