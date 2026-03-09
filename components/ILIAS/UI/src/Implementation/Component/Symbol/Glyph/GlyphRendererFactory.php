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
     * components which render glyphs inside an HTML <button> element (or equivalent),
     * where only palpable content is allowed (no <a>).
     * Any glyph rendered anywhere within these components' subtree will use the
     * ButtonContextRenderer.
     * @see https://html.spec.whatwg.org/#palpable-content
     */
    protected const USE_BUTTON_CONTEXT_RENDERER_FOR = [
        'StandardButton',
        'PrimaryButton',
        'BulkyButton',
        'ShyButton',
        'BulkyLink',
        'ShyLink',
    ];

    /**
     * Components that render glyphs inside an HTML <button> element themselves
     * but also contain child components whose glyphs should NOT use the
     * ButtonContextRenderer. Only glyphs that are direct children of these
     * components (i.e. these components are the immediate parent context) will
     * use the ButtonContextRenderer. This prevents false-positives for glyphs
     * in nested child components.
     */
    protected const USE_BUTTON_CONTEXT_RENDERER_FOR_DIRECT_CONTEXT = [
        'StandardFilterContainerInput',
    ];

    public function getRendererInContext(Component\Component $component, array $contexts): ComponentRenderer
    {
        if (
            $this->isDirectContextMatch($contexts) ||
            count(array_intersect(self::USE_BUTTON_CONTEXT_RENDERER_FOR, $contexts)) > 0
        ) {
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

    /**
     * Checks whether the immediate parent context (the component that directly
     * renders this glyph) matches one of the DIRECT_CONTEXT components.
     * The context array has the current component (glyph) as the last element,
     * so the immediate parent is the second-to-last element.
     *
     * @param string[] $contexts
     */
    private function isDirectContextMatch(array $contexts): bool
    {
        $count = count($contexts);
        if ($count < 2) {
            return false;
        }
        $immediate_parent = $contexts[$count - 2];
        return in_array($immediate_parent, self::USE_BUTTON_CONTEXT_RENDERER_FOR_DIRECT_CONTEXT, true);
    }
}
