<?php

/**
 * Interface ilBiblEntryInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblEntryInterface {


	/**
	 * @param $attributes
	 */
	public function setAttributes($attributes);


	/**
	 * @return string[]
	 */
	public function getAttributes();


	/**
	 * @param int $id
	 */
	public function setId($id);


	/**
	 * @return int
	 */
	public function getId();
}
