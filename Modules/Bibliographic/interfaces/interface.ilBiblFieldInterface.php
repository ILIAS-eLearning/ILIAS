<?php

/**
 * Interface ilBiblEntryInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblFieldInterface {

	/**
	 * @return int
	 */
	public function getId();


	/**
	 * @param int $id
	 */
	public function setId($id);


	/**
	 * @return string
	 */
	public function getIdentifier();


	/**
	 * @param string $identifier
	 */
	public function setIdentifier($identifier);


	/**
	 * @return int
	 */
	public function getPosition();


	/**
	 * @param int $position
	 */
	public function setPosition($position);


	/**
	 * @return int
	 */
	public function getIsStandardField();


	/**
	 * @param int $is_standard_field
	 */
	public function setIsStandardField($is_standard_field);


	/**
	 * @return int
	 */
	public function getDataType();


	/**
	 * @param int $data_type
	 */
	public function setDataType($data_type);
}
