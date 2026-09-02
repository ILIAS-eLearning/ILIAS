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

use ILIAS\UI\Implementation\Render\DefaultRendererFactory;
use ILIAS\UI\Implementation\Render\ComponentRenderer;
use ILIAS\UI\Component\Component;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ListingRendererFactory extends DefaultRendererFactory
{
    /** @var string[] cannonical names of table components */
    protected const array TABLE_COLUMN_CONTEXTS = [
        'OrderingRowTable',
        'DataRowTable',
    ];

    public function getRendererInContext(Component $component, array $contexts): ComponentRenderer
    {
        if (!empty(array_intersect(self::TABLE_COLUMN_CONTEXTS, $contexts))) {
            return new TableColumnContextRenderer(
                $this->ui_factory,
                $this->tpl_factory,
                $this->lng,
                $this->js_binding,
                $this->image_path_resolver,
                $this->data_factory,
                $this->help_text_retriever,
                $this->upload_limit_resolver,
                $this->refinery,
            );
        }

        return parent::getRendererInContext($component, $contexts);
    }
}
