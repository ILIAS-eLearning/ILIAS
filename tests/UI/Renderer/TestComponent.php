<?php declare(strict_types=1);

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Test;

use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Renderer as DefaultRenderer;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Render\ComponentRenderer;

class TestComponent implements Component
{
    use ComponentHelper;

    public string $text;

    public function __construct(string $text)
    {
        $this->text = $text;
    }
}

class JSTestComponent implements Component, \ILIAS\UI\Component\JavaScriptBindable
{
    use ComponentHelper;
    use JavaScriptBindable;

    public $text;

    public function __construct(string $text)
    {
        $this->text = $text;
    }
}

class Renderer implements ComponentRenderer
{
    final public function __construct($ui_factory, $tpl_factory, $lng, $js_binding)
    {
        $this->ui_factory = $ui_factory;
        $this->tpl_factory = $tpl_factory;
        $this->lng = $lng;
        $this->js_binding = $js_binding;
    }

    public function render(Component $component, DefaultRenderer $default_renderer) : string
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

    public function registerResources(ResourceRegistry $registry) : void
    {
        $registry->register("test.js");
    }

    private function bindOnloadCode(\ILIAS\UI\Component\JavaScriptBindable $component) : void
    {
        $binder = $component->getOnLoadCode();
        $this->js_binding->addOnLoadCode($binder("id"));
    }
}
