<?php

/**
 * Interface ilDBPdoManagerInterface
 *
 * All these methods are not in MDB 2 will be moved to a seperate interface file
 */
interface ilDBPdoManagerInterface {

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
