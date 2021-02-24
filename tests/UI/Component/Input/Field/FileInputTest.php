<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");
require_once(__DIR__ . "/InputTest.php");

use ILIAS\Data;
use ILIAS\UI\Component\Input\Field;
use ILIAS\UI\Implementation\Component\Input\Field\FieldRendererFactory;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\Implementation\Component\Symbol\Glyph\GlyphRendererFactory;
use ILIAS\UI\Implementation\Render\DefaultRendererFactory;
use ILIAS\UI\Implementation\Render\FSLoader;
use ILIAS\UI\Implementation\Render\JavaScriptBinding;
use ILIAS\UI\Implementation\Render\LoaderCachingWrapper;
use ILIAS\UI\Implementation\Render\LoaderResourceRegistryWrapper;

class WithSomeButtonNoUIFactory extends NoUIFactory
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

class FileInputTest extends ILIAS_UI_TestBase
{
    public function setUp() : void
    {
        $this->name_source = new DefNamesource();
    }


    protected function buildFactory()
    {
        $df = new Data\Factory();
        $language = $this->createMock(\ilLanguage::class);

        return new ILIAS\UI\Implementation\Component\Input\Field\Factory(
            new SignalGenerator(),
            $df,
            new ILIAS\Refinery\Factory($df, $language),
            $language
        );
    }


    private function getUploadHandler() : Field\UploadHandler
    {
        return new class implements Field\UploadHandler {
            public function getFileIdentifierParameterName() : string
            {
                return 'file_id';
            }


            public function getUploadURL() : string
            {
                return 'uploadurl';
            }


            public function getFileRemovalURL() : string
            {
                return 'removalurl';
            }


            /**
             * @inheritDoc
             */
            public function getExistingFileInfoURL() : string
            {
                return 'infourl';
            }


            /**
             * @inheritDoc
             */
            public function getInfoForExistingFiles(array $file_ids) : array
            {
                return [];
            }
        };
    }


    public function test_implements_factory_interface()
    {
        $f = $this->buildFactory();

        $text = $f->file($this->getUploadHandler(), "label", "byline");

        $this->assertInstanceOf(Field\Input::class, $text);
        $this->assertInstanceOf(Field\File::class, $text);
    }


    public function test_render()
    {
        $f = $this->buildFactory();
        $label = "label";
        $byline = "byline";
        $name = "name_0";
        $text = $f->file($this->getUploadHandler(), $label, $byline)->withNameFrom($this->name_source);

        $r = $this->getDefaultRenderer();
        $html = $this->normalizeHTML($r->render($text));

        $expected
            = '<div class="form-group row">	<label for="name_0" class="control-label col-sm-3">label</label>	<div class="col-sm-9">		<div class="il-input-file" id="id_2"><div class="il-input-file-dropzone">	<button class="btn btn-link" data-action="#" id="id_1">select_files_from_computer</button></div><div class="il-input-file-filelist">	<div class="il-input-file-template dz-preview dz-file-preview" style="display: block; border: 1px solid red" data-file-id="">		<div class="dz-details">			<div class="il-input-file-fileinfo">				<div class="il-input-file-fileinfo-title"><span data-dz-name></span></div>				<div data-dz-size class="il-input-file-fileinfo-size"></div>				<div class="il-input-file-fileinfo-close">					<button type="button" class="close" data-dz-remove>						<span aria-hidden="true">&times;</span>						<span class="sr-only">Close</span>					</button>				</div>			</div>			<!--			<div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>-->			<!--			<div class="dz-success-mark"><span>✔</span></div>-->			<!--			<div class="dz-error-mark"><span>✘</span></div>-->			<div class="dz-error-message il-input-file-error"><span data-dz-errormessage></span></div>		</div>	</div></div><input class="input-template" type="hidden" name="name_0[]" value="" data-file-id=""></div>		<div class="help-block">byline</div>			</div></div>';
        $this->assertEquals($expected, $html);
    }


