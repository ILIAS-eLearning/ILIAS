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

require_once(__DIR__ . "/../../../../../../../vendor/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");
require_once(__DIR__ . "/InputTest.php");
require_once(__DIR__ . "/CommonFieldRendering.php");

use ILIAS\Data;
use ILIAS\UI\Component\Input\Field;
use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\Implementation\Component\Button\Factory as ButtonFactory;
use ILIAS\UI\Implementation\Component\Symbol\Factory as SymbolFactory;
use ILIAS\FileUpload\Handler\FileInfoResult;
use ILIAS\UI\Component\Symbol\Glyph\Glyph;

class WithButtonAndSymbolButNoUIFactory extends NoUIFactory
{
    protected ButtonFactory $button_factory;
    protected SymbolFactory $symbol_factory;

    public function __construct(ButtonFactory $button_factory, SymbolFactory $symbol_factory)
    {
        $this->button_factory = $button_factory;
        $this->symbol_factory = $symbol_factory;
    }

    public function button(): ButtonFactory
    {
        return $this->button_factory;
    }

    public function symbol(): SymbolFactory
    {
        return $this->symbol_factory;
    }
}

class FileInputTest extends ILIAS_UI_TestBase
{
    use CommonFieldRendering;

    protected DefNamesource $name_source;

    public function setUp(): void
    {
        $this->name_source = new DefNamesource();
    }

    protected function brutallyTrimHTML(string $html): string
    {
        $html = str_replace(" />", "/>", $html);
        return parent::brutallyTrimHTML($html);
    }

    private function getUploadHandler(?FileInfoResult $file = null): Field\UploadHandler
    {
        return new class ($file) implements Field\UploadHandler {
            protected ?FileInfoResult $file;

            public function __construct(?FileInfoResult $file)
            {
                $this->file = $file;
            }

            public function getFileIdentifierParameterName(): string
            {
                return 'file_id';
            }

            public function getUploadURL(): string
            {
                return 'uploadurl';
            }

            public function getFileRemovalURL(): string
            {
                return 'removalurl';
            }

            /**
             * @inheritDoc
             */
            public function getExistingFileInfoURL(): string
            {
                return 'infourl';
            }

            /**
             * @inheritDoc
             */
            public function getInfoForExistingFiles(array $file_ids): array
            {
                return [];
            }

            public function getInfoResult(string $identifier): ?FileInfoResult
            {
                if (null !== $this->file && $identifier === $this->file->getFileIdentifier()) {
                    return $this->file;
                }

                return null;
            }

            public function supportsChunkedUploads(): bool
            {
                return false;
            }
        };
    }


    public function testImplementsFactoryInterface(): void
    {
        $f = $this->getFieldFactory();

        $text = $f->file($this->getUploadHandler(), "label", "byline");

        $this->assertInstanceOf(\ILIAS\UI\Component\Input\Container\Form\FormInput::class, $text);
        $this->assertInstanceOf(Field\File::class, $text);
    }


    public function testRender(): void
    {
        $f = $this->getFieldFactory();
        $label = "label";
        $byline = "byline";
        $file_input = $f->file($this->getUploadHandler(), $label, $byline)->withNameFrom($this->name_source);

        $expected = $this->getFormWrappedHtml(
            'file-field-input',
            $label,
            '
            <div class="ui-input-file">
                <div class="ui-input-file-input-list">
                    <template>
                        <div class="ui-input-file-input">
                            <div class="ui-input-file-info"><span data-action="expand"></span><span
                                    data-action="collapse"></span><span data-dz-name></span><span data-dz-size></span><span
                                    data-action="remove"><a tabindex="0" class="glyph" href="#" aria-label="close"><span
                                            class="glyphicon glyphicon-remove" aria-hidden="true"></span></a></span><span
                                    class="ui-input-file-input-error-msg" data-dz-error-msg></span></div>
                            <div class="ui-input-file-metadata" style="display: none;"><input id="id_1" type="hidden"
                                    name="name_0[input_0][]" value="" /></div>
                            <div class="ui-input-file-input-progress-container">
                                <div class="ui-input-file-input-progress-indicator"></div>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="ui-input-file-input-dropzone">
                    <button class="btn btn-link" data-action="#" id="id_2">select_files_from_computer</button>
                    <span class="ui-input-file-input-error-msg" data-dz-error-msg></span>
                </div>
                <div class="help-block"> file_notice 0 B | ui_file_upload_max_nr 1</div>
            </div>
            ',
            $byline,
            null,
            'id_3'
        );
        $this->assertEquals($expected, $this->render($file_input));
    }

