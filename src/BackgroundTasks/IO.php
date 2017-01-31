<?php

namespace ILIAS\BackgroundTasks;

/**
 * Interface IO
 *
 * @package ILIAS\BackgroundTasks
 *
 *          The IO as a defined format of data passed between two tasks. IO MUST be serialisable
 *          since it will bes stored in the database or somewhere else
 */
interface IO extends \Serializable {

	/**
	 * @return string Gets a hash for this IO. If two objects are the same the hash must be the
	 *                same! if two objects are different you need to have as view collitions as
	 *                possible.
	 */
	public function getHash();


	/**
	 * @param \ILIAS\BackgroundTasks\IO $other
	 * @return mixed
	 */
	public function equals(IO $other);


	/**
	 * @var string get the Type of the
	 */
	public function getType();
}
