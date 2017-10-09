<?php

require_once 'tests/UI/AbstractFactoryTest.php';

use \ILIAS\UI\Component\Input\Field;

class FieldFactoryTest extends AbstractFactoryTest {
	public $kitchensink_info_settings = array();


	public $factory_title = 'ILIAS\\UI\\Component\\Input\\Field\\Factory';

	final public function buildFactory() {
		return new \ILIAS\UI\Implementation\Component\Input\Field\Factory;
	}

    public function test_implements_factory_interface() {
        $f = $this->buildFactory();

        $text = $f->text("label", "byline");
        $this->assertInstanceOf(Field\Input::class, $text);
        $this->assertInstanceOf(Field\Text::class, $text);

        $text = $f->numeric("label", "byline");
        $this->assertInstanceOf(Field\Input::class, $text);
        $this->assertInstanceOf(Field\Numeric::class, $text);

        $text = $f->section([], "label", "byline");
        $this->assertInstanceOf(Field\Input::class, $text);
        $this->assertInstanceOf(Field\Group::class, $text);
        $this->assertInstanceOf(Field\Section::class, $text);

        $text = $f->group([]);
        $this->assertInstanceOf(Field\Input::class, $text);
        $this->assertInstanceOf(Field\Group::class, $text);
    }
}
