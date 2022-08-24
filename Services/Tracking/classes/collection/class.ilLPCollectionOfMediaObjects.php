<?php

declare(strict_types=0);
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * LP collection of media objects
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesTracking
 */
class ilLPCollectionOfMediaObjects extends ilLPCollection
{
    protected static array $possible_items = array();

    public function getPossibleItems(): array
    {
        if (!isset(self::$possible_items[$this->obj_id])) {
            $items = array();

            $cast = new ilObjMediaCast($this->obj_id, false);

            foreach ($cast->getSortedItemsArray() as $item) {
                $items[$item["mob_id"]] = array("title" => $item["title"]);
            }
            self::$possible_items[$this->obj_id] = $items;
        }
        return self::$possible_items[$this->obj_id];
    }

    public function getTableGUIData(int $a_parent_ref_id): array
    {
        $data = array();

        foreach ($this->getPossibleItems() as $mob_id => $item) {
            $tmp = array();
            $tmp['id'] = $mob_id;
            $tmp['ref_id'] = 0;
            $tmp['type'] = 'mob';
            $tmp['title'] = $item['title'];
            $tmp['status'] = $this->isAssignedEntry($mob_id);

            $data[] = $tmp;
        }

        return $data;
    }

    /**
     * Scorm items are not copied, they are newly created by reading the manifest.
     * Therefore, they do not have a mapping. So we need to map them via the import_id/identifierref
     * @param int $a_target_id
     * @param int $a_copy_id
     */
    public function cloneCollection(int $a_target_id, int $a_copy_id): void
    {
        $target_obj_id = ilObject::_lookupObjId($a_target_id);
        $new_collection = new static($target_obj_id, $this->mode);
        $possible_items = $new_collection->getPossibleItems();
        foreach ($this->items as $item_id) {
            if (isset($mob_mapping[$item_id]) && isset($possible_items[$mob_mapping[$item_id]])) {
                $new_collection->addEntry($mob_mapping[$item_id]);
            }
        }
    }
}
