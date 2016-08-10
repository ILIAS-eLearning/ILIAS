<?php

/**
 * Class ilTableLockInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilTableLockInterface {

	/**
	 * @param bool $lock_bool
	 * @return ilTableLockInterface
	 */
	public function lockSequence($lock_bool);


	/**
	 * @param string $alias_name
	 * @return ilTableLockInterface
	 */
	public function aliasName($alias_name);
}
