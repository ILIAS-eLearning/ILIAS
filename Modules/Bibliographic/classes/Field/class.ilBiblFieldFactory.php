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
 * Class ilBiblFieldFactory
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblFieldFactory implements ilBiblFieldFactoryInterface
{
    protected \ilBiblTypeInterface $type;


    /**
     * ilBiblFieldFactory constructor.
     */
    public function __construct(\ilBiblTypeInterface $type)
    {
        $this->type = $type;
    }


    /**
     * @inheritdoc
     */
    public function getType() : ilBiblTypeInterface
    {
        return $this->type;
    }


    /**
     * @inheritDoc
     */
    public function findById(int $id) : ilBiblFieldInterface
    {
        /**
         * @var ilBiblField $inst
         */
        $inst = ilBiblField::findOrFail($id);
        if ($this->type->isStandardField($inst->getIdentifier()) !== $inst->isStandardField()) {
            $inst->setIsStandardField($this->type->isStandardField($inst->getIdentifier()));
            $inst->update();
        }

        return $inst;
    }


    /**
     * @inheritdoc
     */
    public function getFieldByTypeAndIdentifier(int $type, string $identifier) : ilBiblFieldInterface
    {
        $inst = $this->getARInstance($type, $identifier);
        if (!$inst) {
            throw new ilException("bibliografic identifier {$identifier} not found");
        }
    
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $inst;
    }


    /**
     * @inheritdoc
     */
    public function findOrCreateFieldByTypeAndIdentifier(int $type, string $identifier) : ilBiblFieldInterface
    {
        $inst = $this->getARInstance($type, $identifier);
        if ($inst === null) {
            $inst = new ilBiblField();
            $inst->setIdentifier($identifier);
            $inst->setDataType($type);
            $inst->setIsStandardField($this->getType()->isStandardField($identifier));
            $inst->create();
        }
        $inst->setDataType($type);
        $inst->setIdentifier($identifier);
        $inst->setIsStandardField($this->getType()->isStandardField($identifier));
        $inst->update();

        return $inst;
    }


    /**
     * @inheritDoc
     */
    public function getAvailableFieldsForObjId(int $obj_id) : array
    {
        global $DIC;
        $sql
            = "SELECT DISTINCT(il_bibl_attribute.name), il_bibl_data.file_type FROM il_bibl_data
					JOIN il_bibl_entry ON il_bibl_entry.data_id = il_bibl_data.id
					JOIN il_bibl_attribute ON il_bibl_attribute.entry_id = il_bibl_entry.id
				WHERE il_bibl_data.id = %s;";

        $result = $DIC->database()->queryF($sql, ['integer'], [$obj_id]);

        $data = [];
        while ($d = $DIC->database()->fetchObject($result)) {
            $data[] = $this->findOrCreateFieldByTypeAndIdentifier($d->file_type, $d->name);
        }

        return $data;
    }


    /**
     * @inheritDoc
     */
    public function filterAllFieldsForType(ilBiblTypeInterface $type, ilBiblTableQueryInfoInterface $queryInfo = null) : array
    {
        return $this->getCollectionForFilter($type, $queryInfo)->get();
    }


    /**
     * @inheritDoc
     */
    public function filterAllFieldsForTypeAsArray(ilBiblTypeInterface $type, ilBiblTableQueryInfoInterface $queryInfo = null) : array
    {
        return $this->getCollectionForFilter($type, $queryInfo)->getArray();
    }


    /**
     * @inheritDoc
     */
    public function findOrCreateFieldOfAttribute(ilBiblAttributeInterface $attribute) : ilBiblFieldInterface
    {
        $field = ilBiblField::where(['identifier' => $attribute->getName()])->first();
        if ($field === null) {
            $field = new ilBiblField();
            $field->setIdentifier($attribute->getName());
            $field->setDataType($this->type->getId());
            $field->setIsStandardField($this->type->isStandardField($attribute->getName()));
            $field->create();
        } else {
            $field->setDataType($this->type->getId());
            $field->update();
        }

        return $field;
    }


    /**
     * @inheritDoc
     */
    public function forcePosition(ilBiblFieldInterface $field) : int
    {
        global $DIC;
        $tablename = ilBiblField::TABLE_NAME;
        $q = "UPDATE {$tablename} SET position = position + 1 WHERE data_type = %s AND position >= %s;";
        $DIC->database()->manipulateF(
            $q,
            ['integer', 'integer'],
            [
                $field->getDataType(),
                $field->getPosition(),
            ]
        );
        $field->store();
        $DIC->database()->query("SET @i=0");
        $DIC->database()->manipulateF(
            "UPDATE {$tablename} SET position = (@i := @i + 1) WHERE data_type = %s ORDER BY position",
            ['integer'],
            [
                $field->getDataType(),
            ]
        );

        return (int) $field->getPosition();
    }

    // Internal Methods


    /**
     * @param ilBiblFieldInterface $field
     *
     * @return int
     */
    private function getNextFreePosition(ilBiblFieldInterface $field) : int
    {
        global $DIC;
        $tablename = ilBiblField::TABLE_NAME;
        $q = "SELECT MAX(position) + 1 as next_position FROM {$tablename} WHERE data_type = %s;";
        $res = $DIC->database()->queryF($q, ['integer'], [$field->getDataType()]);
        $data = $DIC->database()->fetchObject($res);

        return (int) $data->next_position;
    }


    private function getARInstance(int $type, string $identifier) : ?\ilBiblField
    {
        return ilBiblField::where(["identifier" => $identifier, "data_type" => $type])->first();
    }


    private function getCollectionForFilter(ilBiblTypeInterface $type, ilBiblTableQueryInfoInterface $queryInfo = null) : \ActiveRecordList
    {
        $collection = ilBiblField::getCollection();

        $collection->where(array('data_type' => $type->getId()));

        if ($queryInfo) {
            $sorting_column = $queryInfo->getSortingColumn() ? $queryInfo->getSortingColumn() : null;
            $offset = $queryInfo->getOffset() ? $queryInfo->getOffset() : 0;
            $sorting_direction = $queryInfo->getSortingDirection();
            $limit = $queryInfo->getLimit();
            if ($sorting_column) {
                $collection->orderBy($sorting_column, $sorting_direction);
            }
            $collection->limit($offset, $limit);

            foreach ($queryInfo->getFilters() as $queryFilter) {
                switch ($queryFilter->getFieldName()) {
                    default:
                        $collection->where(array($queryFilter->getFieldName() => $queryFilter->getFieldValue()), $queryFilter->getOperator());
                        break;
                }
            }
        }

        return $collection;
    }
}
