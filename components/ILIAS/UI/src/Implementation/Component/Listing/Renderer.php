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
use ILIAS\UI\Implementation\Render\ResourceRegistry;

/**
 * Class Renderer
 * @package ILIAS\UI\Implementation\Component\Listing\Descriptive
 */
class Renderer extends AbstractComponentRenderer
{
    private const int TRUNCATION_DISPLAY_LIMIT = 2;
    /**
     * @inheritdocs
     */
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        if ($component instanceof Component\Listing\Descriptive) {
            return $this->render_descriptive($component, $default_renderer);
        }

        if ($component instanceof Component\Listing\Property) {
            return $this->renderProperty($component, $default_renderer);
        }

        if ($component instanceof Component\Listing\Listing) {
            return $this->render_simple($component, $default_renderer);
        }

        $this->cannotHandleComponent($component);
    }

    protected function render_descriptive(
        Component\Listing\Descriptive $component,
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

    protected function render_simple(Component\Listing\Listing $component, RendererInterface $default_renderer): string
    {
        $tpl_name = "";

        if ($component instanceof Component\Listing\Ordered) {
            $tpl_name = "tpl.ordered.html";
        }
        if ($component instanceof Component\Listing\Unordered) {
            $tpl_name = "tpl.unordered.html";
        }

        $tpl = $this->getTemplate($tpl_name, true, true);

        if ($component->getItems() === []) {
            return $tpl->get();
        }

        $is_truncated = $component->isTruncated();
        $nr_of_items = 0;
        $additional_elements = [];
        foreach ($component->getItems() as $item) {
            $tpl->setCurrentBlock("item");
            if (!is_string($item)) {
                $item = $default_renderer->render($item);
            }

            if (!$is_truncated || $is_truncated && $nr_of_items < self::TRUNCATION_DISPLAY_LIMIT) {
                $tpl->setVariable("ITEM", $item);
                $tpl->parseCurrentBlock();
                $nr_of_items += 1;
                continue;
            }
            $additional_elements[] = $item;
        }

        $id_attribute = '';
        if ($additional_elements !== []) {
            $component = $component->withAdditionalOnLoadCode(
                fn($id): string => 'il.UI.Listing.initTruncation('
                . "document.querySelector('#{$id}'), "
                . json_encode($additional_elements) . ', '
                . '"' . sprintf($this->txt('show_all_items'), $nr_of_items + count($additional_elements)) . '", '
                . '"' . sprintf($this->txt('hide_items'), count($additional_elements)) . '");'
            );
            $id_attribute = " id='{$this->bindJavaScript($component)}'";
        }
        $tpl->setVariable("ID", $id_attribute);

        return $tpl->get();
    }

    protected function renderProperty(
        Component\Listing\Property $component,
        RendererInterface $default_renderer
    ): string {
        $tpl = $this->getTemplate("tpl.propertylisting.html", true, true);

        foreach ($component->getItems() as $property) {
            list($label, $value, $show_label) = $property;
            if (! is_string($value)) {
                $value = $default_renderer->render($value);
            }

            $tpl->setCurrentBlock("property");
            $tpl->setVariable("VALUE", $value);
            if ($show_label) {
                $tpl->setVariable("LABEL", $label);
            }
            $tpl->parseCurrentBlock();
        }
        return $tpl->get();
    }

    /**
    * @inheritdoc
    */
    public function registerResources(ResourceRegistry $registry): void
    {
        parent::registerResources($registry);
        $registry->register('assets/js/listing.min.js');
    }
}
