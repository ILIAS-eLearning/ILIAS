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

use ILIAS\UI\Implementation\Component\Button\Bulky;
use ILIAS\UI\Implementation\Render\DecoratedRenderer;
use ILIAS\UI\Renderer;

//inherit from DecoratedRender to align your renderer with other potential renders in ILIAS to allow manipulations from
//different sources to be chained behind each other.
class ExampleRenderer extends DecoratedRenderer
{
    //define your manipulations. This example add an "A" before every button in ILIAS
    protected function manipulateRendering($component, Renderer $root) : ?string
    {
        //select the component you want to manipulate
        if ($component instanceof Bulky) {
            //if you need the origin rendering (e.g. for append or prepend) you can access it by calling renderDefault()
            return "A" . $this->renderDefault($component, $root);
        }

        //skip components that are not important to you with returning null
        return null;
    }
}