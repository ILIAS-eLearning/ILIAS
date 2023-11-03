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
 * Class arConnectorDB
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version 2.0.7
 */
class arConnectorDB extends arConnector
{
    private ?ilDBInterface $db = null;

    public function __construct(?ilDBInterface $ilDB = null)
    {
        if (is_null($ilDB)) {
            global $DIC;

            $this->db = $GLOBALS['ilDB'] ?? $DIC['ilDB'] ?? null;
        } else {
            $this->db = $ilDB;
        }
    }

    protected function returnDB(): ilDBInterface
    {
        if (is_null($this->db)) {
            throw new arException("No DB-Connection available");
        }
        return $this->db;
    }

    public function checkConnection(ActiveRecord $activeRecord): bool
    {
        return is_object($this->returnDB());
    }

    /**
     * @return mixed
     */
    public function nextID(ActiveRecord $activeRecord): int
    {
        return $this->returnDB()->nextId($activeRecord->getConnectorContainerName());
    }

    public function installDatabase(ActiveRecord $activeRecord, array $fields): bool
    {
        $ilDB = $this->returnDB();
        $ilDB->createTable($activeRecord->getConnectorContainerName(), $fields);
        $arFieldList = $activeRecord->getArFieldList();
        if ($arFieldList->getPrimaryField()->getName() !== '' && $arFieldList->getPrimaryField()->getName() !== '0') {
            $ilDB->addPrimaryKey(
                $activeRecord->getConnectorContainerName(),
                [$arFieldList->getPrimaryField()->getName()]
            );
        }
        if (!$ilDB->sequenceExists($activeRecord->getConnectorContainerName()) && $activeRecord->getArFieldList(
        )->getPrimaryField()->getSequence()) {
            $ilDB->createSequence($activeRecord->getConnectorContainerName());
        }
        $this->updateIndices($activeRecord);

        return true;
    }

    public function updateIndices(ActiveRecord $activeRecord): void
    {
        $ilDB = $this->returnDB();
        $arFieldList = $activeRecord->getArFieldList();
        $existing_indices = $ilDB->loadModule('Manager')->listTableIndexes($activeRecord->getConnectorContainerName());

        foreach ($arFieldList->getFields() as $i => $arField) {
            if (!$arField->getIndex()) {
                continue;
            }
            if (in_array($arField->getName(), $existing_indices)) {
                continue;
            }
            if ($ilDB->indexExistsByFields($activeRecord->getConnectorContainerName(), [$arField->getName()])) {
                continue;
            }
            $ilDB->addIndex($activeRecord->getConnectorContainerName(), [$arField->getName()], 'i' . $i);
        }
    }

    public function updateDatabase(ActiveRecord $activeRecord): bool
    {
        $ilDB = $this->returnDB();
        foreach ($activeRecord->getArFieldList()->getFields() as $arField) {
            if (!$ilDB->tableColumnExists($activeRecord->getConnectorContainerName(), $arField->getName())) {
                $ilDB->addTableColumn(
                    $activeRecord->getConnectorContainerName(),
                    $arField->getName(),
                    $arField->getAttributesForConnector()
                );
            }
        }
        $this->updateIndices($activeRecord);

        return true;
    }

    public function resetDatabase(ActiveRecord $activeRecord): bool
    {
        $ilDB = $this->returnDB();
        if ($ilDB->tableExists($activeRecord->getConnectorContainerName())) {
            $ilDB->dropTable($activeRecord->getConnectorContainerName());
        }
        $activeRecord->installDB();

        return true;
    }

    public function truncateDatabase(ActiveRecord $activeRecord): bool
    {
        $ilDB = $this->returnDB();
        $query = 'TRUNCATE TABLE ' . $activeRecord->getConnectorContainerName();
        $ilDB->query($query);
        if ($ilDB->tableExists($activeRecord->getConnectorContainerName() . '_seq')) {
            $ilDB->dropSequence($activeRecord->getConnectorContainerName());
            $ilDB->createSequence($activeRecord->getConnectorContainerName());
        }

        return true;
    }

    public function checkTableExists(ActiveRecord $activeRecord): bool
    {
        $ilDB = $this->returnDB();

        /**
         * @TODO: This is the proper ILIAS approach on how to do this BUT: This is exteremely slow (listTables is used)! However, this is not the place to fix this issue. Report.
         */

        return $ilDB->tableExists($activeRecord->getConnectorContainerName());
    }

    public function checkFieldExists(ActiveRecord $activeRecord, string $field_name): bool
    {
        $ilDB = $this->returnDB();

        return $ilDB->tableColumnExists($activeRecord->getConnectorContainerName(), $field_name);
    }

