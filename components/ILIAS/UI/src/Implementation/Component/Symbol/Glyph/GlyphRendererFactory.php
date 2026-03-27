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

namespace ILIAS\UI\Implementation\Component\Symbol\Glyph;

use ILIAS\UI\Implementation\Render;
use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Render\ComponentRenderer;

/**
 * @see ButtonLikeContextRenderer for when to use it
 */
class GlyphRendererFactory extends Render\DefaultRendererFactory
{
    /**
     * @see Component\Button\Button, Component\Link\Bulky
     */
    protected const array USE_BUTTON_CONTEXT_RENDERER_FOR_DESCENDANTS_OF = [
        'StandardFilterContainerInput', // embeds glyph directly inside button element
        'MultiSelectFieldInput', // embeds glyph directly inside button element
        'RadioFieldInput', // embeds glyph directly inside button element
        'StandardButton',
        'PrimaryButton',
        'BulkyButton',
        'ShyButton',
        'TagButton',
        'BulkyLink',
    ];

    public function getRendererInContext(Component\Component $component, array $contexts): ComponentRenderer
    {
        if ($this->isButtonLikeContext($contexts)) {
            return new ButtonLikeContextRenderer(
                $this->ui_factory,
                $this->tpl_factory,
                $this->lng,
                $this->js_binding,
                $this->image_path_resolver,
                $this->data_factory,
                $this->help_text_retriever,
                $this->upload_limit_resolver
            );
        }
        return new Renderer(
            $this->ui_factory,
            $this->tpl_factory,
            $this->lng,
            $this->js_binding,
            $this->image_path_resolver,
            $this->data_factory,
            $this->help_text_retriever,
            $this->upload_limit_resolver
        );
    }

    /**
     * @param string[] $contexts canonical names
     */
    private function isButtonLikeContext(array $contexts): bool
    {
        // check if glyph is descendant of button-like component
        return (0 < count(array_intersect($contexts, self::USE_BUTTON_CONTEXT_RENDERER_FOR_DESCENDANTS_OF)));
    }
}
