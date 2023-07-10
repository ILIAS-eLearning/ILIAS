<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilBiblAttributeFactory
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */
class ilBiblAttributeFactory implements ilBiblAttributeFactoryInterface
{
    protected \ilBiblFieldFactoryInterface $field_factory;
    protected ilDBInterface $db;

    public function __construct(ilBiblFieldFactoryInterface $field_factory)
    {
        global $DIC;
        $this->field_factory = $field_factory;
        $this->db = $DIC->database();
    }

    /**
     * @inheritDoc
     */
    public function getPossibleValuesForFieldAndObject(ilBiblFieldInterface $field, int $object_id): array
    {
        $q = "SELECT DISTINCT(a.value) FROM il_bibl_data AS d
JOIN il_bibl_entry AS e ON e.data_id = d.id
JOIN il_bibl_attribute AS a on a.entry_id = e.id
WHERE a.name = %s AND d.id = %s";

        $res = $this->db->queryF($q, ['text', 'integer'], [
            $field->getIdentifier(),
            $object_id,
        ]);
        $result = [];
        while ($data = $this->db->fetchObject($res)) {
            $result[$data->value] = $data->value;
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getAttributesForEntry(ilBiblEntryInterface $entry): array
    {
        return ilBiblAttribute::where(['entry_id' => $entry->getId()])->get();
    }

    /**
     * @inheritDoc
     */
    public function sortAttributes(array $attributes): array
    {
        $sorted = [];
        $type_id = $this->field_factory->getType()->getId();
        $max = 0;
        foreach ($attributes as $attribute) {
            if (!$attribute->getName()) {
                continue;
            }
            $field = $this->field_factory->findOrCreateFieldByTypeAndIdentifier($type_id, $attribute->getName());
            $position = $field->getPosition();
            $position = $position ?: $max + 1;

            $max = (max($position, $max));
            $sorted[$position] = $attribute;
        }

        ksort($sorted);

        return $sorted;
    }

    /**
     * @inheritDoc
     */
    public function createAttribute(string $name, string $value, int $entry_id): bool
    {
        $ilBiblAttribute = new ilBiblAttribute();
        $ilBiblAttribute->setName($name);
        $ilBiblAttribute->setValue($value);
        $ilBiblAttribute->setEntryId($entry_id);
        $ilBiblAttribute->store();

        $this->field_factory->findOrCreateFieldOfAttribute($ilBiblAttribute);

        return true;
    }
}