    public function removeField(ActiveRecord $activeRecord, string $field_name): bool
    {
        $ilDB = $this->returnDB();
        if (!$ilDB->tableColumnExists($activeRecord->getConnectorContainerName(), $field_name)) {
            throw new arException($field_name, arException::COLUMN_DOES_NOT_EXIST);
        }
        $ilDB->dropTableColumn($activeRecord->getConnectorContainerName(), $field_name);
        return true;
    }

    public function renameField(ActiveRecord $activeRecord, string $old_name, string $new_name): bool
    {
        $ilDB = $this->returnDB();
        //throw new arException($old_name, arException::COLUMN_DOES_NOT_EXIST);
        if (!$ilDB->tableColumnExists($activeRecord->getConnectorContainerName(), $old_name)) {
            return true;
        }
        if ($ilDB->tableColumnExists($activeRecord->getConnectorContainerName(), $new_name)) {
            return true;
        }
        //throw new arException($new_name, arException::COLUMN_DOES_ALREADY_EXIST);
        $ilDB->renameTableColumn($activeRecord->getConnectorContainerName(), $old_name, $new_name);
        return true;
    }

    public function create(ActiveRecord $activeRecord): void
    {
        $ilDB = $this->returnDB();
        $ilDB->insert($activeRecord->getConnectorContainerName(), $activeRecord->getArrayForConnector());
    }

    /**
     * @return mixed[]
     */
    public function read(ActiveRecord $activeRecord): array
    {
        $ilDB = $this->returnDB();

        $query = 'SELECT * FROM ' . $activeRecord->getConnectorContainerName(
        ) . ' ' . ' WHERE ' . arFieldCache::getPrimaryFieldName($activeRecord) . ' = '
            . $ilDB->quote($activeRecord->getPrimaryFieldValue(), arFieldCache::getPrimaryFieldType($activeRecord));

        $set = $ilDB->query($query);
        $records = [];
        while ($rec = $ilDB->fetchObject($set)) {
            $records[] = $rec;
        }

        return $records;
    }

    public function update(ActiveRecord $activeRecord): void
    {
        $ilDB = $this->returnDB();

        $ilDB->update(
            $activeRecord->getConnectorContainerName(),
            $activeRecord->getArrayForConnector(),
            [
            arFieldCache::getPrimaryFieldName($activeRecord) => [
                arFieldCache::getPrimaryFieldType($activeRecord),
                $activeRecord->getPrimaryFieldValue()
            ]
        ]
        );
    }

    public function delete(ActiveRecord $activeRecord): void
    {
        $ilDB = $this->returnDB();

        $ilDB->manipulate(
            'DELETE FROM ' . $activeRecord->getConnectorContainerName() . ' WHERE ' . arFieldCache::getPrimaryFieldName(
                $activeRecord
            ) . ' = '
            . $ilDB->quote($activeRecord->getPrimaryFieldValue(), arFieldCache::getPrimaryFieldType($activeRecord))
        );
    }

    /**
     * @return mixed[]
     * @internal param $q
     */
    public function readSet(ActiveRecordList $activeRecordList): array
    {
        $ilDB = $this->returnDB();
        $set = $ilDB->query($this->buildQuery($activeRecordList));
        $records = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $records[] = $rec;
        }

        return $records;
    }

    public function affectedRows(ActiveRecordList $activeRecordList): int
    {
        $ilDB = $this->returnDB();
        $q = $this->buildQuery($activeRecordList);

        $set = $ilDB->query($q);

        /** @noinspection PhpParamsInspection */
        return $ilDB->numRows($set);
    }

    /**
     * @return mixed|string
     */
    protected function buildQuery(ActiveRecordList $activeRecordList): string
    {
        $method = 'asSQLStatement';

        // SELECTS
        $q = $activeRecordList->getArSelectCollection()->{$method}();
        // Concats
        $q .= $activeRecordList->getArConcatCollection()->{$method}();
        $q .= ' FROM ' . $activeRecordList->getAR()->getConnectorContainerName();
        // JOINS
        $q .= $activeRecordList->getArJoinCollection()->{$method}();
        // WHERE
        $q .= $activeRecordList->getArWhereCollection()->{$method}();
        // HAVING
        $q .= $activeRecordList->getArHavingCollection()->{$method}();
        // ORDER
        $q .= $activeRecordList->getArOrderCollection()->{$method}();
        // LIMIT
        $q .= $activeRecordList->getArLimitCollection()->{$method}();

        $activeRecordList->setLastQuery($q);

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
