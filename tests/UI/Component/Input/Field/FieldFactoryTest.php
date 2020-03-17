<?php

require_once 'tests/UI/AbstractFactoryTest.php';

use \ILIAS\UI\Component\Input\Field;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use \ILIAS\Data;
use \ILIAS\Validation;
use \ILIAS\Transformation;

class FieldFactoryTest extends AbstractFactoryTest
{
    public $kitchensink_info_settings = array(
        "text" => array(
            "context" => false,
        ),
        "numeric" => array(
            "context" => false,
        ),
        "group" => array(
            "context" => false,
        ),
        "section" => array(
            "context" => false,
        ),
        "dependantGroup" => array(
            "context" => false,
        ),
        "checkbox" => array(
            "context" => false,
        ),
        "select" => array(
            "context" => false,
        ),
        "textarea" => array(
            "context" => false,
        ),
        "radio" => array(
            "context" => false,
        ),
        "multiSelect" => array(
            "context" => false,
        )
    );
    public $factory_title = 'ILIAS\\UI\\Component\\Input\\Field\\Factory';


    final public function buildFactory()
    {
        $df = new Data\Factory();
        return new \ILIAS\UI\Implementation\Component\Input\Field\Factory(
            new SignalGenerator(),
            $df,
            new Validation\Factory($df, $this->createMock(\ilLanguage::class)),
            new Transformation\Factory()
        );
    }


    public function test_implements_factory_interface()
    {
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
