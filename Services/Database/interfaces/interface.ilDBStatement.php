<?php declare(strict_types=1);

/**
 * Interface ilDBStatement
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilDBStatement
{
    /**
     * @param $fetch_mode int Is either ilDBConstants::FETCHMODE_ASSOC OR ilDBConstants::FETCHMODE_OBJECT
     * @return mixed Returns an array in fetchmode assoc and an object in fetchmode object.
     */
    public function fetchRow(int $fetch_mode);

    /**
     * @return mixed
     */
    public function fetch(int $fetch_mode = ilDBConstants::FETCHMODE_ASSOC);

    public function rowCount() : int;

    public function numRows() : int;

    public function fetchObject() : ?stdClass;

    public function fetchAssoc() : ?array;

    public function execute(array $a_data = null) : ilDBStatement;
}
