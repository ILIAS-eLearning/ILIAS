<?php

// namespace ILIAS\Modules\OrgUnit\Positions\Authorities;

/**
 * Class ilOrguAuthority
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrguAuthority extends \ActiveRecord {

	const EVERYONE = - 1;
	const DEPTH_SAME_ORGU = 1;
	const DEPTH_SUBSEQUENT_ORGUS = 2;


	/**
	 * @return string
	 */
	public static function returnDbTableName() {
		return "il_orgu_authority";
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
	 * @con_length     1
	 */
	protected $over = self::EVERYONE;
	/**
	 * @var int
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     1
	 */
	protected $depth = self::DEPTH_SAME_ORGU;
	/**
	 * @var int
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     1
	 */
	protected $position = 0;

	/**
	 * @return string
	 */
	public function __toString() {
		return "HELLOOOO";
	}


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
	public function getOver() {
		return $this->over;
	}


	/**
	 * @param int $over
	 */
	public function setOver($over) {
		$this->over = $over;
	}


	/**
	 * @return int
	 */
	public function getDepth() {
		return $this->depth;
	}


	/**
	 * @param int $depth
	 */
	public function setDepth($depth) {
		$this->depth = $depth;
	}


	/**
	 * @return int
	 */
	public function getPosition() {
		return $this->position;
	}


	/**
	 * @param int $position
	 */
	public function setPosition($position) {
		$this->position = $position;
	}
}
