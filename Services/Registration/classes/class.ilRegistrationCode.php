<?php

declare(strict_types=1);
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Class ilRegistrationCode
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ServicesRegistration
 */
class ilRegistrationCode
{
    protected const DB_TABLE = 'reg_registration_codes';
    public const CODE_LENGTH = 10;

    public static function create(
        int $role,
        int $stamp,
        array $local_roles,
        string $limit,
        array $limit_date,
        bool $reg_type,
        bool $ext_type
    ): int {
        global $DIC;

        $ilDB = $DIC->database();
        $id = $ilDB->nextId(self::DB_TABLE);

        // create unique code
        $found = true;
        $code = '';
        while ($found) {
            $code = self::generateRandomCode();
            $chk = $ilDB->queryF(
                "SELECT code_id FROM " . self::DB_TABLE . " WHERE code = %s",
                ["text"],
                [$code]
            );
            $found = (bool) $ilDB->numRows($chk);
        }

        if ($limit === "relative") {
            $limit_date = serialize($limit_date); //TODO-PHP8-REVIEW please don't override variables with different types
        }

        $data = [
            'code_id' => ['integer', $id],
            'code' => ['text', $code],
            'generated_on' => ['integer', $stamp],
            'role' => ['integer', $role],
            'role_local' => ['text', implode(";", $local_roles)],
            'alimit' => ['text', $limit],
            'alimitdt' => ['text', $limit_date],
            'reg_enabled' => ['integer', $reg_type],
            'ext_enabled' => ['integer', $ext_type]
        ];

        $ilDB->insert(self::DB_TABLE, $data);
        return $id;
    }

    protected static function generateRandomCode(): string
    {
        // missing : 01iloO
        $map = "23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ";

        $code = "";
        $max = strlen($map) - 1;
        for ($loop = 1; $loop <= self::CODE_LENGTH; $loop++) {
            $code .= $map[random_int(0, $max)];
        }
        return $code;
    }

    public static function getCodesData(
        string $order_field,
        string $order_direction,
        int $offset,
        int $limit,
        string $filter_code,
        int $filter_role,
        string $filter_generated,
        string $filter_access_limitation
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        // filter
        $where = self::filterToSQL($filter_code, $filter_role, $filter_generated, $filter_access_limitation);

        // count query
        $set = $ilDB->query("SELECT COUNT(*) AS cnt FROM " . self::DB_TABLE . $where);
        $cnt = 0;
        if ($rec = $ilDB->fetchAssoc($set)) {
            $cnt = $rec["cnt"];
        }

        $sql = "SELECT * FROM " . self::DB_TABLE . $where;
        if ($order_field) {
            if ($order_field === 'generated') {
                $order_field = 'generated_on';
            }
            $sql .= " ORDER BY " . $order_field . " " . $order_direction;
        }

        // set query
        $ilDB->setLimit($limit, $offset);
        $set = $ilDB->query($sql);
        $result = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $rec['generated'] = $rec['generated_on'];
            $result[] = $rec;
        }
        return ["cnt" => $cnt, "set" => $result];
    }

    public static function loadCodesByIds(array $ids): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query("SELECT * FROM " . self::DB_TABLE . " WHERE " . $ilDB->in(
            "code_id",
            $ids,
            false,
            "integer"
        ));
        $result = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $result[] = $rec;
        }
        return $result;
    }

    public static function deleteCodes(array $ids): bool
    {
        global $DIC;

        $ilDB = $DIC->database();
        if (count($ids)) {
            return (bool) $ilDB->manipulate("DELETE FROM " . self::DB_TABLE . " WHERE " . $ilDB->in(
                "code_id",
                $ids,
                false,
                "integer"
            ));
        }
        return false;
    }

    public static function getGenerationDates(): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query("SELECT DISTINCT(generated_on) genr FROM " . self::DB_TABLE . " ORDER BY genr");
        $result = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $result[] = $rec["genr"];
        }
        return $result;
    }

    protected static function filterToSQL(
        string $filter_code,
        int $filter_role,
        string $filter_generated,
        string $filter_access_limitation
    ): string {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $where = [];
        if ($filter_code) {
            $where[] = $ilDB->like("code", "text", "%" . $filter_code . "%");
        }
        if ($filter_role) {
            $where[] = "role = " . $ilDB->quote($filter_role, "integer");
        }
        if ($filter_generated) {
            $where[] = "generated_on = " . $ilDB->quote($filter_generated, "text");
        }
        if ($filter_access_limitation) {
            $where[] = "alimit = " . $ilDB->quote($filter_access_limitation, "text");
        }
        if (count($where)) {
            return " WHERE " . implode(" AND ", $where);
        }

        return "";
    }

    public static function getCodesForExport(
        string $filter_code,
        int $filter_role,
        string $filter_generated,
        string $filter_access_limitation
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        // filter
        $where = self::filterToSQL($filter_code, $filter_role, $filter_generated, $filter_access_limitation);

        // set query
        $set = $ilDB->query("SELECT code FROM " . self::DB_TABLE . $where . " ORDER BY code_id");
        $result = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $result[] = $rec["code"];
        }
        return $result;
    }

    public static function isUnusedCode(string $code): bool
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $set = $ilDB->query("SELECT used FROM " . self::DB_TABLE . " WHERE code = " . $ilDB->quote($code, "text"));
        $set = $ilDB->fetchAssoc($set);
        return $set && !$set["used"];
    }

    public static function isValidRegistrationCode(string $a_code): bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'SELECT code_id FROM reg_registration_codes ' .
            'WHERE used = ' . $ilDB->quote(0, 'integer') . ' ' .
            'AND reg_enabled = ' . $ilDB->quote(1, 'integer') . ' ' .
            'AND code = ' . $ilDB->quote($a_code, 'text');
        $res = $ilDB->query($query);

        return (bool) $res->numRows();
    }

    public static function useCode(string $code): bool
    {
        global $DIC;

        $ilDB = $DIC->database();
        return (bool) $ilDB->update(
            self::DB_TABLE,
            ["used" => ["timestamp", time()]],
            ["code" => ["text", $code]]
        );
    }

    public static function getCodeRole(string $code): int
    {
        global $DIC;

        $ilDB = $DIC->database();
        $set = $ilDB->query("SELECT role FROM " . self::DB_TABLE . " WHERE code = " . $ilDB->quote($code, "text"));
        $row = $ilDB->fetchAssoc($set);
        if (isset($row["role"])) {
            return (int) $row["role"];
        }
        return 0;
    }

    public static function getCodeData(string $code): array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $set = $ilDB->query("SELECT role, role_local, alimit, alimitdt, reg_enabled, ext_enabled" .
            " FROM " . self::DB_TABLE .
            " WHERE code = " . $ilDB->quote($code, "text"));
        return $ilDB->fetchAssoc($set);
    }
}
