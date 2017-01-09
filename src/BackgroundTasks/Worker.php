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
}
