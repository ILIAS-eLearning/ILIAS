<?php

require_once("libs/composer/vendor/autoload.php");

/**
 * Defines tests every UI-factory should pass.
 */
abstract class FactoryTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->f = $this->getFactoryInstance();
    }

    abstract public function getFactoryInstance();

    public function test_implements_factory_interface() {
        $this->assertInstanceOf("ILIAS\\UI\\Factory", $this->f);
        
        // TODO: One could automate this by using the docstring on Factory.
        // This would be nice as we would make sure that documentation an
        // behaviour match up.
        $this->assertInstanceOf("ILIAS\\UI\\Factory\\Counter", $this->f->counter());
        $this->assertInstanceOf("ILIAS\\UI\\Factory\\Glyph", $this->f->glyph());
    }
}