    public function test_render_error()
    {
        $f = $this->buildFactory();
        $label = "label";
        $byline = "byline";
        $name = "name_0";
        $error = "an_error";
        $text = $f->file($this->getUploadHandler(), $label, $byline)->withNameFrom($this->name_source)->withError($error);

        $r = $this->getDefaultRenderer();
        $html = $this->normalizeHTML($r->render($text));

        $expected
            = '<div class="form-group row">	<label for="name_0" class="control-label col-sm-3">label</label>	<div class="col-sm-9">		<div class="il-input-file" id="id_2"><div class="il-input-file-dropzone">	<button class="btn btn-link" data-action="#" id="id_1">select_files_from_computer</button></div><div class="il-input-file-filelist">	<div class="il-input-file-template dz-preview dz-file-preview" style="display: block; border: 1px solid red" data-file-id="">		<div class="dz-details">			<div class="il-input-file-fileinfo">				<div class="il-input-file-fileinfo-title"><span data-dz-name></span></div>				<div data-dz-size class="il-input-file-fileinfo-size"></div>				<div class="il-input-file-fileinfo-close">					<button type="button" class="close" data-dz-remove>						<span aria-hidden="true">&times;</span>						<span class="sr-only">Close</span>					</button>				</div>			</div>			<!--			<div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>-->			<!--			<div class="dz-success-mark"><span>✔</span></div>-->			<!--			<div class="dz-error-mark"><span>✘</span></div>-->			<div class="dz-error-message il-input-file-error"><span data-dz-errormessage></span></div>		</div>	</div></div><input class="input-template" type="hidden" name="name_0[]" value="" data-file-id=""></div>		<div class="help-block">byline</div>		<div class="help-block alert alert-danger" role="alert">			<img border="0" src="./templates/default/images/icon_alert.svg" alt="alert" />			an_error		</div>	</div></div>';
        $this->assertEquals($expected, $html);
    }


    public function test_render_no_byline()
    {
        $f = $this->buildFactory();
        $label = "label";
        $name = "name_0";
        $text = $f->file($this->getUploadHandler(), $label)->withNameFrom($this->name_source);

        $r = $this->getDefaultRenderer();
        $html = $this->normalizeHTML($r->render($text));

        $expected
            = '<div class="form-group row">	<label for="name_0" class="control-label col-sm-3">label</label>	<div class="col-sm-9">		<div class="il-input-file" id="id_2"><div class="il-input-file-dropzone">	<button class="btn btn-link" data-action="#" id="id_1">select_files_from_computer</button></div><div class="il-input-file-filelist">	<div class="il-input-file-template dz-preview dz-file-preview" style="display: block; border: 1px solid red" data-file-id="">		<div class="dz-details">			<div class="il-input-file-fileinfo">				<div class="il-input-file-fileinfo-title"><span data-dz-name></span></div>				<div data-dz-size class="il-input-file-fileinfo-size"></div>				<div class="il-input-file-fileinfo-close">					<button type="button" class="close" data-dz-remove>						<span aria-hidden="true">&times;</span>						<span class="sr-only">Close</span>					</button>				</div>			</div>			<!--			<div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>-->			<!--			<div class="dz-success-mark"><span>✔</span></div>-->			<!--			<div class="dz-error-mark"><span>✘</span></div>-->			<div class="dz-error-message il-input-file-error"><span data-dz-errormessage></span></div>		</div>	</div></div><input class="input-template" type="hidden" name="name_0[]" value="" data-file-id=""></div>					</div></div>';
        $this->assertEquals($expected, $html);
    }


    public function test_render_value()
    {
        $f = $this->buildFactory();
        $label = "label";
        $value = ["value"];
        $name = "name_0";
        $text = $f->file($this->getUploadHandler(), $label)->withValue($value)->withNameFrom($this->name_source);

        $r = $this->getDefaultRenderer();
        $html = $this->normalizeHTML($r->render($text));

        $expected
            = '<div class="form-group row">	<label for="name_0" class="control-label col-sm-3">label</label>	<div class="col-sm-9">		<div class="il-input-file" id="id_2"><div class="il-input-file-dropzone">	<button class="btn btn-link" data-action="#" id="id_1">select_files_from_computer</button></div><div class="il-input-file-filelist">	<div class="il-input-file-template dz-preview dz-file-preview" style="display: block; border: 1px solid red" data-file-id="">		<div class="dz-details">			<div class="il-input-file-fileinfo">				<div class="il-input-file-fileinfo-title"><span data-dz-name></span></div>				<div data-dz-size class="il-input-file-fileinfo-size"></div>				<div class="il-input-file-fileinfo-close">					<button type="button" class="close" data-dz-remove>						<span aria-hidden="true">&times;</span>						<span class="sr-only">Close</span>					</button>				</div>			</div>			<!--			<div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>-->			<!--			<div class="dz-success-mark"><span>✔</span></div>-->			<!--			<div class="dz-error-mark"><span>✘</span></div>-->			<div class="dz-error-message il-input-file-error"><span data-dz-errormessage></span></div>		</div>	</div></div><input class="input-template" type="hidden" name="name_0[]" value="" data-file-id=""></div>					</div></div>';
        $this->assertEquals($expected, $html);
    }


