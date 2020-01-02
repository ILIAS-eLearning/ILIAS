<?php

/**
 * Interface ilDBManager
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilDBManager
{

    /**
     * @param null $database
     * @return array
     */
    public function listTables($database = null);


    /**
     * @param null $database
     * @return array
     */
    public function listSequences($database = null);


    /**
     * @param $table
     * @param $name
     * @param $definition
     * @return mixed
     */
    public function createConstraint($table, $name, $definition);


    /**
     * @param $table
     * @return mixed
     */
    public function listTableFields($table);


    /**
     * @param $table
     * @return mixed
     */
    public function listTableConstraints($table);


    /**
     * @param $seq_name
     * @param int $start
     * @param array $options
     * @return mixed
     */
    public function createSequence($seq_name, $start = 1, $options = array());


    /**
     * @param $table
     * @return mixed
     */
    public function listTableIndexes($table);


    /**
     * @param $name
     * @param $changes
     * @param $check
     * @return mixed
     */
    public function alterTable($name, $changes, $check);


    /**
     * @param $table
     * @param $name
     * @param $definition
     * @return mixed
     */
    public function createIndex($table, $name, $definition);


    /**
     * @param $table
     * @param $name
     * @return mixed
     */
    public function dropIndex($table, $name);


    /**
     * @param $seq_name
     * @return bool
     */
    public function dropSequence($seq_name);


    /**
     * @param $table
     * @param $name
     * @param bool $primary
     * @return mixed
     */
    public function dropConstraint($table, $name, $primary = false);


    /**
     * @param $name Table-name
     * @return mixed
     */
    public function dropTable($name);

    //
    // NOT YET IMPLEMENTED
    //

    //	/**
    //	 * @param $name
    //	 * @return mixed
    //	 */
    //	public function createDatabase($name);
    //
    //
    //	/**
    //	 * @param $name
    //	 * @return mixed
    //	 */
    //	public function dropDatabase($name);
    //
    //
    //	/**
    //	 * @param $name
    //	 * @param $fields
    //	 * @param array $options
    //	 * @return mixed
    //	 */
    //	public function createTable($name, $fields, $options = array());
    //
    //	/**
    //	 * @return mixed
    //	 */
    //	public function listDatabases();
    //
    //
    //	/**
    //	 * @return mixed
    //	 */
    //	public function listUsers();
    //
    //
    //	/**
    //	 * @return mixed
    //	 */
    //	public function listFunctions();
    //
    //
    //	/**
    //	 * @param null $table
    //	 * @return mixed
    //	 */
    //	public function listTableTriggers($table = null);
    //
    //
    //	/**
    //	 * @param null $database
    //	 * @return mixed
    //	 */
    //	public function listViews($database = null);
    //
    //

    //
}

/**
 * Interface ilDBPdoManagerInterface
 *
 * All these methods are not in MDB 2 will be moved to a seperate interface file
 */
interface ilDBPdoManagerInterface
{

    /**
     * @param $idx
     * @return string
     */
    public function getIndexName($idx);


    /**
     * @param $sqn
     * @return string
     */
    public function getSequenceName($sqn);
}
