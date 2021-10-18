<?php declare(strict_types=1);

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");
require_once(__DIR__ . "/InputTest.php");

use ILIAS\Data;
use ILIAS\UI\Component\Input\Field;
use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Implementation\Component\Input\Field\FieldRendererFactory;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\Implementation\Component\Symbol\Glyph\GlyphRendererFactory;
use ILIAS\UI\Implementation\Render\DefaultRendererFactory;
use ILIAS\UI\Implementation\Render\FSLoader;
use ILIAS\UI\Implementation\Render\JavaScriptBinding;
use ILIAS\UI\Implementation\Render\LoaderCachingWrapper;
use ILIAS\UI\Implementation\Render\LoaderResourceRegistryWrapper;
use ILIAS\UI\Component\Button\Factory;

class WithSomeButtonNoUIFactory extends NoUIFactory
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

class FileInputTest extends ILIAS_UI_TestBase
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


    public function test_implements_factory_interface() : void
    {
        $f = $this->buildFactory();

        $text = $f->file($this->getUploadHandler(), "label", "byline");

        $this->assertInstanceOf(Field\Input::class, $text);
        $this->assertInstanceOf(Field\File::class, $text);
    }


    public function test_render() : void
    {
        $f = $this->buildFactory();
        $label = "label";
        $byline = "byline";
        $text = $f->file($this->getUploadHandler(), $label, $byline)->withNameFrom($this->name_source);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($text));

        $expected = $this->brutallyTrimHTML('
        <div class="form-group row">
           <label for="id_1" class="control-label col-sm-3">label</label>	
           <div class="col-sm-9">
              <div class="il-input-file" id="id_1">
                 <div class="il-input-file-dropzone">	<button class="btn btn-link" data-action="#" id="id_2">select_files_from_computer</button></div>
                 <div class="il-input-file-filelist">
                    <div class="il-input-file-template dz-preview dz-file-preview" style="display: block; border: 1px solid red;" data-file-id="">
                       <div class="dz-details">
                          <div class="il-input-file-fileinfo">
                             <div class="il-input-file-fileinfo-title"><span data-dz-name></span></div>
                             <div data-dz-size class="il-input-file-fileinfo-size"></div>
                             <div class="il-input-file-fileinfo-close">					<button type="button" class="close" data-dz-remove>						<span aria-hidden="true">&times;</span>						<span class="sr-only">Close</span>					</button>				</div>
                          </div>
                          <!--			<div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>-->			<!--			<div class="dz-success-mark"><span>✔</span></div>-->			<!--			<div class="dz-error-mark"><span>✘</span></div>-->			
                          <div class="dz-error-message il-input-file-error"><span data-dz-errormessage></span></div>
                       </div>
                    </div>
                 </div>
                 <input class="input-template" type="hidden" name="name_0[]" value="" data-file-id="" />
              </div>
              <div class="help-block">byline</div>
           </div>
        </div>
        ');
        $this->assertEquals($expected, $html);
    }


    public function test_render_error() : void
    {
        $f = $this->buildFactory();
        $label = "label";
        $byline = "byline";
        $error = "an_error";
        $text = $f->file($this->getUploadHandler(), $label, $byline)->withNameFrom($this->name_source)->withError($error);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($text));

        $expected = $this->brutallyTrimHTML('
        <div class="form-group row">
            <label for="id_1" class="control-label col-sm-3">label</label>
            <div class="col-sm-9">
                <div class="help-block alert alert-danger" role="alert">an_error</div>
                <div class="il-input-file" id="id_1">
                    <div class="il-input-file-dropzone"><button class="btn btn-link" data-action="#" id="id_2">select_files_from_computer</button></div>
                    <div class="il-input-file-filelist">
                        <div class="il-input-file-template dz-preview dz-file-preview" style="display: block; border: 1px solid red;" data-file-id="">
                            <div class="dz-details">
                                <div class="il-input-file-fileinfo">
                                    <div class="il-input-file-fileinfo-title"><span data-dz-name></span></div>
                                    <div data-dz-size class="il-input-file-fileinfo-size"></div>
                                    <div class="il-input-file-fileinfo-close">
                                        <button type="button" class="close" data-dz-remove><span aria-hidden="true">&times;</span> <span class="sr-only">Close</span></button>
                                    </div>
                                </div>
                                <!--			<div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>-->
                                <!--			<div class="dz-success-mark"><span>✔</span></div>-->
                                <!--			<div class="dz-error-mark"><span>✘</span></div>-->
                                <div class="dz-error-message il-input-file-error"><span data-dz-errormessage></span></div>
                            </div>
                        </div>
                    </div>
                    <input class="input-template" type="hidden" name="name_0[]" value="" data-file-id="" />
                </div>
                <div class="help-block">byline</div>
            </div>
        </div>
        ');
        $this->assertEquals($expected, $html);
    }


    public function test_render_no_byline() : void
    {
        $f = $this->buildFactory();
        $label = "label";
        $text = $f->file($this->getUploadHandler(), $label)->withNameFrom($this->name_source);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($text));

        $expected = $this->brutallyTrimHTML('
        <div class="form-group row">
            <label for="id_1" class="control-label col-sm-3">label</label>
            <div class="col-sm-9">
                <div class="il-input-file" id="id_1">
                    <div class="il-input-file-dropzone"><button class="btn btn-link" data-action="#" id="id_2">select_files_from_computer</button></div>
                    <div class="il-input-file-filelist">
                        <div class="il-input-file-template dz-preview dz-file-preview" style="display: block; border: 1px solid red;" data-file-id="">
                            <div class="dz-details">
                                <div class="il-input-file-fileinfo">
                                    <div class="il-input-file-fileinfo-title"><span data-dz-name></span></div>
                                    <div data-dz-size class="il-input-file-fileinfo-size"></div>
                                    <div class="il-input-file-fileinfo-close">
                                        <button type="button" class="close" data-dz-remove><span aria-hidden="true">&times;</span> <span class="sr-only">Close</span></button>
                                    </div>
                                </div>
                                <!--			<div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>-->
                                <!--			<div class="dz-success-mark"><span>✔</span></div>-->
                                <!--			<div class="dz-error-mark"><span>✘</span></div>-->
                                <div class="dz-error-message il-input-file-error"><span data-dz-errormessage></span></div>
                            </div>
                        </div>
                    </div>
                    <input class="input-template" type="hidden" name="name_0[]" value="" data-file-id="" />
                </div>
            </div>
        </div>
        ');
        $this->assertEquals($expected, $html);
    }


    public function test_render_value() : void
    {
        $f = $this->buildFactory();
        $label = "label";
        $value = ["value"];
        $text = $f->file($this->getUploadHandler(), $label)->withValue($value)->withNameFrom($this->name_source);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($text));

        $expected = $this->brutallyTrimHTML('
        <div class="form-group row">
            <label for="id_1" class="control-label col-sm-3">label</label>
            <div class="col-sm-9">
                <div class="il-input-file" id="id_1">
                    <div class="il-input-file-dropzone"><button class="btn btn-link" data-action="#" id="id_2">select_files_from_computer</button></div>
                    <div class="il-input-file-filelist">
                        <div class="il-input-file-template dz-preview dz-file-preview" style="display: block; border: 1px solid red;" data-file-id="">
                            <div class="dz-details">
                                <div class="il-input-file-fileinfo">
                                    <div class="il-input-file-fileinfo-title"><span data-dz-name></span></div>
                                    <div data-dz-size class="il-input-file-fileinfo-size"></div>
                                    <div class="il-input-file-fileinfo-close">
                                        <button type="button" class="close" data-dz-remove><span aria-hidden="true">&times;</span> <span class="sr-only">Close</span></button>
                                    </div>
                                </div>
                                <!--			<div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>-->
                                <!--			<div class="dz-success-mark"><span>✔</span></div>-->
                                <!--			<div class="dz-error-mark"><span>✘</span></div>-->
                                <div class="dz-error-message il-input-file-error"><span data-dz-errormessage></span></div>
                            </div>
                        </div>
                    </div>
                    <input class="input-template" type="hidden" name="name_0[]" value="" data-file-id="" />
                </div>
            </div>
        </div>
        ');
        $this->assertEquals($expected, $html);
    }


    public function test_render_required() : void
    {
        $f = $this->buildFactory();
        $label = "label";
        $text = $f->file($this->getUploadHandler(), $label)->withNameFrom($this->name_source)->withRequired(true);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($text));

        $expected = $this->brutallyTrimHTML('
        <div class="form-group row">
            <label for="id_1" class="control-label col-sm-3">label<span class="asterisk">*</span></label>
            <div class="col-sm-9">
                <div class="il-input-file" id="id_1">
                    <div class="il-input-file-dropzone"><button class="btn btn-link" data-action="#" id="id_2">select_files_from_computer</button></div>
                    <div class="il-input-file-filelist">
                        <div class="il-input-file-template dz-preview dz-file-preview" style="display: block; border: 1px solid red;" data-file-id="">
                            <div class="dz-details">
                                <div class="il-input-file-fileinfo">
                                    <div class="il-input-file-fileinfo-title"><span data-dz-name></span></div>
                                    <div data-dz-size class="il-input-file-fileinfo-size"></div>
                                    <div class="il-input-file-fileinfo-close">
                                        <button type="button" class="close" data-dz-remove><span aria-hidden="true">&times;</span> <span class="sr-only">Close</span></button>
                                    </div>
                                </div>
                                <!--			<div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>-->
                                <!--			<div class="dz-success-mark"><span>✔</span></div>-->
                                <!--			<div class="dz-error-mark"><span>✘</span></div>-->
                                <div class="dz-error-message il-input-file-error"><span data-dz-errormessage></span></div>
                            </div>
                        </div>
                    </div>
                    <input class="input-template" type="hidden" name="name_0[]" value="" data-file-id="" />
                </div>
            </div>
        </div>
        ');
        $this->assertEquals($expected, $html);
    }


    public function test_render_disabled() : void
    {
        $f = $this->buildFactory();
        $label = "label";
        $text = $f->file($this->getUploadHandler(), $label)->withNameFrom($this->name_source)->withDisabled(true);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($text));

        $expected = $this->brutallyTrimHTML('
        <div class="form-group row">
            <label for="id_1" class="control-label col-sm-3">label</label>
            <div class="col-sm-9">
                <div class="il-input-file" id="id_1">
                    <div class="il-input-file-dropzone"><button class="btn btn-link" data-action="#" id="id_2">select_files_from_computer</button></div>
                    <div class="il-input-file-filelist">
                        <div class="il-input-file-template dz-preview dz-file-preview" style="display: block; border: 1px solid red;" data-file-id="">
                            <div class="dz-details">
                                <div class="il-input-file-fileinfo">
                                    <div class="il-input-file-fileinfo-title"><span data-dz-name></span></div>
                                    <div data-dz-size class="il-input-file-fileinfo-size"></div>
                                    <div class="il-input-file-fileinfo-close">
                                        <button type="button" class="close" data-dz-remove><span aria-hidden="true">&times;</span> <span class="sr-only">Close</span></button>
                                    </div>
                                </div>
                                <!--			<div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>-->
                                <!--			<div class="dz-success-mark"><span>✔</span></div>-->
                                <!--			<div class="dz-error-mark"><span>✘</span></div>-->
                                <div class="dz-error-message il-input-file-error"><span data-dz-errormessage></span></div>
                            </div>
                        </div>
                    </div>
                    <input class="input-template" type="hidden" name="name_0[]" value="" data-file-id="" />
                </div>
            </div>
        </div>
        ');

        $this->assertEquals($expected, $html);
    }

    protected function buildButtonFactory() : I\Button\Factory
    {
        return new I\Button\Factory;
    }


    public function getUIFactory() : WithSomeButtonNoUIFactory
    {
        return new WithSomeButtonNoUIFactory($this->buildButtonFactory());
    }

    public function getDefaultRenderer(
        JavaScriptBinding $js_binding = null,
        array $with_stub_renderings = []
    ) : TestDefaultRenderer {
        $ui_factory = $this->getUIFactory();
        $tpl_factory = $this->getTemplateFactory();
        $resource_registry = $this->getResourceRegistry();
        $lng = $this->getLanguage();
        if (!$js_binding) {
            $js_binding = $this->getJavaScriptBinding();
        }

        $refinery = $this->getRefinery();
        $img_resolver = new ilImagePathResolver();

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
                            $refinery,
                            $img_resolver
                        ),
                        new GlyphRendererFactory(
                            $ui_factory,
                            $tpl_factory,
                            $lng,
                            $js_binding,
                            $refinery,
                            $img_resolver
                        ),
                        new FieldRendererFactory(
                            $ui_factory,
                            $tpl_factory,
                            $lng,
                            $js_binding,
                            $refinery,
                            $img_resolver
                        )
                    )
                )
            );

        return new TestDefaultRenderer($component_renderer_loader);
    }
}
