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

namespace ILIAS\MetaData\OERHarvester\ExposedRecords;

class DatabaseRepository implements RepositoryInterface
{
    protected \ilDBInterface $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @return RecordInterface[]
     */
    public function getRecords(
        ?\DateTimeImmutable $from = null,
        ?\DateTimeImmutable $until = null,
        ?int $limit = null,
        ?int $offset = null
    ): \Generator {
        $res = $this->query(
            'SELECT * FROM il_meta_oer_exposed' .
            $this->getDatesWhereCondition($from, $until) .
            ' ORDER BY obj_id' . $this->getLimitAndOffset($limit, $offset)
        );

        foreach ($res as $row) {
            yield $this->getRecordFromRow($row);
        }
    }

    /**
     * @return RecordInfosInterface[]
     */
    public function getRecordInfos(
        ?\DateTimeImmutable $from = null,
        ?\DateTimeImmutable $until = null,
        ?int $limit = null,
        ?int $offset = null
    ): \Generator {
        $res = $this->query(
            'SELECT obj_id, identifier, datestamp FROM il_meta_oer_exposed' .
            $this->getDatesWhereCondition($from, $until) .
            ' ORDER BY obj_id' . $this->getLimitAndOffset($limit, $offset)
        );

        foreach ($res as $row) {
            yield $this->getRecordInfosFromRow($row);
        }
    }

    public function getRecordCount(
        ?\DateTimeImmutable $from = null,
        ?\DateTimeImmutable $until = null
    ): int {
        $res = $this->query(
            'SELECT COUNT(*) AS num FROM il_meta_oer_exposed' .
            $this->getDatesWhereCondition($from, $until)
        );

        foreach ($res as $row) {
            return (int) $row['num'];
        }
        return 0;
    }

    public function getEarliestDatestamp(): \DateTimeImmutable
    {
        $res = $this->query(
            'SELECT MIN(datestamp) AS earliest FROM il_meta_oer_exposed'
        );

        foreach ($res as $row) {
            if (is_null($row['earliest'])) {
                continue;
            }
            return new \DateTimeImmutable('@' . $row['earliest']);
        }
        return new \DateTimeImmutable('@' . $this->getCurrentDatestamp());
    }

    public function getRecordByIdentifier(string $identifier): ?RecordInterface
    {
        $res = $this->query(
            'SELECT * FROM il_meta_oer_exposed WHERE identifier = ' .
            $this->quoteString($identifier)
        );

        foreach ($res as $row) {
            return $this->getRecordFromRow($row);
        }
        return null;
    }

    public function doesRecordWithIdentifierExist(string $identifier): bool
    {
        $res = $this->query(
            'SELECT COUNT(*) AS num FROM il_meta_oer_exposed WHERE identifier = ' .
            $this->quoteString($identifier)
        );

        foreach ($res as $row) {
            return $row['num'] > 0;
        }
        return false;
    }

    public function doesRecordExistForObjID(int $obj_id): bool
    {
        $res = $this->query(
            'SELECT COUNT(*) AS num FROM il_meta_oer_exposed WHERE obj_id = ' . $this->quoteInteger($obj_id)
        );

        foreach ($res as $row) {
            return $row['num'] > 0;
        }
        return false;
    }

    public function createRecord(int $obj_id, string $identifier, \DOMDocument $metadata): void
    {
        $this->manipulate(
            'INSERT INTO il_meta_oer_exposed (obj_id, identifier, datestamp, metadata) VALUES (' .
            $this->quoteInteger($obj_id) . ', ' .
            $this->quoteString($identifier) . ', ' .
            $this->quoteInteger($this->getCurrentDatestamp()) . ', ' .
            $this->quoteClob($metadata->saveXML()) . ')'
        );
    }

    public function updateRecord(int $obj_id, \DOMDocument $metadata): void
    {
        $this->manipulate(
            'UPDATE il_meta_oer_exposed SET ' .
            'metadata = ' . $this->quoteClob($metadata->saveXML()) . ', ' .
            'datestamp = ' . $this->quoteInteger($this->getCurrentDatestamp()) . ' ' .
            'WHERE obj_id = ' . $this->quoteInteger($obj_id)
        );
    }

    public function deleteRecord(int $obj_id): void
    {
        $this->manipulate(
            'DELETE FROM il_meta_oer_exposed WHERE obj_id = ' . $this->quoteInteger($obj_id)
        );
    }

    protected function getRecordFromRow(array $row): RecordInterface
    {
        $md_xml = new \DOMDocument();
        $md_xml->loadXML((string) $row['metadata']);

        return new Record(
            $this->getRecordInfosFromRow($row),
            $md_xml
        );
    }

    protected function getRecordInfosFromRow(array $row): RecordInfosInterface
    {
        return new RecordInfos(
            (int) $row['obj_id'],
            (string) $row['identifier'],
            new \DateTimeImmutable('@' . $row['datestamp'])
        );
    }

    protected function getDatesWhereCondition(
        ?\DateTimeImmutable $from = null,
        ?\DateTimeImmutable $until = null
    ): string {
        $wheres = [];
        if (!is_null($from)) {
            $wheres[] = 'datestamp >= ' . $this->quoteInteger($from->getTimestamp());
        }
        if (!is_null($until)) {
            $wheres[] = 'datestamp <= ' . $this->quoteInteger($until->getTimestamp());
        }

        if (empty($wheres)) {
            return '';
        }
        return ' WHERE ' . implode(' AND ', $wheres);
    }

    protected function getLimitAndOffset(
        ?int $limit = null,
        ?int $offset = null
    ): string {
        $query_limit = '';
        if (!is_null($limit) || !is_null($offset)) {
            $limit = is_null($limit) ? PHP_INT_MAX : $limit;
            $query_limit = ' LIMIT ' . $this->quoteInteger($limit);
        }
        $query_offset = '';
        if (!is_null($offset)) {
            $query_offset = ' OFFSET ' . $this->quoteInteger($offset);
        }
        return $query_limit . $query_offset;
    }

    /**
     * @return array[]
     */
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

    protected function quoteString(string $string): string
    {
        return $this->db->quote($string, \ilDBConstants::T_TEXT);
    }

    protected function quoteClob(string $string): string
    {
        return $this->db->quote($string, \ilDBConstants::T_CLOB);
    }

    protected function getCurrentDatestamp(): int
    {
        $date = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        return $date->setTime(0, 0)->getTimestamp();
    }
}
