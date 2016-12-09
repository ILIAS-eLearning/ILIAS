<?php

/**
 * Class ilBTBUcketContainer
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
class ilBTBUcketContainer {

	/**
	 * @var int
	 */
	protected $bucket_id;
	/**
	 * @var ilBTIO
	 */
	protected $current_input;
	/**
	 * @var ilBTJob
	 */
	protected $current_job;
}