    public function testCommonRendering(): void
    {
        $f = $this->getFieldFactory();
        $file_input = $f->file($this->getUploadHandler(), 'label', null)
            ->withNameFrom($this->name_source);
        $this->testWithError($file_input);
        $this->testWithNoByline($file_input);
        $this->testWithRequired($file_input);
        $this->testWithDisabled($file_input);
        $this->testWithAdditionalOnloadCodeRendersId($file_input);
    }

    public function testRenderValue(): void
    {
        $test_file_id = "test_file_id_1";
        $test_file_name = "test file name 1";

        $test_file_info = $this->createMock(FileInfoResult::class);
        $test_file_info->method('getFileIdentifier')->willReturn("test_file_id_1");
        $test_file_info->method('getName')->willReturn("test file name 1");
        $test_file_info->method('getSize')->willReturn(1001);

        $file_input = $this->getFieldFactory()->file(
            $this->getUploadHandler($test_file_info),
            "",
        )->withValue([
            $test_file_id,
        ])->withNameFrom($this->name_source);

        $expected = $this->getFormWrappedHtml(
            'file-field-input',
            '',
            '
            <div class="ui-input-file">
                <div class="ui-input-file-input-list">
                    <div class="ui-input-file-input">
                        <div class="ui-input-file-info">
                            <span data-action="expand"></span>
                            <span data-action="collapse"></span>
                            <span data-dz-name>test file name 1</span>
                            <span data-dz-size>1 KB</span>
                            <span data-action="remove">
                                <a tabindex="0" class="glyph" href="#" aria-label="close">
                                    <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
                                </a>
                            </span>
                            <span class="ui-input-file-input-error-msg" data-dz-error-msg></span>
                        </div>
                        <div class="ui-input-file-metadata" style="display: none;">
                            <input id="id_1" type="hidden" name="name_0[input_0][]" value="test_file_id_1"/>
                        </div>
                        <div class="ui-input-file-input-progress-container">
                            <div class="ui-input-file-input-progress-indicator"></div>
                        </div>
                    </div>
                    <template>
                        <div class="ui-input-file-input">
                            <div class="ui-input-file-info"><span data-action="expand"></span><span
                                    data-action="collapse"></span><span data-dz-name></span><span data-dz-size></span><span
                                    data-action="remove"><a tabindex="0" class="glyph" href="#" aria-label="close"><span
                                            class="glyphicon glyphicon-remove" aria-hidden="true"></span></a></span><span
                                    class="ui-input-file-input-error-msg" data-dz-error-msg></span></div>
                            <div class="ui-input-file-metadata" style="display: none;"><input id="id_2" type="hidden"
                                    name="name_0[input_0][]" value="" /></div>
                            <div class="ui-input-file-input-progress-container">
                                <div class="ui-input-file-input-progress-indicator"></div>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="ui-input-file-input-dropzone">
                    <button class="btn btn-link" data-action="#" id="id_3">select_files_from_computer</button>
                    <span class="ui-input-file-input-error-msg" data-dz-error-msg></span>
                </div>
                <div class="help-block"> file_notice 0 B | ui_file_upload_max_nr 1</div>
            </div>
            ',
            null,
            null,
            'id_4'
        );
        $this->assertEquals($expected, $this->render($file_input));
    }


