<?php

/**
 * Class ilBiblFileReaderFactory
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblFileReaderFactory implements ilBiblFileReaderFactoryInterface {

	/**
	 * @inheritDoc
	 */
	public function getByType($type) {
		switch ($type) {
			case ilBiblTypeFactoryInterface::DATA_TYPE_BIBTEX:
				return new ilBiblBiblTexFileReader();
			case ilBiblTypeFactoryInterface::DATA_TYPE_RIS:
				return new ilBiblRisFileReader();
			default:
				throw new ilException("bibliografic type not found");
		}
	}
}
