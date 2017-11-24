<?php
/**
 * Interface ilBiblAttributeFactoryInterface
 *
 * @author Benjamin Seglias   <bs@studer-raimann.ch>
 */

interface ilBiblAttributeFactoryInterface {

	/**
	 * @param array
	 *
	 * @deprecated We want to get rid of the old array-structure
	 *
	 * @return  \ilBiblAttribute[]
	 */
	public function convertIlBiblAttributesToObjects($il_bibl_attributes);
}