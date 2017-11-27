<?php
/**
 * Class ilBiblAttributeFactory
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class ilBiblAttributeFactory implements ilBiblAttributeFactoryInterface {

	/**
	 * @inheritdoc
	 */
	public function convertIlBiblAttributesToObjects(array $il_bibl_attributes) {
		$array_of_objects = [];
		foreach ($il_bibl_attributes as $il_bibl_attribute) {
			if (!is_array($il_bibl_attribute)) {
				throw new InvalidArgumentException('no attributes array passed');
			}
			if (!array_diff_key(array_flip([ 'entry_id', 'name', 'value' ]), $il_bibl_attribute)) {
				throw new InvalidArgumentException("array does not contain 'entry_id', 'name', 'value'");
			}
			$ilBiblAttribute = new ilBiblAttribute();
			$ilBiblAttribute->setEntryId($il_bibl_attribute['entry_id']);
			$ilBiblAttribute->setName($il_bibl_attribute['name']);
			$ilBiblAttribute->setValue($il_bibl_attribute['value']);
			$array_of_objects[] = $ilBiblAttribute;
		}

		return $array_of_objects;
	}
}