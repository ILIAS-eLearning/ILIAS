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

namespace ILIAS\UI\Implementation\Component\Symbol\Icon;

use ILIAS\UI\Implementation\Render;
use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Render\ComponentRenderer;

class IconRendererFactory extends Render\DefaultRendererFactory
{
    public const USE_BUTTON_CONTEXT_FOR = [
        'BulkyButton',
        'BulkyLink'
    ];

    public function getRendererInContext(Component\Component $component, array $contexts): ComponentRenderer
    {
        if (count(array_intersect(self::USE_BUTTON_CONTEXT_FOR, $contexts)) > 0) {
            return new ButtonContextRenderer(
                $this->ui_factory,
                $this->tpl_factory,
                $this->lng,
                $this->js_binding,
                $this->refinery,
                $this->image_path_resolver,
                $this->data_factory
            );
        }
        return new Renderer(
            $this->ui_factory,
            $this->tpl_factory,
            $this->lng,
            $this->js_binding,
            $this->refinery,
            $this->image_path_resolver,
            $this->data_factory
        );
    }
}
