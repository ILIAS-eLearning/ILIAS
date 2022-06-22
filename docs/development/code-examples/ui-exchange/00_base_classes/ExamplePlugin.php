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

    public function getPluginName()
    {
        return "NoRealPlugin";
    }
}
