<?php
/**
 * Interface ilBiblAttributeFactoryInterface
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

interface ilBiblAttributeFactoryInterface {

	/**
	 * @param array
	 * @deprecated
	 * @return array \ilBiblAttribute
	 */
	public function convertIlBiblAttributesToObjects($il_bibl_attributes);

}