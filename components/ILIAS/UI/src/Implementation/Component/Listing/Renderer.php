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
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Render\Template;

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
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        if ($component instanceof Descriptive) {
            return $this->renderDescriptiveList($component, $default_renderer);
        }

        if ($component instanceof Property) {
            return $this->renderPropertyList($component, $default_renderer);
        }

        if ($component instanceof Unordered ||
            $component instanceof Ordered ||
            $component instanceof Inline
        ) {
            return $this->renderList($component, $default_renderer);
        }

        $this->cannotHandleComponent($component);
    }

    protected function renderDescriptiveList(
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

    protected function renderList(Unordered|Ordered|Inline $component, RendererInterface $default_renderer): string
    {
        if ($component instanceof Unordered) {
            $tpl_name = "tpl.unordered.html";
        } elseif ($component instanceof Ordered) {
            $tpl_name = "tpl.ordered.html";
        } elseif ($component instanceof Inline) {
            $tpl_name = "tpl.inline.html";
        } else {
            $this->cannotHandleComponent($component);
        }

        $tpl = $this->getTemplate($tpl_name, true, true);

        foreach ($component->getItems() as $item) {
            $tpl->setCurrentBlock("item");
            if (is_string($item)) {
                $tpl->setVariable("ITEM", $item);
            } else {
                $tpl->setVariable("ITEM", $default_renderer->render($item));
            }
            $tpl->parseCurrentBlock();
        }

        $this->bindAndApplyJavaScript($component, $tpl);

        return $tpl->get();
    }

    protected function renderPropertyList(
        Property $component,
        RendererInterface $default_renderer
    ): string {
        $tpl = $this->getTemplate("tpl.propertylisting.html", true, true);

        foreach ($component->getItems() as [$label, $value, $show_label]) {
            $tpl->setCurrentBlock("property");
            if ($show_label) {
                if ($label instanceof Component\Component) {
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
            } elseif ($value instanceof Component\Component) {
                $tpl->setVariable("SHORT_VALUE", $default_renderer->render($value));
            }
            $tpl->parseCurrentBlock();
        }
        return $tpl->get();
    }

    protected function bindAndApplyJavaScript(Component\JavaScriptBindable $component, Template $template): void
    {
        $id = $this->bindJavaScript($component);
        if (null !== $id) {
            $template->setVariable('ID', $id);
        }
    }
}
