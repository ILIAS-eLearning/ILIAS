<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Glyph {
    require_once("libs/composer/vendor/autoload.php");

    use \ILIAS\UI\Component\Component;
    use \ILIAS\UI\Renderer;
    use \ILIAS\UI\Implementation\Render\AbstractComponentRenderer;

    class GlyphNonAbstractRenderer extends AbstractComponentRenderer
    {
        public function render(Component $component, Renderer $default_renderer)
        {
        }
        public function _getTemplate($a, $b, $c)
        {
            return $this->getTemplate($a, $b, $c);
        }
        protected function getComponentInterfaceName()
        {
            return "\\ILIAS\\UI\\Component\\Glyph\\Glyph";
        }
    }

    class GlyphNonAbstractRendererWithJS extends GlyphNonAbstractRenderer
    {
        public $ids = array();
        public function render(Component $component, Renderer $default_renderer)
        {
            $this->ids[] = $this->bindJavaScript($component);
            return "";
        }
    }
}

namespace ILIAS\UI\Implementation\Component\Counter {
    use \ILIAS\UI\Component\Component;
    use \ILIAS\UI\Renderer;
    use \ILIAS\UI\Implementation\Render\AbstractComponentRenderer;

    class CounterNonAbstractRenderer extends AbstractComponentRenderer
    {
        public function render(Component $component, Renderer $default_renderer)
        {
        }
        public function _getTemplate($a, $b, $c)
        {
            return $this->getTemplate($a, $b, $c);
        }
        protected function getComponentInterfaceName()
        {
            return "\\ILIAS\\UI\\Component\\Counter\\Counter";
        }
    }
}

namespace {

    require_once(__DIR__ . "/../Base.php");


    use \ILIAS\UI\Component as C;
    use \ILIAS\UI\Implementation\Render\Template;
    use \ILIAS\UI\Implementation\Render\JavaScriptBinding;
    use \ILIAS\UI\Implementation\Render\TemplateFactory;

    class NullTemplate implements Template
    {
        public function setCurrentBlock($name)
        {
        }
        public function parseCurrentBlock()
        {
        }
        public function touchBlock($name)
        {
        }
        public function setVariable($name, $value)
        {
        }
        public function get($name = null)
        {
            return "";
        }
        public function addOnLoadCode($code)
        {
            return "";
        }
    }

    class TemplateFactoryMock implements TemplateFactory
    {
        public $files = array();
        public function getTemplate($file_name, $purge_unfilled_vars, $purge_unused_blocks)
        {
            $file_name = realpath(__DIR__ . "/../../../" . $file_name);
            $this->files[$file_name] = array($purge_unfilled_vars, $purge_unused_blocks);

            if (!file_exists($file_name)) {
                throw new \InvalidArgumentException();
            }

            return new NullTemplate();
        }
    }

    class NullDefaultRenderer implements \ILIAS\UI\Renderer
    {
        public function render($component)
        {
            return "";
        }
        public function renderAsync($component)
        {
            return '';
        }
        public function withAdditionalContext(C\Component $context)
        {
            return $this;
        }
    }

    class AbstractRendererTest extends ILIAS_UI_TestBase
    {
        public function setUp()
        {
            parent::setUp();
            $this->tpl_factory = new TemplateFactoryMock();
            $this->ui_factory = new NoUIFactory();
            $this->lng = new ilLanguageMock();
            $this->js_binding = new LoggingJavaScriptBinding();
        }

        public function test_getTemplate_successfull()
        {
            $r = new \ILIAS\UI\Implementation\Component\Glyph\GlyphNonAbstractRenderer($this->ui_factory, $this->tpl_factory, $this->lng, $this->js_binding);
            $tpl = $r->_getTemplate("tpl.glyph.html", true, false);

            $expected = array( realpath(__DIR__ . "/../../../src/UI/templates/default/Glyph/tpl.glyph.html")
                    => array(true, false)
            );

            $this->assertEquals($expected, $this->tpl_factory->files);
        }

        public function test_getTemplate_unsuccessfull()
        {
            $r = new \ILIAS\UI\Implementation\Component\Counter\CounterNonAbstractRenderer($this->ui_factory, $this->tpl_factory, $this->lng, $this->js_binding);

            try {
                $tpl = $r->_getTemplate("tpl.counter_foo.html", true, false);
                $this->assertFalse("We should not get here");
            } catch (\InvalidArgumentException $e) {
            };

            $expected = array( realpath(__DIR__ . "/../../src/UI/templates/default/Counter/tpl.counter_foo.html")
                    => array(true, false)
            );
            $this->assertEquals($expected, $this->tpl_factory->files);
        }

        public function test_bindJavaScript_successfull()
        {
            $r = new \ILIAS\UI\Implementation\Component\Glyph\GlyphNonAbstractRendererWithJS($this->ui_factory, $this->tpl_factory, $this->lng, $this->js_binding);

            $g = new \ILIAS\UI\Implementation\Component\Glyph\Glyph(\ILIAS\UI\Component\Glyph\Glyph::SETTINGS, "aria_label");

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

        public function test_bindJavaScript_no_string()
        {
            $r = new \ILIAS\UI\Implementation\Component\Glyph\GlyphNonAbstractRendererWithJS($this->ui_factory, $this->tpl_factory, $this->lng, $this->js_binding);

            $g = new \ILIAS\UI\Implementation\Component\Glyph\Glyph(\ILIAS\UI\Component\Glyph\Glyph::SETTINGS, "aria_label");

            $g = $g->withOnLoadCode(function ($id) {
                return null;
            });

            try {
                $r->render($g, new NullDefaultRenderer());
                $this->assertFalse("This should not happen...");
            } catch (\LogicException $e) {
                $this->assertTrue(true);
            }
        }
    }
}