    public function testRenderWithMetadata(): void
    {
        $factory = $this->getFieldFactory();
        $label = 'file_input';
        $metadata_input = $factory->text("text_input");
        $file_input = $factory->file(
            ($u = $this->getUploadHandler()),
            $label,
            null,
            $metadata_input
        )->withValue([
            [
                $u->getFileIdentifierParameterName() => "file_id",
                ""
            ]
        ])->withNameFrom($this->name_source);

        $expected = $this->getFormWrappedHtml(
            'file-field-input',
            $label,
            '
            <div class="ui-input-file">
                <div class="ui-input-file-input-list">
                    <div class="ui-input-file-input">
                        <div class="ui-input-file-info">
                            <span data-action="expand">
                                <a tabindex="0" class="glyph" href="#" aria-label="expand_content">
                                    <span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span>
                                </a>
                            </span>
                            <span data-action="collapse">
                                <a tabindex="0" class="glyph" href="#" aria-label="collapse_content">
                                    <span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span>
                                </a>
                            </span>
                            <span data-dz-name></span>
                            <span data-dz-size></span>
                            <span data-action="remove">
                                <a tabindex="0" class="glyph" href="#" aria-label="close">
                                    <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
                                </a>
                            </span>
                            <span class="ui-input-file-input-error-msg" data-dz-error-msg></span>
                        </div>
                        <div class="ui-input-file-metadata" style="display: none;">
                            <fieldset class="c-input" data-il-ui-component="text-field-input" data-il-ui-input-name="name_0[input_1][]">
                                <label for="id_1">text_input</label>
                                <div class="c-input__field">
                                    <input id="id_1" type="text" name="name_0[input_1][]" class="c-field-text"/>
                                </div>
                                <div class="c-input__value_representation"></div>
                            </fieldset>
                            <input id="id_2" type="hidden" name="name_0[input_2][]" value="file_id"/>
                        </div>
                        <div class="ui-input-file-input-progress-container">
                            <div class="ui-input-file-input-progress-indicator"></div>
                        </div>
                    </div>
                    <template>
                        <div class="ui-input-file-input">
                            <div class="ui-input-file-info"><span data-action="expand"><a tabindex="0" class="glyph"
                                        href="#" aria-label="expand_content"><span
                                            class="glyphicon glyphicon-triangle-right"
                                            aria-hidden="true"></span></a></span><span data-action="collapse"><a
                                        tabindex="0" class="glyph" href="#" aria-label="collapse_content"><span
                                            class="glyphicon glyphicon-triangle-bottom"
                                            aria-hidden="true"></span></a></span><span data-dz-name></span><span
                                    data-dz-size></span><span data-action="remove"><a tabindex="0" class="glyph" href="#"
                                        aria-label="close"><span class="glyphicon glyphicon-remove"
                                            aria-hidden="true"></span></a></span><span class="ui-input-file-input-error-msg"
                                    data-dz-error-msg></span></div>
                            <div class="ui-input-file-metadata" style="display: none;">
                                <fieldset class="c-input" data-il-ui-component="text-field-input"
                                    data-il-ui-input-name="name_0[input_1][]"><label for="id_3">text_input</label>
                                    <div class="c-input__field"><input id="id_3" type="text" name="name_0[input_1][]"
                                            class="c-field-text" /></div>
                                    <div class="c-input__value_representation"></div>
                                </fieldset><input id="id_4" type="hidden" name="name_0[input_2][]" value="" />
                            </div>
                            <div class="ui-input-file-input-progress-container">
                                <div class="ui-input-file-input-progress-indicator"></div>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="ui-input-file-input-dropzone">
                    <button class="btn btn-link" data-action="#" id="id_5">select_files_from_computer</button>
                    <span class="ui-input-file-input-error-msg" data-dz-error-msg></span>
                </div>
                <div class="help-block"> file_notice 0 B | ui_file_upload_max_nr 1</div>
            </div>
            ',
            null,
            null,
            'id_6',
        );
        $this->assertEquals($expected, $this->render($file_input));
    }


