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

namespace ILIAS\Container\Sorting\Export;

use ilContainer;
use ilXmlWriter;
use ILIAS\Container\Sorting\Settings\Manager as SettingsManager;
use ILIAS\Container\Sorting\Positions\Manager as PositionsManager;

class XMLWriter
{
    public function __construct(
        protected SettingsManager $settings_manager,
        protected PositionsManager $positions_manager
    ) {
    }

    public function writeSorting(
        int $obj_id,
        ilXmlWriter $writer,
    ): void {
        $settings = $this->settings_manager->getSettingsForObject($obj_id);

        $attr = [];
        $attr['direction'] = $settings->getSortDirection() === ilContainer::SORT_DIRECTION_ASC ? "ASC" : "DESC";
        $attr['type'] = match ($settings->getSortMode()) {
            ilContainer::SORT_MANUAL => 'Manual',
            ilContainer::SORT_CREATION => 'Creation',
            ilContainer::SORT_ACTIVATION => 'Activation',
            ilContainer::SORT_INHERIT => 'Inherit',
            default => 'Title'
        };

        if ($settings->getSortMode() !== ilContainer::SORT_MANUAL) {
            $writer->xmlElement('Sort', $attr);
            return;
        }

        $attr['position'] = $settings->getSortNewItemsPosition() === ilContainer::SORT_NEW_ITEMS_POSITION_BOTTOM ? "Bottom" : "Top";
        $attr['order'] = match ($settings->getSortNewItemsOrder()) {
            ilContainer::SORT_NEW_ITEMS_ORDER_ACTIVATION => 'Activation',
            ilContainer::SORT_NEW_ITEMS_ORDER_CREATION => 'Creation',
            default => 'Title'
        };

        $groupings = $this->positions_manager->getPositionsInObject($obj_id);

        $writer->xmlStartTag('Sort', $attr);
        foreach ($groupings as $grouping) {
            $grouping_attr = [
                'parent_id' => $grouping->getParentId(),
                'parent_type' => $grouping->getParentId() ? $grouping->getParentType() : ''
            ];
            $writer->xmlStartTag('Grouping', $grouping_attr);
            foreach ($grouping->getPositions() as $position) {
                $writer->xmlElement('Position', ['child_id' => $position->getChildID()], $position->getPosition());
            }
            $writer->xmlEndTag('Grouping');
        }
        $writer->xmlEndTag('Sort');
    }
}
