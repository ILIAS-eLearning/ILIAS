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

namespace ILIAS\Poll\Image\Repository\Wrapper\DB;

use ilDBConstants;
use ilDBInterface;
use ILIAS\Poll\Image\I\Repository\Element\HandlerInterface as ilPollImageRepositoryElementInterface;
use ILIAS\Poll\Image\I\Repository\FactoryInterface as ilPollImageRepositoryFactoryInterface;
use ILIAS\Poll\Image\I\Repository\Key\HandlerInterface as ilPollImageRepositoryKeyInterface;
use ILIAS\Poll\Image\I\Repository\Values\HandlerInterface as ilPollImageRepositoryValuesInterface;
use ILIAS\Poll\Image\I\Repository\Wrapper\DB\HandlerInterface as ilPollImageRepositoryWrapperDBInterface;

class Handler implements ilPollImageRepositoryWrapperDBInterface
{
    protected ilPollImageRepositoryFactoryInterface $repository;
    protected ilDBInterface $db;

    public function __construct(
        ilPollImageRepositoryFactoryInterface $repository,
        ilDBInterface $db
    ) {
        $this->db = $db;
        $this->repository = $repository;
    }

    public function insert(
        ilPollImageRepositoryKeyInterface $key,
        ilPollImageRepositoryValuesInterface $values
    ): void {
        if (!$key->isValid()) {
            return;
        }
        $this->db->manipulate($this->buildInsertQuery($key, $values));
    }

    public function select(
        ilPollImageRepositoryKeyInterface $key
    ): null|ilPollImageRepositoryElementInterface {
        if (!$key->isValid()) {
            return null;
        }
        $res = $this->db->query($this->buildSelectQuery($key));
        $row = $this->db->fetchAssoc($res);
        if (is_null($row)) {
            return null;
        }
        $values = $this->repository->values()->handler()
            ->withResourceIdSerialized($row['rid']);
        return $this->repository->element()->handler()
            ->withKey($key)
            ->withValues($values);
    }

    public function delete(
        ilPollImageRepositoryKeyInterface $key
    ): void {
        if (!$key->isValid()) {
            return;
        }
        $this->db->manipulate($this->buildDeleteQuery($key));
    }

    public function buildInsertQuery(
        ilPollImageRepositoryKeyInterface $key,
        ilPollImageRepositoryValuesInterface $values
    ): string {
        return "INSERT INTO " . $this->db->quoteIdentifier(self::TABLE_NAME)
            . " VALUES (" . $this->db->quote($key->getObjectId()->toInt(), ilDBConstants::T_INTEGER)
            . ", " . $this->db->quote($values->getResourceIdSerialized(), ilDBConstants::T_TEXT)
            . ") ON DUPLICATE KEY UPDATE"
            . " object_id = " . $this->db->quote($key->getObjectId()->toInt(), ilDBConstants::T_INTEGER)
            . ", rid = " . $this->db->quote($values->getResourceIdSerialized(), ilDBConstants::T_TEXT);
    }

    public function buildSelectQuery(
        ilPollImageRepositoryKeyInterface $key
    ): string {
        return "SELECT * FROM " . $this->db->quoteIdentifier(self::TABLE_NAME)
            . " WHERE object_id = " . $this->db->quote($key->getObjectId()->toInt(), ilDBConstants::T_INTEGER);
    }

    public function buildDeleteQuery(
        ilPollImageRepositoryKeyInterface $key
    ): string {
        return "DELETE FROM " . $this->db->quoteIdentifier(self::TABLE_NAME)
            . " WHERE object_id = " . $this->db->quote($key->getObjectId()->toInt(), ilDBConstants::T_INTEGER);
    }
}
