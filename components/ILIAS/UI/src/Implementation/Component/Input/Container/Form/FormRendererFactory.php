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

namespace ILIAS\UI\Implementation\Component\Input\Container\Form;

use ILIAS\UI\Implementation\Render;
use ILIAS\UI\Component;

class FormRendererFactory extends Render\DefaultRendererFactory
{
    /** @var string[] */
    public const array FORM_CONTEXTS_WITHOUT_BUTTONS = [
        'StateStatePrompt',
        'RoundTripModal',
    ];

    public function getRendererInContext(Component\Component $component, array $contexts): Render\ComponentRenderer
    {
        if ($this->context_matcher->isDescendantOfAny($contexts, self::FORM_CONTEXTS_WITHOUT_BUTTONS)) {
            return new FormWithoutSubmitButtonsContextRenderer(
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
