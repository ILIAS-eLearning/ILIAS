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

use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Render\Template;

/**
 * Renders buttons inside form / field contexts: implicit submit must be avoided
 * for auxiliary controls (Shy, Bulky, Tag). Primary (and implicit submit) stays unchanged.
 */
class FormContextButtonRenderer extends Renderer
{
    protected function maybeRenderFormButtonTypeAttribute(Template $tpl, Component\Button\Button $component): void
    {
        if ($component instanceof Component\Button\Primary) {
            return;
        }
        if ($component instanceof Component\Button\Shy
            || $component instanceof Component\Button\Bulky
            || $component instanceof Component\Button\Tag
        ) {
            $tpl->touchBlock('with_button_type');
        }
    }
}
