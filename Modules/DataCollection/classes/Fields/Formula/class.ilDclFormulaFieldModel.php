<?php

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
    ) : ilDclFormulaRecordQueryObject {
        // use custom record query object for adding custom sorting
        $sql_obj = new ilDclFormulaRecordQueryObject();

        return $sql_obj;
    }

    public function getValidFieldProperties() : array
    {
        return array(ilDclBaseFieldModel::PROP_FORMULA_EXPRESSION);
    }

    public function allowFilterInListView() : bool
    {
        return false;
    }
}
