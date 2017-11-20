<?php

/**
 * Class ilBiblTypeFactory
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblTypeFactory implements ilBiblTypeFactoryInterface {

	/**
	 * @inheritDoc
	 */
	public function getInstanceForType($type) {
		assert(is_int($type));
		switch ($type) {
			case ilBiblTypeFactoryInterface::DATA_TYPE_BIBTEX:
				return new ilBibTex();
			case ilBiblTypeFactoryInterface::DATA_TYPE_RIS:
				return new ilRis();
			default:
				throw new ilException("bibliografic type not found");
		}
	}


	/**
	 * @inheritDoc
	 */
	public function getInstanceForFileName($filename) {
		//return bib for filetype .bibtex:
		if (strtolower(substr($filename, - 6)) == "bibtex"
		    || strtolower(substr($filename, - 3)) == "bib") {
			return $this->getInstanceForType(ilBiblTypeFactoryInterface::DATA_TYPE_BIBTEX);
		}

		//else return its true filetype
		return $this->getInstanceForType(ilBiblTypeFactoryInterface::DATA_TYPE_RIS);
	}


	/**
	 * @inheritDoc
	 */
	public function getInstanceForString($string) {
		switch ($string) {
			case "bib":
				return new ilBibTex();
			case "ris":
				return new ilRis();
			default:
				throw new ilException("bibliografic type not found");
		}
	}
}
