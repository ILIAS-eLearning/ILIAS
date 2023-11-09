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

use ILIAS\UI\Component\Input\Field\MarkdownRenderer;
use ILIAS\UI\Implementation\Component\Input\UploadLimitResolver;
use ILIAS\UI\Implementation\Component\Symbol\Avatar\Factory as AvatarFactory;
use ILIAS\UI\Implementation\Component\Symbol\Glyph\Factory as GlyphFactory;
use ILIAS\UI\Implementation\Component\ViewControl\Factory as ViewControlFactory;
use ILIAS\UI\Implementation\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Implementation\Component\Symbol\Icon\Factory as IconFactory;
use ILIAS\UI\Implementation\Component\ViewControl\Mode as ViewControlMode;
use ILIAS\UI\Implementation\Component\Button\Factory as ButtonFactory;
use ILIAS\UI\Implementation\Component\Symbol\Factory as SymbolFactory;
use ILIAS\UI\Implementation\Component\Symbol\Glyph\Glyph;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Implementation\Render\JavaScriptBinding;

require_once(__DIR__ . "/../../../../../../../vendor/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");
require_once(__DIR__ . "/InputTest.php");

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class MarkdownTest extends ILIAS_UI_TestBase
{
    protected const TEST_ASYNC_URL = 'https://localhost';
    protected const TEST_PARAMETER_NAME = 'preview';

    protected MarkdownRenderer $markdown_renderer;
    protected DefNamesource $name_source;
    protected FieldFactory $factory;

    protected ViewControlMode $view_control_mock;
    protected Glyph $numberedlist_glyph_mock;
    protected Glyph $bulledpoint_glyph_mock;
    protected Glyph $header_glyph_mock;
    protected Glyph $italic_glyph_mock;
    protected Glyph $link_glyph_mock;
    protected Glyph $bold_glyph_mock;

    public function setUp(): void
    {
        $this->markdown_renderer = $this->getMarkdownRendererMock();
        $this->factory = $this->buildMinimalFieldFactory();
        $this->name_source = new DefNamesource();

        $this->view_control_mock = $this->getViewControlModeStub();
        $this->numberedlist_glyph_mock = $this->getGlyphStub('numberedlist');
        $this->bulledpoint_glyph_mock = $this->getGlyphStub('bulletpoint');
        $this->header_glyph_mock = $this->getGlyphStub('header');
        $this->italic_glyph_mock = $this->getGlyphStub('italic');
        $this->link_glyph_mock = $this->getGlyphStub('link');
        $this->bold_glyph_mock = $this->getGlyphStub('bold');

        parent::setUp();
    }

    /**
     * The rendering of this input requires actual or minimal instances of:
     * - ViewControlFactory
     * - ButtonFactory
     * - SymbolFactory
     *
     * @see ILIAS_UI_TestBase::getDefaultRenderer()
     */
    public function getUIFactory(): NoUIFactory
    {
        return new class (
            $this->getViewControlFactoryMock(),
            $this->buildButtonFactory(),
            $this->getSymbolFactoryMock(),
        ) extends NoUIFactory {
            protected ViewControlFactory $view_control_factory;
            protected ButtonFactory $button_factory;
            protected SymbolFactory $symbol_factory;

            public function __construct(
                ViewControlFactory $view_control_factory,
                ButtonFactory $button_factory,
                SymbolFactory $symbol_factory,
            ) {
                $this->view_control_factory = $view_control_factory;
                $this->button_factory = $button_factory;
                $this->symbol_factory = $symbol_factory;
            }

            public function viewControl(): ViewControlFactory
            {
                return $this->view_control_factory;
            }

            public function button(): ButtonFactory
            {
                return $this->button_factory;
            }

            public function symbol(): SymbolFactory
            {
                return $this->symbol_factory;
            }
        };
    }

    public function testRender(): void
    {
        $label = 'test_label';

        $input = $this->factory->markdown($this->markdown_renderer, $label)->withNameFrom($this->name_source);

        $expected = $this->brutallyTrimHTML(
            "
                <div class=\"form-group row\">
                    <label for=\"id_1\" class=\"control-label col-sm-4 col-md-3 col-lg-2\">$label</label>
                    <div class=\"col-sm-8 col-md-9 col-lg-10\">
                        <div class=\"c-input-markdown\">
                            <div class=\"c-input-markdown__controls\">
                                view_control_mode
                                <div class=\"c-input-markdown__actions\">
                                    <span data-action=\"insert-heading\">
                                        <button class=\"btn btn-default\" data-action=\"#\" id=\"id_2\">header</button>
                                    </span>
                                    <span data-action=\"insert-link\">
                                        <button class=\"btn btn-default\" data-action=\"#\" id=\"id_3\">link</button>
                                    </span>
                                    <span data-action=\"insert-bold\">
                                        <button class=\"btn btn-default\" data-action=\"#\" id=\"id_4\">bold</button>
                                    </span>
                                    <span data-action=\"insert-italic\">
                                        <button class=\"btn btn-default\" data-action=\"#\" id=\"id_5\">italic</button>
                                    </span>
                                    <span data-action=\"insert-bullet-points\">
                                        <button class=\"btn btn-default\" data-action=\"#\" id=\"id_7\">bulletpoint</button>
                                    </span>
                                    <span data-action=\"insert-enumeration\">
                                        <button class=\"btn btn-default\" data-action=\"#\" id=\"id_6\">numberedlist</button>
                                    </span>
                                </div>
                            </div>
                            <div class=\"ui-input-textarea\">
                                <textarea id=\"id_1\" class=\"form-control form-control-sm\" name=\"name_0\"></textarea>
                            </div>
                            <div class=\"c-input-markdown__preview hidden\">
                            </div>
                        </div>
                    </div>
                </div>
            "
        );

        $html = $this->brutallyTrimHTML($this->getRendererWithStubs()->render($input));

        $this->assertEquals($expected, $html);
    }

    public function testRenderWithByline(): void
    {
        $label = 'test_label';
        $byline = 'test_byline';

        $input = $this->factory->markdown(
            $this->markdown_renderer,
            $label,
            $byline
        )->withNameFrom($this->name_source);

        $expected = $this->brutallyTrimHTML(
            "
                <div class=\"form-group row\">
                    <label for=\"id_1\" class=\"control-label col-sm-4 col-md-3 col-lg-2\">$label</label>
                    <div class=\"col-sm-8 col-md-9 col-lg-10\">
                        <div class=\"c-input-markdown\">
                            <div class=\"c-input-markdown__controls\">
                                view_control_mode
                                <div class=\"c-input-markdown__actions\">
                                    <span data-action=\"insert-heading\">
                                        <button class=\"btn btn-default\" data-action=\"#\" id=\"id_2\">header</button>
                                    </span>
                                    <span data-action=\"insert-link\">
                                        <button class=\"btn btn-default\" data-action=\"#\" id=\"id_3\">link</button>
                                    </span>
                                    <span data-action=\"insert-bold\">
                                        <button class=\"btn btn-default\" data-action=\"#\" id=\"id_4\">bold</button>
                                    </span>
                                    <span data-action=\"insert-italic\">
                                        <button class=\"btn btn-default\" data-action=\"#\" id=\"id_5\">italic</button>
                                    </span>
                                    <span data-action=\"insert-bullet-points\">
                                        <button class=\"btn btn-default\" data-action=\"#\" id=\"id_7\">bulletpoint</button>
                                    </span>
                                    <span data-action=\"insert-enumeration\">
                                        <button class=\"btn btn-default\" data-action=\"#\" id=\"id_6\">numberedlist</button>
                                    </span>
                                </div>
                            </div>
                            <div class=\"ui-input-textarea\">
                                <textarea id=\"id_1\" class=\"form-control form-control-sm\" name=\"name_0\"></textarea>
                            </div>
                            <div class=\"c-input-markdown__preview hidden\">
                            </div>
                        </div>
                        <div class=\"help-block\">$byline</div>
                    </div>
                </div>
            "
        );

        $html = $this->brutallyTrimHTML($this->getRendererWithStubs()->render($input));

        $this->assertEquals($expected, $html);
    }

    public function testRenderWithLimits(): void
    {
        $label = 'test_label';
        $byline = 'test_byline';
        $min = 1;
        $max = 9;

        $input = $this->factory->markdown(
            $this->markdown_renderer,
            $label,
            $byline
        )->withMinLimit($min)->withMaxLimit($max)->withNameFrom($this->name_source);

        $expected = $this->brutallyTrimHTML(
            "
                <div class=\"form-group row\">
                    <label for=\"id_1\" class=\"control-label col-sm-4 col-md-3 col-lg-2\">$label</label>
                    <div class=\"col-sm-8 col-md-9 col-lg-10\">
                        <div class=\"c-input-markdown\">
                            <div class=\"c-input-markdown__controls\">
                                view_control_mode
                                <div class=\"c-input-markdown__actions\">
                                    <span data-action=\"insert-heading\">
                                        <button class=\"btn btn-default\" data-action=\"#\" id=\"id_2\">header</button>
                                    </span>
                                    <span data-action=\"insert-link\">
                                        <button class=\"btn btn-default\" data-action=\"#\" id=\"id_3\">link</button>
                                    </span>
                                    <span data-action=\"insert-bold\">
                                        <button class=\"btn btn-default\" data-action=\"#\" id=\"id_4\">bold</button>
                                    </span>
                                    <span data-action=\"insert-italic\">
                                        <button class=\"btn btn-default\" data-action=\"#\" id=\"id_5\">italic</button>
                                    </span>
                                    <span data-action=\"insert-bullet-points\">
                                        <button class=\"btn btn-default\" data-action=\"#\" id=\"id_7\">bulletpoint</button>
                                    </span>
                                    <span data-action=\"insert-enumeration\">
                                        <button class=\"btn btn-default\" data-action=\"#\" id=\"id_6\">numberedlist</button>
                                    </span>
                                </div>
                            </div>
                            <div class=\"ui-input-textarea\">
                                <textarea id=\"id_1\" class=\"form-control form-control-sm\" name=\"name_0\" minlength=\"$min\" maxlength=\"$max\"></textarea>
                                <div class=\"ui-input-textarea-remainder\"> ui_chars_remaining<span data-action=\"remainder\">$max</span></div>
                            </div>
                            <div class=\"c-input-markdown__preview hidden\">
                            </div>
                        </div>
                        <div class=\"help-block\">$byline</div>
                    </div>
                </div>
            "
        );

        $html = $this->brutallyTrimHTML($this->getRendererWithStubs()->render($input));

        $this->assertEquals($expected, $html);
    }

    public function testRenderWithDisabled(): void
    {
        $label = 'test_label';
        $byline = 'test_byline';

        $input = $this->factory->markdown(
            $this->markdown_renderer,
            $label,
            $byline
        )->withDisabled(true)->withNameFrom($this->name_source);

        $expected = $this->brutallyTrimHTML(
            "
                <div class=\"form-group row\">
                    <label for=\"id_1\" class=\"control-label col-sm-4 col-md-3 col-lg-2\">$label</label>
                    <div class=\"col-sm-8 col-md-9 col-lg-10\">
                        <div class=\"c-input-markdown\">
                            <div class=\"c-input-markdown__controls\">
                                view_control_mode
                                <div class=\"c-input-markdown__actions\">
                                    <span data-action=\"insert-heading\">
                                        <button class=\"btn btn-default\" data-action=\"#\" disabled=\"disabled\">header</button>
                                    </span>
                                    <span data-action=\"insert-link\">
                                        <button class=\"btn btn-default\" data-action=\"#\" disabled=\"disabled\">link</button>
                                    </span>
                                    <span data-action=\"insert-bold\">
                                        <button class=\"btn btn-default\" data-action=\"#\" disabled=\"disabled\">bold</button>
                                    </span>
                                    <span data-action=\"insert-italic\">
                                        <button class=\"btn btn-default\" data-action=\"#\" disabled=\"disabled\">italic</button>
                                    </span>
                                    <span data-action=\"insert-bullet-points\">
                                        <button class=\"btn btn-default\" data-action=\"#\" disabled=\"disabled\">bulletpoint</button>
                                    </span>
                                    <span data-action=\"insert-enumeration\">
                                        <button class=\"btn btn-default\" data-action=\"#\" disabled=\"disabled\">numberedlist</button>
                                    </span>
                                </div>
                            </div>
                            <div class=\"ui-input-textarea\">
                                <textarea id=\"id_1\" class=\"form-control form-control-sm\" name=\"name_0\" disabled=\"disabled\"></textarea>
                            </div>
                            <div class=\"c-input-markdown__preview hidden\">
                            </div>
                        </div>
                        <div class=\"help-block\">$byline</div>
                    </div>
                </div>
            "
        );

        $html = $this->brutallyTrimHTML($this->getRendererWithStubs()->render($input));

        $this->assertEquals($expected, $html);
    }

    public function testRenderWithRequired(): void
    {
        $label = 'test_label';
        $byline = 'test_byline';

        $input = $this->factory->markdown(
            $this->markdown_renderer,
            $label,
            $byline
        )->withRequired(true)->withNameFrom($this->name_source);

        $expected = $this->brutallyTrimHTML(
            "
                <div class=\"form-group row\">
                    <label for=\"id_1\" class=\"control-label col-sm-4 col-md-3 col-lg-2\">$label<span class=\"asterisk\">*</span></label>
                    <div class=\"col-sm-8 col-md-9 col-lg-10\">
                        <div class=\"c-input-markdown\">
                            <div class=\"c-input-markdown__controls\">
                                view_control_mode
                                <div class=\"c-input-markdown__actions\">
                                    <span data-action=\"insert-heading\">
                                        <button class=\"btn btn-default\" data-action=\"#\" id=\"id_2\">header</button>
                                    </span>
                                    <span data-action=\"insert-link\">
                                        <button class=\"btn btn-default\" data-action=\"#\" id=\"id_3\">link</button>
                                    </span>
                                    <span data-action=\"insert-bold\">
                                        <button class=\"btn btn-default\" data-action=\"#\" id=\"id_4\">bold</button>
                                    </span>
                                    <span data-action=\"insert-italic\">
                                        <button class=\"btn btn-default\" data-action=\"#\" id=\"id_5\">italic</button>
                                    </span>
                                    <span data-action=\"insert-bullet-points\">
                                        <button class=\"btn btn-default\" data-action=\"#\" id=\"id_7\">bulletpoint</button>
                                    </span>
                                    <span data-action=\"insert-enumeration\">
                                        <button class=\"btn btn-default\" data-action=\"#\" id=\"id_6\">numberedlist</button>
                                    </span>
                                </div>
                            </div>
                            <div class=\"ui-input-textarea\">
                                <textarea id=\"id_1\" class=\"form-control form-control-sm\" name=\"name_0\"></textarea>
                            </div>
                            <div class=\"c-input-markdown__preview hidden\">
                            </div>
                        </div>
                        <div class=\"help-block\">$byline</div>
                    </div>
                </div>
            "
        );

        $html = $this->brutallyTrimHTML($this->getRendererWithStubs()->render($input));

        $this->assertEquals($expected, $html);
    }

    public function testRenderWithError(): void
    {
        $label = 'test_label';
        $byline = 'test_byline';
        $error = 'test_error';

        $input = $this->factory->markdown(
            $this->markdown_renderer,
            $label,
            $byline
        )->withError($error)->withNameFrom($this->name_source);

        $expected = $this->brutallyTrimHTML(
            "
                <div class=\"form-group row\">
                    <label for=\"id_1\" class=\"control-label col-sm-4 col-md-3 col-lg-2\">$label</label>
                    <div class=\"col-sm-8 col-md-9 col-lg-10\">
                    <div class=\"help-block alert alert-danger\" aria-describedby=\"id_1\" role=\"alert\">$error</div>
                        <div class=\"c-input-markdown\">
                            <div class=\"c-input-markdown__controls\">
                                view_control_mode
                                <div class=\"c-input-markdown__actions\">
                                    <span data-action=\"insert-heading\">
                                        <button class=\"btn btn-default\" data-action=\"#\" id=\"id_2\">header</button>
                                    </span>
                                    <span data-action=\"insert-link\">
                                        <button class=\"btn btn-default\" data-action=\"#\" id=\"id_3\">link</button>
                                    </span>
                                    <span data-action=\"insert-bold\">
                                        <button class=\"btn btn-default\" data-action=\"#\" id=\"id_4\">bold</button>
                                    </span>
                                    <span data-action=\"insert-italic\">
                                        <button class=\"btn btn-default\" data-action=\"#\" id=\"id_5\">italic</button>
                                    </span>
                                    <span data-action=\"insert-bullet-points\">
                                        <button class=\"btn btn-default\" data-action=\"#\" id=\"id_7\">bulletpoint</button>
                                    </span>
                                    <span data-action=\"insert-enumeration\">
                                        <button class=\"btn btn-default\" data-action=\"#\" id=\"id_6\">numberedlist</button>
                                    </span>
                                </div>
                            </div>
                            <div class=\"ui-input-textarea\">
                                <textarea id=\"id_1\" class=\"form-control form-control-sm\" name=\"name_0\"></textarea>
                            </div>
                            <div class=\"c-input-markdown__preview hidden\">
                            </div>
                        </div>
                        <div class=\"help-block\">$byline</div>
                    </div>
                </div>
            "
        );

        $html = $this->brutallyTrimHTML($this->getRendererWithStubs()->render($input));

        $this->assertEquals($expected, $html);
    }

    protected function getRendererWithStubs(): TestDefaultRenderer
    {
        return $this->getDefaultRenderer(null, [
            $this->view_control_mock,
            $this->header_glyph_mock,
            $this->italic_glyph_mock,
            $this->bold_glyph_mock,
            $this->link_glyph_mock,
            $this->numberedlist_glyph_mock,
            $this->bulledpoint_glyph_mock,
        ]);
    }

    protected function buildMinimalFieldFactory(): FieldFactory
    {
        return new FieldFactory(
            $this->createMock(UploadLimitResolver::class),
            new SignalGenerator(),
            $this->createMock(DataFactory::class),
            $this->createMock(Refinery::class),
            $this->getLanguage()
        );
    }

    protected function buildButtonFactory(): ButtonFactory
    {
        return new ButtonFactory();
    }

    protected function getMarkdownRendererMock(): MarkdownRenderer
    {
        $markdown_renderer = $this->createMock(MarkdownRenderer::class);
        $markdown_renderer->method('getAsyncUrl')->willReturn(self::TEST_ASYNC_URL);
        $markdown_renderer->method('getParameterName')->willReturn(self::TEST_PARAMETER_NAME);
        $markdown_renderer->method('render')->willReturnCallback(
            static function ($value) {
                return $value;
            }
        );

        return $markdown_renderer;
    }

    protected function getSymbolFactoryMock(): SymbolFactory
    {
        $glyph_factory = $this->createMock(GlyphFactory::class);
        $glyph_factory->method('header')->willReturn($this->header_glyph_mock);
        $glyph_factory->method('italic')->willReturn($this->italic_glyph_mock);
        $glyph_factory->method('bold')->willReturn($this->bold_glyph_mock);
        $glyph_factory->method('link')->willReturn($this->link_glyph_mock);
        $glyph_factory->method('numberedlist')->willReturn($this->numberedlist_glyph_mock);
        $glyph_factory->method('bulletlist')->willReturn($this->bulledpoint_glyph_mock);

        $symbol_factory = $this->createMock(SymbolFactory::class);
        $symbol_factory->method('glyph')->willReturn($glyph_factory);

        return $symbol_factory;
    }

    protected function getViewControlFactoryMock(): ViewControlFactory
    {
        $view_control_factory = $this->createMock(ViewControlFactory::class);
        $view_control_factory->method('mode')->willReturn($this->getViewControlModeStub());

        return $view_control_factory;
    }

    protected function getViewControlModeStub(): ViewControlMode
    {
        $view_control = $this->createMock(ViewControlMode::class);
        $view_control->method('getCanonicalName')->willReturn('view_control_mode');

        return $view_control;
    }

    protected function getGlyphStub(string $name): Glyph
    {
        $glyph = $this->createMock(Glyph::class);
        $glyph->method('getCanonicalName')->willReturn($name);
        // will be called in the rendering process of this input.
        $glyph->method('withUnavailableAction')->willReturnSelf();

        return $glyph;
    }
}
