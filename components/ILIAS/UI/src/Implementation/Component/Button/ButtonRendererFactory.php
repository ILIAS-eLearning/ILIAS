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
 * Chooses FormContextButtonRenderer when buttons are rendered as descendants of
 * field inputs inside a form (implicit submit must not apply).
 *
 * Context names come from ComponentHelper::getCanonicalName() with spaces removed.
 * Field implementations use a canonical name suffix "FieldInput"; StandardFilterContainerInput is added explicitly.
 *
 * @see FSLoader
 */
class ButtonRendererFactory extends DefaultRendererFactory
{
    private const string FIELD_INPUT_CONTEXT_SUFFIX = 'FieldInput';

    private const string FILTER_CONTAINER_CONTEXT = 'StandardFilterContainerInput';

    public function getRendererInContext(Component $component, array $contexts): ComponentRenderer
    {
        if ($component instanceof ButtonComponent && $this->needsFormAuxiliaryButtonType($contexts)) {
            return new FormContextButtonRenderer(
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
     * @param string[] $contexts
     */
    private function needsFormAuxiliaryButtonType(array $contexts): bool
    {
        foreach ($contexts as $name) {
            if (str_ends_with($name, self::FIELD_INPUT_CONTEXT_SUFFIX)) {
                return true;
            }
            if ($name === self::FILTER_CONTAINER_CONTEXT) {
                return true;
            }
        }
        return false;
    }
}
