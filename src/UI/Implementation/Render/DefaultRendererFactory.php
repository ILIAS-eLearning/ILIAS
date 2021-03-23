<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Render;

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Factory as RootFactory;

class DefaultRendererFactory implements RendererFactory
{
    /**
     * @var	RootFactory
     */
    protected $ui_factory;

    /**
     * @var	TemplateFactory
     */
    protected $tpl_factory;

    /**
     * @var	\ilLanguage
     */
    protected $lng;

    /**
     * @var	JavaScriptBinding
     */
    protected $js_binding;

    /**
     * @var Refinery
     */
    protected $refinery;

    /**
     * ImagePathResolver
     */
    protected $image_path_resolver;

    public function __construct(
        RootFactory $ui_factory,
        TemplateFactory $tpl_factory,
        \ilLanguage $lng,
        JavaScriptBinding $js_binding,
        Refinery $refinery,
        ImagePathResolver $image_path_resolver
    ) {
        $this->ui_factory = $ui_factory;
        $this->tpl_factory = $tpl_factory;
        $this->lng = $lng;
        $this->js_binding = $js_binding;
        $this->refinery = $refinery;
        $this->image_path_resolver = $image_path_resolver;
    }

    /**
     * @inheritdocs
     */
    public function getRendererInContext(Component $component, array $contexts)
    {
        $name = $this->getRendererNameFor($component);
        return new $name(
            $this->ui_factory,
            $this->tpl_factory,
            $this->lng,
            $this->js_binding,
            $this->refinery,
            $this->image_path_resolver
        );
    }


    /**
     * Get the name for the renderer of Component class.
     *
     * @param	Component $component
     * @return	string
     */
    protected function getRendererNameFor(Component $component)
    {
        $class = get_class($component);
        $parts = explode("\\", $class);
        $parts[count($parts) - 1] = "Renderer";
        $base = implode("\\", $parts);
        return $base;
    }

    /**
     * @inheritdocs
     */
    public function getJSBinding()
    {
        return $this->js_binding;
    }
}
