<?php
require_once 'Services/Repository/classes/class.ilObjectPlugin.php';

/**
* This class performs all interactions with the database in order to get report-content. Puplic methods may be accessed in 
* in the GUI via $this->object->{method-name}.
*/
class ilObjReportBase extends ilObjectPlugin {
	protected $online;
	protected $gIldb;

	public function __construct($a_ref_id = 0) {
		parent::__construct($a_ref_id);
	}

	abstract public static function getTableTitle();

	abstract public function initType();
	abstract public function doCreate();
	abstract public function doRead();
	abstract public function doDelete();
	abstract public function doClone();

	/**
	* This will be used by the GUI to pass a filter to the object, since the filter is needed for query creation.
	*/
	abstract public function setFilter(catFilter $a_filter);

	/**
	* The sql-query is built by the following methods.
	*/
	protected function queryWhere() {

	}
    
	protected function queryHaving() {

	}
    
	protected function queryOrder() {

	}
    
    /**
    * The following methods perform the query and collect data. 
    * getData returns the results, to be put into the table.
    */
	public function getData() { 

	}

	/**
	* this stores query results to an array
	*/
	protected function fetchData() {

	}

	/**
	* Format query results.
	*/
	protected function transformResultRow($a_row) {
	
	}

	protected function replaceEmpty($a_rec) {

	}

	/**
	* Settings of object getter/setter.
	*/
	public function getSettings() {

	}

	public function setSettings() {

	}
}