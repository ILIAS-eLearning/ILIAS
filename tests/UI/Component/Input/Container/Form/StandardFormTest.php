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
 
require_once(__DIR__ . "/../../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../../Base.php");
require_once(__DIR__ . "/FormTest.php");

use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\Data;
use ILIAS\UI\Component\Button\Factory;
use ILIAS\UI\Implementation\Component as I;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Implementation\Component\Input\Container\Form;
use ILIAS\UI\Implementation\Component\Input\NameSource;

class WithButtonNoUIFactory extends NoUIFactory
{
    protected Factory $button_factory;

    public function __construct(Factory $button_factory)
    {
        $this->button_factory = $button_factory;
    }

    public function button() : Factory
    {
        return $this->button_factory;
    }
}

class InputNameSource implements NameSource
{
    public int $count = 0;

    public function getNewName() : string
    {
        $name = "form_input_{$this->count}";
        $this->count++;

        return $name;
    }
}

/**
 * Test on standard form implementation.
 */
class StandardFormTest extends ILIAS_UI_TestBase
{
    protected function buildFactory() : I\Input\Container\Form\Factory
    {
        return new I\Input\Container\Form\Factory($this->buildInputFactory(), new InputNameSource());
    }

    protected function buildInputFactory() : I\Input\Field\Factory
    {
        $df = new Data\Factory();
        $language = $this->createMock(ilLanguage::class);
        return new I\Input\Field\Factory(
            $this->createMock(\ILIAS\UI\Implementation\Component\Input\UploadLimitResolver::class),
            new SignalGenerator(),
            $df,
            new \ILIAS\Refinery\Factory($df, $language),
            $language
        );
    }

    protected function buildButtonFactory() : I\Button\Factory
    {
        return new I\Button\Factory;
    }

    public function getUIFactory() : WithButtonNoUIFactory
    {
        return new WithButtonNoUIFactory($this->buildButtonFactory());
    }

    public function test_getPostURL() : void
    {
        $f = $this->buildFactory();
        $if = $this->buildInputFactory();
        $url = "MY_URL";
        $form = $f->standard($url, [$if->text("label")]);
        $this->assertEquals($url, $form->getPostURL());
    }

    public function test_render() : void
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
      <label for="id_1" class="control-label col-sm-4 col-md-3 col-lg-2">label</label>
      <div class="col-sm-8 col-md-9 col-lg-10">
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

    public function test_submit_caption() : void
    {
        $f = $this->buildFactory();
        $if = $this->buildInputFactory();

        $url = "MY_URL";
        $form = $f->standard($url, [
            $if->text("label", "byline"),
        ]);

        $this->assertNull($form->getSubmitCaption());

        $caption = 'Caption';
        $form = $form->withSubmitCaption($caption);

        $this->assertEquals($caption, $form->getSubmitCaption());
    }

    public function test_submit_caption_render() : void
    {
        $f = $this->buildFactory();
        $if = $this->buildInputFactory();

        $url = "MY_URL";
        $form = $f->standard($url, [
            $if->text("label", "byline"),
        ])->withSubmitCaption('create');

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($form));

        $expected = $this->brutallyTrimHTML('
<form role="form" class="il-standard-form form-horizontal" enctype="multipart/form-data" action="MY_URL" method="post" novalidate="novalidate">
   <div class="il-standard-form-header clearfix">
      <div class="il-standard-form-cmd"><button class="btn btn-default" data-action="">create</button></div>
   </div>
   <div class="form-group row">
      <label for="id_1" class="control-label col-sm-4 col-md-3 col-lg-2">label</label>
      <div class="col-sm-8 col-md-9 col-lg-10">
         <input id="id_1" type="text" name="form_input_1" class="form-control form-control-sm"/>
         <div class="help-block">byline</div>
      </div>
   </div>
   <div class="il-standard-form-footer clearfix">
      <div class="il-standard-form-cmd"><button class="btn btn-default" data-action="">create</button></div>
   </div>
</form>
        ');
        $this->assertHTMLEquals($expected, $html);
    }

    public function test_render_no_url() : void
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
      <label for="id_1" class="control-label col-sm-4 col-md-3 col-lg-2">label</label>
      <div class="col-sm-8 col-md-9 col-lg-10">
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


    public function testRenderWithErrorOnField() : void
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
            $this->createMock(\ILIAS\UI\Implementation\Component\Input\UploadLimitResolver::class),
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
        
        $form = new Form\Standard($if, new InputNameSource, '', [$input]);

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
                    <label for="id_1" class="control-label col-sm-4 col-md-3 col-lg-2">label</label>
                    <div class="col-sm-8 col-md-9 col-lg-10">
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


    public function testRenderWithErrorOnForm() : void
    {
        $r = $this->getDefaultRenderer();
        $df = new Data\Factory();
        $language = $this->createMock(\ilLanguage::class);
        $refinery = new \ILIAS\Refinery\Factory($df, $language);

        $if = new ILIAS\UI\Implementation\Component\Input\Field\Factory(
            $this->createMock(\ILIAS\UI\Implementation\Component\Input\UploadLimitResolver::class),
            new SignalGenerator(),
            $df,
            $refinery,
            $language
        );

        $fail = $refinery->custom()->constraint(function ($v) {
            return false;
        }, "This is a fail on form.");
        $input = $if->text("label", "byline");

        $form = new Form\Standard($if, new InputNameSource, '', [$input]);
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
                    <label for="id_1" class="control-label col-sm-4 col-md-3 col-lg-2">label</label>
                    <div class="col-sm-8 col-md-9 col-lg-10">
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
