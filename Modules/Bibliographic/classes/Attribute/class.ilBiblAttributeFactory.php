<?php
/**
 * Class ilBiblAttributeFactory
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class ilBiblAttributeFactory implements ilBiblAttributeFactoryInterface
{

    /**
     * @var \ilBiblFieldFactory
     */
    protected $field_factory;

    public function __construct(ilBiblFieldFactoryInterface $field_factory)
    {
        $this->field_factory = $field_factory;
    }


    /**
     * @inheritDoc
     */
    public function getPossibleValuesForFieldAndObject(ilBiblFieldInterface $field, $object_id)
    {
        global $DIC;
        $q = "SELECT DISTINCT(a.value) FROM il_bibl_data AS d
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
    public function getAttributesForEntry(ilBiblEntryInterface $entry)
    {
        return ilBiblAttribute::where([ 'entry_id' => $entry->getId() ])->get();
    }


    /**
     * @inheritDoc
     */
    public function sortAttributes(array $attributes)
    {
        /**
         * @var $attribute \ilBiblAttributeInterface
         */
        $sorted = [];
        $type_id = $this->field_factory->getType()->getId();
        $max = 0;
        foreach ($attributes as $attribute) {
            if (!$attribute->getName()) {
                continue;
            }
            $field = $this->field_factory->findOrCreateFieldByTypeAndIdentifier($type_id, $attribute->getName());
            $position = (int) $field->getPosition();
            $position = $position ? $position : $max + 1;

            $max = ($position > $max ? $position : $max);
            $sorted[$position] = $attribute;
        }

        ksort($sorted);

        return $sorted;
    }


    /**
     * @inheritDoc
     */
    public function createAttribute($name, $value, $entry_id)
    {
        $ilBiblAttribute = new ilBiblAttribute();
        $ilBiblAttribute->setName($name);
        $ilBiblAttribute->setValue($value);
        $ilBiblAttribute->setEntryId($entry_id);
        $ilBiblAttribute->store();

        $this->field_factory->findOrCreateFieldOfAttribute($ilBiblAttribute);
    }
}
