<?php

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

declare(strict_types=1);

require_once(__DIR__ . "/../../../../../../../../vendor/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../../Base.php");
require_once(__DIR__ . "/FormTest.php");
require_once(__DIR__ . "/../../Field/CommonFieldRendering.php");

use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\Data;
use ILIAS\UI\Implementation\Component\Button\Factory;
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

    public function button(): Factory
    {
        return $this->button_factory;
    }
}

class InputNameSource implements NameSource
{
    public int $count = 0;

    public function getNewName(): string
    {
        $name = "input_{$this->count}";
        $this->count++;

        return $name;
    }

    public function getNewDedicatedName(string $dedicated_name): string
    {
        $name = $dedicated_name . "_{$this->count}";
        $this->count++;

        return $name;
    }
}

/**
 * Test on standard form implementation.
 */
class StandardFormTest extends ILIAS_UI_TestBase
{
    use CommonFieldRendering;

    protected function buildFactory(): I\Input\Container\Form\Factory
    {

        return new I\Input\Container\Form\Factory(
            $this->getFieldFactory(),
            new SignalGenerator()
        );
    }

    protected function buildButtonFactory(): I\Button\Factory
    {
        return new I\Button\Factory();
    }

    public function getUIFactory(): WithButtonNoUIFactory
    {
        return new WithButtonNoUIFactory($this->buildButtonFactory());
    }

    public function testGetPostURL(): void
    {
        $f = $this->buildFactory();
        $if = $this->getFieldFactory();
        $url = "MY_URL";
        $form = $f->standard($url, [$if->text("label")]);
        $this->assertEquals($url, $form->getPostURL());
    }

    protected function getTextFieldHtml(): string
    {
        return $this->getFormWrappedHtml(
            'text-field-input',
            'label',
            '<input id="id_1" type="text" name="form/input_0" class="c-field-text" />',
            'byline',
            'id_1',
            null,
            'form/input_0'
        );
    }

