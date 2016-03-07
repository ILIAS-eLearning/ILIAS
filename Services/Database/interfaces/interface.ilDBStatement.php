<?php

/**
 * Interface ilDBStatement
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilDBStatement {

	/**
	 * @param $fetchMode int Is either ilDBConstants::FETCHMODE_ASSOC OR ilDBConstants::FETCHMODE_OBJECT
	 * @return mixed Returns an array in fetchmode assoc and an object in fetchmode object.
	 */
	public function fetchRow($fetchMode);


	/**
	 * @param int $fetchMode
	 * @return mixed
	 */
	public function fetch($fetchMode = ilDBConstants::FETCHMODE_ASSOC);


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
}