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
 */

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Listing;

use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Implementation\Render\ResourceRegistry;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class TableColumnContextRenderer extends Renderer
{
    protected const int LIST_DISPLAY_LIMIT = 3;

    public function registerResources(ResourceRegistry $registry): void
    {
        $registry->register('assets/js/listing.min.js');
    }

    protected function renderList(Ordered|Unordered|Inline $component, RendererInterface $default_renderer): string
    {
        if ($component instanceof Inline || self::LIST_DISPLAY_LIMIT >= count($component->getItems())) {
            return parent::renderList($component, $default_renderer);
        }

        $template = $this->getTemplate('tpl.table_column_context.html', true, true);
        $template->setVariable('DISPLAY_LIMIT', self::LIST_DISPLAY_LIMIT);
        $template->setVariable('SHOW_MORE_LABEL', $this->txt('show_more'));

        if ($component instanceof Ordered) {
            $template->setVariable('LIST_TYPE', 'ol');
        } else {
            $template->setVariable('LIST_TYPE', 'ul');
        }

        // array_values() ensures we can use $index for count
        foreach (array_values($component->getItems()) as $index => $item) {
            $template->setCurrentBlock("item");
            if (is_string($item)) {
                $template->setVariable("ITEM", $item);
            } else {
                $template->setVariable("ITEM", $default_renderer->render($item));
            }
            if (self::LIST_DISPLAY_LIMIT > $index) {
                $template->setVariable('VISIBILITY', 'visible');
            } else {
                $template->setVariable('VISIBILITY', 'hidden');
            }
            $template->parseCurrentBlock();
        }

        $enriched_component = $component->withAdditionalOnLoadCode(
            static fn($id) => "il.UI.Listing.createExpandableList('$id');",
        );

        $this->bindAndApplyJavaScript($enriched_component, $template);

        $this->toJS('show_more');
        $this->toJS('show_less');

        return $template->get();
    }
}
