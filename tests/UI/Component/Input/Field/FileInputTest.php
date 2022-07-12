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

use ILIAS\Data;
use ILIAS\UI\Component\Input\Field;
use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Implementation\Component\Input\Field\FieldRendererFactory;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\Implementation\Component\Symbol\Glyph\GlyphRendererFactory;
use ILIAS\UI\Implementation\Component\Symbol\Icon\IconRendererFactory;
use ILIAS\UI\Implementation\Render\DefaultRendererFactory;
use ILIAS\UI\Implementation\Render\FSLoader;
use ILIAS\UI\Implementation\Render\JavaScriptBinding;
use ILIAS\UI\Implementation\Render\LoaderCachingWrapper;
use ILIAS\UI\Implementation\Render\LoaderResourceRegistryWrapper;
use ILIAS\UI\Component\Button\Factory as ButtonFactory;
use ILIAS\UI\Component\Symbol\Factory as SymbolFactory;

class WithButtonAndSymbolButNoUIFactory extends NoUIFactory
{
    protected ButtonFactory $button_factory;
    protected SymbolFactory $symbol_factory;

    public function __construct(ButtonFactory $button_factory, SymbolFactory $symbol_factory)
    {
        $this->button_factory = $button_factory;
        $this->symbol_factory = $symbol_factory;
    }

    public function button() : ButtonFactory
    {
        return $this->button_factory;
    }

    public function symbol() : SymbolFactory
    {
        return $this->symbol_factory;
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

            public function getInfoResult(string $identifier) : ?\ILIAS\FileUpload\Handler\FileInfoResult
            {
                return null;
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
                <label for="id_3" class="control-label col-sm-4 col-md-3 col-lg-2">label</label>
                <div class="col-sm-8 col-md-9 col-lg-10">
                    <div id="id_3" class="ui-input-file">
                        <div class="ui-input-file-input-list ui-input-dynamic-inputs-list"></div>
                        <div class="ui-input-file-input-dropzone">
                            <button class="btn btn-link" data-action="#" id="id_2">select_files_from_computer</button>
                            <span class="ui-input-file-input-error-msg" data-dz-error-msg></span>
                        </div>
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
                <label for="id_3" class="control-label col-sm-4 col-md-3 col-lg-2">label</label>
                <div class="col-sm-8 col-md-9 col-lg-10">
                    <div class="help-block alert alert-danger" role="alert">an_error</div>
                    <div id="id_3" class="ui-input-file">
                        <div class="ui-input-file-input-list ui-input-dynamic-inputs-list"></div>
                        <div class="ui-input-file-input-dropzone">
                            <button class="btn btn-link" data-action="#" id="id_2">select_files_from_computer</button>
                            <span class="ui-input-file-input-error-msg" data-dz-error-msg></span>
                        </div>
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
                <label for="id_3" class="control-label col-sm-4 col-md-3 col-lg-2">label</label>
                <div class="col-sm-8 col-md-9 col-lg-10">
                    <div id="id_3" class="ui-input-file">
                        <div class="ui-input-file-input-list ui-input-dynamic-inputs-list"></div>
                        <div class="ui-input-file-input-dropzone">
                            <button class="btn btn-link" data-action="#" id="id_2">select_files_from_computer</button>
                            <span class="ui-input-file-input-error-msg" data-dz-error-msg></span>
                        </div>
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
                <label for="id_4" class="control-label col-sm-4 col-md-3 col-lg-2">label</label>
                <div class="col-sm-8 col-md-9 col-lg-10">
                    <div id="id_4" class="ui-input-file">
                        <div class="ui-input-file-input-list ui-input-dynamic-inputs-list">
                            <div class="ui-input-file-input ui-input-dynamic-input">
                                <div class="ui-input-file-info">
                                    <span data-dz-name></span>
                                    <span data-dz-size></span>
                                    <a class="glyph" aria-label="close"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></a>
                                    <span class="ui-input-file-input-error-msg" data-dz-error-msg></span>
                                </div>
                                <div class="ui-input-file-metadata" style="display: none;">
                                    <input id="id_1" type="hidden" name="name_0[form_input_0][]" value="value" />
                                </div>
                            </div>
                        </div>
                        <div class="ui-input-file-input-dropzone">
                            <button class="btn btn-link" data-action="#" id="id_3">select_files_from_computer</button>
                            <span class="ui-input-file-input-error-msg" data-dz-error-msg></span>
                        </div>
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
                <label for="id_3" class="control-label col-sm-4 col-md-3 col-lg-2">label<span class="asterisk">*</span></label>
                <div class="col-sm-8 col-md-9 col-lg-10">
                    <div id="id_3" class="ui-input-file">
                        <div class="ui-input-file-input-list ui-input-dynamic-inputs-list"></div>
                        <div class="ui-input-file-input-dropzone">
                            <button class="btn btn-link" data-action="#" id="id_2">select_files_from_computer</button>
                            <span class="ui-input-file-input-error-msg" data-dz-error-msg></span>
                        </div>
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
            <div class="form-group row"><label for="id_3" class="control-label col-sm-4 col-md-3 col-lg-2">label</label>
                <div class="col-sm-8 col-md-9 col-lg-10">
                    <div id="id_3" class="ui-input-file">
                        <div class="ui-input-file-input-list ui-input-dynamic-inputs-list"></div>
                        <div class="ui-input-file-input-dropzone">
                            <button class="btn btn-link" data-action="#" id="id_2">select_files_from_computer</button>
                            <span class="ui-input-file-input-error-msg" data-dz-error-msg></span>
                        </div>
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

    protected function buildSymbolFactory() : I\Symbol\Factory
    {
        return new I\Symbol\Factory(
            new I\Symbol\Icon\Factory(),
            new I\Symbol\Glyph\Factory(),
            new I\Symbol\Avatar\Factory()
        );
    }

    public function getUIFactory() : WithButtonAndSymbolButNoUIFactory
    {
        return new WithButtonAndSymbolButNoUIFactory(
            $this->buildButtonFactory(),
            $this->buildSymbolFactory()
        );
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
                        new IconRendererFactory(
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
