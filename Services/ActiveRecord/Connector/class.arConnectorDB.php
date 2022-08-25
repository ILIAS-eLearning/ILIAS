<?php

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class arConnectorDB
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version 2.0.7
 */
class arConnectorDB extends arConnector
{
    protected function returnDB(): ilDBInterface
    {
        return $GLOBALS['ilDB'];
    }

    public function checkConnection(ActiveRecord $ar): bool
    {
        return is_object($this->returnDB());
    }

    /**
     * @return mixed
     */
    public function nextID(ActiveRecord $ar): int
    {
        return $this->returnDB()->nextId($ar->getConnectorContainerName());
    }

    public function installDatabase(ActiveRecord $ar, array $fields): bool
    {
        $ilDB = $this->returnDB();
        $ilDB->createTable($ar->getConnectorContainerName(), $fields);
        $arFieldList = $ar->getArFieldList();
        if ($arFieldList->getPrimaryField()->getName()) {
            $ilDB->addPrimaryKey($ar->getConnectorContainerName(), array($arFieldList->getPrimaryField()->getName()));
        }
        if (!$ilDB->sequenceExists($ar->getConnectorContainerName()) && $ar->getArFieldList()->getPrimaryField()->getSequence()) {
            $ilDB->createSequence($ar->getConnectorContainerName());
        }
        $this->updateIndices($ar);

        return true;
    }

    public function updateIndices(ActiveRecord $ar): void
    {
        $ilDB = $this->returnDB();
        $arFieldList = $ar->getArFieldList();
        $existing_indices = $ilDB->loadModule('Manager')->listTableIndexes($ar->getConnectorContainerName());

        foreach ($arFieldList->getFields() as $i => $arField) {
            if ($arField->getIndex() === true) {
                if (!in_array($arField->getName(), $existing_indices)) {
                    if (!$ilDB->indexExistsByFields($ar->getConnectorContainerName(), array($arField->getName()))) {
                        $ilDB->addIndex($ar->getConnectorContainerName(), array($arField->getName()), 'i' . $i);
                    }
                }
            }
        }
    }

    public function updateDatabase(ActiveRecord $ar): bool
    {
        $ilDB = $this->returnDB();
        foreach ($ar->getArFieldList()->getFields() as $field) {
            if (!$ilDB->tableColumnExists($ar->getConnectorContainerName(), $field->getName())) {
                $ilDB->addTableColumn(
                    $ar->getConnectorContainerName(),
                    $field->getName(),
                    $field->getAttributesForConnector()
                );
            }
        }
        $this->updateIndices($ar);

        return true;
    }

    public function resetDatabase(ActiveRecord $ar): bool
    {
        $ilDB = $this->returnDB();
        if ($ilDB->tableExists($ar->getConnectorContainerName())) {
            $ilDB->dropTable($ar->getConnectorContainerName());
        }
        $ar->installDB();

        return true;
    }

    public function truncateDatabase(ActiveRecord $ar): bool
    {
        $ilDB = $this->returnDB();
        $query = 'TRUNCATE TABLE ' . $ar->getConnectorContainerName();
        $ilDB->query($query);
        if ($ilDB->tableExists($ar->getConnectorContainerName() . '_seq')) {
            $ilDB->dropSequence($ar->getConnectorContainerName());
            $ilDB->createSequence($ar->getConnectorContainerName());
        }

        return true;
    }

    public function checkTableExists(ActiveRecord $ar): bool
    {
        $ilDB = $this->returnDB();

        /**
         * @TODO: This is the proper ILIAS approach on how to do this BUT: This is exteremely slow (listTables is used)! However, this is not the place to fix this issue. Report.
         */

        return $ilDB->tableExists($ar->getConnectorContainerName());
    }

    public function checkFieldExists(ActiveRecord $ar, string $field_name): bool
    {
        $ilDB = $this->returnDB();

        return $ilDB->tableColumnExists($ar->getConnectorContainerName(), $field_name);
    }

