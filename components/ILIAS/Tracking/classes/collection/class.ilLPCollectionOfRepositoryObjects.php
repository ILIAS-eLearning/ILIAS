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

declare(strict_types=0);

use ILIAS\Tracking\DB\Factory as TrackingDBFactory;


class ilLPCollectionOfRepositoryObjects extends ilLPCollection
{
    protected static array $possible_items = [];

    protected ilTree $tree;
    protected ilObjectDefinition $objDefinition;

    public function __construct(int $a_obj_id, int $a_mode)
    {
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        $this->objDefinition = $DIC['objDefinition'];

        parent::__construct($a_obj_id, $a_mode);
    }

    public function getPossibleItems(
        int $a_ref_id,
        bool $a_full_data = false
    ): array {
        $cache_idx = $a_ref_id . "__" . $a_full_data;
        if (!isset(self::$possible_items[$cache_idx])) {
            $all_possible = [];

            if (!$this->tree->isDeleted($a_ref_id)) {
                if (!$a_full_data) {
                    $data = $this->tree->getRbacSubtreeInfo($a_ref_id);
                } else {
                    $node = $this->tree->getNodeData($a_ref_id);
                    $data = $this->tree->getSubTree($node);
                }
                foreach ($data as $node) {
                    if (!$a_full_data) {
                        $item_ref_id = (int) $node['child'];
                    } else {
                        $item_ref_id = (int) $node['ref_id'];
                    }

                    // avoid recursion
                    if ($item_ref_id == $a_ref_id || !$this->validateEntry(
                        $item_ref_id
                    )) {
                        continue;
                    }

                    switch ($node['type']) {
                        case 'sess':
                        case 'exc':
                        case 'fold':
                        case 'grp':
                        case 'sahs':
                        case 'lm':
                        case 'tst':
                        case 'file':
                        case 'mcst':
                        case 'htlm':
                        case 'svy':
                        case "prg":
                        case 'iass':
                        case 'copa':
                        case 'frm':
                        case 'cmix':
                        case 'lti':
                        case 'lso':
                        case 'crsr':
                            if (!$a_full_data) {
                                $all_possible[] = $item_ref_id;
                            } else {
                                $all_possible[$item_ref_id] = array(
                                    'ref_id' => (int) $item_ref_id,
                                    'obj_id' => (int) $node['obj_id'],
                                    'title' => (string) $node['title'],
                                    'description' => (string) $node['description'],
                                    'type' => (string) $node['type']
                                );
                            }
                            break;

                            // repository plugin object?
                        case $this->objDefinition->isPluginTypeName(
                            $node['type']
                        ):
                            $only_active = false;
                            if (!$this->isAssignedEntry($item_ref_id)) {
                                $only_active = true;
                            }
                            if (ilRepositoryObjectPluginSlot::isTypePluginWithLP(
                                $node['type'],
                                $only_active
                            )) {
                                if (!$a_full_data) {
                                    $all_possible[] = $item_ref_id;
                                } else {
                                    $all_possible[$item_ref_id] = array(
                                        'ref_id' => (int) $item_ref_id,
                                        'obj_id' => (int) $node['obj_id'],
                                        'title' => (string) $node['title'],
                                        'description' => (string) $node['description'],
                                        'type' => (string) $node['type']
                                    );
                                }
                            }
                            break;
                    }
                }
            }

            self::$possible_items[$cache_idx] = $all_possible;
        }

        return self::$possible_items[$cache_idx];
    }

    protected function validateEntry(int $a_item_id): bool
    {
        $a_item_type = ilObject::_lookupType($a_item_id, true);
        // this is hardcoded so we do not need to call all ObjectLP types
        if ($a_item_type == 'tst') {
            // Check anonymized
            $item_obj_id = ilObject::_lookupObjId($a_item_id);
            $olp = ilObjectLP::getInstance($item_obj_id);
            if ($olp->isAnonymized()) {
                return false;
            }
        }
        return true;
    }

    public function cloneCollection(int $a_target_id, int $a_copy_id): void
    {
        parent::cloneCollection($a_target_id, $a_copy_id);

        $cwo = ilCopyWizardOptions::_getInstance($a_copy_id);
        $mappings = $cwo->getMappings();

        $target_obj_id = ilObject::_lookupObjId($a_target_id);
        $target_collection = new static($target_obj_id, $this->mode);

        // clone (active) groupings
        foreach ($this->getGroupedItemsForLPStatus() as $grouping_id => $group) {
            $target_item_ids = [];
            foreach ($group["items"] as $item) {
                if (!isset($mappings[$item]) or !$mappings[$item]) {
                    continue;
                }
                $target_item_ids[] = $mappings[$item];
            }

            // grouping - if not only single item left after copy?
            if ($grouping_id && sizeof($target_item_ids) > 1) {
                // should not be larger than group
                $num_obligatory = min(
                    sizeof($target_item_ids),
                    $group["num_obligatory"]
                );

                $target_collection->createNewGrouping(
                    $target_item_ids,
                    $num_obligatory
                );
            } else {
                // #15487 - single items
                foreach ($target_item_ids as $item_id) {
                    $this->addEntry($item_id);
                }
            }
        }
    }

