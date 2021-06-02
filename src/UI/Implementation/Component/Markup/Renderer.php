<?php

/* Copyright (c) 2021 Adrian Lüthi <adi.l@bluewin.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Markup;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

/**
 * Class Renderer
 * @package ILIAS\UI\Implementation\Component\Markup
 */
class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdocs
     */
    public function render(Component\Component $component, RendererInterface $default_renderer)
    {
        /**
         * @var Component\Markup\Markup $component
         */
        $this->checkComponent($component);
        $tpl = $this->getTemplate('tpl.markup.html', true, true);
        $id = $this->bindJavaScript($component);

        $tpl->setCurrentBlock('content');
        $tpl->setVariable('ID', $id);
        $tpl->setVariable('CONTENT', $component->getContent());
        $tpl->parseCurrentBlock();

        return $tpl->get();
    }

    /**
     * @inheritdoc
     */
    public function registerResources(ResourceRegistry $registry)
    {
        parent::registerResources($registry);

        $registry->register('./src/UI/templates/js/Markup/markup.js');

        $registry->register('./node_modules/codemirror/lib/codemirror.css');
        $registry->register('./node_modules/@toast-ui/editor/dist/toastui-editor.css');
        $registry->register('./node_modules/codemirror/lib/codemirror.js');
        $registry->register('./node_modules/@toast-ui/editor/dist/toastui-editor.js');
    }

    /**
     * @inheritdocs
     */
    protected function getComponentInterfaceName()
    {
        return [Component\Markup\Markup::class];
    }
}
