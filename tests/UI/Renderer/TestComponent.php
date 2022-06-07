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
 
namespace ILIAS\UI\Component\Test;

use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Renderer as DefaultRenderer;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Render\ComponentRenderer;
use ILIAS\UI\Factory;
use ILIAS\UI\Implementation\Render\TemplateFactory;
use ILIAS\UI\Implementation\Render\JavaScriptBinding;

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

    public string $text;

    public function __construct(string $text)
    {
        $this->text = $text;
    }
}

class Renderer implements ComponentRenderer
{
    public Factory $ui_factory;
    public TemplateFactory $tpl_factory;
    public \ilLanguage $lng;
    public JavaScriptBinding $js_binding;
    
    final public function __construct(Factory $ui_factory, TemplateFactory $tpl_factory, \ilLanguage $lng, JavaScriptBinding $js_binding)
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