    protected function read(int $a_obj_id): void
    {
        $ref_ids = ilObject::_getAllReferences($a_obj_id);
        $ref_id = end($ref_ids);
        $possible = $this->getPossibleItems($ref_id);
        $collection = $this->tracking_db_factory->lpCollection()->repository()->readLPCollection($a_obj_id);
        $items = [];
        if (is_null($collection)) {
            $this->items = $items;
            return;
        }
        $active_collection = $collection->getSubCollectionOfItemsByActiveStatus(true);
        foreach ($active_collection as $element) {
            if (
                in_array($element->getItemId(), $possible) &&
                $this->validateEntry($element->getItemId())
            ) {
                $items[] = $element->getItemId();
            } else {
                $this->deleteEntry($element->getItemId());
            }
        }
        $this->items = $items;
    }

    protected function addEntry(int $a_item_id): bool
    {
        // only active entries are assigned!
        if (!$this->isAssignedEntry($a_item_id)) {
            // #13278 - because of grouping inactive items may exist
            $this->deleteEntry($a_item_id);
            $element = $this->tracking_db_factory->lpCollection()->element()->lpCollectionElement()
                ->withLPMode($this->mode)
                ->withItemId($a_item_id)
                ->withGroupingId(0)
                ->withNumObligatory(0)
                ->withIsActive(true);
            $collection = $this->tracking_db_factory->lpCollection()->element()->lpCollection($element)
                ->withObjectId($this->obj_id);
            $this->tracking_db_factory->lpCollection()->repository()->writeLPCollection($collection);
            $this->items[] = $a_item_id;
        }
        return true;
    }

    protected function deleteEntry(int $a_item_id): bool
    {
        $this->tracking_db_factory->lpCollection()->repository()->deleteLPCollectionEntryByGroupingId($this->obj_id, $a_item_id, 0);
        return true;
    }

    public static function hasGroupedItems(int $a_obj_id): bool
    {
        global $DIC;
        $tracking_db_factory = new TrackingDBFactory($DIC->database());
        $collection = $tracking_db_factory->lpCollection()->repository()->readLPCollection($a_obj_id);
        if (is_null($collection)) {
            return false;
        }
        return $collection->getGroupingIdsGreaterZero() > 0;
    }

    protected function getNonGroupedItems(array $a_item_ids): array
    {
        $collection = $this->tracking_db_factory->lpCollection()->repository()->readLPCollection($this->obj_id);
        if (is_null($collection)) {
            return $a_item_ids;
        }
        $grouped_item_ids = $collection
            ->getSubCollectionOfItemsByItemIds(...$a_item_ids)
            ->getSubCollectionOfItemsByGroupingIds(...$collection->getGroupingIdsGreaterZero())
            ->getItemIds();
        return array_diff($a_item_ids, $grouped_item_ids);
    }

    protected function getGroupingIds(array $a_item_ids): array
    {
        $collection = $this->tracking_db_factory->lpCollection()->repository()->readLPCollection($this->obj_id);
        if (is_null($collection)) {
            return [];
        }
        return $collection
            ->getSubCollectionOfItemsByItemIds(...$a_item_ids)
            ->getGroupingIdsGreaterZero();
    }

    public function deactivateEntries(array $a_item_ids): void
    {
        parent::deactivateEntries($a_item_ids);

        $grouping_ids = $this->getGroupingIds($a_item_ids);
        if (count($grouping_ids) === 0) {
            return;
        }
        $collection = $this->tracking_db_factory->lpCollection()->repository()->readLPCollection($this->obj_id);
        $adjusted_collection = $collection
            ->getSubCollectionOfItemsByGroupingIds(...$grouping_ids)
            ->withChangedActiveStatusOfAllElements(false);
        $this->tracking_db_factory->lpCollection()->repository()->writeLPCollection($adjusted_collection);
    }

    public function activateEntries(array $a_item_ids): void
    {
        // 44683: only activate non-grouped items via parent
        $non_grouped_ids = $this->getNonGroupedItems($a_item_ids);
        parent::activateEntries($non_grouped_ids);

        $grouping_ids = $this->getGroupingIds($a_item_ids);
        if (count($grouping_ids) === 0) {
            return;
        }
        $collection = $this->tracking_db_factory->lpCollection()->repository()->readLPCollection($this->obj_id);
        $adjusted_collection = $collection
            ->getSubCollectionOfItemsByGroupingIds(...$grouping_ids)
            ->withChangedActiveStatusOfAllElements(true);
        $this->tracking_db_factory->lpCollection()->repository()->writeLPCollection($adjusted_collection);
    }

