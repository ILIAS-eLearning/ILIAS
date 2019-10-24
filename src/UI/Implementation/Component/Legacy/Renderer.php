<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Legacy;

use ILIAS\UI\Implementation\Component\Signal;
use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

/**
 * Class Renderer
 * @package ILIAS\UI\Implementation\Component\Legacy\Html
 */
class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdocs
     */
    public function render(Component\Component $component, RendererInterface $default_renderer)
    {
        /**
         * @var Component\Legacy\Legacy $component
         */
        $this->checkComponent($component);

        return $component->getContent() . $this->renderCustomSignals($component);
    }

    /**
     * @inheritdocs
     */
    protected function getComponentInterfaceName()
    {
        return [Component\Legacy\Legacy::class];
    }

    /**
     * Renders the custom signals of the given legacy component
     *
     * @param $component
     * @return string
     */
    protected function renderCustomSignals(Legacy $component)
    {
        $signal_list = $component->getAllSignals();

        if(count($signal_list) < 1)
        {
            return '';
        }

        $code = "<script>";
        foreach($signal_list as $signal)
        {
            $code .= "$(this).on('{$signal['signal']->getId()}', function(e){".$signal['js_code']."});";
        }
        $code .= "</script>";

        return $code;
    }
}
