<?php declare(strict_types=1);

/**
 * Interface ilDBReverse
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilDBReverse
{
    public function getTableFieldDefinition(string $table_name, string $field_name) : array;

    public function getTableIndexDefinition(string $table, string $constraint_name) : array;

    public function getTableConstraintDefinition(string $table, string $index_name) : array;

    public function getTriggerDefinition(string $trigger) : array;
}
