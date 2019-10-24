<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Glyph;

use ILIAS\UI\Implementation\Render;
use ILIAS\UI\Component;

class GlyphRendererFactory extends Render\DefaultRendererFactory
{
    public function getRendererInContext(Component\Component $component, array $contexts)
    {
        if (in_array('BulkyButton', $contexts)) {
            return new ButtonContextRenderer($this->ui_factory, $this->tpl_factory, $this->lng, $this->js_binding);
        }
        return new Renderer($this->ui_factory, $this->tpl_factory, $this->lng, $this->js_binding);
    }
}
