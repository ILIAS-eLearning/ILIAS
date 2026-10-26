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

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Implementation\Render;
use ILIAS\UI\Component;

class FieldRendererFactory extends Render\DefaultRendererFactory
{
    /** @var string[] */
    protected const array FILTER_CONTEXT = ['StandardFilterContainerInput'];
    /** @var string[] */
    protected const array FILTER_DURATION_CONTEXT = ['StandardFilterContainerInput', 'DurationFieldInput'];

    public function getRendererInContext(Component\Component $component, array $contexts): Render\ComponentRenderer
    {
        if ($component instanceof DateTime && $this->context_matcher->isDirectDescendantOf($contexts, self::FILTER_DURATION_CONTEXT)) {
            return new FilterDurationContextRenderer(
                $this->ui_factory,
                $this->tpl_factory,
                $this->lng,
                $this->js_binding,
                $this->image_path_resolver,
                $this->data_factory,
                $this->help_text_retriever,
                $this->upload_limit_resolver,
                $this->refinery,
            );
        }
        if ($this->context_matcher->isDescendantOfAny($contexts, self::FILTER_CONTEXT)) {
            return new FilterContextRenderer(
                $this->ui_factory,
                $this->tpl_factory,
                $this->lng,
                $this->js_binding,
                $this->image_path_resolver,
                $this->data_factory,
                $this->help_text_retriever,
                $this->upload_limit_resolver,
                $this->refinery,
            );
        }
        return parent::getRendererInContext($component, $contexts);
    }
}
