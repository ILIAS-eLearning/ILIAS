<?php

require_once 'tests/UI/AbstractFactoryTest.php';

use \ILIAS\UI\Component\Input\Field;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use \ILIAS\Data;
use ILIAS\Refinery;

class FieldFactoryTest extends AbstractFactoryTest
{
    public $kitchensink_info_settings = array(
        "text"           => array(
            "context" => false,
        ),
        "numeric"        => array(
            "context" => false,
        ),
        "group"          => array(
            "context" => false,
        ),
        "section"        => array(
            "context" => false,
        ),
        "optionalGroup" => array(
            "context" => false,
        ),
        "switchableGroup" => array(
            "context" => false,
        ),
        "checkbox"       => array(
            "context" => false,
        ),
        "select"		=> array(
            "context" => false,
        ),
        "textarea"	=> array(
            "context" => false,
        ),
        "radio"			=> array(
            "context" => false,
        ),
        "multiSelect"	=> array(
            "context" => false,
        )
    );
    public $factory_title = 'ILIAS\\UI\\Component\\Input\\Field\\Factory';


    final public function buildFactory()
    {
        $df = new Data\Factory();
        $language = $this->createMock(\ilLanguage::class);
        return new \ILIAS\UI\Implementation\Component\Input\Field\Factory(
            new SignalGenerator(),
            $df,
            new \ILIAS\Refinery\Factory($df, $language)
        );
    }

    public function testImplementsFactoryInterfaceForText()
    {
        $f = $this->buildFactory();

        $input = $f->text("label", "byline");
        $this->assertInstanceOf(Field\Input::class, $input);
        $this->assertInstanceOf(Field\Text::class, $input);
    }

    public function testImplementsFactoryInterfaceForNumeric()
    {
        $f = $this->buildFactory();

        $input = $f->numeric("label", "byline");
        $this->assertInstanceOf(Field\Input::class, $input);
        $this->assertInstanceOf(Field\Numeric::class, $input);
    }

    public function testImplementsFactoryInterfaceForSection()
    {
        $f = $this->buildFactory();

        $input = $f->section([], "label", "byline");
        $this->assertInstanceOf(Field\Input::class, $input);
        $this->assertInstanceOf(Field\Group::class, $input);
        $this->assertInstanceOf(Field\Section::class, $input);
    }

    public function testImplementsFactoryInterfaceForGroup()
    {
        $f = $this->buildFactory();

        $input = $f->group([]);
        $this->assertInstanceOf(Field\Input::class, $input);
        $this->assertInstanceOf(Field\Group::class, $input);
    }

    public function testImplementsFactoryInterfaceForCheckbox()
    {
        $f = $this->buildFactory();

        $input = $f->checkbox("label", "byline");
        $this->assertInstanceOf(Field\Input::class, $input);
        $this->assertInstanceOf(Field\Checkbox::class, $input);
    }

    public function testImplementsFactoryInterfaceForTag()
    {
        $f = $this->buildFactory();

        $input = $f->tag("label", [], "byline");
        $this->assertInstanceOf(Field\Input::class, $input);
        $this->assertInstanceOf(Field\Tag::class, $input);
    }

    public function testImplementsFactoryInterfaceForPassword()
    {
        $f = $this->buildFactory();

        $input = $f->password("label", "byline");
        $this->assertInstanceOf(Field\Input::class, $input);
        $this->assertInstanceOf(Field\Password::class, $input);
    }

    public function testImplementsFactoryInterfaceForSelect()
    {
        $f = $this->buildFactory();

        $input = $f->select("label", [], "byline");
        $this->assertInstanceOf(Field\Input::class, $input);
        $this->assertInstanceOf(Field\Select::class, $input);
    }

    public function testImplementsFactoryInterfaceForTextarea()
    {
        $f = $this->buildFactory();

        $input = $f->textarea("label", "byline");
        $this->assertInstanceOf(Field\Input::class, $input);
        $this->assertInstanceOf(Field\Textarea::class, $input);
    }

    public function testImplementsFactoryInterfaceForRadio()
    {
        $f = $this->buildFactory();

        $input = $f->radio("label", "byline");
        $this->assertInstanceOf(Field\Input::class, $input);
        $this->assertInstanceOf(Field\Radio::class, $input);
    }

    public function testImplementsFactoryInterfaceForMultiselect()
    {
        $f = $this->buildFactory();

        $input = $f->multiSelect("label", [], "byline");
        $this->assertInstanceOf(Field\Input::class, $input);
        $this->assertInstanceOf(Field\MultiSelect::class, $input);
    }

    public function testImplementsFactoryInterfaceForDatetime()
    {
        $f = $this->buildFactory();

        $input = $f->datetime("label", "byline");
        $this->assertInstanceOf(Field\Input::class, $input);
    }

    public function testImplementsFactoryInterfaceForDuration()
    {
        $f = $this->buildFactory();

        $input = $f->duration("label", "byline");
        $this->assertInstanceOf(Field\Input::class, $input);
        $this->assertInstanceOf(Field\Group::class, $input);
    }

    public function test_implements_factory_no_by_line()
    {
        $f = $this->buildFactory();

        $input = $f->text("label");
        $this->assertInstanceOf(Field\Input::class, $input);
        $this->assertInstanceOf(Field\Text::class, $input);

        $input = $f->numeric("label");
        $this->assertInstanceOf(Field\Input::class, $input);
        $this->assertInstanceOf(Field\Numeric::class, $input);

        $input = $f->section([], "label");
        $this->assertInstanceOf(Field\Input::class, $input);
        $this->assertInstanceOf(Field\Group::class, $input);
        $this->assertInstanceOf(Field\Section::class, $input);

        $input = $f->checkbox("label");
        $this->assertInstanceOf(Field\Input::class, $input);
        $this->assertInstanceOf(Field\Checkbox::class, $input);

        $input = $f->tag("label", []);
        $this->assertInstanceOf(Field\Input::class, $input);
        $this->assertInstanceOf(Field\Tag::class, $input);

        $input = $f->password("label");
        $this->assertInstanceOf(Field\Input::class, $input);
        $this->assertInstanceOf(Field\Password::class, $input);

        $input = $f->select("label", []);
        $this->assertInstanceOf(Field\Input::class, $input);
        $this->assertInstanceOf(Field\Select::class, $input);

        $input = $f->textarea("label");
        $this->assertInstanceOf(Field\Input::class, $input);
        $this->assertInstanceOf(Field\Textarea::class, $input);

        $input = $f->radio("label");
        $this->assertInstanceOf(Field\Input::class, $input);
        $this->assertInstanceOf(Field\Radio::class, $input);

        $input = $f->multiSelect("label", []);
        $this->assertInstanceOf(Field\Input::class, $input);
        $this->assertInstanceOf(Field\MultiSelect::class, $input);
    }
}
