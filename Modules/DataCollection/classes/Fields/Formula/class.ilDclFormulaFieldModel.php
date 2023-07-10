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
 * Class ilDclFormulaFieldModel
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclFormulaFieldModel extends ilDclBaseFieldModel
{
    /**
     * Returns a query-object for building the record-loader-sql-query
     */
    public function getRecordQuerySortObject(
        string $direction = "asc",
        bool $sort_by_status = false
    ): ilDclFormulaRecordQueryObject {
        // use custom record query object for adding custom sorting
        $sql_obj = new ilDclFormulaRecordQueryObject();

        return $sql_obj;
    }

    public function getValidFieldProperties(): array
    {
        return array(ilDclBaseFieldModel::PROP_FORMULA_EXPRESSION);
    }

    public function allowFilterInListView(): bool
    {
        return false;
    }
}
