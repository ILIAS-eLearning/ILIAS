<?php

/**
 * Interface ilBiblFileReaderFactoryInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblFileReaderFactoryInterface {

	/**
	 * @param $type
	 *
	 * @return \ilBiblFileReaderInterface
	 */
	public function getByType($type);
}