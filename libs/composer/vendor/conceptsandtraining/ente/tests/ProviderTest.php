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
 * This testcases must be passed by a Provider.
 */
abstract class ProviderTest extends PHPUnit_Framework_TestCase {
    /**
     * To make this interesting, the provider should at least provide for one
     * entity.
     *
     * @return  Provider
     */
    abstract protected function provider();

    /**
     * Some types of components the provider does not provide for.
     *
     * @return  string[]
     */
    abstract protected function doesNotProvideComponentType();

    // TEST

    public function test_only_provides_announced_component_types() {
        $provider = $this->provider();
        foreach ($this->doesNotProvideComponentType() as $component_type) {
            $this->assertEmpty($provider->componentsOfType($component_type));
        }
    }

    /**
     * @dataProvider component_types
     */
    public function test_provides_for_own_entity($component_type) {
        $provider = $this->provider();
        $id = $provider->entity()->id();
        foreach($provider->componentsOfType($component_type) as $component) {
            $this->assertEquals(serialize($id), serialize($component->entity()->id()));
        }
    }

    /**
     * @dataProvider component_types
     */
    public function test_provides_expected_component_types($component_type) {
        $provider = $this->provider();
        foreach($provider->componentsOfType($component_type) as $component) {
            $this->assertInstanceOf($component_type, $component);
        }
    }

    // DATA PROVIDERS

    public function component_types() {
        $provider = $this->provider();
        foreach ($provider->componentTypes() as $type) {
            yield [$type];
        }
    }
}
