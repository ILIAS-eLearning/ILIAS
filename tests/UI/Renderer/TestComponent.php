<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Test;

use \ILIAS\UI\Implementation\Render\ResourceRegistry;
use \ILIAS\UI\Implementation\Component\ComponentHelper;
use \ILIAS\UI\Renderer as DefaultRenderer;
use \ILIAS\UI\Component\Component;
use \ILIAS\UI\Implementation\Component\JavaScriptBindable;

class TestComponent implements \ILIAS\UI\Component\Component
{
    use ComponentHelper;

    public function __construct($text)
    {
        $this->text = $text;
    }
}

class JSTestComponent implements \ILIAS\UI\Component\Component, \ILIAS\UI\Component\JavaScriptBindable
{
    use ComponentHelper;
    use JavaScriptBindable;

    public function __construct($text)
    {
        $this->text = $text;
    }
}

class Renderer implements \ILIAS\UI\Implementation\Render\ComponentRenderer
{
    final public function __construct($ui_factory, $tpl_factory, $lng, $js_binding)
    {
        $this->ui_factory = $ui_factory;
        $this->tpl_factory = $tpl_factory;
        $this->lng = $lng;
        $this->js_binding = $js_binding;
    }

    public function render(Component $component, DefaultRenderer $default_renderer)
    {
        if ($component instanceof JSTestComponent) {
            $text = $component->text;
            $component = $component->withAdditionalOnLoadCode(function ($id) use ($text) {
                return "id:$text.$id content:$text";
            });
            $this->bindOnloadCode($component);
        }
        return $component->text;
    }

    public function registerResources(ResourceRegistry $registry)
    {
        $registry->register("test.js");
    }

    private function bindOnloadCode(\ILIAS\UI\Component\JavaScriptBindable $component)
    {
        $binder = $component->getOnLoadCode();
        $this->js_binding->addOnLoadCode($binder("id"));
    }
}
