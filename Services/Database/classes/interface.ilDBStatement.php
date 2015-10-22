<?php

interface ilDBStatement {

    /**
     * @param $fetchMode int Is either DB_FETCHMODE_ASSOC OR DB_FETCHMODE_OBJECT
     * @return mixed Returns an array in fetchmode assoc and an object in fetchmode object.
     */
    public function fetchRow($fetchMode);

    /**
     * @param int $fetchMode
     * @return mixed
     */
    function fetch($fetchMode = DB_FETCHMODE_ASSOC);

    /**
     * @return int
     */
    function rowCount();

    /**
     * @return int
     */
    function numRows();

    /**
     * @return stdClass
     */
    function fetchObject();
}