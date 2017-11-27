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
	 * @throws InvalidArgumentException if array does not contain 'entry_id', 'name', 'value'
	 *
	 * @deprecated We want to get rid of the old array-structure
	 *
	 * @return  \ilBiblAttribute[]
	 */
	public function convertIlBiblAttributesToObjects(array $il_bibl_attributes);


	/**
	 * @param \ilBiblFieldInterface $field
	 * @param int                   $object_id
	 *
	 * @return array
	 */
	public function getPossibleValuesForFieldAndObject(ilBiblFieldInterface $field, $object_id);


	/**
	 * @param \ilBiblEntryInterface $entry
	 *
	 * @return \ilBiblAttributeInterface[]
	 */
	public function getAttributesForEntry(ilBiblEntryInterface $entry);
}