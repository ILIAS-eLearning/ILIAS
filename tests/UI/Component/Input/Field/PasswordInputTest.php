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
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\Data\Password as PWD;
use ILIAS\UI\Component\Input\Field;
use ILIAS\Data;
use ILIAS\Refinery\Factory as Refinery;

class _PWDInputData implements InputData
{
    /**
     * @ineritdoc
     */
    public function get(string $name) : string
    {
        return 'some value';
    }

    /**
     * @inheritcoc
     */
    public function getOr(string $name, $default) : string
    {
        return 'some alternative value';
    }
}

class PasswordInputTest extends ILIAS_UI_TestBase
{
    protected DefNamesource $name_source;

    public function setUp() : void
    {
        $this->name_source = new DefNamesource();
    }

    protected function buildFactory() : I\Input\Field\Factory
    {
        $df = new Data\Factory();
        $language = $this->createMock(ilLanguage::class);
        return new I\Input\Field\Factory(
            $this->createMock(\ILIAS\UI\Implementation\Component\Input\UploadLimitResolver::class),
            new SignalGenerator(),
            $df,
            new Refinery($df, $language),
            $language
        );
    }

    public function test_implements_factory_interface() : void
    {
        $f = $this->buildFactory();
        $pwd = $f->password("label", "byline");
        $this->assertInstanceOf(Field\Input::class, $pwd);
        $this->assertInstanceOf(Field\Password::class, $pwd);
    }

    public function test_render() : void
    {
        $f = $this->buildFactory();
        $label = "label";
        $byline = "byline";
        $name = "name_0";
        $pwd = $f->password($label, $byline)->withNameFrom($this->name_source);

        $r = $this->getDefaultRenderer();
        $expected = ""
            . "<div class=\"form-group row\">"
                . "<label class=\"control-label col-sm-4 col-md-3 col-lg-2\">$label</label>"
                . "<div class=\"col-sm-8 col-md-9 col-lg-10\">"
                    . "<div class=\"il-input-password\" id=\"id_1\">"
                        . "<input type=\"password\" name=\"$name\" class=\"form-control form-control-sm\" />"
                    . "</div>"
                    . "<div class=\"help-block\">$byline</div>"
                . "</div>"
            . "</div>";
        $this->assertHTMLEquals($expected, $r->render($pwd));
    }

    public function test_render_error() : void
    {
        $f = $this->buildFactory();
        $label = "label";
        $byline = "byline";
        $error = "an_error";
        $pwd = $f->password($label, $byline)->withNameFrom($this->name_source)->withError($error);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($pwd));
        $expected = $this->brutallyTrimHTML('
<div class="form-group row">
   <label class="control-label col-sm-4 col-md-3 col-lg-2">label</label>
   <div class="col-sm-8 col-md-9 col-lg-10">
      <div class="help-block alert alert-danger" role="alert">an_error</div>
      <div class="il-input-password" id="id_1"><input type="password" name="name_0" class="form-control form-control-sm" /></div>
      <div class="help-block">byline</div>
   </div>
</div>');

        $this->assertEquals($expected, $html);
    }

    public function test_render_no_byline() : void
    {
        $f = $this->buildFactory();
        $label = "label";
        $name = "name_0";
        $pwd = $f->password($label)->withNameFrom($this->name_source);

        $r = $this->getDefaultRenderer();
        $expected = ""
            . "<div class=\"form-group row\">"
                . "<label class=\"control-label col-sm-4 col-md-3 col-lg-2\">$label</label>"
                . "<div class=\"col-sm-8 col-md-9 col-lg-10\">"
                    . "<div class=\"il-input-password\" id=\"id_1\">"
                        . "<input type=\"password\" name=\"$name\" class=\"form-control form-control-sm\" />"
                    . "</div>"
                . "</div>"
            . "</div>";
        $this->assertHTMLEquals($expected, $r->render($pwd));
    }

    public function test_render_value() : void
    {
        $f = $this->buildFactory();
        $label = "label";
        $value = "value_0";
        $name = "name_0";
        $pwd = $f->password($label)->withValue($value)->withNameFrom($this->name_source);

        $r = $this->getDefaultRenderer();
        $expected = ""
            . "<div class=\"form-group row\">"
                . "<label class=\"control-label col-sm-4 col-md-3 col-lg-2\">$label</label>"
                . "<div class=\"col-sm-8 col-md-9 col-lg-10\">"
                    . "<div class=\"il-input-password\" id=\"id_1\">"
                        . "<input type=\"password\" name=\"$name\" value=\"$value\" class=\"form-control form-control-sm\" />"
                    . "</div>"
                . "</div>"
            . "</div>";
        $this->assertHTMLEquals($expected, $r->render($pwd));
    }

    public function test_render_required() : void
    {
        $f = $this->buildFactory();
        $label = "label";
        $name = "name_0";
        $pwd = $f->password($label)->withNameFrom($this->name_source)->withRequired(true);

        $r = $this->getDefaultRenderer();
        $html = $r->render($pwd);

        $expected = ""
        . "<div class=\"form-group row\">"
            . "<label class=\"control-label col-sm-4 col-md-3 col-lg-2\">" . "$label"
                . "<span class=\"asterisk\">*</span>"
            . "</label>"
            . "<div class=\"col-sm-8 col-md-9 col-lg-10\">"
                . "<div class=\"il-input-password\" id=\"id_1\">"
                    . "<input type=\"password\" name=\"$name\" class=\"form-control form-control-sm\" />"
                . "</div>"
            . "</div>"
        . "</div>";
        $this->assertHTMLEquals($expected, $html);
    }

    public function test_render_disabled() : void
    {
        $f = $this->buildFactory();
        $label = "label";
        $name = "name_0";
        $pwd = $f->password($label)->withNameFrom($this->name_source)->withDisabled(true);

        $r = $this->getDefaultRenderer();
        $html = $r->render($pwd);

        $expected = ""
        . "<div class=\"form-group row\">"
            . "<label class=\"control-label col-sm-4 col-md-3 col-lg-2\">$label</label>"
            . "<div class=\"col-sm-8 col-md-9 col-lg-10\">"
                . "<div class=\"il-input-password\" id=\"id_1\">"
                    . "<input type=\"password\" name=\"$name\" disabled=\"disabled\" class=\"form-control form-control-sm\" />"
                . "</div>"
            . "</div>"
        . "</div>";
        $this->assertHTMLEquals($expected, $html);
    }

    public function test_value_required() : void
    {
        $f = $this->buildFactory();
        $label = "label";
        $name = "name_0";
        $pwd = $f->password($label)->withNameFrom($this->name_source)->withRequired(true);

        $pwd1 = $pwd->withInput(new DefInputData([$name => "0"]));
        $value1 = $pwd1->getContent();
        $this->assertTrue($value1->isOk());

        $pwd2 = $pwd->withInput(new DefInputData([$name => ""]));
        $value2 = $pwd2->getContent();
        $this->assertTrue($value2->isError());
    }

    public function test_value_type() : void
    {
        $f = $this->buildFactory();
        $label = "label";
        $pwd = $f->password($label)->withNameFrom($this->name_source);
        $this->assertNull($pwd->getValue());

        $post = new _PWDInputData();
        $pwd = $pwd->withInput($post);
        $this->assertEquals($post->getOr('', ''), $pwd->getValue());
        $this->assertInstanceOf(PWD::class, $pwd->getContent()->value());
    }
}
