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

use ILIAS\UI\Implementation\Component\Button\Bulky;
use ILIAS\UI\Implementation\Render\DecoratedRenderer;
use ILIAS\UI\Renderer;

/**
 * Extend the DecoratedRenderer to align your renderer with other potential renderers in ILIAS,
 * and allow manipulations from different sources to be chained to one another.
 */
class ExampleRenderer extends DecoratedRenderer
{
    protected function manipulateRendering($component, Renderer $root) : ?string
    {
        // choose the component you want to manipulate by checking the instance
        // as closely as possible. please note there may be custom components in
        // the current chain, which may extend an interface or implementation,
        // and also run into your manipulation.
        if ($component instanceof Bulky) {
            // render the component by passing it to the rendering chain of
            // renderers that come before this renderer.
            $html = $this->renderDefault($component);
            // manipulate the html to your desire and/or replace it entirely.
            $html .= 'my custom additions';
            // finally pass the manipulated HTML to the cain, so other renderers
            // that come after this may manipulate it too.
            return $html;
        }

        // return null to indicate you are not interested in the given component.
        return null;
    }
}
