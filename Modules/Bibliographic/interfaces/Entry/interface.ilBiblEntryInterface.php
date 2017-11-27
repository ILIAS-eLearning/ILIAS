<?php

/**
 * Interface ilBiblEntryInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblEntryInterface {

	public function doCreate();


	public function doRead();


	public function doUpdate();


	public function doDelete();


	/**
	 * @param $attributes
	 */
	public function setAttributes($attributes);


	/**
	 * @return string[]
	 */
	public function getAttributes();


	/**
	 * @param string $overview
	 */
	public function setOverview($overview);


	/**
	 * @param int $bibliographic_obj_id
	 */
	public function setBibliographicObjId($bibliographic_obj_id);


	/**
	 * @return int
	 */
	public function getBibliographicObjId();


	/**
	 * @param int $entry_id
	 */
	public function setEntryId($entry_id);


	/**
	 * @return int
	 */
	public function getEntryId();


	/**
	 * @param string $type
	 */
	public function setType($type);


	/**
	 * @return string
	 */
	public function getType();


	/**
	 * @return string
	 */
	public function getFileType();


	/**
	 * @param string $file_type
	 */
	public function setFileType($file_type);
}
