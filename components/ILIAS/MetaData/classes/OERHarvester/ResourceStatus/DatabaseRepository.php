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

namespace ILIAS\MetaData\OERHarvester\ResourceStatus;

class DatabaseRepository implements RepositoryInterface
{
    protected \ilDBInterface $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function isHarvestingBlocked(int $obj_id): bool
    {
        $res = $this->query(
            'SELECT blocked FROM il_meta_oer_stat WHERE obj_id = ' . $this->quoteInteger($obj_id)
        );

        foreach ($res as $row) {
            return (bool) $row['blocked'];
        }
        return false;
    }

    public function setHarvestingBlocked(int $obj_id, bool $blocked): void
    {
        $this->manipulate(
            'INSERT INTO il_meta_oer_stat (obj_id, href_id, blocked) VALUES (' .
            $this->quoteInteger($obj_id) . ', ' .
            $this->quoteInteger(0) . ', ' .
            $this->quoteInteger((int) $blocked) . ') ' .
            'ON DUPLICATE KEY UPDATE blocked = ' . $this->quoteInteger((int) $blocked)
        );
    }

    public function isAlreadyHarvested(int $obj_id): bool
    {
        $res = $this->query(
            'SELECT href_id FROM il_meta_oer_stat WHERE obj_id = ' . $this->quoteInteger($obj_id)
        );

        foreach ($res as $row) {
            return (bool) $row['href_id'];
        }
        return false;
    }

    /**
     * @return int[]
     */
    public function getAllHarvestedObjIDs(): \Generator
    {
        $res = $this->query(
            'SELECT obj_id FROM il_meta_oer_stat WHERE href_id > 0'
        );

        foreach ($res as $row) {
            yield (int) $row['obj_id'];
        }
    }

    public function getHarvestRefID(int $obj_id): int
    {
        $res = $this->query(
            'SELECT href_id FROM il_meta_oer_stat WHERE obj_id = ' . $this->quoteInteger($obj_id)
        );

        foreach ($res as $row) {
            return (int) $row['href_id'];
        }
        return 0;
    }

    public function setHarvestRefID(int $obj_id, int $harvested_ref_id): void
    {
        $this->manipulate(
            'INSERT INTO il_meta_oer_stat (obj_id, href_id, blocked) VALUES (' .
            $this->quoteInteger($obj_id) . ', ' .
            $this->quoteInteger($harvested_ref_id) . ', ' .
            $this->quoteInteger(0) . ') ' .
            'ON DUPLICATE KEY UPDATE href_id = ' . $this->quoteInteger($harvested_ref_id)
        );
    }

    public function deleteHarvestRefID(int $obj_id): void
    {
        $this->manipulate(
            'UPDATE il_meta_oer_stat SET href_id = 0 WHERE obj_id = ' . $this->quoteInteger($obj_id)
        );
    }

    /**
     * @return int[]
     */
    public function filterOutBlockedObjects(int ...$obj_ids): \Generator
    {
        $res = $this->query(
            'SELECT obj_id FROM il_meta_oer_stat WHERE blocked = 1 AND ' .
            $this->inWithIntegers('obj_id', ...$obj_ids)
        );

        $blocked_ids = [];
        foreach ($res as $row) {
            $blocked_ids[] = (int) $row['obj_id'];
        }

        foreach ($obj_ids as $obj_id) {
            if (!in_array($obj_id, $blocked_ids)) {
                yield $obj_id;
            }
        }
    }

    public function deleteStatus(int $obj_id): void
    {
        $this->manipulate(
            'DELETE FROM il_meta_oer_stat WHERE obj_id = ' . $this->quoteInteger($obj_id)
        );
    }

    protected function query(string $query): \Generator
    {
        $res = $this->db->query($query);
        while ($row = $res->fetchAssoc()) {
            yield $row;
        }
    }

    protected function manipulate(string $query): void
    {
        $this->db->manipulate($query);
    }

    protected function quoteInteger(int $integer): string
    {
        return $this->db->quote($integer, \ilDBConstants::T_INTEGER);
    }

    protected function inWithIntegers(string $field, int ...$integers): string
    {
        return $this->db->in($field, $integers, false, \ilDBConstants::T_INTEGER);
    }
}
