<?php

/**
 * Interface ilBiblTypeInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblTypeInterface {

	/**
	 * @param string $identifier
	 *
	 * @return bool
	 */
	public function isStandardField($identifier);


	/**
	 * @param string $identifier
	 *
	 * @return bool
	 */
	public function isEntryType($identifier);


	/**
	 * @return string such as "ris" or "bib"
	 */
	public function getStringRepresentation();


	/**
	 * @return int ID, see ilBiblTypeFactoryInterface::DATA_TYPE_BIBTEX or
	 *             DATA_TYPE_BIBTEX::DATA_TYPE_RIS
	 */
	public function getId();
}
