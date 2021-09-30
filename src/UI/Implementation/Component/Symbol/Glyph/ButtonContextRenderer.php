<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Symbol\Glyph;

use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Render\Template;

class ButtonContextRenderer extends Renderer
{
    protected function getTemplateFilename()
    {
        return "tpl.glyph.context_btn.html";
    }

    protected function renderAction(Component\Component $component, Template $tpl)
    {
        return $tpl;
    }
}
