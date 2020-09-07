<?php

/**
 * Interface ilDBReverse
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilDBReverse
{

    /**
     * @param $table_name
     * @param $field_name
     * @return array
     */
    public function getTableFieldDefinition($table_name, $field_name);


    /**
     * @param $table
     * @param $constraint_name
     * @return array
     */
    public function getTableIndexDefinition($table, $constraint_name);


    /**
     * @param $table
     * @param $index_name
     * @return array
     */
    public function getTableConstraintDefinition($table, $index_name);


    /**
     * @param $trigger
     * @return array
     */
    public function getTriggerDefinition($trigger);
}
