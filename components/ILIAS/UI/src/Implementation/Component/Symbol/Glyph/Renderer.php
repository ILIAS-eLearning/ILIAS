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

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Render\Template;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdocs
     */
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        if (!$component instanceof Glyph) {
            $this->cannotHandleComponent($component);
        }

        $tpl = $this->getTemplate("tpl.glyph.standard.html", true, true);

        if ($component->isHighlighted()) {
            $tpl->touchBlock("highlighted");
        }

        $label = $component->getLabel();
        if ('' !== $label) {
            $tpl->touchBlock('with_aria_label');
            // @todo: move translation to factory, this breaks custom labels...
            $tpl->setVariable("LABEL", $this->txt($label));
            $tpl->touchBlock('with_role');
        } else {
            // glyph must be hidden if there is no label (semantic meaning)
            $tpl->touchBlock('with_aria_hidden');
        }

        $id = $this->bindJavaScript($component);

        if ($id !== null) {
            $tpl->setCurrentBlock("with_id");
            $tpl->setVariable("ID", $id);
            $tpl->parseCurrentBlock();
        }
        $tpl->setVariable("GLYPH", $this->getInnerGlyphHTML($component, $default_renderer));
        return $tpl->get();
    }

    protected function getInnerGlyphHTML(Component\Component $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate('tpl.glyph.html', true, true);

        $tpl->touchBlock($component->getType());

        $largest_counter = 0;
        foreach ($component->getCounters() as $counter) {
            if ($largest_counter < $counter->getNumber()) {
                $largest_counter = $counter->getNumber();
            }
            $n = "counter_" . $counter->getType();
            $tpl->setCurrentBlock($n);
            $tpl->setVariable(strtoupper($n), $default_renderer->render($counter));
            $tpl->parseCurrentBlock();
        }

        if ($largest_counter) {
            $tpl->setCurrentBlock("counter_spacer");
            $tpl->setVariable("COUNTER_SPACER", $largest_counter);
            $tpl->parseCurrentBlock();
        }
        return $tpl->get();
    }
}
