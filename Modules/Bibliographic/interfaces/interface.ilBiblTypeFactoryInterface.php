<?php

/**
 * Interface ilBiblTypeFactoryInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblTypeFactoryInterface {

	const DATA_TYPE_BIBTEX = 2;
	const DATA_TYPE_RIS = 1;


	/**
	 * @param int $type
	 *
	 * @throws ilException when type not found
	 *
	 * @return \ilBiblTypeInterface
	 */
	public function getInstanceForType($type);


	/**
	 * @param string $filename
	 *
	 * @throws ilException when type not found
	 *
	 * @return \ilBiblTypeInterface
	 */
	public function getInstanceForFileName($filename);


	/**
	 * @deprecated Legacy
	 *
	 * @param string $string
	 *
	 * @return \ilBiblTypeInterface
	 */
	public function getInstanceForString($string);

	/**
	 * @param string $file_ending
	 *
	 * @throws ilException when no data type for file_ending was found
	 *
	 * @return int
	 */
	public function convertFileEndingToDataType($file_ending);
}
