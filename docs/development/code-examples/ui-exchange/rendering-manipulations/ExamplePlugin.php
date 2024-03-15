<?php

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
 */

declare(strict_types=1);

use ILIAS\DI\Container;

/**
 * Extend some more concrete implementation of @see ilPlugin depending on your
 * plugin-slot.
 */
class ExamplePlugin extends ilUserInterfaceHookPlugin
{
    /**
     * This method can ALWAYS replace the UI renderer, because the method is only ever
     * invoked if a plugin is considered active.
     */
    public function exchangeUIRendererAfterInitialization(Container $dic): Closure
    {
        // we need the previous renderer, which has possibly been exchanged, so we can
        // wrap it by our renderer and keep the rendering chain alive.
        $renderer = $dic->raw('ui.renderer');

        return static function () use ($dic, $renderer) {
            // create an instance of the renderer using the Closure from the container.
            return new ExampleRenderer($renderer($dic));
        };
    }

    public function getPluginName(): string
    {
        return "NoRealPlugin";
    }
}
