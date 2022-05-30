<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Symbol\Icon;

use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Render\Template;

class ButtonContextRenderer extends Renderer
{
    protected function renderLabel(Component\Component $component, Template $tpl) : Template
    {
        return $tpl;
    }
}
