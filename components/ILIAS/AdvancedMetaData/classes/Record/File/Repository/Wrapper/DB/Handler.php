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

namespace ILIAS\AdvancedMetaData\Record\File\Repository\Wrapper\DB;

use ilDBConstants;
use ilDBInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Key\HandlerInterface as FileRepositoryKeyInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Wrapper\DB\HandlerInterface as FileRepositoryDBWrapperInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Element\CollectionInterface as FileRepositoryElementCollectionInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Element\FactoryInterface as FileRepositoryElementFactoryInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Key\FactoryInterface as FileRepositoryKeyFactoryInterface;
use ILIAS\Data\ObjectId;

class Handler implements FileRepositoryDBWrapperInterface
{
    protected ilDBInterface $db;
    protected FileRepositoryElementFactoryInterface $element_factory;
    protected FileRepositoryKeyFactoryInterface $key_factory;

    public function __construct(
        ilDBInterface $db,
        FileRepositoryElementFactoryInterface $element_factory,
        FileRepositoryKeyFactoryInterface $key_factory,
    ) {
        $this->db = $db;
        $this->element_factory = $element_factory;
        $this->key_factory = $key_factory;
    }

    public function insert(
        FileRepositoryKeyInterface $key
    ): void {
        $this->db->query($this->buildInsertQuery($key));
    }

    public function delete(
        FileRepositoryKeyInterface $key
    ): void {
        $this->db->manipulate($this->buildDeleteQuery($key));
    }

    public function select(
        FileRepositoryKeyInterface $key
    ): FileRepositoryElementCollectionInterface {
        $res = $this->db->query($this->buildSelectQuery($key));
        $collection = $this->element_factory->collection();
        while ($row = $res->fetchAssoc()) {
            $key = $this->key_factory->handler()
                ->withObjectId(new ObjectId((int) $row['object_id']))
                ->withResourceIdSerialized($row['rid'])
                ->withIsGlobal((bool) $row['is_global']);
            $element = $this->element_factory->handler()
                ->withKey($key);
            $collection = $collection->withElement($element);
        }
        return $collection;
    }

    public function buildSelectQuery(
        FileRepositoryKeyInterface $key
    ): string {
        if (!$key->isValid()) {
            return "";
        }
        return "SELECT * FROM " . $this->db->quoteIdentifier(self::TABLE_NAME) . $this->buildWhere($key);
    }

    public function buildDeleteQuery(
        FileRepositoryKeyInterface $key
    ): string {
        if (!$key->isValid()) {
            return "";
        }
        return "DELETE FROM " . $this->db->quoteIdentifier(self::TABLE_NAME) . $this->buildWhere($key);
    }

    public function buildInsertQuery(
        FileRepositoryKeyInterface $key
    ): string {
        if (!$key->isValid()) {
            return "";
        }
        return "INSERT INTO " . $this->db->quoteIdentifier(self::TABLE_NAME)
            . " VALUES (" . $this->db->quote($key->getObjectId()->toInt(), ilDBConstants::T_INTEGER)
            . ", " . $this->db->quote($key->getResourceIdSerialized(), ilDBConstants::T_TEXT)
            . ", " . $this->db->quote((int) $key->isGlobal(), ilDBConstants::T_INTEGER)
            . ") ON DUPLICATE KEY UPDATE"
            . " object_id = " . $this->db->quote($key->getObjectId()->toInt(), ilDBConstants::T_INTEGER)
            . ", rid = " . $this->db->quote($key->getResourceIdSerialized(), ilDBConstants::T_TEXT)
            . ", is_global = " . $this->db->quote((int) $key->isGlobal(), ilDBConstants::T_INTEGER);
    }

    protected function buildWhere(
        FileRepositoryKeyInterface $key
    ): string {
        if ($key->isObjectIdKey()) {
            return " WHERE object_id = " . $this->db->quote($key->getObjectId()->toInt(), ilDBConstants::T_INTEGER);
        }
        if ($key->isResourceIdKey()) {
            return " WHERE rid = " . $this->db->quote($key->getResourceIdSerialized(), ilDBConstants::T_TEXT);
        }
        if ($key->isGlobalKey()) {
            return " WHERE is_global = " . $this->db->quote((int) $key->isGlobal(), ilDBConstants::T_INTEGER);
        }
        if ($key->isCompositKeyOfObjectIdAndResourceId()) {
            return " WHERE object_id = " . $this->db->quote($key->getObjectId()->toInt(), ilDBConstants::T_INTEGER)
                . " AND rid = " . $this->db->quote($key->getResourceIdSerialized(), ilDBConstants::T_TEXT);
        }
        if ($key->isCompositKeyOfAll()) {
            return " WHERE object_id = " . $this->db->quote($key->getObjectId()->toInt(), ilDBConstants::T_INTEGER)
                . " AND rid = " . $this->db->quote($key->getResourceIdSerialized(), ilDBConstants::T_TEXT)
                . " AND is_global = " . $this->db->quote((int) $key->isGlobal(), ilDBConstants::T_INTEGER);
        }
        return "";
    }
}
