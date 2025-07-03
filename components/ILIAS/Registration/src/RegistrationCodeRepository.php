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

namespace ILIAS\Registration;

use ilDBConstants;
use ilDBInterface;

class RegistrationCodeRepository
{
    private const string TABLE_NAME = 'reg_registration_codes';

    public function __construct(
        protected readonly ilDBInterface $db
    ) {
    }

    private function filterToSQL(
        CodeFilter $code_filter,
    ): string {
        $where = [];
        if ($code_filter->getCode()) {
            $where[] = $this->db->like('code', ilDBConstants::T_TEXT, '%' . $code_filter->getCode() . '%');
        }
        if ($code_filter->getRole()) {
            $where[] = 'role = ' . $this->db->quote($code_filter->getRole(), ilDBConstants::T_INTEGER);
        }
        if ($code_filter->getGenerated()) {
            $where[] = 'generated_on = ' . $this->db->quote($code_filter->getGenerated(), ilDBConstants::T_TEXT);
        }
        if ($code_filter->getAccessLimitation()) {
            $where[] = 'alimit = ' . $this->db->quote($code_filter->getAccessLimitation(), ilDBConstants::T_TEXT);
        }
        if ($where !== []) {
            return ' WHERE ' . implode(' AND ', $where);
        }

        return '';
    }

    public function getTotalCodeCount(
        ?CodeFilter $code_filter = null
    ): int {
        $set = $this->db->query('SELECT COUNT(*) AS cnt FROM ' . self::TABLE_NAME . ($code_filter ? $this->filterToSQL($code_filter) : ''));
        $cnt = 0;
        if ($rec = $this->db->fetchAssoc($set)) {
            $cnt = (int) ($rec['cnt'] ?? 0);
        }

        return $cnt;
    }

    /**
     * @return list<array{
     *     code_id: int,
     *     code: string,
     *     role: int,
     *     used: int,
     *     role_local: string,
     *     alimit: string,
     *     alimitdt: string,
     *     reg_enabled: int,
     *     ext_enabled: int,
     *     generated: int
     * }
     */
    public function getCodesData(
        string $order_field,
        string $order_direction,
        int $offset,
        int $limit,
        CodeFilter $code_filter = null
    ): array {
        $sql = 'SELECT * FROM ' . self::TABLE_NAME . ($code_filter ? $this->filterToSQL($code_filter) : '');
        if ($order_field) {
            if ($order_field === 'generated') {
                $order_field = 'generated_on';
            }
            $sql .= ' ORDER BY ' . $order_field . ' ' . $order_direction;
        }

        $this->db->setLimit($limit, $offset);
        $set = $this->db->query($sql);
        $result = [];
        while ($rec = $this->db->fetchAssoc($set)) {
            $rec['generated'] = (int) $rec['generated_on'];
            unset($rec['generated_on']);
            $result[] = $rec;
        }

        return $result;
    }

    /**
     * @param list<int> $ids
     * @return list<array{
     *      code_id: int,
     *      code: string,
     *      role: int,
     *      used: int,
     *      role_local: string,
     *      alimit: string,
     *      alimitdt: string,
     *      reg_enabled: int,
     *      ext_enabled: int,
     *      generated: int
     *  }
     */
    public function loadCodesByIds(array $ids): array
    {
        $set = $this->db->query('SELECT * FROM ' . self::TABLE_NAME . ' WHERE ' . $this->db->in(
            'code_id',
            $ids,
            false,
            ilDBConstants::T_INTEGER
        ));
        $result = [];
        while ($rec = $this->db->fetchAssoc($set)) {
            $result[] = $rec;
        }

        return $result;
    }

    /**
     * @param list<int> $ids
     */
    public function deleteCodes(array $ids): bool
    {
        if (\count($ids)) {
            return (bool) $this->db->manipulate('DELETE FROM ' . self::TABLE_NAME . ' WHERE ' . $this->db->in(
                'code_id',
                $ids,
                false,
                ilDBConstants::T_INTEGER
            ));
        }

        return false;
    }

    /**
     * @return list<int>
     */
    public function getGenerationDates(): array
    {
        $set = $this->db->query('SELECT DISTINCT(generated_on) genr FROM ' . self::TABLE_NAME . ' ORDER BY genr');
        $result = [];
        while ($rec = $this->db->fetchAssoc($set)) {
            $result[] = (int) $rec['genr'];
        }

        return $result;
    }

    /**
     * @return list<array{
     *      code_id: int,
     *      code: string,
     *      role: int,
     *      generated_on: int,
     *      used: int,
     *      role_local: string,
     *      alimit: string,
     *      alimitdt: string,
     *      reg_enabled: int,
     *      ext_enabled: int,
     *  }
     */
    public function getCodesByFilter(CodeFilter $code_filter): array
    {
        $set = $this->db->query(
            'SELECT * FROM ' . self::TABLE_NAME .
            ($code_filter ? $this->filterToSQL($code_filter) : '')
        );
        $result = [];
        while ($rec = $this->db->fetchAssoc($set)) {
            $result[] = $rec;
        }

        return $result;
    }
}
