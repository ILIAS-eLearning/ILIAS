<?php

use \CaT\Plugins\ComponentProviderExample\Settings\ComponentProviderExample;

class SettingsTest extends PHPUnit_Framework_TestCase {
    public function test_objId() {
        $cpe = new ComponentProviderExample(23, []);
        $this->assertEquals(23, $cpe->objId());
    }

    public function test_providedStrings() {
        $some_strings = ["a", "b", "c"];
        $cpe = new ComponentProviderExample(23, $some_strings);
        $this->assertEquals($some_strings, $cpe->providedStrings());
    }

    public function test_withProvidedStrings() {
        $cpe = new ComponentProviderExample(23, []);
        $this->assertEquals([], $cpe->providedStrings());
        $some_strings = ["d", "e", "f"];
        $cpe = $cpe->withProvidedStrings($some_strings);
        $this->assertEquals(23, $cpe->objId());
        $this->assertEquals($some_strings, $cpe->providedStrings());
    }
}
