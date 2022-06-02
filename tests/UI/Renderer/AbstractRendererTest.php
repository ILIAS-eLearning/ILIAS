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
 
namespace ILIAS\UI\Implementation\Component\Symbol\Glyph {

    require_once("libs/composer/vendor/autoload.php");

    use ILIAS\UI\Component\Component;
    use ILIAS\UI\Renderer;
    use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
    use ILIAS\UI\Implementation\Render\Template;

    class GlyphNonAbstractRenderer extends AbstractComponentRenderer
    {
        public function render(Component $component, Renderer $default_renderer) : string
        {
        }

        public function _getTemplate(string $a, bool $b, bool $c) : Template
        {
            return $this->getTemplate($a, $b, $c);
        }

        protected function getComponentInterfaceName() : array
        {
            return ["\\ILIAS\\UI\\Component\\Symbol\\Glyph\\Glyph"];
        }
    }

    class GlyphNonAbstractRendererWithJS extends GlyphNonAbstractRenderer
    {
        public array $ids = array();

        public function render(Component $component, Renderer $default_renderer) : string
        {
            $this->ids[] = $this->bindJavaScript($component);
            return "";
        }
    }
}

namespace ILIAS\UI\Implementation\Component\Counter {

    use ILIAS\UI\Component\Component;
    use ILIAS\UI\Renderer;
    use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
    use ILIAS\UI\Implementation\Render\Template;

    class CounterNonAbstractRenderer extends AbstractComponentRenderer
    {
        public function render(Component $component, Renderer $default_renderer) : string
        {
        }

        public function _getTemplate(string $a, bool $b, bool $c) : Template
        {
            return $this->getTemplate($a, $b, $c);
        }

        protected function getComponentInterfaceName() : array
        {
            return ["\\ILIAS\\UI\\Component\\Counter\\Counter"];
        }
    }
}

namespace {

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
     
    require_once(__DIR__ . "/../Base.php");

    use ILIAS\UI\Component as C;
    use ILIAS\UI\Implementation\Render\Template;
    use ILIAS\UI\Implementation\Render\TemplateFactory;
    use ILIAS\UI\Renderer;
    use ILIAS\UI\Implementation\Component\Symbol\Glyph\GlyphNonAbstractRenderer;
    use PHPUnit\Framework\MockObject\MockObject;
    use ILIAS\UI\Implementation\Render\ImagePathResolver;
    use ILIAS\UI\Implementation\Component\Counter\CounterNonAbstractRenderer;
    use ILIAS\UI\Implementation\Component\Symbol\Glyph\GlyphNonAbstractRendererWithJS;
    use ILIAS\UI\Implementation\Component\Symbol\Glyph\Glyph;

    class NullTemplate implements Template
    {
        public function setCurrentBlock(string $name) : bool
        {
            return true;
        }

        public function parseCurrentBlock() : bool
        {
            return true;
        }

        public function touchBlock(string $name) : bool
        {
            return true;
        }

        public function setVariable(string $name, $value) : void
        {
        }

        public function get(string $block = null) : string
        {
            return "";
        }

        public function addOnLoadCode(string $code) : void
        {
        }
    }

    class TemplateFactoryMock implements TemplateFactory
    {
        public array $files = array();

        public function getTemplate(string $path, bool $purge_unfilled_vars, bool $purge_unused_blocks) : Template
        {
            $file_name = realpath(__DIR__ . "/../../../" . $path);
            $this->files[$file_name] = array($purge_unfilled_vars, $purge_unused_blocks);

            if (!file_exists($file_name)) {
                throw new InvalidArgumentException();
            }

            return new NullTemplate();
        }
    }

    class NullDefaultRenderer implements Renderer
    {
        public function render($component, ?Renderer $root = null)
        {
            return "";
        }
        public function renderAsync($component, ?Renderer $root = null)
        {
            return '';
        }

        public function withAdditionalContext(C\Component $context) : Renderer
        {
            return $this;
        }
    }

    class AbstractRendererTest extends ILIAS_UI_TestBase
    {
        protected TemplateFactoryMock $tpl_factory;
        protected NoUIFactory $ui_factory;
        protected ilLanguageMock $lng;
        protected LoggingJavaScriptBinding $js_binding;
        /**
         * @var ImagePathResolver|mixed|MockObject
         */
        protected $image_path_resolver;

        public function setUp() : void
        {
            parent::setUp();
            $this->tpl_factory = new TemplateFactoryMock();
            $this->ui_factory = $this->getUIFactory(); //new NoUIFactory();
            $this->lng = new ilLanguageMock();
            $this->js_binding = new LoggingJavaScriptBinding();
            $this->image_path_resolver = $this->getMockBuilder(ILIAS\UI\Implementation\Render\ImagePathResolver::class)
                                              ->getMock();
        }

        public function test_getTemplate_successfull() : void
        {
            $r = new GlyphNonAbstractRenderer(
                $this->ui_factory,
                $this->tpl_factory,
                $this->lng,
                $this->js_binding,
                $this->getRefinery(),
                $this->image_path_resolver
            );
            $r->_getTemplate("tpl.glyph.html", true, false);

            $expected = array(realpath(__DIR__ . "/../../../src/UI/templates/default/Symbol/tpl.glyph.html")
                              => array(true, false)
            );

            $this->assertEquals($expected, $this->tpl_factory->files);
        }

        public function test_getTemplate_unsuccessfull() : void
        {
            $r = new CounterNonAbstractRenderer(
                $this->ui_factory,
                $this->tpl_factory,
                $this->lng,
                $this->js_binding,
                $this->getRefinery(),
                $this->image_path_resolver
            );

            $this->expectException(TypeError::class);
            $r->_getTemplate("tpl.counter_foo.html", true, false);

            $expected = array(realpath(__DIR__ . "/../../src/UI/templates/default/Counter/tpl.counter_foo.html")
                              => array(true, false)
            );
            $this->assertEquals($expected, $this->tpl_factory->files);
        }

        public function test_bindJavaScript_successfull() : void
        {
            $r = new GlyphNonAbstractRendererWithJS(
                $this->ui_factory,
                $this->tpl_factory,
                $this->lng,
                $this->js_binding,
                $this->getRefinery(),
                $this->image_path_resolver
            );

            $g = new Glyph(C\Symbol\Glyph\Glyph::SETTINGS, "aria_label");

            $ids = array();
            $g = $g->withOnLoadCode(function ($id) use (&$ids) {
                $ids[] = $id;
                return "ID: $id";
            });
            $r->render($g, new NullDefaultRenderer());

            $this->assertEquals($this->js_binding->ids, $ids);
            $this->assertEquals(array("id_1"), $ids);
            $this->assertEquals(array("ID: id_1"), $this->js_binding->on_load_code);
        }

        public function test_bindJavaScript_no_string() : void
        {
            $r = new GlyphNonAbstractRendererWithJS(
                $this->ui_factory,
                $this->tpl_factory,
                $this->lng,
                $this->js_binding,
                $this->getRefinery(),
                $this->image_path_resolver
            );

            $g = new Glyph(C\Symbol\Glyph\Glyph::SETTINGS, "aria_label");

            $g = $g->withOnLoadCode(function ($id) {
                return null;
            });

            try {
                $r->render($g, new NullDefaultRenderer());
                $this->assertFalse("This should not happen...");
            } catch (LogicException $e) {
                $this->assertTrue(true);
            }
        }
    }
}
