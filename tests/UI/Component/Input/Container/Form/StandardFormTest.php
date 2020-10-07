<?php declare(strict_types=1);

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../../Base.php");
require_once(__DIR__ . "/FormTest.php");

use ILIAS\UI\Implementation\Component\SignalGenerator;
use \ILIAS\Data;
use \ILIAS\UI\Component\Input\Container\Form\Form;

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
        $language = $this->createMock(\ilLanguage::class);
        return new ILIAS\UI\Implementation\Component\Input\Field\Factory(
            new SignalGenerator(),
            $df,
            new \ILIAS\Refinery\Factory($df, $language),
            $language
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


    public function test_getPostURL() : Form
    {
        $f = $this->buildFactory();
        $if = $this->buildInputFactory();
        $url = "MY_URL";
        $form = $f->standard($url, [$if->text("label")]);
        $this->assertEquals($url, $form->getPostURL());
        return $form;
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

        $expected = "<form role=\"form\" class=\"il-standard-form form-horizontal\" enctype=\"multipart/form-data\" action=\"MY_URL\" method=\"post\" novalidate=\"novalidate\">        <div class=\"il-standard-form-header clearfix\">          <div class=\"il-standard-form-cmd\"><button class=\"btn btn-default\" data-action=\"\">save</button></div>        </div>  <div class=\"form-group row\">     <label for=\"form_input_1\" class=\"control-label col-sm-3\">label</label>  <div class=\"col-sm-9\">          <input type=\"text\" name=\"form_input_1\" class=\"form-control form-control-sm\" />          <div class=\"help-block\">byline</div>                    </div></div>    <div class=\"il-standard-form-footer clearfix\">          <div class=\"il-standard-form-cmd\"><button class=\"btn btn-default\" data-action=\"\">save</button></div> </div></form>";
        $this->assertHTMLEquals($expected, $html);
    }

    public function test_render_no_url()
    {
        $f = $this->buildFactory();
        $if = $this->buildInputFactory();

        $url = "";
        $form = $f->standard($url, [
            $if->text("label", "byline"),
        ]);

        $r = $this->getDefaultRenderer();
        $html = $r->render($form);

        $expected = "<form role=\"form\" class=\"il-standard-form form-horizontal\" enctype=\"multipart/form-data\" method=\"post\" novalidate=\"novalidate\">        <div class=\"il-standard-form-header clearfix\">          <div class=\"il-standard-form-cmd\"><button class=\"btn btn-default\" data-action=\"\">save</button></div>        </div>  <div class=\"form-group row\">     <label for=\"form_input_1\" class=\"control-label col-sm-3\">label</label>  <div class=\"col-sm-9\">          <input type=\"text\" name=\"form_input_1\" class=\"form-control form-control-sm\" />          <div class=\"help-block\">byline</div>                    </div></div>    <div class=\"il-standard-form-footer clearfix\">          <div class=\"il-standard-form-cmd\"><button class=\"btn btn-default\" data-action=\"\">save</button></div> </div></form>";
        $this->assertHTMLEquals($expected, $html);
    }

    /**
     * @depends test_getPostURL
     */
    public function testSubmitLabel(Form $form)
    {
        $this->assertEquals('save', $form->getSubmitLabel());

        $label = 'someothersubmitlabel';
        $form = $form->withSubmitLabel($label);
        $this->assertEquals($label, $form->getSubmitLabel());
    }

    /**
     * @depends test_getPostURL
     */
    public function testCancelButton(Form $form) : Form
    {
        $this->assertNull($form->getCancelURL());
        $data = new Data\Factory();
        $url = $data->uri('http://www.ilias.de');
        $form = $form->withCancelURL($url);
        $this->assertEquals($url, $form->getCancelURL());
        return $form;
    }

    /**
     * @depends testCancelButton
     */
    public function testCancelButtonRender(Form $form)
    {
        $expected = <<<EOT
<form role="form" class="il-standard-form form-horizontal" enctype="multipart/form-data" action="MY_URL" method="post" novalidate="novalidate">

    <div class="il-standard-form-header clearfix">
        <div class="il-standard-form-cmd">
            <button class="btn btn-default" data-action="">save</button>
            <button class="btn btn-default" data-action="http://www.ilias.de" id="id_1">cancel</button>
        </div>
    </div>

    <div class="form-group row">
        <label for="form_input_1" class="control-label col-sm-3">label</label>
        <div class="col-sm-9">
            <input type="text" name="form_input_1" class="form-control form-control-sm" />
        </div>
    </div>

    <div class="il-standard-form-footer clearfix">
        <div class="il-standard-form-cmd">
            <button class="btn btn-default" data-action="">save</button>
            <button class="btn btn-default" data-action="http://www.ilias.de" id="id_2">cancel</button>
        </div>
    </div>

</form>
EOT;
        $r = $this->getDefaultRenderer();
        $html = $r->render($form);
        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }

    /**
     * @depends test_getPostURL
     */
    public function testBottomButtonOnly(Form $form)
    {
        $this->assertFalse($form->hasBottomButtonsOnly());
        $form = $form->withBottomButtonsOnly();
        $this->assertTrue($form->hasBottomButtonsOnly());
        $form = $form->withBottomButtonsOnly(false);
        $this->assertFalse($form->hasBottomButtonsOnly());
    }

    /**
     * @depends test_getPostURL
     */
    public function testBottomButtonRender(Form $form)
    {
        $form = $form->withBottomButtonsOnly(true);
        $expected = <<<EOT
<form role="form" class="il-standard-form form-horizontal" enctype="multipart/form-data" action="MY_URL" method="post" novalidate="novalidate">

    <div class="form-group row">
        <label for="form_input_1" class="control-label col-sm-3">label</label>
        <div class="col-sm-9">
            <input type="text" name="form_input_1" class="form-control form-control-sm" />
        </div>
    </div>

    <div class="il-standard-form-footer clearfix">
        <div class="il-standard-form-cmd">
            <button class="btn btn-default" data-action="">save</button>
        </div>
    </div>
</form>
EOT;
        $r = $this->getDefaultRenderer();
        $html = $r->render($form);
        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }
}
