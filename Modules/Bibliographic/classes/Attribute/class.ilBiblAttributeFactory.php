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
			$ilBiblAttribute = new ilBiblAttribute();
			$ilBiblAttribute->setEntryId($il_bibl_attribute['entry_id']);
			$ilBiblAttribute->setName($il_bibl_attribute['name']);
			$ilBiblAttribute->setValue($il_bibl_attribute['value']);
			$array_of_objects[] = $ilBiblAttribute;
		}

		return $array_of_objects;
	}
}