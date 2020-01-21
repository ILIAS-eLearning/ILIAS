<?php

/**
 * Class ilDclFormulaFieldModel
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclFormulaFieldModel extends ilDclBaseFieldModel
{

    /**
     * Returns a query-object for building the record-loader-sql-query
     *
     * @return null|ilDclRecordQueryObject
     */
    public function getRecordQuerySortObject($direction = "asc", $sort_by_status = false)
    {
        // use custom record query object for adding custom sorting
        $sql_obj = new ilDclFormulaRecordQueryObject();

        return $sql_obj;
    }


    /**
     * @inheritDoc
     */
    public function getValidFieldProperties()
    {
        return array(ilDclBaseFieldModel::PROP_FORMULA_EXPRESSION);
    }


    /**
     * @return bool
     */
    public function allowFilterInListView()
    {
        return false;
    }
}
