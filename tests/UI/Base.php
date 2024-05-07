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

require_once("libs/composer/vendor/autoload.php");

require_once(__DIR__ . "/Renderer/ilIndependentTemplate.php");
require_once(__DIR__ . "/../../Services/Language/classes/class.ilLanguage.php");

use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Component as IComponent;
use ILIAS\UI\Implementaiton\Component as I;
use ILIAS\UI\Implementation\Render\DecoratedRenderer;
use ILIAS\UI\Implementation\Render\TemplateFactory;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Implementation\Render\JavaScriptBinding;
use ILIAS\UI\Implementation\Render\DefaultRendererFactory;
use ILIAS\UI\Implementation\DefaultRenderer;
use ILIAS\UI\Implementation\Render;
use ILIAS\UI\Implementation\Component\Symbol\Glyph\GlyphRendererFactory;
use ILIAS\UI\Implementation\Component\Symbol\Icon\IconRendererFactory;
use ILIAS\UI\Implementation\Component\Input\Field\FieldRendererFactory;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use PHPUnit\Framework\TestCase;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use ILIAS\UI\Component\Component;
use ILIAS\Data\Factory as DataFactory;

class ilIndependentTemplateFactory implements TemplateFactory
{
    public function getTemplate(string $path, bool $purge_unfilled_vars, bool $purge_unused_blocks): Render\Template
    {
        return new ilIndependentGlobalTemplate($path, $purge_unfilled_vars, $purge_unused_blocks);
    }
}

class NoUIFactory implements Factory
{
    public function counter(): C\Counter\Factory
    {
    }
    public function button(): C\Button\Factory
    {
    }
    public function card(): C\Card\Factory
    {
    }
    public function deck(array $cards): C\Deck\Deck
    {
    }
    public function listing(): C\Listing\Factory
    {
    }
    public function image(): C\Image\Factory
    {
    }
    public function legacy(string $content): C\Legacy\Legacy
    {
    }
    public function panel(): C\Panel\Factory
    {
    }
    public function modal(): C\Modal\Factory
    {
    }
    public function dropzone(): C\Dropzone\Factory
    {
    }
    public function popover(): C\Popover\Factory
    {
    }
    public function divider(): C\Divider\Factory
    {
    }
    public function link(): C\Link\Factory
    {
    }
    public function dropdown(): C\Dropdown\Factory
    {
    }
    public function item(): C\Item\Factory
    {
    }
    public function viewControl(): C\ViewControl\Factory
    {
    }
    public function breadcrumbs(array $crumbs): C\Breadcrumbs\Breadcrumbs
    {
    }
    public function chart(): C\Chart\Factory
    {
    }
    public function input(): C\Input\Factory
    {
    }
    public function table(): C\Table\Factory
    {
    }
    public function messageBox(): C\MessageBox\Factory
    {
    }
    public function layout(): C\Layout\Factory
    {
    }
    public function mainControls(): C\MainControls\Factory
    {
    }
    public function tree(): C\Tree\Factory
    {
    }
    public function menu(): C\Menu\Factory
    {
    }
    public function symbol(): C\Symbol\Factory
    {
    }
    public function toast(): C\Toast\Factory
    {
    }
    public function player(): C\Player\Factory
    {
    }
}

class LoggingRegistry implements ResourceRegistry
{
    public $resources = array();

    public function register(string $name): void
    {
        $this->resources[] = $name;
    }
}

class ilLanguageMock extends ilLanguage
{
    public array $requested = array();

    public function __construct()
    {
    }

    public function txt($a_topic, $a_default_lang_fallback_mod = ""): string
    {
        $this->requested[] = $a_topic;
        return $a_topic;
    }

    public function toJS($a_lang_key, ilGlobalTemplateInterface $a_tpl = null): void
    {
    }

    public string $lang_module = 'common';

    public function loadLanguageModule(string $a_module): void
    {
    }

    public function getLangKey(): string
    {
        return "en";
    }
}

class LoggingJavaScriptBinding implements JavaScriptBinding
{
    public array $on_load_code = array();
    public array $ids = array();
    private int $count = 0;

    public function createId(): string
    {
        $this->count++;
        $id = "id_" . $this->count;
        $this->ids[] = $id;
        return $id;
    }

    public function addOnLoadCode($code): void
    {
        $this->on_load_code[] = $code;
    }

    public function getOnLoadCodeAsync(): string
    {
        return "";
    }
}

class TestDefaultRenderer extends DefaultRenderer
{
    protected array $with_stub_renderings = [];

    public function __construct(Render\Loader $component_renderer_loader, array $with_stub_renderings = [])
    {
        $this->with_stub_renderings = array_map(function ($component) {
            return get_class($component);
        }, $with_stub_renderings);
        parent::__construct($component_renderer_loader);
    }

    public function _getRendererFor(IComponent $component): Render\ComponentRenderer
    {
        return $this->getRendererFor($component);
    }

    public function getRendererFor(IComponent $component): Render\ComponentRenderer
    {
        if (in_array(get_class($component), $this->with_stub_renderings)) {
            return new TestDummyRenderer();
        }
        return parent::getRendererFor($component);
    }

    public function _getContexts(): array
    {
        return $this->getContexts();
    }
}

class TestDummyRenderer implements Render\ComponentRenderer
{
    public function __construct()
    {
    }

    public function render(ILIAS\UI\Component\Component $component, ILIAS\UI\Renderer $default_renderer): string
    {
        return $component->getCanonicalName();
    }

