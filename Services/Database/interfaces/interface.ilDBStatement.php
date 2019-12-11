<?php

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
    public function fetchRow($fetch_mode);


    /**
     * @param int $fetch_mode
     * @return mixed
     */
    public function fetch($fetch_mode = ilDBConstants::FETCHMODE_ASSOC);


    /**
     * @return int
     */
    public function rowCount();


    /**
     * @return int
     */
    public function numRows();


    /**
     * @return stdClass
     */
    public function fetchObject();


    /**
     * @return array
     */
    public function fetchAssoc();


    /**
     * @param array $a_data
     * @return \ilPDOStatement
     */
    public function execute($a_data = null);
}
