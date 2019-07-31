<?php namespace ILIAS\Data;

use ilObject2;

/**
 * Class ReferenceId
 *
 * @package ILIAS\Data
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class ReferenceId {

	/**
	 * @var string
	 */
	private $ref_id;


	/**
	 * ReferenceId constructor.
	 *
	 * @param int $ref_id
	 */
	public function __construct(int $ref_id) {
		$this->ref_id = $ref_id;
	}


	/**
	 * Get the password-string.
	 *
	 * @return  string
	 */
	public function toInt(): int {
		return (int)$this->ref_id;
	}


	/**
	 * @return ObjectId
	 */
	public function toObjectId(): ObjectId {
		return new ObjectId((int)ilObject2::_lookupObjectId($this->ref_id));
	}
}