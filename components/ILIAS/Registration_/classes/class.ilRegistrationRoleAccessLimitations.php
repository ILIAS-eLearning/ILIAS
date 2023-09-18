<?php

declare(strict_types=1);

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
 * Class class.ilRegistrationAccessLimitation
 * @author  Sascha Hofmann <saschahofmann@gmx.de>
 */
class ilRegistrationRoleAccessLimitations
{
    public const IL_REG_ACCESS_LIMITATION_MISSING_MODE = 1;
    public const IL_REG_ACCESS_LIMITATION_OUT_OF_DATE = 2;

    /** @var array<int, array{id: int, absolute: null|int, relative_d: null|int, relative_m: null|int, mode:string}> */
    private array $access_limitations = [];

    protected ilDBInterface $db;

    public function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->read();
    }

    private function read(): void
    {
        $query = "SELECT * FROM reg_access_limit ";
        $res = $this->db->query($query);

        $this->access_limitations = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $role_id = (int) $row->role_id;
            $this->access_limitations[$role_id]['id'] = $role_id;
            $this->access_limitations[$role_id]['absolute'] = is_null($row->limit_absolute) ? null : (int) $row->limit_absolute;
            $this->access_limitations[$role_id]['relative_d'] = is_null($row->limit_relative_d) ? null : (int) $row->limit_relative_d;
            $this->access_limitations[$role_id]['relative_m'] = is_null($row->limit_relative_m) ? null : (int) $row->limit_relative_m;
            $this->access_limitations[$role_id]['mode'] = (string) $row->limit_mode;
        }
    }

    public function save(): bool
    {
        foreach ($this->access_limitations as $role_id => $data) {
            $query = "DELETE FROM reg_access_limit " .
                "WHERE role_id = " . $this->db->quote($role_id, 'integer');
            $this->db->manipulate($query);

            $query = "INSERT INTO reg_access_limit (role_id,limit_mode,limit_absolute," .
                "limit_relative_d,limit_relative_m) " .
                "VALUES( " .
                $this->db->quote($role_id, ilDBConstants::T_INTEGER) . ", " .
                $this->db->quote($data['mode'], ilDBConstants::T_TEXT) . ", " .
                $this->db->quote($data['absolute'] ?? null, ilDBConstants::T_INTEGER) . ", " .
                $this->db->quote($data['relative_d'] ?? null, ilDBConstants::T_INTEGER) . ", " .
                $this->db->quote($data['relative_m'] ?? null, ilDBConstants::T_INTEGER) . " " .
                ")";
            $this->db->manipulate($query);
        }
        return true;
    }

    public function validate(): int
    {
        foreach ($this->access_limitations as $data) {
            if ($data['mode'] === 'null') {
                return self::IL_REG_ACCESS_LIMITATION_MISSING_MODE;
            }

            if ($data['mode'] === 'absolute' && $data['absolute'] < time()) {
                return self::IL_REG_ACCESS_LIMITATION_OUT_OF_DATE;
            }

            if ($data['mode'] === 'relative' && ($data['relative_d'] < 1 && $data['relative_m'] < 1)) {
                return self::IL_REG_ACCESS_LIMITATION_OUT_OF_DATE;
            }
        }
        return 0;
    }

    public function getMode(int $a_role_id): string
    {
        return isset($this->access_limitations[$a_role_id]) ? $this->access_limitations[$a_role_id]['mode'] : 'null';
    }

    public function setMode(string $a_mode, int $a_role_id): void
    {
        $this->access_limitations[$a_role_id]['mode'] = $a_mode;
    }

    public function getAbsolute(int $a_role_id): int
    {
        return $this->access_limitations[$a_role_id]['absolute'] ?? time();
    }

    public function setAbsolute(?string $date, int $a_role_id): void
    {
        if (!is_null($date)) {
            $unix_date = strtotime($date);
            if ($unix_date) {
                $this->access_limitations[$a_role_id]['absolute'] = $unix_date;
            }
        }
    }

    public function getRelative(int $a_role_id, string $a_type): int
    {
        return $this->access_limitations[$a_role_id]['relative_' . $a_type] ?? 0;
    }

    /**
     * @param array{dd: int|string, MM: int|string}|null $a_arr
     */
    public function setRelative(?array $a_arr, int $a_role_id): void
    {
        if (null === $a_arr) {
            $this->access_limitations[$a_role_id]['relative_d'] = null;
            $this->access_limitations[$a_role_id]['relative_m'] = null;
        } else {
            $this->access_limitations[$a_role_id]['relative_d'] = (int) $a_arr['dd'];
            $this->access_limitations[$a_role_id]['relative_m'] = (int) $a_arr['MM'];
        }
    }

    public function resetAccessLimitations(): void
    {
        $this->access_limitations = [];
    }
}
