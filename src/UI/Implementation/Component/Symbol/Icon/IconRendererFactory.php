<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Symbol\Icon;

use ILIAS\UI\Implementation\Render;
use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Render\ComponentRenderer;

class IconRendererFactory extends Render\DefaultRendererFactory
{
    public const USE_BUTTON_CONTEXT_FOR = [
        'BulkyButton',
        'BulkyLink'
    ];

    public function getRendererInContext(Component\Component $component, array $contexts) : ComponentRenderer
    {
        if (count(array_intersect(self::USE_BUTTON_CONTEXT_FOR, $contexts)) > 0) {
            return new ButtonContextRenderer(
                $this->ui_factory,
                $this->tpl_factory,
                $this->lng,
                $this->js_binding,
                $this->refinery,
                $this->image_path_resolver
            );
        }
        return new Renderer(
            $this->ui_factory,
            $this->tpl_factory,
            $this->lng,
            $this->js_binding,
            $this->refinery,
            $this->image_path_resolver
        );
    }
}
