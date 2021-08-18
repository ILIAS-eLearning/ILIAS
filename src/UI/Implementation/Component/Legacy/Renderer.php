<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Legacy;

use ILIAS\UI\Implementation\Component\Signal;
use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Component\Legacy\Legacy;

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
         * @var Legacy $component
         */
        $this->checkComponent($component);

        $component = $this->registerSignals($component);
        $this->bindJavaScript($component);
        return $component->getContent();
    }

    /**
     * @inheritdocs
     */
    protected function getComponentInterfaceName()
    {
        return [Component\Legacy\Legacy::class];
    }

    /**
     * @param Legacy $component
     * @return \ILIAS\UI\Implementation\Component\JavaScriptBindable
     */
    protected function registerSignals(Legacy $component)
    {
        $custom_signals = $component->getAllCustomSignals();

        return $component->withAdditionalOnLoadCode(function ($id) use ($custom_signals) {
            $code = "";
            foreach ($custom_signals as $custom_signal) {
                $signal_id = $custom_signal['signal'];
                $signal_code = $custom_signal['js_code'];
                $code .= "$(document).on('$signal_id', function(event, signalData) { $signal_code });";
            }
            return $code;
        });
    }
}