    public function test_render_required()
    {
        $f = $this->buildFactory();
        $label = "label";
        $name = "name_0";
        $text = $f->file($this->getUploadHandler(), $label)->withNameFrom($this->name_source)->withRequired(true);

        $r = $this->getDefaultRenderer();
        $html = $this->normalizeHTML($r->render($text));

        $expected
            = '<div class="form-group row">	<label for="name_0" class="control-label col-sm-3">label<span class="asterisk">*</span></label>	<div class="col-sm-9">		<div class="il-input-file" id="id_2"><div class="il-input-file-dropzone">	<button class="btn btn-link" data-action="#" id="id_1">select_files_from_computer</button></div><div class="il-input-file-filelist">	<div class="il-input-file-template dz-preview dz-file-preview" style="display: block; border: 1px solid red" data-file-id="">		<div class="dz-details">			<div class="il-input-file-fileinfo">				<div class="il-input-file-fileinfo-title"><span data-dz-name></span></div>				<div data-dz-size class="il-input-file-fileinfo-size"></div>				<div class="il-input-file-fileinfo-close">					<button type="button" class="close" data-dz-remove>						<span aria-hidden="true">&times;</span>						<span class="sr-only">Close</span>					</button>				</div>			</div>			<!--			<div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>-->			<!--			<div class="dz-success-mark"><span>✔</span></div>-->			<!--			<div class="dz-error-mark"><span>✘</span></div>-->			<div class="dz-error-message il-input-file-error"><span data-dz-errormessage></span></div>		</div>	</div></div><input class="input-template" type="hidden" name="name_0[]" value="" data-file-id=""></div>					</div></div>';
        $this->assertEquals($expected, $html);
    }


    public function test_render_disabled()
    {
        $f = $this->buildFactory();
        $label = "label";
        $name = "name_0";
        $text = $f->file($this->getUploadHandler(), $label)->withNameFrom($this->name_source)->withDisabled(true);

        $r = $this->getDefaultRenderer();
        $html = $this->normalizeHTML($r->render($text));

        $expected
            = '<div class="form-group row">	<label for="name_0" class="control-label col-sm-3">label</label>	<div class="col-sm-9">		<div class="il-input-file" id="id_2"><div class="il-input-file-dropzone">	<button class="btn btn-link" data-action="#" id="id_1">select_files_from_computer</button></div><div class="il-input-file-filelist">	<div class="il-input-file-template dz-preview dz-file-preview" style="display: block; border: 1px solid red" data-file-id="">		<div class="dz-details">			<div class="il-input-file-fileinfo">				<div class="il-input-file-fileinfo-title"><span data-dz-name></span></div>				<div data-dz-size class="il-input-file-fileinfo-size"></div>				<div class="il-input-file-fileinfo-close">					<button type="button" class="close" data-dz-remove>						<span aria-hidden="true">&times;</span>						<span class="sr-only">Close</span>					</button>				</div>			</div>			<!--			<div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>-->			<!--			<div class="dz-success-mark"><span>✔</span></div>-->			<!--			<div class="dz-error-mark"><span>✘</span></div>-->			<div class="dz-error-message il-input-file-error"><span data-dz-errormessage></span></div>		</div>	</div></div><input class="input-template" type="hidden" name="name_0[]" value="" data-file-id=""></div>					</div></div>';

        $this->assertEquals($expected, $html);
    }

    //
    //
    //

    protected function buildButtonFactory()
    {
        return new ILIAS\UI\Implementation\Component\Button\Factory;
    }


    public function getUIFactory()
    {
        return new WithSomeButtonNoUIFactory($this->buildButtonFactory());
    }


    public function getDefaultRenderer(JavaScriptBinding $js_binding = null)
    {
        $ui_factory = $this->getUIFactory();
        $tpl_factory = $this->getTemplateFactory();
        $resource_registry = $this->getResourceRegistry();
        $lng = $this->getLanguage();
        if (!$js_binding) {
            $js_binding = $this->getJavaScriptBinding();
        }

        $refinery = $this->getRefinery();

        $component_renderer_loader
            = new LoaderCachingWrapper(
                new LoaderResourceRegistryWrapper(
                    $resource_registry,
                    new FSLoader(
                        new DefaultRendererFactory(
                            $ui_factory,
                            $tpl_factory,
                            $lng,
                            $js_binding,
                            $refinery
                        ),
                        new GlyphRendererFactory(
                            $ui_factory,
                            $tpl_factory,
                            $lng,
                            $js_binding,
                            $refinery
                        ),
                        new FieldRendererFactory(
                            $ui_factory,
                            $tpl_factory,
                            $lng,
                            $js_binding,
                            $refinery
                        )
                    )
                )
            );

        return new TestDefaultRenderer($component_renderer_loader);
    }
}
