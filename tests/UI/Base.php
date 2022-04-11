<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");

require_once(__DIR__ . "/Renderer/ilIndependentTemplate.php");
require_once(__DIR__ . "/../../Services/Language/classes/class.ilLanguage.php");

use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Component as IComponent;
use ILIAS\UI\Implementaiton\Component as I;
use ILIAS\UI\Implementation\Render\TemplateFactory;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Implementation\Render\JavaScriptBinding;
use ILIAS\UI\Implementation\Render\DefaultRendererFactory;
use ILIAS\UI\Implementation\DefaultRenderer;
use ILIAS\UI\Implementation\ComponentRendererFSLoader;
use ILIAS\UI\Implementation\Render;
use ILIAS\UI\Implementation\Component\Symbol\Glyph\GlyphRendererFactory;
use ILIAS\UI\Implementation\Component\Input\Field\FieldRendererFactory;
use ILIAS\UI\Factory;
use PHPUnit\Framework\TestCase;
use ILIAS\UI\Renderer;

class ilIndependentTemplateFactory implements TemplateFactory
{
    public function getTemplate($path, $purge_unfilled_vars, $purge_unused_blocks)
    {
        return new ilIndependentGlobalTemplate($path, $purge_unfilled_vars, $purge_unused_blocks);
    }
}

class NoUIFactory implements Factory
{
    public function counter()
    {
    }
    public function button()
    {
    }
    public function card()
    {
    }
    public function deck(array $cards)
    {
    }
    public function listing()
    {
    }
    public function image()
    {
    }
    public function legacy($content)
    {
    }
    public function panel()
    {
    }
    public function modal()
    {
    }
    public function dropzone()
    {
    }
    public function popover()
    {
    }
    public function divider()
    {
    }
    public function link()
    {
    }
    public function dropdown()
    {
    }
    public function item()
    {
    }
    public function viewControl()
    {
    }
    public function breadcrumbs(array $crumbs)
    {
    }
    public function chart()
    {
    }
    public function input()
    {
    }
    public function table()
    {
    }
    public function messageBox()
    {
    }
    public function layout() : C\Layout\Factory
    {
    }
    public function mainControls() : C\MainControls\Factory
    {
    }
    public function tree()
    {
    }
    public function menu() : C\Menu\Factory
    {
    }
    public function symbol() : C\Symbol\Factory
    {
    }
}

class LoggingRegistry implements ResourceRegistry
{
    public $resources = array();

    public function register($name)
    {
        $this->resources[] = $name;
    }
}

class ilLanguageMock extends \ilLanguage
{
    public $requested = array();
    public function __construct()
    {
    }
    public function txt($a_topic, $a_default_lang_fallback_mod = "")
    {
        $this->requested[] = $a_topic;
        return $a_topic;
    }
    public function toJS($a_lang_key, ilGlobalTemplateInterface $a_tpl = null)
    {
    }
    public $lang_module = 'common';
    public function loadLanguageModule($lang_module)
    {
    }

    public function getLangKey()
    {
        return "en";
    }
}

class LoggingJavaScriptBinding implements JavaScriptBinding
{
    private $count = 0;
    public $ids = array();
    public function createId()
    {
        $this->count++;
        $id = "id_" . $this->count;
        $this->ids[] = $id;
        return $id;
    }
    public $on_load_code = array();
    public function addOnLoadCode($code)
    {
        $this->on_load_code[] = $code;
    }
    public function getOnLoadCodeAsync()
    {
    }
}

class TestDefaultRenderer extends DefaultRenderer
{
    /**
     * @var array
     */
    protected $with_stub_renderings = [];

    public function __construct(Render\Loader $component_renderer_loader, $with_stub_renderings = [])
    {
        $this->with_stub_renderings = array_map(function ($component) {
            return get_class($component);
        }, $with_stub_renderings);
        parent::__construct($component_renderer_loader);
    }

    public function _getRendererFor(IComponent $component)
    {
        return $this->getRendererFor($component);
    }

    public function getRendererFor(IComponent $component)
    {
        if (in_array(get_class($component), $this->with_stub_renderings)) {
            return new TestDummyRenderer();
        }
        return parent::getRendererFor($component);
    }

    public function _getContexts()
    {
        return $this->getContexts();
    }
}

class TestDummyRenderer extends DefaultRenderer
{
    public function __construct()
    {
    }
    public function render($component, ?Renderer $root = null)
    {
        return $component->getCanonicalName();
    }
}

class IncrementalSignalGenerator extends \ILIAS\UI\Implementation\Component\SignalGenerator
{
    protected $id = 0;

    protected function createId()
    {
        return 'signal_' . ++$this->id;
    }
}

class SignalGeneratorMock extends \ILIAS\UI\Implementation\Component\SignalGenerator
{
}

class DummyComponent implements IComponent
{
    public function getCanonicalName()
    {
        return "DummyComponent";
    }
}

/**
 * Provides common functionality for UI tests.
 */
abstract class ILIAS_UI_TestBase extends TestCase
{
    public function setUp() : void
    {
        assert_options(ASSERT_WARNING, 0);
    }

    public function tearDown() : void
    {
        assert_options(ASSERT_WARNING, 1);
    }

    public function getUIFactory()
    {
        return new NoUIFactory();
    }

    public function getTemplateFactory()
    {
        return new ilIndependentTemplateFactory();
    }

    public function getResourceRegistry()
    {
        return new LoggingRegistry();
    }

    public function getLanguage()
    {
        return new ilLanguageMock();
    }

    public function getJavaScriptBinding()
    {
        return new LoggingJavaScriptBinding();
    }

    public function getRefinery()
    {
        return $this->getMockBuilder(\ILIAS\Refinery\Factory::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function getImagePathResolver()
    {
        return new ilImagePathResolver();
    }

    public function getDefaultRenderer(JavaScriptBinding $js_binding = null, $with_stub_renderings = [])
    {
        $ui_factory = $this->getUIFactory();
        $tpl_factory = $this->getTemplateFactory();
        $resource_registry = $this->getResourceRegistry();
        $lng = $this->getLanguage();
        if (!$js_binding) {
            $js_binding = $this->getJavaScriptBinding();
        }

        $refinery = $this->getRefinery();
        $image_path_resolver = $this->getImagePathResolver();

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
                        $image_path_resolver
                    ),
                    new GlyphRendererFactory(
                        $ui_factory,
                        $tpl_factory,
                        $lng,
                        $js_binding,
                        $refinery,
                        $image_path_resolver
                    ),
                    new FieldRendererFactory(
                        $ui_factory,
                        $tpl_factory,
                        $lng,
                        $js_binding,
                        $refinery,
                        $image_path_resolver
                    )
                )
            )
        );
        return new TestDefaultRenderer($component_renderer_loader, $with_stub_renderings);
    }

    public function normalizeHTML($html)
    {
        return trim(str_replace(["\n", "\r"], "", $html));
    }

    /**
     * @param string $expected_html_as_string
     * @param string $html_as_string
     */
    public function assertHTMLEquals($expected_html_as_string, $html_as_string)
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
     *
     * @param $html
     * @return string
     */
    protected function brutallyTrimHTML($html)
    {
        $html = str_replace(["\n", "\r", "\t"], "", $html);
        $html = preg_replace('# {2,}#', " ", $html);
        $html = preg_replace('/<!--(.|\s)*?-->/', '', $html);
        $html = preg_replace("/>(\s+)</", "><", $html);
        $html = str_replace(" >", ">", $html);
        $html = str_replace(" <", "<", $html);
        return trim($html);
    }
}
