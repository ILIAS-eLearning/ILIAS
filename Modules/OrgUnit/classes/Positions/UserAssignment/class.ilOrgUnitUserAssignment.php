<?php

/**
 * Class ilOrgUnitUserAssignment
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitUserAssignment extends \ActiveRecord {

	/**
	 * @return string
	 */
	public static function returnDbTableName() {
		return 'il_orgu_ua';
	}


	/**
	 * @var int
	 *
	 * @con_is_primary true
	 * @con_is_unique  true
	 * @con_sequence   true
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     8
	 */
	protected $id = 0;
	/**
	 * @var int
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     8
	 */
	protected $user_id = 0;
	/**
	 * @var int
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     8
	 */
	protected $position_id = 0;
	/**
	 * @var int
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     8
	 */
	protected $orgu_id = 0;


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
	public function getUserId() {
		return $this->user_id;
	}


	/**
	 * @param int $user_id
	 */
	public function setUserId($user_id) {
		$this->user_id = $user_id;
	}


	/**
	 * @return int
	 */
	public function getPositionId() {
		return $this->position_id;
	}


	/**
	 * @param int $position_id
	 */
	public function setPositionId($position_id) {
		$this->position_id = $position_id;
	}


	/**
	 * @return int
	 */
	public function getOrguId() {
		return $this->orgu_id;
	}


	/**
	 * @param int $orgu_id
	 */
	public function setOrguId($orgu_id) {
		$this->orgu_id = $orgu_id;
	}
}
