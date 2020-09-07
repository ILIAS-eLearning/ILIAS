<?php

/**
 * Class ilDclTextRecordQueryObject
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclTextRecordQueryObject extends ilDclRecordQueryObject
{
    public function applyCustomSorting(ilDclBaseFieldModel $field, array $all_records_ids, $direction = 'asc')
    {
        $sort_array = array();
        foreach ($all_records_ids as $id) {
            $url_field = ilDclCache::getRecordFieldCache(new ilDclBaseRecordModel($id), $field);
            $sort_array[$id] = $url_field->getSortingValue();
        }
        switch (strtolower($direction)) {
            case 'asc':
                asort($sort_array);
                break;
            case 'desc':
                arsort($sort_array);
                break;
        }

        return array_keys($sort_array);
    }
}
