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
use SimpleXMLElement;
use ilImportMapping;
use ILIAS\Container\Sorting\Settings\Manager as SettingsManager;
use ILIAS\Container\Sorting\Positions\Manager as PositionsManager;

class XMLParser
{
    public function __construct(
        protected SettingsManager $settings_manager,
        protected PositionsManager $positions_manager
    ) {
    }

    public function parseSorting(
        int $new_obj_id,
        SimpleXMLElement $sorting,
        ilImportMapping $mapping
    ): void {
        $mode = match ((string) ($sorting['type'] ?? '')) {
            'Manual' => ilContainer::SORT_MANUAL,
            'Creation' => ilContainer::SORT_CREATION,
            'Activation' => ilContainer::SORT_ACTIVATION,
            default => ilContainer::SORT_TITLE
        };
        $direction = match ((string) ($sorting['direction'] ?? '')) {
            'DESC' => ilContainer::SORT_DIRECTION_DESC,
            default => ilContainer::SORT_DIRECTION_ASC
        };
        $position = match((string) ($sorting['position'] ?? '')) {
            'Top' => ilContainer::SORT_NEW_ITEMS_POSITION_TOP,
            default => ilContainer::SORT_NEW_ITEMS_POSITION_BOTTOM
        };
        $order = match ((string) ($sorting['order'] ?? '')) {
            'Creation' => ilContainer::SORT_NEW_ITEMS_ORDER_CREATION,
            'Activation' => ilContainer::SORT_NEW_ITEMS_ORDER_ACTIVATION,
            default => ilContainer::SORT_NEW_ITEMS_ORDER_TITLE
        };
        $this->settings_manager->saveSettingsForObject(
            $new_obj_id,
            $mode,
            $direction,
            $position,
            $order,
        );

        foreach ($sorting->Grouping as $grouping) {
            $old_parent_id = (int) $grouping['parent_id'];
            $new_parent_id = 0;
            if ($old_parent_id !== 0) {
                $new_parent_id = $mapping->getMapping('components/ILIAS/Container', 'objs', (string) $old_parent_id);
            }
            if ($new_parent_id === null) {
                continue;
            }
            $parent_type = (string) $grouping['parent_type'];
            foreach ($grouping->Position as $position) {
                $old_child_id = (int) $position['child_id'];
                $new_child_id = $mapping->getMapping('components/ILIAS/Container', 'refs', (string) $old_child_id);
                if (!$new_child_id) {
                    continue;
                }
                $position = (int) $position;
                $this->positions_manager->savePositionForChild(
                    $new_obj_id,
                    (int) $new_child_id,
                    $position,
                    $parent_type,
                    (int) $new_parent_id
                );
            }
        }
    }
}
