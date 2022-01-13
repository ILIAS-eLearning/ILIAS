<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../../Base.php");
require_once(__DIR__ . "/FormTest.php");

use ILIAS\UI\Implementation\Component\SignalGenerator;
use \ILIAS\Data;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Implementation\Component\Input\Container\Form;

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
        $html = $this->brutallyTrimHTML($r->render($form));

        $expected = $this->brutallyTrimHTML('
<form role="form" class="il-standard-form form-horizontal" enctype="multipart/form-data" action="MY_URL" method="post" novalidate="novalidate">
   <div class="il-standard-form-header clearfix">
      <div class="il-standard-form-cmd"><button class="btn btn-default" data-action="">save</button></div>
   </div>
   <div class="form-group row">
      <label for="id_1" class="control-label col-sm-3">label</label>
      <div class="col-sm-9">
         <input id="id_1" type="text" name="form_input_1" class="form-control form-control-sm"/>
         <div class="help-block">byline</div>
      </div>
   </div>
   <div class="il-standard-form-footer clearfix">
      <div class="il-standard-form-cmd"><button class="btn btn-default" data-action="">save</button></div>
   </div>
</form>
        ');
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
        $html = $this->brutallyTrimHTML($r->render($form));

        $expected = $this->brutallyTrimHTML('
<form role="form" class="il-standard-form form-horizontal" enctype="multipart/form-data" method="post" novalidate="novalidate">
   <div class="il-standard-form-header clearfix">
      <div class="il-standard-form-cmd"><button class="btn btn-default" data-action="">save</button></div>
   </div>
   <div class="form-group row">
      <label for="id_1" class="control-label col-sm-3">label</label>
      <div class="col-sm-9">
         <input id="id_1" type="text" name="form_input_1" class="form-control form-control-sm"/>
         <div class="help-block">byline</div>
      </div>
   </div>
   <div class="il-standard-form-footer clearfix">
      <div class="il-standard-form-cmd"><button class="btn btn-default" data-action="">save</button></div>
   </div>
</form>
        ');
        $this->assertHTMLEquals($expected, $html);
    }


    public function testRenderWithErrorOnField()
    {
        $r = $this->getDefaultRenderer();
        $df = new Data\Factory();
        $language = $this->createMock(\ilLanguage::class);
        $language
            ->expects($this->once())
            ->method("txt")
            ->willReturn('testing error message');

        $refinery = new \ILIAS\Refinery\Factory($df, $language);

        $if = new ILIAS\UI\Implementation\Component\Input\Field\Factory(
            new SignalGenerator(),
            $df,
            $refinery,
            $language
        );

        $fail = $refinery->custom()->constraint(function ($v) {
            return false;
        }, "This is invalid...");
        $input = $if->text("label", "byline");
        
        $input = $input->withAdditionalTransformation($fail);
        
        $form = new Form\Standard($if, '', [$input]);

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects($this->once())
            ->method("getParsedBody")
            ->willReturn([
                'form_input_1' => ''
            ]);

        $form = $form->withRequest($request);
        $this->assertNull($form->getData());

        $html = $this->brutallyTrimHTML($r->render($form));
        $expected = $this->brutallyTrimHTML('
            <form role="form" class="il-standard-form form-horizontal" enctype="multipart/form-data" method="post" novalidate="novalidate">
                <div class="il-standard-form-header clearfix">
                    <div class="il-standard-form-cmd"><button class="btn btn-default" data-action="">save</button></div>
                </div>

                <div class="help-block alert alert-danger" role="alert">testing error message</div>

                <div class="form-group row">
                    <label for="id_1" class="control-label col-sm-3">label</label>
                    <div class="col-sm-9">
                        <div class="help-block alert alert-danger" role="alert">This is invalid...</div>
                        <input id="id_1" type="text" name="form_input_1" class="form-control form-control-sm" />
                        <div class="help-block">byline</div>
                    </div>
                </div>
                <div class="il-standard-form-footer clearfix">
                    <div class="il-standard-form-cmd"><button class="btn btn-default" data-action="">save</button></div>
                </div>
            </form>
        ');
        $this->assertHTMLEquals($expected, $html);
    }


    public function testRenderWithErrorOnForm()
    {
        $r = $this->getDefaultRenderer();
        $df = new Data\Factory();
        $language = $this->createMock(\ilLanguage::class);
        $refinery = new \ILIAS\Refinery\Factory($df, $language);

        $if = new ILIAS\UI\Implementation\Component\Input\Field\Factory(
            new SignalGenerator(),
            $df,
            $refinery,
            $language
        );

        $fail = $refinery->custom()->constraint(function ($v) {
            return false;
        }, "This is a fail on form.");
        $input = $if->text("label", "byline");

        $form = new Form\Standard($if, '', [$input]);
        $form = $form->withAdditionalTransformation($fail);

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects($this->once())
            ->method("getParsedBody")
            ->willReturn([
                'form_input_1' => ''
            ]);

        $form = $form->withRequest($request);
        $this->assertNull($form->getData());

        $html = $this->brutallyTrimHTML($r->render($form));
        $expected = $this->brutallyTrimHTML('
            <form role="form" class="il-standard-form form-horizontal" enctype="multipart/form-data" method="post" novalidate="novalidate">
                <div class="il-standard-form-header clearfix">
                    <div class="il-standard-form-cmd"><button class="btn btn-default" data-action="">save</button></div>
                </div>

                <div class="help-block alert alert-danger" role="alert">This is a fail on form.</div>

                <div class="form-group row">
                    <label for="id_1" class="control-label col-sm-3">label</label>
                    <div class="col-sm-9">
                        <input id="id_1" type="text" name="form_input_1" class="form-control form-control-sm" />
                        <div class="help-block">byline</div>
                    </div>
                </div>
                <div class="il-standard-form-footer clearfix">
                    <div class="il-standard-form-cmd"><button class="btn btn-default" data-action="">save</button></div>
                </div>
            </form>
        ');
        $this->assertHTMLEquals($expected, $html);
    }
}
