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
	public function getByType($type, ilBiblEntryFactoryInterface $entry_factory, ilBiblFieldFactoryInterface $field_factory) {
		switch ($type) {
			case ilBiblTypeFactoryInterface::DATA_TYPE_BIBTEX:
				return new ilBiblTexFileReader($entry_factory, $field_factory);
			case ilBiblTypeFactoryInterface::DATA_TYPE_RIS:
				return new ilBiblRisFileReader($entry_factory, $field_factory);
			default:
				throw new ilException("bibliografic type not found");
		}
	}
}
