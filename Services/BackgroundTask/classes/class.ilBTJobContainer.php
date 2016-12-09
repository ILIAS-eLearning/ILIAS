<?php

/**
 * Class ilBTJobContainer
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
class ilBTJobContainer extends ActiveRecord {

	/**
	 * @var int
	 */
	protected $id;
	/**
	 * @var int
	 */
	protected $next_job_id;
	/**
	 * @var int
	 */
	protected $bucket_id;
	/**
	 * @var string
	 */
	protected $job_type;
}