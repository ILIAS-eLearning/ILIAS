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

namespace ILIAS\UI\Implementation\Component\Listing;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\Template;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component\Component;

/**
 * Class Renderer
 * @package ILIAS\UI\Implementation\Component\Listing\Descriptive
 */
class Renderer extends AbstractComponentRenderer
{
    /** @var int amount of characters that fits into one line on desktop. */
    protected const MAX_CHARS_IN_LINE = 260;

    /**
     * @inheritdocs
     */
    public function render(Component $component, RendererInterface $default_renderer): string
    {
        if ($component instanceof Descriptive) {
            return $this->renderDescriptive($component, $default_renderer);
        }
        if ($component instanceof Property) {
            return $this->renderProperty($component, $default_renderer);
        }
        if ($component instanceof Ordered) {
            return $this->renderOrdered($component, $default_renderer);
        }
        if ($component instanceof Unordered) {
            return $this->renderUnordered($component, $default_renderer);
        }
        if ($component instanceof Inline) {
            return $this->renderInline($component, $default_renderer);
        }

        $this->cannotHandleComponent($component);
    }

    protected function renderDescriptive(
        Descriptive $component,
        RendererInterface $default_renderer
    ): string {
        $tpl = $this->getTemplate("tpl.descriptive.html", true, true);

        foreach ($component->getItems() as $key => $item) {
            if (is_string($item)) {
                $content = $item;
            } else {
                $content = $default_renderer->render($item);
            }

            if (trim($content) != "") {
                $tpl->setCurrentBlock("item");
                $tpl->setVariable("DESCRIPTION", $key);
                $tpl->setVariable("CONTENT", $content);
                $tpl->parseCurrentBlock();
            }
        }
        return $tpl->get();
    }

    protected function renderOrdered(Ordered $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.ordered.html", true, true);

        $tpl = $this->fillItems($tpl, $component, $default_renderer);

        return $tpl->get();
    }

    protected function renderUnordered(Unordered $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.unordered.html", true, true);

        $tpl = $this->fillItems($tpl, $component, $default_renderer);

        return $tpl->get();
    }

    protected function renderInline(Inline $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.inline.html", true, true);

        $tpl = $this->fillItems($tpl, $component, $default_renderer);

        return $tpl->get();
    }

    protected function fillItems(Template $tpl, Listing $component, RendererInterface $default_renderer): Template
    {
        $items = $component->getItems();

        foreach ($items as $item) {
            $tpl->setCurrentBlock("item");
            if ($item instanceof Component) {
                $tpl->setVariable("ITEM", $default_renderer->render($item));
            } else {
                $tpl->setVariable("ITEM", $item);
            }
            $tpl->parseCurrentBlock();
        }

        return $tpl;
    }

    protected function renderProperty(
        Property $component,
        RendererInterface $default_renderer
    ): string {
        $tpl = $this->getTemplate("tpl.propertylisting.html", true, true);

        foreach ($component->getItems() as [$label, $value, $show_label]) {
            $tpl->setCurrentBlock("property");
            if ($show_label) {
                if ($label instanceof Component) {
                    $tpl->setVariable('LABEL', $default_renderer->render($label));
                } else {
                    $tpl->setVariable('LABEL', $this->convertSpecialCharacters($label));
                }
            }
            if (is_string($value) && self::MAX_CHARS_IN_LINE <= mb_strlen($value)) {
                $tpl->setVariable("ID_SHOW_MORE_TOGGLE", $this->createId());
                $tpl->setVariable("MORE", $this->txt("show_more"));
                $tpl->setVariable("LESS", $this->txt("show_less"));
                $tpl->setVariable("LONG_VALUE", $this->convertSpecialCharacters($value));
                $tpl->parseCurrentBlock();
            } elseif (is_string($value)) {
                $tpl->setVariable("SHORT_VALUE", $this->convertSpecialCharacters($value));
            } elseif ($value instanceof Component) {
                $tpl->setVariable("SHORT_VALUE", $default_renderer->render($value));
            }
            $tpl->parseCurrentBlock();
        }
        return $tpl->get();
    }
}
