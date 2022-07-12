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
