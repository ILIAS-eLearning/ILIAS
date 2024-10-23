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

namespace ILIAS\Export\ExportHandler\PublicAccess\Repository\Wrapper\DB;

use ilDBConstants;
use ilDBInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Element\CollectionInterface as ilExportHandlerPublicAccessRepositoryElementCollectionInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Element\FactoryInterface as ilExportHandlerPublicAccessRepositoryElementFactroyInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Element\HandlerInterface as ilExportHandlerPublicAccessRepositoryElementInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Key\CollectionInterface as ilExportHandlerPublicAccessRepositoryKeyCollectionInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Key\FactoryInterface as ilExportHandlerPublicAccessRepositoryKeyFactoryInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Values\FactoryInterface as ilExportHandlerPublicAccessRepositoryValuesFactoryInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Wrapper\DB\HandlerInterface as ilExportHandlerPublicAccessRepositoryDBWrapperInterface;
use ILIAS\Export\ExportHandler\I\Wrapper\DataFactory\HandlerInterface as ilExportHandlerDataFactoryWrapperInterface;

class Handler implements ilExportHandlerPublicAccessRepositoryDBWrapperInterface
{
    protected ilExportHandlerPublicAccessRepositoryElementFactroyInterface $element_factory;
    protected ilExportHandlerPublicAccessRepositoryKeyFactoryInterface $key_factory;
    protected ilExportHandlerPublicAccessRepositoryValuesFactoryInterface $values_factory;
    protected ilDBInterface $db;
    protected ilExportHandlerDataFactoryWrapperInterface $data_factory_wrapper;

    public function __construct(
        ilDBInterface $db,
        ilExportHandlerPublicAccessRepositoryElementFactroyInterface $element_factory,
        ilExportHandlerPublicAccessRepositoryKeyFactoryInterface $key_factory,
        ilExportHandlerPublicAccessRepositoryValuesFactoryInterface $values_factory,
        ilExportHandlerDataFactoryWrapperInterface $data_factory_wrapper
    ) {
        $this->db = $db;
        $this->element_factory = $element_factory;
        $this->key_factory = $key_factory;
        $this->values_factory = $values_factory;
        $this->data_factory_wrapper = $data_factory_wrapper;
    }

    public function storeElement(
        ilExportHandlerPublicAccessRepositoryElementInterface $element
    ): void {
        $this->db->manipulate($this->buldInsertQuery($element));
    }

    public function getElements(
        ilExportHandlerPublicAccessRepositoryKeyCollectionInterface $keys
    ): ilExportHandlerPublicAccessRepositoryElementCollectionInterface {
        $collection = $this->element_factory->collection();
        if ($keys->count() === 0) {
            return $collection;
        }
        $res = $this->db->query($this->buildSelectQuery($keys));
        while ($row = $res->fetchAssoc()) {
            $object_id = $this->data_factory_wrapper->objId((int) $row['object_id']);
            $key = $this->key_factory->handler()
                ->withObjectId($object_id);
            $values = $this->values_factory->handler()
                ->withIdentification($row['identification'])
                ->withExportOptionId($row['export_option_id']);
            $element = $this->element_factory->handler()
                ->withKey($key)
                ->withValues($values);
            $collection = $collection
                ->withElement($element);
        }
        return $collection;
    }

    public function getAllElements(): ilExportHandlerPublicAccessRepositoryElementCollectionInterface
    {
        $collection = $this->element_factory->collection();
        $res = $this->db->query($this->buildSelectAllQuery());
        while ($row = $res->fetchAssoc()) {
            $object_id = $this->data_factory_wrapper->objId((int) $row['object_id']);
            $key = $this->key_factory->handler()
                ->withObjectId($object_id);
            $values = $this->values_factory->handler()
                ->withIdentification($row['identification'])
                ->withExportOptionId($row['export_option_id']);
            $element = $this->element_factory->handler()
                ->withKey($key)
                ->withValues($values);
            $collection = $collection
                ->withElement($element);
        }
        return $collection;
    }

    public function deleteElements(
        ilExportHandlerPublicAccessRepositoryKeyCollectionInterface $keys
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

    public function buildSelectQuery(
        ilExportHandlerPublicAccessRepositoryKeyCollectionInterface $keys
    ): string {
        $conditions = [];
        foreach ($keys as $key) {
            $conditions[] = "object_id = " . $this->db->quote($key->getObjectId()->toInt(), ilDBConstants::T_INTEGER);
        }
        return "SELECT * FROM " . $this->db->quoteIdentifier(self::TABLE_NAME) . " WHERE " . implode(" AND ", $conditions);
    }

    public function buldInsertQuery(
        ilExportHandlerPublicAccessRepositoryElementInterface $element
    ): string {
        return "INSERT INTO " . $this->db->quoteIdentifier(self::TABLE_NAME) . " VALUES"
            . " (" . $this->db->quote($element->getKey()->getObjectId()->toInt(), ilDBConstants::T_INTEGER)
            . ", " . $this->db->quote($element->getValues()->getExportOptionId(), ilDBConstants::T_TEXT)
            . ", " . $this->db->quote($element->getValues()->getIdentification(), ilDBConstants::T_TEXT)
            . ", " . $this->db->quote($element->getValues()->getLastModified()->format("Y-m-d-H-i-s"), ilDBConstants::T_TIMESTAMP)
            . ") ON DUPLICATE KEY UPDATE"
            . " object_id = " . $this->db->quote($element->getKey()->getObjectId()->toInt(), ilDBConstants::T_INTEGER)
            . ", export_option_id = " . $this->db->quote($element->getValues()->getExportOptionId(), ilDBConstants::T_TEXT)
            . ", identification = " . $this->db->quote($element->getValues()->getIdentification(), ilDBConstants::T_TEXT)
            . ", timestamp = " . $this->db->quote($element->getValues()->getLastModified()->format("Y-m-d-H-i-s"), ilDBConstants::T_TIMESTAMP);
    }

    public function buildDeleteQuery(ilExportHandlerPublicAccessRepositoryKeyCollectionInterface $keys)
    {
        $conditions = [];
        foreach ($keys as $key) {
            $conditions[] = "object_id = " . $this->db->quote($key->getObjectId()->toInt(), ilDBConstants::T_INTEGER);
        }
        return "DELETE FROM " . $this->db->quoteIdentifier(self::TABLE_NAME) . " WHERE " . implode(" AND ", $conditions);
    }
}