    public function createNewGrouping(
        array $a_item_ids,
        int $a_num_obligatory = 1
    ): void {
        $this->activateEntries($a_item_ids);
        $grouping_ids = $this->getGroupingIds($a_item_ids);
        $collection = $this->tracking_db_factory->lpCollection()->repository()->readLPCollection($this->obj_id);
        $all_item_ids = array_unique(array_merge(
            $collection->getSubCollectionOfItemsByGroupingIds(...$grouping_ids)->getItemIds(),
            $a_item_ids)
        );
        $this->releaseGrouping($a_item_ids);
        $adjusted_collection = $collection->getSubCollectionOfItemsByItemIds(...$all_item_ids)
            ->withChangedGroupingIdOfAllElements($collection->getMaxGroupingNumber() + 1)
            ->withChangedNumObligatoryIdOfAllElements($a_num_obligatory)
            ->withChangedActiveStatusOfAllElements(true);
        $this->tracking_db_factory->lpCollection()->repository()->writeLPCollection($adjusted_collection);
    }

    public function releaseGrouping(array $a_item_ids): void
    {
        $collection = $this->tracking_db_factory->lpCollection()->repository()->readLPCollection($this->obj_id);
        $grouping_ids = $this->getGroupingIds($a_item_ids);
        $adjusted_collection = $collection->getSubCollectionOfItemsByGroupingIds(...$grouping_ids)
            ->withChangedGroupingIdOfAllElements(0)
            ->withChangedNumObligatoryIdOfAllElements(0);
        $this->tracking_db_factory->lpCollection()->repository()->writeLPCollection($adjusted_collection);
    }

    public function saveObligatoryMaterials(array $a_obl): void
    {
        $collection = $this->tracking_db_factory->lpCollection()->repository()->readLPCollection($this->obj_id);
        foreach ($a_obl as $grouping_id => $num) {
            $col_num = $collection->getSubCollectionOfItemsByGroupingId($grouping_id)->count();
            if ($num <= 0 || $num >= $col_num) {
                throw new UnexpectedValueException();
            }
        }
        foreach ($a_obl as $grouping_id => $num) {
            $adjusted_collection = $collection->getSubCollectionOfItemsByGroupingId($grouping_id)
                ->withChangedNumObligatoryIdOfAllElements($num);
            $this->tracking_db_factory->lpCollection()->repository()->writeLPCollection($adjusted_collection);
        }
    }

    public function getTableGUIData(int $a_parent_ref_id): array
    {
        $items = $this->getPossibleItems($a_parent_ref_id, true);
        $data = [];
        $done = [];
        foreach ($items as $item_id => $item) {
            if (in_array($item_id, $done)) {
                continue;
            }
            $table_item = $this->parseTableGUIItem($item_id, $item);
            // grouping
            $table_item['grouped'] = [];
            $grouped_items = $this->getTableGUItemGroup($item_id);
            if (count((array) ($grouped_items['items'] ?? [])) > 1) {
                foreach ($grouped_items['items'] as $grouped_item_id) {
                    if ($grouped_item_id == $item_id ||
                        !is_array($items[$grouped_item_id] ?? false)) { // #15498
                        continue;
                    }
                    $table_item['grouped'][] = $this->parseTableGUIItem(
                        $grouped_item_id,
                        $items[$grouped_item_id]
                    );
                    $table_item['num_obligatory'] = $grouped_items['num_obligatory'];
                    $table_item['grouping_id'] = $grouped_items['grouping_id'];
                    $done[] = $grouped_item_id;
                }
            }
            $data[] = $table_item;
        }
        return $data;
    }

    protected function parseTableGUIItem(int $a_id, array $a_item): array
    {
        $table_item = $a_item;
        $table_item['id'] = $a_id;
        $table_item['status'] = $this->isAssignedEntry($a_id);
        $olp = ilObjectLP::getInstance($a_item['obj_id']);
        $table_item['mode_id'] = $olp->getCurrentMode();
        $table_item['mode'] = $olp->getModeText($table_item['mode_id']);
        $table_item['anonymized'] = $olp->isAnonymized();
        return $table_item;
    }

    protected function getTableGUItemGroup(int $item_id): array
    {
        $collection = $this->tracking_db_factory->lpCollection()->repository()->readLPCollection($this->obj_id);
        $items = [];
        if (is_null($collection)) {
            return $items;
        }
        $item = $collection->getElementByItemId($item_id);
        if (
            is_null($item) ||
            $item->getGroupingId() <= 0
        ) {
            return $items;
        }
        $items_with_grouping_id = $collection->getSubCollectionOfItemsByGroupingId($item->getGroupingId());
        foreach ($items_with_grouping_id as $item_with_grouping_id) {
            $items['items'][] = $item_with_grouping_id->getItemId();
            $items['num_obligatory'] = $item_with_grouping_id->getNumObligatory();
            $items['grouping_id'] = $item->getGroupingId();
        }
        return $items;
    }

    public function getGroupedItemsForLPStatus(): array
    {
        $items = $this->getItems();
        $collection = $this->tracking_db_factory->lpCollection()->repository()->readLPCollection($this->obj_id);
        if(is_null($collection)) {
            return [];
        }
        $active_items = $collection->getSubCollectionOfActiveItems();
        $grouped = [];
        foreach ($active_items as $item) {
            if (in_array($item->getItemId(), $items)) {
                $grouped[$item->getGroupingId()]['items'][] = $item->getItemId();
                $grouped[$item->getGroupingId()]['num_obligatory'] = $item->getNumObligatory();
            }
        }
        return $grouped;
    }
}
