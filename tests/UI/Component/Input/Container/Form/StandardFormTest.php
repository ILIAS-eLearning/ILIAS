<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../../Base.php");
require_once(__DIR__ . "/FormTest.php");

use ILIAS\UI\Implementation\Component\SignalGenerator;
use \ILIAS\Data;
use \ILIAS\Validation;
use \ILIAS\Transformation;

class WithButtonNoUIFactory extends NoUIFactory
{
    protected $button_factory;


    public function __construct($button_factory)
    {
        $this->button_factory = $button_factory;
    }


    public function button()
    {
        return $this->button_factory;
    }
}

/**
 * Test on standard form implementation.
 */
class StandardFormTest extends ILIAS_UI_TestBase
{
    protected function buildFactory()
    {
        return new ILIAS\UI\Implementation\Component\Input\Container\Form\Factory($this->buildInputFactory());
    }


    protected function buildInputFactory()
    {
        $df = new Data\Factory();
        return new ILIAS\UI\Implementation\Component\Input\Field\Factory(
            new SignalGenerator(),
            $df,
            new Validation\Factory($df, $this->createMock(\ilLanguage::class)),
            new Transformation\Factory()
        );
    }

    protected function buildButtonFactory()
    {
        return new ILIAS\UI\Implementation\Component\Button\Factory;
    }


    public function getUIFactory()
    {
        return new WithButtonNoUIFactory($this->buildButtonFactory());
    }


    public function test_getPostURL()
    {
        $f = $this->buildFactory();
        $if = $this->buildInputFactory();
        $url = "MY_URL";
        $form = $f->standard($url, [$if->text("label")]);
        $this->assertEquals($url, $form->getPostURL());
    }


    public function test_render()
    {
        $f = $this->buildFactory();
        $if = $this->buildInputFactory();

        $url = "MY_URL";
        $form = $f->standard($url, [
                $if->text("label", "byline"),
            ]);

        $r = $this->getDefaultRenderer();
        $html = $r->render($form);

        $expected = "<form role=\"form\" class=\"il-standard-form form-horizontal\" enctype=\"multipart/formdata\" action=\"MY_URL\" method=\"post\" novalidate=\"novalidate\">        <div class=\"il-standard-form-header clearfix\">          <div class=\"il-standard-form-cmd\"><button class=\"btn btn-default\" data-action=\"#\" id=\"id_1\">save</button></div>        </div>  <div class=\"form-group row\">     <label for=\"form_input_1\" class=\"control-label col-sm-3\">label</label>  <div class=\"col-sm-9\">          <input type=\"text\" name=\"form_input_1\" class=\"form-control form-control-sm\" />          <div class=\"help-block\">byline</div>                    </div></div>    <div class=\"il-standard-form-footer clearfix\">          <div class=\"il-standard-form-cmd\"><button class=\"btn btn-default\" data-action=\"#\" id=\"id_2\">save</button></div> </div></form>";
        $this->assertHTMLEquals($expected, $html);
    }
}
