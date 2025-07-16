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

class GlyphRendererFactory extends Render\DefaultRendererFactory
{
    /**
     * components which render glyphs inside an HTML <button> element, where only
     * palpable content is allowed.
     * @see https://html.spec.whatwg.org/#palpable-content
     */
    protected const array USE_BUTTON_CONTEXT_RENDERER_FOR = [
        'BranchNodeFieldInput',
        'LeafNodeFieldInput',
        'AsyncNodeFieldInput',
        'StandardButton',
        'PrimaryButton',
        'BulkyButton',
        'ShyButton',
        'BulkyLink',
        'ShyLink',
    ];

    public function getRendererInContext(Component\Component $component, array $contexts): ComponentRenderer
    {
        if (count(array_intersect(self::USE_BUTTON_CONTEXT_RENDERER_FOR, $contexts)) > 0) {
            return new ButtonContextRenderer(
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
}
