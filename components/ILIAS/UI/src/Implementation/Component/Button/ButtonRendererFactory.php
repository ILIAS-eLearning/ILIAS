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

namespace ILIAS\UI\Implementation\Component\Button;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Button\Button as ButtonComponent;
use ILIAS\UI\Implementation\Render\ComponentRenderer;
use ILIAS\UI\Implementation\Render\DefaultRendererFactory;

/**
 * @see IndirectInputContainerContextRenderer for when to use it
 */
class ButtonRendererFactory extends DefaultRendererFactory
{
    /**
     * Allthough there are various Input Containers, only the ones listed below
     * are actually rendered.
     *
     * @see \ILIAS\UI\Component\Input\Container\Container
     */
    protected const array USE_FORM_CONTEXT_RENDERER_FOR_INDIRECT_DESCENDANTS_OF = [
        'StandardViewControlContainerInput',
        'StandardFilterContainerInput',
        'StandardFormContainerInput',
    ];

    public function getRendererInContext(Component $component, array $contexts): ComponentRenderer
    {
        if ($this->isIndirectInputContainerContext($contexts)) {
            return new IndirectInputContainerContextRenderer(
                $this->ui_factory,
                $this->tpl_factory,
                $this->lng,
                $this->js_binding,
                $this->image_path_resolver,
                $this->data_factory,
                $this->help_text_retriever,
                $this->upload_limit_resolver,
            );
        }
        return parent::getRendererInContext($component, $contexts);
    }

    /**
     * @param string[] $contexts_asc canonical names (first to last)
     */
    protected function isIndirectInputContainerContext(array $contexts_asc): bool
    {
        // ensure minimum context size allowing indirect descendants
        $context_size = count($contexts_asc);
        if (3 > $context_size) {
            return false;
        }
        // check if button is direct descendant
        $direct_ancestor = $contexts_asc[$context_size - 2];
        if (in_array($direct_ancestor, self::USE_FORM_CONTEXT_RENDERER_FOR_INDIRECT_DESCENDANTS_OF, true)) {
            return false;
        }
        // check if button is indirect descendant
        $indirect_ancestors = array_splice($contexts_asc, 0, -2);
        return (0 < count(array_intersect($indirect_ancestors, self::USE_FORM_CONTEXT_RENDERER_FOR_INDIRECT_DESCENDANTS_OF)));
    }
}
