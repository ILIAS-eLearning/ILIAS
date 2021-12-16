<?php declare(strict_types=1);

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");
require_once(__DIR__ . "/InputTest.php");

use ILIAS\UI\Implementation\Component as I;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data;

class HiddenInputTest extends ILIAS_UI_TestBase
{
    protected DefNamesource $name_source;
    protected I\Input\Field\Hidden $input;

    public function setUp() : void
    {
        $this->name_source = new DefNamesource();
        $this->input = new I\Input\Field\Hidden(
            new Data\Factory(),
            new Refinery(
                new Data\Factory(),
                $this->createMock(ilLanguage::class)
            )
        );
    }

    public function test_render() : void
    {
        $input = $this->input->withNameFrom($this->name_source);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($input));

        $expected = $this->brutallyTrimHTML('
            <input id="id_1" type="hidden" name="name_0" value="" />
        ');
        $this->assertEquals($expected, $html);
    }

    public function test_render_disabled() : void
    {
        $input = $this->input->withNameFrom($this->name_source);
        $input = $input->withDisabled(true);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($input));

        $expected = $this->brutallyTrimHTML('
            <input id="id_1" type="hidden" name="name_0" value="" disabled="disabled"/>
        ');
        $this->assertEquals($expected, $html);
    }

    public function test_render_value() : void
    {
        $input = $this->input->withNameFrom($this->name_source);
        $input = $input->withValue('some_value');

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($input));

        $expected = $this->brutallyTrimHTML('
            <input id="id_1" type="hidden" name="name_0" value="some_value" />
        ');
        $this->assertEquals($expected, $html);
    }
}
