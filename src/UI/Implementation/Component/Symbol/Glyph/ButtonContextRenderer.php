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

use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Render\Template;

class ButtonContextRenderer extends Renderer
{
    protected function getTemplateFilename(): string
    {
        return "tpl.glyph.context_btn.html";
    }

    protected function renderAction(Component\Component $component, Template $tpl): Template
    {
        return $tpl;
    }

    protected function renderLabel(Component\Component $component, Template $tpl): Template
    {
        $aria_label = "";
        foreach ($component->getCounters() as $counter) {
            if($counter->getNumber() > 0) {
                $aria_label .= $this->txt("counter_".$counter->getType()). " ".$counter->getNumber(). "; ";
            }
        }

        if($aria_label != "") {
            $tpl->setVariable("LABEL", $aria_label);
        }

        return $tpl;
    }
}
