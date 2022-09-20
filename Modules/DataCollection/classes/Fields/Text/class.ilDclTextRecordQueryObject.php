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
 ********************************************************************
 */

/**
 * Class ilDclTextRecordQueryObject
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclTextRecordQueryObject extends ilDclRecordQueryObject
{
    public function applyCustomSorting(ilDclBaseFieldModel $field, array $all_records, $direction = 'asc'): array
    {
        $sort_array = array();
        foreach ($all_records as $id) {
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
