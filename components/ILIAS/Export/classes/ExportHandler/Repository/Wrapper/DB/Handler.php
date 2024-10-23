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

declare(strict_types=1);

namespace ILIAS\Export\ExportHandler\Repository\Wrapper\DB;

use DateTimeImmutable;
use ilDBConstants;
use ilDBInterface;
use ILIAS\Export\ExportHandler\I\FactoryInterface as ilExportHandlerFactoryInterface;
use ILIAS\Export\ExportHandler\I\Repository\Element\CollectionInterface as ilExportHandlerRepositoryElementCollectionInterface;
use ILIAS\Export\ExportHandler\I\Repository\Element\HandlerInterface as ilExportHandlerRepositoryElementInterface;
use ILIAS\Export\ExportHandler\I\Repository\Key\CollectionInterface as ilExportHandlerRepositoryKeyCollectionInterface;
use ILIAS\Export\ExportHandler\I\Repository\Wrapper\DB\HandlerInterface as ilExportHandlerRepositoryDBWrapperInterface;
use ILIAS\Export\ExportHandler\I\Wrapper\DataFactory\HandlerInterface as ilExportHandlerDataFactoryWrapperInterface;

class Handler implements ilExportHandlerRepositoryDBWrapperInterface
{
    protected const EMPTY_STRING = "";

    protected ilDBInterface $db;
    protected ilExportHandlerFactoryInterface $export_handler;
    protected ilExportHandlerDataFactoryWrapperInterface $data_factory_wrapper;

    public function __construct(
        ilExportHandlerFactoryInterface $export_handler,
        ilDBInterface $db,
        ilExportHandlerDataFactoryWrapperInterface $data_factory_wrapper
    ) {
        $this->export_handler = $export_handler;
        $this->db = $db;
        $this->data_factory_wrapper = $data_factory_wrapper;
    }

    public function store(
        ilExportHandlerRepositoryElementInterface $element
    ): void {
        $this->db->manipulate($this->buldInsertQuery($element));
    }

    public function getElements(
        ilExportHandlerRepositoryKeyCollectionInterface $keys
    ): ilExportHandlerRepositoryElementCollectionInterface {
        $collection = $this->export_handler->repository()->element()->collection();
        if ($keys->count() === 0) {
            return $collection;
        }
        $res = $this->db->query($this->buildSelectQuery($keys));
        while ($row = $res->fetchAssoc()) {
            $key = $this->export_handler->repository()->key()->handler()
                ->withObjectId($this->data_factory_wrapper->objId((int) $row['object_id']))
                ->withResourceIdSerialized($row['rid']);
            $values = $this->export_handler->repository()->values()->handler()
                ->withCreationDate((new DateTimeImmutable($row['timestamp'])))
                ->withOwnerId((int) $row['owner_id']);
            $element = $this->export_handler->repository()->element()->handler()
                ->withKey($key)
                ->withValues($values);
            $collection = $collection->withElement($element);
        }
        return $collection;
    }

    public function deleteElements(
        ilExportHandlerRepositoryKeyCollectionInterface $keys
    ): void {
        if ($keys->count() === 0) {
            return;
        }
        $this->db->manipulate($this->buildDeleteQuery($keys));
    }

    public function buildSelectAllQuery(): string
    {
        return "SELECT * FROM " . $this->db->quoteIdentifier(self::TABLE_NAME);
    }

    public function buildSelectQuery(ilExportHandlerRepositoryKeyCollectionInterface $keys): string
    {
        return "SELECT * FROM " . $this->db->quoteIdentifier(self::TABLE_NAME) . " " . $this->buildWhereClause($keys);
    }

    public function buldInsertQuery(ilExportHandlerRepositoryElementInterface $element): string
    {
        return "INSERT INTO " . $this->db->quoteIdentifier(self::TABLE_NAME) . " VALUES"
            . " (" . $this->db->quote($element->getKey()->getObjectId()->toInt(), ilDBConstants::T_INTEGER)
            . ", " . $this->db->quote($element->getKey()->getResourceIdSerialized(), ilDBConstants::T_TEXT)
            . ", " . $this->db->quote($element->getValues()->getOwnerId(), ilDBConstants::T_INTEGER)
            . ", " . $this->db->quote($element->getValues()->getCreationDate()->format("Y-m-d-H-i-s"), ilDBConstants::T_TIMESTAMP)
            . ") ON DUPLICATE KEY UPDATE"
            . " object_id = " . $this->db->quote($element->getKey()->getObjectId()->toInt(), ilDBConstants::T_INTEGER)
            . ", rid = " . $this->db->quote($element->getKey()->getResourceIdSerialized(), ilDBConstants::T_TEXT)
            . ", owner_id = " . $this->db->quote($element->getValues()->getOwnerId(), ilDBConstants::T_INTEGER)
            . ", timestamp = " . $this->db->quote($element->getValues()->getCreationDate()->format("Y-m-d-H-i-s"), ilDBConstants::T_TIMESTAMP);
    }

    public function buildDeleteQuery(ilExportHandlerRepositoryKeyCollectionInterface $keys)
    {
        return "DELETE FROM " . $this->db->quoteIdentifier(self::TABLE_NAME) . " " . $this->buildWhereClause($keys);
    }

    protected function buildWhereClause(
        ilExportHandlerRepositoryKeyCollectionInterface $keys
    ): string {
        $complete_key_conditions = $this->buildCompleteKeyConditions($keys);
        $incomplete_key_conditions = $this->buildIncompleteKeyConditions($keys);
        if (
            $complete_key_conditions !== self::EMPTY_STRING and
            $incomplete_key_conditions !== self::EMPTY_STRING
        ) {
            return "WHERE " . $complete_key_conditions . " OR " . $incomplete_key_conditions;
        }
        if ($complete_key_conditions !== self::EMPTY_STRING) {
            return "WHERE " . $complete_key_conditions;
        }
        if ($incomplete_key_conditions !== self::EMPTY_STRING) {
            return "WHERE " . $incomplete_key_conditions;
        }
        return self::EMPTY_STRING;
    }

    protected function buildIncompleteKeyConditions(
        ilExportHandlerRepositoryKeyCollectionInterface $keys
    ): string {
        $conditions = [];
        foreach ($keys as $key) {
            if ($key->isCompleteKey()) {
                continue;
            }
            if ($key->isObjectIdKey()) {
                $conditions[] = "object_id = " . $this->db->quote($key->getObjectId()->toInt(), ilDBConstants::T_INTEGER);
            }
            if ($key->isResourceIdKey()) {
                $conditions[] = "rid = " . $this->db->quote($key->getResourceIdSerialized(), ilDBConstants::T_TEXT);
            }
        }
        return count($conditions) > 0 ? implode(" OR ", $conditions) : self::EMPTY_STRING;
    }

    protected function buildCompleteKeyConditions(
        ilExportHandlerRepositoryKeyCollectionInterface $keys
    ): string {
        $tuples = [];
        foreach ($keys as $key) {
            if (!$key->isCompleteKey()) {
                continue;
            }
            $tuples[] = "("
                . $this->db->quote($key->getObjectId()->toInt(), ilDBConstants::T_INTEGER)
                . ","
                . $this->db->quote($key->getResourceIdSerialized(), ilDBConstants::T_TEXT)
                . ")";
        }
        return count($tuples) > 0 ? "(object_id, rid) in (" . implode(",", $tuples) . ")" : self::EMPTY_STRING;
    }
}