    public function registerResources(ResourceRegistry $registry): void
    {
        // TODO: Implement registerResources() method.
    }
}

class TestDecoratedRenderer extends DecoratedRenderer
{
    private $manipulate = false;

    public function manipulate(): void
    {
        $this->manipulate = true;
    }

    protected function manipulateRendering($component, Renderer $root): ?string
    {
        if ($this->manipulate) {
            return "This content was manipulated";
        } else {
            return null;
        }
    }
}

class IncrementalSignalGenerator extends SignalGenerator
{
    protected int $id = 0;

    protected function createId(): string
    {
        return 'signal_' . ++$this->id;
    }
}

class SignalGeneratorMock extends SignalGenerator
{
}

class DummyComponent implements IComponent
{
    public function getCanonicalName(): string
    {
        return "DummyComponent";
    }
}

/**
 * Provides common functionality for UI tests.
 */
abstract class ILIAS_UI_TestBase extends TestCase
{
    public function setUp(): void
    {
        assert_options(ASSERT_WARNING, 0);
    }

    public function tearDown(): void
    {
        assert_options(ASSERT_WARNING, 1);
    }

    public function getUIFactory(): NoUIFactory
    {
        return new NoUIFactory();
    }

    public function getTemplateFactory(): ilIndependentTemplateFactory
    {
        return new ilIndependentTemplateFactory();
    }

    public function getResourceRegistry(): LoggingRegistry
    {
        return new LoggingRegistry();
    }

    public function getLanguage(): ilLanguageMock
    {
        return new ilLanguageMock();
    }

    public function getJavaScriptBinding(): LoggingJavaScriptBinding
    {
        return new LoggingJavaScriptBinding();
    }

    /**
     * @return \ILIAS\Refinery\Factory|mixed|MockObject
     */
    public function getRefinery()
    {
        return $this->getMockBuilder(\ILIAS\Refinery\Factory::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function getImagePathResolver(): ilImagePathResolver
    {
        return new ilImagePathResolver();
    }

    public function getDataFactory(): DataFactory
    {
        return $this->createMock(DataFactory::class);
    }

    public function getDefaultRenderer(
        JavaScriptBinding $js_binding = null,
        array $with_stub_renderings = []
    ): TestDefaultRenderer {
        $ui_factory = $this->getUIFactory();
        $tpl_factory = $this->getTemplateFactory();
        $resource_registry = $this->getResourceRegistry();
        $lng = $this->getLanguage();
        if (!$js_binding) {
            $js_binding = $this->getJavaScriptBinding();
        }

        $refinery = $this->getRefinery();
        $image_path_resolver = $this->getImagePathResolver();
        $data_factory = $this->getDataFactory();

        $component_renderer_loader = new Render\LoaderCachingWrapper(
            new Render\LoaderResourceRegistryWrapper(
                $resource_registry,
                new Render\FSLoader(
                    new DefaultRendererFactory(
                        $ui_factory,
                        $tpl_factory,
                        $lng,
                        $js_binding,
                        $refinery,
                        $image_path_resolver,
                        $data_factory
                    ),
                    new GlyphRendererFactory(
                        $ui_factory,
                        $tpl_factory,
                        $lng,
                        $js_binding,
                        $refinery,
                        $image_path_resolver,
                        $data_factory
                    ),
                    new IconRendererFactory(
                        $ui_factory,
                        $tpl_factory,
                        $lng,
                        $js_binding,
                        $refinery,
                        $image_path_resolver,
                        $data_factory
                    ),
                    new FieldRendererFactory(
                        $ui_factory,
                        $tpl_factory,
                        $lng,
                        $js_binding,
                        $refinery,
                        $image_path_resolver,
                        $data_factory
                    )
                )
            )
        );
        return new TestDefaultRenderer($component_renderer_loader, $with_stub_renderings);
    }

    public function getDecoratedRenderer(Renderer $default)
    {
        return new TestDecoratedRenderer($default);
    }

    public function normalizeHTML(string $html): string
    {
        return trim(str_replace(["\n", "\r"], "", $html));
    }

    public function assertHTMLEquals(string $expected_html_as_string, string $html_as_string): void
    {
        $html = new DOMDocument();
        $html->formatOutput = true;
        $html->preserveWhiteSpace = false;
        $expected = new DOMDocument();
        $expected->formatOutput = true;
        $expected->preserveWhiteSpace = false;
        $html->loadXML($this->normalizeHTML($html_as_string));
        $expected->loadXML($this->normalizeHTML($expected_html_as_string));
        $this->assertEquals($expected->saveHTML(), $html->saveHTML());
    }

    /**
     * A more radical version of normalizeHTML. Use if hard to tackle issues
     * occur by asserting due string outputs produce an equal DOM
     */
    protected function brutallyTrimHTML(string $html): string
    {
        $html = str_replace(["\n", "\r", "\t"], "", $html);
        $html = preg_replace('# {2,}#', " ", $html);
        $html = preg_replace('/<!--(.|\s)*?-->/', '', $html);
        $html = preg_replace("/>(\s+)</", "><", $html);
        $html = str_replace(" >", ">", $html);
        $html = str_replace(" <", "<", $html);
        return trim($html);
    }

    /**
     * A naive replacement of all il_signal-ids with dots
     * to ease comparisons of rendered output.
     */
    protected function brutallyTrimSignals(string $html): string
    {
        $html = preg_replace('/il_signal_(\w+)/', "il_signal...", $html);
        return $html;
    }
}
