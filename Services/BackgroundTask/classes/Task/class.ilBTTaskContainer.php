<?php

/**
 * Class ilBTTaskContainer
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
class ilBTTaskContainer extends ActiveRecord {

	/**
	 * @var int
	 *
	 * @con_is_primary true
	 * @con_is_unique  true
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     8
	 */
	protected $id;
	/**
	 * @var int
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     8
	 */
	protected $next_task_id;
	/**
	 * @var int
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     8
	 */
	protected $bucket_id;
	/**
	 * @var string
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     8
	 */
	protected $job_type;


	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}


	/**
	 * @param int $id
	 */
	public function setId($id) {
		$this->id = $id;
	}


	/**
	 * @return int
	 */
	public function getNextTaskId() {
		return $this->next_task_id;
	}


	/**
	 * @param int $next_task_id
	 */
	public function setNextTaskId($next_task_id) {
		$this->next_task_id = $next_task_id;
	}


	/**
	 * @return int
	 */
	public function getBucketId() {
		return $this->bucket_id;
	}


	/**
	 * @param int $bucket_id
	 */
	public function setBucketId($bucket_id) {
		$this->bucket_id = $bucket_id;
	}


	/**
	 * @return string
	 */
	public function getJobType() {
		return $this->job_type;
	}


	/**
	 * @param string $job_type
	 */
	public function setJobType($job_type) {
		$this->job_type = $job_type;
	}
}