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
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Toast;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Implementation\Render;
use ILIAS\UI\Implementation\Render\ComponentRenderer;

/**
 * @author Ingmar Szmais <iszmais@databay.de>
 */
class ToastRendererFactory extends Render\DefaultRendererFactory
{
    protected int $vanish_time;
    protected int $delay_time;

    public function __construct($c, $c1, $lng, $c2, $refinery, $c3, int $vanish_time, int $delay_time)
    {
        $this->vanish_time = $vanish_time;
        $this->delay_time = $delay_time;
        parent::__construct($c, $c1, $lng, $c2, $refinery, $c3);
    }

    public function getRendererInContext(Component $component, array $contexts): ComponentRenderer
    {
        $name = $this->getRendererNameFor($component);
        return new $name(
            $this->ui_factory,
            $this->tpl_factory,
            $this->lng,
            $this->js_binding,
            $this->refinery,
            $this->image_path_resolver,
            $this->vanish_time,
            $this->delay_time
        );
    }
}
