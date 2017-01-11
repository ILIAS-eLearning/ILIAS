<?php

namespace ILIAS\BackgroundTasks;

/**
 * Interface Worker
 *
 * @package ILIAS\BackgroundTasks
 */
interface Worker {

	/**
	 * @return void
	 */
	public function doWork();

	/**
	 * Returns true iff the worker wants to be called within the current HTTP request.
	 *
	 * @return boolean
	 */
	public function isSynchronised();
}
