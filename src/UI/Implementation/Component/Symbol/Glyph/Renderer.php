<?php declare(strict_types=1);

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
 
namespace ILIAS\UI\Implementation\Component\Symbol\Glyph;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Render\Template;

class Renderer extends AbstractComponentRenderer
{
    protected function getTemplateFilename() : string
    {
        return "tpl.glyph.standard.html";
    }

    /**
     * @inheritdocs
     */
    public function render(Component\Component $component, RendererInterface $default_renderer) : string
    {
        /**
         * @var $component Glyph
         */
        $this->checkComponent($component);

        $tpl_file = $this->getTemplateFilename();
        $tpl = $this->getTemplate($tpl_file, true, true);

        $tpl = $this->renderAction($component, $tpl);

        if ($component->isHighlighted()) {
            $tpl->touchBlock("highlighted");
        }

        if (!$component->isActive()) {
            $tpl->touchBlock("disabled");

            $tpl->setCurrentBlock("with_aria_disabled");
            $tpl->setVariable("ARIA_DISABLED", "true");
            $tpl->parseCurrentBlock();
        }

        $tpl = $this->renderLabel($component, $tpl);

        $id = $this->bindJavaScript($component);

        if ($id !== null) {
            $tpl->setCurrentBlock("with_id");
            $tpl->setVariable("ID", $id);
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable("GLYPH", $this->getInnerGlyphHTML($component, $default_renderer));
        return $tpl->get();
    }

    protected function renderLabel(Component\Component $component, Template $tpl) : Template
    {
        $tpl->setVariable("LABEL", $this->txt($component->getLabel()));
        return $tpl;
    }

    protected function renderAction(Component\Component $component, Template $tpl) : Template
    {
        $action = $component->getAction();
        if ($component->isActive() && $action !== null) {
            $tpl->setCurrentBlock("with_action");
            $tpl->setVariable("ACTION", $component->getAction());
            $tpl->parseCurrentBlock();
        }
        return $tpl;
    }

    protected function getInnerGlyphHTML(Component\Component $component, RendererInterface $default_renderer) : string
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

    /**
     * @inheritdocs
     */
    protected function getComponentInterfaceName() : array
    {
        return array(Component\Symbol\Glyph\Glyph::class);
    }
}
