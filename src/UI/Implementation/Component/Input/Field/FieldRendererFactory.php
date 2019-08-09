<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Implementation\Render;
use ILIAS\UI\Component;

class FieldRendererFactory extends Render\DefaultRendererFactory
{
    public function getRendererInContext(Component\Component $component, array $contexts)
    {
        if (in_array('StandardFilterContainerInput', $contexts)) {
            return new FilterContextRenderer($this->ui_factory, $this->tpl_factory, $this->lng, $this->js_binding);
        }
        return new Renderer($this->ui_factory, $this->tpl_factory, $this->lng, $this->js_binding);
    }
}
