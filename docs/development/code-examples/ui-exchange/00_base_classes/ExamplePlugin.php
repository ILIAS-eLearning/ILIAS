<?php

use ILIAS\DI\Container;

class ExamplePlugin extends ilUserInterfaceHookPlugin
{
    public function exchangeUIRendererAfterInitialization(Container $dic): Closure
    {
        //Safe the origin renderer closure
        $renderer = $dic->raw('ui.renderer');

        //return origin if plugin is not active
        if (!$this->isActive()) {
            return $renderer;
        }

        //else return own renderer with origin as default
        //be aware that you can not provide the renderer itself for the closure since its state changes
        return function () use ($dic, $renderer) {
            return new ExampleRenderer($renderer($dic));
        };
    }

    public function getPluginName() : string
    {
        return "NoRealPlugin";
    }
}
