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


	/**
	 * @inheritDoc
	 */
	public function getPossibleValuesForFieldAndObject(ilBiblFieldInterface $field, $object_id) {
		global $DIC;
		$q = "SELECT DISTINCT(a.value) FROM ilias.il_bibl_data AS d
JOIN il_bibl_entry AS e ON e.data_id = d.id
JOIN il_bibl_attribute AS a on a.entry_id = e.id
WHERE a.name = %s AND d.id = %s";

		$res = $DIC->database()->queryF($q, [ 'text', 'integer' ], [
			$field->getIdentifier(),
			$object_id,
		]);
		$result = [];
		while ($data = $DIC->database()->fetchObject($res)) {
			$result[$data->value] = $data->value;
		}

		return $result;
	}


	/**
	 * @inheritDoc
	 */
	public function getAttributesForEntry(ilBiblEntryInterface $entry) {
		return ilBiblAttribute::where([ 'entry_id' => $entry->getEntryId() ])->get();
	}


	/**
	 * @inheritDoc
	 */
	public function sortAttributes(ilBiblFieldFactoryInterface $fieldFactory, array $attributes) {
		/**
		 * @var $attribute \ilBiblAttributeInterface
		 */
		$sorted = [];
		$type_id = $fieldFactory->getType()->getId();
		$max = 0;
		foreach ($attributes as $attribute) {
			$field = $fieldFactory->findOrCreateFieldByTypeAndIdentifier($type_id, $attribute->getName());
			$position = (int)$field->getPosition();
			$position = $position ? $position : $max + 1;

			$max = ($position > $max ? $position : $max);
			$sorted[$position] = $attribute;
		}

		ksort($sorted);

		return $sorted;
	}
}