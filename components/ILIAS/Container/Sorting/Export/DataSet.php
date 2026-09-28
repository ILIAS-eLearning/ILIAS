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

use ilDataSet;
use ilDBConstants;
use ILIAS\Container\Sorting\Settings\Manager as SettingsManager;
use ILIAS\Container\Sorting\Positions\Manager as PositionsManager;
use ilImportMapping;
use ilContainer;

class DataSet extends ilDataSet
{
    public function __construct(
        protected SettingsManager $settings_manager,
        protected PositionsManager $positions_manager
    ) {
        parent::__construct();
    }

    public function getSupportedVersions(): array
    {
        return ['12.0'];
    }

    protected function getTypes(string $a_entity, string $a_version): array
    {
        if ($a_entity === 'sorting_settings') {
            switch ($a_version) {
                case '12.0':
                    return [
                        'ObjId' => 'integer',
                        'SortMode' => 'int',
                        'SortDirection' => 'int',
                        'NewItemsPosition' => 'int',
                        'NewItemsOrder' => 'int'
                    ];
                default:
                    return [];
            }
        } elseif ($a_entity === 'sorting') {
            switch ($a_version) {
                case '12.0':
                    return [
                        'ObjId' => 'integer',
                        'ChildId' => 'integer',
                        'Position' => 'integer',
                        'ParentType' => 'string',
                        'ParentId' => 'integer'
                    ];
                default:
                    return [];
            }
        }
        return [];
    }

    protected function getXmlNamespace(string $a_entity, string $a_schema_version): string
    {
        return "https://www.ilias.de/xml/Components/Container/" . $a_entity;
    }

    public function readData(string $a_entity, string $a_version, array $a_ids): void
    {
        if ($a_entity === 'sorting_settings') {
            switch ($a_version) {
                case '12.0':
                    foreach ($a_ids as $id) {
                        $settings = $this->settings_manager->getSettingsForObject((int) $id);
                        $this->data[] = [
                            'ObjId' => $settings->getObjId(),
                            'SortMode' => $settings->getSortMode(),
                            'SortDirection' => $settings->getSortDirection(),
                            'NewItemsPosition' => $settings->getSortNewItemsPosition(),
                            'NewItemsOrder' => $settings->getSortNewItemsOrder(),
                        ];
                    }
                    break;
            }
        } elseif ($a_entity === 'sorting') {
            switch ($a_version) {
                case '12.0':
                    foreach ($a_ids as $id) {
                        $groupings = $this->positions_manager->getPositionsInObject((int) $id);
                        foreach ($groupings as $grouping) {
                            foreach ($grouping->getPositions() as $position) {
                                $this->data[] = [
                                    'ObjId' => $grouping->getObjId(),
                                    'ChildId' => $position->getChildID(),
                                    'Position' => $position->getPosition(),
                                    'ParentType' => $grouping->getParentType(),
                                    'ParentId' => $grouping->getParentId(),
                                ];
                            }
                        }
                    }
            }
        }
    }

    public function importRecord(
        string $a_entity,
        array $a_types,
        array $a_rec,
        ilImportMapping $a_mapping,
        string $a_schema_version
    ): void {
        switch ($a_entity) {
            case 'sorting_settings':
                $new_obj_id = (int) $a_mapping->getMapping('components/ILIAS/Container', 'objs', $a_rec['ObjId']);
                if ($new_obj_id === 0) {
                    return;
                }
                $this->settings_manager->saveSettingsForObject(
                    $new_obj_id,
                    (int) ($a_rec['SortMode'] ?? ilContainer::SORT_TITLE),
                    (int) ($a_rec['SortDirection'] ?? ilContainer::SORT_DIRECTION_ASC),
                    (int) ($a_rec['NewItemsPosition'] ?? ilContainer::SORT_NEW_ITEMS_POSITION_BOTTOM),
                    (int) ($a_rec['NewItemsOrder'] ?? ilContainer::SORT_NEW_ITEMS_ORDER_TITLE)
                );
                return;

            case 'sorting':
                $new_obj_id = (int) $a_mapping->getMapping('components/ILIAS/Container', 'objs', (string) $a_rec['ObjId']);
                if ($new_obj_id === 0) {
                    return;
                }
                $new_child_id = (int) $a_mapping->getMapping('components/ILIAS/Container', 'refs', (string) $a_rec['ChildId']);
                if ($new_child_id === 0) {
                    return;
                }

                $old_parent_id = (int) $a_rec['ParentId'];
                $new_parent_id = 0;
                if ($old_parent_id !== 0) {
                    $new_parent_id = (int) $a_mapping->getMapping('components/ILIAS/Container', 'objs', (string) $old_parent_id);
                }

                $this->positions_manager->savePositionForChild(
                    $new_obj_id,
                    $new_child_id,
                    (int) ($a_rec['Position'] ?? 0),
                    (string) ($a_rec['ParentType'] ?? ''),
                    $new_parent_id
                );
                return;
        }
    }
}
