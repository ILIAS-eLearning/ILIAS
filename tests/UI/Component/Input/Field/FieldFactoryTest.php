<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
 
require_once 'tests/UI/AbstractFactoryTest.php';

use ILIAS\UI\Component\Input\Field;
use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\Data;
use ILIAS\Refinery\Factory as Refinery;

class FieldFactoryTest extends AbstractFactoryTest
{
    public array $kitchensink_info_settings = [
        "text" => [
            "context" => false,
        ],
        "numeric" => [
            "context" => false,
        ],
        "group" => [
            "context" => false,
        ],
        "section" => [
            "context" => false,
        ],
        "optionalGroup" => [
            "context" => false,
        ],
        "switchableGroup" => [
            "context" => false,
        ],
        "checkbox" => [
            "context" => false,
        ],
        "select" => [
            "context" => false,
        ],
        "textarea" => [
            "context" => false,
        ],
        "radio" => [
            "context" => false,
        ],
        "multiSelect" => [
            "context" => false,
        ]
    ];

    public string $factory_title = 'ILIAS\\UI\\Component\\Input\\Field\\Factory';


    final public function buildFactory() : I\Input\Field\Factory
    {
        $df = new Data\Factory();
        $language = $this->createMock(ilLanguage::class);
        return new I\Input\Field\Factory(
            new SignalGenerator(),
            $df,
            new Refinery($df, $language),
            $language
        );
    }

    public function testImplementsFactoryInterfaceForText() : void
    {
        $f = $this->buildFactory();

        $input = $f->text("label", "byline");
        $this->assertInstanceOf(Field\Input::class, $input);
        $this->assertInstanceOf(Field\Text::class, $input);
    }

    public function testImplementsFactoryInterfaceForNumeric() : void
    {
        $f = $this->buildFactory();

        $input = $f->numeric("label", "byline");
        $this->assertInstanceOf(Field\Input::class, $input);
        $this->assertInstanceOf(Field\Numeric::class, $input);
    }

    public function testImplementsFactoryInterfaceForSection() : void
    {
        $f = $this->buildFactory();

        $input = $f->section([], "label", "byline");
        $this->assertInstanceOf(Field\Input::class, $input);
        $this->assertInstanceOf(Field\Group::class, $input);
        $this->assertInstanceOf(Field\Section::class, $input);
    }

    public function testImplementsFactoryInterfaceForGroup() : void
    {
        $f = $this->buildFactory();

        $input = $f->group([]);
        $this->assertInstanceOf(Field\Input::class, $input);
        $this->assertInstanceOf(Field\Group::class, $input);
    }

    public function testImplementsFactoryInterfaceForCheckbox() : void
    {
        $f = $this->buildFactory();

        $input = $f->checkbox("label", "byline");
        $this->assertInstanceOf(Field\Input::class, $input);
        $this->assertInstanceOf(Field\Checkbox::class, $input);
    }

    public function testImplementsFactoryInterfaceForTag() : void
    {
        $f = $this->buildFactory();

        $input = $f->tag("label", [], "byline");
        $this->assertInstanceOf(Field\Input::class, $input);
        $this->assertInstanceOf(Field\Tag::class, $input);
    }

    public function testImplementsFactoryInterfaceForPassword() : void
    {
        $f = $this->buildFactory();

        $input = $f->password("label", "byline");
        $this->assertInstanceOf(Field\Input::class, $input);
        $this->assertInstanceOf(Field\Password::class, $input);
    }

    public function testImplementsFactoryInterfaceForSelect() : void
    {
        $f = $this->buildFactory();

        $input = $f->select("label", [], "byline");
        $this->assertInstanceOf(Field\Input::class, $input);
        $this->assertInstanceOf(Field\Select::class, $input);
    }

    public function testImplementsFactoryInterfaceForTextarea() : void
    {
        $f = $this->buildFactory();

        $input = $f->textarea("label", "byline");
        $this->assertInstanceOf(Field\Input::class, $input);
        $this->assertInstanceOf(Field\Textarea::class, $input);
    }

    public function testImplementsFactoryInterfaceForRadio() : void
    {
        $f = $this->buildFactory();

        $input = $f->radio("label", "byline");
        $this->assertInstanceOf(Field\Input::class, $input);
        $this->assertInstanceOf(Field\Radio::class, $input);
    }

    public function testImplementsFactoryInterfaceForMultiselect() : void
    {
        $f = $this->buildFactory();

        $input = $f->multiSelect("label", [], "byline");
        $this->assertInstanceOf(Field\Input::class, $input);
        $this->assertInstanceOf(Field\MultiSelect::class, $input);
    }

    public function testImplementsFactoryInterfaceForDatetime() : void
    {
        $f = $this->buildFactory();

        $input = $f->datetime("label", "byline");
        $this->assertInstanceOf(Field\Input::class, $input);
    }

    public function testImplementsFactoryInterfaceForDuration() : void
    {
        $f = $this->buildFactory();

        $input = $f->duration("label", "byline");
        $this->assertInstanceOf(Field\Input::class, $input);
        $this->assertInstanceOf(Field\Group::class, $input);
    }

    public function test_implements_factory_no_by_line() : void
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
