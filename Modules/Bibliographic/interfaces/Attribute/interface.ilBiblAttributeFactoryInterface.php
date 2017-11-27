<?php
/**
 * Interface ilBiblAttributeFactoryInterface
 *
 * @author Benjamin Seglias   <bs@studer-raimann.ch>
 */

interface ilBiblAttributeFactoryInterface {

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


	/**
	 * @param \ilBiblFieldFactoryInterface $fieldFactory
	 * @param \ilBiblAttributeInterface[]  $attributes
	 *
	 * @return \ilBiblAttributeInterface[]
	 */
	public function sortAttributes(ilBiblFieldFactoryInterface $fieldFactory, array $attributes);
}