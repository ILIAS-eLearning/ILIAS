<?php

class ilBTIOContainer extends ActiveRecord {

	/**
	 * @var int
	 */
	protected $id;
	/**
	 * @var bool
	 */
	protected $do_cache;
	/**
	 * @var int seconds to cache IO
	 */
	protected $cache_duration;
	/**
	 * @var int job_id
	 */
	protected $input_for_job;
	/**
	 * @var string
	 */
	protected $io_type;
	/**
	 * @var string serialized IO data
	 */
	protected $data;
}