    public function removeField(ActiveRecord $ar, string $field_name): bool
    {
        $ilDB = $this->returnDB();
        if (!$ilDB->tableColumnExists($ar->getConnectorContainerName(), $field_name)) {
            throw new arException($field_name, arException::COLUMN_DOES_NOT_EXIST);
        }
        if ($ilDB->tableColumnExists($ar->getConnectorContainerName(), $field_name)) {
            $ilDB->dropTableColumn($ar->getConnectorContainerName(), $field_name);
        }
        return true;
    }

    public function renameField(ActiveRecord $ar, string $old_name, string $new_name): bool
    {
        $ilDB = $this->returnDB();
        if ($ilDB->tableColumnExists($ar->getConnectorContainerName(), $old_name)) {
            //throw new arException($old_name, arException::COLUMN_DOES_NOT_EXIST);

            if (!$ilDB->tableColumnExists($ar->getConnectorContainerName(), $new_name)) {
                //throw new arException($new_name, arException::COLUMN_DOES_ALREADY_EXIST);
                $ilDB->renameTableColumn($ar->getConnectorContainerName(), $old_name, $new_name);
            }
        }

        return true;
    }

    public function create(ActiveRecord $ar): void
    {
        $ilDB = $this->returnDB();
        $ilDB->insert($ar->getConnectorContainerName(), $ar->getArrayForConnector());
    }

    /**
     * @return mixed[]
     */
    public function read(ActiveRecord $ar): array
    {
        $ilDB = $this->returnDB();

        $query = 'SELECT * FROM ' . $ar->getConnectorContainerName() . ' ' . ' WHERE ' . arFieldCache::getPrimaryFieldName($ar) . ' = '
            . $ilDB->quote($ar->getPrimaryFieldValue(), arFieldCache::getPrimaryFieldType($ar));

        $set = $ilDB->query($query);
        $records = array();
        while ($rec = $ilDB->fetchObject($set)) {
            $records[] = $rec;
        }

        return $records;
    }

    public function update(ActiveRecord $ar): void
    {
        $ilDB = $this->returnDB();

        $ilDB->update($ar->getConnectorContainerName(), $ar->getArrayForConnector(), array(
            arFieldCache::getPrimaryFieldName($ar) => array(
                arFieldCache::getPrimaryFieldType($ar),
                $ar->getPrimaryFieldValue(),
            ),
        ));
    }

    public function delete(ActiveRecord $ar): void
    {
        $ilDB = $this->returnDB();

        $ilDB->manipulate('DELETE FROM ' . $ar->getConnectorContainerName() . ' WHERE ' . arFieldCache::getPrimaryFieldName($ar) . ' = '
            . $ilDB->quote($ar->getPrimaryFieldValue(), arFieldCache::getPrimaryFieldType($ar)));
    }

    /**
     * @return mixed[]
     * @internal param $q
     */
    public function readSet(ActiveRecordList $arl): array
    {
        $ilDB = $this->returnDB();
        $set = $ilDB->query($this->buildQuery($arl));
        $records = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $records[] = $rec;
        }

        return $records;
    }

    public function affectedRows(ActiveRecordList $arl): int
    {
        $ilDB = $this->returnDB();
        $q = $this->buildQuery($arl);

        $set = $ilDB->query($q);

        /** @noinspection PhpParamsInspection */
        return $ilDB->numRows($set);
    }

    /**
     * @return mixed|string
     */
    protected function buildQuery(ActiveRecordList $arl): string
    {
        $method = 'asSQLStatement';

        // SELECTS
        $q = $arl->getArSelectCollection()->{$method}();
        // Concats
        $q .= $arl->getArConcatCollection()->{$method}();
        $q .= ' FROM ' . $arl->getAR()->getConnectorContainerName();
        // JOINS
        $q .= $arl->getArJoinCollection()->{$method}();
        // WHERE
        $q .= $arl->getArWhereCollection()->{$method}();
        // HAVING
        $q .= $arl->getArHavingCollection()->{$method}();
        // ORDER
        $q .= $arl->getArOrderCollection()->{$method}();
        // LIMIT
        $q .= $arl->getArLimitCollection()->{$method}();

        $arl->setLastQuery($q);

        return $q;
    }

    /**
     * @param        $value
     */
    public function quote($value, string $type): string
    {
        $ilDB = $this->returnDB();

        return $ilDB->quote($value, $type);
    }
}