    public function testRenderWithMetadataValue(): void
    {
        $test_file_id = "test_file_id_1";
        $test_file_name = "test file name 1";

        $test_file_info = $this->createMock(FileInfoResult::class);
        $test_file_info->method('getFileIdentifier')->willReturn("test_file_id_1");
        $test_file_info->method('getName')->willReturn("test file name 1");
        $test_file_info->method('getSize')->willReturn(1000 * 1000 + 1);

        $factory = $this->getFieldFactory();
        $label = 'file_input';
        $metadata_input = $factory->text("text_input");
        $file_input = $factory->file(
            $u = $this->getUploadHandler($test_file_info),
            $label,
            null,
            $metadata_input
        )->withValue([
            [
                $u->getFileIdentifierParameterName() => $test_file_id,
                "test",
            ]
        ])->withNameFrom($this->name_source);


        $expected = $this->getFormWrappedHtml(
            'file-field-input',
            $label,
            '
            <div class="ui-input-file">
                <div class="ui-input-file-input-list">
                    <div class="ui-input-file-input">
                        <div class="ui-input-file-info">
                            <span data-action="expand">
                                <a tabindex="0" class="glyph" href="#" aria-label="expand_content">
                                    <span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span>
                                </a>
                            </span>
                            <span data-action="collapse">
                                <a tabindex="0" class="glyph" href="#" aria-label="collapse_content">
                                    <span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span>
                                </a>
                            </span>
                            <span data-dz-name>test file name 1</span>
                            <span data-dz-size>1 MB</span>
                            <span data-action="remove">
                                <a tabindex="0" class="glyph" href="#" aria-label="close">
                                    <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
                                </a>
                            </span>
                            <span class="ui-input-file-input-error-msg" data-dz-error-msg></span>
                        </div>
                        <div class="ui-input-file-metadata" style="display: none;">
                            <fieldset class="c-input" data-il-ui-component="text-field-input" data-il-ui-input-name="name_0[input_1][]">
                                <label for="id_1">text_input</label>
                                <div class="c-input__field">
                                    <input id="id_1" type="text" value="test" name="name_0[input_1][]" class="c-field-text"/>
                                </div>
                                <div class="c-input__value_representation"></div>
                            </fieldset>
                            <input id="id_2" type="hidden" name="name_0[input_2][]" value="test_file_id_1"/>
                        </div>
                        <div class="ui-input-file-input-progress-container">
                            <div class="ui-input-file-input-progress-indicator"></div>
                        </div>
                    </div>
                    <template>
                        <div class="ui-input-file-input">
                            <div class="ui-input-file-info">
                            <span data-action="expand">
                                <a tabindex="0" class="glyph" href="#" aria-label="expand_content">
                                    <span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span>
                                </a>
                            </span>
                            <span data-action="collapse">
                                <a tabindex="0" class="glyph" href="#" aria-label="collapse_content">
                                    <span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span>
                                </a>
                            </span>
                            <span data-dz-name></span>
                            <span data-dz-size></span>
                            <span
                                    data-action="remove"><a tabindex="0" class="glyph" href="#" aria-label="close"><span
                                            class="glyphicon glyphicon-remove" aria-hidden="true"></span></a></span><span
                                    class="ui-input-file-input-error-msg" data-dz-error-msg></span></div>
                            <div class="ui-input-file-metadata" style="display: none;">
                                <fieldset class="c-input" data-il-ui-component="text-field-input"
                                data-il-ui-input-name="name_0[input_1][]"><label for="id_3">text_input</label>
                                <div class="c-input__field"><input id="id_3" type="text" name="name_0[input_1][]"
                                        class="c-field-text" /></div>
                                        <div class="c-input__value_representation"></div>
                                </fieldset><input id="id_4" type="hidden" name="name_0[input_2][]" value="" />
                            </div>
                            <div class="ui-input-file-input-progress-container">
                                <div class="ui-input-file-input-progress-indicator"></div>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="ui-input-file-input-dropzone">
                    <button class="btn btn-link" data-action="#" id="id_5">select_files_from_computer</button>
                    <span class="ui-input-file-input-error-msg" data-dz-error-msg></span>
                </div>
                <div class="help-block"> file_notice 0 B | ui_file_upload_max_nr 1</div>
            </div>
            ',
            null,
            null,
            'id_6'
        );
        $this->assertEquals($expected, $this->render($file_input));
    }

    protected function buildButtonFactory(): I\Button\Factory
    {
        return new I\Button\Factory();
    }

    protected function buildSymbolFactory(): I\Symbol\Factory
    {
        return new I\Symbol\Factory(
            new I\Symbol\Icon\Factory(),
            new I\Symbol\Glyph\Factory(),
            new I\Symbol\Avatar\Factory()
        );
    }

    public function getUIFactory(): WithButtonAndSymbolButNoUIFactory
    {
        return new WithButtonAndSymbolButNoUIFactory(
            $this->buildButtonFactory(),
            $this->buildSymbolFactory()
        );
    }
}
