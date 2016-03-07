<?php

/**
 * Interface ilDBManager
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilDBManager {

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
}
