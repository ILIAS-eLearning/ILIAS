<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

use CaT\Ente\Component;
use CaT\Ente\ProviderHelper;

interface SomeComponent extends Component {
}

interface SomeOtherComponent extends Component {
}

interface Unrelated {
}

class SomeComponentImplementation implements SomeComponent, SomeOtherComponent, Unrelated {
    public function entity() { throw new \RuntimeException(); }
}

class ProviderHelperTest extends PHPUnit_Framework_TestCase {
    use ProviderHelper; 

    public function test_componentTypesOf() {
        $impl = new SomeComponentImplementation();
        $expected = [SomeComponent::class, SomeOtherComponent::class];
        $this->assertEquals($expected, $this->componentTypesOf($impl));
    } 
}