    public function testRender(): void
    {
        $f = $this->buildFactory();
        $if = $this->getFieldFactory();

        $url = "MY_URL";
        $form = $f->standard($url, [
                $if->text("label", "byline"),
            ]);

        $r = $this->getDefaultRenderer();
        $html = $this->getDefaultRenderer()->render($form);

        $expected = $this->brutallyTrimHTML('
        <form id="id_2" class="c-form c-form--horizontal" enctype="multipart/form-data" action="MY_URL" method="post">
           <div class="c-form__header">
              <div class="c-form__actions"><button class="btn btn-default" data-action="">save</button></div>
           </div>'
           . $this->getTextFieldHtml() .
          '<div class="c-form__footer">
              <div class="c-form__actions"><button class="btn btn-default" data-action="">save</button></div>
           </div>
        </form>
        ');
        $this->assertHTMLEquals($expected, $html);
    }

    public function testSubmitCaption(): void
    {
        $f = $this->buildFactory();
        $if = $this->getFieldFactory();

        $url = "MY_URL";
        $form = $f->standard($url, [
            $if->text("label", "byline"),
        ]);

        $this->assertNull($form->getSubmitLabel());

        $caption = 'Caption';
        $form = $form->withSubmitLabel($caption);

        $this->assertEquals($caption, $form->getSubmitLabel());
    }

    public function testSubmitCaptionRender(): void
    {
        $f = $this->buildFactory();
        $if = $this->getFieldFactory();

        $url = "MY_URL";
        $form = $f->standard($url, [
            $if->text("label", "byline"),
        ])->withSubmitLabel('create');

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($form));

        $expected = $this->brutallyTrimHTML('
        <form id="id_2" class="c-form c-form--horizontal" enctype="multipart/form-data" action="MY_URL" method="post">
           <div class="c-form__header">
              <div class="c-form__actions"><button class="btn btn-default" data-action="">create</button></div>
           </div>'
            . $this->getTextFieldHtml() .
           '<div class="c-form__footer">
              <div class="c-form__actions"><button class="btn btn-default" data-action="">create</button></div>
           </div>
        </form>
        ');
        $this->assertHTMLEquals($expected, $html);
    }

    public function testRenderNoUrl(): void
    {
        $f = $this->buildFactory();
        $if = $this->getFieldFactory();

        $url = "";
        $form = $f->standard($url, [
            $if->text("label", "byline"),
        ]);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($form));

        $expected = $this->brutallyTrimHTML('
        <form id="id_2" class="c-form c-form--horizontal" enctype="multipart/form-data" method="post">
            <div class="c-form__header">
                <div class="c-form__actions">
                    <button class="btn btn-default" data-action="">save</button>
                </div>
            </div>'
           . $this->getTextFieldHtml() .
           '<div class="c-form__footer">
                <div class="c-form__actions">
                    <button class="btn btn-default" data-action="">save</button>
                </div>
            </div>
        </form>
        ');
        $this->assertHTMLEquals($expected, $html);
    }


    public function testRenderWithErrorOnField(): void
    {
        $r = $this->getDefaultRenderer();
        $df = new Data\Factory();
        $language = $this->createMock(\ILIAS\Language\Language::class);
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

        $form = new Form\Standard(new SignalGenerator(), $if, new InputNameSource(), '', [$input]);

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects($this->once())
            ->method("getParsedBody")
            ->willReturn([
                'form_0/input_1' => ''
            ]);

        $form = $form->withRequest($request);
        $this->assertNull($form->getData());

        $html = $this->brutallyTrimHTML($r->render($form));
        $expected = $this->brutallyTrimHTML('
<form id="id_3" class="c-form c-form--horizontal" enctype="multipart/form-data" method="post">
    <div class="c-form__header">
        <div class="c-form__actions">
            <button class="btn btn-default" data-action="">save</button>
        </div>
    </div>
    <div class="c-form__error-msg alert alert-danger"><span class="sr-only">ui_error:</span>testing error
        message
    </div>
    <fieldset class="c-input" data-il-ui-component="text-field-input" data-il-ui-input-name="form_0/input_1"
              aria-describedby="id_2"><label class="c-input__label" for="id_1">label</label>
        <div class="c-input__field"><input id="id_1" type="text" name="form_0/input_1" class="c-field-text" /></div>
        <div class="c-input__error-msg alert alert-danger" id="id_2"><span class="sr-only">ui_error:</span>This is
            invalid...
        </div>
        <div class="c-input__help-byline">byline</div>
        <div class="c-input__value_representation"></div>
    </fieldset>
    <div class="c-form__footer">
        <div class="c-form__actions">
            <button class="btn btn-default" data-action="">save</button>
        </div>
    </div>
</form>
');
        $this->assertEquals($expected, $html);
        $this->assertHTMLEquals($expected, $html);
    }


    public function testRenderWithErrorOnForm(): void
    {
        $r = $this->getDefaultRenderer();
        $df = new Data\Factory();
        $language = $this->createMock(\ILIAS\Language\Language::class);
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

        $form = new Form\Standard(new SignalGenerator(), $if, new InputNameSource(), '', [$input]);
        $form = $form->withAdditionalTransformation($fail);

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects($this->once())
            ->method("getParsedBody")
            ->willReturn([
                'form_0/input_1' => ''
            ]);

        $form = $form->withRequest($request);
        $this->assertNull($form->getData());


        $field_html = $this->getFormWrappedHtml(
            'text-field-input',
            'label',
            '<input id="id_1" type="text" name="form_0/input_1" class="c-field-text"/>',
            'byline',
            'id_1',
            null,
            'form_0/input_1'
        );

        $html = $this->brutallyTrimHTML($r->render($form));
        $expected = $this->brutallyTrimHTML('
            <form id="id_2" class="c-form c-form--horizontal" enctype="multipart/form-data" method="post">
                <div class="c-form__header">
                    <div class="c-form__actions"><button class="btn btn-default" data-action="">save</button></div>
                </div>
                <div class="c-form__error-msg alert alert-danger"><span class="sr-only">ui_error:</span>This is a fail on form.</div>
                ' . $field_html . '
                <div class="c-form__footer">
                    <div class="c-form__actions"><button class="btn btn-default" data-action="">save</button></div>
                </div>
            </form>
        ');
        $this->assertHTMLEquals($expected, $html);
    }

    public function testStandardFormRenderWithRequired(): void
    {
        $f = $this->buildFactory();
        $if = $this->getFieldFactory();

        $url = "MY_URL";
        $form = $f->standard($url, [$if->text("label", "byline")->withRequired(true)]);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($form));

        $field_html = $this->getFormWrappedHtml(
            'text-field-input',
            'label<span class="asterisk" aria-label="required_field">*</span>',
            '<input id="id_1" type="text" name="form/input_0" class="c-field-text" />',
            'byline',
            'id_1',
            null,
            'form/input_0'
        );

        $expected = $this->brutallyTrimHTML('
<form id="id_2" class="c-form c-form--horizontal" enctype="multipart/form-data" action="MY_URL" method="post">
    <div class="c-form__header">
        <div class="c-form__actions"><button class="btn btn-default" data-action="">save</button></div>
        <div class="c-form__required">
            <span class="asterisk">*</span><span class="small"> required_field</span>
        </div>
    </div>
    ' . $field_html . '
    <div class="c-form__footer">
        <div class="c-form__required">
            <span class="asterisk">*</span><span class="small"> required_field</span>
        </div>
      <div class="c-form__actions"><button class="btn btn-default" data-action="">save</button></div>
   </div>
</form>
        ');
        $this->assertHTMLEquals($expected, $html);
    }
}
