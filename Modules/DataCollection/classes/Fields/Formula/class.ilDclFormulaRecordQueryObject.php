<?php

/**
 * Class ilDclFormulaRecordQueryObject
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclFormulaRecordQueryObject extends ilDclRecordQueryObject
{
    public function applyCustomSorting(ilDclBaseFieldModel $field, array $all_records_ids, $direction = 'asc')
    {
        $sort_array = array();
        foreach ($all_records_ids as $id) {
            $formula_field = ilDclCache::getRecordFieldCache(new ilDclBaseRecordModel($id), $field);
            $sort_array[$id] = $formula_field->getValue();
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
