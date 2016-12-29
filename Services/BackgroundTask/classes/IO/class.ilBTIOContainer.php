<?php

/**
 * Class ilBTIOContainer
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
class ilBTIOContainer extends ActiveRecord {

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
	 * @var bool
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     1
	 */
	protected $do_cache;
	/**
	 * @var int
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     2
	 */
	protected $cache_duration;
	/**
	 * @var int
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     8
	 */
	protected $input_for_job;
	/**
	 * @var string
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     128
	 */
	protected $IO_type;
	/**
	 * @var string
	 *
	 * @con_has_field  true
	 * @con_fieldtype  clob
	 */
	protected $data;


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
	 * @return bool
	 */
	public function isDoCache() {
		return $this->do_cache;
	}


	/**
	 * @param bool $do_cache
	 */
	public function setDoCache($do_cache) {
		$this->do_cache = $do_cache;
	}


	/**
	 * @return int
	 */
	public function getCacheDuration() {
		return $this->cache_duration;
	}


	/**
	 * @param int $cache_duration
	 */
	public function setCacheDuration($cache_duration) {
		$this->cache_duration = $cache_duration;
	}


	/**
	 * @return int
	 */
	public function getInputForJob() {
		return $this->input_for_job;
	}


	/**
	 * @param int $input_for_job
	 */
	public function setInputForJob($input_for_job) {
		$this->input_for_job = $input_for_job;
	}


	/**
	 * @return string
	 */
	public function getIOType() {
		return $this->IO_type;
	}


	/**
	 * @param string $IO_type
	 */
	public function setIOType($IO_type) {
		$this->IO_type = $IO_type;
	}


	/**
	 * @return string
	 */
	public function getData() {
		return $this->data;
	}


	/**
	 * @param string $data
	 */
	public function setData($data) {
		$this->data = $data;
	}
}