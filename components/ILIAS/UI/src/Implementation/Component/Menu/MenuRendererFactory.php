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

namespace ILIAS\UI\Implementation\Component\Menu;

use ILIAS\UI\Implementation\Render\DefaultRendererFactory;
use ILIAS\UI\Implementation\Render\ComponentRenderer;
use ILIAS\UI\Component\Component;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class MenuRendererFactory extends DefaultRendererFactory
{
    public function getRendererInContext(Component $component, array $contexts): ComponentRenderer
    {
        if (in_array('TreeSelectFieldInput', $contexts, true) ||
            in_array('TreeMultiSelectFieldInput', $contexts, true)
        ) {
            return new FieldContextRenderer(
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
}
